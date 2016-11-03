<?php
/**
 * ADFS Integration WebService - Settings
 *
 * @package         local
 * @subpackage      adfs
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    31/10/2015
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_adfs', get_string('pluginname','local_adfs'));
    $ADMIN->add('localplugins', $settings);

    /* Web Service  */
    /* Heading */
    $settings->add(new admin_setting_heading('local_adfs_settings', '', get_string('adfs_settings', 'local_adfs')));
    /* End Point    */
    $settings->add(new admin_setting_configtext('local_adfs/ks_point',get_string('ks_site','local_adfs'),'','',PARAM_TEXT,50));
    /* Web service  */
    $settings->add(new admin_setting_configtext('local_adfs/adfs_service',get_string('adfs_service','local_adfs'),'','',PARAM_TEXT,50));
    /* Token        */
    $settings->add(new admin_setting_configpasswordunmask('local_adfs/adfs_token',get_string('adfs_token','local_adfs'),'',''));
    
    /* ID Porten */
    $settings->add(new admin_setting_configtext('local_adfs/idporten',get_string('adfs_idporten','local_adfs'),'','',PARAM_TEXT,50));
}//if_config