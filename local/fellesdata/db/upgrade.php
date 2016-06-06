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
    /* Imp User JR  */
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

        if ($oldVersion < 2016060600) {
            Fellesdata_Update::Update_FSImpCompany($dbMan);
            Fellesdata_Update::Update_FSCompany($dbMan);
        }//id_oflVersion
        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_Catch
}//xmldb_local_fellesdata_upgrade

class Fellesdata_Update {
    /**
     * @param           $dbMan
     * @throws          Exception
     *
     * @creationDate    06/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Invoice data
     */
    public static function Update_FSImpCompany($dbMan) {
        /* Variables    */
        /* Imp Comapny  */
        $tblFSImpComp   = null;
        $fldPrivate     = null;
        $fldAnsvar      = null;
        $fldTjeneste    = null;
        $fldAdreseOne   = null;
        $fldAdreseTwo   = null;
        $fldAdreseThree = null;
        $fldPostnr      = null;
        $fldPoststed    = null;
        $fldEPost       = null;

        try {
            /* mdl_fs_imp_company  */
            $tblFSImpComp = new xmldb_table('fs_imp_company');

            /* Private Filed    */
            $fldPrivate     = null;
            $fldPrivate     = new xmldb_field('private', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'org_enhet_over');
            if (!$dbMan->field_exists($tblFSImpComp, $fldPrivate)) {
                $dbMan->add_field($tblFSImpComp, $fldPrivate);
            }//if_not_exists

            /* Ansvar Field     */
            $fldAnsvar      = null;
            $fldAnsvar      = new xmldb_field('ansvar', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'private');
            if (!$dbMan->field_exists($tblFSImpComp, $fldAnsvar)) {
                $dbMan->add_field($tblFSImpComp, $fldAnsvar);
            }//if_not_exists

            /* Tjeneste Field   */
            $fldTjeneste    = null;
            $fldTjeneste    = new xmldb_field('tjeneste', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'ansvar');
            if (!$dbMan->field_exists($tblFSImpComp, $fldTjeneste)) {
                $dbMan->add_field($tblFSImpComp, $fldTjeneste);
            }//if_not_exists

            /* Adresse 1        */
            $fldAdreseOne   = null;
            $fldAdreseOne   = new xmldb_field('adresse1', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'tjeneste');
            if (!$dbMan->field_exists($tblFSImpComp, $fldAdreseOne)) {
                $dbMan->add_field($tblFSImpComp, $fldAdreseOne);
            }//if_not_exists

            /* Adresse 2        */
            $fldAdreseTwo   = null;
            $fldAdreseTwo   = new xmldb_field('adresse2', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'adresse1');
            if (!$dbMan->field_exists($tblFSImpComp, $fldAdreseTwo)) {
                $dbMan->add_field($tblFSImpComp, $fldAdreseTwo);
            }//if_not_exists

            /* Adresse 3        */
            $fldAdreseThree = null;
            $fldAdreseThree = new xmldb_field('adresse3', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'adresse2');
            if (!$dbMan->field_exists($tblFSImpComp, $fldAdreseThree)) {
                $dbMan->add_field($tblFSImpComp, $fldAdreseThree);
            }//if_not_exists

            /* Post Number      */
            $fldPostnr      = null;
            $fldPostnr      = new xmldb_field('postnr', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'adresse3');
            if (!$dbMan->field_exists($tblFSImpComp, $fldPostnr)) {
                $dbMan->add_field($tblFSImpComp, $fldPostnr);
            }//if_not_exists

            /* Post sted        */
            $fldPoststed    = null;
            $fldPoststed    = new xmldb_field('poststed', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'postnr');
            if (!$dbMan->field_exists($tblFSImpComp, $fldPoststed)) {
                $dbMan->add_field($tblFSImpComp, $fldPoststed);
            }//if_not_exists

            /* ePost            */
            $fldEPost       = null;
            $fldEPost       = new xmldb_field('epost', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'poststed');
            if (!$dbMan->field_exists($tblFSImpComp, $fldEPost)) {
                $dbMan->add_field($tblFSImpComp, $fldEPost);
            }//if_not_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Update_FSImpCompany

    /**
     * @param           $dbMan
     * @throws          Exception
     *
     * @creationDate    06/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add invoice data
     */
    public static function Update_FSCompany($dbMan) {
        /* Variables    */
        $tblFSCompany   = null;
        $fldPrivate     = null;
        $fldAnsvar      = null;
        $fldTjeneste    = null;
        $fldAdreseOne   = null;
        $fldAdreseTwo   = null;
        $fldAdreseThree = null;
        $fldPostnr      = null;
        $fldPoststed    = null;
        $fldEPost       = null;

        try {
            /* mdl_fs_company           */
            $tblFSCompany       = new xmldb_table('fs_company');

            /* Private Filed    */
            $fldPrivate     = null;
            $fldPrivate     = new xmldb_field('private', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'parent');
            if (!$dbMan->field_exists($tblFSCompany, $fldPrivate)) {
                $dbMan->add_field($tblFSCompany, $fldPrivate);
            }//if_not_exists

            /* Ansvar Field     */
            $fldAnsvar      = null;
            $fldAnsvar      = new xmldb_field('ansvar', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'private');
            if (!$dbMan->field_exists($tblFSCompany, $fldAnsvar)) {
                $dbMan->add_field($tblFSCompany, $fldAnsvar);
            }//if_not_exists

            /* Tjeneste Field   */
            $fldTjeneste    = null;
            $fldTjeneste    = new xmldb_field('tjeneste', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'ansvar');
            if (!$dbMan->field_exists($tblFSCompany, $fldTjeneste)) {
                $dbMan->add_field($tblFSCompany, $fldTjeneste);
            }//if_not_exists

            /* Adresse 1        */
            $fldAdreseOne   = null;
            $fldAdreseOne   = new xmldb_field('adresse1', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'tjeneste');
            if (!$dbMan->field_exists($tblFSCompany, $fldAdreseOne)) {
                $dbMan->add_field($tblFSCompany, $fldAdreseOne);
            }//if_not_exists

            /* Adresse 2        */
            $fldAdreseTwo   = null;
            $fldAdreseTwo   = new xmldb_field('adresse2', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'adresse1');
            if (!$dbMan->field_exists($tblFSCompany, $fldAdreseTwo)) {
                $dbMan->add_field($tblFSCompany, $fldAdreseTwo);
            }//if_not_exists

            /* Adresse 3        */
            $fldAdreseThree = null;
            $fldAdreseThree = new xmldb_field('adresse3', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'adresse2');
            if (!$dbMan->field_exists($tblFSCompany, $fldAdreseThree)) {
                $dbMan->add_field($tblFSCompany, $fldAdreseThree);
            }//if_not_exists

            /* Post Number      */
            $fldPostnr      = null;
            $fldPostnr      = new xmldb_field('postnr', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'adresse3');
            if (!$dbMan->field_exists($tblFSCompany, $fldPostnr)) {
                $dbMan->add_field($tblFSCompany, $fldPostnr);
            }//if_not_exists

            /* Post sted        */
            $fldPoststed    = null;
            $fldPoststed    = new xmldb_field('poststed', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'postnr');
            if (!$dbMan->field_exists($tblFSCompany, $fldPoststed)) {
                $dbMan->add_field($tblFSCompany, $fldPoststed);
            }//if_not_exists

            /* ePost            */
            $fldEPost       = null;
            $fldEPost       = new xmldb_field('epost', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'poststed');
            if (!$dbMan->field_exists($tblFSCompany, $fldEPost)) {
                $dbMan->add_field($tblFSCompany, $fldEPost);
            }//if_not_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Update_FSCompany
}//Fellesdata_Update