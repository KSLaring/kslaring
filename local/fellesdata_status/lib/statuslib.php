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

define('WS_COMPETENCE','wsCompetence');
define('WS_DEL_COMPETENCE','ws_delete_competence');
define('WS_MANAGERS_REPORTERS','ws_managers_reporters');
define('MANAGERS','managers');
define('REPORTERS','reporters');

class STATUS {
    /**********/
    /* PUBLIC */
    /**********/
    
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
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START FELLESDATA STATUS Save competence data . ' . "\n";

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
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA STATUS Save competence data. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            $dbLog = "ERROR: " . $ex->getMessage() . "\n" ."\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA STATUS ERROR Save competence data. ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//save_competence

    public static function save_managers_reporters($data,$type) {
        /* Variables */
        global $CFG;
        $dir        = null;
        $backup = null;
        $path   = null;
        $dblog  = null;

        try {
            // Log
            $dblog = userdate(time(),'%d.%m.%Y', 99, false). ' START FELLESDATA STATUS Save Managers Reporters . ' . "\n";

            // Check if exists temporary directory
            $dir = $CFG->dataroot . '/fellesdata';
            if (!file_exists($dir)) {
                mkdir($dir);
            }//if_dir

            $backup = $CFG->dataroot . '/fellesdata/backup';
            if (!file_exists($backup)) {
                mkdir($backup);
            }//if_backup

            // Clean
            $path = $dir . '/ws_' . $type .'.txt';
            if (file_exists($path)) {
                // Move the file to the new directory
                copy($path,$backup . '/ws_' . $type .'_' . time() . '.txt');

                unlink($path);
            }//if_file_exist

            // Create a new response file
            $file = fopen($path,'w');
            fwrite($file,$data);
            fclose($file);
            
            // Save Managers/Reporters
            self::import_managers_reporters($path,$type);
            
            // Log
            $dblog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA STATUS Save competence data. ' . "\n";
            error_log($dblog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            $dbLog = "ERROR: " . $ex->getMessage() . "\n" ."\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH FELLESDATA STATUS Save Managers Reporters. ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//save_managers_reporters

    /**
     * Description
     * Get total of records to delete
     * 
     * @return          null
     * @throws          Exception
     * 
     * @creationDate    28/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function total_competence_to_delete_ks() {
        /* Variables */
        global $DB;
        $sql    = null;
        $rdo    = null;
        $params = null;

        try {
            //Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = STATUS;

            // SQL Instruction
            $sql = " SELECT		count(*) as 'total'
                     FROM	    {fs_imp_users_jr}	  		fs
                        JOIN    {user}              		u       ON  u.idnumber 			= fs.fodselsnr
                                                                    AND u.deleted  			= 0
                        -- COMPANY
                        JOIN	{ksfs_company}				ksfs 	ON 	ksfs.fscompany 		= fs.ORG_ENHET_ID
                        JOIN	{ks_company}		  		ks	    ON	ks.companyid		= ksfs.kscompany

                        JOIN	(
                                    SELECT	username,
                                            GROUP_CONCAT(DISTINCT companyid ORDER BY companyid SEPARATOR ',') 	as 'companies',
                                            GROUP_CONCAT(DISTINCT id ORDER BY companyid SEPARATOR ',') 			as 'ids'
                                    FROM	{user_info_competence_data}	
                                    GROUP BY username    
                                ) uic 	ON fs.fodselsnr = uic.username
                                        AND LOCATE(ks.companyid,uic.companies) = 0
                     WHERE		fs.imported = :imported
                        AND		fs.action 	= :action ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//total_competence_to_delete_ks

    /**
     * Description
     * Get all comptence that have to be deleted
     * @param           $start
     * @param           $limit
     * 
     * @return          array|null
     * @throws          Exception
     * 
     * @creationDate    28/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function competence_to_delete_ks($start,$limit) {
        /* Variables */
        global $CFG;
        global $DB;
        $dblog      = null;
        $sql        = null;
        $rdo        = null;
        $params     = null;
        $todelete   = array();

        try {
            //Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = STATUS;

            // SQL Instruction
            $sql = " SELECT		uic.userid as 'user',
                                uic.companies,
                                uic.ids as 'keys'
                     FROM	    {fs_imp_users_jr}	  		fs
                        JOIN    {user}              		u       ON  u.idnumber 			= fs.fodselsnr
                                                                    AND u.deleted  			= 0
                        -- COMPANY
                        JOIN	{ksfs_company}				ksfs 	ON 	ksfs.fscompany 		= fs.ORG_ENHET_ID
                        JOIN	{ks_company}		  		ks	    ON	ks.companyid		= ksfs.kscompany
                        -- COMPETENCE
                        JOIN	(
                                    SELECT	 username,
                                             userid,
                                             GROUP_CONCAT(DISTINCT companyid ORDER BY companyid SEPARATOR ',') 	as 'companies',
                                             GROUP_CONCAT(DISTINCT id ORDER BY companyid SEPARATOR ',') 		as 'ids'
                                    FROM	 {user_info_competence_data}	
                                    GROUP BY username    
                                ) uic 	ON fs.fodselsnr = uic.username
                                        AND LOCATE(ks.companyid,uic.companies) = 0
                     WHERE		fs.imported = :imported
                        AND		fs.action 	= :action
                     ORDER BY fs.fodselsnr ";
            
            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//competence_to_delete_ks

    /**
     * Description
     * Synchronized competence that have been deleted
     * 
     * @param       string  $deleted     Competence deleted
     * 
     * @throws              Exception
     * 
     * @creationDate        28/02/2017
     * @author              eFaktor     (fbv)
     */
    public static function synchronize_competence_deleted($deleted) {
        /* Variables */
        global $DB;
        $sql = null;

        try {
            // SQL Instruction
            $sql = " DELETE FROM {user_info_competence_data}
                     WHERE id IN ($deleted) ";

            // Execute
            $DB->execute($sql);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronize_competence_deleted

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Import the content of the file into the DB
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
        $trans       = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();
        
        try {
            // Local time
            $time = time();
            
            // First delete all old records
            $DB->delete_records('user_info_competence_data');
            
            // Get content
            $content = file($competence);
            $content = json_decode($content);

            // Each line file
            foreach($content as $key=>$instance) {
                $instance->timemodified = $time;

                // Add record
                $DB->insert_record('user_info_competence_data',$instance);
            }//for_line
            
            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);
            
            throw $ex;
        }//try_catch
    }//import_competence_data

    /**
     * Description
     * Import the content of the file into the DB
     * 
     * @param           String  $data
     * @param           String  $type
     * 
     * @throws                  Exception
     * 
     * @creationDate    02/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function import_managers_reporters($path,$type) {
        /* Variables */
        global $DB;
        $content     = null;
        $instance    = null;
        $line        = null;
        $key         = null;
        $table       = null;
        $trans       = null;

        // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();
            
            // Get content
            $content = file_get_contents($path);
            $content = json_decode($content);

            // Select table
            switch ($type) {
                case MANAGERS:
                    // First delete all old records
                    $DB->delete_records('user_managers');
                    $table = 'user_managers';

                    break;
                case REPORTERS:
                    // First delete all old records
                    $DB->delete_records('user_reporters');
                    $table = 'user_reporters';

                    break;
            }//switch

            // Each line file
            foreach($content as $key=>$instance) {
                $instance->timemodified = $time;

                // Add record
                $DB->insert_record($table,$instance);
            }//for_line

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//import_managers_reporters

}//status