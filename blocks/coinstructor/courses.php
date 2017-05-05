<?php
// This file is part of ksl
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

require_once( '../../config.php');
require_once( 'lib/coinstructorlib.php');

// Variables!
$contextsystem  = context_system::instance();
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT);
$sort           = 'asc';
$url            = new moodle_url('/blocks/coinstructor/courses.php', array('sort' => $sort, 'page' => $page));
$out            = null;

// Startpage!
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($contextsystem);

// Breadcrumb!
$navbar = $PAGE->navbar->add(get_string('pluginname', 'block_coinstructor'), new moodle_url('/blocks/coinstructor/courses.php'), null);
$navbar->make_active();

// Capabilities!
require_capability('block/coinstructor:myaddinstance', $contextsystem);

$courses    = coinstructor::get_courses();
$path       = coinstructor::get_path($courses);
$count      = coinstructor::get_courses_count();
$out        = coinstructor::display_overview($courses, $path);

// Print Header!
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('courseoverview', 'block_coinstructor'));

echo $OUTPUT->paging_bar($count, $page, $perpage, $url);

echo $out;

echo $OUTPUT->paging_bar($count, $page, $perpage, $url);

// Print Footer!
echo $OUTPUT->footer();