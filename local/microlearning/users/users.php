<?php
/**
 * Micro Learning - Selector Users Page
 *
 * @package         local
 * @subpackage      microlearning
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      12/09/2014
 * @author          eFaktor     (fbv)
 *
 * @updateDate      12/11/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Search field. New javascript and functionality.
 * User filter. Add the search option.
 * Improve the logical to add and remove users from/to campaign
 *
 */
global $CFG,$USER,$PAGE,$OUTPUT,$SITE,$SESSION;

require_once('../../../config.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');
require_once('../microlearninglib.php');
require_once('microuserslib.php');
require_once('users_form.php');
require_once('filter/lib.php');

// Params
$course_id      = required_param('id',PARAM_INT);
$campaign_id    = required_param('cp',PARAM_INT);
$mode_learning  = required_param('mode',PARAM_INT);
$started        = optional_param('st',0,PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);

$course         = get_course($course_id);
$campaign_name  = Micro_Learning::Get_NameCampaign($campaign_id);
$context        = context_system::instance();
$context_course = context_course::instance($course_id);
$url            = new moodle_url('/local/microlearning/users/users.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
$return_url     = new moodle_url('/local/microlearning/index.php',array('id'=>$course_id));
$campaign_url   = null;

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

if ($mode_learning == CALENDAR_MODE) {
    $campaign_url =new moodle_url('/local/microlearning/mode/calendar/calendar_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
}else {
    $campaign_url =new moodle_url('/local/microlearning/mode/activity/activity_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
}//if_mode

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($context_course);
$PAGE->set_pagelayout('course');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('title_index','local_microlearning'),$return_url);
$PAGE->navbar->add(get_string('title_users','local_microlearning'));
$PAGE->navbar->add($campaign_name,$campaign_url);

if (!isset($SESSION->bulk_users)) {
    $SESSION->bulk_users = array();
}
if (!isset($SESSION->to_remove)) {
    $SESSION->to_remove = array();
}
if (!isset($SESSION->removeAll)) {
    $SESSION->removeAll = false;
}

// Create user filter
$user_filter = new microlearning_users_filtering(null,$url,null);
$user_filter->course_id = $course_id;
// Selector user form
$user_list              = Micro_Users::Get_SelectiorUsers_Filter($user_filter,$course_id,$mode_learning,$campaign_id,$started,$addSearch,$removeSearch);
$selector_users         = new microlearning_users_selector_form(null,$user_list);
if ($selector_users->is_cancelled()) {
    unset($SESSION->bulk_users);

    $_POST = array();
    redirect($return_url);
}else if ($data = $selector_users->get_data()) {
    // Return url
    switch ($mode_learning) {
        case CALENDAR_MODE:
            $return_url = new moodle_url('/local/microlearning/mode/calendar/calendar_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));

            break;
        case ACTIVITY_MODE:
            $return_url = new moodle_url('/local/microlearning/mode/activity/activity_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));

            break;
        default:
            break;
    }//switch_mode

    // Add users
    if (!empty($data->add_sel)) {
        // Save users
        Micro_Users::AddUsers_Campaign($course_id,$campaign_id,$mode_learning,$data->ausers);
    }//if_Add_selected

    // Add all users
    if (!empty($data->add_all)) {
        Micro_Users::AddAllUsers_Campaign($course_id,$campaign_id,$mode_learning);
    }//if_Add_selected

    // Remove users
    if (!empty($data->remove_sel)) {
        // Remove/delete users
        Micro_Users::DeleteUsers_Campaign($campaign_id,$data->susers);
    }//if_remove_selected

    // Remove all users
    if (!empty($data->remove_all)) {
        // Remove all users from campaign
        Micro_Users::DeleteAllUsers_Campaign($campaign_id);
    }//if_remove_selected

    // Next action
    if ((isset($data->submitbutton) && $data->submitbutton)) {
        if (isset($data->add_users)) {
            if ($data->add_users) {
                Micro_Learning::Activate_AddNewUsers($campaign_id,$course_id,$mode_learning,1);
            }else {
                Micro_Learning::Activate_AddNewUsers($campaign_id,$course_id,$mode_learning,0);
            }//if_add_new_users
        }else {
            Micro_Learning::Activate_AddNewUsers($campaign_id,$course_id,$mode_learning,0);
        }//if_add_new_users

        $_POST = array();
        redirect($return_url);
    }//if_submitbutton_next

    // reset the form selections
    $user_list      = Micro_Users::Get_SelectiorUsers_Filter($user_filter,$course_id,$mode_learning,$campaign_id,$started,$addSearch,$removeSearch);
    $selector_users = new microlearning_users_selector_form(null, $user_list);
}//if_form


echo $OUTPUT->header();
$str_header  = get_string('header_users_selector','local_microlearning');
$str_header .= ' ' . get_string('name_campaign','local_microlearning');
echo $OUTPUT->heading( $str_header. ' ' . $campaign_name);

// Add the filters
$user_filter->display_add();
$user_filter->display_active();
flush();

$selector_users->display();

// Initialise Selectors
Micro_Users::Init_MicroUsers_Selector($course_id,$campaign_id,$addSearch,$removeSearch);

echo $OUTPUT->footer();