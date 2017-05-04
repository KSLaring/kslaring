<?php
/**
 * Fellesdata Integration - Library
 *
 * @package         local/fellesdata
 * @subpackage      lib
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    01/02/2016
 * @author          eFaktor     (fbv)
 *
 */
/* Fellesdata Services  */
define('TRADIS_FS_COMPANIES','v_leka_oren_tre_nivaa');
define('TRADIS_FS_USERS','v_leka_ressurs');
define('TRADIS_FS_JOBROLES','v_leka_stilling');
define('TRADIS_FS_MANAGERS_REPORTERS','v_leka_oren_tilgang');
define('TRADIS_FS_USERS_JOBROLES','v_leka_ressurs_stilling');

/* KS Services  */
define('KS_SYNC_FS_COMPANY','wsFSCompany');
define('KS_ORG_STRUCTURE','wsKSOrganizationStructure');
define('KS_SYNC_FS_JOBROLES','wsFSJobRoles');
define('KS_JOBROLES','wsKSJobRoles');
define('KS_JOBROLES_GENERICS','wsKSJobRolesGenerics');
define('KS_MANAGER_REPORTER','wsManagerReporter');
define('KS_USER_COMPETENCE','wsUserCompetence');

define('KS_UNMAP_USER_COMPETENCE','wsUnMapUserCompetence');
define('KS_UNMAP_COMPANY','wsUnMapCompany');

define('KS_USER_MANAGER','wsManagerCompany');
define('KS_SYNC_USER_ACCOUNT','wsUsersAccounts');

define('KS_CLEAN_STATUS','wsCleanSynchronization');

define('ADD_ACTION','add');
define('UPDATE_ACTION','modify');
define('DELETE_ACTION','delete');

define('ADD',0);
define('UPDATE',1);
define('DELETE',2);
define('STATUS',3);

define('IMP_USERS',0);
define('IMP_COMPANIES',1);
define('IMP_JOBROLES',2);
define('IMP_MANAGERS_REPORTERS',3);
define('IMP_COMPETENCE_JR',4);
define('MAX_IMP_FS',5000);

define('CLEAN_MANAGERS_REPORTERS',0);
define('CLEAN_COMPETENCE',1);

