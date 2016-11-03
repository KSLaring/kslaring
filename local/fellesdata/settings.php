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
    $settings = new admin_settingpage('local_fellesdata', get_string('pluginname','local_fellesdata'));
    $ADMIN->add('localplugins', $settings);

    // Cron Activate/Deactivate.
    $options = array('0' => get_string('cron_deactivate','local_fellesdata'),
                     '1' => get_string('cron_activate','local_fellesdata'));
    $settings->add(new admin_setting_configselect('local_fellesdata/cron_active', new lang_string('active'),  '', 1, $options));

    //Time
    //$settings->add(new admin_setting_configtime('local_fellesdata/fs_auto_time','fs_auto_time_minute', new lang_string('executeat'), '', array('h' => 0, 'm' => 0)));

    /* Mail Admin               */
    $settings->add(new admin_setting_configtext('local_fellesdata/mail_notification',get_string('basic_notify', 'local_fellesdata'), '', ''));


    /* Fellesdata Heading   */
    $settings->add(new admin_setting_heading('local_fellesdata_FS_settings', '', get_string('fellesdata_settigns', 'local_fellesdata')));
    /* End Point    */
    $settings->add(new admin_setting_configtext('local_fellesdata/fs_point',get_string('fellesdata_end','local_fellesdata'),'','',PARAM_TEXT,50));
    /* Import Days  */
    $settings->add(new admin_setting_configtext('local_fellesdata/fs_days',
                                                get_string('fellesdata_days','local_fellesdata'),
                                                '',
                                                get_string('fellesdata_default_days','local_fellesdata'),PARAM_TEXT,8));
    /* System   */
    $srcOptions = array('0' => 'ADFS',
                        '1' => 'AGRESSO',
                        '2' => 'VISMA');
    $settings->add(new admin_setting_configselect('local_fellesdata/fs_source',
                                                get_string('fellesdata_source','local_fellesdata'),
                                                get_string('fellesdata_source_desc','local_fellesdata'),0,$srcOptions));
    /* User / Password  */
    $settings->add(new admin_setting_configpasswordunmask('local_fellesdata/fs_username',get_string('username'),'',''));
    $settings->add(new admin_setting_configpasswordunmask('local_fellesdata/fs_password',get_string('password'),'',''));



    /* KS Læring Heading    */
    $settings->add(new admin_setting_heading('local_fellesdata_KS_settings', '', get_string('ks_settings', 'local_fellesdata')));
    /* End Point    */
    $settings->add(new admin_setting_configtext('local_fellesdata/ks_point',get_string('ks_end_point','local_fellesdata'),'','',PARAM_TEXT,50));
    /* Token        */
    $settings->add(new admin_setting_configpasswordunmask('local_fellesdata/kss_token',get_string('ks_token','local_fellesdata'),'',''));

    /* Municipality */
    $settings->add(new admin_setting_configtext('local_fellesdata/ks_muni',get_string('ks_municipality','local_fellesdata'),'','',PARAM_TEXT,50));

    ///* Hierarchy Municipality   */
    //$settings->add(new admin_setting_configtext('local_fellesdata/ks_muni_level',get_string('ks_hierarchy','local_fellesdata'),'','',PARAM_TEXT,50));
}//if_config