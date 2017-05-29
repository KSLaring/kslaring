<?php
/**
 * Fellesdata Integration - Cron
 *
 * @package         local/fellesdata
 * @subpackage      cron
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    01/02/2016
 * @author          eFaktor     (fbv)
 *
 */

define('SYNC_COMP','companies');
define('SYNC_JR','jobroles');

define('TEST_ORG',1);
define('TEST_JR',2);

define('TEST_FS_USERS',3);
define('TEST_FS_ORG',4);
define('TEST_FS_JR',5);
define('TEST_FS_MANAGERS_REPORTERS',6);
define('TEST_FS_USER_COMP_JR',7);

define('TEST_FS_SYNC_ORG',8);
define('TEST_FS_SYNC_JR',9);
define('TEST_FS_SYNC_MANAGERS_REPORTERS',10);

define('TEST_FS_SYNC_COMPETENCE',11);
define('TEST_FS_SYNC_FS_USERS',12);

define('TEST_FS_UNMAP_COMPENTECE',14);
define('TEST_FS_UNMAP_MANAGERS_REPORTERS',15);
define('TEST_FS_UNMAP_ORGANIZATION',16);

class FELLESDATA_CRON {
    /**********/
    /* PUBLIC */
    /**********/

    public static function cron($plugin,$fstExecution) {
        /* Variables    */
        global $SESSION;
        global $CFG;
        $dbLog              = null;
        $suspicious_path    = null;

        try {
            unset($SESSION->manual);

            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START FELLESDATA CRON . ' . "\n";

            // Suspicious data
            $suspicious_path = $CFG->dataroot . '/' . $plugin->suspicious_path;
            if ($suspicious_path) {
                if (!file_exists($suspicious_path)) {
                    mkdir($suspicious_path);
                }
            }//if_suspucuous_path

            // Unmap process
            if (!$fstExecution) {
                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START  UNMAP Organizations. ' . "\n";
                self::unmap_organizations($plugin,KS_UNMAP_COMPANY);
            }//fstExecution_tounmap

            // Import KS
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Import KS. ' . "\n";
            self::import_ks($plugin);

            // Import fellesdata
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Import Fellesdata. ' . "\n";
            self::import_fellesdata($plugin);

            // Users accounts synchornization
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Users FS Synchronization. ' . "\n";
            self::users_fs_synchronization($plugin);

            // Companies synchornization
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Companies FS Synchronization. ' . "\n";
            self::companies_fs_synchronization($plugin,$fstExecution);

            // Job roles to map
            self::jobroles_fs_to_map($plugin);

            // Competence synchronization
            if (!$fstExecution) {
                self::competence_synchronization($plugin);
            }

            /* Log  */
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA CRON . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//cron

    /* MANUAL EXECUTION */
    public static function cron_manual($fstExecution,$option) {
        /* Variables    */
        global $SESSION;
        $pluginInfo = null;

        try {
            if (!isset($SESSION->manual)) {
                $SESSION->manual = true;
            }

            /* Plugin Info      */
            $pluginInfo     = get_config('local_fellesdata');

            switch ($option) {
                case TEST_ORG:
                    echo "Organization Structure" . "</br>";
                    /* Import Organization Structure    */
                    self::organization_structure($pluginInfo);

                    break;
                case TEST_JR:
                    echo "JobRoles" . "</br>";
                    /* Import Job Roles */
                    self::import_ks_jobroles($pluginInfo);

                    break;
                case TEST_FS_USERS:
                    echo "Import FS Users" . "</br>";
                    /* Import FS Users              */
                    self::import_fs_users($pluginInfo);
                    
                    break;
                case TEST_FS_ORG:
                    echo "Import FS ORG" . "</br>";
                    /* Import FS Companies          */
                    self::import_fs_orgstructure($pluginInfo);

                    break;
                case TEST_FS_JR:
                    echo "Import FS job Roles" . "</br>";
                    /* Import FS Job roles  */
                    self::import_fs_jobroles($pluginInfo);

                    break;
                case TEST_FS_MANAGERS_REPORTERS:
                    echo "Import FS Managers USrs" . "</br>";
                    /* Import FS User Competence    */
                    self::import_fs_managers_reporters($pluginInfo);

                    break;
                case TEST_FS_USER_COMP_JR:
                    echo "Import Fs User Competence JR" . "</br>";
                    /* Import FS User Competence JR */
                    self::import_fs_user_competence_jr($pluginInfo);

                    break;
                case TEST_FS_SYNC_ORG:
                    echo "Synchronization FS Companies" . "</br>";

                    self::companies_fs_synchronization($pluginInfo,false);

                    break;
                case TEST_FS_SYNC_JR:
                    echo "Check Job Roles to Map - Mailing" . "</br>";

                    self::jobroles_fs_to_map($pluginInfo);

                    break;
                case TEST_FS_SYNC_MANAGERS_REPORTERS:
                    echo "Synchronization Manager Reporters";
                    /* Synchronization Managers && Reporters    */
                    self::manager_reporter_synchronization($pluginInfo,KS_MANAGER_REPORTER);

                    break;
                case TEST_FS_SYNC_COMPETENCE:
                    echo "Synchronization User Competence";
                    /* Synchronization User Competence JobRole  -- Add/Update */
                    self::user_competence_synchronization($pluginInfo,KS_USER_COMPETENCE);

                    /* Synchronization User Competence JobRole  -- Delete */
                    self::user_competence_synchronization($pluginInfo,KS_USER_COMPETENCE,true);

                    break;
                case TEST_FS_SYNC_FS_USERS:
                    echo "Synchronization Users FS";
                    /* Synchronization Users Accounts   */
                    self::users_fs_synchronization($pluginInfo);

                    break;
                case TEST_FS_UNMAP_COMPENTECE:
                    echo "UNMAP USERS COMPETENCE";
                    self::unmap_user_competence($pluginInfo,KS_UNMAP_USER_COMPETENCE);

                    break;
                case TEST_FS_UNMAP_MANAGERS_REPORTERS:
                    echo "UNMAP  MANAGERS REPORTERS";
                    self::unmap_managers_reporters($pluginInfo,KS_MANAGER_REPORTER);

                    break;
                case TEST_FS_UNMAP_ORGANIZATION:
                    echo "UNMAP ORGANIZATIONS";
                    self::unmap_organizations($pluginInfo,KS_UNMAP_COMPANY);

                    break;
                default:
                    break;
            }//switch_option
    }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//cron_manual

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Competence synchronization
     *
     * @param       object  $plugin
     *
     * @throws              Exception
     *
     * @creationDate        20/02/2017
     * @author              eFaktor     (fbv)
     */
    private static function competence_synchronization($plugin) {
        /* Variables */
        global $CFG;
        $dbLog = null;

        try {
            // Synchronization Managers && Reporters
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START  Managers/Reporters FS Synchronization. ' . "\n";
            self::manager_reporter_synchronization($plugin,KS_MANAGER_REPORTER);

            // Synchronization User Competence JobRole  -- Add/Update
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Users competence FS Synchronization. ' . "\n";
            self::user_competence_synchronization($plugin,KS_USER_COMPETENCE);

            // Synchronization User Competence JobRole  -- Delete
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Users Competence to delete FS Synchronization. ' . "\n";
            self::user_competence_synchronization($plugin,KS_USER_COMPETENCE,true);

            // Log
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//competence_synchronization
    
    /**
     * Description
     * Import data from KS site
     *
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    02/02/0216
     * @author          eFaktor     (fbv)
     */
    private static function import_ks($pluginInfo) {
        /* Variables    */
        global $CFG;
        $dbLog          = null;

        try {
            // Import organization structure
            self::organization_structure($pluginInfo);

            // Import jobroles
            self::import_ks_jobroles($pluginInfo);
        }catch (Exception $ex) {
            // Log
            $dbLog  = $ex->getMessage() . "\n\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Import KS . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_ks

    /**
     * Description
     * Import the organization structure from KS, for a specific level
     *
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function organization_structure($pluginInfo) {
        /* Variables */
        global $CFG;
        $infoLevel      = null;
        $params         = null;
        $response       = null;
        $dbLog          = null;

        try {
            // Request web service
            $infoLevel = new stdClass();
            $infoLevel->company   = $pluginInfo->ks_muni;
            $infoLevel->level     = 1;
            // Don't import all companies over and over
            $infoLevel->notIn     = 0;//KS::existing_companies();

            // Call web service
            $params = array('topCompany' => $infoLevel);
            $response = self::process_ks_service($pluginInfo,KS_ORG_STRUCTURE,$params);

            if ($response['error'] == '200') {
                // Import organization structure
                KS::import_ks_organization($response['structure']);
            }else {
                // Log
                $dbLog = "ERROR SERVICE: " . $response['message'] . "\n" . "\n";
                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Fellesdata CRON Ks Organization Structure . ' . "\n";
                error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
            }//if_no_error
        }catch (Exception $ex) {
            // Log
            $dbLog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Fellesdata CRON Ks Organization Structure . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//organization_structure

    /**
     * Description
     * Import all job roles from KS site
     *
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_ks_jobroles($pluginInfo) {
        /* Variables    */
        global $CFG;
        $params     = null;
        $response   = null;
        $infoLevel  = null;
        $notIn      = null;
        $hierarchy  = null;
        $jobRoles   = null;
        $dbLog      = null;
        
        try {
            // Jobroles generics
            $notIn = KS::existing_jobroles(true);

            // Call web service
            $response = self::process_ks_service($pluginInfo,KS_JOBROLES_GENERICS,array('notIn' => $notIn));

            // Import jobroles generics
            if ($response['error'] == '200') {
                KS::ks_jobroles($response['jobroles'],true);
            }else {
                // Log
                $dbLog = "ERROR: " . $response['message'] . "\n" . "\n";
                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Fellesdata CRON KS Job Generics Roles . ' . "\n";
                error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
            }//if_no_error

            // Jobroles no generics
            $hierarchy  = KS::get_hierarchy_jr($pluginInfo->ks_muni);
            if ($hierarchy) {
                $notIn      = KS::existing_jobroles(false,implode(',',$hierarchy));
                foreach ($hierarchy as $top) {
                    // Params web service
                    $infoLevel = new stdClass();
                    $infoLevel->notIn   = $notIn;
                    $infoLevel->top     = $top;

                    // Call web service
                    $params = array('hierarchy' => $infoLevel);
                    $response = self::process_ks_service($pluginInfo,KS_JOBROLES,$params);

                    // Import jobroles no generics
                    if ($response['error'] == '200') {
                        KS::ks_jobroles($response['jobroles']);
                    }else {
                        // Log
                        $dbLog = "ERROR: " . $response['message'] . "\n" . "\n";
                    }//if_no_error
                }//for_hierarchy
            }//if_hierarchy

            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Fellesdata CRON KS Job Roles . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Fellesdata CRON KS Job Roles . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_ks_jobroles

    /**
     * Description
     * KS Web Services to import data from KS site and synchronize data between fellesdata and KS
     *
     * @param           $pluginInfo
     * @param           $service
     * @param           $params
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function process_ks_service($pluginInfo,$service,$params) {
        /* Variables    */
        global $CFG;
        $domain         = null;
        $token          = null;
        $server         = null;
        $error          = false;

        try {
            // Data to call Service
            $domain     = $pluginInfo->ks_point;
            $token      = $pluginInfo->kss_token;

            // Build end Point Service
            $server = $domain . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction=' . $service .'&moodlewsrestformat=json';

            // Paramters web service
            $fields = http_build_query( $params );
            $fields = str_replace( '&amp;', '&', $fields );

            // Call service
            $ch = curl_init($server);
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST,2 );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Length: ' . strlen( $fields ) ) );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields );

            $response = curl_exec( $ch );

            if( $response === false ) {
                $error = curl_error( $ch );
            }

            curl_close( $ch );

            $result = json_decode($response);

            // Conver to array
            if (!is_array($result)) {
                $result = (Array)$result;
            }

            return $result;
        }catch (Exception $ex) {
            // Log
            $dbLog = "ERROR: " . $ex->getMessage() .  "\n\n";
            $dbLog .= $ex->getTraceAsString() . "\n\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Error calling web service . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//process_ks_service

    /**************/
    /* FELLESDATA */
    /**************/

    /**
     * Description
     * Save temporary data. Split the process in n-steps
     * @param           $content
     * @param           $type
     *
     * @throws          Exception
     *
     * @creationDate    05/05/2017
     * @author          eFaktor     (fbv)
     */
    private static function save_temporary_fs($content,$type) {
        /* Variables */
        $data       = null;
        $total      = null;
        $i          = null;

        try {
            // Get total
            $total = count($content);

            // Split the process if it is too big
            if ($total > MAX_IMP_FS) {
                for($i=0;$i<=$total;$i=$i+MAX_IMP_FS) {
                    $data = array_slice($content,$i,MAX_IMP_FS,true);
                    FS::save_temporary_fellesdata($data,$type);
                }
            }else {
                FS::save_temporary_fellesdata($content,$type);
            }//if_max_imp
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//save_temporary_fs

    /**
     * Description
     * Import data from fellesdata
     *
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fellesdata($pluginInfo) {
        /* Variables    */
        global $CFG;
        $dbLog        = null;

        try {
            // Import FS Users
            self::import_fs_users($pluginInfo);

            // Import FS Companies
            self::import_fs_orgstructure($pluginInfo);

            // Import FS Job roles
            self::import_fs_jobroles($pluginInfo);

            // Import FS User Competence
            self::import_fs_managers_reporters($pluginInfo);

            // Import FS User Competence JR
            self::import_fs_user_competence_jr($pluginInfo);

            // Send suspicious notifications
            if ($pluginInfo->suspicious_path) {
                // Send Notifications
                suspicious::send_suspicious_notifications($pluginInfo);
                // Send Reminder
                suspicious::send_suspicious_notifications($pluginInfo,true);
            }//suspicious_path
        }catch (Exception $ex) {
            // Log
            $dbLog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import Fellesdata . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_fellesdata

    /**
     * Description
     * Import all users from Fellesdata
     *
     * @param           object  $plugin
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fs_users($plugin) {
        /* Variables    */
        global $CFG;
        $pathFile       = null;
        $suspiciousPath = null;
        $content        = null;
        $fsUsers        = null;
        $dbLog          = null;
        
        try {
            // Call web service
            $fsResponse = self::process_tradis_service($plugin,TRADIS_FS_USERS);

            // Import data into temporary tables
            if ($fsResponse) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_USERS);

                // Open file
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS . '.txt';
                if (file_exists($pathFile)) {
                    // Get last changes
                    // First check if is a suspicious file
                    if ($plugin->suspicious_path) {
                        if (!suspicious::check_for_suspicious_data(TRADIS_FS_USERS,$pathFile)) {
                            // Get content
                            $content = file_get_contents($pathFile);
                            if (strpos(chr(13),$content)) {
                                $content = explode(chr(13),$content);
                            }else {
                                $content = file($pathFile);
                            }

                            self::save_temporary_fs($content,IMP_USERS);
                        }else {
                            // Mark file as suspicious
                            $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_USERS,$plugin);

                            // Move file to the right folder
                            copy($pathFile,$suspiciousPath);
                            unlink($pathFile);
                        }//if_suspicious
                    }else {
                        // Get content
                        $content = file_get_contents($pathFile);
                        if (strpos(chr(13),$content)) {
                            $content = explode(chr(13),$content);
                        }else {
                            $content = file($pathFile);
                        }

                        self::save_temporary_fs($content,IMP_USERS);
                    }
                }//if_exists
            }//if_fsResponse
        }catch (Exception $ex) {
            // Log
            $dbLog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= $ex->getTraceAsString() . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import FS Users . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_fs_users

    /**
     * Description
     * Import all companies from fellesdata
     *
     * @param           object  $plugin
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fs_orgstructure($plugin) {
        /* Variables    */
        global $CFG;
        $pathFile   = null;
        $content    = null;
        $fsResponse = null;
        $dbLog      = null;

        try {
            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import FS ORG Structure . ' . "\n";

            // Call web service
            $fsResponse = self::process_tradis_service($plugin,TRADIS_FS_COMPANIES);

            // Import data into temporary tables
            if ($fsResponse) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_COMPANIES);

                // Open file
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_COMPANIES . '.txt';
                if (file_exists($pathFile)) {
                    // Get last changes
                    // First check if is a suspicious file
                    if ($plugin->suspicious_path) {
                        if (!suspicious::check_for_suspicious_data(TRADIS_FS_COMPANIES,$pathFile)) {
                            // Get content
                            $content = file_get_contents($pathFile);
                            if (strpos(chr(13),$content)) {
                                $content = explode(chr(13),$content);
                            }else {
                                $content = file($pathFile);
                            }

                            self::save_temporary_fs($content,IMP_COMPANIES);
                        }else {
                            // Mark file as suspicious
                            $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_COMPANIES,$plugin);

                            // Move file to the right folder
                            copy($pathFile,$suspiciousPath);
                            unlink($pathFile);
                        }//if_suspicious
                    }else {
                        // Get content
                        $content = file_get_contents($pathFile);
                        if (strpos(chr(13),$content)) {
                            $content = explode(chr(13),$content);
                        }else {
                            $content = file($pathFile);
                        }

                        self::save_temporary_fs($content,IMP_COMPANIES);
                    }///if_suspicous_path
                }//if_exists
            }else {
                $dbLog .= ' ERROR Import FS ORG Structure - RESPONSE NULL. ' . "\n";
            }//if_fsResponse

            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import FS ORG Structure . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import FS ORG Structure . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_fs_orgstructure

    /**
     * Description
     * Import FS Job roles from fellesdata
     *
     * @param           object  $plugin
     *
     * @throws          Exception
     *
     * @creationDate    04/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fs_jobroles($plugin) {
        /* Variables    */
        global $CFG;
        $pathFile   = null;
        $content    = null;
        $fsResponse = null;
        $dbLog      = null;
        
        try {
            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import FS JOB ROLES . ' . "\n";
            
            // Call web service
            $fsResponse = self::process_tradis_service($plugin,TRADIS_FS_JOBROLES);

            // Import data into temporary tables
            if ($fsResponse) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_JOBROLES);

                // Open file
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_JOBROLES . '.txt';
                if (file_exists($pathFile)) {
                    //Get last changes
                    // First check if is a suspicious file
                    if ($plugin->suspicious_path) {
                        if (!suspicious::check_for_suspicious_data(TRADIS_FS_JOBROLES,$pathFile)) {
                            // Get content
                            $content = file_get_contents($pathFile);
                            if (strpos(chr(13),$content)) {
                                $content = explode(chr(13),$content);
                            }else {
                                $content = file($pathFile);
                            }
                            self::save_temporary_fs($content,IMP_JOBROLES);
                        }else {
                            // Mark file as suspicious
                            $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_JOBROLES,$plugin);

                            // Move file to the right folder
                            copy($pathFile,$suspiciousPath);
                            unlink($pathFile);
                        }//if_suspicious
                    }else {
                        // Get content
                        $content = file_get_contents($pathFile);
                        if (strpos(chr(13),$content)) {
                            $content = explode(chr(13),$content);
                        }else {
                            $content = file($pathFile);
                        }

                        self::save_temporary_fs($content,IMP_JOBROLES);
                    }//if_suspicious_path
                }//if_exists
            }else {
                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import FS JOB ROLES - Response null . ' . "\n";
            }//if_fsResponse

            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import FS JOB ROLES . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import FS Job Roles . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_fs_jobroles

    /**
     * Description
     * Import Managers Reporters
     *
     * @param           object  $plugin
     *
     * @throws          Exception
     *
     * @creationDate    13/06/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fs_managers_reporters($plugin) {
        /* Variables    */
        global $CFG;
        $pathFile               = null;
        $content                = null;
        $fsManagersReporters    = null;
        $dbLog                  = null;
        
        try {
            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import FS MANAGERRS REPORTERS . ' . "\n";

            // Call web service
            $fsManagersReporters = self::process_tradis_service($plugin,TRADIS_FS_MANAGERS_REPORTERS);

            // Import data into temporary tables
            if ($fsManagersReporters) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_MANAGERS_REPORTERS);

                // Open file
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_MANAGERS_REPORTERS . '.txt';
                if (file_exists($pathFile)) {
                    // Get last changes
                    // First check if is a suspicious file
                    if ($plugin->suspicious_path) {
                        if (!suspicious::check_for_suspicious_data(TRADIS_FS_MANAGERS_REPORTERS,$pathFile)) {
                            // Get content
                            $content = file_get_contents($pathFile);
                            if (strpos(chr(13),$content)) {
                                $content = explode(chr(13),$content);
                            }else {
                                $content = file($pathFile);
                            }

                            self::save_temporary_fs($content,IMP_MANAGERS_REPORTERS);
                        }else {
                            // Mark file as suspicious
                            $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_MANAGERS_REPORTERS,$plugin);

                            // Move file to the right folder
                            copy($pathFile,$suspiciousPath);
                            unlink($pathFile);
                        }//if_suspicious
                    }else {
                        // Get content
                        $content = file_get_contents($pathFile);
                        if (strpos(chr(13),$content)) {
                            $content = explode(chr(13),$content);
                        }else {
                            $content = file($pathFile);
                        }

                        self::save_temporary_fs($content,IMP_MANAGERS_REPORTERS);
                    }//if_suspicious_path
                }//if_exists
            }else {
                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import FS MANAGERRS REPORTERS - Response null. ' . "\n";
            }//if_fsResponse

            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import FS MANAGERRS REPORTERS . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // log
            $dbLog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import FS Managers Reporters . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_fs_managers_reporters


    /**
     * Description
     * Import all User - Competence JR from fellesdata
     *
     * @param           object  $plugin
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fs_user_competence_jr($plugin) {
        /* Variables    */
        global $CFG;
        $pathFile           = null;
        $content            = null;
        $usersCompetenceJR  = null;
        $dbLog              = null;
        
        try {
            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import FS USER COMPETENCE JR . ' . "\n";
            
            // Call web service
            $usersCompetenceJR = self::process_tradis_service($plugin,TRADIS_FS_USERS_JOBROLES);

            // Import data into temporary tables
            if ($usersCompetenceJR) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_COMPETENCE_JR);

                // Open file
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS_JOBROLES . '.txt';
                if (file_exists($pathFile)) {
                    // Get last changes
                    // First check if is a suspicious file
                    if ($plugin->suspicious_path) {
                        if (!suspicious::check_for_suspicious_data(TRADIS_FS_USERS_JOBROLES,$pathFile)) {
                            // Get content
                            $content = file_get_contents($pathFile);
                            if (strpos(chr(13),$content)) {
                                $content = explode(chr(13),$content);
                            }else {
                                $content = file($pathFile);
                            }

                            self::save_temporary_fs($content,IMP_COMPETENCE_JR);
                        }else {
                            // Mark file as suspicious
                            $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_USERS_JOBROLES,$plugin);

                            // Move file to the right folder
                            copy($pathFile,$suspiciousPath);
                            unlink($pathFile);
                        }//if_suspicious
                    }else {
                        // Get content
                        $content = file_get_contents($pathFile);
                        if (strpos(chr(13),$content)) {
                            $content = explode(chr(13),$content);
                        }else {
                            $content = file($pathFile);
                        }

                        self::save_temporary_fs($content,IMP_COMPETENCE_JR);
                    }//if_suspicious_path
                }//if_exists
            }else {
                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import FS USER COMPETENCE JR - NULL RESPONSE . ' . "\n";
            }//if_data
            
            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINSH Import FS USER COMPETENCE JR . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import FS User Competence . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_fs_user_competence_jr

    /**
     * Description
     * Call Fellesdata Web service to import all data connected with companies, users...
     *
     * @param           $pluginInfo
     * @param           $service
     * @param           $last
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function process_tradis_service($pluginInfo,$service,$last = false) {
        /* Variables    */
        global $CFG;
        $dir            = null;
        $backup         = null;
        $responseFile   = null;
        $pathFile       = null;
        $urlTradis      = null;
        $fromDate       = null;
        $toDate         = null;
        $date           = null;
        $admin          = null;
        
        try {
            // Get parameters service
            $toDate     = mktime(1, 60, 0, date("m"), date("d"), date("Y"));
            $toDate     = gmdate('Y-m-d\TH:i:s\Z',$toDate);

            if ($last) {
                $fromDate = gmdate('Y-m-d\TH:i:s\Z',0);
            }else {
                // No last status
                $admin      = get_admin();
                $date       = usergetdate($pluginInfo->lastexecution, $admin->timezone);
                $fromDate   = mktime(0, 0, 0, $date['mon'], $date['mday']- $pluginInfo->fs_days, $date['year']);
                $fromDate   = gmdate('Y-m-d\TH:i:s\Z',$fromDate);
            }//if_last


            // Build url end point
            $urlTradis = $pluginInfo->fs_point . '/' . $service . '?fromDate=' . $fromDate . '&toDate=' . $toDate;
            $urlTradis = trim($urlTradis);

            // Call web service
            $ch = curl_init($urlTradis);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2 );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt($ch, CURLOPT_POST, false );
            curl_setopt($ch, CURLOPT_USERPWD, $pluginInfo->fs_username . ":" . $pluginInfo->fs_password);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                                        'User-Agent: Moodle 1.0',
                                                        'Content-Type: application/json')
            );

