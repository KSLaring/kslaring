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
 * Contains class block_course_tags\util
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_tags;

use context_system;
use core_tag_tag;

/**
 * Class with utility methods
 *
 * @package    block_course_tags
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {

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
    static public function get_meta_tags($tagcollid, $metaprefix, $order = array()) {
        $tagssorted = array();
        $tags = \local_tag\collection::get_tags($tagcollid, $metaprefix);

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
}
