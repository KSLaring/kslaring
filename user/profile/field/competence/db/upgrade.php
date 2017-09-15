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
 *  Post-install script for Competence extra user profield.
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/comptence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 *      - Create a new table to save the companies and job roles connected to the user
 *      - Update user_info_competence
 */

function xmldb_profilefield_competence_upgrade($oldversion) {
    /* Variables    */
    global $DB;
    $fieldLevel         = null;
    $fieldEditable      = null;
    $fieldApproved      = null;
    $fieldRejected      = null;
    $fieldToken         = null;
    $fieldTimeReject    = null;

    try {
        // Manager
        $dbman = $DB->get_manager();

        if ($oldversion < 2015020102) {
            /* Competence Table */
            if (!$dbman->table_exists('user_info_competence')) {
                $table_competence = new xmldb_table('user_info_competence');
                /* Create Table */
                /* ID               -->     Primary Key. Autonumeric.   */
                $table_competence->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                /* User Id          -->  Foreign Key to user             */
                $table_competence->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                /* Time modified    -->     The last changes    */
                $table_competence->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                /* Primary Keys         */
                $table_competence->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                /* Index / Foreign Key  */
                $table_competence->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

                $dbman->create_table($table_competence);
            }//if_table_exists_competence

            /* Info Competence Data */
            if (!$dbman->table_exists('user_info_competence_data')) {
                /* Create Table */
                $table_competence_data = new xmldb_table('user_info_competence_data');
                /* ID               -->     Primary Key. Autonumeric.   */
                $table_competence_data->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                /* Competence ID    -->     Primary Key. Foreign Key --> user_info_competence.   */
                $table_competence_data->add_field('competenceid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                /* User Id          -->  Foreign Key to user             */
                $table_competence_data->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                /* Company      --> Company. Foreign Key to mdl_report_gen_company_data      */
                $table_competence_data->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                /* Job Roles        -->     Long text. All the job roles connected to the user and the company      */
                $table_competence_data->add_field('jobroles',XMLDB_TYPE_TEXT,null,null, null, null,null);
                /* Time modified    -->     The last changes    */
                $table_competence_data->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                /* Primary Keys         */
                $table_competence_data->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                /* Index / Foreign Key  */
                $table_competence_data->add_key('competenceid',XMLDB_KEY_FOREIGN,array('competenceid'), 'user_info_competence', array('id'));
                $table_competence_data->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

                $dbman->create_table($table_competence_data);
            }//if_table_exists_competence_data
        }//if_old_version

        if ($oldversion < 2015100900) {
            if ($dbman->table_exists('user_info_competence')) {
                /* New Fields   */
                $tblCompetenceData = new xmldb_table('user_info_competence');
                $fieldTimeModified = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null,null,'userid');
                if (!$dbman->field_exists($tblCompetenceData, $fieldTimeModified)) {
                    $dbman->add_field($tblCompetenceData, $fieldTimeModified);
                }//if_exists
            }
        }//if_old_version

        if ($oldversion <2016012602) {
            if ($dbman->table_exists('user_info_competence_data')) {
                $tblCompetenceData = new xmldb_table('user_info_competence_data');

                /* New Fields   */
                /* Level    */
                $fieldLevel = new xmldb_field('level', XMLDB_TYPE_INTEGER, '2', null, null, null,null,'companyid');
                if (!$dbman->field_exists($tblCompetenceData, $fieldLevel)) {
                    $dbman->add_field($tblCompetenceData, $fieldLevel);
                }//if_exists_level

                /* Editable */
                $fieldEditable = new xmldb_field('editable', XMLDB_TYPE_INTEGER, '2', null, null, null,null,'jobroles');
                if (!$dbman->field_exists($tblCompetenceData, $fieldEditable)) {
                    $dbman->add_field($tblCompetenceData, $fieldEditable);
                }//if_exists_editable

                /* Approved */
                $fieldApproved = new xmldb_field('approved', XMLDB_TYPE_INTEGER, '2', null, null, null,null,'editable');
                if (!$dbman->field_exists($tblCompetenceData, $fieldApproved)) {
                    $dbman->add_field($tblCompetenceData, $fieldApproved);
                }//if_exists_approved

                /* Rejected */
                $fieldRejected = new xmldb_field('rejected', XMLDB_TYPE_INTEGER, '2', null, null, null,null,'approved');
                if (!$dbman->field_exists($tblCompetenceData, $fieldRejected)) {
                    $dbman->add_field($tblCompetenceData, $fieldRejected);
                }//if_exists_rejected

                /* Update the present users level = 3   */
                $rdo = $DB->get_records('user_info_competence_data',null,'','id,level');
                foreach ($rdo as $instance) {
                    $instance->level = 3;
                    /* Update */
                    $DB->update_record('user_info_competence_data',$instance);
                }
                
                $rdo = $DB->get_records('user_info_competence_data',null,'id','id,editable');
                foreach ($rdo as $instance) {
                    $instance->editable = 1;
                    $DB->update_record('user_info_competence_data',$instance);
                }
            }
        }//if_old_version_2016012600

        if ($oldversion < 2016022602) {
            if ($dbman->table_exists('user_info_competence_data')) {
                $tblCompetenceData = new xmldb_table('user_info_competence_data');

                $rdo = $DB->get_records('user_info_competence_data',null,'id','id,approved');
                foreach ($rdo as $instance) {
                    $instance->approved = 1;
                    $DB->update_record('user_info_competence_data',$instance);
                }

                /* Token */
                $fieldToken = new xmldb_field('token', XMLDB_TYPE_CHAR, '100', null, null, null,null,'rejected');
                if (!$dbman->field_exists($tblCompetenceData, $fieldToken)) {
                    $dbman->add_field($tblCompetenceData, $fieldToken);
                }//if_exists_token

                /* Time Rejected    */
                $fieldTimeReject = new xmldb_field('timerejected', XMLDB_TYPE_INTEGER, '10', null, null, null,null,'token');
                if (!$dbman->field_exists($tblCompetenceData, $fieldTimeReject)) {
                    $dbman->add_field($tblCompetenceData, $fieldTimeReject);
                }//if_exists_timerejected
            }//if_Exists
        }//if_old_version

        if ($oldversion < 2017091500) {
            // Competence log
            if (!$dbman->table_exists('user_info_competence_log')) {
                // Table
                $tbllog = new xmldb_table('user_info_competence_log');

                // id -- primary key
                $tbllog->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // managerid
                $tbllog->add_field('managerid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // companyid
                $tbllog->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // userid
                $tbllog->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // action
                $tbllog->add_field('action',XMLDB_TYPE_INTEGER,'2',null, null, null,null);
                // confirmed
                $tbllog->add_field('confirmed',XMLDB_TYPE_INTEGER,'2',null, null, null,null);
                // timecreated
                $tbllog->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

                // Keys
                $tbllog->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Adding indexes
                $tbllog->add_index('ix_manager',XMLDB_INDEX_NOTUNIQUE,array('managerid'));
                $tbllog->add_index('ix_company',XMLDB_INDEX_NOTUNIQUE,array('companyid'));
                $tbllog->add_index('ix_confirm',XMLDB_INDEX_NOTUNIQUE,array('confirmed'));

                // Create table
                $dbman->create_table($tbllog);
            }//if_competence_log
        }//if_oldversion

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_profilefield_competence_upgrade