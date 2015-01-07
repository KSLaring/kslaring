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
 */
require_once('../../../config.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');
require_once('../microlearninglib.php');
require_once('microuserslib.php');
require_once('users_form.php');
require_once('filter/lib.php');

/* PARAMS   */
$course_id      = required_param('id',PARAM_INT);
$campaign_id    = required_param('cp',PARAM_INT);
$mode_learning  = required_param('mode',PARAM_INT);
$started        = optional_param('st',0,PARAM_INT);

$course         = get_course($course_id);
$campaign_name  = Micro_Learning::Get_NameCampaign($campaign_id);
$context        = context_system::instance();
$context_course = context_course::instance($course_id);
$url            = new moodle_url('/local/microlearning/users/users.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
$return_url     = new moodle_url('/local/microlearning/index.php',array('id'=>$course_id));
$campaign_url   = null;

require_capability('local/microlearning:manage',$context);
require_login($course);

if ($mode_learning == CALENDAR_MODE) {
    $campaign_url =new moodle_url('/local/microlearning/mode/calendar/calendar_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
}else {
    $campaign_url =new moodle_url('/local/microlearning/mode/activity/activity_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
}//if_mode

$PAGE->set_url($url);
$PAGE->set_context($context_course);
$PAGE->set_pagelayout('course');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('title_index','local_microlearning'),$return_url);
$PAGE->navbar->add(get_string('title_users','local_microlearning'));
$PAGE->navbar->add($campaign_name,$campaign_url);
$PAGE->requires->js(new moodle_url('/local/microlearning/js/users.js'));

if (!isset($SESSION->bulk_users)) {
    $SESSION->bulk_users = array();
}
if (!isset($SESSION->to_remove)) {
    $SESSION->to_remove = array();
}
if (!isset($SESSION->removeAll)) {
    $SESSION->removeAll = false;
}
/* Create the user filter   */
$user_filter = new microlearning_users_filtering(null,$url,null);
$user_filter->course_id = $course_id;
/* Selector User Form   */
$user_list              = Micro_Users::Get_SelectiorUsers_Filter($user_filter,$course_id,$mode_learning,$campaign_id,$started);
$selector_users = new microlearning_users_selector_form(null,$user_list);
if ($selector_users->is_cancelled()) {
    unset($SESSION->bulk_users);

    $_POST = array();
    redirect($return_url);
}else if ($data = $selector_users->get_data()) {
    $SESSION->removeAll = false;

    $url            = new moodle_url('/local/microlearning/users/users.php',array('id'=>$data->id,'mode' => $data->mode,'cp' => $data->cp,'st' => $data->st));
    $PAGE->set_url($url);

    if (!empty($data->add_all)) {
        Micro_Users::AddSelectionAll($user_filter);

    } else if (!empty($data->add_sel)) {
        if (!empty($data->ausers)) {
            if (in_array(0, $data->ausers)) {
                add_selection_all($user_filter);
            } else {
                foreach($data->ausers as $userid) {
                    if ($userid == -1) {
                        continue;
                    }
                    if (!isset($SESSION->bulk_users[$userid])) {
                        $SESSION->bulk_users[$userid] = $userid;
                    }
                }
            }
        }

    } else if (!empty($data->remove_all)) {
        $SESSION->bulk_users= array();
        $SESSION->removeAll = true;
    } else if (!empty($data->remove_sel)) {
        if (!empty($data->susers)) {
            if (in_array(0, $data->susers)) {
                $SESSION->bulk_users= array();
            } else {
                foreach($data->susers as $userid) {
                    if ($userid == -1) {
                        continue;
                    }
                    unset($SESSION->bulk_users[$userid]);
                    $SESSION->to_remove[$userid] = $userid;
                }
            }
        }
    }

    if ((isset($data->submitbutton) && $data->submitbutton)) {
        if (!$SESSION->bulk_users) {
            Micro_Users::AddSelectionAll($user_filter);
            Micro_Users::set_UsersFilter($SESSION->bulk_users);
            unset($SESSION->bulk_users);
        }else {
            Micro_Users::set_UsersFilter($SESSION->bulk_users);
        }//if_sesion_users_bulk

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

        /* Save the users   */
        $lst_users = Micro_Users::get_UsersFilter();
        Micro_Users::SaveUsers_Campaign($campaign_id,$lst_users);

        $_POST = array();
        redirect($return_url);
    }//if_button_submission_next

    // reset the form selections
    unset($_POST);
    $user_list = Micro_Users::Get_SelectiorUsers_Filter($user_filter,$course_id,$mode_learning,$campaign_id,$started);
    $selector_users = new microlearning_users_selector_form(null, $user_list);
}



echo $OUTPUT->header();
$str_header  = get_string('header_users_selector','local_microlearning');
$str_header .= ' ' . get_string('name_campaign','local_microlearning');
echo $OUTPUT->heading( $str_header. ' ' . $campaign_name);

/* Add the filters  */
$user_filter->display_add();
$user_filter->display_active();
flush();

$selector_users->display();

echo $OUTPUT->footer();