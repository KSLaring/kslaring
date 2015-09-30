<?php
/**
 * KS Læring Integration - Logout
 *
 * @package         local
 * @subpackage      wsks/feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/09/2015
 * @author          eFaktor     (fbv)
 */

require_once('../../../config.php');

$PAGE->set_url('/login/logout.php');
$PAGE->set_context(context_system::instance());


unset($SESSION->ksSource);

$redirect = $CFG->wwwroot.'/';

require_logout();

redirect($redirect);