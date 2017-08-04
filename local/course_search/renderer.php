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
 * Render the course serach page.
 *
 * @package         local
 * @subpackage      course_search
 * @copyright       2017 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Render the course search page.
 *
 * @package         local
 * @subpackage      course_search
 * @copyright       2017 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_course_search_renderer extends plugin_renderer_base {

    /**
     * Display a forum post in the relevant context.
     *
     * @param \mod_forum\output\forum_post $post The post to display.
     * @return string
     */
    public function render_course_search_page(\local_course_search\output\courses $courses) {
        $data = $courses->export_for_template($this);
        return $this->render_from_template('local_course_search/course_search_page', $data);
    }
}
