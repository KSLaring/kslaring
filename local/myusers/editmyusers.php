<?php
/**
 * Edit MyUsers Admin - Category plugin - Main Page
 *
 * Description
 *
 * @package         local
 * @subpackage      myusers
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      30/01/2014
 * @author          eFaktor     (fbv)
 *
 */

global $CFG,$USER,$OUTPUT,$SESSION,$DB,$SITE,$PAGE;

require_once( '../../config.php');
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/user/editadvanced_form.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/lib.php');

$id             = required_param('id', PARAM_INT);
$cat_id         = (isset($SESSION->cat) ? $SESSION->cat :0);
$course         = optional_param('course', SITEID, PARAM_INT);   // course id (defaults to Site)
$url = new moodle_url('/local/myusers/editmyusers.php',array('id' => $id,'course' => $course));

if (isguestuser($USER->id)) { // the real guest user can not be edited
    require_logout();
    print_error('guestnoeditprofileother');
    die();
}
$context_cat    = context_coursecat::instance($cat_id);
$course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_url($url);

if (!empty($USER->newadminuser)) {
    $PAGE->set_course($SITE);
    $PAGE->set_pagelayout('maintenance');
} else {
    if ($course->id == SITEID) {
        require_login();
        $PAGE->set_context(context_system::instance());
    } else {
        require_login($course);
    }
    $PAGE->set_pagelayout('admin');
}

if ($course->id == SITEID) {
    $coursecontext = context_system::instance();   // SYSTEM context
} else {
    $coursecontext = context_course::instance($course->id);   // Course context
}
    // editing existing user
    require_capability('moodle/user:update', $context_cat);
    $user = $DB->get_record('user', array('id'=>$id), '*', MUST_EXIST);
    $PAGE->set_context(context_user::instance($user->id));
    if ($user->id != $USER->id) {
        $PAGE->navigation->extend_for_user($user);
    } else {
        if ($node = $PAGE->navigation->find('myprofile', navigation_node::TYPE_ROOTNODE)) {
            $node->force_open();
        }
    }


// remote users cannot be edited
if ($user->id != -1 and is_mnet_remote_user($user)) {
    redirect($CFG->wwwroot . "/user/view.php?id=$id&course={$course->id}");
}

if ($user->id != $USER->id and is_siteadmin($user) and !is_siteadmin($USER)) {  // Only admins may edit other admins
    print_error('useradmineditadmin');
}

if ($user->deleted) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('userdeleted'));
    echo $OUTPUT->footer();
    die;
}

//load user preferences
useredit_load_preferences($user);

//Load custom profile fields data
profile_load_data($user);

//User interests
if (!empty($CFG->usetags)) {
    require_once($CFG->dirroot.'/tag/lib.php');
    $user->interests = tag_get_tags_array('user', $id);
}

if ($user->id !== -1) {
    $usercontext = context_user::instance($user->id);
    $editoroptions = array(
        'maxfiles'   => EDITOR_UNLIMITED_FILES,
        'maxbytes'   => $CFG->maxbytes,
        'trusttext'  => false,
        'forcehttps' => false,
        'context'    => $usercontext
    );

    $user = file_prepare_standard_editor($user, 'description', $editoroptions, $usercontext, 'user', 'profile', 0);
} else {
    $usercontext = null;
    // This is a new user, we don't want to add files here
    $editoroptions = array(
        'maxfiles'=>0,
        'maxbytes'=>0,
        'trusttext'=>false,
        'forcehttps'=>false,
        'context' => $coursecontext
    );
}

// Prepare filemanager draft area.
$draftitemid = 0;
$filemanagercontext = $editoroptions['context'];
$filemanageroptions = array('maxbytes'       => $CFG->maxbytes,
    'subdirs'        => 0,
    'maxfiles'       => 1,
    'accepted_types' => 'web_image');
file_prepare_draft_area($draftitemid, $filemanagercontext->id, 'user', 'newicon', 0, $filemanageroptions);
$user->imagefile = $draftitemid;
//create form
$userform = new user_editadvanced_form(null, array(
    'editoroptions' => $editoroptions,
    'filemanageroptions' => $filemanageroptions,
    'userid' => $user->id,
    'catadmin' => '1'));
$userform->set_data($user);

