<?php

/**
 * IMDI logout page
 * User: Urs Hunkler
 * Date: 2011-08-07
 *
 * The IMDI logout page logs the user out and redirects to the IMDI site.
 */

require_once( '../../config.php' );


$plugin     = get_config('local_autologin');
$returnurl = $plugin->Return_Link;

if( !isloggedin()) {
    // no confirmation, user has already logged out
    require_logout();
    redirect( $returnurl );
}

$authsequence = get_enabled_auth_plugins(); // auths, in sequence
foreach( $authsequence as $authname ) {
    $authplugin = get_auth_plugin( $authname );
    $authplugin->logoutpage_hook();
}

require_logout();
redirect( $returnurl );
