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

/* PARAMS   */
$courseId          = required_param('courseid',PARAM_INT);
$enrolId           = required_param('id',PARAM_INT);

$course             = get_course($courseId);
$context_course     = context_course::instance($courseId);
$return_url         = new moodle_url('/course/view.php',array('id' => $courseId));
$url                = new moodle_url('/enrol/waitinglist/approval/request.php',array('courseid' => $courseId));

require_login($course);

/* Capability   */
require_capability('enrol/waitinglist:manage',$context_course);

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($context_course);
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
echo $OUTPUT->heading(get_string('title_approval','enrol_waitinglist'));

echo "Sorry, we are working on it";

/* Print Footer */
echo $OUTPUT->footer();