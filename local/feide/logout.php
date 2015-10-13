<?php
/**
 * Feide Integration WebService - Logout
 *
 * @package         local
 * @subpackage      feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    21/09/2015
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../config.php');

$PAGE->set_url('/login/logout.php');
$PAGE->set_context(context_system::instance());

$sesskey    = optional_param('sesskey', '__notpresent__', PARAM_RAW); // we want not null default to prevent required sesskey warning
$login      = optional_param('loginpage', 0, PARAM_BOOL);
$redirect   = $CFG->wwwroot.'/index.php';

$authplugin = get_auth_plugin('saml');
$authplugin->logoutpage_hook();

//require_logout();
//redirect($redirect);