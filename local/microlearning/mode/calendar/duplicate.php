<?php
/**
 * Micro Learning Deliveries    - Duplicate Calendar Campaign
 *
 * @package         local/microlearnig
 * @subpackage      mode/calendar
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      21/11/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../../config.php');
require_once('../../microlearninglib.php');
require_once('calendarmodelib.php');
require_once('duplicate_form.php');

global $PAGE,$USER,$OUTPUT,$SITE,$SESSION,$CFG;

// Params
$course_id      = required_param('id',PARAM_INT);
$campaign_id    = required_param('cp',PARAM_INT);

$context        = context_system::instance();
$context_course = context_course::instance($course_id);
$course         = get_course($course_id);
$error          = false;

$url                = new moodle_url('/local/microlearning/mode/calendar/duplicate.php',array('id'=>$course_id,'cp' => $campaign_id));
$return_url         = new moodle_url('/local/microlearning/index.php',array('id'=>$course_id));

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
$PAGE->navbar->add(get_string('title_duplicate','local_microlearning'));

// Form
$form = new duplicate_calendar_form(null,array($course_id,$campaign_id));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if ($data = $form->get_data()) {
    try {
        // Duplicate campaign
        $new_campaign = Calendar_Mode::DuplicateCampaign($data);

        // Return Calendar Deliveries
        $return_delivery    = new moodle_url('/local/microlearning/mode/calendar/calendar_deliveries.php',array('id'=>$course_id,'mode' => CALENDAR_MODE,'cp' => $new_campaign));
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

/* Foot*/
echo $OUTPUT->footer();