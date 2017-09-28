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

global $CFG, $PAGE,$OUTPUT,$USER,$SITE,$SESSION;

require_once('../../../config.php');
require_once( '../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('employee_report_form.php');
require_once( 'employeelib.php');

// Params
$url                = new moodle_url('/report/manager/employee_report/employee_report.php');
$return             = new moodle_url('/report/manager/index.php');
$my_hierarchy       = null;
$employeeTracker    = null;
$company            = null;
$out                = '';

require_login();

// Settings page
$site_context = context_system::instance();
$PAGE->https_required();
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return);
$PAGE->navbar->add(get_string('employee_report_link','report_manager'),$url);

$IsReporter = CompetenceManager::IsReporter($USER->id);
if (!$IsReporter) {
    require_capability('report/manager:viewlevel4', $site_context,$USER->id);
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();


// My hierarchy
$myHierarchy = CompetenceManager::get_MyHierarchyLevel($USER->id,$site_context,$IsReporter,4);

// Show form
$form = new manager_employee_report_form(null,array($myHierarchy,$IsReporter));
if ($form->is_cancelled()) {
    unset($SESSION->selection);

    $_POST = array();
    redirect($return);
}else if($data = $form->get_data()) {
    // Get data
    $data_form = (Array)$data;

    // Keep selection data --> when it return to the main page
    $SESSION->selection = array();
    $SESSION->selection[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '0']   = (isset($data_form[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '0']) ? $data_form[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '0'] : 0);
    $SESSION->selection[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '1']   = (isset($data_form[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '1']) ? $data_form[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '1'] : 0);
    $SESSION->selection[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '2']   = (isset($data_form[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '2']) ? $data_form[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '2'] : 0);
    $SESSION->selection[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '3']   = (isset($data_form[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '3']) ? $data_form[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '3'] : 0);

    // Get company tracker info
    $company        = $data_form[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '3'];

    // Get employee tracker
    $employeeTracker = EmployeeReport::Get_EmployeeTracker($company,$data_form[REPORT_MANAGER_OUTCOME_LIST ]);

    // Print report
    $out = EmployeeReport::Print_EmployeeTracker($employeeTracker,$data_form[REPORT_MANAGER_COMPLETED_LIST]);
}//if_form


// Header
echo $OUTPUT->header();
// tabs at the top
$current_tab = 'manager_reports';

require('../tabs.php');

if (!empty($out)) {
    echo $out;
}else {
    require('../tabs.php');

    $form->display();

    // Initialise Organization Structure
    CompetenceManager::Init_Organization_Structure(COMPANY_STRUCTURE_LEVEL,null,REPORT_MANAGER_OUTCOME_LIST,0,null,false);
}//if_out

// Footer
echo $OUTPUT->footer();