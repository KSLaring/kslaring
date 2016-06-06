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
define('TRADIS_FS_USERS_COMPANIES','v_leka_oren_tilgang');
define('TRADIS_FS_USERS_JOBROLES','v_leka_ressurs_stilling');

/* KS Services  */
define('KS_SYNC_FS_COMPANY','wsFSCompany');
define('KS_ORG_STRUCTURE','wsKSOrganizationStructure');
define('KS_SYNC_FS_JOBROLES','wsFSJobRoles');
define('KS_JOBROLES','wsKSJobRoles');
define('KS_JOBROLES_GENERICS','wsKSJobRolesGenerics');
define('KS_USER_COMPETENCE_CO','wsUserCompetenceCompany');
define('KS_USER_COMPETENCE_JR','wsUserCompetenceJobRole');
define('KS_USER_MANAGER','wsManagerCompany');
define('KS_SYNC_USER_ACCOUNT','wsUsersAccounts');

define('ADD_ACTION','add');
define('UPDATE_ACTION','modify');
define('DELETE_ACTION','delete');

define('ADD',0);
define('UPDATE',1);
define('DELETE',2);

define('IMP_USERS',0);
define('IMP_COMPANIES',1);
define('IMP_JOBROLES',2);
define('IMP_COMPETENCE_COMP',3);
define('IMP_COMPETENCE_JR',4);

