<?php

/**
 * Report Competence Manager - Outcome.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/outcome
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

$url        = new moodle_url('/report/manager/outcome/edit_outcome.php',array('id' => $outcome_id));
$return     = new moodle_url('/report/manager/index.php');
$return_url     = new moodle_url('/report/manager/outcome/outcome.php');

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return_url);
$PAGE->navbar->add(get_string('outcome', 'report_manager'),$return);
$PAGE->navbar->add(get_string('edit_outcome', 'report_manager'));
$PAGE->requires->js('/report/manager/js/outcome.js');

/* ADD require_capability */
if (!has_capability('report/manager:edit', $site_context)) {
    print_error('nopermissions', 'error', '', 'report/manager:edit');
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

/* Available Job Roles  */
if (!isset($SESSION->jobRoles)) {
    $SESSION->jobRoles = array();
}//companies

/* Selected Job Roles   */
if (!isset($SESSION->selJobRoles)) {
    $SESSION->selJobRoles = array();
}//selCompanies

/* Add all Job Roles    */
if (!isset($SESSION->addAll)) {
    $SESSION->addAll = false;
}//id_addAll

/* Remove Job Roles */
if (!isset($SESSION->removeAll)) {
    $SESSION->removeAll = false;
}//if_removeAll

/* Show Form */
$form = new manager_edit_outcome_form(null,array($outcome_id,$expiration_id));

if ($form->is_cancelled()) {
    setcookie('parentLevelOne',0);
    setcookie('parentLevelTwo',0);
    setcookie('parentLevelTree',0);
    setcookie('courseReport',0);
    setcookie('outcomeReport',0);

    /* Clean SESSION    */
    unset($SESSION->addAll);
    unset($SESSION->removeAll);
    unset($SESSION->jobRoles);
    unset($SESSION->selJobRoles);

    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    $SESSION->addAll    = false;
    $SESSION->removeAll = false;

    /* Add All Job Roles    */
    if (isset($data->add_all) && ($data->add_all)) {
        $SESSION->addAll        = true;
        $SESSION->selJobRoles   = array();
        $SESSION->jobRoles      = array();

        $form = new manager_edit_outcome_form(null,array($outcome_id,$expiration_id));
    }//add_all_jobroles

    /* Remove All Job Roles */
    if (isset($data->remove_all) && ($data->remove_all)) {
        $SESSION->removeAll     = true;
        $SESSION->selJobRoles   = array();
        $SESSION->jobRoles      = array();

        $form = new manager_edit_outcome_form(null,array($outcome_id,$expiration_id));
    }//remove_all_jobroles

    /* Add selected Job Roles       */
    if (isset($data->add_sel) && ($data->add_sel)) {
        foreach($data->ajobroles as $key=>$value) {
            $SESSION->selJobRoles[$value] = $value;
        }
        $form = new manager_edit_outcome_form(null,array($outcome_id,$expiration_id));
    }//if_add_jobroles

    /* Remove selected Job Roles    */
    if (isset($data->remove_sel) && ($data->remove_sel)) {
        foreach($data->sjobroles as $key=>$value) {
            unset($SESSION->selJobRoles[$value]);
            $SESSION->jobRoles[$value] = $value;
        }
        $form = new manager_edit_outcome_form(null,array($outcome_id,$expiration_id));
    }//if_remove_jobroles

    if ((isset($data->submitbutton) && $data->submitbutton)) {
        /* Get Data */
        $outcome = new stdClass();
        $outcome->outcomeid         = $data->id;
        $outcome->expirationperiod  = $data->expiration_period;
        $outcome->modified          = time();
        $select = REPORT_MANAGER_JOB_ROLE_LIST;
        $role_list = $SESSION->selJobRoles;

        if ($expiration_id) {
            /* Update Outcome */
            $outcome->id = $data->expid;
            outcome::Update_Outcome($outcome,$role_list);
        }else {
            /* Insert */
            outcome::Insert_Outcome($outcome,$role_list);
        }//if_else

        unset($SESSION->addAll);
        unset($SESSION->removeAll);
        unset($SESSION->jobRoles);
        unset($SESSION->selJobRoles);

        $_POST = array();
        redirect($return_url);
    }//if_submit_button
}//if_else

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Print Footer */
echo $OUTPUT->footer();
