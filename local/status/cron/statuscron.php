<?php
/**
 * Fellesdata Status Integration - Cron
 *
 * @package         local/status
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

    public static function cron($plugin) {
        /* Varibales */
        global $CFG;
        
        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START FELLESDATA STATUS CRON . ' . "\n";
            
            // Get industry code
            $industry = STATUS::get_industry_code($plugin->ks_muni);

            // Get competence from KS
            self::competence_data($plugin,$industry);

            // Get managers reporters from KS
            self::managers_reporters($plugin,$industry);

            // Import last status from fellesdata
            self::import_status($plugin);

            // Syncronization
            self::synchronization($plugin,$industry);
            
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA STATUS CRON . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            $dbLog = $ex->getMessage() . "\n" ."\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR - FELLESDATA STATUS CRON . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }

    public static function test($plugin) {
        /* Variables */
        $industry = null;

        try {
            // Get industry code
            $industry = STATUS::get_industry_code($plugin->ks_muni);

            echo "Industry --> " . $industry . "</br>";

            // Get competence from KS
            //self::competence_data($plugin,$industry);

            // Get managers reporters from KS
            //self::managers_reporters($plugin,$industry);

            // Import last status from fellesdata
            self::import_status($plugin);

            // Syncronization
            //self::synchronization($plugin,$industry);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Get competence data from KS
     *
     * @param       $plugin
     * @param       $industry
     *
     * @throws      Exception
     *
     * @creationDate    25/02/2017
     * @author          eFaktor     (fbv)
     */
    private static function competence_data($plugin,$industry) {
        /* Variables */
        global $CFG;
        $dblog      = null;
        $params     = null;
        $response   = null;
        $file       = null;
        $path       = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START FELLESDATA STATUS Get KS competence data . ' . "\n";

            // Cal service
            $params = array();
            $params['competence'] = $industry;
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
     * Get managers/reporters from KS
     *
     * @param           Object $plugin
     * @param           String $industry
     *
     * @throws          Exception
     *
     * @creationDate    02/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function managers_reporters($plugin,$industry) {
        /* Variables */
        global $CFG;
        $dblog      = null;
        $params     = null;
        $response   = null;
        $file       = null;
        $path       = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START FELLESDATA STATUS KS Managers/Reporters . ' . "\n";

            // Cal service
            $params = array();
            $params['industry'] = $industry;
            $response = self::process_service($plugin,WS_MANAGERS_REPORTERS,$params);

            // Proces response
            if ($response) {
                if ($response['error'] == '200') {
                    // Save managers
                    if ($response['managers']) {
                        STATUS::save_managers_reporters($response['managers'],MANAGERS);
                    }//managers

                    // Save reporters
                    if ($response['reporters']) {
                        STATUS::save_managers_reporters($response['reporters'],REPORTERS);
                    }//reporters
                }else {
                    // Log
                    $dblog .= "Error WS: " . $response['message'] . "\n" ."\n";
                }//if_no_error
            }else {
                $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR Response null . ' . "\n";
            }//if_else_response

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA STATUS KS Managers/Reporters. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog = $ex->getMessage() . "\n" ."\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA STATUS ERROR KS Managers/Reporters. ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//managers_reporters

    /**
     * Description
     * Carry out all synchronization
     * 
     * @param           Object  $plugin
     * @param                   $industry
     * 
     * @throws                  Exception
     * 
     * @creationDate    06/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function synchronization($plugin,$industry) {
        /* Variables */
        global $CFG;
        $dblog = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization Fellesdata STATUS. ' . "\n";

            // Synchronization FS Users
            self::sync_status_users_accounts($plugin,$industry);
            
            // Synchronization FS Companies
            self::sync_status_fs_organizations($plugin);

            // Synchronization FS Job roles
            self::sync_status_fs_jobroles($plugin);
            
            // Synchronization FS Managers/Reporters to delete
            // Managers
            self::sync_status_delete_managers_reporters($plugin,MANAGERS);
            // Reporters
            self::sync_status_delete_managers_reporters($plugin,REPORTERS);

            // Synchronization FS Managers/Reporters
            self::sync_status_managers_reporters($plugin);

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

    /**
     * Description
     * Import last status from tardis
     *
     * @param        object $plugin
     *
     * @return              bool
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
            self::import_status_users($plugin);

            // Import FS Companies
            self::import_status_orgstructure($plugin);

            // Import FS Job roles
            self::import_status_jobroles($plugin);

            // Import FS User Competence
            self::import_status_managers_reporters($plugin);

            // Import FS User Competence JR
            self::import_status_user_competence($plugin);

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import Fellesdata STATUS. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            return true;
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
        $data       = null;
        $total      = null;
        $i          = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import STATUS Users . ' . "\n";

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_USERS);

            // Import data into temporary tables
            if ($response) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_USERS);

                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    // Get total
                    $total = count($content);
                    // Split the process if it is too big
                    if ($total > MAX_IMP_FS) {
                        for($i=0;$i<=$total;$i=$i+MAX_IMP_FS) {
                            $data = array_slice($content,$i,MAX_IMP_FS,true);
                            FS::save_temporary_fellesdata($data,IMP_USERS,true);
                        }
                        FS::backup_temporary_fellesdata(IMP_USERS);
                    }else {
                        if (FS::save_temporary_fellesdata($content,IMP_USERS,true)) {
                            FS::backup_temporary_fellesdata(IMP_USERS);
                        }//if_status
                    }//if_max_imp
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
        $data       = null;
        $total      = null;
        $i          = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import STATUS ORG Structure . ' . "\n";

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_COMPANIES);

            // Import data into temporary tables
            if ($response) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_COMPANIES);

                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_COMPANIES . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    // Get total
                    $total = count($content);
                    // Split the process if it is too big
                    if ($total > MAX_IMP_FS) {
                        for($i=0;$i<=$total;$i=$i+MAX_IMP_FS) {
                            $data = array_slice($content,$i,MAX_IMP_FS,true);
                            FS::save_temporary_fellesdata($data,IMP_COMPANIES,true);
                        }
                        FS::backup_temporary_fellesdata(IMP_COMPANIES);
                    }else {
                        if (FS::save_temporary_fellesdata($content,IMP_COMPANIES,true)) {
                            FS::backup_temporary_fellesdata(IMP_COMPANIES);
                        }//if_status
                    }//if_max_imp
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
        $data       = null;
        $total      = null;
        $i          = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import STATUS JOB ROLES . ' . "\n";

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_JOBROLES);

            // Import data into temporary tables
            if ($response) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_JOBROLES);

                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_JOBROLES . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    // Get total
                    $total = count($content);
                    // Split the process if it is too big
                    if ($total > MAX_IMP_FS) {
                        for($i=0;$i<=$total;$i=$i+MAX_IMP_FS) {
                            $data = array_slice($content,$i,MAX_IMP_FS,true);
                            FS::save_temporary_fellesdata($data,IMP_JOBROLES,true);
                        }
                        FS::backup_temporary_fellesdata(IMP_JOBROLES);
                    }else {
                        if (FS::save_temporary_fellesdata($content,IMP_JOBROLES,true)) {
                            FS::backup_temporary_fellesdata(IMP_JOBROLES);
                        }//if_status
                    }//if_max_imp
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
        $path       = null;
        $content    = null;
        $response   = null;
        $dblog      = null;
        $data       = null;
        $i          = null;
        $total      = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import STATUS MANAGERRS REPORTERS . ' . "\n";

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_MANAGERS_REPORTERS);

            // Import data into temporary tables
            if ($response) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_MANAGERS_REPORTERS);

                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_MANAGERS_REPORTERS . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    // Get total
                    $total = count($content);
                    // Split the process if it is too big
                    if ($total > MAX_IMP_FS) {
                        for($i=0;$i<=$total;$i=$i+MAX_IMP_FS) {
                            $data = array_slice($content,$i,MAX_IMP_FS,true);
                            FS::save_temporary_fellesdata($data,IMP_MANAGERS_REPORTERS,true);
                        }
                        FS::backup_temporary_fellesdata(IMP_MANAGERS_REPORTERS);
                    }else {
                        if (FS::save_temporary_fellesdata($content,IMP_MANAGERS_REPORTERS,true)) {
                            FS::backup_temporary_fellesdata(IMP_MANAGERS_REPORTERS);
                        }//if_status
                    }//if_max_imp
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
        $path       = null;
        $content    = null;
        $response   = null;
        $dblog      = null;
        $total      = null;
        $i          = null;
        $data       = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import STATUS FS USERS COMPETENCE . ' . "\n";

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_USERS_JOBROLES);

            // Import data into temporary tables
            if ($response) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_COMPETENCE_JR);

                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS_JOBROLES . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    // Get total
                    $total = count($content);
                    // Split the process if it is too big
                    if ($total > MAX_IMP_FS) {
                        for($i=0;$i<=$total;$i=$i+MAX_IMP_FS) {
                            $data = array_slice($content,$i,MAX_IMP_FS,true);
                            FS::save_temporary_fellesdata($data,IMP_COMPETENCE_JR,true);
                        }
                        FS::backup_temporary_fellesdata(IMP_COMPETENCE_JR);
                    }else {
                        if (FS::save_temporary_fellesdata($content,IMP_COMPETENCE_JR,true)) {
                            FS::backup_temporary_fellesdata(IMP_COMPETENCE_JR);
                        }//if_status
                    }//if_max_imp
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
     * Synchronization status competence
     *
     * @param       Object  $plugin
     *
     * @throws              Exception
     *
     * @creationDate    01/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_competence($plugin) {
        /* Variables    */
        global $CFG;
        $competence     = null;
        $rdocompetence  = null;
        $response       = null;
        $dblog          = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization STATUS competence. ' . "\n";

            // User competence to synchronize
            $total = FSKS_USERS::get_total_users_competence_to_synchronize(false,true);
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    list($competence,$rdocompetence) = FSKS_USERS::user_competence_to_synchronize(false,true,$start,$limit);

                    // Call web service
                    if ($competence) {
                        // Params web service
                        $params = array();
                        $params['usersCompetence'] = $competence;

                        $response = self::process_service($plugin,KS_USER_COMPETENCE,$params);
                        if ($response['error'] == '200') {
                            // Synchronize user competence
                            FSKS_USERS::synchronize_user_competence_fs($rdocompetence,$response['usersCompetence']);
                        }else {
                            // Log
                            $dbLog  = "ERROR WS: " . $response['message'] . "\n" . "\n";
                            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Synchronization STATUS competence . ' . "\n";
                            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
                        }//if_no_error

                    }//if_competence
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
        $limit      = 1000;

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

                    // Call service
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
    }//sync_status_delete_competence

    /**
     * Description
     * Synchronization of the managers/reporters that have to be deleted from KS (STATUS)
     *
     * @param           Object  $plugin
     * @param           String  $type
     *
     * @throws                  Exception
     *
     * @creationDate    03/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_delete_managers_reporters($plugin,$type) {
        /* Variables */
        global $CFG;
        $dblog       = null;
        $total       = null;
        $todeleted   = null;
        $params      = null;
        $response    = null;
        $start       = 0;
        $limit       = 1000;
        
        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization STATUS delete managers/reporters. ' . "\n";

            // Get total to delete
            $total = STATUS::total_managers_reporters_to_delete($type);
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get to delete
                    $todeleted = STATUS::managers_reporters_to_delete_ks($type,$start,$limit);

                    // Call service
                    $params = array();
                    $params['type'] = $type;
                    $params['data'] = $todeleted;
                    $response = self::process_service($plugin,WS_CLEAN_MANAGERS_REPORTERS,array('managersreporters' => $params));

                    if ($response) {
                        if ($response['error'] == '200') {
                            if ($response['deleted']) {
                                STATUS::synchronize_managers_reporters_deleted($type,$response['deleted']);
                            }//if_deleted
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
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization STATUS delete managers/reporters. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization STATUS delete competence. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
            throw $ex;
        }//try_catch
    }//sync_status_delete_managers_reporters

    /**
     * Description
     * Synchronization of managers/resporters. Status
     *
     * @param           Object  $plugin
     *
     * @throws                  Exception
     *
     * @creationDate    03/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_managers_reporters($plugin) {
        /* Variables    */
        global $CFG;
        $toSynchronize  = null;
        $rdomanagers    = null;
        $response       = null;
        $dbLog          = null;
        $total          = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // Log
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Manager Reporter Synchronization (STATUS) . ' . "\n";

            // Managers and reporters to synchronize
            $total = FSKS_USERS::get_total_managers_reporters_to_synchronize();
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // To synchronize
                    list($toSynchronize,$rdomanagers) = FSKS_USERS::get_managers_reporters_to_synchronize($start,$limit,true);

                    // Call webs ervice
                    if ($toSynchronize) {
                        $response = self::process_service($plugin,KS_MANAGER_REPORTER,array('managerReporter' => $toSynchronize));
                        if ($response['error'] == '200') {
                            // Syncrhonize managers and reporters
                            FSKS_USERS::synchronize_manager_reporter_fs($rdomanagers,$response['managerReporter']);
                        }else {
                            // Log
                            $dbLog  .= "ERROR WS: " . $response['message'] . "\n" . "\n";
                            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Manager Reporter Synchronization (STATUS) . ' . "\n";
                        }//if_no_error
                    }//if_toSynchronize
                }//for
            }//if_total

            // Log
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Manager Reporter Synchronization (STATUS). ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dbLog  = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Manager Reporter Synchronization (STATUS). ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//sync_status_managers_reporters

    /**
     * Description
     * Synchronize last status of all users accounts
     * 
     * @param           Object  $plugin
     * @param           String  $industry
     * 
     * @throws                  Exception
     * 
     * @creationDate    06/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_users_accounts($plugin,$industry) {
        /* Variables */
        global $CFG;
        $dblog = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Users Accounts (STATUS) . ' . "\n";

            // First users to delete
            self::sync_status_users_accounts_deleted($plugin,$industry);

            // New users accounts
            self::sync_status_new_users_accounts($plugin,$industry);

            // Existing users accounts
            self::sync_status_existing_users_accounts($plugin,$industry);

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Users Accounts (STATUS) . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = $ex->getMessage() . "\n" . "\n";
            $dblog .= $dblog(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Users Accounts (STATUS). ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//sync_status_users_accounts

    /**
     * Description
     * Synchronize status existing users accounts
     * 
     * @param       Object $plugin
     * @param       String $industry
     *
     * @throws             Exception
     *
     * @creationDate    07/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_existing_users_accounts($plugin,$industry) {
        /* Variables */
        global $CFG;
        $dblog      = null;
        $rdousers   = null;
        $lstusers   = null;
        $response   = null;
        $total      = null;
        $start      = 0;
        $limit      = 1000;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Existing Users Accounts (STATUS) . ' . "\n";

            // get total users accounts
            $total = STATUS::get_total_status_existing_users_accounts();
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get users accounts
                    list($lstusers,$rdousers) = STATUS::get_status_existing_users_accounts($industry,$start,$limit);

                    // Call web service
                    $response = self::process_service($plugin,KS_SYNC_USER_ACCOUNT,array('usersAccounts' => $lstusers));

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

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Existing Users Accounts (STATUS) . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = $ex->getMessage() . "\n" . "\n";
            $dblog .= $dblog(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Existing Users Accounts (STATUS). ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//sync_status_existing_users_accounts

    /**
     * Description
     * Synchronize all new users accounts
     *
     * @param           Object $plugin
     * @param           String $industry
     *
     * @throws                 Exception
     *
     * @creationDate    07/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_new_users_accounts($plugin,$industry) {
        /* Variables */
        global $CFG;
        $dblog      = null;
        $rdousers   = null;
        $lstusers   = null;
        $response   = null;
        $total      = null;
        $start      = 0;
        $limit      = 1000;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Users Accounts NEW (STATUS) . ' . "\n";

            // get total users accounts
            $total = STATUS::get_total_status_new_users_accounts();
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get users accounts
                    list($lstusers,$rdousers) = STATUS::get_status_new_users_accounts($industry,$start,$limit);

                    // Call web service
                    $response = self::process_service($plugin,KS_SYNC_USER_ACCOUNT,array('usersAccounts' => $lstusers));

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

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Users Accounts NEW (STATUS) . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = $ex->getMessage() . "\n" . "\n";
            $dblog .= $dblog(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Users Accounts NEW (STATUS). ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//sync_status_new_users_accounts

    /**
     * Description
     * Synchronize the satus of all users accounts that have to be deleted
     * 
     * @param           Object  $plugin
     * @param           String  $industry
     *
     * @throws                  Exception
     *
     * @creationDate    07/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_users_accounts_deleted($plugin,$industry) {
        /* Variables */
        global $CFG;
        $dblog      = null;
        $rdousers   = null;
        $lstusers   = null;
        $response   = null;
        $total      = null;
        $start      = 0;
        $limit      = 1000;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Users Accounts DELETED (STATUS) . ' . "\n";

            // get total users accounts
            $total = STATUS::get_status_total_users_accounts_deleted();
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get users accounts
                    list($lstusers,$rdousers) = STATUS::get_status_users_accounts_deleted($industry,$start,$limit);

                    // Call web service
                    $response = self::process_service($plugin,KS_SYNC_USER_ACCOUNT,array('usersAccounts' => $lstusers));

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
            
            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Users Accounts DELETED (STATUS) . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = $ex->getMessage() . "\n" . "\n";
            $dblog .= $dblog(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Users Accounts DELETED (STATUS). ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//sync_status_users_accounts_deleted


    /**
     * Description
     * Synchronize last status of fs companies
     *
     * @param           Object  $plugin
     * @throws                  Exception
     *
     * @creationDate    05/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_fs_organizations($plugin) {
        /* Variables */
        global $CFG;
        $dblog = null;
        
        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START FS Organizations Synchronization (STATUS) . ' . "\n";

            // First new companies --> Send notifications
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' STATUS New companies. Notifications . ' . "\n";
            STATUS::synchronization_status_new_companies($plugin);

            // Companies don't exists any more
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' STATUS  Companies to delete . ' . "\n";
            self::synchronization_status_companies_no_exist($plugin);

            // Existing companies
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' STATUS Existing companies . ' . "\n";
            self::synchronization_status_existing_companies($plugin);

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FS Organizations Synchronization (STATUS) . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = $ex->getMessage() . "\n" . "\n";
            $dblog .= $dblog(time(),'%d.%m.%Y', 99, false). ' Finish ERROR FS Organizations Synchronization (STATUS). ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//sync_status_fs_organizations

    /**
     * Description
     * Synchronize companies that don't exist any more
     * 
     * @param           Object  $plugin
     * 
     * @throws                  Exception
     * 
     * @creationDate    05/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function synchronization_status_companies_no_exist($plugin) {
        /* Variables */
        global $CFG;
        $dblog = null;
        $rdocompanies   = null;
        $todelete       = null;
        $response       = null;
        $total          = null;
        $start          = 0;
        $limit          = 1000;
        
        try {
            // Get total
            $total = STATUS::get_status_total_companies_to_delete();
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get companies to delete
                    list($todelete,$rdocompanies) = STATUS::get_status_companies_to_delete($start,$limit);

                    // Call webs service
                    if ($todelete) {
                        $params     = array('companiesFS' => $todelete);
                        $response   = self::process_service($plugin,KS_SYNC_FS_COMPANY,$params);

                        if ($response) {
                            if ($response['error'] == '200') {
                                FSKS_COMPANY::synchronize_companies_ksfs($rdocompanies,$response['companies']);
                            }else {
                                /* Log  */
                                $dblog  .= "ERROR WS: " . $response['message'] . "\n\n";
                                $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Status compenies to delete . ' . "\n";
                                error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
                            }//if_no_error
                        }//if_response
                    }//if_toSynchronize
                }//for
            }//if_total
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronization_status_companies_no_exist

    /**
     * Description
     * Synchronize status of existing companies
     * @param           Object  $plugin
     *
     * @throws                  Exception
     *
     * @creationDate    05/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function synchronization_status_existing_companies($plugin) {
        /* Variables */
        global $CFG;
        $dblog = null;
        $rdocompanies   = null;
        $toSynchronize  = null;
        $response       = null;
        $total          = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // Get total
            $total = STATUS::get_total_status_existing_companies();
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get companies to delete
                    list($toSynchronize,$rdocompanies) = STATUS::get_status_existing_companies($start,$limit);

                    // Call webs service
                    if ($toSynchronize) {
                        $params     = array('companiesFS' => $toSynchronize);
                        $response   = self::process_service($plugin,KS_SYNC_FS_COMPANY,$params);

                        if ($response) {
                            if ($response['error'] == '200') {
                                FSKS_COMPANY::synchronize_companies_ksfs($rdocompanies,$response['companies']);
                            }else {
                                /* Log  */
                                $dblog  .= "ERROR WS: " . $response['message'] . "\n\n";
                                $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR Status existing companies . ' . "\n";
                                error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
                            }//if_no_error
                        }//if_response
                    }//if_toSynchronize
                }//for
            }//if_total
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronization_status_existing_companies

    /**
     * Description
     * Synchronize status jobroles
     * @param           Object  $plugin
     * 
     * @throws                  Exception
     * 
     * @creationDate    06/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_fs_jobroles($plugin) {
        /* Variables */
        global $CFG;
        $tomail         = null;
        $dblog          = null;
        $notifyto       = null;
        
        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START Sync Jobroles (STATUS) . ' . "\n";

            // Notifications
            if ($plugin->mail_notification) {
                $notifyto   = explode(',',$plugin->mail_notification);
            }//if_mail_notifications

            // Send notifications
            if ($notifyto) {
                // Jobroles to map
                $toMail = FSKS_JOBROLES::jobroles_fs_tosynchronize_mailing();
                if ($toMail) {
                    STATUS::send_notification(SYNC_JR,$tomail,$notifyto);
                }//If_toMail
                
                // Mark as imported the existing ones
                STATUS::sync_status_existing_jobroles();
            }//if_notigyTo
            
            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Sync Jobroles (STATUS) . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            // Log
            $dblog  = $ex->getTraceAsString() . "\n" . "\n";
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' ERROR FINISH Sync Jobroles (STATUS . ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
            
            throw $ex;
        }//try_catch
    }//sync_status_fs_jobroles
    
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