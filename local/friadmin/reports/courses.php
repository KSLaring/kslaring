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
//
// * Friadmin(local) - courses
// *
// * @package         local                                                 !
// * @subpackage      friadmin/reports                                      !
// * @copyright       2017        eFaktor {@link http://www.efaktor.no}     !
// *                                                                        !
// * @updateDate      20/01/2017                                            !
// * @author          eFaktor     (nas)                                     !

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('lib/categoryrptlib.php');

// Params!
$category = required_param('category', PARAM_INT);
$json = array();
$data = array();
$info = null;

$context = context_system::instance();
$url = new moodle_url('/local/friadmin/reports/courses.php');
$PAGE->set_context($context);
$PAGE->set_url($url);

// Access!
require_login();
require_sesskey();

$courselst = friadminrpt::get_courses_js($category);

$data   = array('courses' => array());
$courses = array();

if ($courselst) {
    foreach ($courselst as $infocourse) {
        $info = new stdClass();
        $info->id = $infocourse->id;
        $info->name = $infocourse->fullname;

        $courses[$info->id] = $info;
    }
}

$data['courses'] = $courses;
$json[] = $data;
echo json_encode(array('results' => $json));