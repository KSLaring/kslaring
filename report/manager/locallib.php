<?php

/**
 * Library code for the report Competence Manager.
 *
 * @package     report
 * @subpackage  manager
 * @copyright   2010 eFaktor
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 */

/* HERE ADD REQUIRE_ONCE */
define('REPORT_MANAGER_COMPANY_CANCEL','rg_cancel');
define('REPORT_MANAGER_COMPANY_FIELD', 'rgcompany');
define('REPORT_MANAGER_ADD_ITEM', 'add_item');
define('REPORT_MANAGER_RENAME_SELECTED', 'rename_selected');
define('REPORT_MANAGER_DELETE_SELECTED', 'delete_selected');
define('REPORT_MANAGER_UNLINK_SELECTED', 'unlink_selected');
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
require_once($CFG->dirroot . '/user/filters/lib.php');
require_once('trackerlib.php');
require_once('report.php');
require_once('report_pdf.php');

class RGException extends Exception {
    public function __construct($message, $code = 0)
    {
        // make sure everything is assigned properly
        parent::__construct($message, $code);

        // log what we know
        $msg = "------------------------------------------------\n";
        $msg .= __CLASS__ . ": [ {$this->code}]: {$this->message}\n";
        $msg .= $this->getTraceAsString() . "\n";
        error_log($msg);
    }

    // overload the __toString() method to suppress any "normal" output
    public function __toString() {
        return $this->printMessage();
    }

    // map error codes to output messages or templates
    public function printMessage() {
        $user_msg = '';
        $code = $this->getCode();

        switch ($code) {
            case REPORT_MANAGER_ERROR_NO_USER_PROFILE_DATA:
                $user_msg = get_string('error_admin_no_company', 'report_manager');
                break;

            default:
                $user_msg = "------------------------------------------------<br />";
                $user_msg .= __CLASS__ . ": [ {$this->code}]: {$this->message}<br />";
                $user_msg .= $this->getTraceAsString() . "<br />";
                break;
        }
        return $user_msg;
    }

    // static exception_handler for default exception handling
    public static function exception_handler($exception)
    {
        throw new RGException($exception);
    }
}//class


/*******************************/
/* REPORT MANAGER FUNCTIONS */
/*******************************/


/**
 * @param           $user_id
 * @return          null
 * @throws          Exception
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the company connected with user
 */
function report_manager_getCompanyUser($user_id) {
    global $DB;

    try {
        /* Search Criteria  */
        $params = array();
        $params['user_id']  = $user_id;
        $params['rg']       = 'rgcompany';

        /* SQL Instruction  */
        $sql = " SELECT		uid.data
                 FROM		{user_info_data}	uid
	                JOIN	{user_info_field}	uif		ON		uid.fieldid 	= uif.id
											            AND 	uif.datatype 	= :rg
                 WHERE		uid.userid = :user_id ";

        /* Execute  */
        $rdo = $DB->get_record_sql($sql,$params);
        if ($rdo) {
            return $rdo->data;
        }else {
            return null;
        }//if_else_rdo
    }catch (Exception $ex) {
        throw $ex;
    }//trry_catch
}//report_manager_getCompanyUser

/**
 * @return          null|string
 * @throws          Exception
 *
 * @creationDate    20/03/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the users are not connected with the main user.
 */
