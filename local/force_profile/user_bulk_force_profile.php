<?php
/**
 * Force Update Profile - Bulk Action
 *
 * Description
 *
 * @package         local
 * @subpackage      force_profile
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      21/08/2014
 * @author          eFaktor     (fbv)
 *
 */

global $CFG,$SESSION,$PAGE,$DB,$SITE,$OUTPUT,$USER;

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/user/lib.php');

require_once('forceprofilelib.php');
require_once('force_profile_form.php');

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

admin_externalpage_setup('userbulk');

// Params
$context    = context_system::instance();
$url        = new moodle_url('/local/force_profile/user_bulk_force_profile.php');
$return     = new moodle_url('/admin/user/user_bulk.php');
if (isset($SESSION->bulk_users)) {
    $users = implode(',',$SESSION->bulk_users);
}else {
    $users = '';
}//if_bulk_users

if (!isset($SESSION->fields)) {
    $SESSION->fields = array();
}

// Capability
require_capability('moodle/user:update', $context);

if (empty($users)) {
    redirect($return);
}//if_users

$PAGE->set_url($url);
$PAGE->set_context($context);

// Form
$add_sel    = ForceProfile::ForceProfile_GetChoicesProfile();

$form       = new force_profile_form(null,array($users,$add_sel));
if($data = $form->get_data()) {
    if (!empty($data->addsel)) {
        foreach($data->add_fields as $value) {
            $SESSION->fields[$value] = $add_sel[$value];
        }
    }//if_data_sel

    if (!empty($data->removesel)) {
        foreach($data->sel_fields as $value) {
            unset($SESSION->fields[$value]);
        }
        if (!$SESSION->fields) {
            unset($SESSION->fields);
        }
    }//if_removesel

    $form           = new force_profile_form(null,array($users,$add_sel));
}//if_else_form

$form_msg = new force_profile_message_form(null);
if ($form_msg->is_cancelled()) {
    unset($SESSION->fields);

    $_POST = array();
    redirect($return);
}else if ($data = $form_msg->get_data()){
    if (isset($SESSION->fields)) {
        $msg            = null;
        $editor         = $data->msg_body;
        $msg            = $editor['text'];

        ForceProfile::ForceProfile_SendNotification($msg);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('force_header', 'local_force_profile'));
        echo $OUTPUT->notification(get_string('exit_notification','local_force_profile'), 'notifysuccess');
        echo $OUTPUT->continue_button($return);
        echo $OUTPUT->footer();

        unset($SESSION->fields);

        die();
    }else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('force_header', 'local_force_profile'));
        echo $OUTPUT->notification(get_string('not_selected','local_force_profile'), 'notifysuccess');
        echo $OUTPUT->continue_button($url);
        echo $OUTPUT->footer();
        die();
    }
}//if_else_if

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('force_header', 'local_force_profile'));

$form->display();
$form_msg->display();

echo $OUTPUT->footer();
die();
