<?php
/**
 * Fellesdata Status Integration - Script installaton DB
 *
 * @package         local/fellesdata_status
 * @subpackage      db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    01/02/2017
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_status_install() {
    /* Variables */
    global $DB;
    $tblcompetence  = null;
    $tblmanagers    = null;
    $tblreporters   = null;

    // Get manager
    $dbman = $DB->get_manager();

    try {
        // Competence data
        if (!$dbman->table_exists('user_info_competence_data')) {
            // create table
            $tblcompetence = new xmldb_table('user_info_competence_data');

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
            $tblcompetence->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            // Keys
            $tblcompetence->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            $dbman->create_table($tblcompetence);
        }//if_table_exists_competence_data

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
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_local_fellesdata_status_install