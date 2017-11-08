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
define('STATUS_ACTION',3);

define('MANAGER','manager');
define('REPORTER','reporter');
define('MAPPED_TARDIS','TARDIS');

class WS_FELLESDATA {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Write fellesdata log
     *
     * @param           $log
     *
     * @throws          Exception
     *
     * @creationDate    16/10/2017
     * @author          eFaktor     (fbv)
     */
    public static function write_fellesdata_log($log) {
        /* Variables */
        global $DB;
        $info   = null;
        $time   = null;
        try {
            // Local time
            $time = time();

            // Write log
            if ($log) {
                asort($log);
                foreach ($log as $info) {
                    $info->timecreated = $time;
                    $DB->insert_record('fs_fellesdata_log',$info);
                }//for_log
            }//if_log
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//write_fellesdata_log


    /**
     * @param           $notIn
     * @param           $result
     * @param           $log
     *
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get generics job roles
     */
    public static function generics_jobroles($notIn,&$result,&$log) {
        /* Variables    */

        try {
            // Generecis job roles
            $result['jobroles'] = self::get_generics_jobroles($notIn['notIn'],$log);
        }catch (Exception $ex) {
            $result['error']    = 409;
            $result['message']  = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//generics_jobroles

    /**
     * @param           $hierarchy
     * @param           $result
     * @param           $log
     *
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get job roles by level
     */
    public static function jobroles_by_level($hierarchy,&$result,&$log) {
        /* Variables */

        try {
            // Job roles by level
            $result['jobroles'] = self::get_jobroles_by_level($hierarchy['top'],$hierarchy['notIn'],$log);
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
    public static function organization_structure_by_top($top,&$result,&$log) {
        /* Variables */
        $infoTop = null;

        try {
            // Convert to object
            $infoTop = (Object)$top;

            // Get orgnaziation structure
            $result['structure'] = self::get_organization_structure_by_top($infoTop,$log);
        }catch (Exception $ex) {
            $result['error']    = 409;
            $result['message']  = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//organization_structure_by_top


    /**
     * @param           $companiesFS
     * @param           $result
     * @param           $log
     *
     * @throws          Exception
     *
     * @creationDate    28/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronization between FS and KS companies
     */
    public static function synchronize_fsks_companies($companiesFS,&$result,&$log) {
        /* Variables */
        global $CFG;
        $file           = null;
        $path           = null;
        $content        = null;
        $company        = null;
        $companyId      = null;
        $dir            = null;
        $imported       = array();
        $infoImported   = null;
        $infolog        = null;

        try {
            // Save file
            $dir = $CFG->dataroot . '/fellesdata';
            if (!file_exists($dir)) {
                mkdir($dir);
            }

            // File
            $path = $dir . '/wsFSCompanies.txt';

            // Clean old data
            if (file_exists($path)) {
                unlink($path);
            }

            // Save new data
            $file = fopen($path,'w');
            fwrite($file,$companiesFS);
            fclose($file);

            // Process Content
            if (file_exists($path)) {
                // Get content
                $data    = file_get_contents($path);
                $content = json_decode($data);

                // Log
                $infolog = new stdClass();
                $infolog->action      = 'Service wsFSCompany  ';
                $infolog->description = 'Data: ' . $data;
                // Add log
                $log[] = $infolog;

                // Synchronization between FS and KS
                if ($content) {
                    foreach ($content as $company) {
                        // Process the company
                        $companyId = self::process_fs_company($company);

                        // Mark as imported
                        if ($companyId) {
                            $infoImported = new stdClass();
                            $infoImported->fsId     = $company->fsid;
                            $infoImported->ksId     = $companyId;
                            $infoImported->imported = 1;
                            $infoImported->key      = $company->fsid;

                            $imported["'" . $company->fsid . "'"] = $infoImported;
                        }//if_companyId
                    }//company

                    // Log
                    $infolog = new stdClass();
                    $infolog->action      = 'Service wsFSCompany  ';
                    $infolog->description = 'synchronize_fsks_companies - Company sync. FS: ' . implode(',',array_keys($imported));
                    // Add log
                    $log[] = $infolog;
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action      = 'Service wsFSCompany  ';
                    $infolog->description = 'synchronize_fsks_companies - No data';
                    // Add log
                    $log[] = $infolog;
                }
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action      = 'Service wsFSCompany  ';
                $infolog->description = 'synchronize_fsks_companies - File does not exist';
                // Add log
                $log[] = $infolog;
            }//if_path

            // Add result
            $result['companies'] = $imported;

        }catch (Exception $ex) {
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
        $objJobRole     = null;
        $jobRoleId      = null;
        $imported       = array();
        $infoImported   = null;
        $infolog        = null;

        try {
            // Synchronization between FS and KS companies
            foreach ($jobRolesFS as $key => $jobRole) {
                // Convert to object
                $objJobRole = (Object)$jobRole;

                // Process jobrole
                $jobRoleId = self::process_fs_jobroles($objJobRole);

                // Jobrole marked as imported
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
        }catch (Exception $ex) {
            $result['error']    = 409;
            $result['message']  = $ex->getMessage();
            $result['jobRoles'] = $imported;

            throw $ex;
        }//try_catch
    }//synchronize_fsks_jobroles

    /**
     * @param           $usersAccounts
     * @param           $log
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
    public static function synchronize_users_accounts($usersAccounts,&$result,&$log) {
        /* Variables    */
        global $CFG;
        $dir            = null;
        $path           = null;
        $file           = null;
        $content        = null;
        $userid         = null;
        $imported       = array();
        $infoimported   = null;
        $infoaccount    = null;
        $infolog        = null;

        try {
            // Check location to save the file temporary
            $dir = $CFG->dataroot . '/fellesdata';
            if (!file_exists($dir)) {
                mkdir($dir);
            }

            // File
            $path = $dir . '/wsUsersAccounts.txt';

            // Clean old one
            unlink($path);

            // Save new data
            $file = fopen($path,'w');
            fwrite($file,$usersAccounts);
            fclose($file);

            // Process content
            if (file_exists($path)) {
                // Get content
                $data    = file_get_contents($path);
                $content = json_decode($data);

                if ($content) {
                    // Synchronization between FS and KS
                    foreach ($content as $infoaccount) {
                        // Process user account
                        $userid = self::process_user_account($infoaccount);

                        // Mark as imported
                        if($userid) {
                            $infoimported = new stdClass();
                            $infoimported->personalnumber   = $infoaccount->personalnumber;
                            $infoimported->imported         = 1;
                            $infoimported->key              = $infoaccount->id;

                            $imported[$infoaccount->id]     = $infoimported;
                        }//if_userid
                    }//for_each

                    if ($imported) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action      = 'Service wsUsersAccounts  ';
                        $infolog->description = 'synchronize_users_accounts -> ' . json_encode($imported);
                        // Add log
                        $log[] = $infolog;
                    }
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action      = 'Service wsUsersAccounts  ';
                    $infolog->description = 'synchronize_users_accounts - No content';
                    // Add log
                    $log[] = $infolog;
                }//content
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action      = 'Service wsUsersAccounts  ';
                $infolog->description = 'synchronize_users_accounts - File does not exist';
                // Add log
                $log[] = $infolog;
            }//if_path

            // Result
            $result['usersAccounts'] = $imported;
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();
            $result['usersAccounts']    = $imported;
            
            throw $ex;
        }//try_catch
    }//synchronize_users_accounts

    /**
     * @param           $userManagerReporter
     * @param           $result
     * @param           $log
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
    public static function synchronize_user_manager_reporter($userManagerReporter,&$result,&$log) {
        /* Variables */
        global $CFG;
        $dir                = null;
        $path               = null;
        $lstmanagers        = null;
        $data               = null;
        $key                = null;
        $info               = null;
        $synchronized       = null;
        $infoImported       = null;
        $imported           = array();
        $infolog            = null;

        try {
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
                if ($lstmanagers) {
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

                    if ($imported) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action      = 'wsManagerReporter  ';
                        $infolog->description = 'synchronize_user_manager_reporter -> ' . json_encode($imported);
                        // Add log
                        $log[] = $infolog;
                    }
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action      = 'wsManagerReporter  ';
                    $infolog->description = 'synchronize_user_manager_reporter - No content';
                    // Add log
                    $log[] = $infolog;
                }//if_lstrmanagers
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action      = 'wsManagerReporter  ';
                $infolog->description = 'synchronize_user_manager_reporter - File does not exist';
                // Add log
                $log[] = $infolog;
            }//file_exists

            $result['managerReporter'] = $imported;
        }catch (Exception $ex) {
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
     * @param                  $log
     *
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      28/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function synchronize_user_competence($competence,&$result,&$log) {
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
        $infolog        = null;

        try {
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
                if ($mydata) {
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

                        // Log
                        $infolog = new stdClass();
                        $infolog->action      = 'Service wsUserCompetence  ';
                        $infolog->description = 'synchronize_user_competence -> ' . json_encode($imported);
                        // Add log
                        $log[] = $infolog;
                    }//if_imported
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action      = 'Service wsUserCompetence  ';
                    $infolog->description = 'synchronize_user_competence - No Content';
                    // Add log
                    $log[] = $infolog;
                }//if_mydata
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action      = 'Service wsUserCompetence  ';
                $infolog->description = 'synchronize_user_competence - File does not exist';
                // Add log
                $log[] = $infolog;
            }//if_file_exists
        }catch (Exception $ex) {
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
     * @param           $log
     *
     * @throws          Exception
     *
     * @creationDate    24/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unmap companies
     */
    public static function unmap_companies($toUnMap,&$result,&$log) {
        /* Variables */
        global $DB;
        $trans          = null;
        $unmapped       = null;
        $orgUnMapped    = array();
        $info           = null;
        $infoOrg        = null;
        $objOrg         = null;
        $infolog        = null;

        // Begin transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // unmap company --> delete company
            if ($toUnMap) {
                foreach ($toUnMap as $infoOrg) {
                    // Convert to object
                    $objOrg = (Object)$infoOrg;

                    // Delete from report_gen_companydata
                    $unmapped = $DB->delete_records('report_gen_companydata',array('id' => $objOrg->kscompany));
                    // Delete from mdl_report_gen_company_relation
                    $DB->delete_records('report_gen_company_relation',array('companyid' => $objOrg->kscompany));

                    // Delete managers
                    $DB->delete_records('report_gen_company_manager',array('levelthree' => $objOrg->kscompany));
                    // Delete reporters
                    $DB->delete_records('report_gen_company_reporter',array('levelthree' => $objOrg->kscompany));
                    // Delete super users
                    $DB->delete_records('report_gen_super_user',array('levelthree' => $objOrg->kscompany));

                    // Delete user competence data
                    $DB->delete_records('user_info_competence_data',array('companyid' => $objOrg->kscompany));

                    // Job roles
                    $rdoJR = $DB->get_records('report_gen_jobrole_relation',array('levelthree' => $objOrg->kscompany));
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

                    if ($unmapped) {
                        $info = new stdClass();
                        $info->unmapped     = true;
                        $info->key          = $objOrg->id;

                        // add
                        $orgUnMapped[$objOrg->id] = $info;
                    }//if_unmapped
                }//for_toUnMap

                // Log
                if ($orgUnMapped) {
                    $infolog = new stdClass();
                    $infolog->action      = 'Service wsUnMapCompany  ';
                    $infolog->description = 'unmap_companies -> ' . json_encode($orgUnMapped);
                    // Add log
                    $log[] = $infolog;
                }
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action      = 'Service wsUnMapCompany  ';
                $infolog->description = 'unmap_companies - No companies to unmap';
                // Add log
                $log[] = $infolog;
            }//if_toUnMap

            /* Add result   */
            $result['orgUnMapped']  = $orgUnMapped;

            // Commmit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            // Error
            $result['error']        = 409;
            $result['message']      = $ex->getMessage();
            $result['orgUnMapped']  = $orgUnMapped;

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
     * @param             $log
     *
     * @throws            Exception
     *
     * @creationDate    24/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function competence_data($industry,&$result,&$log) {
        /* Variables */
        $infolog = null;

        try {
            // get competence data
            $result['competence'] = self::get_competence_data($industry);

            // Log
            $infolog = new stdClass();
            $infolog->action      = 'Service wsCompetence  ';
            $infolog->description = 'competence_data : ' . $result['competence'];
            // Add log
            $log[] = $infolog;
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//comptence_data

    /**
     * Description
     * Delete competence data
     * 
     * @param           string $competence
     * @param           array $result
     * @param                 $log
     *
     * @throws                Exception
     *
     * @creationDate        28/02/2017
     * @author              eFaktor     (fbv)
     */
    public static function delete_competence_data($competence,&$result,&$log) {
        /* Variables */
        global $CFG;
        global $DB;
        $dblog      = null;
        $instance   = null;
        $info       = null;
        $keys       = null;
        $dir        = null;
        $file       = null;
        $path       = null;
        $content    = null;

        try {
            // Save file
            $dir = $CFG->dataroot . '/fellesdata';
            if (!file_exists($dir)) {
                mkdir($dir);
            }

            // File
            $path = $dir . '/delete_competence.txt';

            // Clean old data
            if ($path) {
                unlink($path);
            }
            // Save new data
            $file = fopen($path,'w');
            fwrite($file,$competence);
            fclose($file);

            // Delete competence
            if (file_exists($path)) {
                // Get content
                $content   = file_get_contents($path);
                $content   = json_decode($content);

                // Delete records
                if ($content) {
                    foreach ($content as $instance) {
                        // Delete competence
                        $sql = " DELETE 
                                 FROM   {user_info_competence_data} 
                                 WHERE  userid = :user 
                                    AND companyid IN ($instance->companies) ";

                        // Execute
                        $rdo = $DB->execute($sql,array('user' => $instance->user));
                        if ($rdo) {
                            if ($keys) {
                                $keys .= ',' . $instance->keys;
                            }else {
                                $keys = $instance->keys;
                            }

                            // Log
                            $infolog = new stdClass();
                            $infolog->action      = 'Service ws_delete_competence  ';
                            $infolog->description = 'delete_competence_data -> ' . $instance->companies;
                            // Add log
                            $log[] = $infolog;
                        }//if_rdo
                    }//for_each_content
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action      = 'Service ws_delete_competence  ';
                    $infolog->description = 'delete_competence_data - No contetn';
                    // Add log
                    $log[] = $infolog;
                }//if_else
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action      = 'Service ws_delete_competence  ';
                $infolog->description = 'delete_competence_data - File does not exist';
                // Add log
                $log[] = $infolog;
            }//if_file

            $result['deleted'] = $keys;
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//delete_competence_data

    /**
     * Description
     * Get managers/reporters
     *
     * @param           String $industry
     * @param           String $result
     * @param                  $log
     *
     * @throws          Exception
     *
     * @creationDate    01/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function managers_reporters($industry,&$result,&$log) {
        /* Variables */
        $infolog = null;

        try {
            // Get managers
            $result['managers']     = self::get_managers_reporters_ks($industry,MANAGER);
            // Log
            $infolog = new stdClass();
            $infolog->action      = 'Service ws_get_managers_reporters  ';
            $infolog->description = 'managers_reporters - managers --> ' . $result['managers'];
            // Add log
            $log[] = $infolog;

            // Get reporters
            $result['reporters']    = self::get_managers_reporters_ks($industry,REPORTER);
            // Log
            $infolog = new stdClass();
            $infolog->action      = 'Service ws_get_managers_reporters  ';
            $infolog->description = 'managers_reporters - reporters --> ' . $result['reporters'];
            // Add log
            $log[] = $infolog;
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();

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
     * @param               $log
     * 
     * @throws      Exception
     * 
     * @creationDate    02/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function clean_managers_reporters($data,$type,&$result,&$log) {
        /* Variables */
        global $DB;
        global $CFG;
        $infolog    = null;
        $rdo        = null;
        $params     = null;
        $table      = null;
        $field      = null;
        $deleted    = null;
        $path       = null;
        $file       = null;
        $content    = null;
        
        try {
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

            // Save file
            $dir = $CFG->dataroot . '/fellesdata';
            if (!file_exists($dir)) {
                mkdir($dir);
            }

            // File
            $path = $dir . '/clean_' . $type . '.txt';

            // Clean old data
            if ($path) {
                unlink($path);
            }
            // Save new data
            $file = fopen($path,'w');
            fwrite($file,$data);
            fclose($file);

            // Delete records
            if (file_exists($path)) {
                // Get content
                $content   = file_get_contents($path);
                $content   = json_decode($content);

                // Delete records
                if ($content) {
                    $params = array();
                    foreach ($content as $instance) {
                        $params['id']   = $instance->key;
                        $params[$field] = $instance->user;

                        $DB->delete_records($table,$params);

                        if ($deleted) {
                            $deleted .= ',' . $instance->key;
                        }else {
                            $deleted = $instance->key;
                        }//if_deleted
                    }//for_data
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action      = 'Service ws_clean_managers_reporters  ';
                    $infolog->description = 'clean_managers_reporters - No content';
                    // Add log
                    $log[] = $infolog;
                }//if_content
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action      = 'Service ws_clean_managers_reporters  ';
                $infolog->description = 'clean_managers_reporters - File does not exist';
                // Add log
                $log[] = $infolog;
            }//if_file_exists

            $result['deleted'] = $deleted;
        }catch (Exception $ex) {
            $result['error']            = 409;
            $result['message']          = $ex->getMessage();

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
        $one        = null;
        $two        = null;
        $three      = null;

        try {
            // Companies by level
            $one    = self::get_companies_by_industry_level($industry,1);

            $two    = self::get_companies_by_industry_level($industry,2);

            $three  = self::get_companies_by_industry_level($industry,3);


            // Search criteria
            $params = array();
            $params['industry'] = $industry;

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
            $sql = " SELECT   re.id                             as 'keyid',
                              $field 						    as 'userid',
                              u.username,
                              IF(re.levelone,re.levelone,0)     as 'levelone',
                              IF(re.leveltwo,re.leveltwo,0)     as 'leveltwo',
                              IF(re.levelthree,re.levelthree,0) as 'levelthree'
                     FROM	    {" . $table . "}	re
                         JOIN	{user}							u	ON  u.id  			= $field
                         JOIN	{report_gen_company_relation}	cr  ON	cr.parentid 	= re.levelzero
                         JOIN	{report_gen_companydata}		co	ON  co.id 			= cr.companyid 
                                                                    AND co.industrycode = :industry
                     WHERE 	re.levelzero IS NOT NULL
                        AND re.levelzero != 0
                            AND (re.levelone IS NULL
                                 OR
                                 re.levelone IN ($one))
                            AND (re.leveltwo IS NULL
                                 OR 
                                 re.leveltwo IN ($two))
                            AND (re.levelthree IS NULL
                                 OR 
                                 re.levelthree IN ($three))
                     ORDER by $field, u.username ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $data .= json_encode($instance) . "\n";
                }

            }//if_Rdo

            return $data;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_managers_ks

    private static function get_companies_by_industry_level($industrycode,$level) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $params     = null;
        $sql        = null;
        $companies  = array();

        try {
            // Search criteria
            $params = array();
            $params['industry'] = $industrycode;
            $params['mapped']   = 'TARDIS';
            $params['level']    = $level;

            // SQL Instruction
            $sql = " SELECT co.id 
                     FROM 	{report_gen_companydata}	co
                     where 	co.industrycode   = :industry	
                        AND co.mapped         = :mapped
                        AND co.hierarchylevel = :level ";
            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $companies[$instance->id] = $instance->id;
                }
                return implode(',',$companies);
            }else {
                return 0;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_companies_by_industry

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
                foreach ($rdo as $instance) {
                    $competence .=  json_encode($instance) . "\n";
                }
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
                    case UPDATE_ACTION:
                    case STATUS_ACTION:
                        // Add the user as manager if it's the case
                        if ($manager) {
                            // Check if the user is already manager or not 
                            $IsManager = self::is_manager_reporter($infoManager,MANAGER);
                            if (!$IsManager) {
                                // Create
                                $DB->insert_record('report_gen_company_manager',$infoManager);
                            }//if_manager
                        }else if($reporter) {
                            // Check if the user is already reporter or not
                            $IsReporter = self::is_manager_reporter($infoReporter,REPORTER);
                            if (!$IsReporter) {
                                // Create
                                $DB->insert_record('report_gen_company_reporter',$infoReporter);
                            }//if_reporter
                        }

                        // Synchronize
                        $sync = true;

                        break;
                    case DELETE_ACTION:
                        // Delete From Manager
                        if ($manager) {
                            $IsManager = self::is_manager_reporter($infoManager,MANAGER);
                            if ($IsManager) {
                                $DB->delete_records('report_gen_company_manager',array('id' => $IsManager));
                            }//if_Manager
                        }else if ($reporter) {
                            // Delete From Reporter
                            $IsReporter = self::is_manager_reporter($infoReporter,REPORTER);
                            if ($IsReporter) {
                                $DB->delete_records('report_gen_company_reporter',array('id' => $IsReporter));
                            }//if_reporter
                        }//if_manager

                        // Synchronized
                        $sync = true;

                        break;
                }//action
            }//if_user

            // Commit
            $trans->allow_commit();

            return $sync;
        }catch (Exception $ex) {
            // Rollback
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
                    case STATUS_ACTION:
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

        // Begin transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();

            // Check if the user already exist
            $rdoUser = $DB->get_record('user',array('username' => $userAccount->personalnumber));

            // Extract user data
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

            // Appply action
            switch ($userAccount->action) {
                case ADD_ACTION:
                case UPDATE_ACTION:
                case STATUS_ACTION:
                    if (!$rdoUser) {
                        // Execute
                        $userId = $DB->insert_record('user',$infoUser);
                    }else {
                        $rdoUser->firstname    = $userAccount->firstname;
                        $rdoUser->lastname     = $userAccount->lastname;
                        $rdoUser->email        = self::process_right_email($rdoUser->email,$userAccount->email);
                        $rdoUser->timemodified = $time;
                        $rdoUser->deleted      = 0;

                        // Execute
                        $DB->update_record('user',$rdoUser);
                    }//if_notExist

                    // Synchronized
                    $sync = true;

                    break;
                case DELETE_ACTION:
                    // Delete user
                    if ($rdoUser) {
                        // Delete his/her connection with the municipality
                        self::remove_connection_municipality($rdoUser->id,$userAccount->industry);
                    }else {
                        // Execute
                        //$userId             = $DB->insert_record('user',$infoUser);
                    }//if_infoUsers

                    // Synchronized
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
                    // Update
                    $rdo->ressursnr     = $userAccount->ressursnr;
                    $rdo->industrycode  = $userAccount->industry;

                    // Execute
                    $DB->update_record('user_resource_number',$rdo);
                }else {
                    // Insert
                    $instance = new stdClass();
                    $instance->userid       = $userId;
                    $instance->ressursnr    = $userAccount->ressursnr;
                    $instance->industrycode = $userAccount->industry;

                    // Execute
                    $DB->insert_record('user_resource_number',$instance);
                }//if_rdo
            }//if_resource_number

            // Add gender
            if ($userAccount->action != DELETE_ACTION) {
                if (is_numeric($userAccount->personalnumber) && ($userAccount->personalnumber) == 11) {
                    Gender::Add_UserGender($userId,$userAccount->personalnumber);
                }
            }

            // Commit
            $trans->allow_commit();

            return $sync;
        }catch (Exception $ex) {
            // Log
            $dbLog = 'Error --> ' . $ex->getTraceAsString() . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            // Rollback
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

        // Begin transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();


            // Check if already exists
            $params = array();
            $params['id']               = $companyInfo->ksid;
            $params['org_enhet_id']     = $companyInfo->fsid;
            $params['hierarchylevel']   = $companyInfo->level;
            $params['industrycode']     = $companyInfo->industry;

            // SQL Instruction
            $sql = " SELECT * 
                     FROM   {report_gen_companydata} 
                     WHERE  hierarchylevel  = :hierarchylevel 
                      AND   industrycode    = :industrycode ";

            // Check with org_enhet_id
            $sqlrdo = $sql . " AND id           = :id 
                               AND org_enhet_id = :org_enhet_id ";
            $rdo = $DB->get_record_sql($sqlrdo,$params);
            if (!$rdo) {
                unset($params['id']);
                $params['name'] = $companyInfo->name;

                //Check name without id
                $sqlrdo = $sql . " AND name = :name 
                                   AND org_enhet_id = :org_enhet_id";
                $rdo = $DB->get_record_sql($sqlrdo,$params);
                if (!$rdo) {
                    // Compare without org_enhet_id && name
                    unset($params['org_enhet_id']);
                    unset($params['name']);
                    $params['id']               = $companyInfo->ksid;

                    $sqlrdo = $sql . " AND id           = :id 
                                       AND org_enhet_id IS NULL ";
                    $rdo = $DB->get_record_sql($sqlrdo,$params);
                    if (!$rdo) {
                        // Compare without org_enhet_id && id. with name
                        unset($params['id']);
                        $params['name'] = $companyInfo->name;

                        $sqlrdo = $sql . " AND name = :name 
                                           AND org_enhet_id IS NULL ";
                        $rdo = $DB->get_record_sql($sqlrdo,$params);
                    }

                }
            }


            // Extract info company
            $instanceCompany = new stdClass();
            $instanceCompany->name              = $companyInfo->name;
            $instanceCompany->industrycode      = $companyInfo->industry;
            $instanceCompany->hierarchylevel    = $companyInfo->level;
            $instanceCompany->public            = $companyInfo->public;
            // Invoice data
            $instanceCompany->ansvar            = ($companyInfo->ansvar     ? $companyInfo->ansvar      : null);
            $instanceCompany->tjeneste          = ($companyInfo->tjeneste   ? $companyInfo->tjenester   : null);
            $instanceCompany->adresse1          = ($companyInfo->adresse1   ? $companyInfo->adresse1    : null);
            $instanceCompany->adresse2          = ($companyInfo->adresse2   ? $companyInfo->adresse2    : null);
            $instanceCompany->adresse3          = ($companyInfo->adresse3   ? $companyInfo->adresse3    : null);
            $instanceCompany->postnr            = ($companyInfo->postnr     ? $companyInfo->postnr      : null);
            $instanceCompany->poststed          = ($companyInfo->poststed   ? $companyInfo->poststed    : null);
            $instanceCompany->epost             = ($companyInfo->epost      ? $companyInfo->epost       : null);
            $instanceCompany->mapped            = MAPPED_TARDIS;
            $instanceCompany->org_enhet_id      = $companyInfo->fsid;
            $instanceCompany->modified          = $time;

            // Apply action
            switch ($companyInfo->action) {
                case ADD_ACTION:
                case UPDATE_ACTION:
                case STATUS_ACTION:
                    if (!$rdo) {
                        // Execute
                        $companyId = $DB->insert_record('report_gen_companydata',$instanceCompany);
                    }else {
                        // Execute
                        $companyId           = $rdo->id;
                        $instanceCompany->id = $rdo->id;
                        $DB->update_record('report_gen_companydata',$instanceCompany);
                    }//if_no_exists

                    // Relation parent
                    if ($companyInfo->parent) {
                        // Only one parent connected with
                        // Check if already exists
                        $rdo = $DB->get_record('report_gen_company_relation',array('companyid' => $companyId),'id,parentid,modified');
                        if (!$rdo) {
                            // Create relation
                            $instanceParent = new stdClass();
                            $instanceParent->companyid  = $companyId;
                            $instanceParent->parentid   = $companyInfo->parent;
                            $instanceParent->modified   = $time;

                            // Execute
                            $DB->insert_record('report_gen_company_relation',$instanceParent);
                        }else {
                            // Update to nw parent
                            $rdo->parentid   = $companyInfo->parent;
                            $rdo->modified   = $time;

                            // Execute
                            $DB->update_record('report_gen_company_relation',$rdo);
                        }//if_!rdo
                    }//if_parent

                    break;

                case DELETE_ACTION:
                    $companyId = $companyInfo->ksid;
                    // Delete company
                    $DB->delete_records('report_gen_companydata',array('id' => $companyInfo->ksid));

                    // Delete relations
                    $DB->delete_records('report_gen_company_relation',array('companyid' => $companyInfo->ksid));

                    // Delete user competence data
                    $DB->delete_records('user_info_competence_data',array('companyid' => $companyInfo->ksid));

                    // Delete report_managers
                    $DB->delete_records('report_gen_company_manager',array('levelthree' => $companyInfo->ksid));

                    // Delete report_reporters
                    $DB->delete_records('report_gen_company_reporter',array('levelthree' => $companyInfo->ksid));

                    // Delete report_super_user
                    $DB->delete_records('report_gen_super_user',array('levelthree' => $companyInfo->ksid));

                    // Job roles
                    $rdoJR = $DB->get_records('report_gen_jobrole_relation',array('levelthree' => $companyInfo->ksid));
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

                    break;
            }//company_Action

            // Commit
            $trans->allow_commit();
            
            return $companyId;
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//process_fs_company


    /**
     * @param           $topCompany
     * @param           $log
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
    private static function get_organization_structure_by_top($topCompany,&$log) {
        /* Variables */
        global $DB;
        $sql                = null;
        $rdo                = null;
        $params             = null;
        $orgStructure       = array();
        $infoOrganization   = null;
        $maxLevel           = null;
        $i                  = null;
        $notIn              = null;
        $infolog            = null;

        try {
            // Get highest level of the hierarchy
            $maxLevel = self::get_max_level_organization();

            // Search criteria
            $params = array();
            $params['level']    = $topCompany->level;

            // Not in companies
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
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                // Top company
                $infoOrganization = new stdClass();
                $infoOrganization->id           = $rdo->id;
                $infoOrganization->name         = $rdo->name;
                $infoOrganization->industrycode = $rdo->industrycode;
                $infoOrganization->level        = $rdo->hierarchylevel;
                $infoOrganization->parent       = 0;

                // Add company
                $orgStructure[$rdo->id] = $infoOrganization;

                // Get hierarchy
                if ($maxLevel) {
                    $parents = implode(',',array_keys($orgStructure));

                    // Log
                    $infolog = new stdClass();
                    $infolog->action      = 'Service wsKSOrganizationStructure  ';
                    $infolog->description = 'get_organization_structure_by_top --> Organizations : ' . $parents ;
                    // Add log
                    $log[] = $infolog;

                    for($i=2;$i<=$maxLevel;$i++) {
                        // Information about the rest hierarchy
                        $parents = self::get_my_levels($parents,$i,$orgStructure,$rdo->industrycode,$notIn);

                        // Log
                        $infolog = new stdClass();
                        $infolog->action      = 'Service wsKSOrganizationStructure  ';
                        $infolog->description = 'get_organization_structure_by_top --> Organizations : ' . $parents ;
                        // Add log
                        $log[] = $infolog;
                    }
                }//if_MaxLevel
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action      = 'Service wsKSOrganizationStructure  ';
                $infolog->description = 'get_organization_structure_by_top --> No elements ' ;
                // Add log
                $log[] = $infolog;
            }//if_Rdo

            return $orgStructure;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_organization_structure_by_top

    /**
     * @param           $parents
     * @param           $level
     * @param           $orgStructure
     * @param           $industrycode
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
    private static function get_my_levels($parents,$level,&$orgStructure,$industrycode,$notIn) {
        /* Variables    */
        global $DB;
        $sql                = null;
        $rdo                = null;
        $companies          = array();
        $infoOrganization   = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['level']    = $level;
            $params['industry'] = $industrycode;

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
                        AND co.industrycode   = :industry
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
     * @param           $log
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
    private static function get_generics_jobroles($notIn,&$log) {
        /* Variables */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $infoJobRole    = null;
        $jobRoles       = array();
        $infolog        = null;

        try {
            // SQL Isntruction
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

            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Info
                    $infoJobRole = new stdClass();
                    $infoJobRole->id            = $instance->id;
                    $infoJobRole->name          = $instance->name;
                    $infoJobRole->industryCode  = $instance->industrycode;

                    // Add
                    $jobRoles[$instance->id] = $infoJobRole;
                }//for_Rdo

                // Log
                $infolog = new stdClass();
                $infolog->action      = 'wsKSJobRolesGenerics  ';
                $infolog->description = 'get_generics_jobroles -> ' . implode(',',array_keys($jobRoles));
                // Add log
                $log[] = $infolog;
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action      = 'wsKSJobRolesGenerics  ';
                $infolog->description = 'get_generics_jobroles - No jobroles';
                // Add log
                $log[] = $infolog;
            }//if_Rdo

            return $jobRoles;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_generics_jobroles

    /**
     * @param           $top
     * @param           $notIn
     * @param           $log
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
    private static function get_jobroles_by_level($top,$notIn,&$log) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $infoJobRole    = null;
        $jobRoles       = array();
        $infolog        = null;

        try {
            // SQL Instruction
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

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Job role info
                    $infoJobRole = new stdClass();
                    $infoJobRole->id            = $instance->id;
                    $infoJobRole->name          = $instance->name;
                    $infoJobRole->industryCode  = $instance->industrycode;
                    $infoJobRole->relation      = self::get_jobrole_relation($instance->myrelations);

                    // Add job role
                    $jobRoles[$instance->id] = $infoJobRole;
                }//for_rdo

                // Log
                $infolog = new stdClass();
                $infolog->action      = 'Service wsKSJobRoles  ';
                $infolog->description = 'get_jobroles_by_level -> ' . implode(',',array_keys(',',$jobRoles));
                // Add log
                $log[] = $infolog;
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action      = 'Service wsKSJobRoles  ';
                $infolog->description = 'get_jobroles_by_level - No job roles';
                // Add log
                $log[] = $infolog;
            }//if_rdo

            return $jobRoles;
        }catch (Exception $ex) {
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