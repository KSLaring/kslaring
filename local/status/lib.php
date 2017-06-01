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
    /* Variables */
    global $CFG;
    $plugin = null;
    $dblog  = null;

    try {
        require_once('cron/statuscron.php');
        require_once('lib/statuslib.php');
        require_once('../../local/fellesdata/lib/fellesdatalib.php');

        // Plugin info
        $plugin = get_config('local_fellesdata');
        
        // Call cron
        \STATUS_CRON::cron($plugin);

    }catch (Exception $ex) {
        throw $ex;
    }
}//fellesdata_cron