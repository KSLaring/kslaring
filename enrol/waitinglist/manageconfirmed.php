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
 * Manage Waiting List Enrolment Confirmations
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id         = required_param('id', PARAM_INT); // course id
$action     = optional_param('action', '', PARAM_ALPHANUMEXT);
$centryid = optional_param('centryid', 0, PARAM_INT);
$format = optional_param('format', 'display', PARAM_TEXT);
$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

if ($course->id == SITEID) {
    redirect("$CFG->wwwroot/");
}

require_login($course);
require_capability('moodle/course:enrolreview', $context);
$canconfig = has_capability('moodle/course:enrolconfig', $context);

$PAGE->set_url('/enrol/waitinglist/manageconfirmed.php', array('id'=>$course->id));
if($format=='print'){
 	//$PAGE->set_pagelayout('print');
 	$PAGE->set_pagelayout('popup');
}else{
	$PAGE->set_pagelayout('admin');
}
$PAGE->set_title(get_string('manageconfirmed', 'enrol_waitinglist'));
$PAGE->set_heading($course->fullname);

$entryman= \enrol_waitinglist\entrymanager::get_by_course($course->id);

//init our error flag/message
$error = false;

if ($canconfig and $action and confirm_sesskey()) {
        switch($action){
			case 'unconfirm':
				$ok = $entryman->unconfirm_entry($centryid);
				if($ok){
					redirect($PAGE->url);
				}else{
					$error = get_string('unconfirmfailed','enrol_waitinglist');
				}
				break;
            }	
	}

// iterate through enrol plugins and add to the display table
$centries =$entryman->get_confirmed_entries();
$url = '/enrol/waitinglist/manageconfirmed.php';
$totalseats = 0;
$rows = array();
foreach ($centries as $centry) {
    $actions = array();

    if ($canconfig) {
			// edit links
			$aurl = new moodle_url($url, array('action'=>'unconfirm','centryid'=>$centry->id,'sesskey'=>sesskey(), 'id'=>$course->id));
			$unconfirmbutton= new single_button($aurl, get_string('unconfirm','enrol_waitinglist'));
			$unconfirmbutton->add_confirm_action(get_string('unconfirmwarning','enrol_waitinglist'));
			$actions[] = $OUTPUT->render($unconfirmbutton);
    }

	// add a row to the table
	$user = $DB->get_record('user',array('id'=>$centry->userid));
	if($user){
		// Get workplaces connected with user
		if (!$centry->companyid) {
			$centry->company = $entryman->get_workplace_connected($user->id);
		}//companydid
		
		$newrow= array('user'=>fullname($user),
			'email'=>$user->email,
			'institution'=>$centry->company,
			'methodtype'=>get_string($centry->methodtype .'_displayname','enrol_waitinglist'),
			'seats'=>$centry->seats, 
			'confirmedseats'=>$centry->confirmedseats + 1);
		if($format == 'display'){
			$newrow['actions']=implode('&nbsp;', $actions);
		}
		$rows[] = $newrow;
		$totalseats = $totalseats + $centry->confirmedseats + 1;
	}
}

//setup the headrow
$headrow = array(get_string('name'),get_string('email'),get_string('institution'),
	get_string('methodheader','enrol_waitinglist'),get_string('requestedseatsheader','enrol_waitinglist'),
	get_string('confirmedseatsheader','enrol_waitinglist'));
$lastrow=array('','','','','',get_string('totalcell','enrol_waitinglist',$totalseats)); 
if($format != 'csv' && $format !='print'){
			$headrow[]=get_string('unconfirm','enrol_waitinglist');
			$lastrow[]='';
}

//Prepare the heading for the report
$reporttitle = get_string('manageconfirmedheading', 'enrol_waitinglist', $course->fullname);
$tableheading="";


//prepare our renderer
$renderer = $PAGE->get_renderer('enrol_waitinglist');

//if this a CSV export, don't print any html
//quit after exports
if($format== 'csv'){
	$renderer->render_table_csv($reporttitle, 'manageconfirmed', $headrow, $rows);
	exit;
}

//Start printing html
echo $renderer->header();
echo $renderer->heading($reporttitle,3);

if($error){
	echo $renderer->heading($error,3);
}
echo $renderer->box_start('generalbox boxalignleft boxwidthwide');

//is the list empty? If so report that.
if($entryman->get_confirmed_listtotal()==0){
	echo $renderer->heading(get_string('confirmedlistisempty', 'enrol_waitinglist'),2);
}

//we could use these .. maybe
//$aligns = array('left','left','center','center', 'center', 'center');

switch($format){
	case 'print':
		echo $renderer->render_table_html($tableheading, 'manageconfirmed', $headrow,$lastrow ,$rows);
		echo $renderer->render_report_footer();
		exit;
	default:
		echo $renderer->render_table_html($tableheading, 'manageconfirmed', $headrow,$lastrow, $rows);
		echo $renderer->show_reports_options($course->id,'manageconfirmed');
}
echo $renderer->box_end();
echo $renderer->footer();
