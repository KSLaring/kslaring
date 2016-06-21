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
 * Adds new instance of enrol_waitinglist to specified course
 * or edits current instance.
 *
 * @package    enrol_waitinglist
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @updateDate  28/10/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Add the invoice information option
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class enrol_waitinglist_edit_form extends moodleform {

    function definition() {
        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_waitinglist'));

        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_waitinglist'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_waitinglist');
        $mform->setDefault('status', $plugin->get_config('status'));
		
		//waitlist required fields
		$mform->addElement('date_selector', ENROL_WAITINGLIST_FIELD_CUTOFFDATE, get_string('cutoffdate', 'enrol_waitinglist'),array('optional' => true));	
		$mform->addElement('text',ENROL_WAITINGLIST_FIELD_MAXENROLMENTS,  get_string('maxenrolments', 'enrol_waitinglist'), array('size' => '8'));
		$mform->addElement('text', ENROL_WAITINGLIST_FIELD_WAITLISTSIZE,  get_string('waitlistsize', 'enrol_waitinglist'), array('size' => '8'));
		$mform->addRule(ENROL_WAITINGLIST_FIELD_MAXENROLMENTS, null, 'numeric', null, 'client');
		$mform->addRule(ENROL_WAITINGLIST_FIELD_WAITLISTSIZE, null, 'numeric', null, 'client');
		$mform->setType(ENROL_WAITINGLIST_FIELD_MAXENROLMENTS, PARAM_INT);
		$mform->setType(ENROL_WAITINGLIST_FIELD_WAITLISTSIZE, PARAM_INT);
		$mform->setDefault(ENROL_WAITINGLIST_FIELD_CUTOFFDATE, 0);
		$mform->setDefault(ENROL_WAITINGLIST_FIELD_MAXENROLMENTS, $plugin->get_config('maxenrolments'));
		$mform->setDefault(ENROL_WAITINGLIST_FIELD_WAITLISTSIZE, $plugin->get_config('waitlistsize'));


        /**
         * @updateDate  21/06/2016
         * @author      eFaktor     (fbv)
         *
         * Description
         * Internal && External Price
         */
        /* Internal price */
        $mform->addElement('text',ENROL_WAITINGLIST_FIELD_INTERNAL_PRICE,  get_string('in_price', 'enrol_waitinglist'), array('size' => '8'));
        $mform->setType(ENROL_WAITINGLIST_FIELD_INTERNAL_PRICE,PARAM_TEXT);
        /* External price */
        $mform->addElement('text',ENROL_WAITINGLIST_FIELD_EXTERNAL_PRICE,  get_string('ext_price', 'enrol_waitinglist'), array('size' => '8'));
        $mform->setType(ENROL_WAITINGLIST_FIELD_EXTERNAL_PRICE,PARAM_TEXT);

        /**
         * @updateDate  28/10/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Check if exists Invoice enrolment method to add the option
         */
        $pluginInvoice = enrol_get_plugin('invoice');
        if ($pluginInvoice) {
            $mform->addElement('advcheckbox', ENROL_WAITINGLIST_FIELD_INVOICE, get_string('invoice', 'enrol_waitinglist'));
            $mform->addHelpButton(ENROL_WAITINGLIST_FIELD_INVOICE, 'invoice', 'enrol_waitinglist');
        }

        /**
         * @updateDate  24/12/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Add the option to be an approval enrolment method
         */
        //$mform->addElement('advcheckbox', ENROL_WAITINGLIST_FIELD_APPROVAL, get_string('approval', 'enrol_waitinglist'));
        //$mform->addHelpButton(ENROL_WAITINGLIST_FIELD_APPROVAL, 'approval', 'enrol_waitinglist');

        /* None Option              */
        $mform->addElement('radio',ENROL_WAITINGLIST_FIELD_APPROVAL,get_string('none_approval','enrol_waitinglist'),'',APPROVAL_NONE);
        /* Approval required by manager */
        $mform->addElement('radio',ENROL_WAITINGLIST_FIELD_APPROVAL,get_string('approval','enrol_waitinglist'),'',APPROVAL_REQUIRED);
        /* Mail to manager option   */
        $mform->addElement('radio',ENROL_WAITINGLIST_FIELD_APPROVAL,get_string('approval_message','enrol_waitinglist'),'',APPROVAL_MESSAGE);

        /*
        $settings->add(new admin_setting_configtext('enrol_waitinglist/maxenrolments', get_string('maxenrolments', 'enrol_waitinglist'), get_string('maxenrolments_desc', 'enrol_waitinglist'), ''));
        */
        /*
            $settings->add(new admin_setting_configtext('enrol_waitinglist/cutoffdate', get_string('cutoffdate', 'enrol_waitinglist'), get_string('cutoffdate_desc', 'enrol_waitinglist'), ''));
        */
        /*
        $settings->add(new admin_setting_configtext('enrol_waitinglist/waitlistsize', get_string('waitlistsize', 'enrol_waitinglist'), get_string('waitlistsize_desc', 'enrol_waitinglist'), ''));
        */

        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $plugin->get_config('roleid'));
        }
        $mform->addElement('select', 'roleid', get_string('defaultrole', 'role'), $roles);
        $mform->setDefault('roleid', $plugin->get_config('roleid'));

        $mform->addElement('duration', 'enrolperiod', get_string('defaultperiod', 'enrol_waitinglist'), array('optional' => true, 'defaultunit' => 86400));
        $mform->setDefault('enrolperiod', $plugin->get_config('enrolperiod'));
        $mform->addHelpButton('enrolperiod', 'defaultperiod', 'enrol_waitinglist');

        $options = array(0 => get_string('no'), 1 => get_string('expirynotifyenroller', 'core_enrol'), 2 => get_string('expirynotifyall', 'core_enrol'));
        $mform->addElement('select', 'expirynotify', get_string('expirynotify', 'core_enrol'), $options);
        $mform->addHelpButton('expirynotify', 'expirynotify', 'core_enrol');

        $mform->addElement('duration', 'expirythreshold', get_string('expirythreshold', 'core_enrol'), array('optional' => false, 'defaultunit' => 86400));
        $mform->addHelpButton('expirythreshold', 'expirythreshold', 'core_enrol');
        $mform->disabledIf('expirythreshold', 'expirynotify', 'eq', 0);
        
        $mform->addElement('advcheckbox', ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE, get_string('sendcoursewelcomemessage', 'enrol_waitinglist'));
        $mform->addHelpButton(ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE, 'sendcoursewelcomemessage', 'enrol_waitinglist');
        $mform->setDefault(ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE, $plugin->get_config('sendcoursewelcomemessage'));

        $mform->addElement('textarea', ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE, get_string('customwelcomemessage', 'enrol_waitinglist'), array('cols'=>'60', 'rows'=>'8'));
        $mform->addHelpButton(ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE, 'customwelcomemessage', 'enrol_waitinglist');
		$mform->setDefault(ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE,get_string('welcometocoursetext','enrol_waitinglist'));

		$mform->addElement('advcheckbox', ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE, get_string('sendcoursewaitlistmessage', 'enrol_waitinglist'));
        $mform->addHelpButton(ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE, 'sendcoursewaitlistmessage', 'enrol_waitinglist');
		$mform->setDefault(ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE, $plugin->get_config('sendcoursewaitlistmessage'));

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        if (enrol_accessing_via_instance($instance)) {
            $mform->addElement('static', 'selfwarn', get_string('instanceeditselfwarning', 'core_enrol'), get_string('instanceeditselfwarningtext', 'core_enrol'));
        }

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }

    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        if ($data['expirynotify'] > 0 and $data['expirythreshold'] < 86400) {
            $errors['expirythreshold'] = get_string('errorthresholdlow', 'core_enrol');
        }

        return $errors;
    }
}
