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
 * The friadmin reports index page - shows the course summary
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2017 eFaktor
 * @author          Nicolai A. Samuelsen
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( '../../../config.php');
require_once( 'forms/rpt_forms.php');
require_once( 'lib/categoryrptlib.php');
require_once($CFG->dirroot . '/lib/excellib.class.php');

require_login();

// Variables!
global $CFG,$PAGE,$OUTPUT;
$contextsystem      = context_system::instance();
$coursescoordinator = null;
$url                = new moodle_url('/local/friadmin/reports/coordinator.php');

// Start page
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($contextsystem);
$PAGE->requires->js('/local/friadmin/reports/js/report.js');

// Capabilities!
require_capability('local/friadmin:course_locations_manage', $contextsystem);

// Form!
$mform = new course_coordinator_form(null);

// Calls a function to get the javascript values to fill into the form based on the previous search criteria.
friadminrpt::get_javascript_values('course', 'category', null);

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot);
} else if ($fromform = $mform->get_data()) {
    // Get course with coordinators
    $coursescoordinator = friadminrpt::get_courses_with_coordinator($fromform);

    // Download file
    ob_end_clean();
    friadminrpt::download_participants_list_coordinator($coursescoordinator,$fromform);

    die;
}

// Print Header!
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('coordinatorheading', 'local_friadmin'));

$mform->display();

// Print Footer!
echo $OUTPUT->footer();
