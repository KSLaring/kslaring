<?php
/**
 * WSDOSKOM - Cron
 *
 * @package         local
 * @subpackage      wsdoskom/cron
 * @copyright       2015        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      27/02/2015
 * @author          eFaktor     (fbv)
 *
 */

function doskom_cron() {
    /* Variables    */
    global $DB,$CFG;
    $pluginInfo     = null;
    $admin          = null;
    $now            = null;
    $timezone       = null;
    $cronHour       = null;
    $cronMin        = null;
    $date           = null;
    $timeYesterday  = null;

    /* Plugins Info */
    $pluginInfo     = get_config('local_doskom');


    try {
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START CRON ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/doskom.log");
        
        /* Library */
        require_once('cron/wsssocron.php');

        /* First execution or no */
        $activate = get_config('local_doskom','wsdoskom_cron_active');
        if ($activate) {
            WSDOSKOM_Cron::cron();
            
            set_config('lastexecution', time(), 'local_doskom');
        }else {
            mtrace('... WSDOSKOM Cron Disabled');
        }
    }catch (Exception $ex) {
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' ERROR CRON ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/doskom.log");
        
        throw $ex;
    }
}//local_wssso_cron