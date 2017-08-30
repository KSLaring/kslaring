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
 * Friadmin - Category reports (Courses search)
 *
 * @package         local/friadmin
 * @subpackage      reports
 * @copyright       2012        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    01/04/2017  (nas)
 * @author          eFaktor
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('lib/categoryrptlib.php');

global $PAGE,$USER;

// Params
$category = required_param('category', PARAM_INT);
$categories     = null;
$json           = array();
$data           = null;
$info           = null;
$lstcourses     = null;
$course         = null;
$context        = context_system::instance();
$url            = new moodle_url('/local/friadmin/reports/courses.php');

// Set page
$PAGE->set_context($context);
$PAGE->set_url($url);

// Access
require_login();
require_sesskey();

// Categories connected with the user
//$mycategories   = friadminrpt::get_my_categories_by_context($USER->id);

// Get subcategories
/**
if ($mycategories) {
    if ($mycategories->total) {
        $aux = implode(',',$mycategories->total);
        foreach ($aux as $cat) {
            $category   = "/" . $cat . "/";
            if ($categories) {
                $categories .= ',';
            }
            $categories = friadminrpt::get_subcategories_by_cat($category);
        }

        if ($categories) {
            $categories .= ',';
        }
        $categories .= $mycategories->total;
    }
} **/

$category   = "/" . $cat . "/";
if ($categories) {
    $categories .= ',';
}
$categories = friadminrpt::get_subcategories_by_cat($category);

global $CFG;
$dblog = "CATEGORIES --> " . $categories . "\n";
error_log($dblog, 3, $CFG->dataroot . "/CATEGORIES.log");

// Get courses connected with
$courselst = friadminrpt::get_courses_by_cat($categories);

$data   = array('mycourses' => array());
$courses = array();

// Extract data to send
if ($courselst) {
    foreach ($courselst as $id => $course) {
        $info       = new stdClass();
        $info->id   = $id;
        $info->name = $course;
        
        $data['mycourses'][$info->id] = $info;
    }
}//if_courses_lst

// Send data
$json[] = $data;
echo json_encode(array('results' => $json));