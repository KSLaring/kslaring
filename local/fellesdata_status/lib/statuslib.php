<?php
/**
 * Fellesdata Status Integration - Library
 *
 * @package         local/fellesdata_status
 * @subpackage      cron
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    24/02/2017
 * @author          eFaktor     (fbv)
 *
 */

define('WS_COMPETENCE','ws_get_competence');

class STATUS {
    /**********/
    /* PUBLIC */
    /**********/

    public static function backup_temporary_fellesdata($type) {
        /* Variables */
        global $CFG;
        $file           = null;
        $backupstatus   = null;
        $content        = null;
        $path           = null;
        $time           = null;

        try {
            // Local time
            $time = time();

            $backupstatus = $CFG->dataroot . '/fellesdata/backup_status';
            if (!file_exists($backupstatus)) {
                mkdir($backupstatus);
            }//if_backup

            switch ($type) {
                case IMP_USERS:
                    // IMP USERS
                    $path = $backupstatus . "/fs_imp_users_" . $time . ".txt";
                    self::backup_imp_fs_tables($path,'fs_imp_users');

                    break;

                case IMP_COMPANIES:
                    // IMP COMPANIES
                    $path = $backupstatus . "/fs_imp_company_" . $time . ".txt";
                    self::backup_imp_fs_tables($path,'fs_imp_company');

                    break;

                case IMP_JOBROLES:
                    // IMP JOBROLES
                    $path = $backupstatus . "/fs_imp_jobroles_" . $time . ".txt";
                    self::backup_imp_fs_tables($path,'fs_imp_jobroles');

                    break;

                case IMP_MANAGERS_REPORTERS:
                    // IMP MANAGERS_REPORTERS
                    $path = $backupstatus . "/fs_imp_managers_reporters_" . $time . ".txt";
                    self::backup_imp_fs_tables($path,'fs_imp_managers_reporters');

                    break;

                case IMP_COMPETENCE_JR:
                    // IMP COMPETENCE JR
                    $path = $backupstatus . "/fs_imp_users_jr_" . $time . ".txt";
                    self::backup_imp_fs_tables($path,'fs_imp_users_jr');

                    break;
            }//type
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//backup_temporary_fellesdata
    
    /**
     * Description
     * Get industry code
     * 
     * @param       String $muni
     * 
     * @return      int|string
     * @throws      Exception
     * 
     * @creationDate    24/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_industry_code($muni) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $params = null;

        try {
            if ($muni) {
                // Search criteria
                $params = array();
                $params['name']             = $muni;
                $params['hierarchylevel']   = 1;

                // Execute
                $rdo = $DB->get_record('ks_company',$params,'industrycode');
                if ($rdo) {
                    $industrycode = trim($rdo->industrycode);
                } else {
                    $industrycode = 0;
                }//if_rdo
            }else {
                $industrycode = 0;
            }//if muni

            return $industrycode;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_industry_code

    /**
     * Description
     * Save competence data coming rom the service
     *
     * @param       String $competence
     * @throws      Exception
     *
     * @creationDate    27/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function save_competence($competence) {
        /* Variables */
        global $CFG;
        $dir    = null;
        $backup = null;
        $path   = null;
        $dblog  = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START FELLESDATA STATUS Import competence data . ' . "\n";

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
            $path = $dir . '/' . WS_COMPETENCE . '.txt';
            if (file_exists($path)) {
                // Move the file to the new directory
                copy($path,$backup . '/' . WS_COMPETENCE . '_' . time() . '.txt');

                unlink($path);
            }//if_file_exist

            // Create a new response file
            $file = fopen($path,'w');
            fwrite($file,$competence);
            fclose($file);

            // Import into DB
            self::import_competence_data($path);

            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA STATUS Import competence data. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            $dbLog = $ex->getMessage() . "\n" ."\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA STATUS ERROR Import competence data. ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//save_competence

    
    /***********/
    /* PRIVATE */
    /***********/
    

    /**
     * Description
     * Impot the content of the file into the DB
     * 
     * @param           String $competence
     *
     * @throws          Exception
     *
     * @creationDate    27/02/2017
     * @author          eFaktor     (fbv)
     */
    private static function import_competence_data($competence) {
        /* Variables */
        global $DB;
        $content     = null;
        $instance    = null;
        $line        = null;
        $key         = null;
        
        try {
            // Local time
            $time = time();
            
            // Get content
            $content = file($competence);

            // Each line file
            foreach($content as $key=>$line) {
                $instance    = json_decode($line);
                $instance->timemodified = $time;

                // Add record
                $DB->insert_record('user_info_competence_data',$instance);
            }//for_line
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//import_competence_data
}//status