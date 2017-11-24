<?php
/**
 * Micro Learning - Activity Mode Page
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
require_once('../../microlearninglib.php');
require_once('activitymodelib.php');
require_once('activity_form.php');

global $PAGE,$USER,$OUTPUT,$SITE,$SESSION,$CFG;

// Params
$course_id      = required_param('id',PARAM_INT);
$mode_learning  = required_param('mode',PARAM_INT);
$campaign_id    = required_param('cp',PARAM_INT);
$delivery_id    = optional_param('cm',0,PARAM_INT);

$context        = context_system::instance();
$context_course = context_course::instance($course_id);
$course         = get_course($course_id);
$campaign_name  = Micro_Learning::Get_NameCampaign($campaign_id);
$url            = new moodle_url('/local/microlearning/mode/activity/activity.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
$url_deliveries = new moodle_url('/local/microlearning/mode/activity/activity_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
$return_url     = new moodle_url('/local/microlearning/index.php',array('id'=>$course_id));
$delivery_info  = null;

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

// Get details of the delivery
if ($delivery_id) {
    $url->param('cm',$delivery_id);
    $delivery_info = Activity_Mode::GetDeliveryInfo_ActivityMode($campaign_id,$delivery_id);
}else {
    $delivery_info = new stdClass();
    $delivery_info->body            = '';
    $delivery_info->bodyformat      = FORMAT_HTML;
}//if_delivery_id

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($context_course);
$PAGE->set_pagelayout('course');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('title_index','local_microlearning'),$return_url);
$PAGE->navbar->add(get_string('title_activity','local_microlearning'));
$PAGE->navbar->add($campaign_name,$url_deliveries);
$PAGE->requires->js(new moodle_url('/local/microlearning/js/microlearning.js'));

if (!isset($SESSION->activities)) {
    $SESSION->activities = array();
}
if (!isset($SESSION->removeActivities)) {
    $SESSION->removeActivities = array();
}

// Editor
$edit_options   = array('maxfiles' => 0, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true, 'context' => $context_course);
$delivery_info = file_prepare_standard_editor($delivery_info, 'body', $edit_options,$context_course, 'course', 'activity_mode',0);

// Get users
$users_campaign = Micro_Learning::GetUsers_Campaign($campaign_id);
// Get course activities
$activities = Micro_Learning::Get_ActivitiesList($course_id);

// Form
$form       = new activity_mode_form(null,array($course_id,$mode_learning,$users_campaign,$campaign_id,$delivery_info,$edit_options));
if ($form->is_cancelled()) {
    unset($SESSION->activities);

    $_POST = array();
    redirect($url_deliveries);
}else if ($data = $form->get_data()) {
    if ((isset($data->submitbutton) && ($data->submitbutton))
        ||
        (isset($data->submitbutton2) && ($data->submitbutton2))) {
        // New activity mode optios - instance
        $activity_mode = new stdClass();
        $activity_mode->microid = $campaign_id;
        $activity_mode->subject = $data->subject;
        // Get email body
        $editor = new stdClass();
        $editor->body_editor = $data->body_editor;
        $editor->body = '';
        $editor = file_postupdate_standard_editor($editor, 'body', $edit_options, $context_course, 'course', 'activity_mode', 0);
        $activity_mode->body    = $editor->body;

        // Send options
        switch ($data->sel_opt) {
            case ACTIVITY_X_DAYS_AFTER_ENROL:
                $activity_mode->afterenrol          = $data->x_days_after_enrol;
                $activity_mode->aftercompletion     = null;
                $activity_mode->tocomplete          = null;
                $activity_mode->afternotcompletion  = null;
                $activity_mode->notcomplete         = null;

                break;
            case ACTIVITY_X_DAYS_AFTER_ACT:
                $activity_mode->aftercompletion     = $data->x_days_after_completion;
                $activity_mode->tocomplete          = $data->act_after_completion;
                $activity_mode->afterenrol          = null;
                $activity_mode->afternotcompletion  = null;
                $activity_mode->notcomplete         = null;

                break;
            case ACTIVITY_NOT_DONE_AFTER:
                $activity_mode->afternotcompletion  = $data->x_days_not_done;
                $activity_mode->notcomplete         = $data->act_not_done;
                $activity_mode->afterenrol          = null;
                $activity_mode->aftercompletion     = null;
                $activity_mode->tocomplete          = null;

                break;
        }//switch_send_options

        // Get type of activities
        $activities_type = Micro_Learning::Get_ActivitiesType($data->id);

        // Create new delivery or udpate
        if ($delivery_id) {
            // Update
            $activity_mode->id = $delivery_id;
            $bool = Activity_Mode::UpdateDelivery_ActivityMode($activity_mode,$users_campaign,$SESSION->activities,$activities_type,$data->id);
        }else {
            // New
            $bool = Activity_Mode::CreateDelivery_ActivityMode($activity_mode,$users_campaign,$SESSION->activities,$activities_type,$data->id);
        }//if_delivery_id

        // Clean
        $_POST = array();
        unset($SESSION->activities);
        unset($SESSION->removeActivities);
        if ($bool) {
            // Get the correct place to return
            if (isset($data->submitbutton2) && ($data->submitbutton2)) {
                $url_deliveries = new moodle_url('/course/view.php',array('id' => $data->id));
            }//if_save_return_course

            redirect($url_deliveries);
        }else {
            echo $OUTPUT->header();
            echo $OUTPUT->notification(get_string('err_generic','local_microlearning'), 'notifysuccess');
            echo $OUTPUT->continue_button($url_deliveries);
            echo $OUTPUT->footer();
        }//if_bool

    }//if_save_buttons

    if (isset($data->add_sel) && ($data->add_sel)) {
        $PAGE->url->params(array('id'=>$data->id,'mode' => $data->mode,'cp' => $data->cp));
        foreach($data->add_activities as $key=>$value) {
            $SESSION->activities[$value] = $activities[$value];
        }

        $form = new activity_mode_form(null,array($data->id,$mode_learning,$users_campaign,$campaign_id,$delivery_info,$edit_options));
    }//if_add_activities

    if (isset($data->remove_sel) && ($data->remove_sel)) {
        $PAGE->url->params(array('id'=>$data->id,'mode' => $data->mode,'cp' => $data->cp));
        foreach($data->sel_activities as $key=>$value) {
            $SESSION->removeActivities[$value] = $activities[$value];
        }
        $form = new activity_mode_form(null,array($data->id,$mode_learning,$users_campaign,$campaign_id,$delivery_info,$edit_options));
    }//if_remove_activities
}//if_form

$str_header = $campaign_name . ' - ' . get_string('activity_mode','local_microlearning');
echo $OUTPUT->header();
echo $OUTPUT->heading($str_header);

$form->display();

echo $OUTPUT->footer();