            $response   = curl_exec( $ch );
            curl_close( $ch );

            // Format data
            if ($response === false) {
                return null;
            }else {
                // Check if exists temporary directory
                $dir = $CFG->dataroot . '/fellesdata';
                if (!file_exists($dir)) {
                    mkdir($dir);
                }//if_dir

                $backup = $CFG->dataroot . '/fellesdata/backup';
                if (!file_exists($backup)) {
                    mkdir($backup);
                }//if_backup

                // Clean all response
                $pathFile = $dir . '/' . $service . '.txt';
                if (file_exists($pathFile)) {
                    // Move the file to the new directory
                    copy($pathFile,$backup . '/' . $service . '_' . time() . '.txt');

                    unlink($pathFile);
                }

                $paqui = $dir . '/' . $service . '_PAQUI.txt';
                $responseFile = fopen($paqui,'w');
                fwrite($responseFile,$response);
                fclose($responseFile);
                
                // Create a new response file
                $responseFile = fopen($pathFile,'w');
                // Remove bad characters
                $content = str_replace('\"','"',$response);
                // CR - LF && EOL
                $content = str_replace('\r\n',chr(13),$content);
                $content = str_replace('\r',chr(13),$content);
                $content = str_replace('\n',chr(13),$content);
                fwrite($responseFile,$content);
                fclose($responseFile);

                if (isset($response->error)) {
                    mtrace($response->message);
                    return false;
                }else {
                    return true;
                }
            }//if_response
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//process_tradis_service

