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
 * Register attendance fakeregister
 * Use this page to toggle the module completion state for the module instance
 * given in the query parameter »id« and the user user given in the query parameter »userid«.
 *
 * @package    mod
 * @subpackage registerattendance
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/completionlib.php');

$id = required_param('id', PARAM_INT);    // Course Module ID
$userid = optional_param('userid', 0, PARAM_INT);

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
$canregister = has_capability('mod/registerattendance:registerattendance', $context);

// Print header.
$page = get_string('completionprogressdetails', 'mod_registerattendance');
$title = format_string($course->fullname) . ': ' . $page;

$PAGE->set_url('/mod/registerattendance/fakeregister.php', array('id' => $id));
$PAGE->set_title(get_string('course') . ': ' . $course->fullname);
$PAGE->set_heading($title);
$PAGE->set_cm($cm);

echo $OUTPUT->header();

// Show the content.
echo $OUTPUT->heading(format_string($cm->name) . ' - fakeregister', 2);

if ($canregister) {
    if ($userid && $completion->is_enabled($cm) && $cm->completion != COMPLETION_TRACKING_MANUAL) {
        $completiondata = $completion->get_data($cm, false, $userid);

        $completionstate = COMPLETION_COMPLETE;
        $strcompletionstate = 'COMPLETION_COMPLETE';
        if ($completiondata->completionstate == COMPLETION_COMPLETE) {
            $completionstate = COMPLETION_INCOMPLETE;
            $strcompletionstate = 'COMPLETION_INCOMPLETE';
        }

        $cache = cache::make('mod_registerattendance', 'registerattendance');
        $result = $cache->set($cm->id . '_' . $userid, $completionstate);
        $completion->update_state($cm, COMPLETION_UNKNOWN, $userid);

        echo 'State changed to ' . $strcompletionstate . ' for user ' . $userid;
        //echo 'Completion state changed.';
    } else {
        echo 'No userid given or wrong completion.';
    }
} else {
    echo 'Cannot register.';
}

// Display the footer.
echo $OUTPUT->footer();
