<?php
// This file is part of ksl
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

class main_form extends moodleform {
    public function definition() {
        global $CFG;
        global $SESSION;
        $industrycode = null;

        // Gets the industrycode from the session.
        if (isset($SESSION->industrycode)) {
            $industrycode = $SESSION->industrycode;
        }//if_session_industrycode

        // Gets the organization in the dropdown lists from the session.
        if (isset($SESSION->organization0) && ($SESSION->organization1) && ($SESSION->organization2) && ($SESSION->organization3)) {
            $level0sel = $SESSION->organization0;
            $level1sel = $SESSION->organization1;
            $level2sel = $SESSION->organization2;
            $level3sel = $SESSION->organization3;
            $levelzero = ksl::get_companies_level_lst(0, null);
            $levelone = ksl::get_companies_level_lst(1, $level0sel);
            $leveltwo = ksl::get_companies_level_lst(2, $level1sel);
            $levelthree = ksl::get_companies_level_lst(3, $level2sel);
        } else {
            $level0sel = 0;
            $level1sel = 0;
            $level2sel = 0;
            $level3sel = 0;
            $levelzero = ksl::get_companies_level_lst(0, null);
            $levelone = ksl::get_companies_level_lst(1, null);
            $leveltwo = ksl::get_companies_level_lst(2, null);
            $levelthree = ksl::get_companies_level_lst(3, null);
        }//if_session_organization

        // Gets the type from the session, else it's industrycode.
        if (isset($SESSION->type)) {
            $seltype = $SESSION->type;
        } else {
            $seltype = '1';
        }

        $mform = $this->_form;

        $mform->addElement('radio', 'type', '', get_string('industrycode', 'local_ksl'), 1);
        $mform->addElement('text', 'industrycode', get_string('industrycode', 'local_ksl'));
        $mform->setType('industrycode', PARAM_TEXT);
        $mform->addRule('industrycode', null , 'numeric', null, 'client');
        $mform->addElement('radio', 'type', '', get_string('organization', 'local_ksl'), 0);

        if ($seltype == 0) {
            $mform->setDefault('type', '0');
        } else if ($seltype == 1) {
            $mform->setDefault('type', '1');
            $level0sel = 0;
            $level1sel = 0;
            $level2sel = 0;
            $level3sel = 0;
        }

        if ($industrycode) {
            $mform->setDefault('industrycode', $industrycode);
        } else {
            $mform->setDefault('industrycode', '');
        }

        $mform->addElement('select', 'level_0', get_string('levelzero', 'local_ksl'), $levelzero);
        $mform->setDefault('level_0', $level0sel);
        $mform->addElement('select', 'level_1', get_string('levelone', 'local_ksl'), $levelone);
        $mform->setDefault('level_1', $level1sel);
        $mform->addElement('select', 'level_2', get_string('leveltwo', 'local_ksl'), $leveltwo);
        $mform->setDefault('level_2', $level2sel);
        $mform->addElement('select', 'level_3', get_string('levelthree', 'local_ksl'), $levelthree);
        $mform->setDefault('level_3', $level3sel);
        $this->add_action_buttons(true, get_string('search'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['type'] == 0) {
            if ($data['level_3'] == 0) {
                $errors['level_3'] = get_string('levelrequired', 'local_ksl');
            }
        } else if ($data['type'] == 1) {
            if (!$data['industrycode']) {
                $errors['industrycode'] = get_string('required', 'local_ksl');
            }
        }

        return $errors;
    }
}