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
 * Self enrol plugin implementation.
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_waitinglist\method\unnamedbulk;
 
require_once($CFG->libdir.'/formslib.php');


class enrolmethodunnamedbulk_enrolform extends \moodleform {
    protected $method;
    protected $toomany = false;

    /**
     * Overriding this function to get unique form id for multiple self enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
		list( $waitinglist,$method,$queuestatus) = $this->_customdata;
        $formid = $method->id.'_'.get_class($this);
        return $formid;
    }

    public function definition() {
        $mform = $this->_form;
       list( $waitinglist,$method,$queuestatus) = $this->_customdata;
        $this->method = $method;
        $plugin = enrol_get_plugin('waitinglist');

        $heading = $plugin->get_instance_name($waitinglist);
        $mform->addElement('header', 'selfheader', $heading. ' : ' . get_string('unnamedbulk_menutitle','enrol_waitinglist'));
        
        $mform->addElement('static','formintro',
			'',
			get_string('unnamedbulk_enrolformintro','enrol_waitinglist'));
        
        //add caution for number of seats available, and waiting list size etc
        if($queuestatus){
			$mform->addElement('static','aboutqueuestatus',
			get_string('unnamedbulk_enrolformqueuestatus_label','enrol_waitinglist'),
			get_string('unnamedbulk_enrolformqueuestatus','enrol_waitinglist',$queuestatus));
        }
        
        //add form input elements
        $mform->addElement('text','seats',  get_string('reserveseatcount', 'enrol_waitinglist'), array('size' => '8'));
		$mform->addRule('seats', null, 'numeric', null, 'client');
		$mform->setType('seats', PARAM_INT);

        

		$mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $waitinglist->courseid);
		$mform->addElement('hidden', 'waitinglist');
        $mform->setType('waitinglist', PARAM_INT);
		$mform->setDefault('waitinglist', $waitinglist->id);
		$mform->addElement('hidden', 'methodtype');
        $mform->setType('methodtype', PARAM_TEXT);
		$mform->setDefault('methodtype', $this->method->get_methodtype());
		$mform->addElement('hidden', 'datarecordid');
        $mform->setType('datarecordid', PARAM_INT);
		
		$this->add_action_buttons(false, get_string('reserveseats', 'enrol_waitinglist'));

    }

    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        $method = $this->method;

        if ($this->toomany) {
            $errors['notice'] = get_string('error');
            return $errors;
        }
/*
        if ($method->password) {
            if ($data['enrolpassword'] !== $method->password) {
                if ($method->{enrolmethodunnamedbulk::MFIELD_GROUPKEY}) {
                    $groups = $DB->get_records('groups', array('courseid'=>$method->courseid), 'id ASC', 'id, enrolmentkey');
                    $found = false;
                    foreach ($groups as $group) {
                        if (empty($group->enrolmentkey)) {
                            continue;
                        }
                        if ($group->enrolmentkey === $data['enrolpassword']) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        // We can not hint because there are probably multiple passwords.
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_self');
                    }

                } else {
                    $plugin = enrol_get_plugin('self');
                    if ($plugin->get_config('showhint')) {
                        $hint = core_text::substr($method->password, 0, 1);
                        $errors['enrolpassword'] = get_string('passwordinvalidhint', 'enrol_self', $hint);
                    } else {
                        $errors['enrolpassword'] = get_string('passwordinvalid', 'enrol_self');
                    }
                }
            }
        }
    */

        return $errors;
    }
}
