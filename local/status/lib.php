<?php
/**
 * Fellesdata Status Integration - Lib
 *
 * @package         local
 * @subpackage      status
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    23/02/2017
 * @author          eFaktor     (fbv)
 *
 */

function status_cron() {
    global $CFG;

    try {
        require_once('cron/statuscron.php');
        require_once('lib/statuslib.php');
        //require_once('../fellesdata/lib/fellesdatalib.php');
        
        $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' HOLA LOCAL STATUS . ' . "\n";

        $dblog .= ' BYE BYE LOCAL STATUS . ' . "\n";
        error_log($dblog, 3, $CFG->dataroot . "/STATUS_PAQUI.log");
    }catch (Exception $ex) {
        throw $ex;
    }
}//fellesdata_cron