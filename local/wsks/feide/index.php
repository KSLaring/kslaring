<?php
/**
 * KS Læring Integration - Login via Feide
 *
 * @package         local
 * @subpackage      wsks/feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/09/2015
 * @author          eFaktor     (fbv)
 */

include_once('../../../config.php');
require_once('feidelib.php');

$PAGE->set_url("$CFG->httpswwwroot/login/index.php");
$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_pagelayout('login');


/* PARAMS   */
$userInfo   = null;
$userId     = null;
$errURL     = new moodle_url('/local/wsks/feide/error.php',array('er' => FEIDE_ERR_PROCESS));
$login      = null;

if (!isset($_SESSION['user'])) {
    redirect($errUrl);
}else {
    $userInfo = $_SESSION['user'];
}

$login = KS_FEIDE::LoginUser($userInfo);
if ($login) {
    $user = get_complete_user_data('username', $userInfo['username']);
    $SESSION->ksSource = 'Feide';

    /**
     * @updateDate  10/11/2014
     * @author      eFaktor     (fbv)
     *
     * Description
     * Check if it is the first access. Then the user has to check and update his/her profile
     */
    require_once('../../first_access/locallib.php');

    if (!isguestuser($user)) {
        if (FirstAccess::HasToUpdate_Profile($USER->id)) {
            redirect(new moodle_url('/local/first_access/index.php',array('id'=>$USER->id)));
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
            if (ForceProfile::ForceProfile_HasToUpdateProfile($USER->id)) {
                echo $OUTPUT->header();
                $url = new moodle_url('/local/force_profile/confirm_profile.php',array('id' => $USER->id));
                echo $OUTPUT->notification(get_string('msg_force_update','local_force_profile'), 'notifysuccess');
                echo $OUTPUT->continue_button($url);
                echo $OUTPUT->footer();
                die();
            }else {
                // test the session actually works by redirecting to self
                redirect($CFG->wwwroot);
            }//if_else_UpdateProfile
        }//if_first_access
    } else {
        require_logout();
        redirect($CFG->wwwroot);
    }//if_guest_user
}else {
    redirect($errURL);
}//if_login

echo $OUTPUT->header();
echo $OUTPUT->footer();
