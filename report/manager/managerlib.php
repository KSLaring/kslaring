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
 * Library code for the Outcome Report Competence Manager.
 *
 * @package         report
 * @subpackage      manager/outcome_report
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    26/03/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Library for the Report Competence Manager
 *
 */
define('REPORT_MANAGER_COMPANY_CANCEL','rg_cancel');
define('REPORT_MANAGER_ADD_ITEM', 'add_item');
define('REPORT_MANAGER_RENAME_SELECTED', 'rename_selected');
define('REPORT_MANAGER_DELETE_SELECTED', 'delete_selected');
define('REPORT_MANAGER_DELETE_EMPLOYEES', 'delete_employees');
define('REPORT_MANAGER_DELETE_ALL_EMPLOYEES', 'delete_all_employees');
define('REPORT_MANAGER_UNLINK_SELECTED', 'unlink_selected');
define('REPORT_MANAGER_MANAGERS_SELECTED','managers_selected');
define('REPORT_MANAGER_REPORTERS_SELECTED','reporters_selected');
define('REPORT_MANAGER_MOVED_SELECTED','move_selected');
define('REPORT_MANAGER_GET_LEVEL', 'get_level');
define('REPORT_MANAGER_GET_UNCONNECTED', 'get_unconnected');
define('REPORT_MANAGER_REMOVE_SELECTED', 'remove_selected');
define('REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL', 'company_structure_level');
define('REPORT_MANAGER_COMPANY_LIST', 'company_list');
define('REPORT_MANAGER_EMPLOYEE_LIST', 'employee_list');
define('REPORT_MANAGER_JOB_ROLE_LIST', 'job_role_list');
define('REPORT_MANAGER_OUTCOME_LIST', 'outcome_list');
define('REPORT_MANAGER_COURSE_LIST', 'course_list');
define('REPORT_MANAGER_USER_LIST', 'user_list');
define('REPORT_MANAGER_COMPLETED_LIST', 'completed_list');
define('REPORT_MANAGER_EXPIRE_NEXT_LIST', 'expire_next_list');
define('REPORT_MANAGER_REPORT_FORMAT_LIST', 'report_format_list');
define('REPORT_MANAGER_COURSE_REPORT_SELECT_DATA', 'course_report_format_list');
define('REPORT_MANAGER_OUTCOME_REPORT_SELECT_DATA', 'outcome_report_format_list');
define('REPORT_MANAGER_IN_PROGRESS', 'in_progress');
define('REPORT_MANAGER_COMPLETED', 'completed');
define('REPORT_MANAGER_COMPLETED_BEFORE', 'completed_before');
define('REPORT_MANAGER_REP_FORMAT_SCREEN', 0);
define('REPORT_MANAGER_REP_FORMAT_PDF', 1);
define('REPORT_MANAGER_REP_FORMAT_PDF_MAIL', 2);
define('REPORT_MANAGER_REP_FORMAT_CSV', 3);
define('REPORT_MANAGER_ERROR', 0);
define('REPORT_MANAGER_SUCCESS', 1);
define('REPORT_MANAGER_ERROR_NO_USER_PROFILE_DATA', 0);

define('COMPANY_STRUCTURE_LEVEL','level_');

define('REPORT_MANAGER_IMPORT_0',0);
define('REPORT_MANAGER_IMPORT_1',1);
define('REPORT_MANAGER_IMPORT_2',2);
define('REPORT_MANAGER_IMPORT_3',3);

define('CSV_LOAD_ERROR','csv_load_error');
define('CSV_EMPTY_FILE','csv_empty_file');
define('CANNOT_READ_TMP_FILE','cannot_read_tmp_file');
define('CSV_FEW_COLUMNS','csv_few_columns');
define('INVALID_FILE_NAME','invalid_field_name');
define('DUPLICATE_FIELD_NAME','duplicate_field_name');
define('NON_ERROR','non_error');

define('ORG_MAPPED_TARDIS','TARDIS');

define('TBL_REPORTERS','report_gen_company_reporter');
define('TBL_MANAGERS','report_gen_company_manager');

if (!defined('MAX_BULK_USERS')) {
    define('MAX_BULK_USERS', 2000);
}

class CompetenceManager {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    /**
     * Description
     * Check capabilities to see report
     *
     * @param           $isreporter
     * @param           $level
     * @param           $contenxt
     *
     * @throws          Exception
     *
     * @creationDate    26/09/2017
     * @author          eFaktor     (fbv)
     */
    public static function check_capability_reports($isreporter,$level,$contenxt) {
        try {
            switch ($level) {
                case 0:
                    if (!has_capability('report/manager:viewlevel0', $contenxt)) {
                        if (!$isreporter) {
                            print_error('nopermissions', 'error', '', 'report/manager:viewlevel0');
                        }//ifReporter
                    }

                    break;
                case 1:
                    if (!has_capability('report/manager:viewlevel1', $contenxt)) {
                        if (!$isreporter) {
                            print_error('nopermissions', 'error', '', 'report/manager:viewlevel1');
                        }//ifReporter
                    }

                    break;
                case 2:
                    if (!has_capability('report/manager:viewlevel2', $contenxt)) {
                        if (!$isreporter) {
                            print_error('nopermissions', 'error', '', 'report/manager:viewlevel2');
                        }//ifReporter
                    }

                    break;
                case 3:
                    if (!has_capability('report/manager:viewlevel3', $contenxt)) {
                        if (!$isreporter) {
                            print_error('nopermissions', 'error', '', 'report/manager:viewlevel3');
                        }//ifReporter
                    }

                    break;
            }//switch
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//check_capability_reports

    /**
     * @param           $userId
     * @param           $level
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    01/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user is reporter
     *
     */
    public static function IsReporter($userId,$level=-1) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['managerid']           = $userId;
            if ($level >= 0) {
                $params['hierarchylevel']   = $level;
            }
            /* Execute  */
            $rdo = $DB->get_records('report_gen_company_manager',$params);

            if ($rdo) {
                return true;
            }else {
                unset($params['managerid']);
                $params['reporterid']           = $userId;
                $rdo = $DB->get_records('report_gen_company_reporter',$params);
                if ($rdo) {
                    return true;
                }else {
                    return false;
                }
            }
        }catch (Exception $ex) {
            throw $ex;
        }
    }//IsReporter

