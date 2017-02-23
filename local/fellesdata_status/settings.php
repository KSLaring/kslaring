<?php
/**
 * Fellesdata Status Integration - Settings
 *
 * @package         local
 * @subpackage      fellesdata_status
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    23/02/2017
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_fellesdata_status', get_string('pluginname','local_fellesdata_status'));
    $ADMIN->add('localplugins', $settings);
    

    /* Mail Admin               */
    $settings->add(new admin_setting_configtext('local_fellesdata_status/mail_notification',get_string('basic_notify', 'local_fellesdata'), '', ''));


    /* Fellesdata Heading   */
    $settings->add(new admin_setting_heading('local_fellesdata_status_FS_settings', '', get_string('fellesdata_settigns', 'local_fellesdata')));
    /* End Point    */
    $settings->add(new admin_setting_configtext('local_fellesdata_status/fs_point',get_string('fellesdata_end','local_fellesdata'),'','',PARAM_TEXT,50));

    
    /* User / Password  */
    $settings->add(new admin_setting_configpasswordunmask('local_fellesdata_status/fs_username',get_string('username'),'',''));
    $settings->add(new admin_setting_configpasswordunmask('local_fellesdata_status/fs_password',get_string('password'),'',''));



    /* KS Læring Heading    */
    $settings->add(new admin_setting_heading('local_fellesdata_status_KS_settings', '', get_string('ks_settings', 'local_fellesdata')));
    /* End Point    */
    $settings->add(new admin_setting_configtext('local_fellesdata_status/ks_point',get_string('ks_end_point','local_fellesdata'),'','',PARAM_TEXT,50));
    /* Token        */
    $settings->add(new admin_setting_configpasswordunmask('local_fellesdata_status/kss_token',get_string('ks_token','local_fellesdata'),'',''));

    /* Municipality */
    $settings->add(new admin_setting_configtext('local_fellesdata_status/ks_muni',get_string('ks_municipality','local_fellesdata'),'','',PARAM_TEXT,50));
}//if_config