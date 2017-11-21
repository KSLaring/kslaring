<?php
/**
 * Micro Learning Deliveries    - Activity Mode Page
 *
 * @package         local/microlearnig
 * @subpackage      mode/activity
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      17/10/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../../config.php');
require_once('../../microlearninglib.php');
require_once('activitymodelib.php');

global $PAGE,$USER,$OUTPUT,$SITE,$SESSION,$CFG;

// Params
$course_id      = required_param('id',PARAM_INT);
$mode_learning  = required_param('mode',PARAM_INT);
$campaign_id    = required_param('cp',PARAM_INT);
$sort           = optional_param('sort','ASC',PARAM_ALPHA);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 10, PARAM_INT);        // how many per page
$act            = optional_param('act',0,PARAM_INT);

$context        = context_system::instance();
$context_course = context_course::instance($course_id);
$course         = get_course($course_id);
$campaign_name  = Micro_Learning::Get_NameCampaign($campaign_id);

$url            = new moodle_url('/local/microlearning/mode/activity/activity_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id,'sort' => $sort,'page' => $page, 'perpage' => $perpage));
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

// Page settigns
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

// Check activate/deactivate campaign
if ($act) {
    // Change status
    Micro_Learning::ChangeStatus_Campaign($campaign_id,$course_id);
    $url            = new moodle_url('/local/microlearning/mode/activity/activity_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
    redirect($url);
}else {
    echo $OUTPUT->header();
    // Get deliveries
    $total_deliveries = Activity_Mode::Get_TotalActivityDeliveries($campaign_id);
    $deliveries_lst   = Activity_Mode::Get_ActivityDeliveries($campaign_id,$sort,$page*$perpage,$perpage);

    // Print the table
    echo Micro_Learning::Get_CampaignDeliveries_Table($campaign_id,$campaign_name,$deliveries_lst,$mode_learning,$course_id,false);
    echo "</br>";
    echo $OUTPUT->paging_bar($total_deliveries, $page, $perpage, $url);

    // Add actions buttons
    echo "</br></br>";
    echo Activity_Mode::AddButtons_ActivityDeliveries_Menu($course_id,$mode_learning,$campaign_id);

    echo $OUTPUT->footer();
}//if_Act