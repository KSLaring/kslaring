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
 * Fellesdata Status Integration - Cron
 *
 * @package         local/status
 * @subpackage      cron
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    23/02/2017
 * @author          eFaktor     (fbv)
 *
 */

class STATUS_CRON {
    protected static $log           = null;

    /***********/
    /* PUBLIC  */
    /***********/

    public static function cron($plugin) {
        /* Varibales */
        $infolog = null;

        try {
            // Start log
            self::$log    =    array();

            // Log
            $infolog = new stdClass();
            $infolog->action      = 'Cron STATUS' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description = 'START Status Cron';
            // Add log
            self::$log[] = $infolog;
            
            // Get industry code
            $industry = STATUS::get_industry_code($plugin->ks_muni);

            // Get competence from KS
            self::competence_data($plugin,$industry);

            // Write log
            STATUS::write_status_log(self::$log);
            // Start log
            self::$log    =    array();

            // Get managers reporters from KS
            self::managers_reporters($plugin,$industry);

            // Repair connections
            self::repair_connections();

            // Write log
            STATUS::write_status_log(self::$log);
            // Start log
            self::$log    =    array();

            // Import last status from fellesdata
            self::import_status($plugin);

            // Write log
            STATUS::write_status_log(self::$log);
            // Start log
            self::$log    =    array();

            // Syncronization
            self::synchronization($plugin,$industry);

            // Log
            $infolog = new stdClass();
            $infolog->action      = 'Cron ' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description = 'FINSIH Status Cron';
            // Add log
            self::$log[] = $infolog;

            // Write log
            STATUS::write_status_log(self::$log);
        }catch (Exception $ex) {
            // Send error notification
            FS_CRON::send_notification_error_process($plugin,'TARDIS STATUS');
            FS_CRON::deactivate_cron('status');

            // Log
            $infolog = new stdClass();
            $infolog->action      = 'ERROR Status Cron ' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description = 'FINISH ERROR: ';
            $infolog->description .= $ex->getTraceAsString();
            // Add log
            self::$log[] = $infolog;

            // Write log
            STATUS::write_status_log(self::$log);

            throw $ex;
        }//try_catch
    }

