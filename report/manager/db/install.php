<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 *  Post-install script for the report Competence Manager plugin.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate        22/01/2015
 * @author          eFaktor     (fbv)
 *
 * Install Script
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Report manager upgrade code.
 */
function xmldb_report_manager_install() {
    /* Variables    */
    global $DB;

    /* Get Manager  */
    $db_man = $DB->get_manager();

    /* Start Transaction    */
    $trans = $DB->start_delegated_transaction();
    try {
        /* Rename Tables Old Version        */
        CompetenceManager_Install::RenameTables_OldVersion($db_man);
        /* Create tables for new version    */
        /* Company Data     */
        CompetenceManager_Install::CreateCompanyData_NewVersion($db_man);
        /* Company Data Relation    */
        CompetenceManager_Install::CreateCompanyDataRelation_NewVersion($db_man);
        /* Job Roles                */
        CompetenceManager_Install::CreateJobRole_NewVersion($db_man);
        /* Job Roles Relation */
        CompetenceManager_Install::CreateJobRoleRelation_NewVersion($db_man);
        /* Job Role Outcomes    */
        CompetenceManager_Install::CreateJobRoleOutcome_Relation($db_man);
        /* Outcomes Expiration  */
        CompetenceManager_Install::CreateOutcomesExpiration($db_man);

        /* Competence Import    */
        CompetenceManager_Install::CreateCompetenceImport($db_man);

        /* Super User   */
        CompetenceManager_Install::CreateSuperUser($db_man);

        /* Temporary table      */
        CompetenceManager_Install::CreateTemporaryTable($db_man);

        /* Manager && Reporter Tables   */
        CompetenceManager_Install::ManagerReporterTables($db_man);

        // Create views
        CompetenceManager_Install::view_companies_with_users();
        CompetenceManager_Install::view_course_company_user_enrol();

        /* For Kommit   */
        /* Level Zero */
        Kommit_CompetenceManager::InsertLevelZero();
        /* Level One    */
        Kommit_CompetenceManager::InsertLevelOne_Østfold();
        Kommit_CompetenceManager::InsertLevelOne_Akershus();
        Kommit_CompetenceManager::InsertLevelOne_Oslo();
        Kommit_CompetenceManager::InsertLevelOne_Hedmark();
        Kommit_CompetenceManager::InsertLevelOne_Oppland();
        Kommit_CompetenceManager::InsertLevelOne_Buskerud();
        Kommit_CompetenceManager::InsertLevelOne_Vestfold();
        Kommit_CompetenceManager::InsertLevelOne_Telemark();
        Kommit_CompetenceManager::InsertLevelOne_AustAgder();
        Kommit_CompetenceManager::InsertLevelOne_VestAgder();
        Kommit_CompetenceManager::InsertLevelOne_Rogaland();
        Kommit_CompetenceManager::InsertLevelOne_Hordaland();
        Kommit_CompetenceManager::InsertLevelOne_Sogn_OG_Fjordane();
        Kommit_CompetenceManager::InsertLevelOne_Møre_OG_Romsdal();
        Kommit_CompetenceManager::InsertLevelOne_SørTrøndelag();
        Kommit_CompetenceManager::InsertLevelOne_NordTrøndelag();
        Kommit_CompetenceManager::InsertLevelOne_Nordland();
        Kommit_CompetenceManager::InsertLevelOne_Troms();
        Kommit_CompetenceManager::InsertLevelOne_Finnmark();
        Kommit_CompetenceManager::InsertLevelOne_Svalbard();

        /* Commit   */
        $trans->allow_commit();

        return true;
    }catch (Exception $ex) {
        /* Roll Back    */
        $trans->rollback($ex);

        return false;
    }//try_catch
}//xmldb_report_manager_install

class CompetenceManager_Install {
    /**
     * Description
     * Create companies_with_users view
     *
     * @throws      Exception
     *
     * @creationDate    22/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function view_companies_with_users() {
        /* Variables */
        global $DB;
        $view       = null;
        $sql        = null;

