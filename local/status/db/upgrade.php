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
 * Fellesdata Status - Script UPGRADE installaton DB
 *
 * @package         local/fellesdata
 * @subpackage      db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    01/03/2017
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_status_upgrade($oldversion) {
    /* Variables */
    global $DB;
    $tblmanagers    = null;
    $tblreporters   = null;

    // Get manager
    $dbman = $DB->get_manager();
    
    if ($oldversion < 2017030100) {
        // Managers
        if (!$dbman->table_exists('user_managers')) {
            // create table
            $tblmanagers = new xmldb_table('user_managers');

            // Fields
            // Id - Primary key
            $tblmanagers->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            // User id
            $tblmanagers->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            // Username
            $tblmanagers->add_field('username',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            // Level one.
            $tblmanagers->add_field('levelone',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            // Level two.
            $tblmanagers->add_field('leveltwo',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            // Level three.
            $tblmanagers->add_field('levelthree',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            // Time modified
            $tblmanagers->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            // Keys
            $tblmanagers->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            $dbman->create_table($tblmanagers);
        }//if_managers

        // Reporters
        if (!$dbman->table_exists('user_reporters')) {
            // create table
            $tblreporters = new xmldb_table('user_reporters');

            // Fields
            // Id - Primary key
            $tblreporters->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            // User id
            $tblreporters->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            // Username
            $tblreporters->add_field('username',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            // Level one.
            $tblreporters->add_field('levelone',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            // Level two.
            $tblreporters->add_field('leveltwo',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            // Level three.
            $tblreporters->add_field('levelthree',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            // Time modified
            $tblreporters->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            // Keys
            $tblreporters->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            $dbman->create_table($tblreporters);
        }//if_reporters
    }//if_old_version

    if ($oldversion < 2017092004) {
        // Table - reporters
        $table   = new xmldb_table('user_reporters');

        // New field - levelone
        $fdl = new xmldb_field('levelone', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0,'username');
        if (!$dbman->field_exists($table, $fdl)) {
            $dbman->add_field($table, $fdl);
        }//if_not_exists
        // New field - key
        $fdl = new xmldb_field('keyid', XMLDB_TYPE_INTEGER, '10', null, null, null, null,'id');
        if (!$dbman->field_exists($table, $fdl)) {
            $dbman->add_field($table, $fdl);
        }//if_not_exists

        // Table - managers
        $table   = new xmldb_table('user_managers');

        // New field
        $fdl = new xmldb_field('levelone', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0,'username');
        if (!$dbman->field_exists($table, $fdl)) {
            $dbman->add_field($table, $fdl);
        }//if_not_exists
        // New field - key
        $fdl = new xmldb_field('keyid', XMLDB_TYPE_INTEGER, '10', null, null, null, null,'id');
        if (!$dbman->field_exists($table, $fdl)) {
            $dbman->add_field($table, $fdl);
        }//if_not_exists
    }


    if ($oldversion < 2017101014) {
        // Missing indexes
        $tbl        = new xmldb_table('user_managers');
        $tblrepo    = new xmldb_table('user_reporters');
        // levelone
        $index = new xmldb_index('levelone', XMLDB_INDEX_NOTUNIQUE, array('levelone'));
        if (!$dbman->index_exists($tbl, $index)) {
            $dbman->add_index($tbl, $index);
        }
        if (!$dbman->index_exists($tblrepo, $index)) {
            $dbman->add_index($tblrepo, $index);
        }
        // leveltwo
        $index = new xmldb_index('leveltwo', XMLDB_INDEX_NOTUNIQUE, array('leveltwo'));
        if (!$dbman->index_exists($tbl, $index)) {
            $dbman->add_index($tbl, $index);
        }
        if (!$dbman->index_exists($tblrepo, $index)) {
            $dbman->add_index($tblrepo, $index);
        }
        // levelthree
        $index = new xmldb_index('levelthree', XMLDB_INDEX_NOTUNIQUE, array('levelthree'));
        if (!$dbman->index_exists($tbl, $index)) {
            $dbman->add_index($tbl, $index);
        }
        if (!$dbman->index_exists($tblrepo, $index)) {
            $dbman->add_index($tblrepo, $index);
        }

        /**
        // Missing indes
        $tbl        = new xmldb_table('user_info_competence_data');
        // companyid
        $index = new xmldb_index('companyid', XMLDB_INDEX_NOTUNIQUE, array('companyid'));
        if (!$dbman->index_exists($tbl, $index)) {
            $dbman->add_index($tbl, $index);
        }
        // username
        $index = new xmldb_index('username', XMLDB_INDEX_NOTUNIQUE, array('username'));
        if (!$dbman->index_exists($tbl, $index)) {
            $dbman->add_index($tbl, $index);
        } **/

        if (!$dbman->table_exists('fs_status_log')) {
            $tbl = new xmldb_table('fs_status_log');

            // Fields
            // Id --> primary key
            $tbl->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            // action
            $tbl->add_field('action',XMLDB_TYPE_CHAR,'250',null, null, null,null);
            // description
            $tbl->add_field('description',XMLDB_TYPE_TEXT,null,null, null, null,null);
            // completion
            $tbl->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            // Adding keys, index, foreing keys
            $tbl->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            ///$tbl->add_index('timecreated',XMLDB_INDEX_NOTUNIQUE,array('timecreated'));
            ///$tbl->add_index('action',XMLDB_INDEX_NOTUNIQUE,array('action'));

            // Crete table
            $dbman->create_table($tbl);

            // Competence data - log
            if (!$dbman->table_exists('user_info_compe_data_log')) {
                // create table
                $tblcompetence = new xmldb_table('user_info_compe_data_log');

                // Fields
                // Id - Primary key
                $tblcompetence->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // User id
                $tblcompetence->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // Username
                $tblcompetence->add_field('username',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // Company      --> Company.
                $tblcompetence->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // Level
                $tblcompetence->add_field('level',XMLDB_TYPE_INTEGER,'2',null, null, null,null);
                // Job roles
                $tblcompetence->add_field('jobroles',XMLDB_TYPE_TEXT,null,null, null, null,null);
                // Time modified
                $tblcompetence->add_field('timereceived',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                // Keys
                $tblcompetence->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Index
                //$tblcompetence->add_index('companyid',XMLDB_INDEX_NOTUNIQUE,array('companyid'));
                //$tblcompetence->add_index('username',XMLDB_INDEX_NOTUNIQUE,array('username'));
                //$tblcompetence->add_index('timereceived',XMLDB_INDEX_NOTUNIQUE,array('timereceived'));

                $dbman->create_table($tblcompetence);
            }//if_table_exists_competence_data

            // Managers - log/historical
            if (!$dbman->table_exists('user_managers_log')) {
                // create table
                $tblmanagers = new xmldb_table('user_managers_log');

                // Fields
                // Id - Primary key
                $tblmanagers->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                //key
                $tblmanagers->add_field('keyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // User id
                $tblmanagers->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // Username
                $tblmanagers->add_field('username',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // Level one.
                $tblmanagers->add_field('levelone',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // Level two.
                $tblmanagers->add_field('leveltwo',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // Level three.
                $tblmanagers->add_field('levelthree',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // Time modified
                $tblmanagers->add_field('timereceived',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                // Keys
                $tblmanagers->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Index
                $tblmanagers->add_index('levelone',XMLDB_INDEX_NOTUNIQUE,array('levelone'));
                $tblmanagers->add_index('leveltwo',XMLDB_INDEX_NOTUNIQUE,array('leveltwo'));
                $tblmanagers->add_index('levelthree',XMLDB_INDEX_NOTUNIQUE,array('levelthree'));
                $tblmanagers->add_index('timereceived',XMLDB_INDEX_NOTUNIQUE,array('timereceived'));

                $dbman->create_table($tblmanagers);
            }//if_managers_log

            // Reporters - log/historical
            if (!$dbman->table_exists('user_reporters_log')) {
                // create table
                $tblreporters = new xmldb_table('user_reporters_log');

                // Fields
                // Id - Primary key
                $tblreporters->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                //key
                $tblreporters->add_field('keyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // User id
                $tblreporters->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // Username
                $tblreporters->add_field('username',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // Level one.
                $tblreporters->add_field('levelone',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // Level two.
                $tblreporters->add_field('leveltwo',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // Level three.
                $tblreporters->add_field('levelthree',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // Time modified
                $tblreporters->add_field('timereceived',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                // Keys
                $tblreporters->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Index
                $tblreporters->add_index('levelone',XMLDB_INDEX_NOTUNIQUE,array('levelone'));
                $tblreporters->add_index('leveltwo',XMLDB_INDEX_NOTUNIQUE,array('leveltwo'));
                $tblreporters->add_index('levelthree',XMLDB_INDEX_NOTUNIQUE,array('levelthree'));
                $tblreporters->add_index('timereceived',XMLDB_INDEX_NOTUNIQUE,array('timereceived'));

                $dbman->create_table($tblreporters);
            }//if_reporters_log
        }
    }
    return true;
}//xmldb_local_fellesdata_status_upgrade