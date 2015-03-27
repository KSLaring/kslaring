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

function local_doskom_cron() {
    /* Plugins Info */
    $plugin_info     = get_config('local_doskom');

    /* Check if the cron is Activate    */
    if ($plugin_info->wsdoskom_cron_active) {
        require_once('cron/wsssocron.php');

        $date_hour  = date('H',time());
        $date_min   = date('i',time());
        $cron_hour  = $plugin_info->wsdoskom_auto_time;
        $cron_min   = $plugin_info->wsdoskom_auto_time_minute;

        if (($date_hour >= $cron_hour) && ($date_min >= $cron_min)) {
            if (isset($plugin_info->lastcron)) {
                $time = time() - (60*60*24);
                if ($plugin_info->lastcron <= $time){
                    WSDOSKOM_Cron::cron();
                }
            }else {
                WSDOSKOM_Cron::cron();
            }
        }
    }else {
        mtrace('... WSDOSKOM Cron Disabled');
    }
}//local_wssso_cron