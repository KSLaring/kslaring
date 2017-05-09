<?php
/**
 * Approval Request - Action Request Admin
 *
 * @package         enrol/waitinglist
 * @subpackage      approval
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    29/12/2015
 * @author          efaktor     (fbv)
 *
 * Description
 */
require('../../../config.php');
require_once('approvallib.php');

/* PARAMS */
global $USER,$PAGE,$OUTPUT;
$courseId       = required_param('co',PARAM_INT);
$userId         = required_param('id',PARAM_INT);
$action         = required_param('act',PARAM_INT);
$waitingId      = required_param('ea',PARAM_INT);
$return         = new moodle_url('/enrol/waitinglist/approval/request.php',array('courseid' => $courseId,'id' => $waitingId));
$url            = new moodle_url('/enrol/waitinglist/approval/act_request.php',array('co' => $courseId,'id' => $userId, 'ea' => $waitingId, 'act' => $action));
$contextCourse  = CONTEXT_COURSE::instance($courseId);
$user           = null;

require_login();

/* Capability   */
require_capability('enrol/waitinglist:manage',$contextCourse);

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('admin');
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

/* Get Request */
$infoRequest = Approval::get_request($userId,$courseId,$waitingId);
$infoRequest->action = $action;
// Get manager connection
if (is_siteadmin($USER->id)) {
    // Create a special entry for the admin
    $infoManager = Approval::add_approval_entry_admin($infoRequest->id,$USER->id);
}else {
    $infoManager  = Approval::get_request_manager(null,$USER->id);
}//if_else


$strTitle = null;
if (Approval::apply_action_from_manager($infoRequest,$infoManager)) {
    $user = get_complete_user_data('id',$userId);
    $infoNotification = new stdClass();
    $infoNotification->user = fullname($user);
    Approval::get_infocourse_notification($courseId,$infoNotification);

    switch ($infoRequest->action) {
        case APPROVED_ACTION:
            $strTitle = get_string('approved_mnd','enrol_waitinglist',$infoNotification);

            break;
        case REJECTED_ACTION:
            $strTitle = get_string('rejected_mnd','enrol_waitinglist',$infoNotification);

            break;
    }
}else {
    $strTitle = get_string('err_process','enrol_waitinglist');
}

echo $OUTPUT->notification($strTitle, 'notifysuccess');
echo $OUTPUT->continue_button($return);

/* Print Footer */
echo $OUTPUT->footer();

