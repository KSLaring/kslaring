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
 * @package   mod_completionreset
 * @category  backup
 * @copyright  2015 Justin Hunt (poodll.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define all the backup steps that will be used by the backup_completionreset_activity_task
 */

/**
 * Define the complete completionreset structure for backup, with file and id annotations
 */
class backup_completionreset_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        //$userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $completionreset = new backup_nested_element('completionreset', array('id'), array(
            'name','timemodified'));
            
        //activities
        $completionreset_activities = new backup_nested_element('completionreset_activities', array('id'), array(
            'activities','timemodified'));
        
        // Build the tree.
        $completionreset->add_child($completionreset_activities);

        // Define sources
        $completionreset->set_source_table('completionreset', array('id' => backup::VAR_ACTIVITYID));
		$completionreset_activities->set_source_table('completionreset_activities',array());
        // Return the root element (completionreset), wrapped into standard activity structure
        return $this->prepare_activity_structure($completionreset);
    }
}
