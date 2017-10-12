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
 * Fellesdata Integration - Script UPGRADE installaton DB
 *
 * @package         local/fellesdata
 * @subpackage      db
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
    $tblImpUsersJR      = null;
    $fldStillins        = null;
    $tblKSCompany       = null;
    $tblResource        = null;
    $fldParent          = null;
    $tblFSCompany       = null;
    $fldFSParent        = null;
    $fldIndustryCode    = null;
    $fldADFS            = null;
    $fldUsersImp        = null;
    $fldToken           = null;
    $tblSuspicious      = null;
    $tblFSImpJR         = null;
    $tblImpManagers     = null;
    $tblImpUsers        = null;
    $fdlTimeImp         = null;
    $fdlTime            = null;
    $fdlmoved           = null;

    // Get manager
    $dbMan = $DB->get_manager();


    try {

        if ($oldVersion < 2016031400) {
            // Table
            $tblImpUsersJR = new xmldb_table('fs_imp_users_jr');

            // New field
            $fldStillins = new xmldb_field('STILLINGSNR', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'FODSELSNR');
            if (!$dbMan->field_exists($tblImpUsersJR, $fldStillins)) {
                $dbMan->add_field($tblImpUsersJR, $fldStillins);
            }//if_not_exists
        }//if_oldVersion

        if ($oldVersion < 2016060600) {
            Fellesdata_Update::Update_FSImpCompany($dbMan);
            Fellesdata_Update::Update_FSCompany($dbMan);
        }//id_oflVersion

        if ($oldVersion < 2016060604) {
            // Table
            $tblKSCompany = new xmldb_table('ks_company');

            // New field
            $fldParent = new xmldb_field('parent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            if (!$dbMan->field_exists($tblKSCompany, $fldParent)) {
                $dbMan->add_field($tblKSCompany, $fldParent);
            }//if_not_exists
        }

        if ($oldVersion < 2016060606) {
            // Table
            $tblFSCompany   = new xmldb_table('fs_company');

            // New field
            $fldFSParent = new xmldb_field('fs_parent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null,'parent');
            if (!$dbMan->field_exists($tblFSCompany, $fldFSParent)) {
                $dbMan->add_field($tblFSCompany, $fldFSParent);
            }//if_not_exists
        }

        // Managers Reporters Temporary Table
        if ($oldVersion < 2016061204) {
            Fellesdata_Update::Update_FSImpManagersReporters($dbMan);
        }//managersReporters

        // User Competence Table
        if ($oldVersion < 2016061400) {
            Fellesdata_Update::Update_FSUserCompetence($dbMan);
        }//UserCompetence

        if ($oldVersion < 2016092300) {
            Fellesdata_Update::ResourceNumber($dbMan);
        }//ResourceNumber

        if ($oldVersion < 2016092700) {
            // Table
            $tblResource   = new xmldb_table('user_resource_number');

            // New field
            $fldIndustryCode = new xmldb_field('industrycode', XMLDB_TYPE_CHAR, '50',null,XMLDB_NOTNULL,null,null, 'ressursnr');
            if (!$dbMan->field_exists($tblResource, $fldIndustryCode)) {
                $dbMan->add_field($tblResource, $fldIndustryCode);
            }//if_not_exists
        }//if_oldVersion_2016092700

        //  Add ADFS ID
        if ($oldVersion < 2016102504) {
            $fldUsersImp = new xmldb_table('fs_imp_users');
            if ($dbMan->table_exists('fs_imp_users')) {
                // ADFS ID
                $fldADFS = new xmldb_field('brukernavn', XMLDB_TYPE_CHAR, '50',null,null,null,null, 'epost');
                if (!$dbMan->field_exists($fldUsersImp, $fldADFS)) {
                    $dbMan->add_field($fldUsersImp, $fldADFS);
                }//if_not_exists
            }//if_exists
        }//if_oldVersion_2016102504
        
        if ($oldVersion < 2016111700) {
            Fellesdata_Update::UnMapTables($dbMan);    
        }//if_oldVersion
        
        // Suspicious tables
        if ($oldVersion < 2016122600) {
            Fellesdata_Update::add_suspicious($dbMan);
            Fellesdata_Update::add_suspicious_action($dbMan);
        }//if_old_versin

        // Add token connected with the file
        if ($oldVersion < 2017011802) {
            // Table
            $tblSuspicious = new xmldb_table('fs_suspicious');

            // New field
            $fldToken = new xmldb_field('token',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,0, 'impfs');
            if (!$dbMan->field_exists($tblSuspicious, $fldToken)) {
                $dbMan->add_field($tblSuspicious, $fldToken);
            }//if_not_exists
        }//if_oldVersion

        // Add new fields timeimport and timemodified
        if ($oldVersion < 2017022002) {
            // New fields
            // timeimport
            $fdlTimeImp = new xmldb_field('timeimport',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            // timemodified
            $fdlTime = new xmldb_field('timemodified',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

            // fs_imp_company
            $tblFSCompany = new xmldb_table('fs_imp_company');
            if (!$dbMan->field_exists($tblFSCompany, $fdlTimeImp)) {
                $dbMan->add_field($tblFSCompany, $fdlTimeImp);
            }//if_not_exists_timeimport
            if (!$dbMan->field_exists($tblFSCompany, $fdlTime)) {
                $dbMan->add_field($tblFSCompany, $fdlTime);
            }//if_not_exists_timemodified

            // fs_imp_jobroles
            $tblFSImpJR = new xmldb_table('fs_imp_jobroles');
            if (!$dbMan->field_exists($tblFSImpJR, $fdlTimeImp)) {
                $dbMan->add_field($tblFSImpJR, $fdlTimeImp);
            }//if_not_exists_timeimport
            if (!$dbMan->field_exists($tblFSImpJR, $fdlTime)) {
                $dbMan->add_field($tblFSImpJR, $fdlTime);
            }//if_not_exists_timemodified

            // fs_imp_users_jr
            $tblImpUsersJR = new xmldb_table('fs_imp_users_jr');
            if (!$dbMan->field_exists($tblImpUsersJR, $fdlTimeImp)) {
                $dbMan->add_field($tblImpUsersJR, $fdlTimeImp);
            }//if_not_exists_timeimport
            if (!$dbMan->field_exists($tblImpUsersJR, $fdlTime)) {
                $dbMan->add_field($tblImpUsersJR, $fdlTime);
            }//if_not_exists_timemodified

            // fs_imp_managers_reporters
            $tblImpManagers = new xmldb_table('fs_imp_managers_reporters');
            if (!$dbMan->field_exists($tblImpManagers, $fdlTimeImp)) {
                $dbMan->add_field($tblImpManagers, $fdlTimeImp);
            }//if_not_exists_timeimport
            if (!$dbMan->field_exists($tblImpManagers, $fdlTime)) {
                $dbMan->add_field($tblImpManagers, $fdlTime);
            }//if_not_exists_timemodified

            // fs_imp_users
            $tblImpUsers = new xmldb_table('fs_imp_users');
            if (!$dbMan->field_exists($tblImpUsers, $fdlTimeImp)) {
                $dbMan->add_field($tblImpUsers, $fdlTimeImp);
            }//if_not_exists_timeimport
            if (!$dbMan->field_exists($tblImpUsers, $fdlTime)) {
                $dbMan->add_field($tblImpUsers, $fdlTime);
            }//if_not_exists_timemodified
        }//if_oldversion

        if ($oldVersion < 2017090600) {
            // Table
            $tblFSCompany   = new xmldb_table('fs_company');

            // New field
            $fdlmoved = new xmldb_field('moved', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0,'fs_parent');
            if (!$dbMan->field_exists($tblFSCompany, $fdlmoved)) {
                $dbMan->add_field($tblFSCompany, $fdlmoved);
            }//if_not_exists
        }

        if ($oldVersion < 2017101014) {
            Fellesdata_Update::fs_imp_company_log($dbMan);
            Fellesdata_Update::fs_imp_users_log($dbMan);
            Fellesdata_Update::fs_users_sync_log($dbMan);
            Fellesdata_Update::fs_imp_jobroles_log($dbMan);
            Fellesdata_Update::fs_jobroles_sync_log($dbMan);
            Fellesdata_Update::fs_imp_users_jr_log($dbMan);
            Fellesdata_Update::fs_imp_managers_reporters_log($dbMan);
            Fellesdata_Update::fellesdata_log_table($dbMan);
        }

        return true;
    }catch (Exception $ex) {
        throw $ex;
    }//try_Catch
}//xmldb_local_fellesdata_upgrade

class Fellesdata_Update {
    /**
     * Description
     * Fellesdata generic log
     *
     * @param           $dbman
     *
     * @throws          Exception
     *
     * @creationDate    10/10/2017
     * @author          eFaktor     (fbv)
     */
    public static function fellesdata_log_table($dbman) {
        /* Variables */
        $tbl = null;

        try {
            if (!$dbman->table_exists('fs_fellesdata_log')) {
                $tbl = new xmldb_table('fs_fellesdata_log');

                // Fields
                // Id --> primary key
                $tbl->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // action
                $tbl->add_field('action',XMLDB_TYPE_CHAR,'250',null, null, null,null);
                // description
                $tbl->add_field('description',XMLDB_TYPE_TEXT,null,null, null, null,null);
                // completion
                $tbl->add_field('timecreated',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                // Adding keys, index, foreing keys
                $tbl->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                $tbl->add_index('timecreated',XMLDB_INDEX_NOTUNIQUE,array('timecreated'));
                $tbl->add_index('action',XMLDB_INDEX_NOTUNIQUE,array('action'));

                // Crete table
                $dbman->create_table($tbl);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//fellesdata_log_table

    /**
     * Description
     * Log/historical fro managers:reporters coming from TARDIS
     *
     * @param           $dbman
     *
     * @throws          Exception
     *
     * @creationDate    09/10/2017
     * @author          eFaktor     (fbv)
     */
    public static function fs_imp_managers_reporters_log($dbman) {
        /* Variables */
        $tbl = null;

        try {
            if (!$dbman->table_exists('fs_imp_mng_rpt_log')) {
                // fs_imp_managers_reporters_log table
                $tbl = new xmldb_table('fs_imp_mng_rpt_log');

                // Fields
                // Id --> Primary key
                $tbl->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // org_enhet_id --> Company id
                $tbl->add_field('org_enhet_id',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // org_nivaa --> Hierarchy level of the company
                $tbl->add_field('org_nivaa',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
                // fodselsnr --> Personal number
                $tbl->add_field('fodselsnr',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
                // prioritet --> Manager or not
                $tbl->add_field('prioritet',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
                // action --> Action to apply
                $tbl->add_field('action',XMLDB_TYPE_CHAR,'25',null, XMLDB_NOTNULL, null,null);
                // time sent
                $tbl->add_field('timereceived',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

                // Keys
                $tbl->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Index
                $tbl->add_index('fodselsnr',XMLDB_INDEX_NOTUNIQUE,array('fodselsnr'));
                $tbl->add_index('enhet_id',XMLDB_INDEX_NOTUNIQUE,array('org_enhet_id'));
                $tbl->add_index('nivaa',XMLDB_INDEX_NOTUNIQUE,array('org_nivaa'));
                $tbl->add_index('received',XMLDB_INDEX_NOTUNIQUE,array('timereceived'));

                // Create table
                $dbman->create_table($tbl);
            }//if_exists

            // Add extra fields and index
            $tbl = new xmldb_table('fs_users_company');
            $tblfield = new xmldb_field('timesync', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);
            if (!$dbman->field_exists($tbl, $tblfield)) {
                $dbman->add_field($tbl, $tblfield);
            }//if_not_exists

            // Index - companyid
            $index = new xmldb_index('companyid', XMLDB_INDEX_NOTUNIQUE, array('companyid'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // index parent
            $index = new xmldb_index('pnumber', XMLDB_INDEX_NOTUNIQUE, array('personalnumber'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // Index - time sync
            $index = new xmldb_index('sync', XMLDB_INDEX_NOTUNIQUE, array('timesync'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }

            $tbl = new xmldb_table('fs_imp_managers_reporters');
            // fodselsnr
            $index = new xmldb_index('fodselsnr', XMLDB_INDEX_NOTUNIQUE, array('fodselsnr'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // org_enhet_id
            $index = new xmldb_index('enhet_id', XMLDB_INDEX_NOTUNIQUE, array('org_enhet_id'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // org_nivaa
            $index = new xmldb_index('nivaa', XMLDB_INDEX_NOTUNIQUE, array('org_nivaa'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//fs_imp_managers_reporters_log

    /**
     * Description
     * Log/historical for users competence
     *
     * @param           $dbman
     *
     * @throws          Exception
     *
     * @creationDate    09/10/2017
     * @auhtor          eFaktor     (fbv)
     */
    public static function fs_imp_users_jr_log($dbman) {
        /* Variables */
        $tbl = null;

        try {
            if (!$dbman->table_exists('fs_imp_users_jr_log')) {
                // fs_imp_users_jr_log table
                $tbl = new xmldb_table('fs_imp_users_jr_log');

                // Fields
                // Id --> Primary key
                $tbl->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // stillingsnr --> Extra primary key from fellesdata
                $tbl->add_field('stillingsnr',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // fodselsnr    --> Personal number
                $tbl->add_field('fodselsnr',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
                // fornavn      --> First name
                $tbl->add_field('fornavn',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // etternavn    --> Last name
                $tbl->add_field('etternavn',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // epost        --> eMail
                $tbl->add_field('epost',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // org_enhet_id     --> Company id
                $tbl->add_field('org_enhet_id',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // stillingskode    --> Job Role code
                $tbl->add_field('stillingskode',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
                // stillingstekst   --> Job Role Name
                $tbl->add_field('stillingstekst',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // alternative      --> alternative name
                $tbl->add_field('stillingstekst_alternativ',XMLDB_TYPE_CHAR,'255',null, null, null,null);
                // hovedstilling    --> Main job role or not
                $tbl->add_field('hovedstilling',XMLDB_TYPE_CHAR,'1',null, XMLDB_NOTNULL, null,null);
                // action
                $tbl->add_field('action',XMLDB_TYPE_CHAR,'25',null, XMLDB_NOTNULL, null,null);
                // time sent
                $tbl->add_field('timereceived',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

                // Keys
                $tbl->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Index
                $tbl->add_index('fodselsnr',XMLDB_INDEX_NOTUNIQUE,array('fodselsnr'));
                $tbl->add_index('enhet_id',XMLDB_INDEX_NOTUNIQUE,array('org_enhet_id'));
                $tbl->add_index('jrcode',XMLDB_INDEX_NOTUNIQUE,array('stillingskode'));
                $tbl->add_index('received',XMLDB_INDEX_NOTUNIQUE,array('timereceived'));

                // Create table
                $dbman->create_table($tbl);
            }//if_exists

            // Add extra fields and index
            $tbl = new xmldb_table('fs_users_competence');
            $tblfield = new xmldb_field('timesync', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);
            if (!$dbman->field_exists($tbl, $tblfield)) {
                $dbman->add_field($tbl, $tblfield);
            }//if_not_exists
            /**
            // Index - companyid
            $index = new xmldb_index('companyid', XMLDB_INDEX_NOTUNIQUE, array('companyid'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // index personalnumber
            $index = new xmldb_index('pnumber', XMLDB_INDEX_NOTUNIQUE, array('personalnumber'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // Index - time sync
            $index = new xmldb_index('timesync', XMLDB_INDEX_NOTUNIQUE, array('timesync'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }

            $tbl = new xmldb_table('fs_imp_users_jr');
            // fodselsnr
            $index = new xmldb_index('fodselsnr', XMLDB_INDEX_NOTUNIQUE, array('fodselsnr'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // org_enhet_id
            $index = new xmldb_index('enhet_id', XMLDB_INDEX_NOTUNIQUE, array('org_enhet_id'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // stillingskode
            $index = new xmldb_index('jrcode', XMLDB_INDEX_NOTUNIQUE, array('stillingskode'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }**/
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//fs_imp_users_jr_log

    /**
     * Description
     * Log/historical for jobroles coming from TARDIS
     *
     * @param           $dbman
     *
     * @throws          Exception
     *
     * @creationDate    09/10/2017
     * @author          eFaktor     (fbv)
     */
    public static function fs_imp_jobroles_log($dbman) {
        /* Variables */
        $tbl = null;

        try {
            if (!$dbman->table_exists('fs_imp_jobroles_log')) {
                // fs_imp_jobroles_log table
                $tbl = new xmldb_table('fs_imp_jobroles_log');

                // Fields
                // Id primary key
                $tbl->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // stillingskode    --> Job Role code
                $tbl->add_field('stillingskode',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
                // stillingstekst   --> Job Role Name
                $tbl->add_field('stillingsstekst',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // alternative      --> alternative name
                $tbl->add_field('stillingsstekst_alternativ',XMLDB_TYPE_CHAR,'255',null, null, null,null);
                // action           --> Action to apply
                $tbl->add_field('action',XMLDB_TYPE_CHAR,'25',null, XMLDB_NOTNULL, null,null);
                // time sent
                $tbl->add_field('timereceived',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

                // Keys
                $tbl->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Index
                $tbl->add_index('jrcode',XMLDB_INDEX_NOTUNIQUE,array('stillingskode'));
                $tbl->add_index('received',XMLDB_INDEX_NOTUNIQUE,array('timereceived'));

                // Create table
                $dbman->create_table($tbl);
            }//if_exists

            // Extra - missing index
            $tbl = new xmldb_table('fs_imp_jobroles');
            $index = new xmldb_index('jrcode', XMLDB_INDEX_NOTUNIQUE, array('stillingskode'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//fs_imp_jobroles_log

    /**
     * Description
     * Log for jobroles mapped
     *
     * @param           $dbman
     *
     * @throws          Exception
     *
     * @creationDate    09/10/2017
     * @author          eFaktor     (fbv)
     */
    public static function fs_jobroles_sync_log($dbman) {
        /* Variables */
        $tbl = null;

        try {
            if (!$dbman->table_exists('fs_jobroles_sync_log')) {
                // fs_jobroles_sync_log table
                $tbl = new xmldb_table('fs_jobroles_sync_log');

                // Fields
                // Id primary key
                $tbl->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // fsjobrole
                $tbl->add_field('fsjobrole',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
                // ksjobrole
                $tbl->add_field('ksjobrole',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
                // name
                $tbl->add_field('name',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // action
                $tbl->add_field('action',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
                // time mapped
                $tbl->add_field('timemapped',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

                // Keys
                $tbl->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Index
                $tbl->add_index('fsjobrole',XMLDB_INDEX_NOTUNIQUE,array('fsjobrole'));
                $tbl->add_index('ksjobrole',XMLDB_INDEX_NOTUNIQUE,array('ksjobrole'));
                $tbl->add_index('timemapped',XMLDB_INDEX_NOTUNIQUE,array('timemapped'));

                // Create table
                $dbman->create_table($tbl);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//fs_jobroles_sync_log

    /**
     * Description
     * Log/historical for users coming from TARDIS
     *
     * @param           $dbman
     *
     * @throws          Exception
     *
     * @creationDate    09/10/2017
     * @author          eFaktor     (fbv)
     */
    public static function fs_imp_users_log($dbman) {
        /* Variables */
        $tbl = null;

        try {
            if (!$dbman->table_exists('fs_imp_users_log')) {
                // fs_imp_users_log table
                $tbl = new xmldb_table('fs_imp_users_log');

                // Fields
                // Id primary key
                $tbl->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // personalnumber --> Personal number
                $tbl->add_field('fodselsnr',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
                // Resource number
                $tbl->add_field('ressursnr',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
                // firstname    --> First name
                $tbl->add_field('fornavn',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // middle name
                $tbl->add_field('mellomnavn',XMLDB_TYPE_CHAR,'255',null, null, null,null);
                // last name
                $tbl->add_field('etternavn',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
                // email
                $tbl->add_field('epost',XMLDB_TYPE_CHAR,'255',null, null, null,null);
                // ADFS ID
                $tbl->add_field('brukernavn',XMLDB_TYPE_CHAR,'50',null, null, null,null);
                // action
                $tbl->add_field('action',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
                // time sent
                $tbl->add_field('timereceived',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                // Keys
                $tbl->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Index
                $tbl->add_index('fodselsnr',XMLDB_INDEX_NOTUNIQUE,array('fodselsnr'));
                $tbl->add_index('timereceived',XMLDB_INDEX_NOTUNIQUE,array('timereceived'));
                $tbl->add_index('brukernavn',XMLDB_INDEX_NOTUNIQUE,array('brukernavn'));

                // Crete table
                $dbman->create_table($tbl);
            }//if_exists

            // Missing indexes
            $tbl = new xmldb_table('fs_imp_users');
            // fodselsnr
            $index = new xmldb_index('fodselsnr', XMLDB_INDEX_NOTUNIQUE, array('fodselsnr'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // brukernavn
            $index = new xmldb_index('brukernavn', XMLDB_INDEX_NOTUNIQUE, array('brukernavn'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//fs_imp_users_log

    /**
     * Description
     * Log for synchronized users
     *
     * @param           $dbman
     *
     * @throws          Exception
     *
     * @creationDate    09/10/2017
     * @author          eFaktor     (fbv)
     */
    public static function fs_users_sync_log($dbman) {
        /* Variables */
        $tbl = null;

        try {
            if (!$dbman->table_exists('fs_users_sync_log')) {
                // fs_users_sync_log table
                $tbl = new xmldb_table('fs_users_sync_log');

                // Fields
                // Id primary key
                $tbl->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
                // username
                $tbl->add_field('username',XMLDB_TYPE_CHAR,'255',null, null, null,null);
                // idnumber - personal number
                $tbl->add_field('idnumber',XMLDB_TYPE_CHAR,'50',null, null, null,null);
                // adfs
                $tbl->add_field('adfs',XMLDB_TYPE_CHAR,'50',null, null, null,null);
                // firstname
                $tbl->add_field('firstname',XMLDB_TYPE_CHAR,'255',null, null, null,null);
                // lastname
                $tbl->add_field('lastname',XMLDB_TYPE_CHAR,'255',null, null, null,null);
                // email
                $tbl->add_field('email',XMLDB_TYPE_CHAR,'255',null, null, null,null);
                // action
                $tbl->add_field('action',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
                // time sync
                $tbl->add_field('timesync',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

                // Keys
                $tbl->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                // Index
                $tbl->add_index('username',XMLDB_INDEX_NOTUNIQUE,array('username'));
                $tbl->add_index('adfs',XMLDB_INDEX_NOTUNIQUE,array('adfs'));
                $tbl->add_index('idnumber',XMLDB_INDEX_NOTUNIQUE,array('idnumber'));
                $tbl->add_index('timesync',XMLDB_INDEX_NOTUNIQUE,array('timesync'));

                // Crete table
                $dbman->create_table($tbl);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//fs_users_sync_log

    /**
     * Description
     * Log/historical of all companies coming from TARDIS
     *
     * @param           $dbman
     *
     * @throws          Exception
     *
     * @creationDate    09/10/2017
     * @author          eFaktor     (fbv)
     */
    public static function fs_imp_company_log($dbman) {
        /* Variables */
        $tbl = null;

        try {
            // Table
            $tbl = new xmldb_table('fs_imp_comp_log');

            // Fields
            // Id --> primary key
            $tbl->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            // org_enhet_id     --> Company Id from fellesdata
            $tbl->add_field('org_enhet_id',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            // org_nivaa        --> Hierarchy level from fellesdata
            $tbl->add_field('org_nivaa',XMLDB_TYPE_INTEGER,'2',null, XMLDB_NOTNULL, null,null);
            // org_navn         --> Company name
            $tbl->add_field('org_navn',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            // org_enhet_over   --> Parent company
            $tbl->add_field('org_enhet_over',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            // privat --> public
            $tbl->add_field('privat',XMLDB_TYPE_INTEGER,'1',null, null, null,null);
            // ansvar
            $tbl->add_field('ansvar',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            // tjeneste
            $tbl->add_field('tjeneste',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            // adresse1
            $tbl->add_field('adresse1',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            // adresse2
            $tbl->add_field('adresse2',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            // adresse3
            $tbl->add_field('adresse3',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            // postnr
            $tbl->add_field('postnr',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            // poststed
            $tbl->add_field('poststed',XMLDB_TYPE_CHAR,'50',null, null, null,null);
            // epost
            $tbl->add_field('epost',XMLDB_TYPE_CHAR,'255',null, null, null,null);
            // action
            $tbl->add_field('action',XMLDB_TYPE_CHAR,'25',null, XMLDB_NOTNULL, null,null);
            // time sent
            $tbl->add_field('timereceived',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);

            // Keys
            $tbl->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            // Index

            $tbl->add_index('co',XMLDB_INDEX_NOTUNIQUE,array('org_enhet_id'));
            $tbl->add_index('nu',XMLDB_INDEX_NOTUNIQUE,array('org_nivaa'));
            $tbl->add_index('ov',XMLDB_INDEX_NOTUNIQUE,array('org_enhet_over'));
            $tbl->add_index('tr',XMLDB_INDEX_NOTUNIQUE,array('timereceived'));

            if (!$dbman->table_exists('fs_imp_comp_log')) {
                $dbman->create_table($tbl);
            }//if_exists


            // Add extra fields and index
            $tbl = new xmldb_table('fs_company');
            $tblfield = new xmldb_field('timesync', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0);
            if (!$dbman->field_exists($tbl, $tblfield)) {
                $dbman->add_field($tbl, $tblfield);
            }//if_not_exists

            // companyid
            $index = new xmldb_index('co', XMLDB_INDEX_NOTUNIQUE, array('companyid'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // Index - fs_parent
            $index = new xmldb_index('fs_parent', XMLDB_INDEX_NOTUNIQUE, array('fs_parent'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // index parent
            $index = new xmldb_index('parent', XMLDB_INDEX_NOTUNIQUE, array('parent'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // Index - time sync
            $index = new xmldb_index('timesync', XMLDB_INDEX_NOTUNIQUE, array('timesync'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }

            $tbl = new xmldb_table('fs_imp_company');
            // org_enhet_id
            $index = new xmldb_index('enhet_id', XMLDB_INDEX_NOTUNIQUE, array('org_enhet_id'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // org_nivaa
            $index = new xmldb_index('nivaa', XMLDB_INDEX_NOTUNIQUE, array('org_nivaa'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
            // org_enhet_over
            $index = new xmldb_index('enhet_over', XMLDB_INDEX_NOTUNIQUE, array('org_enhet_over'));
            if (!$dbman->index_exists($tbl, $index)) {
                $dbman->add_index($tbl, $index);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//fs_imp_company_log

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
            $tblUsersFSJR->add_field('companyid',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
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
            $tblImpManagersReporters->add_field('org_enhet_id',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
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
            $tblImpManagersReporters->add_index('enhet_id',XMLDB_INDEX_NOTUNIQUE,array('org_enhet_id'));

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

    /**
     * @param           $dbMan
     * @throws          Exception
     *
     * @creationDate    17/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create tables:
     * mdl_ksfs_org_unmap
     * mdl_ksfs_jr_unmap
     */
    public static function UnMapTables($dbMan) {
        try {
            self::UnMapOrg_Table($dbMan);
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//UnMapTables

    /**
     * Description
     * Create mdl_fs_suspicious table
     *
     * @creationDate    26/12/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $dbMan
     *
     * @throws          Exception
     */
    public static function add_suspicious($dbMan) {
        /* Variables */
        $tblSuspicious = null;

        try {
            // table
            $tblSuspicious = new xmldb_table('fs_suspicious');

            // Fields
            // id               - Primary key
            $tblSuspicious->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            // file             - Name of the file
            $tblSuspicious->add_field('file',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            // path             - Location
            $tblSuspicious->add_field('path',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            // impfs            - Type of imformation to import
            $tblSuspicious->add_field('impfs',XMLDB_TYPE_CHAR,'50',null, XMLDB_NOTNULL, null,null);
            $tblSuspicious->add_field('token',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,0);
            // detected         - When the file was marked as suspicious
            $tblSuspicious->add_field('detected',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            // approved         - Approved or not
            $tblSuspicious->add_field('approved',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,null);
            // rejected         - Rejected or not
            $tblSuspicious->add_field('rejected',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,null);
            // notificationsent - Notification has been sent
            $tblSuspicious->add_field('notificationsent',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            // remaindersent    - Remainder has been sent
            $tblSuspicious->add_field('remaindersent',XMLDB_TYPE_INTEGER,'10',null, null, null,null);
            // remainder        - Remainder has to be sent
            $tblSuspicious->add_field('remainder',XMLDB_TYPE_INTEGER,'10',null, null, null,null);

            // Keys
            $tblSuspicious->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            // Create table
            if (!$dbMan->table_exists('fs_suspicious')) {
                $dbMan->create_table($tblSuspicious);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_suspicious

    /**
     * Description
     * Create mdl_fs_suspucious_action table
     *
     * @creationDate    26/12/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $dbMan
     *
     * @throws          Exception
     */
    public static function add_suspicious_action($dbMan) {
        /* Variables */
        $tblAction = null;

        try {
            // table
            $tblAction = new xmldb_table('fs_suspicious_action');

            // Fields
            // id           - Primary key
            $tblAction->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            // suspiciousid - Fireign key to mdl_fs_suspicious
            $tblAction->add_field('suspiciousid',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            // action       - (1) Approve (2) Reject
            $tblAction->add_field('action',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,null);
            // token        - Token connected with the action
            $tblAction->add_field('token',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);

            // Keys
            $tblAction->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            // Create table
            if (!$dbMan->table_exists('fs_suspicious_action')) {
                $dbMan->create_table($tblAction);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_suspicious_action
    
    /***********/
    /* PRIVATE */
    /***********/
    
    /**
     * @param           $dbMan
     * @throws          Exception
     *
     * @creationDate    17/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create table mdl_ksfs_org_unmap
     */
    private static function UnMapOrg_Table($dbMan) {
        /* Variables */
        $tblUnMap = null;

        try {
            /* Table */
            $tblUnMap = new xmldb_table('ksfs_org_unmap');

            /* Id               --> Primary key.  */
            $tblUnMap->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
            /* kscompany        --> Foreign key     */
            $tblUnMap->add_field('kscompany',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, null,null);
            /* fscompany        --> Foreign key     */
            $tblUnMap->add_field('fscompany',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
            /* tosync           --> Not null. To be synchronized or not     */
            $tblUnMap->add_field('tosync',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,null);
            /* sync             --> Not null. If it is already synchronized */
            $tblUnMap->add_field('sync',XMLDB_TYPE_INTEGER,'1',null, XMLDB_NOTNULL, null,null);

            /* Keys */
            $tblUnMap->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $tblUnMap->add_key('kscompany',XMLDB_KEY_FOREIGN,array('kscompany'), 'ks_company', array('companyid'));
            $tblUnMap->add_key('fscompany',XMLDB_KEY_FOREIGN,array('fscompany'), 'fs_company', array('companyid'));

            /* Create table */
            if (!$dbMan->table_exists('ksfs_org_unmap')) {
                $dbMan->create_table($tblUnMap);
            }//if_exists
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UnMapOrg_Table
    
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