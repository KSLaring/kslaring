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

//namespace local_friadmin;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/formslib.php');

//use \stdClass;

/**
 * The form for the local_friadmin course_template selection area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_coursetemplate_select_form extends \moodleform {
    function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('hidden', 'temptype', $customdata['temptype']);
        $mform->setType('temptype', PARAM_INT);

        $defaultText = get_string('fullnamecourse');
        $attributes = array(
            'placeholder' => $defaultText
        );
        $mform->addElement('text', 'selfullname', $defaultText, $attributes);
        $mform->addRule('selfullname', get_string('missingfullname'),
            'required', null, 'client');
        $mform->setType('selfullname', PARAM_TEXT);

        $defaultText = get_string('shortnamecourse');
        $attributes = array(
            'placeholder' => $defaultText
        );
        $mform->addElement('text', 'selshortname', $defaultText, $attributes);
        $mform->addRule('selshortname', get_string('missingshortname'),
            'required', null, 'client');
        $mform->setType('selshortname', PARAM_TEXT);

        $options = array('' => get_string('selcategory', 'local_friadmin') . ' ...');
        $options = $options + $customdata['categories'];
        $mform->addElement('select', 'selcategory',
            get_string('selcategory', 'local_friadmin'), $options);
        $mform->addRule('selcategory', get_string('missingselcategory', 'local_friadmin'),
            'required', null, 'client');
        $mform->setDefault('selcategory', '');

        $options = array('' => get_string($customdata['seltemplate'], 'local_friadmin') . ' ...');
        $options = $options + $customdata['templates'];
        $mform->addElement('select', 'seltemplate',
            get_string('seltemplate', 'local_friadmin'), $options);
        $mform->addRule('seltemplate', get_string('missingseltemplate', 'local_friadmin'),
            'required', null, 'client');
        $mform->setDefault('seltemplate', $customdata['preftemplate']);

        $mform->addElement('submit', 'submitcreate',
            get_string('selsubmitcreate', 'local_friadmin'));
    }

    /**
     * Set the default form values
     *
     * The associative $defaults: array 'elementname' => 'defaultvalue'
     *
     * @param Array $defaults The default values
     */
    public function set_defaults($defaults = array()) {
        $mform = $this->_form;
        foreach ($defaults as $elementname => $defaultvalue) {
            $mform->setDefault($elementname, $defaultvalue);
        }
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate shortname.
        if ($course = $DB->get_record('course', array('shortname' => $data['selshortname']), '*', IGNORE_MULTIPLE)) {
            if (empty($data['id']) || $course->id != $data['id']) {
                $errors['selshortname'] = get_string('shortnametaken', '', $course->fullname);
            }
        }

        return $errors;
    }
}
