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
 * Waiting List enrolments methods
 *
 * @package    core_enrol
 * @copyright  2015 Justin Hunt {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/enrol/waitinglist/lib.php');


$id         = required_param('id', PARAM_INT); // course id
$action     = optional_param('action', '', PARAM_ALPHANUMEXT);
$methodtype = optional_param('methodtype', '', PARAM_TEXT);


$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$waitinglist = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'waitinglist'), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

if ($course->id == SITEID) {
    redirect("$CFG->wwwroot/");
}

require_login($course);
require_capability('moodle/course:enrolreview', $context);

$canconfig = has_capability('moodle/course:enrolconfig', $context);

$PAGE->set_url('/enrol/waitinglist/managemethods.php', array('id'=>$course->id));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managemethods', 'enrol_waitinglist'));
$PAGE->set_heading($course->fullname);


$methods = enrol_waitinglist_plugin::get_methods($course,$waitinglist->id);

if ($canconfig and $action and confirm_sesskey()) {


        if ($action === 'disable') {
			foreach($methods as $method){
				if($methodtype == $method->get_type()){
					$method->deactivate();
				}
			}
			

        } else if ($action === 'enable') {
			foreach($methods as $method){
				if($methodtype == $method->get_type()){
					$method->activate();
				}
			}
			
        }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managemethods', 'enrol_waitinglist'));

echo $OUTPUT->box_start('generalbox boxalignleft boxwidthwide');

// display strings
$strenable  = get_string('enable');
$strdisable = get_string('disable');
$strmanage  = get_string('settings');

$table = new html_table();
$table->head  = array(get_string('name'), $strenable, $strmanage);
$table->align = array('left', 'center', 'center');
$table->width = '100%';
$table->data  = array();


foreach ($methods as $method) {
	$settingsurl = new moodle_url('/enrol/waitinglist/editmethod.php', array('sesskey'=>sesskey(), 'courseid'=>$course->id, 'methodtype'=>$method->get_type()));
    $visibilityurl = new moodle_url('/enrol/waitinglist/managemethods.php', array('sesskey'=>sesskey(),'id'=>$course->id, 'methodtype'=>$method->get_type()));
	
	$displayname = $method->get_display_name();
    if (!$method->is_active()) {
        $displayname = html_writer::tag('span', $displayname, array('class'=>'dimmed_text'));
		$aurl = new moodle_url($visibilityurl, array('action'=>'enable'));
         $visibilityicon = $OUTPUT->action_icon($aurl, new pix_icon('t/show', $strenable, 'core', array('class' => 'iconsmall')));
    }else{
		$aurl = new moodle_url($visibilityurl, array('action'=>'disable'));
         $visibilityicon = $OUTPUT->action_icon($aurl, new pix_icon('t/hide', $strdisable, 'core', array('class' => 'iconsmall')));
	}

    if ($method->has_settings()) {
		 $aurl = new moodle_url($settingsurl, array('action'=>'settings'));
         $editicon = $OUTPUT->action_icon($aurl, new pix_icon('t/edit', $strmanage, 'core', array('class' => 'iconsmall')));
	}else{
		$editicon=false;
	}
     

    // add a row to the table
    $table->data[] = array($displayname, $visibilityicon,$editicon ? $editicon : '');

}
echo html_writer::table($table);

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
