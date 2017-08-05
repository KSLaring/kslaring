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
 * Course tags block renderer.
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Course tags block renderer class.
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_tags_renderer extends plugin_renderer_base {

    /**
     * Display tag item
     *
     * @param stdClass $tag
     *
     * @return string
     */
    public function tag_item_checked($tag, $courseid) {
        $tagoutput = new \block_course_tags\output\tagischecked($tag, $courseid);

        return $tagoutput->render($this);
    }

    /**
     * Rebder a taggroup
     *
     * @param array $taggroup The tags in a group
     *
     * @return string
     */
    public function taggroup($taggroup) {
        return $this->render_from_template('block_course_tags/taggroup', $taggroup);
    }

    /**
     * Display tag item
     *
     * @param object $course    The course object
     * @param int    $tagcollid The tag collection id
     * @param int    $ctx       The course context id
     *
     * @return string
     */
    public function settags_page($course, $tagcollid, $ctx, $showtagsonly = false) {
        $settagspage = new \block_course_tags\output\settagspage($course, $tagcollid, $ctx);
        $settagspage->show_tags_only($showtagsonly);

        return $this->render_from_template('block_course_tags/settagspage', $settagspage->export_for_template($this));
    }
}
