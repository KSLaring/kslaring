<?php
/**
 * Invoice Enrolment Method - Settings
 *
 * @package         enrol
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    25/09/2014
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    /**
     * General Settings
     */
    $settings->add(new admin_setting_heading('enrol_invoice_settings', '', get_string('pluginname_desc', 'enrol_invoice')));

    /**
     * Default Settings
     */
    /* Require Password */
    $settings->add(new admin_setting_configcheckbox('enrol_invoice/require_password',
                                                     get_string('require_password', 'enrol_invoice'), get_string('require_password_desc', 'enrol_invoice'), 0));
    /* Password Policy  */
    $settings->add(new admin_setting_configcheckbox('enrol_invoice/use_password_policy',
                                                     get_string('use_password_policy', 'enrol_invoice'), get_string('use_password_policy_desc', 'enrol_invoice'), 0));
    /* Hint             */
    $settings->add(new admin_setting_configcheckbox('enrol_invoice/show_hint',
                                                     get_string('show_hint', 'enrol_invoice'), get_string('show_hint_desc', 'enrol_invoice'), 0));
    /* Expired Action   */
    $options = array(ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
                     ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
                     ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_invoice/expired_action',
                                                   get_string('expired_action', 'enrol_invoice'), get_string('expired_action_help', 'enrol_invoice'),
                                                   ENROL_EXT_REMOVED_KEEP, $options));
    /* Expiry Notify Hour   */
    $options = array();
    for ($i=0; $i<24; $i++) {
        $options[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('enrol_invoice/expiry_notify_hour',
                                                   get_string('expirynotifyhour', 'core_enrol'), '', 6, $options));

    /* Enrol Default Instance   */
    $settings->add(new admin_setting_heading('enrol_invoice_defaults',
                                              get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));
    /* Default Enrol    */
    $settings->add(new admin_setting_configcheckbox('enrol_invoice/default_enrol',
                                                     get_string('defaultenrol', 'enrol'), get_string('defaultenrol_desc', 'enrol'), 1));
    /* Status   */
    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_invoice/status',
                                                   get_string('status', 'enrol_invoice'), get_string('status_desc', 'enrol_invoice'),
                                                   ENROL_INSTANCE_DISABLED, $options));
    /* New Enrols       */
    $options = array(1  => get_string('yes'), 0 => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_invoice/new_enrols',
                                                   get_string('new_enrols', 'enrol_invoice'), get_string('new_enrols_desc', 'enrol_invoice'), 1, $options));
    /* Group Key        */
    $options = array(1  => get_string('yes'),0 => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_invoice/group_key',
                                                   get_string('group_key', 'enrol_invoice'), get_string('group_key_desc', 'enrol_invoice'), 0, $options));
    /* Role             */
    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_invoice/role_id',
                                                       get_string('default_role', 'enrol_invoice'), get_string('default_role_desc', 'enrol_invoice'), $student->id, $options));
    }
    /* Enrol Period     */
    $settings->add(new admin_setting_configduration('enrol_invoice/enrol_period',
                                                     get_string('enrol_period', 'enrol_invoice'), get_string('enrol_period_desc', 'enrol_invoice'), 0));
    /* Expiry Notify Enrol   */
    $options = array(0 => get_string('no'), 1 => get_string('expirynotifyenroller', 'core_enrol'), 2 => get_string('expirynotifyall', 'core_enrol'));
    $settings->add(new admin_setting_configselect('enrol_invoice/expiry_notify',
                                                   get_string('expirynotify', 'core_enrol'), get_string('expirynotify_help', 'core_enrol'), 0, $options));
    /* expiry threshold */
    $settings->add(new admin_setting_configduration('enrol_invoice/expiry_threshold',
                                                     get_string('expirythreshold', 'core_enrol'), get_string('expirythreshold_help', 'core_enrol'), 86400, 86400));
    /* Timezone         */
    $options = array(0                      => get_string('never'),
                     1800 * 3600 * 24       => get_string('numdays', '', 1800),
                     1000 * 3600 * 24       => get_string('numdays', '', 1000),
                     365 * 3600 * 24        => get_string('numdays', '', 365),
                     180 * 3600 * 24        => get_string('numdays', '', 180),
                     150 * 3600 * 24        => get_string('numdays', '', 150),
                     120 * 3600 * 24        => get_string('numdays', '', 120),
                     90 * 3600 * 24         => get_string('numdays', '', 90),
                     60 * 3600 * 24         => get_string('numdays', '', 60),
                     30 * 3600 * 24         => get_string('numdays', '', 30),
                     21 * 3600 * 24         => get_string('numdays', '', 21),
                     14 * 3600 * 24         => get_string('numdays', '', 14),
                     7 * 3600 * 24          => get_string('numdays', '', 7));
    $settings->add(new admin_setting_configselect('enrol_invoice/long_time_no_see',
                                                   get_string('long_time_no_see', 'enrol_invoice'), get_string('long_time_no_see_help', 'enrol_invoice'), 0, $options));
    /* Max Enrolled     */
    $settings->add(new admin_setting_configtext('enrol_invoice/max_enrolled',
                                                 get_string('max_enrolled', 'enrol_invoice'), get_string('max_enrolled_help', 'enrol_invoice'), 0, PARAM_INT));
    /* Send Welcome     */
    $settings->add(new admin_setting_configcheckbox('enrol_invoice/send_course_welcome_message',
                                                     get_string('send_course_welcome_message', 'enrol_invoice'), get_string('send_course_welcome_message_help', 'enrol_invoice'), 1));
}//if_admin_full_tree