<?php
/**
 * Fellesdata Integration - Lib
 *
 * @package         local
 * @subpackage      fellesdata
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    01/02/2016
 * @author          eFaktor     (fbv)
 *
 */

//


function local_fellesdata_extends_navigation(global_navigation $navigation) {
    /* Variables    */
    global $USER;

    if (isloggedin()) {
        if (is_siteadmin($USER->id) || has_capability('local/fellesdata:manage', CONTEXT_SYSTEM::instance())) {
            $nodeTracker = $navigation->add(get_string('menu_title','local_fellesdata'));

            $nodBar = $nodeTracker->add(get_string('header_fellesdata','local_fellesdata'),new moodle_url('/local/fellesdata/mapping/mapping.php'));
        }//if_else
    }
}

function local_fellesdata_cron() {
    /* Variables */
    global $DB;
    $pluginInfo     = null;
    $admin          = null;
    $now            = null;
    $timezone       = null;
    $cronHour       = null;
    $cronMin        = null;
    $date           = null;
    $timeYesterday  = null;
    $fstExecution   = null;

    /* Plugins Info */
    $pluginInfo     = get_config('local_fellesdata');

    if ($pluginInfo->cron_active) {
        require_once('cron/fellesdatacron.php');

        /* Admin */
        $admin      = get_admin();
        $now        = time();
        $timezone   = $admin->timezone;
        $cronHour   = $pluginInfo->fs_auto_time;
        $cronMin    = $pluginInfo->fs_auto_time_minute;
        $date       = usergetdate($now, $timezone);

        /* Check if has to be run it    */
        if (isset($pluginInfo->lastcron)) {
            /* Calculate when it has to be triggered it */
            $timeYesterday  = mktime($cronHour, $cronMin, 0, $date['mon'], $date['mday'] - 1, $date['year']);

            if (($pluginInfo->lastexecution <= $timeYesterday)) {
                $fstExecution = false;
                FELLESDATA_CRON::cron($fstExecution);
                set_config('lastexecution', $now, 'local_microlearning');
            }
        }else {
            $fstExecution = true;
            FELLESDATA_CRON::cron($fstExecution);
            set_config('lastexecution', $now, 'local_microlearning');
        }//if_else_lastcron
    }else {
        mtrace('... FELLESDATA CRON DISABLE');
    }//if_cron_Active
}//local_fellesdata_cron