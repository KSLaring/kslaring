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

//Params
global $DB;
global $SESSION;
$courseid   = optional_param('course', 0, PARAM_INT);  // Completion Reset CourseId
$resetusers = optional_param('resetusers', 0, PARAM_INT);
$reset      = null;
$chooser    = '';

// Session variable to know from where it comes
//if (!isset($SESSION->addusers)) {
//    $SESSION->addusers = 0;
//}

// Course and cotnext
$course         = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context        = context_course::instance($course->id);
$return         = new moodle_url('/course/view.php',array('id' => $course->id));
$url            = new moodle_url('/mod/completionreset/choose.php', array('course' => $course->id));
// Get reset
$reset              = $DB->get_record(MOD_COMPLETIONRESET_TABLE,array('course' =>$courseid),'id');

require_login($course, true);
require_capability('mod/completionreset:manage', $context);

// Set page
$PAGE->set_url($url);
$PAGE->set_pagelayout('course');
$PAGE->set_context($context);
$PAGE->set_title($course->shortname.': '. get_string('title','completionreset'));
$PAGE->set_heading($course->fullname);
$renderer = $PAGE->get_renderer('mod_completionreset');

$mform = new mod_completionreset_chooseform(null,array($context,'',$resetusers));

//if the cancel button was pressed, we are out of here
if (!$mform->is_cancelled()) {
    /**
     * Description
     * Add the new functionality.
     * My users to the completion reset
     *
     * @updateDate  29/03/2017
     * @author      eFaktor     (fbv)
     */
    //if we have data, then our job here is to save it;
    if ($formdata = $mform->get_data()) {

        if (isset($formdata->savechanges) && ($formdata->savechanges)) {
            if ($resetusers) {
                mod_completionreset_helper::add_users_completion_reset($course->id,$reset->id,$formdata->{MOD_COMPLETIONRESET_UPDATEFIELD});
                mod_completionreset_helper::perform_reset($course,true);
                $redirecturl = new moodle_url('/course/view.php', array('id'=>$course->id));
                redirect($redirecturl,get_string('courseusershasbeenreset','completionreset'),3);
                return;
            }else {
                // Set data
                $data = new stdClass();
                $data->course       = $formdata->course;
                $data->activities   = $formdata->{MOD_COMPLETIONRESET_UPDATEFIELD};
                $data->timemodified = time();
                // Check if the activity already exist
                if($DB->record_exists(MOD_COMPLETIONRESET_ACTIVITIESTABLE,array('course'=>$formdata->course))){
                    $data->id = $formdata->id;
                    $DB->update_record(MOD_COMPLETIONRESET_ACTIVITIESTABLE,$data);
                }else{
                    // Add activity
                    $DB->insert_record(MOD_COMPLETIONRESET_ACTIVITIESTABLE,$data);
                }
            }
        }
    }
}else {
    //if ($SESSION->addusers) {
    //    $return = $url;
    //}
    $_POST = array();
    redirect($return);
}

//get our javascript all ready to go
$jsmodule = array(
    'name'     => 'mod_completionreset',
    'fullpath' => '/mod/completionreset/module.js',
    'requires' => array('io','json','button','array-extras')
);
// Data selectors/ Javascript
$opts =Array();
$opts['chosen']         = MOD_COMPLETIONRESET_CHOSEN;
$opts['unchosen']       = MOD_COMPLETIONRESET_UNCHOSEN;
$opts['updatefield']    = MOD_COMPLETIONRESET_UPDATEFIELD;

$data = new stdClass();

if (!$resetusers) {
    $allactivities = mod_completionreset_helper::get_all_activities($course);
    $chosendata=$allactivities->chosendata;
    $unchosendata=$allactivities->unchosendata;
    $sortorderarray=$allactivities->sortorderarray;

    // Data Selectors
    $opts['chosendata']     = $chosendata;
    $opts['unchosendata']   = $unchosendata;
    $opts['sortorder']      = implode(',',$sortorderarray);

    // Get selectors
    $chooser = $renderer->fetch_chooser($chosendata,$unchosendata);

    $rec = $DB->get_record(MOD_COMPLETIONRESET_ACTIVITIESTABLE,array('course'=>$course->id));
    if($rec){
        $data->id = $rec->id;
        $data->{MOD_COMPLETIONRESET_UPDATEFIELD}=$rec->activities;
    }
}else {
    // Get all users to add/remove
    $choosenusers = mod_completionreset_helper::choose_users_selectors($context,$reset->id);

    // Data Selectors
    $opts['chosendata']     = $choosenusers->selected;
    $opts['unchosendata']   = $choosenusers->availables;
    $opts['sortorder']      = implode(',',$choosenusers->sort);

    // Get selectors
    $chooser = $renderer->fetch_chooser($choosenusers->selected,$choosenusers->availables);
}//if_addusers

// Initialize javascript
$PAGE->requires->js_init_call('M.mod_completionreset.init', array($opts),false,$jsmodule);

$mform = new mod_completionreset_chooseform(null,array($context,$chooser,$resetusers));

$data->course=$course->id;
$mform->set_data($data);
echo $renderer->header_choose($resetusers);
$mform->display();
echo $renderer->footer();

