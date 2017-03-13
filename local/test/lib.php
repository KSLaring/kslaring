<?php
/**
 * Created by PhpStorm.
 * User: paqui
 * Date: 13/03/17
 * Time: 10:59
 */

function test_cron() {
    global $CFG;

    try {
        $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' HOLA LOCAL TESTS . ' . "\n";

        $dblog .= ' BYE BYE LOCAL TEST . ' . "\n";
        error_log($dblog, 3, $CFG->dataroot . "/TEST_PAQUI.log");
    }catch (Exception $ex) {
        throw $ex;
    }
}//fellesdata_cron