    /**
     * @param           $userId
     * @param           $level
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    18/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user is a manager
     */
    public static function IsManager($userId,$level=-1) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['managerid']           = $userId;
            if ($level >= 0) {
                $params['hierarchylevel']   = $level;
            }
            /* Execute  */
            $rdo = $DB->get_records('report_gen_company_manager',$params);

            if ($rdo) {
                return true;
            }else {
                return false;
            }
        }catch (Exception $ex) {
            throw $ex;
        }
    }//IsManager

    /**
     * @param           $userId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    23/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user is a Super User
     */
    public static function IsSuperUser($userId) {
        /* Variables    */
        global $DB;
        $params = null;
        $rdo    = null;
        $sql    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user']     = $userId;
            $params['deleted']  = 0;

            /* SQL Instruction  */
            $sql = " SELECT		sp.id
                     FROM		{report_gen_super_user}	sp
                        JOIN	{user}					u	ON 	u.id 		= sp.userid
                                                            AND	u.deleted 	= :deleted
                     WHERE		sp.userid = :user ";


            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsSuperUser

    /**
     * @param           $userId
     * @return          array
     *
     * @throws          Exception
     *
     * @creationDate    23/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get level access connected with user.
     */
    public static function Get_MyAccess($userId) {
        /* Variables    */
        global $DB;
        $params     = null;
        $rdo        = null;
        $sql        = null;
        $myAccess   = array();
        $infoAccess = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $userId;

            /* SQL Instruction  */
            $sql = " SELECT		sp.levelzero,
                                GROUP_CONCAT(DISTINCT sp.levelone 	ORDER BY sp.levelone 	SEPARATOR ',') 	as 'levelone',
                                GROUP_CONCAT(DISTINCT sp.leveltwo 	ORDER BY sp.leveltwo 	SEPARATOR ',') 	as 'leveltwo',
                                GROUP_CONCAT(DISTINCT sp.levelthree ORDER BY sp.levelthree 	SEPARATOR ',') 	as 'levelthree'
                     FROM		{report_gen_super_user}	sp
                     WHERE		sp.userid = :user
                     GROUP BY	sp.levelzero ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Access  */
                    $infoAccess = new stdClass();
                    $infoAccess->levelZero  = ($instance->levelzero ? $instance->levelzero : 0);
                    $infoAccess->levelOne   = ($instance->levelone ? $instance->levelone : 0);
                    $infoAccess->levelTwo   = ($instance->leveltwo ? $instance->leveltwo : 0);
                    $infoAccess->levelThree = ($instance->levelthree ? $instance->levelthree : 0);

                    /* Add Access   */
                    $myAccess[$instance->levelzero] = $infoAccess;
                }//for_rdo
            }//if_rdo

            return $myAccess;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyAccess

    /**
     * @param           $selector
     * @param           $employeeSel
     * @param           $outcomeSel
     * @param           $superUser
     * @param           $myAccess
     * @param           $btnActions
     *
     * @throws          Exception
     *
     * @creationDate    27/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize the organization structure selectors
     */
    public static function Init_Organization_Structure($selector,$employeeSel,$outcomeSel,$superUser,$myAccess,$btnActions) {
        /* Variables    */
        global $PAGE;
        $options        = null;
        $hash           = null;
        $jsModule       = null;
        $name           = null;
        $path           = null;
        $requires       = null;
        $strings        = null;
        $grpOne         = null;
        $grpTwo         = null;
        $grpThree       = null;
        $sp             = null;
        $delEmployees   = null;

        try {
            /* Initialise variables */
            $name       = 'level_structure';
            $path       = '/report/manager/js/organization.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
                             );

            $sp = ($superUser ? 1 : 0);

            /* Window Confirm parameters    */
            $delEmployees = array();
            $delEmployees['title']      = get_string('del_title','report_manager');
            $delEmployees['question']   = get_string('delete_all_employees','report_manager');
            $delEmployees['yes']        = get_string('del_yes','report_manager');
            $delEmployees['no']         = get_string('del_no','report_manager');
            $PAGE->requires->js_init_call('M.core_user.init_organization',
                                          array($selector,$employeeSel,$outcomeSel,$sp,$myAccess,$btnActions,$delEmployees),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Organization_Structure

    /**
     * Description
     * Init and call javascript with company structure
     *
     * @creationDate    06/02/2017
     * @author          eFaktor     (fbv)
     *
     * @param           $selector
     * @param           $employeeSel
     * @param           $outcomeSel
     * @param           $superUser
     * @param           $myAccess
     * @param           $btnActions
     *
     * @throws          Exception
     */
    public static function init_company_structure($selector,$employeeSel,$outcomeSel,$superUser,$myAccess,$btnActions) {
        /* Variables    */
        global $PAGE;
        $options        = null;
        $hash           = null;
        $jsModule       = null;
        $name           = null;
        $path           = null;
        $requires       = null;
        $strings        = null;
        $grpOne         = null;
        $grpTwo         = null;
        $grpThree       = null;
        $sp             = null;
        $delEmployees   = null;

        try {
            /* Initialise variables */
            $name       = 'level_structure';
            $path       = '/report/manager/js/structure.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                'fullpath'    => $path,
                'requires'    => $requires,
                'strings'     => $strings
            );

            $sp = ($superUser ? 1 : 0);

            /* Window Confirm parameters    */
            $delEmployees = array();
            $delEmployees['title']      = get_string('del_title','report_manager');
            $delEmployees['question']   = get_string('delete_all_employees','report_manager');
            $delEmployees['yes']        = get_string('del_yes','report_manager');
            $delEmployees['no']         = get_string('del_no','report_manager');
            $PAGE->requires->js_init_call('M.core_user.init_organization',
                array($selector,$employeeSel,$outcomeSel,$sp,$myAccess,$btnActions,$delEmployees),
                false,
                $jsModule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_company_Structure

    /**
     * @param           $selector
     * @param           $jrSelector
     * @param           $rptLevel
     *
     * @throws          Exception
     *
     * @creationDate    27/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize the organization structure selectors for course report
     */
    public static function Init_OrganizationStructure_CourseReport($selector,$jrSelector,$rptLevel) {
        /* Variables    */
        global $PAGE;
        $options    = null;
        $hash       = null;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;
        $sp         = null;

        try {
            /* Initialise variables */
            $name       = 'level_structure';
            $path       = '/report/manager/course_report/js/organization.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
                             );

            $PAGE->requires->js_init_call('M.core_user.init_organization',
                                          array($selector,$jrSelector,$rptLevel),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_OrganizationStructure_CourseReport

    /**
     * @param           $selector
     * @param           $jrSelector
     * @param           $outSelector
     * @param           $rptLevel
     *
     * @throws          Exception
     *
     * @creationDate    27/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize the organization structure selectors for outcome report
     */
    public static function Init_OrganizationStructure_OutcomeReport($selector,$jrSelector,$outSelector,$rptLevel) {
        /* Variables    */
        global $PAGE;
        $options    = null;
        $hash       = null;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;
        $sp         = null;

        try {
            /* Initialise variables */
            $name       = 'level_structure';
            $path       = '/report/manager/outcome_report/js/organization.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
                             );

            $PAGE->requires->js_init_call('M.core_user.init_organization',
                                          array($selector,$jrSelector,$outSelector,$rptLevel),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_OrganizationStructure_OutcomeReport

    /**
     * @param           $tab
     * @param           $site_context
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the Level links to the main page
     */
    public static function GetLevelLink_ReportPage($tab,$site_context) {
        /* Variables    */
        global $USER;

        /* Create links - It's depend on View permissions */
        $out = '<ul class="unlist report-selection">' . "\n";
        if (self::IsReporter($USER->id,0)) {
            $out .= self::Get_ZeroLevelLink($tab);
        }else if (self::IsReporter($USER->id,1)) {
            $out .= self::Get_FirstLevelLink($tab);
        }else if (self::IsReporter($USER->id,2)) {
            $out .= self::Get_SecondLevelLink($tab);
        }else if (self::IsReporter($USER->id,3)) {
            $out .= self::Get_ThirdLevelLink($tab);
        }else {
            if (is_siteadmin($USER->id)) {
                $out = self::Get_ZeroLevelLink($tab);
                $out .= self::Get_FirstLevelLink($tab);
                $out .= self::Get_SecondLevelLink($tab);
                $out .= self::Get_ThirdLevelLink($tab);
            }else {
                if (has_capability('report/manager:viewlevel0', $site_context)) {
                    $out .= self::Get_ZeroLevelLink($tab);
                }else if (has_capability('report/manager:viewlevel1', $site_context)) {
                    $out .= self::Get_FirstLevelLink($tab);
                }else if(has_capability('report/manager:viewlevel2', $site_context)) {
                    $out .= self::Get_SecondLevelLink($tab);
                }else if (has_capability('report/manager:viewlevel3', $site_context)) {
                    $out .= self::Get_ThirdLevelLink($tab);
                }//if_capabitity
            }
        }
        $out .= '</ul>' . "\n";

        /* Draw Links */
        echo $out;
    }//GetLevelLink_ReportPage

    /**
     * @static
     * @param           $user_id
     * @param           $site_context
     * @param           $IsReporterManager
     * @param           $reportLevel
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get my hierarchy level
     */
    public static function get_MyHierarchyLevel($user_id,$site_context,$IsReporterManager,$reportLevel) {
        /* Variables    */
        $myHierarchy   = null;

        try {
            /* Build my hierarchy   */
            $myHierarchy               = new stdClass();
            $myHierarchy->IsRepoter         = $IsReporterManager;
            if ($IsReporterManager) {
                $myHierarchy->competence    = self::get_myreporter_competence($user_id);
                $myHierarchy->my_level      = $reportLevel;
            }else {
                $myHierarchy->competence    = self::get_mycompetence($user_id);
                $myHierarchy->my_level      = self::Get_MyLevelView($user_id,$site_context);
            }//if_IsReporter

            return $myHierarchy;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_MyHierarchyLevel

    /**
     * @param           $my_companies
     * @return          array
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get companies split by level
     *
     * @updateDate      15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the companies connected with my level and/or my competence
     */
    public static function get_mycompanies_by_level($my_companies) {
        /* Variables    */
        $three      = null;
        $two        = null;
        $one        = null;
        $zero       = null;

        try {

            if ($my_companies) {
                foreach ($my_companies as $company) {
                    // Level zero
                    if ($company->levelzero) {
                        if ($zero) {
                            $zero .= ',';
                        }

                        $zero .= $company->levelzero;
                    }//level_zero

                    // Level one
                    if ($company->levelone) {
                        if ($one) {
                            $one .= ',';
                        }

                        $one .= $company->levelone;
                    }//level_one

                    // Level two
                    if ($company->leveltwo) {
                        if ($two) {
                            $two .= ',';
                        }
                        $two .= $company->leveltwo;
                    }//level_two

                    if ($three) {
                        $three .= ',';
                    }
                    $three .= $company->levelthree;
                }//ofr_my_companies
            }

            return array($zero,$one,$two,$three);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetMyCompanies_By_Level

    /**
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    27/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get companies with employees
     */
    public static function GetCompanies_WithEmployees($levelZero,$levelOne=null,$levelTwo=null,$levelThree=null) {
        /* Variables    */
        global $DB;
        $params     = null;
        $sql        = null;
        $rdo        = null;
        $companies  = null;

        try {
            // Search criteria
            $params = array();
            $params['zero'] = $levelZero;

            // SQL Instruction
            $sql = " SELECT	co.levelzero  	as 'levelzero',
                            GROUP_CONCAT(DISTINCT co.levelone  	 ORDER BY co.levelone 	SEPARATOR ',') 	as 'levelone',
                            GROUP_CONCAT(DISTINCT co.leveltwo  	 ORDER BY co.leveltwo 	SEPARATOR ',') 	as 'leveltwo',
                            GROUP_CONCAT(DISTINCT co.levelthree  ORDER BY co.levelthree SEPARATOR ',') 	as 'levelthree'
                     FROM	companies_with_users co
                     WHERE	co.levelzero = :zero
                     ";

            // Criteria level one
            if ($levelOne) {
                $sql .= " AND co.levelone IN ($levelOne) ";
            }
            // Criteria level two
            if ($levelTwo) {
                $sql .= " AND co.leveltwo IN ($levelTwo) ";
            }
            // Criteria level three
            if ($levelThree) {
                $sql .= " AND co.levelthree IN ($levelThree) ";
            }//if_levelThree

            // Execute
            $sql .= " GROUP BY co.levelzero ";
            $rdo = $DB->get_record_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompanies_WithEmployees

    /**
     * @param           $companyLst
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    22/04/2016
     * @author          eFaktor     (fbv)
     *
     */
    public static function GetCompaniesInfo($companyLst) {
        /* Variables */
        global $DB;
        $sql = null;
        $rdo = null;
        $infoCompany    = null;
        $companies      = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT c.id,
                            c.name
                     FROM   {report_gen_companydata} c
                     WHERE  c.id IN ($companyLst)
                     ORDER BY c.name ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//trY_catch
    }

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get completed list
     */
    public static function GetCompletedList() {
        /* Variables    */
        $list = null;

        try {
            $list = array(
                            0 => get_string('numdays', '', 1),
                            1 => get_string('numweeks', '', 1),
                            2 => get_string('numweeks', '', 2),
                            3 => get_string('numweeks', '', 3),
                            4 => get_string('nummonths', '', 1),
                            5 => get_string('nummonths', '', 2),
                            6 => get_string('nummonths', '', 3),
                            7 => get_string('nummonths', '', 4),
                            8 => get_string('nummonths', '', 5),
                            9 => get_string('nummonths', '', 6),
                            10 => get_string('numyears', '', 1),
                            11 => get_string('numyears', '', 2)
                        );

            return $list;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompletedList

    /**
     * @param           $index
     * @param           bool $future
     * @return          int
     * @throws          Exception
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Calculate the completion date
     */
    public static function Get_CompletedDate_Timestamp($index, $future = false) {
        /* Variables    */
        $today = strtotime("today", usertime( time() ));
        $future = $future ? 1 : -1;
        $ts = 0;

        try {
            switch($index) {
                case 0:
                    $ts = strtotime('today', $today);
                    break;

                case 1:
                    $ts = strtotime(1 * $future . ' week', $today);
                    break;

                case 2:
                    $ts = strtotime(2 * $future . ' weeks', $today);
                    break;

                case 3:
                    $ts = strtotime(3 * $future . ' weeks', $today);
                    break;

                case 4:
                    $ts = strtotime(1 * $future . ' month', $today);
                    break;

                case 5:
                    $ts = strtotime(2 * $future . ' month', $today);
                    break;

                case 6:
                    $ts = strtotime(3 * $future . ' month', $today);
                    break;

                case 7:
                    $ts = strtotime(4 * $future . ' month', $today);
                    break;

                case 8:
                    $ts = strtotime(5 * $future . ' month', $today);
                    break;

                case 9:
                    $ts = strtotime(6 * $future . ' month', $today);
                    break;

                case 10:
                    $ts = strtotime(1 * $future . ' year', $today);
                    break;

                case 11:
                    $ts = strtotime(2 * $future . ' years', $today);
                    break;

                default:
                    $ts = 0;
            }//switch_index

            return $ts;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompletedDate_Timestamp

    /**
     * @param           $company
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the company is public or private
     */
    public static function IsPublic($company) {
        /* Variables    */
        global $DB;

        try {
            /* Get Public Field */
            $rdo = $DB->get_record('report_gen_companydata',array('id' => $company),'public');
            if ($rdo) {
                if ($rdo->public) {
                    return true;
                }else {
                    return false;
                }//if_else
            }else {
                return false;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsPublic

    /**
     * @static
     * @param           $jr_lst
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the Job Roles List
     */
    public static function Get_JobRolesList($jr_lst = null) {
        /* Variables    */
        global $DB;
        $job_roles_lst = array();

        try {
            /* SQL Instruction  */
            $sql = " SELECT     DISTINCT id,
                                         name,
                                         industrycode
                     FROM       {report_gen_jobrole} ";

            /* Search Criteria  */
            if ($jr_lst) {
                $sql .= " WHERE id IN ($jr_lst) ";
            }//if_jr_lst

            /* ORDER    */
            $sql .= " ORDER BY   industrycode, name ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $job_role) {
                    $job_roles_lst[$job_role->id] = $job_role->industrycode . ' - '. $job_role->name;
                }//for_rdo_job_role
            }//if_rdo
            return $job_roles_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRolesList


    /**
     * @param           $options
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the job roles that are generics
     */
    public static function GetJobRoles_Generics(&$options) {
        /* Variables    */
        global $DB;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT      jr.id,
                                              jr.name,
                                              jr.industrycode
                     FROM		{report_gen_jobrole}				jr
                        JOIN	{report_gen_jobrole_relation}		jr_rel	ON 	jr_rel.jobroleid = jr.id
                                                                            AND jr_rel.levelzero IS NULL
                     ORDER BY jr.industrycode, jr.name ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $options[$instance->id] = $instance->industrycode . ' - ' . $instance->name;
                }//for_each
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetJobRoles_Generics

    /**
     * @param           $options
     * @param           $level
     * @param           $levelZero
     * @param      null $levelOne
     * @param      null $levelTwo
     * @param      null $levelThree
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the job roles connected with the levels
     */
    public static function GetJobRoles_Hierarchy(&$options,$level,$levelZero,$levelOne=null,$levelTwo=null, $levelThree=null) {
        /* Variables    */
        global $DB;
        $sqlOne     = null;
        $sqlTwo     = null;
        $sqlThree   = null;


        try {
            // SQL Instruction to get job roles
            $sql = " SELECT		DISTINCT      jr.id,
                                              jr.name,
                                              jr.industrycode
                     FROM		{report_gen_jobrole}				jr
                        JOIN	{report_gen_jobrole_relation}		jr_rel	ON 	 jr_rel.jobroleid = jr.id ";

            switch ($level) {
                case 0:
                    $sql .= " AND  jr_rel.levelzero    IN ($levelZero) ";

                    break;
                case 1:
                    $sql .= " AND  jr_rel.levelzero    IN ($levelZero) ";
                    if ($levelOne) {
                        $sql .= " AND  jr_rel.levelone     IN ($levelOne) ";
                    }//if_levelOne

                    break;
                case 2:
                    $sql .= " AND  jr_rel.levelzero    IN ($levelZero) ";
                    if ($levelOne) {
                        $sql .= " AND  jr_rel.levelone     IN ($levelOne) ";
                    }//if_levelOne
                    if ($levelTwo) {
                        $sql .= " AND  jr_rel.leveltwo     IN ($levelTwo) ";
                    }//if_levelTwo

                    break;
                case 3:
                    if ($levelOne && $levelTwo && $levelThree) {
                        $sql .= "  AND (
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IN ($levelOne)
                                     AND
                                     jr_rel.leveltwo     IN ($levelTwo)
                                     AND
                                     jr_rel.levelthree   IN ($levelThree)
                                    )
                                    OR
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IN ($levelOne)
                                     AND
                                     jr_rel.leveltwo     IN ($levelTwo)
                                     AND
                                     jr_rel.levelthree   IS NULL
                                    )
                                    OR
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IN ($levelOne)
                                     AND
                                     jr_rel.leveltwo     IS NULL
                                     AND
                                     jr_rel.levelthree   IS NULL
                                    )
                                    OR
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IS NULL
                                     AND
                                     jr_rel.leveltwo     IS NULL
                                     AND
                                     jr_rel.levelthree   IS NULL
                                    )
                               ) ";
                    }else if ($levelOne && $levelTwo && !$levelThree) {
                        $sql .= "  AND (
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IN ($levelOne)
                                     AND
                                     jr_rel.leveltwo     IN ($levelTwo)
                                     AND
                                     jr_rel.levelthree   IS NULL
                                    )
                                    OR
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IN ($levelOne)
                                     AND
                                     jr_rel.leveltwo     IS NULL
                                     AND
                                     jr_rel.levelthree   IS NULL
                                    )
                                    OR
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IS NULL
                                     AND
                                     jr_rel.leveltwo     IS NULL
                                     AND
                                     jr_rel.levelthree   IS NULL
                                    )
                               ) ";
                    }else if ($levelOne && !$levelTwo && !$levelThree) {
                        $sql .= "  AND (
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IN ($levelOne)
                                     AND
                                     jr_rel.leveltwo     IS NULL
                                     AND
                                     jr_rel.levelthree   IS NULL
                                    )
                                    OR
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IS NULL
                                     AND
                                     jr_rel.leveltwo     IS NULL
                                     AND
                                     jr_rel.levelthree   IS NULL
                                    )
                               ) ";
                    }else {
                        $sql .= " AND  jr_rel.levelzero    IN ($levelZero) ";
                    }

                    break;
            }//switch_level

            $sql .= " ORDER BY jr.industrycode, jr.name ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $options[$instance->id] = $instance->industrycode . ' - ' . $instance->name;
                }//for_each
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetJobRoles_Hierarchy

    /**
     * @param               $level
     * @param       int     $parent_id
     * @param       null    $companies_in
     * @return              array
     * @throws              Exception
     *
     * @creationDate        26/03/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get a list of all the companies are connected a specific level.
     */
    public static function GetCompanies_LevelList($level, $parent_id = 0,$companies_in = null) {
        /* Variables */
        global $DB;
        $levels = array();

        try {
            /* List Companies   */
            $levels[0] = get_string('select_level_list','report_manager');

            /* Research Criteria */
            $params = array();
            $params['level']    = $level;

            /* SQL Instruction */
            $sql = " SELECT     DISTINCT  rcd.id,
                                          rcd.name,
                                          rcd.industrycode
                     FROM       {report_gen_companydata} rcd ";

            /* Parents  */
            if ($parent_id) {
                $sql .= " JOIN  {report_gen_company_relation} rcr   ON    rcr.companyid = rcd.id
                                                                    AND   rcr.parentid  IN ($parent_id) ";
            }//if_level

            /* Conditions   */
            $sql .= " WHERE     rcd.hierarchylevel = :level ";
            /* Companies In */
            if ($companies_in) {
                $sql .= " AND rcd.id IN ($companies_in) ";
            }//if_companies_in

            /* Order    */
            $sql .= " ORDER BY  rcd.industrycode, rcd.name ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $field) {
                    $levels[$field->id] = $field->industrycode . ' - '. $field->name;
                }//foreach
            }//if_rdo

            return $levels;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_Companies_LevelList

    /**
     * Description
     * Get extra information about the company.
     * If the company is connected with tardis, public,...
     * 
     * @creationDate    02/02/2017
     * @author          eFaktor     (fbv)
     * 
     * @param           $company
     * 
     * @return          null|stdClass
     * @throws          Exception
     */
    public static function get_extra_info_company($company) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $extra  = null;
        
        try {
            $rdo = $DB->get_record('report_gen_companydata',array('id' => $company),'id,name,public,mapped');
            if ($rdo) {
                $extra = new stdClass();
                $extra->id      = $rdo->id;
                $extra->name    = $rdo->name;
                $extra->public  = $rdo->public;
                if ($rdo->mapped == ORG_MAPPED_TARDIS) {
                    $extra->tardis = 1;
                }else {
                    $extra->tardis = 0;
                }
            }//if_rdo
            
            return $extra;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_extra_info_company

    /**
     * @param           $company
     * @return          null
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company name
     */
    public static function GetCompany_Name($company) {
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['company'] = $company;

            /* SQL Instruction   */
            $sql = " SELECT     rgc.name
                     FROM       {report_gen_companydata} rgc
                     WHERE      rgc.id = :company";


            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompany_Name

    /**
     * @param           $my_companies
     * @param           $user_id
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    08/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the users connected with my companies
     */
    public static function GetUsers_MyCompanies($my_companies,$user_id) {
        /* Variables    */
        global $DB;
        $my_users   = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT     DISTINCT	u.id
                     FROM		{user}						u
                        JOIN	{user_info_competence_data}	uicd	ON 	uicd.userid = u.id
                                                                    AND uicd.companyid  IN ($my_companies)
                     WHERE		u.deleted = 0 ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $my_users = implode(',',array_keys($rdo));
            }//if_rdo

            return $my_users;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_MyCompanies

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/


    /**
     * @param           $tab
     * @return          string
     *
     * @creationDate    26/03/2015
     * @author          eFktor      (fbv)
     *
     * Description
     * Add links to level Zero
     */
    private static function Get_ZeroLevelLink($tab) {
        /* Variables    */
        $out        = null;
        $url_zero   = new moodle_url('/report/manager/' . $tab .'/' . $tab .'_level.php',array('rpt'=>0));

        $out  = '<li>' . "\n";
        $out .= '<a href="'.$url_zero .'">'. get_string('level_report','report_manager',0) .'</a>';
        $out .= '</li>' . "\n";
        $out .= self::Get_FirstLevelLink($tab);

        return $out;
    }//Get_ZeroLevelLink

    /**
     * @param           $tab
     * @return          string
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the links to level one
     */
    private static function Get_FirstLevelLink($tab) {
        /* Variables    */
        $out            = null;
        $url_first      = new moodle_url('/report/manager/' . $tab .'/' . $tab .'_level.php',array('rpt'=>1));

        $out  = '<li>' . "\n";
        $out .= '<a href="'.$url_first .'">'. get_string('level_report','report_manager',1) .'</a>';
        $out .= '</li>' . "\n";
        $out .= self::Get_SecondLevelLink($tab);

        return $out;
    }//Get_FirstLevelLink

    /**
     * @param           $tab
     * @return          string
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add links to level two
     */
    private static function Get_SecondLevelLink($tab) {
        /* Variables    */
        $out                = null;
        $url_second         = new moodle_url('/report/manager/' . $tab .'/' . $tab .'_level.php',array('rpt'=>2));

        $out  = '<li>' . "\n";
        $out .= '<a href="'.$url_second .'">'. get_string('level_report','report_manager',2) .'</a>';
        $out .= '</li>' . "\n";
        $out .= self::Get_ThirdLevelLink($tab);

        return $out;
    }//Get_SecondLevelLink

    /**
     * @param           $tab
     * @return          string
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add link to the third level
     */
    private static function Get_ThirdLevelLink($tab) {
        /* Variables    */
        $out            = null;
        $url_third      = new moodle_url('/report/manager/' . $tab .'/' . $tab .'_level.php',array('rpt'=>3));

        $out = '<li class="last">' . "\n";
        $out .= '<a href="'.$url_third .'">'. get_string('level_report','report_manager',3) .'</a>';
        $out .= '</li>' . "\n";

        return $out;
    }//Get_ThirdLevelLink

    /**
     * Description
     * Get comptence connected with the user
     *
     * @param           $user
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    13/03/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_mycompetence($user) {
        /* Variables */
        global $DB;
        $competence = null;
        $sql        = null;
        $rdo        = null;
        $params     = null;

        try {
            // Search criteria
            $params = array();
            $params['user']  = $user;

            // SQL Instruction
            $sql = " SELECT	  cr_zero.parentid as 'levelzero',
                              GROUP_CONCAT(DISTINCT cr_one.parentid  	ORDER BY cr_one.parentid SEPARATOR ',') as 'levelone',
                              GROUP_CONCAT(DISTINCT cr_two.parentid  	ORDER BY cr_two.parentid SEPARATOR ',') as 'leveltwo',
                              GROUP_CONCAT(DISTINCT uicd.companyid ORDER BY uicd.companyid SEPARATOR ',') 	    as 'levelthree',
                              uicd.jobroles
                     FROM	  {user_info_competence_data} 	uicd
                        -- LEVEL TWO
                        JOIN  {report_gen_company_relation} cr_two	ON 	cr_two.companyid  = uicd.companyid
                        -- LEVEL ONE
                        JOIN  {report_gen_company_relation} cr_one	ON 	cr_one.companyid  = cr_two.parentid
                        -- LEVEL ZERO
                        JOIN  {report_gen_company_relation} cr_zero	ON 	cr_zero.companyid = cr_one.parentid
                     WHERE	  uicd.userid = :user
                     GROUP BY cr_zero.parentid ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $competence[$instance->levelzero] = $instance;
                }//for_rdo
            }//if_rdo

            return $competence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_mycompetence

    /**
     * Description
     * Get competence, access level, to the reporters
     *
     * @param           $userid
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    23/12/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_myreporter_competence($userid) {
        /* Variables */
        $zero   = array();
        $one    = null;
        $two    = null;
        $three  = null;

        try {
            // Level zero
            self::get_myreporter_zero(TBL_REPORTERS,$userid,$zero,$one,$two,$three);
            self::get_myreporter_zero(TBL_MANAGERS,$userid,$zero,$one,$two,$three);

            // Level one
            self::get_myreporter_one(TBL_REPORTERS,$userid,$zero,$one,$two,$three);
            self::get_myreporter_one(TBL_MANAGERS,$userid,$zero,$one,$two,$three);

            // Level two
            self::get_myreporter_two(TBL_REPORTERS,$userid,$zero,$one,$two,$three);
            self::get_myreporter_two(TBL_MANAGERS,$userid,$zero,$one,$two,$three);

            // Level three
            self::get_myreporter_three(TBL_REPORTERS,$userid,$zero,$one,$two,$three);
            self::get_myreporter_three(TBL_MANAGERS,$userid,$zero,$one,$two,$three);

            return $zero;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyReporterCompetence

    /**
     * Description
     * Reporter competence level zero
     * 
     * @param           $table
     * @param           $reporter
     * @param           $zero
     * @param           $one
     * @param           $two
     * @param           $three
     *
     * @throws          Exception
     *
     * @creationDate    20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_myreporter_zero($table,$reporter,&$zero,&$one,&$two,&$three) {
        /* Variables */
        global  $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;
        $field  = null;

        try {
            // Select right table
            switch ($table) {
                case TBL_REPORTERS:
                    $field = 're.reporterid';

                    break;
                case TBL_MANAGERS:
                    $field = 're.managerid';

                    break;
            }//select field_table

            // Search criteria
            $params = array();
            $params['zero']     = 0;
            $params['reporter'] = $reporter;

            // SQL Instruction
            $sql = " SELECT  re.levelzero AS 'levelzero',
                             GROUP_CONCAT(DISTINCT cr.companyid	 	ORDER BY cr.companyid ASC SEPARATOR ',')     AS 'levelone',
                             GROUP_CONCAT(DISTINCT cr_two.companyid ORDER BY cr_two.companyid ASC SEPARATOR ',') AS 'leveltwo',
                             GROUP_CONCAT(DISTINCT cr_tre.companyid ORDER BY cr_tre.companyid ASC SEPARATOR ',') AS 'levelthree',
                             re.hierarchylevel as 'level'
                     FROM	 {". $table ."} 	            re
						-- Level One
                        LEFT JOIN {report_gen_company_relation} 	cr  	ON  cr.parentid 	= re.levelzero
						-- Level Two
                        LEFT JOIN {report_gen_company_relation} 	cr_two  ON  cr_two.parentid =  cr.companyid
						-- Level Three
                        LEFT JOIN {report_gen_company_relation} 	cr_tre 	ON  cr_tre.parentid = cr_two.companyid
                     WHERE	$field 		= :reporter
                        AND re.hierarchylevel  	= 0
                     GROUP BY re.levelzero
                     ORDER BY $field ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $zero[$instance->levelzero] = $instance;
                    // Level one
                    if ($instance->levelone) {
                        if ($one) {
                            $one .= ',';
                        }//if_one

                        $one .= $instance->levelone;
                    }//if_levelone

                    // Level two
                    if ($instance->leveltwo) {
                        if ($two) {
                            $two .= ',';
                        }//if_two

                        $two .= $instance->leveltwo;
                    }//if_leveltwo

                    // Level three
                    if ($instance->levelthree) {
                        if ($three) {
                            $three .= ',';
                        }//if_three

                        $three .= $instance->levelthree;
                    }//if_levelthree
                }//for_Rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_myreporter_zero

    /**
     * Description
     * Reporter competence by level one
     *
     * @param           $table
     * @param           $reporter
     * @param           $zero
     * @param           $one
     * @param           $two
     * @param           $three
     *
     * @throws          Exception
     *
     * @creationDate    20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_myreporter_one($table,$reporter,&$zero,&$one,&$two,&$three) {
        /* Variables */
        global  $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;
        $notin  = null;
        $field  = null;

        try {
            // Select right table
            switch ($table) {
                case TBL_REPORTERS:
                    $field = 're.reporterid';

                    break;
                case TBL_MANAGERS:
                    $field = 're.managerid';

                    break;
            }//select field_table

            // get not in
            $notin = 0;
            if ($zero) {
                $notin = implode(',',array_keys($zero));
            }

            // Search criteria
            $params = array();
            $params['one']      = 1;
            $params['reporter'] = $reporter;

            // SQL Instruction
            $sql = " SELECT   re.levelzero 	as 'levelzero',
                              re.levelone 	as 'levelone',
                              GROUP_CONCAT(DISTINCT cr_two.companyid ORDER BY cr_two.companyid ASC SEPARATOR ',') AS 'leveltwo',
                              GROUP_CONCAT(DISTINCT cr_tre.companyid ORDER BY cr_tre.companyid ASC SEPARATOR ',') AS 'levelthree',
                              re.hierarchylevel as 'level'
                     FROM	  {". $table ."}                  re
                        -- Level two
                        LEFT JOIN  {report_gen_company_relation}   cr_two  ON  cr_two.parentid = re.levelone
                        -- Level three
                        LEFT JOIN  {report_gen_company_relation}   cr_tre  ON  cr_tre.parentid = cr_two.companyid
                     WHERE	  $field 	= :reporter
                        AND   re.levelzero NOT IN ($notin)
                        AND   re.hierarchylevel = :one
                     GROUP BY re.levelzero
                     ORDER BY $field ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $zero[$instance->levelzero] = $instance;
                    // Level one
                    if ($instance->levelone) {
                        if ($one) {
                            $one .= ',';
                        }//if_one

                        $one .= $instance->levelone;
                    }//if_levelone

                    // Level two
                    if ($instance->leveltwo) {
                        if ($two) {
                            $two .= ',';
                        }//if_two

                        $two .= $instance->leveltwo;
                    }//if_leveltwo

                    // Level three
                    if ($instance->levelthree) {
                        if ($three) {
                            $three .= ',';
                        }//if_three

                        $three .= $instance->levelthree;
                    }//if_levelthree
                }//for_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw  $ex;
        }//try_catch
    }//get_myreporter_one

    /**
     * Description
     * Reporter competence level two
     *
     * @param           $table
     * @param           $reporter
     * @param           $zero
     * @param           $one
     * @param           $two
     * @param           $three
     *
     * @throws          Exception
     *
     * @creationDate    20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_myreporter_two($table,$reporter,&$zero,&$one,&$two,&$three) {
        /* Variables */
        global  $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;
        $notin  = null;
        $notone = null;
        $field  = null;

        try {
            // Select right table
            switch ($table) {
                case TBL_REPORTERS:
                    $field = 're.reporterid';

                    break;
                case TBL_MANAGERS:
                    $field = 're.managerid';

                    break;
            }//select field_table

            // get not in
            $notin = 0;
            if ($zero) {
                $notin = implode(',',array_keys($zero));
            }

            // not lvel one
            $notone = 0;
            if ($one) {
                $notone = $one;
            }


            // Search criteria
            $params = array();
            $params['two']      = 2;
            $params['reporter'] = $reporter;

            // SQL Instruction
            $sql = " SELECT   re.levelzero 	as 'levelzero',
                              re.levelone 	as 'levelone',
                              re.leveltwo   as 'leveltwo',
                              GROUP_CONCAT(DISTINCT cr_tre.companyid ORDER BY cr_tre.companyid ASC SEPARATOR ',') AS 'levelthree',
                              re.hierarchylevel as 'level'
                     FROM	  {". $table ."}                  re
                        -- Level three
                        LEFT JOIN  {report_gen_company_relation}   cr_tre  ON  cr_tre.parentid = re.leveltwo
                     WHERE	  $field 	= :reporter
                        AND   re.levelzero NOT IN ($notin)
                        AND   re.levelone  NOT IN ($notone)
                        AND   re.hierarchylevel = :two
                     GROUP BY re.levelzero
                     ORDER BY $field ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $zero[$instance->levelzero] = $instance;
                    // Level one
                    if ($instance->levelone) {
                        if ($one) {
                            $one .= ',';
                        }//if_one

                        $one .= $instance->levelone;
                    }//if_levelone

                    // Level two
                    if ($instance->leveltwo) {
                        if ($two) {
                            $two .= ',';
                        }//if_two

                        $two .= $instance->leveltwo;
                    }//if_leveltwo

                    // Level three
                    if ($instance->levelthree) {
                        if ($three) {
                            $three .= ',';
                        }//if_three

                        $three .= $instance->levelthree;
                    }//if_levelthree
                }//for_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw  $ex;
        }//try_catch
    }//get_myreporter_two

    /**
     * Description
     * Reporter competence level three
     *
     * @param           $table
     * @param           $reporter
     * @param           $zero
     * @param           $one
     * @param           $two
     * @param           $three
     *
     * @throws          Exception
     *
     * @creationDate    20/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_myreporter_three($table,$reporter,&$zero,&$one,&$two,&$three) {
        /* Variables */
        global  $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;
        $notin  = null;
        $notone = null;
        $nottwo = null;
        $field  = null;

        try {
            // Select right table
            switch ($table) {
                case TBL_REPORTERS:
                    $field = 're.reporterid';

                    break;
                case TBL_MANAGERS:
                    $field = 're.managerid';

                    break;
            }//select field_table

            // get not in
            $notin = 0;
            if ($zero) {
                $notin = implode(',',array_keys($zero));
            }

            // not lvel one
            $notone = 0;
            if ($one) {
                $notone = $one;
            }

            // not level two
            $nottwo = 0;
            if ($two) {
                $nottwo = $two;
            }

            // Search criteria
            $params = array();
            $params['three']    = 1;
            $params['reporter'] = $reporter;

            // SQL Instruction
            $sql = " SELECT   re.levelzero 	    as 'levelzero',
                              re.levelone 	    as 'levelone',
                              re.leveltwo       as 'leveltwo',
                              re.levelthree     as 'levelthree',
                              re.hierarchylevel as 'level'
                     FROM	  {". $table ."}  re
                     WHERE	  $field 	= :reporter
                        AND   re.levelzero NOT IN ($notin)
                        AND   re.levelone  NOT IN ($notone)
                        AND   re.leveltwo  NOT IN ($nottwo)
                        AND   re.hierarchylevel = :three
                     GROUP BY re.levelzero
                     ORDER BY $field ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $zero[$instance->levelzero] = $instance;
                    // Level one
                    if ($instance->levelone) {
                        if ($one) {
                            $one .= ',';
                        }//if_one

                        $one .= $instance->levelone;
                    }//if_levelone

                    // Level two
                    if ($instance->leveltwo) {
                        if ($two) {
                            $two .= ',';
                        }//if_two

                        $two .= $instance->leveltwo;
                    }//if_leveltwo

                    // Level three
                    if ($instance->levelthree) {
                        if ($three) {
                            $three .= ',';
                        }//if_three

                        $three .= $instance->levelthree;
                    }//if_levelthree
                }//for_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw  $ex;
        }//try_catch
    }//get_myreporter_three

    /**
     * @static
     * @param           $user_id
     * @param           $site_context
     * @return          int
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbV)
     *
     * Description
     * Get the report/manager view permissions to see the reports
     */
    private static function Get_MyLevelView($user_id,$site_context) {
        /* Variables    */
        $my_level = 4;

        /* Level Zero   */
        if (has_capability('report/manager:viewlevel0', $site_context,$user_id) &&
            has_capability('report/manager:viewlevel1', $site_context,$user_id) &&
            has_capability('report/manager:viewlevel2', $site_context,$user_id) &&
            has_capability('report/manager:viewlevel3', $site_context,$user_id) &&
            has_capability('report/manager:viewlevel4', $site_context,$user_id)) {
            $my_level = 0;
        }else {
            /* Level One    */
            if (!has_capability('report/manager:viewlevel0', $site_context,$user_id) &&
                has_capability('report/manager:viewlevel1', $site_context,$user_id) &&
                has_capability('report/manager:viewlevel2', $site_context,$user_id) &&
                has_capability('report/manager:viewlevel3', $site_context,$user_id) &&
                has_capability('report/manager:viewlevel4', $site_context,$user_id)) {
                $my_level = 1;
            }else {
                /* Level Two    */
                if (!has_capability('report/manager:viewlevel0', $site_context,$user_id) &&
                    !has_capability('report/manager:viewlevel1', $site_context,$user_id) &&
                    has_capability('report/manager:viewlevel2', $site_context,$user_id) &&
                    has_capability('report/manager:viewlevel3', $site_context,$user_id) &&
                    has_capability('report/manager:viewlevel4', $site_context,$user_id)) {
                    $my_level = 2;
                }else {
                    /* Level Third  */
                    if (!has_capability('report/manager:viewlevel0', $site_context,$user_id) &&
                        !has_capability('report/manager:viewlevel1', $site_context,$user_id) &&
                        !has_capability('report/manager:viewlevel2', $site_context,$user_id) &&
                        has_capability('report/manager:viewlevel3', $site_context,$user_id) &&
                        has_capability('report/manager:viewlevel4', $site_context,$user_id)) {
                        $my_level = 3;
                    }else {
                        /* Level Four   */
                        if (!has_capability('report/manager:viewlevel0', $site_context,$user_id) &&
                            !has_capability('report/manager:viewlevel1', $site_context,$user_id) &&
                            !has_capability('report/manager:viewlevel2', $site_context,$user_id) &&
                            !has_capability('report/manager:viewlevel3', $site_context,$user_id) &&
                            has_capability('report/manager:viewlevel4', $site_context,$user_id)) {
                            $my_level = 4;
                        }//if_level_four
                    }//if_level_third
                }//if_level_two
            }//if_level_one
        }//if_else_level_zero

        return $my_level;
    }//Get_MyLevelView
}//CompetenceManager

