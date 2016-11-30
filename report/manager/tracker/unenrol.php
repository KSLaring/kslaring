<?php
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

require_once('../../../config.php');
require_once('trackerlib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS */
$courseID       = required_param('id',PARAM_INT);
$unWait         = optional_param('w',0,PARAM_INT);

$confirmed      = optional_param('confirm', false, PARAM_BOOL);
$url            = new moodle_url('/report/manager/tracker/unenrol.php',array('id' => $courseID,'w' => $unWait));
$confirmUrl     = new moodle_url('/report/manager/tracker/unenrol.php',array('id' => $courseID,'confirm' => true,'w' => $unWait));
$returnUrl      = new moodle_url($CFG->wwwroot . '/index.php');
$siteContext    = context_system::instance();
$message        = null;

/* Start the page */
$PAGE->https_required();

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$returnUrl);

$PAGE->verify_https_required();


/* Print Header */
echo $OUTPUT->header();

if ($confirmed) {
    if ($unWait) {
        /* Remove from waiting list */
        /* Unenrol */
        if(TrackerManager::unwait_from_course($courseID,$USER->id)) {
            $message = get_string('exit_unwait','report_manager');
        }else {
            $message = get_string('err_unenrol','report_manager');
        }//if_else
    }else {
        /* Unenrol */
        if(TrackerManager::unenrol_from_course($courseID,$USER->id)) {
            $message = get_string('exit_unenrol','report_manager');
        }else {
            $message = get_string('err_unenrol','report_manager');
        }//if_else
    }

    echo $OUTPUT->notification($message, 'notifysuccess');
    echo $OUTPUT->continue_button($returnUrl);
}else {
    /* First Confirm    */
    $course = get_course($courseID);
    if ($unWait) {
        /* Remove from waiting list*/
        $message = get_string('unwaitconfirm', 'report_manager', array('user'=>fullname($USER, true), 'course'=>format_string($course->fullname)));
    }else {
        $message = get_string('unenrolconfirm', 'core_enrol', array('user'=>fullname($USER, true), 'course'=>format_string($course->fullname)));    
    }
    
    echo $OUTPUT->confirm($message,$confirmUrl,$returnUrl);
}//if_else

/* Print Footer */
echo $OUTPUT->footer();