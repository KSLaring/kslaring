<?php

/**
 * Autologin Plugin  - Settings
 *
 * @package         local
 * @subpackage      autologin
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    15/11/2013
 * @author          eFaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_autologin', get_string('pluginname','local_autologin'));
    $ADMIN->add('localplugins', $settings);

    /* Return Link */
    $settings->add(new admin_setting_configtext('local_autologin/Return_Link',
                                                get_string('leave_link','local_autologin'),
                                                '',
                                                get_string('leave_value','local_autologin'),
                                                PARAM_TEXT,50));
}
