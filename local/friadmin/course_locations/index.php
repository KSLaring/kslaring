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
 * Course Locations - Index
 *
 * @package         local
 * @subpackage      friadmin/course_locations
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    27/04/2015
 * @author          eFaktor     (fbv)
 *
 * @updateDate      16/06/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Integrate into Friadmin Plugin
 *
 */
require_once('../../../config.php');
require_once('locationslib.php');
require_once('locations_form.php');

global $USER,$PAGE,$SITE,$OUTPUT,$CFG,$SESSION;

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}

// Params
$url            = new moodle_url('/local/friadmin/course_locations/index.php');
$url_view       = new moodle_url('/local/friadmin/course_locations/locations.php');
$context        = context_system::instance();
$myCompetence   = null;
$IsAdmin        = false;

/**
 * @updateDate  22/06/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Check if the user is super user
 */
if (!has_capability('local/friadmin:course_locations_manage',$context)) {
    if (!local_friadmin_helper::CheckCapabilityFriAdmin()) {
        print_error('nopermissions', 'error', '', 'block/frikomport:view');
    }//if_superuser
}

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('plugin_course_locations','local_friadmin'));
$PAGE->navbar->add(get_string('lst_locations','local_friadmin'),$url);

if (isset($SESSION->county)) {
    $SESSION->county = null;
}//county

if (isset($SESSION->muni)) {
    $SESSION->muni = null;
}

if (isset($SESSION->act)) {
    $SESSION->act = null;
}

// Clean cookies
setcookie('dir','ASC');
setcookie('field','');

// Check if it´s admin
$IsAdmin = is_siteadmin($USER->id);

// Get locations connected with the competence
$myCompetence = CourseLocations::Get_MyCompetence($USER->id);

// Form
$form = new locations_search_form(null,array($myCompetence,$IsAdmin));
if($data = $form->get_data()) {
    // Get data
    $dataForm = (Array)$data;

    // Get filter - Search criteria
    $SESSION->county   = $dataForm[COURSE_LOCATION_COUNTY];
    if (isset($dataForm[COURSE_LOCATION_MUNICIPALITY]) && $dataForm[COURSE_LOCATION_MUNICIPALITY]) {
        $SESSION->muni     = $dataForm[COURSE_LOCATION_MUNICIPALITY];
    }else {
        $SESSION->muni = 0;
    }


    if (isset($dataForm['activate']) && ($dataForm['activate'])) {
        $SESSION->act = 1;
    }else {
        $SESSION->act = 0;
    }//if_checkbox_Activate

    redirect($url_view);
}//if_cancel

// Header
echo $OUTPUT->header();

// Table with locations
echo $OUTPUT->heading(get_string('filter','local_friadmin'));

if (!$myCompetence) {
    $form->display();
}else if (!$myCompetence->levelZero && !$IsAdmin) {
    echo $OUTPUT->heading(get_string('no_competence_profile', 'local_friadmin'),4);
}else {
    $form->display();
}

// Footer
echo $OUTPUT->footer();