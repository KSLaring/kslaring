<?php

/**
 * Report generator - Job Role.
 *
 * Description
 *
 * @package         report
 * @subpackage      generator/job_role
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    06/11/2014
 * @author          eFaktor     (fbv)
 *
 * Add Job Role
 *
 */

require_once('../../../config.php');
require_once('../locallib.php');
require_once( 'jobrolelib.php');
require_once('add_job_role_form.php');
require_once($CFG->libdir . '/adminlib.php');


/* Params */
$return_url     = new moodle_url('/report/generator/job_role/job_role.php');
$url            = new moodle_url('/report/generator/job_role/add_job_role.php');
$return         = new moodle_url('/report/generator/index.php');

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_generator','local_tracker'),$return);
$PAGE->navbar->add(get_string('job_roles', 'report_generator'),$return_url);
$PAGE->navbar->add(get_string('add_job_role', 'report_generator'));

/* ADD require_capability */
if (!has_capability('report/generator:edit', $site_context)) {
    print_error('nopermissions', 'error', '', 'report/generator:edit');
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

/* Show Form */
$form = new generator_add_job_role_form(null,null);

if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Insert New Job Role */
    job_role::Insert_JobRole($data);

    $_POST = array();
    redirect($return_url);
}//if_else

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Print Footer */
echo $OUTPUT->footer();