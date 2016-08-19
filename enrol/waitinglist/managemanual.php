<?php
/**
 * Waiting List - Manual submethod
 *
 * @package         enrol/waitinglist
 * @subpackage
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    17/08/2016
 * @author          efaktor     (fbv)
 *
 * Description
 */
require('../../config.php');
require_once('lib.php');
require_once('classes/method/manual/managemanual_form.php');
require_once('classes/method/manual/enrolmethodmanual.php');

/* PARAMS   */
$instanceId     = required_param('id',PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);
//$manualClass    = 'enrol_waitinglist\method\manual\enrolmethodmanual';

$instance   = $DB->get_record('enrol', array('id' => $instanceId));
$course     = $DB->get_record('course',array('id' => $instance->courseid), '*', MUST_EXIST);
$context    = context_course::instance($course->id, MUST_EXIST);
$url        = new moodle_url('/enrol/waitinglist/managemanual.php',array('id' => $instanceId));
$return     = new moodle_url('/enrol/instances.php', array('id'=>$course->id));

require_login($course);
require_capability('enrol/waitinglist:config', $context);

/* PAGE */
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managemethods', 'enrol_waitinglist'));
$PAGE->set_heading($course->fullname);

if (!enrol_is_enabled('waitinglist')) {
    redirect($return);
}

/* Show Form */
$form       = new managemanual_form(null,array($instance,$course->id,$addSearch,$removeSearch));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return);
}else if($data = $form->get_data()) {
    /* Enrol Users    */
    if (!empty($data->add_sel)) {
        if (isset($data->addselect)) {
            $manualClass = new enrol_waitinglist\method\manual\enrolmethodmanual();
            $manualClass->EnrolUsers($data->addselect,$instance);
        }//if_addselect
    }//if_add

    /* Unenrol Users  */
    if (!empty($data->remove_sel)) {
        if (isset($data->removeselect)) {
            $manualClass = new enrol_waitinglist\method\manual\enrolmethodmanual();
            $manualClass->UnenrolUsers($data->removeselect,$instance);
        }//if_removeselect
    }//if_remove

    $form  = new managemanual_form(null,array($instance,$course->id,$addSearch,$removeSearch));
}//if_else

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manual_displayname', 'enrol_waitinglist'));

$form->display();

/* Initialise Selectors */
enrol_waitinglist\method\manual\enrolmethodmanual::Init_ManualSelectors($instance->id,$course->id,$addSearch,$removeSearch);

echo $OUTPUT->footer();


