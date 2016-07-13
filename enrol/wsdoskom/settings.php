<?php

/**
 * Single Sign On Enrolment Plugin - Settings
 *
 * @package         enrol
 * @subpackage      wsdoskom
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    26/02/2015
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    /*
     * General Settings
     */
    $settings->add(new admin_setting_heading('enrol_wsdoskom_settings', '', get_string('pluginname_desc', 'enrol_wsdoskom')));

    /**
     * Default Settings
     */
    $settings->add(new admin_setting_heading('enrol_wsdoskom_defaults',
                                             '',
                                             get_string('show_applications', 'enrol_wsdoskom')));

    $settings->add(new admin_setting_configcheckbox('enrol_wsdoskom/defaultenrol',
                                                    get_string('defaultenrol', 'enrol'),
                                                    get_string('defaultenrol_desc', 'enrol'), 1));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_wsdoskom/roleid',
                                                      get_string('defaultrole', 'enrol_wsdoskom'),
                                                      get_string('defaultrole_desc', 'enrol_wsdoskom'), $student->id, $options));
    }

    $settings->add(new admin_setting_configtext('enrol_wsdoskom/enrolperiod',
                                                get_string('enrol_period', 'enrol_wsdoskom'),
                                                get_string('enrol_period_desc', 'enrol_wsdoskom'), 0, PARAM_INT));
}//if_full_tree