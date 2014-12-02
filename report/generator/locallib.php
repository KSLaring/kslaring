<?php

/**
 * Library code for the report generator.
 *
 * @package     report
 * @subpackage  generator
 * @copyright   2010 eFaktor
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 */

/* HERE ADD REQUIRE_ONCE */
define('REPORT_GENERATOR_COMPANY_CANCEL','rg_cancel');
define('REPORT_GENERATOR_COMPANY_FIELD', 'rgcompany');
define('REPORT_GENERATOR_JOB_ROLE_FIELD', 'rgjobrole');
define('REPORT_GENERATOR_ADD_ITEM', 'add_item');
define('REPORT_GENERATOR_RENAME_SELECTED', 'rename_selected');
define('REPORT_GENERATOR_DELETE_SELECTED', 'delete_selected');
define('REPORT_GENERATOR_UNLINK_SELECTED', 'unlink_selected');
define('REPORT_GENERATOR_GET_LEVEL', 'get_level');
define('REPORT_GENERATOR_GET_UNCONNECTED', 'get_unconnected');
define('REPORT_GENERATOR_REMOVE_SELECTED', 'remove_selected');
define('REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL', 'company_structure_level');
define('REPORT_GENERATOR_COMPANY_LIST', 'company_list');
define('REPORT_GENERATOR_EMPLOYEE_LIST', 'employee_list');
define('REPORT_GENERATOR_JOB_ROLE_LIST', 'job_role_list');
define('REPORT_GENERATOR_OUTCOME_LIST', 'outcome_list');
define('REPORT_GENERATOR_COURSE_LIST', 'course_list');
define('REPORT_GENERATOR_USER_LIST', 'user_list');
define('REPORT_GENERATOR_COMPLETED_LIST', 'completed_list');
define('REPORT_GENERATOR_EXPIRE_NEXT_LIST', 'expire_next_list');
define('REPORT_GENERATOR_REPORT_FORMAT_LIST', 'report_format_list');
define('REPORT_GENERATOR_COURSE_REPORT_SELECT_DATA', 'course_report_format_list');
define('REPORT_GENERATOR_OUTCOME_REPORT_SELECT_DATA', 'outcome_report_format_list');
define('REPORT_GENERATOR_IN_PROGRESS', 'in_progress');
define('REPORT_GENERATOR_COMPLETED', 'completed');
define('REPORT_GENERATOR_COMPLETED_BEFORE', 'completed_before');
define('REPORT_GENERATOR_REP_FORMAT_SCREEN', 0);
define('REPORT_GENERATOR_REP_FORMAT_PDF', 1);
define('REPORT_GENERATOR_REP_FORMAT_PDF_MAIL', 2);
define('REPORT_GENERATOR_REP_FORMAT_CSV', 3);
define('REPORT_GENERATOR_ERROR', 0);
define('REPORT_GENERATOR_SUCCESS', 1);
define('REPORT_GENERATOR_ERROR_NO_USER_PROFILE_DATA', 0);

define('REPORT_GENERATOR_IMPORT_1',1);
define('REPORT_GENERATOR_IMPORT_2',2);
define('REPORT_GENERATOR_IMPORT_3',3);

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
            case REPORT_GENERATOR_ERROR_NO_USER_PROFILE_DATA:
                $user_msg = get_string('error_admin_no_company', 'report_generator');
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
/* REPORT GENERATOR FUNCTIONS */
/*******************************/

/**
 * @return          array
 * @throws          Exception
 *
 * @creationDate    21/08/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the counties list
 */
