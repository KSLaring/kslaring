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

namespace local_course_search;

/**
 * Course search common utility functions.
 *
 * @package         local
 * @subpackage      course_search
 * @copyright       2017 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Course search common utility functions.
 *
 * @package         local
 * @subpackage      course_search
 * @copyright       2017 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @property boolean $viewfullnames Whether to override fullname()
 */
class util {
    /**
     * @return mixed
     */
    public static function get_searchtags($userid) {
        $context = null;

        $strsortby = get_string('sortby', 'local_course_search');
        $strdisplayoptions = get_string('displayoptions', 'local_course_search');
        $strsortdesc = get_string('sortdesc', 'local_course_search');
        $strshowtags = get_string('showtags', 'local_course_search');
        $strcoursename = get_string('course_name', 'local_friadmin');
        $strcoursedate = get_string('course_date', 'local_friadmin');
        $strcourseseats = get_string('course_seats', 'local_friadmin');
        $strcoursedeadline = get_string('course_deadline', 'local_friadmin');
        $strcoursemunicipality = get_string('course_municipality', 'local_friadmin');
        $strcourselocation = get_string('course_location', 'local_friadmin');

        if (false) {
            $json = file_get_contents(__DIR__ . '/../fixtures/course_search.json');
            $context = json_decode($json);
        } else {
            $groups = null;
            if ($userid) {
                $groups = static::get_user_search_criteria($userid);
            }

            $context = (object)array(
                'searchtags' => (object)array(
                    'groups' => $groups,
                    'diplayoptions' => array(
                        (object)array(
                            'type' => 'display',
                            'groupid' => 0,
                            'group' => 'display',
                            'title' => $strdisplayoptions,
                            'shown' => array(
                                (object)array(
                                    'type' => 'display',
                                    'id' => 'd0',
                                    'groupid' => 0,
                                    'group' => 'sort',
                                    'name' => $strsortby,
                                    'sort' => 'name',
                                    'isselect' => 1,
                                    'elementitems' => array(
                                        (object)array(
                                            'value' => 'name',
                                            'text' => \core_text::strtolower($strcoursename)
                                        ),
                                        (object)array(
                                            'value' => 'date',
                                            'text' => \core_text::strtolower($strcoursedate)
                                        ),
                                        (object)array(
                                            'value' => 'availseats',
                                            'text' => \core_text::strtolower($strcourseseats)
                                        ),
                                        (object)array(
                                            'value' => 'deadline',
                                            'text' => \core_text::strtolower($strcoursedeadline)
                                        ),
                                        (object)array(
                                            'value' => 'municipality',
                                            'text' => \core_text::strtolower($strcoursemunicipality)
                                        ),
                                        (object)array(
                                            'value' => 'location',
                                            'text' => \core_text::strtolower($strcourselocation)
                                        )
                                    ),
                                    'checked' => 1
                                ),
                                (object)array(
                                    'type' => 'display',
                                    'id' => 'd1',
                                    'groupid' => 0,
                                    'group' => 'sortdesc',
                                    'name' => $strsortdesc,
                                    'isselect' => 0,
                                    'checked' => 0
                                ),
                                (object)array(
                                    'type' => 'display',
                                    'id' => 'd2',
                                    'groupid' => 0,
                                    'group' => 'tags',
                                    'name' => $strshowtags,
                                    'ishidden' => 1,
                                    'isselect' => 0,
                                    'checked' => 0
                                ),
                            ),
                        )
                    ),
                )
            );
        }

        return $context->searchtags;
    }

