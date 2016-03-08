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
 * Strings for component 'enrol_waitinglist', language 'en'.
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
namespace enrol_waitinglist\method\unnamedbulk;
 
require_once($CFG->libdir.'/formslib.php');


class enrolmethodunnamedbulk_form extends \moodleform {

    function definition() {
        global $DB;

        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('unnamedbulk_menutitle','enrol_waitinglist'));

        $mform->addElement('selectyesno', 'status', get_string('enable'));
        $mform->addHelpButton('status', 'status', 'enrol_self');


        $mform->addElement('text',enrolmethodunnamedbulk::MFIELD_MAXENROLLED, get_string('maxenrolled', 'enrol_self'));
        $mform->addHelpButton(enrolmethodunnamedbulk::MFIELD_MAXENROLLED, 'maxenrolled', 'enrol_self');
        $mform->setType(enrolmethodunnamedbulk::MFIELD_MAXENROLLED, PARAM_INT);
        $mform->setDefault(enrolmethodunnamedbulk::MFIELD_MAXENROLLED, 0);


 
        
        $mform->addElement('advcheckbox','emailalert', get_string('sendcoursewaitlistmessage', 'enrol_waitinglist'));
        $mform->addHelpButton('emailalert', 'sendcoursewaitlistmessage', 'enrol_waitinglist');
        $mform->setDefault('emailalert', true);
        
        $mform->addElement('textarea',enrolmethodunnamedbulk::MFIELD_WAITLISTMESSAGE, get_string('customwaitlistmessage', 'enrol_waitinglist'), array('cols'=>'60', 'rows'=>'8'));
        $mform->addHelpButton(enrolmethodunnamedbulk::MFIELD_WAITLISTMESSAGE, 'customwaitlistmessage', 'enrol_waitinglist');
        $mform->setDefault(enrolmethodunnamedbulk::MFIELD_WAITLISTMESSAGE,get_string('waitlistmessagetext_unnamedbulk','enrol_waitinglist'));
        
        $mform->addElement('advcheckbox',enrolmethodunnamedbulk::MFIELD_SENDCONFIRMMESSAGE, get_string('sendconfirmmessage', 'enrol_waitinglist'));
        $mform->addHelpButton(enrolmethodunnamedbulk::MFIELD_SENDCONFIRMMESSAGE, 'sendconfirmmessage', 'enrol_waitinglist');
        $mform->setDefault(enrolmethodunnamedbulk::MFIELD_SENDCONFIRMMESSAGE, true);
        
        $mform->addElement('textarea',enrolmethodunnamedbulk::MFIELD_CONFIRMEDMESSAGE, get_string('customconfirmedmessage', 'enrol_waitinglist'), array('cols'=>'60', 'rows'=>'8'));
        $mform->addHelpButton(enrolmethodunnamedbulk::MFIELD_CONFIRMEDMESSAGE, 'customconfirmedmessage', 'enrol_waitinglist');
         $mform->setDefault(enrolmethodunnamedbulk::MFIELD_CONFIRMEDMESSAGE,get_string('confirmedmessagetext_unnamedbulk','enrol_waitinglist'));

        
        
        
/*
        $mform->addElement('advcheckbox', enrolmethodunnamedbulk::MFIELD_SENDWELCOMEMESSAGE, get_string('sendcoursewelcomemessage', 'enrol_self'));
        $mform->addHelpButton(enrolmethodunnamedbulk::MFIELD_SENDWELCOMEMESSAGE, 'sendcoursewelcomemessage', 'enrol_self');

        $mform->addElement('textarea',enrolmethodunnamedbulk::MFIELD_WELCOMEMESSAGE, get_string('customwelcomemessage', 'enrol_self'), array('cols'=>'60', 'rows'=>'8'));
        $mform->addHelpButton(enrolmethodunnamedbulk::MFIELD_WELCOMEMESSAGE, 'customwelcomemessage', 'enrol_self');
*/
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
		$mform->addElement('hidden', 'methodtype');
        $mform->setType('methodtype', PARAM_TEXT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }

    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
	
		/*

        if ($data['status'] == ENROL_INSTANCE_ENABLED) {
            if (!empty($data['enrolenddate']) and $data['enrolenddate'] < $data['enrolstartdate']) {
                $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_self');
            }
        }

        if ($data['expirynotify'] > 0 and $data['expirythreshold'] < 86400) {
            $errors['expirythreshold'] = get_string('errorthresholdlow', 'core_enrol');
        }
*/
        return $errors;
    }

    /**
    * Gets a list of roles that this user can assign for the course as the default for self-enrolment.
    *
    * @param context $context the context.
    * @param integer $defaultrole the id of the role that is set as the default for self-enrolment
    * @return array index is the role id, value is the role name
    */
    function extend_assignable_roles($context, $defaultrole) {
        global $DB;

        $roles = get_assignable_roles($context, ROLENAME_BOTH);
        if (!isset($roles[$defaultrole])) {
            if ($role = $DB->get_record('role', array('id'=>$defaultrole))) {
                $roles[$defaultrole] = role_get_name($role, $context, ROLENAME_BOTH);
            }
        }
        return $roles;
    }
}
