<?php
/**
 * Fellesdata Integration - Script UPGRADE installaton DB
 *
 * @package         local/fellesdata
 * @subpackage      db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    14/03/2016
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();


function xmldb_local_fellesdata_upgrade($oldVersion) {
    /* Variables */
    global $DB;
    $tblImpUsersJR  = null;
    $fldStillins    = null;
    /* Get Manager  */
    $dbMan = $DB->get_manager();


    try {

        if ($oldVersion < 2016031400) {
            /* Table        */
            $tblImpUsersJR = new xmldb_table('fs_imp_users_jr');

            /* New Field    */
            $fldStillins = new xmldb_field('stillingsnr', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'fodselsnr');
            if (!$dbMan->field_exists($tblImpUsersJR, $fldStillins)) {
                $dbMan->add_field($tblImpUsersJR, $fldStillins);
            }//if_not_exists
        }//if_oldVersion

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_Catch
}//xmldb_local_fellesdata_upgrade