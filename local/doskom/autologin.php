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

$SESSION->user          = $_GET['id'];
$SESSION->ticket        = $_GET['ticket'];
$SESSION->RedirectPage  = $_GET['RedirectPage'];
$SESSION->LogoutUrl     = $_GET['LogoutUrl'];

redirect($url);

