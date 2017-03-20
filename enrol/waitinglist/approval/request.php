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
$courseId          = required_param('courseid',PARAM_INT);
$enrolId           = required_param('id',PARAM_INT);

$course             = get_course($courseId);
$context_course     = context_course::instance($courseId);
$return_url         = new moodle_url('/course/view.php',array('id' => $courseId));
$url                = new moodle_url('/enrol/waitinglist/approval/request.php',array('courseid' => $courseId));
$approvalRequests   = null;

/* Capability   */
if (!has_capability('enrol/waitinglist:manage',$context_course)) {
    require_login();
}else {
    require_login($course);
}//if_capabilities

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('admin');
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

/*  Get Requests    */
$approvalRequests = Approval::approval_requests($courseId,$enrolId);

/* Show Report      */
echo Approval::display_approval_requests($approvalRequests);

/* Print Footer */
echo $OUTPUT->footer();