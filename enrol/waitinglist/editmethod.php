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
 * Adds new instance of enrol_waitinglist to specified course
 * or edits current instance.
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt   {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('edit_form.php');
require_once('lib.php');

$courseid = required_param('courseid', PARAM_INT);
$methodtype = required_param('methodtype', PARAM_TEXT);
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/waitinglist:config', $context);

$PAGE->set_url('/enrol/waitinglist/editmethod.php', array('courseid'=>$course->id,'methodtype'=>$methodtype));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/waitinglist/managemethods.php', array('id'=>$course->id));
/*
if (!enrol_is_enabled('waitinglist')) {
    redirect($return);
}
*/
$class = '\enrol_waitinglist\method\\' . $methodtype. '\enrolmethod' .$methodtype ;
if(!class_exists($class)){redirect($return);}
$method = $class::get_by_course($courseid);
$plugin = enrol_get_plugin('waitinglist');
$dummyplugin = $method->get_dummy_form_plugin();

/*
if ($instances = $DB->get_records('enrol_waitinglist_method', array('courseid'=>$course->id,$DB->sql_compare_text('methodtype')=>"'" . $methodtype ."'"), 'id ASC')) {
	*/
	
	/*
if(	!$instance = $DB->get_record_sql("SELECT * FROM {enrol_waitinglist_method} WHERE courseid = $course->id AND " .$DB->sql_compare_text('methodtype') . "='".$methodtype ."'")){
*/
	
		//BETTER ERROR LOGIC HERE
//		redirect($return);
/*
} else {
    require_capability('moodle/course:enrolconfig', $context);
    // No instance yet, we have to add new instance.
    navigation_node::override_active_url(new moodle_url('/enrol/waitinglist/managemethods.php', array('id'=>$course->id)));
    $instance = new stdClass();
    $instance->id              = null;
    $instance->courseid        = $course->id;
}
*/


//$mform = new enrol_waitinglist_edit_form(null, array($instance, $dummyplugin, $context));
$formclass = $class . '_form';
$mform = new $formclass(null, array($method, $dummyplugin, $context));

if ($mform->is_cancelled()) {
    redirect($return);

} else if ($data = $mform->get_data()) {

    if ($method->id) {

        $data->timemodified    = time();

        $DB->update_record('enrol_waitinglist_method', $data);

		//what to do here? Justin 2015/02
        // Use standard API to update instance status.
		/*
        if ($method->status != $data->status) {
            $method = $DB->get_record('enrol_waitinglist_method', array('id'=>$method->id));
            
			$plugin->update_status($instance, $data->status);
            $context->mark_dirty();
        }
		*/

    } else {
        //we should never get here
        $DB->insert_record('enrol_waitinglist_method', $data);
    }

    redirect($return);
}

$PAGE->set_title($method->get_display_name());
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($method->get_display_name());
$mform->display();
echo $OUTPUT->footer();
