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
 * Contains class \local_tag\output\tagmetagroup
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tag\output;

use templatable;
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
class tagmetagroup extends \local_tag\output\output_base {

    /** @var string The meta tag group name */
    protected $metagroupname;

    /** @var array The tag collection */
    protected $tagset;

    /** @var array The group options */
    protected $metatags;

    /**
     * Constructor
     *
     * Get the tags in the given collection which relate to the given group tag.
     * Put all other related meta tags into an extra meta tag collection.
     *
     * @param int    $tagcollid The tag collection id
     * @param object $grouptag  The group tag object
     * @param int    $ctx       The tag context, tagged tags have the context 1
     */
    public function __construct($tagcollid, $grouptag, $ctx = 1) {
        $metatagprefix = \local_tag\tag::get_meta_prefix();
        $this->tagset = array();
        $this->metatags = array();
        $this->metagroupname = \local_tag\tag::get_meta_tag_stripped_name($grouptag->name,
                \local_tag\tag::get_meta_group_prefix());

        if ($result = \local_tag\collection::get_course_tags_by_group($tagcollid, $grouptag->id, $ctx)) {
            foreach ($result as $id => $onetag) {
                if (strpos($onetag->name, $metatagprefix) === false) {
                    $this->tagset[$onetag->id] = (object) array(
                            'id' => $onetag->id, 'name' => $onetag->name, 'rawname' => $onetag->rawname);
                } else {
                    $this->metatags[$onetag->name] = (object) array(
                            'id' => $onetag->id, 'name' => $onetag->name, 'rawname' => $onetag->rawname);
                }
            }
        }
    }

    /**
     * Magic getter
     *
     * @return string
     */
    protected function get_metagroupname() {
        return $this->metagroupname;
    }

    /**
     * Magic getter
     *
     * @return array
     */
    protected function get_tagset() {
        return $this->tagset;
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
                'metagroupname' => $this->metagroupname,
                'tagset' => $tagset
        );
    }
}
