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
 * Course tags block.
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_tags extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_course_tags');
    }

    function has_config() {
        return true;
    }

    public function instance_allow_config() {
        return true;
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function applicable_formats() {
        return array('all' => true);
    }

    public function specialization() {
        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_course_tags');
        } else {
            $this->title = $this->config->title;
        }
    }

    public function get_content() {
        global $CFG, $COURSE, $USER, $SCRIPT, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($CFG->usetags)) {
            if ($this->page->user_is_editing()) {
                $this->content->text = get_string('disabledtags', 'block_course_tags');
            }

            return $this->content;
        }

        if (empty($this->instance)) {
            return $this->content;
        }

        // Set the context to the course context.
        $ctx = 0;
        $parentcontext = context::instance_by_id($this->instance->parentcontextid);
        if ($parentcontext->contextlevel > CONTEXT_COURSE) {
            $coursecontext = $parentcontext->get_course_context();
            $ctx = $coursecontext->id;
        } else if ($parentcontext->contextlevel == CONTEXT_COURSE) {
            $coursecontext = $parentcontext;
        } else {
            $coursecontext = context_course::instance($COURSE->id);
        }
        if ($parentcontext->contextlevel != CONTEXT_SYSTEM) {
            $ctx = $parentcontext->id;
        }

        // Check if the user has the capability.
        if (!has_capability('block/course_tags:view', $coursecontext)) {
            return $this->content;
        }

        // Get the defined course tag collection.
        $tagcollid = core_tag_area::get_collection('core', 'course');

        // Get the group tags.
        $value = get_config('', 'block_course_tags_groupsortorder');
        $order = explode(' ', $value);
        $grouptags = \block_course_tags\util::get_meta_tags($tagcollid, \local_tag\tag::get_meta_group_prefix(), $order);

        // Get all tags related to the group tags and list them.
        foreach ($grouptags as $id => $grouptag) {
            $tagmetagroup = new \local_tag\output\tagmetagroup($tagcollid, $grouptag, $ctx);
            $this->content->text .= $OUTPUT->render_from_template('local_tag/tagmetagroup',
                $tagmetagroup->export_for_template($OUTPUT));
        }

        // Show the link to the course tags settings page.
        if (has_capability('block/course_tags:edit', $coursecontext)) {
            $urlparams = array(
                'id' => $COURSE->id,
                'tagcollid' => $tagcollid,
                'ctx' => $ctx
            );
            $url = new moodle_url('/blocks/course_tags/settags.php', $urlparams);
            $strlink = get_string('settagslinktext', 'block_course_tags');
            $this->content->footer = html_writer::link($url, $strlink);
        }

        return $this->content;
    }
}
