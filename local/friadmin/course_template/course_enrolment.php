<?php

/**
 * Course Template - Enrolment Methods
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/01/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Course create form template. Enrolment Methods
 */

require_once('../../../config.php');
require_once('lib/coursetemplatelib.php');
require_once('classes/ct_enrolment_form.php');
require_once('../../../course/lib.php');

require_login();

/* PARAMS   */
$courseId       = required_param('id',PARAM_INT);
$waitinglist    = optional_param('waitinglist',0,PARAM_INT);
$contextCourse  = CONTEXT_COURSE::instance($courseId);
$url            = new moodle_url('/local/friadmin/course_template/course_enrolment.php',array('id' => $courseId));
$returnUrl = new moodle_url('/local/friadmin/course_template/course_template.php',array('id' => $courseId));

$course         = get_course($courseId);
$strTitle       = get_string('coursetemplate_title', 'local_friadmin');
$strSubTitle    = get_string('course_enrolment', 'local_friadmin');
$instance       = null;


/* Check Permissions/Capability */
if (!has_capability('local/friadmin:view',CONTEXT_SYSTEM::instance())) {
    if (!local_friadmin_helper::CheckCapabilityFriAdmin()) {
        print_error('nopermissions', 'error', '', 'block/frikomport:view');
    }//if_superuser
}

$PAGE->set_url($url);
$PAGE->set_context($contextCourse);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_friadmin'));
$PAGE->navbar->add($strTitle);
$PAGE->navbar->add($strSubTitle);


/* Form */
if ($waitinglist) {
    /* Get Enrol Instance */
    $instance   = CourseTemplate::GetEnrolInstance($courseId,$waitinglist);
    $form       = new ct_enrolment_settings_form(null,array($courseId,$waitinglist,$instance));
}else {
    $form = new ct_enrolment_form(null,$courseId);
}

if ($form->is_cancelled()) {
    if (isset($SESSION->fakepermission)) {
        CourseTemplate::Delete_FakePermission($fakepermission->id);
    }

    $_POST = array();
    redirect($returnUrl);
}else if ($data = $form->get_data()) {

    if ($waitinglist) {
        if ($data->waitinglistid) {
            /* Update   */
            CourseTemplate::UpdateWaitingEnrolment($data);

        }else {
            /* New      */
            CourseTemplate::CreateWaitingEnrolment($data);
        }

        if (isset($SESSION->fakepermission)) {
            CourseTemplate::Delete_FakePermission($fakepermission->id);
        }

        redirect($returnUrl);
    }
}

/* Header   */
echo $OUTPUT->header();

echo $OUTPUT->heading($strTitle,2);
echo $OUTPUT->heading($strSubTitle,3);

$form->display();

/* Footer   */
echo $OUTPUT->footer();


