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
 * Contains class \local_tag\output\tagsourcelist
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tag\output;

use renderer_base;
use stdClass;
use core_tag_area;

/**
 * Class to display the tagsourcelist with not grouped tags.
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tagsourcelist extends \local_tag\output\output_base {

    /** @var string $title The tag source list title */
    protected $title;

    /** @var array $tagsourcelist The tag group collection */
    protected $tagset = array();

    /** @var string $templatename The name of the connected mustache template */
    protected $templatename = 'local_tag/tagsourcelist';

    /**
     * Constructor
     *
     * @param string $title The tag source list title
     */
    public function __construct($title) {
        $this->title = $title;
    }

    /**
     * Magic getter for the template name.
     *
     * @return string
     */
    protected function get_templatename() {
        return $this->templatename;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $this->create_tagsourcelist_data();

        return (object) array(
                'title' => $this->title,
                'tagset' => $this->tagset
        );
    }

    /**
     * Create the tag source list data.
     *
     * Tag object with 'id' and 'name'.
     */
    protected function create_tagsourcelist_data() {
        $tagcollid = \core_tag_area::get_collection('core', 'course');
        $tagset = \local_tag\collection::get_course_tags_in_no_group($tagcollid, \local_tag\tag::get_meta_prefix());

        // Show the user given name in the rawname field instead of the Moodle internal used name.
        $tagset = array_map(function($tag) {
            $tag->name = $tag->rawname;
            unset($tag->rawname);
            return $tag;
        }, $tagset);

        $this->tagset = $tagset;
    }
}
