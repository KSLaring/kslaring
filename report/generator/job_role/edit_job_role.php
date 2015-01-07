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
 * @updateDate      06/11/2014
 * @author          eFaktor     (fbv)
 *
 * Edit Job Role
 *
 */

require_once('../../../config.php');
require_once('../locallib.php');
require_once( 'jobrolelib.php');
require_once('edit_job_role_form.php');
require_once($CFG->libdir . '/adminlib.php');

/* Params */
$job_role_id    = required_param('id',PARAM_INT);
$return_url     = new moodle_url('/report/generator/job_role/job_role.php');
$url            = new moodle_url('/report/generator/job_role/edit_job_role.php',array('id' => $job_role_id));
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
$PAGE->navbar->add(get_string('edit_job_roles', 'report_generator'));

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

/* Job Role Info    */
$jr_info = job_role::JobRole_Info($job_role_id);
/* Form     */
$form = new generator_edit_job_role_form(null,$jr_info);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Update New Job Role */
    job_role::Update_JobRole($data);

    $_POST = array();
    redirect($return_url);
}//if_else

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Print Footer */
echo $OUTPUT->footer();