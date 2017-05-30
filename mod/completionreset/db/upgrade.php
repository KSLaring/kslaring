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
 * Completion Reset Module upgrade code
 *
 * This file keeps track of upgrades to
 * the resource module
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package mod_completionreset
 * @copyright  2015 Justin Hunt (http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_completionreset_upgrade($oldversion) {
    global $CFG, $DB;
    $tblUsers = null;

    try {
        $dbman = $DB->get_manager();

        // Moodle v2.7.0 release upgrade line.
        // Put any upgrade step following this.

        // Add new table - completionreset_users
        if ($oldversion < 20170032800) {
            $tblUsers = new xmldb_table('completionreset_users');

            // Add fields
            // id           - Primary key
            $tblUsers->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            // resetid      - Foreign key to completionreset table
            $tblUsers->add_field('resetid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            // course       - Course id
            $tblUsers->add_field('course',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            // userid       - User id
            $tblUsers->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            // Keys
            $tblUsers->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblUsers->add_key('resetid',XMLDB_KEY_FOREIGN,array('resetid'), 'completionreset', array('id'));

            if (!$dbman->table_exists('completionreset_users')) {
                $dbman->create_table($tblUsers);
            }//if_not_exist
        }

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }
}
