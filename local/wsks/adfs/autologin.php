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

global $USER,$OUTPUT,$SESSION;
require( '../../../config.php' );

/* PARAMS       */
/* Log In URL   */
$url        = new moodle_url('/local/wsks/adfs/login.php');

// Checking access
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
}


$SESSION->user          = (isset($_GET['id']) ? $_GET['id'] :0);

/**
 * @updateDate  15/08/2016
 * @author      eFaktor     (fbv)
 *
 * Description
 * Activity/Course link
 */
if (isset($_GET['modlnk']) && isset($_GET['modid'])) {
    $SESSION->modlnk = str_replace('\*','/',str_replace('\=','=',$_GET['modlnk']));
    $SESSION->modid  = str_replace('\=','=',$_GET['modid']);
}

redirect($url);