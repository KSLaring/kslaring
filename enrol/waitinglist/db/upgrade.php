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


/**
 * @updateDate      28/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add an extra field to know if it will be required invoice information
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_waitinglist_upgrade($oldversion) {
    /* Variables    */
    global $DB,$CFG;
    $sql                = null;
    $rdo                = null;
    $managers           = null;
    $dbman              = $DB->get_manager();
    $tblWaitingLst      = null;
    $tblApproval        = null;
    $tblApprovalAction  = null;
    $table              = null;
    $field              = null;
    $fldEnrolEnd        = null;

    try {
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

        if ($oldversion < 2015122400) {
            /* enrol_approval table         */
            $tblApproval = new xmldb_table('enrol_approval');
            /* Add Fields */
            /* Id       -- Primary Key                                  */
            $tblApproval->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* userid   -- Foreign key. User Id                         */
            $tblApproval->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* courseid -- Foreign key. Course Id                       */
            $tblApproval->add_field('courseid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* token    -- Not null. unique                             */
            $tblApproval->add_field('token',XMLDB_TYPE_CHAR,'100',null, XMLDB_NOTNULL, null,null);
            /* userenrolid      -- Foreign key. User enrolment id       */
            $tblApproval->add_field('userenrolid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* waitinglistid    -- Foreign key. Waiting enrolment id    */
            $tblApproval->add_field('waitinglistid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* methodtype       -- Self or unnamedbulk.                 */
            $tblApproval->add_field('methodtype',XMLDB_TYPE_CHAR,'25',null, XMLDB_NOTNULL, null,null);
            /* arguments        -- Not Null                             */
            $tblApproval->add_field('arguments',XMLDB_TYPE_TEXT,null,null,XMLDB_NOTNULL,null,null);
            /* Seats */
            $tblApproval->add_field('seats',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* approved         -- Not null                             */
            $tblApproval->add_field('approved',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,0);
            /* rejected         -- Not Null                             */
            $tblApproval->add_field('rejected',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,0);
            /* onwait           -- Not Null                             */
            $tblApproval->add_field('onwait',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,0);
            /* unenrol          -- Not Null                             */
            $tblApproval->add_field('unenrol',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,0);
            /* timecreated      -- Not Null                             */
            $tblApproval->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* timesent         --                                      */
            $tblApproval->add_field('timesent',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* timeremainder    --                                      */
            $tblApproval->add_field('timereminder',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* timemodified                                             */
            $tblApproval->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

            //Adding Keys
            $tblApproval->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            //Adding Index
            $tblApproval->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'),'user', array('id'));
            $tblApproval->add_key('courseid',XMLDB_KEY_FOREIGN,array('courseid'),'course', array('id'));

            if (!$dbman->table_exists('enrol_approval')) {
                $dbman->create_table($tblApproval);
            }//if_table_exists

            /* enrol_approval_action table  */
            $tblApprovalAction = new xmldb_table('enrol_approval_action');
            /* Add Fields */
            /* Id           -- Primary Key.                     */
            $tblApprovalAction->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* approvalid   -- Foreign Key. Enrol approval id   */
            $tblApprovalAction->add_field('approvalid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* token        -- Not Null                         */
            $tblApprovalAction->add_field('token',XMLDB_TYPE_CHAR,'100',null, XMLDB_NOTNULL, null,null);
            /* action       -- Not Null. Integer                */
            $tblApprovalAction->add_field('action',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,0);

            //Adding Keys
            $tblApprovalAction->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            //Adding Index
            $tblApprovalAction->add_key('approvalid',XMLDB_KEY_FOREIGN,array('approvalid'),'enrol_approval', array('id'));

            if (!$dbman->table_exists('enrol_approval_action')) {
                $dbman->create_table($tblApprovalAction);
            }//if_table_exists
        }//if_oldVersion

        if ($oldversion < 2017021204) {
            // Add company field
            $table = new xmldb_table('enrol_waitinglist_queue');
            $field = new xmldb_field('companyid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0', 'userid');

            // Launch add field selection.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            // Dataform savepoint reached.
            //upgrade_plugin_savepoint(true, 2016091200, 'enrol','waitinglist');
        }//if_old_version

        if ($oldversion < 2017021204) {
            // Add company field
            $table = new xmldb_table('enrol_approval');
            $field = new xmldb_field('companyid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0', 'userid');

            // Launch add field selection.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            // Dataform savepoint reached.
            //upgrade_plugin_savepoint(true, 2016091202, 'enrol','waitinglist');
        }//if_old_version

        if ($oldversion < 2017021204) {
            // Add company field
            $table = new xmldb_table('enrol_invoice');
            $field = new xmldb_field('companyid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0', 'userid');

            // Launch add field selection.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            // Dataform savepoint reached.
            //upgrade_plugin_savepoint(true, 2016091400, 'enrol','waitinglist');
        }//if_old_version

        if ($oldversion < 2017021204) {
            /* New Table    */
            $tblUnenrol = new xmldb_table('enrol_waitinglist_unenrol');

            /* Fields   */
            /* Id               -- Primary Key  */
            $tblUnenrol->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* Waitinglist id   -- Foreign Key  */
            $tblUnenrol->add_field('waitingid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* Course id        -- Foreign Key  */
            $tblUnenrol->add_field('courseid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* Token Course                     */
            $tblUnenrol->add_field('tokenco',XMLDB_TYPE_CHAR,'100',null, XMLDB_NOTNULL, null,null);
            /* User Id          -- Foreign Key  */
            $tblUnenrol->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* Token User                       */
            $tblUnenrol->add_field('tokenus',XMLDB_TYPE_CHAR,'100',null, XMLDB_NOTNULL, null,null);
            /* Time created                     */
            $tblUnenrol->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblUnenrol->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            //Adding Index
            $tblUnenrol->add_key('waitingid',XMLDB_KEY_FOREIGN,array('waitingid'),'enrol', array('id'));
            $tblUnenrol->add_key('courseid',XMLDB_KEY_FOREIGN,array('courseid'),'course', array('id'));
            $tblUnenrol->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'),'user', array('id'));

            if (!$dbman->table_exists('enrol_waitinglist_unenrol')) {
                $dbman->create_table($tblUnenrol);
            }//if_table_exists
        }

        if ($oldversion < 2017021204) {
            $table       = new xmldb_table('enrol_waitinglist_method');
            $fldEnrolEnd = new xmldb_field('unenrolenddate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'status');

            if (!$dbman->field_exists($table, $fldEnrolEnd)) {
                $dbman->add_field($table, $fldEnrolEnd);
            }
        }//if_odlVersion

        // Create enrol_approval_approvers
        if ($oldversion < 2017050800) {
            $table       = new xmldb_table('enrol_approval_approvers');

            if (!$dbman->table_exists('enrol_approval_approvers')) {
                // Fields
                // Primary Key
                $table->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // Foreign key to enrol_approval table
                $table->add_field('approvalid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // Manager id
                $table->add_field('managerid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // Action. 0 --> None action. 1 --> Approve action. 2 --> Reject action
                $table->add_field('action',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
                // token
                $table->add_field('token',XMLDB_TYPE_CHAR,'100',null, XMLDB_NOTNULL, null,null);
                // timecreated
                $table->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // timeupdate
                $table->add_field('timeupdated',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

                // Keys
                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Adding indexes
                $table->add_key('approvalid',XMLDB_KEY_FOREIGN,array('approvalid'),'enrol_approval', array('id'));

                $dbman->create_table($table);
            }//if_table_exists

            // Add entries for old version
            require_once($CFG->dirroot . '/enrol/waitinglist/approval/approvallib.php');

            // Get all entries from old version
            $sql = " SELECT	DISTINCT 
                              ea.id,
                              ea.userid,
                              ea.courseid,
                              ea.companyid,
                              ea.waitinglistid
                     FROM	{enrol_approval}  ea
                     WHERE	ea.approved = 0
                        AND	ea.rejected = 0 ";

            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                // Create entries - Sen reminders
                foreach ($rdo as $instance) {
                    $managers = Approval::managers_connected($instance->userid,$instance->companyid);
                    // Create entries
                    $entries = Approval::add_approval_entry_manager($managers,$instance->id,$instance->courseid);

                    // Send reminders
                    $user = get_complete_user_data('id',$instance->userid);
                    $remainder          = \Approval::get_notification_sent($instance->userid,$instance->courseid);
                    \Approval::send_reminder($user,$remainder,$instance->waitinglistid,$managers);
                }
            }//if_rdo
        }//if_odl_version_2017050800

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}




