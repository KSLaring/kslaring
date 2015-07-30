<?php
//This file is part of Moodle - http://moodle.org/.
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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local
 * @subpackage cmenu
 * @copyright 2013 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Creates table and its fields in the database
 */
 
function xmldb_local_centre_upgrade($oldversion=0) {
    global $CFG, $DB;
	
	$dbman = $DB->get_manager(); 
    $result = TRUE;

    if ($oldversion < 2013042308) {
        // Define table cmenu to be created
        $table = new xmldb_table('bulkemails');

        // Adding fields to table cmenu
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('email', XMLDB_TYPE_TEXT, '255', null, XMLDB_NOTNULL, null, null);
		$table->add_field('from', XMLDB_TYPE_TEXT, '255', null, XMLDB_NOTNULL, null, null);
		$table->add_field('to', XMLDB_TYPE_TEXT, '255', null, XMLDB_NOTNULL, null, null);
		$table->add_field('subject', XMLDB_TYPE_TEXT, '255', null, XMLDB_NOTNULL, null, null);
		$table->add_field('message', XMLDB_TYPE_TEXT, '255', null, XMLDB_NOTNULL, null, null);
		$table->add_field('timestart', XMLDB_TYPE_TEXT, '255', null, XMLDB_NOTNULL, null, null);
		$table->add_field('timeend', XMLDB_TYPE_TEXT, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('created', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
		

        // Adding keys to table cmenu
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for cmenu
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // cmenu savepoint reached
        upgrade_plugin_savepoint(true, 2013042308, 'local', 'bulkemails');
    } 
    return $result;
}
?>