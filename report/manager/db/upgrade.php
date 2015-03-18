<?php
/**
 *  Post-install script for the report Competence Manager plugin.
 *
 * Description
 *
 * @package             report
 * @subpackage          manager
 * @copyright           2010 eFaktor
 *
 * @creationDate        03/02/2015
 * @author              eFaktor     (fbv)
 *
 * Update Script
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_report_manager_upgrade($old_version) {
    /* Variables    */
    global $DB;
    $public_companies = null;

    try {
        /* Manager  */
        $db_man = $DB->get_manager();

        if ($old_version < 2015020304) {
            /* Add Public Field */
            $table_company_data = new xmldb_table('report_gen_companydata');
            $field_public       = new xmldb_field('public', XMLDB_TYPE_INTEGER, 1, null, null, null,null,'hierarchylevel');
            if (!$db_man->field_exists($table_company_data, $field_public)) {
                $db_man->add_field($table_company_data, $field_public);
            }//if_exists

            /* Update the Company Data with the correct value   */
            /* First get the public companies   */
            $public_companies = CompetenceManager_Update::GetPublic_Companies();
            if ($public_companies) {
                /* Update Status Public Companies   */
                CompetenceManager_Update::Update_PublicCompanies($public_companies);
            }//if_public_companies

            /* Update Status Private Companies  */
            CompetenceManager_Update::Update_PrivateCompanies();
        }//if_old_Version



        if (!$db_man->table_exists('report_gen_outcome_jobrole')) {
            $table_outcome_job_role = new xmldb_table('report_gen_outcome_jobrole');
            //Adding fields
            /* id           (Primary)                   */
            $table_outcome_job_role->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* outcomeid    (Foreign key - Not null)    */
            $table_outcome_job_role->add_field('outcomeid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            /* jobroleid    (Foreign key - Not null)    */
            $table_outcome_job_role->add_field('jobroleid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            /* modified     (Not null)                  */
            $table_outcome_job_role->add_field('modified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            //Adding Keys
            $table_outcome_job_role->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table_outcome_job_role->add_key('outcomeid',XMLDB_KEY_FOREIGN,array('outcomeid'), 'grade_outcomes', array('id'));
            $table_outcome_job_role->add_key('jobroleid',XMLDB_KEY_FOREIGN,array('jobroleid'), 'report_gen_jobrole', array('id'));

            $db_man->create_table($table_outcome_job_role);
        }//if_table_not_exits

        if (!$db_man->table_exists('report_gen_outcome_exp')) {
            $table_outcome_expiration = new xmldb_table('report_gen_outcome_exp');
            //Adding fields
            /* id               (Primary)                   */
            $table_outcome_expiration->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* outcomeid        (Foreign key - Not null)    */
            $table_outcome_expiration->add_field('outcomeid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            /* expirationperiod (Int - Not null - Index)    */
            $table_outcome_expiration->add_field('expirationperiod',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL,null,0);
            /* modified         (Not null)                  */
            $table_outcome_expiration->add_field('modified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            //Adding Keys
            $table_outcome_expiration->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table_outcome_expiration->add_key('outcomeid',XMLDB_KEY_FOREIGN,array('outcomeid'), 'grade_outcomes', array('id'));
            //Adding Index
            $table_outcome_expiration->add_index('expirationperiod',XMLDB_INDEX_NOTUNIQUE,array('expirationperiod'));

            $db_man->create_table($table_outcome_expiration);
        }//if_table_not_exits

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_Catch
}//xmldb_report_manager_upgrade

class CompetenceManager_Update {

    /**
     * @return          string
     * @throws          Exception
     *
     * @creationDate    03/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the companies are public
     */
    public static function GetPublic_Companies() {
        /* Variables    */
        global $DB;
        $public_companies   = '';
        $info_hierarchy     = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT			co.id 																						as 'level_zero',
                                    GROUP_CONCAT(DISTINCT level_one.companyid ORDER BY level_one.companyid SEPARATOR ',') 		as 'level_one',
                                    GROUP_CONCAT(DISTINCT level_two.companyid ORDER BY level_two.companyid SEPARATOR ',') 		as 'level_two',
                                    GROUP_CONCAT(DISTINCT level_three.companyid ORDER BY level_three.companyid SEPARATOR ',') 	as 'level_three'
                     FROM			{report_gen_companydata}			co
                        JOIN		{counties}						    con 		ON con.idcounty 		= co.industrycode
                        LEFT JOIN 	{report_gen_company_relation} 	    level_one 	ON level_one.parentid 	= co.id
                        LEFT JOIN	{report_gen_company_relation}		level_two	ON level_two.parentid	= level_one.companyid
                        LEFT JOIN	{report_gen_company_relation}		level_three	ON level_three.parentid = level_two.companyid
                     WHERE			co.hierarchylevel = 0
                     GROUP  BY		co.id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Hierarchy */
                    $info_hierarchy = new stdClass();
                    $info_hierarchy->levelZero  = $instance->level_zero;
                    $info_hierarchy->levelOne   = $instance->level_one;
                    $info_hierarchy->levelTwo   = $instance->level_two;
                    $info_hierarchy->levelThree = $instance->level_three;

                    $public_companies[$instance->level_zero] = $info_hierarchy;
                }//for_rdo
            }//if_rdo

            return $public_companies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetPublic_Companies_By_Level

    /**
     * @param           $public_companies
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    03/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the status of public companies
     */
    public static function Update_PublicCompanies($public_companies) {
        /* Variables    */
        global $DB;
        $sqlWhere = '';
        $public   = '';

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {

            /* SQL Instruction  */
            $sql = " UPDATE {report_gen_companydata}
                     SET    public = 1 ";

            foreach ($public_companies as $levelZero) {
                $public = $levelZero->levelZero;
                /* Level One   */
                if ($levelZero->levelOne) {
                    $public .= ',' . $levelZero->levelOne;
                }//if_levelOne
                /* Level Two    */
                if ($levelZero->levelTwo) {
                    $public .= ',' . $levelZero->levelTwo;
                }//if_levelTwo
                /* Level Three  */
                if ($levelZero->levelThree) {
                    $public .= ',' . $levelZero->levelThree;
                }//if_levelThree

                $sqlWhere = " WHERE id IN ($public) ";


                /* Execute  */
                $DB->execute($sql . $sqlWhere);
            }//for_public_companies

            /* Commit  */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Update_PublicCompanies

    /**
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    03/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the status of private companies
     */
    public static function Update_PrivateCompanies() {
        /* Variables    */
        global $DB;

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* SQL Instruction  */
            $sql = " UPDATE {report_gen_companydata}
                     SET    public = 0
                     WHERE  public IS NULL ";

            /* Execute  */
            $DB->execute($sql);

            /* Commit  */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Update_PrivateCompanies
}//CompetenceManager_Update
