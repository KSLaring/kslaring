<?php

/**
 * Single Sing On - Login
 *
 * @package         local
 * @subpackage      doskom
 * @copyright       2015 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    20/02/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Autologin for users that are comming from a different system
 */

require( '../../config.php' );
require_once ('lib/wsdoskomlib.php');

global $SESSION,$USER,$PAGE,$CFG,$OUTPUT;

// Params
// User Id
$id             = (isset($SESSION->user) ? $SESSION->user : 0);
// User token
$key            = (isset($SESSION->ticket) ? $SESSION->ticket : 0);
// Where the user will be redirected
$RedirectPage   = (isset($SESSION->RedirectPage) ? $SESSION->RedirectPage : 0);
// Where the user will be redirected after logging out
$LogoutUrl      = (isset($SESSION->LogoutUrl) ? $SESSION->LogoutUrl : 0);
$url            = new moodle_url('/local/doskom/login.php');
$return         = new moodle_url($CFG->wwwroot);

// Clean SESSION Variables
unset($SESSION->user);
unset($SESSION->ticket);
unset($SESSION->RedirectPage);


// Start PAGE
$PAGE->https_required();

$PAGE->set_url($url);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->verify_https_required();
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

if (!$id || !$key || !$RedirectPage || !$LogoutUrl) {
    // Print Header
    echo $OUTPUT->header();

    echo $OUTPUT->notification(get_string('err_authenticate','local_doskom'), 'notifysuccess');
    echo $OUTPUT->continue_button($return);

    // Print Footer
    echo $OUTPUT->footer();
}else {
// Authenticate the user to log in
$authenticated = wsdoskom::authenticate_user($id,$key);

if ($authenticated) {
    wsdoskom::delete_key($authenticated);
    $user = get_complete_user_data('id',$id);
    complete_user_login($user);

    redirect($RedirectPage);
}else {
    // Print Header
    echo $OUTPUT->header();

    echo $OUTPUT->notification(get_string('err_authenticate','local_doskom'), 'notifysuccess');
    echo '<br>';
    if ($LogoutUrl) {
        $return = $LogoutUrl;
    }//if_back
    echo $OUTPUT->continue_button($return);

    // Print Footer
    echo $OUTPUT->footer();
    }//if_else_authenticated
}

