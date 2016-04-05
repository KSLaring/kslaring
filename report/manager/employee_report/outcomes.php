<?php
/**
 * Report Competence Manager - Employee Report - JS get Outcomes
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/employee_report
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    26/10/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once( '../managerlib.php');
require_once('employeelib.php');

/* PARAMS   */
$levelZero    = required_param('levelZero',PARAM_INT);
$levelOne     = required_param('levelOne',PARAM_INT);
$levelTwo     = required_param('levelTwo',PARAM_INT);
$levelThree   = required_param('levelThree',PARAM_INT);

$json           = array();
$data           = array();
$outcome_lst    = null;
$outcomes       = array();
$info           = null;

$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/report/manager/employee_report/outcomes.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Outcomes    */
if ($levelThree) {
    $outcome_lst    = array();
    $outcome_lst = EmployeeReport::GetOutcomes_EmployeeReport($levelZero,$levelOne,$levelTwo,$levelThree);
}else {
    $outcome_lst    = array();
    $outcome_lst[0] = get_string('select');
}//IF_COOKIE

/* Get Data */
$data               =  array('outcomes' => array());
foreach ($outcome_lst as $id => $outcome) {
    /* Info Company */
    $info            = new stdClass;
    $info->id        = $id;
    $info->name      = $outcome;

    /* Add Company*/
    $outcomes[$info->name] = $info;
}

/* Encode and Send */
$data['outcomes'] = $outcomes;
$json[] = $data;
echo json_encode(array('results' => $json));


