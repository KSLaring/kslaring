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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * The form for the local_friadmin mysettings selection area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_mysettings_select_form extends \moodleform {
    function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $options = array('' => get_string('seltemplcategory', 'local_friadmin'));
        $options = $options + $customdata['categories'];

        $mform->addElement('select', 'selcategory',
            get_string('seltemplcategorylabel', 'local_friadmin'), $options);
        $mform->addRule('selcategory', get_string('missingseltemplcategory', 'local_friadmin'),
            'required', null, 'client');
        $mform->setDefault('selcategory', $customdata['localtempcategory']);
        $mform->addElement('static', 'seltemplcategorydesc', '',
            get_string('seltemplcategorydesc', 'local_friadmin'));


        $options = array('' => get_string('selpreftemplate', 'local_friadmin'));
        $options = $options + $customdata['eventtemplates'];

        $mform->addElement('select', 'selpreftemplate',
            get_string('selpreftemplatelabel', 'local_friadmin'), $options);
        $mform->setDefault('selpreftemplate',
            $customdata['preftemplates'][TEMPLATE_TYPE_EVENT]);
        $mform->addElement('static', 'selpreftemplatedesc', '',
            get_string('selpreftemplatedesc', 'local_friadmin'));


        $options = array('' => get_string('selprefnetcoursetemplate', 'local_friadmin'));
        $options = $options + $customdata['netcoursetemplates'];

        $mform->addElement('select', 'selprefnetcoursetemplate',
            get_string('selprefnetcoursetemplatelabel', 'local_friadmin'), $options);
        $mform->setDefault('selprefnetcoursetemplate',
            $customdata['preftemplates'][TEMPLATE_TYPE_NETCOURSE]);
        $mform->addElement('static', 'selprefnetcoursetemplatedesc', '',
            get_string('selprefnetcoursetemplatedesc', 'local_friadmin'));

        $mform->addElement('submit', 'mysettingssave',
            get_string('selmysetingssave', 'local_friadmin'));
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
