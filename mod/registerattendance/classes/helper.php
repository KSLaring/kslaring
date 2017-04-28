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

//namespace mod_registerattendance;

defined('MOODLE_INTERNAL') || die;

//use moodle_url;
//use context_system;
//use stdClass;

/**
 * The Register attendance class with utility methods
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_registerattendance_helper {
    /**
     * @param $cmid
     * @param $userid
     * @param $state
     *
     * @return bool
     */
    public static function change_completionstate($cmid, $userid, $state) {
        global $DB;

        if (!$cm = get_coursemodule_from_id('registerattendance', $cmid)) {
            print_error('invalidcoursemodule');
        }

        if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
            print_error('coursemisconf');
        }

        // Load completion data.
        $completion = new completion_info($course);
        $cache = cache::make('mod_registerattendance', 'registerattendance');
        $result = $cache->set($cm->id . '_' . $userid, $state);
        $completion->update_state($cm, COMPLETION_UNKNOWN, $userid);

        return true;
    }

    /**
     * @param $cmid
     * @param $userids
     * @param $state
     *
     * @return bool
     */
    public static function change_completionstates($cmid, $userids, $state) {
        global $DB;

        if (!$cm = get_coursemodule_from_id('registerattendance', $cmid)) {
            print_error('invalidcoursemodule');
        }

        if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
            print_error('coursemisconf');
        }

        // Load completion data.
        $completion = new completion_info($course);

        foreach ($userids as $userid) {
            $cache = cache::make('mod_registerattendance', 'registerattendance');
            $result = $cache->set($cm->id . '_' . $userid, $state);
            $completion->update_state($cm, COMPLETION_UNKNOWN, $userid);
        }

        return true;
    }
}
