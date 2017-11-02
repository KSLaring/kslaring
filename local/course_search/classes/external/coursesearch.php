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
 * Web Service functions for course search.
 *
 * @package    local
 * @subpackage course_search
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_search\external;

require_once(__DIR__ . '/../../../../config.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use coursecat;

/**
 * Web Service functions for course search.
 *
 * @package    local
 * @subpackage course_search
 * @copyright  2017 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursesearch extends external_api {
    /**
     * Get the course data.
     *
     * @param int $userid The userid
     *
     * @return array The course data
     */
    public static function get_course_data($userid, $nocache) {
        global $CFG, $PAGE;

        $result = array();

        // Switch to turn caching on/off.
        if ($nocache) {
            if (false) {
                $coursedata = file_get_contents(__DIR__ . '/../../fixtures/courses.json');
            } else {
                $PAGE->set_context(\context_system::instance());
                $courses = new \local_course_search\output\courses();
                //$coursedata = json_encode(array('courses' => $courses->export_course_data()));
                $coursedata = $courses->export_json_course_data();
            }
        } else {
            // Try to get the cached course data. If no data is cached request the database and save the data in the cache.
            $cache = \cache::make('local_course_search', 'courses');
            $coursedata = $cache->get($userid); // Get the cached data for the user.
            if (!$coursedata) {
                // Load the fixture for development.
                if (false) {
                    $coursedata = file_get_contents(__DIR__ . '/../../fixtures/courses.json');
                } else {
                    $PAGE->set_context(\context_system::instance());
                    $courses = new \local_course_search\output\courses();
                    //$coursedata = json_encode(array('courses' => $courses->export_course_data()));
                    $coursedata = $courses->export_json_course_data();
                    $cache->set($userid, $coursedata); // Cache data for the user.
                }
            }
        }

        $result['coursedata'] = $coursedata;

        return $result;
    }

    /**
     * The parameters for get_course_data.
     *
     * @return external_function_parameters
     */
    public static function get_course_data_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User id'),
            'nocache' => new external_value(PARAM_INT, 'Don\'t use the cachce'),
        ]);
    }

    /**
     * The return configuration for get_course_data.
     *
     * @return external_single_structure
     */
    public static function get_course_data_returns() {
        return new external_single_structure([
            'coursedata' => new external_value(PARAM_RAW, 'The course data as JSON',
                VALUE_OPTIONAL),
        ]);
    }

    /**
     * Save the user's search preselection.
     *
     * @param string $data JSON string with user preselects
     *
     * @return array The result
     */
    public static function save_search_criteria($data) {
        global $USER, $DB;

        $data = json_decode($data);

        if (confirm_sesskey($data->sesskey)) {
            // Delete the cached course search data for the user.
            $cache = \cache::make('local_course_search', 'courses');
            $cache->delete($USER->id); // Delete the cached data for the user.

            $data->userid = $USER->id;
            //if (!empty($data->tags)) {
            self::save_user_search_criteria($USER, $data->tags);
            //}
        } else {
            $data = array('success' => false);
        }

        $result = array(
            'result' => json_encode($data)
        );

        return $result;
    }

    /**
     * The parameters for save_search_criteria.
     *
     * @return external_function_parameters
     */
    public static function save_search_criteria_parameters() {
        return new external_function_parameters([
            'data' => new external_value(PARAM_RAW, 'Search preselection data'),
        ]);
    }

    /**
     * The return configuration for save_search_criteria.
     *
     * @return external_single_structure
     */
    public static function save_search_criteria_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_RAW, 'The result',
                VALUE_OPTIONAL)
        ]);
    }

    /**
     * Save the user preselected tags.
     *
     * Delete all existing user entries and save the transmitted ones.
     *
     * @param object $user The user object
     * @param array  $tags The selected tag ids
     */
    protected static function save_user_search_criteria($user, $tags) {
        global $DB;

        $DB->delete_records('local_course_search_presel', array('user' => $user->id));

        if (!empty($tags)) {
            $dataobjects = array();

            foreach ($tags as $tagid) {
                $dataobjects[] = (object)array(
                    'user' => $user->id,
                    'itemtype' => 'tag',
                    'itemid' => $tagid
                );
            }

            $DB->insert_records('local_course_search_presel', $dataobjects);
        }
    }


    /**
     * Get the structured user search criteria.
     *
     * @param int $userid The userid
     *
     * @return array The result
     */
    public static function get_user_search_criteria($userid) {
        $tagdata = \local_course_search\util::get_user_search_criteria($userid);

        $result['data'] = json_encode($tagdata);

        return $result;
    }

    /**
     * The parameters for get_user_search_criteria.
     *
     * @return external_function_parameters
     */
    public static function get_user_search_criteria_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User id'),
        ]);
    }

    /**
     * The return configuration for get_user_search_criteria.
     *
     * @return external_single_structure
     */
    public static function get_user_search_criteria_returns() {
        return new external_single_structure([
            'data' => new external_value(PARAM_RAW, 'The selected tags data as JSON',
                VALUE_OPTIONAL),
        ]);
    }

    /**
     * Get all structured course tags.
     *
     * @return array The result
     */
    public static function get_all_course_tags($userid) {
        $tagdata = \local_course_search\util::get_all_course_tags();

        $result['data'] = json_encode($tagdata);

        return $result;
    }

    /**
     * The parameters for get_all_course_tags.
     *
     * @return external_function_parameters
     */
    public static function get_all_course_tags_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User id'),
        ]);
    }

    /**
     * The return configuration for get_all_course_tags.
     *
     * @return external_single_structure
     */
    public static function get_all_course_tags_returns() {
        return new external_single_structure([
            'data' => new external_value(PARAM_RAW, 'All structured course tags data as JSON',
                VALUE_OPTIONAL),
        ]);
    }
}
