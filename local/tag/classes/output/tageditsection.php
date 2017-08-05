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
 * Contains class \local_tag\output\tageditsection
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
 * Class to display the interactive sections with input fields, buttons etc.
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tageditsection extends \local_tag\output\output_base {

    /** @var array $sections The edit section collection */
    protected $sections = array();

    /** @var string $templatename The name of the connected mustache template */
    protected $templatename = 'local_tag/tageditsection';

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
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        $this->create_sections_data($output);

        return $this->sections;
    }

    /**
     * Create the tag source list data.
     *
     * @param renderer_base $output The core renderer
     */
    protected function create_sections_data(renderer_base $output) {
        $this->sections = array(
                (object) array('type' => 'filter', 'desc' => null,
                        'label' => get_string('editgrouptags_filterlist', 'local_tag')),
                (object) array('type' => 'add', 'desc' => get_string('inputstandardtags', 'tag'),
                        'label' => get_string('add')),
        );
    }
}
