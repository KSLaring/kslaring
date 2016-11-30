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

/**
 * Completion Reset configuration form
 *
 * @package mod_completionreset
 * @copyright  2015 Justin Hunt (http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/completionreset/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_completionreset_mod_form extends moodleform_mod {
    function definition() {
        global $CFG;

        $mform = $this->_form;
        $config = get_config('completionreset');

       // $mform->addElement('header', 'formheading', get_string('formheading', 'completionreset'));
		
		//invisible name field
        /*
		$mform->addElement('hidden', 'name', get_string('name'));
		$mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', get_string('title','completionreset'));
		*/
		
		//visible name field
		$mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
		$mform->setDefault('name', get_string('title','completionreset'));
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
		
		$this->standard_coursemodule_elements();
        $this->add_action_buttons();

    }
}

