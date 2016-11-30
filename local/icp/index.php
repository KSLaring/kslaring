<?php
/**
 * Inconsistencies Course Completions  - Index
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
require_once('index_form.php');

/* PARAMS   */
$courseID       = optional_param('id',0,PARAM_INT);
$url            = new moodle_url('/local/icp/index.php',array('id' => $courseID));
$return         = new moodle_url('/course/view.php',array('id' => $courseID));
$show           = new moodle_url('/local/icp/show.php',array('id' => $courseID));

$courseInfo             = null;
$users                  = null;
$usersInconsistencies   = null;

$context        = context_system::instance();
$contextCourse  = context_course::instance($courseID);
require_login($courseID);


require_capability('local/icp:manage',$contextCourse);

$PAGE->set_url($url);
$PAGE->set_context($contextCourse);
$PAGE->set_pagelayout('course');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

/* Add Form */
$form = new inconsistencies_start_form(null,$courseID);
if ($form->is_cancelled()) {
    redirect($return);
}else if ($data = $form->get_data()) {
    /* Get Info Course  */
    $courseInfo             = InconsistenciesCompletions::Get_InfoCourseCompletion($data->id);
    /* Get Users        */
    $users                  = InconsistenciesCompletions::Get_Users($data->id);

    /* Get Users With Inconsistencies   */
    $usersInconsistencies   = InconsistenciesCompletions::Users_WithInconsistencies($users,$courseInfo);

    if ($usersInconsistencies) {
        redirect($show);
    }else {
        /* Header   */
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('title_index','local_icp'));

        echo $OUTPUT->notification(get_string('none_inconsistencies','local_icp'), 'notifysuccess');
        echo $OUTPUT->continue_button($return);
        /* Footer   */
        echo $OUTPUT->footer();
        die();
    }
}//if_else

/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('title_index','local_icp'));


if (InconsistenciesCompletions::ExistUsers_ToClean($courseID)) {
    echo $OUTPUT->notification(get_string('still_inconsistencies','local_icp'), 'notifysuccess');
    echo $OUTPUT->continue_button($show);
}else {
    $form->display();
}

/* Footer   */
echo $OUTPUT->footer();

