<?php

/**
 * Report Competence Manager - Employee report.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/employee_report/
 * @copyright       2014 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    14/04/2015
 * @author          eFaktor     (fbv)
 *
 * @updateDate      15/06/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Companies connected with my level and/or competence
 *
 */

require_once('../../../config.php');
require_once( '../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('employee_report_form.php');
require_once( 'employeelib.php');


/* Params */
$url                = new moodle_url('/report/manager/employee_report/employee_report.php');
$return             = new moodle_url('/report/manager/index.php');
$my_hierarchy       = null;
$employeeTracker    = null;
$company            = null;
$out                = '';

require_login();

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return);
$PAGE->navbar->add(get_string('employee_report_link','report_manager'),$url);

require_capability('report/manager:viewlevel4', $site_context,$USER->id);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* My Hierarchy */
$my_hierarchy = CompetenceManager::get_MyHierarchyLevel($USER->id,$site_context);

/* Show Form    */
$form = new manager_employee_report_form(null,$my_hierarchy);
if ($form->is_cancelled()) {
    /* Clean Cookies     */
    setcookie('parentLevelZero',0);
    setcookie('parentLevelOne',0);
    setcookie('parentLevelTwo',0);
    setcookie('parentLevelThree',0);
    setcookie('courseReport',0);
    setcookie('outcomeReport',0);

    $_POST = array();
    redirect($return);
}else if($data = $form->get_data()) {
    /* Get Data */
    $data_form = (Array)$data;

    /* Get Company Tracker Info */
    $company        = $data_form[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '3'];

    /* Get Employee Tracker */
    $employeeTracker = EmployeeReport::Get_EmployeeTracker($company,$data_form[REPORT_MANAGER_OUTCOME_LIST ]);
    /* Print Report         */
    $out = EmployeeReport::Print_EmployeeTracker($employeeTracker,$data_form[REPORT_MANAGER_COMPLETED_LIST]);
}//if_form

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'employee_report';

if (!empty($out)) {
    echo $out;
}else {
    require('../tabs.php');

    $form->display();
}//if_out

/* Print Footer */
echo $OUTPUT->footer();