<?php
/**
 * Express Login - Settings
 *
 * Description
 *
 * @package         local
 * @subpackage      express_login
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      26/11/2014
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_express_login', get_string('pluginname','local_express_login'));
    $ADMIN->add('localplugins', $settings);

    /**
     * Description Settings
     */
    $settings->add(new admin_setting_heading('local_express_login_settings', '', get_string('settings_desc', 'local_express_login')));

    /* Activate Express Login   */
    $settings->add(new admin_setting_configcheckbox('local_express_login/activate_express',
                                                    get_string('set_activate', 'local_express_login'),
                                                    get_string('set_activate_desc', 'local_express_login'), 0));

    /* Deny Identical Digits    */
    $settings->add(new admin_setting_configcheckbox('local_express_login/deny_identical',
                                                    get_string('set_deny', 'local_express_login'),
                                                    get_string('set_deny_desc', 'local_express_login'), 0));

    /* Expire After             */
    $settings->add(new admin_setting_configduration('local_express_login/expiry_after',
                                                    get_string('set_expire', 'local_express_login'),
                                                    get_string('set_expire_desc', 'local_express_login'), 86400*60, 86400));
    /* Force New Express login tokens   */
    $settings->add(new admin_setting_configcheckbox('local_express_login/force_token',
                                                    get_string('set_force', 'local_express_login'),
                                                    get_string('set_force_desc', 'local_express_login'), 0));

    /* Minimum number of digits         */
    $options = array('4','6','8');
    $settings->add(new admin_setting_configselect('local_express_login/minimum_digits',
                                                  get_string('set_minimum', 'local_express_login'),
                                                  get_string('set_minimum', 'local_express_login'), 6, $options));

    /* Encrypthon Phrase                */
    //$settings->add(new admin_setting_configtext('local_express_login/encrypt_phrase',
    //                                            get_string('set_encryption','local_express_login'),
    //                                            get_string('set_encryption','local_express_login'),
    //                                            '',
    //                                            PARAM_TEXT,50));

    /**
     * CRON Settings
     */
    $settings->add(new admin_setting_heading('local_express_login_cron_settings', get_string('cron_settings', 'local_express_login'),''));
    // Cron Activate/Deactivate.
    $options = array('0' => get_string('cron_deactivate','local_express_login'),
                     '1' => get_string('cron_activate','local_express_login'));
    $settings->add(new admin_setting_configselect('local_express_login/express_cron_active', new lang_string('active'),  '', 1, $options));
    //Time
    //$settings->add(new admin_setting_configtime('local_express_login/express_auto_time','express_auto_time_minute', new lang_string('executeat'), '', array('h' => 0, 'm' => 0)));
}//if_hasconfig
