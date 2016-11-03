<?php
/**
 * Fellesdata Integration - Script installaton DB
 *
 * @package         local/fellesdata
 * @subpackage      db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    01/02/2016
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_fellesdata_install() {
    /* Variables    */
    global $DB;
    /* Get Manager  */
    $dbMan = $DB->get_manager();

    try {
        Fellesdata_Install::DeleteFellesdata_Tables($dbMan);
        Fellesdata_Install::Delete_SynchronizationTables($dbMan);

        /* Create table for the synchronization between KS and FS */
        Fellesdata_Install::FellesdataTables($dbMan);
        Fellesdata_Install::SynchronizationTables($dbMan);
        
        /* Last time executed   */
        set_config('lastexecution', 0, 'local_fellesdata');
    }catch (Exception $ex) {
        /* Delete Tables created    */
        Fellesdata_Install::DeleteFellesdata_Tables($dbMan);
        Fellesdata_Install::Delete_SynchronizationTables($dbMan);
        throw $ex;
    }
}//xmldb_local_fellesdata_install


class Fellesdata_Install {


    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Fellesdata tables
     */
    public static function FellesdataTables($dbMan) {

        try {
            /* Create Fellesdata temporary tables */
            self::ImpCompany_FSTable($dbMan);

            self::ImpJobRoles_FSTable($dbMan);

            self::ImpUsersJR_FSTable($dbMan);

            self::ImpManagersReporters_FSTable($dbMan);

            self::ImpUsers_FSTable($dbMan);
        }catch (Exception $ex) {
            /* Delete Tables    */
            self::DeleteFellesdata_Tables($dbMan);

            throw $ex;
        }//try_Catch
    }//ImportFellesdataTables

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronization tables
     */
    public static function SynchronizationTables($dbMan) {
        try {
            self::KSCompany_Table($dbMan);
            self::FSCompany_Table($dbMan);
            self::KSFS_Relation_Table($dbMan);

            self::UsersFSCompany_Table($dbMan);
            self::FSJobRoles_Table($dbMan);

            self::KSJobRoles_Table($dbMan);
            self::KSJobRoles_Relation_Table($dbMan);
            self::JR_KSFS_Relation_Table($dbMan);

            self::UsersFSCompetence_Table($dbMan);

            self::ResourceNumber($dbMan);
        }catch (Exception $ex) {
            /* Delete tables */
            self::Delete_SynchronizationTables($dbMan);

            throw $ex;
        }//try_catch
    }//SynchronizationTables

