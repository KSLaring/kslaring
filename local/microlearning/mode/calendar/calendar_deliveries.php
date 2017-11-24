<?php
/**
 * Micro Learning Deliveries    - Calendar Mode Page
 *
 * @package         local/microlearnig
 * @subpackage      mode/calendar
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      16/10/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../../config.php');
require_once('../../microlearninglib.php');
require_once('calendarmodelib.php');

global $PAGE,$USER,$OUTPUT,$SITE,$SESSION,$CFG;

// Params
$course_id      = required_param('id',PARAM_INT);
$mode_learning  = required_param('mode',PARAM_INT);
$campaign_id    = required_param('cp',PARAM_INT);
$sort           = optional_param('sort','ASC',PARAM_ALPHA);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 10, PARAM_INT);        // how many per page
$act            = optional_param('act',0,PARAM_INT);
$strAlert       = null;

$context        = context_system::instance();
$context_course = context_course::instance($course_id);
$course         = get_course($course_id);
$campaign_name  = Micro_Learning::Get_NameCampaign($campaign_id);

$url            = new moodle_url('/local/microlearning/mode/calendar/calendar_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id,'sort' => $sort,'page' => $page, 'perpage' => $perpage));
$return_url     = new moodle_url('/local/microlearning/index.php',array('id'=>$course_id));

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
$PAGE->navbar->add(get_string('title_delivery','local_microlearning'));
$PAGE->navbar->add($campaign_name,$url);

// Clean session
unset($SESSION->activities);
unset($SESSION->removeActivities);
unset($SESSION->bulk_users);
unset($SESSION->to_remove);
unset($SESSION->removeAll);

// Check activate/deactivate
if ($act) {
    // Change status
    Micro_Learning::ChangeStatus_Campaign($campaign_id,$course_id);
    $url = new moodle_url('/local/microlearning/mode/calendar/calendar_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
    redirect($url);
}else {
    // Header
    echo $OUTPUT->header();

    // Get deliveries
    $total_deliveries   = Calendar_Mode::Get_TotalCalendarDeliveries($campaign_id);
    $deliveries_lst     = Calendar_Mode::Get_CalendarDeliveries($campaign_id,$sort,$page*$perpage,$perpage);
    $started            = Calendar_Mode::HasStarted_Campaign($campaign_id);
    $canBeAct           = Calendar_Mode::CanBeActivated($campaign_id);
    if (!$canBeAct) {
        $strAlert   = $strAlert   = get_string('alert_campaign','local_microlearning');
    }//if_canBeAct

    // Print table
    echo Micro_Learning::Get_CampaignDeliveries_Table($campaign_id,$campaign_name,$deliveries_lst,$mode_learning,$course_id,$started,$strAlert);
    echo "</br>";
    echo $OUTPUT->paging_bar($total_deliveries, $page, $perpage, $url);

    // Action buttons
    echo "</br></br>";
    echo Calendar_Mode::AddButtons_CalendarDeliveries_Menu($course_id,$mode_learning,$campaign_id);

    echo $OUTPUT->footer();
}//if_Act



