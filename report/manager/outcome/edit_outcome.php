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

global $CFG,$SESSION,$PAGE,$USER,$SITE,$OUTPUT;

require_once('../../../config.php');
require_once( 'outcomelib.php');
require_once('edit_outcome_form.php');
require_once($CFG->libdir . '/adminlib.php');

// Params
$outcome_id     = required_param('id', PARAM_INT);
$expiration_id  = optional_param('expid', 0, PARAM_INT);
$removeSelected = optional_param_array('removeselect',0,PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);
$url        = new moodle_url('/report/manager/outcome/edit_outcome.php',array('id' => $outcome_id));
$return     = new moodle_url('/report/manager/index.php');
$return_url = new moodle_url('/report/manager/outcome/outcome.php');
$site_context   = CONTEXT_SYSTEM::instance();

// Page settings
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return_url);
$PAGE->navbar->add(get_string('outcome', 'report_manager'),$return);
$PAGE->navbar->add(get_string('edit_outcome', 'report_manager'));

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
if (!has_capability('report/manager:edit', $site_context)) {
    print_error('nopermissions', 'error', '', 'report/manager:edit');
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

// Form
$form = new manager_edit_outcome_form(null,array($outcome_id,$expiration_id,$addSearch,$removeSearch,$removeSelected));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    if (!empty($data->add_sel)) {
        if (isset($data->addselect)) {
            // get data
            $infoOutcome = new stdClass();
            $infoOutcome->outcomeid         = $data->id;
            $infoOutcome->expirationperiod  = $data->expiration_period;
            $infoOutcome->modified          = time();

            if ($data->expid) {
                // Update
                $infoOutcome->id = $data->expid;
                outcome::Update_Outcome($infoOutcome,$data->addselect);
            }else {
                // ad
                outcome::Insert_Outcome($infoOutcome,$data->addselect);
            }//if_else
        }//if_addselect
    }if (!empty($data->remove_sel)) {
        if (isset($data->removeselect)) {
           outcome::Delete_JR_Outcome($data->id,$data->removeselect);
        }
    }

    if ((isset($data->submitbutton) && $data->submitbutton)) {
        // Get data
        $outcome = new stdClass();
        $outcome->outcomeid         = $data->id;
        $outcome->expirationperiod  = $data->expiration_period;
        $outcome->modified          = time();

        if ($expiration_id) {
            // Update
            $outcome->id = $data->expid;
            outcome::Update_Outcome($outcome,null);
        }else {
            // Add
            outcome::Insert_Outcome($outcome,null);
        }//if_else

        $_POST = array();
        redirect($return_url);
    }//if_submit_button

    $_POST = array();
}//if_else

$PAGE->verify_https_required();

// Header
echo $OUTPUT->header();

$form->display();

// Initialise selectors
outcome::Init_JobRoles_Selectors($outcome_id,$addSearch,$removeSearch,$removeSelected);

// Footer
echo $OUTPUT->footer();
