<?php

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
$navbar = $PAGE->navbar->add(get_string('pluginname', 'block_coinstructor'), new moodle_url('/block/coinstructor/courses.php'), null);
$navbar->make_active();

// Capabilities!
require_capability('block/coinstructor:myaddinstance', $contextsystem);

$courses    = coinstructor::get_courses();
$count      = coinstructor::get_courses_count();
$mycount    = $count->count;
$out        = coinstructor::display_overview($courses);

// Print Header!
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('courseoverview', 'block_coinstructor'));

echo $OUTPUT->paging_bar($mycount, $page, $perpage, $url);

echo $out;

echo $OUTPUT->paging_bar($mycount, $page, $perpage, $url);

// Print Footer!
echo $OUTPUT->footer();