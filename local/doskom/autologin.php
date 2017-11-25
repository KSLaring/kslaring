<?php
/**
 * Single Sing On - Autologin
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

/* PARAMS       */
/* Log In URL   */
$url        = new moodle_url('/local/doskom/login.php');

global $USER,$SESSION,$OUTPUT,$CFG;

// Checking access
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}

$SESSION->user          = (isset($_GET['id']) ? $_GET['id'] : 0);
$SESSION->ticket        = (isset($_GET['ticket']) ? $_GET['ticket'] : 0);
$SESSION->RedirectPage  = (isset($_GET['RedirectPage']) ? $_GET['RedirectPage'] :0);
$SESSION->LogoutUrl     = (isset($_GET['LogoutUrl']) ? $_GET['LogoutUrl'] : 0);

redirect($url);

