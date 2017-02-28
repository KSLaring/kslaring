<?php
/**
 * Fellesdata Status Integration - Cron
 *
 * @package         local/fellesdata_status
 * @subpackage      cron
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    23/02/2017
 * @author          eFaktor     (fbv)
 *
 */



class STATUS_CRON {
    /***********/
    /* PUBLIC  */
    /***********/

    public static function test($plugin) {
        try {

            // Get competence from KS
            //self::competence_data($plugin);

            // Get managers reporters from KS

            // Import last status from fellesdata
            //self::import_status($plugin);

            // Syncronization
            self::synchronization($plugin);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }

    /***********/
    /* PRIVATE */
    /***********/

    private static function synchronization($plugin) {
        /* Variables */
        global $CFG;
        $dblog = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization Fellesdata STATUS. ' . "\n";

            // Synchronization FS Users

            // Synchronization FS Companies

            // Synchronization FS Job roles

            // Synchronization FS Managers/Reporters

            // Synchronization FS User Competence to Delete
            self::sync_status_delete_competence($plugin);

            // Synchronization FS User Competence
            self::sync_status_competence($plugin);

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization Fellesdata STATUS. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization Fellesdata STATUS. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//synchronization

    private static function sync_status_competence($plugin) {
        /* Variables    */
        global $CFG;
        $competence  = null;
        $response    = null;
        $dblog       = null;
        $start       = 0;
        $limit       = 500;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization STATUS competence. ' . "\n";

            // User competence to synchronize
            $total = FSKS_USERS::get_total_users_competence_to_synchronize(false,true);
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    $competence = FSKS_USERS::user_competence_to_synchronize(false,true,$start,$limit);

                    // Call web service
                    // Params web service
                    $params = array();
                    $params['usersCompetence'] = $competence;

                    $response = self::process_service($plugin,KS_USER_COMPETENCE,$params);
                    if ($response['error'] == '200') {
                        // Synchronize user competence
                        FSKS_USERS::synchronize_user_competence_fs($competence,$response['usersCompetence']);
                    }else {
                        // Log
                        $dbLog  = "ERROR WS: " . $response['message'] . "\n" . "\n";
                        $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Synchronization STATUS competence . ' . "\n";
                        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
                    }//if_no_error
                }//for_rdo
            }//if_totla

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization STATUS competence. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization STATUS competence. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//sync_status_competence

    /**
     * Description
     * Synchronization of all competence data that has to be deleted
     * 
     * @param           object  $plugin
     * 
     * @throws                  Exception
     * 
     * @creationDate    28/02/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_delete_competence($plugin) {
        /* Variables */
        global $CFG;
        $dblog      = null;
        $total      = null;
        $todelete   = null;
        $params     = null;
        $response   = null;
        $start      = 0;
        $limit      = 500;
        
        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization STATUS delete competence. ' . "\n";
            
            // Get total to delete
            $total = STATUS::total_competence_to_delete_ks();
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // get to delete
                    $todelete = STATUS::competence_to_delete_ks($start,$limit);

                    // Params web service
                    $params = array();
                    $params['competence'] = $todelete;

                    // Cal service
                    $response = self::process_service($plugin,WS_DEL_COMPETENCE,$params);

                    if ($response) {
                        if ($response['error'] == '200') {
                            STATUS::synchronize_competence_deleted($response['deleted']);
                        }else {
                            // Log
                            $dblog .= "Error WS: " . $response['message'] . "\n" ."\n";
                        }//if_no_error
                    }else {
                        $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Response null . ' . "\n";
                    }//if_else_response
                }//for
            }//if_total
            
            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization STATUS delete competence. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization STATUS delete competence. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_Catch
    }//sync_status_competence

    /**
     * Description
     * Import last status from tardis
     *
     * @param        object $plugin
     *
     * @throws              Exception
     *
     * @creationDate    27/02/2017
     * @author          eFaktor     (fbv)
     */
    private static function import_status($plugin) {
        /* Variables    */
        global $CFG;
        $dblog        = null;

        try {
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import Fellesdata STATUS. ' . "\n";

            // Import FS Users
            //self::import_status_users($plugin);

            // Import FS Companies
            //self::import_status_orgstructure($plugin);

            // Import FS Job roles
            //self::import_status_jobroles($plugin);

            // Import FS User Competence
            //self::import_status_managers_reporters($plugin);

            // Import FS User Competence JR
            self::import_status_user_competence($plugin);

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import Fellesdata STATUS. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import Fellesdata STATUS. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_fellesdata

    /**
     * Description
     * Get last status of all users from tardis
     *
     * @param           object $plugin
     *
     * @throws                 Exception
     *
     * @creationDate    27/02/2017
     * @author          eFaktor (fbv)
     */
    private static function import_status_users($plugin) {
        /* Variables    */
        global $CFG;
        $path       = null;
        $content    = null;
        $response   = null;
        $dblog      = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import STATUS Users . ' . "\n";

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_USERS);

            // Import data into temporary tables
            if ($response) {
                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    if (FS::save_temporary_fellesdata($content,IMP_USERS,true)) {
                        FS::backup_temporary_fellesdata(IMP_USERS);
                    }
                }//if_exists
            }//if_fsResponse

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import STATUS Users . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= $ex->getTraceAsString() . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import STATUS Users . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_status_users

    /**
     * Description
     * Get last status of all organizations from Tardis
     *
     * @param       object  $plugin
     *
     * @throws              Exception
     *
     * @creationDate        27/02/2017
     * @author              eFaktor     (fbv)
     */
    private static function import_status_orgstructure($plugin) {
        /* Variables    */
        global $CFG;
        $path       = null;
        $content    = null;
        $response   = null;
        $dblog      = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import STATUS ORG Structure . ' . "\n";

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_COMPANIES);

            // Import data into temporary tables
            if ($response) {
                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_COMPANIES . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    if (FS::save_temporary_fellesdata($content,IMP_COMPANIES,true)) {
                        FS::backup_temporary_fellesdata(IMP_COMPANIES);
                    }//if_save_temporary
                }//if_exists
            }else {
                $dblog .= ' ERROR Import STATUS ORG Structure - RESPONSE NULL. ' . "\n";
            }//if_fsResponse

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import STATUS ORG Structure . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import STATUS ORG Structure . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_status_orgstructure

    /**
     * Description
     * Get last satus of all jobroles from tardis
     *
     * @param       object  $plugin
     *
     * @throws              Exception
     *
     * @creationDate        27/02/2017
     * @author              eFaktor     (fbv)
     */
    private static function import_status_jobroles($plugin) {
        /* Variables    */
        global $CFG;
        $path       = null;
        $content    = null;
        $response   = null;
        $dblog      = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import STATUS JOB ROLES . ' . "\n";

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_JOBROLES);

            // Import data into temporary tables
            if ($response) {
                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_JOBROLES . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    if (FS::save_temporary_fellesdata($content,IMP_JOBROLES,true)) {
                        FS::backup_temporary_fellesdata(IMP_JOBROLES);
                    }//if_save_temporay
                }//if_exists
            }else {
                $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import STATUS JOB ROLES - Response null . ' . "\n";
            }//if_fsResponse

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import STATUS JOB ROLES . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dblog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import STATUS Job Roles . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_status_jobroles

    /**
     * Description
     * Get last status of all managers/reportes from Tardis
     *
     * @param       object  $plugin
     *
     * @throws              Exception
     *
     * @creationDate        27/02/2017
     * @author              eFaktor     (fbv)
     */
    private static function import_status_managers_reporters($plugin) {
        /* Variables    */
        global $CFG;
        $path      = null;
        $content   = null;
        $response  = null;
        $dblog     = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import STATUS MANAGERRS REPORTERS . ' . "\n";

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_MANAGERS_REPORTERS);

            // Import data into temporary tables
            if ($response) {
                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_MANAGERS_REPORTERS . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    if (FS::save_temporary_fellesdata($content,IMP_MANAGERS_REPORTERS,true)) {
                        FS::backup_temporary_fellesdata(IMP_MANAGERS_REPORTERS);
                    }//if_save_temporary
                }//if_exists
            }else {
                $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import STATUS MANAGERRS REPORTERS - Response null. ' . "\n";
            }//if_fsResponse

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import STATUS MANAGERRS REPORTERS . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // log
            $dblog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import STATUS Managers Reporters . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_status_managers_reporters

    /**
     * Description
     * Get competence users from tardis
     *
     * @param       object  $plugin
     *
     * @throws              Exception
     *
     * @creationDate        27/02/2017
     * @author              eFaktor     (fbv)
     */
    private static function import_status_user_competence($plugin) {
        /* Variables    */
        global $CFG;
        $path        = null;
        $content     = null;
        $response    = null;
        $dblog       = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import STATUS FS USERS COMPETENCE . ' . "\n";

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_USERS_JOBROLES);

            // Import data into temporary tables
            if ($response) {
                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS_JOBROLES . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    if (FS::save_temporary_fellesdata($content,IMP_COMPETENCE_JR,true)) {
                        FS::backup_temporary_fellesdata(IMP_COMPETENCE_JR);
                    }//if_status
                }//if_exists
            }else {
                $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import STATUS USER COMPETENCE JR - NULL RESPONSE . ' . "\n";
            }//if_data

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINSH Import STATUS USER COMPETENCE JR . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Import STATUS User Competence . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//import_status_user_competence

    /**
     * Description
     * Get competence data from KS
     *
     * @param       $plugin
     *
     * @throws      Exception
     *
     * @creationDate    25/02/2017
     * @author          eFaktor     (fbv)
     */
    private static function competence_data($plugin) {
        /* Variables */
        global $CFG;
        $dblog      = null;
        $industry   = null;
        $params     = null;
        $response   = null;
        $file       = null;
        $path       = null;
        
        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START FELLESDATA STATUS Get KS competence data . ' . "\n";
            
            // Get industry code
            $industry = STATUS::get_industry_code($plugin->ks_muni);
            $params = array();
            $params['competence'] = $industry;

            // Cal service
            $response = self::process_service($plugin,WS_COMPETENCE,$params);
            
            if ($response) {
                if ($response['error'] == '200') {
                    STATUS::save_competence($response['competence']);
                }else {
                    // Log
                    $dblog .= "Error WS: " . $response['message'] . "\n" ."\n";
                }//if_no_error
            }else {
                $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Response null . ' . "\n";
            }//if_else_response

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA STATUS Get KS competence data . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog = $ex->getMessage() . "\n" ."\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA STATUS ERROR Get KS competence data . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
            
            throw $ex;
        }//try_catch
    }//competence_data
    
    /**
     * Description
     * KS Web Services to import data from KS site and synchronize data between fellesdata and KS
     *
     * @param           $plugin
     * @param           $service
     * @param           $params
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function process_service($plugin,$service,$params) {
        /* Variables    */
        global $CFG;
        $domain         = null;
        $token          = null;
        $server         = null;
        $error          = false;

        try {
            // Data to call Service
            $domain     = $plugin->ks_point;
            $token      = $plugin->kss_token;

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
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields);

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

    /**
     * Description
     * Call Fellesdata Web service to import last status connected with companies, users...
     *
     * @param           $plugin
     * @param           $service
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    27/02/2017
     * @author          eFaktor     (fbv)
     */
    private static function process_tardis_status($plugin,$service) {
        /* Variables    */
        global $CFG;
        $dir            = null;
        $backup         = null;
        $file           = null;
        $path           = null;
        $url            = null;
        $from           = null;
        $to             = null;
        $date           = null;
        $admin          = null;

        try {
            // Get parameters service
            $to     = mktime(1, 60, 0, date("m"), date("d"), date("Y"));
            $to     = gmdate('Y-m-d\TH:i:s\Z',$to);
            $from   = gmdate('Y-m-d\TH:i:s\Z',0);

            // Build url end point
            $url = $plugin->fs_point . '/' . $service . '?fromDate=' . $from . '&toDate=' . $to;

            // Call web service
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2 );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt($ch, CURLOPT_POST, false );
            curl_setopt($ch, CURLOPT_USERPWD, $plugin->fs_username . ":" . $plugin->fs_password);
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
                $path = $dir . '/' . $service . '.txt';
                if (file_exists($path)) {
                    // Move the file to the new directory
                    copy($path,$backup . '/' . $service . '_' . time() . '.txt');

                    unlink($path);
                }

                // Create a new response file
                $file = fopen($path,'w');
                fwrite($file,$response);
                fclose($file);

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
}//STATUS_CRON