    /**
     * Get the structured user search criteria.
     *
     * @param int $userid The userid
     *
     * @return array The result
     */
    public static function get_user_search_criteria($userid) {
        global $DB;

        $savedtagsids = array();
        $groups = array();

        // Get the tag ids from the saved tags table.
        $savedtags = $DB->get_records('local_course_search_presel', array('user' => $userid));
        foreach ($savedtags as $tag) {
            if ($tag->itemtype === 'tag') {
                $savedtagsids[] = $tag->itemid;
            }
        }

        list($tagcollid, $metaprefix, $grouptags) = self::get_groups_data();

        // Get all tags related to the group tags and prepare the tag group data.
        foreach ($grouptags as $id => $grouptag) {
            $metagrouptags = array();
            if (!empty($savedtagsids)) {
                $metagrouptags = \local_tag\collection::get_group_tags($tagcollid, $grouptag->id, 1, false, 0,
                    '', '', $metaprefix, $savedtagsids);
            }
            $groupname = \local_tag\tag::get_meta_tag_stripped_name($grouptag->name, \local_tag\tag::get_meta_group_prefix());
            $taggroup = \core_text::strtolower($groupname);
            $group = $taggroup;
            $groupid = $grouptag->id;
            $grouptitle = $groupname;

            $onegroup = (object)array(
                'type' => 'tag',
                'hidden' => null,
                'groupid' => $groupid,
                'group' => $group,
                'title' => $grouptitle,
                'shown' => array()
            );

            foreach ($metagrouptags as $tagid => $onetag) {
                $onegroup->shown[] = (object)array(
                    'id' => $onetag->id,
                    'name' => $onetag->rawname,
                    'type' => 'course',
                    'groupid' => $grouptag->id,
                    'group' => $taggroup,
                );
            }

            // If no tags for this group are preselected then don't show the group.
            if (empty($onegroup->shown)) {
                $onegroup->hidden = true;
            }

            $groups[] = $onegroup;
        }

        $strdate = get_string('date', 'local_course_search');
        $groups[] = (object)array(
            'type' => '',
            'hidden' => null,
            'groupid' => 0,
            'group' => '',
            'title' => $strdate,
            'shown' => array(),
            'showdate' => (object)array(
                'title' => $strdate
            )
        );

        return $groups;
    }

    /**
     * Get the structured course tags.
     *
     * @return object The result
     */
    public static function get_all_course_tags() {
        global $DB;

        $taglist = (object)array(
            'others' => (object)array(
                'type' => 'others',
                'title' => get_string('preselectothers', 'local_course_search'),
                'hassubcategories' => 1,
                'subcategories' => array()
            )
        );

        list($tagcollid, $metaprefix, $grouptags) = self::get_groups_data();

        $otherssubcategories = array();

        // Get all tags related to the group tags and prepare the tag group data.
        foreach ($grouptags as $id => $grouptag) {
            $metagrouptags = \local_tag\collection::get_group_tags($tagcollid, $grouptag->id, 1, false, 0,
                '', '', $metaprefix);
            $groupname = \local_tag\tag::get_meta_tag_stripped_name($grouptag->name, \local_tag\tag::get_meta_group_prefix());
            $onegroup = (object)array(
                'type' => \core_text::strtolower($groupname),
                'title' => $groupname,
                'hassubcategories' => 0,
                'tags' => array()
            );

            foreach ($metagrouptags as $tagid => $onetag) {
                $onegroup->tags[] = (object)array(
                    'id' => $onetag->id,
                    'name' => $onetag->rawname,
                    'type' => 'course',
                    'groupid' => $grouptag->id,
                    'group' => \core_text::strtolower($groupname),
                );
            }

            $otherssubcategories[] = $onegroup;
        }

        $taglist->others->subcategories = $otherssubcategories;

        return (object)array(
            'taglist' => $taglist
        );
    }

    /**
     * Get the ids of those courses that meet the user preseleted tags.
     *
     * @return array The course ids
     */
    public static function get_user_tagged_courseids() {
        global $DB, $USER;

        $preselectedtags = array();
        $courseids = array();

        if ($savedtags = $DB->get_records('local_course_search_presel', array('user' => $USER->id, 'itemtype' => 'tag'))) {
            foreach ($savedtags as $onetag) {
                $preselectedtags[] = $onetag->itemid;
            }
        }

        if (!empty($preselectedtags)) {
            list ($insql, $inparams) = $DB->get_in_or_equal($preselectedtags);
            $sql = '
                SELECT DISTINCT t.itemid
                FROM {tag_instance} t
                WHERE itemtype = "course"
                      AND t.tagid ' . $insql . '
                ORDER BY t.itemid
            ';

            if ($result = $DB->get_records_sql($sql, $inparams)) {
                foreach ($result as $row) {
                    $courseids[] = $row->itemid;
                }
            }
        }

        return $courseids;
    }

    /**
     * Get the the grouptags.
     *
     * @return array the grouptags
     */
    protected static function get_groups_data() {
        $tagcollid = \core_tag_area::get_collection('core', 'course');
        $metaprefix = \local_tag\tag::get_meta_prefix();

        // Get the group tags.
        $value = get_config('', 'block_course_tags_groupsortorder');
        $order = explode(' ', $value);
        $grouptags = \local_tag\collection::get_meta_tags($tagcollid, \local_tag\tag::get_meta_group_prefix(), $order);

        return array($tagcollid, $metaprefix, $grouptags);
    }
}