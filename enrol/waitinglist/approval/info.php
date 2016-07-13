<?php
/**
 * Approval Request
 *
 * @package         enrol/waitinglist
 * @subpackage      approval
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    24/12/2015
 * @author          efaktor     (fbv)
 *
 * Description
 */
require('../../../config.php');
require_once('approvallib.php');

/* PARAMS   */
$userId     = required_param('id',PARAM_INT);
$courseId   = required_param('co',PARAM_INT);
$seats      = required_param('se',PARAM_INT);

$course             = get_course($courseId);
$contextCourse      = context_course::instance($courseId);
$returnUrl          = new moodle_url('/course/view.php',array('id' => $courseId));
$url                = new moodle_url('/enrol/waitinglist/approval/info.php',array('courseid' => $courseId));
$strMessage         = null;

require_login();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_context($contextCourse);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);


if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('enrolmentoptions','enrol'));

echo '<h3>' . $course->fullname . '</h3>';

if ($seats) {
    $strMessage = get_string('request_sent','enrol_waitinglist');
}else {
    $strMessage = get_string('approval_occupied','enrol_waitinglist');
}
echo $OUTPUT->notification($strMessage, 'notifysuccess');
echo $OUTPUT->continue_button($returnUrl);

/* Print Footer */
echo $OUTPUT->footer();

