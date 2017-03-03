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
define('MAPPED_TARDIS','TARDIS');

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
    public static function generics_jobroles($notIn,&$result) {
        /* Variables    */

        try {
            /* Get generics job roles */
            $result['jobroles'] = self::get_generics_jobroles($notIn['notIn']);
        }catch (Exception $ex) {
            $result['error']    = 409;
            $result['message']  = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//generics_jobroles

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
    public static function jobroles_by_level($hierarchy,&$result) {
        /* Variables */

        try {
            /* Job Roles by Level */
            $result['jobroles'] = self::get_jobroles_by_level($hierarchy['top'],$hierarchy['notIn']);
        }catch (Exception $ex) {
            $result['error']    = 409;
            $result['message']  = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//jobroles_by_level

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
    public static function organization_structure_by_top($top,&$result) {
        /* Variables */
        $infoTop = null;

        try {
            /* Convert to object    */
            $infoTop = (Object)$top;

            /* Get Organization Structure*/
            $result['structure'] = self::get_organization_structure_by_top($infoTop);
        }catch (Exception $ex) {
            $result['error']    = 409;
            $result['message']  = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//organization_structure_by_top


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
    public static function synchronize_fsks_companies($companiesFS,&$result) {
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
                $companyId = self::process_fs_company($objCompany);

                /* Marked s imported    */
                if ($companyId) {
                    $infoImported = new stdClass();
                    $infoImported->fsId     = $objCompany->fsId;
                    $infoImported->ksId     = $companyId;
                    $infoImported->imported = 1;
                    $infoImported->key      = $objCompany->fsId;

                    $imported[$objCompany->fsId] = $infoImported;
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
    }//synchronize_fsks_companies

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
    public static function synchronize_fsks_jobroles($jobRolesFS,&$result) {
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
                $jobRoleId = self::process_fs_jobroles($objJobRole);

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
    }//synchronize_fsks_jobroles

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
    public static function synchronize_users_accounts($usersAccounts,&$result) {
        /* Variables    */
        global $CFG;
        $dir            = null;
        $pathFile       = null;
        $userId         = null;
        $imported       = array();
        $infoImported   = null;
        $infoAccount    = null;
        $dbLog          = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization Users Accoutns . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        
        try {
            /* Save Data Temporary */
            $dir = $CFG->dataroot . '/fellesdata';
            if (!file_exists($dir)) {
                mkdir($dir);
            }
            /* Clean all response   */
            $pathFile = $dir . '/wsUsersAccounts.txt';

            /* Create a new response file */
            /* Clean Old Data   */
            unlink($pathFile);
            $responseFile = fopen($pathFile,'w');
            fwrite($responseFile,$usersAccounts);
            fclose($responseFile);
            
            /* Read Content */
            if (file_exists($pathFile)) {
                /* Get Content */
                $data = file($pathFile);

                /* Synchronization between FS and KS. Users accounts */
                foreach($data as $key=>$line) {
                    if ($line) {
                        $infoAccount = json_decode($line);
                        
                        /* Process Account */
                        $userId = self::process_user_account($infoAccount);

                        /* Marked as imported */
                        if($userId) {
                            $infoImported = new stdClass();
                            $infoImported->personalnumber   = $infoAccount->personalnumber;
                            $infoImported->imported         = 1;
                            $infoImported->key              = $infoAccount->id;

                            $imported[$infoAccount->id]     = $infoImported;
                        }//if_userid
                        
                        $result['usersAccounts'] = $imported;
                    }//if_line
                }//for_line_File
                
                /* Log  */
                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization Users Accoutns . ' . "\n"."\n";
                error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
            }//if_exists
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();
            $result['usersAccounts']    = $imported;

            $dbLog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). 'FINISH Synchronization Users Accoutns . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
            
            throw $ex;
        }//try_catch
    }//synchronize_users_accounts

    /**
     * @param           $userManagerReporter
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      03/03/2017
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize managers reporters from fellesdata
     */
    public static function synchronize_user_manager_reporter($userManagerReporter,&$result) {
        /* Variables */
        global $CFG;
        $dblog              = null;
        $dir                = null;
        $path               = null;
        $lstmanagers        = null;
        $data               = null;
        $key                = null;
        $info               = null;
        $synchronized       = null;
        $infoImported       = null;
        $imported           = array();


        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization User Manager Reporter  . ' . "\n";

            // Save file
            $dir = $CFG->dataroot . '/fellesdata';
            if (!file_exists($dir)) {
                mkdir($dir);
            }

            // File
            $path = $dir . '/wsManagersReporters.txt';

            // Clean old data
            if (file_exists($path)) {
                unlink($path);
            }

            // Save new data
            $file = fopen($path,'w');
            fwrite($file,$userManagerReporter);
            fclose($file);

            // Process Content
            if (file_exists($path)) {
                // Get content
                $data        = file_get_contents($path);
                $lstmanagers = json_decode($data);

                // Synchronization
                foreach($lstmanagers as $key=>$info) {
                    // Process manager
                    $synchronized = self::process_user_manager_reporter($info);

                    // MArk as imported
                    if ($synchronized) {
                        $infoImported = new stdClass();
                        $infoImported->personalNumber   = $info->personalnumber;
                        $infoImported->imported         = 1;
                        $infoImported->key              = $info->key;

                        $imported[$info->key] = $infoImported;
                    }//if_competenceData
                }//for_managers
            }//file_exists

            $result['managerReporter'] = $imported;

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish Synchronization User Manager Reporter  . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Synchronization User Manager Reporter  . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            $result['error']            = 409;
            $result['message']          = $ex->getMessage();
            $result['managerReporter']  = $imported;

            throw $ex;
        }//try_catch
    }//synchronize_user_manager_reporter

    /**
     * Description
     * Synchronize user competence between FS and KS
     *
     * @param           String $competence
     * @param           array  $result
     *
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      28/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function synchronize_user_competence($competence,&$result) {
        /* Variables */
        global $CFG;
        $data           = null;
        $dir            = null;
        $path           = null;
        $filecompetence = null;
        $imported       = array();
        $infoimported   = null;
        $infocompetence = null;
        $competenceid   = null;
        $dblog          = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization User Competence. ' . "\n";

            // Save file
            $dir = $CFG->dataroot . '/fellesdata';
            if (!file_exists($dir)) {
                mkdir($dir);
            }

            // File
            $path = $dir . '/wsUserCompetence.txt';

            // Clean old data
            if (file_exists($path)) {
                unlink($path);
            }//if_file_exst

            // Save new data
            $filecompetence = fopen($path,'w');
            fwrite($filecompetence,$competence);
            fclose($filecompetence);

            // Process content
            if (file_exists($path)) {
                // Get content
                $data   = file_get_contents($path);
                $mydata = json_decode($data);

                // Synchronization
                foreach($mydata as $key=>$infocompetence) {
                    // Process competence
                    $competenceid = self::process_user_competence($infocompetence);

                    // Marked as imported
                    if ($competenceid) {
                        $infoimported = new stdClass();
                        $infoimported->personalNumber   = $infocompetence->personalnumber;
                        $infoimported->imported         = 1;
                        $infoimported->key              = $infocompetence->key;

                        $imported[$infocompetence->key] = $infoimported;
                    }//if_competenceDataID
                }//for_line_File

                if ($imported) {
                    $result['usersCompetence'] = $imported;
                }//if_imported
            }//if_file_exists

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization User Competence. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog = $ex->getMessage() . "\n\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Synchronization User Competence. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            $result['error']            = 409;
            $result['message']          = $ex->getMessage();
            $result['usersCompetence']  = $imported;

            throw $ex;
        }//try_catch
    }//synchronize_user_competence


    /**
     * @param           $userMail
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    27/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if it's a fake email
     */
    public static function IsFakeMail($userMail) {
        /* Variables    */
        $index = null;

        try {
            $index   = strpos($userMail,'@byttmegut.no');
            if ($index) {
                return true;
            }else  {
                return false;
            }//if
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsFakeMail

    /**
     * @param           $toUnMap
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    24/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unmap companies
     */
    public static function unmap_companies($toUnMap,&$result) {
        /* Variables */
        global $DB,$CFG;
        $trans          = null;
        $unmapped       = null;
        $orgUnMapped    = array();
        $info           = null;
        $infoOrg        = null;
        $objOrg         = null;
        $dbLog          = null;

        // Begin transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Un-Map companies . ' . "\n";

            // unmap company --> delete company
            if ($toUnMap) {
                foreach ($toUnMap as $infoOrg) {
                    // Convert to object
                    $objOrg = (Object)$infoOrg;

                    // Delete from report_gen_companydata
                    $unmapped = $DB->delete_records('report_gen_companydata',array('id' => $objOrg->kscompany));
                    // Delete from mdl_report_gen_company_relation
                    $DB->delete_records('report_gen_company_relation',array('companyid' => $objOrg->kscompany));

                    if ($unmapped) {
                        $info = new stdClass();
                        $info->unmapped     = true;
                        $info->key          = $objOrg->id;

                        /* Add */
                        $orgUnMapped[$objOrg->id] = $info;
                    }//if_unmapped
                }//for_toUnMap
            }//if_toUnMap

            /* Add result   */
            $result['orgUnMapped']  = $orgUnMapped;

            /* Log  */
            $dbLog .= ' FINISH Un-Map companies . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            // Commmit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            // Error
            $result['error']        = 409;
            $result['message']      = $ex->getMessage();
            $result['orgUnMapped']  = $orgUnMapped;

            $dbLog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). 'FINISH ERROR Un-Map companies . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_Catch
    }//unmap_companies

    /**
     * @param           $toUnMap
     * @param           $result
     *
     * @throws          Exception
     *
     * @creationDate    24/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unmap user competence
     */
    public static function unmap_user_competence($toUnMap,&$result) {
        /* Variables */
        global $CFG;
        $dbLog          = null;
        $usersUnMapped  = array();
        $objCompetence  = null;
        $info           = null;

        try {
            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Un-Map user competence . ' . "\n";

            // Unmap competence --> delete competence
            if ($toUnMap) {
                foreach ($toUnMap as $infoCompetence) {
                    // Convert to object
                    $objCompetence = (Object)$infoCompetence;

                    // Unmap
                    $info = self::process_unmap_competence_user($objCompetence);
                    if ($info) {
                        // Add
                        $usersUnMapped[$objCompetence->key] = $info;
                    }//if_info
                }//for_toUnMap 
            }//if_toUnmape

            // Add result
            $result['usersUnMapped']    = $usersUnMapped;

            // Log
            $dbLog .= ' FINISH Un-Map user competence . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();
            $result['usersUnMapped']    = $usersUnMapped;

            $dbLog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). 'FINISH ERROR Un-Map user competence . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//unmap_user_competence


    /**
     * Description
     * Get competence data for all users in a string
     *
     * @param             $industry
     * @param       array $result
     *
     * @throws            Exception
     *
     * @creationDate    24/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function competence_data($industry,&$result) {
        /* Variables */
        global $CFG;
        $dblog = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START GET COMPETENCE DATA . ' . "\n";
            
            // get competence data
            $result['competence'] = self::get_competence_data($industry);

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH GET COMPETENCE DATA . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();

            // Log
            $dblog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). 'FINISH ERROR GET COMPETENCE DATA . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//comptence_data

    /**
     * Description
     * Delete competence data
     * 
     * @param           array $competence
     * @param           array $result
     *
     * @throws                Exception
     *
     * @creationDate        28/02/2017
     * @author              eFaktor     (fbv)
     */
    public static function delete_competence_data($competence,&$result) {
        /* Variables */
        global $CFG;
        global $DB;
        $dblog      = null;
        $instance   = null;
        $info       = null;
        $keys       = null;
        
        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START DELETE COMPETENCE DATA . ' . "\n";

           // Delete competence
            if ($competence) {
                foreach ($competence as $info) {
                    // Convert to object
                    $instance = (Object)$info;

                    // Delete competence
                    $sql = " DELETE 
                             FROM   {user_info_competence_data} 
                             WHERE  userid = :user 
                                AND companyid IN ($instance->companies) ";

                    $rdo = $DB->execute($sql,array('user' => $instance->user));
                    if ($rdo) {
                        if ($keys) {
                            $keys .= ',' . $instance->keys;
                        }else {
                            $keys = $instance->keys;
                        }
                    }//if_rdo
                }//for_competence
            }//if_competence

            $result['deleted'] = $keys;

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH DELETE COMPETENCE DATA . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();

            // Log
            $dblog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). 'FINISH ERROR DELETE COMPETENCE DATA . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//delete_competence_data

    /**
     * Description
     * Get managers/reporters
     *
     * @param           String $industry
     * @param           String $result
     *
     * @throws          Exception
     *
     * @creationDate    01/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function managers_reporters($industry,&$result) {
        /* Variables */
        global $CFG;
        $dblog = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START GET Managers Reporters. ' . "\n";

            // Get managers
            $result['managers']     = self::get_managers_reporters_ks($industry,MANAGER);
            // Get reporters
            $result['reporters']    = self::get_managers_reporters_ks($industry,REPORTER);

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH GET Managers Reporters . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();

            // Log
            $dblog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). 'FINISH ERROR Get Managers Reporters . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//managers_reporters

    /**
     * Description
     * Clean managers/reporters
     * 
     * @param       array   $data
     * @param       String  $type
     * @param               $result
     * 
     * @throws      Exception
     * 
     * @creationDate    02/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function clean_managers_reporters($data,$type,&$result) {
        /* Variables */
        global $DB;
        global $CFG;
        $dblog      = null;
        $rdo        = null;
        $params     = null;
        $table      = null;
        $field      = null;
        $deleted    = null;
        
        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Delete Managers Reporters (Status). ' . "\n";

            // Select table
            switch ($type) {
                case MANAGER:
                    $table = 'report_gen_company_manager';
                    $field = 'managerid';
                    break;
                case REPORTER:
                    $table = 'report_gen_company_reporter';
                    $field = 'reporterid';

                    break;
            }//switch

            // Delete records
            $params = array();
            foreach ($data as $instance) {
                $params['id']   = $instance->key;
                $params[$field] = $instance->user;
                
                $DB->delete_records($table,$params);
                
                if ($deleted) {
                    $deleted .= ',' . $instance->key;
                }else {
                    $deleted = $instance->key;
                }//if_deleted
            }//for_data

            $result['deleted'] = $deleted;
            
            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Delete Managers Reporters (Status). ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();

            // Log
            $dblog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). 'FINISH Delete Managers Reporters (Status) . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//clean_managers_reporters

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Get managers/reporters from KS
     *
     * @param       String $industry
     * @param       String $type
     *
     * @return      null|string
     * @throws      Exception
     *
     * @creationDate    01/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_managers_reporters_ks($industry,$type) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        $data       = null;
        $table      = null;
        $field      = null;

        try {
            // Search criteria
            $params = array();
            $params['industry2'] = $industry;
            $params['mapped2']   = 'TARDIS';
            $params['industry3'] = $industry;
            $params['mapped3']   = 'TARDIS';

            // Select table
            switch ($type) {
                case MANAGER:
                    $table = 'report_gen_company_manager';
                    $field = 're.managerid';

                    break;
                case REPORTER:
                    $table = 'report_gen_company_reporter';
                    $field = 're.reporterid';

                    break;
            }//switch_Type

            // SQL Instruction
            $sql = " SELECT       re.id,
                                  $field as 'userid',
                                  u.username,
                                  re.leveltwo,
                                  re.levelthree
                     FROM		  {" .$table . "}	re
                        JOIN	  {user}						u		ON u.id 				= $field
                        -- Level Two
                        JOIN 	  {report_gen_companydata}		co_two 	ON 	co_two.id 			= re.leveltwo
                                                                        AND	co_two.mapped 		= :mapped2
                                                                        AND co_two.industrycode = :industry2
                        -- Level Three
                        LEFT JOIN	{report_gen_companydata}	co		ON 	co.id 			    = re.levelthree
                                                                        AND co.mapped 		    = :mapped3
                                                                        AND co.industrycode     = :industry3 ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $data = json_encode($rdo);
            }//if_Rdo

            return $data;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_managers_ks

    /**
     * Description
     * Get competence data in a string
     *
     * @param       $industry
     *
     * @return      null|string
     * @throws      Exception
     *
     * @creationDate    24/02/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_competence_data($industry) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        $competence = null;

        try {
            // Search criteria
            $params = array();
            $params['industry'] = $industry;
            $params['mapped']   = 'TARDIS';

            // SQL Isntruction
            $sql = " SELECT   uic.id,
                              uic.userid,
                              u.username,
                              uic.companyid,
                              uic.level,
                              uic.jobroles
                     FROM	  {user_info_competence_data}	uic
                        JOIN  {report_gen_companydata}		co 	ON  co.id 			= uic.companyid
                                                                AND co.mapped 		= :mapped
                                                                AND	co.industrycode	= :industry
                        JOIN  {user}						u 	ON 	u.id 			= uic.userid ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $competence =  json_encode($rdo);
            }//if_Rdo

            return $competence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//clean_competence_user

    /**
     * @param           $infoCompetence
     *
     * @return          null|stdClass
     * @throws          Exception
     * @throws          dml_transaction_exception
     *
     * @creationDate    24/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * unmap competence for a specific user
     */
    private static function process_unmap_competence_user($infoCompetence) {
        /* Variables */
        global $DB;
        $trans          = null;
        $unmapped       = null;
        $params         = null;
        $paramsDel      = null;
        $infoUser       = null;
        $infoUnMap      = null;

        /* Start transaccrion */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Get Info User */
            $params = array();
            $params['username'] = $infoCompetence->personalnumber;
            /* Execute */
            $infoUser   = $DB->get_record('user',$params,'id');

            /* Unmap */
            if ($infoUser) {
                /* Criteria */
                $paramsDel = array();
                $paramsDel['userid']    = $infoUser->id;
                $paramsDel['companyid'] = $infoCompetence->companyid;

                /* Execute */
                $unmapped = $DB->delete_records('user_info_competence_data',$paramsDel);
                if ($unmapped) {
                    $infoUnMap = new stdClass();
                    $infoUnMap->unmapped    = true;
                    $infoUnMap->key         = $infoCompetence->key;

                    /**
                     * Check if there are more records connected with the user
                     */
                    unset($paramsDel['companyid']);
                    $rdo = $DB->get_records('user_info_competence_data',$paramsDel);
                    if (!$rdo) {
                        /* Delete entry from the table mdl_user_info_competence */
                        $DB->delete_records('user_info_competence',$paramsDel);
                    }//if_Rdo
                }//if_unmmaped
            }//if_infoUSer
            

            /* Commit */
            $trans->allow_commit();

            return $infoUnMap;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//process_unmap_competence_user

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
    private static function process_user_manager_reporter($managerReporter) {
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
            $user = $DB->get_record('user',array('username' => $managerReporter->personalnumber,'deleted' => '0'),'id');

            /* Check If Exist */
            if ($user) {
                /* Manager && Reporter  */
                if (($managerReporter->prioritet == 1) ||
                    ($managerReporter->prioritet == 2)) {
                    $manager  = 1;
                    $reporter = 1;
                }//Manager&&Reporter
                /* Reporter */
                if (($managerReporter->prioritet != 1) &&
                    ($managerReporter->prioritet != 2)) {
                    $reporter  = 1;
                }//Manager&&Reporter

                /* Get Info Manager */
                list($infoManager,$infoReporter) = self::get_info_manager($managerReporter->ksid,$managerReporter->level,$user->id);

                /* Apply Action */
                switch ($managerReporter->action) {
                    case ADD_ACTION:

                        /* Add the user as manager if it's the case */
                        if ($manager) {
                            /* Check if the user is already manager or not */
                            $IsManager = self::is_manager_reporter($infoManager,MANAGER);
                            if (!$IsManager) {
                                /* Create   */
                                $DB->insert_record('report_gen_company_manager',$infoManager);
                            }//if_manager
                        }else if($reporter) {
                            /* Check if the user is already reporter or not */
                            $IsReporter = self::is_manager_reporter($infoReporter,REPORTER);
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
                            $IsManager = self::is_manager_reporter($infoManager,MANAGER);
                            if (!$IsManager) {
                                /* Create   */
                                $DB->insert_record('report_gen_company_manager',$infoManager);
                            }//if_manager
                        }else if ($reporter) {
                            /* Check if the user is already reporter or not */
                            $IsReporter = self::is_manager_reporter($infoReporter,REPORTER);
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
                            $IsManager = self::is_manager_reporter($infoManager,MANAGER);
                            if ($IsManager) {
                                $DB->delete_records('report_gen_company_manager',array('id' => $IsManager));
                            }//if_Manager
                        }else if ($reporter) {
                            /* Delete From Reporter */
                            $IsReporter = self::is_manager_reporter($infoReporter,REPORTER);
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
    }//process_user_manager_reporter

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
    private static function get_info_manager($company,$level,$userId) {
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
    }//get_info_manager

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
    private static function is_manager_reporter($info,$type) {
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
    }//is_manager_reporter


    private static function process_user_competence($userCompetence) {
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
            $user = $DB->get_record('user',array('username' => $userCompetence->personalnumber,'deleted' => '0'),'id');

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
    }//process_user_competence


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
    private static function process_user_account($userAccount) {
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
            $rdoUser = $DB->get_record('user',array('username' => $userAccount->personalnumber));

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
                $infoUser->lang         = 'no';
            }else {
                $userId = $rdoUser->id;
                $infoUser->lang = 'no';
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
                        $rdoUser->email        = self::process_right_email($rdoUser->email,$userAccount->email);
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
                        $rdoUser->email        = self::process_right_email($rdoUser->email,$userAccount->email);
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
                        /* Delete his/her connection with the municipality */
                        self::remove_connection_municipality($rdoUser->id,$userAccount->industry);
                    }else {
                        /* Execute  */
                        //$infoUser->deleted  = 1;
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
                if (is_numeric($userAccount->personalnumber) && ($userAccount->personalnumber) == 11) {
                    Gender::Add_UserGender($userId,$userAccount->personalnumber);
                }
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
    }//process_user_account

    /**
     * @param           $userId
     * @param           $industryCode
     *
     * @throws          Exception
     * @throws          dml_transaction_exception
     *
     * @creationDate    26/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * For users with delete action, their connection with municipality has to be removed
     */
    private static function remove_connection_municipality($userId,$industryCode) {
        /* Variables */
        global $DB;
        $sql        = null;
        $sqlMng     = null;
        $sqlRpt     = null;
        $sqlSuper   = null;
        $rdo        = null;
        $params     = null;
        $trans      = null;

        /* Start transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /**
             * Search Criteria
             */
            $params = array();
            $params['userid']   = $userId;
            $params['industry'] = $industryCode;

            /**
             * SQL instruction
             */
            $sql = " SELECT		icd.id
                     FROM		{user_info_competence_data}	icd
                        JOIN	{report_gen_companydata}	co 	ON 	co.id 			= icd.companyid
                                                                AND co.industrycode = :industry
                     WHERE icd.userid = :userid ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $DB->delete_records('user_info_competence_data',array('id' => $instance->id));
                }
            }//if_rdo_competence

            /**
             * Delete entry from mdl_user_info_competence
             */
            $rdo = $DB->get_records('user_info_competence_data',array('userid' => $userId));
            if (!$rdo) {
                $DB->delete_records('user_info_competence',array('userid' => $userId));
            }//if_rdo

            /**
             * Delete from managers
             */
            $sqlMng = " SELECT DISTINCT ma.id
                        FROM		{report_gen_company_manager} 	ma
                            JOIN	{report_gen_companydata}		co 	ON 	(co.id 			= ma.levelone
                                                                             OR
                                                                             co.id			= ma.leveltwo
                                                                             OR
                                                                             co.id			= ma.levelthree)
                                                                        AND co.industrycode = :industry
                        WHERE ma.managerid = :userid ";

            /* Execute */
            $rdo = $DB->get_records_sql($sqlMng,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $DB->delete_records('report_gen_company_manager',array('id' => $instance->id));
                }
            }//if_rdo_managers

            /**
             * Delete from reporters
             */
            $sqlRpt = " SELECT DISTINCT re.id
                        FROM		{report_gen_company_reporter} re
                            JOIN	{report_gen_companydata}	  co ON (co.id 			= re.levelone
                                                                         OR
                                                                         co.id			= re.leveltwo
                                                                         OR
                                                                         co.id			= re.levelthree)
                                                                     AND co.industrycode = :industry
                        WHERE re.reporterid = :userid ";
            /* Execute */
            $rdo = $DB->get_records_sql($sqlRpt,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $DB->delete_records('report_gen_company_reporter',array('id' => $instance->id));
                }
            }//if_rdo_reporters

            /**
             * Delete from super users
             */
            $sqlSuper = " SELECT DISTINCT	su.id
                          FROM		{report_gen_super_user}	  su
                            JOIN	{report_gen_companydata}  co ON (co.id 			= su.levelone
                                                                     OR
                                                                     co.id			= su.leveltwo
                                                                     OR
                                                                     co.id			= su.levelthree)
                                                                 AND co.industrycode = :industry
                          WHERE su.userid = :userid ";
            /* Execute */
            $rdo = $DB->get_records_sql($sqlSuper,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $DB->delete_records('report_gen_super_user',array('id' => $instance->id));
                }
            }//if_rdo_super_users

            /* Commit */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//remove_connection_municipality

    /**
     * @param           $userEmail
     * @param           $newEmail
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    27/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the right email to update
     */
    private static function process_right_email($userEmail,$newEmail) {
        /* Variables */
        $rightEmail    = null;
        $indexUser     = null;
        $indexNew      = null;

        try {
            /**
             * Case 1.
             * user Email and new Email are exactly the same.
             */
            if ($userEmail == $newEmail) {
                $rightEmail = $newEmail;
            }else {
                /* user Email is fake?  */
                $indexUser  = strpos($userEmail,'@byttmegut.no');
                /* new Email is fake?   */
                $indexNew   = strpos($newEmail,'@byttmegut.no');

                /**
                 * Case 2.
                 * user Email fake and new Email not fake
                 */
                if ($indexUser && !$indexNew) {
                    $rightEmail = $newEmail;
                }else if (!$indexUser && $indexNew) {
                    /**
                     * Case 3.
                     * user Email not fake and new Email fake
                     */
                    $rightEmail = $userEmail;
                }else if (!$indexNew && !$indexUser){
                    /**
                     * Case 4.
                     * user Email and new Email not fake
                     */
                    $rightEmail = $newEmail;
                }else {
                    $rightEmail = $newEmail;
                }
            }//if_user_email

            return $rightEmail;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//process_right_email


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
    private static function process_fs_jobroles($jobRoleInfo) {
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
    }//process_fs_jobroles

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
    private static function process_fs_company($companyInfo) {
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
            // Invoice data
            if ($companyInfo->ansvar) {
                $instanceCompany->ansvar        = $companyInfo->ansvar;
            }//if_ansvar
            if ($companyInfo->tjeneste) {
                $instanceCompany->tjeneste      = $companyInfo->tjeneste;
            }//if_tjeneste
            if ($companyInfo->adresseOne) {
                $instanceCompany->adresse1      = $companyInfo->adresseOne;
            }//if_adresseOne
            if ($companyInfo->adresseTwo) {
                $instanceCompany->adresse2      = $companyInfo->adresseTwo;
            }//if_adresseTwo
            if ($companyInfo->adresseThree) {
                $instanceCompany->adresse3      = $companyInfo->adresseThree;
            }//if_adresseThree
            if ($companyInfo->postnr) {
                $instanceCompany->postnr        = $companyInfo->postnr;
            }//if_postnr
            if ($companyInfo->poststed) {
                $instanceCompany->poststed      = $companyInfo->poststed;
            }//if_poststed
            if ($companyInfo->epost) {
                $instanceCompany->epost         = $companyInfo->epost;
            }//if_epost

            $instanceCompany->mapped            = MAPPED_TARDIS;

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
                    }else {
                        /* Execute  */
                        $companyId           = $rdo->id;
                        $instanceCompany->id = $rdo->id;
                        $DB->update_record('report_gen_companydata',$instanceCompany);
                    }//if_no_exists

                    break;
                case UPDATE_ACTION:
                    if (!$rdo) {
                        /* Execute  */
                        $companyId = $DB->insert_record('report_gen_companydata',$instanceCompany);
                    }else {
                        /* Execute  */
                        $companyId           = $rdo->id;
                        $instanceCompany->id = $rdo->id;
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
                        $companyId = $rdo->id;
                        /* Delete  Company */
                        $DB->delete_records('report_gen_companydata',array('id' => $companyInfo->ksId));

                        /* Delete Relations */
                        $DB->delete_records('report_gen_company_relation',array('companyid' => $companyInfo->ksId));

                        // Delete user_info_competence_data
                        $DB->delete_records('user_info_competence_data',array('companyid' => $companyInfo->ksId));

                        // Delete report_managers
                        $DB->delete_records('report_gen_company_manager',array('levelthree' => $companyInfo->ksId));

                        // Delete report_reporters
                        $DB->delete_records('report_gen_company_reporter',array('levelthree' => $companyInfo->ksId));

                        // Delete report_super_user
                        $DB->delete_records('report_gen_super_user',array('levelthree' => $companyInfo->ksId));

                        // Job roles
                        $rdoJR = $DB->get_records('report_gen_jobrole_relation',array('levelthree' => $companyInfo->ksId));
                        if ($rdoJR) {
                            foreach ($rdoJR as $instance) {
                                // Delete job role connected
                                $jr = $instance->jobroleid;
                                $DB->delete_records('report_gen_jobrole_relation',$instance);
                                // If there is not any record more, then add as generic
                                $rdoAux = $DB->get_record('report_gen_jobrole_relation',array('jobroleid' => $jr));
                                if (!$rdoAux) {
                                    $generic = new stdClass();
                                    $generic->jobroleid = $jr;

                                    // Execute
                                    $DB->insert_record('report_gen_jobrole_relation',$generic);
                                }//if_aux
                            }//for_rdo
                        }//if_rdo_jobrole
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
    }//process_fs_company


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
    private static function get_organization_structure_by_top($topCompany) {
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
            $maxLevel = self::get_max_level_organization();

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
                        $parents = self::get_my_levels($parents,$i,$orgStructure,$notIn);
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
    }//get_organization_structure_by_top

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
    private static function get_my_levels($parents,$level,&$orgStructure,$notIn) {
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
    }//get_my_levels


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
    private static function get_generics_jobroles($notIn) {
        /* Variables */
        global $DB,$CFG;
        $sql            = null;
        $rdo            = null;
        $infoJobRole    = null;
        $jobRoles       = array();
        $dbLog          = null;

        // Log
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
    }//get_generics_jobroles

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
    private static function get_jobroles_by_level($top,$notIn) {
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
                $infoJobRole->relation      = self::get_jobrole_relation($instance->myrelations);

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
    }//get_jobroles_by_level

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
    private static function get_jobrole_relation($myRelations) {
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
    }//get_jobrole_relation

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
    private static function get_max_level_organization() {
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
    }//get_max_level_organization


}//class_WS_FELLESDATA