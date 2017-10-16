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
    protected static $log           = null;

    /**********/
    /* PUBLIC */
    /**********/

    public static function cron($plugin,$fstExecution) {
        /* Variables    */
        global $SESSION;
        global $CFG;
        $suspicious_path    = null;

        try {
            unset($SESSION->manual);

            // Start log
            self::$log    =    array();

            // Log
            $infolog = new stdClass();
            $infolog->action      = 'Cron ' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description = 'START Fellesdata Cron';
            // Add log
            self::$log[] = $infolog;

            // Suspicious data
            $suspicious_path = $CFG->dataroot . '/' . $plugin->suspicious_path;
            if ($suspicious_path) {
                if (!file_exists($suspicious_path)) {
                    mkdir($suspicious_path);
                }
            }//if_suspucuous_path

            // Unmap process
            if (!$fstExecution) {
                self::unmap_organizations($plugin,KS_UNMAP_COMPANY);
            }//fstExecution_tounmap

            // Import KS
            self::import_ks($plugin);

            // Import fellesdata
            self::import_fellesdata($plugin);

            // Write log
            FS_CRON::write_fellesdata_log(self::$log);
            // Start log
            self::$log    =    array();

            // Users accounts synchornization
            self::users_fs_synchronization($plugin);

            // Write log
            FS_CRON::write_fellesdata_log(self::$log);
            // Start log
            self::$log    =    array();

            // Companies synchornization
            if (self::companies_fs_synchronization($plugin,false)) {
                // Send notifications
                $notifyTo = null;
                // Notifications
                if ($plugin->mail_notification) {
                    $notifyTo   = explode(',',$plugin->mail_notification);
                }//if_mail_notifications

                // Notification manual synchronization
                if (!$plugin->automatic) {
                    if ($notifyTo) {
                        // Get companies to send notifications
                        $toMail = array();
                        FSKS_COMPANY::get_companiesfs_to_mail(2,$toMail);
                        FSKS_COMPANY::get_companiesfs_to_mail(3,$toMail);

                        if ($toMail) {
                            self::send_notifications(SYNC_COMP,$toMail,$notifyTo,$plugin->fs_source);

                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'companies_fs_synchronization';
                            $infolog->description 	= 'Send notifications';
                            // Add log
                            self::$log[] = $infolog;
                        }//if_toMail
                    }//if_notify
                }//if_automatic
            }

            // Write log
            FS_CRON::write_fellesdata_log(self::$log);
            // Start log
            self::$log    =    array();

            // Job roles to map
            self::jobroles_fs_to_map($plugin);

            // Competence synchronization
            if (!$fstExecution) {
                self::competence_synchronization($plugin);
            }//if_fstExecution_competence

            // Write log
            FS_CRON::write_fellesdata_log(self::$log);
            // Start log
            self::$log    =    array();

            // Log
            $infolog = new stdClass();
            $infolog->action      = 'Cron ' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description = 'FINSIH Fellesdata Cron';
            // Add log
            self::$log[] = $infolog;

            // Write log
            FS_CRON::write_fellesdata_log(self::$log);
        }catch (Exception $ex) {
            // Send error notification
            FS_CRON::send_notification_error_process($plugin,'TARDIS');
            FS_CRON::deactivate_cron('fellesdata');

            // Log
            $infolog = new stdClass();
            $infolog->action      = 'ERROR Cron ' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description = 'FINISH ERROR: ';
            $infolog->description .= $ex->getTraceAsString();
            // Add log
            self::$log[] = $infolog;

            // Write log
            FS_CRON::write_fellesdata_log(self::$log);

            throw $ex;
        }//try_catch
    }//cron


    /* MANUAL EXECUTION */
    public static function cron_manual($fstExecution,$option) {
        /* Variables    */
        global $SESSION;
        $pluginInfo     = null;
        $infolog        = null;
        $toMail         = null;

        try {
            // Start log
            self::$log    =    array();

            // Action Log
            $infolog = new stdClass();
            $infolog->action      = 'Cron Manual ' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description = 'START Fellesdata Manual';
            // Add log
            self::$log[] = $infolog;

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

                    // Synchronize Companies
                    if (self::companies_fs_synchronization($pluginInfo,false)) {
                        // Send notifications
                        $notifyTo = null;
                        // Notifications
                        if ($pluginInfo->mail_notification) {
                            $notifyTo   = explode(',',$pluginInfo->mail_notification);
                        }//if_mail_notifications

                        // Notification manual synchronization
                        if (!$pluginInfo->automatic) {
                            if ($notifyTo) {
                                // Get companies to send notifications
                                $toMail = array();
                                FSKS_COMPANY::get_companiesfs_to_mail(2,$toMail);
                                FSKS_COMPANY::get_companiesfs_to_mail(3,$toMail);

                                if ($toMail) {
                                    self::send_notifications(SYNC_COMP,$toMail,$notifyTo,$pluginInfo->fs_source);

                                    // Log
                                    $infolog = new stdClass();
                                    $infolog->action 		= 'companies_fs_synchronization';
                                    $infolog->description 	= 'Send notifications';
                                    // Add log
                                    self::$log[] = $infolog;
                                }//if_toMail
                            }//if_notify
                        }//if_automatic
                    }

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
                    //self::unmap_user_competence($pluginInfo,KS_UNMAP_USER_COMPETENCE);

                    break;
                case TEST_FS_UNMAP_MANAGERS_REPORTERS:
                    echo "UNMAP  MANAGERS REPORTERS";
                    //self::unmap_managers_reporters($pluginInfo,KS_MANAGER_REPORTER);

                    break;
                case TEST_FS_UNMAP_ORGANIZATION:
                    echo "UNMAP ORGANIZATIONS";
                    self::unmap_organizations($pluginInfo,KS_UNMAP_COMPANY);

                    break;
                default:
                    break;
            }//switch_option

            // Log
            $infolog = new stdClass();
            $infolog->action        = 'FINISH Cron Manual ' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description   = 'FINISH Fellesdata Manual';
            // Add log
            self::$log[] = $infolog;

            foreach (self::$log as $info) {
                echo $info->description . "</br>";
            }

            // Write log
            FS_CRON::write_fellesdata_log(self::$log);
    }catch (Exception $ex) {
            // Log
            $infolog = new stdClass();
            $infolog->action      = 'ERROR Cron Manual ' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description = 'FINISH ERROR: ';
            $infolog->description .= $ex->getTraceAsString();
            // Add log
            self::$log[] = $infolog;

            echo " ERROR: " . $ex->getTraceAsString() . "</br>";

            // Write log
            FS_CRON::write_fellesdata_log(self::$log);

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

        try {
            // Synchronization Managers && Reporters
            self::manager_reporter_synchronization($plugin,KS_MANAGER_REPORTER);

            // Synchronization User Competence JobRole  -- Add/Update
            self::user_competence_synchronization($plugin,KS_USER_COMPETENCE);

            // Synchronization User Competence JobRole  -- Delete
            self::user_competence_synchronization($plugin,KS_USER_COMPETENCE,true);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//competence_synchronization
    
    /**
     * Description
     * Import data from KS site
     *
     * @param           Object $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    02/02/0216
     * @author          eFaktor     (fbv)
     */
    private static function import_ks($pluginInfo) {
        /* Variables    */

        try {
            // Import organization structure
            self::organization_structure($pluginInfo);

            // Import jobroles
            self::import_ks_jobroles($pluginInfo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//import_ks

    /**
     * Description
     * Import the organization structure from KS, for a specific level
     *
     * @param           Object $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function organization_structure($pluginInfo) {
        /* Variables */
        $infoLevel      = null;
        $params         = null;
        $response       = null;
        $infolog        = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action      = 'START organization_structure ';
            $infolog->description = 'START organization_structure';
            // Add log
            self::$log[] = $infolog;

            // Request web service
            $infoLevel = new stdClass();
            $infoLevel->company   = $pluginInfo->ks_muni;
            $infoLevel->level     = 1;
            $infoLevel->notIn     = 0;

            // Call web service
            $params     = array('topCompany' => $infoLevel);
            $response   = self::process_ks_service($pluginInfo,KS_ORG_STRUCTURE,$params);

            if ($response) {
                if ($response['error'] == '200') {
                    // Import organization structure
                    KS::import_ks_organization($response['structure']);
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action      = 'organization_structure';
                    $infolog->description = "ERROR SERVICE: " . $response['message'];
                    // Add log
                    self::$log[] = $infolog;
                }//if_no_error
            }//if_else

            // Log
            $infolog = new stdClass();
            $infolog->action      = 'FINISH organization_structure ';
            $infolog->description = 'FINISH organization_structure';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//organization_structure

    /**
     * Description
     * Import all job roles from KS site
     *
     * @param           Object $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_ks_jobroles($pluginInfo) {
        /* Variables    */
        $params     = null;
        $response   = null;
        $infoLevel  = null;
        $notIn      = null;
        $hierarchy  = null;
        $jobRoles   = null;
        $infolog    = null;
        
        try {
            // Log
            $infolog = new stdClass();
            $infolog->action      = 'START import_ks_jobroles ';
            $infolog->description = 'START import_ks_jobroles';
            // Add log
            self::$log[] = $infolog;

            // Jobroles generics
            $notIn = KS::existing_jobroles(true);

            // Call web service - JR generics
            $response = self::process_ks_service($pluginInfo,KS_JOBROLES_GENERICS,array('notIn' => $notIn));

            // Import jobroles generics
            if ($response) {
                if ($response['error'] == '200') {
                    KS::ks_jobroles($response['jobroles'],true);
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action        = 'import_ks_jobroles';
                    $infolog->description   = 'ERROR JR GENERICS: ' . $response['message'];
                    // Add log
                    self::$log[] = $infolog;
                }//if_no_error
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action        = 'import_ks_jobroles';
                $infolog->description   = 'JR GENERICS - RESPONSE NOT VALID';
                // Add log
                self::$log[] = $infolog;
            }//if_response

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
                    if ($response) {
                        if ($response['error'] == '200') {
                            KS::ks_jobroles($response['jobroles']);
                        }else {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action        = 'import_ks_jobroles';
                            $infolog->description   = 'ERROR JR NO GENERICS: ' . $response['message'];
                            // Add log
                            self::$log[] = $infolog;
                        }//if_no_error
                    }else {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action        = 'import_ks_jobroles';
                        $infolog->description   = 'JR NO GENERICS - RESPONSE NOT VALID';
                        // Add log
                        self::$log[] = $infolog;
                    }//if_response
                }//for_hierarchy
            }//if_hierarchy

            // Log
            $infolog = new stdClass();
            $infolog->action      = 'FINISH import_ks_jobroles ';
            $infolog->description = 'FINISH import_ks_jobroles';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $domain         = null;
        $token          = null;
        $server         = null;
        $infolog        = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START process_ks_service';
            $infolog->description 	= 'START process_ks_service: ' . $service;
            // Add log
            self::$log[] = $infolog;

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

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'process_ks_service - Service: ' . $service;
                $infolog->description 	= 'ERROR: ' . $error;
                // Add log
                self::$log[] = $infolog;
            }

            curl_close( $ch );

            $result = json_decode($response);

            // Conver to array
            if (!is_array($result)) {
                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'process_ks_service - Service: ' . $service;
                $infolog->description 	= 'RESULT: ' . $response;
                // Add log
                self::$log[] = $infolog;

                $result = (Array)$result;
            }

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH process_ks_service';
            $infolog->description 	= 'FINSIH process_ks_service: ' . $service;
            // Add log
            self::$log[] = $infolog;

            return $result;
        }catch (Exception $ex) {
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
     * @param           Object $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function import_fellesdata($pluginInfo) {
        /* Variables    */

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
        $infolog        = null;
        
        try {
            // Log
            $infolog = new stdClass();
            $infolog->action      = 'START import_fs_users';
            $infolog->description = 'START import_fs_users';
            // Add log
            self::$log[] = $infolog;

            // Call web service
            $fsResponse = self::process_tradis_service($plugin,TRADIS_FS_USERS);

            // Import data into temporary tables
            if ($fsResponse) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_USERS);

                // Log
                $infolog = new stdClass();
                $infolog->action      = 'import_fs_users';
                $infolog->description = 'Clean : ' . IMP_USERS;
                // Add log
                self::$log[] = $infolog;

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

                            // Log
                            $infolog = new stdClass();
                            $infolog->action      = 'import_fs_users';
                            $infolog->description = 'save_temporary_fs : ' . IMP_USERS;
                            // Add log
                            self::$log[] = $infolog;
                        }else {
                            // Mark file as suspicious
                            $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_USERS,$plugin);

                            // Move file to the right folder
                            copy($pathFile,$suspiciousPath);
                            unlink($pathFile);

                            // Log
                            $infolog = new stdClass();
                            $infolog->action      = 'import_fs_users';
                            $infolog->description = 'mark_suspicious_file : ' . $pathFile;
                            // Add log
                            self::$log[] = $infolog;
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

                        // Log
                        $infolog = new stdClass();
                        $infolog->action      = 'import_fs_users';
                        $infolog->description = 'save_temporary_fs : ' . IMP_USERS;
                        // Add log
                        self::$log[] = $infolog;
                    }
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action      = 'import_fs_users';
                    $infolog->description = 'File does not exists' . $pathFile;
                    // Add log
                    self::$log[] = $infolog;
                }//if_exists
            }//if_fsResponse

            // Log
            $infolog = new stdClass();
            $infolog->action      = 'FINISH import_fs_users';
            $infolog->description = 'FINISH import_fs_users';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
            echo $ex->getTraceAsString() . "</br>";
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
        $infolog    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action        = 'START import_fs_orgstructure';
            $infolog->description   = 'START import_fs_orgstructure';
            // Add log
            self::$log[] = $infolog;

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

                            // Log
                            $infolog = new stdClass();
                            $infolog->action      = 'import_fs_orgstructure';
                            $infolog->description = 'save_temporary_fs : ' . IMP_COMPANIES;
                            // Add log
                            self::$log[] = $infolog;
                        }else {
                            // Mark file as suspicious
                            $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_COMPANIES,$plugin);

                            // Move file to the right folder
                            copy($pathFile,$suspiciousPath);
                            unlink($pathFile);

                            // Log
                            $infolog = new stdClass();
                            $infolog->action      = 'import_fs_orgstructure';
                            $infolog->description = 'mark_suspicious_file : ' . $pathFile;
                            // Add log
                            self::$log[] = $infolog;
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

                        // Log
                        $infolog = new stdClass();
                        $infolog->action      = 'import_fs_orgstructure';
                        $infolog->description = 'save_temporary_fs : ' . IMP_COMPANIES;
                        // Add log
                        self::$log[] = $infolog;
                    }///if_suspicous_path
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action      = 'import_fs_orgstructure';
                    $infolog->description = 'File does not exist : ' . $pathFile;
                    // Add log
                    self::$log[] = $infolog;
                }//if_exists
            }//if_fsResponse

            // Log
            $infolog = new stdClass();
            $infolog->action        = 'FINISH import_fs_orgstructure';
            $infolog->description   = 'FINISH import_fs_orgstructure';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $infolog    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START import_fs_jobroles ';
            $infolog->description 	= 'START import_fs_jobroles';
            // Add log
            self::$log[] = $infolog;

            // Call web service
            $fsResponse = self::process_tradis_service($plugin,TRADIS_FS_JOBROLES);

            // Import data into temporary tables
            if ($fsResponse) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_JOBROLES);

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'import_fs_jobroles';
                $infolog->description 	= 'Clean : ' . IMP_JOBROLES;
                // Add log
                self::$log[] = $infolog;

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

                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'import_fs_jobroles';
                            $infolog->description 	= 'save_temporary_fs : ' . IMP_JOBROLES;
                            // Add log
                            self::$log[] = $infolog;
                        }else {
                            // Mark file as suspicious
                            $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_JOBROLES,$plugin);

                            // Move file to the right folder
                            copy($pathFile,$suspiciousPath);
                            unlink($pathFile);

                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'import_fs_jobroles';
                            $infolog->description 	= 'mark_suspicious_file : ' . $pathFile;
                            // Add log
                            self::$log[] = $infolog;
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

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_fs_jobroles';
                        $infolog->description 	= 'save_temporary_fs : ' . IMP_JOBROLES;
                        // Add log
                        self::$log[] = $infolog;
                    }//if_suspicious_path
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'import_fs_jobroles';
                    $infolog->description 	= 'File does not exist : ' . $pathFile;
                    // Add log
                    self::$log[] = $infolog;
                }//if_exists
            }//if_fsResponse

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH import_fs_jobroles ';
            $infolog->description 	= 'FINISH import_fs_jobroles';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $infolog                = null;
        
        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START import_fs_managers_reporters ';
            $infolog->description 	= 'START import_fs_managers_reporters';
            // Add log
            self::$log[] = $infolog;

            // Call web service
            $fsManagersReporters = self::process_tradis_service($plugin,TRADIS_FS_MANAGERS_REPORTERS);

            // Import data into temporary tables
            if ($fsManagersReporters) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_MANAGERS_REPORTERS);

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'import_fs_managers_reporters';
                $infolog->description 	= 'Clean: ' . IMP_MANAGERS_REPORTERS;
                // Add log
                self::$log[] = $infolog;

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

                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'import_fs_managers_reporters';
                            $infolog->description 	= 'save_temporary_fs: ' . IMP_MANAGERS_REPORTERS;
                            // Add log
                            self::$log[] = $infolog;
                        }else {
                            // Mark file as suspicious
                            $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_MANAGERS_REPORTERS,$plugin);

                            // Move file to the right folder
                            copy($pathFile,$suspiciousPath);
                            unlink($pathFile);

                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'import_fs_managers_reporters';
                            $infolog->description 	= 'mark_suspicious_file: ' . $pathFile;
                            // Add log
                            self::$log[] = $infolog;
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

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_fs_managers_reporters';
                        $infolog->description 	= 'save_temporary_fs: ' . IMP_MANAGERS_REPORTERS;
                        // Add log
                        self::$log[] = $infolog;
                    }//if_suspicious_path
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'import_fs_managers_reporters';
                    $infolog->description 	= 'File does not exist: ' . $pathFile;
                    // Add log
                    self::$log[] = $infolog;
                }//if_exists
            }//if_fsResponse

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH import_fs_managers_reporters ';
            $infolog->description 	= 'FINISH import_fs_managers_reporters';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $infolog            = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START import_fs_user_competence_jr ';
            $infolog->description 	= 'START import_fs_user_competence_jr';
            // Add log
            self::$log[] = $infolog;
            
            // Call web service
            $usersCompetenceJR = self::process_tradis_service($plugin,TRADIS_FS_USERS_JOBROLES);

            // Import data into temporary tables
            if ($usersCompetenceJR) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_COMPETENCE_JR);

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'import_fs_user_competence_jr';
                $infolog->description 	= 'Clean: ' . IMP_COMPETENCE_JR;
                // Add log
                self::$log[] = $infolog;

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

                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'import_fs_user_competence_jr';
                            $infolog->description 	= 'save_temporary_fs: ' . IMP_COMPETENCE_JR;
                            // Add log
                            self::$log[] = $infolog;
                        }else {
                            // Mark file as suspicious
                            $suspiciousPath = suspicious::mark_suspicious_file(TRADIS_FS_USERS_JOBROLES,$plugin);

                            // Move file to the right folder
                            copy($pathFile,$suspiciousPath);
                            unlink($pathFile);

                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'import_fs_user_competence_jr';
                            $infolog->description 	= 'mark_suspicious_file: ' . $pathFile;
                            // Add log
                            self::$log[] = $infolog;
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

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_fs_user_competence_jr';
                        $infolog->description 	= 'save_temporary_fs: ' . IMP_COMPETENCE_JR;
                        // Add log
                        self::$log[] = $infolog;
                    }//if_suspicious_path
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'import_fs_user_competence_jr';
                    $infolog->description 	= 'File does not exist: ' . $pathFile;
                    // Add log
                    self::$log[] = $infolog;
                }//if_exists
            }//if_data

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH import_fs_user_competence_jr ';
            $infolog->description 	= 'FINISH import_fs_user_competence_jr';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//import_fs_user_competence_jr

    /**
     * Description
     * Call Fellesdata Web service to import all data connected with companies, users...
     *
     * @param           $pluginInfo
     * @param           $service
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function process_tradis_service($pluginInfo,$service) {
        /* Variables    */
        global $CFG;
        $dir            = null;
        $backup         = null;
        $original       = null;
        $responseFile   = null;
        $pathFile       = null;
        $urlTradis      = null;
        $fromDate       = null;
        $toDate         = null;
        $date           = null;
        $admin          = null;
        $time           = null;
        $infolog        = null;
        
        try {
            // Local time
            $time = time();

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START process_tradis_service';
            $infolog->description 	= 'START process_tradis_service Service : ' . $service;
            // Add log
            self::$log[] = $infolog;

            // Check if exists temporary directory
            $dir = $CFG->dataroot . '/fellesdata';
            if (!file_exists($dir)) {
                mkdir($dir);
            }//if_dir

            // Original files
            $original = $CFG->dataroot . '/fellesdata/original';
            if (!file_exists($original)) {
                mkdir($original);
            }//if_backup

            // Get parameters service
            // To
            $toDate     = mktime(1, 60, 0, date("m"), date("d"), date("Y"));
            $toDate     = gmdate('Y-m-d\TH:i:s\Z',$toDate);
            // From
            $admin      = get_admin();
            $date       = usergetdate($pluginInfo->lastexecution, $admin->timezone);
            $fromDate   = mktime(0, 0, 0, $date['mon'], $date['mday']- $pluginInfo->fs_days, $date['year']);
            $fromDate   = gmdate('Y-m-d\TH:i:s\Z',$fromDate);

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

            // Save original file receive it
            $pathFile = $original . '/' . $service . '.txt';
            if (file_exists($pathFile)) {
                // DELETE
                unlink($pathFile);
            }
            // Overwrite
            $responseFile = fopen($pathFile,'w');
            fwrite($responseFile,$response);
            fclose($responseFile);

            if( $response === false ) {
                $error = curl_error( $ch );

                // Send notification
                FS_CRON::send_notifications_service($pluginInfo,'FS',$service);

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'process_tradis_service - Service: ' . $service;
                $infolog->description 	= 'ERROR: ' . $error;
                // Add log
                self::$log[] = $infolog;

                return false;
            }else if ($response == null) {
                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'process_tradis_service - Service: ' . $service;
                $infolog->description 	= 'ERROR RESPONSE TARDIS - NULL OBJECT  ';
                // Add log
                self::$log[] = $infolog;

                return false;
            }else if (isset($response->status) && $response->status != "200") {
                // Send notification
                FS_CRON::send_notifications_service($pluginInfo,'FS',$service);

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'process_tradis_service - Service: ' . $service;
                $infolog->description 	= 'ERROR RESPONSE TARDIS : ' . $response->message;
                $infolog->description  .= $response;
                // Add log
                self::$log[] = $infolog;

                return false;
            }else {
                // Check the file content
                $index = strpos($response, 'html');
                if ($index) {
                    // Send notification
                    FS_CRON::send_notifications_service($pluginInfo,'FS',$service);

                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'process_tradis_service - Service: ' . $service;
                    $infolog->description 	= 'ERROR RESPONSE TARDIS : ';
                    $infolog->description  .= $response;
                    // Add log
                    self::$log[] = $infolog;

                    return false;
                } else {
                    $index = strpos($response,'changeType');
                    if (!$index) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'process_tradis_service - Service: ' . $service;
                        $infolog->description 	= 'ERROR RESPONSE TARDIS - EMPTY FILE';
                        $infolog->description  .= $response;
                        // Add log
                        self::$log[] = $infolog;

                        return false;
                    }else {
                        // Clean all response
                        $pathFile = $dir . '/' . $service . '.txt';

                        // Remove bad characters
                        $content = str_replace('\"','"',$response);
                        // CR - LF && EOL
                        $content = str_replace('\r\n',chr(13),$content);
                        $content = str_replace('\r',chr(13),$content);
                        $content = str_replace('\n',chr(13),$content);

                        // Create a new response file
                        $responseFile = fopen($pathFile,'w');
                        fwrite($responseFile,$content);
                        fclose($responseFile);

                        return true;
                    }
                }//if_else_index
            }//if_response
        }catch (Exception $ex) {
            // Send notification
            FS_CRON::send_notifications_service($pluginInfo,'FS',$service);

            throw $ex;
        }//try_catch
    }//process_tradis_service

    /**
     * Description
     * Synchronization of users accounts between KS and FS
     * Add resource number
     *
     * @param           Object $plugin
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
        global $SESSION,$DB;
        $rdo            = null;
        $total          = null;
        $industry       = null;
        $lstusers       = null;
        $rdousers       = null;
        $response       = null;
        $params         = null;
        $infolog        = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // Industry code by default
            $industry = 0;

            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 100;
            }//if_session_manul

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START users_fs_synchronization';
            $infolog->description 	= 'START users_fs_synchronization';
            // Add log
            self::$log[] = $infolog;

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

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'users_fs_synchronization';
                $infolog->description 	= 'Total: ' . $total;
                // Add log
                self::$log[] = $infolog;

                if ($total) {
                    for ($i=0;$i<=$total;$i=$i+$limit) {
                        // Get users accounts
                        list($lstusers,$rdousers) = FSKS_USERS::get_users_accounts($industry,$start,$limit);

                        if ($lstusers) {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'users_fs_synchronization';
                            $infolog->description 	= 'To synchronize: ' . $lstusers;
                            // Add log
                            self::$log[] = $infolog;

                            // Call web service
                            $response = self::process_ks_service($plugin,KS_SYNC_USER_ACCOUNT,array('usersAccounts' => $lstusers));

                            if ($response) {
                                if ($response['error'] == '200') {
                                    // Synchronize users accounts FS
                                    FSKS_USERS::synchronize_users_fs($rdousers,$response['usersAccounts']);
                                }else {
                                    // Log
                                    $infolog = new stdClass();
                                    $infolog->action 		= 'users_fs_synchronization';
                                    $infolog->description 	= 'Error WS: ' . $response['message'];
                                    // Add log
                                    self::$log[] = $infolog;
                                }//if_no_error
                            }//if_response
                        }
                    }//for
                }//if_total
            }//if_synchronization

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH users_fs_synchronization';
            $infolog->description 	= 'FINISH users_fs_synchronization';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function companies_fs_synchronization($pluginInfo,$fstExecution) {
        /* Variables    */
        $toMail         = null;
        $notifyTo       = null;
        $response       = null;
        $infolog        = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START companies_fs_synchronization ';
            $infolog->description 	= 'START companies_fs_synchronization';
            // Add log
            self::$log[] = $infolog;

            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_COMPANIES)) {
                // Notifications
                if ($pluginInfo->mail_notification) {
                    $notifyTo   = explode(',',$pluginInfo->mail_notification);
                }//if_mail_notifications

                // First execution
                if ($fstExecution) {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'companies_fs_synchronization';
                    $infolog->description 	= 'First execution ';
                    // Add log
                    self::$log[] = $infolog;

                    // Mail --> manual synchronization
                    if ($notifyTo) {
                        self::send_notifications(SYNC_COMP,null,$notifyTo,$pluginInfo->fs_source);

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'companies_fs_synchronization';
                        $infolog->description 	= 'First execution - Send notifications';
                        // Add log
                        self::$log[] = $infolog;
                    }//if_notify
                }else {
                    // Apply company changes inside the same level
                    FSKS_COMPANY::update_company_changes_same_level();

                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'companies_fs_synchronization';
                    $infolog->description 	= 'update_company_changes_same_level ';
                    // Add log
                    self::$log[] = $infolog;

                    // Companies to create automatically
                    if ($pluginInfo->automatic) {
                        // Level two
                        //self::companies_automatically_synchronized($pluginInfo,$pluginInfo->map_two);
                        // Level three
                        //self::companies_automatically_synchronized($pluginInfo,$pluginInfo->map_three);
                    }//if_automatic

                    // Synchronize new companies
                    self::companies_new_fs_synchronization($pluginInfo);

                    // Synchronize no new companies
                    self::companies_no_new_fs_synchronization($pluginInfo);
                }//if_else
            }//if_synchronization

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH companies_fs_synchronization ';
            $infolog->description 	= 'FINISH companies_fs_synchronization';
            // Add log
            self::$log[] = $infolog;

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//companies_fs_synchronization

    /**
     * Description
     * Synchronization of the companies when the synchronization has to be done automatically
     *
     * @param           $plugin
     * @param           $level
     *
     * @throws          Exception
     *
     * @creationDate    05/09/2017
     * @author          eFaktor     (fbv)
     */
    private static function companies_automatically_synchronized($plugin,$level) {
        /* Variables */
        global $SESSION;
        $response       = null;
        $moved          = null;
        $rdocompanies   = null;
        $toSynchronize  = null;
        $infolog        = null;
        $total          = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 150;
            }//if_session_manul

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START companies_fs_synchronization - companies_automatically_synchronized ';
            $infolog->description 	= 'START companies_fs_synchronization - companies_automatically_synchronized';
            // Add log
            self::$log[] = $infolog;

            // Get total
            $total = FSKS_COMPANY::get_total_companies_automatically($level);
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'companies_fs_synchronization - companies_automatically_synchronized';
            $infolog->description 	= 'Total : ' . $total;
            // Add log
            self::$log[] = $infolog;

            // Synchronize
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get companies to synchronize
                    list($toSynchronize,$rdocompanies) = FSKS_COMPANY::get_companies_to_synchronize_automatically($level,$start,$limit);

                    // Call webs service
                    if ($toSynchronize) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'companies_fs_synchronization - companies_automatically_synchronized';
                        $infolog->description 	= 'To Synchronize : ' . $toSynchronize;
                        // Add log
                        self::$log[] = $infolog;

                        $params     = array('companiesFS' => $toSynchronize);
                        $response   = self::process_ks_service($plugin,KS_SYNC_FS_COMPANY,$params);

                        if ($response) {
                            if ($response['error'] == '200') {
                                FSKS_COMPANY::synchronize_companies_ksfs($rdocompanies,$response['companies'],true);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'companies_fs_synchronization - companies_automatically_synchronized';
                                $infolog->description 	= 'ERROR WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }//if_response
                    }//if_toSynchronize
                }//for
            }//if_total

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH companies_fs_synchronization - companies_automatically_synchronized ';
            $infolog->description 	= 'FINISH companies_fs_synchronization - companies_automatically_synchronized';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//companies_automatically_synchronized

    /**
     * Description
     * Synchronize companies created as a new from Tardis
     * 
     * @param       Object  $plugin
     * 
     * @throws             Exception
     * 
     * @creationDate        17/02/2017
     * @author              eFaktor     (fbv)
     */
    private static function companies_new_fs_synchronization($plugin) {
        /* Variables */
        global $SESSION;
        $rdocompanies   = null;
        $toSynchronize  = null;
        $response       = null;
        $infolog        = null;
        $total          = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 150;
            }//if_session_manul

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START companies_fs_synchronization - companies_new_fs_synchronization';
            $infolog->description 	= 'START companies_fs_synchronization - companies_new_fs_synchronization';
            // Add log
            self::$log[] = $infolog;

            // Get total
            $total = FSKS_COMPANY::get_total_new_companiesfs_to_synchronize();
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'companies_fs_synchronization - companies_new_fs_synchronization';
            $infolog->description 	= 'Total: ' . $total;
            // Add log
            self::$log[] = $infolog;

            // Synchronize
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get companies to synchronize
                    list($toSynchronize,$rdocompanies) = FSKS_COMPANY::get_new_companiesfs_to_synchronize($start,$limit);

                    // Call webs service
                    if ($toSynchronize) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'companies_fs_synchronization - companies_new_fs_synchronization';
                        $infolog->description 	= 'To Synchronize: ' . $toSynchronize;
                        // Add log
                        self::$log[] = $infolog;

                        $params     = array('companiesFS' => $toSynchronize);
                        $response   = self::process_ks_service($plugin,KS_SYNC_FS_COMPANY,$params);

                        if ($response) {
                            if ($response['error'] == '200') {
                                FSKS_COMPANY::synchronize_companies_ksfs($rdocompanies,$response['companies']);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'companies_fs_synchronization - companies_new_fs_synchronization';
                                $infolog->description 	= 'ERROR WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }//if_response
                    }//if_toSynchronize
                }//for
            }//if_total

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH companies_fs_synchronization - companies_new_fs_synchronization';
            $infolog->description 	= 'FINISH companies_fs_synchronization - companies_new_fs_synchronization';
            // Add log
            self::$log[] = $infolog;
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
        global $SESSION;
        $rdocompanies   = null;
        $toSynchronize  = null;
        $response       = null;
        $infolog        = null;
        $total          = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 150;
            }//if_session_manul

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START companies_fs_synchronization - companies_no_new_fs_synchronization';
            $infolog->description 	= 'START companies_fs_synchronization - companies_no_new_fs_synchronization';
            // Add log
            self::$log[] = $infolog;

            // Get total
            $total = FSKS_COMPANY::get_total_update_companiesfs_to_synchronize();
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'companies_fs_synchronization - companies_no_new_fs_synchronization';
            $infolog->description 	= 'Total : ' . $total;
            // Add log
            self::$log[] = $infolog;

            // Synchronize
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get companies to synchronize
                    list($toSynchronize,$rdocompanies) = FSKS_COMPANY::get_update_companiesfs_to_synchronize($start,$limit);

                    // Call webs service
                    if ($toSynchronize) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'companies_fs_synchronization - companies_no_new_fs_synchronization';
                        $infolog->description 	= 'To Synchronize : ' . $toSynchronize;
                        // Add log
                        self::$log[] = $infolog;

                        $params     = array('companiesFS' => $toSynchronize);
                        $response   = self::process_ks_service($plugin,KS_SYNC_FS_COMPANY,$params);

                        if ($response) {
                            if ($response['error'] == '200') {
                                FSKS_COMPANY::synchronize_companies_ksfs($rdocompanies,$response['companies']);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'companies_fs_synchronization - companies_no_new_fs_synchronization';
                                $infolog->description 	= 'ERROR WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }//if_response
                    }//if_toSynchronize
                }//for
            }//if_total

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH companies_fs_synchronization - companies_no_new_fs_synchronization';
            $infolog->description 	= 'FINISH companies_fs_synchronization - companies_no_new_fs_synchronization';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//companies_no_new_fs_synchronization

    /**
     * Description
     * Un map organizations between FS & KS
     *
     * @param           Object  $pluginInfo
     * @param                   $service
     *
     * @throws          Exception
     *
     * @creationDate    23/11/2016
     * @author          unmap companies
     */
    private static function unmap_organizations($pluginInfo,$service) {
        /* Variables */
        $toUnMap    = null;
        $response   = null;
        $infolog    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START unmap_organizations';
            $infolog->description 	= 'START unmap_organizations';
            // Add log
            self::$log[] = $infolog;

            // Comapnies to unmap
            $toUnMap = FSKS_COMPANY::companies_to_unmap();

            if ($toUnMap) {
                // Call web service
                if ($toUnMap) {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'unmap_organizations';
                    $infolog->description 	= 'unmap: ' . json_encode($toUnMap);
                    // Add log
                    self::$log[] = $infolog;

                    $response = self::process_ks_service($pluginInfo,$service,array('toUnMap' => $toUnMap));
                    if ($response['error'] == '200') {
                        FSKS_COMPANY::unmap_companies_ksfs($response['orgUnMapped']);
                    }else {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'unmap_organizations';
                        $infolog->description 	= 'ERROR WS: ' . $response['error'];
                        // Add log
                        self::$log[] = $infolog;
                    }//if_no_error
                }//if_toSynchronize
            }//if_toUnMap

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH unmap_organizations';
            $infolog->description 	= 'FINISH unmap_organizations';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $toMail         = null;
        $notifyTo       = null;
        $infolog        = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START jobroles_fs_to_map';
            $infolog->description 	= 'START jobroles_fs_to_map';
            // Add log
            self::$log[] = $infolog;

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
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'jobroles_fs_to_map';
                        $infolog->description 	= 'JR - send_notifications';
                        // Add log
                        self::$log[] = $infolog;
                    }else {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'jobroles_fs_to_map';
                        $infolog->description 	= 'JR - None JR to map';
                        // Add log
                        self::$log[] = $infolog;
                    }//If_toMail
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'jobroles_fs_to_map';
                    $infolog->description 	= 'JR - None JR to map';
                    // Add log
                    self::$log[] = $infolog;
                }//if_notigyTo
            }//if_synchronization

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH jobroles_fs_to_map';
            $infolog->description 	= 'FINISH jobroles_fs_to_map';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//jobroles_fs_to_map

    /**
     * Description
     * Synchronization User Competence
     *
     * @param                $pluginInfo
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
        global $SESSION;
        $toSynchronize  = null;
        $response       = null;
        $infolog        = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 150;
            }//if_session_manul

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START user_competence_synchronization';
            $infolog->description 	= 'START user_competence_synchronization';
            // Add log
            self::$log[] = $infolog;

            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_COMPETENCE_JR)) {
                // User competence to synchronize
                $total = FSKS_USERS::get_total_users_competence_to_synchronize($toDelete,$status);

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'user_competence_synchronization';
                $infolog->description 	= 'Total: ' . $total;
                // Add log
                self::$log[] = $infolog;

                if ($total) {
                    for ($i=0;$i<=$total;$i=$i+$limit) {
                        list($competence,$rdocompetence) = FSKS_USERS::user_competence_to_synchronize($toDelete,$status,$start,$limit);

                        // Call web service
                        if ($competence) {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'user_competence_synchronization';
                            $infolog->description 	= 'To Synchronize: ' . $competence;
                            // Add log
                            self::$log[] = $infolog;

                            // Params web service
                            $params = array();
                            $params['usersCompetence'] = $competence;
                            $response = self::process_ks_service($pluginInfo,$service,$params);
                            if ($response['error'] == '200') {
                                // Synchronize user competence
                                FSKS_USERS::synchronize_user_competence_fs($rdocompetence,$response['usersCompetence']);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'user_competence_synchronization';
                                $infolog->description 	= 'ERROR WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }//if_toSynchronize
                    }//for_rdo
                }//if_totla
            }//if_synchronization

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH user_competence_synchronization';
            $infolog->description 	= 'FINISH user_competence_synchronization';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        global $SESSION;
        $toSynchronize  = null;
        $rdomanagers    = null;
        $response       = null;
        $infolog        = null;
        $total          = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // To avoid problems timeout
            if (isset($SESSION->manual) && ($SESSION->manual)) {
                $limit          = 150;
            }//if_session_manul

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START manager_reporter_synchronization';
            $infolog->description 	= 'START manager_reporter_synchronization';
            // Add log
            self::$log[] = $infolog;

            // check if the synchronization can be run
            if (suspicious::run_synchronization(IMP_SUSP_MANAGERS_REPORTERS)) {
                // Managers and reporters to synchronize
                $total = FSKS_USERS::get_total_managers_reporters_to_synchronize();
                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'manager_reporter_synchronization';
                $infolog->description 	= 'Total: ' . $total;
                // Add log
                self::$log[] = $infolog;
                if ($total) {
                    for ($i=0;$i<=$total;$i=$i+500) {
                        // To synchronize
                        list($toSynchronize,$rdomanagers) = FSKS_USERS::get_managers_reporters_to_synchronize($start,$limit);

                        // Call webs ervice
                        if ($toSynchronize) {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'manager_reporter_synchronization';
                            $infolog->description 	= 'To Synchronize: ' . $toSynchronize;
                            // Add log
                            self::$log[] = $infolog;

                            $response = self::process_ks_service($pluginInfo,$service,array('managerReporter' => $toSynchronize));
                            if ($response['error'] == '200') {
                                // Syncrhonize managers and reporters
                                FSKS_USERS::synchronize_manager_reporter_fs($rdomanagers,$response['managerReporter']);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'manager_reporter_synchronization';
                                $infolog->description 	= 'ERROR WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }//if_toSynchronize
                    }//for
                }//if_total
            }//if_synchronization

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH manager_reporter_synchronization';
            $infolog->description 	= 'FINISH manager_reporter_synchronization';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
                        $info->companies = implode('<br/>',$toMail);
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

