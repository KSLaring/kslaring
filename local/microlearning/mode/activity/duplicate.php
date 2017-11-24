<?php
/**
 * Micro Learning Deliveries    - Duplicate Activity Campaign
 *
 * @package         local/microlearnig
 * @subpackage      mode/activity
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      21/11/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../../config.php');
require_once('../../microlearninglib.php');
require_once('activitymodelib.php');
require_once('duplicate_form.php');

global $PAGE,$USER,$OUTPUT,$SITE,$SESSION,$CFG;

// Params
$course_id      = required_param('id',PARAM_INT);
$campaign_id    = required_param('cp',PARAM_INT);

$context        = context_system::instance();
$context_course = context_course::instance($course_id);
$course         = get_course($course_id);

$url                = new moodle_url('/local/microlearning/mode/activity/duplicate.php',array('id'=>$course_id,'cp' => $campaign_id));
$return_url         = new moodle_url('/local/microlearning/index.php',array('id'=>$course_id));
$error              = false;

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
$PAGE->navbar->add(get_string('title_activity','local_microlearning'));
$PAGE->navbar->add(get_string('title_duplicate','local_microlearning'));

// Form
$form = new duplicate_activity_form(null,array($course_id,$campaign_id));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if ($data = $form->get_data()) {
    try {
        // Duplicate Campaign
        $new_campaign = Activity_Mode::DuplicateCampaign($data);

        // Return Activity Deliveries
        $return_delivery    = new moodle_url('/local/microlearning/mode/activity/activity_deliveries.php',array('id'=>$course_id,'mode' => ACTIVITY_MODE,'cp' => $new_campaign));
        $_POST = array();
        redirect($return_delivery);
    }catch (Exception $ex) {
        $error = true;
    }//try_catch
}//if_form


// Header
echo $OUTPUT->header();

if ($error) {
    echo $OUTPUT->notification(get_string('err_generic','local_microlearning'), 'notifysuccess');
    echo $OUTPUT->continue_button($return_url);
}else {
    $form->display();
}

// Footer
echo $OUTPUT->footer();