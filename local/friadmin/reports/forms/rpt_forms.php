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

        $mform = $this->_form;

        // Calls a function that gets all the categories from the database.
        $categorylist = friadminrpt::get_categories();

        $mform->addElement('select', 'category', get_string('category', 'local_friadmin'), $categorylist);
        $mform->addRule('category', null, 'required');
        $mform->setDefault('cateogry', $categorylist[0]);
        $mform->addElement('date_selector', 'selsummaryfrom', get_string('summaryfrom', 'local_friadmin'),
            array('optional' => false));
        $mform->addRule('selsummaryfrom', null, 'required');
        $mform->addElement('date_selector', 'selsummaryto', get_string('summaryto', 'local_friadmin'),
            array('optional' => false));
        $mform->addRule('selsummaryto', null, 'required');
        $this->add_action_buttons(false, get_string('download', 'local_friadmin'));

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $date = time();
        if ($data['selsummaryfrom'] > $data['selsummaryto']) {
            $errors['selsummaryfrom'] = get_string('biggerthanto', 'local_friadmin');
            $errors['selsummaryto'] = get_string('smallerthanfrom', 'local_friadmin)');
        } else if ($data['selsummaryfrom'] > $date) {
            $errors['selsummaryfrom'] = get_string('biggerthannow', 'local_friadmin');
        } else if ($data['selsummaryto'] - $data['selsummaryfrom'] > 60 * 60 * 24 * 365 * 2) {
            $errors['selsummaryfrom'] = get_string('morethantwoyears', 'local_friadmin');
        }

        if ($data['category'] == '0') {
            $errors['category'] = 'You need to select a category';
        }
        return $errors;
    }
}

class course_instructor_form extends moodleform {
    public function definition() {
        global $CFG;
        global $SESSION;

        $mform = $this->_form;

        // Calls a function that gets all the categories from the database.
        $categorylist = friadminrpt::get_categories();

        // Calls a function that gets all the courses from the database.
        $courses = friadminrpt::get_courses();

        $mform->addElement('select', 'category', get_string('category', 'local_friadmin'), $categorylist);
        $mform->addRule('category', null, 'required');

        $mform->addElement('select', 'course', get_string('course', 'local_friadmin'), $courses);

        $mform->addElement('header', 'header_instructorsfilter', get_string('header_instructorsfilter', 'local_friadmin'));
        $mform->setExpanded('header_instructorsfilter',true);

        $mform->addElement('text', 'userfullname', get_string('userfullname', 'local_friadmin'));
        $mform->setType('userfullname', PARAM_TEXT);

        $mform->addElement('text', 'username', get_string('username', 'local_friadmin'));
        $mform->setType('username', PARAM_TEXT);

        $mform->addElement('text', 'useremail', get_string('useremail', 'local_friadmin'));
        $mform->setType('useremail', PARAM_EMAIL);

        $mform->addElement('text', 'userworkplace', get_string('userworkplace', 'local_friadmin'));
        $mform->setType('userworkplace', PARAM_TEXT);

        $mform->addElement('text', 'userjobrole', get_string('userjobrole', 'local_friadmin'));
        $mform->setType('userjobrole', PARAM_TEXT);

        $this->add_action_buttons(false, get_string('download', 'local_friadmin'));

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

        // Calls a function that gets all the categories from the database.
        $categorylist = friadminrpt::get_categories();

        // Calls a function that gets all the courses from the database.
        $courses = friadminrpt::get_courses();

        $mform->addElement('select', 'category', get_string('category', 'local_friadmin'), $categorylist);
        $mform->addRule('category', null, 'required');

        $mform->addElement('select', 'course', get_string('course', 'local_friadmin'), $courses);

        $mform->addElement('header', 'header_coordinatorfilter', get_string('header_coordinatorfilter', 'local_friadmin'));
        $mform->setExpanded('header_coordinatorfilter',true);

        $mform->addElement('text', 'userfullname', get_string('userfullname', 'local_friadmin'));
        $mform->setType('userfullname', PARAM_TEXT);

        $mform->addElement('text', 'username', get_string('username', 'local_friadmin'));
        $mform->setType('username', PARAM_TEXT);

        $mform->addElement('text', 'useremail', get_string('useremail', 'local_friadmin'));
        $mform->setType('useremail', PARAM_EMAIL);

        $mform->addElement('text', 'userworkplace', get_string('userworkplace', 'local_friadmin'));
        $mform->setType('userworkplace', PARAM_TEXT);

        $mform->addElement('text', 'userjobrole', get_string('userjobrole', 'local_friadmin'));
        $mform->setType('userjobrole', PARAM_TEXT);

        $this->add_action_buttons(false, get_string('download', 'local_friadmin'));

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }
}