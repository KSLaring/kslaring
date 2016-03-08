<?php
/**
 * Approval Request - Action Manager
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
$contextSystem = CONTEXT_SYSTEM::instance();
$returnUrl         = $CFG->wwwroot . '/index.php';
$url               = new moodle_url('/enrol/waitinglist/approval/action.php');

$relativePath      = get_file_argument();
//extract relative path components
$args   = explode('/', ltrim($relativePath, '/'));

$PAGE->set_url($url);
$PAGE->set_context($contextSystem);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

/* Print Header */
echo $OUTPUT->header();

if (count($args) != 2) {
    echo html_writer::start_tag('div',array('class' => 'loginerrors'));
    echo $OUTPUT->error_text('<h4>' . get_string('err_link','enrol_waitinglist') . '</h4>');
    echo html_writer::end_tag('div');
}else {
    $infoRequest = Approval::Get_NotificationRequest($args);
    if (!$infoRequest) {
        echo html_writer::start_tag('div',array('class' => 'loginerrors'));
        echo $OUTPUT->error_text('<h4>' . get_string('err_link','enrol_waitinglist') . '</h4>');
        echo html_writer::end_tag('div');
    }else {
        $strTitle = null;

        if (Approval::ApplyAction_FromManager($infoRequest)) {
            $user = get_complete_user_data('id',$infoRequest->userid);
            $infoNotification = new stdClass();
            $infoNotification->user = fullname($user);
            Approval::GetInfoCourse_Notification($infoRequest->courseid,$infoNotification);

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

        echo html_writer::start_tag('div');
        echo '<h4>' . $strTitle . '</h4>';
        echo html_writer::end_tag('div');
    }
}//if_args


/* Print Footer */
echo $OUTPUT->footer();