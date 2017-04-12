<?php
// This file is part of friadmin
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

require_once("$CFG->libdir/formslib.php");

class summary_form extends moodleform {
    public function definition() {
        global $CFG;
        global $SESSION;

        // Calls a function that gets all the categories from the database.
        $categorylist = friadminrpt::get_categories();

        $mform = $this->_form;

        $mform->addElement('select', 'category', get_string('category', 'local_friadmin'), $categorylist);
        $mform->addRule('category', null, 'required');
        $mform->setDefault('cateogry', $categorylist[0]);
        $mform->addElement('date_selector', 'selsummaryfrom', get_string('summaryfrom', 'local_friadmin'),
            array('optional' => false));
        $mform->addRule('selsummaryfrom', null, 'required');
        $mform->addElement('date_selector', 'selsummaryto', get_string('summaryto', 'local_friadmin'),
            array('optional' => false));
        $mform->addRule('selsummaryto', null, 'required');
        $this->add_action_buttons(false, get_string('downlaod', 'local_friadmin'));

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }
}

class course_instructor_form extends moodleform {
    public function definition() {
        global $CFG;
        global $SESSION;

        $mform = $this->_form;

        $category = array();
        $category[0] = 'Select one...';
        $category[1] = 'hello';
        $category[2] = 'goodbye';

        $courses = array();
        $courses[0] = 'Select one...';
        $courses[1] = 'c1';
        $courses[2] = 'c2';

        $mform->addElement('select', 'category', get_string('category', 'local_ksl'), $category);
        $mform->addElement('select', 'course', get_string('course', 'local_ksl'), $courses);

        $this->add_action_buttons(false, get_string('download'));

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }
}

class course_coordinator_form extends moodleform {
    public function definition() {
        global $CFG;
        global $SESSION;

        $mform = $this->_form;

        $category = array();
        $category[0] = 'Select one...';
        $category[1] = 'hello';
        $category[2] = 'goodbye';

        $courses = array();
        $courses[0] = 'Select one...';
        $courses[1] = 'c1';
        $courses[2] = 'c2';

        $mform->addElement('select', 'category', get_string('category', 'local_ksl'), $category);
        $mform->addElement('select', 'course', get_string('course', 'local_ksl'), $courses);

        $this->add_action_buttons(false, get_string('download'));

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }
}