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
 * Main entry point for export
 *
 * @package   local_lessonexport
 * @copyright 2017 Adam King, SHEilds eLearning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
global $CFG, $DB, $USER, $PAGE;
require_once($CFG->dirroot.'/local/lessonexport/lib.php');

$cmid = required_param('id', PARAM_INT);
$exporttype = required_param('type', PARAM_ALPHA);
$cm = get_coursemodule_from_id('lesson', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$lesson = $DB->get_record('lesson', array('id' => $cm->instance), '*', MUST_EXIST);
$url = new moodle_url('/local/lessonexport/export.php', array('id' => $cm->id, 'type' => $exporttype));

$PAGE->set_url($url);
require_login($course, false, $cm);

$export = new local_lessonexport($cm, $lesson, $exporttype);
$export->check_access();

if ($exporttype == "pdf") {
    $export->export(true);
} else {
    $export->export();
}
