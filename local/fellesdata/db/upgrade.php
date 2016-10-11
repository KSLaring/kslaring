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
    global $DB;
    /* Imp User JR  */
    $tblImpUsersJR      = null;
    $fldStillins        = null;
    $tblKSCompany       = null;
    $tblResource        = null;
    $fldParent          = null;
    $tblFSCompany       = null;
    $fldFSParent        = null;
    $fldIndustryCode    = null;

    /* Get Manager  */
    $dbMan = $DB->get_manager();


    try {

        if ($oldVersion < 2016031400) {
            /* Table        */
            $tblImpUsersJR = new xmldb_table('fs_imp_users_jr');

            /* New Field    */
            $fldStillins = new xmldb_field('STILLINGSNR', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'FODSELSNR');
            if (!$dbMan->field_exists($tblImpUsersJR, $fldStillins)) {
                $dbMan->add_field($tblImpUsersJR, $fldStillins);
            }//if_not_exists
        }//if_oldVersion

        if ($oldVersion < 2016060600) {
            Fellesdata_Update::Update_FSImpCompany($dbMan);
            Fellesdata_Update::Update_FSCompany($dbMan);
        }//id_oflVersion

        if ($oldVersion < 2016060604) {
            /* Table        */
            $tblKSCompany = new xmldb_table('ks_company');

            /* New Field    */
            $fldParent = new xmldb_field('parent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            if (!$dbMan->field_exists($tblKSCompany, $fldParent)) {
                $dbMan->add_field($tblKSCompany, $fldParent);
            }//if_not_exists
        }

        if ($oldVersion < 2016060606) {
            /* Table */
            $tblFSCompany   = new xmldb_table('fs_company');

            /* New Field */
            $fldFSParent = new xmldb_field('fs_parent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null,'parent');
            if (!$dbMan->field_exists($tblFSCompany, $fldFSParent)) {
                $dbMan->add_field($tblFSCompany, $fldFSParent);
            }//if_not_exists
        }

        /* Managers Reporters Temporary Table */
        if ($oldVersion < 2016061204) {
            Fellesdata_Update::Update_FSImpManagersReporters($dbMan);
        }//managersReporters

        /* User Competence Table */
        if ($oldVersion < 2016061400) {
            Fellesdata_Update::Update_FSUserCompetence($dbMan);
        }//UserCompetence

        if ($oldVersion < 2016092300) {
            Fellesdata_Update::ResourceNumber($dbMan);
        }//ResourceNumber

        if ($oldVersion < 2016092700) {
            /* Table */
            $tblResource   = new xmldb_table('user_resource_number');

            /* New Field */
            $fldIndustryCode = new xmldb_field('industrycode', XMLDB_TYPE_CHAR, '50',null,XMLDB_NOTNULL,null,null, 'ressursnr');
            if (!$dbMan->field_exists($tblResource, $fldIndustryCode)) {
                $dbMan->add_field($tblResource, $fldIndustryCode);
            }//if_not_exists
        }
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
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update User Competence
     */
    public static function Update_FSUserCompetence($dbMan) {
        /* Variables */
        $tblUserCompetence = null;

        try {
            /* mdl_fs_users_competence    */
            $tblUsersFSJR = new xmldb_table('fs_users_competence');

            /* Fields */
            /* Id               --> Primary Key                     */
            $tblUsersFSJR->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* personalnumber   --> Personal number                 */
            $tblUsersFSJR->add_field('personalnumber',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* companyid        --> Company Id from fellesdata.       */
            $tblUsersFSJR->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* jrcode           --> Job role Id from fellesdata     */
            $tblUsersFSJR->add_field('jrcode',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* ksjrcode           --> Job role Id from ks     */
            $tblUsersFSJR->add_field('ksjrcode',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* synchronized                                         */
            $tblUsersFSJR->add_field('synchronized',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblUsersFSJR->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblUsersFSJR->add_key('companyid',XMLDB_KEY_FOREIGN,array('companyid'), 'fs_company', array('companyid'));
            $tblUsersFSJR->add_key('personalnumber',XMLDB_KEY_FOREIGN,array('personalnumber'), 'user', array('username'));
            /* Index    */

            if (!$dbMan->table_exists('fs_users_competence')) {
                $dbMan->create_table($tblUsersFSJR);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Update_FSUserCompetence

    /**
     * @param           $dbMan
     * @throws          Exception
     *
     * @creationDate    13/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create managers reportes temporary table
     */
    public static function Update_FSImpManagersReporters($dbMan) {
        /* Variables */
        $tblImpManagersReporters = null;

        try {
            /* mdl_fs_imp_managers_reporters     */
            $tblImpManagersReporters = new xmldb_table('fs_imp_managers_reporters');

            /* Fields   */
            /* Id --> Primary key                           */
            $tblImpManagersReporters->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* org_enhet_id --> Company id                  */
            $tblImpManagersReporters->add_field('org_enhet_id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* org_nivaa --> Hierarchy level of the company */
            $tblImpManagersReporters->add_field('org_nivaa',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* fodselsnr --> Personal number                */
            $tblImpManagersReporters->add_field('fodselsnr',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* prioritet --> Manager or not                 */
            $tblImpManagersReporters->add_field('prioritet',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* action --> Action to apply                   */
            $tblImpManagersReporters->add_field('action',XMLDB_TYPE_CHAR,'25',null, XMLDB_NOTNULL, null,null);
            /* imported                                     */
            $tblImpManagersReporters->add_field('imported',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblImpManagersReporters->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            /* Index    */
            $tblImpManagersReporters->add_index('fodselsnr',XMLDB_INDEX_NOTUNIQUE,array('fodselsnr'));
            $tblImpManagersReporters->add_index('org_enhet_id',XMLDB_INDEX_NOTUNIQUE,array('org_enhet_id'));

            if (!$dbMan->table_exists('fs_imp_managers_reporters')) {
                $dbMan->create_table($tblImpManagersReporters);
            }//if_exists

            $tblImpUsersCompany = new xmldb_table('fs_imp_users_company');
            if ($dbMan->table_exists('fs_imp_users_company')) {
                $dbMan->drop_table($tblImpUsersCompany);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Update_FSImpManagersReporters

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
            $fldPrivate     = new xmldb_field('PRIVAT', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'ORG_ENHET_OVER');
            if (!$dbMan->field_exists($tblFSImpComp, $fldPrivate)) {
                $dbMan->add_field($tblFSImpComp, $fldPrivate);
            }//if_not_exists

            /* Ansvar Field     */
            $fldAnsvar      = null;
            $fldAnsvar      = new xmldb_field('ANSVAR', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'PRIVAT');
            if (!$dbMan->field_exists($tblFSImpComp, $fldAnsvar)) {
                $dbMan->add_field($tblFSImpComp, $fldAnsvar);
            }//if_not_exists

            /* Tjeneste Field   */
            $fldTjeneste    = null;
            $fldTjeneste    = new xmldb_field('TJENESTE', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'ANSVAR');
            if (!$dbMan->field_exists($tblFSImpComp, $fldTjeneste)) {
                $dbMan->add_field($tblFSImpComp, $fldTjeneste);
            }//if_not_exists

            /* Adresse 1        */
            $fldAdreseOne   = null;
            $fldAdreseOne   = new xmldb_field('ADRESSE1', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'TJENESTE');
            if (!$dbMan->field_exists($tblFSImpComp, $fldAdreseOne)) {
                $dbMan->add_field($tblFSImpComp, $fldAdreseOne);
            }//if_not_exists

            /* Adresse 2        */
            $fldAdreseTwo   = null;
            $fldAdreseTwo   = new xmldb_field('ADRESSE2', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'ADRESSE1');
            if (!$dbMan->field_exists($tblFSImpComp, $fldAdreseTwo)) {
                $dbMan->add_field($tblFSImpComp, $fldAdreseTwo);
            }//if_not_exists

            /* Adresse 3        */
            $fldAdreseThree = null;
            $fldAdreseThree = new xmldb_field('ADRESSE3', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'ADRESSE2');
            if (!$dbMan->field_exists($tblFSImpComp, $fldAdreseThree)) {
                $dbMan->add_field($tblFSImpComp, $fldAdreseThree);
            }//if_not_exists

            /* Post Number      */
            $fldPostnr      = null;
            $fldPostnr      = new xmldb_field('POSTNR', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'ADRESSE3');
            if (!$dbMan->field_exists($tblFSImpComp, $fldPostnr)) {
                $dbMan->add_field($tblFSImpComp, $fldPostnr);
            }//if_not_exists

            /* Post sted        */
            $fldPoststed    = null;
            $fldPoststed    = new xmldb_field('POSTSTED', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'POSTNR');
            if (!$dbMan->field_exists($tblFSImpComp, $fldPoststed)) {
                $dbMan->add_field($tblFSImpComp, $fldPoststed);
            }//if_not_exists

            /* ePost            */
            $fldEPost       = null;
            $fldEPost       = new xmldb_field('EPOST', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'POSTSTED');
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
            $fldPrivate     = new xmldb_field('privat', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'parent');
            if (!$dbMan->field_exists($tblFSCompany, $fldPrivate)) {
                $dbMan->add_field($tblFSCompany, $fldPrivate);
            }//if_not_exists

            /* Ansvar Field     */
            $fldAnsvar      = null;
            $fldAnsvar      = new xmldb_field('ansvar', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'privat');
            if (!$dbMan->field_exists($tblFSCompany, $fldAnsvar)) {
                $dbMan->add_field($tblFSCompany, $fldAnsvar);
            }//if_not_exists

            /* Tjeneste Field   */
            $fldTjeneste    = null;
            $fldTjeneste    = new xmldb_field('tjeneste', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'ansvar');
            if (!$dbMan->field_exists($tblFSCompany, $fldTjeneste)) {
                $dbMan->add_field($tblFSCompany, $fldTjeneste);
            }//if_not_exists

            /* Adresse 1        */
            $fldAdreseOne   = null;
            $fldAdreseOne   = new xmldb_field('adresse1', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'tjeneste');
            if (!$dbMan->field_exists($tblFSCompany, $fldAdreseOne)) {
                $dbMan->add_field($tblFSCompany, $fldAdreseOne);
            }//if_not_exists

            /* Adresse 2        */
            $fldAdreseTwo   = null;
            $fldAdreseTwo   = new xmldb_field('adresse2', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'adresse1');
            if (!$dbMan->field_exists($tblFSCompany, $fldAdreseTwo)) {
                $dbMan->add_field($tblFSCompany, $fldAdreseTwo);
            }//if_not_exists

            /* Adresse 3        */
            $fldAdreseThree = null;
            $fldAdreseThree = new xmldb_field('adresse3', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'adresse2');
            if (!$dbMan->field_exists($tblFSCompany, $fldAdreseThree)) {
                $dbMan->add_field($tblFSCompany, $fldAdreseThree);
            }//if_not_exists

            /* Post Number      */
            $fldPostnr      = null;
            $fldPostnr      = new xmldb_field('postnr', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'adresse3');
            if (!$dbMan->field_exists($tblFSCompany, $fldPostnr)) {
                $dbMan->add_field($tblFSCompany, $fldPostnr);
            }//if_not_exists

            /* Post sted        */
            $fldPoststed    = null;
            $fldPoststed    = new xmldb_field('poststed', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'postnr');
            if (!$dbMan->field_exists($tblFSCompany, $fldPoststed)) {
                $dbMan->add_field($tblFSCompany, $fldPoststed);
            }//if_not_exists

            /* ePost            */
            $fldEPost       = null;
            $fldEPost       = new xmldb_field('epost', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'poststed');
            if (!$dbMan->field_exists($tblFSCompany, $fldEPost)) {
                $dbMan->add_field($tblFSCompany, $fldEPost);
            }//if_not_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Update_FSCompany

    /**
     * @param           $dbMan
     * 
     * @throws          Exception
     * 
     * @creationDate    23/09/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Add resource number
     */
    public static function ResourceNumber($dbMan) {
        /* Variables */
        $tblUserResource    = null;
        $tblImpUsers        = null;
        $fldResource        = null;

        try {
            /* First Create the table   */
            if (!$dbMan->table_exists('user_resource_number')) {
                /* Create Table */
                $tblUserResource =  new xmldb_table('user_resource_number');

                /* Add fields   */
                /* Id               --> Primary Key */
                $tblUserResource->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                /* User Id          --> Foreign Key */
                $tblUserResource->add_field('userid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                /* Resource Number  */
                $tblUserResource->add_field('ressursnr',XMLDB_TYPE_CHAR,'50',null,XMLDB_NOTNULL,null,null);

                /* Add Keys */
                $tblUserResource->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                $tblUserResource->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

                $dbMan->create_table($tblUserResource);
            }
            
            /* Extra field to fs_imp_users table */
            if (!$dbMan->table_exists('fs_imp_users')) {
                self::ImpUsers_FSTable($dbMan);
            }else {
                $tblImpUsers = new xmldb_table('fs_imp_users');
                $fldResource = new xmldb_field('ressursnr', XMLDB_TYPE_CHAR, '50',null,XMLDB_NOTNULL,null,null, 'fodselsnr');
                if (!$dbMan->field_exists($tblImpUsers, $fldResource)) {
                    $dbMan->add_field($tblImpUsers, $fldResource);
                }//if_not_exists                
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ResourceNumber

    private static function ImpUsers_FSTable($dbMan) {
        /* Variables */
        $tblImpUsers = null;

        try {
            /* mdl_fs_imp_users             */
            $tblImpUsers = new xmldb_table('fs_imp_users');

            /* Fields */
            /* Id --> Primary key                   */
            $tblImpUsers->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* personalnumber --> Personal number   */
            $tblImpUsers->add_field('fodselsnr',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* REsource number  */
            $tblImpUsers->add_field('ressursnr',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* firstname    --> First name          */
            $tblImpUsers->add_field('fornavn',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* lastname     --> lastname            */
            $tblImpUsers->add_field('mellomnavn',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* lastname     --> lastname            */
            $tblImpUsers->add_field('etternavn',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* eMail        --> eMail               */
            $tblImpUsers->add_field('epost',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* action   --> Action to apply         */
            $tblImpUsers->add_field('action',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* Imported                             */
            $tblImpUsers->add_field('imported',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblImpUsers->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            /* Index    */
            $tblImpUsers->add_index('fodselsnr',XMLDB_INDEX_NOTUNIQUE,array('fodselsnr'));

            if (!$dbMan->table_exists('fs_imp_users')) {
                $dbMan->create_table($tblImpUsers);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ImpUsers_FSTable
}//Fellesdata_Update