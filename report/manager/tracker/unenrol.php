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
 * Report Competence Manager  - unenrol.
 *
 * @package         report
 * @subpackage      manager/tracker
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    20/11/2015
 * @author          eFaktor     (fbv)
 *
 */
global $CFG,$SITE,$PAGE,$OUTPUT,$USER;

require_once('../../../config.php');
require_once('trackerlib.php');
require_once($CFG->libdir . '/adminlib.php');

// Params
$courseID       = required_param('id',PARAM_INT);
$unWait         = optional_param('w',0,PARAM_INT);

$confirmed      = optional_param('confirm', false, PARAM_BOOL);
$url            = new moodle_url('/report/manager/tracker/unenrol.php',array('id' => $courseID,'w' => $unWait));
$confirmUrl     = new moodle_url('/report/manager/tracker/unenrol.php',array('id' => $courseID,'confirm' => true,'w' => $unWait));
$returnUrl      = new moodle_url($CFG->wwwroot . '/index.php');
$siteContext    = context_system::instance();
$message        = null;

// Page settings
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$returnUrl);
$PAGE->verify_https_required();

// Checking access
require_login();
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

if ($confirmed) {
    if ($unWait) {
        // Remove from waiting list
        // Unrol
        if(TrackerManager::unwait_from_course($courseID,$USER->id)) {
            $message = get_string('exit_unwait','report_manager');
        }else {
            $message = get_string('err_unenrol','report_manager');
        }//if_else
    }else {
        //Unrol
        if(TrackerManager::unenrol_from_course($courseID,$USER->id)) {
            $message = get_string('exit_unenrol','report_manager');
        }else {
            $message = get_string('err_unenrol','report_manager');
        }//if_else
    }

    echo $OUTPUT->notification($message, 'notifysuccess');
    echo $OUTPUT->continue_button($returnUrl);
}else {
    // First confirm
    $course = get_course($courseID);
    if ($unWait) {
        // Remove from waiting list
        $message = get_string('unwaitconfirm', 'report_manager', array('user'=>fullname($USER, true), 'course'=>format_string($course->fullname)));
    }else {
        $message = get_string('unenrolconfirm', 'core_enrol', array('user'=>fullname($USER, true), 'course'=>format_string($course->fullname)));    
    }
    
    echo $OUTPUT->confirm($message,$confirmUrl,$returnUrl);
}//if_else

// Footer
echo $OUTPUT->footer();