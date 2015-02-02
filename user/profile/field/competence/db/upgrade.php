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

    try {
        /* Manager  */
        $db_man = $DB->get_manager();

        if ($old_version < 2015020102) {
            /* Competence Table */
            if ($db_man->table_exists('user_info_competence')) {
                $table_competence = new xmldb_table('user_info_competence');
                $db_man->drop_table($table_competence);

                /* Create Table */
                /* ID               -->     Primary Key. Autonumeric.   */
                $table_competence->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                /* User Id          -->  Foreign Key to user             */
                $table_competence->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

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

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_profilefield_competence_upgrade