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
 * Event observer for waitinglist enrolment plugin.
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin HUnt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/enrol/waitinglist/locallib.php');

/**
 * Event observer for enrol_meta.
 *
 * @package    enrol_meta
 * @copyright  2013 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_waitinglist_observer{

    /**
     * Triggered via user_enrolment_created event.
     *
     * @param \core\event\user_enrolment_created $event
     * @return bool true on success.
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {
        if (!enrol_is_enabled('waitinglist')) {
            return true;
        }

        if ($event->other['enrol'] != 'waitinglist') {
            return true;
        }

        $waitinglist = enrol_get_plugin('waitinglist');
		$waitinglist->handle_enrol($event->courseid, $event->relateduserid);
		return true;
    }

    /**
     * Description
     * Triggered via enrol_instance_updated
     *
     * @param       \core\event\enrol_instance_updated $event
     * @return      bool
     *
     * @creationDate    06/07/2017
     * @author          eFaktor     (fbv)
     */
    public static function enrol_instance_updated(\core\event\enrol_instance_updated $event) {

        $waitinglist = enrol_get_plugin('waitinglist');
        $waitinglist->handle_enrolupdated($event->courseid,$event->objectid);

        return true;

    }//enrol_instance_updated

    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param \core\event\user_enrolment_deleted $event
     * @return bool true on success.
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        if (!enrol_is_enabled('waitinglist')) {
            return true;
        }

        if ($event->other['enrol'] != 'waitinglist') {
            return true;
        }
        
        $waitinglist = enrol_get_plugin('waitinglist');
		$waitinglist->handle_unenrol($event->courseid, $event->relateduserid);
        return true;
    }

    
   

    /**
     * Triggered via course_deleted event.
     *
     * @param \core\event\course_deleted $event
     * @return bool true on success
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        if (!enrol_is_enabled('waitinglist')) {
            return true;
        }
        
        $waitinglist = enrol_get_plugin('waitinglist');
        $waitinglist->handle_coursedeleted($event->objectid);
        return true;
	}
}
