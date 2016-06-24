<?php
/**
 * Created by PhpStorm.
 * User: paqui
 * Date: 24/06/16
 * Time: 10:16
 */

function test_cron(){
    global $CFG;

    /* Plugins Info */
    $pluginInfo     = get_config('local_test');

    $dbLog  = "START SCHEDULE LOCAL TEST CRON." . "\n"."\n";
    $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). "\n\n";
    if ($pluginInfo->cron_active) {
        /* Admin */
        $admin      = get_admin();
        $now        = time();
        $timezone   = $admin->timezone;
        $cronHour   = $pluginInfo->fs_auto_time;
        $cronMin    = $pluginInfo->fs_auto_time_minute;
        $date       = usergetdate($now, $timezone);

        $dbLog .= " Cron Hour: " . $cronHour . " Cron Minute: " . $cronMin . "\n\n";

    }else {
        $dbLog .= "No Active " . "\n\n";
    }//if_cronactivate

    error_log($dbLog, 3, $CFG->dataroot . "/LOCAL_TEST.log");
}