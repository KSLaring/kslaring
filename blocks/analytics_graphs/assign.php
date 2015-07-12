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


require('../../config.php');
require('graph_submission.php');
require('javascriptfunctions.php');
require('lib.php');

$course = required_param('id', PARAM_INT);

$title = get_string('submissions_assign', 'block_analytics_graphs');
$submissionsgraph = new graph_submission($course, $title);


$students = block_analytics_graphs_get_students($course);
$numberofstudents = count($students);
if ($numberofstudents == 0) {
    error(get_string('no_students', 'block_analytics_graphs'));
}
$result = block_analytics_graphs_get_assign_submission($course, $students);
$numberoftasks = count($result);
if ($numberoftasks == 0) {
    error(get_string('no_graph', 'block_analytics_graphs'));
}
$submissionsgraphoptions = $submissionsgraph->create_graph($result, $students);

/* Discover groups and members */
$groupmembers = block_analytics_graphs_get_course_group_members($course);
$groupmembersjson = json_encode($groupmembers);

$studentsjson = json_encode($students);
$resultjson = json_encode($result);
$statisticsjson = $submissionsgraph->get_statistics();

$codename = "assign.php";
require('groupjavascript.php');