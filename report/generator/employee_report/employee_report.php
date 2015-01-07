<?php

/**
 * Report generator - Employee report.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator/company_report/
 * @copyright   2014 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  21/02/2014
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( '../locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('employee_report_form.php');
require_once( 'employeelib.php');


/* Params */
$url        = new moodle_url('/report/generator/employee_report/employee_report.php');
$return     = new moodle_url('/report/generator/index.php');

require_login();

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_generator','local_tracker'),$return);
$PAGE->navbar->add(get_string('employee_report','report_generator'),$url);

require_capability('report/generator:viewlevel4', $site_context,$USER->id);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* SHOW Form     */
setcookie('parentLevelOne',0);
setcookie('parentLevelTwo',0);
setcookie('parentLevelTree',0);
setcookie('courseReport',0);
setcookie('outcomeReport',0);
setcookie('employeeReport',0);

$form = new generator_employee_report_form(null);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return);
}else if($data = $form->get_data()) {
    /* Get Data */
    $data_form = (Array)$data;

    $company_id = report_generator_getCompanyUser($USER->id);
    $outcome_id = $data_form[REPORT_GENERATOR_OUTCOME_LIST];
    /* Get Expiration Time */
    $options = report_generator_get_completed_list();
    $completed_time = $data_form[REPORT_GENERATOR_COMPLETED_LIST];

    /* Employee Report Info */
    $employee_rpt = report_generator_EmployeeReport_getInfo($company_id,$outcome_id);

    /* Get the report to display    */
    $out  = '<a href="'.$url .'">'. get_string('employee_return_to_selection','report_generator') .'</a></br>';
    $out .= html_writer::start_tag('div',array('class' => 'employee_div'));
        $out .= html_writer::start_tag('div',array('class' => 'expiration'));
        $out .= get_string('expired_next','report_generator') . ': ' . $options[$data_form[REPORT_GENERATOR_COMPLETED_LIST]];
        $out .= html_writer::end_tag('div'); //div_expiration

        $out .= report_generator_EmployeeReport_getTagTitleOutcome($employee_rpt->outcome);
        if ($employee_rpt->courses_id) {
            $courses = explode(',',$employee_rpt->courses);
            $out .= report_generator_EmployeeReport_geContentReport($courses,$employee_rpt->expiration,$employee_rpt->users,$completed_time);
        }else {
            $out .= get_string('no_data', 'report_generator');
        }//if_courses
    $out .= html_writer::end_tag('div');

    $out .= '<a href="'.$url .'">'. get_string('employee_return_to_selection','report_generator') .'</a>';
}//if_else_form

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'employee_report';

if (isset($out)) {
    echo $OUTPUT->heading(get_string('employee_report','report_generator'));
    echo $out;
}else {
    require('../tabs.php');
    $form->display();
}//if_out

/* Print Footer */
echo $OUTPUT->footer();