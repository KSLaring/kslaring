<?php

/**
 * Report Competence Manager - Course report Level.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/course_report
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  14/09/2012
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( '../locallib.php');
require_once('course_report_level_form.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/gradelib.php');

/* Params */
$report_level   = required_param('rpt', PARAM_INT);
$return_url     = new moodle_url('/report/manager/course_report/course_report.php');

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url('/report/manager/course_report/course_report_level.php');

/* ADD requiere_capibility */
switch ($report_level) {
    case 1:
        if (!has_capability('report/manager:viewlevel1', $site_context)) {
            print_error('nopermissions', 'error', '', 'report/manager:viewlevel1');
        }
        break;
    case 2:
        if (!has_capability('report/manager:viewlevel2', $site_context)) {
            print_error('nopermissions', 'error', '', 'report/manager:viewlevel2');
        }
        break;
    case 3:
        if (!has_capability('report/manager:viewlevel3', $site_context)) {
            print_error('nopermissions', 'error', '', 'report/manager:viewlevel3');
        }
        break;
}//switch

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

/* Show Form */
$url = new moodle_url('/report/manager/course_report/course_report_level.php',array('rpt'=>$report_level));
$form = new manager_course_report_level_form(null,array($report_level));
$out = '';

if ($form->is_cancelled()) {
    setcookie('parentLevelOne',0);
    setcookie('parentLevelTwo',0);
    setcookie('parentLevelTree',0);
    setcookie('courseReport',0);
    setcookie('outcomeReport',0);
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Get Data */
    $data_form = (Array)$data;

    $out = report_manager_display_course_report($data_form);
}//if_else

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

if (!empty($out)) {
    echo $OUTPUT->heading($out);
}else {
    /* Print tabs at the top */
    $current_tab = 'course_report';
    $show_roles = 1;
    require('../tabs.php');

    $form->display();
}//if_else

/* Print Footer */
echo $OUTPUT->footer();
