<?php
/**
 * Kommit ADFS Integration WebService - Login Page
 *
 * @package         local
 * @subpackage      wsks/adfs
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    30/10/2015
 * @author          eFaktor     (fbv)
 *
 */
global $PAGE,$USER,$OUTPUT,$SESSION;
require( '../../../config.php' );
require_once ('../wsadfslib.php');

// Params
$url            = new moodle_url('/local/wsks/adfs/login.php');
$redirect       = new moodle_url('/index.php');
$errUrl         = new moodle_url('/local/wsks/adfs/error.php');

// Checking access
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
}


/* Start PAGE   */
$PAGE->https_required();

$PAGE->set_url($url);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->verify_https_required();
$PAGE->set_pagelayout('login');

/* User ID      */
$id             = $SESSION->user;

/* Clean SESSION Variables  */
unset($SESSION->user);

try {
    $user = get_complete_user_data('id',$id);
    complete_user_login($user);
    
    /**
     * @updateDate  10/11/2014
     * @author      eFaktor     (fbv)
     *
     * Description
     * Check if it is the first access. Then the user has to check and update his/her profile
     */
    require_once('../../first_access/locallib.php');

    if (!isguestuser($user) && !is_siteadmin($user->id)) {
        if (FirstAccess::has_to_update_profile($user->id)) {
            redirect(new moodle_url('/local/first_access/index.php',array('id'=>$user->id)));
            die();
        }else {
            /**
             * @updateDate      28/04/2014
             * @author          eFaktor     (fbv)
             *
             * Description
             * Check if the user has to update his/her profile
             */
            require_once('../../force_profile/forceprofilelib.php');
            if (ForceProfile::ForceProfile_HasToUpdateProfile($user->id)) {
                echo $OUTPUT->header();
                $url = new moodle_url('/local/force_profile/confirm_profile.php',array('id' => $user->id));
                echo $OUTPUT->notification(get_string('msg_force_update','local_force_profile'), 'notifysuccess');
                echo $OUTPUT->continue_button($url);
                echo $OUTPUT->footer();
                die();
            }else {

                /**
                 * @updateDate  15/08/2016
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Check if the redirect url has to be the course/activity
                 */
                if ((isset($SESSION->modlnk)) && (isset($SESSION->modid))) {
                    // Build url
                    if (substr($SESSION->modlnk,0,1) != '/') {
                        $SESSION->modlnk  = '/' . $SESSION->modlnk;
                    }
                    $redirect = new moodle_url($SESSION->modlnk . "?" . $SESSION->modid);
                }

                // test the session actually works by redirecting to self
                redirect($redirect);
            }//if_else_UpdateProfile
        }//if_first_access
    } else {
        require_logout();
        print_error('guestsarenotallowed');
    }//if_guest_user

}catch (Exception $ex) {
    redirect($errUrl);
}


echo $OUTPUT->header();
echo $OUTPUT->footer();
