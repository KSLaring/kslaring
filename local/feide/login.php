<?php
/**
 * Feide Integration WebService - Login
 *
 * @package         local
 * @subpackage      feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    28/09/2015
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../config.php');

$PAGE->set_url('/login/logout.php');
$PAGE->set_context(context_system::instance());

$SESSION->ksSource = 'KS';

$url = new moodle_url('/auth/saml/index.php');
redirect($url);