function report_generator_GetCounties_List() {
    /* Variables    */
    global $DB;

    try {
        /* Counties List    */
        $county_lst     = array();
        $county_lst[0]  = get_string('sel_county','report_generator');

        /* Execute  */
        $rdo = $DB->get_records('counties',null,'county','idcounty,county');
        if ($rdo) {
            foreach ($rdo as $county) {
                $county_lst[$county->idcounty] = $county->county;
            }//for_rdo
        }//if_rdo

        return $county_lst;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_generator_GetCounties_List

/**
 * @param           null $id_county
 * @return          array
 * @throws          Exception
 *
 * @creationDate    21/08/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the municipalities connected with the county
 */
function  report_generator_GetMunicipalities_List($id_county = null) {
    /* Variables    */
    global $DB;

    try {
        /* Municipalities List  */
        $municipality_lst       = array();
        $municipality_lst[0]    = get_string('sel_municipality','report_generator');

        /* Search Criteria  */
        $params = null;
        if ($id_county) {
            $params = array();
            $params['idcounty'] = $id_county;
        }
        /* Execute  */
        $rdo = $DB->get_records('municipality',$params,'idcounty,municipality','id,idcounty,idmuni,municipality');
        if ($rdo) {
            foreach ($rdo as $muni) {
                $municipality_lst[$muni->idcounty . '_' . $muni->idmuni] = $muni->municipality;
            }//for_rdo
        }//if_rdo

        return $municipality_lst;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_generator_GetMunicipalities_List

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
function report_generator_getCompanyUser($user_id) {
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
}//report_generator_getCompanyUser

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
function report_generator_getUsersNotAllowed(){
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
        if (has_capability('report/generator:viewlevel1',$site_context,$USER->id)) {
            return null;
        }else if(has_capability('report/generator:viewlevel2',$site_context,$USER->id)) {
            $params['not_level1'] = "report/generator:viewlevel1";

            $sql .= " WHERE		rcap.capability = :not_level1 ";
        }else if(has_capability('report/generator:viewlevel3',$site_context,$USER->id)) {
            $params['not_level1'] = "report/generator:viewlevel1";
            $params['not_level2'] = "report/generator:viewlevel2";

            $sql .= " WHERE		rcap.capability = :not_level1
                         OR     rcap.capability = :not_level2 ";
        }else if(has_capability('report/generator:viewlevel4',$site_context,$USER->id)) {
            $params['not_level1'] = "report/generator:viewlevel1";
            $params['not_level2'] = "report/generator:viewlevel2";
            $params['not_level3'] = "report/generator:viewlevel3";

            $sql .=  " WHERE	rcap.capability = :not_level1
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
            $users_not_my_companies = report_generator_UsersNotMyCompanies($USER->id);

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
}//report_generator_getUsers_SameCapability

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
function report_generator_UsersNotMyCompanies($user_id) {
    global $DB;

    try {
        /* Companies alowwed */
        $companies = report_generator_get_companies_user($user_id);

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
}//report_generator_UsersNotMyCompanies

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
function report_generator_get_companies_user($user_id) {
    global $DB;

    try {
        /* Company User */
        $my_company = report_generator_getCompanyUser($user_id);

        $site_context = CONTEXT_SYSTEM::instance();

        /* Get the levels not alowed to see */
        if (has_capability('report/generator:viewlevel1',$site_context,$user_id)) {
            return report_generator_getCompanies_Level(3);
        }else if(has_capability('report/generator:viewlevel2',$site_context,$user_id)) {
            $level_2 = report_generator_getCompanies_Level(2);
            return  report_generator_getCompanies_Level(3,$level_2);
        }else if(has_capability('report/generator:viewlevel3',$site_context,$user_id)) {
            $level_2 = $DB->get_record('report_gen_company_relation',array('companyid' => $my_company),'parentid');
            return  report_generator_getCompanies_Level(3,$level_2->parentid);
        }else if(has_capability('report/generator:viewlevel4',$site_context,$user_id)) {
            return  $my_company;
        }//IF_capabilities

        return null;
    }catch(Exception $ex) {
        throw $ex;
    }//try_catch
}//report_generator_get_companies_user

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
function report_generator_getCompanies_Level($level,$lst_parent=null) {
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
}//report_generator_getCompanies_LastLevel




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
function report_generator_get_company_level($level,$list_parent){
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
}//report_generator_get_company_last_level

/**
 * @param           $company
 * @return          null
 * @throws          Exception
 *
 * @creationDate    08/01/2013
 * @auhor           eFaktor         (fbv)
 *
 * Description
 * Get the name of the company
 */
function report_generator_get_company_name($company) {
    global $DB;

    try {
        /* SQL Instruction   */
        $sql = " SELECT     GROUP_CONCAT(DISTINCT rgc.name ORDER BY rgc.name SEPARATOR ',') as 'names'
                 FROM       {report_gen_companydata} rgc
                 WHERE      rgc.id IN ($company) ";


        /* Execute */
        if ($rdo = $DB->get_record_sql($sql)) {
            return $rdo->names;
        }else {
            return null;
        }//if_rdo
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_generator_get_company_name

/**
 * @param       null $list
 * @return      array
 * @throws      Exception
 *
 * @updateDate  08/10/2014
 * @author      eFaktor     (fbv)
 *
 * Description
 * Return a list of all job roles available
 */
function report_generator_get_job_role_list($list = null){
    /* Variables    */
    global $DB;
    $job_role_list = array();

    try {
        /* SQL Instruction */
        $sql_Select = " SELECT     id,
                                   name
                        FROM       {report_gen_jobrole} ";

        $sql_Where = '';
        if ($list) {
            $sql_Where .= " WHERE id IN ({$list}) ";
        }//if_list
        $sql_Order = " ORDER BY name ASC ";

        $sql = $sql_Select . $sql_Where . $sql_Order;

        /* Execute */
        if ($rdo = $DB->get_records_sql($sql)) {
            foreach ($rdo as $field) {
                $job_role_list[$field->id] = $field->name;
            }//for_rod_job_roles
        }//if_execute

        return $job_role_list;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_generator_get_job_role_list




/**
 * @param           $job_role_id
 * @return          bool
 * @throws          Exception
 *
 * @creationDate    12/09/2012
 * @updateDate      08/10/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the job role's name
 */
function report_generator_get_job_role_name($job_role_id) {
    /* Variables    */
    global $DB;

    try {
        if ($rdo = $DB->get_record('report_gen_jobrole',array('id'=>$job_role_id))) {
            return $rdo->name;
        }else {
            return false;
        }//if_else_rdo
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_generator_get_job_role_name




/**
 * @return      array|bool       Courses List
 * @throws      Exception
 *
 * @author      eFaktor     (urs)
 *
 * Description
 * Get a list of all courses available
 */
function report_generator_get_course_list() {
    /* Variables    */
    global $DB;

    try {
        /* Course List  */
        $courses_lst = array();
        $courses_lst[0] = get_string('select') . '...';

        /* Get Courses  */
        $rdo = $DB->get_records('course',array('visible' => 1),'fullname','id,fullname');
        if ($rdo) {
            foreach ($rdo as $course) {
                if ($course->id > 1) {
                    $courses_lst[$course->id] =  $course->fullname;
                }
            }//for_rdo
        }//if_rdo

        return $courses_lst;
    }catch(Exception $ex){
        throw $ex;
    }//try_catch
}//report_generator_get_course_list

/**
 * @return      array
 *
 * @author      eFaktor
 *
 * Description
 * Completed List
 */
function report_generator_get_completed_list() {
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
}//report_generator_get_completed_list

/**
 * @param           bool $with_csv
 * @return          array
 *
 * @author          eFaktor
 *
 * Description
 * Report format List
 */
function report_generator_get_report_format_list( $with_csv = true ) {
    $list = array(
        REPORT_GENERATOR_REP_FORMAT_SCREEN      => get_string('preview', 'report_generator'),
        REPORT_GENERATOR_REP_FORMAT_PDF         => get_string('pdf', 'report_generator'),
        REPORT_GENERATOR_REP_FORMAT_PDF_MAIL    => get_string('pdf_and_email', 'report_generator')
    );

    if( $with_csv )
    {
        $list[ REPORT_GENERATOR_REP_FORMAT_CSV ] = get_string('export_csv', 'report_generator');
    }

    return $list;
}//report_generator_get_report_format_list

/**
 * @param           $data_form
 * @return          array|bool
 *
 * @updateDate      20/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return all companies have been selected by the user.
 */
function report_generator_get_companies_to_report($data_form) {
    /* Variables */
    $company_list = array();

    /* Get companies selected */
    $report_level = $data_form['rpt'];
    switch ($report_level) {
        case 1:
            $parent_list = report_generator_get_company_level(2,$data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'1']);
            if (!$parent_list) {
                return false;
            }//if_empty
            $parent_list = join(',',array_keys($parent_list));
            $company_list = report_generator_get_company_level(3,$parent_list);
            break;
        case 2:
            $company_list = report_generator_get_company_level(3,$data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'2']);
            if (!$company_list) {
                return false;
            }//if_empty
            break;
        case 3:
            $company_list = report_generator_get_company_level(3,$data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'2']);
            if (!empty($data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'3'])) {
                $company_keys   = array_keys($company_list);
                $companies      = array_intersect($data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'3'],$company_keys);
                $companies      = array_fill_keys($companies,null);
                $company_list   = array_intersect_key($company_list,$companies);
            }
            if (!$company_list){
                return false;
            }//if_empty
            break;
    }//switch report_level

    return $company_list;
}//report_generator_get_companies_to_report

/**
 * @param           $data_form      Data form
 * @return          string
 *
 * @updateDate      17/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the course report data.
 */
function report_generator_display_course_report($data_form){
    global $DB;

    /* Variables */
    $return_url = new moodle_url('/report/generator/course_report/course_report_level.php',array('rpt' => $data_form['rpt']));
    $return     = '<a href="'.$return_url .'">'. get_string('course_return_to_selection','report_generator') .'</a>';
    $no_data    = get_string('no_data', 'report_generator');
    $no_data   .=  '<br/>' . $return;
    $data       = array();
    $out        = '';

    /* Get Information about course  */
    $course = $DB->get_record('course',array('id'=>$data_form[REPORT_GENERATOR_COURSE_LIST]));
    /* All job roles  selected */
    if (!empty($data_form[REPORT_GENERATOR_JOB_ROLE_LIST])) {
        $list = join(',',$data_form[REPORT_GENERATOR_JOB_ROLE_LIST]);
        $job_role_list = report_generator_job_role_outcome_course_list($list);
    }else {
        $job_role_list = report_generator_job_role_outcome_course_list();
    }//if_else

    if (!$job_role_list) {
        return $no_data;
    }//if_job_role_list

    /* Get Completed Time - Course  */
    $completed_time = $data_form[REPORT_GENERATOR_COMPLETED_LIST];
    $completed_time = report_generator_get_completed_date_timestamp($completed_time,true);

    /* Get companies selected */
    $course_report_info = report_generator_get_companies_to_report($data_form);
    if (!$course_report_info) {
        return $no_data;
    }//if_empty_company_list

    /* Get information about how many users have finished the course or not by Job Role... */
    report_generator_get_course_report_info($course,$course_report_info,$job_role_list,$completed_time);
    if (!$course_report_info) {
        return $no_data;
    }

    /* Course Report  Data */
    $data['report_type']        = 'course';
    $data['report_level']       = $data_form['rpt'];
    $data['course']             = $course;
    $data['completed_time']     = $completed_time;
    $data['course_report_info'] = $course_report_info;
    $company_id                 = $data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'1'];
    $data['level_1']            = report_generator_get_company_name($company_id);

    if ($data['report_level'] > 1) {
        $company_id             = $data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'2'];
        $data['level_2']        = report_generator_get_company_name($company_id);
    }
    $data['format']             = $data_form[REPORT_GENERATOR_REPORT_FORMAT_LIST];

    /* Create Data */
    switch ($data_form[REPORT_GENERATOR_REPORT_FORMAT_LIST]) {
        case REPORT_GENERATOR_REP_FORMAT_SCREEN:
            $out = '<br/><br/>';
            $out .= report_generator_create_course_report_screen_out($data);
            $out .= $return;
            $out .= '<br/><br/>';
            break;
        case REPORT_GENERATOR_REP_FORMAT_PDF:
        case REPORT_GENERATOR_REP_FORMAT_PDF_MAIL:
            $out = report_generator_create_course_report_pdf_out($data);
            /* Check if the report has been created and send it.    */
            if ($out){
                $return     = '<a href="'.$return_url .'">'. get_string('course_return_to_selection','report_generator') .'</a>';
                $no_data    =  $out . '<br/>' . $return;
                return $no_data;
            }//_if_out
            break;
        case REPORT_GENERATOR_REP_FORMAT_CSV:
            $out = report_generator_create_course_report_csv_out($data);
           break;
    }//switch_report

    return $out;
}//report_generator_display_course_report


/**
 * @param           $course             Course data.
 * @param           $company_list       Companies selected.
 * @param           $job_role_list      Job Roles selected.
 * @param           $completed_time     Completed time
 * @throws          Exception
 *
 * @updateDate      18/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all information of the report. How many users have completed or not the course, when they have completed it...
 *
 * Structure Output:
 *
 * course_report_info   Array
 *      [id_company]
 *                      --> company_name
 *                      --> total_completed
 *                      --> total_progress
 *                      --> total_before
 *                      --> report_job      Array
 *                              [id_job_role]
 *                                          --> job_role (name)
 *                                          --> users_completed     Array
 *                                                  [id_user]
 *                                                          --> user_name
 *                                                          --> time_completed
 *                                          --> users_before        Array
 *                                                  [id_user]
 *                                                          --> user_name
 *                                                          --> time_completed
 *                                          --> users_progress      Array
 *                                                  [id_user]
 *                                                          --> user_name
 *                                                          --> time_completed
 */
function report_generator_get_course_report_info($course,&$company_list,&$job_role_list,$completed_time) {
    global $DB;

    /* Company List */
    $companies_keys = array_keys($company_list);
    $companies = join(',',$companies_keys);
    /* Job Roles Keys   */
    $job_roles_keys = array_keys($job_role_list);

    try {
        /* Search Criteria  */
        $params = array();
        $params['course_id'] = $course->id;

        /* SQL Instructio -- Get the users connected with the companies */
        $sql = "    SELECT		DISTINCT
                                u.id,
                                CONCAT(u.firstname, ' ', u.lastname) as user_name
                    FROM 		{user}					u
                        JOIN	{user_enrolments}		ue			ON		ue.userid 	  	= u.id
                        JOIN	{enrol}					e			ON		e.id		  	= ue.enrolid
                                                                    AND		e.courseid	  	=	:course_id
                        JOIN	{user_info_data}		uid_cd		ON		uid_cd.userid 	= ue.userid
                                                                    AND		uid_cd.data		= :company_id
                        JOIN	{user_info_field}		uif_cd  	ON 		uif_cd.id	    = uid_cd.fieldid
                                                                    AND		uif_cd.datatype	= 'rgcompany'
                        JOIN	{user_info_data}		uid_rg		ON		uid_rg.userid   = uid_cd.userid
                        JOIN	{user_info_field}		uif_rg		ON		uif_rg.id		= uid_rg.fieldid
                                                                    AND 	uif_rg.datatype = 'rgjobrole'
                     WHERE        u.deleted = 0
                     ";

        foreach ($job_role_list as $job_id => $job_name) {
            $sql_Sel = " AND uid_rg.data like '%{$job_id}%'
                         ORDER BY	user_name ASC ";

            foreach ($companies_keys as $key) {
                $params['company_id'] = $key;

                $job_role = new stdClass();
                $job_role->job_role         = $job_name;
                $job_role->users_before     = array();
                $job_role->users_completed  = array();
                $job_role->users_progress   = array();

        /* Execute  */
                $rdo_users = $DB->get_records_sql($sql . $sql_Sel,$params);
            if ($rdo_users) {
                foreach ($rdo_users as $user) {
                    /* User Info */
                    $user_info = new stdClass();
                    $user_info->user_name       = $user->user_name;
                    $time_completed             = report_generator_get_course_completed_info_user($params['course_id'],$user->id);
                    $user_info->time_completed  = $time_completed;

                    if ($time_completed) {
                        if ($time_completed < $completed_time) {
                            $job_role->users_before[$user->id] = $user_info;
                                $company_list[$key]->total_before += 1;
                        }else {
                            $job_role->users_completed[$user->id] = $user_info;
                                $company_list[$key]->total_completed += 1;
                        }
                    }else {
                        $job_role->users_progress[$user->id] = $user_info;
                            $company_list[$key]->total_progress += 1;
                    }//if_time_completed
                    }//for_Each_users
            }//if_rdo_users

                $company_list[$key]->report_job[$job_id] = $job_role;
            }//for_companies
        }//for_job_roles
    }catch (Exception $ex){
        throw $ex;
    }//try_catch
}//report_generator_get_course_report_info

/**
 * @param       null $list
 * @return          array
 *
 * @creationDate    18/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return all job roles that are connected with a course and outcomes.
 */
function report_generator_job_role_outcome_course_list($list = null) {
    global $DB;

    $job_role_list = array();

    /* SQL Instruction */

    $sql_Select = " SELECT		DISTINCT jr.id,
                                         jr.name
                    FROM		{report_gen_jobrole}			jr ";
    $sql_Where = "";
    if ($list) {
        $sql_Where .= " WHERE jr.id IN ({$list}) ";
    }//if_list
    $sql_Order = " ORDER BY jr.name ASC ";

    $sql = $sql_Select . $sql_Where . $sql_Order;

    /* Execute */
    if ($rdo = $DB->get_records_sql($sql)) {
        foreach ($rdo as $field) {
            $report_info = new stdClass();
            //$report_info->job_role          = $field->name;
            //$report_info->users_completed   = array();
            //$report_info->users_progress    = array();
            //$report_info->users_before      = array();

            $job_role_list[$field->id] = $field->name;
        }
    }//if_execute

    return $job_role_list;
}//report_generator_job_role_outcome_course_list


/**
 * @param           $course_id      Course Identity
 * @param           $user_id        User Identity
 * @return          bool
 *
 * @creationDate    19/09/2012
 * @author          eFaktor (fbv)
 *
 * Description
 * Return the date when user has completed the course.
 */
function report_generator_get_course_completed_info_user($course_id,$user_id) {
    global $DB;

    /* SQL Instruction  */
    $sql = " SELECT		timecompleted
             FROM		{course_completions}
             WHERE		course = :course
                AND		userid = :user ";

    /* Search Criteria  */
    $params = array();
    $params['course']   = $course_id;
    $params['user']     = $user_id;

    /* Execute  */
    if ($rdo = $DB->get_record_sql($sql,$params)) {
        return $rdo->timecompleted;
    }else {
        return false;
    }
}//report_generator_get_course_completed_info_user

/**
 * @param       $empty_row
 * @param       $report_level
 * @param       $company
 * @param       $report_info
 * @param       $type
 * @param       $out_cvs
 *
 * @updateDate  01/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get the data for the csv outcome file
 */
function report_generator_get_cvs_outcome_table($empty_row,$report_level,$company,$report_info,$type,&$out_cvs) {

    switch ($report_level) {
        case 1;case 2:
        foreach($report_info as $job=>$info) {
            if ($info->courses) {
                foreach ($info->courses as $co=>$course) {
                    $users = report_generator_get_users_info_table($type,$course);
                    if ($users) {
                        $row        = $empty_row;
                        $row[0]     = $company;
                        $row[1]     = $info->job_role;
                        $row[2]     = $course->course_name;
                        $row[3]     = count($users);
                        $out_cvs[]  = $row;
                    }//if_users
                }//for_courses
            }//if_courses
        }//for_job
        break;
        case 3:
            foreach($report_info as $job=>$info) {
                if ($info->courses) {
                    foreach ($info->courses as $id=>$course) {
                        $users = report_generator_get_users_info_table($type,$course);
                        if ($users) {
                            foreach($users as $id=>$user){
                                $row        = $empty_row;
                                $row[0]     = $company;
                                $row[1]     = $info->job_role;
                                $row[2]     = $course->course_name;
                                $row[3]     = $user->user_name;
                                if ($user->time_completed){
                                    $row[4] = userdate($user->time_completed,'%d.%m.%Y', 99, false);
                                }else {
                                    $row[4] = '';
                                }//if_else
                                $row[5]     = 1;
                                $out_cvs[]  = $row;
                            }//for_users
                        }//if_users
                    }//for_courses
                }//if_courses
            }//for_job
            break;
    }//switch_report_level
}//report_generator_get_cvs_outcome_table

/**
 * @param       $cols_cvs
 * @return      array
 *
 * @updateDate  01/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Create a empty row.
 */
function report_generator_create_empty_row_cvs($cols_cvs) {
    $empty_row = array();

    for ($i = 0; $i<$cols_cvs; $i++) {
        $empty_row[$i] = '';
    }//for_cols_cvs

    return $empty_row;
}//report_generator_create_empty_row_cvs

/**
 * @param       $report
 * @param       $report_type
 *
 * @updateDate  01/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Download the csv file.
 */
function report_generator_download_report_cvs($report,$report_type) {
    global $CFG;

    $text_delimiter = '"';
    $delimiter = ',';
    $enc_del_im  = '&#'.ord($delimiter);

    $time = userdate(time(),'%d.%m.%Y', 99, false);
    $filename = clean_filename($report_type . 'report_' . $time . '.txt');

    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header('Content-Description: File Transfer');
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename={$filename}");
    header("Expires: 0");
    header("Pragma: public");

    $fh = @fopen( 'php://output', 'wb' );

    foreach ($report as $row) {
        $row_csv = array();
        foreach ($row as $field) {
            if (!empty($field)) {
                $row_csv[] = $text_delimiter . str_replace($text_delimiter, $text_delimiter . $text_delimiter, $field) . $text_delimiter;
            }
        }
        fputcsv($fh, $row_csv);
    }

    fclose($fh);
    exit;
}//report_generator_download_report_cvs

/**
 * @param       $data           Information of the report
 * @return      bool|string
 *
 * @updateDate  01/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get course report - CSV Format.
 */
function report_generator_create_course_report_csv_out($data) {
    /* Variables    */
    $report_type            = $data['report_type'];
    $report_level           = $data['report_level'];
    $course                 = $data['course'];
    $completed_time         = $data['completed_time'];
    $course_report_info     = $data['course_report_info'];
    $level_1                = $data['level_1'];
    if ($report_level > 1) {
        $level_2            = $data['level_2'];
    }else {
        $level_2            = 0;
    }

    /* Data of the report   */
    $out_csv = array();

    $fields_csv = report_generator_get_fields_report_course_csv($report_level);
    /* Create Empty Row */
    $cols_cvs   = count($fields_csv);
    $empty_row  = report_generator_create_empty_row_cvs($cols_cvs);

    /* Create Header    */
    $out_csv = report_generator_get_header_report_course_csv($empty_row,$report_level,$course,$level_1,$level_2);
    /* Fill the file    */
    report_generator_print_report_course_csv_tables($empty_row,$report_type,$report_level,$course_report_info,$completed_time,$out_csv);
    /* Download the CVS report  */
    report_generator_download_report_cvs($out_csv,$report_type);

    return true;
}//report_generator_create_course_report_csv_out

/**
 * @param       $report_level
 * @return      array
 *
 * @updateDate  01/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get the columns of the csv course file
 */
function report_generator_get_fields_report_course_csv($report_level) {
    /* Variables    */
    $str_job_role       = get_string('job_role', 'report_generator');
    $str_count          = get_string('count', 'report_generator');
    $str_company_name   = get_string('company', 'report_generator');
    $str_username       = get_string('name');
    $str_cert_date      = get_string('cert_date', 'report_generator');

    /* Fields of the CVS file   */
    $fields_csv = array();

    switch ($report_level) {
        case 1;case 2:
            $fields_csv = array($str_company_name,
                                $str_job_role,
                                $str_count);
            break;
        case 3:
            $fields_csv = array($str_company_name,
                                $str_job_role,
                                $str_username,
                                $str_cert_date,
                                $str_count);
            break;
    }//$report_level

    return $fields_csv;
}//report_generator_get_fields_report_course_csv

/**
 * @param       $empty_row
 * @param       $report_level
 * @param       $course
 * @param       $level_1
 * @param       $level_2
 * @return      array
 *
 * @updateDate  01/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get the header of the csv course file
 */
function report_generator_get_header_report_course_csv($empty_row,$report_level,$course,$level_1,$level_2) {
    /* Data of the report   */
    $out = array();

    $row    = $empty_row;
    $row[0] = userdate(time(),'%d.%m.%Y', 99, false);
    $out[]  = $row;
    /* Course Detail   */
    /* Outcome Name     */
    $row    = $empty_row;
    $row[0] = get_string('course') . ' "' . $course->fullname . '"';
    $out[]  = $row;
    /* Course Description  */
    $row    = $empty_row;
    $row[0] = strip_tags($course->summary);
    $out[]  = $row;

    /* Information about companies level */
    /* Level 1 */
    $row    = $empty_row;
    $row[0] = get_string('company_structure_level', 'report_generator', 1) . ': ' . $level_1;
    $out[]  = $row;
    /* Level 2 */
    if ($report_level > 1) {
        $row    = $empty_row;
        $row[0] = get_string('company_structure_level', 'report_generator', 2) . ': ' . $level_2;
        $out[]  = $row;
    }//if_level_2

    return $out;
}//report_generator_get_header_report_course_csv

/**
 * @param       $empty_row
 * @param       $report_type
 * @param       $report_level
 * @param       $course_report_info
 * @param       $completed_time
 * @param       $out_csv
 *
 * @updateDate  01/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get the data for the csv course file
 */
function report_generator_print_report_course_csv_tables($empty_row,$report_type,$report_level,$course_report_info,$completed_time,&$out_csv) {
    /* Variables     */
    $completed_time = userdate($completed_time,'%d.%m.%Y', 99, false);
    $str_completed  = get_string($report_type . '_units_have_completed_since', 'report_generator',$completed_time);
    $str_progress   = get_string($report_type . '_units_in_progress', 'report_generator');
    $str_before     = get_string($report_type . '_units_have_completed_before', 'report_generator',$completed_time);

    /* Create Tables    */
    foreach ($course_report_info as $id=>$company_info) {
        $company    = $company_info->company_name;
        $report_job = $company_info->report_job;

        /* Completed    */
        if ($company_info->total_completed) {;
            $total      = $company_info->total_completed;
            $row        = $empty_row;
            $row[0]     = strip_tags($str_completed);
            $out_csv[]  = $row;
            report_generator_get_cvs_course_table($empty_row,$report_level,$company,$report_job,$total,'completed',$out_csv);
        }//_completed

        /* Progress     */
        if ($company_info->total_progress) {
            $total      = $company_info->total_progress;
            $row        = $empty_row;
            $row[0]     = strip_tags($str_progress);
            $out_csv[]  = $row;
            report_generator_get_cvs_course_table($empty_row,$report_level,$company,$report_job,$total,'progress',$out_csv);
        }//_progress

        /* Before       */
        if ($company_info->total_before) {
            $total      = $company_info->total_before;
            $row        = $empty_row;
            $row[0]     = strip_tags($str_before);
            $out_csv[]  = $row;
            report_generator_get_cvs_course_table($empty_row,$report_level,$company,$report_job,$total,'before',$out_csv);
        }//_before
    }//for_company
}//report_generator_print_report_course_csv_tables

/**
 * @param       $empty_row
 * @param       $report_level
 * @param       $company
 * @param       $report_info
 * @param       $total
 * @param       $type
 * @param       $out_csv
 *
 * @updateDate  01/10/2012
 * @author      eFaktor (fbv)
 *
 * Description
 * Get the data for the csv course file
 */
function report_generator_get_cvs_course_table($empty_row,$report_level,$company,$report_info,$total,$type,&$out_csv) {
    switch($report_level) {
        case 1:case 2;
            foreach($report_info as $job=>$info) {
                $users       = report_generator_get_users_info_table($type,$info);
                if ($users) {
                    $row        = $empty_row;
                    $row[0]     = $company;
                    $row[1]     = $info->job_role;
                    $row[2]     = count($users);
                    $out_csv[]  = $row;
                }//if_users
            }//for_job
            break;
        case 3:
            foreach($report_info as $job=>$info) {
                $users       = report_generator_get_users_info_table($type,$info);
                if ($users) {
                    foreach($users as $id=>$user){
                        $row        = $empty_row;
                        $row[0]     = $company;
                        $row[1]     = $info->job_role;
                        $row[2]     = $user->user_name;
                        if ($user->time_completed){
                            $row[3] = userdate($user->time_completed,'%Y.%m.%d', 99, true);
                        }else {
                            $row[3] = '';
                        }
                        $row[4]     = 1;
                        $out_csv[]  = $row;
                    }//for_users
                }//if_users
            }//for_job
            break;
    }//switch_report_level
}//report_generator_get_cvs_course_table

/**
 * @param       $data       Information of the report
 * @return      string
 *
 * @updateDate  18/09/2012
 * @author      eFaktor (fbv)
 *
 * Description
 * Get the course report - Screen Format
 */
function report_generator_create_course_report_screen_out($data) {
    /* Variables */
    $out                = '';
    $report_type        = $data['report_type'];
    $report_level       = $data['report_level'];
    $course             = $data['course'];
    $completed_time     = $data['completed_time'];
    $course_report_info = $data['course_report_info'];
    $level_1            = $data['level_1'];
    if ($report_level > 1) {
        $level_2        = $data['level_2'];
    }

    /* Create Report */
    $time = userdate(time(),'%d.%m.%Y', 99, false);
    $out .= '<div id="course-report" class="rg-report">';
    $out .= '<h3>';
    $out .= get_string('date') . ': ' . $time . '<br/>';
    $out .= '</h3>';
    $out .= '</div>';

    /* Course Detail */
    $out .= '<h2>';
    $out .= get_string('course') . ' "' . $course->fullname . '"';
    $out .= '</h2>';
    $out .= '<h6>' . format_text($course->summary) . '</h6>';

    /* Companies Level Detail */
    $out .= '<ul class="level-list unlist">';
        $out .= '<li>';
            $out .= '<h2>';
            $out .= get_string('company_structure_level', 'report_generator', 1) . ': ' . $level_1;
            $out .= '</h2>';
        $out .= '</li>';

        if ($report_level >1) {
            $out .= '<li>';
                $out .= '<h2>';
                $out .= get_string('company_structure_level', 'report_generator', 2) . ': ' . $level_2;
                $out .= '</h2>';
            $out .= '</li>';
        }//if_level_2
    $out .= '</ul>';

    $out .= report_generator_print_report_tables($report_type,$report_level,$course_report_info,$completed_time);

    return $out;
}//report_generator_create_course_report_screen_out


/**
 * @param       $data       Information of the report
 * @return      bool|string
 *
 * @updateDate  25/09/2012
 * @author      eFaktor (fbv)
 *
 * Description
 * Get the course report - PDF Format
 */
function report_generator_create_course_report_pdf_out($data) {
    /* Variables    */
    $out_pdf                = array();
    $report_type            = $data['report_type'];
    $report_level           = $data['report_level'];
    $course                 = $data['course'];
    $completed_time         = $data['completed_time'];
    $course_report_info     = $data['course_report_info'];
    $level_1                = $data['level_1'];
    if ($report_level > 1) {
        $level_2            = $data['level_2'];
    }
    $format                 = $data['format'];

    $out_pdf['report_date'] = userdate(time(),'%d.%m.%Y', 99, false);
    $out_pdf['report_name'] = get_string('course') . ' "' . $course->fullname . '"';
    $out_pdf['summary']     = strip_tags($course->summary);

    /* Information about Companies Level*/
    $out_pdf['level_1'] = get_string('company_structure_level', 'report_generator', 1) . ': ' . $level_1;
    if ($report_level > 1) {
        $out_pdf['level_2'] = get_string('company_structure_level', 'report_generator', 2) . ': ' . $level_2;
    }//if_level_2

    $out_pdf['report_format']   = $format;
    $out_pdf['report_type']     = $report_type;
    $out_pdf['report_level']    = $report_level;

    report_generator_print_report_pdf_tables($report_type,$report_level,$course_report_info,$completed_time,$out_pdf);
    return report_generator_prepare_send_pdf($out_pdf);
}//report_generator_create_course_report_pdf_out

/**
 * @param       $report_data    Data of the report
 * @return      bool|string
 *
 * @updateDate  02/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Download or send the report as PDF
 * Create the content rows from the $report array
 * Out put the PDF to the browser to download.
 */
function report_generator_prepare_send_pdf($report_data) {

    $report = new report($report_data);

    return $report->prepare_and_send_pdf();
}//report_generator_prepare_send_pdf


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
function report_generator_get_completed_date_timestamp($index, $future = false) {
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
}//report_generator_get_completed_date_timestamp

/**
 * @param       $report_type
 * @param       $report_level
 * @param       $company_report_info
 * @param       $completed_time
 * @param       $out_pdf
 *
 * @updateDate  255/09/2012
 * @author      Efaktor (fbv)
 *
 * Description
 * Create the tables that contains all information to the report
 */
function report_generator_print_report_pdf_tables($report_type,$report_level,$company_report_info,$completed_time,&$out_pdf) {
    /* Variables     */
    $completed_time = userdate($completed_time,'%d.%m.%Y', 99, false);
    $str_completed  = get_string($report_type . '_units_have_completed_since', 'report_generator',$completed_time);
    $str_progress   = get_string($report_type . '_units_in_progress', 'report_generator');
    $str_before     = get_string($report_type . '_units_have_completed_before', 'report_generator',$completed_time);

    $out            = array();

    /* Create Tables */
    foreach ($company_report_info as $id=>$company_info) {
        $out_completed      = array();
        $out_progress       = array();
        $out_before         = array();

        $company = $company_info->company_name;

        /* Completed */
        if ($company_info->total_completed) {
            $out_completed['intro'] = strip_tags($str_completed);
            $out_completed['tables'] = array();

            $table_completed = report_generator_get_table($report_type,$report_level, 'completed',$company,$company_info->report_job,$company_info->total_completed);
            $out_completed['tables'] = $table_completed;
        }//_completed

        /* Progress */
        if ($company_info->total_progress) {
            $out_progress['intro'] = strip_tags($str_progress);
            $out_progress['tables'] = array();

            $table_progress = report_generator_get_table($report_type,$report_level, 'progress',$company,$company_info->report_job,$company_info->total_progress);
            $out_progress['tables'] = $table_progress;
        }//_in_progress

        /* Before */
        if ($company_info->total_before) {
            $out_before['intro'] = strip_tags($str_before);
            $out_before['tables'] = array();

            $table_before = report_generator_get_table($report_type,$report_level,'before',$company,$company_info->report_job,$company_info->total_before);
            $out_before['tables'] = $table_before;
        }//_before

        $out[$id][REPORT_GENERATOR_COMPLETED]        = $out_completed;
        $out[$id][REPORT_GENERATOR_IN_PROGRESS]      = $out_progress;
        $out[$id][REPORT_GENERATOR_COMPLETED_BEFORE] = $out_before;
    }//for_company_list

    $out_pdf['tables'] = $out;
}//report_generator_print_report_pdf_tables

/**
 * @param           $report_type            Type of report
 * @param           $report_level           Level of report
 * @param           $company_report_info    The data have to be show it.
 * @param           $completed_time         Completed time
 * @return          string
 *
 * @updateDate      18/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Create the tables that contains all information to the report.
 */
function report_generator_print_report_tables($report_type,$report_level,$company_report_info,$completed_time) {
    /* Variables     */
    $completed_time = userdate($completed_time,'%d.%m.%Y', 99, false);
    $str_completed  = get_string($report_type . '_units_have_completed_since', 'report_generator',$completed_time);
    $str_progress   = get_string($report_type . '_units_in_progress', 'report_generator');
    $str_before     = get_string($report_type . '_units_have_completed_before', 'report_generator',$completed_time);

    $out_completed = '';
    $out_progress  = '';
    $out_before    = '';

    /* Create Tables */
    foreach ($company_report_info as $company_info) {
        $company = $company_info->company_name;
        /* Completed */
        if ($company_info->total_completed) {
            $out_completed .= '<p class="time-info">';
            $out_completed .= '<h7>' . $str_completed . '</h7>';
            $out_completed .= '</p>';
            $table_completed = report_generator_get_table($report_type,$report_level,'completed',$company,$company_info->report_job,$company_info->total_completed);
            $out_completed  .= html_writer::table($table_completed);
        }//_completed

        /* Progress */
        if ($company_info->total_progress) {
            $out_progress .= '<p class="time-info">';
            $out_progress .= '<h7>' . $str_progress . '</h7>';
            $out_progress .= '</p>';
            $table_progress = report_generator_get_table($report_type,$report_level,'progress',$company,$company_info->report_job,$company_info->total_progress);
            $out_progress  .= html_writer::table($table_progress);
        }//_in_progress

        /* Before */
        if ($company_info->total_before) {
            $out_before .= '<p class="time-info">';
            $out_before .= '<h7>' . $str_before . '</h7>';
            $out_before .= '</p>';
            $table_before = report_generator_get_table($report_type,$report_level,'before',$company,$company_info->report_job,$company_info->total_before);
            $out_before  .= html_writer::table($table_before);
        }//_before
    }//for_each_company

    if ($report_type == 'course') {
        $out = $out_completed . $out_progress . $out_before;
    }else if ($report_type == 'outcome'){
        $out =  $out_before . $out_progress . $out_completed;
    }//_report_type

    return $out;
}//report_generator_print_course_report_tables

/**
 * @param           $report_level
 * @param           $company
 * @param           $table
 *
 * @creationDate    21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the  head to course report.
 */
function report_generator_get_head_course_table($report_level,$company,&$table) {
    $str_job_role       = get_string('job_role', 'report_generator');
    $str_count          = get_string('count', 'report_generator');
    $str_company_name   = get_string('company', 'report_generator');
    $str_username       = get_string('name');
    $str_cert_date      = get_string('cert_date', 'report_generator');

    switch ($report_level) {
        case 1; case 2:
            $table->head  = array($str_company_name,$str_job_role,$str_count);
            $table->align = array('left','left','center');
            $table->width = "100%";
            /* First Row */
            $row = array();
            /* Company Col  */
            $row[] = $company;
            /* Job Role Col */
            $row[] = '';
            /* Amount       */
            $row[] = '';
            $table->data[] = $row;
            break;
        case 3:
            $table->head  = array($str_company_name,$str_job_role,$str_username,$str_cert_date,$str_count);
            $table->align = array('left','left','left','center','center');
            $table->width = "100%";
            /* First Row */
            $row = array();
            /* Company Col  */
            $row[] = $company;
            /* Job Role Col */
            $row[] = '';
            /* User Name Col */
            $row[] = '';
            /* Date Col     */
            $row[] = '';
            /* Amount       */
            $row[] = '';
            $table->data[] = $row;
            break;
    }//switch_report_level
}//report_generator_get_head_course_table

/**
 * @param           $report_level
 * @param           $company
 * @param           $table
 *
 * @creationDate    21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the head to outcome report
 */
function report_generator_get_head_outcome_table($report_level,$company,&$table) {
    $str_job_role       = get_string('job_role', 'report_generator');
    $str_count          = get_string('count', 'report_generator');
    $str_company_name   = get_string('company', 'report_generator');
    $str_course         = get_string('course');
    $str_username       = get_string('name');
    $str_cert_date      = get_string('cert_date', 'report_generator');

    switch ($report_level) {
        case 1; case 2:
            $table->head  = array($str_company_name,$str_job_role,$str_course,$str_count);
            $table->align = array('left','left','left','center');
            $table->width = "100%";
            /* First Row */
            $row = array();
            /* Company Col  */
            $row[] = $company;
            /* Job Role Col */
            $row[] = '';
            /* Course Col   */
            $row[] = '';
            /* Amount       */
            $row[] = '';
            $table->data[] = $row;
            break;
        case 3:
            $table->head  = array($str_company_name,$str_job_role,$str_course,$str_username,$str_cert_date,$str_count);
            $table->align = array('left','left','left','left','center','center');
            $table->width = "100%";
            /* First Row */
            $row = array();
            /* Company Col  */
            $row[] = $company;
            /* Job Role Col */
            $row[] = '';
            /* Course Col   */
            $row[] = '';
            /* User Name Col */
            $row[] = '';
            /* Date Col     */
            $row[] = '';
            /* Amount       */
            $row[] = '';
            $table->data[] = $row;
            break;
    }//switch_report_level
}//report_generator_get_head_outcome_table

/**
 * @param           $report_level
 * @param           $job
 * @param           $row_info
 * @param           $table
 *
 * @creationDate    21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add a new row into course report table.
 */
function report_generator_add_row_course_table($report_level,$job,$row_info,&$table) {
    switch ($report_level) {
        case 1; case 2:
            /* Rows */
            $row = array();

            /* Company Col  */
            $row[] = '';
             /* Job Role Col */
            $row[] = $job;
            /* Amount Count Col */
            $row[] = count($row_info);

            /* Add row */
            $table->data[] = $row;
            break;
        case 3:
            /* Rows */
            $row = array();

            /* Company Col  */
            $row[] = '';
            /* Job Role Col */
            $row[] = $job;
            /* User Name Col */
            $row[] = '';
            /* Date Col */
            $row[] = '';
            /* Amount Count Col */
            $row[] = '';

            /* Add Job Role Row */
            $table->data[] = $row;

            /* Add User Row */
            foreach ($row_info as $id=>$user) {
                /* Rows */
                $row = array();

                /* Company Col  */
                $row[] = '';
                /* Job Role Col */
                $row[] = '';
                /* User Col     */
                $row[] = $user->user_name;
                /* Date Col     */
                if ($user->time_completed) {
                    $row[] = userdate($user->time_completed,'%d.%m.%Y', 99, false);
                }else {
                    $row[] = '-';
                }
                /* Amount Col   */
                $row[] = '';

                /* Add Job Role Row */
                $table->data[] = $row;
            }//for_users
            break;
    }//switch_report_level
}//report_generator_add_row_course_table

/**
 * @param           $report_level
 * @param           $row_info
 * @param           $type
 * @param           $table
 *
 * @creationDate    21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add a new row into outcome report table
 */
function report_generator_add_row_outcome_table($report_level,$row_info,$type,&$table){
    switch ($report_level){
        case 1;case 2:
            /* Rows */
            $row = array();

            /* Company Col  */
            $row[] = '';
            /* Job Role Col */
            $row[] = $row_info->job_role;
            /* Course Col   */
            $row[] = '';
            /* Amount Count Col */
            $row[] = '';

            /* Add row */
            $table->data[] = $row;

            foreach ($row_info->courses as $id=>$course) {
                $users = report_generator_get_users_info_table($type,$course);

                if ($users) {
                    /* Rows */
                    $row = array();

                    /* Company Col  */
                    $row[] = '';
                    /* Job Role Col */
                    $row[] = '';
                    /* Course Col   */
                    $row[] = $course->course_name;
                    /* Amount Count Col */
                    $row[] = count($users);

                    /* Add row */
                    $table->data[] = $row;
                }//if_users
            }//for_courses

            break;
        case 3:
            /* Rows */
            $row = array();

            /* Company Col  */
            $row[] = '';
            /* Job Role Col */
            $row[] = $row_info->job_role;
            /* Course Col   */
            $row[] = '';
            /* User Col */
            $row[] = '';
            /* Completed Time   */
            $row[] = '';
            /* Amount Count Col */
            $row[] = '';

            /* Add row */
            $table->data[] = $row;

            $total_users = 0;
            foreach ($row_info->courses as $id=>$course) {
                $users = report_generator_get_users_info_table($type,$course);
                if ($users) {
                    /* Add row */
                    $row = array();

                    /* Company Col  */
                    $row[] = '';
                    /* Job Role Col */
                    $row[] = '';
                    /* Course Col   */
                    $row[] = $course->course_name;
                    /* User Col */
                    $row[] = '';
                    /* Completed Time   */
                    $row[] = '';
                    /* Amount Count Col */
                    $row[] = '';

                    $table->data[] = $row;

                    foreach($users as $id=>$info){
                        /* Rows */
                        $row = array();

                        /* Company Col  */
                        $row[] = '';
                        /* Job Role Col */
                        $row[] = '';
                        /* Course Col   */
                        $row[] = '';
                        /* User Col */
                        $row[] = $info->user_name;
                        /* Completed Time   */
                        if ($info->time_completed){
                            $row[] = userdate($info->time_completed,'%d.%m.%Y', 99, false);
                        }else {
                            $row[] = '-';
                        }
                        /* Amount Count Col */
                        $row[] = '';
                        $total_users += 1;
                        /* Add row */
                        $table->data[] = $row;
                    }//for_users
                }//users
            }//for_courses

            /* Add Last row */
            $table->data[] = report_generator_add_last_row_outcome_table($total_users);
            break;
    }//switch
}//report_generator_add_row_outcome_table


/**
 * @param           $total
 * @return          array
 *
 * @creationDate    21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add the last row into outcome report table. It's only to the last level.
 */
function report_generator_add_last_row_outcome_table($total){
    /* Rows */
    $row = array();

    /* Company Col  */
    $row[] = '';
    /* Job Role Col */
    $row[] = '';
    /* Course Col   */
    $row[] = '';
    /* User Col */
    $row[] = '';
    /* Completed Time   */
    $row[] = '';
    /* Amount Count Col */
    $row[] = $total;

    return $row;
}//report_generator_add_last_row_outcome_report

/**
 * @param           $type       Information type. Completed, in progress or completed before.
 * @param           $info       Information report.
 * @return          array
 *
 * @updateDate      21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return all the users that they have completed or not the course, or they have completed it before a specific date.
 */
function report_generator_get_users_info_table($type,$info) {
    $users = array();

    switch ($type) {
        case 'completed':
            $users =  $info->users_completed;
            break;
        case 'progress':
            $users =  $info->users_progress;
            break;
        case 'before':
            $users =  $info->users_before;
            break;
    }

    return $users;
}//report_generator_get_users_info_table

/**
 * @param           $total
 * @param           $table
 *
 * @creationDate    21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add the last row. It's only to the last level.
 */
function report_generator_add_last_row_course_table($total, &$table) {
    /* First Row */
    $row = array();
    /* Company Col  */
    $row[] = '';
    /* Job Role Col */
    $row[] = '';
    /* User Name Col */
    $row[] = '';
    /* Date Col     */
    $row[] = '';
    /* Amount       */
    $row[] = $total;
    $table->data[] = $row;
}//report_generator_add_last_row_course_table

/**
 * @param           $report_type        Report type
 * @param           $report_level       Report Level
 * @param           $type_table         Information type. Completed, In progress or Completed before
 * @param           $company            Company's name
 * @param           $report_info        Report Data
 * @param           $total              Total amount
 * @return          html_table
 *
 * @updateDate      20/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Create the table with all user of the course.
 */
function report_generator_get_table($report_type,$report_level,$type_table,$company,$report_info,$total) {
    /* Create Table */
    $table = new html_table();

    switch ($report_type) {
        case 'course':
            report_generator_get_head_course_table($report_level,$company,$table);
            /* Row -> Job Roles */
            foreach ($report_info as $id=>$info) {
                $users = report_generator_get_users_info_table($type_table,$info);

                if ($users) {
                    report_generator_add_row_course_table($report_level,$info->job_role,$users,$table);
                }//if_users
            }//for
            if ($report_level == 3) {
                report_generator_add_last_row_course_table($total, $table);
            }
            break;
        case 'outcome':
            report_generator_get_head_outcome_table($report_level,$company,$table);
            foreach($report_info as $id=>$info) {
                if ($info->courses) {
                    report_generator_add_row_outcome_table($report_level,$info,$type_table,$table);
                }//if_courses
            }//for
            break;
    }//switch_report_type

    return $table;
}//report_generator_get_table

/**
 * Feedback dialog
 *
 * Create feedback dialog with given text
 *
 * @param       $text
 * @return      string
 */
function report_generator_get_feedback_dialog($text) {
    $sendpdfdialog = '<div class="modal" id="sendpdfdialog" title="' .
        get_string( 'sendpdfdialogtitle', 'report_reportgenerator' ) . '">';
    $sendpdfdialog .= '<div>';
    $sendpdfdialog .= '<p>' . $text . '</p>';
    $sendpdfdialog .= '<p class="button"><button class="close"> Ok </button></p>';
    $sendpdfdialog .= '</div>';
    $sendpdfdialog .= '</div>';

    return $sendpdfdialog;
}


/**
 * @updateDate      14/09/2012
 * @author          eFaktor (fbv)
 *
 * Description
 * Draw different options of report page.
 */
function report_generator_print_report_page($tab, $site_context) {
    global $OUTPUT;

    /* Create links - It's depend on View permissions */
    $out = '<ul class="unlist report-selection">' . "\n";
        if (has_capability('report/generator:viewlevel3', $site_context)) {
            $out .= report_generator_get_links_report_third_level($tab);
        }else if(has_capability('report/generator:viewlevel2', $site_context)) {
            //$out .= report_generator_get_links_report_second_level($tab);
        }else if (has_capability('report/generator:viewlevel1', $site_context)) {
            //$out .= report_generator_get_links_report_first_level($tab);
        }//if_capabitity
    $out .= '</ul>' . "\n";

    /* Draw Links */
    echo $OUTPUT->heading($out);
}//report_generator_print_course_report_page

/**
 * @param           $tab        Course or Outcome
 * @return          string      Level Links
 *
 * @creationDate    18/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Build the report link to the first level.
 */
function report_generator_get_links_report_first_level($tab) {
    $url_level_1 = new moodle_url('/report/generator/' . $tab .'/' . $tab .'_level.php',array('rpt'=>1));

    $out = '';
    $out .= '<li class="first">' . "\n";
    $out .= '<a href="'.$url_level_1 .'">'. get_string('level_report','report_generator',1) .'</a>';
    $out .= '</li>' . "\n";

    return $out;
}//report_generator_get_links_report_first_level

/**
 * @param           $tab        Course or Outcome
 * @return          string      Level links
 *
 * @creationDate    18/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Build the report link to the second level.
 */
function report_generator_get_links_report_second_level($tab) {
    $url_level_2 = new moodle_url('/report/generator/' . $tab .'/' . $tab .'_level.php',array('rpt'=>2));

    $out = report_generator_get_links_report_first_level($tab);
    $out .= '<li>' . "\n";
    $out .= '<a href="'.$url_level_2 .'">'. get_string('level_report','report_generator',2) .'</a>';
    $out .= '</li>' . "\n";

    return $out;
}//report_generator_get_links_report_second_level

/**
 * @param           $tab        Course or Outcome
 * @return          string      Level links
 *
 * @creationDate    18/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Build the report link to the third level.
 */
function report_generator_get_links_report_third_level($tab) {
    $url_level_3 = new moodle_url('/report/generator/' . $tab .'/' . $tab .'_level.php',array('rpt'=>3));

    //$out = report_generator_get_links_report_second_level($tab);
    $out = '<li class="last">' . "\n";
    $out .= '<a href="'.$url_level_3 .'">'. get_string('level_report','report_generator',3) .'</a>';
    $out .= '</li>' . "\n";

    return $out;
}//report_generator_get_links_report_third_level










































