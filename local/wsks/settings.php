<?php
/**
 * KS Læring Integration - Settings
 *
 * @package         local
 * @subpackage      wsks
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/09/2015
 * @author          eFaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_wsks', get_string('pluginname','local_wsks'));
    $ADMIN->add('localplugins', $settings);

    /* Login Feide  */
    /* Heading */
    $settings->add(new admin_setting_heading('local_wsks_feide_settings', '', get_string('feide_settings', 'local_wsks')));
    /* Settings */
    /* Activate/Deactivate  */
    /* Activate Express Login   */
    $settings->add(new admin_setting_configcheckbox('local_wsks/activate_feide',
                                                    get_string('set_activate', 'local_wsks'),
                                                    get_string('set_activate_desc', 'local_wsks'), 0));
    /* End Point / Web Service  */
    $settings->add(new admin_setting_configtext('local_wsks/feide_point',
                                                 get_string('feide_site','local_wsks'),
                                                 get_string('feide_site_desc','local_wsks'),'',PARAM_TEXT,50));
    $settings->add(new admin_setting_configtext('local_wsks/feide_service',get_string('feide_service','local_wsks'),'','',PARAM_TEXT,50));
    $settings->add(new admin_setting_configpasswordunmask('local_wsks/feide_token',get_string('feide_token','local_wsks'),'',''));
}//if_hasconfig
