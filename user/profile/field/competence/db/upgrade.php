<?php
/**
 *  Post-install script for Competence extra user profield.
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/comptence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 *      - Create a new table to save the companies and job roles connected to the user
 *      - Update user_info_competence
 */

function xmldb_profilefield_competence_upgrade($old_version) {
    /* Variables    */
    global $DB;
    $fieldLevel         = null;
    $fieldEditable      = null;
    $fieldApproved      = null;
    $fieldRejected      = null;
    $fieldToken         = null;
    $fieldTimeReject    = null;

    try {
        /* Manager  */
        $db_man = $DB->get_manager();

        if ($old_version < 2015020102) {
            /* Competence Table */
            if (!$db_man->table_exists('user_info_competence')) {
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

                $db_man->create_table($table_competence);
            }//if_table_exists_competence

            /* Info Competence Data */
            if (!$db_man->table_exists('user_info_competence_data')) {
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

                $db_man->create_table($table_competence_data);
            }//if_table_exists_competence_data
        }//if_old_version

        if ($old_version < 2015100900) {
            if ($db_man->table_exists('user_info_competence')) {
                /* New Fields   */
                $tblCompetenceData = new xmldb_table('user_info_competence');
                $fieldTimeModified = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null,null,'userid');
                if (!$db_man->field_exists($tblCompetenceData, $fieldTimeModified)) {
                    $db_man->add_field($tblCompetenceData, $fieldTimeModified);
                }//if_exists
            }
        }//if_old_version

        if ($old_version <2016012602) {
            if ($db_man->table_exists('user_info_competence_data')) {
                $tblCompetenceData = new xmldb_table('user_info_competence_data');

                /* New Fields   */
                /* Level    */
                $fieldLevel = new xmldb_field('level', XMLDB_TYPE_INTEGER, '2', null, null, null,null,'companyid');
                if (!$db_man->field_exists($tblCompetenceData, $fieldLevel)) {
                    $db_man->add_field($tblCompetenceData, $fieldLevel);
                }//if_exists_level

                /* Editable */
                $fieldEditable = new xmldb_field('editable', XMLDB_TYPE_INTEGER, '2', null, null, null,null,'jobroles');
                if (!$db_man->field_exists($tblCompetenceData, $fieldEditable)) {
                    $db_man->add_field($tblCompetenceData, $fieldEditable);
                }//if_exists_editable

                /* Approved */
                $fieldApproved = new xmldb_field('approved', XMLDB_TYPE_INTEGER, '2', null, null, null,null,'editable');
                if (!$db_man->field_exists($tblCompetenceData, $fieldApproved)) {
                    $db_man->add_field($tblCompetenceData, $fieldApproved);
                }//if_exists_approved

                /* Rejected */
                $fieldRejected = new xmldb_field('rejected', XMLDB_TYPE_INTEGER, '2', null, null, null,null,'approved');
                if (!$db_man->field_exists($tblCompetenceData, $fieldRejected)) {
                    $db_man->add_field($tblCompetenceData, $fieldRejected);
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

        if ($old_version < 2016022602) {
            if ($db_man->table_exists('user_info_competence_data')) {
                $tblCompetenceData = new xmldb_table('user_info_competence_data');

                $rdo = $DB->get_records('user_info_competence_data',null,'id','id,approved');
                foreach ($rdo as $instance) {
                    $instance->approved = 1;
                    $DB->update_record('user_info_competence_data',$instance);
                }

                /* Token */
                $fieldToken = new xmldb_field('token', XMLDB_TYPE_CHAR, '100', null, null, null,null,'rejected');
                if (!$db_man->field_exists($tblCompetenceData, $fieldToken)) {
                    $db_man->add_field($tblCompetenceData, $fieldToken);
                }//if_exists_token

                /* Time Rejected    */
                $fieldTimeReject = new xmldb_field('timerejected', XMLDB_TYPE_INTEGER, '10', null, null, null,null,'token');
                if (!$db_man->field_exists($tblCompetenceData, $fieldTimeReject)) {
                    $db_man->add_field($tblCompetenceData, $fieldTimeReject);
                }//if_exists_timerejected
            }//if_Exists
        }//if_old_version

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_profilefield_competence_upgrade