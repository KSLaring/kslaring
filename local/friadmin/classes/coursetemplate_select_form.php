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

use \stdClass;

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

        $defaultText = get_string('selname', 'local_friadmin');
        $attributes = array(
            'placeholder' => $defaultText
        );
        $mform->addElement('text', 'selname',
            get_string('selname', 'local_friadmin'), $attributes);
        $mform->setType('selname', PARAM_TEXT);

        $options = array('0' => get_string('selcategory', 'local_friadmin'));
        $options = array_merge($options, $customdata['categories']);
        $mform->addElement('select', 'selcategory',
            get_string('selcategory', 'local_friadmin'), $options);
        $mform->setDefault('selcategory', '0');

        $options = array('0' => get_string('seltemplate', 'local_friadmin'));
        $options = array_merge($options, $customdata['templates']);
        $mform->addElement('select', 'seltemplate',
            get_string('seltemplate', 'local_friadmin'), $options);
        $mform->setDefault('seltemplate', '0');

        $mform->addElement('submit', 'submitbutton',
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
}
