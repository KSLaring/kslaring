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
 * First access - First access
 *
 * @package
 * @subpackage
 * @copyright       2012    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    10/11/2014
 * @author          eFaktor     (fbv)
 *
 * @updateDate      12/06/2017
 * @author          eFaktor     (fbv)
 */
require_once('../../config.php');
require_once('locallib.php');
require_once('first_access_form.php');

global $PAGE, $CFG,$OUTPUT,$USER,$SITE;

// Params
$userId         = $USER->id;
$context        = context_system::instance();
$url            = new moodle_url('/local/first_access/first_access.php');
$user_context   = context_user::instance($userId);
$redirect       = new moodle_url('/user/profile.php',array('id'=>$userId));
$urlcourses     = new moodle_url('/local/course_search/search.php');

// Start Page
$PAGE->set_url($url);
$PAGE->set_context($user_context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->requires->js(new moodle_url('/local/first_access/js/firstaccess.js'));

$stringman = get_string_manager();
$strings = $stringman->load_component_strings('local_first_access', 'en');
$PAGE->requires->strings_for_js(array_keys($strings), 'local_first_access');

$strings = $stringman->load_component_strings('local_first_access', 'no');
$PAGE->requires->strings_for_js(array_keys($strings), 'local_first_access');

// Checking access
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}

// Show form
$form = new first_access_form(null,$userId);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($CFG->wwwroot);
}else if ($data = $form->get_data()){

    // Generic data
    FirstAccess::update_user_profile($data);
    // Save custom profile fields data.
    profile_save_data($data);

    // Check if it still remains to update competence profile
    //if (!FirstAccess::has_completed_competence_profile($data->id)) {
    //    $redirect = new moodle_url('/user/profile/field/competence/competence.php',array('id' => $data->id));
    //}//if_CompletedCompetenceProfile

    $user = get_complete_user_data('id',$data->id);
    complete_user_login($user);

    if (isset($data->submitbutton)) {
        if (($data->submitbutton)) {
            redirect($urlcourses);
        }
    }else if (isset($data->submitbutton2)) {
        if (($data->submitbutton2)) {
            $redirect = new moodle_url('/user/profile/field/competence/competence.php',array('id' => $data->id));
            redirect($redirect);
        }
    }else {
        redirect($redirect);
    }
}//if_else

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('welcome_title','local_first_access',$SITE->shortname));

echo html_writer::start_div();
    echo "<h5>" . get_string('welcome_message','local_first_access') . "</h5></br>";
echo html_writer::end_div();

$form->display();

echo $OUTPUT->footer();