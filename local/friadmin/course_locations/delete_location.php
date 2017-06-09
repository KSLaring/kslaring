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
 * Course Locations - Delete Location
 *
 * @package             local
 * @subpackage          friadmin/course_locations
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 * @license             http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate        04/05/2015
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

require_login();

/* PARAMS   */
$locationId     = optional_param('id',0,PARAM_INT);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT);        // how many per page
$sort           = optional_param('sort','ASC',PARAM_TEXT);
$confirmed      = optional_param('confirm', false, PARAM_BOOL);
$url            = new moodle_url('/local/friadmin/course_locations/delete_location.php');
$return         = new moodle_url('/local/friadmin/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage, 'sort' => $sort));
$confirm_url    = null;
$index_url      = new moodle_url('/local/friadmin/course_locations/index.php');
$context        = context_system::instance();

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

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('plugin_course_locations','local_friadmin'),$index_url);
$PAGE->navbar->add(get_string('lst_locations','local_friadmin'),$return);
$PAGE->navbar->add(get_string('del_location','local_friadmin'));


/* Header   */
echo $OUTPUT->header();

if ($confirmed) {
    /* Check if the location can be removed */
    if (CourseLocations::Has_CoursesConnected($locationId)) {
        /* Not Remove */
        echo $OUTPUT->notification(get_string('error_deleting_location','local_friadmin'), 'notifysuccess');
        echo $OUTPUT->continue_button($return);
    }else {
        /* Deleted  */
        if (CourseLocations::Delete_Location($locationId)) {
            echo $OUTPUT->notification(get_string('deleted_location','local_friadmin'), 'notifysuccess');
            echo $OUTPUT->continue_button($return);
        }
    }
}else {
    /* First Confirm    */
    $location   = CourseLocations::Get_LocationDetail($locationId);
    $a = new stdClass();
    $a->muni = $location->muni;
    $a->name = $location->name;
    $a->address = $location->street . ", " . $location->postcode . ' ' . $location->city;
    $confirm_url    = new moodle_url('/local/friadmin/course_locations/delete_location.php',array('page' => $page, 'perpage' => $perpage, 'confirm' => true,'id' => $locationId));
    echo $OUTPUT->confirm(get_string('delete_location_are_you_sure','local_friadmin',$a),$confirm_url,$return);
}//if_confirmed

/* Footer   */
echo $OUTPUT->footer();