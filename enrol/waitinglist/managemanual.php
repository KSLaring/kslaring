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
 * Waiting List - Manual submethod
 *
 * @package         enrol
 * @subpackage      waitinglist
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    17/08/2016
 * @author          efaktor     (fbv)
 *
 */
global $CFG,$SITE,$PAGE,$OUTPUT,$DB;

require('../../config.php');
require_once('lib.php');
require_once('classes/method/manual/managemanual_form.php');
require_once('classes/method/manual/enrolmethodmanual.php');
require_once($CFG->dirroot . '/report/manager/managerlib.php');

// Params
$instanceId     = required_param('id',PARAM_INT);
$courseId       = required_param('co',PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);
$removeSelected = optional_param_array('removeselect',0,PARAM_INT);
$addSelected    = optional_param_array('addselect',0,PARAM_INT);
$course         = null;
$instance       = null;

// Get data connected with
$instance   = $DB->get_record('enrol', array('id' => $instanceId,'enrol' => 'waitinglist'));
$course     = $DB->get_record('course',array('id' => $courseId), '*', MUST_EXIST);


$context    = context_course::instance($course->id, MUST_EXIST);
$url        = new moodle_url('/enrol/waitinglist/managemanual.php',array('id' => $instanceId,'co' => $courseId));
$return     = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
$isInvoice  = false;

require_login($course);
require_capability('enrol/waitinglist:config', $context);

// Page settings
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managemethods', 'enrol_waitinglist'));
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);

if (!enrol_is_enabled('waitinglist')) {
    redirect($return);
}

// Form
$form       = new managemanual_form(null,array($instance,$course->id,$addSearch,$removeSearch,$addSelected,$removeSelected));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return);
}else if($data = $form->get_data()) {
    // Enrol users
    if (!empty($data->add_sel)) {
        if ($addSelected) {
            $manualClass = new enrol_waitinglist\method\manual\enrolmethodmanual();
            $manualClass->EnrolUsers($addSelected,$instance,$data->level_3);
        }//if_addselect
    }//if_add

    // Unenrol users
    if (!empty($data->remove_sel)) {
        if ($removeSelected) {
            $manualClass = new enrol_waitinglist\method\manual\enrolmethodmanual();
            $manualClass->UnenrolUsers($removeSelected,$instance);
        }//if_removeselect
    }//if_remove

    $form       = new managemanual_form(null,array($instance,$course->id,$data->addselect_searchtext,$data->removeselect_searchtext,$addSelected,$removeSelected));
    $_POST = array();
}//if_else

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manual_displayname', 'enrol_waitinglist'));

$form->display();

// Initialize selectors
enrol_waitinglist\method\manual\enrolmethodmanual::Init_ManualSelectors($instance->id,$course->id,$addSearch,$removeSearch);
if ($instance->{ENROL_WAITINGLIST_FIELD_APPROVAL} != COMPANY_NO_DEMANDED) {
    if ($instance->{ENROL_WAITINGLIST_FIELD_INVOICE}) {
        $isInvoice = true;
    }
    enrol_waitinglist\method\manual\enrolmethodmanual::Init_Organization_Structure(true,$isInvoice);
}

echo $OUTPUT->footer();


