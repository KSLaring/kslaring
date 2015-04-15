<?php

/**
 * Report Competence Manager - Outcome report Level.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/outcome_report
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  14/09/2012
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( '../managerlib.php');
require_once( 'outcomerptlib.php');
require_once('outcome_report_level_form.php');

/* Params */
$report_level           = required_param('rpt', PARAM_INT);
$company_id             = optional_param('co',0,PARAM_INT);
$parentTwo              = optional_param('lt',0,PARAM_INT);
$parentOne              = optional_param('lo',0,PARAM_INT);
$completed_option       = optional_param('opt',0,PARAM_INT);
$return_url             = new moodle_url('/report/manager/outcome_report/outcome_report.php',array('rpt' => $report_level));
$url                    = new moodle_url('/report/manager/outcome_report/outcome_report_level.php',array('rpt' => $report_level));
$outcome_report         = null;

/* Context */
$site_context = CONTEXT_SYSTEM::instance();
$site = get_site();

require_login();

$PAGE->requires->js(new moodle_url('/report/manager/js/tracker.js'));
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),new moodle_url('/report/manager/index.php'));
$PAGE->navbar->add(get_string('outcome_report', 'report_manager'),$return_url);
$PAGE->navbar->add(get_string('level_report','report_manager',$report_level),$url);

/* ADD requiere_capibility */
switch ($report_level) {
    case 0:
        if (!has_capability('report/manager:viewlevel0', $site_context)) {
            print_error('nopermissions', 'error', '', 'report/manager:viewlevel1');
        }

        break;
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

/* Start the page */
$PAGE->verify_https_required();

/* My Hierarchy */
$my_hierarchy = CompetenceManager::get_MyHierarchyLevel($USER->id,$site_context);

/* Show Form */
if ($company_id) {
    $data_form = array();
    if (isset($SESSION->job_roles)) {
        $data_form[REPORT_MANAGER_JOB_ROLE_LIST]        = $SESSION->job_roles;
    }else {
        $data_form[REPORT_MANAGER_JOB_ROLE_LIST]        = null;
    }

    $data_form['rpt']                                   = $report_level;
    $data_form[OUTCOME_REPORT_FORMAT_LIST]              = OUTCOME_REPORT_FORMAT_SCREEN;
    $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0']    = $_COOKIE['parentLevelZero'];
    $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1']    = $parentOne;
    $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2']    = $parentTwo;
    $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3']    = array($company_id);
    $data_form[REPORT_MANAGER_OUTCOME_LIST]             = $_COOKIE['outcomeReport'];
    $data_form[REPORT_MANAGER_COMPLETED_LIST]           = $completed_option;

    /* Get the data to the report   */
    $outcome_report = outcome_report::Get_OutcomeReportLevel($data_form,$my_hierarchy);
    $out = outcome_report::Print_OutcomeReport_Screen($outcome_report,$data_form[REPORT_MANAGER_COMPLETED_LIST]);
}

$form = new manager_outcome_report_level_form(null,array($report_level,$my_hierarchy));
if ($form->is_cancelled()) {
    setcookie('parentLevelZero',0);
    setcookie('parentLevelOne',0);
    setcookie('parentLevelTwo',0);
    setcookie('parentLevelThree',0);
    setcookie('courseReport',0);
    setcookie('outcomeReport',0);

    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Get Data */
    $data_form = (Array)$data;

    $outcome_report = outcome_report::Get_OutcomeReportLevel($data_form,$my_hierarchy);

    if ($outcome_report) {
        /* Screen / Excel   */
        switch ($data_form[OUTCOME_REPORT_FORMAT_LIST]) {
           case OUTCOME_REPORT_FORMAT_SCREEN:
                $out = outcome_report::Print_OutcomeReport_Screen($outcome_report,$data_form[REPORT_MANAGER_COMPLETED_LIST]);

                break;
            case OUTCOME_REPORT_FORMAT_SCREEN_EXCEL:
                outcome_report::Download_OutcomeReport($outcome_report);

                break;
            default:
                break;
        }//switch_report_format
    }else {
        /* Non Data */
        $return  = '<a href="'.$url .'">'. get_string('outcome_return_to_selection','report_manager') .'</a>';
        $out     = get_string('no_data', 'report_manager');
        $out    .=  '<br/>' . $return;
    }//if_outcome_report
}//if_else

/* Print Header */
echo $OUTPUT->header();

if (!empty($out)) {
    echo $out;
}else {
    /* Print tabs at the top */
    $current_tab = 'outcome_report_level';
    $show_roles = 1;
    require('../tabs.php');

    $form->display();
}//if_else

/* Print Footer */
echo $OUTPUT->footer();