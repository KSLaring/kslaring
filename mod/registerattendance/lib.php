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
 * Library of functions and constants for module registerattendance
 *
 * @package    mod
 * @subpackage registerattendance
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

define('SHOW_ATTENDED_ALL', 0);
define('SHOW_ATTENDED_ATTENDED', 1);
define('SHOW_ATTENDED_NOT_ATTENDED', 2);
define('MAX_LISTED_USERS', 10);

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global       object
 *
 * @param object $registerattendance
 *
 * @return bool|int
 */
function registerattendance_add_instance($registerattendance) {
    global $DB;

    $registerattendance->timemodified = time();

    return $DB->insert_record("registerattendance", $registerattendance);
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global       object
 *
 * @param object $registerattendance
 *
 * @return bool
 */
function registerattendance_update_instance($registerattendance) {
    global $DB;

    $registerattendance->timemodified = time();
    $registerattendance->id = $registerattendance->instance;

    return $DB->update_record("registerattendance", $registerattendance);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global    object
 *
 * @param int $id
 *
 * @return bool
 */
function registerattendance_delete_instance($id) {
    global $DB;

    if (!$registerattendance = $DB->get_record("registerattendance", array("id" => $id))) {
        return false;
    }

    $result = true;

    if (!$DB->delete_records("registerattendance", array("id" => $registerattendance->id))) {
        $result = false;
    }

    return $result;
}

/**
 * @return array
 */
function registerattendance_get_view_actions() {
    return array();
}

/**
 * @return array
 */
function registerattendance_get_post_actions() {
    return array();
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param object $data the data submitted from the reset course.
 *
 * @return array status array
 */
function registerattendance_reset_userdata($data) {
    return array();
}

/**
 * Returns all other caps used in module
 *
 * @return array
 */
function registerattendance_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * Obtains the automatic completion state for this registerattendance based on any conditions
 * in the plugin settings.
 *
 * @param object $course Course
 * @param object $cm     Course-module
 * @param int    $userid User ID
 * @param bool   $type   Type of comparison (or/and; can be used as return value if no conditions)
 *
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function registerattendance_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

    // Get feedback details
    $registerattendance = $DB->get_record('registerattendance', array('id'=>$cm->instance), '*', MUST_EXIST);

    // If completion option is enabled, evaluate it and return true/false
    if ($registerattendance->completionattended) {
        $cache = cache::make('mod_registerattendance', 'registerattendance');
        $key = $cm->id . '_' . $userid;
        $value = $cache->get($key);

        if (!is_bool($value)) {
            $cache->delete($key);
            return $value;
        } else {
            return $type;
        }
    } else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_COMPLETION_HAS_RULES
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 *
 * @param string $feature FEATURE_xx constant for requested feature
 *
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function registerattendance_supports($feature) {
    switch ($feature) {
        case FEATURE_IDNUMBER:
            return false;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_GROUPMEMBERSONLY:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_NO_VIEW_LINK:
            return false;

        default:
            return null;
    }
}
