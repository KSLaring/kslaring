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
    /* Variables    */
    global $DB;
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


    /* Check if the cron is Activate    */
    if ($pluginInfo->wsdoskom_cron_active) {
        require_once('cron/wsssocron.php');

        /* Admin */
        $admin      = get_admin();
        $now        = time();
        $timezone   = $admin->timezone;
        $cronHour   = $pluginInfo->wsdoskom_auto_time;
        $cronMin    = $pluginInfo->wsdoskom_auto_time_minute;
        $date       = usergetdate($now, $timezone);

        /* Check if has to be run it    */
        if (isset($pluginInfo->lastcron)) {
            /* Calculate when it has to be triggered it */
            $timeYesterday  = mktime($cronHour, $cronMin, 0, $date['mon'], $date['mday'] - 1, $date['year']);

            if (($pluginInfo->lastexecution <= $timeYesterday)) {
                WSDOSKOM_Cron::cron();
                set_config('lastexecution', $now, 'local_doskom');
            }else {

            }
        }else {
            WSDOSKOM_Cron::cron();
            set_config('lastexecution', $now, 'local_doskom');
        }//if_else_lastcron
    }else {
        mtrace('... WSDOSKOM Cron Disabled');
    }
}//local_wssso_cron