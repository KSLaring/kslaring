<?php
/**
 * Created by PhpStorm.
 * User: paqui
 * Date: 13/03/17
 * Time: 10:59
 */

function status_cron() {
    global $CFG;

    try {
        //require_once('cron/statuscron.php');
        //require_once('lib/statuslib.php');
        //require_once('../fellesdata/lib/fellesdatalib.php');
        
        $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' HOLA LOCAL STATUS . ' . "\n";

        $dblog .= ' BYE BYE LOCAL STATUS . ' . "\n";
        error_log($dblog, 3, $CFG->dataroot . "/STATUS_PAQUI.log");
    }catch (Exception $ex) {
        throw $ex;
    }
}//fellesdata_cron