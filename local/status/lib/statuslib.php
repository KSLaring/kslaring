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
 * Fellesdata Status Integration - Library
 *
 * @package         local/status
 * @subpackage      lib
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    24/02/2017
 * @author          eFaktor     (fbv)
 *
 */

define('WS_COMPETENCE','wsCompetence');
define('WS_DEL_COMPETENCE','ws_delete_competence');
define('WS_MANAGERS_REPORTERS','ws_get_managers_reporters');
define('WS_CLEAN_MANAGERS_REPORTERS','ws_clean_managers_reporters');

define('MANAGERS','manager');
define('REPORTERS','reporter');

define('SYNC_STATUS_COMP','companies');
define('SYNC_STATUS_JR','jobroles');

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

        try {
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
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//save_competence

    /**
     * Description
     * Import managers/reporters status
     * @param           $data
     * @param           $type
     *
     * @throws          Exception
     *
     * @creationDate    01/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function save_managers_reporters($data,$type) {
        /* Variables */
        global $CFG;
        $dir    = null;
        $backup = null;
        $path   = null;

        try {
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
        }catch (Exception $ex) {
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
        $sql        = null;
        $rdo        = null;
        $params     = null;

        try {
            //Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = STATUS;

            // SQL Instruction
            $sql = " SELECT		count(DISTINCT uic.username) as 'total'
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
     * @return          array|string
     * @throws          Exception
     * 
     * @creationDate    28/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function competence_to_delete_ks($start,$limit) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $params     = null;
        $todelete   = null;

        try {
            //Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = STATUS;

            // SQL Instruction
            $sql = " SELECT		DISTINCT
                                  uic.userid as 'user',
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
            if ($rdo) {
                $todelete = json_encode($rdo);
            }


            return $todelete;
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

    /**
     * Description
     * Get total managers/reporters to delete
     *
     * @param       string  $type
     *
     * @return              null
     * @throws              Exception
     *
     * @creationDate    03/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function total_managers_reporters_to_delete($type) {
        /* Variables */
        global $DB;
        $table  = null;
        $field  = null;
        $sql    = null;
        $params = null;

        try {
            //Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = STATUS;

            switch ($type) {
                case MANAGERS:
                    $table = "user_managers";

                    break;
                case REPORTERS:
                    $table = "user_reporters";

                    break;
            }//switch

            // SQL Instruction
            $sql = " SELECT  count(DISTINCT mr.id) as 'total'
                     FROM		{". $table . "} 			mr
                        JOIN	{fs_imp_managers_reporters}	fs	ON  fs.FODSELSNR    = mr.username
                         										AND fs.action       = :action
                                                                AND fs.imported     = :imported ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_rdo

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//total_managers_reporters_to_delete

    /**
     * Description
     * Get managers/reporters to delete from KS (STATUS)
     * @param       String  $type
     * @param               $start
     * @param               $limit
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    03/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function managers_reporters_to_delete_ks($type,$start,$limit) {
        /* Variables */
        global $DB;
        $table      = null;
        $field      = null;
        $sql        = null;
        $todelete   = null;
        $params     = null;

        try {
            //Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = STATUS;

            switch ($type) {
                case MANAGERS:
                    $table = "user_managers";

                    break;
                case REPORTERS:
                    $table = "user_reporters";

                    break;
            }//switch

            // SQL Instruction
            $sql = " SELECT  DISTINCT 
                                  mr.id     as 'key',
                                  mr.userid as 'user'
                     FROM		{" . $table . "} 		    mr
                        JOIN	{fs_imp_managers_reporters}	fs	ON  fs.FODSELSNR = mr.username
                                                                AND fs.action 	 = :action
                                                                AND fs.imported  = :imported ";
            
            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);
            if ($rdo) {
                $todelete = json_encode($rdo);
            }//if_rdo
            
            return $todelete;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//managers_reporters_to_delete

    /**
     * Description
     * Synchronization of managers/reporters that have been deleted
     *
     * @param           $type
     * @param           $deleted
     *
     * @throws          Exception
     *
     * @creationDate    03/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function synchronize_managers_reporters_deleted($type,$deleted) {
        /* Variables */
        global $DB;
        $table      = null;
        $field      = null;

        try {
            // Select table
            switch ($type) {
                case MANAGERS:
                    $table = "user_managers";

                    break;
                case REPORTERS:
                    $table = "user_reporters";

                    break;
            }//switch_type

            // SQL Instruction
            $sql = " DELETE 
                     FROM {" . $table . "} 
                     WHERE id IN ($deleted) ";

            // Execute
            $DB->execute($sql);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//synchronize_managers_reporters_deleted

    /**
     * Description
     * Synchronize new companies
     * 
     * @param           Object $plugin
     * 
     * @throws                 Exception
     * 
     * @creationDate    05/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function synchronization_status_new_companies($plugin) {
        /* Variables */
        $newcompanies = null;
        $notifyTo     = null;
        
        try {
            // Get new comapnies
            $newcompanies = self::get_new_fs_organizations($plugin);
            if ($newcompanies) {
                // Notifications
                if ($plugin->mail_notification) {
                    $notifyTo   = explode(',',$plugin->mail_notification);

                    // Send notifications
                    self::send_notification(SYNC_STATUS_COMP,$newcompanies,$notifyTo);
                }//if_mail_notifications
                
            }//if_newcompanies
        }catch (Exception $ex){
            throw $ex;
        }//try_catch
    }//synchronization_status_new_comapnies

    /**
     * Description
     * Send notifications
     *
     * @param           array $toMail
     * @param           array $notifyTo
     *
     * @throws                Exception
     *
     * @creationDate    05/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function send_notification($type,$toMail,$notifyTo) {
        /* Variables */
        global $USER,$SITE;
        $urlMapping = null;
        $subject    = null;
        $body       = null;
        $info       = null;
        $to         = null;

        try {
            // Subject
            $subject = (string)new lang_string('subject','local_fellesdata',$SITE->shortname,$USER->lang);

            // Body
            switch ($type) {
                case SYNC_STATUS_COMP:
                    // url mapping
                    $urlMapping = new moodle_url('/local/fellesdata/mapping/mapping_org.php',array('m' => 'co'));

                    // Body to sent
                    $info = new stdClass();
                    $info->companies = implode(',',$toMail);
                    $info->mapping  = $urlMapping;
                    $body = (string)new lang_string('body_company_to_sync','local_fellesdata',$info,$USER->lang);

                    break;
                case SYNC_STATUS_JR:
                    // Url mapping
                    $urlMapping = new moodle_url('/local/fellesdata/mapping/jobroles.php');

                    // Body to sent
                    $info = new stdClass();
                    $info->jobroles = implode(',',$toMail);
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
            throw $ex;
        }//try_catch
    }//send_notification
    
    /**
     * Description
     * Get total companies that do not exist any more and they have to be deleted
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    05/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_status_total_companies_to_delete() {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;

        try {
            // SQL Instruction
            $sql = " SELECT	count(DISTINCT fs.id) as 'total'		
                     FROM	  		{fs_company}		fs
                        -- INFO KS
                        JOIN  		{ksfs_company}	    fk 		ON 	fk.fscompany 	    = fs.companyid
                        -- INFO PARENT
                        JOIN  		{ks_company}		ks_pa	ON 	ks_pa.companyid     = fk.kscompany
                        LEFT JOIN   {fs_imp_company}	fs_imp 	ON 	fs_imp.org_enhet_id = fs.companyid
                     WHERE 	fs_imp.id IS NULL ";

            // Execute
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_status_total_companies_to_delete

    /**
     * Description
     * Get companies that do not exist any more and they have to be deleted
     *
     * @param           integer $start
     * @param           integer $limit
     *
     * @return                  array
     * @throws                  Exception
     *
     * @creationDate    05/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_status_companies_to_delete($start,$limit) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $companies  = null;

        try {
            // SQL Instruction
            $sql = " SELECT	 DISTINCT
                                  fs.companyid                          as 'fsid',
                                  fk.kscompany                          as 'ksid',
                                  TRIM(fs.name)                         as 'name',
                                  fs.level,
                                  fs.parent,
                                  ks_pa.industrycode                    as 'industry',
                                  TRIM(IF(fs.privat,0,1)) 	            as 'public',
                                  TRIM(IF(fs.ansvar != '',fs.ansvar,0))       as 'ansvar',
                                  TRIM(IF(fs.tjeneste != '',fs.tjeneste,0))   as 'tjeneste',
                                  TRIM(IF(fs.adresse1 != '',fs.adresse1,0))   as 'adresse1',
                                  TRIM(IF(fs.adresse2 != '',fs.adresse2,0))   as 'adresse2',
                                  TRIM(IF(fs.adresse3 != '',fs.adresse3,0))   as 'adresse3',
                                  TRIM(IF(fs.postnr != '',fs.postnr,0))       as 'postnr',
                                  TRIM(IF(fs.poststed != '',fs.poststed,0))   as 'poststed',
                                  TRIM(IF(fs.epost != '',fs.epost,0))         as 'epost',
                                  '2' 							        as 'action'
                     FROM	  	  {fs_company}		fs
                        -- INFO KS
                        JOIN  	  {ksfs_company}	fk 		ON 	fk.fscompany 	    = fs.companyid
                        -- INFO PARENT
                        JOIN  	  {ks_company}		ks_pa	ON 	ks_pa.companyid     = fk.kscompany
                        LEFT JOIN {fs_imp_company}	fs_imp 	ON 	fs_imp.org_enhet_id = fs.companyid
                     WHERE 	fs_imp.id IS NULL ";

            // Execute
            $rdo = $DB->get_records_sql($sql,null,$start,$limit);
            if ($rdo) {
                $companies = json_encode($rdo);
            }//if_rdo

            return array($companies,$rdo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_status_companies_to_delete

    /**
     * Description
     * Get total status existing companies
     *
     * @return          null
     *
     * @throws          Exception
     *
     * @creationDate    05/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_total_status_existing_companies() {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['action']   = STATUS;
            $params['imported'] = 0;

            // SQL Instruction
            $sql = " SELECT	  count(DISTINCT fs.id) as 'total'		
                     FROM	  {fs_company}		fs
                        -- INFO KS
                        JOIN  {ksfs_company}	fk 		ON 	fk.fscompany 	    = fs.companyid
                        -- INFO PARENT
                        JOIN  {ks_company}		ks_pa	ON 	ks_pa.companyid     = fk.kscompany
                        JOIN  {fs_imp_company}	fs_imp 	ON 	fs_imp.org_enhet_id = fs.companyid
                     WHERE 	  fs_imp.action 	= :action
                        AND   fs_imp.imported 	= :imported ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_status_existing_companies

    /**
     * Description
     * Get existing companies
     * 
     * @param       integer $start
     * @param       integer $limit
     * 
     * @return              array
     * @throws              Exception
     * 
     * @creationDate    05/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_status_existing_companies($start,$limit) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $companies  = null;
        $sql        = null;
        $params     = null;

        try {
            // Search criteria
            $params = array();
            $params['action']   = STATUS;
            $params['imported'] = 0;

            // SQL Instruction
            $sql = " SELECT	DISTINCT 
                              fs.companyid                                      as 'fsid',
                              fk.kscompany                                      as 'ksid',
                              IF(fs.level > 1,TRIM(fs.name), TRIM(ks_pa.name))  as 'name',
                              fs.level,
                              fs.parent,
                              ks_pa.industrycode                    as 'industry',
                              TRIM(IF(fs.privat,0,1)) 	            as 'public',
                              TRIM(IF(fs.ansvar != '',fs.ansvar,0))       as 'ansvar',
                              TRIM(IF(fs.tjeneste != '',fs.tjeneste,0))   as 'tjeneste',
                              TRIM(IF(fs.adresse1 != '',fs.adresse1,0))   as 'adresse1',
                              TRIM(IF(fs.adresse2 != '',fs.adresse2,0))   as 'adresse2',
                              TRIM(IF(fs.adresse3 != '',fs.adresse3,0))   as 'adresse3',
                              TRIM(IF(fs.postnr != '',fs.postnr,0))       as 'postnr',
                              TRIM(IF(fs.poststed != '',fs.poststed,0))   as 'poststed',
                              TRIM(IF(fs.epost != '',fs.epost,0))         as 'epost',
                              fs_imp.action
                     FROM	  {fs_company}		fs
                        -- INFO KS
                        JOIN  {ksfs_company}	fk 		ON 	fk.fscompany 	    = fs.companyid
                        -- INFO PARENT
                        JOIN  {ks_company}		ks_pa	ON 	ks_pa.companyid     = fk.kscompany
                        JOIN  {fs_imp_company}	fs_imp 	ON 	fs_imp.org_enhet_id = fs.companyid
                     WHERE 	  fs_imp.action 	= :action
                        AND	  fs_imp.imported   = :imported ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);
            if ($rdo) {
                $companies = json_encode($rdo);
            }//if_Rdo

            return array($companies,$rdo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//get_status_existing_companies

    /**
     * Description
     * mark as imported all jobroles that already exist
     * 
     * @throws          Exception
     * 
     * @creationDate    06/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function sync_status_existing_jobroles() {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;
        $time   = null;

        try {
            // Local time
            $time = time();

            // Search criteria
            $params = array();
            $params['action']   = DELETE;
            $params['imported'] = 0;

            // SQL Insturction
            $sql = " SELECT  DISTINCT 
                                fs.id,
                                fs.imported
                     FROM		{fs_imp_jobroles}   fs
                        JOIN	{fs_jobroles}		fsjr	ON  fsjr.jrcode 	= fs.stillingskode
                        JOIN	{ksfs_jobroles}		ksfs	ON 	ksfs.fsjobrole  = fsjr.jrcode
                     WHERE      fs.action   != :action
                        AND		fs.imported  = :imported ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                // Update as imported
                foreach ($rdo as $instance) {
                    $instance->imported     = 1;
                    $instance->timemodified = $time;
                    
                    // Execute
                    $DB->update_record('fs_imp_jobroles',$instance);
                }//if_Rdo
            }//if_rdo

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//sync_status_existing_jobroles

    /**
     * Description
     * Get total users accounts that don't exist any more
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    06/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_status_total_users_accounts_deleted() {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;
        $sql    = null;

        try {
            // Search criteria
            $params = array();
            $params['auth']     = 'saml';
            $params['deleted']  = 0;

            // SQL Instruction
            $sql = " SELECT	      count(u.id) as 'total'
                     FROM		  {user}		  u
                        LEFT JOIN {fs_imp_users}  fs ON fs.fodselsnr = u.idnumber
                     WHERE 	      fs.id IS NULL
                          AND     u.auth 		= :auth
                          AND     u.deleted 	= :deleted
                          AND     u.idnumber IS NOT NULL
                          AND     u.idnumber != ''";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_status_total_users_accounts_deleted

    /**
     * Description
     * Get all users accounts that don't exist any more
     *
     * @param           $industry
     * @param           $start
     * @param           $limit
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    06/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_status_users_accounts_deleted($industry,$start,$limit) {
        /* Variables */
        global $DB;
        $params     = null;
        $rdo        = null;
        $userssacc  = null;
        $sql        = null;

        try {
            // Search criteria
            $params = array();
            $params['auth']     = 'saml';
            $params['deleted']  = 0;

            // SQL Instruction
            $sql = " SELECT	      u.id,
                                  u.idnumber 		as 'personalnumber',
                                  u.username 		as 'adfs',
                                  u.firstname,
                                  u.lastname,
                                  '' 				as 'ressursnr',
                                  $industry 		as 'industry',
                                  u.email,
                                  '2' 			    as 	'action'
                      FROM		  {user}			u
                        LEFT JOIN {fs_imp_users}	fs ON fs.fodselsnr = u.idnumber
                      WHERE 	  fs.id IS NULL
                          AND     u.auth 		= :auth
                          AND     u.deleted 	= :deleted
                          AND     u.idnumber IS NOT NULL
                          AND     u.idnumber != '' ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);
            if ($rdo) {
                $userssacc = json_encode($rdo);
            }//if_rdo

            return array($userssacc,$rdo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_status_users_accounts_deleted

    /**
     * Description
     * Get total all new users accounts
     * 
     * @return          null
     * @throws          Exception
     * 
     * @creationDate    07/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_total_status_new_users_accounts() {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;
        $sql    = null;
        $total  = null;

        try {
            // Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = STATUS;

            // SQL Instruction
            $sql = " SELECT			count(fs.id) as 'total'
                     FROM			{fs_imp_users}	fs
                        LEFT JOIN	{user}			u ON u.idnumber = fs.fodselsnr
                     WHERE 			fs.imported = :imported
                            AND		fs.action 	= :action
                            AND 	u.id IS NULL ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_status_total_new_users_accounts

    /**
     * Description
     * Get all new users accounts
     * 
     * @param       String  $industry
     * @param               $start
     * @param               $limit
     *
     * @return              array
     * @throws              Exception
     *
     * @creationDate    07/03/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_status_new_users_accounts($industry,$start,$limit) {
        /* Variables */
        global $DB;
        $params   = null;
        $rdo      = null;
        $sql      = null;
        $lstusers = null;

        try {
            // Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = STATUS;

            // SQL Instruction
            $sql = " SELECT	fs.id,
                            trim(fs.fodselsnr) 											as 'personalnumber',
                            IF (fs.brukernavn,fs.brukernavn,0) 							as 'adfs',
                            IF (fs.ressursnr,fs.ressursnr,0) 							as 'ressursnr',
                            $industry													as 'industry',
                            CONCAT(fs.fornavn,' ',IF(fs.mellomnavn,fs.mellomnavn,'')) 	as 'firstname',
                            trim(fs.etternavn) 											as 'lastname',
                            trim(fs.epost) 												as 'email',
                            fs.action
                     FROM			{fs_imp_users}	fs
                        LEFT JOIN	{user}			u ON u.idnumber = fs.fodselsnr
                     WHERE 			fs.imported = :imported
                            AND		fs.action 	= :action
                            AND 	u.id IS NULL ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);
            if ($rdo) {
                $lstusers = json_encode($rdo);
            }
            
            return array($lstusers,$rdo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_status_new_users_accounts

    /**
     * Description
     * Get total of existing users accounts last (status)
     * @throws          Exception
     *
     * @creationDate    07/03/2017
     * @author          eFaktor (fbv)
     */
    public static function get_total_status_existing_users_accounts() {
        /* Variables */
        global $DB,$CFG;
        $rdo    = null;
        $sql    = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = STATUS;

            // SQL Instruction
            $sql = " SELECT		count(DISTINCT fs.id) as 'total'
                     FROM		{fs_imp_users}	fs
                        JOIN	{user}			u ON u.idnumber = fs.fodselsnr
                     WHERE 		fs.imported = :imported
                        AND		fs.action 	= :action ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_status_existing_users_accounts

    /**
     * Description
     * Get all existing users accounts
     * 
     * @param           String  $industry
     * @param                   $start
     * @param                   $limit
     * 
     * @return                  array
     * @throws                  Exception
     */
    public static function get_status_existing_users_accounts($industry,$start,$limit) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        $lstusers   = null;

        try {
            // Search criteria
            $params = array();
            $params['imported'] = 0;
            $params['action']   = STATUS;

            // SQL Instruction
            $sql = " SELECT	DISTINCT 
                                fs.id,
                                trim(fs.fodselsnr) 											as 'personalnumber',
                                IF (fs.brukernavn,fs.brukernavn,0) 							as 'adfs',
                                IF (fs.ressursnr,fs.ressursnr,0) 							as 'ressursnr',
                                $industry 													as 'industry',
                                CONCAT(fs.fornavn,' ',IF(fs.mellomnavn,fs.mellomnavn,'')) 	as 'firstname',
                                trim(fs.etternavn) 											as 'lastname',
                                trim(fs.epost) 												as 'email',
                                fs.action
                     FROM		{fs_imp_users}	fs
                        JOIN	{user}			u ON u.idnumber = fs.fodselsnr
                     WHERE 		fs.imported = :imported
                        AND		fs.action 	= :action ";
            
            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);
            if ($rdo) {
                $lstusers = json_encode($rdo);
            }//if_rdo
            
            return array($lstusers,$rdo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_status_existing_users_accounts



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

            // Each line file
            foreach($content as $key => $instance) {
                $line = json_decode($instance);
                $line->timemodified = $time;

                // Add record
                $DB->insert_record('user_info_competence_data',$line);
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
     * @param           String  $path
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
            $content = file($path);

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
                $line = json_decode($instance);
                $line->timemodified = $time;

                // Add record
                $DB->insert_record($table,$line);
            }//for_line

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//import_managers_reporters

    /**
     * Description
     *
     * @param           Object $plugin
     *
     * @return                 null
     * @throws                 Exception
     *
     * @creationDate    05/03/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_new_fs_organizations($plugin) {
        /* Variables */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $params         = null;
        $newcompanies   = null;
        $levels         = null;
        $params         = null;

        try {
            // Search criteria
            $params = array();
            $params['del'] = DELETE;

            // Get levels
            $levels = $plugin->map_two . "," . $plugin->map_three;

            // SQL Instruction
            $sql = " SELECT			fs_imp.id,
                                    fs_imp.ORG_NAVN
                     FROM			{fs_imp_company}	fs_imp
                        LEFT JOIN	{fs_company}		fs		ON fs.companyid = fs_imp.ORG_ENHET_ID
                     WHERE 	        fs.id IS NULL
                        AND         fs_imp.org_nivaa IN ($levels) 
                        AND         fs_imp.action != :del
                     LIMIT 0,5 ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $newcompanies[$instance->id] = $instance->org_navn;
                }//for_rdo
            }//if_Rdo

            return $newcompanies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_new_fs_organizations
}//status