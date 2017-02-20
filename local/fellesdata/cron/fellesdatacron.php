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

    public static function cron_ok($plugin,$fstExecution) {
        /* Variables    */
        global $CFG;
        $dbLog              = null;
        $suspicious_path    = null;

        try {
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
                self::unmapping($plugin,$dbLog);
            }//fstExecution_tounmap

            // Import KS
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Import KS. ' . "\n";
            self::import_ks($plugin);

            // Import fellesdata
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Import Fellesdata. ' . "\n";
            self::import_fellesdata($plugin,$fstExecution);

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
                self::competence_synchronization($plugin,$dbLog);
            }

            /* Log  */
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA CRON . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//cron

    public static function cron_test($plugin,$fstExecution) {
        /* Variables */
        global $CFG;
        
        try {
            $last = self::get_last_status($plugin,$fstExecution);

            if ($last) {
                FS::backup_temporary_fellesdata();
                
                // Ask for the last status
                self::import_fs_users($plugin,true);

                // Import FS Companies
                //self::import_fs_orgstructure($plugin,true);

                // Import FS Job roles
                //self::import_fs_jobroles($plugin,true);

                // Import FS User Competence
                //self::import_fs_managers_reporters($plugin,true);

                // Import FS User Competence JR
                //self::import_fs_user_competence_jr($plugin,true);
            }

        }catch (Exception $ex) {
            throw $ex;
        }
    }//cron_test


    /* MANUAL EXECUTION */
    public static function cron_manual($fstExecution,$option) {
        /* Variables    */
        $pluginInfo = null;

        try {
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
     * Check if it has to ask for the last status or not
     *
     * @param       object  $plugin
     * @param       bool    $fstExecution
     *
     * @return              bool|null
     * @throws              Exception
     *
     * @creationDate        20/02/2017
     * @author              eFaktor     (fbv)
     */
    private static function get_last_status($plugin,$fstExecution) {
        /* Variables */
        global $CFG;
        $dbLog      = null;
        $laststatus = null;
        $time       = null;
        $calendar   = null;

        try {
            // Calendar
            $calendar = array();
            $calendar[0] = new lang_string('sunday', 'calendar');
            $calendar[1] = new lang_string('monday', 'calendar');
            $calendar[2] = new lang_string('tuesday', 'calendar');
            $calendar[3] = new lang_string('wednesday', 'calendar');
            $calendar[4] = new lang_string('thursday', 'calendar');
            $calendar[5] = new lang_string('friday', 'calendar');
            $calendar[6] = new lang_string('saturday', 'calendar');

            // Local time
            $time   = time();
            $today  = getdate($time);

            // Check first execution
            if ($fstExecution) {
                $laststatus = true;
            }else {
                if (!$plugin->nextstatus) {
                    $laststatus = true;
                }else {
                    echo "DAY TO RUN : " . $calendar[$plugin->fs_calendar_status] . "</br>";
                    echo "TIME: " . userdate($time,'%d.%m.%Y', 99, false) . "</br>";
                    echo "LAST: " . userdate($plugin->laststatus,'%d.%m.%Y', 99, false) . "</br>";
                    echo "NEXT: " . userdate($plugin->nextstatus,'%d.%m.%Y', 99, false) . "</br>";
                    if ($today['weekday'] == $calendar[$plugin->fs_calendar_status]) {
                        $laststatus = true;
                    }else {
                        if (($plugin->laststatus < $time) && ($time > $plugin->nextstatus)) {
                            $laststatus = true;
                        }else {
                            $laststatus = false;
                        }
                    }
                }
            }//if_fstExecution

            return $laststatus;
        }catch (Exception $ex) {
            // Log
            $dbLog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Fellesdata CRON get_last_status . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//get_last_status

    /**
     * Description
     * Unmap process
     * - Unmap user compentence
     * - Unmap managers reportes
     * - Unmap organizations
     *
     * @param       object  $plugin
     * @param       String  $dbLog
     *
     * @throws              Exception
     *
     * @creationDate        20/02/2017
     * @author              eFaktor     (fbv)
     */
    private static function unmapping($plugin,&$dbLog) {
        /* Variables */

        try {
            // Unmap user competence
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START  UNMAP User competence. ' . "\n";
            self::unmap_user_competence($plugin,KS_UNMAP_USER_COMPETENCE);

            // Unmap managers
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START  UNMAP Manager/Reporter. ' . "\n";
            self::unmap_managers_reporters($plugin,KS_MANAGER_REPORTER);

            // Unmap organizations
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START  UNMAP Organizations. ' . "\n";
            self::unmap_organizations($plugin,KS_UNMAP_COMPANY);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//unmapping

    /**
     * Description
     * Competence synchornization
     *
     * @param       object  $plugin
     * @param       String  $dbLog
     *
     * @throws              Exception
     *
     * @creationDate        20/02/2017
     * @author              eFaktor     (fbv)
     */
    private static function competence_synchronization($plugin,&$dbLog) {
        /* Variables */

        try {
            // Synchronization Managers && Reporters
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START  Managers/Reporters FS Synchronization. ' . "\n";
            self::manager_reporter_synchronization($plugin,KS_MANAGER_REPORTER);

            // Synchronization User Competence JobRole  -- Add/Update
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Users competence FS Synchronization. ' . "\n";
            self::user_competence_synchronization($plugin,KS_USER_COMPETENCE);

            // Synchronization User Competence JobRole  -- Delete
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Users Competence to delete FS Synchronization. ' . "\n";
            self::user_competence_synchronization($plugin,KS_USER_COMPETENCE,true);
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
            $infoLevel->notIn     = KS::existing_companies();

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
                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Fellesdata CRON KS Job Roles . ' . "\n";
                error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
            }//if_no_error

            // Jobroles no generics
            $hierarchy  = KS::get_hierarchy_jr($pluginInfo->ks_muni);
            $notIn      = KS::existing_jobroles(false,$hierarchy);

            // Params web service
            $infoLevel = new stdClass();
            $infoLevel->notIn   = $notIn;
            $infoLevel->top     = $hierarchy;

            // Call web service
            $params = array('hierarchy' => $infoLevel);
            $response = self::process_ks_service($pluginInfo,KS_JOBROLES,$params);

            // Import jobroles no generics
            if ($response['error'] == '200') {
                KS::ks_jobroles($response['jobroles']);
            }else {
                // Log
                $dbLog = "ERROR: " . $response['message'] . "\n" . "\n";
                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Fellesdata CRON KS Job Roles . ' . "\n";
                error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
            }//if_no_error
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
     * Import data from fellesdata
     *
     * @param           $pluginInfo
     * @param           $status
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fellesdata($pluginInfo,$status = false) {
        /* Variables    */
        global $CFG;
        $dbLog        = null;

        try {
            // Import FS Users
            self::import_fs_users($pluginInfo,$status);

            // Import FS Companies
            self::import_fs_orgstructure($pluginInfo,$status);

            // Import FS Job roles
            self::import_fs_jobroles($pluginInfo,$status);

            // Import FS User Competence
            self::import_fs_managers_reporters($pluginInfo,$status);

            // Import FS User Competence JR
            self::import_fs_user_competence_jr($pluginInfo,$status);

            // Send suspicious notifications
            if (!$status) {
                if ($pluginInfo->suspicious_path) {
                    // Send Notifications
                    suspicious::send_suspicious_notifications($pluginInfo);
                    // Send Reminder
                    suspicious::send_suspicious_notifications($pluginInfo,true);
                }//suspicious_path
            }//if_status
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
     * @param           boolean $status If it has to get the last status
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fs_users($plugin,$status = false) {
        /* Variables    */
        global $CFG;
        $pathFile       = null;
        $suspiciousPath = null;
        $content        = null;
        $fsUsers        = null;
        $dbLog          = null;
        
        try {
            // Call web service
            $fsResponse = self::process_tradis_service($plugin,TRADIS_FS_USERS,$status);

            // Import data into temporary tables
            if ($fsResponse) {
                // Open file
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS . '.txt';
                if (file_exists($pathFile)) {
                    if ($status) {
                        // Get last status
                        // Get content
                        $content = file($pathFile);

                        FS::save_temporary_fellesdata($content,IMP_USERS,$status);
                    }else {
                        // Get last changes
                        // First check if is a suspicious file
                        if ($plugin->suspicious_path) {
                            if (!suspicious::check_for_suspicious_data(TRADIS_FS_USERS,$pathFile)) {
                                // Get content
                                $content = file($pathFile);

                                FS::save_temporary_fellesdata($content,IMP_USERS);
                            }else {
                                // Mark file as suspicious
                                $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_USERS,$plugin);

                                // Move file to the right folder
                                copy($pathFile,$suspiciousPath);
                                unlink($pathFile);
                            }//if_suspicious
                        }else {
                            // Get content
                            $content = file($pathFile);

                            FS::save_temporary_fellesdata($content,IMP_USERS);
                        }
                    }//if_status
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
     * @param           boolean $status
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fs_orgstructure($plugin,$status = false) {
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
            $fsResponse = self::process_tradis_service($plugin,TRADIS_FS_COMPANIES,$status);

            // Import data into temporary tables
            if ($fsResponse) {
                // Open file
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_COMPANIES . '.txt';
                if (file_exists($pathFile)) {
                    if ($status) {
                        // Get last status
                        // Get content
                        //$content = file($pathFile);

                        //FS::save_temporary_fellesdata($content,IMP_COMPANIES);
                    }else {
                        // Get last changes
                        // First check if is a suspicious file
                        if ($plugin->suspicious_path) {
                            if (!suspicious::check_for_suspicious_data(TRADIS_FS_COMPANIES,$pathFile)) {
                                // Get content
                                $content = file($pathFile);

                                FS::save_temporary_fellesdata($content,IMP_COMPANIES);
                            }else {
                                // Mark file as suspicious
                                $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_COMPANIES,$plugin);

                                // Move file to the right folder
                                copy($pathFile,$suspiciousPath);
                                unlink($pathFile);
                            }//if_suspicious
                        }else {
                            // Get content
                            $content = file($pathFile);

                            FS::save_temporary_fellesdata($content,IMP_COMPANIES);
                        }///if_suspicous_path
                    }//if_status
                }//if_exists
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
     * @param           boolean $status
     *
     * @throws          Exception
     *
     * @creationDate    04/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fs_jobroles($plugin,$status = false) {
        /* Variables    */
        global $CFG;
        $pathFile   = null;
        $content    = null;
        $fsResponse = null;
        $dbLog      = null;
        
        try {
            // Call web service
            $fsResponse = self::process_tradis_service($plugin,TRADIS_FS_JOBROLES,$status);

            // Import data into temporary tables
            if ($fsResponse) {
                // Open file
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_JOBROLES . '.txt';
                if (file_exists($pathFile)) {
                    if ($status) {
                        //Get last status
                        // Get content
                        //$content = file($pathFile);

                        //FS::save_temporary_fellesdata($content,IMP_JOBROLES);
                    }else {
                        //Get last changes
                        // First check if is a suspicious file
                        if ($plugin->suspicious_path) {
                            if (!suspicious::check_for_suspicious_data(TRADIS_FS_JOBROLES,$pathFile)) {
                                // Get content
                                $content = file($pathFile);

                                FS::save_temporary_fellesdata($content,IMP_JOBROLES);
                            }else {
                                // Mark file as suspicious
                                $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_JOBROLES,$plugin);

                                // Move file to the right folder
                                copy($pathFile,$suspiciousPath);
                                unlink($pathFile);
                            }//if_suspicious
                        }else {
                            // Get content
                            $content = file($pathFile);

                            FS::save_temporary_fellesdata($content,IMP_JOBROLES);
                        }//if_suspicious_path
                    }//if_status
                }//if_exists
            }//if_fsResponse
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
     * @param           boolean $status
     *
     * @throws          Exception
     *
     * @creationDate    13/06/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fs_managers_reporters($plugin,$status = false) {
        /* Variables    */
        global $CFG;
        $pathFile               = null;
        $content                = null;
        $fsManagersReporters    = null;
        $dbLog                  = null;
        
        try {
            // Call web service
            $fsManagersReporters = self::process_tradis_service($plugin,TRADIS_FS_MANAGERS_REPORTERS,$status);

            // Import data into temporary tables
            if ($fsManagersReporters) {
                // Open file
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_MANAGERS_REPORTERS . '.txt';
                if (file_exists($pathFile)) {
                    if ($status) {
                        // Get last status
                        // Get content
                        //$content = file($pathFile);

                        //FS::save_temporary_fellesdata($content,IMP_MANAGERS_REPORTERS);
                    }else {
                        // Get last changes
                        // First check if is a suspicious file
                        if ($plugin->suspicious_path) {
                            if (!suspicious::check_for_suspicious_data(TRADIS_FS_MANAGERS_REPORTERS,$pathFile)) {
                                // Get content
                                $content = file($pathFile);

                                FS::save_temporary_fellesdata($content,IMP_MANAGERS_REPORTERS);
                            }else {
                                // Mark file as suspicious
                                $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_MANAGERS_REPORTERS,$plugin);

                                // Move file to the right folder
                                copy($pathFile,$suspiciousPath);
                                unlink($pathFile);
                            }//if_suspicious
                        }else {
                            // Get content
                            $content = file($pathFile);

                            FS::save_temporary_fellesdata($content,IMP_MANAGERS_REPORTERS);
                        }//if_suspicious_path                        
                    }//if_status
                }//if_exists
            }//if_fsResponse
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
     * @param           boolean $status
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fs_user_competence_jr($plugin,$status = false) {
        /* Variables    */
        global $CFG;
        $pathFile           = null;
        $content            = null;
        $usersCompetenceJR  = null;
        $dbLog              = null;
        
        try {
            // Call web service
            $usersCompetenceJR = self::process_tradis_service($plugin,TRADIS_FS_USERS_JOBROLES,$status);

            // Import data into temporary tables
            if ($usersCompetenceJR) {
                // Open file
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS_JOBROLES . '.txt';
                if (file_exists($pathFile)) {
                    if ($status) {
                        // Get last status
                        // Get content
                        //$content = file($pathFile);

                        //FS::save_temporary_fellesdata($content,IMP_COMPETENCE_JR);
                    }else {
                        // Get last changes
                        // First check if is a suspicious file
                        if ($plugin->suspicious_path) {
                            if (!suspicious::check_for_suspicious_data(TRADIS_FS_USERS_JOBROLES,$pathFile)) {
                                // Get content
                                $content = file($pathFile);

                                FS::save_temporary_fellesdata($content,IMP_COMPETENCE_JR);
                            }else {
                                // Mark file as suspicious
                                $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_USERS_JOBROLES,$plugin);

                                // Move file to the right folder
                                copy($pathFile,$suspiciousPath);
                                unlink($pathFile);
                            }//if_suspicious
                        }else {
                            // Get content
                            $content = file($pathFile);

                            FS::save_temporary_fellesdata($content,IMP_COMPETENCE_JR);
                        }//if_suspicious_path
                    }//if_status
                }//if_exists
            }//if_data
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

                // Create a new response file
                $responseFile = fopen($pathFile,'w');
                fwrite($responseFile,$response);
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
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      23/09/2016
     * @author          eFaktor     (fbv)
     */
    private static function users_fs_synchronization($pluginInfo) {
        /* Variables    */
        global $DB,$CFG;
        $rdo            = null;
        $total          = null;
        $usersFS        = null;
        $lstUsersFS     = null;
        $infoUser       = null;
        $response       = null;
        $dbLog          = null;
        $rdoIC          = null;
        $industryCode   = null;
        $params         = null;
        $start          = 0;
        $limit          = 100;

        try {
            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization Users Accoutns . ' . "\n";

            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_USERS)) {
                // Industry code
                if ($pluginInfo->ks_muni) {
                    $params = array();
                    $params['name']             = $pluginInfo->ks_muni;
                    $params['hierarchylevel']   = 1;
                    $rdoIC = $DB->get_record('ks_company',$params,'industrycode');

                    if ($rdoIC) {
                        $industryCode = trim($rdoIC->industrycode);
                    }
                }else {
                    $industryCode = 0;
                }//if_muni

                // Users to synchronize
                $total = $DB->count_records('fs_imp_users',array('imported' => '0'));
                if ($total) {
                    for ($i=0;$i<=$total;$i=$i+100) {
                        $rdo = $DB->get_records('fs_imp_users',array('imported' => '0'),'','*',$start,$limit);

                        // Prepare data
                        if ($rdo) {
                            $usersFS    = array();
                            $lstUsersFS = null;

                            foreach ($rdo as $instance) {
                                // User account info
                                $infoUser = new stdClass();
                                $infoUser->id               = $instance->id;
                                $infoUser->personalnumber   = trim($instance->fodselsnr);
                                $infoUser->adfs             = trim(($instance->brukernavn ? $instance->brukernavn : 0));
                                $infoUser->ressursnr        = trim(($instance->ressursnr ? $instance->ressursnr : 0));
                                $infoUser->industry         = $industryCode;
                                $infoUser->firstname        = trim($instance->fornavn) . ' ' . trim($instance->mellomnavn);
                                $infoUser->lastname         = trim($instance->etternavn);
                                $infoUser->email            = trim($instance->epost);
                                $infoUser->action           = trim($instance->action);

                                // add user
                                $usersFS[$instance->id] = $infoUser;

                                $lstUsersFS .= json_encode($infoUser) . "\n";
                            }//for_rdo

                            // Call web service
                            $response = self::process_ks_service($pluginInfo,KS_SYNC_USER_ACCOUNT,array('usersAccounts' => $lstUsersFS));

                            if ($response['error'] == '200') {
                                // Synchornize users accounts FS
                                FSKS_USERS::synchronize_users_fs($usersFS,$response['usersAccounts']);

                                /* Clean Table*/
                                //$DB->delete_records('fs_imp_users',array('imported' => '1'));
                            }else {
                                // Log
                                $dbLog .= "Error WS: " . $response['message'] . "\n" ."\n";
                                $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Synchronization Users Accoutns . ' . "\n";
                            }//if_no_error
                        }//if_Rdo
                    }
                }//if_total
            }//if_synchronization

            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization Users Accoutns . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog = $ex->getMessage() . "\n" ."\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Synchronization Users Accoutns . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

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
        $toSynchronize  = null;
        $synchronizeFS  = null;
        $toMail         = null;
        $notifyTo       = null;
        $response       = null;
        $dbLog          = null;

        try {
            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Companies FS/KS Synchronization . ' . "\n";

            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_COMPANIES)) {
                // Synchronize new companies
                self::companies_new_fs_synchronization($pluginInfo);

                // Synchronize no new companies
                self::companies_no_new_fs_synchronization($pluginInfo);

                /* Clean Table*/
                //$DB->delete_records('fs_imp_company',array('imported' => '1'));
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
        global $CFG;
        $toSynchronize  = null;
        $response       = null;
        $dbLog          = null;
        $total          = null;
        $start          = 0;
        $limit          = 50;

        try {
            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Companies NEW FS/KS Synchronization . ' . "\n";

            // Get total
            $total = FSKS_COMPANY::get_total_new_companiesfs_to_synchronize();

            // Synchronize
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get companies to synchronize
                    $toSynchronize = FSKS_COMPANY::get_new_companiesfs_to_synchronize($start,$limit);
                    
                    // Call webs service
                    if ($toSynchronize) {
                        $params     = array('companiesFS' => $toSynchronize);
                        $response   = self::process_ks_service($plugin,KS_SYNC_FS_COMPANY,$params);

                        if ($response) {
                            if ($response['error'] == '200') {
                                FSKS_COMPANY::synchronize_companies_ksfs($toSynchronize,$response['companies']);
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
        global $CFG;
        $toSynchronize  = null;
        $response       = null;
        $dbLog          = null;
        $total          = null;
        $start          = 0;
        $limit          = 50;

        try {
            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' START Companies NO NEW FS/KS Synchronization . ' . "\n";

            // Get total
            $total = FSKS_COMPANY::get_total_update_companiesfs_to_synchronize();

            // Synchronize
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get companies to synchronize
                    $toSynchronize = FSKS_COMPANY::get_update_companiesfs_to_synchronize($start,$limit);

                    // Call webs service
                    if ($toSynchronize) {
                        $params     = array('companiesFS' => $toSynchronize);
                        $response   = self::process_ks_service($plugin,KS_SYNC_FS_COMPANY,$params);

                        if ($response) {
                            if ($response['error'] == '200') {
                                FSKS_COMPANY::synchronize_companies_ksfs($toSynchronize,$response['companies']);
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
     * @param           $pluginInfo
     * @param           $service
     * @param           bool $toDelete
     *
     * @throws          Exception
     *
     * @creationDate    14/06/2016
     * @author          eFaktor     (fbv)
     */
    private static function user_competence_synchronization($pluginInfo,$service,$toDelete = false) {
        /* Variables    */
        global $CFG;
        $toSynchronize  = null;
        $response       = null;
        $dbLog          = null;
        $start          = 0;
        $limit          = 100;

        try {
            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_COMPETENCE_JR)) {
                // User competence to synchronize
                $total = FSKS_USERS::get_total_users_competence_to_synchronize($toDelete);
                if ($total) {
                    for ($i=0;$i<=$total;$i=$i+100) {
                        $toSynchronize = FSKS_USERS::user_competence_to_synchronize($toDelete,$start,$limit);

                        // Call web service
                        if ($toSynchronize) {
                            // Params web service
                            $params = array();
                            $params['usersCompetence'] = $toSynchronize;
                            $response = self::process_ks_service($pluginInfo,$service,$params);
                            if ($response['error'] == '200') {
                                // Synchronize user competence
                                FSKS_USERS::synchronize_user_competence_fs($toSynchronize,$response['usersCompetence']);

                                //$DB->delete_records('fs_users_competence',array('imported' => '1'));
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
        global $CFG;
        $toUnMap    = null;
        $response   = null;
        $dbLog      = null;
        $start      = 0;
        $limit      = 100;
        
        try {
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
        global $CFG;
        $toSynchronize  = null;
        $response       = null;
        $dbLog          = null;
        $total          = null;
        $start          = 0;
        $limit          = 100;

        try {
            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Manager Reporter Synchronization . ' . "\n";

            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_MANAGERS_REPORTERS)) {
                // Managers and reporters to synchronize
                $total = FSKS_USERS::get_total_managers_reporters_to_synchronize();
                if ($total) {
                    for ($i=0;$i<=$total;$i=$i+100) {
                        // To synchronize
                        $toSynchronize = FSKS_USERS::get_managers_reporters_to_synchronize($start,$limit);

                        // Call webs ervice
                        if ($toSynchronize) {
                            $response = self::process_ks_service($pluginInfo,$service,array('managerReporter' => $toSynchronize));
                            if ($response['error'] == '200') {
                                // Syncrhonize managers and reporters
                                FSKS_USERS::synchronize_manager_reporter_fs($toSynchronize,$response['managerReporter']);

                                //$DB->delete_records('fs_imp_managers_reporters',array('imported' => '1'));
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
        global $CFG;
        $toUnMap  = null;
        $response = null;
        $dbLog    = null;
        $total    = null;
        $start    = 0;
        $limit    = 100;

        try {
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

