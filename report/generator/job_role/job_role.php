<?php

/**
 * Report generator - Job role.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( '../locallib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* Clean Cookies */
setcookie('parentLevelOne',0);
setcookie('parentLevelTwo',0);
setcookie('parentLevelTree',0);
$_POST = array();

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url('/report/generator/job_role/job_role.php');

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
/* Print tabs at the top */
$current_tab = 'job_roles';
$show_roles = 1;
require('../tabs.php');


/* Get Job Role List */
$job_roles = report_generator_get_jobrole_list_with_rel_outcomes();

if (empty($job_roles)) {
    /* Print Title */
    echo $OUTPUT->heading(get_string('available_job_roles', 'report_generator'));
    echo '<p>' . get_string('no_job_roles_available', 'report_generator') . '</p>';
}else {
    /* Print Title */
    echo $OUTPUT->heading(get_string('job_roles', 'report_generator'));
    $table = report_generator_table_job_roles($job_roles);

    echo html_writer::table($table);
}//if_else

$url_edit = new moodle_url('/report/generator/job_role/edit_job_role.php');
echo $OUTPUT->single_button($url_edit,get_string('add_job_role', 'report_generator'));

/* Print Footer */
echo $OUTPUT->footer();