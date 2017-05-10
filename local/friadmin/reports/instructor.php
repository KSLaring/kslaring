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

// Params!
require_login();

// Variables!
$contextsystem  = context_system::instance();
$CFG->wwwroot;

// Startpage!
$url = new moodle_url('/local/friadmin/reports/instructor.php');

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($contextsystem);
$PAGE->requires->js('/local/friadmin/reports/js/report.js');

// Capabilities!
require_capability('local/friadmin:course_locations_manage', $contextsystem);

// Form!
$mform = new course_instructor_form(null);

// Calls a function to get the javascript values to fill into the form based on the previous search criteria.
friadminrpt::get_javascript_values('course', 'category', null);

if ($mform->is_cancelled()) {

} else if ($fromform = $mform->get_data()) {
    $instructors = friadminrpt::get_course_instructors(
        $fromform->course,
        $fromform->category,
        $fromform->userfullname,
        $fromform->username,
        $fromform->useremail,
        $fromform->userworkplace,
        $fromform->userjobrole);

    echo $instructors;
    $instructorsinfo = friadminrpt::get_course_instructor_data($instructors);

    ob_end_clean();
    friadminrpt::download_participants_list_instructor($instructorsinfo);

    die;
}

// Print Header!
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('instructorheading', 'local_friadmin'));

$mform->display();

// Print Footer!
echo $OUTPUT->footer();
