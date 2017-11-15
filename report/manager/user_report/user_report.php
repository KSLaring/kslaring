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
 * Report Competence Manager - User report.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/user_report
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    24/05/2017
 * @author          eFaktor     (fbv)
 */

require_once('../../../config.php');
require_once( 'usersrptlib.php');
require_once( 'user_report_form.php');
require_once( '../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

// Params
$url            = new moodle_url('/report/manager/user_report/user_report.php');
$return_url     = new moodle_url('/report/manager/index.php');
$IsReporter     = null;
$myHierarchy    = null;
$out            = null;
$data_rpt       = null;

$site_context = context_system::instance();
$IsReporter = CompetenceManager::IsReporter($USER->id);
if (!$IsReporter) {
    require_capability('report/manager:viewlevel4', $site_context,$USER->id);
}

// Set page
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return_url);
$PAGE->navbar->add(get_string('user_report', 'report_manager'),$url);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

// Security
$PAGE->verify_https_required();

// My hierarchy
$myHierarchy = CompetenceManager::get_my_hierarchy_level($USER->id,$site_context,$IsReporter,4);

// Form
$form = new manager_user_report_form(null,array($myHierarchy,$IsReporter));
if ($form->is_cancelled()) {
    unset($SESSION->selection);

    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    // Get data
    $data_form = (Array)$data;

    // Levels selected
    $data_form[USER_REPORT_STRUCTURE_LEVEL . '0'] = $data_form['h0'];
    $data_form[USER_REPORT_STRUCTURE_LEVEL . '1'] = $data_form['h1'];
    $data_form[USER_REPORT_STRUCTURE_LEVEL . '2'] = $data_form['h2'];

    // Keep selection data --> when it returns to the main page
    $SESSION->selection = array();
    $SESSION->selection[USER_REPORT_STRUCTURE_LEVEL . '0']   = $data_form[USER_REPORT_STRUCTURE_LEVEL . '0'];
    $SESSION->selection[USER_REPORT_STRUCTURE_LEVEL . '1']   = $data_form[USER_REPORT_STRUCTURE_LEVEL . '1'];
    $SESSION->selection[USER_REPORT_STRUCTURE_LEVEL . '2']   = $data_form[USER_REPORT_STRUCTURE_LEVEL . '2'];
    $SESSION->selection[USER_REPORT_STRUCTURE_LEVEL . '3']   = $data_form[USER_REPORT_STRUCTURE_LEVEL . '3'];

    // Get data connected with the report
    $data_rpt = UserReport::data_user_report($data_form);

    // Select report (Screen - Excel)
    switch ($data_form[USER_REPORT_FORMAT_LIST]) {
        case USER_REPORT_FORMAT_SCREEN:
            $out = UserReport::print_user_report_screen($data_rpt);

            break;
        case USER_REPORT_FORMAT_SCREEN_EXCEL:
            ob_end_clean();
            UserReport::download_user_report($data_rpt);

            die;
            break;
        default:
            break;
    }//switch_report_format
}//if_form

// Header
echo $OUTPUT->header();
// Tabs
$current_tab = 'manager_reports';
$show_roles = 1;
require('../tabs.php');

// Title
echo $OUTPUT->heading(get_string('user_report', 'report_manager'));

if ($out) {
    echo $out;
}else {
    $form->display();
}

// Initialise Organization Structure
UserReport::Init_OrganizationStructure(USER_REPORT_STRUCTURE_LEVEL);
// Footer
echo $OUTPUT->footer();
