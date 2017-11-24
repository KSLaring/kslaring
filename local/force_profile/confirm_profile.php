<?php
/**
 * Confirm Profile - Force PRofile
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
 * @updateDate      23/04/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add the correct identifier for the profile fields, instead of the language string
 */

global $CFG,$SESSION,$PAGE,$DB,$SITE,$OUTPUT,$USER;

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/tag/lib.php');
require_once('forceprofilelib.php');
require_once('confirm_profile_form.php');


// Params
$user_id    = required_param('id',PARAM_INT);
$context    = context_system::instance();
$url        = new moodle_url('/local/force_profile/confirm_profile.php',array('id'=>$user_id));

if (!isset($SESSION->elements)) {
    $SESSION->elements = array();
}

if (!isset($SESSION->force_profile)) {
    $SESSION->force_profile = true;
}

$user_context = context_user::instance($user_id);

$PAGE->set_url($url);
$PAGE->set_context($user_context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->navbar->add(get_string('force_bulk','local_force_profile'));

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


// My fields
$my_fields  = ForceProfile::ForceProfile_GetFieldsToUpdate($user_id);
$user       = $DB->get_record('user',array('id' => $user_id));
// Prepare the editor and create form

// Prepare filemanager draft area.
$draftitemid = 0;
$filemanageroptions = array('maxbytes'       => $CFG->maxbytes,
                            'subdirs'        => 0,
                            'maxfiles'       => 1,
                            'accepted_types' => 'web_image');
file_prepare_draft_area($draftitemid, $user_context->id, 'user', 'newicon', 0, $filemanageroptions);
$user->imagefile = $draftitemid;

// Add form
$form = new confirm_profile_form(null,array($my_fields,$user,$filemanageroptions));
if ($data = $form->get_data()) {
    // First normal fields
    if ($my_fields->normal) {
        $normal_fields = $my_fields->normal;
        foreach ($normal_fields as $field) {
            $name = $field->name;

            $field->timeupdated = time();
            $field->confirmed   = 1;

            switch ($name) {
                case DESCRIPTION:
                    $editor             = $data->$name;
                    $field->value       = $editor['text'];
                    $field->old_value   = $user->$name;

                    break;
                case PICTURE:
                    $field->old_value   = $user->picture;
                    $user->imagefile    = $data->imagefile;
                    $field->value       = $data->$name;

                    break;
                case INTEREST:
                    $field->old_value   = tag_get_tags_csv('user', $user_id);
                    $field->value       = $data->$name;

                    break;
                default:
                    $field->value       = $data->$name;
                    $field->old_value   = $user->$name;

                    break;
            }//switch_name

            useredit_update_picture($user,$form,$filemanageroptions);
            ForceProfile::ForceProfile_UpdateUserForceProfile($user_id,$field,$name);
        }//for_normal_fields
    }//normal_fields

    // Extra profile fields
    if ($my_fields->profile) {
        $profile_fields = $my_fields->profile;
        foreach ($profile_fields as $field) {
            $name = $SESSION->elements[$field->name];

            $field->timeupdated = time();
            $field->confirmed   = 1;
            $field->value       = $data->$name;

            ForceProfile::ForceProfile_UpdateExtraUserForceProfile($user_id,$field,$data,$name);
        }//for_profile_fields
    }//profile_fields

    // Check that the user has updated all fields
    if (!ForceProfile::ForceProfile_HasToUpdateProfile($user_id)) {
        unset($SESSION->elements);
        unset($SESSION->force_profile);
        unset($SESSION->time);

        $return = new moodle_url('/user/profile.php',array('id' => $user_id));
        redirect($return);
    }else {
        redirect($url);
    }//if_else

}//if_form_get_data

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();