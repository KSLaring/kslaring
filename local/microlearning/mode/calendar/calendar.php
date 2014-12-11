<?php
/**
 * Micro Learning - Calendar Mode Page
 *
 * @package         local/microlearnig
 * @subpackage      mode/calendar
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      12/09/2014
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../../config.php');
require_once('../../microlearninglib.php');
require_once('calendar_form.php');
require_once('calendarmodelib.php');

/* PARAMS   */
$course_id      = required_param('id',PARAM_INT);
$mode_learning  = required_param('mode',PARAM_INT);
$campaign_id    = required_param('cp',PARAM_INT);
$delivery_id    = optional_param('cm',0,PARAM_INT);

$context        = context_system::instance();
$context_course = context_course::instance($course_id);
$course         = get_course($course_id);
$campaign_name  = Micro_Learning::Get_NameCampaign($campaign_id);
$url            = new moodle_url('/local/microlearning/mode/calendar/calendar.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
$url_deliveries = new moodle_url('/local/microlearning/mode/calendar/calendar_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
$return_url     = new moodle_url('/local/microlearning/index.php',array('id'=>$course_id));
$delivery_info  = null;

require_capability('local/microlearning:manage',$context);
require_login($course);

/* Get the details of the delivery */
if ($delivery_id) {
    $url->param('cm',$delivery_id);
    $delivery_info = Calendar_Mode::GetDeliveryInfo_CalendarMode($campaign_id,$delivery_id);
}//if_delivery_id

$PAGE->set_url($url);
$PAGE->set_context($context_course);
$PAGE->set_pagelayout('course');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('title_index','local_microlearning'),$return_url);
$PAGE->navbar->add(get_string('title_calendar','local_microlearning'));
$PAGE->navbar->add($campaign_name,$url_deliveries);
$PAGE->requires->js(new moodle_url('/local/microlearning/js/microlearning.js'));

if (!isset($SESSION->activities)) {
    $SESSION->activities = array();
}
if (!isset($SESSION->removeActivities)) {
    $SESSION->removeActivities = array();
}

/* Form */
/* Get the users            */
$users_campaign = Micro_Learning::GetUsers_Campaign($campaign_id);
/* Get Activities Course    */
$activities = Micro_Learning::Get_ActivitiesList($course_id);
$form = new calendar_mode_form(null,array($course_id,$mode_learning,$users_campaign,$campaign_id,$delivery_info));

if ($form->is_cancelled()) {
    unset($SESSION->activities);

    $_POST = array();
    redirect($url_deliveries);
}else if ($data = $form->get_data()) {

    if ((isset($data->submitbutton) && ($data->submitbutton))
        ||
        (isset($data->submitbutton2) && ($data->submitbutton2))) {
        /* New Calendar Mode Options - Instance */
        $calendar_mode = new stdClass();
        $calendar_mode->microid     = $campaign_id;
        $calendar_mode->subject     = $data->subject;
        $calendar_mode->body        = $data->body;
        /* Send Options */
        switch ($data->sel_date) {
            case CALENDAR_DATE_TO_SEND:
                $calendar_mode->datesend        = $data->date_send;
                $calendar_mode->dateafter       = null;
                $calendar_mode->daysafter       = null;
                $calendar_mode->activityafter   = null;

                break;
            case CALENDAR_X_DAYS:
                $calendar_mode->datesend        = null;
                $calendar_mode->dateafter       = $data->date_after;
                $calendar_mode->daysafter       = $data->x_days;
                $calendar_mode->activityafter   = $data->act_not_done;

                break;
        }//switch_options

        /* Get the type of activities   */
        $activities_type = Micro_Learning::Get_ActivitiesType($data->id);

        /* Create New Delivery or Update delivery */
        if ($delivery_id) {
            /* Update it        */
            $calendar_mode->id = $delivery_id;
            $bool = Calendar_Mode::UpdateDelivery_CalendarMode($calendar_mode,$users_campaign,$SESSION->activities,$activities_type);
        }else {
            /* Create New One   */
            $bool = Calendar_Mode::CreateDelivery_CalendarMode($calendar_mode,$users_campaign,$SESSION->activities,$activities_type);
        }//if_delivery_id

        /* Clean    */
        $_POST = array();
        unset($SESSION->activities);
        unset($SESSION->removeActivities);
        if ($bool) {
            /* Get the correct place to return */
            if (isset($data->submitbutton2) && ($data->submitbutton2)) {
                $return_url = new moodle_url('/course/view.php',array('id' => $data->id));
            }//if_save_return_course

            /* Return to the correct place */
            redirect($return_url);
        }else {
            echo $OUTPUT->header();
            echo $OUTPUT->notification(get_string('err_generic','local_microlearning'), 'notifysuccess');
            echo $OUTPUT->continue_button($url_deliveries);
            echo $OUTPUT->footer();
        }//if_else_bool

    }//if_save_buttons

    if (isset($data->add_sel) && ($data->add_sel)) {
        $PAGE->url->params(array('id'=>$data->id,'mode' => $data->mode,'cp' => $data->cp));
        foreach($data->add_activities as $key=>$value) {
            $SESSION->activities[$value] = $activities[$value];
        }
        $form = new calendar_mode_form(null,array($data->id,$mode_learning,$users_campaign,$campaign_id,$delivery_info));
    }//if_add_activities

    if (isset($data->remove_sel) && ($data->remove_sel)) {
        $PAGE->url->params(array('id'=>$data->id,'mode' => $data->mode,'cp' => $data->cp));
        foreach($data->sel_activities as $key=>$value) {
            $SESSION->removeActivities[$value] = $activities[$value];
        }
        $form = new calendar_mode_form(null,array($data->id,$mode_learning,$users_campaign,$campaign_id,$delivery_info));
    }//if_remove_activities
}//if_form

$str_header = $campaign_name . ' - ' . get_string('calendar_mode','local_microlearning');
echo $OUTPUT->header();
echo $OUTPUT->heading($str_header);

$form->display();

echo $OUTPUT->footer();