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
 * @updateDate  17/03/2015
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( 'courserptlib.php');
require_once( '../managerlib.php');
require_once('course_report_level_form.php');

// Params
$report_level           = optional_param('rpt',0,PARAM_INT);
$company_id             = optional_param('co',0,PARAM_INT);
$parentTwo              = optional_param('lt',0,PARAM_INT);
$parentOne              = optional_param('lo',0,PARAM_INT);
$completed_option       = optional_param('opt',0,PARAM_INT);
$return_url             = new moodle_url('/report/manager/course_report/course_report.php',array('rpt' => $report_level));
$url                    = new moodle_url('/report/manager/course_report/course_report_level.php',array('rpt' => $report_level));
$course_report          = null;
$IsReporter             = null;
$myHierarchy            = null;

// Context
$site_context = context_system::instance();
$site = get_site();

// Report
$out     = '';

require_login();

$PAGE->requires->js(new moodle_url('/report/manager/js/tracker.js'));
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),new moodle_url('/report/manager/index.php'));
$PAGE->navbar->add(get_string('course_report', 'report_manager'),$return_url);
$PAGE->navbar->add(get_string('level_report','report_manager',$report_level),$url);

// Require capabilty
$IsReporter = CompetenceManager::IsReporter($USER->id);
switch ($report_level) {
    case 0:
        if (!has_capability('report/manager:viewlevel0', $site_context)) {
            if (!$IsReporter) {
                print_error('nopermissions', 'error', '', 'report/manager:viewlevel0');
            }//ifReporter
        }

        break;
    case 1:
        if (!has_capability('report/manager:viewlevel1', $site_context)) {
            if (!$IsReporter) {
                print_error('nopermissions', 'error', '', 'report/manager:viewlevel1');
            }//ifReporter
        }

        break;
    case 2:
        if (!has_capability('report/manager:viewlevel2', $site_context)) {
            if (!$IsReporter) {
                print_error('nopermissions', 'error', '', 'report/manager:viewlevel2');
            }//ifReporter
        }

        break;
    case 3:
        if (!has_capability('report/manager:viewlevel3', $site_context)) {
            if (!$IsReporter) {
                print_error('nopermissions', 'error', '', 'report/manager:viewlevel3');
            }//ifReporter
        }

        break;
}//switch

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

// Security
$PAGE->verify_https_required();

// My hierarchy
$myHierarchy = CompetenceManager::get_MyHierarchyLevel($USER->id,$site_context,$IsReporter,$report_level);

// Show form
if ($company_id) {
    $data_form = array();
}else {
    // Clean temporary
    course_report::CleanTemporary();
}

$form = new manager_course_report_level_form(null,array($report_level,$myHierarchy,$IsReporter));
if ($form->is_cancelled()) {
    unset($SESSION->selection);

    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Get Data */
    //
    $data_form = (Array)$data;

    $out .= "Sorry, we are working on it" . "</br>";
    $out .= "</br></br>";
    $out .= "Zero:   " . $data_form['h_0'] . "</br>";
    $out .= "One :   " . $data_form['h_1'] . "</br>";
    $out .= "Two:    " . $data_form['h_2'] . "</br>";
    $out .= "Three:  " . $data_form['h_3'] . "</br>";

}//if_form

/* Print Header */
echo $OUTPUT->header();

if (!empty($out)) {
    echo $OUTPUT->heading($out);
}else {
    /* Print tabs at the top */
    $current_tab = 'manager_reports';
    $show_roles = 1;
    require('../tabs.php');

    /* Add Levels Links */
    $linkLevels = '<a href="' . $return_url . '">' . get_string('select_report_levels','report_manager') . '</a>';
    echo $OUTPUT->action_link($return_url,get_string('select_report_levels','report_manager'));

    $form->display();

    /* Initialise Organization Structure    */
    CompetenceManager::Init_OrganizationStructure_CourseReport(COMPANY_STRUCTURE_LEVEL,REPORT_MANAGER_JOB_ROLE_LIST,$report_level);
}//if_else

/* Print Footer */
echo $OUTPUT->footer();
