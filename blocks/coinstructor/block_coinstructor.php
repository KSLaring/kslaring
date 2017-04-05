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

defined('MOODLE_INTERNAL') || die();

class block_coinstructor extends block_base {
    function init() {
        // TODO -> declare visibility.
        $this->title = get_string('pluginname', 'block_coinstructor');
    }//init

    function get_content() {
        // TODO -> declare visibility.

        try {
            // Title Block!
            $this->title = get_string('blocktitle', 'block_coinstructor');

            if ($this->content !== NULL) {
                return $this->content;
            }

            // Add the content to the block.
            $this->content = new stdClass;
            $this->content->text = '';
            $this->content->footer = '';

            // Add course info to the block.
            require_once('lib/coinstructorlib.php');
            $courses = coinstructor::get_courses();
            $mycourses = coinstructor::display_courses($courses);
            $amount = coinstructor::get_courses_count();

            $this->content->text .= $mycourses;

            $amount->count = 24;
            // Display the "show all" link if more than 20 results.
            if ($amount->count > 20) {
                $this->content->text .= "</br>";
                $url = new moodle_url('/blocks/coinstructor/courses.php');
                $this->content->text .= "<div><a href=$url>" . get_string('showall', 'block_coteacher') . " </a> </div>";
            }

            return $this->content;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_content
}//block_coinstructor