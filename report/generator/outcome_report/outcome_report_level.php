<?php

/**
 * Report generator - Outcome report Level.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator/outcome_report
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  14/09/2012
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( '../locallib.php');
require_once( 'outcomerptlib.php');
require_once('outcome_report_level_form.php');

/* Params */
$report_level   = required_param('rpt', PARAM_INT);
$company_id     = optional_param('co',0,PARAM_INT);
$return_url     = new moodle_url('/report/generator/outcome_report/outcome_report.php',array('rpt' => $report_level));
$url            = new moodle_url('/report/generator/outcome_report/outcome_report_level.php',array('rpt' => $report_level));
$outcome_report = null;

/* Context */
$site_context = CONTEXT_SYSTEM::instance();
$site = get_site();

require_login();

$PAGE->requires->js(new moodle_url('/report/generator/js/tracker.js'));
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_generator','local_tracker'),new moodle_url('/report/generator/index.php'));
$PAGE->navbar->add(get_string('outcome_report', 'report_generator'),$return_url);
$PAGE->navbar->add(get_string('level_report','report_generator',$report_level),$url);

/* ADD requiere_capibility */
switch ($report_level) {
    case 1:
        if (!has_capability('report/generator:viewlevel1', $site_context)) {
            print_error('nopermissions', 'error', '', 'report/generator:viewlevel1');
        }

        break;
    case 2:
        if (!has_capability('report/generator:viewlevel2', $site_context)) {
            print_error('nopermissions', 'error', '', 'report/generator:viewlevel2');
        }

        break;
    case 3:
        if (!has_capability('report/generator:viewlevel3', $site_context)) {
            print_error('nopermissions', 'error', '', 'report/generator:viewlevel3');
        }

        break;
}//switch

if (empty($CFG->loginhttps)) {
   $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

/* My Hierarchy */
$my_hierarchy = outcome_report::get_MyHierarchyLevel($USER->id,$site_context);
$url = new moodle_url('/report/generator/outcome_report/outcome_report_level.php',array('rpt'=>$report_level));
/* Show Form */
if ($company_id) {
    $SESSION->level_three = array($company_id);
}else {
    unset($SESSION->level_three);
    unset($SESSION->job_roles);
}
$form = new generator_outcome_report_level_form(null,array($report_level,$my_hierarchy));
/* Report Variables */
$out     = '';

if ($form->is_cancelled()) {
    setcookie('parentLevelOne',0);
    setcookie('parentLevelTwo',0);
    setcookie('parentLevelTree',0);
    setcookie('outcomeReport',0);

    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Get Data */
    $data_form = (Array)$data;

    $outcome_report = outcome_report::Get_OutcomeReportLevel($data_form);

    if ($outcome_report) {
        /* Screen / Excel   */
        switch ($data_form[OUTCOME_REPORT_FORMAT_LIST]) {
            case OUTCOME_REPORT_FORMAT_SCREEN:
                $out = outcome_report::Print_OutcomeReport_Screen($outcome_report);

                break;
            case OUTCOME_REPORT_FORMAT_SCREEN_EXCEL:
                outcome_report::Download_OutcomeReport($outcome_report);

                break;
            default:
                break;
        }//switch_report_format
    }else {
        /* Non Data */
        $return  = '<a href="'.$url .'">'. get_string('outcome_return_to_selection','report_generator') .'</a>';
        $out     = get_string('no_data', 'report_generator');
        $out    .=  '<br/>' . $return;
    }//if_outcome_report

    //$out = report_generator_display_outcome_report($data_form);
}//if_else

/* Start the page */
$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

if (!empty($out)) {
    echo $OUTPUT->heading($out);
}else {
    /* Print tabs at the top */
    $current_tab = 'outcome_report';
    $show_roles = 1;
    require('../tabs.php');

    $form->display();
}//if_else

/* Print Footer */
echo $OUTPUT->footer();