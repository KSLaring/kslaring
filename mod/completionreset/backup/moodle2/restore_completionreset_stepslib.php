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

/**
 * Define all the restore steps that will be used by the restore_completionreset_activity_task
 */

/**
 * Structure step to restore one completionreset activity
 */
class restore_completionreset_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $completionreset = new restore_path_element('completionreset', '/activity/completionreset');
        $paths[] = $completionreset; 
        $completionreset_activities = new restore_path_element('completionreset_activities', '/activity/completionreset/completionreset_activities');
        $paths[] = $completionreset_activities;

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_completionreset($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // insert the completionreset record
        $newitemid = $DB->insert_record('completionreset', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }
    
    protected function process_completionreset_activities($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // insert the completionreset record
        if($DB->record_exists('completionreset_activities', array('course'=>$data->course))){
    		//we only have one activities entry per course
    		//if it exists just exit
        	return;
        }
        
        //this is the part I am not so sure what to do
        if($data->activities && !empty($data->activities)){
        	$oldcmids = explode(',',$data->activities);
        	$newcmids = array();
        	foreach($oldcmids as $oldcmid){
        		//HERE WE NEED TO DO SOME LOOK UP FOR THE NEW CMID
        		$newcmids[] = $oldcmid;
        	}
        	$data->activities = implode(',',$newcmids);
        }
        
        $newitemid = $DB->insert_record('completionreset_activities', $data);
        // immediately after inserting "activity" record, call this
       // $this->apply_activity_instance($newitemid);
    }

}
