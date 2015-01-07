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
}//local_microlearning_extends_settings_navigation

/**
 * @creationDate    06/12/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Cron - Micro Learning
 */
function local_microlearning_cron() {
    /* Plugins Info */
    $plugin_info     = get_config('local_microlearning');

    /* Check if the cron is Activate    */
    if ($plugin_info->micro_cron_active) {
        require_once('mode/calendar/calendarcronlib.php');
        require_once('mode/activity/activitycronlib.php');

        $date_hour  = date('H',time());
        $date_min   = date('i',time());
        $cron_hour  = $plugin_info->micro_auto_time;
        $cron_min   = $plugin_info->micro_auto_time_minute;

        if (($date_hour >= $cron_hour) && ($date_min >= $cron_min)) {
            $time = time() - (60*60*24);
            if ($plugin_info->lastcron <= $time) {
                Calendar_ModeCron::cron();
                Activity_ModeCron::cron();
            }
        }
    }else {
        mtrace('... Micro Learning Cron Disabled');
    }

}//function_cron