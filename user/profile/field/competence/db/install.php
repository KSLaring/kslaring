<?php
/**
 *  Post-install script for Competence extra user profield.
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/comptence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 *      - Create a new table to save the companies and job roles connected to the user
 *      - Move all the old information from rgcoompany and rgjobrole to the new table
 */

function xmldb_profilefield_competence_install() {
    /* Variables    */
    $field_id = null;

    try {
        /* First Create the table   */
        CompetenceProfile_Install::Create_CompetenceTable();
        /* Create User Profile Competence   */
        $field_id   = CompetenceProfile_Install::Create_UserProfileCompetence();

        /* Move the rgcompany and rgjobrole information to the new table    */
        CompetenceProfile_Install::Move_OldProfile_To_Competence($field_id);

    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_profilefield_competence_install

class CompetenceProfile_Install {
    /**
     * @throws          Exception
     *
     * @creationDate    27/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the mdl_user_info_competence table
     */
    public static function Create_CompetenceTable() {
        /* Variables    */
        global $DB;

        try {
            /* Manager  */
            $db_man = $DB->get_manager();

            /* Competence Table */
            if (!$db_man->table_exists('user_info_competence')) {
                /* Create Table */
                $table_competence = new xmldb_table('user_info_competence');
                /* ID               -->     Primary Key. Autonumeric.   */
                $table_competence->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                /* User Id          -->  Foreign Key to user             */
                $table_competence->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                /* Time modified    -->     The last changes    */
                $table_competence->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                /* Primary Keys         */
                $table_competence->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                /* Index / Foreign Key  */
                $table_competence->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

                $db_man->create_table($table_competence);
            }//if_table_exists_competence

            /* Info Competence Data */
            if (!$db_man->table_exists('user_info_competence_data')) {
                /* Create Table */
                $table_competence_data = new xmldb_table('user_info_competence_data');
                /* ID               -->     Primary Key. Autonumeric.   */
                $table_competence_data->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                /* Competence ID    -->     Primary Key. Foreign Key --> user_info_competence.   */
                $table_competence_data->add_field('competenceid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                /* User Id          -->  Foreign Key to user             */
                $table_competence_data->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                /* Company      --> Company. Foreign Key to mdl_report_gen_company_data      */
                $table_competence_data->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                /* Job Roles        -->     Long text. All the job roles connected to the user and the company      */
                $table_competence_data->add_field('jobroles',XMLDB_TYPE_TEXT,null,null, null, null,null);
                /* Time modified    -->     The last changes    */
                $table_competence_data->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                /* Primary Keys         */
                $table_competence_data->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                /* Index / Foreign Key  */
                $table_competence_data->add_key('competenceid',XMLDB_KEY_FOREIGN,array('competenceid'), 'user_info_competence', array('id'));
                $table_competence_data->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

                $db_man->create_table($table_competence_data);
            }//if_table_exists_competence_data
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Create_CompetenceTable

    /**
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    27/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create User Profile Competence
     */
    public static function Create_UserProfileCompetence() {
        /* Variables    */
        global $DB;

        try {
            /* Instance user_info_field */
            $info = new stdClass();
            $info->shortname    = 'competence';
            $info->name         = 'Competence';
            $info->datatype     = 'competence';
            $info->categoryid   = 1;
            $info->required     = 1;
            $info->locked       = 0;
            $info->visible      = 1;
            $info->forceunique  = 0;
            $info->signup       = 0;

            $info->id = $DB->insert_record('user_info_field',$info);

            return $info->id;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Create_UserProfileCompetence

    /**
     * @param           $field_id
     * @throws          Exception
     *
     * @creationDate    27/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Move old profile to the new version
     * rgcompany/rgjobrole --> competence
     */
    public static function Move_OldProfile_To_Competence($field_id) {
        /* Variables    */
        global $DB;
        $time = time();

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* Get The users to move */
            //$users_to_move = self::GetCompaniesJobRoles_By_User();
            //if ($users_to_move) {
            //    foreach ($users_to_move as $user) {
            //        /* Info Competence   */
            //        $competence = new stdClass();
            //        $competence->userid         = $user->id;
            //        $competence->companyid      = $user->companies;
            //        $competence->jobroleid      = $user->jobroles;
            //        $competence->timemodified   = $time;
                    /* Insert User Info Competence  */
            //        $competence->id = $DB->insert_record('user_info_competence',$competence);

                    /* Create  User Info Data   */
            //        $info_data = new stdClass();
            //        $info_data->userid  = $user->id;
            //        $info_data->fieldid = $field_id;
            //        $info_data->data    = $competence->id;
            //        /* Insert  User Info Data   */
            //        $info_data->id = $DB->insert_record('user_info_data',$info_data);
            //    }//for_users_to_move
            //}//if_users

            $DB->delete_records('user_info_field',array('datatype' => 'rgcompany'));
            $DB->delete_records('user_info_field',array('datatype' => 'rgjobrole'));

            $trans->allow_commit();
        }catch (Exception $ex) {
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Move_OldProfile_To_Competence

    /************/
    /* PRIVATE */
    /***********/

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the companies and job roles connected to the user
     */
    private static function GetCompaniesJobRoles_By_User() {
        /* Variables    */
        global $DB;
        $users_info         = array();

        try {
            /* SQL Instruction  */
            $sql = " SELECT		  u.id,
                                  rg_co.data  as 'companies',
                                  rg_jr.data  as 'jobroles'
                     FROM		  {user}				               u
                        LEFT JOIN (
                                    SELECT		uid.userid,
                                                uid.data
                                    FROM		{user_info_data}		uid
                                        JOIN	{user_info_field}		uif		ON		uif.id 			= uid.fieldid
                                                                                AND		uif.datatype	= 'rgcompany'
                                    WHERE       uid.data IS NOT NULL
                                        AND     uid.data != ''
                                  ) rg_co	ON rg_co.userid = u.id
                        LEFT JOIN (
                                    SELECT		uid.userid,
                                                uid.data
                                    FROM		{user_info_data}		uid
                                        JOIN	{user_info_field}		uif		ON		uif.id 			= uid.fieldid
                                                                                AND		uif.datatype	= 'rgjobrole'
                                    WHERE       uid.data IS NOT NULL
                                        AND     uid.data != ''
                                  ) rg_jr	ON rg_jr.userid = u.id
                     WHERE 	 	u.deleted = 0
                        AND		u.username != 'guest'
                     ORDER BY u.id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    if (($instance->companies) && ($instance->jobroles)) {
                        /* Info */
                        $info = new stdClass();
                        $info->id           = $instance->id;
                        $info->companies    = $instance->companies;
                        $info->jobroles     = $instance->jobroles;

                        /* Add User Info    */
                        $users_info[$instance->id] = $info;
                    }//if_data
                }//for_rdo
            }//if_rdo

            return $users_info;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CompaniesJobRoles_By_User
}//CompetenceProfile_Install