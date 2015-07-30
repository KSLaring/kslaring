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
 * Change password page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('add_form.php');

require_login();
$id  = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', 0, PARAM_ALPHANUM);
$systemcontext = context_system::instance();
$PAGE->set_url('/local/bulkemails/index.php', array('id'=>$id));
$PAGE->set_pagelayout('frontpage');
global $DB;

if($id && $action=='edit'){
    $res = $DB->get_record("bulkemails", array("id"=>$id));
	$editdata=$res;
	$editdata->action='update';
}else if($id && $action=='delete'){
	$con=array('id'=>$id);
	$res= $DB->delete_records('bulkemails',$con);
	redirect($CFG->wwwroot.'/local/bulkemails/index.php');
}else{
	$editdata=new stdClass();
	$editdata->action='add';
}

$data=array('editdata'=>$editdata);
$mform = new add_form(NULL,$data);
$mform->set_data();
if ($mdata = $mform->get_data()) {
    if($mdata->submit=='add'){
	//print_r($mdata); die;  
		$insert->institution=$mdata->institution;
		$insert->bulkemails=$mdata->bulkemails;
		$insert->created=strtotime('now');
		$insert->refid=0;
		$res=$DB->insert_record('bulkemails',$insert, $returnid=true, $bulk=false);
    //print_r($res); die;  
   
    }else if($mdata->submit=='update'){
	 
		$update->id=$mdata->id; 
		$update->bulkemails=$mdata->bulkemails;
		$update->created=strtotime('now');
	   $res=$DB->update_record('bulkemails', $update,$bulk=false);
	}else{
    
   
    }
	redirect($CFG->wwwroot.'/local/bulkemails/index.php');
}

$str = 'Manage bulkemails';
$PAGE->set_title($str);
$PAGE->set_heading($str);
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
