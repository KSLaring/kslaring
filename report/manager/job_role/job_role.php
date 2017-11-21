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
 * Report Competence Manager - Job role.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 */

global $CFG,$SESSION,$PAGE,$USER,$SITE,$OUTPUT;

require_once('../../../config.php');
require_once( 'jobrolelib.php');
require_once('../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');

// Params
$url        = new moodle_url('/report/manager/job_role/job_role.php');
$return_url = new moodle_url('/report/manager/index.php');
$superUser      = false;
$myAccess       = null;
$site_context   = CONTEXT_SYSTEM::instance();

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

// Page settings
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

// Super user
$superUser  = CompetenceManager::is_super_user($USER->id);
$myAccess   = CompetenceManager::get_my_access($USER->id);
// Capability
if (!$superUser) {
    require_capability('report/manager:edit', $site_context);
    $PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return_url);
}else {
    $return_url = $url;
}//if_SuperUser
$PAGE->navbar->add(get_string('job_roles', 'report_manager'),$url);


if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

// Header
echo $OUTPUT->header();
// Tabs
$current_tab = 'job_roles';
$show_roles = 1;
require('../tabs.php');

// Job role list
$job_roles = job_role::JobRole_With_Outcomes($superUser,$myAccess);

// Add jr link
$url_edit = new moodle_url('/report/manager/job_role/add_job_role.php');
echo $OUTPUT->action_link($url_edit,get_string('add_job_role','report_manager'));

if (empty($job_roles)) {
    echo $OUTPUT->heading(get_string('available_job_roles', 'report_manager'));
    echo '<p>' . get_string('no_job_roles_available', 'report_manager') . '</p>';
}else {
    echo $OUTPUT->heading(get_string('job_roles', 'report_manager'));

    // Job role table
    $url_edit = new moodle_url('/report/manager/job_role/add_job_role.php');
    $table = job_role::JobRoles_table($job_roles,$superUser);

    echo html_writer::table($table);
}//if_else

echo $OUTPUT->action_link($url_edit,get_string('add_job_role','report_manager'));

// Footer
echo $OUTPUT->footer();