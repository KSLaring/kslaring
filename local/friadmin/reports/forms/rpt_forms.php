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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class summary_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        // Calls a function that gets all the categories from the database.
        $categorylist = friadminrpt::get_categories();

        // Category.
        $mform->addElement('select', 'category', get_string('category', 'local_friadmin'), $categorylist);
        $mform->addRule('category', null, 'required');
        $mform->setDefault('cateogry', $categorylist[0]);

        // From.
        $mform->addElement('date_selector', 'selsummaryfrom', get_string('summaryfrom', 'local_friadmin'),
            array('optional' => false));
        $mform->addRule('selsummaryfrom', null, 'required');

        // To.
        $mform->addElement('date_selector', 'selsummaryto', get_string('summaryto', 'local_friadmin'),
            array('optional' => false));
        $mform->addRule('selsummaryto', null, 'required');

        // Download.
        $this->add_action_buttons(false, get_string('download', 'local_friadmin'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $date = getdate();

        // The selected dates.
        $fromdate = $data['selsummaryfrom'];
        $todate   = $data['selsummaryto'];

        // The selected dates rounded.
        $fromdaterounded = $fromdate + ($fromdate % 86400);
        $todaterounded = $todate + ($todate % 86400);

        // Variables for the years (0 for normal year and 1 for leap year).
        $yearbetween = $fromdate + (60 * 60 * 24 * 365);
        $firstyear = date('L', $fromdate);
        $secondyear = date('L', $yearbetween);
        $lastyear = date('L', $todate);

        // Checking for leap years.
        if ($firstyear == 1 || $secondyear == 1 || $lastyear == 1) {
            // If leap year.
            $twoyears = (60 * 60 * 24 * 365 * 2) + (60 * 60 * 24);
            $twoyearsrounded = $twoyears + ($twoyears % 86400);
        } else {
            // If no leap year.
            $twoyears = (60 * 60 * 24 * 365 * 2);
            $twoyearsrounded = $twoyears + ($twoyears % 86400);
        }

        // Checks the data.
        if ($data['selsummaryfrom'] > $data['selsummaryto']) {
            $errors['selsummaryfrom'] = get_string('biggerthanto', 'local_friadmin');
            $errors['selsummaryto'] = get_string('smallerthanfrom', 'local_friadmin)');
        } else if ($data['selsummaryfrom'] > $date) {
            $errors['selsummaryfrom'] = get_string('biggerthannow', 'local_friadmin');
        } else if ($todaterounded - $fromdaterounded > $twoyearsrounded) {
            $errors['selsummaryfrom'] = get_string('morethantwoyears', 'local_friadmin');
        }

        if ($data['category'] == '0') {
            $errors['category'] = 'You need to select a category';
        }
        return $errors;
    }
} // end summary_form

class course_instructor_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        // Calls a function that gets all the categories from the database.
        $categorylist = friadminrpt::get_categories();

        // Calls a function that gets all the courses from the database.
        $courses = friadminrpt::get_courses();

        // Category.
        $mform->addElement('select', 'category', get_string('category', 'local_friadmin'), $categorylist);
        $mform->addRule('category', null, 'required');

        // Course.
        $mform->addElement('select', 'course', get_string('course', 'local_friadmin'), $courses);

        // Header.
        $mform->addElement('header', 'header_instructorsfilter', get_string('header_instructorsfilter', 'local_friadmin'));
        $mform->setExpanded('header_instructorsfilter', true);

        // Users full name.
        $mform->addElement('text', 'userfullname', get_string('userfullname', 'local_friadmin'));
        $mform->setType('userfullname', PARAM_TEXT);

        // Username.
        $mform->addElement('text', 'username', get_string('username', 'local_friadmin'));
        $mform->setType('username', PARAM_TEXT);

        // Users email.
        $mform->addElement('text', 'useremail', get_string('useremail', 'local_friadmin'));
        $mform->setType('useremail', PARAM_EMAIL);

        // Users workplace.
        $mform->addElement('text', 'userworkplace', get_string('userworkplace', 'local_friadmin'));
        $mform->setType('userworkplace', PARAM_TEXT);

        // Users jobrole.
        $mform->addElement('text', 'userjobrole', get_string('userjobrole', 'local_friadmin'));
        $mform->setType('userjobrole', PARAM_TEXT);

        // Download button.
        $this->add_action_buttons(false, get_string('download', 'local_friadmin'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['category'] == '0') {
            $errors['category'] = 'You need to select a category';
        }

        return $errors;
    }
} // end course_instructor_form

class course_coordinator_form extends moodleform {
    public function definition() {
        global $CFG;
        global $SESSION;

        $mform = $this->_form;

        // Calls a function that gets all the categories from the database.
        $categorylist = friadminrpt::get_categories();

        // Calls a function that gets all the courses from the database.
        $courses = friadminrpt::get_courses();

        // Category.
        $mform->addElement('select', 'category', get_string('category', 'local_friadmin'), $categorylist);
        $mform->addRule('category', null, 'required');

        // Course.
        $mform->addElement('select', 'course', get_string('course', 'local_friadmin'), $courses);

        // Header.
        $mform->addElement('header', 'header_coordinatorfilter', get_string('header_coordinatorfilter', 'local_friadmin'));
        $mform->setExpanded('header_coordinatorfilter', true);

        // Users full name.
        $mform->addElement('text', 'userfullname', get_string('userfullname', 'local_friadmin'));
        $mform->setType('userfullname', PARAM_TEXT);

        // Username.
        $mform->addElement('text', 'username', get_string('username', 'local_friadmin'));
        $mform->setType('username', PARAM_TEXT);

        // Users email.
        $mform->addElement('text', 'useremail', get_string('useremail', 'local_friadmin'));
        $mform->setType('useremail', PARAM_EMAIL);

        // Users workplace.
        $mform->addElement('text', 'userworkplace', get_string('userworkplace', 'local_friadmin'));
        $mform->setType('userworkplace', PARAM_TEXT);

        // Users jobrole.
        $mform->addElement('text', 'userjobrole', get_string('userjobrole', 'local_friadmin'));
        $mform->setType('userjobrole', PARAM_TEXT);

        // Download button.
        $this->add_action_buttons(false, get_string('download', 'local_friadmin'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['category'] == '0') {
            $errors['category'] = 'You need to select a category';
        }

        return $errors;
    }
} // end course_coordinator_form