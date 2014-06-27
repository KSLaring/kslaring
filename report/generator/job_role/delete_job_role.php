<?php

/**
 * Report generator - Job Role.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator/job_role
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
require_once('../locallib.php');
require_once($CFG->libdir . '/adminlib.php');

/* Params */
$job_role_id  = required_param('id',PARAM_INT);
$return_url = new moodle_url('/report/generator/job_role/job_role.php');

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url('/report/generator/job_role/delete_job_role.php');

/* ADD require_capability */
if (!has_capability('report/generator:edit', $site_context)) {
    print_error('nopermissions', 'error', '', 'report/generator:edit');
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

/* Check if the job role can be removed */
$user_connected = report_generator_count_connected_users($job_role_id,REPORT_GENERATOR_JOB_ROLE_FIELD);
if (!$user_connected) {
    /* Remove */
    report_generator_delete_job_role_out($job_role_id);
    echo $OUTPUT->notification(get_string('deleted_job_role','report_generator'), 'notifysuccess');
    echo $OUTPUT->continue_button($return_url);
}else {
    /* Not Remove */
    echo $OUTPUT->notification(get_string('error_deleting_job_role','report_generator'), 'notifysuccess');
    echo $OUTPUT->continue_button($return_url);
}//if_else
/* Print Footer */
echo $OUTPUT->footer();