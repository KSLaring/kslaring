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
 * The course search
 *
 * @package         local
 * @subpackage      course_search
 * @copyright       2017 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

defined('MOODLE_INTERNAL') || die;

// Set up the page.
/* @var \moodle_page $PAGE The Moodle page object */
$PAGE->set_url('/local/course_search/search.php');
$PAGE->set_course($SITE);

$PAGE->set_pagetype('course-search');
$PAGE->set_pagelayout('base');
$PAGE->set_title(get_string('title', 'local_course_search'));

// Require needed JavaScript.
$langstringsfriadmin = array(
    'course_name',
    'course_date',
    'course_seats',
    'course_deadline',
    'course_municipality',
    'course_location',
);
$langstrings = array(
    'sortby',
    'searchtext',
);
$PAGE->requires->strings_for_js($langstringsfriadmin, 'local_friadmin');
$PAGE->requires->strings_for_js($langstrings, 'local_course_search');
$PAGE->requires->js_call_amd('local_course_search/coursesearch', 'init', array(current_language()));

/* @var \local_course_search_renderer $pluginrenderer The course search plugin renderer. */
$pluginrenderer = $PAGE->get_renderer('local_course_search');
$courses = new \local_course_search\output\courses();

/* @var \core_renderer $OUTPUT The Moodle core renderer */
echo $OUTPUT->header();

echo $pluginrenderer->render_course_search_page($courses);

echo $OUTPUT->footer();
