<?php
/**
 * Inconsistencies Course Completions  - Show Table Users Inconsistencies
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

/* PARAMS   */
$courseID       = required_param('id',PARAM_INT);
$url            = new moodle_url('/local/icp/show.php',array('id' => $courseID));
$urlIndex       = new moodle_url('/local/icp/index.php',array('id' => $courseID));
$urlClean       = new moodle_url('/local/icp/clean.php',array('id' => $courseID));
$return         = new moodle_url('/course/view.php',array('id' => $courseID));

$totalCompleted     = null;
$totalNotCompleted  = null;
$tableInfo          = null;

$context        = context_system::instance();
$contextCourse  = context_course::instance($courseID);
require_login($courseID);


require_capability('local/icp:manage',$contextCourse);

$PAGE->set_url($url);
$PAGE->set_context($contextCourse);
$PAGE->set_pagelayout('course');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('title_index','local_icp'),$urlIndex);
$PAGE->navbar->add(get_string('users_inconsistencies','local_icp'),$url);

/* Total Completed Inconsistencies      */
$totalCompleted     = InconsistenciesCompletions::GetTotalUsers_CompletedWithInconsistencies($courseID);
/* Total Not Completed Inconsistencies  */
$totalNotCompleted  = InconsistenciesCompletions::GetTotalUsers_NotCompletedWithInconsistencies($courseID);

/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('users_inconsistencies','local_icp'));

if (!$totalCompleted && !$totalNotCompleted) {
    echo $OUTPUT->notification(get_string('none_inconsistencies','local_icp'), 'notifysuccess');
    echo $OUTPUT->continue_button($return);
}else {
    $tableInfo = InconsistenciesCompletions::Get_TableInfo($totalCompleted,$totalNotCompleted);
    echo html_writer::tag('div', html_writer::table($tableInfo), array('class'=>'flexible-wrap'));

    echo $OUTPUT->action_link($urlClean,get_string('clean','local_icp'));
    echo "</br>";
}

/* Footer   */
echo $OUTPUT->footer();
