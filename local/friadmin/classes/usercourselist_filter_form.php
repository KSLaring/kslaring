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
 * The form for the local_friadmin usercourse_list selection area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_usercourselist_filter_form extends \moodleform {
    function definition() {
        global $SESSION;
        $mform = $this->_form;
        $customdata = $this->_customdata;

        // The first form row
        $elementgroup = array();

        $options = array('0' => get_string('seleverywhere', 'local_friadmin'));
        $options = $options + $customdata['municipality'];
        $elementgroup[] = $mform->createElement('select', 'selmunicipality', '', $options);
        $mform->setDefault('selmunicipality', '0');

        $options = array('0' => get_string('selsector', 'local_friadmin'));
        $options = $options + $customdata['sector'];
        $elementgroup[] = $mform->createElement('select', 'selsector', '', $options);
        $mform->setDefault('selsector', '0');
        //$mform->disabledIf('selsector', 'selmunicipality', 'eq', '0');

        $options = array('0' => get_string('sellocation', 'local_friadmin'));
        $options = $options + $customdata['location'];
        $elementgroup[] = $mform->createElement('select', 'sellocation', '', $options);
        $mform->setDefault('sellocation', '0');
        //$mform->disabledIf('sellocation', 'selmunicipality', 'eq', '0');

        $mform->addGroup($elementgroup, 'selectrowone',
            get_string('locationline', 'local_friadmin'),
            '<span class="group-spacer"> </span>', false);
        $mform->addHelpButton('selectrowone', 'locationline', 'local_friadmin');


        // The second form row
        $elementgroup = array();

        $options = array('optional' => true);
        if (!empty($customdata['from'])) {
            $options['startyear'] = date('Y', $customdata['from']);
        }
        if (!empty($customdata['to'])) {
            $options['stopyear'] = date('Y', $customdata['to']);
        }

        $elementgroup[] = $mform->createElement('date_selector', 'seltimefrom',
            '', $options);
        $defaultvalue = empty($customdata['from']) ? (time() + 3600 * 24) : $customdata['from'];
        $mform->setDefault('seltimefrom', $defaultvalue);

        $elementgroup[] = $mform->createElement('date_selector', 'seltimeto',
            '', $options);
        $mform->setDefault('seltimeto', $customdata['to']);

        $mform->addGroup($elementgroup, 'selectrowtwo',
            get_string('fromto', 'local_friadmin'),
            '<span class="group-spacer"> </span>', false);
        $mform->addHelpButton('selectrowtwo', 'fromto', 'local_friadmin');


        // The third form row
        $elementgroup = array();

        $defaultText = get_string('selname', 'local_friadmin');
        $attributes = array(
            'placeholder' => $defaultText
        );
        $textinput = $mform->createElement('text', 'selname',
            get_string('selname', 'local_friadmin'), $attributes);
        $textinput->setHiddenLabel(false);
        $mform->setType('selname', PARAM_TEXT);
        $elementgroup[] = $textinput;
        $mform->addGroup($elementgroup, 'selectrowthree', get_string('coursename', 'local_friadmin'),
            '<span class="group-spacer"> </span>', false);
        $mform->addHelpButton('selectrowthree', 'coursename', 'local_friadmin');

        $elementgroup = array();
        $classRoom = $mform->createElement('checkbox', 'classroom', '',
            get_string('only_classroom', 'local_friadmin'));
        $elementgroup[] = $classRoom;
        $mform->setDefault('classroom', false);
        /**
         * @updateDate  02/12/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Add checkbox -- Only eLearnign Course
         */
        $eLearning = $mform->createElement('checkbox', 'elearning', '',
            get_string('only_elearning', 'local_friadmin'));
        $elementgroup[] = $eLearning;
        $mform->setDefault('elearning', false);

        $elementgroup[] = $mform->createElement('submit', 'submitbutton',
            get_string('selsubmit', 'local_friadmin'));

        $mform->addGroup($elementgroup, 'selectrowthree', '&nbsp;&nbsp;&nbsp;&nbsp;',
            '<span class="group-spacer"> </span>', false);

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
}
