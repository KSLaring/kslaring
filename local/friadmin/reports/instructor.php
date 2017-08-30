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
 * Friadmin - Category reports (Instructor report)
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

require_once( '../../../config.php');
require_once( 'forms/rpt_forms.php');
require_once( 'lib/categoryrptlib.php');
require_once($CFG->dirroot . '/lib/excellib.class.php');

require_login();

global $CFG,$PAGE,$OUTPUT,$USER;

// Params
$parent             = optional_param('parentcat', 0,PARAM_INT);
$contextsystem      = context_system::instance();
$coursescoordinator = null;
$mycategories       = null;
$instructorsinfo    = null;
$url                = new moodle_url('/local/friadmin/reports/instructor.php');

//Start page
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($contextsystem);

// Capabilities!
if (!has_capability('local/friadmin:course_locations_manage',$contextsystem)) {
    if (!local_friadmin_helper::CheckCapabilityFriAdmin()) {
        print_error('nopermissions', 'error', '', 'block/frikomport:view');
    }
}

// Get my categories
$mycategories = friadminrpt::get_my_categories_by_context($USER->id);

// Form!
$mform = new course_instructor_form(null,array($mycategories,$parent));

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot);
} else if ($fromform = $mform->get_data()) {
    // Get instructors
    $instructors = friadminrpt::get_course_instructors($fromform,$mycategories->totalpath);

    // Get courses connected with the instructors
    if ($instructors) {
        $instructorsinfo = friadminrpt::get_course_instructor_data($instructors, $fromform->course, $fromform->parentcat,$mycategories->totalpath);
    }

    // Download file
    ob_end_clean();
    friadminrpt::download_participants_list_instructor($instructorsinfo, $fromform);

    die;
}

// Print Header!
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('instructorheading', 'local_friadmin'));

$mform->display();

// Javascript to et categories and courses
friadminrpt::ini_data_reports('parent','category','course');

// Print Footer!
echo $OUTPUT->footer();
