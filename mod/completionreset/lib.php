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
 * @package mod_completionreset
 * @copyright  2015 Justin Hunt (http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

define('MOD_COMPLETIONRESET_TABLE','completionreset');
define('MOD_COMPLETIONRESET_ACTIVITIESTABLE','completionreset_activities');
define('MOD_COMPLETIONRESET_SELECT','mod_completionreset_select');
define('MOD_COMPLETIONRESET_CHOSEN','mod_completionreset_chosen');
define('MOD_COMPLETIONRESET_UNCHOSEN','mod_completionreset_unchosen');
define('MOD_COMPLETIONRESET_UPDATEFIELD','activities');
define('MOD_COMPLETIONRESET_LISTSIZE',10);
/**
 * List of features supported in Completion Reset Module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function completionreset_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;
        case FEATURE_MOD_INTRO:               return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_SHOW_DESCRIPTION:        return false;
        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function completionreset_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function completionreset_reset_userdata($data) {
    return array();
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function completionreset_get_view_actions() {
    return array('view','view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function completionreset_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add Completion Reset instance.
 * @param stdClass $data
 * @param mod_completionreset_mod_form $mform
 * @return int new page instance id
 */
function completionreset_add_instance($data, $mform = null) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid = $data->coursemodule;

    $data->timemodified = time();
    $data->id = $DB->insert_record('completionreset', $data);

    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
	
	//This was misguided and I think we don't need it.
	/*
	$rec = $DB->get_record(MOD_COMPLETIONRESET_ACTIVITIESTABLE,array('course'=>$data->course));
	if(!$rec || empty($rec->activities)){
		$DB->set_field('course_modules', 'visible', false, array('id'=>$cmid));
	}
	*/
    return $data->id;
}

function completionreset_add_to_course_navigation($courseid){
	global $PAGE;
	$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
	$completionresetnode = $coursenode->add(get_string('choosemenulabel','completionreset'), 
			new moodle_url('/mod/completionreset/choose.php',array('course'=>$courseid)));
	$completionresetnode->make_active();
}
/*
function completionreset_extend_navigation($completionresetnode,$course,$module,$cm){
	completionreset_add_to_course_navigation($course->id);
}
*/

/**
 * Update page instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function completionreset_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid        = $data->coursemodule;
    $data->timemodified = time();
    $data->id           = $data->instance;
    $DB->update_record('completionreset', $data);
    return true;
}

/**
 * Delete Completion Reset instance.
 * @param int $id
 * @return bool true
 */
function completionreset_delete_instance($id) {
    global $DB;

    if (!$completionreset = $DB->get_record('completionreset', array('id'=>$id))) {
        return false;
    }
    $DB->delete_records('completionreset', array('id'=>$completionreset->id));

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param stdClass $coursemodule
 * @return cached_cm_info Info to customise main page display
 */
function completionreset_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if (!$completionreset	= $DB->get_record('completionreset', array('id'=>$coursemodule->instance),
            'id, name')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $completionreset->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = $completionreset->name;
    }

    return $info;
}


/**
 * Lists all browsable file areas
 *
 * @package  mod_completionreset
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @return array
 */
function completionreset_get_file_areas($course, $cm, $context) {
    $areas = array();
    return $areas;
}

/**
 * File browsing support for Completion Reset Module content area.
 *
 * @package  mod_completionreset
 * @category files
 * @param stdClass $browser file browser instance
 * @param stdClass $areas file areas
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param int $itemid item ID
 * @param string $filepath file path
 * @param string $filename file name
 * @return file_info instance or null if not found
 */
function completionreset_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    return null;
}

/**
 * Serves the page files.
 *
 * @package  mod_completionreset
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function completionreset_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
	return false;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function completionreset_completionreset_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-page-*'=>get_string('page-mod-page-x', 'page'));
    return $module_pagetype;
}

/**
 * Export page resource contents
 *
 * @return array of file content
 */
function completionreset_export_contents($cm, $baseurl) {
    global $CFG, $DB;
    $contents = array();
    return $contents;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function completionreset_dndupload_register() {
    return array('types' => array());
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function completionreset_dndupload_handle($uploadinfo) {

    return false;
}
