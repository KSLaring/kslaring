<?php
/**
 * Micro Learning - Library
 *
 * @package         local
 * @subpackage      microlearning
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      12/09/2014
 * @author          eFaktor     (fbv)
 *
 */

function local_microlearning_extends_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('local/microlearning:manage', context_course::instance($PAGE->course->id))) {
        return;
    }

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {

        $str_title = get_string('title_index', 'local_microlearning');
        $url = new moodle_url('/local/microlearning/index.php', array('id' => $PAGE->course->id));
        $micro_node = navigation_node::create($str_title,
                                              $url,
                                              navigation_node::TYPE_SETTING,'microlearning',
                                              'microlearning',
                                              new pix_icon('i/settings', $str_title)
                                              );
        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $micro_node->make_active();
        }
        $settingnode->add_node($micro_node,'users');
    }
}//local_microlearning_extend_settings_navigation

/**
 * @creationDate    06/12/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Cron - Micro Learning
 *
 * @updateDate      08/09/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Fix problem with the last execution and last cron
 */
function local_microlearning_cron() {
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
    $pluginInfo     = get_config('local_microlearning');

    /* Check if the cron is Activate    */
    if ($pluginInfo->micro_cron_active) {
        require_once('mode/calendar/calendarcronlib.php');
        require_once('mode/activity/activitycronlib.php');

        /* Admin */
        $admin      = get_admin();
        $now        = time();
        $timezone   = $admin->timezone;
        $cronHour   = $pluginInfo->micro_auto_time;
        $cronMin    = $pluginInfo->micro_auto_time_minute;
        $date       = usergetdate($now, $timezone);

        /* Check if has to be run it    */
        if (isset($pluginInfo->lastcron)) {
            /* Calculate when it has to be triggered it */
            $timeYesterday  = mktime($cronHour, $cronMin, 0, $date['mon'], $date['mday'] - 1, $date['year']);

            if (($pluginInfo->lastexecution <= $timeYesterday)) {
                Calendar_ModeCron::cron();
                Activity_ModeCron::cron();
                set_config('lastexecution', $now, 'local_microlearning');
            }
        }else {
            Calendar_ModeCron::cron();
            Activity_ModeCron::cron();
            set_config('lastexecution', $now, 'local_microlearning');
        }//if_else_lastcron
    }else {
        mtrace('... Micro Learning Cron Disabled');
    }

}//function_cron
