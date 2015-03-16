<?php
/**
 * Completion Event -  Settings
 *
 * @package         local
 * @subpackage      completion
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      17/02/2015
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_completion', get_string('pluginname','local_completion'));
    $ADMIN->add('localplugins', $settings);

    /* Completion Event */
    $options = array('0' => get_string('completion_disabled','local_completion'),
                     '1' => get_string('completion_enabled','local_completion'));
    $settings->add(new admin_setting_configselect('local_completion/completion_activate', new lang_string('active'),  '', 1, $options));

    /* Web Service  */
    $settings->add(new admin_setting_configtext('local_completion/completion_end_point',get_string('completion_end_point','local_completion'),'','',PARAM_TEXT,50));
    $settings->add(new admin_setting_configpasswordunmask('local_completion/completion_username',get_String('username'),'',''));
    $settings->add(new admin_setting_configpasswordunmask('local_completion/completion_password',get_string('password'),'',''));
}//if_config