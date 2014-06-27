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
 * Edit Job Role
 *
 */

require_once('../../../config.php');
require_once('../locallib.php');
require_once('edit_job_role_form.php');
require_once($CFG->libdir . '/adminlib.php');


/* Params */
$job_role_id    = optional_param('id',0,PARAM_INT);
$return_url     = new moodle_url('/report/generator/job_role/job_role.php');
/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url('/report/generator/job_role/edit_job_role.php');

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
$form = new generator_edit_job_role_form(null,$job_role_id);

if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Get Data */
    $job_role = new stdClass();
    $job_role->modified = time();
    $job_role->name = $data->job_role_name;
    $select = REPORT_GENERATOR_OUTCOME_LIST;

    if (!isset($data->$select)) {
        $outcome_list = array();
    }else {
        $outcome_list = $data->$select;
    }

    if ($job_role_id){
        /* Update Job Role      */
        $job_role->id = $data->id;
        report_generator_update_job_role_out($job_role,$outcome_list);
    }else {
        /* Insert New Job Role */
        report_generator_insert_job_role_out($job_role,$outcome_list);
    }//if_job_id

    $_POST = array();
    redirect($return_url);
}//if_else

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Print Footer */
echo $OUTPUT->footer();