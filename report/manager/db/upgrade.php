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
 * @package             report
 * @subpackage          manager
 * @copyright           2010 eFaktor
 * @license             http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
    $tblCompetencyImport    = null;
    $fieldJRMatch           = null;
    $fieldMapped            = null;

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


        /* New Table for Import Competence Profile   */
        if ($old_version < 2015083102) {
            CompetenceManager_Update::CreateCompetenceImport($db_man);
        }//if_old_version

        /* New Table for Super Uses */
        if ($old_version < 2015102000) {
            CompetenceManager_Update::CreateSuperUser($db_man);
        }//if_old_Version

        if ($old_version < 2015112400) {
            if ($db_man->table_exists('report_gen_competence_imp')) {
                /* New Fields   */
                $tblCompetencyImport = new xmldb_table('report_gen_competence_imp');
                $fieldJRMatch = new xmldb_field('jobrole_match',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                $db_man->change_field_precision($tblCompetencyImport,$fieldJRMatch);
            }
        }//if_odl_version

        if ($old_version < 2015113000) {
            /* New Temporary table  */
            CompetenceManager_Update::CreateTemporaryTable($db_man);
        }//if_old_version

        /* Manager && Reporter tables   */
        if ($old_version < 2015122200) {
            CompetenceManager_Update::ManagerReporterTables($db_man);
        }//if_old_version

        /* Add Invoice data */
        if ($old_version < 2016060602) {
            CompetenceManager_Update::UpdateInvoiceDataCompany($db_man);
        }

        // Add mapped with (source) field
        if ($old_version < 2017020100) {
            $tblCompanyData = new xmldb_table('report_gen_companydata');

            /* Mapped   */
            $fieldMapped = new xmldb_field ('mapped',XMLDB_TYPE_CHAR,'50',null, null,null,null,'public');
            if (!$db_man->field_exists($tblCompanyData, $fieldMapped)) {
                $db_man->add_field($tblCompanyData, $fieldMapped);
            }//if_exists
        }

        // Add mapped with (source) field
        if ($old_version < 2017110800) {
            $tblCompanyData = new xmldb_table('report_gen_companydata');

            /* Mapped   */
            $fieldMapped = new xmldb_field ('org_enhet_id',XMLDB_TYPE_CHAR,'255',null, null,null,null,'mapped');
            if (!$db_man->field_exists($tblCompanyData, $fieldMapped)) {
                $db_man->add_field($tblCompanyData, $fieldMapped);
            }//if_exists
        }

        // Add mapped field for managers && reporters
        if ($old_version < 2017111600) {
            // field
            $fieldMapped = new xmldb_field ('mapped',XMLDB_TYPE_CHAR,'50',null, null,null,null,'hierarchylevel');

            // Mapped - manager
            $tbl = new xmldb_table('report_gen_company_manager');
            if (!$db_man->field_exists($tbl, $fieldMapped)) {
                $db_man->add_field($tbl, $fieldMapped);
            }//if_exists

            // Mapped - reporter
            $tbl = new xmldb_table('report_gen_company_reporter');
            if (!$db_man->field_exists($tbl, $fieldMapped)) {
                $db_man->add_field($tbl, $fieldMapped);
            }//if_exists
        }//2017111300

        // Create views
        if ($old_version < 2017121400) {
            CompetenceManager_Update::view_companies_with_users();
            CompetenceManager_Update::view_course_company_user_enrol();
        }


        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_Catch
}//xmldb_report_manager_upgrade

class CompetenceManager_Update {
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
            // SQL for view - OLD
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

            // SQL for view
            $sql = " SELECT	  cc.id,
                              cc.course 									AS 'courseid',
                              uic.companyid                                 AS 'companyid',
                              u.id                                          AS 'user',
                              CONCAT(u.firstname, ' ', u.lastname)          AS 'name',
                              uic.jobroles                                  AS 'jobroles',
                              IF(cc.timecompleted,cc.timecompleted,0)       AS 'timecompleted'
                     FROM	  {course_completions} 			cc
                        JOIN  {user}						u   	ON  u.id 	 		= cc.userid
                                                                    AND u.deleted 		= 0
                        JOIN  {user_info_competence_data} 	uic 	ON  uic.userid  	= u.id
                        JOIN  companies_with_users 		  	co  	ON  co.levelthree 	= uic.companyid ";

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

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    06/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add invoice data to the company
     */
    public static function UpdateInvoiceDataCompany($dbMan) {
        /* Variables */
        $tblCompanyData = null;
        $fldAnsvar      = null;
        $fldTjeneste    = null;
        $fldAdreseOne   = null;
        $fldAdreseTwo   = null;
        $fldAdreseThree = null;
        $fldPostnr      = null;
        $fldPoststed    = null;
        $fldEPost       = null;

        try {
            $tblCompanyData = new xmldb_table('report_gen_companydata');

            /* Ansvar Field     */
            $fldAnsvar      = null;
            $fldAnsvar      = new xmldb_field('ansvar', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'public');
            if (!$dbMan->field_exists($tblCompanyData, $fldAnsvar)) {
                $dbMan->add_field($tblCompanyData, $fldAnsvar);
            }//if_not_exists

            /* Tjeneste Field   */
            $fldTjeneste    = null;
            $fldTjeneste    = new xmldb_field('tjeneste', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'public');
            if (!$dbMan->field_exists($tblCompanyData, $fldTjeneste)) {
                $dbMan->add_field($tblCompanyData, $fldTjeneste);
            }//if_not_exists

            /* Adresse 1        */
            $fldAdreseOne   = null;
            $fldAdreseOne   = new xmldb_field('adresse1', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'public');
            if (!$dbMan->field_exists($tblCompanyData, $fldAdreseOne)) {
                $dbMan->add_field($tblCompanyData, $fldAdreseOne);
            }//if_not_exists

            /* Adresse 2        */
            $fldAdreseTwo   = null;
            $fldAdreseTwo   = new xmldb_field('adresse2', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'public');
            if (!$dbMan->field_exists($tblCompanyData, $fldAdreseTwo)) {
                $dbMan->add_field($tblCompanyData, $fldAdreseTwo);
            }//if_not_exists

            /* Adresse 3        */
            $fldAdreseThree = null;
            $fldAdreseThree = new xmldb_field('adresse3', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'public');
            if (!$dbMan->field_exists($tblCompanyData, $fldAdreseThree)) {
                $dbMan->add_field($tblCompanyData, $fldAdreseThree);
            }//if_not_exists

            /* Post Number      */
            $fldPostnr      = null;
            $fldPostnr      = new xmldb_field('postnr', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'public');
            if (!$dbMan->field_exists($tblCompanyData, $fldPostnr)) {
                $dbMan->add_field($tblCompanyData, $fldPostnr);
            }//if_not_exists

            /* Post sted        */
            $fldPoststed    = null;
            $fldPoststed    = new xmldb_field('poststed', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'public');
            if (!$dbMan->field_exists($tblCompanyData, $fldPoststed)) {
                $dbMan->add_field($tblCompanyData, $fldPoststed);
            }//if_not_exists

            /* ePost            */
            $fldEPost       = null;
            $fldEPost       = new xmldb_field('epost', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'public');
            if (!$dbMan->field_exists($tblCompanyData, $fldEPost)) {
                $dbMan->add_field($tblCompanyData, $fldEPost);
            }//if_not_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UpdateReportGenCompany
}//CompetenceManager_Update
