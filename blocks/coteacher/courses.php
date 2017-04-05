<?php

require_once( '../../config.php');
require_once( 'lib/coteacherlib.php');

// Variables!
$contextsystem  = context_system::instance();
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT);
$sort           = 'asc';
$url            = new moodle_url('/blocks/coteacher/courses.php', array('sort' => $sort, 'page' => $page));
$out            = null;

// Startpage!
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($contextsystem);

// Breadcrumb!
$navbar = $PAGE->navbar->add(get_string('pluginname', 'block_coteacher'), new moodle_url('/block/coteacher/courses.php'), null);
$navbar->make_active();

// Capabilities!
require_capability('block/coteacher:myaddinstance', $contextsystem);

$courses    = coteacher::get_courses();
$count      = coteacher::get_courses_count();
$mycount    = $count->count;
$out        = coteacher::display_overview($courses);

// Print Header!
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('courseoverview', 'block_coteacher'));

echo $OUTPUT->paging_bar($mycount, $page, $perpage, $url);

echo $out;

echo $OUTPUT->paging_bar($mycount, $page, $perpage, $url);

// Print Footer!
echo $OUTPUT->footer();