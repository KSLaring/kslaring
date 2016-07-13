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
 * eLearning Frikomport Format - Format
 *
 * Description
 *
 * @package             course
 * @subpackage          format/elearning_frikomport
 * @copyright           2010 eFaktor
 *
 * @creationDate        20/04/2015
 * @author              eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

// Horrible backwards compatible parameter aliasing..
if ($topic = optional_param('topic', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing..

$context = context_course::instance($course->id);

if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// make sure all sections are created
$course = course_get_format($course)->get_course();
course_create_sections_if_missing($course, range(0, $course->numsections));

$renderer = $PAGE->get_renderer('format_elearning_frikomport');

/**
 * @updateDate  02/03/2016
 * @author      eFaktor     (fbv)
 *
 * Description
 * Check the option one section by page
 */
if ($course->coursedisplay && $course->numsections) {
    if ($displaysection) {
        $renderer->print_single_section_page($course, null, null, null, null, $displaysection);
    }else {
        $renderer->print_single_section_page($course, null, null, null, null, 1);
    }
}else {
    $renderer->print_multiple_section_page($course, null, null, null, null);
}

//if (!empty($displaysection)) {
//    $renderer->print_single_section_page($course, null, null, null, null, $displaysection);
//} else {
//    $renderer->print_multiple_section_page($course, null, null, null, null);
//}

// Include course format js module
$PAGE->requires->js('/course/format/elearning_frikomport/format.js');