/***********************/
/* CLASS FSKS_JOBROLES */
/***********************/
class FSKS_JOBROLES {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @return          array
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all job roles that have to be synchronized
     */
    public static function JobRolesFSToSynchronize() {
        /* Variables    */
        $toSynchronize  = null;
        $toMail         = null;
        $notIn          = null;

        try {
            /* To Synchronize only in FS    */
            $toSynchronize = self::GetJobRoles_ToSynchronize();

            /* To Mail  */
            if ($toSynchronize) {
                $notIn = implode(',',array_keys($toSynchronize));

                $toMail = self::GetJobRoles_ToMail($notIn);
            }//if_synchronize

            return array($toSynchronize,$toMail);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CompaniesFSToSynchronize

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
    public static function Synchronize_JobRoles($toSynchronize) {
        /* Variables */

        try {
            /* Synchronize Job Role */
            foreach ($toSynchronize as $jobRole) {
                self::SynchronizeJobroleFS($jobRole);
            }//for_to_synchronize
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Synchronize_JobRoles

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
                              fs_imp.alternative,
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
                    $infoJR->jralternative  = $instance->alternative;
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
     * @param           $notIn
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get job role that have to be manully mapped
     */
    private static function GetJobRoles_ToMail($notIn) {
        /* Variables    */
        global $DB;
        $toMail = array();
        $sql    = null;
        $rdo    = null;
        $params = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['imported'] = 0;

            /* SQL Instruction */
            $sql = " SELECT	fs.id,
                            fs.stillingstekst
                     FROM	{fs_imp_jobroles}	fs
                     WHERE	fs.imported = :imported
                        AND fs.id NOT IN ($notIn)
                     ORDER BY fs.stillingstekst
                     LIMIT 0,5 ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach($rdo as $instance) {
                    $toMail[$instance->id] = $instance->stillingstekst;
                }//for_Rdo
            }//if_rdo

            return $toMail;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetJobRoles_ToMail

    /**
     * @param           $jobRole
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize job role
     */
    private static function SynchronizeJobRoleFS($jobRole) {
        /* Variables */
        global $DB;
        $infoImp        = null;
        $rdoFS          = null;
        $params         = null;
        $time           = null;
        $sync           = null;
        $trans          = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time   */
            $time = time();

            /* Get Info Job Role FS   */
            $params = array();
            $params['jrcode'] = $jobRole->jrcode;
            $rdoFS = $DB->get_record('fs_jobroles',$params);

            /* Apply Action */
            switch ($jobRole->action) {
                case UPDATE:
                    if ($rdoFS) {
                        $rdoFS->jrname          = $jobRole->jrname;
                        $rdoFS->jralternative   = $jobRole->jralternative;
                        $rdoFS->synchronized    = 1;
                        $rdoFS->timemodified    = $time;

                        /* Execute  */
                        $DB->update_record('fs_jobroles',$rdoFS);

                        /* Synchronized */
                        $sync = true;
                    }//if_exist

                    break;
                case DELETE:
                    if ($rdoFS) {
                        /* Delete fs_jobroles   */
                        $DB->delete_records('fs_jobroles',array('id' => $rdoFS->id));

                        /* Delete KSFS Job roles    */
                        $params = array();
                        $params['fsjobrole'] = $jobRole->jrcode;
                        /* Execute  */
                        $DB->delete_records('ksfs_jobroles',$params);

                        /* Synchronized */
                        $sync = true;
                    }//if_exist

                    break;
            }//action

            /* Synchronized */
            if ($sync) {
                $infoImp = new stdClass();
                $infoImp->id        = $jobRole->id;
                $infoImp->imported  = 1;

                /* Execute  */
                $DB->update_record('fs_imp_jobroles',$infoImp);
            }//if_sync

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//SynchronizeJobRoleFS

}//FSKS_JOBROLES


/**********************/
/* CLASS FSKS_COMAPNY */
/**********************/
class FSKS_COMPANY {
    /**********/
    /* PUBLIC */
    /**********/

    /**/
    /**
     * @return          array
     *
     * @throws          Exception
     *
     * @creationDate    09/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all companies that have to be synchronized
     */
    public static function CompaniesFSToSynchronize() {
        /* Variables    */
        $toSynchronize  = null;
        $synchronizeFS  = null;
        $toUpdate       = null;
        $toMail         = null;
        $notIn          = 0;

        try {
            /* Companies to Synchronize between FS and KS   */
            /* New - Create */
            //self::GetNewCompaniesFS_ToSynchronize($toSynchronize);
            /* New - Update */
            self::GetUpdateCompaniesFS_ToSynchronize($toSynchronize);

            /* To synchronize Only in FS    */
            if ($toSynchronize) {
                $notIn .= ',' . implode(',',array_keys($toSynchronize));
            }//if_toSynchronize
            $synchronizeFS = self::GetCompaniesFS_ToSynchronizeFS($notIn);

            /* To Mail */
            if ($synchronizeFS) {
                $notIn .= ',' . implode(',',array_keys($synchronizeFS));
            }//fi_synchronize
            $toMail = self::GetCompaniesFS_ToMail($notIn);

            return array($toSynchronize,$synchronizeFS,$toMail);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CompaniesFSToSynchronize

    /**
     * @param           $companiesFSKS
     * @param           $companiesImported
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize companies between FS and KS
     */
    public static function Synchronize_CompaniesKSFS($companiesFSKS,$companiesImported) {
        /* Variables */
        $infoCompany    = null;
        $objCompany     = null;

        try {
            /* Synchronize companies that have been imported    */
            foreach ($companiesImported as $company) {
                /* Convert to object */
                $objCompany = (Object)$company;

                if ($objCompany->imported) {
                    /* Get Company  */
                    $infoCompany = $companiesFSKS[$objCompany->key];

                    /* Synchronize Company  */
                    $infoCompany->ksId = $objCompany->ksId;
                    self::SynchronizeCompanyKSFS($infoCompany,$objCompany->key);
                }//if_imported
            }//for_companiesFS
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Synchronize_CompaniesKSFS

    /**
     * @param           $companiesFS
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize companies only on FS site.
     */
    public static function Synchronize_CompaniesFS($companiesFS) {
        /* Variables    */

        try {
            /* Synchronize /update  FS Company  */
            foreach ($companiesFS as $company) {
                self::SynchronizeCompanyFs($company);
            }//for_company
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Synchronize_CompaniesFS

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $toSynchronize
     *
     * @throws          Exception
     *
     * @creationDate    09/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get companies, created with option new during the mapping, that have to be created to KS site.
     */
    private static function GetNewCompaniesFS_ToSynchronize(&$toSynchronize) {
        /* Variables    */
        global $DB;
        $infoCompany    = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['synchronized'] = 0;
            $params['new']          = 1;

            /* SQL Instruction  */
            $sql = " SELECT	  fs.id,
                              fs.companyid,
                              fs.name,
                              fs.level,
                              fk.kscompany as 'parent',
                              ks.industrycode
                     FROM	  {fs_company}		fs
                        JOIN  {ksfs_company}	fk 	ON fk.fscompany = fs.parent
                        JOIN  {ks_company}		ks	ON ks.companyid = fk.kscompany
                     WHERE	fs.synchronized = :synchronized
                        AND	fs.new 			= :new ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Company */
                    $infoCompany = new stdClass();
                    $infoCompany->fsId      = $instance->companyid;
                    $infoCompany->ksId      = 0;
                    $infoCompany->name      = $instance->name;
                    $infoCompany->industry  = $instance->industrycode;
                    $infoCompany->level     = $instance->level;
                    $infoCompany->parent    = $instance->parent;
                    $infoCompany->action    = ADD;

                    /* Add Company */
                    $toSynchronize[$instance->id] = $infoCompany;
                }//for_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetNewCompaniesFS_ToSynchronize

    /**
     * @param           $toSynchronize
     *
     * @throws          Exception
     *
     * @creationDate    09/02/0216
     * @author          eFaktor     (fbv)
     *
     * Description
     * For all companies created with 'new' option during the mapped,
     * Get all companies that have to be updates or delete from the KS site.
     */
    private static function GetUpdateCompaniesFS_ToSynchronize(&$toSynchronize) {
        /* Variables    */
        global $DB;
        $infoCompany    = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            /* Search Criteria   */
            $params = array();
            $params['new']      = 1;
            $params['imported'] = 0;

            /* SQL Instruction  */
            $sql = " SELECT	  fs_imp.id,
                              fs.companyid,
                              fk.kscompany,
                              fs_imp.org_navn,
                              fs.level,
                              ks_pa.companyid as 'parent',
                              ks_pa.industrycode,
                              fs_imp.action
                     FROM	  {fs_imp_company}	fs_imp
                        JOIN  {fs_company}		fs		ON 	fs_imp.org_enhet_id = fs.companyid
                                                        AND	fs.new = :new
                        -- INFO KS
                        JOIN	{ksfs_company}	fk 		ON 	fk.fscompany 	= fs.companyid
                        -- INFO PARENT
                        JOIN	{ksfs_company}	fk_pa 	ON 	fk_pa.fscompany = fs_imp.org_enhet_over
                        JOIN	{ks_company}	ks_pa	ON 	ks_pa.companyid = fk_pa.kscompany
                     WHERE		fs_imp.imported = :imported ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Company */
                    $infoCompany = new stdClass();
                    $infoCompany->fsId      = $instance->companyid;
                    $infoCompany->ksId      = $instance->kscompany;
                    $infoCompany->name      = $instance->org_navn;
                    $infoCompany->industry  = $instance->industrycode;
                    $infoCompany->level     = $instance->level;
                    $infoCompany->parent    = $instance->parent;
                    $infoCompany->action    = $instance->action;

                    /* Add Company */
                    $toSynchronize[$instance->id] = $infoCompany;
                }//for_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUpdateCompaniesFS_ToSynchronize

    /**
     * @param           $notIn
     *
     * @return          array
     *
     * @throws          Exception
     *
     * @creationDate    09/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all companies that have to be updated or deleted only in the FS site
     */
    private static function GetCompaniesFS_ToSynchronizeFS($notIn) {
        /* Variables    */
        global $DB;
        $synchronizeFS  = array();
        $infoCompany    = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['imported'] = 0;
            $params['add']      = ADD;

            /* SQL Instruction  */
            $sql = " SELECT   fs.id,
                              fs.org_enhet_id   as 'companyid',
                              fs.org_navn       as 'name',
                              fs.org_enhet_over as 'parent',
                              fs.action
                     FROM	    {fs_imp_company}	fs
                     WHERE	fs.action 	!= :add
                        AND	fs.imported  = :imported
                        AND fs.id NOT IN ($notIn) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Company */
                    $infoCompany = new stdClass();
                    $infoCompany->id        = $instance->id;
                    $infoCompany->fscompany = $instance->companyid;
                    $infoCompany->name      = $instance->name;
                    $infoCompany->parent    = $instance->parent;
                    $infoCompany->action    = $instance->action;

                    /* Add company  */
                    $synchronizeFS[$instance->id] = $infoCompany;
                }//for_rdo
            }//if_rdo

            return $synchronizeFS;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompaniesFS_ToSynchronizeFS

    /**
     * @param           $notIn
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all companies that have to be synchronized manually.
     */
    private static function GetCompaniesFS_ToMail($notIn) {
        /* Variables    */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $companiesFS    = array();

        try {
            /* SQL Instruction */
            $sql = " SELECT	fs.id,
                            fs.org_navn
                     FROM	{fs_imp_company}	fs
                     WHERE	fs.imported = 0
                        AND fs.id NOT IN ($notIn)
                     ORDER BY fs.org_navn
                     LIMIT 0,5 ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $companiesFS[$instance->id] = $instance->org_navn;
                }//for_rdo
            }//if_rdo

            return $companiesFS;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompaniesFS_ToMail

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
    private static function SynchronizeCompanyKSFS($companyKSFS,$impKey) {
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

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time   */
            $time = time();

            /* Company Info to check if already exists or no    */
            $params = array();
            $params['companyid'] = $companyKSFS->fsId;
            $rdoCompany = $DB->get_record('fs_company',$params,'id');

            /* Apply Synchronization    */
            switch ($companyKSFS->action) {
                case ADD:
                    if ($rdoCompany) {
                        $rdoCompany->synchronized  = 1;
                        $rdoCompany->timemodified  = $time;

                        /* Execute  */
                        $DB->update_record('fs_company',$rdoCompany);

                        /* Insert KS Company        */
                        /* Check if already exists  */
                        $rdo = $DB->get_record('ks_company',array('companyid' => $companyKSFS->ksId));
                        if ((!$rdo) && ($rdoCompany->new)) {
                            $infoCompany = new stdClass();
                            $infoCompany->companyid         = $companyKSFS->ksId;
                            $infoCompany->name              = $companyKSFS->name;
                            $infoCompany->industrycode      = $companyKSFS->industry;
                            $infoCompany->hierarchylevel    = $companyKSFS->level;

                            /* Execute  */
                            $DB->insert_record('ks_company',$infoCompany);
                        }//if_no_exist

                        /* Relation FS KS Companies */
                        $params = array();
                        $params['kscompany']    = 0;
                        $params['fscompany']    = $companyKSFS->fsId;
                        $rdoRelation = $DB->get_record('ksfs_company',$params);
                        if ($rdoRelation) {
                            /* Execute  */
                            $params['kscompany']    = $companyKSFS->ksId;
                            $DB->update_record_raw('ksfs_company',$params,false);
                        }else {
                            /* Execute  */
                            $params['kscompany']    = $companyKSFS->ksId;
                            $DB->insert_record_raw('ksfs_company',$params,false);
                        }//if_rdo

                        /* Synchronized */
                        $sync = true;
                    }//if_exists
                    break;
                case UPDATE:
                    if ($rdoCompany) {
                        $rdoCompany->name          = $companyKSFS->name;
                        $rdoCompany->parent        = $companyKSFS->parent;
                        $rdoCompany->synchronized  = 1;
                        $rdoCompany->timemodified  = $time;

                        /* Execute  */
                        $DB->update_record('fs_company',$rdoCompany);

                        /* Relation FS KS Companies */
                        $params = array();
                        $params['kscompany']    = $companyKSFS->ksId;
                        $params['fscompany']    = $companyKSFS->fsId;
                        $rdoRelation = $DB->get_record('ksfs_company',$params);
                        if (!$rdoRelation) {
                            /* Execute  */
                            $DB->insert_record_raw('ksfs_company',$params,false);
                        }//if_rdo

                        /* Update    */
                        if ($rdoCompany->new) {
                            /* Get Record */
                            $rdo = $DB->get_record('ks_company',array('companyid' => $companyKSFS->ksId));
                            if ($rdo) {
                                $rdo->companyid         = $companyKSFS->ksId;
                                $rdo->name              = $companyKSFS->name;
                                $rdo->industrycode      = $companyKSFS->industry;
                                $rdo->hierarchylevel    = $companyKSFS->level;

                                $DB->update_record('ks_company',$rdo);
                            }
                        }//if_new

                        /* Synchronized */
                        $sync = true;
                    }//if_exists

                    break;
                case DELETE:
                    /* Delete from fs_company   */
                    if ($rdoCompany) {
                        $DB->delete_records('fs_company',array('companyid' => $companyKSFS->fsId));

                        /* Delete Relations     */
                        $DB->delete_records('ksfs_company',array('fscompany' => $companyKSFS->fsId));

                        if ($rdoCompany->new) {
                            $DB->delete_records('ks_company',array('companyid' => $companyKSFS->ksId));
                        }
                    }//if_company

                    /* Synchronized */
                    $sync = true;

                    break;
            }//switch_Action

            /* Synchronized */
            if ($sync) {
                $instance = new stdClass();
                $instance->id       = $impKey;
                $instance->imported = 1;

                $DB->update_record('fs_imp_company',$instance);
            }//if_sync

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//SynchronizeCompanyKSFS

    /**
     * @param           $companyFS
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize company only in FS site.
     */
    private static function SynchronizeCompanyFs($companyFS) {
        /* Variables    */
        global $DB;
        $rdoCompany     = null;
        $params         = null;
        $sync           = null;
        $time           = null;
        $trans          = null;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time   */
            $time = time();

            /* Get Info Company */
            $params = array();
            $params['companyid'] = $companyFS->fscompany;
            $rdoCompany  = $DB->get_record('fs_company',$params);

            /* Apply Action */
            switch ($companyFS->action) {
                case UPDATE:
                    if ($rdoCompany) {
                        $rdoCompany->name          = $companyFS->name;
                        $rdoCompany->parent        = $companyFS->parent;
                        $rdoCompany->synchronized  = 1;
                        $rdoCompany->timemodified  = $time;

                        /* Execute */
                        $DB->update_record('fs_company',$rdoCompany);

                        /* Synchronized */
                        $sync = true;
                    }//if_exists

                    break;
                case DELETE:
                    /* Delete if exists */
                    if ($rdoCompany) {
                        /* Delete FS Company */
                        $DB->delete_records('fs_company',array('id' => $rdoCompany->id));

                        /* Delete FS KS Relation    */
                        $params = array();
                        $params['fscompany'] = $companyFS->fscompany;
                        /* Execute  */
                        $DB->delete_records('ksfs_company',$params);

                        /* Synchronized */
                        $sync = true;
                    }//if_exists

                    break;
            }//action

            /* Synchronized */
            if ($sync) {
                $instance = new stdClass();
                $instance->id       = $companyFS->id;
                $instance->imported = 1;

                $DB->update_record('fs_imp_company',$instance);
            }//if_sync

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//SynchronizeCompanyFs
}//FSKS_COMPANY

/********************/
/* CLASS FSFK_USERS */
/********************/
class FSKS_USERS {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $usersFS
     * @param           $usersImported
     *
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize users accounts
     */
    public static function Synchronize_UsersFS($usersFS,$usersImported) {
        /* Variables    */
        $infoUser   = null;
        $objUser    = null;

        try {
            /* Synchronize users have been imported  */
            foreach ($usersImported as $user) {
                /* Convert to object */
                $objUser = (Object)$user;

                if ($objUser->imported) {
                    /* Get Info User    */
                    $infoUser = $usersFS[$objUser->key];

                    /* Synchronize User */
                    self::SynchronizeUserFS($infoUser,$objUser->key);
                }//if_user_imported
            }//for_userImported
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UsersFS_To_Synchronize

    /**
     * @param           $competenceType
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    11/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get users competences to synchronize
     * companies or Job Roles
     */
    public static function UserCompetenceToSynchronize($competenceType) {
        /* Variables    */
        $toSynchronize = null;

        try {
            /* Get Users Competence  to synchronize  */
            switch ($competenceType) {
                case IMP_COMPETENCE_COMP:
                    $toSynchronize = self::GetUsersCompetence_ToSynchronize();

                    break;
                case IMP_COMPETENCE_JR:
                    $toSynchronize = self::GetUsersCompetenceJR_ToSynchronize();

                    break;
            }//competenceType

            return $toSynchronize;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UserCompetenceToSynchronize

    /**
     * @param           $usersCompetence
     * @param           $competencesImported
     * @param           $competenceType
     *
     * @throws          Exception
     *
     * @creationDate    11/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize User Competence in FS Site
     * Companies or job roles
     */
    public static function Synchronize_UsersCompetenceFS($usersCompetence, $competencesImported,$competenceType) {
        /* Variables    */
        $infoCompetence = null;
        $objCompetence  = null;
        $functionName   = null;

        try {
            /* Competence Type  */
            switch ($competenceType) {
                case IMP_COMPETENCE_COMP:
                    $functionName = 'SynchronizeCompetenceCompanyFS';

                    break;
                case IMP_COMPETENCE_JR:
                    $functionName = 'SynchronizeCompetenceJobRolesFS';

                    break;
            }//competence_type

            /* Synchronize User Competence Company */
            foreach ($competencesImported as $competence) {
                /* Convert to object    */
                $objCompetence = (Object)$competence;

                if ($objCompetence->imported) {
                    /* Get Info Competence */
                    $infoCompetence = $usersCompetence[$objCompetence->key];

                    /* Synchronize Competence Company */
                    self::$functionName($infoCompetence,$objCompetence->key);
                }//if_imported
            }//for_competencesImported
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Synchronize_UsersCompetenceFS


    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $userFS
     * @param           $fsKey
     *
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize user account ino FS.
     */
    private static function SynchronizeUserFS($userFS,$fsKey) {
        /* Variables    */
        global $DB,$CFG;
        $rdoUser    = null;
        $params     = null;
        $infoUser   = null;
        $instance   = null;
        $time       = null;
        $sync       = false;
        $trans      = null;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time   */
            $time = time();

            /* Info User to check if already exist  */
            $params = array();
            $params['username'] = $userFS->personalnumber;
            $rdoUser = $DB->get_record('user',$params,'id');

            /* Info Account */
            if (!$rdoUser) {
                /* Create new Account   */
                $infoUser = new stdClass();
                $infoUser->username     = $userFS->personalnumber;
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
            }//if_no_exist

            /* Apply synchronization    */
            switch ($userFS->action) {
                case ADD:
                    /* Execute      */
                    if (!$rdoUser) {
                        $infoUser->id = $DB->insert_record('user',$infoUser);

                        /* Synchronized */
                        $sync = true;
                    }//if_no_exists

                    break;
                case UPDATE:
                    /* Check if exists  */
                    if ($rdoUser) {
                        /* Update   */
                        $rdoUser->username     = $userFS->personalnumber;
                        $rdoUser->firstname    = $userFS->firstname;
                        $rdoUser->lastname     = $userFS->lastname;
                        $rdoUser->email        = $userFS->email;
                        $rdoUser->timemodified = $time;

                        /* Execute  */
                        $DB->update_record('user',$rdoUser);
                    }else {
                        /* Execute  */
                        $infoUser->id = $DB->insert_record('user',$infoUser);
                    }//if_else

                    /* Synchronized */
                    $sync = true;

                    break;
                case DELETE:
                    /* Delete   */
                    if ($rdoUser) {
                        $rdoUser->timemodified = $time;
                        $rdoUser->deleted      = 1;

                        /* Execute  */
                        $DB->update_record('user',$rdoUser);
                    }else {
                        /* Execute  */
                        $infoUser->deleted      = 1;
                        $infoUser->id = $DB->insert_record('user',$infoUser);
                    }//if_exist

                    /* Synchronized */
                    $sync = true;

                    break;
            }//switch_Action

            /* Synchronized */
            if ($sync) {
                $instance = new stdClass();
                $instance->id       = $fsKey;
                $instance->imported = 1;

                $DB->update_record('fs_imp_users',$instance);
            }//if_sync

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//SynchronizeUserFS

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    11/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get User Competences Companies to synchronize
     */
    private static function GetUsersCompetence_ToSynchronize() {
        /* Variables    */
        global $DB;
        $params     = null;
        $sql        = null;
        $rdo        = null;
        $usersComp  = array();
        $infoComp   = null;

        try {
            /* Search criteria */
            $params = array();
            $params['imported'] = 0;

            /* SQL Instruction  */
            $sql = " SELECT	fs.id,
                            fs.fodselsnr,
                            fsk.fscompany,
                            fsk.kscompany,
                            ks.hierarchylevel,
                            fs.prioritet,
                            fs.action
                     FROM	  {fs_imp_users_company}  fs
                        JOIN  {user}				  u 	ON  u.username    = fs.fodselsnr
                                                            AND u.deleted     = 0
                        JOIN  {ksfs_company}		  fsk	ON  fsk.fscompany = fs.org_enhet_id
                        JOIN  {ks_company}			  ks	ON	ks.companyid  = fsk.kscompany
                     WHERE	fs.imported	= :imported ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Competence  */
                    $infoComp = new stdClass();
                    $infoComp->personalNumber   = $instance->fodselsnr;
                    $infoComp->ksId             = $instance->kscompany;
                    $infoComp->fsId             = $instance->fscompany;
                    $infoComp->level            = $instance->hierarchylevel;
                    $infoComp->manager          = ($instance->prioritet == 1 ? 1 : 0);
                    $infoComp->action           = $instance->action;

                    /* Add Competence   */
                    $usersComp[$instance->id] = $infoComp;
                }//for_Rdo
            }//if_rdo

            return $usersComp;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsersCompetence_ToSynchronize

    /**
     * @return          null
     * @throws          Exception
     *
     * @creationDate    12/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Users Competence Job Role to synchronize
     */
    private static function GetUsersCompetenceJR_ToSynchronize() {
        /* Variables */
        global $DB;
        $params     = null;
        $sql        = null;
        $rdo        = null;
        $usersComp  = null;
        $infoComp   = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['imported'] = 0;

            /* SQL Instruction  */
            $sql = " SELECT	  fs_imp.id,
                              fs_imp.fodselsnr,
                              fsk_jr.fsjobrole,
                              ks_jr.jobroleid,
                              fsk_co.fscompany,
                              ks_co.companyid,
                              ks_co.hierarchylevel,
                              fs_imp.action
                     FROM	    {fs_imp_users_jr}	fs_imp
                        JOIN	{user}				u		ON 		u.username 			= fs_imp.fodselsnr
                                                            AND     u.deleted           = 0
                        -- CHECK USER COMPETENCE COMAPNY
                        JOIN	{fs_users_company}	uco		ON		uco.personalnumber	= u.username
                                                            AND		uco.companyid		= fs_imp.org_enhet_id
                        -- CHECK COMPANY
                        JOIN	{ksfs_company}		fsk_co	ON 		fsk_co.fscompany	= uco.companyid
                        JOIN	{ks_company}		ks_co	ON		ks_co.companyid		= fsk_co.kscompany
                        -- CHECK JOB ROLE
                        JOIN	{ksfs_jobroles}		fsk_jr 	ON 		fsk_jr.fsjobrole 	= fs_imp.stillingskode
                        JOIN 	{ks_jobroles}		ks_jr	ON		ks_jr.jobroleid 	= fsk_jr.ksjobrole

                     WHERE		fs_imp.imported = :imported ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Competence JR   */
                    $infoComp = new stdClass();
                    $infoComp->personalNumber   = $instance->fodselsnr;
                    $infoComp->fsJrId           = $instance->fsjobrole;
                    $infoComp->jobrole          = $instance->jobroleid;
                    $infoComp->fsId             = $instance->fscompany;
                    $infoComp->company          = $instance->companyid;
                    $infoComp->level            = $instance->hierarchylevel;
                    $infoComp->action           = $instance->action;

                    /* Add competence */
                    $usersComp[$instance->id] = $infoComp;
                }//for_rdo
            }//if_Rdo

            return $usersComp;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsersCompetenceJR_ToSynchronize

    /**
     * @param           $competenceFS
     * @param           $fsKey
     *
     * @throws          Exception
     *
     * @creationDate    11/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize user competence company in FS
     */
    private static function SynchronizeCompetenceCompanyFS($competenceFS,$fsKey) {
        /* Variables    */
        global $DB;
        $infoCompetence = null;
        $rdo            = null;
        $params         = null;
        $sync           = null;
        $trans          = null;

        /* Start Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* GEt info fs_user_company     */
            $params = array();
            $params['personalnumber']    = $competenceFS->personalNumber;
            $params['companyid']         = $competenceFS->fsId;
            $rdo = $DB->get_record('fs_users_company',$params);

            /* Apply action */
            switch ($competenceFS->action) {
                case ADD:
                    /* Check if already exists  */
                    if ($rdo) {
                        $rdo->synchronized = 1;

                        /* Execute  */
                        $DB->update_record('fs_users_company',$rdo);
                    }else {
                        /* Create Entry */
                        $infoCompetence = new stdClass();
                        $infoCompetence->companyid          = $competenceFS->fsId;
                        $infoCompetence->personalnumber     = $competenceFS->personalNumber;
                        $infoCompetence->level              = $competenceFS->level;
                        $infoCompetence->priority           = ($competenceFS->manager ? 1 : 99);
                        $infoCompetence->synchronized       = 1;

                        /* Execute  */
                        $DB->insert_record('fs_users_company',$infoCompetence);
                    }//if_exists

                    /* Synchronized */
                    $sync = true;

                    break;
                case UPDATE:
                    /* Update if exists */
                    if ($rdo) {
                        $rdo->companyid          = $competenceFS->fsId;
                        $rdo->personalnumber     = $competenceFS->personalNumber;
                        $rdo->level              = $competenceFS->level;
                        $rdo->priority           = ($competenceFS->manager ? 1 : 99);
                        $rdo->synchronized       = 1;

                        /* Execute  */
                        $DB->update_record('fs_users_company',$rdo);

                        /* Synchronized */
                        $sync = true;
                    }//if_exists

                    break;
                case DELETE:
                    /* Delete if exists */
                    if ($rdo) {
                        $DB->delete_records('fs_users_company',array('id' => $rdo->id));

                        /* Synchronized */
                        $sync = true;
                    }//if_exists

                    break;
            }//action

            /* Synchronized */
            if ($sync) {
                $instance = new stdClass();
                $instance->id       = $fsKey;
                $instance->imported = 1;

                $DB->update_record('fs_imp_users_company',$instance);
            }//if_sync

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//SynchronizeCompetenceCompanyFS

    /**
     * @param           $competenceFS
     * @param           $fsKey
     *
     * @throws          Exception
     *
     * @creationDate    12/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize user competence job role in FS
     */
    private static function SynchronizeCompetenceJobRolesFS($competenceFS,$fsKey) {
        /* Variables */
        global $DB;
        $params         = null;
        $rdo            = null;
        $sync           = null;
        $infoCompetence = null;
        $trans          = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Get Info User Job Role (FS) */
            $params = array();
            $params['personalnumber']   = $competenceFS->personalNumber;
            $params['companyid']        = $competenceFS->fsId;
            $params['jrcode']           = $competenceFS->fsJrId;
            $rdo = $DB->get_record('fs_users_jobroles',$params);

            /* Apply Action */
            switch ($competenceFS->action) {
                case ADD:
                    /* Check if already exists  */
                    if ($rdo) {
                        $rdo->synchronized = 1;

                        /* Execute */
                        $DB->update_record('fs_users_jobroles',$rdo);
                    }else {
                        /* New Entry    */
                        $infoCompetence = new stdClass();
                        $infoCompetence->personalnumber = $competenceFS->personalNumber;
                        $infoCompetence->companyid      = $competenceFS->fsId;
                        $infoCompetence->jrcode         = $competenceFS->fsJrId;
                        $infoCompetence->synchronized   = 1;

                        /* Execute */
                        $DB->insert_record('fs_users_jobroles',$infoCompetence);
                    }//if_else

                    /* Synchronized */
                    $sync = true;

                    break;
                case UPDATE:
                    /* Check if already exists  */
                    if ($rdo) {
                        /* Update */
                        $rdo->synchronized   = 1;

                        /* Execute */
                        $DB->update_record('fs_users_jobroles',$rdo);

                        /* Synchronized */
                        $sync = true;
                    }//if_exist

                    break;
                case DELETE:
                    /* Delete if exists  */
                    if ($rdo) {
                        $DB->delete_records('fs_users_jobroles',array('id' => $rdo->id));

                        /* Synchronized */
                        $sync = true;
                    }//if_exits

                    break;
            }//switch_action

            /* Synchronized */
            if ($sync) {
                $instance = new stdClass();
                $instance->id       = $fsKey;
                $instance->imported = 1;

                $DB->update_record('fs_imp_users_jr',$instance);
            }//if_sync

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback    */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//SynchronizeCompetenceJobRolesFS

}//FSKS_USERS

/************/
/* CLASS FS */
/************/
class FS {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $data
     * @param           $type
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save in temporary tables. Step before synchronization
     */
    public static function SaveTemporary_Fellesdata($data,$type) {
        /* Variables    */
        $action         = null;
        $newEntry       = null;
        $lineContent    = null;
        $toSave         = array();

        try {

            foreach($data as $key=>$line) {
                $line           = strtolower($line);
                $lineContent    = json_decode($line);

                /* Get New Entry    */
                $newEntry = $lineContent->newrecord;

                /* Get Action       */
                switch (trim($lineContent->changetype)) {
                    case ADD_ACTION:
                        $action = 0;

                        break;
                    case UPDATE_ACTION:
                        $action = 1;

                        break;
                    case DELETE_ACTION:
                        /* Old Entry        */
                        if (isset($lineContent->oldrecord)) {
                            $newEntry = $lineContent->odlrecord;
                        }//if_old_record

                        $action = 2;

                        break;
                }//action

                $newEntry->action   = $action;
                $newEntry->imported = 0;

                /* Add Record   */
                $toSave[$key] = $newEntry;
            }

            if ($toSave) {
                switch ($type) {
                    case IMP_USERS:
                        /* FS Users     */
                        self::ImportTemporary_FSUsers($toSave);

                        break;
                    case IMP_COMPANIES:
                        /* FS Companies */
                        self::ImportTemporary_FSCompany($toSave);

                        break;
                    case IMP_JOBROLES:
                        /* FS JOB ROLES */
                        self::ImportTemporary_FSJobRoles($toSave);

                        break;
                    case IMP_COMPETENCE_COMP:
                        /* Competence Company */
                        self::ImportTemporary_CompetenceCompany($toSave);

                        break;
                    case IMP_COMPETENCE_JR:
                        /* Competence Job Role  */
                        self::ImportTemporary_CompetenceJobRole($toSave);

                        break;
                }//type
            }//if_toSave
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ExtractData_TemporaryFellesdata

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $data
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save FS users in temporary tables before the synchronization
     */
    private static  function ImportTemporary_FSUsers($data) {
        /* Variables    */
        global $DB;
        $infoUser   = null;
        $trans      = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* User Info    */
            /* Execute  */
            $DB->insert_records('fs_imp_users',$data);

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ImportTemporary_FSUsers

    /**
     * @param           $data
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save FS companies in temporary tables before the synchronization
     */
    private static function ImportTemporary_FSCompany($data) {
        /* Variables    */
        global $DB;
        $infoFS     = null;
        $trans      = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* FS Company Info  */
            /* Execute  */
            $DB->insert_records('fs_imp_company',$data);

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ImportTemporary_FSCompany

    /**
     * @param               $data
     *
     * @throws              Exception
     *
     * @creationDate    04/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save FS Jobroles in temporary tables before the synchronization
     */
    private static function ImportTemporary_FSJobRoles($data) {
        /* Variables    */
        global $DB;
        $infoFS = null;
        $trans  = null;

        /* Start transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* FS Job Role Info */
            /* Execute  */
            $DB->insert_records('fs_imp_jobroles',$data);

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ImportTemporary_FSJobRoles

    /**
     * @param           $data
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save User Company (FS)  in temporary tables before the synchronization
     */
    private static function ImportTemporary_CompetenceCompany($data) {
        /* Variables */
        global $DB;
        $infoCompetence     = null;
        $infoOldCompetence  = null;
        $trans              = null;

        /* Start transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Info Competence      */
            /* Execute  */
            $DB->insert_records('fs_imp_users_company',$data);

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ImportTemporary_CompetenceCompany

    /**
     * @param           $data
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save User Job Role (FS)  in temporary tables before the synchronization
     */
    private static function ImportTemporary_CompetenceJobRole($data) {
        /* Variables    */
        global $DB;
        $infoCompetenceJR       = null;
        $infoOldCompetenceJR    = null;
        $trans                  = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Execute */
            $DB->insert_records('fs_imp_users_jr',$data);

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ImportTemporary_CompetenceJobRole
}//class FS

/************/
/* CLASS KS */
/************/
class KS {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @return          int|string
     * @throws          Exception
     *
     * @creationDate    04/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get existing companies
     */
    public static function ExistingCompanies() {
        /* Variables    */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $companiesKS    = 0;
        $companies      = array();
        $params         = null;

        try {
            /* Execute  */
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
    }//ExistingCompanies

    /**
     * @param           $orgStructure
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import Organization Structure
     */
    public static function ImportKSOrganization($orgStructure) {
        /* Variables */
        $infoCompany    = null;

        try {
            /* Import KS Company    */
            foreach ($orgStructure as $company) {
                /* Convert to object    */
                $infoCompany = (Object)$company;

                self::ImportKSCompany($infoCompany);
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//ImportKSCompany

    /**
     * @param           $jobRoles
     * @param      bool $generics
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import KS Job Roles
     */
    public static function KSJobRoles($jobRoles,$generics=false) {
        /* Variables */
        $infoJR    = null;

        try {
            /* Import KS Job Role */
            foreach ($jobRoles as $jr) {
                /* Convert to object    */
                $infoJR = (Object)$jr;

                self::ImportKSJobRole($infoJR,$generics);
            }//for_jobroles
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//KSJobRoles

    /**
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
     * Description
     * Get existing job roles
     *
     */
    public static function ExistingJobRoles($generics=false,$top=null) {
        /* Variables    */
        global $DB;
        $jobRoles   = null;
        $jrFS       = 0;
        $sql        = null;
        $rdo        = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT jr.id
                     FROM 		{ks_jobroles}			jr
                        JOIN 	{ks_jobroles_relation} 	jr_re	ON jr_re.jobroleid = jr.jobroleid
                   ";

            /* Generics */
            if ($generics) {
                $sql .= "  AND  jr_re.levelzero IS NULL
                           OR   jr_re.levelzero = 0";
            }else {
                $sql .= " AND jr_re.levelzero IN ($top) ";
            }//if_generics

            /* Execute  */
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
    }//ExistingJobRoles

    /**
     * @param           $name
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    04/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get top hierarchy
     */
    public static function GetHierarchy_JR($name) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $hierarchy  = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT ks.companyid
                     FROM	{ks_company} ks
                     WHERE	ks.name like '%". $name . "%'
                        AND	ks.hierarchylevel = 0 ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                $hierarchy = array();

                foreach ($rdo as $instance) {
                    $hierarchy[$instance->companyid] = $instance->companyid;
                }//for_Rdo

                $hierarchy = implode(',',$hierarchy);
            }//if_Rdo

            return $hierarchy;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetHierarchy_JR

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $company
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import KS Company
     */
    private static function ImportKSCompany($company) {
        /* Variables */
        global $DB;
        $infoCompany    = null;
        $infoRelation   = null;
        $rdoCompany     = null;
        $rdoRelation    = null;
        $trans          = null;

        /* Start Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Get Company to check if already exists   */
            $rdoCompany = $DB->get_record('ks_company',array('companyid' => $company->id));
            if (!$rdoCompany) {
                /* KS Company       */
                $infoCompany = new stdClass();
                $infoCompany->companyid         = $company->id;
                $infoCompany->name              = $company->name;
                $infoCompany->industrycode      = $company->industrycode;
                $infoCompany->hierarchylevel    = $company->level;
                $infoCompany->parent            = $company->parent;

                /* Execute */
                $DB->insert_record('ks_company',$infoCompany);
            }//if_rdoCompany

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ImportKSCompany

    /**
     * @param           $jobRole
     * @param      bool $generic
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import Job Role
     */
    private static function ImportKSJobRole($jobRole,$generic=false) {
        /* Variables */
        global $DB;
        $infoJR         = null;
        $infoJRRelation = null;
        $rdoJR          = null;
        $rdoRelation    = null;
        $params         = null;
        $trans          = null;

        /* Start transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Check if already exists  */
            $rdoJR = $DB->get_record('ks_jobroles',array('jobroleid' => $jobRole->id));
            if (!$rdoJR) {
                /* Job Role */
                $infoJR = new stdClass();
                $infoJR->jobroleid      = $jobRole->id;
                $infoJR->name           = $jobRole->name;
                $infoJR->industrycode   = $jobRole->industryCode;

                /* Execute */
                $DB->insert_record('ks_jobroles',$infoJR);

                /* Job Role Relation */
                $infoJRRelation = new stdClass();
                $infoJRRelation->jobroleid  = $jobRole->id;
                if ($generic) {
                    $infoJRRelation->levelzero  = null;
                    $infoJRRelation->levelone   = null;
                    $infoJRRelation->leveltwo   = null;
                    $infoJRRelation->levelthree = null;

                    /* Execute */
                    $DB->insert_record('ks_jobroles_relation',$infoJRRelation);
                }else {
                    /* Search criteria  */
                    $params = array();
                    $params['jobroleid'] = $jobRole->id;

                    foreach ($jobRole->relation as $relation) {
                        /* Check if already exist   */
                        $params['levelzero']    = $relation['levelZero'];
                        $params['levelone']     = $relation['levelOne'];
                        $params['leveltwo']     = $relation['levelTwo'];
                        $params['levelthree']   = $relation['levelThree'];

                        /* Execute */
                        $rdoRelation = $DB->get_record('ks_jobroles_relation',$params);
                        if (!$rdoRelation) {
                            $infoJRRelation->levelzero  = $relation['levelZero'];
                            $infoJRRelation->levelone   = $relation['levelOne'];
                            $infoJRRelation->leveltwo   = $relation['levelTwo'];
                            $infoJRRelation->levelthree = $relation['levelThree'];

                            /* Execute */
                            $DB->insert_record('ks_jobroles_relation',$infoJRRelation);
                        }//if_not_Exist
                    }//relations
                }//if_generic
            }//if_no_exit

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ImportKSJobRoles
}//class_KS
