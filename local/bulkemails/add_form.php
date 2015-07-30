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
 * Forgot password page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2006 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Reset forgotten password form definition.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2006 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_form extends moodleform {

    /**
     * Define the forgot password form.
     */
    function definition() {
        $mform    = $this->_form;
       
       global $USER;
        $editdata=$this->_customdata['editdata'];
       
//print_r($editdata); die;

       $mform->addElement('header', 'Add New bulkemails','Add New bulkemails', '');
	   
	   if(isset($editdata->id)){
		$id=$editdata->id;
		}else{
		$id='';
		}
		$mform->addElement('hidden', 'id',$id);
	   	$institutions=get_institutions_list($USER->id);
		$select = $mform->addElement('select', 'institution','Institution', $institutions);
		//$select->setSelected('0000ff');
	   
	   
	   if(isset($editdata->bulkemails)){
		$bulkemails=$editdata->bulkemails;
		}else{
		$bulkemails='';
		}
       	$mform->addElement('text', 'bulkemails','Add New bulkemails Here','value='.$bulkemails); // Add elements to your form
     
        $submitlabel='Submit';
        $mform->addElement('submit', 'submit', $editdata->action);
    }

   

}
