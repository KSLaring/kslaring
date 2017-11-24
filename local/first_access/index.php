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
 * First access - Index
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

global $USER,$PAGE,$OUTPUT,$SITE;

// Params
$userId         = required_param('id',PARAM_INT);
$context        = context_system::instance();
$url            = new moodle_url('/local/first_access/index.php',array('id'=>$userId));
$urlCompetence  = new moodle_url('/user/profile/field/competence/competence.php',array('id' => $userId));
$urlUserProfile = new moodle_url('/local/first_access/first_access.php',array('id' => $userId));
$urlProfile     = $urlUserProfile;
$user_context   = context_user::instance($userId);

// Start page
$PAGE->set_url($url);
$PAGE->set_context($user_context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_pagelayout('standard');
$PAGE->requires->js(new moodle_url('/local/first_access/js/firstaccess.js'));

$stringman = get_string_manager();
$strings = $stringman->load_component_strings('local_first_access', 'en');
$PAGE->requires->strings_for_js(array_keys($strings), 'local_first_access');

$strings = $stringman->load_component_strings('local_first_access', 'no');
$PAGE->requires->strings_for_js(array_keys($strings), 'local_first_access');

require_login();
// Checking access
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('welcome_title','local_first_access',$SITE->shortname));

//Check if it only remains to update the competence profile
if (FirstAccess::has_completed_all_user_profile($userId) && FirstAccess::has_completed_all_extra_profile($userId)) {
    if (!FirstAccess::has_completed_competence_profile($userId)) {
        $urlProfile = $urlCompetence;
    }//if_CompletedCompetenceProfile

    redirect($urlProfile);
}

//echo html_writer::start_div();
//    echo "</br>";
//    echo get_string('welcome_message','local_first_access');
//    echo "</br></br>";

//    echo html_writer::start_div('buttons');
//        echo '<a href="' . $urlProfile . '">';
//            echo '<button id="complete">' . get_string('welcome_btn','local_first_access') . '</button>';
//        echo '</a>';
//    echo html_writer::end_div();//buttons
//echo html_writer::end_div();

echo $OUTPUT->footer();

