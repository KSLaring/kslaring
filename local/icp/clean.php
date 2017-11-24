<?php
/**
 * Inconsistencies Course Completions  - Clean Inconsistencies
 *
 * @package         local
 * @subpackage      icp
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    25/05/2015
 * @author          eFaktor     (fbv)
 */

require_once('../../config.php');
require_once('icplib.php');

global $USER,$PAGE,$SITE,$OUTPUT,$CFG;

// Params
$courseID       = required_param('id',PARAM_INT);
$confirmed      = optional_param('co', false, PARAM_BOOL);
$url            = new moodle_url('/local/icp/clean.php',array('id' => $courseID));
$urlConfirm     = new moodle_url('/local/icp/clean.php',array('id' => $courseID,'co' => true));
$urlShow        = new moodle_url('/local/icp/show.php',array('id' => $courseID));
$urlIndex       = new moodle_url('/local/icp/index.php',array('id' => $courseID));
$return         = new moodle_url('/course/view.php',array('id' => $courseID));
$courseInfo     = null;
$cleaned        = null;

$context        = context_system::instance();
$contextCourse  = context_course::instance($courseID);

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
require_login($courseID);

require_capability('local/icp:manage',$contextCourse);

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($contextCourse);
$PAGE->set_pagelayout('course');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('title_index','local_icp'),$urlIndex);
$PAGE->navbar->add(get_string('users_inconsistencies','local_icp'),$urlShow);
$PAGE->navbar->add(get_string('clean','local_icp'),$url);

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('clean','local_icp'));

if ($confirmed) {
    // Get info course
    $courseInfo             = InconsistenciesCompletions::Get_InfoCourseCompletion($courseID);

    // Clean inconsistencies
    $cleaned = InconsistenciesCompletions::CleanInconsistencies($courseID,$courseInfo);
    if ($cleaned) {
        echo $OUTPUT->notification(get_string('inconsistencies_cleaned','local_icp'), 'notifysuccess');
        echo $OUTPUT->continue_button($return);
    }else {
        echo $OUTPUT->notification(get_string('err_process','local_icp'), 'notifysuccess');
        echo $OUTPUT->continue_button($return);
    }//ifCleaned
}else {
    // First confirm
    echo $OUTPUT->confirm(get_string('delete_are_you_sure','local_icp'),$urlConfirm,$return);
}//if_confirmed

// Footer
echo $OUTPUT->footer();