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

$id         = optional_param('id', 0, PARAM_INT); // Course Module ID
$reset      = optional_param('reset', 0, PARAM_INT); // action
$resetusers = optional_param('resetusers', 0, PARAM_INT); // action
$cr         = optional_param('cr', 0, PARAM_INT);  // Completion Reset instance ID

if ($cr) {
    if (!$completionreset = $DB->get_record('completionreset', array('id'=>$cr))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('completionreset', $completionreset->id, $completionreset->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('completionreset', $id)) {
        print_error('invalidcoursemodule');
    }
    $completionreset = $DB->get_record('completionreset', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

//set page url so we have somewhere to return to if bumped off to login
$PAGE->set_url('/mod/completionreset/view.php', array('id' => $cm->id));

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/completionreset:view', $context);
$renderer=$PAGE->get_renderer('mod_completionreset');


//do the reset if action=reset
if($reset==1){
    mod_completionreset_helper::perform_reset($course);
	$redirecturl = new moodle_url('/course/view.php', array('id'=>$course->id));
	redirect($redirecturl,get_string('coursehasbeenreset','completionreset'),3); 
	return;
}elseif ($resetusers==1) {
    mod_completionreset_helper::perform_reset($course);

    $redirecturl = new moodle_url('/course/view.php', array('id'=>$course->id));
    redirect($redirecturl,get_string('courseusershasbeenreset','completionreset'),3);
    return;
}


//get reset activities
$allactivities = mod_completionreset_helper::get_all_activities($course);


$PAGE->set_title($course->shortname.': '. get_string('title','completionreset'));
$PAGE->set_heading($course->fullname);

echo $renderer->header_reset($completionreset->name);
echo $renderer->show_reset_instructions();
if(empty($allactivities->chosencms)){
	echo $renderer->show_no_activities($course);
}else{
	echo $renderer->show_reset_activities($allactivities->chosencms,$course);
	echo $renderer->show_reset_buttons($course,$cm);
}

$coursecontext = context_course::instance($course->id);
if(has_capability('mod/completionreset:manage', $coursecontext)){
	echo $renderer->show_choose_button($course);
}
echo $renderer->footer();
