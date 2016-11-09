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

    $ADMIN->add('modules',new admin_category('slaves', get_string('lst_slaves','local_wsks')));

    /**
     * Add Menu
     */
    /**
     * List of Slaves Systems
     */
    $url = new moodle_url('/local/wsks/slaves/classes/slaves.php');
    $ADMIN->add('slaves', new admin_externalpage('slaves_systems', get_string('lst_slaves','local_wsks'), $url));
    /**
     * Add new slave
     */
    $url = new moodle_url('/local/wsks/slaves/classes/add_slave.php');
    $ADMIN->add('slaves', new admin_externalpage('new_slaves_systems', get_string('add_slave','local_wsks'), $url));
    /**
     * Update slaves systems
     */
    $url = new moodle_url('/local/wsks/slaves/classes/update_slaves.php');
    $ADMIN->add('slaves', new admin_externalpage('update_slaves_systems', get_string('update_slaves','local_wsks'), $url));

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


    $settings->add(new admin_setting_heading('local_wsks_slave_settings', '', get_string('slave_settings', 'local_wsks')));
    $settings->add(new admin_setting_configtext('local_wsks/slaves_service',get_string('slave_service','local_wsks'),'','',PARAM_TEXT,50));
}//if_hasconfig
