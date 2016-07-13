<?php
/**
 * Report Competence Manager - Company Structure - JS get Employees
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/copany_structure
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    23/10/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('company_structurelib.php');

/* PARAMS   */
$levelThree     = optional_param('levelThree',0,PARAM_TEXT);
$employees      = optional_param('employees',0,PARAM_TEXT);
$delete         = optional_param('delete',0,PARAM_INT);
$deleteAll      = optional_param('deleteAll',0,PARAM_INT);

$json           = array();
$data           = array();
$infoCompany    = null;

$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/report/manager/company_structure/employees.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Company */
if ($levelThree) {
    $levelThree = str_replace('#',',',$levelThree);
}
/* Get Employees */
if ($employees) {
    $employees = str_replace('#',',',$employees);
}
if ($deleteAll) {
    company_structure::DeleteEmployees($levelThree,$employees,true);
}else if ($delete) {
    company_structure::DeleteEmployees($levelThree,$employees);
}else {
    $employees = company_structure::Get_EmployeeLevel($levelThree);

    /* GEt Employees Info to Send */
    $employeesInfo = array();
    foreach ($employees as $id=>$user) {
        /* Info */
        $info = new stdClass();
        $info->id   = $id;
        $info->name = $user;

        /* Add Employee Info    */
        $employeesInfo[$info->name] = $info;
    }
    /* Get Data */
    $data           =  array('users' => array());
    $data['users']  = $employeesInfo;
}//if_delete

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));