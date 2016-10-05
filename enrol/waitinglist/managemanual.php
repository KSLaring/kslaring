<?php
/**
 * Waiting List - Manual submethod
 *
 * @package         enrol
 * @subpackage      waitinglist
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    17/08/2016
 * @author          efaktor     (fbv)
 *
 */
require('../../config.php');
require_once('lib.php');
require_once('classes/method/manual/managemanual_form.php');
require_once('classes/method/manual/enrolmethodmanual.php');
require_once($CFG->dirroot . '/report/manager/managerlib.php');

/* PARAMS   */
$instanceId     = optional_param('id',0,PARAM_INT);
$courseId       = optional_param('co',0,PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);
$removeSelected = optional_param_array('removeselect',0,PARAM_INT);
$addSelected    = optional_param_array('addselect',0,PARAM_INT);
$isInvoice      = false;

if ($instanceId) {
    $instance   = $DB->get_record('enrol', array('id' => $instanceId));
    $course     = $DB->get_record('course',array('id' => $instance->courseid), '*', MUST_EXIST);
}else if ($courseId) {
    $course     = $DB->get_record('course',array('id' => $courseId), '*', MUST_EXIST);
    $instance   = $DB->get_record('enrol', array('courseid' => $courseId,'enrol' => 'waitinglist'));
}

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
$PAGE->set_context($context);

if (!enrol_is_enabled('waitinglist')) {
    redirect($return);
}

/* Show Form */
$form       = new managemanual_form(null,array($instance,$course->id,$addSearch,$removeSearch,$addSelected,$removeSelected));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return);
}else if($data = $form->get_data()) {
    /* Enrol Users    */
    if (!empty($data->add_sel)) {
        if ($addSelected) {
            $manualClass = new enrol_waitinglist\method\manual\enrolmethodmanual();
            $manualClass->EnrolUsers($addSelected,$instance,$data->level_3);
        }//if_addselect
    }//if_add

    /* Unenrol Users  */
    if (!empty($data->remove_sel)) {
        if ($removeSelected) {
            $manualClass = new enrol_waitinglist\method\manual\enrolmethodmanual();
            $manualClass->UnenrolUsers($removeSelected,$instance);
        }//if_removeselect
    }//if_remove

    $_POST = array();
}//if_else

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manual_displayname', 'enrol_waitinglist'));

$form->display();

/* Initialise Selectors */
enrol_waitinglist\method\manual\enrolmethodmanual::Init_ManualSelectors($instance->id,$course->id,$addSearch,$removeSearch);
if ($instance->{ENROL_WAITINGLIST_FIELD_APPROVAL} != COMPANY_NO_DEMANDED) {
    echo "1";
    if ($instance->{ENROL_WAITINGLIST_FIELD_INVOICE}) {
        $isInvoice = true;
    }else {
        $isInvoice = false;
    }
    echo "--> " . $instance->{ENROL_WAITINGLIST_FIELD_INVOICE} . "</br>";
    echo "Invoice : " . $isInvoice . "</br>";
    enrol_waitinglist\method\manual\enrolmethodmanual::Init_Organization_Structure(true,$isInvoice);
}else {
    echo "2";
}

echo $OUTPUT->footer();


