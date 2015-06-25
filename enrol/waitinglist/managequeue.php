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
 * Manage Waitling List Enrolment Queue
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id         = required_param('id', PARAM_INT); // course id
$action     = optional_param('action', '', PARAM_ALPHANUMEXT);
$qentryid = optional_param('qentryid', 0, PARAM_INT);
$confirm    = optional_param('confirm', 0, PARAM_BOOL);
$confirm2   = optional_param('confirm2', 0, PARAM_BOOL);

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

$context = context_course::instance($course->id, MUST_EXIST);

if ($course->id == SITEID) {
    redirect("$CFG->wwwroot/");
}

require_login($course);
require_capability('moodle/course:enrolreview', $context);
$canconfig = has_capability('moodle/course:enrolconfig', $context);

$PAGE->set_url('/enrol/waitinglist/managequeue.php', array('id'=>$course->id));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managequeue', 'enrol_waitinglist'));
$PAGE->set_heading($course->fullname);

$queueman= \enrol_waitinglist\queuemanager::get_by_course($course->id);
$entryman= \enrol_waitinglist\entrymanager::get_by_course($course->id);

//init our error flag/message
$error = false;

if ($canconfig and $action and confirm_sesskey()) {
        switch($action){
			case 'up':
				$qentry = $queueman->get_qentry($qentryid);
				if($qentry && $qentry->queueno >1){
					$ok = $queueman->bump($qentry, $qentry->queueno,$qentry->queueno-1);
					if($ok){
						redirect($PAGE->url);
					}else{
						$error = get_string('qmovefailed','enrol_waitinglist');
					}
				}
				break;
				
			case 'down':
				$qentry = $queueman->get_qentry($qentryid);
				if($qentry && $qentry->queueno <$queueman->get_listtotal()){
					$ok = $queueman->bump($qentry, $qentry->queueno,$qentry->queueno+1);
					if($ok){
						redirect($PAGE->url);
					}else{
						$error = get_string('qmovefailed','enrol_waitinglist');
					}
				}
				break;

			case 'remove':
				$qentry = $queueman->get_qentry($qentryid);
				if($qentry->allocseats >1){
					$ok = $entryman->update_seats($qentry->id,$qentry->allocseats);
				}else{
					$ok = $queueman->really_remove_entry($qentryid);
				}
				if($ok){
					redirect($PAGE->url);
				}else{
					$error = get_string('qremovefailed','enrol_waitinglist');
				}
				break;
            }	
	}

            

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managequeue', 'enrol_waitinglist'));
if($error){
	echo $OUTPUT->heading($error,3);
}
echo $OUTPUT->box_start('generalbox boxalignleft boxwidthwide');

// display strings
$strup      = get_string('up');
$strdown    = get_string('down');
$strdelete  = get_string('delete');
$strenable  = get_string('enable');
$strdisable = get_string('disable');
$strmanage  = get_string('managequeue', 'enrol_waitinglist');

if($queueman->get_listtotal()==0){
	echo $OUTPUT->heading(get_string('waitinglistisempty', 'enrol_waitinglist'),2);
}

$table = new html_table();
//$table->head  = array(get_string('name'), get_string('users'), $strup.'/'.$strdown, get_string('edit'));
$table->head  = array('',get_string('name'),get_string('email'),get_string('institution'),get_string('methodheader','enrol_waitinglist'),get_string('seatsheader','enrol_waitinglist'),get_string('allocseatsheader','enrol_waitinglist'),get_string('updownheader','enrol_waitinglist'), get_string('remove'));
$table->align = array('left','left','left','center','center', 'center', 'center');
$table->width = '100%';
$table->data  = array();

// iterate through enrol plugins and add to the display table
$updowncount = 1;
//$icount = count($instances);
$url = '/enrol/waitinglist/managequeue.php';

foreach ($queueman->qentries as $qentry) {

    $updown = array();
    $edit = array();
    
    //if all the seats are allocated, we do not need to show them
    if($qentry->allocseats >= $qentry->seats){
    	continue;
    }

    if ($canconfig) {
        if ($qentry->queueno > 1) {
            $aurl = new moodle_url($url, array('action'=>'up','qentryid'=>$qentry->id,'sesskey'=>sesskey(), 'id'=>$course->id));
            $updown[] = $OUTPUT->action_icon($aurl, new pix_icon('t/up', $strup, 'core', array('class' => 'iconsmall')));
        } else {
            $updown[] = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('spacer'), 'alt'=>'', 'class'=>'iconsmall'));
        }
        if ($qentry->queueno < $queueman->get_entrycount()) {
            $aurl = new moodle_url($url, array('action'=>'down','qentryid'=>$qentry->id,'sesskey'=>sesskey(), 'id'=>$course->id));
            $updown[] = $OUTPUT->action_icon($aurl, new pix_icon('t/down', $strdown, 'core', array('class' => 'iconsmall')));
        } else {
            $updown[] = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('spacer'), 'alt'=>'', 'class'=>'iconsmall'));
        }


			// edit links
			$aurl = new moodle_url($url, array('action'=>'remove','qentryid'=>$qentry->id,'sesskey'=>sesskey(), 'id'=>$course->id));
			/*
			$deleteicon= new moodle_action_icon($aurl, new pix_icon('t/delete', $strdelete, 'core', array('class' => 'iconsmall')));
			$deleteicon->add_confirm_action('really delete');
			$edit[] = $OUTPUT->action_icon(deleteicon);
			*/
			$deletebutton= new single_button($aurl, get_string('remove'));
			$deletebutton->add_confirm_action(get_string('removeconfirmmessage','enrol_waitinglist'));
			$edit[] = $OUTPUT->render($deletebutton);
    }


	// add a row to the table
	//	$updown = array('up','down');
	//	$edit = array('edit','delete');
	$user = $DB->get_record('user',array('id'=>$qentry->userid));
	if($user){
		$table->data[] = array($qentry->queueno,fullname($user),$user->email,$user->institution, get_string($qentry->methodtype .'_displayname','enrol_waitinglist'), $qentry->seats,$qentry->allocseats, implode('&nbsp;', $updown), implode('&nbsp;', $edit));
	}

}

echo html_writer::table($table);


echo $OUTPUT->box_end();

echo $OUTPUT->footer();
