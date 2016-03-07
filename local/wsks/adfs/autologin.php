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

/* PARAMS       */
/* Log In URL   */
$url        = new moodle_url('/local/wsks/adfs/login.php');

$SESSION->user          = $_GET['id'];

redirect($url);