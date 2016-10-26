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

//namespace mod_registerattendance;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/formslib.php');

//use \stdClass;

/**
 * The form for the mod_registerattendance view selection area
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_registerattendance_view_filter_form extends \moodleform {
    function definition() {
        global $SESSION;
        $mform = $this->_form;
        $customdata = $this->_customdata;

        // Add the course module id.
        $mform->addElement('hidden', 'id', $customdata['id']);
        $mform->setType('id', PARAM_INT);

        // Filter.
        $mform->addElement('header', 'filterhdr', get_string('filterhdr', 'mod_registerattendance'));
        $mform->setExpanded('filterhdr', false);

        // The first form row.
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'showattended', '',
            get_string('showattended_all', 'mod_registerattendance'), SHOW_ATTENDED_ALL);
        $elementgroup[] = $mform->createElement('radio', 'showattended', '',
            get_string('showattended_attended', 'mod_registerattendance'), SHOW_ATTENDED_ATTENDED);
        $elementgroup[] = $mform->createElement('radio', 'showattended', '',
            get_string('showattended_notattended', 'mod_registerattendance'), SHOW_ATTENDED_NOT_ATTENDED);
        $mform->setDefault('showattended', 0);

        $mform->addGroup($elementgroup, 'showattendedarr', '', '<span class="group-spacer"> </span>', false);


        // The second form row.
        $elementgroup = array();
        $attributes = array(
            'placeholder' => get_string('searchnameshort', 'mod_registerattendance'),
            'size' => 40
        );
        $textinput = $mform->createElement('text', 'searchname',
            get_string('searchnamelabel', 'mod_registerattendance'), $attributes);
        $textinput->setHiddenLabel(false);
        $mform->setType('searchname', PARAM_TEXT);
        $elementgroup[] = $textinput;

        $label = $mform->createElement('static', 'searchnamelabel', '',
            get_string('searchnamelabel', 'mod_registerattendance'));
        $elementgroup[] = $label;

        $mform->addGroup($elementgroup, 'selectrowtwo', '', '<span class="group-spacer"> </span>', false);


        // The third form row.
        $elementgroup = array();
        $attributes = array(
            'size' => 5
        );
        $textinput = $mform->createElement('text', 'showperpage',
            get_string('showperpage', 'mod_registerattendance'), $attributes);
        $textinput->setHiddenLabel(false);
        $mform->setType('showperpage', PARAM_TEXT);
        $elementgroup[] = $textinput;

        $label = $mform->createElement('static', 'showperpagelabel', '',
            get_string('showperpage', 'mod_registerattendance'));
        $elementgroup[] = $label;

        $mform->addGroup($elementgroup, 'selectrowthree', '', '<span class="group-spacer"> </span>', false);

        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('addfilter', 'mod_registerattendance'));
        //$buttonarray[] = $mform->createElement('reset', 'resetbutton', get_string('revert'));
        $mform->addGroup($buttonarray, 'buttonar', '', '<span class="group-spacer"> </span>', false);
    }

    /**
     * Set the default form values
     *
     * The associative $defaults: array 'elementname' => 'defaultvalue'
     *
     * @param array $defaults The default values
     */
    public function set_defaults($defaults = array()) {
        $mform = $this->_form;
        foreach ($defaults as $elementname => $defaultvalue) {
            $mform->setDefault($elementname, $defaultvalue);
        }
    }
}
