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
 * Course Locations - Add Location
 *
 * @package             local
 * @subpackage          friadmin/course_locations
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 * @license             http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate        27/04/2015
 * @author              eFaktor     (fbv)
 *
 * @updateDate          16/06/2015
 * @author              eFaktor     (fbv)
 *
 * Description
 * Integrate into friadmin plugin
 *
 */

require_once('../../../config.php');
require_once('locationslib.php');
require_once('locations_form.php');

global $USER,$PAGE,$SITE,$OUTPUT,$CFG;

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
$url            = new moodle_url('/local/friadmin/course_locations/add_location.php');
$return_url     = new moodle_url('/local/friadmin/course_locations/index.php');
$context        = context_system::instance();
$edit_options   = null;
$myCompetence   = null;
$strTitle       = null;
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
$PAGE->navbar->add(get_string('plugin_course_locations','local_friadmin'),$return_url);
$PAGE->navbar->add(get_string('new_location','local_friadmin'),$url);
$PAGE->requires->js('/local/friadmin/course_locations/js/locations.js');

// Get locations connected with the competence
$myCompetence = CourseLocations::Get_MyCompetence($USER->id);

// Editor options
$edit_options   = array('maxfiles' => 0, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true, 'context' => $context);
// Prepare editor
$location_info = new stdClass();
$location_info->description            = '';
$location_info->descriptionformat      = FORMAT_HTML;
$location_info = file_prepare_standard_editor($location_info, 'description', $edit_options,$context, 'local', 'course_locations',0);

// Check if it is admin user
$IsAdmin = is_siteadmin($USER->id);

// Form
$form = new add_location_form(null,array($myCompetence,$IsAdmin,$edit_options));
if ($form->is_cancelled()) {
    setcookie('parentCounty',0);
    setcookie('parentMunicipality',0);
    setcookie('parentActivate',1);

    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    // Get data
    $dataForm = (Array)$data;

    // Get editor info
    $location_info->description_editor   = $dataForm['description_editor'];
    $location_info->description          = '';
    $location_info           = file_postupdate_standard_editor($location_info, 'description', $edit_options, $context, 'local', 'course_locations', 0);
    $dataForm['description'] = $location_info->description;

    // Add new location
    CourseLocations::Add_NewLocation($dataForm,$USER->id);

    setcookie('parentCounty',0);
    setcookie('parentMunicipality',0);
    setcookie('parentActivate',1);

    $_POST = array();
    redirect($url);
}//if_cancel

// Header
echo $OUTPUT->header();

if ($myCompetence && !$myCompetence->levelZero && !$IsAdmin) {
    echo $OUTPUT->heading(get_string('no_competence_add_location', 'local_friadmin'),4);
}else {
    $form->display();
}

// Footer
echo $OUTPUT->footer();