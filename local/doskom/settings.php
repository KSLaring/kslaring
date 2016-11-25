<?php
/**
 * WSDOSKOM - Cron Settings
 *
 * @package         local
 * @subpackage      doskom/cron
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      27/02/2015
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_doskom', get_string('pluginame','local_doskom'));
    $ADMIN->add('localplugins', $settings);

    // Cron Activate/Deactivate.
    $options = array('0' => get_string('cron_deactivate','local_doskom'),
                     '1' => get_string('cron_activate','local_doskom'));
    $settings->add(new admin_setting_configselect('local_doskom/wsdoskom_cron_active', new lang_string('active'),  '', 1, $options));

    //Time
    //$settings->add(new admin_setting_configtime('local_doskom/wsdoskom_auto_time','wsdoskom_auto_time_minute', new lang_string('executeat'), '', array('h' => 0, 'm' => 0)));

    /* Web Service  */
    $settings->add(new admin_setting_configtext('local_doskom/wsdoskom_end_point',get_string('end_point','local_doskom'),'','',PARAM_TEXT,50));
    //$settings->add(new admin_setting_configpasswordunmask('local_doskom/local_wsdoskom_username',get_String('username'),'',''));
    //$settings->add(new admin_setting_configpasswordunmask('local_doskom/local_wsdoskom_password',get_string('password'),'',''));


    /* Production or Pilot Site */
    $settings->add(new admin_setting_configcheckbox('local_doskom/wsdoskom_end_point_production',
                                                    get_string('end_point_production','local_doskom'),
                                                    get_string('end_point_production_desc','local_doskom'),1));
}//if