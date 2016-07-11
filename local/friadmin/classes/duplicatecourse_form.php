<?php
/**
 * Friadmin Plugin - duplicate course form
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/formslib.php');

class local_friadmin_duplicatecourse_form extends moodleform {
    function definition() {
        $myCategories = array();

        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('hidden', 'id', $customdata['id']);
        $mform->setType('id', PARAM_INT);

        // Get my categories.
        $myCategories[0] = get_string('sel_category', 'local_friadmin');
        $myCategories = $myCategories + local_friadmin_helper::getMyCategories();

        // Static element - extra info for the user.
        $mform->addElement('static', 'extra_info', null, get_string('info_dup_course', 'local_friadmin'));

        // Categories selection
        $mform->addElement('select', 'selcategory', get_string('my_categories', 'local_friadmin'), $myCategories);
        $mform->addHelpButton('selcategory', 'my_categories', 'local_friadmin');
        $mform->addRule('selcategory', 'required', 'required', 'nonzero', 'client');
        $mform->addRule('selcategory', 'required', 'nonzero', null, 'client');
        $mform->setDefault('selcategory', $customdata['coursecat']);

        // Full name for the duplicated course.
        $mform->addElement('text', 'selfullname', get_string('selfullname', 'local_friadmin'),
            array('size' => 50));
        $mform->addHelpButton('selfullname', 'selfullname', 'local_friadmin');
        $mform->addRule('selfullname', get_string('missingfullname'), 'required', null, 'client');
        $mform->setType('selfullname', PARAM_TEXT);
        $mform->setDefault('selfullname', $customdata['selfullname']);

        // Short name for the duplicated course.
        $mform->addElement('text', 'selshortname', get_string('selshortname', 'local_friadmin'),
            array('size' => 50));
        $mform->addHelpButton('selshortname', 'selshortname', 'local_friadmin');
        $mform->addRule('selshortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('selshortname', PARAM_TEXT);
        $mform->setDefault('selshortname', $customdata['selshortname']);

        // Check if users shall be included.
        $mform->addElement('advcheckbox', 'includeusers', get_string('includeusers', 'local_friadmin'));
        $mform->addHelpButton('includeusers', 'includeusers', 'local_friadmin');
        $mform->setDefault('includeusers', 0);

        $this->add_action_buttons(true, get_string('continue'));
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     *
     * @return array the errors that were found
     */
    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate shortname.
        if ($course = $DB->get_record('course', array('shortname' => $data['selshortname']),
            '*', IGNORE_MULTIPLE)
        ) {
            $errors['selshortname'] = get_string('shortnametaken', '', $course->fullname);
        }

        return $errors;
    }
}//local_friadmin_new_course_form
