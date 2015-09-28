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
    $SESSION->ksSource = 'Feide';
    redirect($CFG->wwwroot);
}else {
    redirect($errURL);
}//if_login

echo $OUTPUT->header();
echo $OUTPUT->footer();
