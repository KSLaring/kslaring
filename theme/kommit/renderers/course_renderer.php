<?php
// This file is part of the custom Moodle elegance theme
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
 * Renderers to align Moodle's HTML with that expected by elegance
 *
 * @package    theme_elegance
 * @copyright  2014 Julian Ridden http://moodleman.net
 * @authors    Julian Ridden -  Bootstrap 3 work by Bas Brands, David Scotson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/course/renderer.php");

class theme_kommit_core_course_renderer extends core_course_renderer {

    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
        global $CFG, $OUTPUT;

        $content = '';
        $arrow = '';

        $content .= html_writer::start_tag('div', array('class' => 'panel panel-default coursebox'));
        $content .= html_writer::start_tag('div', array('class' => 'panel-heading'));

        // Course name.
        $coursename = $chelper->get_course_formatted_name($course);
        $content .=  html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
            $coursename, array('class' => $course->visible ? '' : 'dimmed'));


        $content .= html_writer::end_tag('div'); // End .panel-heading.
        $content .= html_writer::start_tag('div', array('class' => 'panel-body'));

        // This gets the course image or files
        $content .= $this->coursecat_coursebox_content($chelper, $course);

        if ($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_EXPANDED) {
            $icondirection = 'left';
            if ('ltr' === get_string('thisdirection', 'langconfig')) {
                $icondirection = 'right';
            }
            $btn = html_writer::tag('div', get_string('course') . ' ' . $arrow, array('class' => 'coursequicklink'));
            $content .= html_writer::link(new moodle_url('/course/view.php',
                array('id' => $course->id)), $btn, array('class' => 'coursebtn btn buttons'));
        }

        $content .= html_writer::end_tag('div'); // End .panel-body.
        $content .= html_writer::end_tag('div'); // End .panel.

        return $content;
    }


}
