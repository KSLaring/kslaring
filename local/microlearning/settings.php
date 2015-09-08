<?php
/**
 * Micro Learning - Settings
 *
 * Description
 *
 * @package         local
 * @subpackage      microlearning
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    06/12/2014
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_microlearning', get_string('pluginname','local_microlearning'));
    $ADMIN->add('localplugins', $settings);

    // Cron Activate/Deactivate.
    $options = array('0' => get_string('cron_deactivate','local_microlearning'),
                     '1' => get_string('cron_activate','local_microlearning'));
    $settings->add(new admin_setting_configselect('local_microlearning/micro_cron_active', new lang_string('active'),  '', 1, $options));

    //Time
    $settings->add(new admin_setting_configtime('local_microlearning/micro_auto_time','micro_auto_time_minute', new lang_string('executeat'), '', array('h' => 0, 'm' => 0)));


}//if