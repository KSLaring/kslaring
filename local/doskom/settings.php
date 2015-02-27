<?php
/**
 * WSDOSKOM - Cron Settings
 *
 * @package         local
 * @subpackage      wsdoskom/cron
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      27/02/2015
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_wsdoskom', get_string('cron_wsso','local_wsdoskom'));
    $ADMIN->add('localplugins', $settings);

    // Cron Activate/Deactivate.
    $options = array('0' => get_string('cron_deactivate','local_wsdoskom'),
                     '1' => get_string('cron_activate','local_wsdoskom'));
    $settings->add(new admin_setting_configselect('local_wsdoskom/wsdoskom_cron_active', new lang_string('active'),  '', 1, $options));

    //Time
    $settings->add(new admin_setting_configtime('local_wsdoskom/wsdoskom_auto_time','wssso_auto_time_minute', new lang_string('executeat'), '', array('h' => 0, 'm' => 0)));

    /* Web Service  */
    $settings->add(new admin_setting_configtext('local_wsdoskom/wsdoskom_end_point',get_string('end_point','local_wsdoskom'),'','',PARAM_TEXT,50));
    $settings->add(new admin_setting_configpasswordunmask('local_wsdoskom/local_wsdoskom_username',get_String('username'),'',''));
    $settings->add(new admin_setting_configpasswordunmask('local_wsdoskom/local_wsdoskom_password',get_string('password'),'',''));
}//if