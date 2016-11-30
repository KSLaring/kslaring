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
 * Completion Reset Module version information
 *
 * @package mod_completionreset
 * @copyright  2015 Justin Hunt (http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/completionreset/locallib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once('locallib.php');

//$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
//$p       = optional_param('p', 0, PARAM_INT);  // Completion Reset instance ID
$courseid= optional_param('course', 0, PARAM_INT);  // Completion Reset CourseId


$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id);
$PAGE->set_url('/mod/completionreset/choose.php', array('id' => $course->id));
require_login($course, true);
require_capability('mod/completionreset:manage', $context);
$PAGE->set_pagelayout('course');
$PAGE->set_context($context);
$PAGE->set_title($course->shortname.': '. get_string('title','completionreset'));
$PAGE->set_heading($course->fullname);
$renderer = $PAGE->get_renderer('mod_completionreset');

//$context = context_module::instance($cm->id);


$mform = new mod_completionreset_chooseform(null,array(''));
//if the cancel button was pressed, we are out of here
if (!$mform->is_cancelled()) {
    //if we have data, then our job here is to save it;
	if ($formdata = $mform->get_data()) {
		$data=new stdClass();
		$data->course=$formdata->course;
		$data->activities=$formdata->{MOD_COMPLETIONRESET_UPDATEFIELD};
		$data->timemodified=time();
		if($DB->record_exists(MOD_COMPLETIONRESET_ACTIVITIESTABLE,array('course'=>$formdata->course))){
			$data->id=$formdata->id;
			$DB->update_record(MOD_COMPLETIONRESET_ACTIVITIESTABLE,$data);
		}else{
			$DB->insert_record(MOD_COMPLETIONRESET_ACTIVITIESTABLE,$data);
		}
		//toggle disabled/enabled of completion reset links
		/*
		if(!empty($data->activities)){
			mod_completionreset_helper::set_completionreset_availability($data->course,true);
		}else{
			mod_completionreset_helper::set_completionreset_availability($data->course,false);
		}
		*/
	}
}

//data
/*
$rec = $DB->get_record(MOD_COMPLETIONRESET_ACTIVITIESTABLE,array('course'=>$course->id));
if($rec){
	$activities = explode(',',$rec->activities);
}else{
	$activities=array();
}
$modinfo = get_fast_modinfo($course);
$cms = $modinfo->get_cms();
$unchosendata = array();
$chosendata = array();
$sortorderarray = array();
foreach($cms as $cm){
	if(in_array($cm->instance,$activities)){
		$chosendata[$cm->instance]=$cm->name;
	}else{
		$unchosendata[$cm->instance]=$cm->name;
	}
	$sortorderarray[]=$cm->instance;
}
*/
$allactivities = mod_completionreset_helper::get_all_activities($course);
$chosendata=$allactivities->chosendata;
$unchosendata=$allactivities->unchosendata;
$sortorderarray=$allactivities->sortorderarray;

/*
echo 'activities:<br/>';
print_r($activities);
echo 'chosendata:<br/>';
print_r($chosendata);
echo 'unchosendata:<br/>';
print_r($unchosendata);
*/
//get our javascript all ready to go
$jsmodule = array(
	'name'     => 'mod_completionreset',
	'fullpath' => '/mod/completionreset/module.js',
	'requires' => array('io','json','button','array-extras')
);
$opts =Array();
$opts['chosen'] =MOD_COMPLETIONRESET_CHOSEN;
$opts['unchosen'] =MOD_COMPLETIONRESET_UNCHOSEN;
$opts['updatefield'] =MOD_COMPLETIONRESET_UPDATEFIELD;
$opts['chosendata'] =$chosendata;
$opts['unchosendata'] =$unchosendata;
$opts['sortorder']=implode(',',$sortorderarray);
$PAGE->requires->js_init_call('M.mod_completionreset.init', array($opts),false,$jsmodule);
$chooser = $renderer->fetch_chooser($chosendata,$unchosendata);
$mform = new mod_completionreset_chooseform(null,array($chooser));
$data=new stdClass();
$rec = $DB->get_record(MOD_COMPLETIONRESET_ACTIVITIESTABLE,array('course'=>$course->id));
if($rec){
	$data->id = $rec->id;
	$data->{MOD_COMPLETIONRESET_UPDATEFIELD}=$rec->activities;
}
$data->course=$course->id;
$mform->set_data($data);
echo $renderer->header_choose();
$mform->display();
echo $renderer->footer();
