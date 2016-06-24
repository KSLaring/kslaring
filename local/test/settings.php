<?php
/**
 * Fellesdata Integration - Settings
 *
 * @package         local
 * @subpackage      fellesdata
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    01/02/2016
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_test', get_string('pluginname','local_test'));
    $ADMIN->add('localplugins', $settings);

    // Cron Activate/Deactivate.
    $options = array('0' => get_string('cron_deactivate','local_test'),
                     '1' => get_string('cron_activate','local_test'));
    $settings->add(new admin_setting_configselect('local_test/cron_active', new lang_string('active'),  '', 1, $options));

    //Time
    $settings->add(new admin_setting_configtime('local_test/fs_auto_time','fs_auto_time_minute', new lang_string('executeat'), '', array('h' => 0, 'm' => 0)));
}//if_config