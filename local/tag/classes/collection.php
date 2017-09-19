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
 * Class to manage tag collections, extends core_tag_collection.
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tag;

use core_tag_collection;
use core_text;

defined('MOODLE_INTERNAL') || die();

/**
 * Class to manage tag collections, extends core_tag_collection.
 *
 *
 * @package   local_tag
 * @copyright 2017 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class collection extends core_tag_collection {

    /** @var string used for function cloud_sort() */
    public static $taggroupsortfield = 'name';

    /**
     * Returns all tags in the given tag collection
     *
     * @param int    $tagcollid  The tag collection id
     * @param string $metaprefix The prefix of the meta tags to find
     *
     * @return array An array of tag objects
     */
    public static function get_tags($tagcollid, $metaprefix = '') {
        $tags = array();

        $tags = self::query_db_for_tags($tagcollid, $metaprefix);

        return $tags;
    }

    /**
     * Query the db.
     *
     * @param int    $tagcollid  The tag collection id
     * @param string $metaprefix The prefix of the meta tags to find
     *
     * @return array An array of tag objects
     */
    public static function query_db_for_tags($tagcollid, $metaprefix = '') {
        global $DB;
        $params = array();

        // Set the collection id for the basic where clause.
        $whereclause = 'WHERE tagcollid = :tagcollid';
        $params['tagcollid'] = $tagcollid;

        // Add the metaprefix if given.
        if (strval($metaprefix) !== '') {
            $whereclause .= ' AND tg.name LIKE :metaprefix';
            $metaprefix = str_replace('_', '\_', $metaprefix);
            $params['metaprefix'] = $metaprefix . '%';
        }

        $sql = "
            SELECT tg.id, tg.name, tg.rawname, tg.isstandard, tg.flag, tg.timemodified,
                      tg.userid, COUNT(ti.id) AS count, tg.tagcollid
            FROM {tag} tg
            LEFT JOIN {tag_instance} ti ON ti.tagid = tg.id
            $whereclause
            GROUP BY tg.id, tg.name, tg.rawname, tg.isstandard, tg.flag, tg.timemodified,
                       tg.userid, tg.tagcollid";

        $tagdata = $DB->get_records_sql($sql, $params);

        return $tagdata;
    }

    /**
     * Returns the list of tags related to a group meta tag.
     *
     * @param int       $tagcollid
     * @param int       $grouptagid   The id of the group meta tag
     * @param int       $ctx          The context id
     * @param null|bool $isstandard   Return only standard tags
     * @param int       $limit        Maximum number of tags to retrieve, tags are sorted by the instance count
     *                                descending here regardless of $sort parameter
     * @param string    $sort         Sort order for display, default 'name' - tags will be sorted after they are retrieved
     * @param string    $search       Search string
     * @param string    $excludename  Exclude the tags containing the name, for example exclude the meta tags
     * @param array     $inidlist     List of tag ids to filter against
     *
     * @return array The group tags, if sorted indices 0 ... n, if not sorted the indices are the tag ids
     */
    public static function get_group_tags($tagcollid, $grouptagid, $ctx = 1, $isstandard = false, $limit = 150,
            $sort = 'name', $search = '', $excludename = '', $inidlist = array()) {
        global $DB;
        $params = array();

        $fromclause = 'FROM {tag_instance} ti JOIN {tag} tg ON tg.id = ti.itemid';
        $whereclause = 'WHERE ti.itemtype = \'tag\'';
        $whereclause .= ' AND tg.tagcollid = ?';
        $params[] = $tagcollid;
        $whereclause .= ' AND ti.tagid = ?';
        $params[] = $grouptagid;
        $whereclause .= ' AND ti.contextid = ?';
        $params[] = $ctx; // Tagged tags have the contextid 1.
        if ($isstandard) {
            $whereclause .= ' AND tg.isstandard = 1';
        }
        if (strval($search) !== '') {
            $whereclause .= ' AND tg.name LIKE ?';
            $params[] = '%' . core_text::strtolower($search) . '%';
        }
        if (strval($excludename) !== '') {
            $whereclause .= ' AND tg.name NOT LIKE ?';
            $params[] = '%' . core_text::strtolower($excludename) . '%';
        }
        if (!empty($inidlist)) {
            list($insql, $inparams) = $DB->get_in_or_equal($inidlist);
            $whereclause .= ' AND tg.id ' . $insql;
            $params = array_merge($params, $inparams);
        }

        $collatedanish = '';
        if (current_language() === "no" || current_language() === "da" || current_language() === "sv") {
            $collatedanish = 'collate utf8_danish_ci';
        }

        $sql = "SELECT tg.id, tg.rawname, tg.name, tg.isstandard, tg.flag, tg.tagcollid
                        $fromclause
                        $whereclause
                        GROUP BY tg.id, tg.rawname, tg.name, tg.flag, tg.isstandard, tg.tagcollid
                        ORDER BY tg.name $collatedanish ASC";

        $grouptags = $DB->get_records_sql($sql, $params, 0, $limit);

        $tagscount = count($grouptags);
        if ($tagscount == $limit) {
            $tagscount = $DB->get_field_sql("SELECT COUNT(DISTINCT tg.id) $fromclause $whereclause", $params);
        }

        // It's not necessary to sort, the data comes sorted form the db.
        //if (strval($sort) !== '') {
        //    self::$taggroupsortfield = $sort;
        //    usort($grouptags, "self::taggroup_sort");
        //}

        return $grouptags;
    }

    /**
     * Returns the list of course tags related to a group meta tag and a context.
     *
     * @param int       $tagcollid
     * @param int       $grouptagid The id of the group meta tag
     * @param int       $ctx        The given context
     * @param null|bool $isstandard return only standard tags
     * @param int       $limit      maximum number of tags to retrieve, tags are sorted by the instance count
     *                              descending here regardless of $sort parameter
     * @param string    $sort       sort order for display, default 'name' - tags will be sorted after they are retrieved
     * @param string    $search     search string
     *
     * @return array The group tags, if sorted indices 0 ... n, if not sorted the indices are the tag ids
     */
    public static function get_course_tags_by_group($tagcollid, $grouptagid, $ctx = 1, $isstandard = false, $limit = 150,
            $sort = 'name', $search = '') {
        global $DB;
        $params = array();

        $fromclause = 'FROM {tag} tg JOIN {tag_instance} ti ON tg.id = ti.tagid JOIN {tag_instance} ti2 ON tg.id = ti2.itemid';
        $whereclause = 'WHERE ti.itemtype = \'course\'';
        $whereclause .= ' AND tg.tagcollid = ?';
        $params[] = $tagcollid;
        $whereclause .= ' AND ti2.tagid = ?';
        $params[] = $grouptagid;
        $whereclause .= ' AND ti.contextid = ?';
        $params[] = $ctx; // Tagged tags have the contextid 1.
        if ($isstandard) {
            $whereclause .= ' AND tg.isstandard = 1';
        }
        if (strval($search) !== '') {
            $whereclause .= ' AND tg.name LIKE ?';
            $params[] = '%' . core_text::strtolower($search) . '%';
        }

        $collatedanish = '';
        if (current_language() === "no" || current_language() === "da" || current_language() === "sv") {
            $collatedanish = 'collate utf8_danish_ci';
        }

        $sql = "SELECT tg.id, tg.rawname, tg.name, tg.isstandard, tg.flag, tg.tagcollid
                        $fromclause
                        $whereclause
                        GROUP BY tg.id, tg.rawname, tg.name, tg.flag, tg.isstandard, tg.tagcollid
                        ORDER BY tg.name $collatedanish ASC";

        $grouptags = $DB->get_records_sql($sql, $params, 0, $limit);

        $tagscount = count($grouptags);
        if ($tagscount == $limit) {
            $tagscount = $DB->get_field_sql("SELECT COUNT(DISTINCT tg.id) $fromclause $whereclause", $params);
        }

        // It's not necessary to sort, the data comes sorted form the db.
        //if (strval($sort) !== '') {
        //    self::$taggroupsortfield = $sort;
        //    usort($grouptags, "self::taggroup_sort");
        //}

        return $grouptags;
    }

    /**
     * Returns the list of group meta tags related to a tag.
     *
     * @param int       $tagcollid
     * @param int       $tagid        The id of the tag
     * @param int       $ctx          The context id
     * @param null|bool $isstandard   Return only standard tags
     * @param int       $limit        Maximum number of tags to retrieve, tags are sorted by the instance count
     *                                descending here regardless of $sort parameter
     * @param string    $sort         Sort order for display, default 'name' - tags will be sorted after they are retrieved
     * @param string    $search       Search string
     * @param string    $excludename  Exclude the tags containing the name, for example exclude the meta tags
     *
     * @return array The group tags, if sorted indices 0 ... n, if not sorted the indices are the tag ids
     */
    public static function get_tag_related_group_tags($tagcollid, $tagid, $metagroup, $ctx = 1, $isstandard = false, $limit = 150,
            $search = '', $excludename = '') {
        global $DB;
        $params = array();

        $fromclause = 'FROM {tag_instance} ti JOIN {tag} tg ON tg.id = ti.itemid';
        $whereclause = 'WHERE ti.itemtype = \'tag\'';
        $whereclause .= ' AND tg.tagcollid = ?';
        $params[] = $tagcollid;
        $whereclause .= ' AND ti.tagid = ?';
        $params[] = $tagid;
        $whereclause .= ' AND tg.name LIKE ?';
        $params[] = core_text::strtolower($metagroup) . '%';
        $whereclause .= ' AND ti.contextid = ?';
        $params[] = $ctx; // Tagged tags have the contextid 1.
        if ($isstandard) {
            $whereclause .= ' AND tg.isstandard = 1';
        }
        if (strval($search) !== '') {
            $whereclause .= ' AND tg.name LIKE ?';
            $params[] = '%' . core_text::strtolower($search) . '%';
        }
        if (strval($excludename) !== '') {
            $whereclause .= ' AND tg.name NOT LIKE ?';
            $params[] = '%' . core_text::strtolower($excludename) . '%';
        }

        $collatedanish = '';
        if (current_language() === "no" || current_language() === "da" || current_language() === "sv") {
            $collatedanish = 'collate utf8_danish_ci';
        }

        $sql = "SELECT tg.id, tg.rawname, tg.name, tg.isstandard, tg.flag, tg.tagcollid
                        $fromclause
                        $whereclause
                        GROUP BY tg.id, tg.rawname, tg.name, tg.flag, tg.isstandard, tg.tagcollid
                        ORDER BY tg.name $collatedanish ASC";

        $grouptags = $DB->get_records_sql($sql, $params, 0, $limit);

        return $grouptags;
    }

    /**
     * Returns all tags in the given tag collection which are not in a tag group.
     *
     * @param int    $tagcollid  The tag collection id
     * @param string $metaprefix The prefix of the meta tags to exclude
     *
     * @return array An array of tag objects
     */
    public static function get_course_tags_in_no_group($tagcollid, $metaprefix) {
        global $DB;

        $tags = null;
        $metaprefix = str_replace('_', '\_', $metaprefix);

        $collatedanish = '';
        if (current_language() === "no" || current_language() === "da" || current_language() === "sv") {
            $collatedanish = 'collate utf8_danish_ci';
        }

        $sql = "
            SELECT
              tg.id,
              tg.name,
              tg.rawname
            FROM {tag} tg
              LEFT JOIN {tag_instance} ti ON ti.tagid = tg.id
            WHERE tagcollid = ?
                  AND tg.name NOT LIKE ?
                  AND tg.id NOT IN (
              SELECT ti1.tagid
              FROM {tag_instance} ti1
              WHERE ti1.contextid = 1
                    AND ti1.itemtype = 'tag'
              GROUP BY ti1.tagid
            )
            GROUP BY tg.id, tg.name
            ORDER BY tg.name $collatedanish ASC;
        ";

        $params = array($tagcollid, $metaprefix . '%');
        $tagdata = $DB->get_records_sql($sql, $params);

        self::$taggroupsortfield = 'name';

        // It's not necessary to sort, the data comes sorted form the db.
        //usort($tagdata, "self::taggroup_sort");

        return $tagdata;
    }

    /**
     * Get the meta tags in the given collection with the given prefix.
     * If order is set as an array of tag ids it is ued as the sortorder and the sorted tags are returned.
     *
     * @param int    $tagcollid  The tag collection id
     * @param string $metaprefix The prefix of the meta tags to find
     * @param array  $order      The tag ids used to sort the tags
     *
     * @return array
     */
    public static function get_meta_tags($tagcollid, $metaprefix, $order = array()) {
        $tagssorted = array();
        $tags = self::get_tags($tagcollid, $metaprefix);

        if (!empty($order)) {
            // Sort the tags in the saved order. To be able to deal with added or removed tags
            // the sorted tags are removed from the collection.
            foreach ($order as $id) {
                if (array_key_exists($id, $tags)) {
                    $tagssorted[$id] = $tags[$id];
                    unset($tags[$id]);
                }
            }

            // If there are tags left which have not been in the saved sort order add them at the end.
            if (count($tags)) {
                $tagssorted += $tags;
            }

            $tags = $tagssorted;
        }

        return $tags;
    }

    /**
     * This function is used to sort the tags.
     *
     * @param   string $a Tag name to compare against $b
     * @param   string $b Tag name to compare against $a
     *
     * @return  int    The result of the comparison/validation 1, 0 or -1
     */
    public static function taggroup_sort($a, $b) {
        $tagsort = self::$taggroupsortfield ? : 'name';

        if (is_numeric($a->$tagsort)) {
            return ($a->$tagsort == $b->$tagsort) ? 0 : ($a->$tagsort > $b->$tagsort) ? 1 : -1;
        } else {
            if (is_string($a->$tagsort)) {
                return strnatcmp($a->$tagsort, $b->$tagsort);
            } else {
                return 0;
            }
        }
    }
}
