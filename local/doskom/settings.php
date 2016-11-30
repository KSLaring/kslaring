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
    $settings = new admin_settingpage('local_doskom', get_string('pluginname','local_doskom'));
    $ADMIN->add('localplugins', $settings);

    // Cron Activate/Deactivate.
    $options = array('0' => get_string('cron_deactivate','local_doskom'),
                     '1' => get_string('cron_activate','local_doskom'));
    $settings->add(new admin_setting_configselect('local_doskom/wsdoskom_cron_active', new lang_string('active'),  '', 1, $options));
    
    // Web Service
    $settings->add(new admin_setting_configtext('local_doskom/wsdoskom_end_point',get_string('end_point','local_doskom'),'','',PARAM_TEXT,50));

    // Production or Pilot Site 
    $settings->add(new admin_setting_configcheckbox('local_doskom/wsdoskom_end_point_production',
                                                    get_string('end_point_production','local_doskom'),
                                                    get_string('end_point_production_desc','local_doskom'),1));
}//if