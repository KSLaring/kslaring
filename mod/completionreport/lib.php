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
 * Library of functions and constants for module completionreport
 *
 * @package    mod
 * @subpackage completionreport
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/** COMPLETIONREPORT_MAX_NAME_LENGTH = 50 */
define("COMPLETIONREPORT_MAX_NAME_LENGTH", 50);

/**
 * @uses COMPLETIONREPORT_MAX_NAME_LENGTH
 * @param object $completionreport
 * @return string
 */
function get_completionreport_name($completionreport) {
    $name = strip_tags(format_string($completionreport->intro,true));
    if (core_text::strlen($name) > COMPLETIONREPORT_MAX_NAME_LENGTH) {
        $name = core_text::substr($name, 0, COMPLETIONREPORT_MAX_NAME_LENGTH)."...";
    }

    if (empty($name)) {
        // arbitrary name
        $name = get_string('modulename','completionreport');
    }

    return $name;
}
/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $completionreport
 * @return bool|int
 */
function completionreport_add_instance($completionreport) {
    global $DB;

    $completionreport->name = get_completionreport_name($completionreport);
    $completionreport->timemodified = time();

    return $DB->insert_record("completionreport", $completionreport);
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $completionreport
 * @return bool
 */
function completionreport_update_instance($completionreport) {
    global $DB;

    $completionreport->name = get_completionreport_name($completionreport);
    $completionreport->timemodified = time();
    $completionreport->id = $completionreport->instance;

    return $DB->update_record("completionreport", $completionreport);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function completionreport_delete_instance($id) {
    global $DB;

    if (! $completionreport = $DB->get_record("completionreport", array("id"=>$id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("completionreport", array("id"=>$completionreport->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @global object
 * @param object $coursemodule
 * @return cached_cm_info|null
 */
function completionreport_get_coursemodule_info($coursemodule) {
    global $DB;

    if ($completionreport = $DB->get_record('completionreport', array('id'=>$coursemodule->instance), 'id, name, intro, introformat')) {
        if (empty($completionreport->name)) {
            // completionreport name missing, fix it
            $completionreport->name = "completionreport{$completionreport->id}";
            $DB->set_field('completionreport', 'name', $completionreport->name, array('id'=>$completionreport->id));
        }
        $info = new cached_cm_info();
        // no filtering hre because this info is cached and filtered later
        $info->content = format_module_intro('completionreport', $completionreport, $coursemodule->id, false);
        $info->name  = $completionreport->name;
        return $info;
    } else {
        return null;
    }
}

/**
 * @return array
 */
function completionreport_get_view_actions() {
    return array();
}

/**
 * @return array
 */
function completionreport_get_post_actions() {
    return array();
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function completionreport_reset_userdata($data) {
    return array();
}

/**
 * Returns all other caps used in module
 *
 * @return array
 */
function completionreport_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function completionreport_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return false;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:          return false;
        case FEATURE_NO_VIEW_LINK:            return false;

        default: return null;
    }
}
