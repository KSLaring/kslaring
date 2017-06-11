<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 02/02/16
 * Time: 12:45
 * To change this template use File | Settings | File Templates.
 */

require( '../../config.php' );
require_once('cron/fellesdatacron.php');
require_once('lib/fellesdatalib.php');
require_once('lib/suspiciouslib.php');

require_login();

/* PARAMS */
$option = optional_param('op',0,PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/fellesdata/Test.php');

/* Print Header */
echo $OUTPUT->header();

try {
    echo " TESTING FELLESDATA CRON " . "</br>";
    echo "Start ... " . "</br>";

    if (!isset($SESSION->manual)) {
        $SESSION->manual = true;
    }

    $pluginInfo     = get_config('local_fellesdata');

    if ($option) {
        if ($option == 20) {
            //$pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_JOBROLES . '.txt';
            //strlen($response);
           echo "Sending suspicious notifications..." . "</br>";

            // Send Notifications
            suspicious::send_suspicious_notifications($pluginInfo);
            // Send Reminder
            suspicious::send_suspicious_notifications($pluginInfo,true);
        }else {
            $SESSION->manual = true;
            FELLESDATA_CRON::cron_manual(true,$option);
        }
    }else {
        echo " --> " . FS_CRON::can_run();
    }
}catch (Exception $ex) {
    throw $ex;
}//try_catch

echo "</br> Finish ... " . "</br>";

/* Print Footer */
echo $OUTPUT->footer();
