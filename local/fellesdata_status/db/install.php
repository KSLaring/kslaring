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

function xmldb_local_fellesdata_status_install() {
    /* Variables */
    global $DB;
    $tblcompetence = null;
    /* Get Manager  */
    $dbman = $DB->get_manager();

    try {
        /* Info Competence Data */
        if (!$dbman->table_exists('user_info_competence_data')) {
            /* Create Table */
            $tblcompetence = new xmldb_table('user_info_competence_data');
            // Id - Primary key
            $tblcompetence->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            // User id
            $tblcompetence->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            // Username
            $tblcompetence->add_field('username',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* Company      --> Company. Foreign Key to mdl_report_gen_company_data      */
            $tblcompetence->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* Level    */
            $tblcompetence->add_field('level',XMLDB_TYPE_INTEGER,'2',null, null, null,null);
            /* Job Roles        -->     Long text. All the job roles connected to the user and the company      */
            $tblcompetence->add_field('jobroles',XMLDB_TYPE_TEXT,null,null, null, null,null);
            /* Time modified    -->     The last changes    */
            $tblcompetence->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            /* Primary Keys         */
            $tblcompetence->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            $dbman->create_table($tblcompetence);
        }//if_table_exists_competence_data
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_local_fellesdata_status_install