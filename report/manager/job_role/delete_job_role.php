<?php

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

require_once('../../../config.php');
require_once( 'jobrolelib.php');
require_once('../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');

/* Params */
$job_role_id    = required_param('id',PARAM_INT);
$confirmed      = optional_param('confirm', false, PARAM_BOOL);

$return_url     = new moodle_url('/report/manager/job_role/job_role.php');
$return         = new moodle_url('/report/manager/index.php');
$url            = new moodle_url('/report/manager/job_role/delete_job_role.php',array('id' => $job_role_id));
$confirmUrl     = new moodle_url('/report/manager/job_role/delete_job_role.php',array('id' => $job_role_id,'confirm'=>true));

$superUser      = false;

$jobRoleInfo    = null;
$jobName        = null;

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('job_roles', 'report_manager'),$return_url);

/* Info Super Users */
$superUser  = CompetenceManager::IsSuperUser($USER->id);
$myAccess   = CompetenceManager::Get_MyAccess($USER->id);

/* ADD require_capability */
if (!$superUser) {
    require_capability('report/manager:edit', $site_context);
    $PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return);
}else {
    /* Check job role is valid  */
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

/* Print Header */
echo $OUTPUT->header();

if ($confirmed) {
    /* Check if the job role can be removed */
    $user_connected = job_role::Users_Connected($job_role_id);
    if (!$user_connected) {
        /* Remove */
        job_role::Delete_JobRole($job_role_id);
        echo $OUTPUT->notification(get_string('deleted_job_role','report_manager'), 'notifysuccess');
        echo $OUTPUT->continue_button($return_url);
    }else {
        /* Not Remove */
        echo $OUTPUT->notification(get_string('error_deleting_job_role','report_manager'), 'notifysuccess');
        echo $OUTPUT->continue_button($return_url);
    }//if_else
}else {
    /* First Confirm    */
    $jobRoleInfo    = job_role::JobRole_Info($job_role_id);
    $jobName        = $jobRoleInfo->industry_code . ' - '. $jobRoleInfo->name;
    echo $OUTPUT->confirm(get_string('delete_job_role_sure','report_manager',$jobName),$confirmUrl,$return_url);
}//if_confirm_delte_company

/* Print Footer */
echo $OUTPUT->footer();