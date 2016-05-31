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
 * Event observer.
 *
 * @package    theme_frikomport
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_frikomport\event;

defined('MOODLE_INTERNAL') || die();

class observer {
    /**
     * Redirect all events to this log manager, but only if this
     * log manager is actually used.
     *
     * @param \core\event\base $event
     */
    public static function process_user_created(\core\event\base $event) {
        global $CFG;

        // Only run when the fripomort theme is activated.
        if (get_string('pluginname', 'theme_frikomport') === $CFG->theme) {
            $data = $event->get_data();
            $objectid = $data['objectid'];
            $objecttable = $data['objecttable'];

            if ($objecttable === 'user') {
                self::set_user_preference($objectid);
            }
        }
    }

    /**
     * Set the user preferences for the navigation and settings block
     * to collapsed.
     *
     * @param string $name   The name of the preference to set
     * @param string $value  The value to set
     * @param int    $userid The userid
     *
     * @throws \coding_exception
     */
    protected static function set_user_preference($userid) {
        $blockids = self::get_blockinstanceids();

        if (!is_null($blockids)) {
            foreach ($blockids as $blockid) {
                set_user_preference('block' . $blockid . 'hidden', 1, $userid);
            }
        }
    }

    /**
     * Get the instance ids of the navigation and settings blocks.
     *
     * @return array|null    Null if error, else the block ids
     *
     * @throws \coding_exception
     */
    protected static function get_blockinstanceids() {
        global $DB;
        $return = null;

        $sql = "
            SELECT
              id,
              blockname
            FROM {block_instances}
            WHERE blockname = 'navigation'
                  OR blockname = 'settings'
        ";

        if ($result = $DB->get_records_sql($sql)) {
            $return = array();
            foreach ($result as $row) {
                $return[] = $row->id;
            }
        }

        return $return;
    }
}
