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
 * Database upgrade steps
 *
 * @package   local_lessonexport
 * @copyright 2014 Davo Smith, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_lessonexport_upgrade($oldversion = 0) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2014102000) {
        // Define table local_lessonexport_order to be created.
        $table = new xmldb_table('local_lessonexport_order');

        // Adding fields to table local_lessonexport_order.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pageid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table local_lessonexport_order.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('cmid', XMLDB_KEY_FOREIGN, array('cmid'), 'course_modules', array('id'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->add_key('pageid', XMLDB_KEY_FOREIGN_UNIQUE, array('pageid'), 'lesson_pages', array('id'));

        // Conditionally launch create table for local_lessonexport_order.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // lessonexport savepoint reached.
        upgrade_plugin_savepoint(true, 2014102000, 'local', 'lessonexport');
    }

    if ($oldversion < 2014102004) {
        // Remove the 'sortorder' field added to 'lesson_pages' by previous versions of this plugin.
        $table = new xmldb_table('lesson_pages');
        $field = new xmldb_field('sortorder');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2014102004, 'local', 'lessonexport');
    }

    if ($oldversion < 2015071300) {

        // Define table lessonexport_queue to be created.
        $table = new xmldb_table('local_lessonexport_queue');

        // Adding fields to table lessonexport_queue.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        // $table->add_field('lessonid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('exportattempts', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table lessonexport_queue.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for lessonexport_queue.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // lessonexport savepoint reached.
        upgrade_plugin_savepoint(true, 2015071300, 'local', 'lessonexport');
    }

    return true;
}
