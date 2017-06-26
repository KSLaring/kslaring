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
 * Contains class block_course_tags\output\settagspage.
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_tags\output;

use templatable;
use renderer_base;
use moodle_url;
use block_course_tags_renderer;

/**
 * Class to prepare the course tag setting page.
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settagspage implements templatable {

    /** @var object $course The related course object. */
    protected $course;

    /** @var int $tagcollid The tag collection id. */
    protected $tagcollid = 0;

    /** @var array $taggroups The collection of tag group objects. */
    protected $taggroups = array();

    /** @var string $debug Debug information. */
    protected $debug = '';

    /**
     * Constructor
     *
     * @param object $course    The course object
     * @param int    $tagcollid The tag collection id
     * @param int    $ctx       The course context id
     */
    public function __construct($course, $tagcollid, $ctx) {
        $this->course = $course;
        $this->tagcollid = $tagcollid;
        $metaprefix = \local_tag\tag::get_meta_prefix();

        // Get the group tags.
        $value = get_config('', 'block_course_tags_groupsortorder');
        $order = explode(' ', $value);
        $grouptags = \block_course_tags\util::get_meta_tags($tagcollid, \local_tag\tag::get_meta_group_prefix(), $order);

        // Get all tags related to the group tags and list them.
        $first = true;
        foreach ($grouptags as $id => $grouptag) {
            if ($first) {
                //$open = 'in';
                $open = '';
                $first = false;
            } else {
                $open = '';
            }

            $metagrouptags = \local_tag\collection::get_group_tags($tagcollid, $grouptag->id, 1, false, 0,
                    '', '', $metaprefix);
            /* @var \local_tag\output\tagmetagroup $coursetags The tag meta group collection. */
            $coursetags = new \local_tag\output\tagmetagroup($tagcollid, $grouptag, $ctx);
            $coursetagids = array_keys($coursetags->tagset);

            // Set the singleselect attribute if set as a metatag.
            $metagroupmetatags = \local_tag\collection::get_group_tags($tagcollid, $grouptag->id, 1, false, 0,
                    '', $metaprefix, '');
            $singleselct = 0;
            foreach ($metagroupmetatags as $onemetagroupmetatag) {
                //if ($onemetagroupmetatag->name === \local_tag\tag::COURSE_TAGS_META_OPTION_SINGLESELECT) {
                if ($onemetagroupmetatag->name === \local_tag\tag::get_meta_option_singleselect()) {
                    $singleselct = 1;
                    break;
                }
            }

            $tagcollection = (object) array(
                    'id' => $id,
                    'groupname' => $coursetags->metagroupname,
                    'open' => $open,
                    'singleselect' => $singleselct,
                    'tagset' => array()
            );

            foreach ($metagrouptags as $tagid => $onetag) {
                $tagcollection->tagset[$onetag->id] = (object) array(
                        'id' => $onetag->id,
                        'name' => $onetag->rawname,
                        'datatagname' => \core_text::strtolower($onetag->rawname),
                        'ischecked' => in_array($onetag->id, $coursetagids)
                );
            }

            $this->taggroups[] = $tagcollection;
        }
    }

    /**
     * Magic getter
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name) {
        return $this->$name;
    }

    /**
     * Magic isset method
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name) {
        return isset($this->$name);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output The base renderer object
     *
     * @return object
     */
    public function export_for_template(renderer_base $output) {
        /* @var block_course_tags_renderer $output The renderer */
        $backurl = new moodle_url('/course/view.php', array('id' => $this->course->id));
        $taggroups = '';

        foreach ($this->taggroups as $group) {
            // Render selected as inline editable.
            foreach ($group->tagset as $id => $onetag) {
                $group->tagset[$id]->ischecked = $output->tag_item_checked($onetag, $this->course->id);
            }

            $group->tagset = array_values($group->tagset);
            $taggroups .= $output->taggroup($group);
        }

        return (object) array(
                'id' => $this->course->id,
                'tagcollid' => $this->tagcollid,
                'title' => get_string('settagstitle', 'block_course_tags'),
                'filterbytagname' => get_string('filterbytagname', 'block_course_tags'),
                'taggroups' => $taggroups,
                'backurl' => $backurl->out(),
                'backstr' => get_string('backstr', 'block_course_tags'),
                'debugoutput' => $this->debug
        );
    }
}