    /**********************/
    /* TABLES FELLESDATA  */
    /**********************/

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create table mdl_fs_imp_company
     */
    private static function ImpCompany_FSTable($dbMan) {
        /* Variables */
        $tblFSImpComp = null;

        try {
            /* mdl_fs_imp_company  */
            $tblFSImpComp = new xmldb_table('fs_imp_company');

            /* Fields   */
            /* Id               --> Primary key.  */
            $tblFSImpComp->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* org_enhet_id     --> Company Id from fellesdata          */
            $tblFSImpComp->add_field('org_enhet_id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* org_nivaa        --> Hierarchy level from fellesdata     */
            $tblFSImpComp->add_field('org_nivaa',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* org_navn         --> Company name                        */
            $tblFSImpComp->add_field('org_navn',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* org_enhet_over   --> Parent company                      */
            $tblFSImpComp->add_field('org_enhet_over',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* privat --> public */
            $tblFSImpComp->add_field('privat',XMLDB_TYPE_INTEGER,'1',null, null, null,null);
            /* ansvar   */
            $tblFSImpComp->add_field('ansvar',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            /* tjeneste */
            $tblFSImpComp->add_field('tjeneste',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            /* adresse1 */
            $tblFSImpComp->add_field('adresse1',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* adresse2 */
            $tblFSImpComp->add_field('adresse2',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* adresse3 */
            $tblFSImpComp->add_field('adresse3',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* postnr   */
            $tblFSImpComp->add_field('postnr',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            /* poststed */
            $tblFSImpComp->add_field('poststed',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            /* epost    */
            $tblFSImpComp->add_field('epost',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* action           --> Action to apply                     */
            $tblFSImpComp->add_field('action',XMLDB_TYPE_CHAR,'25',null, XMLDB_NOTNULL, null,null);
            /* Imported         */
            $tblFSImpComp->add_field('imported',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblFSImpComp->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            /* Index    */
            $tblFSImpComp->add_index('org_enhet_id',XMLDB_INDEX_NOTUNIQUE,array('org_enhet_id'));

            if (!$dbMan->table_exists('fs_imp_company')) {
                $dbMan->create_table($tblFSImpComp);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ImpCompany_FSTable

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create table mdl_fs_imp_jobroles
     */
    private static function ImpJobRoles_FSTable($dbMan) {
        /* Variables    */
        $tblFSImpJR = null;

        try {
            /* mdl_fs_imp_users_jr          */
            $tblFSImpJR = new xmldb_table('fs_imp_jobroles');

            /* Fields   */
            /* Id --> Primary key                           */
            $tblFSImpJR->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* stillingskode    --> Job Role code           */
            $tblFSImpJR->add_field('stillingskode',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* stillingstekst   --> Job Role Name           */
            $tblFSImpJR->add_field('stillingsstekst',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* alternative      --> alternative name        */
            $tblFSImpJR->add_field('stillingsstekst_alternativ',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* action           --> Action to apply         */
            $tblFSImpJR->add_field('action',XMLDB_TYPE_CHAR,'25',null, XMLDB_NOTNULL, null,null);
            /* imported                                     */
            $tblFSImpJR->add_field('imported',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblFSImpJR->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            /* Index    */
            $tblFSImpJR->add_index('stillingskode',XMLDB_INDEX_NOTUNIQUE,array('stillingskode'));

            if (!$dbMan->table_exists('fs_imp_jobroles')) {
                $dbMan->create_table($tblFSImpJR);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ImpJobRoles_FSTable

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create table mdl_fs_imp_users_jr
     */
    private static function ImpUsersJR_FSTable($dbMan) {
        /* Variables */
        $tblImpUsersJR = null;

        try {
            /* mdl_fs_imp_users_jr          */
            $tblImpUsersJR = new xmldb_table('fs_imp_users_jr');

            /* Fields   */
            /* Id --> Primary key                           */
            $tblImpUsersJR->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* stillingsnr --> Extra primary key from fellesdata                           */
            $tblImpUsersJR->add_field('stillingsnr',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* fodselsnr    --> Personal number             */
            $tblImpUsersJR->add_field('fodselsnr',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* fornavn      --> First name                  */
            $tblImpUsersJR->add_field('fornavn',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* etternavn    --> Last name                   */
            $tblImpUsersJR->add_field('etternavn',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* epost        --> eMail                       */
            $tblImpUsersJR->add_field('epost',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* org_enhet_id     --> Company id              */
            $tblImpUsersJR->add_field('org_enhet_id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* stillingskode    --> Job Role code           */
            $tblImpUsersJR->add_field('stillingskode',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* stillingstekst   --> Job Role Name           */
            $tblImpUsersJR->add_field('stillingstekst',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* alternative      --> alternative name        */
            $tblImpUsersJR->add_field('stillingstekst_alternativ',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* hovedstilling    --> Main job role or not    */
            $tblImpUsersJR->add_field('hovedstilling',XMLDB_TYPE_CHAR,'1',null, XMLDB_NOTNULL, null,null);
            /* action           --> Action to apply         */
            $tblImpUsersJR->add_field('action',XMLDB_TYPE_CHAR,'25',null, XMLDB_NOTNULL, null,null);
            /* imported                                     */
            $tblImpUsersJR->add_field('imported',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblImpUsersJR->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            /* Index    */
            $tblImpUsersJR->add_index('fodselsnr',XMLDB_INDEX_NOTUNIQUE,array('fodselsnr'));
            $tblImpUsersJR->add_index('org_enhet_id',XMLDB_INDEX_NOTUNIQUE,array('org_enhet_id'));

            if (!$dbMan->table_exists('fs_imp_users_jr')) {
                $dbMan->create_table($tblImpUsersJR);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ImpUsersJR_FSTable


    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create table mdl_fs_imp_managers_reporters
     */
    private static function ImpManagersReporters_FSTable($dbMan) {
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
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ImpUsersCompany_FSTable


    /**
     * @param           $dbMan
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create table mdl_fs_imp_users
     */
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


    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete fellesdata temporary tables
     */
    public static function DeleteFellesdata_Tables($dbMan) {
        /* Variables    */
        $tblFSImpComp               = null;
        $tblFSImpJR                 = null;
        $tblImpUsersJR              = null;
        $tblImpManagersReporters    = null;
        $tblImpUsers                = null;

        try {
            /* Tables   */
            $tblFSImpComp       = new xmldb_table('fs_imp_company');
            if ($dbMan->table_exists('fs_imp_company')) {
                $dbMan->drop_table($tblFSImpComp);
            }//if_exists

            $tblFSImpJR = new xmldb_table('fs_imp_jobroles');
            if ($dbMan->table_exists('fs_imp_jobroles')) {
                $dbMan->drop_table($tblFSImpJR);
            }//if_exists

            $tblImpUsersJR = new xmldb_table('fs_imp_users_jr');
            if ($dbMan->table_exists('fs_imp_users_jr')) {
                $dbMan->drop_table($tblImpUsersJR);
            }//if_exists


            $tblImpUsersCompany = new xmldb_table('fs_imp_users_company');
            if ($dbMan->table_exists('fs_imp_users_company')) {
                $dbMan->drop_table($tblImpUsersCompany);
            }//if_exists

            $tblImpManagersReporters = new xmldb_table('fs_imp_managers_reporters');
            if ($dbMan->table_exists('fs_imp_managers_reporters')) {
                $dbMan->drop_table($tblImpManagersReporters);
            }//if_exists

            $tblImpUsers = new xmldb_table('fs_imp_users');
            if ($dbMan->table_exists('fs_imp_users')) {
                $dbMan->drop_table($tblImpUsers);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//DeleteFellesdata_Tables

    /****************************/
    /* TABLES SYNCHRONIZATION   */
    /****************************/

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create mdl_ks_company
     */
    private static function KSCompany_Table($dbMan) {
        /* Variables    */
        $tblKSCompany = null;

        try {
            /* mdl_ks_company           */
            $tblKSCompany       = new xmldb_table('ks_company');

            /* Fields   */
            /* Id   --> Primary Key                                 */
            $tblKSCompany->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* companyid --> Company ID from KS                     */
            $tblKSCompany->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* name --> Company name                                */
            $tblKSCompany->add_field('name',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* industrycode --> */
            $tblKSCompany->add_field('industrycode',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* hierarchylevel --> hierarchy inside the organization */
            $tblKSCompany->add_field('hierarchylevel',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* Parent */
            $tblKSCompany->add_field('parent',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblKSCompany->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            /* Index    */
            $tblKSCompany->add_index('companyid',XMLDB_INDEX_UNIQUE,array('companyid'));

            if (!$dbMan->table_exists('ks_company')) {
                $dbMan->create_table($tblKSCompany);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//KSCompany_Table


    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create mdl_fs_company
     */
    private static function FSCompany_Table($dbMan) {
        /* Variables */
        $tblFSCompany = null;

        try {
            /* mdl_fs_company           */
            $tblFSCompany       = new xmldb_table('fs_company');

            /* Fields   */
            /* Id           --> Primary key                                     */
            $tblFSCompany->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* companyid    --> Company Id from fellesdata                      */
            $tblFSCompany->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* name         --> Company name                                    */
            $tblFSCompany->add_field('name',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* level        --> Level of the company inside the organization    */
            $tblFSCompany->add_field('level',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* parent       --> Parent of the company                           */
            $tblFSCompany->add_field('parent',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* fs_parent    */
            $tblFSCompany->add_field('fs_parent',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* privat --> public */
            $tblFSCompany->add_field('privat',XMLDB_TYPE_INTEGER,'1',null, null, null,null);
            /* ansvar   */
            $tblFSCompany->add_field('ansvar',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            /* tjeneste */
            $tblFSCompany->add_field('tjeneste',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            /* adresse1 */
            $tblFSCompany->add_field('adresse1',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* adresse2 */
            $tblFSCompany->add_field('adresse2',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* adresse3 */
            $tblFSCompany->add_field('adresse3',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* postnr   */
            $tblFSCompany->add_field('postnr',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            /* poststed */
            $tblFSCompany->add_field('poststed',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            /* epost    */
            $tblFSCompany->add_field('epost',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            /* synchronized                                                     */
            $tblFSCompany->add_field('synchronized',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* New  --> To create a new one */
            $tblFSCompany->add_field('new',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* timemodified */
            $tblFSCompany->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblFSCompany->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            /* Index    */
            $tblFSCompany->add_index('companyid',XMLDB_INDEX_UNIQUE,array('companyid'));

            if (!$dbMan->table_exists('fs_company')) {
                $dbMan->create_table($tblFSCompany);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FSCompany_Table

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create mdl_ksfs_company
     */
    private static function KSFS_Relation_Table($dbMan) {
        /* Variables */
        $tblKSFSCompany = null;

        try {
            /* mdl_ksfs_company */
            $tblKSFSCompany = new xmldb_table('ksfs_company');

            /* Fields   */
            /* Id           --> Primary key                     */
            $tblKSFSCompany->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* kscompany    --> Foreign key. Company id from KS */
            $tblKSFSCompany->add_field('kscompany',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* fscompany    --> Foreign key. Company id from FS */
            $tblKSFSCompany->add_field('fscompany',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblKSFSCompany->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblKSFSCompany->add_key('kscompany',XMLDB_KEY_FOREIGN,array('kscompany'), 'ks_company', array('companyid'));
            $tblKSFSCompany->add_key('fscompany',XMLDB_KEY_FOREIGN,array('fscompany'), 'fs_company', array('companyid'));
            /* index    */

            if (!$dbMan->table_exists('ksfs_company')) {
                $dbMan->create_table($tblKSFSCompany);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//KSFS_Relation_Table

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create mdl_fs_users_company
     */
    private static function UsersFSCompany_Table($dbMan) {
        /* Variables */
        $tblUsersFSCompany = null;

        try {
            /* mdl_fs_users_company     */
            $tblUsersFSCompany = new xmldb_table('fs_users_company');

            /* Fields   */
            /* Id               --> primary key                                     */
            $tblUsersFSCompany->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* companyid        --> Company ID from fellesdata                      */
            $tblUsersFSCompany->add_field('companyid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* level            --> Level of the company inside the organization    */
            $tblUsersFSCompany->add_field('level',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* personalnumber   --> Personal number                                 */
            $tblUsersFSCompany->add_field('personalnumber',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* priority         --> Manager or not                                  */
            $tblUsersFSCompany->add_field('priority',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* synchronized                                                         */
            $tblUsersFSCompany->add_field('synchronized',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblUsersFSCompany->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblUsersFSCompany->add_key('companyid',XMLDB_KEY_FOREIGN,array('companyid'), 'fs_company', array('companyid'));
            $tblUsersFSCompany->add_key('personalnumber',XMLDB_KEY_FOREIGN,array('personalnumber'), 'user', array('username'));
            /* Index    */

            if (!$dbMan->table_exists('fs_users_company')) {
                $dbMan->create_table($tblUsersFSCompany);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UsersFSCompany_Table

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create mdl_fs_jobroles
     */
    private static function FSJobRoles_Table($dbMan) {
        /* Variables */
        $tblFSJobRoles = null;

        try {
            /* mdl_fs_jobroles          */
            $tblFSJobRoles = new xmldb_table('fs_jobroles');

            /* Fields */
            /* Id               --> Primary Key                     */
            $tblFSJobRoles->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* jrcode           --> Job Role code from fellesdata   */
            $tblFSJobRoles->add_field('jrcode',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            /* jrname           --> Job role name                   */
            $tblFSJobRoles->add_field('jrname',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* jralternative    --> Alternative job role            */
            $tblFSJobRoles->add_field('jralternative',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* synchronized                                                         */
            $tblFSJobRoles->add_field('synchronized',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* New */
            $tblFSJobRoles->add_field('new',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            /* timemodified */
            $tblFSJobRoles->add_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblFSJobRoles->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            /* Index    */
            $tblFSJobRoles->add_index('jrcode',XMLDB_INDEX_NOTUNIQUE,array('jrcode'));

            if (!$dbMan->table_exists('fs_jobroles')) {
                $dbMan->create_table($tblFSJobRoles);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FSJobRoles_Table

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create mdl_ks_jobroles
     */
    private static function KSJobRoles_Table($dbMan) {
        /* Variables */
        $tblKSJobRoles = null;

        try {
            /* mdl_ks_jobroles          */
            $tblKSJobRoles = new xmldb_table('ks_jobroles');

            /* Fields */
            /* Id --> Primary Key                   */
            $tblKSJobRoles->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* jobroleid --> Job role id from KS    */
            $tblKSJobRoles->add_field('jobroleid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* name --> job role name               */
            $tblKSJobRoles->add_field('name',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* industrycode --> */
            $tblKSJobRoles->add_field('industrycode',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblKSJobRoles->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            /* Index    */
            $tblKSJobRoles->add_index('jobroleid',XMLDB_INDEX_NOTUNIQUE,array('jobroleid'));

            if (!$dbMan->table_exists('ks_jobroles')) {
                $dbMan->create_table($tblKSJobRoles);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//KSJobRoles_Table

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create mdl_ks_jobroles_relation
     */
    private static function KSJobRoles_Relation_Table($dbMan) {
        /* Variables */
        $tblKSJobRolesRelation = null;

        try {
            /* mdl_ks_jobroles_relation */
            $tblKSJobRolesRelation = new xmldb_table('ks_jobroles_relation');

            /* Fields   */
            /* Id           --> Primary key                     */
            $tblKSJobRolesRelation->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* jobroleid    --> Job role id. Foreign Key        */
            $tblKSJobRolesRelation->add_field('jobroleid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* levelzero    --> Level zero of the hierarchy     */
            $tblKSJobRolesRelation->add_field('levelzero',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* levelone     --> Level one of the hierarchy      */
            $tblKSJobRolesRelation->add_field('levelone',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* leveltwo     --> Level two of the hierarchy      */
            $tblKSJobRolesRelation->add_field('leveltwo',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            /* levelthree   --> Level three of the hierarchy    */
            $tblKSJobRolesRelation->add_field('levelthree',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

            /* Key      */
            $tblKSJobRolesRelation->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblKSJobRolesRelation->add_key('jobroleid',XMLDB_KEY_FOREIGN,array('jobroleid'), 'ks_jobroles', array('jobroleid'));
            /* Index    */

            if (!$dbMan->table_exists('ks_jobroles_relation')) {
                $dbMan->create_table($tblKSJobRolesRelation);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//KSJobRoles_Relation_Table

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create mdl_ksfs_jobroles
     */
    private static function JR_KSFS_Relation_Table($dbMan) {
        /* Variables */
        $tblKSFS_JR = null;

        try {
            /* mdl_ksfs_jobroles        */
            $tblKSFS_JR = new xmldb_table('ksfs_jobroles');

            /* Fields   */
            /* Id --> Primary key   */
            $tblKSFS_JR->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* ksjobrole --> Job role id from KS. Foreign key   */
            $tblKSFS_JR->add_field('ksjobrole',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* fsjobrole --> Job role id from FS. Foreign key   */
            $tblKSFS_JR->add_field('fsjobrole',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);

            /* Keys     */
            $tblKSFS_JR->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblKSFS_JR->add_key('ksjobrole',XMLDB_KEY_FOREIGN,array('ksjobrole'), 'ks_jobroles', array('jobroleid'));
            @$tblKSFS_JR->add_key('fsjobrole',XMLDB_KEY_FOREIGN,array('fsjobrole'), 'fs_jobroles', array('jrcode'));
            /* index    */

            if (!$dbMan->table_exists('ksfs_jobroles')) {
                $dbMan->create_table($tblKSFS_JR);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//JR_KSFS_Relation_Table
    }//JR_KSFS_Relation_Table

    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create table mdl_fs_users_competence
     */
    private static function UsersFSCompetence_Table($dbMan) {
        /* Variables */
        $tblUsersFSJR = null;

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
    }//UsersFSCompetence_Table


    /**
     * @param           $dbMan
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor         (fbv)
     *
     * Description
     * Delete tables
     */
    public static function Delete_SynchronizationTables($dbMan) {
        /* Variables */

        try {
            /* mdl_ks_company           */
            $tblKSCompany       = new xmldb_table('ks_company');
            if ($dbMan->table_exists('ks_company')) {
                $dbMan->drop_table($tblKSCompany);
            }//if_exists

            /* mdl_fs_company           */
            $tblFSCompany       = new xmldb_table('fs_company');
            if ($dbMan->table_exists('fs_company')) {
                $dbMan->drop_table($tblFSCompany);
            }//if_exists

            /* mdl_ksfs_company */
            $tblKSFSCompany = new xmldb_table('ksfs_company');
            if ($dbMan->table_exists('ksfs_company')) {
                $dbMan->drop_table($tblKSFSCompany);
            }//if_exists

            /* mdl_fs_users_company     */
            $tblUsersFSCompany = new xmldb_table('fs_users_company');
            if ($dbMan->table_exists('fs_users_company')) {
                $dbMan->drop_table($tblUsersFSCompany);
            }//if_exists

            /* mdl_fs_jobroles          */
            $tblFSJobRoles = new xmldb_table('fs_jobroles');
            if ($dbMan->table_exists('fs_jobroles')) {
                $dbMan->drop_table($tblFSJobRoles);
            }//if_exists

            /* mdl_ks_jobroles          */
            $tblKSJobRoles = new xmldb_table('ks_jobroles');
            if ($dbMan->table_exists('ks_jobroles')) {
                $dbMan->drop_table($tblKSJobRoles);
            }//if_exists

            /* mdl_ks_jobroles_relation */
            $tblKSJobRolesRelation = new xmldb_table('ks_jobroles_relation');
            if ($dbMan->table_exists('ks_jobroles_relation')) {
                $dbMan->drop_table($tblKSJobRolesRelation);
            }//if_exists

            /* mdl_ksfs_jobroles        */
            $tblKSFS_JR = new xmldb_table('ksfs_jobroles');
            if ($dbMan->table_exists('ksfs_jobroles')) {
                $dbMan->drop_table($tblKSFS_JR);
            }//if_exists

            /* mdl_fs_users_competence    */
            $tblUsersFSJR = new xmldb_table('fs_users_competence');
            if ($dbMan->table_exists('fs_users_competence')) {
                $dbMan->drop_table($tblUsersFSJR);
            }//if_exists

            /* mdl_user_resource_number */
            $tblUserResource = new xmldb_table('user_resource_number');
            if ($dbMan->table_exists('user_resource_number')) {
                $dbMan->drop_table($tblUserResource);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Delete_SynchronizationTables

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
                /* industry code  */
                $tblUserResource->add_field('industrycode',XMLDB_TYPE_CHAR,'50',null,XMLDB_NOTNULL,null,null);

                /* Add Keys */
                $tblUserResource->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                $tblUserResource->add_key('userid',XMLDB_KEY_FOREIGN,array('userid'), 'user', array('id'));

                $dbMan->create_table($tblUserResource);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ResourceNumber
}//Fellesdata_Install
