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

require( '../../../config.php' );
require_once ('../wsadfslib.php');

/* PARMAS   */
/* User ID      */
$id             = $SESSION->user;

$url            = new moodle_url('/local/wsks/adfs/login.php');
$index          = new moodle_url('/login/index.php');
$errUrl         = new moodle_url('/local/wsks/adfs/error.php');

/* Clean SESSION Variables  */
unset($SESSION->user);

/* Start PAGE   */
$PAGE->https_required();

$PAGE->set_url($url);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->verify_https_required();
$PAGE->set_pagelayout('login');

try {
    $user = get_complete_user_data('id',$id);
    complete_user_login($user,true);

    redirect($index);
}catch (Exception $ex) {
    redirect($errUrl);
}
