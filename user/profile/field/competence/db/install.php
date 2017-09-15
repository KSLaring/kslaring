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
 *      - Move all the old information from rgcoompany and rgjobrole to the new table
 */

function xmldb_profilefield_competence_install() {
    /* Variables    */
    $field_id = null;

    try {
        // Create tables
        CompetenceProfile_Install::Create_CompetenceTable();
        // Create competence user profile
        $field_id   = CompetenceProfile_Install::Create_UserProfileCompetence();

    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_profilefield_competence_install

class CompetenceProfile_Install {
    /**
     * @throws          Exception
     *
     * @creationDate    27/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the mdl_user_info_competence table
     */
    public static function Create_CompetenceTable() {
        /* Variables    */
        global $DB;
        $tbllog = null;

        try {
            // Manager
            $dbman = $DB->get_manager();

            // Competence table
            if (!$dbman->table_exists('user_info_competence')) {
                // Table
                $table_competence = new xmldb_table('user_info_competence');

                // ID               -->     Primary Key. Autonumeric
                $table_competence->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // User Id          -->  Foreign Key to user
                $table_competence->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // Time modified    -->     The last changes
                $table_competence->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                // Primary key
                $table_competence->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Foreign key
                $table_competence->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

                $dbman->create_table($table_competence);
            }//if_table_exists_competence

            /* Info Competence Data */
            if (!$dbman->table_exists('user_info_competence_data')) {
                // Table
                $table_competence_data = new xmldb_table('user_info_competence_data');

                // ID               -->     Primary Key. Autonumeric
                $table_competence_data->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // Competence ID    -->     Primary Key. Foreign Key --> user_info_competence
                $table_competence_data->add_field('competenceid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // User Id          -->  Foreign Key to user
                $table_competence_data->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // Company      --> Company. Foreign Key to mdl_report_gen_company_data
                $table_competence_data->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // Level
                $table_competence_data->add_field('level',XMLDB_TYPE_INTEGER,'2',null, null, null,null);
                // Job Roles        -->     Long text. All the job roles connected to the user and the company
                $table_competence_data->add_field('jobroles',XMLDB_TYPE_TEXT,null,null, null, null,null);
                // Editable
                $table_competence_data->add_field('editable',XMLDB_TYPE_INTEGER,'2',null, null, null,null);
                // Approved
                $table_competence_data->add_field('approved',XMLDB_TYPE_INTEGER,'2',null, null, null,null);
                // Rejected
                $table_competence_data->add_field('rejected',XMLDB_TYPE_INTEGER,'2',null, null, null,null);
                // Token
                $table_competence_data->add_field('token',XMLDB_TYPE_CHAR,'100',null, null, null,null);
                // Time rejected
                $table_competence_data->add_field('timerejected',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
                // Time modified
                $table_competence_data->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                // Primary keys
                $table_competence_data->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Index / Foreign Key
                $table_competence_data->add_key('competenceid',XMLDB_KEY_FOREIGN,array('competenceid'), 'user_info_competence', array('id'));
                $table_competence_data->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

                $dbman->create_table($table_competence_data);
            }//if_table_exists_competence_data

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
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Create_CompetenceTable

    /**
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    27/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create User Profile Competence
     */
    public static function Create_UserProfileCompetence() {
        /* Variables    */
        global $DB;

        try {
            /* Instance user_info_field */
            $info = new stdClass();
            $info->shortname    = 'competence';
            $info->name         = 'Competence';
            $info->datatype     = 'competence';
            $info->categoryid   = 1;
            $info->required     = 0;
            $info->locked       = 0;
            $info->visible      = 1;
            $info->forceunique  = 0;
            $info->signup       = 0;

            $info->id = $DB->insert_record('user_info_field',$info);

            return $info->id;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Create_UserProfileCompetence
}//CompetenceProfile_Install