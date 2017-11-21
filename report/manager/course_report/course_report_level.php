<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
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

global $PAGE,$CFG,$SESSION,$SITE,$USER,$OUTPUT;

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

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

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
$IsReporter = CompetenceManager::is_reporter($USER->id);
CompetenceManager::check_capability_reports($IsReporter,$report_level,$site_context);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

// Security
$PAGE->verify_https_required();

// My hierarchy
$myHierarchy = CompetenceManager::get_my_hierarchy_level($USER->id,$site_context,$IsReporter,$report_level);

// Show form
$SESSION->onlyCompany = array();
if ($company_id) {
    $data_form = array();
    if (isset($SESSION->job_roles)) {
        $data_form[REPORT_MANAGER_JOB_ROLE_LIST]    = $SESSION->job_roles;
    }else {
        $data_form[REPORT_MANAGER_JOB_ROLE_LIST]    = null;
    }

    $data_form['rpt']                               = $report_level;
    $data_form[COURSE_REPORT_FORMAT_LIST]           = COURSE_REPORT_FORMAT_SCREEN;
    $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'] = $USER->levelZero;
    $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'] = $parentOne;
    $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2'] = $parentTwo;
    $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3'] = array($company_id => $company_id);
    $data_form[REPORT_MANAGER_COURSE_LIST]          = $USER->courseReport;
    $data_form[REPORT_MANAGER_COMPLETED_LIST]       = $completed_option;

    // Keep selection data --> when it returns to the main page
    $SESSION->selection = array();
    $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . '0']   = $USER->levelZero;
    $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . '1']   = $parentOne;
    $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . '2']   = $parentTwo;
    $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . '3']   = array($company_id => $company_id);
    $SESSION->selection[REPORT_MANAGER_COURSE_LIST]             = $USER->courseReport;

    // Get data report
    $course_report = course_report::Get_CourseReportLevel($data_form,$myHierarchy,$IsReporter);
    $out = course_report::Print_CourseReport_Screen($course_report,$data_form[REPORT_MANAGER_COMPLETED_LIST]);
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
    // Get data
    $data_form = (Array)$data;

    // Levels selected
    $data_form[MANAGER_COURSE_STRUCTURE_LEVEL . '0'] = $data_form['h0'];
    $data_form[MANAGER_COURSE_STRUCTURE_LEVEL . '1'] = $data_form['h1'];
    $data_form[MANAGER_COURSE_STRUCTURE_LEVEL . '2'] = $data_form['h2'];
    $data_form['h3'] = explode(',',$data_form['h3']);
    $three = array();
    foreach ($data_form['h3'] as $id) {
        $three[$id] = $id;
    }
    $data_form[MANAGER_COURSE_STRUCTURE_LEVEL . '3'] = $three;
    $data_form['h3']                                 = $three;
    
    // Report data
    $course_report = course_report::Get_CourseReportLevel($data_form,$myHierarchy,$IsReporter);

    if (isset($data_form[REPORT_MANAGER_JOB_ROLE_LIST]) && $data_form[REPORT_MANAGER_JOB_ROLE_LIST]) {
        unset($SESSION->job_roles);
        $SESSION->job_roles = $data_form[REPORT_MANAGER_JOB_ROLE_LIST];
    }else {
        unset($SESSION->job_roles);
    }

    // Keep selection data --> when it returns to the main page
    $SESSION->selection = array();
    $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . '0']   = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL . '0'];
    $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . '1']   = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL . '1'];
    $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . '2']   = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL . '2'];
    $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . '3']   = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL . '3'];
    $SESSION->selection[REPORT_MANAGER_COURSE_LIST]             = (isset($data_form[REPORT_MANAGER_COURSE_LIST]) ? $data_form[REPORT_MANAGER_COURSE_LIST] : 0);

    if ($course_report) {
        // Select report (Screen - Excel)
        switch ($data_form[COURSE_REPORT_FORMAT_LIST]) {
            case COURSE_REPORT_FORMAT_SCREEN:
                $out = course_report::Print_CourseReport_Screen($course_report,$data_form[REPORT_MANAGER_COMPLETED_LIST]);

                break;
            case COURSE_REPORT_FORMAT_SCREEN_EXCEL:
                course_report::Download_CourseReport($course_report);

                break;
            default:
                break;
        }//switch_report_format
    }else {
        // Non data
        $return  = '<a href="'.$url .'">'. get_string('course_return_to_selection','report_manager') .'</a>';
        $out     = '</h3>' . get_string('no_data', 'report_manager') . '</h3>';
        $out    .=  '<br/>' . $return;
    }//if_outcome_report
}//if_form

// Header
echo $OUTPUT->header();


if (!empty($out)) {
    echo $OUTPUT->heading($out);
}else {
    // Add tabs on the top
    $current_tab = 'manager_reports';
    $show_roles = 1;
    require('../tabs.php');

    // Add level links
    $linkLevels = '<a href="' . $return_url . '">' . get_string('select_report_levels','report_manager') . '</a>';
    echo $OUTPUT->action_link($return_url,get_string('select_report_levels','report_manager'));

    $form->display();

    // Initialise Organization structure
    CompetenceManager::init_organization_structure_coursereport(COMPANY_STRUCTURE_LEVEL,REPORT_MANAGER_JOB_ROLE_LIST,$report_level);
}//if_else

// Footer
echo $OUTPUT->footer();
