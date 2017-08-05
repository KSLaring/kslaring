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
 * Contains class \local_tag\output\edit_group_tags_page
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tag\output;

use renderer_base;
use stdClass;

/**
 * Class to display the tag edit page.
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_group_tags_page extends \local_tag\output\output_base {

    /** @var string $title The accordion title */
    protected $title;

    /** @var object $tagsourcelist The not grouped tags collection */
    protected $tagsourcelist;

    /** @var object $grouptags The grouped tags collection */
    protected $grouptags;

    /** @var array $sections The interactive section - filter, buttons etc. */
    protected $sections;

    /** @var string $templatename The name of the connected mustache template */
    protected $templatename = 'local_tag/edit_group_tags_page';

    /**
     * Constructor
     *
     * Get the tags in the given collection which 2822relate to the given group tag.
     *
     * @param string $title   The accordion title
     */
    public function __construct($title) {
        $this->title = $title;
    }

    /**
     * Magic setter for the source list
     *
     * @param object $data The source list data
     */
    protected function set_tagsourcelist ($data) {
        $this->tagsourcelist = $data;
    }

    /**
     * Magic setter for the sections
     *
     * @param array $data The sections data
     */
    protected function set_sections ($data) {
        $this->sections = $data;
    }

    /**
     * Magic setter for the group tags
     *
     * @param object $data The group tags data
     */
    protected function set_grouptags ($data) {
        $this->grouptags = $data;
    }

    /**
     * Magic getter
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
        return (object) array(
                'title' => $this->title,
                'tagsourcelist' => $this->tagsourcelist,
                'sections' => $this->sections,
                'grouptags' => $this->grouptags
        );
    }
}
