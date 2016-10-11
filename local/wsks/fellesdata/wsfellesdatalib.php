<?php
/**
 * Fellesdata Integration - Library
 *
 * @package         local
 * @subpackage      wsks/fellesdata
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    26/01/2016
 * @author          eFaktor     (fbv)
 *
 */

define('ADD_ACTION',0);
define('UPDATE_ACTION',1);
define('DELETE_ACTION',2);

define('MANAGER','manager');
define('REPORTER','reporter');

class WS_FELLESDATA {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $notIn
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get generics job roles
     */
    public static function GenericsJobRoles($notIn,&$result) {
        /* Variables    */

        try {
            /* Get generics job roles */
            $result['jobroles'] = self::Get_GenericsJobRoles($notIn['notIn']);
        }catch (Exception $ex) {
            $result['error']    = 409;
            $result['message']  = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//GenericsJobRoles

    /**
     * @param           $hierarchy
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get job roles by level
     */
    public static function JobRolesByLevel($hierarchy,&$result) {
        /* Variables */

        try {
            /* Job Roles by Level */
            $result['jobroles'] = self::Get_JobRolesByLevel($hierarchy['top'],$hierarchy['notIn']);
        }catch (Exception $ex) {
            $result['error']    = 409;
            $result['message']  = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//JobRolesByLevel

    /**
     * @param           $top
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get organization structure for a specific level
     * In this case, top level is company.
     */
    public static function OrganizationStructureByTop($top,&$result) {
        /* Variables */
        $infoTop = null;

        try {
            /* Convert to object    */
            $infoTop = (Object)$top;

            /* Get Organization Structure*/
            $result['structure'] = self::Get_OrganizationStructureByTop($infoTop);
        }catch (Exception $ex) {
            $result['error']    = 409;
            $result['message']  = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//OrganizationStructureByTop


    /**
     * @param           $companiesFS
     *
     * @param           $result
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronization between FS and KS companies
     */
    public static function Synchronize_FSKS_Companies($companiesFS,&$result) {
        /* Variables */
        global $CFG;
        $objCompany     = null;
        $companyId      = null;
        $imported       = array();
        $infoImported   = null;
        $dbLog = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronize FSKS Companies . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Synchronization between FS and KS companies */
            foreach ($companiesFS as $key => $company) {
                /* Convert to object    */
                $objCompany = (Object)$company;

                /* Process the company */
                $companyId = self::ProcessFSCompany($objCompany);

                /* Marked s imported    */
                if ($companyId) {
                    $infoImported = new stdClass();
                    $infoImported->fsId     = $objCompany->fsId;
                    $infoImported->ksId     = $companyId;
                    $infoImported->imported = 1;
                    $infoImported->key      = $key;

                    $imported[$key] = $infoImported;
                }//if_companyId
            }//for_FS_companies

            $result['companies'] = $imported;

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronize FSKS Companies . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog  = $ex->getMessage() . "\n\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Synchronize FSKS Companies . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            $result['error']     = 409;
            $result['message']   = $ex->getMessage();
            $result['companies'] = $imported;

            throw $ex;
        }//try_catch
    }//Synchronize_FSKS_Companies

    /**
     * @param           $jobRolesFS
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize Job roles between FS and KS
     */
    public static function Synchronize_FSKS_JobRoles($jobRolesFS,&$result) {
        /* Variables */
        global $CFG;
        $objJobRole     = null;
        $jobRoleId      = null;
        $imported       = array();
        $infoImported   = null;
        $dbLog = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronize FSKS JobRoles . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Synchronization between FS and KS companies */
            foreach ($jobRolesFS as $key => $jobRole) {
                /* Convert to obejct    */
                $objJobRole = (Object)$jobRole;

                /* Process job role */
                $jobRoleId = self::ProcessFSJobRoles($objJobRole);

                /* Marked as Imported   */
                if ($jobRoleId) {
                    $infoImported = new stdClass();
                    $infoImported->fsId     = $objJobRole->fsId;
                    $infoImported->ksId     = $jobRoleId;
                    $infoImported->imported = 1;
                    $infoImported->key      = $key;

                    $imported[$key] = $infoImported;
                }//ifJobRoleId
            }//for_jobRoles

            $result['jobRoles'] = $imported;

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronize FSKS JobRoles . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog  = $ex->getMessage() . "\n\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINSIH ERROR Synchronize FSKS JobRoles . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            $result['error']    = 409;
            $result['message']  = $ex->getMessage();
            $result['jobRoles'] = $imported;

            throw $ex;
        }//try_catch
    }//Synchronize_FSKS_JobRoles

    /**
     * @param           $usersAccounts
     *
     * @param           $result
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize users accounts between FS and KS
     */
    public static function Synchronize_UsersAccounts($usersAccounts,&$result) {
        /* Variables    */
        global $CFG;
        $userId         = null;
        $imported       = array();
        $infoImported   = null;
        $infoAccount    = null;
        $dbLog          = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization Users Accoutns . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Synchronization between FS and KS. Users accounts */
            foreach ($usersAccounts as $key => $account) {
                /* InfoAccount  */
                $infoAccount = (Object)$account;

                /* Process Account */
                $userId = self::ProcessUserAccount($infoAccount);

                /* Marked as imported */
                if($userId) {
                    $infoImported = new stdClass();
                    $infoImported->personalnumber   = $infoAccount->personalnumber;
                    $infoImported->imported         = 1;
                    $infoImported->key              = $key;

                    $imported[$key] = $infoImported;
                }//if_userid
            }//for_usersAccounts

            $result['usersAccounts'] = $imported;

            /* Log  */
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization Users Accoutns . ' . "\n"."\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();
            $result['usersAccounts']    = $imported;

            $dbLog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). 'FINISH Synchronization Users Accoutns . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//Synchronize_UsersAccounts


    /**
     * @param           $userManagerReporter
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize managers reporters from fellesdata
     */
    public static function Synchronize_UserManagerReporter($userManagerReporter,&$result) {
        /* Variables */
        global $CFG;
        $objManagerReporter     = null;
        $managerReporter        = null;
        $synchronized           = null;
        $infoImported           = null;
        $imported               = array();
        $dbLog                  = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization User Manager Reporter  . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Synchronize user manager reporter between FS and KS */
            foreach ($userManagerReporter as $key => $managerReporter) {
                /* Convert to object    */
                $objManagerReporter = (Object)$managerReporter;

                /* Process user Manager Reporter */
                $synchronized = self::ProcessUserManagerReporter($objManagerReporter);

                /* Marked as imported */
                if ($synchronized) {
                    $infoImported = new stdClass();
                    $infoImported->personalNumber   = $objManagerReporter->personalNumber;
                    $infoImported->imported         = 1;
                    $infoImported->key              = $key;

                    $imported[$key] = $infoImported;
                }//if_competenceData
            }//for_competences

            $result['managerReporter'] = $imported;

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' Finish Synchronization User Manager Reporter  . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog  = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Synchronization User Manager Reporter  . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            $result['error']            = 409;
            $result['message']          = $ex->getMessage();
            $result['managerReporter']  = $imported;

            throw $ex;
        }//try_catch
    }//Synchronize_UserManagerReporter

    /**
     * @param           $usersCompetence
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize user competence between FS and KS
     */
    public static function Synchronize_UserCompetence($usersCompetence,&$result) {
        /* Variables */
        global $CFG;
        $objCompetence      = null;
        $competenceDataID   = null;
        $infoImported       = null;
        $imported           = array();
        $dbLog              = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization User Competence. ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Synchronization competence between FS and KS */
            foreach ($usersCompetence as $key => $competence) {
                /* Convert to object    */
                $objCompetence = (Object)$competence;

                /* Process the competence */
                $competenceDataID = self::ProcessUserCompetence($objCompetence);

                /* Marked as imported */
                if ($competenceDataID) {
                    $infoImported = new stdClass();
                    $infoImported->personalNumber   = $objCompetence->personalNumber;
                    $infoImported->imported         = 1;
                    $infoImported->key              = $key;

                    $imported[$key] = $infoImported;
                }//if_competenceDataID
            }//for_competences

            $result['usersCompetence'] = $imported;

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization User Competence. ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog = $ex->getMessage() . "\n\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Synchronization User Competence. ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            $result['error']            = 409;
            $result['message']          = $ex->getMessage();
            $result['usersCompetence']  = $imported;

            throw $ex;
        }//try_catch
    }//Synchronize_UserCompetence

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $managerReporter
     *
     * @return          bool|null
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Process user Manager Reporter from Fellesdata
     */
    private static function ProcessUserManagerReporter($managerReporter) {
        /* Variables */
        global $DB;
        $time                   = null;
        $infoManager            = null;
        $infoReporter           = null;
        $manager                = 0;
        $reporter               = 0;

        $user                   = null;
        $rdo                    = null;
        $params                 = null;
        $sync                   = null;
        $trans                  = null;


        /* Start Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Get User data */
            $user = $DB->get_record('user',array('username' => $managerReporter->personalNumber,'deleted' => '0'),'id');

            /* Check If Exist */
            if ($user) {
                /* Manager && Reporter  */
                if (($managerReporter->prioritet == 1) ||
                    ($managerReporter->prioritet == 2)) {
                    $manager = 1;
                    $reporter = 1;
                }//Manager&&Reporter
                /* Reporter */
                if (($managerReporter->prioritet != 1) &&
                    ($managerReporter->prioritet != 2)) {
                    $reporter  = 1;
                }//Manager&&Reporter

                /* Get Info Manager */
                list($infoManager,$infoReporter) = self::GetInfoManager($managerReporter->ksId,$managerReporter->level,$user->id);

                /* Apply Action */
                switch ($managerReporter->action) {
                    case ADD_ACTION:

                        /* Add the user as manager if it's the case */
                        if ($manager) {
                            /* Check if the user is already manager or not */
                            $IsManager = self::IsManagerReporter($infoManager,MANAGER);
                            if (!$IsManager) {
                                /* Create   */
                                $DB->insert_record('report_gen_company_manager',$infoManager);
                            }//if_manager

                            /* Check if the user is already reporter or not */
                            $IsReporter = self::IsManagerReporter($infoReporter,REPORTER);
                            if (!$IsReporter) {
                                /* Create */
                                $DB->insert_record('report_gen_company_reporter',$infoReporter);
                            }//if_reporter
                        }else if($reporter) {
                            /* Check if the user is already reporter or not */
                            $IsReporter = self::IsManagerReporter($infoReporter,REPORTER);
                            if (!$IsReporter) {
                                /* Create */
                                $DB->insert_record('report_gen_company_reporter',$infoReporter);
                            }//if_reporter
                        }

                        /* Synchronized */
                        $sync = true;

                        break;
                    case UPDATE_ACTION:

                        /* Add the user as manager if it's the case */
                        if ($manager) {
                            /* Check if the user is already manager or not */
                            $IsManager = self::IsManagerReporter($infoManager,MANAGER);
                            if (!$IsManager) {
                                /* Create   */
                                $DB->insert_record('report_gen_company_manager',$infoManager);
                            }//if_manager

                            /* Check if the user is already reporter or not */
                            $IsReporter = self::IsManagerReporter($infoReporter,REPORTER);
                            if (!$IsReporter) {
                                /* Create */
                                $DB->insert_record('report_gen_company_reporter',$infoReporter);
                            }//if_reporter
                        }else if ($reporter) {
                            /* Check if the user is already reporter or not */
                            $IsReporter = self::IsManagerReporter($infoReporter,REPORTER);
                            if (!$IsReporter) {
                                /* Create */
                                $DB->insert_record('report_gen_company_reporter',$infoReporter);
                            }//if_reporter
                        }

                        /* Synchronized */
                        $sync = true;

                        break;
                    case DELETE_ACTION:
                        /* Delete From Manager  */
                        if ($manager) {
                            $IsManager = self::IsManagerReporter($infoManager,MANAGER);
                            if ($IsManager) {
                                $DB->delete_records('report_gen_company_manager',array('id' => $IsManager));
                            }//if_Manager

                            /* Delete From Reporter */
                            $IsReporter = self::IsManagerReporter($infoReporter,REPORTER);
                            if ($IsReporter) {
                                $DB->delete_records('report_gen_company_reporter',array('id' => $IsReporter));
                            }//if_reporter
                        }else if ($reporter) {
                            /* Delete From Reporter */
                            $IsReporter = self::IsManagerReporter($infoReporter,REPORTER);
                            if ($IsReporter) {
                                $DB->delete_records('report_gen_company_reporter',array('id' => $IsReporter));
                            }//if_reporter
                        }//if_manager

                        /* Synchronized */
                        $sync = true;

                        break;
                }//action
            }//if_user

            /* Commit */
            $trans->allow_commit();

            return $sync;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ProcessUserManagerReporter

    /**
     * @param           $company
     * @param           $level
     * @param           $userId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    11/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get information to add the user as a manager and reporter
     */
    private static function GetInfoManager($company,$level,$userId) {
        /* Variables    */
        global $DB;
        $infoManager    = null;
        $infoReporter   = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $maxLevel       = null;
        $time           = null;

        try {
            /* Local Time   */
            $time = time();

            /* Info Manager */
            $infoManager = new stdClass();
            $infoManager->levelzero         = 0;
            $infoManager->levelone          = null;
            $infoManager->leveltwo          = null;
            $infoManager->levelthree        = null;
            $infoManager->managerid         = $userId;
            $infoManager->hierarchylevel    = $level;
            $infoManager->timecreated       = $time;

            /* Info Reporter    */
            $infoReporter = new stdClass();
            $infoReporter->reporterid        = $userId;
            $infoReporter->levelzero         = 0;
            $infoReporter->levelone          = null;
            $infoReporter->leveltwo          = null;
            $infoReporter->levelthree        = null;
            $infoReporter->hierarchylevel    = $level;
            $infoReporter->timecreated       = $time;

            /* Search Criteria  */
            $params = array();
            $params['company'] = $company;
            $params['level'] = $level;

            /* SQL Instruction  */
            switch ($level) {
                case '0':
                    /* Manager  */
                    $infoManager->levelzero         = $company;
                    $infoManager->levelone          = null;
                    $infoManager->leveltwo          = null;
                    $infoManager->levelthree        = null;

                    /* Reporter */
                    $infoReporter->levelzero         = $company;
                    $infoReporter->levelone          = null;
                    $infoReporter->leveltwo          = null;
                    $infoReporter->levelthree        = null;

                    break;
                case '1':
                    /* SQL Instruction  */
                    $sql = " SELECT	co.id 		as 'levelone',
                                    co_zero.id 	as 'levelzero'
                             FROM	  {report_gen_companydata}	co
                                -- LEVEL ZERO
                                JOIN  {report_gen_company_relation}	cr_zero		ON	cr_zero.companyid 		= co.id
                                JOIN  {report_gen_companydata}		co_zero		ON	co_zero.id				= cr_zero.parentid
                                                                                    AND	co_zero.hierarchylevel 	= 0
                             WHERE	co.id             = :company
                                AND	co.hierarchylevel = :level ";

                    /* Execute  */
                    $rdo = $DB->get_record_sql($sql,$params);
                    if ($rdo) {
                        /* Manager  */
                        $infoManager->levelzero         = $rdo->levelzero;
                        $infoManager->levelone          = $rdo->levelone;
                        $infoManager->leveltwo          = null;
                        $infoManager->levelthree        = null;

                        /* Reporter */
                        $infoReporter->levelzero         = $rdo->levelzero;
                        $infoReporter->levelone          = $rdo->levelone;
                        $infoReporter->leveltwo          = null;
                        $infoReporter->levelthree        = null;
                    }//if_rdo

                    break;
                case '2':
                    /* SQL Instruction  */
                    $sql = " SELECT	co.id 		as 'leveltwo',
                                    co_one.id 	as 'levelone',
                                    co_zero.id 	as 'levelzero'
                             FROM	  {report_gen_companydata}	co
                                -- LEVEL ONE
                                JOIN  {report_gen_company_relation}	cr_one 		ON 	cr_one.companyid 		= co.id
                                JOIN  {report_gen_companydata}		co_one		ON	co_one.id 				= cr_one.parentid
                                                                                    AND co_one.hierarchylevel 	= 1
                                -- LEVEL ZERO
                                JOIN  {report_gen_company_relation}	cr_zero		ON	cr_zero.companyid 		= co_one.id
                                JOIN  {report_gen_companydata}		co_zero		ON	co_zero.id				= cr_zero.parentid
                                                                                    AND	co_zero.hierarchylevel 	= 0
                             WHERE	co.id 				= :company
                                AND	co.hierarchylevel 	= :level ";

                    /* Execute  */
                    $rdo = $DB->get_record_sql($sql,$params);
                    if ($rdo) {
                        /* Manager  */
                        $infoManager->levelzero         = $rdo->levelzero;
                        $infoManager->levelone          = $rdo->levelone;
                        $infoManager->leveltwo          = $rdo->leveltwo;
                        $infoManager->levelthree        = null;

                        /* Reporter */
                        $infoReporter->levelzero         = $rdo->levelzero;
                        $infoReporter->levelone          = $rdo->levelone;
                        $infoReporter->leveltwo          = $rdo->leveltwo;
                        $infoReporter->levelthree        = null;
                    }//if_Rdo

                    break;
                case '3':
                    /* SQL Instruction  */
                    $sql = " SELECT	co.id 		as 'levelthree',
                                    co_two.id 	as 'leveltwo',
                                    co_one.id 	as 'levelone',
                                    co_zero.id 	as 'levelzero'
                             FROM	  {report_gen_companydata}	co
                                -- LEVEL TWO
                                JOIN  {report_gen_company_relation}	cr_two		ON 	cr_two.companyid		= co.id
                                JOIN  {report_gen_companydata}		co_two		ON  co_two.id				= cr_two.parentid
                                                                                    AND	co_two.hierarchylevel	= 2
                                -- LEVEL ONE
                                JOIN  {report_gen_company_relation}	cr_one 		ON 	cr_one.companyid 		= co_two.id
                                JOIN  {report_gen_companydata}		co_one		ON	co_one.id 				= cr_one.parentid
                                                                                    AND co_one.hierarchylevel 	= 1
                                -- LEVEL ZERO
                                JOIN  {report_gen_company_relation}	cr_zero		ON	cr_zero.companyid 		= co_one.id
                                JOIN  {report_gen_companydata}		co_zero		ON	co_zero.id				= cr_zero.parentid
                                                                                AND	co_zero.hierarchylevel 	= 0
                             WHERE	co.id 				= :company
                                AND	co.hierarchylevel 	= :level ";

                    /* Execute  */
                    $rdo = $DB->get_record_sql($sql,$params);
                    if ($rdo) {
                        /* Manager  */
                        $infoManager->levelzero         = $rdo->levelzero;
                        $infoManager->levelone          = $rdo->levelone;
                        $infoManager->leveltwo          = $rdo->leveltwo;
                        $infoManager->levelthree        = $rdo->levelthree;

                        /* Reporter */
                        $infoReporter->levelzero         = $rdo->levelzero;
                        $infoReporter->levelone          = $rdo->levelone;
                        $infoReporter->leveltwo          = $rdo->leveltwo;
                        $infoReporter->levelthree        = $rdo->levelthree;
                    }//if_rdo

                    break;
            }//switch

            return array($infoManager,$infoReporter);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfoManager

    /**
     * @param           $info
     * @param           $type
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    11/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user is manager/reporter for a specific level
     */
    private static function IsManagerReporter($info,$type) {
        /* Variables    */
        global $DB;
        $sql    = null;
        $rdo    = null;
        $params = null;
        $table  = null;
        $field  = null;

        try {
            /* Search Criteria  */
            $params = array();

            $params['level']    = $info->hierarchylevel;

            switch ($type) {
                case MANAGER:
                    $params['manager']  = $info->managerid;
                    $table = 'report_gen_company_manager';
                    $field = 'managerid';

                    break;
                case REPORTER:
                    $params['manager']  = $info->reporterid;
                    $table = 'report_gen_company_reporter';
                    $field = 'reporterid';

                    break;
            }//switch_type

            /* SQL Instruction  */
            $sql = " SELECT	ma.id
                     FROM	{". $table . "}	ma
                     WHERE	ma." . $field. " 	= :manager
                        AND	ma.hierarchylevel 	= :level ";

            switch ($info->hierarchylevel) {
                case '0':
                    /* Criteria */
                    $params['zero'] = $info->levelzero;

                    /* SQL */
                    $sql .= " AND ma.levelzero  = :zero
                              AND ma.levelone   IS NULL
                              AND ma.leveltwo   IS NULL
                              AND ma.levelthree IS NULL ";

                    break;
                case '1':
                    /* Criteria */
                    $params['zero'] = $info->levelzero;
                    $params['one']  = $info->levelone;

                    /* SQL */
                    $sql .= " AND ma.levelzero  = :zero
                              AND ma.levelone   = :one
                              AND ma.leveltwo   IS NULL
                              AND ma.levelthree IS NULL ";

                    break;
                case '2':
                    /* Criteria */
                    $params['zero'] = $info->levelzero;
                    $params['one']  = $info->levelone;
                    $params['two']  = $info->leveltwo;

                    /* SQL */
                    $sql .= " AND ma.levelzero  = :zero
                              AND ma.levelone   = :one
                              AND ma.leveltwo   = :two
                              AND ma.levelthree IS NULL ";

                    break;
                case '3':
                    /* Criteria */
                    $params['zero']     = $info->levelzero;
                    $params['one']      = $info->levelone;
                    $params['two']      = $info->leveltwo;
                    $params['three']    = $info->levelthree;

                    /* SQL */
                    $sql .= " AND ma.levelzero  = :zero
                              AND ma.levelone   = :one
                              AND ma.leveltwo   = :two
                              AND ma.levelthree = :three ";

                    break;
            }//switch_level

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->id;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsManagerReporter


    private static function ProcessUserCompetence($userCompetence) {
        /* Variables */
        global $DB;
        $time               = null;
        $myJobRoles         = null;
        $competenceId       = null;
        $infoCompetenceData = null;
        $infoCompetence     = null;
        $competenceData     = null;
        $user               = null;
        $rdo                = null;
        $params             = null;
        $sync               = null;
        $trans              = null;


        /* Begin Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time   */
            $time = time();

            /* Get data user */
            $user = $DB->get_record('user',array('username' => $userCompetence->personalNumber,'deleted' => '0'),'id');

            /* Check if user exists */
            if ($user) {
                /* Competence Reference */
                $rdo = $DB->get_record('user_info_competence',array('userid' => $user->id),'id');
                if ($rdo) {
                    $competenceId = $rdo->id;
                }else {
                    /* Competence */
                    $infoCompetence = new stdClass();
                    $infoCompetence->userid         = $user->id;
                    $infoCompetence->timemodified   = $time;

                    $competenceId = $DB->insert_record('user_info_competence',$infoCompetence);
                }//if_Rdo

                /* Extract Data */
                $infoCompetenceData = new stdClass();
                $infoCompetenceData->userid         = $user->id;
                $infoCompetenceData->competenceid   = $competenceId;
                $infoCompetenceData->companyid      = $userCompetence->company;
                $infoCompetenceData->level          = $userCompetence->level;
                $infoCompetenceData->jobroles       = $userCompetence->jobrole;
                $infoCompetenceData->editable       = 0;
                $infoCompetenceData->approved       = 1;
                $infoCompetenceData->rejected       = 0;
                $infoCompetenceData->timemodified   = $time;

                /* Checks if the competence data already exists */
                $params = array();
                $params['userid']       = $user->id;
                $params['competenceid'] = $rdo->id;
                $params['companyid']    = $userCompetence->company;
                $params['level']        = $userCompetence->level;

                /* Execute */
                $competenceData = $DB->get_record('user_info_competence_data',$params);

                /* Apply Action */
                switch ($userCompetence->action) {
                    case ADD_ACTION:
                    case UPDATE_ACTION:
                        if ($competenceData) {
                            /* Update */
                            /* Extract current job roles */
                            $myJobRoles = explode(',',$competenceData->jobroles);

                            if (!in_array($userCompetence->jobrole,$myJobRoles)) {
                                /* Add Job role */
                                $competenceData->jobroles .= ',' . $userCompetence->jobrole;
                                $competenceData->editable = 0;

                                /* Execute */
                                $DB->update_record('user_info_competence_data',$competenceData);

                            }//if_no_exist
                        }else {
                            /* Create New   */
                            $infoCompetenceData->id = $DB->insert_record('user_info_competence_data',$infoCompetenceData);
                        }

                        /* Synchronized */
                        $sync = true;

                        break;
                    case DELETE_ACTION:
                        /* Delete if exists */
                        if ($competenceData->jobroles) {
                            /* Extract current job roles */
                            $myJobRoles = explode(',',$competenceData->jobroles);
                            if (in_array($userCompetence->jobrole,$myJobRoles)) {
                                /* Delete job role from the competence */
                                $myJobRoles = array_flip($myJobRoles);
                                unset($myJobRoles[$userCompetence->jobrole]);
                                $myJobRoles = array_flip($myJobRoles);

                                $competenceData->jobroles = implode(',',$myJobRoles);

                                /* Execute */
                                $DB->update_record('user_info_competence_data',$competenceData);

                                /* Synchronized */
                                $sync = true;
                            }//if_exists
                        }//if_competenceData

                        break;
                }//switch
            }//if_user

            /* Commit */
            $trans->allow_commit();

            return $sync;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ProcessUserCompetence


    /**
     * @param           $userAccount
     *
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    29/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Process the user account to synchronize
     *
     * @updateDate      23/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add resource number
     *
     * @updateDate      05/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the gender
     */
    private static function ProcessUserAccount($userAccount) {
        /* Variables */
        global $DB,$CFG;
        $time       = null;
        $infoUser   = null;
        $sync       = null;
        $rdoUser    = null;
        $trans      = null;
        $userId     = null;

        /* Begin transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time */
            $time = time();

            /* Check if already exists the account */
            $rdoUser = get_complete_user_data('username',$userAccount->personalnumber);

            /* Extract user data */
            if (!$rdoUser) {
                $infoUser = new stdClass();
                $infoUser->username     = $userAccount->personalnumber;
                $infoUser->firstname    = $userAccount->firstname;
                $infoUser->lastname     = $userAccount->lastname;
                $infoUser->email        = $userAccount->email;
                $infoUser->timemodified = $time;
                $infoUser->timecreated  = $time;
                $infoUser->auth         = 'saml';
                $infoUser->password     = AUTH_PASSWORD_NOT_CACHED;
                $infoUser->confirmed    = '1';
                $infoUser->firstaccess  = $time;
                $infoUser->calendartype = $CFG->calendartype;
                $infoUser->mnethostid   = $CFG->mnet_localhost_id;
            }else {
                $userId = $rdoUser->id;
            }//if_not_info_user

            /* Apply Action */
            switch ($userAccount->action) {
                case ADD_ACTION:
                    if (!$rdoUser) {
                        /* Execute  */
                        $userId = $DB->insert_record('user',$infoUser);
                    }else {
                        $rdoUser->firstname    = $userAccount->firstname;
                        $rdoUser->lastname     = $userAccount->lastname;
                        $rdoUser->email        = $userAccount->email;
                        $rdoUser->timemodified = $time;
                        $rdoUser->deleted      = 0;

                        /* Execute */
                        $DB->update_record('user',$rdoUser);
                    }//if_notExist

                    /* Synchronized */
                    $sync = true;

                    break;
                case UPDATE_ACTION:
                    /* Update Data */
                    if ($rdoUser) {
                        $rdoUser->firstname    = $userAccount->firstname;
                        $rdoUser->lastname     = $userAccount->lastname;
                        $rdoUser->email        = $userAccount->email;
                        $rdoUser->timemodified = $time;
                        $rdoUser->deleted      = 0;

                        /* Execute */
                        $DB->update_record('user',$rdoUser);
                    }else {
                        /* Execute  */
                        $userId = $DB->insert_record('user',$infoUser);
                    }//if_infoUSer

                    /* Synchronized */
                    $sync = true;

                    break;
                case DELETE_ACTION:
                    /* Delete User  */
                    if ($rdoUser) {
                        $rdoUser->deleted      = 1;
                        $rdoUser->timemodified = $time;

                        /* Execute */
                        $DB->update_record('user',$rdoUser);
                    }else {
                        /* Execute  */
                        $infoUser->deleted      = 1;
                        $userId             = $DB->insert_record('user',$infoUser);
                    }//if_infoUsers

                    /* Synchronized */
                    $sync = true;

                    break;
            }//action

            /**
             * Create the connection between user and his/her resource number
             */
            /*
             * First. Check if already exist an entry for this user.
             */
            if ($userAccount->ressursnr) {
                $rdo = $DB->get_record('user_resource_number',array('userid' => $userId));
                if ($rdo) {
                    /* Update   */
                    $rdo->ressursnr     = $userAccount->ressursnr;
                    $rdo->industrycode  = $userAccount->industry;

                    /* Execute */
                    $DB->update_record('user_resource_number',$rdo);
                }else {
                    /* Insert   */
                    $instance = new stdClass();
                    $instance->userid       = $userId;
                    $instance->ressursnr    = $userAccount->ressursnr;
                    $instance->industrycode = $userAccount->industry;

                    /* Execute  */
                    $DB->insert_record('user_resource_number',$instance);
                }//if_rdo
            }//if_resource_number

            /**
             * Add the gender
             */
            if ($userAccount->action != DELETE_ACTION) {
                //Gender::Add_UserGender($userId,$userAccount->personalnumber);
            }

            /* Commit */
            $trans->allow_commit();

            return $sync;
        }catch (Exception $ex) {
            /* Log  */
            $dbLog = 'Error --> ' . $ex->getTraceAsString() . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ProcessUserAccount

    /**
     * @param           $jobRoleInfo
     *
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Process the job role to synchronize
     */
    private static function ProcessFSJobRoles($jobRoleInfo) {
        /* Variables */
        global $DB;
        $instanceJR     = null;
        $relationInfo   = null;
        $instanceJRRel  = null;
        $competencesJR  = null;
        $jobRoles       = null;
        $time           = null;
        $rdo            = null;
        $sync           = null;
        $trans          = null;

        /* Begin transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time */
            $time = time();

            /* Get Job Role */

            /* Extract Info Job Role    */
            $instanceJR = new stdClass();
            $instanceJR->name           = $jobRoleInfo->name;
            $instanceJR->industrycode   = $jobRoleInfo->industry;
            $instanceJR->modified       = $time;

            /* Apply Action */
            switch ($jobRoleInfo->action) {
            }//switch_action

            /* Commit */
            $trans->allow_commit();

            return $instanceJR->id;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ProcessFSJobRoles

    /**
     * @param           $jobRoleId
     * @param           $jobRoleInfo
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the job role relation exists
     */
    private static function ExistsJobRoleRelation($jobRoleId,$jobRoleInfo) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $params     = null;

        try {
            /* Search Criteria */
            $params = array();
            $params['jr'] = $jobRoleId;

            /* SQL Instruction */
            $sql = " SELECT rel.id
                     FROM   {report_gen_jobrole_relation} rel
                     WHERE  rel.jobroleid = :jr ";

            /* Add Criteria */
            if ($jobRoleInfo->levelZero && $jobRoleInfo->levelOne && $jobRoleInfo->levelTwo && $jobRoleInfo->levelThree) {
                /* Criteria */
                $params['zero'] = $jobRoleInfo->levelZero;
                $params['one']  = $jobRoleInfo->levelOne;
                $params['two']  = $jobRoleInfo->levelTwo;
                $params['tre']  = $jobRoleInfo->levelThree;

                $sql .= " AND rel.levelzero = :zero AND rel.levelone = :one AND rel.leveltwo = :two AND rel.levelthree = :tre ";
            }else if ($jobRoleInfo->levelZero && $jobRoleInfo->levelOne && $jobRoleInfo->levelTwo && !$jobRoleInfo->levelThree) {
                /* Criteria */
                $params['zero'] = $jobRoleInfo->levelZero;
                $params['one']  = $jobRoleInfo->levelOne;
                $params['two']  = $jobRoleInfo->levelTwo;

                $sql .= " AND rel.levelzero = :zero AND rel.levelone = :one AND rel.leveltwo = :two AND rel.levelthree IS NULL ";
            }else if ($jobRoleInfo->levelZero && $jobRoleInfo->levelOne && !$jobRoleInfo->levelTwo && !$jobRoleInfo->levelThree) {
                /* Criteria */
                $params['zero'] = $jobRoleInfo->levelZero;
                $params['one']  = $jobRoleInfo->levelOne;

                $sql .= " AND rel.levelzero = :zero AND rel.levelone = :one AND rel.leveltwo IS NULL AND rel.levelthree IS NULL ";
            }else if ($jobRoleInfo->levelZero && !$jobRoleInfo->levelOne && !$jobRoleInfo->levelTwo && !$jobRoleInfo->levelThree) {
                /* Criteria */
                $params['zero'] = $jobRoleInfo->levelZero;

                $sql .= " AND rel.levelzero = :zero AND rel.levelone IS NULL AND rel.leveltwo IS NULL AND rel.levelthree IS NULL ";
            }else {
                $sql .= " AND rel.levelzero IS NULL ";
            }//if_criteria

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->id;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ExistsJobRoleRelation

    /**
     * @param           $jobRoleId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all competence connected with the job role
     */
    private static function GetCompetencesJobRoles($jobRoleId) {
        /* Variables */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $competencesJR  = array();
        $info           = null;

        try {
            /* SQL Instruction */
            $sql = " SELECT	id,
                            jobroles
                     FROM	{user_info_competence_data}
                     WHERE	jobroles = '"       . $jobRoleId    . "'
                            OR
                            jobroles LIKE '"    . $jobRoleId    . ",%'
                            OR
                            jobroles LIKE '%,"  . $jobRoleId    . "'
                            OR
                            jobroles LIKE '%,"  . $jobRoleId    . ",%' ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach($rdo as $instance) {
                    /* Competence Info  */
                    $info = new stdClass();
                    $info->id       = $instance->id;
                    $info->jobroles = $instance->jobroles;

                    /* Add Competence */
                    $competencesJR[$instance->id] = $info;
                }//for_rdo
            }//if_Rdo

            return $competencesJR;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompetencesJobRoles

    /**
     * @param           $companyInfo
     *
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Process the company to synchronize
     *
     * @updateDate      06/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add invoice data
     */
    private static function ProcessFSCompany($companyInfo) {
        /* Variables */
        global $DB;
        $companyId          = null;
        $instanceCompany    = null;
        $instanceParent     = null;
        $time               = null;
        $rdo                = null;
        $rdoEmployee        = null;
        $sync               = null;
        $trans              = null;

        /* Begin transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local Time */
            $time = time();

            /* Check if already exists  */
            $rdo = $DB->get_record('report_gen_companydata',array('id' => $companyInfo->ksId));

            /* Extract Info Company  */
            $instanceCompany = new stdClass();
            $instanceCompany->name              = $companyInfo->name;
            $instanceCompany->industrycode      = $companyInfo->industry;
            $instanceCompany->hierarchylevel    = $companyInfo->level;
            $instanceCompany->public            = $companyInfo->public;
            $instanceCompany->ansvar            = $companyInfo->ansvar;
            $instanceCompany->tjeneste          = $companyInfo->tjeneste;
            $instanceCompany->adresse1          = $companyInfo->adresseOne;
            $instanceCompany->adresse2          = $companyInfo->adresseTwo;
            $instanceCompany->adresse3          = $companyInfo->adresseThree;
            $instanceCompany->postnr            = $companyInfo->postnr;
            $instanceCompany->poststed          = $companyInfo->poststed;
            $instanceCompany->epost             = $companyInfo->epost;

            /* Invoice Data */
            $instanceCompany->modified          = $time;

            /* Apply Action */
            switch ($companyInfo->action) {
                case ADD_ACTION:
                    if (!$rdo) {
                        /* Execute  */
                        $companyId = $DB->insert_record('report_gen_companydata',$instanceCompany);

                        /* Relation Parent  */
                        if ($companyInfo->parent) {
                            $instanceParent = new stdClass();
                            $instanceParent->companyid  = $companyId;
                            $instanceParent->parentid   = $companyInfo->parent;
                            $instanceParent->modified   = $time;

                            /* Execute  */
                            $DB->insert_record('report_gen_company_relation',$instanceParent);
                        }//if_parent
                    }//if_no_exists

                    break;
                case UPDATE_ACTION:
                    if (!$rdo) {
                        /* Execute  */
                        $companyId = $DB->insert_record('report_gen_companydata',$instanceCompany);
                    }else {
                        /* Execute  */
                        $companyId = $instanceCompany->id = $companyInfo->ksId;
                        $DB->update_record('report_gen_companydata',$instanceCompany);
                    }

                    /* Create Relation */
                    if ($companyInfo->parent) {
                        /* Check if Already Exists  */
                        $rdo = $DB->get_record('report_gen_company_relation',array('companyid' => $companyInfo->ksId,'parentid' => $companyInfo->parent),'id');
                        if (!$rdo) {
                            /* Create Relation */
                            $instanceParent = new stdClass();
                            $instanceParent->companyid  = $companyInfo->ksId;
                            $instanceParent->parentid   = $companyInfo->parent;
                            $instanceParent->modified   = $time;

                            /* Execute  */
                            $DB->insert_record('report_gen_company_relation',$instanceParent);
                        }//if_!rdo
                    }//if_parent

                    break;
                case DELETE_ACTION:
                    if ($rdo) {
                        /* Delete  Company */
                        /* Check there are none users connected with */
                        $rdoEmployee = $DB->get_records('user_info_competence_data',array('companyid' => $companyInfo->ksId));
                        if (!$rdoEmployee) {
                            $companyId = $rdo->id;
                            /* Delete Comapny   */
                            $DB->delete_records('report_gen_companydata',array('id' => $companyInfo->ksId));

                            /* Delete Relations */
                            $DB->delete_records('report_gen_company_relation',array('companyid' => $companyInfo->ksId));
                        }else {
                            $companyId = 0;
                        }//if_no_employees
                    }//if_exists

                    break;
            }//company_Action

            /* Commit */
            $trans->allow_commit();

            return $companyId;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//ProcessFSCompany


    /**
     * @param           $topCompany
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the organization structure for a specific top level.
     * In this case, top level is company.
     * Compatible with Lx version of Report manager
     */
    private static function Get_OrganizationStructureByTop($topCompany) {
        /* Variables */
        global $DB, $CFG;
        $sql                = null;
        $rdo                = null;
        $params             = null;
        $orgStructure       = array();
        $infoOrganization   = null;
        $maxLevel           = null;
        $i                  = null;
        $notIn              = null;
        $dbLog              = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START GET KS Organization Structure. ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Get the highest level of the hierarchy   */
            $maxLevel = self::GetMaxLevelOrganization();

            /* Search Criteria  */
            $params = array();
            $params['level']    = $topCompany->level;

            /* Not In Companies */
            $notIn = $topCompany->notIn;

            /* SQL Instruction */
            $sql = " SELECT	co.id,
                            co.name,
                            co.industrycode,
                            co.hierarchylevel
                     FROM	{report_gen_companydata}	co
                     WHERE	co.name like '%". $topCompany->company ."%'
                        AND	co.hierarchylevel	= :level ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Top Company */
                    $infoOrganization = new stdClass();
                    $infoOrganization->id           = $instance->id;
                    $infoOrganization->name         = $instance->name;
                    $infoOrganization->industrycode = $instance->industrycode;
                    $infoOrganization->level        = $instance->hierarchylevel;
                    $infoOrganization->parent       = 0;

                    /* Add Company */
                    $orgStructure[$instance->id] = $infoOrganization;
                }//for_Rdo

                /* Get the hierarchy */
                if ($maxLevel) {
                    $parents = implode(',',array_keys($orgStructure));
                    for($i=2;$i<=$maxLevel;$i++) {
                        /* Get Information About the rest hierarchy */
                        $parents = self::GetMyLevels($parents,$i,$orgStructure,$notIn);
                    }
                }//if_MaxLevel
            }//if_Rdo

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' FINISH GET KS Organization Structure. ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            return $orgStructure;
        }catch (Exception $ex) {
            $dbLog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH GET KS Organization Structure. ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//Get_OrganizationStructureByLevel

    /**
     * @param           $parents
     * @param           $level
     * @param           $orgStructure
     * @param           $notIn
     *
     * @return          int|string
     * @throws          Exception
     *
     * @creationDate    04/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get info of each memeber of the hierarchy
     */
    private static function GetMyLevels($parents,$level,&$orgStructure,$notIn) {
        /* Variables    */
        global $DB;
        $sql                = null;
        $rdo                = null;
        $companies          = array();
        $infoOrganization   = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['level'] = $level;

            /* SQL Instruction  */
            $sql = " SELECT	  co.id,
                              co.name,
                              co.industrycode,
                              co.hierarchylevel,
                              cr.parentid
                     FROM	  {report_gen_companydata}			co
                        JOIN  {report_gen_company_relation}		cr 	ON 	cr.companyid 	= co.id
                                                                    AND cr.parentid 	IN ($parents)
                     WHERE	co.hierarchylevel = :level
                        AND co.id NOT IN ($notIn) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Company */
                    $infoOrganization = new stdClass();
                    $infoOrganization->id           = $instance->id;
                    $infoOrganization->name         = $instance->name;
                    $infoOrganization->industrycode = $instance->industrycode;
                    $infoOrganization->level        = $instance->hierarchylevel;
                    $infoOrganization->parent       = $instance->parentid;

                    /* Add Company */
                    $orgStructure[$instance->id] = $infoOrganization;

                    $companies[$instance->id] = $instance->id;
                }//fpr_rdo

                return implode(',',$companies);
            }else {
                return 0;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetMyLevels


    /**
     * @param           $notIn
     *
     * @return          array
     *
     * @throws          Exception
     *
     * @creationDate    27/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get al generics job roles
     */
    private static function Get_GenericsJobRoles($notIn) {
        /* Variables */
        global $DB,$CFG;
        $sql            = null;
        $rdo            = null;
        $infoJobRole    = null;
        $jobRoles       = array();
        $dbLog          = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START KS Job Roles Generics . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* SQL Instruction */
            $sql = " SELECT   jr.id,
                              jr.name,
                              jr.industrycode
                     FROM	  {report_gen_jobrole} 			jr
                        JOIN  {report_gen_jobrole_relation}	jr_re 	ON  jr_re.jobroleid = jr.id
                                                                            AND (
                                                                                 jr_re.levelzero IS NULL
                                                                                 OR
                                                                                 jr_re.levelzero = 0
                                                                                )
                     WHERE jr.id NOT IN ($notIn) ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* JR Info */
                    $infoJobRole = new stdClass();
                    $infoJobRole->id            = $instance->id;
                    $infoJobRole->name          = $instance->name;
                    $infoJobRole->industryCode  = $instance->industrycode;

                    /* Add job role */
                    $jobRoles[$instance->id] = $infoJobRole;
                }//for_Rdo
            }//if_Rdo

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' FINISH KS Job Roles Generics . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            return $jobRoles;
        }catch (Exception $ex) {
            /* Log  */
            $dbLog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH KS Job Roles Generics . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//Get_GenericsJobRoles

    /**
     * @param           $top
     * @param           $notIn
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all job roles connected with a specific level zero
     */
    private static function Get_JobRolesByLevel($top,$notIn) {
        /* Variables */
        global $DB,$CFG;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $infoJobRole    = null;
        $jobRoles       = array();
        $dbLog          = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START KS Job Roles No Generics . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {

            /* SQL Instruction */
            $sql = " SELECT	jr.id,
                            jr.name,
                            jr.industrycode,
                            GROUP_CONCAT(DISTINCT jr_re.id 	ORDER BY jr_re.id 	SEPARATOR ',') as 'myrelations'
                     FROM		{report_gen_jobrole}			jr
                        JOIN	{report_gen_jobrole_relation}	jr_re 	ON  jr_re.jobroleid = jr.id
                                                                        AND jr_re.levelone IS NOT NULL
                                                                        AND jr_re.levelone IN ($top)
                     WHERE  jr.id NOT IN ($notIn)
                     GROUP BY jr.id ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            foreach ($rdo as $instance) {
                /* JR Info */
                $infoJobRole = new stdClass();
                $infoJobRole->id            = $instance->id;
                $infoJobRole->name          = $instance->name;
                $infoJobRole->industryCode  = $instance->industrycode;
                $infoJobRole->relation      = self::Get_JobRoleRelation($instance->myrelations);

                /* Add job role */
                $jobRoles[$instance->id] = $infoJobRole;
            }//for_rdo

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' FINISH KS Job Roles No Generics . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            return $jobRoles;
        }catch (Exception $ex) {
            /* Log  */
            $dbLog  = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH KS Job Roles No Generics . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//Get_JobRolesByLevel

    /**
     * @param           $myRelations
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    29/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all levels connected with the job role
     */
    private static function Get_JobRoleRelation($myRelations) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $relations      = array();
        $infoRelation   = null;

        try {

            /* SQL Instruction */
            $sql = " SELECT id,
                            levelzero,
                            levelone,
                            leveltwo,
                            levelthree
                     FROM	mdl_report_gen_jobrole_relation
                     WHERE	id IN ($myRelations) ";


            /* Execute */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Relation */
                    $infoRelation = new stdClass();
                    $infoRelation->levelZero    = $instance->levelzero;
                    $infoRelation->levelOne     = $instance->levelone;
                    $infoRelation->levelTwo     = $instance->leveltwo;
                    $infoRelation->levelThree   = $instance->levelthree;

                    /* Add relation */
                    $relations[$instance->id] = $infoRelation;
                }//for_Rdo_relations
            }//if_rdo

            return $relations;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRoleRelation

    /**
     * @return          null
     *
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the highest level of the organization
     */
    private static function GetMaxLevelOrganization() {
        /* Variables */
        global $DB;
        $sql = null;
        $rdo = null;

        try {
            /* SQL Instruction */
            $sql = " SELECT MAX(hierarchylevel) as 'max'
                     FROM 	{report_gen_companydata} ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                return $rdo->max;
            }else {
                return null;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetMaxLevelOrganization


}//class_WS_FELLESDATA