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
 * Contains class \local_tag\output\accordiontaggroup
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
 * Class to display the tags in a tag meta group.
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class accordiontaggroup extends \local_tag\output\output_base {

    /** @var string $groupid The tag group id */
    protected $groupid;

    /** @var string $groupname The tag group name */
    protected $groupname;

    /** @var array $tagset The tag collection */
    protected $tagset;

    /** @var string $templatename The name of the connected mustache template */
    protected $templatename = 'local_tag/accordiontaggroup';

    /**
     * Constructor
     *
     * Get the tags in the given collection which relate to the given group tag.
     *
     * @param string $groupid   The tag group id
     * @param string $groupname The tag group name
     * @param array  $tags      The tags data
     */
    public function __construct($groupid, $groupname, $tags) {
        $this->groupid = $groupid;
        $this->groupname = $groupname;

        foreach ($tags as $onetag) {
            $this->tagset[$onetag->id] = (object) array(
                    'id' => $onetag->id, 'name' => $onetag->name);
        }
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
        // Transform the associative array into a normal array for mustache if not empty.
        $tagset = !empty($this->tagset) ? array_values($this->tagset) : null;
        return (object) array(
                'groupid' => $this->groupid,
                'groupname' => $this->groupname,
                'tagset' => $tagset
        );
    }
}
