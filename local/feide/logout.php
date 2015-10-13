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
$ks         = optional_param('ks',0,PARAM_INT);


if ($ks) {
    /* Back to KS   */
    /* Plugin Info */
    $pluginInfo = get_config('local_feide');
    $redirect =  $pluginInfo->ks_point . "/local/wsks/feide/logout.php";
    redirect($redirect);
    die;
}else {
    $authplugin = get_auth_plugin('saml');
    $authplugin->logoutpage_hook();
}