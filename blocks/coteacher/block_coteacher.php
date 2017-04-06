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

class block_coteacher extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_coteacher');
    }//init

    public function get_content() {
        try {
            // Title Block!
            $this->title = get_string('blocktitle', 'block_coteacher');

            if ($this->content !== null) {
                return $this->content;
            }

            // Add the content to the block!
            $this->content = new stdClass;
            $this->content->text = '';
            $this->content->footer = '';

            // Add course info to the block!
            require_once('lib/coteacherlib.php');
            $courses = coteacher::get_courses();
            $mycourses = coteacher::display_courses($courses);
            $amount = coteacher::get_courses_count();

            $this->content->text .= $mycourses;

            // Display the "show all" link if more than 20 results.
            if ($amount->count > 20) {
                $this->content->text .= "</br>";
                $url = new moodle_url('/blocks/coteacher/courses.php');
                $this->content->text .= "<div><a href=$url>" . get_string('showall', 'block_coteacher') . " </a> </div>";
            }

            return $this->content;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_content
}//block_coteacher

