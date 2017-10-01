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
 * Report Competence Manager - Company Structure - Course Report
 *
 * Description
 *
 * @package         report
 * @subpackage      manager
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    23/10/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once( 'courserptlib.php');

global $PAGE,$USER,$OUTPUT,$CFG;

// Params
$parentcat      = optional_param('parent',0,PARAM_INT);
$depth          = optional_param('depth',1,PARAM_INT);
$type           = optional_param('type','cat',PARAM_TEXT);
$parent         = null;


$json           = array();
$data           = array();
$categories     = null;
$courses        = null;


$context        = context_system::instance();
$url            = new moodle_url('/report/manager/course_report/categoriescourses.php');


$PAGE->set_context($context);
$PAGE->set_url($url);

// Check correct access
require_login();
require_sesskey();

echo $OUTPUT->header();

// Get data
$dblog = null;

switch ($type) {
    case 'cat':
        // Get categories
        $categories   = course_report::get_my_categories_by_depth($depth,$parentcat);

        // set data to send javascript
        $data       = array('categories' => array(),'parentcat' => null);
        if ($categories) {
            foreach ($categories as $id => $category) {
                if ($id != 0) {
                    $key = "'" . $category . "'#" . $id;
                }else {
                    $key = $id;
                }
                $data['categories'][$key] = $category;

            }
        }//if_lstcategories

        // Parent info
        $parent = new stdClass();
        $parent->id         = $parentcat;
        $parent->name       = ($parentcat ? course_report::get_category_name($parentcat) : '');
        $data['parentcat']  = $parent;

        break;
    case 'cou':
        // Get courses
        $courses = course_report::Get_CoursesList($parentcat);

        // set data to send javascript
        $data   = array('mycourses' => array());
        if ($courses) {
            foreach ($courses as $id => $course) {
                if ($id != 0) {
                    $key = "'" . $course . "'#" . $id;
                }else {
                    $key = $id;
                }
                $data['mycourses'][$key] = $course;

            }
        }//if_lstcategories

        break;
}

// Encode and send
$json[] = $data;
echo json_encode(array('results' => $json));

