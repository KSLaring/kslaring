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
    $publicCompanies        = null;
    $tblCompanyData         = null;
    $fieldPublic            = null;
    $tblOutcomeJobRole      = null;
    $tblOutcomeExpiration   = null;

    try {
        /* Manager  */
        $db_man = $DB->get_manager();

        if ($old_version < 2015020304) {
            /* Add Public Field */
            $tblCompanyData = new xmldb_table('report_gen_companydata');
            $fieldPublic       = new xmldb_field('public', XMLDB_TYPE_INTEGER, 1, null, null, null,null,'hierarchylevel');
            if (!$db_man->field_exists($tblCompanyData, $fieldPublic)) {
                $db_man->add_field($tblCompanyData, $fieldPublic);
            }//if_exists

            /* Update the Company Data with the correct value   */
            /* First get the public companies   */
            $publicCompanies = CompetenceManager_Update::GetPublic_Companies();
            if ($publicCompanies) {
                /* Update Status Public Companies   */
                CompetenceManager_Update::Update_PublicCompanies($publicCompanies);
            }//if_public_companies

            /* Update Status Private Companies  */
            CompetenceManager_Update::Update_PrivateCompanies();
        }//if_old_Version



        if (!$db_man->table_exists('report_gen_outcome_jobrole')) {
            $tblOutcomeJobRole = new xmldb_table('report_gen_outcome_jobrole');
            //Adding fields
            /* id           (Primary)                   */
            $tblOutcomeJobRole->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* outcomeid    (Foreign key - Not null)    */
            $tblOutcomeJobRole->add_field('outcomeid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            /* jobroleid    (Foreign key - Not null)    */
            $tblOutcomeJobRole->add_field('jobroleid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            /* modified     (Not null)                  */
            $tblOutcomeJobRole->add_field('modified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            //Adding Keys
            $tblOutcomeJobRole->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblOutcomeJobRole->add_key('outcomeid',XMLDB_KEY_FOREIGN,array('outcomeid'), 'grade_outcomes', array('id'));
            $tblOutcomeJobRole->add_key('jobroleid',XMLDB_KEY_FOREIGN,array('jobroleid'), 'report_gen_jobrole', array('id'));

            $db_man->create_table($tblOutcomeJobRole);
        }//if_table_not_exits

        if (!$db_man->table_exists('report_gen_outcome_exp')) {
            $tblOutcomeExpiration = new xmldb_table('report_gen_outcome_exp');
            //Adding fields
            /* id               (Primary)                   */
            $tblOutcomeExpiration->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* outcomeid        (Foreign key - Not null)    */
            $tblOutcomeExpiration->add_field('outcomeid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            /* expirationperiod (Int - Not null - Index)    */
            $tblOutcomeExpiration->add_field('expirationperiod',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL,null,0);
            /* modified         (Not null)                  */
            $tblOutcomeExpiration->add_field('modified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            //Adding Keys
            $tblOutcomeExpiration->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblOutcomeExpiration->add_key('outcomeid',XMLDB_KEY_FOREIGN,array('outcomeid'), 'grade_outcomes', array('id'));
            //Adding Index
            $tblOutcomeExpiration->add_index('expirationperiod',XMLDB_INDEX_NOTUNIQUE,array('expirationperiod'));

            $db_man->create_table($tblOutcomeExpiration);
        }//if_table_not_exits

        if ($old_version < 2015083102) {
            if (!$db_man->table_exists('report_gen_competence_imp')) {
                /* New table    */
                $tblCompetencyImport = new xmldb_table('report_gen_competence_imp');
                /* Add fields   */
                /* Id               --> Primary Key.                        */
                $tblCompetencyImport->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                /* Line             --> File line                           */
                $tblCompetencyImport->add_field('line',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                /* Username         --> Username                            */
                $tblCompetencyImport->add_field('username',XMLDB_TYPE_CHAR,'50',null, null, null,null);
                /* User ID                                                  */
                $tblCompetencyImport->add_field('userid',XMLDB_TYPE_CHAR,'50',null, null, null,null);
                /* workplace        --> Level three name                    */
                $tblCompetencyImport->add_field('workplace',XMLDB_TYPE_CHAR,'255',null, null, null,null);
                /* workplace_ic     --> Industry code. Level three          */
                $tblCompetencyImport->add_field('workplace_ic',XMLDB_TYPE_CHAR,'50',null, null, null,null);
                /* workplace_match  --> Level three id                      */
                $tblCompetencyImport->add_field('workplace_match',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                /* sector           --> level two connected with workplace  */
                $tblCompetencyImport->add_field('sector',XMLDB_TYPE_CHAR,'255',null, null, null,null);
                /* sector_match   --> Sector Id. Level Two Id.            */
                $tblCompetencyImport->add_field('sector_match',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                /* jobrole          --> Job role name.                      */
                $tblCompetencyImport->add_field('jobrole',XMLDB_TYPE_CHAR,'255',null, null, null,null);
                /* jobrole_ic       --> Industry code job role.             */
                $tblCompetencyImport->add_field('jobrole_ic',XMLDB_TYPE_CHAR,'50',null, null, null,null);
                /* generic          --> true or false                       */
                $tblCompetencyImport->add_field('generic',XMLDB_TYPE_INTEGER,'1',null, null, null,null);
                /* jobrole_match  --> Job role ID.                        */
                $tblCompetencyImport->add_field('jobrole_match',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                /* delete           --> true/false                          */
                $tblCompetencyImport->add_field('todelete',XMLDB_TYPE_INTEGER,'1',null, null, null,null);
                /* toimport         --> true/false                          */
                $tblCompetencyImport->add_field('toimport',XMLDB_TYPE_INTEGER,'1',null, null, null,null);
                /* error            --> type of error. Message              */
                $tblCompetencyImport->add_field('error',XMLDB_TYPE_CHAR,'255',null, null, null,null);

                /* Adding keys  */
                $tblCompetencyImport->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

                $db_man->create_table($tblCompetencyImport);
            }//if_not_exit_report_gen_competence_imp
        }//if_old_version

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
