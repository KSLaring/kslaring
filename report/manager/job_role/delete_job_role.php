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
 * Report Competence Manager - Job Role.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/job_role
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  12/09/2012
 * @author      eFaktor     (fbv)
 *
 * Delete Job Role
 *
 */

global $CFG,$SESSION,$PAGE,$USER,$SITE,$OUTPUT;

require_once('../../../config.php');
require_once( 'jobrolelib.php');
require_once('../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');

// Params
$job_role_id    = required_param('id',PARAM_INT);
$confirmed      = optional_param('confirm', false, PARAM_BOOL);

$return_url     = new moodle_url('/report/manager/job_role/job_role.php');
$return         = new moodle_url('/report/manager/index.php');
$url            = new moodle_url('/report/manager/job_role/delete_job_role.php',array('id' => $job_role_id));
$confirmUrl     = new moodle_url('/report/manager/job_role/delete_job_role.php',array('id' => $job_role_id,'confirm'=>true));
$superUser      = false;
$jobRoleInfo    = null;
$jobName        = null;
$site_context   = CONTEXT_SYSTEM::instance();

// Page settings
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('job_roles', 'report_manager'),$return_url);

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}
// Super user
$superUser  = CompetenceManager::is_super_user($USER->id);
$myAccess   = CompetenceManager::get_my_access($USER->id);

/* ADD require_capability */
if (!$superUser) {
    require_capability('report/manager:edit', $site_context);
    $PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return);
}else {
    // Check job role is valid
    if (!job_role::CheckJobRoleAccess($myAccess,$job_role_id)){
        print_error('nopermissions', 'error', '', 'report/manager:edit');
    }
}//if_SuperUser

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

// Header
echo $OUTPUT->header();

if ($confirmed) {
    // Check if it can be deleted
    $user_connected = job_role::Users_Connected($job_role_id);
    if (!$user_connected) {
        // Remove
        job_role::Delete_JobRole($job_role_id);
        echo $OUTPUT->notification(get_string('deleted_job_role','report_manager'), 'notifysuccess');
        echo $OUTPUT->continue_button($return_url);
    }else {
        echo $OUTPUT->notification(get_string('error_deleting_job_role','report_manager'), 'notifysuccess');
        echo $OUTPUT->continue_button($return_url);
    }//if_else
}else {
    // First confirm
    $jobRoleInfo    = job_role::JobRole_Info($job_role_id);
    $jobName        = $jobRoleInfo->industry_code . ' - '. $jobRoleInfo->name;
    echo $OUTPUT->confirm(get_string('delete_job_role_sure','report_manager',$jobName),$confirmUrl,$return_url);
}//if_confirm_delte_company

// Footer
echo $OUTPUT->footer();