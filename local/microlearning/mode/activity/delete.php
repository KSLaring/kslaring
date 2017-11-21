<?php
/**
 * Micro Learning - Activity Mode Page (Delete)
 *
 * @package         local/microlearnig
 * @subpackage      mode/activity
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      12/09/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../../config.php');
require_once('activitymodelib.php');
require_once('../../microlearninglib.php');

global $PAGE,$USER,$OUTPUT,$SITE,$SESSION,$CFG;

// Params
$course_id      = required_param('id',PARAM_INT);
$campaign_id    = required_param('cp',PARAM_INT);
$campaign_name  = required_param('cp_name',PARAM_TEXT);

$context        = context_system::instance();
$context_course = context_course::instance($course_id);
$course         = get_course($course_id);

$url            = new moodle_url('/local/microlearning/mode/activity/delete.php',array('id'=>$course_id,'cp' => $campaign_id, 'cp_name' => $campaign_name));
$return_url     = new moodle_url('/local/microlearning/index.php',array('id'=>$course_id));

// Checking access
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}
if (!has_capability('local/microlearning:manage',$context)) {
    if (!Micro_Learning::HasPermissions($course_id,$USER->id)) {
        print_error('nopermissions', 'error', '', 'local/microlearning:manage');
    }
}
require_login($course);

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($context_course);
$PAGE->set_pagelayout('course');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('title_index','local_microlearning'),$return_url);
$PAGE->navbar->add(get_string('title_calendar','local_microlearning'));
$PAGE->navbar->add(get_string('delete') . ' ' . $campaign_name,$url);

echo $OUTPUT->header();

if (Activity_Mode::Delete_ActivityMode($campaign_id,$course_id)) {
    // Delete
    echo $OUTPUT->notification(get_string('deleted_campaign','local_microlearning',$campaign_name), 'notifysuccess');
    echo $OUTPUT->continue_button($return_url);
}else {
    echo $OUTPUT->notification(get_string('error_deleted_campaign','local_microlearning',$campaign_name), 'notifysuccess');
    echo $OUTPUT->continue_button($return_url);
}//if_else_delete

echo $OUTPUT->footer();