function report_manager_getUsersNotAllowed(){
    global $DB,$USER;

    try {
        /* Context  */
        $site_context = CONTEXT_SYSTEM::instance();
        $not_allowed = null;

        /* Search Criteria  */
        $params = array();
        $params['context_id']   = $site_context->id;
        $params['user_id']      = $USER->id;

        /* SQL Instruction  */
        $sql = " SELECT		DISTINCT ra.userid
                 FROM		{role_capabilities} 	rcap
                    JOIN	{role}				 	r		ON 		r.id 			= rcap.roleid
                    JOIN	{role_assignments}		ra		ON		ra.roleid		= r.id
                                                            AND		ra.contextid 	= rcap.contextid
                                                            AND		ra.contextid 	= :context_id
                                                            AND     ra.userid       <> :user_id ";

        /* Get the levels not alowed to see */
        if (has_capability('report/manager:viewlevel0',$site_context,$USER->id)) {
            return null;
        }else if (has_capability('report/manager:viewlevel1',$site_context,$USER->id)) {
            $params['not_level0'] = "report/manager:viewlevel0";

            $sql .= " WHERE		rcap.capability = :not_level0 ";
        }else if(has_capability('report/manager:viewlevel2',$site_context,$USER->id)) {
            $params['not_level0'] = "report/manager:viewlevel0";
            $params['not_level1'] = "report/manager:viewlevel1";

            $sql .= " WHERE		rcap.capability = :not_level0
                         OR     rcap.capability = :not_level1 ";
        }else if(has_capability('report/manager:viewlevel3',$site_context,$USER->id)) {
            $params['not_level0'] = "report/manager:viewlevel0";
            $params['not_level1'] = "report/manager:viewlevel1";
            $params['not_level2'] = "report/manager:viewlevel2";

            $sql .= " WHERE		rcap.capability = :not_level0
                         OR     rcap.capability = :not_level1
                         OR     rcap.capability = :not_level2 ";
        }else if(has_capability('report/manager:viewlevel4',$site_context,$USER->id)) {
            $params['not_level0'] = "report/manager:viewlevel0";
            $params['not_level1'] = "report/manager:viewlevel1";
            $params['not_level2'] = "report/manager:viewlevel2";
            $params['not_level3'] = "report/manager:viewlevel3";

            $sql .=  " WHERE	rcap.capability = :not_level0
                          OR    rcap.capability = :not_level1
                          OR    rcap.capability = :not_level2
                          OR    rcap.capability = :not_level3 ";
        }//IF_capabilities

        /* Execute  */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            /* Users List   */
            $not_allowed = implode(',',array_keys($rdo));
            if (!is_siteadmin($USER->id)) {
                if ($not_allowed) {
                $not_allowed .= ',2';
                }else {
                    $not_allowed .= '2';
                }

            }
            /* Add the users that are not from my companies */
            $users_not_my_companies = report_manager_UsersNotMyCompanies($USER->id);

            if ($users_not_my_companies) {
                $not_allowed .= ',' . $users_not_my_companies;
            }//if_users_not_my_companies

            return $not_allowed;
        }else {
            return null;
        }//if_else
    }catch(Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_getUsers_SameCapability

/**
 * @param           $user_id
 * @return          null
 * @throws          Exception
 *
 * @creationDate    20/03/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the users thar are not connected with my companies.
 */
function report_manager_UsersNotMyCompanies($user_id) {
    global $DB;

    try {
        /* Companies alowwed */
        $companies = report_manager_get_companies_user($user_id);

        if (!$companies) {
            $companies = 0;
        }

        /* Search Criteria  */
        $params = array();
        $params['rgcompany'] = 'rgcompany';

        /* SQL Instruction  */
        $sql = " SELECT		u.id
                 FROM		{user}              u
                    JOIN    {user_info_data}	uid ON uid.userid 		= u.id
                    JOIN	{user_info_field}	uif ON uif.id			=	uid.fieldid
                                                    AND	uif.datatype 	=  :rgcompany
                 WHERE		uid.data NOT IN ($companies)
                    AND     u.deleted = 0 ";

        /* Execute  */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            $users = implode(',',array_keys($rdo));
            return $users;
        }else {
            return null;
        }//if_rdo
    }catch(Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_UsersNotMyCompanies

/**
 * @param           $user_id
 * @return          null
 * @throws          Exception
 *
 * @creationDate    20/03/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the companies that the user can see. It depends on the user role.
 */
function report_manager_get_companies_user($user_id) {
    global $DB;

    try {
        /* Company User */
        $my_company = report_manager_getCompanyUser($user_id);

        $site_context = CONTEXT_SYSTEM::instance();

        /* Get the levels not alowed to see */
        if (has_capability('report/manager:viewlevel1',$site_context,$user_id)) {
            return report_manager_getCompanies_Level(3);
        }else if(has_capability('report/manager:viewlevel2',$site_context,$user_id)) {
            $level_2 = report_manager_getCompanies_Level(2);
            return  report_manager_getCompanies_Level(3,$level_2);
        }else if(has_capability('report/manager:viewlevel3',$site_context,$user_id)) {
            $level_2 = $DB->get_record('report_gen_company_relation',array('companyid' => $my_company),'parentid');
            return  report_manager_getCompanies_Level(3,$level_2->parentid);
        }else if(has_capability('report/manager:viewlevel4',$site_context,$user_id)) {
            return  $my_company;
        }//IF_capabilities

        return null;
    }catch(Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_get_companies_user

/**
 * @param           $level
 * @param      null $lst_parent
 * @return          null
 * @throws          Exception
 *
 * @creationDate    20/03/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the companies connected with one level and parent.
 */
function report_manager_getCompanies_Level($level,$lst_parent=null) {
    global $DB;

    try {
        /* Research Criteria */
        $params = array();
        $params['level']    = $level;

        /* SQL Instruction   */
        $sql_Select = " SELECT     DISTINCT rcd.id
                        FROM       {report_gen_companydata} rcd ";
        /* Join */
        $sql_Join = " ";
        if ($lst_parent) {
            $sql_Join = " JOIN  {report_gen_company_relation} rcr ON   rcr.companyid = rcd.id
                                                                 AND   rcr.parentid  IN ($lst_parent) ";
        }//if_level

        $sql_Where = " WHERE rcd.hierarchylevel = :level ";

        /* SQL */
        $sql = $sql_Select . $sql_Join . $sql_Where;

        /* Execute  */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            return implode(',',array_keys($rdo));
        }else {
            return null;
        }
    }catch(Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_getCompanies_LastLevel




/**
 * @param           $level
 * @param           $list_parent
 * @return          array
 * @throws          Exception
 *
 * @creationDate    18/09/2012
 * @updateDate      08/10/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return a list of all companies that belong to a specific level and they are under a specific company.
 */
function report_manager_get_company_level($level,$list_parent){
    /* Variables    */
    global $DB;
    $company_list = array();

    try {
        /* Search Criteria */
        $params = array();
        $params['level'] = $level;

        /* SQL Instruction */
        $sql = " SELECT     DISTINCT rcd.id,
                            rcd.name
                 FROM       {report_gen_companydata} rcd
                    JOIN    {report_gen_company_relation} rcr ON    rcr.companyid = rcd.id
                                                              AND   rcr.parentid  IN ({$list_parent})
                 WHERE      rcd.hierarchylevel = :level
                 ORDER BY   rcd.name ASC ";

        /* Execute  */
        if ($rdo = $DB->get_records_sql($sql,$params)) {
            foreach ($rdo as $field) {
                $company_info = new stdClass();
                $company_info->company_name     = $field->name;
                $company_info->total_completed  = 0;
                $company_info->total_progress   = 0;
                $company_info->total_before     = 0;
                $company_info->report_job       = null;

                $company_list[$field->id] = $company_info;
            }//foreach
        }//if_rdo

        return $company_list;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_get_company_last_level




/**
 * @return      array
 *
 * @author      eFaktor
 *
 * Description
 * Completed List
 */
function report_manager_get_completed_list() {
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
}//report_manager_get_completed_list


/**
 * Get timestamp for selected time period
 *
 * Calculate the time from the selected time relative to the actaul date.
 * $future defines if the time shall be in the future or in the past
 *
 * @param int $index
 * @param boolean $future
 * @return int unix timestamp
 */
function report_manager_get_completed_date_timestamp($index, $future = false) {
    $today = strtotime("today", usertime( time() ));
    $future = $future ? 1 : -1;
    $ts = 0;

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
}//report_manager_get_completed_date_timestamp










































