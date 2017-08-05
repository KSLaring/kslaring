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
 * Contains class \local_tag\output\accordiontaggroup_wrapper
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
 * Class to display the accordion with the meta tags.
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class accordiontaggroup_wrapper extends \local_tag\output\output_base {

    /** @var string $title The accordion title */
    protected $title;

    /** @var array $taggroups The tag group collection */
    protected $taggroups = array();

    /** @var string $templatename The name of the connected mustache template */
    protected $templatename = 'local_tag/accordiontaggroup_wrapper';

    /**
     * Constructor
     *
     * Get the tags in the given collection which relate to the given group tag.
     *
     * @param string        $title  The accordion title
     * @param renderer_base $output The core renderer
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
        $this->create_taggroup_data($output);

        return (object) array(
                'title' => $this->title,
                'taggroups' => $this->taggroups
        );
    }

    /**
     * Create the tag group data.
     *
     * Tag object with 'id' and 'name'.
     *
     * @param renderer_base $output The core renderer
     */
    protected function create_taggroup_data(renderer_base $output) {
        $tagcollid = \core_tag_area::get_collection('core', 'course');
        $metaprefix = \local_tag\tag::get_meta_prefix();

        // Get the group tags.
        $value = get_config('', 'block_course_tags_groupsortorder');
        $order = explode(' ', $value);
        $grouptags = \local_tag\collection::get_meta_tags($tagcollid, \local_tag\tag::get_meta_group_prefix(), $order);

        // Get all tags related to the group tags and prepare the tag group data.
        foreach ($grouptags as $id => $grouptag) {
            $metagrouptags = \local_tag\collection::get_group_tags($tagcollid, $grouptag->id, 1, false, 0, '', '', $metaprefix);

            $tagset = array();
            foreach ($metagrouptags as $tagid => $onetag) {
                $tagset[] = (object) array(
                        'id' => $onetag->id,
                        'name' => $onetag->rawname
                );
            }

            $groupname = \local_tag\tag::get_meta_tag_stripped_name($grouptag->name, \local_tag\tag::get_meta_group_prefix());
            $groupdata = new \local_tag\output\accordiontaggroup('group-' . $grouptag->id, $groupname, $tagset);

            $this->taggroups[] = $groupdata->export_for_template($output);
        }
    }
}
