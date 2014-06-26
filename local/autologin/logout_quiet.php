<?php

/**
 * IMDI logout quiet page
 * User: Urs Hunkler
 * Date: 2011-12-01
 *
 * The IMDI logout page logs the user out when the user leaves the page.
 */

require_once( '../../config.php' );

if( !isloggedin())
{
    // no confirmation, user has already logged out
    require_logout();
}

$authsequence = get_enabled_auth_plugins(); // auths, in sequence
foreach( $authsequence as $authname )
{
    $authplugin = get_auth_plugin( $authname );
    $authplugin->logoutpage_hook();
}

require_logout();