        try {
            // SQL for the view
            $sql = " SELECT DISTINCT
                              co.id 		  AS 'levelthree',
                              cr_tre.parentid AS 'leveltwo',
                              cr_two.parentid AS 'levelone',
                              cr_one.parentid AS 'levelzero',
                              co.industrycode AS 'industrycode'
                     FROM 	  {report_gen_companydata} co
                        JOIN  {user_info_competence_data} 	uic 	ON uic.companyid 	= co.id
                        JOIN  {report_gen_company_relation} cr_tre  ON cr_tre.companyid = uic.companyid
                        JOIN  {report_gen_company_relation} cr_two  ON cr_two.companyid = cr_tre.parentid
                        JOIN  {report_gen_company_relation} cr_one  ON cr_one.companyid = cr_two.parentid
                     WHERE	co.hierarchylevel = 3
                     ORDER bY co.industrycode, cr_one.parentid,cr_two.parentid,cr_tre.parentid ";

            // Create view
            $view = " CREATE OR REPLACE VIEW companies_with_users 
                                        AS ($sql) ";

            // Execute
            $DB->execute($view);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//view_companies_with_users

    /**
     * Description
     * Create course_company_user_enrol view
     *
     * @throws      Exception
     *
     * @creationDate    22/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function view_course_company_user_enrol() {
        /* Variables */
        global $DB;
        $view       = null;
        $sql        = null;

        try {
            // SQL for view
            $sql = " SELECT       CONCAT(e.courseid,'_',uic.companyid,'_',u.id) AS 'id',
                                  e.courseid                                    AS 'courseid',
                                  uic.companyid                                 AS 'companyid',
                                  u.id                                          AS 'user',
                                  CONCAT(u.firstname, ' ', u.lastname)          AS 'name',
                                  uic.jobroles                                  AS 'jobroles',
                                  IF(cc.timecompleted,cc.timecompleted,0)       AS 'timecompleted'
                     FROM		  {user_enrolments}		      ue
                        JOIN 	  {enrol}					  e   ON  e.id 	 		= ue.enrolid
                                                                  AND e.status 		= 0
                        JOIN 	  {user} 					  u   ON  u.id 	 		= ue.userid
                                                                  AND u.deleted 	= 0
                        JOIN 	  {user_info_competence_data} uic ON  uic.userid  	= u.id
                        JOIN 	  companies_with_users 		  co  ON  co.levelthree = uic.companyid
                        LEFT JOIN {course_completions}	      cc  ON  cc.userid 	= uic.userid
                                                                  AND cc.course 	= e.courseid
                     ORDER BY e.courseid , uic.companyid , u.id ";

            // Create view
            $view = " CREATE OR REPLACE VIEW course_company_user_enrol 
                                        AS ($sql) ";

            // Execute
            $DB->execute($view);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//view_course_company_user_enrol

    /**
     * @param           $db_man
     * @throws          Exception
     *
     * @creationDate    22/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Rename the tables from the old version
     */
    public static function RenameTables_OldVersion($db_man) {
        /* Variables    */
        $tblCompanyData     = null;
        $tblCompanyRelation = null;
        $tblJobRole         = null;
        $tblJobRoleRelation = null;

        try {
            /* Rename Tables    */
            /* Company Data */
            if ($db_man->table_exists('report_gen_companydata')) {
                /* Rename   */
                $tblCompanyData = new xmldb_table('report_gen_companydata');
                $db_man->rename_table($tblCompanyData,'report_gen_companydata_old');
            }//table_company_data

            /* Company Data Relation    */
            if ($db_man->table_exists('report_gen_company_relation')) {
                /* Rename   */
                $tblCompanyRelation = new xmldb_table('report_gen_company_relation');
                $db_man->rename_table($tblCompanyRelation,'report_gen_company_relation_old');
            }//table_company_data_relation

            /* Job Roles    */
            if ($db_man->table_exists('report_gen_jobrole')) {
                /* Rename   */
                $tblJobRole = new xmldb_table('report_gen_jobrole');
                $db_man->rename_table($tblJobRole,'report_gen_jobrole_old');
            }//table_company_data_relation

            /* Job Roles Relation       */
            if ($db_man->table_exists('report_gen_jobrole_relation')) {
                /* Rename   */
                $tblJobRoleRelation = new xmldb_table('report_gen_jobrole_relation');
                $db_man->rename_table($tblJobRoleRelation,'report_gen_jobrole_relation_old');
            }//table_company_data_relation
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//RenameTables_OldVersion

    /**
     * @param           $db_man
     * @throws          Exception
     *
     * @creationDate    22/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create CompanyData table - New Version
     */
    public static function CreateCompanyData_NewVersion($db_man) {
        /* Variables    */
        $tblCompanyData = null;

        try {
            $tblCompanyData = new xmldb_table('report_gen_companydata');
            //Adding fields
            /* id               (Primary)           */
            $tblCompanyData->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* name             (Not null)          */
            $tblCompanyData->add_field('name',XMLDB_TYPE_CHAR,'255',null,XMLDB_NOTNULL,null,null);
            /* naringskode */
            $tblCompanyData->add_field('industrycode',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL,null,null);
            /* hierarchylevel   (Not null - Index)  */
            $tblCompanyData->add_field('hierarchylevel',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL,null,null);
            /* Public           --> Not Null    */
            $tblCompanyData->add_field('public',XMLDB_TYPE_INTEGER,'1',null, null,null,null);
            /* Mapped   */
            $tblCompanyData->add_field('mapped',XMLDB_TYPE_CHAR,'50',null, null,null,null);
            // org_enhet_id
            $tblCompanyData->add_field('org_enhet_id',XMLDB_TYPE_CHAR,'255',null, null,null,null);
            /* ansvar   */
            $tblCompanyData->add_field('ansvar',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* tjeneste */
            $tblCompanyData->add_field('tjeneste',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* adresse1 */
            $tblCompanyData->add_field('adresse1',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* adresse2 */
            $tblCompanyData->add_field('adresse2',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* adresse3 */
            $tblCompanyData->add_field('adresse3',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* postnr   */
            $tblCompanyData->add_field('postnr',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            /* poststed */
            $tblCompanyData->add_field('poststed',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            /* epost    */
            $tblCompanyData->add_field('epost',XMLDB_TYPE_CHAR,'255',null, null, null,null);

            /* modified         (Not null)          */
            $tblCompanyData->add_field('modified',XMLDB_TYPE_INTEGER,'10',null, null,null,null);

            //Adding Keys
            $tblCompanyData->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            //Adding Index
            $tblCompanyData->add_index('hierarchylevel',XMLDB_INDEX_NOTUNIQUE,array('hierarchylevel'));

            if (!$db_man->table_exists('report_gen_companydata')) {
                $db_man->create_table($tblCompanyData);
            }//if_table_not_exits
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateCompanyData_NewVersion

    /**
     * @param           $db_man
     * @throws          Exception
     *
     * @creationDate    22/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create CompanyData Relation table - New Version
     */
    public static function CreateCompanyDataRelation_NewVersion($db_man) {
        /* Variables    */
        $tblCompanyRelation = null;

        try {
            $tblCompanyRelation = new xmldb_table('report_gen_company_relation');
            //Adding fields
            /* id           (Primary)                   */
            $tblCompanyRelation->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* companyid    (Foreign Key - Not null)    */
            $tblCompanyRelation->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            /* parentid     (Foreign Key - Not null)    */
            $tblCompanyRelation->add_field('parentid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            /* modified     (Not null)                  */
            $tblCompanyRelation->add_field('modified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            //Adding Keys
            $tblCompanyRelation->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblCompanyRelation->add_key('companyid',XMLDB_KEY_FOREIGN,array('companyid'), 'report_gen_companydata', array('id'));
            $tblCompanyRelation->add_key('parentid',XMLDB_KEY_FOREIGN,array('parentid'), 'report_gen_companydata', array('id'));

            if (!$db_man->table_exists('report_gen_company_relation')) {
                $db_man->create_table($tblCompanyRelation);
            }//if_table_not_exits
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateCompanyDataRelation_NewVersion

    /**
     * @param           $db_man
     * @throws          Exception
     *
     * @creationDate    22/01/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Create JobRoles table - New Version
     */
    public static function CreateJobRole_NewVersion($db_man) {
        /* Variables    */
        $tblJobRole = null;

        try {
            $tblJobRole = new xmldb_table('report_gen_jobrole');
            //Adding fields
            /* id               (Primary)       */
            $tblJobRole->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* name             (Not null)      */
            $tblJobRole->add_field('name',XMLDB_TYPE_CHAR,'255',null,XMLDB_NOTNULL,null,null);
            /* Industry code    (Not Null)      */
            $tblJobRole->add_field('industrycode',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL,null,null);
            /* modified         (Not null)      */
            $tblJobRole->add_field('modified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            //Adding Keys
            $tblJobRole->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            if (!$db_man->table_exists('report_gen_jobrole')) {
                $db_man->create_table($tblJobRole);
            }//if_table_not_exits
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateJobRole_NewVersion

    /**
     * @param           $db_man
     * @throws          Exception
     *
     * @creationDate    22/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create JobRoles Relation table - New Version
     */
    public static function CreateJobRoleRelation_NewVersion($db_man) {
        /* Variables    */
        $tblJobRoleRelation = null;

        try {
            $tblJobRoleRelation = new xmldb_table('report_gen_jobrole_relation');
            //Adding fields
            /* id               (Primary)       */
            $tblJobRoleRelation->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* name             (Not null)      */
            $tblJobRoleRelation->add_field('jobroleid',XMLDB_TYPE_CHAR,'10',null, null,null,null);
            /* County  */
            $tblJobRoleRelation->add_field('levelzero',XMLDB_TYPE_INTEGER,'10',null, null,null,null);
            /* Level  One */
            $tblJobRoleRelation->add_field('levelone',XMLDB_TYPE_INTEGER,'10',null, null,null,null);
            /* Level  Two */
            $tblJobRoleRelation->add_field('leveltwo',XMLDB_TYPE_INTEGER,'10',null, null,null,null);
            /* Level  Three */
            $tblJobRoleRelation->add_field('levelthree',XMLDB_TYPE_INTEGER,'10',null, null,null,null);
            /* modified         (Not null)      */
            $tblJobRoleRelation->add_field('modified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL,null,null);
            //Adding Keys
            $tblJobRoleRelation->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblJobRoleRelation->add_key('jobroleid',XMLDB_KEY_FOREIGN,array('jobroleid'), 'report_gen_jobrole', array('id'));
            $tblJobRoleRelation->add_key('levelzero',XMLDB_KEY_FOREIGN,array('levelzero'), 'report_gen_companydata', array('id'));
            $tblJobRoleRelation->add_key('levelone',XMLDB_KEY_FOREIGN,array('levelone'), 'report_gen_companydata', array('id'));
            $tblJobRoleRelation->add_key('leveltwo',XMLDB_KEY_FOREIGN,array('leveltwo'), 'report_gen_companydata', array('id'));
            $tblJobRoleRelation->add_key('levelthree',XMLDB_KEY_FOREIGN,array('levelthree'), 'report_gen_companydata', array('id'));

            if (!$db_man->table_exists('report_gen_jobrole_relation')) {
                $db_man->create_table($tblJobRoleRelation);
            }//if_table_not_exits
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateJobRoleRelation_NewVersion

    /**
     * @param           $db_man
     * @throws          Exception
     *
     * @creationDate    22/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create JobRoles Outcome Relation table
     */
    public static function CreateJobRoleOutcome_Relation($db_man) {
        /* Variables    */
        $tblOutcomeJobRole = null;

        try {
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

            if (!$db_man->table_exists('report_gen_outcome_jobrole')) {
                $db_man->create_table($tblOutcomeJobRole);
            }//if_table_not_exits
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateJobRoleOutcome_Relation

    /**
     * @param           $db_man
     * @throws          Exception
     *
     * @creationDate    22/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create Outcomes Expiration table
     */
    public static function CreateOutcomesExpiration($db_man) {
        /* Variables    */
        $tblOutcomeExpiration = null;

        try {
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

            if (!$db_man->table_exists('report_gen_outcome_exp')) {
                $db_man->create_table($tblOutcomeExpiration);
            }//if_table_not_exits
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateOutcomesExpiration

    /**
     * @param           $db_man
     * @throws          Exception
     *
     * @creationDate    24/08/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create Competence Import Table
     */
    public static function CreateCompetenceImport($db_man) {
        /* Variables    */
        $tblCompetencyImport = null;

        try {
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
            
            if (!$db_man->table_exists('report_gen_competence_imp')) {
                $db_man->create_table($tblCompetencyImport);
            }//if_not_exist
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateCompetenceImport

    /**
     * @param           $db_man
     * @throws          Exception
     *
     * @creationDate    14/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create Super user table
     */
    public static function CreateSuperUser($db_man) {
        /* Variables    */
        $tblSuperUser = null;

        try {
            /* New Table    */
            $tblSuperUser = new xmldb_table('report_gen_super_user');
            /* Add fields   */
            /* Id           --  Primary Key */
            $tblSuperUser->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* userid       --  Foreign Key */
            $tblSuperUser->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* County  */
            $tblSuperUser->add_field('levelzero',XMLDB_TYPE_INTEGER,'10',null, null,null,null);
            /* Level  One */
            $tblSuperUser->add_field('levelone',XMLDB_TYPE_INTEGER,'10',null, null,null,null);
            /* Level  Two */
            $tblSuperUser->add_field('leveltwo',XMLDB_TYPE_INTEGER,'10',null, null,null,null);
            /* Level  Three */
            $tblSuperUser->add_field('levelthree',XMLDB_TYPE_INTEGER,'10',null, null,null,null);

            /* Adding keys  */
            $tblSuperUser->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblSuperUser->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));
            $tblSuperUser->add_key('levelzero',XMLDB_KEY_FOREIGN,array('levelzero'), 'report_gen_companydata', array('id'));

            /* Create Table */
            if (!$db_man->table_exists('report_gen_super_user')) {
                $db_man->create_table($tblSuperUser);
            }//if_not_exist
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateSuperUser

    /**
     * @param           $db_man
     *
     * @throws          Exception
     *
     * @creationDate    30/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Temporary table to save the data connected with outcome and course report
     */
    public static function CreateTemporaryTable($db_man) {
        /* Variables    */
        $tblTemporary = null;

        try {
            /* New Table    */
            $tblTemporary = new xmldb_table('report_gen_temp');

            /* Add Fields   */
            /* Id               --> Primary Key */
            $tblTemporary->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* manager          --> Foreign Key. Who ask for the report     */
            $tblTemporary->add_field('manager',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* report   */
            $tblTemporary->add_field('report',XMLDB_TYPE_CHAR,'25',null, XMLDB_NOTNULL, null,null);
            /* userid           --> Foreign Key. User id                    */
            $tblTemporary->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* Name */
            $tblTemporary->add_field('name',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* companyid        --> Foreign Key. Company id                 */
            $tblTemporary->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, null,null,null);
            /* courseid         --> Foreign Key. Course id                  */
            $tblTemporary->add_field('courseid',XMLDB_TYPE_INTEGER,'10',null, null,null,null);
            /* outcomeid        --> Foreign Key. Outcome id                 */
            $tblTemporary->add_field('outcomeid',XMLDB_TYPE_INTEGER,'10',null, null,null,null);
            /* completed        --> Null. Boolean. Course Completed         */
            $tblTemporary->add_field('completed',XMLDB_TYPE_INTEGER,'1',null, null,null,null);
            /* notcompleted     --> Null. Boolean. Course Not Completed     */
            $tblTemporary->add_field('notcompleted',XMLDB_TYPE_INTEGER,'1',null, null,null,null);
            /* notenrol         --> Null. Boolean.                          */
            $tblTemporary->add_field('notenrol',XMLDB_TYPE_INTEGER,'1',null, null,null,null);
            /* timecompleted    --> Null.                                   */
            $tblTemporary->add_field('timecompleted',XMLDB_TYPE_INTEGER,'10',null, null,null,null);

            /* Adding Keys  */
            $tblTemporary->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblTemporary->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));
            $tblTemporary->add_key('courseid',XMLDB_KEY_FOREIGN,array('courseid'), 'course', array('id'));
            $tblTemporary->add_key('companyid',XMLDB_KEY_FOREIGN,array('companyid'), 'report_gen_companydata', array('id'));

            /* Create Table */
            if (!$db_man->table_exists('report_gen_temp')) {
                $db_man->create_table($tblTemporary);
            }//if_not_exist
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateTemporaryTable

    /**
     * @param           $db_man
     * @throws          Exception
     *
     * @creationDate    21/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create manager and reporter tables.
     */
    public static function ManagerReporterTables($db_man) {
        /* Variables */
        $tblManagerTable    = null;
        $tblReporterTable   = null;

        try {
            /* New Table    */
            /* Manager      */
            $tblManagerTable = new xmldb_table('report_gen_company_manager');
            /* Add Fields   */
            /* Id --> Primary Key   */
            $tblManagerTable->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* managerid        */
            $tblManagerTable->add_field('managerid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* levelzero        */
            $tblManagerTable->add_field('levelzero',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* levelone        */
            $tblManagerTable->add_field('levelone',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* leveltwo        */
            $tblManagerTable->add_field('leveltwo',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* levelthree        */
            $tblManagerTable->add_field('levelthree',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* hierarchylevel   */
            $tblManagerTable->add_field('hierarchylevel',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* timecreated      */
            $tblManagerTable->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            /* Adding Keys  */
            $tblManagerTable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblManagerTable->add_key('managerid',XMLDB_KEY_FOREIGN,array('managerid'), 'user', array('id'));
            $tblManagerTable->add_key('levelzero',XMLDB_KEY_FOREIGN,array('levelzero'), 'report_gen_companydata', array('id'));

            /* Create Table */
            if (!$db_man->table_exists('report_gen_company_manager')) {
                $db_man->create_table($tblManagerTable);
            }//if_not_exist

            /* New Table    */
            /* Reporter     */
            $tblReporterTable = new xmldb_table('report_gen_company_reporter');
            /* Add Fields   */
            /* Id --> Primary Key   */
            $tblReporterTable->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* reporterid        */
            $tblReporterTable->add_field('reporterid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* levelzero        */
            $tblReporterTable->add_field('levelzero',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* levelone        */
            $tblReporterTable->add_field('levelone',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* leveltwo        */
            $tblReporterTable->add_field('leveltwo',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* levelthree        */
            $tblReporterTable->add_field('levelthree',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* hierarchylevel   */
            $tblReporterTable->add_field('hierarchylevel',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* timecreated      */
            $tblReporterTable->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            /* Adding Keys  */
            $tblReporterTable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblReporterTable->add_key('reporterid',XMLDB_KEY_FOREIGN,array('reporterid'), 'user', array('id'));
            $tblReporterTable->add_key('levelzero',XMLDB_KEY_FOREIGN,array('levelzero'), 'report_gen_companydata', array('id'));

            /* Create Table */
            if (!$db_man->table_exists('report_gen_company_reporter')) {
                $db_man->create_table($tblReporterTable);
            }//if_not_exist
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ManagerReporterTables

}//CompetenceManager_Install

class Kommit_CompetenceManager {
    public static function InsertLevelZero() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                    " VALUES ('01','Østfold',0,1,"             . $time . "), " .
                    "        ('02','Akershus',0,1,"            . $time . "), " .
                    "        ('03','Oslo',0,1,"                . $time . "), " .
                    "        ('04','Hedmark',0,1,"             . $time . "), " .
                    "        ('05','Oppland',0,1,"             . $time . "), " .
                    "        ('06','Buskerud',0,1,"            . $time . "), " .
                    "        ('07','Vestfold',0,1,"            . $time . "), " .
                    "        ('08','Telemark',0,1,"            . $time . "), " .
                    "        ('09','Aust-Agder',0,1,"          . $time . "), " .
                    "        ('10','Vest-Agder',0,1,"          . $time . "), " .
                    "        ('11','Rogaland',0,1,"            . $time . "), " .
                    "        ('12','Hordaland',0,1,"           . $time . "), " .
                    "        ('14','Sogn og Fjordane',0,1,"    . $time . "), " .
                    "        ('15','Møre og Romsdal',0,1,"     . $time . "), " .
                    "        ('16','Sør-Trøndelag',0,1,"       . $time . "), " .
                    "        ('17','Nord-Trøndelag',0,1,"      . $time . "), " .
                    "        ('18','Nordland',0,1,"            . $time . "), " .
                    "        ('19','Troms',0,1,"               . $time . "), " .
                    "        ('20','Finnmark',0,1,"            . $time . "), " .
                    "        ('21','Svalbard',0,1,"            . $time . ") ";

            $DB->execute($sql);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//InsertLevelZero

    public static function InsertLevelOne_Østfold() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                    " VALUES ('01','Østfold Fylkeskommune',1,1,"          . $time . "), " .
                    "        ('0124','Askim',1,1,"                        . $time . "), " .
                    "        ('0125','Eidsberg',1,1,"                     . $time . "), " .
                    "        ('0106','Fredrikstad',1,1,"                  . $time . "), " .
                    "        ('0101','Halden',1,1,"                       . $time . "), " .
                    "        ('0138','Hobøl',1,1,"                        . $time . "), " .
                    "        ('0111','Hvaler',1,1,"                       . $time . "), " .
                    "        ('0119','Marker',1,1,"                       . $time . "), " .
                    "        ('0104','Moss',1,1,"                         . $time . "), " .
                    "        ('0128','Rakkestad',1,1,"                    . $time . "), " .
                    "        ('0136','Rygge',1,1,"                        . $time . "), " .
                    "        ('0121','Rømskog',1,1,"                      . $time . "), " .
                    "        ('0135','Råde',1,1,"                         . $time . "), " .
                    "        ('0105','Sarpsborg',1,1,"                    . $time . "), " .
                    "        ('0127','Skiptvet',1,1,"                     . $time . "), " .
                    "        ('0123','Spydeberg',1,1,"                    . $time . "), " .
                    "        ('0122','Trøgstad',1,1,"                     . $time . "), " .
                    "        ('0137','Våler',1,1,"                        . $time . ") ";

            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '01'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('01','0124','0125','0106','0101','0138','0111','0119','0104','0128','0136','0121','0135','0105','0127','0123','0122','0137') ";
            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Østfold

    public static function InsertLevelOne_Akershus() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                    " VALUES ('02','Akershus Fylkeskommune',1,1," . $time . "), " .
                    "        ('0220','Asker',1,1,"                . $time . "), " .
                    "        ('0221','Aurskog-Høland',1,1,"       . $time . "), " .
                    "        ('0219','Bærum',1,1,"                . $time . "), " .
                    "        ('0237','Eidsvoll',1,1,"             . $time . "), " .
                    "        ('0229','Enebakk',1,1,"              . $time . "), " .
                    "        ('0227','Fet',1,1,"                  . $time . "), " .
                    "        ('0215','Frogn',1,1,"                . $time . "), " .
                    "        ('0234','Gjerdrum',1,1,"             . $time . "), " .
                    "        ('0239','Hurdal',1,1,"               . $time . "), " .
                    "        ('0230','Lørenskog',1,1,"            . $time . "), " .
                    "        ('0238','Nannestad',1,1,"            . $time . "), " .
                    "        ('0236','Nes',1,1,"                  . $time . "), " .
                    "        ('0216','Nesodden',1,1,"             . $time . "), " .
                    "        ('0233','Nittedal',1,1,"             . $time . "), " .
                    "        ('0217','Oppegård',1,1,"             . $time . "), " .
                    "        ('0228','Rælingen',1,1,"             . $time . "), " .
                    "        ('0231','Skedsmo',1,1,"              . $time . "), " .
                    "        ('0213','Ski',1,1,"                  . $time . "), " .
                    "        ('0226','Sørum',1,1,"                . $time . "), " .
                    "        ('0235','Ullensaker',1,1,"           . $time . "), " .
                    "        ('0211','Vestby',1,1,"               . $time . "), " .
                    "        ('0214','Ås',1,1,"                   . $time . ") ";

            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '02'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('02','0211','0213','0214','0215','0216','0217','0219','0220','0221','0226','0227','0228','0229','0230','0231','0233','0234','0235','0236','0237','0238','0239') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//install_Akershus

    public static function InsertLevelOne_Oslo() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                    " VALUES ('03','Oslo Fylkeskommune',1,1," . $time . "), " .
                    "        ('0301','Oslo',1,1,"             . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '03'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('03','0301') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Oslo

    public static function InsertLevelOne_Hedmark() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                    " VALUES ('04','Hedmark Fylkeskommune',1,1,"  . $time . "), " .
                    "        ('0438','Alvdal',1,1,"               . $time . "), " .
                    "        ('0420','Eidskog',1,1,"              . $time . "), " .
                    "        ('0427','Elverum',1,1,"              . $time . "), " .
                    "        ('0434','Engerdal',1,1,"             . $time . "), " .
                    "        ('0439','Folldal',1,1,"              . $time . "), " .
                    "        ('0423','Grue',1,1,"                 . $time . "), " .
                    "        ('0403','Hamar',1,1,"                . $time . "), " .
                    "        ('0402','Kongsvinger',1,1,"          . $time . "), " .
                    "        ('0415','Løten',1,1,"                . $time . "), " .
                    "        ('0418','Nord-Odal',1,1,"            . $time . "), " .
                    "        ('0441','Os',1,1,"                   . $time . "), " .
                    "        ('0432','Rendalen',1,1,"             . $time . "), " .
                    "        ('0412','Ringsaker',1,1,"            . $time . "), " .
                    "        ('0417','Stange',1,1,"               . $time . "), " .
                    "        ('0430','Stor-Elvdal',1,1,"          . $time . "), " .
                    "        ('0419','Sør-Odal',1,1,"             . $time . "), " .
                    "        ('0436','Tolga',1,1,"                . $time . "), " .
                    "        ('0428','Trysil',1,1,"               . $time . "), " .
                    "        ('0437','Tynset',1,1,"               . $time . "), " .
                    "        ('0426','Våler',1,1,"                . $time . "), " .
                    "        ('0429','Åmot',1,1,"                 . $time . "), " .
                    "        ('0425','Åsnes',1,1,"                . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '04'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('04','0402','0403','0412','0415','0417','0418','0419','0420','0423','0425','0426','0427','0428','0429','0430','0432','0434','0436','0437','0438','0439','0441') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Hedmark

    public static function InsertLevelOne_Oppland() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                    " VALUES ('05','Oppland Fylkeskommune',1,1,"  . $time . "), " .
                    "        ('0511','Dovre',1,1,"                . $time . "), " .
                    "        ('0541','Etnedal',1,1,"              . $time . "), " .
                    "        ('0522','Gausdal',1,1,"              . $time . "), " .
                    "        ('0502','Gjøvik',1,1,"               . $time . "), " .
                    "        ('0534','Gran',1,1,"                 . $time . "), " .
                    "        ('0532','Jevnaker',1,1,"             . $time . "), " .
                    "        ('0512','Lesja',1,1,"                . $time . "), " .
                    "        ('0501','Lillehammer',1,1,"          . $time . "), " .
                    "        ('0514','Lom',1,1,"                  . $time . "), " .
                    "        ('0533','Lunner',1,1,"               . $time . "), " .
                    "        ('0542','Nord-Aurdal',1,1,"          . $time . "), " .
                    "        ('0516','Nord-Fron',1,1,"            . $time . "), " .
                    "        ('0538','Nordre Land',1,1,"          . $time . "), " .
                    "        ('0520','Ringebu',1,1,"              . $time . "), " .
                    "        ('0517','Sel',1,1,"                  . $time . "), " .
                    "        ('0513','Skjåk',1,1,"                . $time . "), " .
                    "        ('0536','Søndre Land',1,1,"          . $time . "), " .
                    "        ('0540','Sør-Aurdal',1,1,"           . $time . "), " .
                    "        ('0519','Sør-Fron',1,1,"             . $time . "), " .
                    "        ('0545','Vang',1,1,"                 . $time . "), " .
                    "        ('0543','Vestre Slidre',1,1,"        . $time . "), " .
                    "        ('0529','Vestre Toten',1,1,"         . $time . "), " .
                    "        ('0515','Vågå',1,1,"                 . $time . "), " .
                    "        ('0528','Østre Toten',1,1,"          . $time . "), " .
                    "        ('0521','Øyer',1,1,"                 . $time . "), " .
                    "        ('0544','Øystre Slidre',1,1,"        . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '05'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('05','0501','0502','0511','0512','0513','0514','0515','0516','0517','0519','0520','0521','0522','0528','0529','0532','0533','0534','0536','0538','0540','0541','0542','0543','0544','0545') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Oppland

    public static function InsertLevelOne_Buskerud() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                    " VALUES ('06','Buskerud Fylkeskommune',1,1," . $time . "), " .
                    "        ('0602','Drammen',1,1,"              . $time . "), " .
                    "        ('0631','Flesberg',1,1,"             . $time . "), " .
                    "        ('0615','Flå',1,1,"                  . $time . "), " .
                    "        ('0617','Gol',1,1,"                  . $time . "), " .
                    "        ('0618','Hemsedal',1,1,"             . $time . "), " .
                    "        ('0620','Hol',1,1,"                  . $time . "), " .
                    "        ('0612','Hole',1,1,"                 . $time . "), " .
                    "        ('0628','Hurum',1,1,"                . $time . "), " .
                    "        ('0604','Kongsberg',1,1,"            . $time . "), " .
                    "        ('0622','Krødsherad',1,1,"           . $time . "), " .
                    "        ('0626','Lier',1,1,"                 . $time . "), " .
                    "        ('0623','Modum',1,1,"                . $time . "), " .
                    "        ('0625','Nedre Eiker',1,1,"          . $time . "), " .
                    "        ('0616','Nes',1,1,"                  . $time . "), " .
                    "        ('0633','Nore og Uvdal',1,1,"        . $time . "), " .
                    "        ('0605','Ringerike',1,1,"            . $time . "), " .
                    "        ('0632','Rollag',1,1,"               . $time . "), " .
                    "        ('0627','Røyken',1,1,"               . $time . "), " .
                    "        ('0621','Sigdal',1,1,"               . $time . "), " .
                    "        ('0624','Øvre Eiker',1,1,"           . $time . "), " .
                    "        ('0619','Ål',1,1,"                   . $time . ")  ";
            /* Execute */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '06'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('06','0602','0604','0605','0612','0615','0616','0617','0618','0619','0620','0621','0622','0623','0624','0625','0626','0627','0628','0631','0632','0633') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Buskerud

    public static function InsertLevelOne_Vestfold() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                    " VALUES ('07','Vestfold Fylkeskommune',1,1," . $time . "), " .
                    "        ('0719','Andebu',1,1,"               . $time . "), " .
                    "        ('0714','Hof',1,1,"                  . $time . "), " .
                    "        ('0702','Holmestrand',1,1,"          . $time . "), " .
                    "        ('0701','Horten',1,1,"               . $time . "), " .
                    "        ('0728','Lardal',1,1,"               . $time . "), " .
                    "        ('0709','Larvik',1,1,"               . $time . "), " .
                    "        ('0722','Nøtterøy',1,1,"             . $time . "), " .
                    "        ('0716','Re',1,1,"                   . $time . "), " .
                    "        ('0713','Sande',1,1,"                . $time . "), " .
                    "        ('0706','Sandefjord',1,1,"           . $time . "), " .
                    "        ('0720','Stokke',1,1,"               . $time . "), " .
                    "        ('0711','Svelvik',1,1,"              . $time . "), " .
                    "        ('0723','Tjøme',1,1,"                . $time . "), " .
                    "        ('0704','Tønsberg',1,1,"             . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '07'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('07','0701','0702','0704','0706','0709','0711','0713','0714','0716','0719','0720','0722','0723','0728') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Vestfold

    public static function InsertLevelOne_Telemark() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                    " VALUES ('08','Telemark Fylkeskommune',1,1," . $time . "), " .
                    "        ('0814','Bamble',1,1,"               . $time . "), " .
                    "        ('0821','Bø',1,1,"                   . $time . "), " .
                    "        ('0817','Drangedal',1,1,"            . $time . "), " .
                    "        ('0831','Fyresdal',1,1,"             . $time . "), " .
                    "        ('0827','Hjartdal',1,1,"             . $time . "), " .
                    "        ('0815','Kragerø',1,1,"              . $time . "), " .
                    "        ('0829','Kviteseid',1,1,"            . $time . "), " .
                    "        ('0830','Nissedal',1,1,"             . $time . "), " .
                    "        ('0819','Nome',1,1,"                 . $time . "), " .
                    "        ('0807','Notodden',1,1,"             . $time . "), " .
                    "        ('0805','Porsgrunn',1,1,"            . $time . "), " .
                    "        ('0822','Sauherad',1,1,"             . $time . "), " .
                    "        ('0828','Seljord',1,1,"              . $time . "), " .
                    "        ('0811','Siljan',1,1,"               . $time . "), " .
                    "        ('0806','Skien',1,1,"                . $time . "), " .
                    "        ('0826','Tinn',1,1,"                 . $time . "), " .
                    "        ('0833','Tokke',1,1,"                . $time . "), " .
                    "        ('0834','Vinje',1,1,"                . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '08'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('08','0805','0806','0807','0811','0814','0815','0817','0819','0821','0822','0826','0827','0828','0829','0830','0831','0833','0834') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch


    }//install_Telemark

    public static function InsertLevelOne_AustAgder() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                    " VALUES ('09','Aust-Agder Fylkeskommune',1,1,"   . $time . "), " .
                    "        ('0906','Arendal',1,1,"                  . $time . "), " .
                    "        ('0928','Birkenes',1,1,"                 . $time . "), " .
                    "        ('0938','Bygland',1,1,"                  . $time . "), " .
                    "        ('0941','Bykle',1,1,"                    . $time . "), " .
                    "        ('0937','Evje og Hornnes',1,1,"          . $time . "), " .
                    "        ('0919','Froland',1,1,"                  . $time . "), " .
                    "        ('0911','Gjerstad',1,1,"                 . $time . "), " .
                    "        ('0904','Grimstad',1,1,"                 . $time . "), " .
                    "        ('0935','Iveland',1,1,"                  . $time . "), " .
                    "        ('0926','Lillesand',1,1,"                . $time . "), " .
                    "        ('0901','Risør',1,1,"                    . $time . "), " .
                    "        ('0914','Tvedestrand',1,1,"              . $time . "), " .
                    "        ('0940','Valle',1,1,"                    . $time . "), " .
                    "        ('0912','Vegårshei',1,1,"                . $time . "), " .
                    "        ('0929','Åmli',1,1,"                     . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '09'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('09','0901','0904','0906','0911','0912','0914','0919','0926','0928','0929','0935','0937','0938','0940','0941') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Aust_Agder

    public static function InsertLevelOne_VestAgder() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                " VALUES ('10','Vest-Agder Fylkeskommune',1,1,"   . $time . "), " .
                "        ('1027','Audnedal',1,1,"                 . $time . "), " .
                "        ('1003','Farsund',1,1,"                  . $time . "), " .
                "        ('1004','Flekkefjord',1,1,"              . $time . "), " .
                "        ('1034','Hægebostad',1,1,"               . $time . "), " .
                "        ('1001','Kristiansand',1,1,"             . $time . "), " .
                "        ('1037','Kvinesdal',1,1,"                . $time . "), " .
                "        ('1029','Lindesnes',1,1,"                . $time . "), " .
                "        ('1032','Lyngdal',1,1,"                  . $time . "), " .
                "        ('1002','Mandal',1,1,"                   . $time . "), " .
                "        ('1021','Marnardal',1,1,"                . $time . "), " .
                "        ('1046','Sirdal',1,1,"                   . $time . "), " .
                "        ('1017','Songdalen',1,1,"                . $time . "), " .
                "        ('1018','Søgne',1,1,"                    . $time . "), " .
                "        ('1014','Vennesla',1,1,"                 . $time . "), " .
                "        ('1026','Åseral',1,1,"                   . $time . ") ";
            /* Execute  */
            $DB->execute($sql);


            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '10'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('10','1001','1002','1003','1004','1014','1017','1018','1021','1026','1027','1029','1032','1034','1037','1046') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Vest_Agder

    public static function InsertLevelOne_Rogaland() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                " VALUES ('11','Rogaland Fylkeskommune',1,1," . $time . "), " .
                "        ('1114','Bjerkreim',1,1,"            . $time . "), " .
                "        ('1145','Bokn',1,1,"                 . $time . "), " .
                "        ('1101','Eigersund',1,1,"            . $time . "), " .
                "        ('1141','Finnøy',1,1,"               . $time . "), " .
                "        ('1129','Forsand',1,1,"              . $time . "), " .
                "        ('1122','Gjesdal',1,1,"              . $time . "), " .
                "        ('1106','Haugesund',1,1,"            . $time . "), " .
                "        ('1133','Hjelmeland',1,1,"           . $time . "), " .
                "        ('1119','Hå',1,1,"                   . $time . "), " .
                "        ('1149','Karmøy',1,1,"               . $time . "), " .
                "        ('1120','Klepp',1,1,"                . $time . "), " .
                "        ('1144','Kvitsøy',1,1,"              . $time . "), " .
                "        ('1112','Lund',1,1,"                 . $time . "), " .
                "        ('1127','Randaberg',1,1,"            . $time . "), " .
                "        ('1142','Rennesøy',1,1,"             . $time . "), " .
                "        ('1102','Sandnes',1,1,"              . $time . "), " .
                "        ('1135','Sauda',1,1,"                . $time . "), " .
                "        ('1111','Sokndal',1,1,"              . $time . "), " .
                "        ('1124','Sola',1,1,"                 . $time . "), " .
                "        ('1103','Stavanger',1,1,"            . $time . "), " .
                "        ('1130','Strand',1,1,"               . $time . "), " .
                "        ('1134','Suldal',1,1,"               . $time . "), " .
                "        ('1121','Time',1,1,"                 . $time . "), " .
                "        ('1146','Tysvær',1,1,"               . $time . "), " .
                "        ('1151','Utsira',1,1,"               . $time . "), " .
                "        ('1160','Vindafjord',1,1,"           . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '11'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('11','1101','1102','1103','1106','1111','1112','1114','1119','1120','1121','1122','1124','1127','1129','1130','1133','1134','1135','1141','1142','1144','1145','1146','1149','1151','1160') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Rogaland

    public static function InsertLevelOne_Hordaland() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                " VALUES ('12','Hordaland Fylkeskommune',1,1,"    . $time . "), " .
                "        ('1247','Askøy',1,1,"                    . $time . "), " .
                "        ('1244','Austevoll',1,1,"                . $time . "), " .
                "        ('1264','Austrheim',1,1,"                . $time . "), " .
                "        ('1201','Bergen',1,1,"                   . $time . "), " .
                "        ('1219','Bømlo',1,1,"                    . $time . "), " .
                "        ('1232','Eidfjord',1,1,"                 . $time . "), " .
                "        ('1211','Etne',1,1,"                     . $time . "), " .
                "        ('1265','Fedje',1,1,"                    . $time . "), " .
                "        ('1222','Fitjar',1,1,"                   . $time . "), " .
                "        ('1246','Fjell',1,1,"                    . $time . "), " .
                "        ('1241','Fusa',1,1,"                     . $time . "), " .
                "        ('1234','Granvin',1,1,"                  . $time . "), " .
                "        ('1227','Jondal',1,1,"                   . $time . "), " .
                "        ('1238','Kvam',1,1,"                     . $time . "), " .
                "        ('1224','Kvinnherad',1,1,"               . $time . "), " .
                "        ('1263','Lindås',1,1,"                   . $time . "), " .
                "        ('1266','Masfjorden',1,1,"               . $time . "), " .
                "        ('1256','Meland',1,1,"                   . $time . "), " .
                "        ('1252','Modalen',1,1,"                  . $time . "), " .
                "        ('1228','Odda',1,1,"                     . $time . "), " .
                "        ('1243','Os',1,1,"                       . $time . "), " .
                "        ('1253','Osterøy',1,1,"                  . $time . "), " .
                "        ('1260','Radøy',1,1,"                    . $time . "), " .
                "        ('1242','Samnanger',1,1,"                . $time . "), " .
                "        ('1221','Stord',1,1,"                    . $time . "), " .
                "        ('1245','Sund',1,1,"                     . $time . "), " .
                "        ('1216','Sveio',1,1,"                    . $time . "), " .
                "        ('1223','Tysnes',1,1,"                   . $time . "), " .
                "        ('1231','Ullensvang',1,1,"               . $time . "), " .
                "        ('1233','Ulvik',1,1,"                    . $time . "), " .
                "        ('1251','Vaksdal',1,1,"                  . $time . "), " .
                "        ('1235','Voss',1,1,"                     . $time . "), " .
                "        ('1259','Øygarden',1,1,"                 . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '12'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('12','1201','1211','1216','1219','1221','1222','1223','1224','1227','1228','1231','1232','1233','1234','1235','1238','1241','1242','1243','1244','1245','1246','1247','1251','1252','1253','1256','1259','1260','1263','1264','1265','1266') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Hordaland

    public static function InsertLevelOne_Sogn_OG_Fjordane() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                " VALUES ('14','Sogn og Fjordane Fylkeskommune',1,1,"     . $time . "), " .
                "        ('1428','Askvoll',1,1,"                          . $time . "), " .
                "        ('1421','Aurland',1,1,"                          . $time . "), " .
                "        ('1418','Balestrand',1,1,"                       . $time . "), " .
                "        ('1438','Bremanger',1,1,"                        . $time . "), " .
                "        ('1443','Eid',1,1,"                              . $time . "), " .
                "        ('1429','Fjaler',1,1,"                           . $time . "), " .
                "        ('1401','Flora',1,1,"                            . $time . "), " .
                "        ('1432','Førde',1,1,"                            . $time . "), " .
                "        ('1430','Gaular',1,1,"                           . $time . "), " .
                "        ('1445','Gloppen',1,1,"                          . $time . "), " .
                "        ('1411','Gulen',1,1,"                            . $time . "), " .
                "        ('1444','Hornindal',1,1,"                        . $time . "), " .
                "        ('1413','Hyllestad',1,1,"                        . $time . "), " .
                "        ('1416','Høyanger',1,1,"                         . $time . "), " .
                "        ('1431','Jølster',1,1,"                          . $time . "), " .
                "        ('1419','Leikanger',1,1,"                        . $time . "), " .
                "        ('1426','Luster',1,1,"                           . $time . "), " .
                "        ('1422','Lærdal',1,1,"                           . $time . "), " .
                "        ('1433','Naustdal',1,1,"                         . $time . "), " .
                "        ('1441','Selje',1,1,"                            . $time . "), " .
                "        ('1420','Sogndal',1,1,"                          . $time . "), " .
                "        ('1412','Solund',1,1,"                           . $time . "), " .
                "        ('1449','Stryn',1,1,"                            . $time . "), " .
                "        ('1417','Vik',1,1,"                              . $time . "), " .
                "        ('1439','Vågsøy',1,1,"                           . $time . "), " .
                "        ('1424','Årdal',1,1,"                            . $time . ") ";
            /* Execute */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '14'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('14','1401','1411','1412','1413','1416','1417','1418','1419','1420','1421','1422','1424','1426','1428','1429','1430','1431','1432','1433','1438','1439','1441','1443','1444','1445','1449') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Sogn_og_Fjordane

    public static function InsertLevelOne_Møre_OG_Romsdal() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                " VALUES ('15','Møre og Romsdal Fylkeskommune',1,1,"          . $time . "), " .
                "        ('1547','Aukra',1,1,"                                . $time . "), " .
                "        ('1576','Aure',1,1,"                                 . $time . "), " .
                "        ('1554','Averøy',1,1,"                               . $time . "), " .
                "        ('1551','Eide',1,1,"                                 . $time . "), " .
                "        ('1548','Fræna',1,1,"                                . $time . "), " .
                "        ('1532','Giske',1,1,"                                . $time . "), " .
                "        ('1557','Gjemnes',1,1,"                              . $time . "), " .
                "        ('1571','Halsa',1,1,"                                . $time . "), " .
                "        ('1534','Haram',1,1,"                                . $time . "), " .
                "        ('1517','Hareid',1,1,"                               . $time . "), " .
                "        ('1515','Herøy',1,1,"                                . $time . "), " .
                "        ('1505','Kristiansund',1,1,"                         . $time . "), " .
                "        ('1545','Midsund',1,1,"                              . $time . "), " .
                "        ('1502','Molde',1,1,"                                . $time . "), " .
                "        ('1543','Nesset',1,1,"                               . $time . "), " .
                "        ('1524','Norddal',1,1,"                              . $time . "), " .
                "        ('1539','Rauma',1,1,"                                . $time . "), " .
                "        ('1567','Rindal',1,1,"                               . $time . "), " .
                "        ('1514','Sande',1,1,"                                . $time . "), " .
                "        ('1546','Sandøy',1,1,"                               . $time . "), " .
                "        ('1529','Skodje',1,1,"                               . $time . "), " .
                "        ('1573','Smøla',1,1,"                                . $time . "), " .
                "        ('1526','Stordal',1,1,"                              . $time . "), " .
                "        ('1525','Stranda',1,1,"                              . $time . "), " .
                "        ('1531','Sula',1,1,"                                 . $time . "), " .
                "        ('1563','Sunndal',1,1,"                              . $time . "), " .
                "        ('1566','Surnadal',1,1,"                             . $time . "), " .
                "        ('1528','Sykkylven',1,1,"                            . $time . "), " .
                "        ('1560','Tingvoll',1,1,"                             . $time . "), " .
                "        ('1516','Ulstein',1,1,"                              . $time . "), " .
                "        ('1511','Vanylven',1,1,"                             . $time . "), " .
                "        ('1535','Vestnes',1,1,"                              . $time . "), " .
                "        ('1519','Volda',1,1,"                                . $time . "), " .
                "        ('1523','Ørskog',1,1,"                               . $time . "), " .
                "        ('1520','Ørsta',1,1,"                                . $time . "), " .
                "        ('1504','Ålesund',1,1,"                              . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '15'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('15','1502','1504','1505','1511','1514','1515','1516','1517','1519','1520','1523','1524','1525','1526','1528','1529','1531','1532','1534','1535','1539','1543','1545','1546','1547','1548','1551','1554','1557','1560','1563','1566','1567','1571','1573','1576') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex){
            throw $ex;
        }//try_catch
    }//install_Møre_og_Romsdal

    public static function InsertLevelOne_SørTrøndelag() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                " VALUES ('16','Sør-Trøndelag Fylkeskommune',1,1,"    . $time . "), " .
                "        ('1622','Agdenes',1,1,"                      . $time . "), " .
                "        ('1627','Bjugn',1,1,"                        . $time . "), " .
                "        ('1620','Frøya',1,1,"                        . $time . "), " .
                "        ('1612','Hemne',1,1,"                        . $time . "), " .
                "        ('1617','Hitra',1,1,"                        . $time . "), " .
                "        ('1644','Holtålen',1,1,"                     . $time . "), " .
                "        ('1662','Klæbu',1,1,"                        . $time . "), " .
                "        ('1663','Malvik',1,1,"                       . $time . "), " .
                "        ('1636','Meldal',1,1,"                       . $time . "), " .
                "        ('1653','Melhus',1,1,"                       . $time . "), " .
                "        ('1648','Midtre Gauldal',1,1,"               . $time . "), " .
                "        ('1634','Oppdal',1,1,"                       . $time . "), " .
                "        ('1638','Orkdal',1,1,"                       . $time . "), " .
                "        ('1633','Osen',1,1,"                         . $time . "), " .
                "        ('1635','Rennebu',1,1,"                      . $time . "), " .
                "        ('1624','Rissa',1,1,"                        . $time . "), " .
                "        ('1632','Roan',1,1,"                         . $time . "), " .
                "        ('1640','Røros',1,1,"                        . $time . "), " .
                "        ('1664','Selbu',1,1,"                        . $time . "), " .
                "        ('1657','Skaun',1,1,"                        . $time . "), " .
                "        ('1613','Snillfjord',1,1,"                   . $time . "), " .
                "        ('1601','Trondheim',1,1,"                    . $time . "), " .
                "        ('1665','Tydal',1,1,"                        . $time . "), " .
                "        ('1621','Ørland',1,1,"                       . $time . "), " .
                "        ('1630','Åfjord',1,1,"                       . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '16'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('16','1601','1612','1613','1617','1620','1621','1622','1624','1627','1630','1632','1633','1634','1635','1636','1638','1640','1644','1648','1653','1657','1662','1663','1664','1665') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Sør_Trøndelag

    public static function InsertLevelOne_NordTrøndelag() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                " VALUES ('17','Nord-Trøndelag Fylkeskommune',1,1,"       . $time . "), " .
                "        ('1749','Flatanger',1,1,"                        . $time . "), " .
                "        ('1748','Fosnes',1,1,"                           . $time . "), " .
                "        ('1717','Frosta',1,1,"                           . $time . "), " .
                "        ('1742','Grong',1,1,"                            . $time . "), " .
                "        ('1743','Høylandet',1,1,"                        . $time . "), " .
                "        ('1756','Inderøy',1,1,"                          . $time . "), " .
                "        ('1755','Leka',1,1,"                             . $time . "), " .
                "        ('1718','Leksvik',1,1,"                          . $time . "), " .
                "        ('1719','Levanger',1,1,"                         . $time . "), " .
                "        ('1738','Lierne',1,1,"                           . $time . "), " .
                "        ('1711','Meråker',1,1,"                          . $time . "), " .
                "        ('1725','Namdalseid',1,1,"                       . $time . "), " .
                "        ('1703','Namsos',1,1,"                           . $time . "), " .
                "        ('1740','Namsskogan',1,1,"                       . $time . "), " .
                "        ('1751','Nærøy',1,1,"                            . $time . "), " .
                "        ('1744','Overhalla',1,1,"                        . $time . "), " .
                "        ('1739','Røyrvik',1,1,"                          . $time . "), " .
                "        ('1736','Snåsa',1,1,"                            . $time . "), " .
                "        ('1702','Steinkjer',1,1,"                        . $time . "), " .
                "        ('1714','Stjørdal',1,1,"                         . $time . "), " .
                "        ('1721','Verdal',1,1,"                           . $time . "), " .
                "        ('1724','Verran',1,1,"                           . $time . "), " .
                "        ('1750','Vikna',1,1,"                            . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '17'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('17','1702','1703','1711','1714','1717','1718','1719','1721','1724','1725','1736','1738','1739','1740','1742','1743','1744','1748','1749','1750','1751','1755','1756') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Nord_Trøndelag

    public static function InsertLevelOne_Nordland() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                " VALUES ('18','Nordland Fylkeskommune',1,1,"         . $time . "), " .
                "        ('1820','Alstahaug',1,1,"                    . $time . "), " .
                "        ('1871','Andøy',1,1,"                        . $time . "), " .
                "        ('1854','Ballangen',1,1,"                    . $time . "), " .
                "        ('1839','Beiarn',1,1,"                       . $time . "), " .
                "        ('1811','Bindal',1,1,"                       . $time . "), " .
                "        ('1804','Bodø',1,1,"                         . $time . "), " .
                "        ('1813','Brønnøy',1,1,"                      . $time . "), " .
                "        ('1867','Bø',1,1,"                           . $time . "), " .
                "        ('1827','Dønna',1,1,"                        . $time . "), " .
                "        ('1853','Evenes',1,1,"                       . $time . "), " .
                "        ('1841','Fauske',1,1,"                       . $time . "), " .
                "        ('1859','Flakstad',1,1,"                     . $time . "), " .
                "        ('1838','Gildeskål',1,1,"                    . $time . "), " .
                "        ('1825','Grane',1,1,"                        . $time . "), " .
                "        ('1866','Hadsel',1,1,"                       . $time . "), " .
                "        ('1849','Hamarøy',1,1,"                      . $time . "), " .
                "        ('1826','Hattfjelldal',1,1,"                 . $time . "), " .
                "        ('1832','Hemnes',1,1,"                       . $time . "), " .
                "        ('1818','Herøy',1,1,"                        . $time . "), " .
                "        ('1822','Leirfjord',1,1,"                    . $time . "), " .
                "        ('1834','Lurøy',1,1,"                        . $time . "), " .
                "        ('1851','Lødingen',1,1,"                     . $time . "), " .
                "        ('1837','Meløy',1,1,"                        . $time . "), " .
                "        ('1874','Moskenes',1,1,"                     . $time . "), " .
                "        ('1805','Narvik',1,1,"                       . $time . "), " .
                "        ('1828','Nesna',1,1,"                        . $time . "), " .
                "        ('1833','Rana',1,1,"                         . $time . "), " .
                "        ('1836','Rødøy',1,1,"                        . $time . "), " .
                "        ('1856','Røst',1,1,"                         . $time . "), " .
                "        ('1840','Saltdal',1,1,"                      . $time . "), " .
                "        ('1870','Sortland',1,1,"                     . $time . "), " .
                "        ('1848','Steigen',1,1,"                      . $time . "), " .
                "        ('1812','Sømna',1,1,"                        . $time . "), " .
                "        ('1845','Sørfold',1,1,"                      . $time . "), " .
                "        ('1852','Tjeldsund',1,1,"                    . $time . "), " .
                "        ('1835','Træna',1,1,"                        . $time . "), " .
                "        ('1850','Tysfjord',1,1,"                     . $time . "), " .
                "        ('1824','Vefsn',1,1,"                        . $time . "), " .
                "        ('1815','Vega',1,1,"                         . $time . "), " .
                "        ('1860','Vestvågøy',1,1,"                    . $time . "), " .
                "        ('1816','Vevelstad',1,1,"                    . $time . "), " .
                "        ('1857','Værøy',1,1,"                        . $time . "), " .
                "        ('1865','Vågan',1,1,"                        . $time . "), " .
                "        ('1868','Øksnes',1,1,"                       . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '18'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('18','1804','1805','1811','1812','1813','1815','1816','1818','1820','1822','1824','1825','1826','1827','1828','1832','1833','1834','1835','1836','1837','1838','1839','1840','1841','1845','1848','1849','1850','1851','1852','1853','1854','1856','1857','1859','1860','1865','1866','1867','1868','1870','1871','1874') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Nordland

    public static function InsertLevelOne_Troms() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                " VALUES ('19','Troms Fylkeskommune',1,1,"    . $time . "), " .
                "        ('1933','Balsfjord',1,1,"            . $time . "), " .
                "        ('1922','Bardu',1,1,"                . $time . "), " .
                "        ('1929','Berg',1,1,"                 . $time . "), " .
                "        ('1926','Dyrøy',1,1,"                . $time . "), " .
                "        ('1919','Gratangen',1,1,"            . $time . "), " .
                "        ('1903','Harstad',1,1,"              . $time . "), " .
                "        ('1917','Ibestad',1,1,"              . $time . "), " .
                "        ('1936','Karlsøy',1,1,"              . $time . "), " .
                "        ('1911','Kvæfjord',1,1,"             . $time . "), " .
                "        ('1943','Kvænangen',1,1,"            . $time . "), " .
                "        ('1940','Kåfjord',1,1,"              . $time . "), " .
                "        ('1920','Lavangen',1,1,"             . $time . "), " .
                "        ('1931','Lenvik',1,1,"               . $time . "), " .
                "        ('1938','Lyngen',1,1,"               . $time . "), " .
                "        ('1924','Målselv',1,1,"              . $time . "), " .
                "        ('1942','Nordreisa',1,1,"            . $time . "), " .
                "        ('1923','Salangen',1,1,"             . $time . "), " .
                "        ('1941','Skjervøy',1,1,"             . $time . "), " .
                "        ('1913','Skånland',1,1,"             . $time . "), " .
                "        ('1939','Storfjord',1,1,"            . $time . "), " .
                "        ('1925','Sørreisa',1,1,"             . $time . "), " .
                "        ('1928','Torsken',1,1,"              . $time . "), " .
                "        ('1927','Tranøy',1,1,"               . $time . "), " .
                "        ('1902','Tromsø',1,1,"               . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '19'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('19','1902','1903','1911','1913','1917','1919','1920','1922','1923','1924','1925','1926','1927','1928','1929','1931','1933','1936','1938','1939','1940','1941','1942','1943') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Troms

    public static function InsertLevelOne_Finnmark() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql =  " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) " .
                " VALUES ('20','Finnmark Fylkeskommune',1,1,"         . $time . "), " .
                "        ('2012','Alta',1,1,"                         . $time . "), " .
                "        ('2024','Berlevåg',1,1,"                     . $time . "), " .
                "        ('2028','Båtsfjord',1,1,"                    . $time . "), " .
                "        ('2023','Gamvik',1,1,"                       . $time . "), " .
                "        ('2004','Hammerfest',1,1,"                   . $time . "), " .
                "        ('2015','Hasvik',1,1,"                       . $time . "), " .
                "        ('2021','Karasjok',1,1,"                     . $time . "), " .
                "        ('2011','Kautokeino',1,1,"                   . $time . "), " .
                "        ('2017','Kvalsund',1,1,"                     . $time . "), " .
                "        ('2022','Lebesby',1,1,"                      . $time . "), " .
                "        ('2014','Loppa',1,1,"                        . $time . "), " .
                "        ('2018','Måsøy',1,1,"                        . $time . "), " .
                "        ('2027','Nesseby',1,1,"                      . $time . "), " .
                "        ('2019','Nordkapp',1,1,"                     . $time . "), " .
                "        ('2020','Porsanger',1,1,"                    . $time . "), " .
                "        ('2030','Sør-Varanger',1,1,"                 . $time . "), " .
                "        ('2025','Tana',1,1,"                         . $time . "), " .
                "        ('2003','Vadsø',1,1,"                        . $time . "), " .
                "        ('2002','Vardø',1,1,"                        . $time . ") ";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '20'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('20','2002','2003','2004','2011','2012','2014','2015','2017','2018','2019','2020','2021','2022','2023','2024','2025','2027','2028','2030') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Finnmark

    public static function InsertLevelOne_Svalbard() {
        /* Variables    */
        global $DB;
        $time = time();

        try {
            /* First Level One  */
            /* SQL Instruction  */
            $sql = " INSERT INTO {report_gen_companydata} (industrycode,name,hierarchylevel,public,modified) VALUES ('2111','Longyearbyen lokalstyre',1,1," . $time . ")";
            /* Execute  */
            $DB->execute($sql);

            /* Relation Level One - Level Zero  */
            /* Get Level Zero   */
            $levelZero = $DB->get_record('report_gen_companydata',array('hierarchylevel' => 0,'industrycode' => '21'));
            /* Get Level One    */
            $sql = " SELECT 	id
                     FROM 	    {report_gen_companydata}
                     WHERE 	    hierarchylevel = 1
                      AND		industrycode IN ('2111') ";

            /* Execute  */
            $rdo = $DB->get_recordset_sql($sql);
            foreach ($rdo as $levelOne) {
                /* Company Relation */
                $relation = new stdClass();
                $relation->companyid    = $levelOne->id;
                $relation->parentid     = $levelZero->id;
                $relation->modified     = $time;

                /* Insert   */
                $DB->insert_record('report_gen_company_relation',$relation);
            }//for_each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//install_Svalbard
}//Kommit_CompetenceManager
