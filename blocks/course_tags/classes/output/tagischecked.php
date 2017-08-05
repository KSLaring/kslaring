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
 * Contains class block_course_tags\output\tagischecked
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_tags\output;

use context_system;
use core_tag_tag;
use context_course;

/**
 * Class to display/toggle tag ischecked attribute
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tagischecked extends \core\output\inplace_editable {

    /**
     * Constructor.
     *
     * @param \stdClass|core_tag_tag $tag
     */
    public function __construct($tag, $courseid) {
        $editable = has_capability('block/course_tags:edit', \context_course::instance($courseid));
        $value = (int)(bool)$tag->ischecked;

        parent::__construct('block_course_tags', 'tagischecked-' . $courseid, $tag->id, $editable, $value, $value);
        $this->set_type_toggle();
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     *
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        if ($this->value) {
            $this->edithint = get_string('settypedefault', 'core_tag');
            $this->displayvalue = $output->pix_icon('i/checked', $this->edithint);
        } else {
            $this->edithint = get_string('settypestandard', 'core_tag');
            $this->displayvalue = $output->pix_icon('i/unchecked', $this->edithint);
        }

        return parent::export_for_template($output);
    }

    /**
     * Updates the value in database and returns itself, called from inplace_editable callback
     *
     * @param string $itemtype The itemtype coded with the course id "tagischecked-2"
     * @param int    $itemid
     * @param mixed  $newvalue
     *
     * @return \self
     */
    public static function update($itemtype, $itemid, $newvalue) {
        $courseid = (int)explode('-', $itemtype)[1];
        require_capability('block/course_tags:edit', \context_course::instance($courseid));
        $tag = core_tag_tag::get($itemid, '*', MUST_EXIST);
        // Get the course id from the itemtype, the id is the number behind the dash: "tagischecked-2".
        $tag->ischecked = clean_param($newvalue, PARAM_BOOL);

        if ($tag->ischecked) {
            core_tag_tag::add_item_tag('core', 'course', $courseid, context_course::instance($courseid), $tag->name);
        } else {
            core_tag_tag::remove_item_tag('core', 'course', $courseid, $tag->name);
        }

        return new self($tag, $courseid);
    }
}
