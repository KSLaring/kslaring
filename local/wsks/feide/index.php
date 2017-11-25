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

global $CFG,$PAGE,$SESSION,$OUTPUT,$USER;

$PAGE->set_url("$CFG->httpswwwroot/login/index.php");
$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_pagelayout('login');

// Checking access
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}
// Params
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
    $SESSION->ksSource = 'Feide';

    /**
     * @updateDate  10/11/2014
     * @author      eFaktor     (fbv)
     *
     * Description
     * Check if it is the first access. Then the user has to check and update his/her profile
     */
    require_once('../../first_access/locallib.php');

    if (!isguestuser($login) && !is_siteadmin($login->id)) {
        if (FirstAccess::has_to_update_profile($login->id)) {
            redirect(new moodle_url('/local/first_access/index.php',array('id'=>$login->id)));
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
            if (ForceProfile::ForceProfile_HasToUpdateProfile($login->id)) {
                echo $OUTPUT->header();
                $url = new moodle_url('/local/force_profile/confirm_profile.php',array('id' => $login->id));
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
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
        echo $OUTPUT->continue_button($CFG->wwwroot);
        echo $OUTPUT->footer();
    }//if_guest_user
}else {
    redirect($errURL);
}//if_login

echo $OUTPUT->header();
echo $OUTPUT->footer();
