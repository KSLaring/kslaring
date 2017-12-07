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
 * Unenrol Action
 *
 * @package         enrol/waitinglist
 * @subpackage      unenrol
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    29/12/2015
 * @author          efaktor     (fbv)
 *
 */
require('../../../config.php');
require_once('unenrollib.php');

global $CFG, $PAGE,$OUTPUT,$SITE,$USER;

/* PARAMS */
$contextSystem     = context_system::instance();
$returnUrl         = $CFG->wwwroot . '/index.php';
$url               = new moodle_url('/enrol/waitinglist/unenrol/unenrol.php');
$unenrol           = false;
$confirmed         = null;
$relativePath      = get_file_argument();
//extract relative path components
$args   = explode('/', ltrim($relativePath, '/'));

$PAGE->set_url($url);
$PAGE->set_context($contextSystem);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

// Checking access
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}

// Header
echo $OUTPUT->header();

// Check args and content
if (count($args) != 5) {
    echo html_writer::start_tag('div',array('class' => 'loginerrors'));
    echo $OUTPUT->error_text('<h4>' . get_string('err_link','enrol_waitinglist') . '</h4>');
    echo html_writer::end_tag('div');
}else {
    // Get confirm parameter
    $confirmed = $args[0];

    if ($confirmed == 1) {
        // Check if the user has already been unenrolled
        if (Unenrol_Waiting::IsUnenrolled($args)) {
            echo html_writer::start_tag('div',array('class' => 'loginerrors'));
            echo $OUTPUT->error_text('<h4>' . get_string('user_not_enrolled','enrol_waitinglist') . '</h4>');
            echo html_writer::end_tag('div');
        }else {
            // Check arguments for unenrol action
            $unenrol = Unenrol_Waiting::Check_UnenrolLink($args);
            if ($unenrol) {
                // Check Deadline for unenrol
                if (Unenrol_Waiting::Can_Unenrol($unenrol->userid,$unenrol->courseid,$unenrol->waitingid)) {
                    // Unrol user
                    if (Unenrol_Waiting::UnenrolUser($args)) {
                        echo html_writer::start_tag('div',array('class' => 'loginerrors'));
                        echo $OUTPUT->error_text('<h4>' . get_string('user_unenrolled','enrol_waitinglist') . '</h4>');
                        echo html_writer::end_tag('div');
                    }else {
                        echo html_writer::start_tag('div',array('class' => 'loginerrors'));
                        echo $OUTPUT->error_text('<h4>' . get_string('err_process','enrol_waitinglist') . '</h4>');
                        echo html_writer::end_tag('div');
                    }//Unrol_user
                }else {
                    echo html_writer::start_tag('div',array('class' => 'loginerrors'));
                    echo $OUTPUT->error_text('<h4>' . get_string('err_process','enrol_waitinglist') . '</h4>');
                    echo html_writer::end_tag('div');
                }//if_can_unrol
            }else {
                // Wrong link
                echo html_writer::start_tag('div',array('class' => 'loginerrors'));
                echo $OUTPUT->error_text('<h4>' . get_string('err_link','enrol_waitinglist') . '</h4>');
                echo html_writer::end_tag('div');
            }//if_lnk_unenrol
        }//is_unenrol
    }else {
        $params = array();
        $params['u']    = $args[1];
        $params['tu']   = $args[2];
        $params['c']    = $args[3];
        $params['tc']   = $args[4];

        // Ask for confirmation
        $co = get_course($args[3]);
        $infomssg = new stdClass();
        $infomssg->course = $co->fullname;
        $strconfirm   = get_string('confirm_unrol','enrol_waitinglist',$infomssg);
        $relativePath = new moodle_url('/enrol/waitinglist/unenrol/applyunrol.php',$params);

        echo $OUTPUT->confirm($strconfirm,$relativePath,$returnUrl);
    }//if_confirm
}

/* Print Footer */
echo $OUTPUT->footer();