    public static function test($plugin) {
        /* Variables */
        $industry   = null;
        $time       = null;
        $infolog    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'Status Cron Manual ' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description 	= 'START Status Cron Manual';
            // Add log
            self::$log[] = $infolog;

            // Get industry code
            $industry = STATUS::get_industry_code($plugin->ks_muni);

            // Get competence from KS
            self::competence_data($plugin,$industry);

            // Get managers reporters from KS
            self::managers_reporters($plugin,$industry);

            // Repair connections
            self::repair_connections();

            // Write log
            STATUS::write_status_log(self::$log);
            // Start log
            self::$log    =    array();

            // Import last status from fellesdata
            self::import_status($plugin);

            // Write log
            STATUS::write_status_log(self::$log);
            // Start log
            self::$log    =    array();

            // Syncronization
            //self::synchronization($plugin,$industry);

            // Write log
            //STATUS::write_status_log(self::$log);
            // Start log
            //self::$log    =    array();

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'Status Cron Manual ' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description 	= 'FINISH Status Cron Manual';
            // Add log
            self::$log[] = $infolog;


            // Write log
            STATUS::write_status_log(self::$log);
        }catch (Exception $ex) {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'Status Cron Manual ' . userdate(time(),'%d.%m.%Y %H:%M', 99, false);
            $infolog->description 	= 'FINISH ERROR: ';
            $infolog->description  .= $ex->getTraceAsString();
            // Add log
            self::$log[] = $infolog;

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
        $params     = null;
        $response   = null;
        $file       = null;
        $path       = null;
        $infolog    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START competence_data';
            $infolog->description 	= 'START competence_data';
            // Add log
            self::$log[] = $infolog;

            // Cal service
            $params = array();
            $params['competence'] = $industry;
            $response = self::process_service($plugin,WS_COMPETENCE,$params);

            if ($response) {
                if ($response['error'] == '200') {
                    STATUS::save_competence($response['competence']);
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'competence_data';
                    $infolog->description 	= 'Service: ' . WS_COMPETENCE;
                    $infolog->description  .= 'Error WS: ' . $response['message'] ;
                    // Add log
                    self::$log[] = $infolog;
                }//if_no_error
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'competence_data';
                $infolog->description 	= 'Response not valid - Service: ' . WS_COMPETENCE;
                // Add log
                self::$log[] = $infolog;
            }//if_else_response

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH competence_data';
            $infolog->description 	= 'FINISH competence_data';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//competence_data

    /**
     * Description
     * Get managers/reporters from KS
     *
     * @param           Object  $plugin
     * @param           String  $industry
     *
     * @throws          Exception
     *
     * @creationDate    02/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function managers_reporters($plugin,$industry) {
        /* Variables */
        $params     = null;
        $response   = null;
        $file       = null;
        $path       = null;
        $infolog    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START managers_reporters';
            $infolog->description 	= 'START managers_reporters';
            // Add log
            self::$log[] = $infolog;

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
                    $infolog = new stdClass();
                    $infolog->action 		= 'managers_reporters - ' . WS_MANAGERS_REPORTERS;
                    $infolog->description 	= 'Error WS: ' . $response['message'];
                    // Add log
                    self::$log[] = $infolog;
                }//if_no_error
            }else {
                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'managers_reporters - ' . WS_MANAGERS_REPORTERS;
                $infolog->description 	= 'RESPONSE NOT VALID';
                // Add log
                self::$log[] = $infolog;
            }//if_else_response

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH managers_reporters';
            $infolog->description 	= 'FINISH managers_reporters';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//managers_reporters

    /**
     * Description
     * Carry out all synchronization
     * 
     * @param           Object  $plugin
     * @param           String  $industry
     * 
     * @throws                  Exception
     * 
     * @creationDate    06/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function synchronization($plugin,$industry) {
        /* Variables */

        try {
            // Synchronization FS Users
            self::sync_status_users_accounts($plugin,$industry);
            
            // Synchronization FS Companies
            self::sync_status_fs_organizations($plugin);

            // Synchronization FS Job roles
            self::sync_status_fs_jobroles($plugin);
            
            // Synchronization FS Managers/Reporters to delete
            // Managers
            self::sync_status_delete_managers_reporters($plugin,MANAGERS,1);
            self::sync_status_delete_managers_reporters($plugin,MANAGERS,2);
            self::sync_status_delete_managers_reporters($plugin,MANAGERS,3);
            // Reporters
            self::sync_status_delete_managers_reporters($plugin,REPORTERS,1);
            self::sync_status_delete_managers_reporters($plugin,REPORTERS,2);
            self::sync_status_delete_managers_reporters($plugin,REPORTERS,3);

            // Synchronization FS Managers/Reporters
            self::sync_status_managers_reporters($plugin);

            STATUS::synchronize_managers_reporters_deleted(MANAGERS);
            STATUS::synchronize_managers_reporters_deleted(REPORTERS);

            // Synchronization FS User Competence to Delete
            self::sync_status_delete_competence($plugin);

            // Synchronization FS User Competence
            self::sync_status_competence($plugin);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronization

    /**
     * Description
     * Repair all missing connections
     *
     * @throws          Exception
     *
     * @creationDate    03/07/2017
     * @author          eFaktor     (fbv)
     */
    private static function repair_connections() {
        /* Variables */
        global $DB;
        $connections = null;
        $infolog     = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START repair_connections';
            $infolog->description 	= 'START repair_connections';
            // Add log
            self::$log[] = $infolog;

            // Get connections missed
            $connections = self::get_connections_missing();

            // Add connection
            if ($connections) {
                foreach ($connections as $connection) {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'repair_connections';
                    $infolog->description 	= 'repair_connections between ' . $connection->fscompany . ' && ' . $connection->kscompany ;
                    // Add log
                    self::$log[] = $infolog;

                    $DB->insert_record('ksfs_company',$connection);
                }
            }//if_connections

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH repair_connections';
            $infolog->description 	= 'FINISH repair_connections';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//repair_connections

    /**
     * Description
     * Get connections that are missing
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    03/07/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_connections_missing() {
        /* Variables */
        global $DB;
        $rdo = null;
        $sql = null;

        try {
            // SQL Instruction
            $sql = " SELECT	      fs.companyid as 'fscompany',
                                  ks.companyid as 'kscompany'
                     FROM		  {fs_company}	  fs
                        JOIN	  {ks_company}	  ks   ON   ks.name			= fs.name
                        LEFT JOIN {ksfs_company}  ksfs ON   ksfs.fscompany 	= fs.companyid 
                                                       AND  ksfs.kscompany  = ks.companyid
                     WHERE 	      ksfs.id IS NULL
                          AND     fs.synchronized = 1
                     ORDER BY     ks.companyid,fs.companyid ";

            // Execute
            $rdo = $DB->get_records_sql($sql);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_connections_missing


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

        try {
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

            return true;
        }catch (Exception $ex) {
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
        $data       = null;
        $total      = null;
        $i          = null;
        $infolog    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START import_status_users';
            $infolog->description 	= 'START import_status_users';
            // Add log
            self::$log[] = $infolog;

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_USERS,$dblog);

            // Import data into temporary tables
            if ($response) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_USERS);

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'import_status_users';
                $infolog->description 	= 'Clean: ' . IMP_USERS;
                // Add log
                self::$log[] = $infolog;

                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    // Get total
                    $total = count($content);

                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'import_status_users';
                    $infolog->description 	= 'TOTAL: ' . $total;
                    // Add log
                    self::$log[] = $infolog;

                    // Split the process if it is too big
                    if ($total > MAX_IMP_FS) {
                        for($i=0;$i<=$total;$i=$i+MAX_IMP_FS) {
                            $data = array_slice($content,$i,MAX_IMP_FS,true);
                            FS::save_temporary_fellesdata($data,IMP_USERS,true);
                        }

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_status_users';
                        $infolog->description 	= 'save_temporary_fellesdata: ' . IMP_USERS;
                        // Add log
                        self::$log[] = $infolog;
                    }else {
                        FS::save_temporary_fellesdata($content,IMP_USERS,true);

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_status_users';
                        $infolog->description 	= 'save_temporary_fellesdata: ' . IMP_USERS;
                        // Add log
                        self::$log[] = $infolog;
                    }//if_max_imp
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'import_status_users';
                    $infolog->description 	= 'Fiel does not exist: ' . $path;
                    // Add log
                    self::$log[] = $infolog;
                }//if_exists
            }//if_fsResponse

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH import_status_users';
            $infolog->description 	= 'FINISH import_status_users';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $data       = null;
        $total      = null;
        $i          = null;
        $infolog    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START import_status_orgstructure';
            $infolog->description 	= 'START import_status_orgstructure';
            // Add log
            self::$log[] = $infolog;

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_COMPANIES,$dblog);

            // Import data into temporary tables
            if ($response) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_COMPANIES);

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'import_status_orgstructure';
                $infolog->description 	= 'Clean: ' . IMP_COMPANIES;
                // Add log
                self::$log[] = $infolog;

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

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_status_orgstructure';
                        $infolog->description 	= 'save_temporary_fellesdata: ' . IMP_COMPANIES;
                        // Add log
                        self::$log[] = $infolog;
                    }else {
                        FS::save_temporary_fellesdata($content,IMP_COMPANIES,true);

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_status_orgstructure';
                        $infolog->description 	= 'save_temporary_fellesdata: ' . IMP_COMPANIES;
                        // Add log
                        self::$log[] = $infolog;
                    }//if_max_imp
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'import_status_orgstructure';
                    $infolog->description 	= 'File does not exist: ' . $path;
                    // Add log
                    self::$log[] = $infolog;
                }//if_exists
            }//if_fsResponse

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH import_status_orgstructure';
            $infolog->description 	= 'FINISH import_status_orgstructure';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $data       = null;
        $total      = null;
        $i          = null;
        $infolog    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START import_status_jobroles';
            $infolog->description 	= 'START import_status_jobroles';
            // Add log
            self::$log[] = $infolog;

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_JOBROLES,$dblog);

            // Import data into temporary tables
            if ($response) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_JOBROLES);

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'import_status_jobroles';
                $infolog->description 	= 'Clean: ' . IMP_JOBROLES;
                // Add log
                self::$log[] = $infolog;

                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_JOBROLES . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    // Get total
                    $total = count($content);

                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'import_status_jobroles';
                    $infolog->description 	= 'TOTAL: ' . $total;
                    // Add log
                    self::$log[] = $infolog;

                    // Split the process if it is too big
                    if ($total > MAX_IMP_FS) {
                        for($i=0;$i<=$total;$i=$i+MAX_IMP_FS) {
                            $data = array_slice($content,$i,MAX_IMP_FS,true);
                            FS::save_temporary_fellesdata($data,IMP_JOBROLES,true);
                        }

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_status_jobroles';
                        $infolog->description 	= 'save_temporary_fellesdata: ' . IMP_JOBROLES;
                        // Add log
                        self::$log[] = $infolog;
                    }else {
                        FS::save_temporary_fellesdata($content,IMP_JOBROLES,true);

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_status_jobroles';
                        $infolog->description 	= 'save_temporary_fellesdata: ' . IMP_JOBROLES;
                        // Add log
                        self::$log[] = $infolog;
                    }//if_max_imp
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'import_status_jobroles';
                    $infolog->description 	= 'File does not exist: ' . $path;
                    // Add log
                    self::$log[] = $infolog;
                }//if_exists
            }//if_fsResponse

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH import_status_jobroles';
            $infolog->description 	= 'FINIDH import_status_jobroles';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $data       = null;
        $i          = null;
        $total      = null;
        $infolog    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START import_status_managers_reporters';
            $infolog->description 	= 'START import_status_managers_reporters';
            // Add log
            self::$log[] = $infolog;

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_MANAGERS_REPORTERS,$dblog);

            // Import data into temporary tables
            if ($response) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_MANAGERS_REPORTERS);

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'import_status_managers_reporters';
                $infolog->description 	= 'Clean: ' . IMP_MANAGERS_REPORTERS;
                // Add log
                self::$log[] = $infolog;

                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_MANAGERS_REPORTERS . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    // Get total
                    $total = count($content);

                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'import_status_managers_reporters';
                    $infolog->description 	= 'Total: ' . $total;
                    // Add log
                    self::$log[] = $infolog;

                    // Split the process if it is too big
                    if ($total > MAX_IMP_FS) {
                        for($i=0;$i<=$total;$i=$i+MAX_IMP_FS) {
                            $data = array_slice($content,$i,MAX_IMP_FS,true);
                            FS::save_temporary_fellesdata($data,IMP_MANAGERS_REPORTERS,true);
                        }

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_status_managers_reporters';
                        $infolog->description 	= 'save_temporary_fellesdata: ' . IMP_MANAGERS_REPORTERS;
                        // Add log
                        self::$log[] = $infolog;
                    }else {
                        FS::save_temporary_fellesdata($content,IMP_MANAGERS_REPORTERS,true);

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_status_managers_reporters';
                        $infolog->description 	= 'save_temporary_fellesdata: ' . IMP_MANAGERS_REPORTERS;
                        // Add log
                        self::$log[] = $infolog;
                    }//if_max_imp
                }else {
                    $dblog .= 'FILE DOES NOT EXIST ' . "\n";
                }//if_exists
            }//if_fsResponse

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH import_status_managers_reporters';
            $infolog->description 	= 'FINISH import_status_managers_reporters';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $total      = null;
        $i          = null;
        $data       = null;
        $infolgo    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START import_status_user_competence';
            $infolog->description 	= 'START import_status_user_competence';
            // Add log
            self::$log[] = $infolog;

            // Call web service
            $response = self::process_tardis_status($plugin,TRADIS_FS_USERS_JOBROLES,$dblog);

            // Import data into temporary tables
            if ($response) {
                // Clean temporary table
                FS::clean_temporary_fellesdata(IMP_COMPETENCE_JR);

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'import_status_user_competence';
                $infolog->description 	= 'Clean: ' . IMP_COMPETENCE_JR;
                // Add log
                self::$log[] = $infolog;

                // Open file
                $path = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS_JOBROLES . '.txt';
                if (file_exists($path)) {
                    // Get last status
                    $content = file($path);

                    // Get total
                    $total = count($content);

                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'import_status_user_competence';
                    $infolog->description 	= 'Total: ' . $total;
                    // Add log
                    self::$log[] = $infolog;

                    // Split the process if it is too big
                    if ($total > MAX_IMP_FS) {
                        for($i=0;$i<=$total;$i=$i+MAX_IMP_FS) {
                            $data = array_slice($content,$i,MAX_IMP_FS,true);
                            FS::save_temporary_fellesdata($data,IMP_COMPETENCE_JR,true);
                        }

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_status_user_competence';
                        $infolog->description 	= 'save_temporary_fellesdata: ' . IMP_COMPETENCE_JR;
                        // Add log
                        self::$log[] = $infolog;
                    }else {
                        FS::save_temporary_fellesdata($content,IMP_COMPETENCE_JR,true);

                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'import_status_user_competence';
                        $infolog->description 	= 'save_temporary_fellesdata: ' . IMP_COMPETENCE_JR;
                        // Add log
                        self::$log[] = $infolog;
                    }//if_max_imp
                }else {
                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'import_status_user_competence';
                    $infolog->description 	= 'File does not exist: ' . $path;
                    // Add log
                    self::$log[] = $infolog;
                }//if_exists
            }//if_data

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH import_status_user_competence';
            $infolog->description 	= 'FINISH import_status_user_competence';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $competence     = null;
        $rdocompetence  = null;
        $response       = null;
        $infolog        = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START sync_status_competence';
            $infolog->description 	= 'START sync_status_competence';
            // Add log
            self::$log[] = $infolog;

            // User competence to synchronize
            $total = FSKS_USERS::get_total_users_competence_to_synchronize(false,true);

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'sync_status_competence';
            $infolog->description 	= 'Total: ' . $total;
            // Add log
            self::$log[] = $infolog;

            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    list($competence,$rdocompetence) = FSKS_USERS::user_competence_to_synchronize(false,true,$start,$limit);

                    // Call web service
                    if ($competence) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'sync_status_competence';
                        $infolog->description 	= 'To synchornize: ' . $competence;
                        // Add log
                        self::$log[] = $infolog;

                        // Params web service
                        $params = array();
                        $params['usersCompetence'] = $competence;

                        $response = self::process_service($plugin,KS_USER_COMPETENCE,$params);
                        if ($response) {
                            if ($response['error'] == '200') {
                                // Synchronize user competence
                                FSKS_USERS::synchronize_user_competence_fs($rdocompetence,$response['usersCompetence']);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'sync_status_competence';
                                $infolog->description 	= 'ERROR WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }else {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'sync_status_competence';
                            $infolog->description 	= 'RESPONSE NOT VALID';
                            // Add log
                            self::$log[] = $infolog;
                        }//if_else_response
                    }//if_competence
               }//for_rdo
            }//if_totla

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH sync_status_competence';
            $infolog->description 	= 'FINISH sync_status_competence';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $total      = null;
        $todelete   = null;
        $params     = null;
        $response   = null;
        $infolog    = null;
        $start      = 0;
        $limit      = 1000;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START sync_status_delete_competence';
            $infolog->description 	= 'START sync_status_delete_competence';
            // Add log
            self::$log[] = $infolog;

            // Get total to delete
            $total = STATUS::total_competence_to_delete_ks();

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'sync_status_delete_competence';
            $infolog->description 	= 'Total: ' . $total;
            // Add log
            self::$log[] = $infolog;

            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // get to delete
                    $todelete = STATUS::competence_to_delete_ks($start,$limit);

                    if ($todelete) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'sync_status_delete_competence';
                        $infolog->description 	= 'To synchronizer: ' . $todelete;
                        // Add log
                        self::$log[] = $infolog;

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
                                $infolog = new stdClass();
                                $infolog->action 		= 'sync_status_delete_competence';
                                $infolog->description 	= 'Error WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }else {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'sync_status_delete_competence';
                            $infolog->description 	= 'RESPONSE NOT VALID';
                            // Add log
                            self::$log[] = $infolog;
                        }//if_else_response
                    }
                }//for
            }//if_total

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH sync_status_delete_competence';
            $infolog->description 	= 'FINISH sync_status_delete_competence';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
    private static function sync_status_delete_managers_reporters($plugin,$type,$level) {
        /* Variables */
        $total       = null;
        $todeleted   = null;
        $params      = null;
        $response    = null;
        $start       = 0;
        $limit       = 1000;
        
        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START sync_status_delete_managers_reporters - Level ' . $level;
            $infolog->description 	= 'START sync_status_delete_managers_reporters - Level ' . $level;
            // Add log
            self::$log[] = $infolog;

            // Get total to delete
            $total = STATUS::total_managers_reporters_to_delete($level,$type);

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START sync_status_delete_managers_reporters - Level ' . $level;
            $infolog->description 	= 'Total: ' . $total;
            // Add log
            self::$log[] = $infolog;

            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get to delete
                    $todeleted = STATUS::managers_reporters_to_delete_ks($level,$type,$start,$limit);

                    if ($todeleted) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'START sync_status_delete_managers_reporters - Level ' . $level;
                        $infolog->description 	= 'To synchronize: ' . $todeleted;
                        // Add log
                        self::$log[] = $infolog;

                        // Call service
                        $params = array();
                        $params['type'] = $type;
                        $params['data'] = $todeleted;
                        $response = self::process_service($plugin,WS_CLEAN_MANAGERS_REPORTERS,array('managersreporters' => $params));

                        if ($response) {
                            if ($response['error'] != '200') {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'START sync_status_delete_managers_reporters - Level ' . $level;
                                $infolog->description 	= 'Error WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }else {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'START sync_status_delete_managers_reporters - Level ' . $level;
                            $infolog->description 	= 'RESPONSE NOT VALID';
                            // Add log
                            self::$log[] = $infolog;
                        }//if_else_response
                    }//if_todeleted
                }//for
            }//if_total
            
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH sync_status_delete_managers_reporters - Level ' . $level;
            $infolog->description 	= 'FINISH sync_status_delete_managers_reporters - Level ' . $level;
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $toSynchronize  = null;
        $rdomanagers    = null;
        $response       = null;
        $total          = null;
        $infolog        = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START sync_status_managers_reporters';
            $infolog->description 	= 'START sync_status_managers_reporters';
            // Add log
            self::$log[] = $infolog;

            // Managers and reporters to synchronize
            $total = FSKS_USERS::get_total_managers_reporters_to_synchronize();

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'sync_status_managers_reporters';
            $infolog->description 	= 'Total: ' . $total;
            // Add log
            self::$log[] = $infolog;

            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // To synchronize
                    list($toSynchronize,$rdomanagers) = FSKS_USERS::get_managers_reporters_to_synchronize($start,$limit,true);

                    // Call webs ervice
                    if ($toSynchronize) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'sync_status_managers_reporters';
                        $infolog->description 	= 'To synchronize: ' . $toSynchronize;
                        // Add log
                        self::$log[] = $infolog;

                        $response = self::process_service($plugin,KS_MANAGER_REPORTER,array('managerReporter' => $toSynchronize));
                        if ($response) {
                            if ($response['error'] == '200') {
                                // Syncrhonize managers and reporters
                                FSKS_USERS::synchronize_manager_reporter_fs($rdomanagers,$response['managerReporter']);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'sync_status_managers_reporters';
                                $infolog->description 	= 'ERROR WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }else {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'sync_status_managers_reporters';
                            $infolog->description 	= 'RESPONSE NOT VALID';
                            // Add log
                            self::$log[] = $infolog;
                        }//if_else_response
                    }//if_toSynchronize
                }//for
            }//if_total

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH sync_status_managers_reporters';
            $infolog->description 	= 'FINISH sync_status_managers_reporters';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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

        try {
            // First users to delete
            self::sync_status_users_accounts_deleted($plugin,$industry);

            // New users accounts
            self::sync_status_new_users_accounts($plugin,$industry);

            // Existing users accounts
            self::sync_status_existing_users_accounts($plugin,$industry);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//sync_status_users_accounts

    /**
     * Description
     * Synchronize status existing users accounts
     * 
     * @param       Object  $plugin
     * @param       String  $industry
     *
     * @throws             Exception
     *
     * @creationDate    07/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_existing_users_accounts($plugin,$industry) {
        /* Variables */
        $rdousers   = null;
        $lstusers   = null;
        $response   = null;
        $total      = null;
        $infolog    = null;
        $start      = 0;
        $limit      = 1000;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START sync_status_existing_users_accounts';
            $infolog->description 	= 'START sync_status_existing_users_accounts';
            // Add log
            self::$log[] = $infolog;

            // get total users accounts
            $total = STATUS::get_total_status_existing_users_accounts();

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'sync_status_existing_users_accounts';
            $infolog->description 	= 'Total: ' . $total;
            // Add log
            self::$log[] = $infolog;

            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get users accounts
                    list($lstusers,$rdousers) = STATUS::get_status_existing_users_accounts($industry,$start,$limit);
                    if ($lstusers) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'sync_status_existing_users_accounts';
                        $infolog->description 	= 'To synchronize: ' . $lstusers;
                        // Add log
                        self::$log[] = $infolog;

                        // Call web service
                        $response = self::process_service($plugin,KS_SYNC_USER_ACCOUNT,array('usersAccounts' => $lstusers));
                        if ($response) {
                            if ($response['error'] == '200') {
                                // Synchronize users accounts FS
                                FSKS_USERS::synchronize_users_fs($rdousers,$response['usersAccounts']);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'sync_status_existing_users_accounts';
                                $infolog->description 	= 'Error WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }else {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'sync_status_existing_users_accounts';
                            $infolog->description 	= 'RESPONSE NOT VALID';
                            // Add log
                            self::$log[] = $infolog;
                        }//if_response
                    }//if_lstusers
                }//for
            }//if_total

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH sync_status_existing_users_accounts';
            $infolog->description 	= 'FINISH sync_status_existing_users_accounts';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//sync_status_existing_users_accounts

    /**
     * Description
     * Synchronize all new users accounts
     *
     * @param           Object  $plugin
     * @param           String  $industry
     *
     * @throws                 Exception
     *
     * @creationDate    07/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_new_users_accounts($plugin,$industry) {
        /* Variables */
        $rdousers   = null;
        $lstusers   = null;
        $response   = null;
        $total      = null;
        $infolog    = null;
        $start      = 0;
        $limit      = 1000;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START sync_status_new_users_accounts';
            $infolog->description 	= 'START sync_status_new_users_accounts';
            // Add log
            self::$log[] = $infolog;

            // get total users accounts
            $total = STATUS::get_total_status_new_users_accounts();

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'sync_status_new_users_accounts';
            $infolog->description 	= 'Total: ' . $total;
            // Add log
            self::$log[] = $infolog;
            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get users accounts
                    list($lstusers,$rdousers) = STATUS::get_status_new_users_accounts($industry,$start,$limit);
                    if ($lstusers) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'sync_status_new_users_accounts';
                        $infolog->description 	= 'To Synchronize: ' . $lstusers;
                        // Add log
                        self::$log[] = $infolog;

                        // Call web service
                        $response = self::process_service($plugin,KS_SYNC_USER_ACCOUNT,array('usersAccounts' => $lstusers));

                        if ($response) {
                            if ($response['error'] == '200') {
                                // Synchronize users accounts FS
                                FSKS_USERS::synchronize_users_fs($rdousers,$response['usersAccounts']);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'sync_status_new_users_accounts';
                                $infolog->description 	= 'Error WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }else {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'sync_status_new_users_accounts';
                            $infolog->description 	= 'RESPONSE NOT VALID';
                            // Add log
                            self::$log[] = $infolog;
                        }//if_response
                    }
                }//for
            }//if_total

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH sync_status_new_users_accounts';
            $infolog->description 	= 'FINISH sync_status_new_users_accounts';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $rdousers   = null;
        $lstusers   = null;
        $response   = null;
        $total      = null;
        $infolog    = null;
        $start      = 0;
        $limit      = 1000;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START sync_status_users_accounts_deleted';
            $infolog->description 	= 'START sync_status_users_accounts_deleted';
            // Add log
            self::$log[] = $infolog;

            // get total users accounts
            $total = STATUS::get_status_total_users_accounts_deleted();

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'sync_status_users_accounts_deleted';
            $infolog->description 	= 'Total to delete: ' . $total;
            // Add log
            self::$log[] = $infolog;

            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get users accounts
                    list($lstusers,$rdousers) = STATUS::get_status_users_accounts_deleted($industry,$start,$limit);
                    if ($lstusers) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'sync_status_users_accounts_deleted';
                        $infolog->description 	= 'To synchronize: ' . $lstusers;
                        // Add log
                        self::$log[] = $infolog;

                        // Call web service
                        $response = self::process_service($plugin,KS_SYNC_USER_ACCOUNT,array('usersAccounts' => $lstusers));

                        if ($response) {
                            if ($response['error'] == '200') {
                                // Synchronize users accounts FS
                                FSKS_USERS::synchronize_users_fs($rdousers,$response['usersAccounts']);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'sync_status_users_accounts_deleted';
                                $infolog->description 	= 'Error WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }else {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'sync_status_users_accounts_deleted';
                            $infolog->description 	= 'RESPONSE NOT VALID';
                            // Add log
                            self::$log[] = $infolog;
                        }//if_response
                    }//if_lstusers
                }//for
            }//if_total

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START sync_status_users_accounts_deleted';
            $infolog->description 	= 'START sync_status_users_accounts_deleted';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        
        try {
            // First new companies
            STATUS::synchronization_status_new_companies($plugin);

            // Companies don't exists any more
            self::synchronization_status_companies_no_exist($plugin);

            // Existing companies
            self::synchronization_status_existing_companies($plugin);
        }catch (Exception $ex) {
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
        $rdocompanies   = null;
        $todelete       = null;
        $response       = null;
        $total          = null;
        $infolog        = null;
        $start          = 0;
        $limit          = 1000;
        
        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START synchronization_status_companies_no_exist';
            $infolog->description 	= 'START synchronization_status_companies_no_exist';
            // Add log
            self::$log[] = $infolog;

            // Get total
            $total = STATUS::get_status_total_companies_to_delete();

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'synchronization_status_companies_no_exist';
            $infolog->description 	= 'Total: ' . $total;
            // Add log
            self::$log[] = $infolog;

            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get companies to delete
                    list($todelete,$rdocompanies) = STATUS::get_status_companies_to_delete($start,$limit);

                    // Call webs service
                    if ($todelete) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'synchronization_status_companies_no_exist';
                        $infolog->description 	= 'To synchronizer: ' . $todelete;
                        // Add log
                        self::$log[] = $infolog;

                        $params     = array('companiesFS' => $todelete);
                        $response   = self::process_service($plugin,KS_SYNC_FS_COMPANY,$params);

                        if ($response) {
                            if ($response['error'] == '200') {
                                FSKS_COMPANY::synchronize_companies_ksfs($rdocompanies,$response['companies']);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'synchronization_status_companies_no_exist';
                                $infolog->description 	= 'ERROR WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }else {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'synchronization_status_companies_no_exist';
                            $infolog->description 	= 'RESPONSE NOT VALID';
                            // Add log
                            self::$log[] = $infolog;
                        }//if_response
                    }//if_toSynchronize
                }//for
            }//if_total

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH synchronization_status_companies_no_exist';
            $infolog->description 	= 'FINISH synchronization_status_companies_no_exist';
            // Add log
            self::$log[] = $infolog;
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
        $rdocompanies   = null;
        $toSynchronize  = null;
        $response       = null;
        $total          = null;
        $infolog        = null;
        $start          = 0;
        $limit          = 1000;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START synchronization_status_existing_companies';
            $infolog->description 	= 'START synchronization_status_existing_companies';
            // Add log
            self::$log[] = $infolog;

            // Get total
            $total = STATUS::get_total_status_existing_companies();

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'synchronization_status_existing_companies';
            $infolog->description 	= 'Total: ' . $total;
            // Add log
            self::$log[] = $infolog;

            if ($total) {
                for ($i=0;$i<=$total;$i=$i+$limit) {
                    // Get companies to delete
                    list($toSynchronize,$rdocompanies) = STATUS::get_status_existing_companies($start,$limit);

                    // Call webs service
                    if ($toSynchronize) {
                        // Log
                        $infolog = new stdClass();
                        $infolog->action 		= 'synchronization_status_existing_companies';
                        $infolog->description 	= 'To synchronize: ' . $toSynchronize;
                        // Add log
                        self::$log[] = $infolog;

                        $params     = array('companiesFS' => $toSynchronize);
                        $response   = self::process_service($plugin,KS_SYNC_FS_COMPANY,$params);

                        if ($response) {
                            if ($response['error'] == '200') {
                                FSKS_COMPANY::synchronize_companies_ksfs($rdocompanies,$response['companies']);
                            }else {
                                // Log
                                $infolog = new stdClass();
                                $infolog->action 		= 'synchronization_status_existing_companies';
                                $infolog->description 	= 'ERROR WS: ' . $response['message'];
                                // Add log
                                self::$log[] = $infolog;
                            }//if_no_error
                        }else {
                            // Log
                            $infolog = new stdClass();
                            $infolog->action 		= 'synchronization_status_existing_companies';
                            $infolog->description 	= 'RESPONSE NOT VALID';
                            // Add log
                            self::$log[] = $infolog;
                        }//if_response
                    }//if_toSynchronize
                }//for
            }//if_total

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH synchronization_status_existing_companies';
            $infolog->description 	= 'FINISH synchronization_status_existing_companies';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronization_status_existing_companies

    /**
     * Description
     * Synchronize status jobroles
     *
     * @param           Object  $plugin
     * 
     * @throws                  Exception
     * 
     * @creationDate    06/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function sync_status_fs_jobroles($plugin) {
        /* Variables */
        $tomail     = null;
        $notifyto   = null;
        $infolog    = null;

        try {
            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'START sync_status_fs_jobroles';
            $infolog->description 	= 'START sync_status_fs_jobroles';
            // Add log
            self::$log[] = $infolog;

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

                    // Log
                    $infolog = new stdClass();
                    $infolog->action 		= 'sync_status_fs_jobroles';
                    $infolog->description 	= 'Send notificaations';
                    // Add log
                    self::$log[] = $infolog;
                }//If_toMail
                
                // Mark as imported the existing ones
                STATUS::sync_status_existing_jobroles();

                // Log
                $infolog = new stdClass();
                $infolog->action 		= 'sync_status_fs_jobroles';
                $infolog->description 	= 'Mark as imported';
                // Add log
                self::$log[] = $infolog;
            }//if_notigyTo

            // Log
            $infolog = new stdClass();
            $infolog->action 		= 'FINISH sync_status_fs_jobroles';
            $infolog->description 	= 'FINISH sync_status_fs_jobroles';
            // Add log
            self::$log[] = $infolog;
        }catch (Exception $ex) {
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
        $domain         = null;
        $token          = null;
        $server         = null;

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
            throw $ex;
        }//try_catch
    }//process_ks_service

    /**
     * Description
     * Call Fellesdata Web service to import last status connected with companies, users...
     *
     * @param           $plugin
     * @param           $service
     * @param           $dblog
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    27/02/2017
     * @author          eFaktor     (fbv)
     */
    private static function process_tardis_status($plugin,$service,&$dblog) {
        /* Variables    */
        global $CFG;
        $dir            = null;
        $backup         = null;
        $original       = null;
        $file           = null;
        $path           = null;
        $url            = null;
        $from           = null;
        $to             = null;
        $date           = null;
        $admin          = null;
        $index          = null;
        $time           = null;

        try {
            // Local time
            $time = time();

            // Check if exists temporary directory
            $dir = $CFG->dataroot . '/fellesdata';
            if (!file_exists($dir)) {
                mkdir($dir);
            }//if_dir

            // Backup
            $backup = $CFG->dataroot . '/fellesdata/backup';
            if (!file_exists($backup)) {
                mkdir($backup);
            }//if_backup

            // Original files
            $original = $CFG->dataroot . '/fellesdata/original';
            if (!file_exists($original)) {
                mkdir($original);
            }//if_backup

            // Get parameters service
            $to     = mktime(1, 60, 0, date("m"), date("d"), date("Y"));
            $to     = gmdate('Y-m-d\TH:i:s\Z',$to);
            $from   = gmdate('Y-m-d\TH:i:s\Z',0);

            // Build url end point
            $url = $plugin->fs_point . '/' . $service . '?fromDate=' . $from . '&toDate=' . $to;
            $url = trim($url);

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

            // Format data
            if ($response === false) {
                // Send notification
                FS_CRON::send_notifications_service($plugin,'STATUS',$service);

                // Log
                $dblog .=  ' ERROR RESPONSE STATUS - NULL OBJECT . ' . "\n";

                return null;
            }else if ($response == null){
                // Log
                $dblog .=  ' ERROR RESPONSE TARDIS - NULL OBJECT . ' . "\n";
                return null;
            }else if (isset($response->status) && $response->status != "200") {
                // Send notification
                FS_CRON::send_notifications_service($plugin,'STATUS',$service);

                // Log
                $dblog .=  ' ERROR RESPONSE STATUS . ' . "\n";
                $dblog .= $response->message . "\n\n";
                $dblog .= "\n" . $response . "\n";

                return null;
            }else {
                // Check the file content
                $index = strpos($response,'html');
                if ($index) {
                    // Send notification
                    FS_CRON::send_notifications_service($plugin,'STATUS',$service);

                    // Log
                    $dblog .=  ' ERROR RESPONSE STATUS . ' . "\n";
                    $dblog .= "\n" . $response . "\n";

                    return null;
                }else {
                    $index = strpos($response,'changeType');
                    if (!$index) {
                        // Log
                        $dblog .=  ' ERROR RESPONSE TARDIS - EMPTY FILE . ' . "\n";
                        return null;
                    }else {
                        // Clean all response
                        $path = $dir . '/' . $service . '.txt';
                        if (file_exists($path)) {
                            // Move the file to the new directory
                            copy($path,$backup . '/' . $service . '_' . $time . '.txt');

                            unlink($path);
                        }

                        // Remove bad characters
                        $content = str_replace('\"','"',$response);
                        // CR - LF && EOL
                        $content = str_replace('\r\n',chr(13),$content);
                        $content = str_replace('\r',chr(13),$content);
                        $content = str_replace('\n',chr(13),$content);

                        // Create a new response file
                        $file = fopen($path,'w');
                        fwrite($file,$content);
                        fclose($file);

                        return true;
                    }//if_index
                }//if_else_index
            }//if_response
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//process_tradis_service
}//STATUS_CRON