<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Waitinglist enrolment plugin settings and presets.
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_waitinglist_settings', '', get_string('pluginname_desc', 'enrol_waitinglist')));

    // Note: let's reuse the ext sync constants and strings here, internally it is very similar,
    //       it describes what should happend when users are not supposed to be enerolled any more.
    $options = array(
        ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPEND        => get_string('extremovedsuspend', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_waitinglist/expiredaction', get_string('expiredaction', 'enrol_waitinglist'), get_string('expiredaction_help', 'enrol_waitinglist'), ENROL_EXT_REMOVED_KEEP, $options));

    $options = array();
    for ($i=0; $i<24; $i++) {
        $options[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('enrol_waitinglist/expirynotifyhour', get_string('expirynotifyhour', 'core_enrol'), '', 6, $options));

  	
	//--- enrol instance defaults ----------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_waitinglist_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $settings->add(new admin_setting_configcheckbox('enrol_waitinglist/defaultenrol',
        get_string('defaultenrol', 'enrol'), get_string('defaultenrol_desc', 'enrol'), 1));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_waitinglist/status',
        get_string('status', 'enrol_waitinglist'), get_string('status_desc', 'enrol_waitinglist'), ENROL_INSTANCE_ENABLED, $options));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_waitinglist/roleid',
            get_string('defaultrole', 'role'), '', $student->id, $options));
    }

    $settings->add(new admin_setting_configduration('enrol_waitinglist/enrolperiod',
        get_string('defaultperiod', 'enrol_waitinglist'), get_string('defaultperiod_desc', 'enrol_waitinglist'), 0));

    $options = array(0 => get_string('no'), 1 => get_string('expirynotifyenroller', 'core_enrol'), 2 => get_string('expirynotifyall', 'core_enrol'));
    $settings->add(new admin_setting_configselect('enrol_waitinglist/expirynotify',
        get_string('expirynotify', 'core_enrol'), get_string('expirynotify_help', 'core_enrol'), 0, $options));

    $settings->add(new admin_setting_configduration('enrol_waitinglist/expirythreshold',
        get_string('expirythreshold', 'core_enrol'), get_string('expirythreshold_help', 'core_enrol'), 86400, 86400));
        

	$settings->add(new admin_setting_configtext('enrol_waitinglist/maxenrolments', get_string('maxenrolments', 'enrol_waitinglist'), '', 50, PARAM_FLOAT, 4));
	$settings->add(new admin_setting_configtext('enrol_waitinglist/waitlistsize', get_string('waitlistsize', 'enrol_waitinglist'), '', 100, PARAM_FLOAT, 4));
	$settings->add(new admin_setting_configcheckbox('enrol_waitinglist/sendcoursewelcomemessage', get_string('sendcoursewelcomemessage', 'enrol_waitinglist'), '', 1));
	$settings->add(new admin_setting_configcheckbox('enrol_waitinglist/sendcoursewaitlistmessage', get_string('sendcoursewaitlistmessage', 'enrol_waitinglist'), '', 1));

    /* ICal Path */
    $settings->add(new admin_setting_configtext('enrol_waitinglist/file_location',get_string('ical_path', 'enrol_waitinglist'), '', ''));
}
