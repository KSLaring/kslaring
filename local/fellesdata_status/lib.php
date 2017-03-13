<?php
/**
 * Fellesdata Status Integration - Lib
 *
 * @package         local
 * @subpackage      fellesdata_status
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    23/02/2017
 * @author          eFaktor     (fbv)
 *
 */
function fellesdata_status_cron() {
    global $CFG;
    $plugin         = null;

    try {
        require_once('cron/statuscron.php');
        require_once('lib/statuslib.php');
        require_once('../fellesdata/lib/fellesdatalib.php');

        // Plugin info
        $plugin = get_config('local_fellesdata');

        // Log
        $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START CRON . ' . "\n";
        $dblog .= $plugin->ks_muni . "\n";
        error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

        //\STATUS_CRON::cron($plugin);
    }catch (Exception $ex) {
        $dbLog = $ex->getMessage() . "\n" ."\n";
        $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR - STATUS CRON . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        throw $ex;
    }
}//fellesdata_cron