if ($usernew = $userform->get_data()) {

    if (empty($usernew->auth)) {
        //user editing self
        $authplugin = get_auth_plugin($user->auth);
        unset($usernew->auth); //can not change/remove
    } else {
        $authplugin = get_auth_plugin($usernew->auth);
    }

    $usernew->timemodified = time();
    $createpassword = false;

    if ($usernew->id == -1) {
        //TODO check out if it makes sense to create account with this auth plugin and what to do with the password
        unset($usernew->id);
        $createpassword = !empty($usernew->createpassword);
        unset($usernew->createpassword);
        $usernew = file_postupdate_standard_editor($usernew, 'description', $editoroptions, null, 'user', 'profile', null);
        $usernew->mnethostid = $CFG->mnet_localhost_id; // always local user
        $usernew->confirmed  = 1;
        $usernew->timecreated = time();
        if ($createpassword) {
            $usernew->password = '';
        } else {
            $usernew->password = hash_internal_user_password($usernew->newpassword);
        }
        $usernew->id = user_create_user($usernew, false);
    } else {
        $usernew = file_postupdate_standard_editor($usernew, 'description', $editoroptions, $usercontext, 'user', 'profile', 0);
        // Pass a true old $user here.
        if (!$authplugin->user_update($user, $usernew)) {
            // Auth update failed.
            print_error('cannotupdateuseronexauth', '', '', $user->auth);
        }
        user_update_user($usernew, false);

        //set new password if specified
        if (!empty($usernew->newpassword)) {
            if ($authplugin->can_change_password()) {
                if (!$authplugin->user_update_password($usernew, $usernew->newpassword)){
                    print_error('cannotupdatepasswordonextauth', '', '', $usernew->auth);
                }
                unset_user_preference('create_password', $usernew); // prevent cron from generating the password
            }
        }

        // force logout if user just suspended
        if (isset($usernew->suspended) and $usernew->suspended and !$user->suspended) {
            \core\session\manager::kill_user_sessions($user->id);
        }
    }

    $usercontext = context_user::instance($usernew->id);

    //update preferences
    useredit_update_user_preference($usernew);

    // update tags
    if (!empty($CFG->usetags) and empty($USER->newadminuser)) {
        useredit_update_interests($usernew, $usernew->interests);
    }

    //update user picture
    if (empty($USER->newadminuser)) {
        useredit_update_picture($usernew, $userform, $filemanageroptions);
    }

    // update mail bounces
    useredit_update_bounces($user, $usernew);

    // update forum track preference
    useredit_update_trackforums($user, $usernew);

    // save custom profile fields data
    profile_save_data($usernew);

    // reload from db
    $usernew = $DB->get_record('user', array('id'=>$usernew->id));

    if ($createpassword) {
        setnew_password_and_mail($usernew);
        unset_user_preference('create_password', $usernew);
        set_user_preference('auth_forcepasswordchange', 1, $usernew);
    }

    if ($user->id == $USER->id) {
        // Override old $USER session variable
        foreach ((array)$usernew as $variable => $value) {
            if ($variable === 'description' or $variable === 'password') {
                // These are not set for security nad perf reasons.
                continue;
            }
            $USER->$variable = $value;
        }
        // preload custom fields
        profile_load_custom_fields($USER);

        if (!empty($USER->newadminuser)) {
            unset($USER->newadminuser);
            // apply defaults again - some of them might depend on admin user info, backup, roles, etc.
            admin_apply_default_settings(NULL , false);
            // redirect to admin/ to continue with installation
            redirect("$CFG->wwwroot/$CFG->admin/");
        } else {
            redirect("$CFG->wwwroot/user/view.php?id=$USER->id&course=$course->id");
        }
    } else {
        \core\session\manager::gc(); // Remove stale sessions.
        $url = new moodle_url('/local/myusers/myusers.php',array('id' => $SESSION->cat));
        unset($SESSION->cat);
        redirect($url);
    }
    //never reached
}

// make sure we really are on the https page when https login required
$PAGE->verify_https_required();


/// Display page header
if ($user->id == -1 or ($user->id != $USER->id)) {
    if ($user->id == -1) {
        echo $OUTPUT->header();
    } else {
        $PAGE->set_heading($SITE->fullname);
        echo $OUTPUT->header();
        $userfullname = fullname($user, true);
        echo $OUTPUT->heading($userfullname);
    }
} else if (!empty($USER->newadminuser)) {
    $strinstallation = get_string('installation', 'install');
    $strprimaryadminsetup = get_string('primaryadminsetup');

    $PAGE->navbar->add($strprimaryadminsetup);
    $PAGE->set_title($strinstallation);
    $PAGE->set_heading($strinstallation);
    $PAGE->set_cacheable(false);

    echo $OUTPUT->header();
    echo $OUTPUT->box(get_string('configintroadmin', 'admin'), 'generalbox boxwidthnormal boxaligncenter');
    echo '<br />';
} else {
    $streditmyprofile = get_string('editmyprofile');
    $strparticipants  = get_string('participants');
    $strnewuser       = get_string('newuser');
    $userfullname     = fullname($user, true);

    $PAGE->set_title("$course->shortname: $streditmyprofile");
    $PAGE->set_heading($course->fullname);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($userfullname);
}

/// Finally display THE form
$userform->display();

/// and proper footer
echo $OUTPUT->footer();

