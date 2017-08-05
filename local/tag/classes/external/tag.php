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
 * Web Service functions for steps.
 *
 * @package    local
 * @subpackage tag
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tag\external;

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use core_tag_area;
use core_tag_tag;

/**
 * Web Service functions for tags.
 *
 * @package    local
 * @subpackage tag
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag extends external_api {
    /**
     * Add given course tags.
     *
     * @param   string $taglist The comma separated list of tag names.
     *
     * @return  array             Feedback
     */
    public static function add_course_tags($taglist) {
        $tagcollid = \core_tag_area::get_collection('core', 'course');
        $addedtags = array();

        $tagobjects = array();
        if ($tagcollid) {
            $newtags = preg_split('/\s*,\s*/', trim($taglist), -1, PREG_SPLIT_NO_EMPTY);
            $tagobjects = \core_tag_tag::create_if_missing($tagcollid, $newtags, true);
        }
        foreach ($tagobjects as $tagobject) {
            $addedtags[] = (object) array('id' => $tagobject->id, 'name' => $tagobject->rawname);

            if (!$tagobject->isstandard) {
                $tagobject->update(array('isstandard' => 1));
            }
        }

        $result['addsuccess'] = 1;
        $result['tagarray'] = json_encode($addedtags);

        return $result;
    }

    /**
     * The parameters for add_course_tags.
     *
     * @return external_function_parameters
     */
    public static function add_course_tags_parameters() {
        return new external_function_parameters([
                'taglist' => new external_value(PARAM_TEXT, 'Tag list'),
        ]);
    }

    /**
     * The return configuration for add_course_tags.
     *
     * @return external_single_structure
     */
    public static function add_course_tags_returns() {
        return new external_single_structure([
                'addsuccess' => new external_value(PARAM_INT, 'Tag add success', VALUE_OPTIONAL),
                'tagarray' => new external_value(PARAM_TEXT, 'Taglist back', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Add the tag list to the given group, remove the tags from the group they may have been be part of.
     *
     * @param   int    $groupid The group id.
     * @param   string $taglist The comma separated list of tag ids.
     *
     * @return  array             Feedback
     */
    public static function group_tags($groupid, $taglist) {
        $result = array();

        // If no tag ids given then return.
        if (empty($taglist)) {
            $result['result'] = get_string('notagsinlist', 'local_tag');

            return $result;
        }

        $tagsingroup = array();
        $tagsingrouparray = array();
        $tagcollid = \core_tag_area::get_collection('core', 'course');
        $tagids = explode(' ', $taglist);
        $tagidsingroup = array();
        $newtagsforgroup = null;

        // Get the actual tag list of the given tag group or the tags in no group.
        if ($groupid) {
            $tagsingroup = \local_tag\collection::get_group_tags($tagcollid, $groupid, 1, true, 150, 'name', '',
                    \local_tag\tag::get_meta_prefix());
        } else {
            if ($groupid == 0) {
                $tagsingroup = \local_tag\collection::get_course_tags_in_no_group($tagcollid, \local_tag\tag::get_meta_prefix());
            }
        }

        // Get the new tags from the submitted tag list that are actually not in the group.
        foreach ($tagsingroup as $onetag) {
            $tagidsingroup[] = $onetag->id;
            $tagsingrouparray[$onetag->id] = $onetag;
        }

        // Get the ids of the added tags and get the tag objects for the group tag and the new tags.
        $newtagidsforgroup = array_diff($tagids, $tagidsingroup);

        $grouptag = \core_tag_tag::get($groupid);
        $newtags = array();
        foreach ($newtagidsforgroup as $tagid) {
            if ($tagid != -1) {
                $tag = \core_tag_tag::get($tagid);
                $newtags[$tag->id] = $tag;
            }
        }

        // Get the related group tags for the given tag.
        $related = '';
        $relatedtagnames = array();
        foreach ($newtags as $onetag) {
            // Get the related meta group tags.
            $relatedgrouptags = \local_tag\collection::get_tag_related_group_tags($tagcollid, $onetag->id,
                    \local_tag\tag::get_meta_group_prefix());

            // Delete existing meta group relations, use the core method »set_item_tags«.
            foreach ($relatedgrouptags as $relatedgrouptag) {
                /* @var \core_tag_tag $relatedtag The tag object */
                $relatedtag = \core_tag_tag::get($relatedgrouptag->id);
                $manualrelatedtags = $relatedtag->get_manual_related_tags();
                // Get the names of all manual related tags, remove the actual tag and set the related tags
                // with the reduced tag set.
                $updatedtagnames = array();
                foreach ($manualrelatedtags as $mrt) {
                    if ($mrt->name !== $onetag->name) {
                        $updatedtagnames[] = $mrt->name;
                    }
                }
                $relatedtag->set_item_tags('core', 'tag', $relatedtag->id, \context_system::instance(), $updatedtagnames);
            }

            $relatedtagnames[] = $onetag->name;

            $related .= $onetag->id . ': ' . json_encode(array_keys($relatedgrouptags)) . '  ';
        }

        // If the new group id is not 0 then add the tags to the new group.
        if ($groupid > 0) {
            /* @var \core_tag_tag $newgrouptag The tag object */
            $newgrouptag = \core_tag_tag::get($grouptag->id);
            // Add the new meta group related tags.
            $newgrouptag->add_related_tags($relatedtagnames);
        }

        $result['result'] = $grouptag->id . ', ' . json_encode(array_keys($newtags)) . ' || related: ' . $related;

        return $result;
    }

    /**
     * The parameters for group_tags.
     *
     * @return external_function_parameters
     */
    public static function group_tags_parameters() {
        return new external_function_parameters([
                'groupid' => new external_value(PARAM_INT, 'Group id'),
                'taglist' => new external_value(PARAM_TEXT, 'Tag list'),
        ]);
    }

    /**
     * The return configuration for group_tags.
     *
     * @return external_single_structure
     */
    public static function group_tags_returns() {
        return new external_single_structure([
                'result' => new external_value(PARAM_TEXT, 'The result text', VALUE_OPTIONAL),
        ]);
    }
}
