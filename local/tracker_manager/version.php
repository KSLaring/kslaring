<?PHP // $Id: version.php,v 3.1.8

///////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of certificate
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
///////////////////////////////////////////////////////////////////////////////

// requires certificate module 2008080904
$plugin->version  = 2015012400;  // The current module version (Date: YYYYMMDDXX)
//$plugin->requires = 2007101506;  // Requires this Moodle version
//$plugin->cron     = 0;           // Period for cron to check this module (secs)
$plugin->component = 'local_tracker_manager';
?>