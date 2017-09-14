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
 * Approval Request - Action Manager
 *
 * @package         enrol/waitinglist
 * @subpackage      approval
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    29/12/2015
 * @author          efaktor     (fbv)
 *
 * Description
 */
require('../../../config.php');
require_once('approvallib.php');

global $PAGE,$CFG,$OUTPUT;

/* PARAMS */
$contextSystem      = context_system::instance();
$returnUrl          = $CFG->wwwroot . '/index.php';
$url                = new moodle_url('/enrol/waitinglist/approval/action.php');
$infoManager        = null;
$infoRequest        = null;
$infolog            = null;
$confirmed          = null;
$infomssg           = null;
$strconfirm         = null;

$relativePath      = get_file_argument();
//extract relative path components
$args   = explode('/', ltrim($relativePath, '/'));

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($contextSystem);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

// Header
echo $OUTPUT->header();

if (count($args) != 4) {
    echo html_writer::start_tag('div',array('class' => 'loginerrors'));
    echo $OUTPUT->error_text('<h4>' . get_string('err_link','enrol_waitinglist') . '</h4>');
    echo html_writer::end_tag('div');
}else {
    // Data connected with the request
    $infoRequest  = Approval::get_notification_request($args);
    $infoManager  = Approval::get_request_manager($args[3]);

    $confirmed = $args[0];
    if ($confirmed == 1) {
        if ((!$infoRequest) || (!$infoManager)) {
            echo html_writer::start_tag('div',array('class' => 'loginerrors'));
            echo $OUTPUT->error_text('<h4>' . get_string('err_link','enrol_waitinglist') . '</h4>');
            echo html_writer::end_tag('div');
        }else {
            $strTitle = null;

            if (Approval::apply_action_from_manager($infoRequest,$infoManager)) {
                $user = get_complete_user_data('id',$infoRequest->userid);
                $infoNotification = new stdClass();
                $infoNotification->user = fullname($user);
                Approval::get_infocourse_notification($infoRequest->courseid,$infoNotification);

                switch ($infoRequest->action) {
                    case APPROVED_ACTION:
                        $strTitle = get_string('approved_mnd','enrol_waitinglist',$infoNotification);

                        break;
                    case REJECTED_ACTION:
                        $strTitle = get_string('rejected_mnd','enrol_waitinglist',$infoNotification);

                        break;
                }

                // Write log
                Approval::write_approval_log($infoRequest,$infoManager->managerid,true,FROM_MAIL);
            }else {
                $strTitle = get_string('err_process','enrol_waitinglist');
            }

            echo html_writer::start_tag('div');
            echo '<h4>' . $strTitle . '</h4>';
            echo html_writer::end_tag('div');
        }//if_request
    }else {
        if ((!$infoRequest) || (!$infoManager)) {
            echo html_writer::start_tag('div',array('class' => 'loginerrors'));
            echo $OUTPUT->error_text('<h4>' . get_string('err_link','enrol_waitinglist') . '</h4>');
            echo html_writer::end_tag('div');
        }else {
            // Write log
            Approval::write_approval_log($infoRequest,$infoManager->managerid,false,FROM_MAIL);

            // Message
            $infomssg = new stdClass();
            $infomssg->user     = $infoRequest->firstname . ' ' . $infoRequest->lastname;
            $infomssg->course   = $infoRequest->fullname;

            switch ($infoRequest->action) {
                case APPROVED_ACTION:
                    $strconfirm = get_string('confirm_approve','enrol_waitinglist',$infomssg);

                    break;
                case REJECTED_ACTION:
                    $strconfirm = get_string('confirm_reject','enrol_waitinglist',$infomssg);
                    break;
            }//action

            $relativePath = new moodle_url('/enrol/waitinglist/approval/applyact.php',array('r' => $args[1],'a' => $args[2],'t' => $args[3]));

            echo $OUTPUT->confirm($strconfirm,$relativePath,$returnUrl);
        }
    }//if_confirm
    /**





    **/
}//if_args


// Footer
echo $OUTPUT->footer();