/***********************/
/* CLASS FSKS_JOBROLES */
/***********************/
class FS_CRON {
    public static function can_run() {
        /* variables */
        $fellesdata = null;
        $fsdate     = null;
        $status     = null;
        $stdate     = null;
        
        try {
            // Next time for fellesdata
            $fellesdata = self::get_my_nexttime('fellesdata');
            
            // Next time for status
            $status = self::get_my_nexttime('status');

            if ($status) {
                if ($fellesdata->disabled) {
                    return false;
                }else if ($status->disabled) {
                    return true;
                }else {
                    $fsdate = getdate($fellesdata->nextruntime);
                    $stdate = getdate($status->lastruntime);

                    if ($fsdate && $stdate) {
                        if (($fsdate['year'] == $stdate['year'])
                            &&
                            ($fsdate['mon'] == $stdate['mon'])
                            &&
                            ($fsdate['mday'] == $stdate['mday'])) {

                            return false;
                        }else {
                            return true;
                        }
                    }
                    return true;
                }//fellesdata_disable
            }else {
                return true;
            }//if_status

        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//can_run

    /**
     * Description
     * Get the next time that the task has to be executed
     * 
     * @param       String $pluginmame
     * 
     * @return              mixed|null
     * @throws              Exception
     * 
     * @creationDate    19/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_my_nexttime($pluginmame) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // SQL instruction - get mu nextime
            $sql = " SELECT nextruntime,
                            lastruntime,
                            disabled
                     FROM 	{task_scheduled}
                     WHERE   component like '%$pluginmame%' ";
            
            // Execute
            $rdo = $DB->get_record_sql($sql);
            
            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_my_nexttime
}

class FSKS_JOBROLES {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Check if there are new job roles that have to be mapped and synchronized
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    03/11/2016
     * @author          eFaktor     (fbv)
     */
    public static function jobroles_fs_tosynchronize_mailing() {
        /* Variables */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $params         = null;
        $lstJobRoles    = array();

        try {
            // Search Criteria
            $params = array();
            $params['imported'] = 0;
            $params['add']      = DELETE;

            // SQL Instruction
            $sql = " SELECT   DISTINCT
                                  fs.id,
                                  fs.stillingskode,
                                  fs.stillingstekst
                     FROM		  {fs_imp_jobroles}   fs
                        JOIN	  {fs_jobroles}		fsjr	ON  fsjr.jrcode 	= fs.stillingskode
                        LEFT JOIN {ksfs_jobroles}		ksfs	ON 	ksfs.fsjobrole  = fsjr.jrcode
                     WHERE  	  ksfs.id IS NULL
                          AND     fs.action   != :add
                     ORDER BY     fs.stillingskode, fs.stillingstekst ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params,0,5);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lstJobRoles[$instance->id] = $instance->stillingskode . " - " . $instance->stillingstekst;
                }//for_rdo
            }//if_Rdo

            return $lstJobRoles;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//jobroles_fs_tosynchronize_mailing

    /**
     * @param           $toSynchronize
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize job roles
     */
    public static function synchronize_jobroles($toSynchronize) {
        /* Variables */

        try {
            /* Synchronize Job Role */
            foreach ($toSynchronize as $jobRole) {
                self::synchronize_jobrole_fs($jobRole);
            }//for_to_synchronize
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronize_jobroles

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @return          array
     *
     * @throws          Exception
     *
     * @creationDate    10/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get job roles to synchronize
     */
    private static function GetJobRoles_ToSynchronize() {
        /* Variables */
        global $DB;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $infoJR         = null;
        $toSynchronize  = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['imported'] = 0;
            $params['add']      = ADD;

            /* SQL Instruction */
            $sql = " SELECT	  fs_imp.id,
                              fs_imp.stillingskode,
                              fs_imp.stillingstekst,
                              fs_imp.stillingstekst_alternativ,
                              fs_imp.action
                     FROM	  {fs_imp_jobroles}	fs_imp
                     WHERE	  fs_imp.imported  = :imported
                        AND   fs_imp.action   != :add
                     ORDER BY fs_imp.stillingstekst ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach($rdo as $instance) {
                    /* Info Job Role    */
                    $infoJR = new stdClass();
                    $infoJR->id             = $instance->id;
                    $infoJR->jrcode         = $instance->stillingskode;
                    $infoJR->jrname         = $instance->stillingstekst;
                    $infoJR->jralternative  = $instance->stillingstekst_alternativ;
                    $infoJR->action         = $instance->action;

                    /* Add Job Role */
                    $toSynchronize[$instance->id] = $infoJR;
                }//for_Rdo
            }//if_rdo

            return $toSynchronize;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetJobRoles_ToSynchronize

    /**
     * Description
     * Synchronize job role
     *
     * @param           $jobRole
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function synchronize_jobrole_fs($jobRole) {
        /* Variables */
        global $DB;
        $infoImp        = null;
        $rdoFS          = null;
        $params         = null;
        $time           = null;
        $sync           = null;
        $trans          = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local Time
            $time = time();

            // Get Info Job Role FS
            $params = array();
            $params['jrcode'] = $jobRole->jrcode;
            $rdoFS = $DB->get_record('fs_jobroles',$params);

            // Apply action
            switch ($jobRole->action) {
                case UPDATE:
                    if ($rdoFS) {
                        $rdoFS->jrname          = $jobRole->jrname;
                        $rdoFS->jralternative   = $jobRole->jralternative;
                        $rdoFS->synchronized    = 1;
                        $rdoFS->timemodified    = $time;

                        // Execute
                        $DB->update_record('fs_jobroles',$rdoFS);

                        // Synchronized
                        $sync = true;
                    }//if_exist

                    break;

                case DELETE:
                    if ($rdoFS) {
                        // Delete fs_jobroles
                        $DB->delete_records('fs_jobroles',array('id' => $rdoFS->id));

                        // Delete KSFS Job roles
                        $params = array();
                        $params['fsjobrole'] = $jobRole->jrcode;
                        /* Execute  */
                        $DB->delete_records('ksfs_jobroles',$params);

                        // Synchronized
                        $sync = true;
                    }//if_exist

                    break;
            }//action

            // Synchronized
            if ($sync) {
                $infoImp = new stdClass();
                $infoImp->id            = $jobRole->id;
                $infoImp->imported      = 1;
                $infoImp->timemodified  = $time;

                // Execute
                $DB->update_record('fs_imp_jobroles',$infoImp);
            }//if_sync

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//synchronize_jobrole_fs
}//FSKS_JOBROLES


/**********************/
/* CLASS FSKS_COMAPNY */
/**********************/
class FSKS_COMPANY {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Get all companies mapped as new that have to be synchornized
     * @param           integer $start
     * @param           integer $end
     *
     * @return                  array
     * @throws                  Exception
     *
     * @creationDate    17/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_new_companiesfs_to_synchronize($start,$end) {
        /* Variables    */
        global $DB;
        $infoCompany    = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $toSynchronize  = array();

        try {
            // Search Criteria
            $params = array();
            $params['synchronized'] = 0;
            $params['new']          = 1;

            // SQL Instruction
            $sql = " SELECT	  DISTINCT 
                                  fs.companyid                          as 'fsid',
                                  IF(ks_fs.id,ks_fs.kscompany,0)        as 'ksid',
                                  TRIM(fs.name)                         as 'name',
                                  fs.level,
                                  ks.industrycode                       as 'industry',
                                  fs.parent,
                                  IF(fs.privat,0,1)                     as 'public',
                                  TRIM(IF(fs.ansvar,fs.ansvar,0))       as 'ansvar',
                                  TRIM(IF(fs.tjeneste,fs.tjeneste,0))   as 'tjeneste',
                                  TRIM(IF(fs.adresse1,fs.adresse1,0))   as 'adresse1',
                                  TRIM(IF(fs.adresse2,fs.adresse2,0))   as 'adresse2',
                                  TRIM(IF(fs.adresse3,fs.adresse3,0))   as 'adresse3',
                                  TRIM(IF(fs.postnr,fs.postnr,0))       as 'postnr',
                                  TRIM(IF(fs.poststed,fs.poststed,0))   as 'poststed',
                                  TRIM(IF(fs.epost,fs.epost,0))         as 'epost',
                                  '0'                                   as 'action'
                     FROM		  {fs_company}	  fs
                        JOIN      {ks_company}	  ks 	ON ks.companyid     = fs.parent
                        LEFT JOIN {ksfs_company}  ks_fs	ON ks_fs.fscompany 	= fs.companyid
                     WHERE	      fs.synchronized = :synchronized
                          AND	  fs.new 		  = :new
                     ";


            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$end);
            if ($rdo) {
                $toSynchronize = json_encode($rdo);
            }//if_rdo

            return array($toSynchronize,$rdo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_new_companiesfs_to_synchronize

    /**
     * Description
     * Get all companies mapped as a new that have to be synchronized
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    17/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_total_new_companiesfs_to_synchronize() {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['new']  = 1;
            $params['sync'] = 0;

            // SQL Instruction
            $sql = " SELECT	      count(DISTINCT fs.id)   as 'total'
                     FROM		  {fs_company}	  fs
                        JOIN      {ks_company}	  ks 	ON ks.companyid     = fs.parent
                        LEFT JOIN {ksfs_company}  ks_fs	ON ks_fs.fscompany 	= fs.companyid
                     WHERE	      fs.synchronized = :sync
                        AND	  	  fs.new 		  = :new ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_new_companiesfs_to_synchronize


    /**
     * Description
     * Get all comapnies mapped that have to be synchornized
     *
     * @param           $start
     * @param           $end
     * @param           $status
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    09/02/0216
     * @author          eFaktor     (fbv)
     */
    public static function get_update_companiesfs_to_synchronize($start,$end,$status = false) {
        /* Variables    */
        global $DB;
        $infoCompany    = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $toSynchronize  = array();

        try {
            // Search Criteria
            $params = array();
            $params['new']          = 0;
            $params['imported']     = 1;
            $params['synchronized'] = 0;

            // SQL Instruction
            $sql = " SELECT DISTINCT 
                              fs.companyid                    as 'fsid',
                              fk.kscompany                    as 'ksid',
                              TRIM(fs.name)                   as 'name',
                              fs.level,
                              fs.parent,
                              ks_pa.industrycode                    as 'industry',
                              IF(fs.privat,0,1) 	                as 'public',
                              TRIM(IF(fs.ansvar,fs.ansvar,0))       as 'ansvar',
                              TRIM(IF(fs.tjeneste,fs.tjeneste,0))   as 'tjeneste',
                              TRIM(IF(fs.adresse1,fs.adresse1,0))   as 'adresse1',
                              TRIM(IF(fs.adresse2,fs.adresse2,0))   as 'adresse2',
                              TRIM(IF(fs.adresse3,fs.adresse3,0))   as 'adresse3',
                              TRIM(IF(fs.postnr,fs.postnr,0))       as 'postnr',
                              TRIM(IF(fs.poststed,fs.poststed,0))   as 'poststed',
                              TRIM(IF(fs.epost,fs.epost,0))         as 'epost',
                              fs_imp.action
                     FROM	  {fs_company}		fs
                        JOIN  {fs_imp_company}	fs_imp 	ON 	fs_imp.org_enhet_id = fs.companyid
                                                        AND fs_imp.imported     = :imported
                        -- INFO KS
                        JOIN  {ksfs_company}	fk 		ON 	fk.fscompany 	    = fs.companyid
                        -- INFO PARENT
                        JOIN  {ks_company}		ks_pa	ON 	ks_pa.companyid     = fk.kscompany
                     WHERE	  fs.new 			= :new
                        AND   fs.synchronized   = :synchronized ";

            // Status criterua
            if ($status) {
                $params['action'] = STATUS;
                $sql .= " fs_imp.action = : action ";
            }
            
            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$end);
            if ($rdo) {
                $toSynchronize = json_encode($rdo);
            }//if_rdo

            return array($toSynchronize,$rdo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_update_companiesfs_to_synchronize

    /**
     * Description
     * Get total of companies mapped that have to be synchronized
     *
     * @param           $status
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    17/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_total_update_companiesfs_to_synchronize($status = false) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['new']          = 0;
            $params['imported']     = 1;
            $params['synchronized'] = 0;

            // SQL Instruction
            $sql = " SELECT DISTINCT 
                              count(DISTINCT fs.id)	as 'total'
                     FROM	  {fs_company}		fs
                        JOIN  {fs_imp_company}	fs_imp 	ON 	fs_imp.org_enhet_id = fs.companyid
                                                        AND fs_imp.imported     = :imported
                        -- INFO KS
                        JOIN  {ksfs_company}	fk 		ON 	fk.fscompany 	    = fs.companyid
                        -- INFO PARENT
                        JOIN  {ks_company}		ks_pa	ON 	ks_pa.companyid     = fk.kscompany
                     WHERE	  fs.new 			= :new
                        AND   fs.synchronized   = :synchronized ";

            // Status criterua
            if ($status) {
                $params['action'] = STATUS;
                $sql .= " fs_imp.action = : action ";
            }

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_update_companiesfs_to_synchronize

    /**
     * Description
     * Synchronize companies between FS and KS
     *
     * @param           $companiesFSKS
     * @param           $companiesImported
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     */
    public static function synchronize_companies_ksfs($companiesFSKS,$companiesImported) {
        /* Variables */
        $infoCompany    = null;
        $objCompany     = null;
        
        try {
            $keys = array_keys($companiesFSKS);
            // Synchronize companies that have been imported
            foreach ($companiesImported as  $company) {
                // Convert to object
                $objCompany = (Object)$company;

                if ($objCompany->imported) {
                    // Get Company
                    if (in_array($objCompany->fsId,$keys)) {
                        $index = array_search($objCompany->fsId,$keys);
                        $infoCompany        = $companiesFSKS[$keys[$index]];
                    }

                    $infoCompany->ksid  = $objCompany->ksId;

                    // Synchronize Company
                    self::synchronize_company_ksfs($infoCompany,$objCompany->fsId);
                }//if_imported
            }//for_companiesFS
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronize_companies_ksfs

    /**
     * Description
     * Synchronize companies only on FS site.
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     */
    public static function synchronize_companies_fs() {
        /* Variables    */
        $companiesFS = null;

        try {
            /* Get Companies to synchronize only in FS  */
            $companiesFS = self::get_companiesfs_to_synchronizefs();

            /* Synchronize /update  FS Company  */
            if ($companiesFS) {
                foreach ($companiesFS as $company) {
                    self::synchronize_company_fs($company);
                }//for_company
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronize_companies_fs

    /**
     * Description
     * Get all companies that have to be synchronized manually.
     *
     * @param           $notIn
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_companiesfs_to_mail($notIn = 0) {
        /* Variables    */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $companiesFS    = array();
        $params         = null;

        try {
            // Search Criteria
            $params = array();
            $params['add'] = ADD;

            // SQL Instruction
            $sql = " SELECT   DISTINCT
                                  fs_imp.id,
                                  fs_imp.org_navn,
                                  fs.companyid,
                                  fs.synchronized
                     FROM 	      {fs_imp_company}	fs_imp
                        LEFT JOIN {fs_company}		fs		ON fs.companyid = fs_imp.ORG_ENHET_ID
                     WHERE 	      fs_imp.imported 	 = 0
                          AND     fs.synchronized    = 0
                          AND     fs_imp.org_nivaa 	!= 4
                          AND     fs_imp.action      = :add
                          AND     fs_imp.id NOT IN ($notIn) 
                     ORDER BY   fs_imp.org_navn 
                     LIMIT 0,5 ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $companiesFS[$instance->id] = $instance->org_navn;
                }//for_rdo
            }//if_rdo

            return $companiesFS;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_companiesfs_to_mail
    
    /**
     * Description
     * Get companies to unmap
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     */
    public static function companies_to_unmap() {
        /* Variables */
        global $DB;
        $params     = null;
        $info       = null;
        $toUnMap    = array();
        $info       = null;
        

        try {
            // Search criteria
            $params = array();
            $params['tosync']   = 1;
            $params['sync']     = 0;

            // Execute
            $rdo = $DB->get_records('ksfs_org_unmap',$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->kscompany    = $instance->kscompany;
                    $toUnMap[$info->id] = $info;
                }//for_rdo
            }//if_rdo

            return $toUnMap;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//companies_to_unmap

    /**
     * @param           $unMapped
     *
     * @throws          Exception
     *
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unmap companies
     */
    public static function unmap_companies_ksfs($unMapped) {
        /* Variables */
        global $DB;
        $infoCompany    = null;
        $objUnMapped    = null;

        try {
            // Unmap companies
            foreach ($unMapped as $company) {
                // Convert to object
                $objCompany = (Object)$company;

                if ($objCompany->unmapped) {
                    // Unmap company
                    $DB->delete_records('ksfs_org_unmap',array('id' => $objCompany->id));
                }//unmapped
            }//for_unmap
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//unmap_companies_ksfs

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Get all companies that have to be updated or deleted only in the FS site
     *
     * @return          array
     *
     * @throws          Exception
     *
     * @creationDate    09/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function get_companiesfs_to_synchronizefs() {
        /* Variables    */
        global $DB;
        $synchronizeFS  = array();
        $infoCompany    = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            // Search Criteria
            $params = array();
            $params['imported'] = 0;
            $params['add']      = ADD;

            // SQL Instruction
            $sql = " SELECT DISTINCT
                            fs.id,
                            fs.org_enhet_id   as 'companyid',
                            fs.org_navn       as 'name',
                            fs.org_enhet_over as 'fs_parent',
                            fs.privat,
                            IF(fs.ansvar,fs.ansvar,0)       as 'ansvar',
                            IF(fs.tjeneste,fs.tjeneste,0)   as 'tjeneste',
                            IF(fs.adresse1,fs.adresse1,0)   as 'adresse1',
                            IF(fs.adresse2,fs.adresse2,0)   as 'adresse2',
                            IF(fs.adresse3,fs.adresse3,0)   as 'adresse3',
                            IF(fs.postnr,fs.postnr,0)       as 'postnr',
                            IF(fs.poststed,fs.poststed,0)   as 'poststed',
                            IF(fs.epost,fs.epost,0)         as 'epost',
                            fs.action
                     FROM   {fs_imp_company}	fs
                     WHERE	fs.action 	 != :add
                        AND	fs.imported  = :imported ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Info Company
                    $infoCompany = new stdClass();
                    $infoCompany->id            = $instance->id;
                    $infoCompany->fscompany     = $instance->companyid;
                    $infoCompany->name          = $instance->name;
                    $infoCompany->fs_parent     = $instance->fs_parent;
                    $infoCompany->privat        = $instance->privat;
                    $infoCompany->ansvar        = $instance->ansvar;
                    $infoCompany->tjeneste      = $instance->tjeneste;
                    $infoCompany->adresseOne    = $instance->adresse1;
                    $infoCompany->adresseTwo    = $instance->adresse2;
                    $infoCompany->adresseThree  = $instance->adresse3;
                    $infoCompany->postnr        = $instance->postnr;
                    $infoCompany->poststed      = $instance->poststed;
                    $infoCompany->epost         = $instance->epost;
                    $infoCompany->action        = $instance->action;

                    // Add company
                    $synchronizeFS[$instance->id] = $infoCompany;
                }//for_rdo
            }//if_rdo

            return $synchronizeFS;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_companiesfs_to_synchronizefs

    /**
     * @param           $companyKSFS
     * @param           $impKey
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize company between FS and KS sites.
     */
    private static function synchronize_company_ksfs($companyKSFS,$impKey) {
        /* Variables    */
        global $DB;
        $rdoCompany     = null;
        $rdoRelation    = null;
        $rdo            = null;
        $params         = null;
        $infoCompany    = null;
        $infoRelation   = null;
        $instance       = null;
        $time           = null;
        $sync           = false;
        $trans          = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local Time
            $time = time();

            // Company Info to check if already exists or no
            $params = array();
            $params['companyid'] = $companyKSFS->fsid;
            $rdoCompany = $DB->get_record('fs_company',$params,'id');

            //  Apply Synchronization
            switch ($companyKSFS->action) {
                case ADD:
                case UPDATE:
                case STATUS:
                    if ($rdoCompany) {
                        $rdoCompany->name          = $companyKSFS->name;
                        $rdoCompany->parent        = $companyKSFS->parent;
                        $rdoCompany->synchronized  = 1;
                        $rdoCompany->timemodified  = $time;

                        // Execute
                        $DB->update_record('fs_company',$rdoCompany);

                        // Insert KS Company
                        // Check if already exists
                        $rdo = $DB->get_record('ks_company',array('companyid' => $companyKSFS->ksid));
                        if (!$rdo) {
                            $infoCompany = new stdClass();
                            $infoCompany->companyid         = $companyKSFS->ksid;
                            $infoCompany->name              = $companyKSFS->name;
                            $infoCompany->industrycode      = $companyKSFS->industry;
                            $infoCompany->hierarchylevel    = $companyKSFS->level;
                            $infoCompany->parent            = $companyKSFS->parent;

                            // Execute
                            $DB->insert_record('ks_company',$infoCompany);
                        }else {
                            $rdo->companyid         = $companyKSFS->ksid;
                            $rdo->name              = $companyKSFS->name;
                            $rdo->industrycode      = $companyKSFS->industry;
                            $rdo->hierarchylevel    = $companyKSFS->level;
                            $rdo->parent            = $companyKSFS->parent;

                            $DB->update_record('ks_company',$rdo);
                        }//if_no_exist

                        // Relation FS KS Companies
                        // Check it with kscompany  --> 0 For new companies
                        // Check it with kscompany  --> $companyKSFS->ksid
                        $params = array();
                        $params['kscompany']    = $companyKSFS->ksid;
                        $params['fscompany']    = $companyKSFS->fsid;
                        $rdoRelation = $DB->get_record('ksfs_company',$params);
                        if ($rdoRelation) {
                            // Execute
                            $rdoRelation->kscompany = $companyKSFS->ksid;
                            $DB->update_record('ksfs_company',$rdoRelation);
                        }else {
                            // Execute
                            $params['kscompany']    = $companyKSFS->ksid;
                            $DB->insert_record_raw('ksfs_company',$params,false);
                        }//if_rdo

                        // Synchronized
                        $sync = true;
                    }//if_exists

                    break;

                case DELETE:
                    // Delete from fs_company
                    $DB->delete_records('fs_company',array('companyid' => $companyKSFS->fsid));

                    // Delete Relations
                    $DB->delete_records('ksfs_company',array('fscompany' => $companyKSFS->fsid));

                    $DB->delete_records('ks_company',array('companyid' => $companyKSFS->ksid));

                    // Synchronized
                    $sync = true;

                    break;
            }//switch_Action

            // Synchronized
            if ($sync) {
                $instance = $DB->get_record('fs_imp_company',array('ORG_ENHET_ID' => $impKey),'id,imported');
                if ($instance) {
                    $instance->imported         = 1;
                    $instance->timemodified     = $time;
                    $DB->update_record('fs_imp_company',$instance);
                }
            }//if_sync

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//synchronize_company_ksfs

    /**
     * Description
     * Synchronize company only in FS site.
     *
     * @param           $companyFS
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function synchronize_company_fs($companyFS) {
        /* Variables    */
        global $DB;
        $infoCompany    = null;
        $rdoCompany     = null;
        $params         = null;
        $sync           = null;
        $time           = null;
        $trans          = null;

        // Start Transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local Time
            $time = time();

            // Get Info Company
            $params = array();
            $params['companyid'] = $companyFS->fscompany;
            $rdoCompany          = $DB->get_record('fs_company',$params);

            // Apply Action
            switch ($companyFS->action) {
                case UPDATE:
                    if ($rdoCompany) {
                        $rdoCompany->name           = $companyFS->name;
                        $rdoCompany->fs_parent      = $companyFS->fs_parent;
                        $rdoCompany->privat         = $companyFS->privat;
                        $rdoCompany->ansvar         = $companyFS->ansvar;
                        $rdoCompany->tjeneste       = $companyFS->tjeneste;
                        $rdoCompany->adresse1       = $companyFS->adresseOne;
                        $rdoCompany->adresse2       = $companyFS->adresseTwo;
                        $rdoCompany->adresse3       = $companyFS->adresseThree;
                        $rdoCompany->postnr         = $companyFS->postnr;
                        $rdoCompany->poststed       = $companyFS->poststed;
                        $rdoCompany->epost          = $companyFS->epost;
                        $rdoCompany->synchronized   = 0;
                        $rdoCompany->timemodified   = $time;
                        // Execute
                        $DB->update_record('fs_company',$rdoCompany);

                        // Synchronized
                        $sync = true;
                    }//if_exists

                    break;
                case DELETE:
                    // Delete if exists
                    if ($rdoCompany) {
                        // Delete FS Company
                        $DB->delete_records('fs_company',array('id' => $rdoCompany->id));

                        // Delete FS KS Relation
                        $params = array();
                        $params['fscompany'] = $companyFS->fscompany;
                        // Execute
                        $DB->delete_records('ksfs_company',$params);

                        // Synchronized
                        $sync = true;
                    }//if_exists

                    break;
            }//action

            // Synchronized
            if ($sync) {
                $instance = new stdClass();
                $instance->id           = $companyFS->id;
                $instance->imported     = 1;
                $instance->timemodified = $time;

                $DB->update_record('fs_imp_company',$instance);
            }//if_sync

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//synchronize_company_fs
}//FSKS_COMPANY

/********************/
/* CLASS FSFK_USERS */
/********************/
class FSKS_USERS {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Get users accounts to be synchronized
     *
     * @param       $industry
     * @param       $start
     * @param       $limit
     *
     * @return      array
     * @throws      Exception
     *
     * @creationDate    06/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_users_accounts($industry,$start,$limit) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $params     = null;
        $usersacc   = null;

        try {
            // Search criteria
            $params = array();
            $params['imported'] = 0;

            // SQL Instruction
            $sql = " SELECT	DISTINCT
                            fs.id,
                            trim(fs.fodselsnr) 											as 'personalnumber',
                            trim(IF (fs.brukernavn,fs.brukernavn,0)) 					as 'adfs',
                            trim(IF (fs.ressursnr,fs.ressursnr,0)) 						as 'ressursnr',
                            $industry 												    as 'industry',
                            CONCAT(fs.fornavn,' ',IF(fs.mellomnavn,fs.mellomnavn,'')) 	as 'firstname',
                            trim(fs.etternavn) 											as 'lastname',
                            trim(fs.epost) 												as 'email',
                            fs.action
                     FROM	{fs_imp_users}	fs
                     WHERE 	fs.imported = :imported ";
            
            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);
            if ($rdo) {
                $usersacc = json_encode($rdo);
            }//if_rdo
            
            return array($usersacc,$rdo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_users_accounts

    /**
     * Description
     * Synchronize users accounts
     *
     * @param           $usersFS
     * @param           $usersImported
     *
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     */
    public static function synchronize_users_fs($usersFS,$usersImported) {
        /* Variables    */
        $infoUser   = null;
        $objUser    = null;

        try {
            // Synchronize users have been imported
            foreach ($usersImported as $user) {
                // Convert to object
                $objUser = (Object)$user;

                if ($objUser->imported) {
                    // Get Info User
                    $infoUser = $usersFS[$objUser->key];

                    // Synchronize User
                    self::synchronize_user_fs($infoUser,$objUser->key);
                }//if_user_imported
            }//for_userImported
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronize_users_fs

    /**
     * Description
     * Get total managers to synchronize
     *
     * @param           $status
     * 
     * @return          null
     * @throws          Exception
     *
     * @creationDate    01/11/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_total_managers_reporters_to_synchronize($status = false) {
        /* Variables    */
        global $DB;
        $params             = null;
        $sql                = null;
        $rdo                = null;

        try {
            // Search criteria
            $params = array();
            $params['imported'] = 0;

            // SQL Instruction
            $sql = " SELECT	  count(DISTINCT fs.id) as 'total'
                     FROM	  {fs_imp_managers_reporters}   fs
                        JOIN  {user}                        u   ON  u.idnumber = fs.fodselsnr
                                                                AND u.deleted  = 0
                        JOIN  {ksfs_company}		        fsk	ON  fsk.fscompany = fs.org_enhet_id
                        JOIN  {ks_company}			        ks	ON	ks.companyid  = fsk.kscompany
                     WHERE	  fs.imported	= :imported ";

            // Status criteria
            if ($status) {
                $params['action'] = STATUS;
                $sql .= " AND fs.action = :action ";
            }//if_status
            
            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->total) {
                    return $rdo->total;
                }else {
                    return null;
                }
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_managers_reporters_to_synchronize
    
    /**
     * Description
     * Get User Managers/Reporters to synchronize
     *
     * @param           $start
     * @param           $limit
     * @param           $status
     * 
     * @return          array
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_managers_reporters_to_synchronize($start,$limit,$status = false) {
        /* Variables    */
        global $DB;
        $params             = null;
        $sql                = null;
        $rdo                = null;
        $managersreporters  = null;
        $info               = null;

        try {
            // Search criteria
            $params = array();
            $params['imported'] = 0;

            // SQL Instruction
            $sql = " SELECT DISTINCT 
                              fs.id 				as 'key',
                              trim(fs.fodselsnr) 	as 'personalnumber',
                              fsk.fscompany 		as 'fsid',
                              fsk.kscompany 		as 'ksid',
                              ks.hierarchylevel 	as 'level',
                              fs.prioritet,
                              fs.action
                     FROM	  {fs_imp_managers_reporters}   fs
                        JOIN  {user}                        u   ON  u.idnumber    = fs.fodselsnr
                                                                AND u.deleted     = 0
                        JOIN  {ksfs_company}		        fsk	ON  fsk.fscompany = fs.org_enhet_id
                        JOIN  {ks_company}			        ks	ON	ks.companyid  = fsk.kscompany
                     WHERE	  fs.imported	= :imported ";

            // Status criteria
            if ($status) {
                $params['action'] = STATUS;
                $sql .= " AND fs.action = :action ";
            }//if_status
            
            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);
            if ($rdo) {
                $managersreporters = json_encode($rdo);
            }//if_rdo

            return array($managersreporters,$rdo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_managers_reporters_to_synchronize

    /**
     * Description
     * Get the total amount of managers/reporters that
     * have to be unmapped from company
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_total_managers_reporters_to_unmap() {
        /* Variables */
        global $DB;
        $sql    = null;
        $rdo    = null;

        try {
            // SQL Instruction
            $sql = " SELECT		count(DISTINCT fsu.id) as 'total'
                     FROM		{fs_users_company}	fsu
                        JOIN	{ksfs_org_unmap}	un	ON  un.fscompany = fsu.companyid ";

            // Execute
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_managers_reporters_to_unmap

    /**
     * Description
     * Get Managers/Reporters that have to be unmapped
     *
     * @param           $start
     * @param           $limit
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_managers_reporters_to_unmap($start,$limit) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $toUnMap    = array();
        $info       = null;

        try {
            // SQL Instruction
            $sql = " SELECT DISTINCT
                                fsu.id,
                                fsu.personalnumber,
                                fsu.companyid 	as 'fscompany',
                                un.kscompany 	as 'kscompany',
                                fsu.level,
                                fsu.priority
                     FROM		{fs_users_company}	fsu
                        JOIN	{ksfs_org_unmap}	un	  ON  un.fscompany = fsu.companyid
                     ORDER BY   fsu.personalnumber ";

            // Execute
            $rdo = $DB->get_records_sql($sql,null,$start,$limit);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Info
                    $info = new stdClass();
                    $info->key              = $instance->id;
                    $info->personalNumber   = trim($instance->personalnumber);
                    $info->ksId             = $instance->kscompany;
                    $info->fsId             = $instance->fscompany;
                    $info->level            = $instance->level;
                    $info->prioritet        = $instance->priority;
                    $info->action           = DELETE;

                    // Add
                    $toUnMap[$instance->id] = $info;
                }//for_rdo
            }//if_rdo

            return $toUnMap;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_managers_reporters_to_unmap

    /**
     * Description
     * User competence to synchronize
     *
     * @param       bool $toDelete
     * @param       bool $status
     * @param            $start
     * @param            $limit
     *
     * @return           null
     * @throws           Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     */
    public static function user_competence_to_synchronize($toDelete = false,$status = false,$start,$limit) {
        /* Variables    */
        $toSynchronize = null;

        try {
            // Get Users Competence  to synchronize
            $toSynchronize = self::get_users_competence_to_synchronize($toDelete,$status,$start,$limit);

            return $toSynchronize;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//user_competence_to_synchronize


    /**
     * @param           $start
     * @param           $limit
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unmap user competence (company)
     */
    public static function user_competence_to_unmap($start,$limit) {
        /* Variables */
        $toUnMap = null;

        try {
            // Get users competence to unmap
            $toUnMap = self::get_users_competence_to_unmap($start,$limit);

            return $toUnMap;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//user_competence_to_unmap

    /**
     * Description
     * Synchronize Manager && Reporters
     *
     * @param           $usersTo
     * @param           $managersImported
     *
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     */
    public static function synchronize_manager_reporter_fs($usersTo,$managersImported) {
        /* Variables    */
        $infoUser       = null;

        try {
            // Synchronize Manager && Reporter
            foreach ($managersImported as $manager) {
                // Get Info
                $infoUser = $usersTo[$manager->key];

                // Synchronize Manager&&Reporter
                self::get_synchronize_manager_reporter_fs($infoUser,$manager->key);
            }//for_competencesImported
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronize_manager_reporter_fs

    /**
     * @param           $toUnMap
     * @param           $competencesUnMapped
     *
     * @throws          Exception
     *
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unmap managers/reporters from company
     */
    public static function unmap_manager_reporter_fs($toUnMap,$competencesUnMapped) {
        /* Variables    */
        $infoUser       = null;
        $objUnMapped  = null;

        try {
            // UnMapp User Competence
            foreach ($competencesUnMapped as $competence) {
                // Convert to object
                $objCompetence = (Object)$competence;

                if ($objCompetence->imported) {
                    // Get Info
                    $infoUser = $toUnMap[$objCompetence->key];

                    // UnMap
                    self::unmap_managerreporter_fs($infoUser);
                }//unmapped
            }//competenceUnMapped
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//unmap_manager_reporter_fs

    /**
     * Description
     * Synchronization User Competence between FS and KS
     *
     * @param           $usersCompetence
     * @param           $competencesImported
     *
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     */
    public static function synchronize_user_competence_fs($usersCompetence, $competencesImported) {
        /* Variables    */
        $infoUser       = null;
        $objCompetence  = null;

        try {
            // Synchronize User Competence
            foreach ($competencesImported as $competence) {
                // Convert to object
                $objCompetence = (Object)$competence;

                if ($objCompetence->imported) {
                    // Get Info
                    $infoUser = $usersCompetence[$objCompetence->key];

                    // Synchronize User Competence
                    self::synchronize_competence_fs($infoUser);
                }//if_imported
            }//for_competencesImported
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronize_user_competence_fs

    /**
     * Description
     * Get total users to synchronize
     *
     * @param           $toDelete
     * @param           $status
     *
     * @return          int|null
     * @throws          Exception
     *
     * @creationDate    22/06/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_total_users_competence_to_synchronize($toDelete,$status = false) {
        /* Variables */
        global $DB;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            // Search Criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = DELETE;

            // SQL Instruction
            $sql = " SELECT	    count(DISTINCT fs.id) as 'total'
                     FROM	    {fs_imp_users_jr}	  fs
                        JOIN    {user}                u         ON      u.idnumber = fs.fodselsnr
                                                                AND     u.deleted  = 0
                        -- COMPANY
                        JOIN	{ksfs_company}		  ksfs 		ON 		ksfs.fscompany 		= fs.ORG_ENHET_ID
                        JOIN	{ks_company}		  ks	    ON		ks.companyid		= ksfs.kscompany
                        -- JOB ROLE
                        JOIN	{ksfs_jobroles}		  fsk_jr 	ON 		fsk_jr.fsjobrole 	= fs.stillingskode
                     WHERE		fs.imported = :imported ";

            // To Delete
            if ($status) {
                $params['action'] = STATUS;
                $sql .= " AND fs.action = :action ";
            }else if ($toDelete) {
                $sql .= " AND fs.action = :action ";
            }else {
                $sql .= " AND fs.action != :action ";
            }//if_else

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_Rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_users_competence_to_synchronize

    /**
     * Description
     * Get total users that have to be unmapped
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_total_users_competence_to_unmap() {
        /* Variables */
        global $DB;
        $sql    = null;
        $rdo    = null;

        try {
            // Sql Instruction
            $sql = " SELECT 	count(DISTINCT fsu.id) as 'total'
                     FROM		{fs_users_competence} fsu
                        JOIN	{ksfs_org_unmap}	  un	ON 	un.kscompany = fsu.companyid ";

            // Execute
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_users_competence_to_unmap

    /**
     * Description
     * Un map user competence - companies
     *
     * @param           $toUnMap
     * @param           $competencesUnMapped
     * 
     * @throws          Exception
     * 
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     */
    public static function unmap_user_competence_fs($toUnMap, $competencesUnMapped) {
        /* Variables    */
        $infoUser       = null;
        $objUnMapped  = null;

        try {
            // UnMapp User Competence
            foreach ($competencesUnMapped as $competence) {
                // Convert to object
                $objCompetence = (Object)$competence;

                if ($objCompetence->unmapped) {
                    // Get Info
                    $infoUser = $toUnMap[$objCompetence->key];

                    // UnMap
                    self::unmap_competence_fs($infoUser);
                }//unmapped
            }//competenceUnMapped
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//unmap_user_competence_fs

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Synchronize user account ino FS.
     * Add resource number
     * ADFS ID.
     * Merge ADFS and Fellesdata accounts
     *
     * @param           $userFS
     * @param           $fsKey
     *
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      23/09/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      27/10/2016
     * @author          eFaktor     (fbv)
     */
    private static function synchronize_user_fs($userFS,$fsKey) {
        /* Variables    */
        global $DB,$CFG;
        $rdoUser        = null;
        $rdoFellesdata  = null;
        $params         = null;
        $infoUser       = null;
        $instance       = null;
        $time           = null;
        $sync           = false;
        $trans          = null;
        $userId         = null;
        $dbLog          = null;

        // Start Transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local Time
            $time = time();

            // Check if user already exists.
            // Can be connected with ADFS or not.
            if ($userFS->adfs) {
                // Connected with
                $params = array();
                $params['username'] = $userFS->adfs;
                $rdoUser = $DB->get_record('user',$params,'id');

                if (!$rdoUser) {
                    $params['username'] = $userFS->personalnumber;
                    $rdoUser = $DB->get_record('user',$params,'id');
                }else {
                    // Fellesdata account to delete
                    $rdoFellesdata = $DB->get_record('user',array('username' => $userFS->personalnumber),'id,username');
                }//if_rdoUser
            }else {
                // No Connected
                $params = array();
                $params['username'] = $userFS->personalnumber;
                $rdoUser = $DB->get_record('user',$params,'id');
            }//if_adfs

            // Info Account
            if (!$rdoUser) {
                // Create new Account
                $infoUser = new stdClass();
                if ($userFS->adfs) {
                    // Connected
                    $infoUser->username     = $userFS->adfs;
                }else {
                    // No connected
                    $infoUser->username     = $userFS->personalnumber;
                }//if_adfs
                $infoUser->idnumber     = $userFS->personalnumber;
                $infoUser->firstname    = $userFS->firstname;
                $infoUser->lastname     = $userFS->lastname;
                $infoUser->email        = $userFS->email;
                $infoUser->timemodified = $time;
                $infoUser->timecreated  = $time;
                $infoUser->auth         = 'saml';
                $infoUser->password     = AUTH_PASSWORD_NOT_CACHED;
                $infoUser->confirmed    = '1';
                $infoUser->firstaccess  = $time;
                $infoUser->calendartype = $CFG->calendartype;
                $infoUser->mnethostid   = $CFG->mnet_localhost_id;
                $infoUser->lang         = 'no';
            }else {
                $rdoUser->lang          = 'no';
                $userId = $rdoUser->id;
                // Two merge accounts
                if ($userFS->adfs) {
                    // Connected
                    $rdoUser->username  = $userFS->adfs;
                    $rdoUser->idnumber  = $userFS->personalnumber;
                }//if_adfs
            }//if_no_exist

            // Apply synchronization
            switch ($userFS->action) {
                case ADD:
                case UPDATE:
                case STATUS:
                    // Execute
                    if (!$rdoUser) {
                        $userId = $DB->insert_record('user',$infoUser);
                    }else {
                        // Update
                        $userId = $rdoUser->id;
                        $rdoUser->firstname     = $userFS->firstname;
                        $rdoUser->lastname      = $userFS->lastname;
                        $rdoUser->email         = $userFS->email;
                        $rdoUser->deleted       = 0;
                        $rdoUser->timemodified  = $time;

                        // Execute
                        $DB->update_record('user',$rdoUser);
                    }//if_no_exists

                    // Synchronized
                    $sync = true;

                    break;
                case DELETE:
                    /* Delete   */
                    if ($rdoUser) {
                        $rdoUser->timemodified = $time;
                        $rdoUser->deleted      = 0;
                        $rdoUser->email        = '';

                        /* Execute  */
                        $DB->update_record('user',$rdoUser);
                    }else {
                        /* Execute  */
                        $infoUser->deleted      = 0;
                        $infoUser->email        = '';
                        $userId = $DB->insert_record('user',$infoUser);
                    }//if_exist

                    // Synchronized
                    $sync = true;

                    break;
            }//switch_Action

            // Synchronized
            if ($sync) {
                $instance = new stdClass();
                $instance->id           = $fsKey;
                $instance->imported     = 1;
                $instance->timemodified = $time;

                $DB->update_record('fs_imp_users',$instance);
            }//if_sync

            // Create the connection between user and his/her resource number
            // First. Check if already exist an entry for this user.
            if ($userFS->ressursnr) {
                $rdo = $DB->get_record('user_resource_number',array('userid' => $userId));
                if ($rdo) {
                    // Update
                    $rdo->ressursnr      = $userFS->ressursnr;
                    $rdo->industrycode   = $userFS->industry;

                    // Execute
                    $DB->update_record('user_resource_number',$rdo);
                }else {
                    // Insert
                    $instance = new stdClass();
                    $instance->userid        = $userId;
                    $instance->ressursnr     = $userFS->ressursnr;
                    $instance->industrycode  = $userFS->industry;

                    // Execute
                    $DB->insert_record('user_resource_number',$instance);
                }//if_rdo
            }//if_resource_number

            // Fellesdata account has to be deleted
            // Only one account for user
            if ($rdoFellesdata) {
                // From user
                $DB->delete_records('user',array('id' => $rdoFellesdata->id,'username' => $rdoFellesdata->username));

                // From user resource number
                $DB->delete_records('user_resource_number',array('userid' => $rdoFellesdata->id));
            }//if_fellesdata

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Log
            $dbLog .= $ex->getTraceAsString() . "\n" ."\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR SynchronizeUserFS . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//synchronize_user_fs

    /**
     * Description
     * Synchronization Manager&&Reporters from fellesdata
     *
     * @param           $infoUserFS
     * @param           $fsKey
     *
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     */
    private static function get_synchronize_manager_reporter_fs($infoUserFS,$fsKey) {
        /* Variables    */
        global $DB;
        $infoFS = null;
        $rdo            = null;
        $params         = null;
        $sync           = null;
        $trans          = null;
        $time           = null;

        // Start Transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();

            // GEt info fs_user_company
            $params = array();
            $params['personalnumber']    = $infoUserFS->personalnumber;
            $params['companyid']         = $infoUserFS->fsid;
            // Execute
            $rdo = $DB->get_record('fs_users_company',$params);

            // Create the instance if it does not exist
            if (!$rdo) {
                // Create Entry
                $infoFS = new stdClass();
                $infoFS->companyid          = $infoUserFS->fsid;
                $infoFS->personalnumber     = $infoUserFS->personalnumber;
                $infoFS->level              = $infoUserFS->level;
                $infoFS->priority           = $infoUserFS->prioritet;
                $infoFS->synchronized       = 1;
            }//if_rdo

            // Apply action
            switch ($infoUserFS->action) {
                case ADD:
                case UPDATE:
                case STATUS:
                    // Check if already exists
                    if ($rdo) {
                        $rdo->companyid          = $infoUserFS->fsid;
                        $rdo->personalnumber     = $infoUserFS->personalnumber;
                        $rdo->level              = $infoUserFS->level;
                        $rdo->priority           = $infoUserFS->prioritet;
                        $rdo->synchronized       = 1;

                        // Execute
                        $DB->update_record('fs_users_company',$rdo);

                        // Synchronized
                        $sync = true;
                    }else {
                        // Execute
                        $DB->insert_record('fs_users_company',$infoFS);
                    }//if_exists
                    // Synchronized
                    $sync = true;

                    break;
                case DELETE:
                    // Delete if exists
                    if ($rdo) {
                        $DB->delete_records('fs_users_company',array('id' => $rdo->id));

                        // Synchronized
                        $sync = true;
                    }//if_exists

                    break;
            }//action

            // Synchronized
            if ($sync) {
                $instance = new stdClass();
                $instance->id           = $fsKey;
                $instance->imported     = 1;
                $instance->timemodified = $time;
                $DB->update_record('fs_imp_managers_reporters',$instance);
            }//if_sync

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//get_synchronize_manager_reporter_fs

    /**
     * Description
     * Unmap manager/reporter from the company
     *
     * @param           $infoUserFS
     * 
     * @throws          Exception
     * 
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     */
    private static function unmap_managerreporter_fs($infoUserFS) {
        /* Variables */
        global $DB;
        $params = null;

        try {
            // Search Criteria
            $params = array();
            $params['id']               = $infoUserFS->key;
            $params['personalnumber']   = $infoUserFS->personalNumber;
            $params['companyid']        = $infoUserFS->fsId;

            // Execute
            $DB->delete_records('fs_users_company',$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//unmap_managerreporter_fs

    /**
     * Description
     * Get user competence to synchronize
     *
     * @param           $toDelete
     * @param           $start
     * @param           $limit
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     */
    private static function get_users_competence_to_synchronize($toDelete,$status,$start,$limit) {
        /* Variables */
        global $DB,$CFG;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $usersComp      = null;
        $infoComp       = null;
        $lstCompetence  = null;
        $dblog          = null;

        try {
            // Search Criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = DELETE;

            $sql = " SELECT   fs.id				  as 'key',
                              trim(fs.fodselsnr)  as 'personalnumber',
                              ksfs.fscompany	  as 'fsid',	
                              ks.companyid		  as 'company',
                              ks.hierarchylevel   as 'level',
                              fsk_jr.ksjobrole 	  as 'jobrole',
                              GROUP_CONCAT(DISTINCT fs.stillingskode ORDER BY fs.stillingskode SEPARATOR ',') as 'fsjobroles',
                              GROUP_CONCAT(DISTINCT fs.id ORDER BY fs.id SEPARATOR ',')                       as 'impkeys',
                              fs.action 													                  as 'action'
                     FROM	  {fs_imp_users_jr}	  fs
                        JOIN  {user}              u       ON    u.idnumber 			= fs.fodselsnr
                                                          AND   u.deleted  			= 0
                        -- COMPANY
                        JOIN  {ksfs_company}	  ksfs 	  ON    ksfs.fscompany 		= fs.ORG_ENHET_ID
                        JOIN  {ks_company}		  ks	  ON	ks.companyid		= ksfs.kscompany
                        -- JOB ROLE
                        JOIN  {ksfs_jobroles}	  fsk_jr  ON 	fsk_jr.fsjobrole 	= fs.stillingskode
                     WHERE	  fs.imported = :imported ";

            // Action
            if ($status) {
                $params['action'] = STATUS;
                $sql .= " AND fs.action = :action ";
            }else if ($toDelete) {
                $sql .= " AND fs.action = :action ";
            }else {
                $sql .= " AND fs.action != :action ";
            }//if_else

            // GROUP / ORDER
            $sql .= " GROUP BY fs.fodselsnr,ksfs.fscompany
                      ORDER BY fs.fodselsnr ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);
            if ($rdo) {
                $lstCompetence = json_encode($rdo);
            }else {
                // Log
                $dblog  = "User Competence - GetUsersCompetence_ToSynchronize NO RDO".  "\n\n";
                $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH GetUsersCompetence_ToSynchronize . ' . "\n";
                error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
            }//if_rdo

            return array($lstCompetence,$rdo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_users_competence_to_synchronize

    /**
     * Description
     * User that have to be unmapped from company
     *
     * @param           $start
     * @param           $limit
     * 
     * @return          array
     * @throws          Exception
     * 
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     */
    private static function get_users_competence_to_unmap($start,$limit) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $dbLog      = null;
        $toUnMap    = array();
        $info       = null;

        try {
            // SQL Instruction
            $sql = " SELECT DISTINCT 
                              fsu.id,
                              fsu.personalnumber,
                              fsu.companyid
                     FROM	  {fs_users_competence}	fsu
                        JOIN  {ksfs_org_unmap}		un	ON 	un.kscompany = fsu.companyid
                     ORDER BY fsu.personalnumber ";

            // Executed
            $rdo = $DB->get_records_sql($sql,null,$start,$limit);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Info to unmap
                    $info = new stdClass();
                    $info->key              = $instance->id;
                    $info->personalnumber   = trim($instance->personalnumber);
                    $info->companyid        = $instance->companyid;

                    // Add
                    $toUnMap[$instance->id] = $info;
                }//for_rdo
            }//if_rdo

            return $toUnMap;
        }catch (Exception $ex) {
            throw $ex;
        }//tryCatch
    }//get_users_competence_to_unmap

    /**
     * Description
     * Delete competence from FS
     *
     * @param           $competence
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     */
    private static function delete_from_competence_fs($competence) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $params         = null;
        $myFSJobroles   = null;
        $fsJobRoles     = null;
        $toDeleteFromKS = false;
        $impKeys        = null;
        $time           = null;

        try {
            // Local time
            $time = time();

            // Search criteria
            $params = array();
            $params['personalnumber']   = $competence->fodselsnr;
            $params['companyid']        = $competence->companyid;
            $params['ksjrcode']         = $competence->ksjobrole;

            // Execute
            $rdo = $DB->get_record('fs_users_competence',$params);
            if ($rdo) {
                $myFSJobroles   = array_flip(explode(',',$rdo->jrcode));
                $fsJobRoles     = explode(',',$competence->fsjobroles);
                foreach ($fsJobRoles as $fsJR) {
                    if (array_key_exists($fsJR,$myFSJobroles)) {
                        unset($myFSJobroles[$fsJR]);
                        $rdo->jrcode = implode(',',array_keys($myFSJobroles));
                    }
                }

                // Update
                $DB->update_record('fs_users_competence',$rdo);

                // To know if it has to be deleted
                if (!$rdo->jrcode) {
                    $toDeleteFromKS = true;
                }else {
                    $impKeys = explode(',',$competence->impkeys);

                    foreach ($impKeys as $fsKey) {
                        $instance = new stdClass();
                        $instance->id           = $fsKey;
                        $instance->imported     = 1;
                        $instance->timemodified = $time;

                        $DB->update_record('fs_imp_users_jr',$instance);
                    }
                }
            }

            return $toDeleteFromKS;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//delete_from_competence_fs

    /**
     * Description
     * Synchronize between user competence between FS and KS
     *
     * @param           $competenceFS
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     */
    private static function synchronize_competence_fs($competenceFS) {
        /* Variables */
        global $DB;
        $params         = null;
        $rdo            = null;
        $sync           = null;
        $infoCompetence = null;
        $trans          = null;
        $fsKey          = null;
        $impKeys        = null;
        $time           = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();

            // Get Info User Job Role (FS)
            $params = array();
            $params['personalnumber']   = $competenceFS->personalnumber;
            $params['companyid']        = $competenceFS->company;
            $params['ksjrcode']         = $competenceFS->jobrole;
            $rdo = $DB->get_record('fs_users_competence',$params);

            // Apply Action
            switch ($competenceFS->action) {
                // Check if already exists
                case ADD:
                case UPDATE:
                case STATUS:
                    if ($rdo) {
                        // Update
                        $rdo->synchronized = 1;
                        $rdo->ksjrcode       = $competenceFS->jobrole;
                        // Execute
                        $DB->update_record('fs_users_competence',$rdo);

                        // Synchronized
                        $sync = true;
                    }else {
                        // New Entry
                        $infoCompetence = new stdClass();
                        $infoCompetence->personalnumber = $competenceFS->personalnumber;
                        $infoCompetence->companyid      = $competenceFS->company;
                        $infoCompetence->jrcode         = $competenceFS->fsjobroles;
                        $infoCompetence->ksjrcode       = $competenceFS->jobrole;
                        $infoCompetence->synchronized   = 1;

                        // Execute
                        $DB->insert_record('fs_users_competence',$infoCompetence);

                        // Synchronized
                        $sync = true;
                    }

                    break;

                case DELETE:
                    // Delete if exists
                    if ($rdo) {
                        //self::delete_from_competence_fs($rdo);
                        
                        $DB->delete_records('fs_users_competence',array('id' => $rdo->id));

                        // Synchronized
                        $sync = true;
                    }//if_exits

                    break;
                default:
                    break;
            }//action

            // Synchronized
            if ($sync) {
                $impKeys = explode(',',$competenceFS->impkeys);

                foreach ($impKeys as $fsKey) {
                    $instance = new stdClass();
                    $instance->id           = $fsKey;
                    $instance->imported     = 1;
                    $instance->timemodified = $time;

                    $DB->update_record('fs_imp_users_jr',$instance);
                }
            }//if_sync

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//synchronize_competence_fs

    /**
     * Description
     * Un map competence from the user
     *
     * @param           $infoUser
     * 
     * @throws          Exception
     * 
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     */
    private static function unmap_competence_fs($infoUser) {
        /* Variables */
        global $DB;
        $params = null;

        try {
            // Delete record
            $params = array();
            $params['id'] = $infoUser->key;
            $DB->delete_records('fs_users_competence',$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//unmap_competence_fs
}//FSKS_USERS

/************/
/* CLASS FS */
/************/
class FS {
    /**********/
    /* PUBLIC */
    /**********/

    public static function backup_temporary_fellesdata($type) {
        /* Variables */
        global $CFG;
        $file           = null;
        $backupstatus   = null;
        $content        = null;
        $path           = null;
        $time           = null;

        try {
            // Local time
            $time = time();

            $backupstatus = $CFG->dataroot . '/fellesdata/backup_status';
            if (!file_exists($backupstatus)) {
                mkdir($backupstatus);
            }//if_backup

            switch ($type) {
                case IMP_USERS:
                    // IMP USERS
                    $path = $backupstatus . "/fs_imp_users_" . $time . ".txt";
                    self::backup_imp_fs_tables($path,'fs_imp_users');

                    break;

                case IMP_COMPANIES:
                    // IMP COMPANIES
                    $path = $backupstatus . "/fs_imp_company_" . $time . ".txt";
                    self::backup_imp_fs_tables($path,'fs_imp_company');

                    break;

                case IMP_JOBROLES:
                    // IMP JOBROLES
                    $path = $backupstatus . "/fs_imp_jobroles_" . $time . ".txt";
                    self::backup_imp_fs_tables($path,'fs_imp_jobroles');

                    break;

                case IMP_MANAGERS_REPORTERS:
                    // IMP MANAGERS_REPORTERS
                    $path = $backupstatus . "/fs_imp_managers_reporters_" . $time . ".txt";
                    self::backup_imp_fs_tables($path,'fs_imp_managers_reporters');

                    break;

                case IMP_COMPETENCE_JR:
                    // IMP COMPETENCE JR
                    $path = $backupstatus . "/fs_imp_users_jr_" . $time . ".txt";
                    self::backup_imp_fs_tables($path,'fs_imp_users_jr');

                    break;
            }//type
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//backup_temporary_fellesdata

    private static function backup_imp_fs_tables($path,$table) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $file   = null;

        try {
            // get content table
            $sql = " SELECT * FROM {" . $table . "} WHERE action != " . STATUS ;
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                // content to string
                $content = json_encode($rdo);

                // Add content to the file
                $file = fopen($path,'w');
                fwrite($file,$content);
                fclose($file);
            }//if_rdo

            // Delete all records
            $DB->delete_records($table,array('action' => 0));
            $DB->delete_records($table,array('action' => 1));
            $DB->delete_records($table,array('action' => 2));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//backup_imp_fs_users

    /**
     * @param            $data
     * @param            $type
     * @param       bool $status
     *
     * @return           bool
     * @throws           Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save in temporary tables. Step before synchronization
     */
    public static function save_temporary_fellesdata($data,$type,$status = false) {
        /* Variables    */
        $action         = null;
        $newEntry       = null;
        $lineContent    = null;
        $toSave         = array();
        $time           = null;

        try {
            // Local time
            $time = time();

            // Each line file
            foreach($data as $key=>$line) {
                $lineContent    = json_decode($line);

                // Get New Entry
                if ($lineContent) {
                    if ($status) {
                        $newEntry = $lineContent->newRecord;
                        $newEntry->action   = 3;
                        $newEntry->imported = 0;
                    }else {
                        // Get Action
                        switch (trim($lineContent->changeType)) {
                            case ADD_ACTION:
                                // Add
                                $newEntry = $lineContent->newRecord;
                                $newEntry->action   = 0;
                                $newEntry->imported = 0;

                                break;

                            case UPDATE_ACTION:
                                // Update
                                $newEntry = $lineContent->newRecord;
                                $newEntry->action   = 1;
                                $newEntry->imported = 0;

                                break;

                            case DELETE_ACTION:
                                // Old Entry
                                if (isset($lineContent->oldRecord)) {
                                    $newEntry = $lineContent->oldRecord;
                                    $newEntry->action   = 2;
                                    $newEntry->imported = 0;
                                }//if_old_record

                                break;
                        }//action
                    }//if_status


                    // Add Record
                    //if ($newEntry) {
                        $newEntry->timeimport   = $time;
                        $newEntry->timemodified = $time;
                    //    $toSave[$key] = $newEntry;
                    //}

                    switch ($type) {
                        case IMP_USERS:
                            // FS Users
                            self::import_temporary_fs_users($newEntry,$status);

                            // Fake eMails
                            self::update_fake_mails();


                            break;

                        case IMP_COMPANIES:
                            // FS Companies
                            self::import_temporary_fs_company($newEntry,$status);

                            break;

                        case IMP_JOBROLES:
                            // FS JOB ROLES
                            self::import_temporary_fs_jobroles($newEntry,$status);

                            break;

                        case IMP_MANAGERS_REPORTERS:
                            // Managers Reporters
                            self::import_temporary_managers_reporters($newEntry,$status);

                            break;

                        case IMP_COMPETENCE_JR:
                            // Competence Job Role
                            self::import_temporary_competence_jobrole($newEntry,$status);

                            break;
                    }//type
                }//ifLineContent
            }//for


            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//save_temporary_fellesdata

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Save FS users in temporary tables before the synchronization
     *
     * @param           $data
     * @param           $status
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static  function import_temporary_fs_users($data,$status = false) {
        /* Variables    */
        global $DB;
        $infoUser   = null;
        $trans      = null;
        $rdo        = null;
        $params     = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Search criteria
            $params = array();

            // Status criteria
            if ($status) {
                $params['action'] = STATUS;
            }//if_status

            //foreach ($data as $key => $infoUser) {
                // Criteria
                $params['FODSELSNR'] = $infoUser->FODSELSNR;

                // Execute
                $rdo = $DB->get_record('fs_imp_users',$params);
                if (!$rdo) {
                    $DB->insert_record('fs_imp_users',$data);
                }else {
                    $data->id       = $rdo->id;
                    $DB->update_record('fs_imp_users',$data);
                }//if_rdo
            //}//ofr_each

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//import_temporary_fs_users

    /**
     * Description
     * Generate a fake email for users with empty email
     *
     * @throws          Exception
     * 
     * @creationDate    26/10/2016
     * @author          eFaktor     (fbv)
     */
    private static function update_fake_mails() {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $time       = null;
        
        try {
            // Local time
            $time = time();
            
            // SQL Instruction
            $sql = " SELECT DISTINCT
                            fs.id,
                            fs.EPOST
                     FROM	{fs_imp_users}	fs
                     WHERE 	fs.EPOST IS NULL
                        OR 	fs.EPOST    = '' ";

            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Fake eMail
                    $instance->EPOST        = random_string() . '@byttmegut.no';
                    $instance->timemodified = $time;
                    
                    // Update
                    $DB->update_record('fs_imp_users',$instance);
                }//rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//update_fake_mails

    /**
     * Description
     * Save FS companies in temporary tables before the synchronization
     *
     * @param           $data
     * @param           $status
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_temporary_fs_company($data,$status = false) {
        /* Variables    */
        global $DB;
        $infoFS     = null;
        $trans      = null;
        $rdo        = null;
        $params     = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Search criteria
            $params = array();

            // Status criteria
            if ($status) {
                $params['action'] = STATUS;
            }//if_status

            // FS Company Info
            //foreach($data as $key => $infoFS) {
                // Criteria
                $params['ORG_ENHET_ID'] = $infoFS->ORG_ENHET_ID;

                // Execute
                $rdo = $DB->get_record('fs_imp_company',$params);
                if (!$rdo) {
                    $DB->insert_record('fs_imp_company',$data);
                }else {
                    $data->id             = $rdo->id;
                    $DB->update_record('fs_imp_company',$data);
                }//if_rdo
            //}//for_each

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//import_temporary_fs_company

    /**
     * Description
     * Save FS Jobroles in temporary tables before the synchronization
     *
     * @param                $data
     * @param           bool $status
     *
     * @throws                  Exception
     *
     * @creationDate    04/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_temporary_fs_jobroles($data,$status = false) {
        /* Variables    */
        global $DB;
        $infoFS = null;
        $trans  = null;
        $rdo    = null;
        $params = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Search criteria
            $params = array();

            // Status criteria
            if ($status) {
                $params['action'] = STATUS;
            }//if_status

            // FS jobrole info
            //foreach($data as $key => $infoFS) {
                // Criteria
                $params['STILLINGSKODE'] = $infoFS->STILLINGSKODE;

                // Execute
                $rdo = $DB->get_record('fs_imp_jobroles',$params);
                if (!$rdo) {
                    $DB->insert_record('fs_imp_jobroles',$data);
                }else {
                    $data->id         = $rdo->id;
                    $DB->update_record('fs_imp_jobroles',$data);
                }//if_rdo
            //}//for_each

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//import_temporary_fs_jobroles

    /**
     * Description
     * Import Temporary ManagersReporters
     *
     * @param           $data
     * @param           $status
     *
     * @throws          Exception
     *
     * @creationDate    13/06/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_temporary_managers_reporters($data,$status = false) {
        /* Variables */
        global $DB;
        $info   = null;
        $trans  = null;
        $params = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Search criteria
            $params = array();

            // Status criteria
            if ($status) {
                $params['action'] = STATUS;
            }//if_status

            //foreach ($data as $key => $info) {
                // Criteria
                $params['ORG_ENHET_ID'] = $info->ORG_ENHET_ID;
                $params['ORG_NIVAA']    = $info->ORG_NIVAA;
                $params['FODSELSNR']    = $info->FODSELSNR;
                $params['PRIORITET']    = $info->PRIORITET;

                // Execute
                $rdo = $DB->get_record('fs_imp_managers_reporters',$params);
                if (!$rdo) {
                    $DB->insert_record('fs_imp_managers_reporters',$data);
                }else {
                    $data->id       = $rdo->id;
                    $DB->update_record('fs_imp_managers_reporters',$data);
                }//if_rdo
            //}//for_rdo

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//import_temporary_managers_reporters

    /**
     * Description
     * Save User Job Role (FS)  in temporary tables before the synchronization
     *
     * @param           $data
     * @param           $status
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_temporary_competence_jobrole($data, $status = false) {
        /* Variables    */
        global $DB;
        $infoCompetenceJR       = null;
        $infoOldCompetenceJR    = null;
        $trans                  = null;
        $rdo                    = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Execute
            if ($status) {
                $DB->delete_records('fs_imp_users_jr',array('action' => STATUS));
            }//status

            // Execute
            $DB->insert_record('fs_imp_users_jr',$data);
            //$DB->insert_records('fs_imp_users_jr',$data);


            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//import_temporary_competence_jobrole
}//class FS

/************/
/* CLASS KS */
/************/
class KS {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Get existing companies
     *
     * @return          int|string
     * @throws          Exception
     *
     * @creationDate    04/03/2016
     * @author          eFaktor     (fbv)
     */
    public static function existing_companies() {
        /* Variables    */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $companiesKS    = 0;
        $companies      = array();
        $params         = null;

        try {
            // Execute
            $rdo = $DB->get_records('ks_company',null,'companyid','companyid');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $companies[$instance->companyid] = $instance->companyid;
                }//for_Rdo

                $companiesKS = implode(',',$companies);
            }//if_rdo

            return $companiesKS;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//existing_companies

    /**
     * Description
     * Import Organization Structure
     *
     * @param           $orgStructure
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    public static function import_ks_organization($orgStructure) {
        /* Variables */
        $infoCompany    = null;

        try {
            // Import KS Company
            foreach ($orgStructure as $company) {
                // Convert to object
                $infoCompany = (Object)$company;

                self::import_ks_company($infoCompany);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//import_ks_organization

    /**
     * Description
     * Import KS Job Roles
     *
     * @param           $jobRoles
     * @param      bool $generics
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    public static function ks_jobroles($jobRoles,$generics=false) {
        /* Variables */
        $infoJR    = null;

        try {
            // Import KS jobrole
            foreach ($jobRoles as $jr) {
                // Convert to object
                $infoJR = (Object)$jr;

                self::import_ks_jobrole($infoJR,$generics);
            }//for_jobroles
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ks_jobroles

    /**
     * Description
     * Get existing job roles
     *
     * @param           bool $generics
     * @param           null $top
     *
     * @return          int|string
     *
     * @throws          Exception
     *
     * @creationDate    04/02/2016
     * @author          eFaktor     (fbv)
     *
     */
    public static function existing_jobroles($generics=false,$top=null) {
        /* Variables    */
        global $DB;
        $jobRoles   = null;
        $jrFS       = 0;
        $sql        = null;
        $rdo        = null;

        try {
            // SQL Instruction
            $sql = " SELECT   DISTINCT jr.id
                     FROM 	  {ks_jobroles}			  jr
                        JOIN  {ks_jobroles_relation}  jr_re	ON jr_re.jobroleid = jr.jobroleid ";

            // Generics
            if ($generics) {
                $sql .= "  AND  jr_re.levelzero IS NULL
                           OR   jr_re.levelzero = 0";
            }else {
                if ($top) {
                    $sql .= " AND jr_re.levelone IN ($top) ";
                }
            }//if_generics

            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $jobRoles[$instance->id] = $instance->id;
                }//for_rdo

                $jrFS = implode(',',$jobRoles);
            }//if_rdo

            return $jrFS;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//existing_jobroles

    /**
     * Description
     * Get top hierarchy
     *
     * @param           $name
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    04/02/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_hierarchy_jr($name) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $hierarchy  = null;

        try {
            // SQL Instruction
            $sql = " SELECT ks.companyid
                     FROM	{ks_company} ks
                     WHERE	ks.name like '%". $name . "%'
                        AND	ks.hierarchylevel = 1 ";

            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                $hierarchy = array();

                foreach ($rdo as $instance) {
                    $hierarchy[$instance->companyid] = $instance->companyid;
                }//for_Rdo
            }//if_Rdo

            return $hierarchy;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_hierarchy_jr

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Import KS Company
     *
     * @param           $company
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_ks_company($company) {
        /* Variables */
        global $DB;
        $infoCompany    = null;
        $infoRelation   = null;
        $rdoCompany     = null;
        $rdoRelation    = null;
        $trans          = null;

        // Start Transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Get Company to check if already exists
            $rdoCompany = $DB->get_record('ks_company',array('companyid' => $company->id));
            if (!$rdoCompany) {
                // KS Company
                $infoCompany = new stdClass();
                $infoCompany->companyid         = $company->id;
                $infoCompany->name              = $company->name;
                $infoCompany->industrycode      = $company->industrycode;
                $infoCompany->hierarchylevel    = $company->level;
                $infoCompany->parent            = $company->parent;

                // Execute
                $DB->insert_record('ks_company',$infoCompany);
            }//if_rdoCompany

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//import_ks_company

    /**
     * Description
     * Import Job Role
     *
     * @param           $jobRole
     * @param      bool $generic
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_ks_jobrole($jobRole,$generic=false) {
        /* Variables */
        global $DB;
        $infoJR         = null;
        $infoJRRelation = null;
        $rdoJR          = null;
        $rdoRelation    = null;
        $params         = null;
        $trans          = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Check if already exists
            $rdoJR = $DB->get_record('ks_jobroles',array('jobroleid' => $jobRole->id));
            if (!$rdoJR) {
                // Jobrole
                $infoJR = new stdClass();
                $infoJR->jobroleid      = $jobRole->id;
                $infoJR->name           = $jobRole->name;
                $infoJR->industrycode   = $jobRole->industryCode;

                // Execute
                $DB->insert_record('ks_jobroles',$infoJR);

                // Jobrole Relation
                $infoJRRelation = new stdClass();
                $infoJRRelation->jobroleid  = $jobRole->id;
                if ($generic) {
                    $infoJRRelation->levelzero  = null;
                    $infoJRRelation->levelone   = null;
                    $infoJRRelation->leveltwo   = null;
                    $infoJRRelation->levelthree = null;

                    // Execute
                    $DB->insert_record('ks_jobroles_relation',$infoJRRelation);
                }else {
                    // Search criteria
                    $params = array();
                    $params['jobroleid'] = $jobRole->id;

                    foreach ($jobRole->relation as $relation) {

                        // Check if already exist
                        $params['levelzero']    = $relation->levelZero;
                        $params['levelone']     = $relation->levelOne;
                        $params['leveltwo']     = $relation->levelTwo;
                        $params['levelthree']   = $relation->levelThree;

                        // Execute
                        $rdoRelation = $DB->get_record('ks_jobroles_relation',$params);
                        if (!$rdoRelation) {
                            $infoJRRelation->levelzero  = $relation->levelZero;
                            $infoJRRelation->levelone   = $relation->levelOne;
                            $infoJRRelation->leveltwo   = $relation->levelTwo;
                            $infoJRRelation->levelthree = $relation->levelThree;

                            // Execute
                            $DB->insert_record('ks_jobroles_relation',$infoJRRelation);
                        }//if_not_Exist
                    }//relations
                }//if_generic
            }//if_no_exit

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//import_ks_jobrole
}//class_KS
