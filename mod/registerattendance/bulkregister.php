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
 * Register attendance bulk register page
 *
 * @package    mod
 * @subpackage registerattendance
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID.

if (!$cm = get_coursemodule_from_id('registerattendance', $id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

// Check permissions.
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Load completion data.
$completion = new completion_info($course);

$registerattendance = new mod_registerattendance\registerattendance();

// Get the renderer for this plugin.
$output = $PAGE->get_renderer('mod_registerattendance');

// Prepare the renderables for the page and the page areas.
$page = new mod_registerattendance_bulkregister_page($cm);
$selector = new mod_registerattendance_bulkregister_selector($cm);

$registerattendance->set_bulkregister_references($page, $selector, $output, $course, $cm, $completion);

// Basic page init - set context and pagelayout.
$registerattendance->init_page();

$registerattendance->setup_bulkregister_page();
$registerattendance->display_bulkregister_page();
