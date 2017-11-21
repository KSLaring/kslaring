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
 * Course Locations - Locations List
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
 * Integrate into Friadmin plugin
 *
 */
require_once('../../../config.php');
require_once('locationslib.php');

global $USER,$PAGE,$SITE,$OUTPUT,$CFG,$SESSION;

require_login();
// Checking access
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

// Params
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT);        // how many per page
$sort           = optional_param('sort','ASC',PARAM_TEXT);
$act            = optional_param('act',0,PARAM_INT);
$locationId     = optional_param('id',0,PARAM_INT);
$format         = optional_param('format', 0, PARAM_INT);
$url            = new moodle_url('/local/friadmin/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage));
$return_url     = new moodle_url('/local/friadmin/course_locations/index.php');
$context        = context_system::instance();
$filter         = array();
$county         = null;
$locations      = null;
$totalLocations = 0;
$fieldSort      = null;

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
$PAGE->set_pagelayout('report');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('plugin_course_locations','local_friadmin'));
$PAGE->navbar->add(get_string('lst_locations','local_friadmin'), $return_url);
$PAGE->requires->js('/local/friadmin/course_locations/js/locations.js');

// Filter
$filter['county']   = $SESSION->county;
$filter['muni']     = $SESSION->muni;
$filter['activate'] = $SESSION->act;

// Activate or deactivate location
if ($act) {
    // Change status location
    CourseLocations::ChangeStatus_Location($locationId);
    $url            = new moodle_url('/local/friadmin/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage,'sort' => $sort,'act' => 0));
    redirect($url);
}else {
    // Get total locations
    $totalLocations = CourseLocations::Get_TotalLocationsList($filter);

    if (($totalLocations <= $page*$perpage) && $page) {
        $page --;
    }

    // Sort order
    if (isset($_COOKIE['dir'])) {
        $sort = $_COOKIE['dir'];
    }else {
        $sort = 'ASC';
    }//if_dir

    // Sort by field
    if (isset($_COOKIE['field'])) {
        $fieldSort = $_COOKIE['field'];
    }else {
        $fieldSort = '';
    }//if_dir

    // Download in excel
    if ($format == 1) {
        ob_end_clean();

        if ($SESSION->muni) {
            CourseLocations::download_all_locations_data($SESSION->county, $SESSION->muni);
        } else {
            CourseLocations::download_all_locations_data($SESSION->county, null);
        }

        die;
    }else if ($format == 2) {
        ob_end_clean();

        CourseLocations::download_one_location_data($locationId);

        die;
    }

    // Get locations
    $locations = CourseLocations::Get_LocationsList($filter,$page*$perpage,$perpage,$sort,$fieldSort);

    // Print locations table
    // County name
    $county = CourseLocations::Get_CompanyLevelName($filter['county']);
    // Locations table
    $out = CourseLocations::Print_LocationsList($county,$locations,$totalLocations,$page,$perpage,$sort,$fieldSort);

    /* Header   */
    echo $OUTPUT->header();

    /* Print Locations Table    */
    echo $out;

    /* Footer   */
    echo $OUTPUT->footer();
}//if_Act
