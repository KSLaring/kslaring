<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 02/02/16
 * Time: 12:45
 * To change this template use File | Settings | File Templates.
 */

require( '../../config.php' );
require_once('../../local/fellesdata/lib/fellesdatalib.php');
require_once('cron/statuscron.php');
require_once('lib/statuslib.php');

require_login();

/* PARAMS */
$option = optional_param('op',0,PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/status/Test.php');

/* Print Header */
echo $OUTPUT->header();

try {
    echo " TESTING FELLESDATA STATUS CRON " . "</br>";
    echo "Start ... " . "</br>";

    // Plugin info
    $plugin = get_config('local_fellesdata');

    // Call cron
    $companies = \STATUS::get_new_fs_organizations($plugin);
}catch (Exception $ex) {
    throw $ex;
}//try_catch

echo "</br> Finish ... " . "</br>";

/* Print Footer */
echo $OUTPUT->footer();