    /**
     * Description
     * Synchronization of users accounts between KS and FS
     * Add resource number
     *
     * @param           $plugin
     *
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      23/09/2016
     * @author          eFaktor     (fbv)
     */
    private static function users_fs_synchronization($plugin) {
        /* Variables    */
        global $SESSION,$DB,$CFG;
        $rdo            = null;
        $total          = null;
        $industry       = null;
        $lstusers       = null;
        $rdousers       = null;
        $response       = null;
        $dblog          = null;
        $params         = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // Industry code by default
            $industry = 0;

            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 2;
            }//if_session_manul

            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization Users Accoutns . ' . "\n";

            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_USERS)) {
                // Industry code
                if ($plugin->ks_muni) {
                    $params = array();
                    $params['name']             = $plugin->ks_muni;
                    $params['hierarchylevel']   = 1;
                    $rdo = $DB->get_record('ks_company',$params,'industrycode');
                    if ($rdo) {
                        $industry = trim($rdo->industrycode);
                    }
                }//if_muni

                // Users to synchronize
                $total = $DB->count_records('fs_imp_users',array('imported' => '0'));

                echo "</br> LIMIT --> " . $limit . " TOTAL --> " . $total . "</br>";
                echo "Industry --> " . $industry . "</br>";

                if ($total) {
                    for ($i=0;$i<=$total;$i=$i+$limit) {
                        // Get users accounts
                        list($lstusers,$rdousers) = FSKS_USERS::get_users_accounts($industry,$start,$limit);

                        // Call web service
                        $response = self::process_ks_service($plugin,KS_SYNC_USER_ACCOUNT,array('usersAccounts' => $lstusers));

                        if ($response) {
                            if ($response['error'] == '200') {
                                // Synchronize users accounts FS
                                FSKS_USERS::synchronize_users_fs($rdousers,$response['usersAccounts']);
                            }else {
                                // Log
                                $dblog .= "Error WS: " . $response['message'] . "\n" ."\n";
                            }//if_no_error
                        }//if_response
                    }//for
                }//if_total
            }//if_synchronization

            // Log
            $dblog .= ' FINISH Synchronization Users Accoutns . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog = $ex->getMessage() . "\n" ."\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Synchronization Users Accoutns . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//users_fs_synchronization

    /**
     * Description
     * Synchronization of companies between FS and KS
     *
     * @param           $pluginInfo
     * @param           $fstExecution
     *
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function companies_fs_synchronization($pluginInfo,$fstExecution) {
        /* Variables    */
        global $CFG;
        $toMail         = null;
        $notifyTo       = null;
        $response       = null;
        $dbLog          = null;

        try {
            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Companies FS/KS Synchronization . ' . "\n";

            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_COMPANIES)) {
                // Notifications
                if ($pluginInfo->mail_notification) {
                    $notifyTo   = explode(',',$pluginInfo->mail_notification);
                }//if_mail_notifications

                // First execution
                if ($fstExecution) {
                    // Mail --> manual synchronization
                    if ($notifyTo) {
                        self::send_notifications(SYNC_COMP,null,$notifyTo,$pluginInfo->fs_source);
                    }//if_notify
                }else {
                    // Synchronize new companies
                    self::companies_new_fs_synchronization($pluginInfo);

                    // Synchronize no new companies
                    self::companies_no_new_fs_synchronization($pluginInfo);

                    // Send notifications
                    // Notification manual synchronization
                    if ($notifyTo) {
                        // Get companies to send notifications
                        $toMail = FSKS_COMPANY::get_companiesfs_to_mail();

                        if ($toMail) {
                            self::send_notifications(SYNC_COMP,$toMail,$notifyTo,$pluginInfo->fs_source);
                        }//if_toMail
                    }//if_notify
                }//if_else
            }//if_synchronization

            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Companies FS/KS Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog  = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Companies FS/KS Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//companies_fs_synchronization
    
    /**
     * Description
     * Synchronize companies created as a new from Tardis
     * 
     * @param       Object $plugin
     * 
     * @throws             Exception
     * 
     * @creationDate        17/02/2017
     * @author              eFaktor     (fbv)
     */
    private static function companies_new_fs_synchronization($plugin) {
        /* Variables */
        global $SESSION;
        global $CFG;
        $rdocompanies   = null;
        $toSynchronize  = null;
        $response       = null;
        $dbLog          = null;
        $total          = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 150;
            }//if_session_manul

            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Companies NEW FS/KS Synchronization . ' . "\n";

            // Get total
            $total = FSKS_COMPANY::get_total_new_companiesfs_to_synchronize();

            // Synchronize
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get companies to synchronize
                    list($toSynchronize,$rdocompanies) = FSKS_COMPANY::get_new_companiesfs_to_synchronize($start,$limit);
                    
                    // Call webs service
                    if ($toSynchronize) {
                        $params     = array('companiesFS' => $toSynchronize);
                        $response   = self::process_ks_service($plugin,KS_SYNC_FS_COMPANY,$params);

                        if ($response) {
                            if ($response['error'] == '200') {
                                FSKS_COMPANY::synchronize_companies_ksfs($rdocompanies,$response['companies']);
                            }else {
                                /* Log  */
                                $dbLog  .= "ERROR WS: " . $response['message'] . "\n\n";
                                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Companies NEW FS/KS Synchronization . ' . "\n";
                            }//if_no_error
                        }else {
                            /* Log  */
                            $dbLog  .= "ERROR NUL OBJECT " . "\n\n";
                            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Companies NEW FS/KS Synchronization . ' . "\n";
                        }//if_response
                    }//if_toSynchronize
                }//for
            }//if_total

            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//companies_new_fs_synchronization

    /**
     * Description 
     * Synchronize companies mapped
     * @param           Object  $plugin
     * 
     * @throws                  Exception
     * 
     * @creationDate        17/02/2017
     * @author              eFaktor     (fbv)
     */
    private static function companies_no_new_fs_synchronization($plugin) {
        /* Variables */
        global $SESSION,$CFG;
        $rdocompanies   = null;
        $toSynchronize  = null;
        $response       = null;
        $dbLog          = null;
        $total          = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 150;
            }//if_session_manul

            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Companies NO NEW FS/KS Synchronization . ' . "\n";

            // Get total
            $total = FSKS_COMPANY::get_total_update_companiesfs_to_synchronize();

            // Synchronize
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get companies to synchronize
                    list($toSynchronize,$rdocompanies) = FSKS_COMPANY::get_update_companiesfs_to_synchronize($start,$limit);

                    // Call webs service
                    if ($toSynchronize) {
                        $params     = array('companiesFS' => $toSynchronize);
                        $response   = self::process_ks_service($plugin,KS_SYNC_FS_COMPANY,$params);

                        if ($response) {
                            if ($response['error'] == '200') {
                                FSKS_COMPANY::synchronize_companies_ksfs($rdocompanies,$response['companies']);
                            }else {
                                /* Log  */
                                $dbLog  .= "ERROR WS: " . $response['message'] . "\n\n";
                                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Companies NO NEW FS/KS Synchronization . ' . "\n";
                            }//if_no_error
                        }else {
                            /* Log  */
                            $dbLog  .= "ERROR NUL OBJECT " . "\n\n";
                            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Companies NO NEW FS/KS Synchronization . ' . "\n";
                        }//if_response
                    }//if_toSynchronize
                }//for
            }//if_total

            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//companies_no_new_fs_synchronization

    /**
     * Description
     * Un map organizations between FS & KS
     *
     * @param           $pluginInfo
     * @param           $service
     *
     * @throws          Exception
     *
     * @creationDate    23/11/2016
     * @author          unmap companies
     */
    private static function unmap_organizations($pluginInfo,$service) {
        /* Variables */
        global $CFG;
        $toUnMap    = null;
        $response   = null;
        $dbLog      = null;

        try {
            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Unmap FS/KS Companies . ' . "\n";

            // Comapnies to unmap
            $toUnMap = FSKS_COMPANY::companies_to_unmap();

            if ($toUnMap) {
                // Call web service
                if ($toUnMap) {
                    $response = self::process_ks_service($pluginInfo,$service,array('toUnMap' => $toUnMap));
                    if ($response['error'] == '200') {
                        FSKS_COMPANY::unmap_companies_ksfs($response['orgUnMapped']);
                    }else {
                        // Log
                        $dbLog  .= "ERROR WS: " . $response['error'] . "\n\n";
                        $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Unmap FS/KS Companies . ' . "\n";
                    }//if_no_error
                }//if_toSynchronize
            }//if_toUnMap

            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Unmap FS/KS Companies . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog  = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Unmap FS/KS Companies . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//unmap_organizations

    /**
     * Description
     * Check if there are new job roles that have to be mapped and send the notifications
     *
     * @param           $pluginInfo
     * 
     * @throws          Exception
     * 
     * @creationDate    03/11/2016
     * @author          eFaktor     (fbv)
     */
    private static function jobroles_fs_to_map($pluginInfo) {
        /* Variables    */
        global $CFG;
        $toMail         = null;
        $dbLog          = null;
        $notifyTo       = null;

        try {
            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Jobroles FS to Map - Mailing . ' . "\n";

            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_JOBROLES)) {
                // Notifications
                if ($pluginInfo->mail_notification) {
                    $notifyTo   = explode(',',$pluginInfo->mail_notification);
                }//if_mail_notifications

                // Send notifications
                if ($notifyTo) {
                    // Jobroles to map
                    $toMail = FSKS_JOBROLES::jobroles_fs_tosynchronize_mailing();
                    if ($toMail) {
                        self::send_notifications(SYNC_JR,$toMail,$notifyTo,$pluginInfo->fs_source);
                    }else {
                        $dbLog .= "None JR to map " . "\n";
                    }//If_toMail
                }else {
                    // No jobroles to map
                    $dbLog .= " JR - No One to notify " . "\n";
                }//if_notigyTo
            }//if_synchronization
            
            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Jobroles FS to Map - Mailing . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog  = $ex->getTraceAsString() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Jobroles FS to Map - Mailing . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//jobroles_fs_to_map

    /**
     * Description
     * Synchronization User Competence
     *
     * @param                   $pluginInfo
     * @param                $service
     * @param           bool $toDelete
     * @param           bool $status
     *
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     */
    private static function user_competence_synchronization($pluginInfo,$service,$toDelete = false,$status = false) {
        /* Variables    */
        global $SESSION,$CFG;
        $toSynchronize  = null;
        $response       = null;
        $dbLog          = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 150;
            }//if_session_manul

            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_COMPETENCE_JR)) {
                // User competence to synchronize
                $total = FSKS_USERS::get_total_users_competence_to_synchronize($toDelete,$status);
                if ($total) {
                    for ($i=0;$i<=$total;$i=$i+$limit) {
                        list($competence,$rdocompetence) = FSKS_USERS::user_competence_to_synchronize($toDelete,$status,$start,$limit);

                        // Call web service
                        if ($competence) {
                            // Params web service
                            $params = array();
                            $params['usersCompetence'] = $competence;
                            $response = self::process_ks_service($pluginInfo,$service,$params);
                            if ($response['error'] == '200') {
                                // Synchronize user competence
                                FSKS_USERS::synchronize_user_competence_fs($rdocompetence,$response['usersCompetence']);
                            }else {
                                // Log
                                $dbLog  = "ERROR WS: " . $response['message'] . "\n" . "\n";
                                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR User Competence Synchronization . ' . "\n";
                                error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
                            }//if_no_error
                        }//if_toSynchronize
                    }//for_rdo
                }//if_totla
            }//if_synchronization

            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish User Competence Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog  = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR User Competence Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//user_competence_synchronization

    /**
     * Description
     * Unmap competence (company) from the users
     *
     * @param           $pluginInfo
     * @param           $service
     *
     * @throws          Exception
     *
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     */
    private static function unmap_user_competence($pluginInfo,$service) {
        /* Variables */
        global $SESSION,$CFG;
        $toUnMap    = null;
        $response   = null;
        $dbLog      = null;
        $start      = 0;
        $limit      = 1000;
        
        try {
            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 150;
            }//if_session_manul

            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false) . " Start UNAMP User Competence Synchronization. " . "\n\n";
            
            // Competence to unmap
            $total = FSKS_USERS::get_total_users_competence_to_unmap();
            if ($total) {
                // Get users competence that have to be unmapped
                for ($i=0;$i<=$total;$i=$i+100) {
                    $toUnMap = FSKS_USERS::user_competence_to_unmap($start,$limit);
                    
                    // Call web service
                    if ($toUnMap) {
                        $response = self::process_ks_service($pluginInfo,$service,array('usersUnMapCompetence' => $toUnMap));
                        if ($response['error'] == '200') {
                            // Unmap user competence
                            FSKS_USERS::unmap_user_competence_fs($toUnMap,$response['usersUnMapped']);
                        }else {
                            // Log
                            $dbLog  = "ERROR WS: " . $response['message'] . "\n" . "\n";
                            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR UNMAP User Competence Synchronization . ' . "\n";
                        }//if_no_error
                    }//if_toSynchronize
                }//for
            }//if_total

            // Log
            $dbLog .= " FINISH UNAMP User Competence Synchronization. " . "\n\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog  = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR UNMAP User Competence Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
            
            throw $ex;
        }//try_catch
    }//unmap_user_competence
    
    /**
     * Description
     * Synchronization Managers Reporters between Fellesdata and KS
     *
     * @param           $pluginInfo
     * @param           $service
     *
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     */
    private static function manager_reporter_synchronization($pluginInfo,$service) {
        /* Variables    */
        global $SESSION,$CFG;
        $toSynchronize  = null;
        $rdomanagers    = null;
        $response       = null;
        $dbLog          = null;
        $total          = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 150;
            }//if_session_manul

            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Manager Reporter Synchronization . ' . "\n";

            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_MANAGERS_REPORTERS)) {
                // Managers and reporters to synchronize
                $total = FSKS_USERS::get_total_managers_reporters_to_synchronize();
                if ($total) {
                    for ($i=0;$i<=$total;$i=$i+500) {
                        // To synchronize
                        list($toSynchronize,$rdomanagers) = FSKS_USERS::get_managers_reporters_to_synchronize($start,$limit);

                        // Call webs ervice
                        if ($toSynchronize) {
                            $response = self::process_ks_service($pluginInfo,$service,array('managerReporter' => $toSynchronize));
                            if ($response['error'] == '200') {
                                // Syncrhonize managers and reporters
                                FSKS_USERS::synchronize_manager_reporter_fs($rdomanagers,$response['managerReporter']);
                            }else {
                                // Log
                                $dbLog  .= "ERROR WS: " . $response['message'] . "\n" . "\n";
                                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Manaer Reporter Synchronization . ' . "\n";
                            }//if_no_error
                        }//if_toSynchronize
                    }//for
                }//if_total
            }//if_synchronization

            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Manager Reporter Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog  = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Manager Reporter Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//manager_reporter_synchronization

    /**
     * Description
     * Unmap manager/reporter form the company
     *
     * @param           $pluginInfo
     * @param           $service
     * 
     * @throws          Exception
     * 
     * @creationDate    23/11/2016
     * @author          eFaktor     (fbv)
     */
    private static function unmap_managers_reporters($pluginInfo,$service) {
        /* Variables    */
        global $SESSION,$CFG;
        $toUnMap  = null;
        $response = null;
        $dbLog    = null;
        $total    = null;
        $start    = 0;
        $limit    = 1000;

        try {
            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 150;
            }//if_session_manul

            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START UnMap Manager/Reporter . ' . "\n";

            // Managers and reporters to unmap
            $total = FSKS_USERS::get_total_managers_reporters_to_unmap();
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+100) {
                    // Managers and reporters to unmap
                    $toUnMap = FSKS_USERS::get_managers_reporters_to_unmap($start,$limit);

                    // Call web service
                    if ($toUnMap) {
                        $response = self::process_ks_service($pluginInfo,$service,array('managerReporter' => $toUnMap));
                        if ($response['error'] == '200') {
                            // Synchronize managers and reporters
                            FSKS_USERS::unmap_manager_reporter_fs($toUnMap,$response['managerReporter']);
                        }else {
                            // Log
                            $dbLog  .= "ERROR WS: " . $response['message'] . "\n" . "\n";
                            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Manaer Reporter Synchronization . ' . "\n";
                        }//if_no_error
                    }//if_toSynchronize
                }//for_total
            }//if_total
            
            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH UnMap Manager/Reporter . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog  = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR UnMap Manager Reporter . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//unmap_managers_reporters

    /*******************/
    /* Extra Functions */
    /*******************/

    /**
     * @param           $type
     * @param           $toMail
     * @param           $notifyTo
     * @param           $source
     *
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send notifications
     */
    private static function send_notifications($type,$toMail,$notifyTo,$source) {
        /* Variables    */
        global $USER,$SITE,$CFG;
        $urlMapping = null;
        $subject    = null;
        $body       = null;
        $info       = null;
        $to         = null;

        try {
            // Subject
            $subject = (string)new lang_string('subject','local_fellesdata',$SITE->shortname,$USER->lang);

            // Body to sent
            $info = new stdClass();
            switch ($type) {
                case SYNC_COMP:
                    // url mapping
                    $urlMapping = new moodle_url('/local/fellesdata/mapping/mapping_org.php');

                    if ($toMail) {
                        $info->companies = implode(',',$toMail);
                    }else {
                        $info->companies = null;
                    }//if_ToMail

                    $urlMapping->param('m','co');
                    $info->mapping  = $urlMapping;

                    $body = (string)new lang_string('body_company_to_sync','local_fellesdata',$info,$USER->lang);

                    break;
                
                case SYNC_JR:
                    $info->jobroles = implode(',',$toMail);
                    
                    $urlMapping = new moodle_url('/local/fellesdata/mapping/jobroles.php');
                    $info->mapping  = $urlMapping;

                    $body = (string)new lang_string('body_jr_to_sync','local_fellesdata',$info,$USER->lang);

                    break;
            }//type

            // send
            foreach ($notifyTo as $to) {
                $USER->email    = $to;
                email_to_user($USER, $SITE->shortname, $subject, $body,$body);
            }//for_Each
        }catch (Exception $ex) {
            // Log
            $dbLog  = $ex->getMessage() . "\n\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Send Notification . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
            throw $ex;
        }//try_catch
    }//send_notifications
}//Fellesdata_cron

