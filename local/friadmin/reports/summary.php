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
 * Friadmin - Category reports (Summary report)
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

// Params!
require_login();

// Variables!
$contextsystem  = context_system::instance();
$CFG->wwwroot;

// Startpage!
$url = new moodle_url('/local/friadmin/reports/summary.php');

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($contextsystem);

// Capabilities!
if (!has_capability('local/friadmin:course_locations_manage',$contextsystem)) {
    if (!local_friadmin_helper::CheckCapabilityFriAdmin()) {
        print_error('nopermissions', 'error', '', 'block/frikomport:view');
    }
}

// Form!
$mform = new summary_form(null);
$noresults = null;

$cat = "/90";
$path = '/21/55/56/90';

$index = strpos($path,$cat);
if ($index) {
    $path = substr($path,$index);
    echo $path;
}
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot);
} else if ($fromform = $mform->get_data()) {
    $coursesdata = friadminrpt::get_course_summary_data($fromform);

    // Download file
    ob_end_clean();
    friadminrpt::download_participants_list($coursesdata, $fromform);

    die;
}

// Print Header!
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('summaryheading', 'local_friadmin'));

$mform->display();

// Print Footer!
echo $OUTPUT->footer();
