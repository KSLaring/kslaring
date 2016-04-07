<?php
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
define('REPORT_MANAGER_UNLINK_SELECTED', 'unlink_selected');
define('REPORT_MANAGER_MANAGERS_SELECTED','managers_selected');
define('REPORT_MANAGER_REPORTERS_SELECTED','reporters_selected');
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

if (!defined('MAX_BULK_USERS')) {
    define('MAX_BULK_USERS', 2000);
}

class CompetenceManager {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

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
            $params['reporterid']           = $userId;
            if ($level >= 0) {
                $params['hierarchylevel']   = $level;
            }
            /* Execute  */
            $rdo = $DB->get_records('report_gen_company_reporter',$params);

            if ($rdo) {
                return true;
            }else {
                return false;
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
            $PAGE->requires->js_init_call('M.core_user.init_organization',
                                          array($selector,$employeeSel,$outcomeSel,$sp,$myAccess,$btnActions),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Organization_Structure

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
            if (($IsReporterManager) && (!is_siteadmin($user_id))) {
                $myHierarchy->competence    = self::Get_MyReporterCompetence($user_id);
                $myHierarchy->my_level      = $reportLevel;
            }else {
                $myHierarchy->competence    = self::Get_MyCompetence($user_id);
                $myHierarchy->my_level      = self::Get_MyLevelView($user_id,$site_context);
            }//if_IsReporter

            return $myHierarchy;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_MyHierarchyLevel

    /**
     * @param           $my_companies
     * @param           $my_level
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
    public static function GetMyCompanies_By_Level($my_companies,$my_level) {
        /* Variables    */
        $levelThree = array();
        $levelTwo   = array();
        $levelOne   = array();
        $levelZero  = array();

        try {
            foreach ($my_companies as $company) {
                $levelZero  = explode(',',$company->levelZero);
                $levelOne   = explode(',',$company->levelOne);
                $levelTwo   = explode(',',$company->levelTwo);
                $levelThree[$company->levelThree]   = $company->levelThree;
            }

            switch ($my_level) {
                case 0:
                    $levelZero  = array();
                    $levelOne   = array();
                    $levelTwo   = array();
                    $levelThree = array();

                   break;
                case 1:
                    $levelOne   = array();
                    $levelTwo   = array();
                    $levelThree = array();

                    break;
                case 2:
                    $levelTwo   = array();
                    $levelThree = array();

                    break;
               case 3:
                    $levelThree = array();

                    break;
               case 4:
                    break;
               default:
                    break;
            }
            return array($levelZero,$levelOne,$levelTwo,$levelThree);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetMyCompanies_By_Level

    /**
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    27/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get companies with employees
     */
    public static function GetCompanies_WithEmployees() {
        /* Variables    */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $companies  = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		GROUP_CONCAT(DISTINCT uicd.companyid  	ORDER BY uicd.companyid SEPARATOR ',') 		as 'levelthree',
                                GROUP_CONCAT(DISTINCT cr_two.parentid  	ORDER BY cr_two.parentid SEPARATOR ',') 	as 'leveltwo',
                                GROUP_CONCAT(DISTINCT cr_one.parentid  	ORDER BY cr_one.parentid SEPARATOR ',') 	as 'levelone',
                                GROUP_CONCAT(DISTINCT cr_zero.parentid  ORDER BY cr_zero.parentid SEPARATOR ',') 	as 'levelzero'
                     FROM		{user_info_competence_data} 		uicd
                        -- LEVEL TWO
                        JOIN	{report_gen_company_relation}   	cr_two	ON 	cr_two.companyid 		= uicd.companyid
                        JOIN	{report_gen_companydata}			co_two	ON 	co_two.id 				= cr_two.parentid
                                                                            AND co_two.hierarchylevel 	= 2
                        -- LEVEL ONE
                        JOIN	{report_gen_company_relation}   	cr_one	ON 	cr_one.companyid 		= cr_two.parentid
                        JOIN	{report_gen_companydata}			co_one	ON 	co_one.id 				= cr_one.parentid
                                                                            AND co_one.hierarchylevel 	= 1
                        -- LEVEL ZERO
                        JOIN	{report_gen_company_relation} 	    cr_zero	ON 	cr_zero.companyid 		= cr_one.parentid
                        JOIN	{report_gen_companydata}			co_zero	ON 	co_zero.id 				= cr_zero.parentid
                                                                            AND co_zero.hierarchylevel 	= 0 ";

            /* EXecute  */
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                $companies = new stdClass();
                $companies->levelZero   = explode(',',$rdo->levelzero);
                $companies->levelOne    = explode(',',$rdo->levelone);
                $companies->levelTwo    = explode(',',$rdo->leveltwo);
                $companies->levelThree  = explode(',',$rdo->levelthree);
            }//if_rdo

            return $companies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompanies_WithEmployees

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

        try {
            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT      jr.id,
                                              jr.name,
                                              jr.industrycode
                     FROM		{report_gen_jobrole}				jr
                        JOIN	{report_gen_jobrole_relation}		jr_rel	ON 	jr_rel.jobroleid = jr.id ";

            switch ($level) {
                case 0:
                    $sql .= "  AND  jr_rel.levelzero    IN ($levelZero) ";

                    break;
                case 1:
                    $sql .= "   AND  jr_rel.levelzero    IN ($levelZero)
                                AND  jr_rel.levelone     IN ($levelOne)
                            ";

                    break;
                case 2:
                    $sql .= "  AND  jr_rel.levelzero    IN ($levelZero)
                               AND  jr_rel.levelone     IN ($levelOne)
                               AND  jr_rel.leveltwo     IN ($levelTwo)
                            ";
                    break;
                case 3:
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
                     WHERE		u.id != :user
                        AND		u.deleted = 0 ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                echo "1";
                foreach ($rdo as $instance) {
                    echo $instance->id . "</br>";
                }
                $my_users = implode(',',array_keys($rdo));

                echo "My Users: " . $my_users . "</br>";

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
     * @static
     * @param           $user_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    13/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get competence data connected with the user
     */
    private static function Get_MyCompetence($user_id) {
        /* Variables    */
        global $DB;
        $my_competence      = array();
        $info_hierarchy     = null;


        try {
            /* Search Criteria  */
            $params = array();
            $params['user_id']  = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		uicd.companyid 		as 'levelthree',
                                GROUP_CONCAT(DISTINCT cr_two.parentid  	ORDER BY cr_two.parentid SEPARATOR ',') 	as 'leveltwo',
                                GROUP_CONCAT(DISTINCT cr_one.parentid  	ORDER BY cr_one.parentid SEPARATOR ',') 	as 'levelone',
                                GROUP_CONCAT(DISTINCT cr_zero.parentid  ORDER BY cr_zero.parentid SEPARATOR ',') 	as 'levelzero',
                                uicd.jobroles
                     FROM		{user_info_competence_data} 	uicd
                        -- LEVEL TWO
                        JOIN	{report_gen_company_relation}   cr_two	ON 	cr_two.companyid 		= uicd.companyid
                        JOIN	{report_gen_companydata}		co_two	ON 	co_two.id 				= cr_two.parentid
                                                                        AND co_two.hierarchylevel 	= 2
                        -- LEVEL ONE
                        JOIN	{report_gen_company_relation}   cr_one	ON 	cr_one.companyid 		= cr_two.parentid
                        JOIN	{report_gen_companydata}		co_one	ON 	co_one.id 				= cr_one.parentid
                                                                        AND co_one.hierarchylevel 	= 1
                        -- LEVEL ZERO
                        JOIN	{report_gen_company_relation} cr_zero	ON 	cr_zero.companyid 		= cr_one.parentid
                        JOIN	{report_gen_companydata}	  co_zero	ON 	co_zero.id 				= cr_zero.parentid
                                                                        AND co_zero.hierarchylevel 	= 0
                     WHERE		uicd.userid = :user_id
                     GROUP BY uicd.companyid ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Hierarchy Info   */
                    $info_hierarchy = new stdClass();
                    $info_hierarchy->levelThree     = $instance->levelthree;
                    $info_hierarchy->levelTwo       = $instance->leveltwo;
                    $info_hierarchy->levelOne       = $instance->levelone;
                    $info_hierarchy->levelZero      = $instance->levelzero;
                    /* Job Roles        */
                    $info_hierarchy->roles          = $instance->jobroles;

                    /* Add  */
                    $my_competence[$instance->levelthree] = $info_hierarchy;
                }//for_companies
            }//if_rdo

            return $my_competence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyCompetence

    /**
     * @param           $userId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    23/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get competence, access level, to the reporters
     */
    private static function Get_MyReporterCompetence($userId) {
        /* Variables */
        global $DB;
        $myCompetence   = null;
        $infoHierarchy  = null;
        $sql            = null;
        $rdo            = null;
        $params         = null;
        $levelOne       = null;
        $levelTwo       = null;
        $levelThree     = null;

        try {
            /* Search Criteria */
            $params = array();
            $params['user']  = $userId;

            $sql = " SELECT GROUP_CONCAT(DISTINCT re.levelzero  ORDER BY re.levelzero SEPARATOR ',')    as 'levelzero',
                            GROUP_CONCAT(DISTINCT re.levelone  	ORDER BY re.levelone 	SEPARATOR ',') 	as 'levelone',
                            GROUP_CONCAT(DISTINCT re.leveltwo  	ORDER BY re.leveltwo 	SEPARATOR ',') 	as 'leveltwo',
                            GROUP_CONCAT(DISTINCT re.levelthree ORDER BY re.levelthree	SEPARATOR ',') 	as 'levelthree'
                     FROM	{report_gen_company_reporter} re
                     WHERE	re.reporterid 		= :user  ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Hierarchy */
                $myCompetence = new stdClass();
                $myCompetence->levelZero   = explode(',',$rdo->levelzero);
                /* Level One    */
                if ($rdo->levelone) {
                    $myCompetence->levelOne    = explode(',',$rdo->levelone);
                }else {
                    $levelOne = self::GetCompanies_LevelList(1,$rdo->levelzero);
                    unset($levelOne[0]);
                    $myCompetence->levelOne = array_keys($levelOne);
                }
                /* Level Two    */
                if ($rdo->leveltwo) {
                    $myCompetence->levelTwo    = explode(',',$rdo->leveltwo);
                }else {
                    $levelTwo = self::GetCompanies_LevelList(2,$rdo->levelone);
                    unset($levelTwo[0]);
                    $myCompetence->levelTwo = array_keys($levelTwo);
                }
                /* Level Three */
                if ($rdo->levelthree) {
                    $myCompetence->levelThree    = explode(',',$rdo->levelthree);
                }else {
                    $levelThree = self::GetCompanies_LevelList(3,$rdo->leveltwo);
                    unset($levelThree[0]);
                    $myCompetence->levelThree = array_keys($levelThree);
                }
            }//if_rdo

            return $myCompetence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyReporterCompetence

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

