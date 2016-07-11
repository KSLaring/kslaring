<?php
/**
 * Participants List  - Install script
 *
 * @package         local
 * @subpackage      participants/db
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    06/07/2016
 * @author          eFaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_participants_install() {
    /* Variables    */
    global $DB;
    $tblParticipants    = null;
    $dbMan              = null;

    try {
        /* DB Manager */
        $dbMan = $DB->get_manager();

        /* table -- mdl_course_attendance */
        if (!$dbMan->table_exists('course_attendance')) {
            /* Create Table */
            $tblParticipants = new xmldb_table('course_attendance');

            /* Fields       */
            /* Id               -- Primary key                  */
            $tblParticipants->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* courseid         -- Foreign key - course table   */
            $tblParticipants->add_field('courseid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* userid           -- Foreign Key - user table     */
            $tblParticipants->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* attendacedate    -- Not Null.                    */
            $tblParticipants->add_field('attendacedate',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* ticket by       */
            $tblParticipants->add_field('ticketby',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblParticipants->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblParticipants->add_key('courseid',XMLDB_KEY_FOREIGN,array('courseid'), 'course', array('id'));
            $tblParticipants->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

            /* Create Table */
            $dbMan->create_table($tblParticipants);
        }//if_exists
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_local_participants_install