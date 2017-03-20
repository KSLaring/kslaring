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
 * Show the enrol form (pulled from enrol_page_hook)
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt   {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('lib.php');

$courseid = required_param('id', PARAM_INT);
$methodtype = required_param('methodtype', PARAM_TEXT);
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
//require_capability('enrol/waitinglist:canbulkenrol', $context);

$formsurl = new moodle_url('/enrol/waitinglist/edit_enrolform.php', array('id'=>$course->id));
$instancesurl = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
$PAGE->set_url($formsurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($course->fullname);

//get our waitinglist and method instances.
//redeirect to instances page if we can't
if (!enrol_is_enabled('waitinglist')) {
    redirect($instancesurl);
}
//get waitlist plugin and data instance
$waitinglist = $DB->get_record('enrol', array('courseid' => $courseid,'enrol'=>'waitinglist'));
if(!$waitinglist){redirect($instancesurl);}


//get method instance
$class = '\enrol_waitinglist\method\\' . $methodtype. '\enrolmethod' .$methodtype ;
if (!class_exists($class)){redirect($instancesurl);}
$themethod = $class::get_by_course($course->id, $waitinglist->id); 
if (!$themethod){redirect($instancesurl);}

$wl = enrol_get_plugin('waitinglist');
$ret = $wl->can_enrol($waitinglist);
$flagged= false;
$warningmessage='';
if($ret !== true){
	$warningmessage=$ret;
	$flagged= true;
}
/* Clean Cookies    */
setcookie('level_0',0);
setcookie('level_1',0);
setcookie('level_2',0);
setcookie('level_3',0);
setcookie('ansvar_selected',0);

list($ok,$formhtml) = $themethod->enrol_page_hook($waitinglist,$flagged);
if($ok){
	echo $OUTPUT->header();
	echo $formhtml;
	echo $OUTPUT->footer();
}else{
	echo $OUTPUT->header();
	echo $warningmessage . '<br/>' . $formhtml;
	echo $OUTPUT->footer();
}