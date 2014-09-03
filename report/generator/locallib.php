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
        $rdo = $DB->get_records('municipality',$params,'idcounty,municipality','idmuni,municipality');
        if ($rdo) {
            foreach ($rdo as $muni) {
                $municipality_lst[$muni->idmuni] = $muni->municipality;
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

    /* Search Criteria  */
    $params = array();
    $params['user_id']  = $user_id;
    $params['rg']       = 'rgcompany';

    try {
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
 * @return          array
 *
 * @creationDate    18/11/2013
 * @author          eFaktor (fbv)
 *
 * Description
 * Gets the levels list for the 'Import Structure Companies' function
 */
function report_generator_get_level_list_to_import() {
    /* Level to Import  */
    $level_to_import = array();

    $level_to_import[0] = get_string('sel_level','report_generator');
    $level_to_import[REPORT_GENERATOR_IMPORT_1] = get_string('level_1','report_generator');
    $level_to_import[REPORT_GENERATOR_IMPORT_2] = get_string('level_2','report_generator');
    $level_to_import[REPORT_GENERATOR_IMPORT_3] = get_string('level_3','report_generator');

    return $level_to_import;
}//report_generator_get_level_list_to_import

/**
 * @param           $level
 * @param           $parent
 * @return          array
 * @throws          Exception
 *
 * @creationDate    18/11/2013
 * @author          eFaktor (fbv)
 *
 * Description
 * Gets children list for one level.
 */
function report_generator_get_list_parent_import($level, $parent=null) {
    global $DB;

    /* parent_import */
    $parent_import = array();
    $parent_import[0] = get_string('sel_parent','report_generator');

    try {
        if ($level > 1) {
            /* Search Criteria  */
            $params = array();
            $params['hierarchylevel'] = $level-1;

            if (!$parent) {
                $rdo = $DB->get_records('report_gen_companydata',$params,'name ASC','id, name');
            }else {
                /* Search Criteria  */
                $params = array();
                $params['parent'] = $parent;

                /* SQL Instruction   */
                $sql = " SELECT     rcd.id,
                                    rcd.name
                         FROM       {report_gen_companydata}       rcd
                            JOIN    {report_gen_company_relation}  rcr ON    rcr.companyid = rcd.id
                                                                       AND   rcr.parentid  = :parent ";

                $rdo = $DB->get_records_sql($sql,$params);
            }

            if ($rdo) {
                foreach ($rdo as $instance) {
                    $parent_import[$instance->id] = $instance->name;
                }//for_rdo
            }//if_rdo
        }//if_level

        return $parent_import;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_generator_get_list_parent_import

/**
 * @param           csv_import_reader $cir
 * @param           $stdfields
 * @param           $error
 * @return          array
 *
 * @creationDate    18/11/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Checks the columns from the CSV file
 */
function report_generator_import_validate_columns(csv_import_reader $cir, $stdfields, &$error) {
    $columns = $cir->get_columns();
    $error = NON_ERROR;

    if (empty($columns)) {
        $cir->close();
        $cir->cleanup();
        $error = CANNOT_READ_TMP_FILE;
    }

    // test columns
    $processed = array();
    foreach ($columns as $key=>$unused) {
        $field = $columns[$key];
        $lcfield = $field;
        if (in_array($field, $stdfields) or in_array($lcfield, $stdfields)) {
            // standard fields are only lowercase
            $newfield = $lcfield;
        } else if (preg_match('/^(cohort|course|group|type|role|enrolperiod)\d+$/', $lcfield)) {
            // special fields for enrolments
            $newfield = $lcfield;
        } else {
            $cir->close();
            $cir->cleanup();
            $error = CSV_LOAD_ERROR;
        }
        if (in_array($newfield, $processed)) {
            $cir->close();
            $cir->cleanup();
            $error = DUPLICATE_FIELD_NAME;
        }
        $processed[$key] = $newfield;
    }//for

    return $processed;
}//report_generator_import_validate_columns

/**
 * @param           $columns
 * @param           $cir
 * @return          stdClass
 *
 * @creationDate    18/11/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Validates the content of the CSV file
 */
function report_generator_import_validate_data($columns, $cir) {
    global $DB;

    /* Records File */
    $records_file               = new stdClass();
    $records_file->errors       = array();
    $records_file->info         = array();

    /* Validate the file */
    $i = 0;
    $cir->init();
    while ($fields = $cir->next()) {
        $status = '';
        foreach($fields as $key => $field) {
            $field_name         = $columns[$key];
            $rows[$field_name]  = trim(s($field));

            /* Check that doesn't exist another company with the same name */
            $data = trim(s($field));
            if ($DB->get_record('report_gen_companydata',array('name' => $data),'id')) {
                $status .= get_string('err_company','report_generator') . '<br/>';
            }//if_exist
        }//foreach

        if ($status != '') {
            $records_file->errors[$i] = $i;
        }//if_error
        $rows['status'] = $status;
        $records_file->info[$i] = $rows;

        $i += 1;
    }//while

    $cir->close();

    return $records_file;
}//report_generator_import_validate_data

/**
 * @param           $records_file
 * @param           $level
 * @param           $level_parent
 * @return          bool
 *
 * @creationDate    18/11/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Import the company structure for a specific level
 */
function report_generator_import_structure($records_file,$level,$level_parent) {
    global $DB;

    /* Import Company Structure */
    $errors         = $records_file->errors;
    $info_records   = $records_file->info;

    /* Begin Transaction */
    $trans = $DB->start_delegated_transaction();
    try {
        foreach($info_records as $line=>$record) {
            if (!array_key_exists($line,$errors)) {
                $record = $info_records[$line];

                /* Insert the new company  */
                $company = new stdClass();
                $company->name              = $record['company'];
                $company->hierarchylevel    = $level;
                $company->modified          = time();

                $company->id = $DB->insert_record('report_gen_companydata',$company);
                if ($level_parent) {
                    $parent = new stdClass();
                    $parent->companyid  = $company->id;
                    $parent->parentid   = $level_parent;
                    $parent->modified   = time();

                    $DB->insert_record('report_gen_company_relation',$parent);
                }//if_parent
            }//if_line_error
        }//for

        /* Commit */
        $trans->allow_commit();

        return true;
    }catch(Exception $ex){
        /* Rollback */
        $trans->rollback($ex);

        return false;
    }//try_catch
}//report_generator_import_structure

/**
 * @param           $records_file
 * @param           $per_page
 * @param           $total_not_imported
 * @return          html_table
 *
 * @creationDate    18/11/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Creates the table which shows all the records have not been imported
 */
function report_generator_import_not_imported($records_file,$per_page,$total_not_imported) {
    /* Table Not Imported   */
    $table_not_imported = report_generator_import_header_not_imported();

    /* Data */
    $errors         = $records_file->errors;
    $info_records   = $records_file->info;

    /* Records to show  */
    if ($total_not_imported <= $per_page) {
        $index = $total_not_imported;
    }else {
        $index = $per_page;
    }//if_total_not_imported

    for ($i = 0; $i<$index; $i++) {
        /* Info */
        $err_line = array_shift($errors);
        $info = $info_records[$err_line];

        /* New Row  */
        $row = array();

        /* Line Row     */
        $row[] = $err_line;
        /* Company Row  */
        $row[] = $info['company'];
        /* Status Row   */
        $row[] = $info['status'];

        $table_not_imported->data[] = $row;
    }//for_index

    if ($total_not_imported > $per_page) {
        /* Empty Row    */
        $row = array();

        /* Line Row     */
        $row[] = '...';
        /* Company Row  */
        $row[] = '';
        /* Status Row   */
        $row[] = '';

        $table_not_imported->data[] = $row;
    }//if_empty_row

    return $table_not_imported;
}//report_generator_import_not_imported

/**
 * @return          html_table
 *
 * @creationDate    18/11/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Creates the header of the table.
 */
function report_generator_import_header_not_imported() {
    /* Table */
    $table = new html_table();
    $table->id                  = "uupreview";
    $table->attributes['class'] = 'generaltable';
    $table->attributes['align'] = 'center';

    /* Header */
    $table->head                = array(get_string('csv_line','report_Generator'),
                                        get_string('company','report_Generator'),
                                        get_string('status','report_Generator'));

    return $table;
}//report_generator_import_header_not_imported


/**
 * @param       $level.         Hierarchy level of company.
 * @param   int $parent_id      Company´s parent.
 * @return      array           Companies list.
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get a list of all the companies are connected a specific level.
 */
function report_generator_get_level_list($level, $parent_id = 0) {
    global $DB;

    /* Variables */
    $levels = array();

    /* Research Criteria */
    $params = array();
    $params['level']    = $level;

    /* SQL Instruction   */
    $sql_Select = " SELECT     DISTINCT rcd.id,
                               rcd.name
                    FROM       {report_gen_companydata} rcd ";
    /* Join */
    $sql_Join = " ";
    if ($level > 1) {
           $sql_Join = " JOIN  {report_gen_company_relation} rcr ON    rcr.companyid = rcd.id
                                                                 AND   rcr.parentid  IN ($parent_id) ";
    }//if_level

    $sql_Where = " WHERE rcd.hierarchylevel = :level ";
    $sql_Order = " ORDER BY rcd.name ASC ";

    /* SQL */
    $sql = $sql_Select . $sql_Join . $sql_Where . $sql_Order;

    $levels[0] = get_string('select_level_list','report_generator');
    if ($rdo = $DB->get_records_sql($sql,$params)) {
        foreach ($rdo as $field) {
            $levels[$field->id] = $field->name;
        }//foreach
    }//if_rdo

    return $levels;
}//report_generator_get_level_list

/**
 * @param           $level          Company Level
 * @param           $list_parent    Parent List
 * @return          array           Companies List
 *
 * @creationDate    18/09/2012
 * @autho           eFaktor     (fbv)
 *
 * Description
 * Return a list of all companies that belong to a specific level and they are under a specific company.
 */
function report_generator_get_company_level($level,$list_parent){
    global $DB;

    $company_list = array();

    /* SQL Instruction */
    $sql = " SELECT     DISTINCT rcd.id,
                        rcd.name
             FROM       {report_gen_companydata} rcd
                JOIN    {report_gen_company_relation} rcr ON    rcr.companyid = rcd.id
                                                          AND   rcr.parentid  IN ({$list_parent})
             WHERE      rcd.hierarchylevel = :level
             ORDER BY   rcd.name ASC ";

    /* Search Criteria */
    $params = array();
    $params['level'] = $level;

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
}//report_generator_get_company_last_level

/**
 * @param       $parent.        It's the company who has employees.
 * @return      array.          Employees List.
 *
 * @updateDate  13/09/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get a list of all employees who work to a specific company.
 */
function report_generator_get_level_Employee($parent) {
    global $DB;

    $employee_list = array();

    /* SQL Instruction   */
    $sql = " SELECT 	DISTINCT u.id,
                        CONCAT(u.firstname,
                               ' ',
                               u.lastname) name
             FROM		{user} 			  u
                JOIN	{user_info_data}  uid	ON 	u.id 		 	= uid.userid
                                                AND uid.data 	 	= :parent
                JOIN	{user_info_field} uif	ON	uid.fieldid 	= uif.id
                                                AND uif.datatype 	= :dtotype
             ORDER BY	u.lastname ASC ";

    /* Research Criteria */
    $params = array();
    $params['parent']   = $parent;
    $params['dtotype']  = 'rgcompany';

    /* Execute */
    if ($rdo = $DB->get_records_sql($sql,$params)) {
        foreach ($rdo as $field) {
            $employee_list[$field->id] = $field->name;
        }//for
    }

    return $employee_list;
}//report_generator_get_level_Employee

/**
 * @param       array   $data.      Form data.
 * @return      array               Action and level.
 *
 * @updateDate  06/09/2012.
 * @author      eFaktor     (fbv)
 *
 * Description
 * Return the action that the user want to carry out and the level.
 */
function report_generator_get_action_and_level($data = array()) {
    $action = null;
    $level = 0;

    if ($data) {
        foreach ($data as $key => $value) {
                if (strpos($key, 'submitbutton') !== false) {
                    $action = 'submit';
                    $level = -1;
                } else if (strpos($key, 'btn-') !== false) {
                    $action = substr($key, 4, -1);
                    $level = (int)substr($key, -1);
                }
        }//for
    }//if_data

    return array($action, $level);
}//report_generator_get_action_and_level

/**
 * @param           $job_role
 * @return          bool
 *
 * @creationDate    08/01/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return if the job role already exists.
 */
function report_generator_exists_jobrole($job_role) {
    global $DB;

    /* SQL Instruction */
    $sql = " SELECT   id
             FROM     {report_gen_jobrole}
             WHERE    name = :job_role ";

    /* Search Criteria */
    $params = array();
    $params['job_role'] = $job_role;

    /* Execute */
    if ($rdo = $DB->get_records_sql($sql,$params)) {
        return true;
    }else {
        return false;
    }
}//report_generator_exists_jobrole



/**
 * @param       $level.       Hierarchy level of company.
 * @param       $parent       Company's parent identity.
 * @return      mixed         Company's name
 *
 * @updateDate  10/09/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get the company's name
 */
function report_generator_get_parent_name($level, $parent) {
    global $DB;

    /* SQL Instruction   */
    $sql = " SELECT     name
             FROM       {report_gen_companydata}
             WHERE      id = :parent
                AND     hierarchylevel = :level ";

    /* Research Criteria */
    $params = array();
    $params['level']    = $level;
    $params['parent']   = $parent;

    /* Execute */
    if ($rdo = $DB->get_record_sql($sql,$params)) {
        return $rdo->name;
    }//if_rdo
}//report_generator_get_parent_name

/**
 * @param           $company
 * @return          mixed
 *
 * @creationDate    08/01/2013
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the name of the company.
 */
function report_generator_get_company_name($company) {
    global $DB;

    /* SQL Instruction   */
    $sql = " SELECT     name
             FROM       {report_gen_companydata}
             WHERE      id = :company ";

    /* Research Criteria */
    $params = array();
    $params['company']   = $company;

    /* Execute */
    if ($rdo = $DB->get_record_sql($sql,$params)) {
        return $rdo->name;
    }else {
        return null;
    }//if_rdo
}//report_generator_get_company_name

/**
 * @param           $company_id
 * @return          mixed|null
 * @throws          Exception
 *
 * @creationDate    02/09/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all details of the company
 */
function report_generator_getCompany_Detail($company_id) {
    /* Variables    */
    global $DB;

    try {
        /* Execute  */
        $rdo = $DB->get_record('report_gen_companydata',array('id' => $company_id),'id,name,idcounty,idmuni');
        if ($rdo) {
            return $rdo;
        }else {
            return null;
        }//if_rdo
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_generator_getCompany_Detail

/**
 * @param       null $list
 * @return      array
 *
 * @updateDate  14/09/2012
 * @author      eFaktor
 *
 * Description
 * Return a list of all job roles available
 */
function report_generator_get_job_role_list($list = null){
    global $DB;

    $job_role_list = array();

    /* SQL Instruction */
    $sql_Select = " SELECT     id,
                               name
                    FROM       {report_gen_jobrole} ";

    $sql_Where = '';
    if ($list) {
        $sql_Where .= " WHERE id IN ({$list}) ";
    }
    $sql_Order = " ORDER BY name ASC ";

    $sql = $sql_Select . $sql_Where . $sql_Order;
    /* Execute */
    if ($rdo = $DB->get_records_sql($sql)) {
        foreach ($rdo as $field) {
            $job_role_list[$field->id] = $field->name;
        }
    }//if_execute

    return $job_role_list;
}//report_generator_get_job_role_list

/**
 * @return      array|bool      List of all job roles and their outcomes connected with them.
 *
 * @updateDate  12/09/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get a list of all job roles and their outcomes connected with them.
 */
function report_generator_get_jobrole_list_with_rel_outcomes(){
    global $DB;

    $job_roles = array();
    /* SQL Instruction */
    $sql = " SELECT		jr.id,
                        jr.name,
                        oc.outcomename outcome_name
             FROM  		{report_gen_jobrole} 			jr
                LEFT JOIN (SELECT     GROUP_CONCAT(go.fullname
                                                   ORDER BY go.fullname ASC
                                                   SEPARATOR ', '
                                                   ) outcomename,
                                      ojrel.jobroleid
                           FROM     {report_gen_outcome_jobrole} ojrel
                              JOIN  {grade_outcomes} go
                                ON  ojrel.outcomeid = go.id
                           GROUP BY ojrel.jobroleid
                          ) oc
                      ON jr.id = oc.jobroleid
             ORDER BY	jr.name ASC ";

    /* Execute */
    if ($rdo = $DB->get_records_sql($sql)) {
        foreach ($rdo as $field) {
            $job_roles[$field->id] = $field;
        }//for_rdo
        return $job_roles;
    }else {
        return false;
    }
}//report_generator_get_jobrole_list_with_rel_outcomes


/**
 * @return      array|bool.     List of all outcomes and their job roles connected with them.
 *
 * @updateDate  12/09/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get a list of all outcomes and their job roles connected with them.
 */
function report_generator_get_outcome_list_with_rel_roles() {
    global $DB;

    $outcome_list = array();

    /* SQL Instruction */
    $sql = " SELECT       go.id,
                          go.fullname,
                          jr.jobrolename jobroles,
                          oex.id expirationid,
                          oex.expirationperiod
             FROM         {grade_outcomes}  go
                LEFT JOIN (SELECT   GROUP_CONCAT(job.name
                                                 ORDER BY job.name ASC
                                                 SEPARATOR ', ') jobrolename,
                                    ojrel.outcomeid
                           FROM     {report_gen_outcome_jobrole} ojrel
                              JOIN  {report_gen_jobrole}         job
                                ON  ojrel.jobroleid = job.id
                           GROUP BY ojrel.outcomeid
                          ) jr
                     ON go.id = jr.outcomeid
                LEFT JOIN {report_gen_outcome_exp} oex
                     ON   go.id = oex.outcomeid
             WHERE    go.courseid IS NULL
                OR    go.courseid = 0
             ORDER BY go.fullname ASC ";

    /* Execute */
    if ($rdo = $DB->get_records_sql($sql)) {
        foreach ($rdo as $field) {
            $outcome_list[$field->id] = $field;
        }//for
        return $outcome_list;
    }else {
        return false;
    }//if_else
}//report_generator_get_outcome_list_with_rel_roles

/**
 * @param           $job_role_id.       Job role Identity.
 * @return          array|bool          Outcome List
 *
 * @updateDate      12/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get a list of all outcomes available and which of them are connected with a specific job role.
 */
function report_generator_get_outcome_list_with_selected($job_role_id){
    global $DB;

    $out_job_roles  = array();
    $out_selected   = array();

    /* SQL Instruction */
    $sql = " SELECT 	   	go.id,
                            go.fullname,
                            ojr.outcomeid
             FROM	  	   	{grade_outcomes} 				go
                LEFT JOIN	{report_gen_outcome_jobrole}	ojr ON 	ojr.outcomeid = go.id
                                                                AND	ojr.jobroleid = :jobrole
             ORDER BY		go.fullname ASC ";

    /* Params  */
    $params = array();
    $params['jobrole'] = $job_role_id;

    /* Execute */
    if ($rdo = $DB->get_records_sql($sql,$params)) {
        foreach ($rdo as $field) {
            $out_job_roles[$field->id] = $field->fullname;
            if ($field->outcomeid) {
                $out_selected[] = $field->id;
            }//if_selected
        }//for
        return array($out_job_roles,$out_selected);
    }else {
        return false;
    }
}//report_generator_get_outcome_list_with_selected

/**
 * @param           $outcome_id.    Outcome Identity.
 * @return          array|bool      Job role list.
 *
 * @updateDate      12/09/2012
 * @author          eFaktor (fbv)
 *
 * Description
 * Get a list of all job roles available and which of them are connected with a specific outcome.
 */
function report_generator_get_role_list_with_selected($outcome_id) {
    global $DB;

    $job_roles_list = array();
    $roles_selected = array();

    /* SQL Instruction */
    $sql = " SELECT        	jr.id,
                            jr.name,
                            ojr.jobroleid
             FROM          	{report_gen_jobrole}  		  jr
                LEFT JOIN	{report_gen_outcome_jobrole}  ojr   ON 	ojr.jobroleid = jr.id
                                                                AND	ojr.outcomeid = :outcome
             ORDER BY jr.name ASC ";

    /* Params  */
    $params = array();
    $params['outcome'] = $outcome_id;

    /* Execute */
    if ($rdo = $DB->get_records_sql($sql,$params)) {
        foreach ($rdo as $field) {
            $job_roles_list[$field->id] = $field->name;
            if ($field->jobroleid) {
                $roles_selected[] = $field->id;
            }
        }//for
        return array($job_roles_list,$roles_selected);
    }else {
        return false;
    }

}//report_generator_get_role_list_with_selected

/**
 * @param           $job_role.          Job role data.
 * @param           $outcome_list       Outcomes are connected with job role.
 * @return          bool
 *
 * @updateDate      13/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Update job role data
 */
function report_generator_update_job_role_out($job_role,$outcome_list) {
    global $DB;

    if ($DB->update_record('report_gen_jobrole',$job_role)) {
        /* If it has outcomes selected
            First   --> Delete all relations
            Second  --> Create new relations */
        $DB->delete_records_select('report_gen_outcome_jobrole','jobroleid='.$job_role->id);

        $outcome_rel = new stdClass();
        $outcome_rel->modified = $job_role->modified;
        $outcome_rel->jobroleid = $job_role->id;
        $url = new moodle_url('/report/generator/job_role/edit_job_role.php',array('id'=>$job_role->id));

        if ($outcome_list) {
            foreach ($outcome_list as $outcome) {
                $outcome_rel->outcomeid = $outcome;
                if (!$DB->insert_record('report_gen_outcome_jobrole',$outcome_rel)) {
                    print_error('error_updating_job_role', 'report_generator', $url);
                }
            }//for_select_outcomes
        }//if_outcomelist

        return true;
    }else {
        return false;
    }
}//report_generator_update_job_role_out

/**
 * @param           $outcome        Outcome data.
 * @param           $role_list      Job roles are connected with outcome.
 * @return          bool
 *
 * @updateDate      14/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Update outcome data
 */
function report_generator_update_outcome_role($outcome,$role_list){
    global $DB;

    if ($DB->update_record('report_gen_outcome_exp',$outcome)) {
        /* First --> Clean old relations */
        $DB->delete_records_select('report_gen_outcome_jobrole','outcomeid='.$outcome->outcomeid);

        /* Second --> Add new relations */
        $job_role_sel = new stdClass();
        $job_role_sel->modified = $outcome->modified;
        $job_role_sel->outcomeid = $outcome->outcomeid;

        $url = new moodle_url('/report/generator/outcome/edit_outcome.php');

        foreach ($role_list as $rol) {
            $job_role_sel->jobroleid = $rol;
            if (!$DB->insert_record('report_gen_outcome_jobrole',$job_role_sel)) {
                print_error('error_updating_outcome_job_role', 'report_generator', $url);
            }
        }//for

        return true;
    }else {
        return false;
    }
}//report_generator_update_outcome_role

/**
 * @param           $job_role           Job role data.
 * @param           $outcome_list       Outcomes are connected with job role.
 *
 * @updateDate      13/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Insert a new job role into database.
 */
function report_generator_insert_job_role_out($job_role,$outcome_list) {
    global $DB;

    $url = new moodle_url('/report/generator/job_role/edit_job_role.php');
    if ($job_role->id = $DB->insert_record('report_gen_jobrole',$job_role)) {
        /* Create all relations */
        $outcome_rel = new stdClass();
        $outcome_rel->modified = $job_role->modified;
        $outcome_rel->jobroleid = $job_role->id;
        $url = new moodle_url('/report/generator/job_role/edit_job_role.php',array('id'=>$job_role->id));

        if ($outcome_list) {
            foreach ($outcome_list as $outcome) {
                $outcome_rel->outcomeid = $outcome;
                if (!$DB->insert_record('report_gen_outcome_jobrole',$outcome_rel)) {
                    print_error('error_insert_job_role', 'report_generator', $url);
                }
            }//for_select_outcomes
        }//if_outcome_list
    }else {
        print_error('error_insert_job_role', 'report_generator', $url);
    }//if-else
}//report_generator_insert_job_role_out

/**
 * @param           $outcome        Outcome data.
 * @param           $role_list      Job role are connected with outcome.
 *
 * @updateDate      14/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Insert a new outcome into database.
 */
function report_generator_insert_outcome_role($outcome,$role_list){
    global $DB;

    $url = new moodle_url('/report/generator/outcome/edit_outcome.php');
    if ($outcome->id = $DB->insert_record('report_gen_outcome_exp',$outcome)) {
        $job_role_sel = new stdClass();
        $job_role_sel->modified = $outcome->modified;
        $job_role_sel->outcomeid = $outcome->outcomeid;

        /* First --> Clean old relations */
        $DB->delete_records_select('report_gen_outcome_jobrole','outcomeid='.$outcome->outcomeid);
        /* Second --> Add new relations. */
        foreach ($role_list as $rol) {
            $job_role_sel->jobroleid = $rol;
            if (!$DB->insert_record('report_gen_outcome_jobrole',$job_role_sel)) {
                print_error('error_updating_outcome_job_role', 'report_generator', $url);
            }
        }//for
    }else {
        print_error('error_updating_outcome_job_role', 'report_generator', $url);
    }
}//report_generator_insert_outcome_role

/**
 * @param               $job_role_id        Job role identity.
 * @param       string  $field              Type of company
 * @return              int                 Number of users
 *
 * @updateDate          10/09/2012
 * @author              eFaktor     (fbv)
 *
 * Description
 * Return the number of users that are connected with a specific job role.
 */
function report_generator_count_connected_users($job_role_id, $field = REPORT_GENERATOR_COMPANY_FIELD) {
    global $DB;

    $count = 0;
    /* SQL Instruction   */
    $sql = " SELECT 	COUNT(DISTINCT uid.id) count
             FROM		{user_info_data} 	uid
                JOIN	{user_info_field} 	uif ON uid.fieldid = uif.id
             WHERE 		uid.data = :job_role_id
                AND     uif.datatype = :field ";
    /* Research Criteria */
    $params = array();
    $params['job_role_id'] = $job_role_id;
    $params['field'] = $field;
    /* Execute */
    if ($rdo = $DB->get_record_sql($sql,$params)) {
        $count = $rdo->count;
    }
    return $count;
}//report_generator_count_connected_users

/**
 * @param       $job_role_id        Job role identity.
 *
 * @updateDate  12/09/2012
 * @author      eFaktor (fbv)
 *
 * Description
 * Delete the job role from database.
 */
function report_generator_delete_job_role_out($job_role_id){
    global $DB;

    $url = new moodle_url('/report/generator/job_role/job_role.php');
    if ($DB->delete_records('report_gen_jobrole',array('id'=>$job_role_id))) {
        /* Remove all outcomes connected */
        $DB->delete_records_select('report_gen_outcome_jobrole','jobroleid='.$job_role_id);
    }else {
        print_error('error_deleting_job_role', 'report_generator', $url);
    }//if_else

    /* Add log */
    add_to_log(SITEID, 'report_gen_jobrole', 'delete', "delete_job_role.php?id=$job_role_id");
}//report_generator_delete_job_role_out


/**
 * @param           $job_role_id        Job role identity
 * @return          bool
 *
 * @creationDate    12/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return job role's name.
 */
function report_generator_get_job_role_name($job_role_id) {
    global $DB;

    if ($rdo = $DB->get_record('report_gen_jobrole',array('id'=>$job_role_id))) {
        return $rdo->name;
    }else {
        return false;
    }
}//report_generator_get_job_role_name

/**
 * @param           $job_role_id
 * @return          mixed|null
 * @throws          Exception
 *
 * @creationDate    21/08/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the information connected with the Job Role
 */
function report_generator_getJobRole_Detail($job_role_id) {
    /* Variables    */
    global $DB;

    try {
        /* Search Criteria  */
        $params = array();
        $params['jr_id'] = $job_role_id;

        /* SQL Instruction  */
        $sql = " SELECT			jr.id,
                                jr.name,
                                m.idcounty,
                                m.idmuni
                 FROM			{report_gen_jobrole}	jr
                    LEFT JOIN	{municipality}		    m ON m.idmuni = jr.idmuni
                 WHERE          jr.id = :jr_id";

        /* Execute  */
        $rdo = $DB->get_record_sql($sql,$params);
        if ($rdo) {
            return $rdo;
        }else {
            return null;
        }//if_rdo
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_generator_getJobRole_Detail


/**
 * @param           $exp_id         Outcome expedition identity
 * @return          bool
 *
 * @creationDate    14/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return the expiration period connected with a specific outcome.
 */
function report_generator_get_outcome_expiration_period($exp_id) {
    global $DB;

    if ($rdo = $DB->get_record('report_gen_outcome_exp',array('id'=>$exp_id))) {
        return $rdo->expirationperiod;
    }else {
        return false;
    }
}//report_generator_get_outcome_expiration_period


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
 * @return array
 *
 * Description
 * Get a list with all outcomes available.
 */
function report_generator_get_outcome_list(){
    global $DB;

    $outcome_list = array();

    /* SQL Instruction */
    $sql = " SELECT     id,
                        fullname
             FROM       {grade_outcomes}
             ORDER BY   fullname ASC ";

    /* Execute */
    if ($rdo = $DB->get_records_sql($sql)) {
        $outcome_list[0] = get_string('select') . '...';
        foreach ($rdo as $field) {
            $outcome_list[$field->id] = $field->fullname;
        }
    }

    return $outcome_list;
}//report_generator_get_outcome_list

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
 * @param           $data_form
 * @return          string
 *
 * @updateDate      21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the outcome report
 */
function report_generator_display_outcome_report($data_form) {
    global $DB;

    /* Variables */
    $return_url = new moodle_url('/report/generator/outcome_report/outcome_report_level.php',array('rpt' => $data_form['rpt']));
    $return     = '<a href="'.$return_url .'">'. get_string('outcome_return_to_selection','report_generator') .'</a>';
    $no_data    = get_string('no_data', 'report_generator');
    $no_data   .=  '<br/>' . $return;
    $data       = array();
    $out        = '';

    /* All job roles  selected */
    $outcome = $data_form[REPORT_GENERATOR_OUTCOME_LIST];
    if (!empty($data_form[REPORT_GENERATOR_JOB_ROLE_LIST])) {
        $list = join(',',$data_form[REPORT_GENERATOR_JOB_ROLE_LIST]);
        $job_role_list = report_generator_outcome_job_role_list($outcome,$list);
    }else {
        $job_role_list = report_generator_outcome_job_role_list($outcome);
    }//if_else

    if (!$job_role_list) {
        return $no_data;
    }//if_job_role_list

    /* Get Completed Time - Course  */
    $completed_time = $data_form[REPORT_GENERATOR_COMPLETED_LIST];
    $completed_time = report_generator_get_completed_date_timestamp($completed_time,true);

    /* Get companies selected */
    $outcome_report_info = report_generator_get_companies_to_report($data_form);
    if (!$outcome_report_info) {
        return $no_data;
    }//if_empty_company_list

    /* Get information about how many users have finished the course or not by Job Role... */
    report_generator_get_outcome_report_info($outcome,$outcome_report_info,$job_role_list,$completed_time);
    if (!$outcome_report_info) {
        return $no_data;
    }//if_empty_outcome_report

    /* Outcome Report  Data */
    $data['report_type']            = 'outcome';
    $data['report_level']           = $data_form['rpt'];
    $data['completed_time']         = $completed_time;
    $data['outcome_report_info']    = $outcome_report_info;
    $company_id                     = $data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'1'];
    $data['level_1']                = report_generator_get_company_name($company_id);

    if ($data['report_level'] > 1) {
        $company_id                 = $data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'2'];
        $data['level_2']            = report_generator_get_company_name($company_id);
    }

    $data['outcome']                = $outcome;
    $data['format']                 = $data_form[REPORT_GENERATOR_REPORT_FORMAT_LIST];
    /* Create Report Data */
    switch ($data_form[REPORT_GENERATOR_REPORT_FORMAT_LIST]) {
        case REPORT_GENERATOR_REP_FORMAT_SCREEN:
            $out = '<br/><br/>';
            $out .= report_generator_create_outcome_report_screen_out($data);
            $out .= $return;
            $out .= '<br/><br/>';

            break;

        case REPORT_GENERATOR_REP_FORMAT_PDF:
        case REPORT_GENERATOR_REP_FORMAT_PDF_MAIL:
            $out = report_generator_create_outcome_report_pdf_out($data);
            /* Check if the report has been created and send it.    */
            $return     = '<a href="'.$return_url .'">'. get_string('outcome_return_to_selection','report_generator') .'</a>';
            if ($out){
                $no_data =  $out . '<br/>' . $return;
                return $no_data;
            }//_if_out

            break;
        case REPORT_GENERATOR_REP_FORMAT_CSV:
            $out = report_generator_create_outcome_report_csv_out($data);
            break;
    }//switch_report

    return $out;
}//report_generator_display_outcome_report

/**
 * @param           $outcome_id         Outcome identity
 * @param           $company_list       Companies selected
 * @param           $job_role_list      Job roles selected
 * @param           $completed_time     Completed time
 * @throws          moodle_exception
 *
 * @creationDate    21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all information of the report. How many users have completed or not the course, when they have completed it...
 *
 * Outcome report info - Structure
 *
 *      [id_company]
 *                  --> company_name
 *                  --> total_completed
 *                  --> total_progress
 *                  --> total_before
 *                  --> report_job      Array
 *                      [id_job_role]
 *                              --> job_role    (name)
 *                              --> courses     Array
 *                                     [id_course]
 *                                          --> course_name
 *                                          --> user_completed      Array
 *                                                  [id_user]
 *                                                      --> user_name
 *                                                      --> time_completed
 *                                          --> user_before         Array
 *                                                  [id_user]
 *                                                      --> user_name
 *                                                      --> time_completed
 *                                          --> user_progress       Array
 *                                                  [id_user]
 *                                                      --> user_name
 *                                                      --> time_completed
 *
 */
function report_generator_get_outcome_report_info($outcome_id,&$company_list,&$job_role_list,$completed_time)  {
    global $DB;

    /* Company List */
    $companies_keys = array_keys($company_list);
    $companies = join(',',$companies_keys);

    /* Outcome Expiration */
    $out_expiration = report_generator_get_outcome_expiration($outcome_id);

    try {
        /* SQL Instruction */
        $sql_Sel = " SELECT		DISTINCT concat(u.id, '_', uid_cd.data,'_',e.courseid) as uc_id,
                                uid_cd.data as company,
                                e.courseid,
                                u.id,
                                CONCAT(u.firstname, ' ', u.lastname) as user_name
                     FROM 		{user}					u
                        JOIN	{user_enrolments}		ue			ON		ue.userid 	  		= u.id
                        JOIN	{enrol}					e			ON		e.id		  		= ue.enrolid
                        JOIN	{user_info_data}		uid_cd		ON		uid_cd.userid 		= ue.userid
                                                                    AND		uid_cd.data		    IN (". $companies . ")
                        JOIN	{user_info_field}		uif_cd  	ON 		uif_cd.id	    	= uid_cd.fieldid
                                                                    AND		uif_cd.datatype	    = 'rgcompany'
                        JOIN	{user_info_data}		uid_rg		ON		uid_rg.userid   	= uid_cd.userid
                        JOIN	{user_info_field}		uif_rg		ON		uif_rg.id			= uid_rg.fieldid
                                                                    AND 	uif_rg.datatype 	= 'rgjobrole'
                     WHERE      u.deleted = 0 ";

        /* Execute  */
        foreach ($job_role_list as $job_id=>$job_role) {
            /* Courses List */
            $courses_keys = array_keys($job_role->courses);
            $courses_list = join(',',$courses_keys);

            $sql = $sql_Sel . " AND 		uid_rg.data like '%{$job_id}%'
                                    AND		e.courseid IN (" . $courses_list . ")
                                ORDER BY	company, user_name ASC ";

            $rdo_users = $DB->get_records_sql($sql);
            if ($rdo_users) {
                foreach ($rdo_users as $user) {
                    /* User Info */
                    $user_info = new stdClass();
                    $user_info->user_name       = $user->user_name;
                    $time_completed             = report_generator_get_course_completed_info_user($user->courseid,$user->id);
                    $user_info->time_completed  = $time_completed;

                    /* Course Info */
                    $course_info = $job_role->courses[$user->courseid];
                    if ($time_completed) {
                        if ($time_completed + $out_expiration < $completed_time) {
                            $course_info->users_before[$user->id] = $user_info;
                            $company_list[$user->company]->total_before += 1;
                        }else {
                            $course_info->users_completed[$user->id] = $user_info;
                            $company_list[$user->company]->total_completed += 1;
                        }
                    }else {
                        $course_info->users_progress[$user->id] = $user_info;
                        $company_list[$user->company]->total_progress += 1;
                    }//if_time_completed

                    $job_role->courses[$user->courseid]                 = $course_info;
                    $company_list[$user->company]->report_job[$job_id]  = $job_role;
                }//for_user
            }//if_rdo_urses
        }//for_job_role
    }catch (Exception $ex){
        throw new moodle_exception($ex->getMessage());
    }//try_catch
}//report_generator_get_outcome_report_info

/**
 * @param           $outcome_id
 * @param           $job_role_id
 * @return          array|bool
 * @throws          moodle_exception
 *
 * @creationDate    21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the list of all courses are connected with an outcome and job role
 */
function report_generator_get_courses_list_by_outcome_job_role($outcome_id,$job_role_id) {
    global $DB;

    /* Courses */
    $course_list    = array();

    try {
        /* Search Criteria  */
        $params = array();
        $params['outcome']  = $outcome_id;
        $params['job_role'] = $job_role_id;

        /* SQL Instruction  */
        $sql = " SELECT			co.id,
                                co.fullname
                 FROM			{course}						co
                    JOIN		{grade_outcomes_courses}		goc		ON		goc.courseid	=	co.id
                                                                        AND		goc.outcomeid 	= 	:outcome
                    JOIN		{report_gen_outcome_jobrole}	jro		ON		jro.outcomeid	= 	goc.outcomeid
                                                                        AND		jro.jobroleid	= 	:job_role
                 ORDER BY	fullname ASC ";

        /* Execute  */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            foreach ($rdo as $course) {
                $course_info = new stdClass();

                $course_info->course_name       = $course->fullname;
                $course_info->users_completed   = array();
                $course_info->users_progress    = array();
                $course_info->users_before      = array();

                $course_list[$course->id] = $course_info;
            }//for_rdo
        }//if_rdo

        return $course_list;
    }catch(Exception $ex){
        throw new moodle_exception($ex->getMessage());
    }//try_catch
}//report_generator_get_courses_list_by_outcome_job_role

/**
 * @param           $outcome_id
 * @return          mixed
 *
 * @creationDate    21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return the expiration period of an outcome
 */
function report_generator_get_outcome_expiration($outcome_id) {
    global $DB;

    /* Execute  */
    if ($rdo = $DB->get_record('report_gen_outcome_exp',array('outcomeid'=>$outcome_id))) {
        return $rdo->expirationperiod;
    }else {
        return 0;
    }//if_rdo
}//report_generator_get_outcome_expiration

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
 * @param           $outcome_id
 * @param           null $list
 * @return          array
 * @throws          moodle_exception
 *
 * @updateDate      21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return all job roles connected with a specific outcome.
 */
function report_generator_outcome_job_role_list($outcome_id, $list = null) {
    global $DB;

    /* Job Roles & Course */
    $job_role_list = array();

    try {
        /* Search Criteria  */
        $params = array();
        $params['outcome_id'] = $outcome_id;

        /* SQL Instruction  */
        $sql = " SELECT		jr.id,
                            jr.name
                 FROM		{report_gen_jobrole} 			jr
                     JOIN	{report_gen_outcome_jobrole}	jro		ON  	jro.jobroleid 	= jr.id
                                                                    AND		jro.outcomeid	= :outcome_id
                     ";
        if ($list) {
            $sql = $sql . "WHERE		jr.id IN ({$list}) ";
        }
        $sql = $sql . " ORDER BY 	jr.name ASC ";

        /* Execute  */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            foreach ($rdo as $job_role) {
                $report_info = new stdClass();
                $report_info->job_role          = $job_role->name;
                /* Get the courses connected with   */
                $report_info->courses   = report_generator_get_courses_list_by_outcome_job_role($outcome_id,$job_role->id);

                $job_role_list[$job_role->id] = $report_info;
            }//
        }//if_rdo

        return $job_role_list;
    }catch (Exception $ex) {
        throw new moodle_exception($ex->getMessage());
    }
}//report_generator_outcome_job_role_list

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
 * @param           $data       Information of the report
 * @return          string
 *
 * @updateDate      21/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the outcome report - Screen Format
 */
function report_generator_create_outcome_report_screen_out($data){
    global $DB;

    /* Variables    */
    $out                    = '';
    $report_type            = $data['report_type'];
    $report_level           = $data['report_level'];
    $completed_time         = $data['completed_time'];
    $outcome_report_info    = $data['outcome_report_info'];
    $level_1                = $data['level_1'];
    if ($report_level > 1) {
        $level_2            = $data['level_2'];
    }
    $outcome                = $data['outcome'];

    /* Create Report */
    $time = userdate(time(),'%d.%m.%Y', 99, false);
    $out .= '<div id="course-report" class="rg-report">';
    $out .= '<h3>';
    $out .= get_string('date') . ': ' . $time;
    $out .= '</h3>';
    $out .= '</div>';

    /* Get detail outcome */
    $outcome_detail = $DB->get_record('grade_outcomes',array('id'=>$outcome));
    $out .= '<h2>';
    $out .= get_string('outcome', 'report_generator') . ' "' . $outcome_detail->fullname . '"';
    $out .= '</h2>';
    $out .= '<h6>' . format_text($outcome_detail->description) . '</h6>' . '<br/>';

    /* Information about Companies Level */
    $out .= '<ul class="level-list unlist">';
    $out .= '<li><h2>';
    $out .= get_string('company_structure_level', 'report_generator', 1) . ': ' . $level_1;
    $out .= '</h2></li>';
    if ($report_level > 1) {
        $out .= '<li><h2>';
        $out .= get_string('company_structure_level', 'report_generator', 2) . ': ' . $level_2;
        $out .= '</h2></li>';
    }//if_level_2
    $out .= '</ul>';

    $out .= report_generator_print_report_tables($report_type,$report_level,$outcome_report_info,$completed_time);

    return $out;
}//report_generator_create_outcome_report_screen_out


/**
 * @param       $data           Information of the report
 * @return      bool|string
 *
 * @updateDate  25/09/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get outcome report - PDF Format.
 */
function report_generator_create_outcome_report_pdf_out($data) {
    global $DB;

    /* Variables    */
    $out_pdf                = array();
    $report_type            = $data['report_type'];
    $report_level           = $data['report_level'];
    $completed_time         = $data['completed_time'];
    $outcome_report_info    = $data['outcome_report_info'];
    $level_1                = $data['level_1'];
    if ($report_level > 1) {
        $level_2                = $data['level_2'];
    }
    $outcome                = $data['outcome'];
    $format                 = $data['format'];

    $out_pdf['report_date'] = userdate(time(),'%d.%m.%Y', 99, false);
    /* Get detail outcome */
    $outcome_detail = $DB->get_record('grade_outcomes',array('id'=>$outcome));
    $out_pdf['report_name'] = get_string('outcome', 'report_generator') . ' "' . $outcome_detail->fullname . '"';
    $out_pdf['summary'] = strip_tags($outcome_detail->description);

    /* Information about Companies Level */
    $out_pdf['level_1'] = get_string('company_structure_level', 'report_generator', 1) . ': ' . $level_1;
    if ($report_level > 1) {
        $out_pdf['level_2'] = get_string('company_structure_level', 'report_generator', 2) . ': ' . $level_2;
    }//if_level_2

    $out_pdf['report_format']   = $format;
    $out_pdf['report_type']     = $report_type;
    $out_pdf['report_level']    = $report_level;

    report_generator_print_report_pdf_tables($report_type,$report_level,$outcome_report_info,$completed_time,$out_pdf);
    return report_generator_prepare_send_pdf($out_pdf);
}//report_generator_create_outcome_pdf_out

/**
 * @param       $data           Information of the report
 * @return      bool|string
 *
 * @updateDate  01/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get outcome report - CSV Format.
 */
function report_generator_create_outcome_report_csv_out($data){
    /* Variables    */
    $report_type            = $data['report_type'];
    $report_level           = $data['report_level'];
    $level_1                = $data['level_1'];
    if ($report_level > 1) {
        $level_2            = $data['level_2'];
    }else {
        $level_2            = 0;
    }
    $outcome                = $data['outcome'];
    $outcome_report_info    = $data['outcome_report_info'];
    $completed_time         = $data['completed_time'];
    /* Data of the report */
    $out_cvs = array();

    $fields_cvs = report_generator_get_fields_report_outcome_cvs($report_level);
    /* Create Empty Row */
    $cols_cvs   = count($fields_cvs);
    $empty_row  = report_generator_create_empty_row_cvs($cols_cvs);

    /* Create Header    */
    $out_cvs    = report_generator_get_header_report_outcome_cvs($empty_row,$report_level,$outcome,$level_1,$level_2);
    /* Fill the file    */
    report_generator_print_report_outcome_csv_tables($empty_row,$report_type,$report_level,$outcome_report_info,$completed_time,$out_cvs);
    /* Download the CVS report  */
    report_generator_download_report_cvs($out_cvs,$report_type);

    return true;
}//report_generator_create_outcome_report_csv_out

/**
 * @param       $empty_row
 * @param       $report_level
 * @param       $outcome
 * @param       $level_1
 * @param       $level_2
 * @return      array
 *
 * @updateDate  01/10/2012
 * @author      eFaktor (fbv)
 *
 * Description
 * Get the header of the csv outcome file.
 */
function report_generator_get_header_report_outcome_cvs($empty_row,$report_level,$outcome,$level_1, $level_2) {
    global $DB;

    /* Data of the report   */
    $out = array();

    $row    = $empty_row;
    $row[0] = userdate(time(),'%d.%m.%Y', 99, false);
    $out[]  = $row;
    /* Outcome Detail   */
    $outcome_detail = $DB->get_record('grade_outcomes',array('id'=>$outcome));
    /* Outcome Name     */
    $row    = $empty_row;
    $row[0] = get_string('outcome', 'report_generator') . ' "' . $outcome_detail->fullname . '"';
    $out[]  = $row;
    /* Outcome Description  */
    $row    = $empty_row;
    $row[0] = strip_tags($outcome_detail->description);
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
}//report_generator_get_header_report_outcome_cvs

/**
 * @param       $report_level
 * @return      array
 *
 * @updateDate  01/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Get the columns of the csv outcome file.
 */
function report_generator_get_fields_report_outcome_cvs($report_level) {
    /* Variables    */
    $str_job_role       = get_string('job_role', 'report_generator');
    $str_count          = get_string('count', 'report_generator');
    $str_company_name   = get_string('company', 'report_generator');
    $str_course         = get_string('course');
    $str_username       = get_string('name');
    $str_cert_date      = get_string('cert_date', 'report_generator');

    /* Fields of the CVS file   */
    $fields_cvs = array();

    switch ($report_level) {
        case 1;case 2:
            $fields_cvs = array($str_company_name,
                                $str_job_role,
                                $str_course,
                                $str_count);

            break;
        case 3:
            $fields_cvs = array($str_company_name,
                                $str_job_role,
                                $str_course,
                                $str_username,
                                $str_cert_date,
                                $str_count);
            break;
    }//switch_report_level

    return $fields_cvs;
}//report_generator_get_fields_report_cvs

/**
 * @param       $empty_row
 * @param       $report_type
 * @param       $report_level
 * @param       $company_report_info
 * @param       $completed_time
 * @param       $out_cvs
 *
 * @updateDate  01/10/2012
 * @author      eFaktor (fbv)
 *
 * Description
 * Get the data for the csv outcome file.
 */
function report_generator_print_report_outcome_csv_tables($empty_row,$report_type,$report_level,$company_report_info,$completed_time,&$out_cvs) {
    /* Variables     */
    $completed_time = userdate($completed_time,'%d.%m.%Y', 99, false);
    $str_completed  = get_string($report_type . '_units_have_completed_since', 'report_generator',$completed_time);
    $str_progress   = get_string($report_type . '_units_in_progress', 'report_generator');
    $str_before     = get_string($report_type . '_units_have_completed_before', 'report_generator',$completed_time);

    /* Create Tables    */
    foreach ($company_report_info as $id=>$company_info) {
        $company    = $company_info->company_name;
        $report_job = $company_info->report_job;

        /* Before       */
        if ($company_info->total_before) {
            $total      = $company_info->total_before;
            $row        = $empty_row;
            $row[0]     = strip_tags($str_before);
            $out_cvs[]  = $row;
            report_generator_get_cvs_outcome_table($empty_row,$report_level,$company,$report_job,'before',$out_cvs);
        }//_before

        /* Progress     */
        if ($company_info->total_progress) {
            $total      = $company_info->total_progress;
            $row        = $empty_row;
            $row[0]     = strip_tags($str_progress);
            $out_cvs[]  = $row;
            report_generator_get_cvs_outcome_table($empty_row,$report_level,$company,$report_job,'progress',$out_cvs);
        }//_progress

        /* Completed    */
        if ($company_info->total_completed) {;
            $total      = $company_info->total_completed;
            $row        = $empty_row;
            $row[0]     = strip_tags($str_completed);
            $out_cvs[]  = $row;
            report_generator_get_cvs_outcome_table($empty_row,$report_level,$company,$report_job,'completed',$out_cvs);
        }//_completed
    }//for_company
}//report_generator_print_report_outcome_csv_tables

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
 * @param           $job_roles      Job roles list
 * @return          html_table
 *
 * @updateDate      12/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Draw a table which contains all job roles available.
 */
function report_generator_table_job_roles($job_roles){
    global $CFG;

    $context      = CONTEXT_SYSTEM::instance();
    $can_edit      = has_capability('report/generator:edit', $context);

    $str_fullname  = get_string('fullname');
    $str_outcomes  = get_string('outcomes_for_job_role', 'report_generator');
    $str_edit      = get_string('edit');

    /* Create Table */
    $table = new html_table();

    $table->head = array($str_fullname, $str_outcomes, $str_edit);
    $table->colclasses = array($str_fullname, $str_outcomes, $str_edit);

    $table->width = "60%";

    foreach ($job_roles as $job_role) {
        global $OUTPUT;

        /* Rows */
        $row = array();
        /* Buttons */
        $buttons = array();

        /* Fullname Col */
        $row[] = $job_role->name;
        /* Outcomes Col */
        $row[] = $job_role->outcome_name;
        /* Edit Col */
        if ($can_edit) {
            /* Edit Button */
            $url_edit = new moodle_url('/report/generator/job_role/edit_job_role.php',array('id'=>$job_role->id));
            $buttons[] = html_writer::link($url_edit,
                                           html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
                                                                               'alt'=>get_string('edit'),
                                                                               'class'=>'iconsmall')),
                                           array('title'=>get_string('edit_this_job_role', 'report_generator')));
            /* Delete Button */
            $url_delete = new moodle_url('/report/generator/job_role/delete_job_role.php',array('id'=>$job_role->id));
            $buttons[] = html_writer::link($url_delete,
                                           html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'),
                                                                               'alt'=>get_string('delete'),
                                                                               'class'=>'iconsmall')),
                                           array('title'=>get_string('delete_this_job_role', 'report_generator')));

            $row[] = implode(' ',$buttons);
        }else {
            $row[] = '';
        }//if_can_edit

        /* Add row */
        $table->data[] = $row;
    }//for_job_roles

    return $table;
}//report_generator_table_jobroles


/**
 * @param           $outcome_list       Outcome list
 * @return          html_table
 *
 * @updateDate      13/09/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Draw a table which contains all outcomes available
 */
function report_generator_table_outcomes($outcome_list) {
    global $CFG;

    $context = CONTEXT_SYSTEM::instance();
    $can_edit = has_capability('report/generator:edit', $context);

    $str_fullname           = get_string('fullname');
    $str_expiration_period  = get_string('expiration_period', 'report_generator');
    $str_job_roles          = get_string('job_roles_for_outcome', 'report_generator');
    $str_edit               = get_string('edit');

    /* Create Table */
    $table = new html_table();

    $table->head = array($str_fullname, $str_expiration_period, $str_job_roles, $str_edit);
    $table->colclasses = array($str_fullname, $str_expiration_period, $str_job_roles, $str_edit);
    $table->width = "60%";

    foreach ($outcome_list as $outcome) {
        global $OUTPUT;

        /* Rows */
        $row = array();
        /* Buttons */
        $buttons = array();

        /* Fullname Column */
        $row[] = $outcome->fullname;
        /* Expiration Period Col */
        $row[] = $outcome->expirationperiod;
        /* Job Roles Col */
        $row[] = $outcome->jobroles;
        /* Edit Col */
        if ($can_edit) {
            /* Edit Button */
            $url_edit = new moodle_url('/report/generator/outcome/edit_outcome.php',array('id'=>$outcome->id,'expid'=>$outcome->expirationid));
            $buttons[] = html_writer::link($url_edit,
                                           html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
                                                                               'alt'=>get_string('edit'),
                                                                               'class'=>'iconsmall')),
                                           array('title'=>get_string('edit')));

            $row[] = implode('',$buttons);
        }else {
            $row[] = '';
        }//if_can_edit

        /* Add Row */
        $table->data[] = $row;
    }//for

    return $table;
}//report_generator_table_outcomes

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
            $out .= report_generator_get_links_report_second_level($tab);
        }else if (has_capability('report/generator:viewlevel1', $site_context)) {
            $out .= report_generator_get_links_report_first_level($tab);
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

    $out = report_generator_get_links_report_second_level($tab);
    $out .= '<li class="last">' . "\n";
    $out .= '<a href="'.$url_level_3 .'">'. get_string('level_report','report_generator',3) .'</a>';
    $out .= '</li>' . "\n";

    return $out;
}//report_generator_get_links_report_third_level










































