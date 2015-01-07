<?php

/**
 * Report generator - Outcome.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator/outcome
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  13/09/2012
 * @author      eFaktor     (fbv)
 *
 * Edit Outcome
 *
 */

require_once('../../../config.php');
require_once('../locallib.php');
require_once( 'outcomelib.php');
require_once('edit_outcome_form.php');
require_once($CFG->libdir . '/adminlib.php');

/* Params */
$outcome_id    = required_param('id', PARAM_INT);
$expiration_id = optional_param('expid', 0, PARAM_INT);

$url        = new moodle_url('/report/generator/outcome/edit_outcome.php',array('id' => $outcome_id));
$return     = new moodle_url('/report/generator/index.php');
$return_url     = new moodle_url('/report/generator/outcome/outcome.php');

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_generator','local_tracker'),$return_url);
$PAGE->navbar->add(get_string('outcome', 'report_generator'),$return);
$PAGE->navbar->add(get_string('edit_outcome', 'report_generator'));

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
$form = new generator_edit_outcome_form(null,array($outcome_id,$expiration_id));

if ($form->is_cancelled()) {
    setcookie('parentLevelOne',0);
    setcookie('parentLevelTwo',0);
    setcookie('parentLevelTree',0);
    setcookie('courseReport',0);
    setcookie('outcomeReport',0);

    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Get Data */
    $outcome = new stdClass();
    $outcome->outcomeid         = $data->id;
    $outcome->expirationperiod  = $data->expiration_period;
    $outcome->modified          = time();
    $select = REPORT_GENERATOR_JOB_ROLE_LIST;
    $role_list = $data->$select;

    if ($expiration_id) {
        /* Update Outcome */
        $outcome->id = $data->expid;
        outcome::Update_Outcome($outcome,$role_list);
    }else {
        /* Insert */
        outcome::Insert_Outcome($outcome,$role_list);
    }//if_else

    $_POST = array();
    redirect($return_url);
}//if_else

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Print Footer */
echo $OUTPUT->footer();
