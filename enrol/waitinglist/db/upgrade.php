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
 * This file keeps track of upgrades to the waitinglist enrolment plugin
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt {@link http://poodll.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_waitinglist_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015021101) {
         // Add email alert field 
        $table = new xmldb_table('enrol_waitinglist_method');
        $field = new xmldb_field('emailalert', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'maxseats');

        // Launch add field selection.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Dataform savepoint reached.
        upgrade_plugin_savepoint(true, 2015021101, 'enrol','waitinglist');
    }
     if ($oldversion < 2015021601) {
         // Add allocated seats field
        $table = new xmldb_table('enrol_waitinglist_queue');
        $field = new xmldb_field('allocseats', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'seats');

        // Launch add field selection.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Dataform savepoint reached.
        upgrade_plugin_savepoint(true, 2015021601, 'enrol','waitinglist');
    }

    /**
     * @updateDate      23/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Move 'customint1 (cut_off_date)' value into 'enrolenddate'
     */
    if ($oldversion < 2015062300) {
        /* SQL Instruction  */
        $sql = " SELECT   e.id,
                          e.enrolenddate,
                          e.customint1
                 FROM 	  {enrol}	e
                 WHERE 	  e.enrol 	= 'waitinglist' ";

        /* Execute  */
        $rdo = $DB->get_records_sql($sql);
        if ($rdo) {
            foreach ($rdo as $instance) {
                if ($instance->customint1) {
                    $instance->enrolenddate = $instance->customint1;
                    $instance->customint1   = null;

                    /* Update   */
                    $DB->update_record('enrol',$instance);
                }
            }//for_each_instance
        }//if_Rdo
    }//if_old_Version

    return true;
}


