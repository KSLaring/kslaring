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
require_once( 'outcomelib.php');
require_once('edit_outcome_form.php');
require_once($CFG->libdir . '/adminlib.php');

/* Params */
$outcome_id     = required_param('id', PARAM_INT);
$expiration_id  = optional_param('expid', 0, PARAM_INT);
$removeSelected = optional_param_array('removeselect',0,PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);

$url        = new moodle_url('/report/manager/outcome/edit_outcome.php',array('id' => $outcome_id));
$return     = new moodle_url('/report/manager/index.php');
$return_url = new moodle_url('/report/manager/outcome/outcome.php');

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

/* ADD require_capability */
if (!has_capability('report/manager:edit', $site_context)) {
    print_error('nopermissions', 'error', '', 'report/manager:edit');
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

/* Show Form */
$form = new manager_edit_outcome_form(null,array($outcome_id,$expiration_id,$addSearch,$removeSearch,$removeSelected));

if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    if (!empty($data->add_sel)) {
        if (isset($data->addselect)) {
            /* Add Jobroles */
            /* Get Data */
            $infoOutcome = new stdClass();
            $infoOutcome->outcomeid         = $data->id;
            $infoOutcome->expirationperiod  = $data->expiration_period;
            $infoOutcome->modified          = time();

            if ($data->expid) {
                /* Update Outcome */
                $infoOutcome->id = $data->expid;
                outcome::Update_Outcome($infoOutcome,$data->addselect);
            }else {
                /* Insert */
                outcome::Insert_Outcome($infoOutcome,$data->addselect);
            }//if_else
        }//if_addselect
    }if (!empty($data->remove_sel)) {
        if (isset($data->removeselect)) {
           outcome::Delete_JR_Outcome($data->id,$data->removeselect);
        }
    }

    if ((isset($data->submitbutton) && $data->submitbutton)) {
        /* Get Data */
        $outcome = new stdClass();
        $outcome->outcomeid         = $data->id;
        $outcome->expirationperiod  = $data->expiration_period;
        $outcome->modified          = time();

        if ($expiration_id) {
            /* Update Outcome */
            $outcome->id = $data->expid;
            outcome::Update_Outcome($outcome,null);
        }else {
            /* Insert */
            outcome::Insert_Outcome($outcome,null);
        }//if_else

        $_POST = array();
        redirect($return_url);
    }//if_submit_button

    $_POST = array();
}//if_else

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Initialise Selectors */
outcome::Init_JobRoles_Selectors($outcome_id,$addSearch,$removeSearch,$removeSelected);

/* Print Footer */
echo $OUTPUT->footer();
