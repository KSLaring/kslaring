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
define('TEST_FS_USER_COMP',6);
define('TEST_FS_USER_COMP_JR',7);
define('TEST_FS_SYNC_ORG',8);
define('TEST_FS_SYNC_JR',9);
define('TEST_FS_SYNC_COMP_U',10);
define('TEST_FS_SYNC_COMP_JR',11);
define('TEST_FS_SYNC_FS_USERS',12);

class FELLESDATA_CRON {
    /**********/
    /* PUBLIC */
    /**********/

    public static function cronXXXX($fstExecution) {
        /* Variables    */
        global $SESSION;
        $pluginInfo = null;


        try {
            if (isset($SESSION->manual)) {
                unset($SESSION->manual);
            }
            /* Plugin Info      */
            $pluginInfo     = get_config('local_fellesdata');

            ini_set('memory_limit','1024M');

            /* Import KS */
            //self::ImportKS($pluginInfo);

            /* Import Fellesdata        */
            //self::ImportFellesdata($pluginInfo);


            /* SYNCHRONIZATION  */
            /* Synchronization Users Accounts   */
            //self::UsersFS_Synchronization($pluginInfo);

            /* Synchronization Companies    */
            //self::CompaniesFS_Synchronization($pluginInfo,$fstExecution);

            /* Synchronization Job Roles    */
            //self::JobRolesFS_Synchronization($pluginInfo,$fstExecution);

            /* Synchronization Comeptence   */
            if (!$fstExecution) {
                /* Synchronization User Competence Company  */
                //self::UserCompetence_Synchronization($pluginInfo,IMP_COMPETENCE_COMP,KS_USER_COMPETENCE_CO);

                /* Synchronization User Competence JobRole  */
                //self::UserCompetence_Synchronization($pluginInfo,IMP_COMPETENCE_JR,KS_USER_COMPETENCE_JR);
           }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//cron

    public static function cron_manual($fstExecution,$option) {
        /* Variables    */
        $pluginInfo = null;

        try {
            /* Plugin Info      */
            $pluginInfo     = get_config('local_fellesdata');

            ini_set('memory_limit','1024M');

            switch ($option) {
                case TEST_ORG:
                    echo "Organization Structure" . "</br>";
                    /* Import Organization Structure    */
                    self::OrganizationStructure($pluginInfo);

                    break;
                case TEST_JR:
                    echo "JobRoles" . "</br>";
                    /* Import Job Roles */
                    self::ImportKSJobRoles($pluginInfo);

                    break;
                case TEST_FS_USERS:
                    echo "Import FS Users" . "</br>";
                    /* Import FS Users              */
                    self::ImportFSUsers($pluginInfo);

                    break;
                case TEST_FS_ORG:
                    echo "Import FS ORG" . "</br>";
                    /* Import FS Companies          */
                    self::ImportFSOrgStructure($pluginInfo);

                    break;
                case TEST_FS_JR:
                    echo "Import FS job Roles" . "</br>";
                    /* Import FS Job roles  */
                    self::ImportFSJobRoles($pluginInfo);

                    break;
                case TEST_FS_USER_COMP:
                    echo "Import FS User Competence" . "</br>";
                    /* Import FS User Competence    */
                    self::ImportFSUserCompetence($pluginInfo);

                    break;
                case TEST_FS_USER_COMP_JR:
                    echo "Import Fs User Competence JR" . "</br>";
                    /* Import FS User Competence JR */
                    self::ImportFSUserCompetenceJR($pluginInfo);

                    break;
                case TEST_FS_SYNC_ORG:
                    echo "Synchronization FS Companies" . "</br>";

                    self::CompaniesFS_Synchronization($pluginInfo,false);

                    break;
                case TEST_FS_SYNC_JR:
                    /* Synchronization Job Roles    */
                    //self::JobRolesFS_Synchronization($pluginInfo,false);

                    break;
                case TEST_FS_SYNC_COMP_U:
                    /* Synchronization User Competence Company  */
                    self::UserCompetence_Synchronization($pluginInfo,IMP_COMPETENCE_COMP,KS_USER_COMPETENCE_CO);

                    break;
                case TEST_FS_SYNC_COMP_JR:
                    /* Synchronization User Competence JobRole  */
                    self::UserCompetence_Synchronization($pluginInfo,IMP_COMPETENCE_JR,KS_USER_COMPETENCE_JR);

                    break;
                case TEST_FS_SYNC_FS_USERS:
                    /* Synchronization Users Accounts   */
                    self::UsersFS_Synchronization($pluginInfo);

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
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    02/02/0216
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import data from KS site
     */
    private static function ImportKS($pluginInfo) {
        /* Variables    */
        global $CFG;
        $dbLog          = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import KS . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Import Organization Structure    */
            self::OrganizationStructure($pluginInfo);

            /* Import Job Roles */
            self::ImportKSJobRoles($pluginInfo);

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import KS . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog  = $ex->getMessage() . "\n\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH ERROR Import KS . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//ImportKS

    /**
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import the organization structure from KS, for a specific level
     */
    private static function OrganizationStructure($pluginInfo) {
        /* Variables */
        global $CFG;
        $infoLevel      = null;
        $response       = null;
        $dbLog          = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Fellesdata CRON Ks Organization Structure . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Request Web Service */
            $infoLevel = array();
            $infoLevel['company']   = $pluginInfo->ks_muni;
            $infoLevel['level']     = 1;
            /* Don't import all companies over and over */
            $infoLevel['notIn']     = KS::ExistingCompanies();

            /* Call Web Service     */
            $response = self::ProcessKSService($pluginInfo,KS_ORG_STRUCTURE,$infoLevel);

            if ($response['error'] == '200') {
                /* Import Organization Structure    */
                KS::ImportKSOrganization($response['structure']);
            }else {
                /* Log Error    */
            }//if_no_error

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Fellesdata CRON Ks Organization Structure . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog = "ERROR: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Fellesdata CRON Ks Organization Structure . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//OrganizationStructure

    /**
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    01/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import all job roles from KS site
     */
    private static function ImportKSJobRoles($pluginInfo) {
        /* Variables    */
        global $CFG;
        $response   = null;
        $infoLevel  = null;
        $notIn      = null;
        $hierarchy  = null;
        $jobRoles   = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Fellesdata CRON KS Job Roles . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Job Roles Generics       */
            $notIn = KS::ExistingJobRoles(true);

            /* Call Web Service             */
            $response = self::ProcessKSService($pluginInfo,KS_JOBROLES_GENERICS,$notIn);
            /* Import Job Roles Generics    */
            if ($response['error'] == '200') {
                KS::KSJobRoles($response['jobroles'],true);
            }else {
                /* Log Error    */
            }//if_no_error

            /* Job Roles No Generics    */
            $hierarchy = KS::GetHierarchy_JR($pluginInfo->ks_muni);
            $notIn = KS::ExistingJobRoles(false,$hierarchy);

            $infoLevel = array('notIn'  => $notIn,
                               'top'    => $hierarchy);
            /* Call Web Service             */
            $response = self::ProcessKSService($pluginInfo,KS_JOBROLES,$infoLevel);
            /* Import Job Roles Generics    */
            if ($response['error'] == '200') {
                KS::KSJobRoles($response['jobroles']);
            }//if_no_error

            /* Log  */
            $dbLog = $response['message'] . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Fellesdata CRON KS Job Roles . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Fellesdata CRON KS Job Roles . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//ImportKSJobRoles

    /**
     * @param           $pluginInfo
     * @param           $service
     * @param           $params
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * KS Web Services to import data from KS site and synchronize data between fellesdata and KS
     */
    private static function ProcessKSService($pluginInfo,$service,$params) {
        /* Variables    */
        $domain         = null;
        $token          = null;
        $server         = null;

        try {
            /* Data to call Service */
            $domain     = $pluginInfo->ks_point;
            $token      = $pluginInfo->kss_token;

            /* Build end Point Service  */
            $server     = $domain . '/webservice/soap/server.php?wsdl=1&wstoken=' . $token;

            /* Call service */
            $client     = new SoapClient($server);
            $response   = $client->$service($params);

            return $response;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ProcessKSService

    /**************/
    /* FELLESDATA */
    /**************/

    /**
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import data from fellesdata
     */
    private static function ImportFellesdata($pluginInfo) {
        /* Variables    */
        global $CFG;
        $dbLog = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Import Fellesdata . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Import FS Users              */
            self::ImportFSUsers($pluginInfo);

            /* Import FS Companies          */
            self::ImportFSOrgStructure($pluginInfo);

            /* Import FS Job roles  */
            self::ImportFSJobRoles($pluginInfo);

            /* Import FS User Competence    */
            self::ImportFSUserCompetence($pluginInfo);

            /* Import FS User Competence JR */
            self::ImportFSUserCompetenceJR($pluginInfo);

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import Fellesdata . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog  = "Error: " . $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Import Fellesdata . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//ImportFellesdata

    /**
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import all users from Fellesdata
     */
    private static function ImportFSUsers($pluginInfo) {
        /* Variables    */
        global $CFG;
        $pathFile   = null;
        $content    = null;
        $fsUsers    = null;

        try {
            /* Call Web service */
            $fsResponse = self::ProcessTradisService($pluginInfo,TRADIS_FS_USERS);

            /* Import/Save data in Temporary tables */
            if ($fsResponse) {
                /* Open File */
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS . '.txt';
                if (file_exists($pathFile)) {
                    /* Get Content */
                    $content = file($pathFile);

                    FS::SaveTemporary_Fellesdata($content,IMP_USERS);
                }//if_exists
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ImportFSUsers

    /**
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import all companies from fellesdata
     */
    private static function ImportFSOrgStructure($pluginInfo) {
        /* Variables    */
        global $CFG;
        $pathFile   = null;
        $content    = null;
        $fsResponse = null;

        try {
            echo "Start ImpFSOrgStructure" . "</br>";
            /* Call Web service */
            $fsResponse = self::ProcessTradisService($pluginInfo,TRADIS_FS_COMPANIES);

            /* Import/Save data in Temporary tables */
            if ($fsResponse) {
                /* Open File */
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_COMPANIES . '.txt';
                if (file_exists($pathFile)) {
                    /* Get Content */
                    $content = file($pathFile);

                    FS::SaveTemporary_Fellesdata($content,IMP_COMPANIES);
                }//if_exists
            }
            echo "Finish ImpFSOrgStructure" . "</br>";
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ImportFSOrgStructure

    /**
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    04/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import FS Job roles from fellesdata
     */
    private static function ImportFSJobRoles($pluginInfo) {
        /* Variables    */
        global $CFG;
        $pathFile   = null;
        $content    = null;
        $fsResponse = null;

        try {
            echo "Start ImportFSJobRoles" . "</br>";
            /* Call Web Service */
            $fsResponse = self::ProcessTradisService($pluginInfo,TRADIS_FS_JOBROLES);

            /* Import/Save data in temporary tables */
            if ($fsResponse) {
                /* Open File */
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_JOBROLES . '.txt';
                if (file_exists($pathFile)) {
                    /* Get Content */
                    $content = file($pathFile);

                    FS::SaveTemporary_Fellesdata($content,IMP_JOBROLES);
                }//if_exists
            }
            echo "Finish ImportFSJobroles" . "</br>";
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ImportFSJobRoles

    /**
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import all User - Competence Company from fellesdata
     */
    private static function ImportFSUserCompetence($pluginInfo) {
        /* Variables    */
        global $CFG;
        $pathFile           = null;
        $content            = null;
        $usersCompetence    = null;

        try {
            echo "Start ImportFSUserCompetence" . "</br>";
            /* Call Web Service */
            $usersCompetence = self::ProcessTradisService($pluginInfo,TRADIS_FS_USERS_COMPANIES);

            /* Import/Save in temporary tables  */
            if ($usersCompetence) {
                /* Open File */
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS_COMPANIES . '.txt';
                if (file_exists($pathFile)) {
                    /* Get Content */
                    $content = file($pathFile);

                    FS::SaveTemporary_Fellesdata($content,IMP_COMPETENCE_COMP);
                }//if_exists
            }
            echo "Finist ImportFSUserCompetence" . "</br>";
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ImportFSUserCompetence


    /**
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Import all User - Competence JR from fellesdata
     */
    private static function ImportFSUserCompetenceJR($pluginInfo) {
        /* Variables    */
        global $CFG;
        $pathFile           = null;
        $content            = null;
        $usersCompetenceJR  = null;

        try {
            echo "Start ImportFSUserCompetenceJR" . "</br>";
            /* Call Web Service */
            $usersCompetenceJR = self::ProcessTradisService($pluginInfo,TRADIS_FS_USERS_JOBROLES);

            /* Import/Save in temporary tables */
            if ($usersCompetenceJR) {
                /* Open File */
                $pathFile = $CFG->dataroot . '/fellesdata/' . TRADIS_FS_USERS_JOBROLES . '.txt';
                if (file_exists($pathFile)) {
                    /* Get Content */
                    $content = file($pathFile);

                    FS::SaveTemporary_Fellesdata($content,IMP_COMPETENCE_JR);
                }//if_exists
            }//if_data
            echo "Finish ImportFSUserCompetenceJR" . "</br>";
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ImportFSUserCompetenceJR


    /**
     * @param           $pluginInfo
     * @param           $service
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    02/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Call Fellesdata Web service to import all data connected with companies, users...
     */
    private static function ProcessTradisService($pluginInfo,$service) {
        /* Variables    */
        global $CFG;
        $dir            = null;
        $responseFile   = null;
        $pathFile       = null;
        $urlTradis      = null;
        $fromDate       = null;
        $toDate         = null;

        try {
            /* Get Parameters service    */
            $toDate     = mktime(1, 60, 0, date("m"), date("d"), date("Y"));
            $toDate     = gmdate('Y-m-d\TH:i:s\Z',$toDate);
            if (isset($pluginInfo->lastexecution) && $pluginInfo->lastexecution) {
                /* No First Execution   */
                $fromDate   = mktime(1, 60, 0, date("m"), date("d"), date("Y"));
                $fromDate   = gmdate('Y-m-d\TH:i:s\Z',$fromDate);
            }else {
                /* First Execution      */
                $fromDate = gmdate('Y-m-d\TH:i:s\Z',0);
            }

            echo "From: " . $fromDate . "</br></br>";
            echo "To:   " . $toDate . "</br></br>";

            /* Build url end point  */
            $urlTradis = $pluginInfo->fs_point . '/tardis/fellesdata/' . $service . '?fromDate=' . $fromDate . '&toDate=' . $toDate;

            /* Call Web Service     */
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

            /* Format Data  */
            if ($response === false) {
                return null;
            }else {
                /* Check if exists temporary directory */
                $dir = $CFG->dataroot . '/fellesdata';
                if (!file_exists($dir)) {
                    mkdir($dir);
                }

                /* Clean all response   */
                $pathFile = $dir . '/' . $service . '.txt';
                if (file_exists($pathFile)) {
                    unlink($pathFile);
                }

                /* Create a new response file */
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
    }//ProcessTradisService

    /**
     * @param           $pluginInfo
     *
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronization of users accounts between KS and FS
     */
    private static function UsersFS_Synchronization($pluginInfo) {
        /* Variables    */
        global $DB,$CFG,$SESSION;
        $rdo        = null;
        $usersFS    = array();
        $infoUser   = null;
        $response   = null;
        $dbLog      = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Synchronization Users Accoutns . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Get user to synchronize  */
            if ($SESSION->manual) {
                $rdo = $DB->get_records('fs_imp_users',array('imported' => '0'),'','*',0,2000);
            }else {
                $rdo = $DB->get_records('fs_imp_users',array('imported' => '0'),'','*');
            }//if_manual


            /* Prepare data */
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Users account info   */
                    $infoUser = new stdClass();
                    $infoUser->personalnumber   = $instance->fodselsnr;
                    $infoUser->firstname        = $instance->fornavn;
                    $infoUser->lastname         = $instance->mellomnavn . ' ' . $instance->etternavn;
                    $infoUser->email            = $instance->epost;
                    $infoUser->action           = $instance->action;

                    /* Add User */
                    $usersFS[$instance->id] = $infoUser;
                }//for_rdo

                /* Call Web Service */
                $response = self::ProcessKSService($pluginInfo,KS_SYNC_USER_ACCOUNT,$usersFS);
                if ($response['error'] == '200') {
                    /* Synchronize Users Accounts FS    */
                    FSKS_USERS::Synchronize_UsersFS($usersFS,$response['usersAccounts']);

                    /* Clean Table*/
                    //$DB->delete_records('fs_imp_users',array('imported' => '1'));
                }//if_no_error
            }//if_Rdo

            /* Log  */
            $dbLog = $response['message'] . "\n" ."\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization Users Accoutns . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog = $ex->getMessage() . "\n" ."\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' FINISH Synchronization Users Accoutns . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//UsersFS_Synchronization

    /**
     * @param           $pluginInfo
     * @param           $fstExecution
     *
     * @throws          Exception
     *
     * @creationDate    03/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronization of companies between FS and KS
     */
    private static function CompaniesFS_Synchronization($pluginInfo,$fstExecution) {
        /* Variables    */
        global $DB,$CFG;
        $toSynchronize  = null;
        $synchronizeFS  = null;
        $toMail         = null;
        $notifyTo       = null;
        $response       = null;
        $dbLog          = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Companies FS/KS Synchronization . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Get Notifications    */
            if ($pluginInfo->mail_notification) {
                $notifyTo   = explode(',',$pluginInfo->mail_notification);
            }//if_mail_notifications

            /* First Execution */
            if ($fstExecution) {
                /* Send eMail --> Manual synchronization    */
                if ($notifyTo) {
                    self::SendNotifications(SYNC_COMP,null,$notifyTo,$pluginInfo->fs_source);
                }//if_notify
            }else {
                /*  Get Info to Synchronize and mail */
                list($toSynchronize,$synchronizeFS,$toMail) = FSKS_COMPANY::CompaniesFSToSynchronize();

                /* Send Mail --> Manual Synchronization     */
                if ($notifyTo) {
                    if ($toMail) {
                        self::SendNotifications(SYNC_COMP,$toMail,$notifyTo,$pluginInfo->fs_source);
                    }//if_toMail
                }//if_notify

                /* Synchronize Companies FSKS */
                /* Call web service    */
                if ($toSynchronize) {
                    echo "To Synchronize";
                    $response = self::ProcessKSService($pluginInfo,KS_SYNC_FS_COMPANY,$toSynchronize);
                    if ($response['error'] == '200') {

                        FSKS_COMPANY::Synchronize_CompaniesKSFS($toSynchronize,$response['companies']);
                    }else {
                        /* Log Error    */
                        echo "1 - Error" . "</br>";
                    }//if_no_error
                }//if_toSynchronize

                /* Synchronize Companies Only FS */
                if ($synchronizeFS) {
                    FSKS_COMPANY::Synchronize_CompaniesFS($synchronizeFS);
                }//if_synchronize

                /* Clean Table*/
                //$DB->delete_records('fs_imp_company',array('imported' => '1'));
            }//if_else

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' Finish Companies FS/KS Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog  = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish Companies FS/KS Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//CompaniesFS_Synchronization

    /**
     * @param           $pluginInfo
     * @param           $fstExecution
     *
     * @throws          Exception
     *
     * @creationDate    10/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronization of job roles
     */
    private static function JobRolesFS_Synchronization($pluginInfo,$fstExecution) {
        /* Variables    */
        global $DB,$CFG;
        $toSynchronize  = null;
        $toMail         = null;
        $notifyTo       = null;
        $response       = null;
        $dbLog          = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START Job Roles Synchronization . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Get Notifications    */
            if ($pluginInfo->mail_notification) {
                $notifyTo   = explode(',',$pluginInfo->mail_notification);
            }//if_mail_notifications

            /* First Execution */
            if ($fstExecution) {
                /* Send eMail --> Manual synchronization    */
                if ($notifyTo) {
                    self::SendNotifications(SYNC_JR,null,$notifyTo,$pluginInfo->fs_source);
                }//if_notify
            }else {
                /*  Get Info to Synchronize and mail */
                list($toSynchronize,$toMail) = FSKS_JOBROLES::JobRolesFSToSynchronize();

                /* Send Mail --> Manual Synchronization     */
                if ($notifyTo) {
                    if ($toMail) {
                        self::SendNotifications(SYNC_JR,$toMail,$notifyTo,$pluginInfo->fs_source);
                    }//if_toMail
                }//if_notify

                /* Synchronize Job Roles Only FS */
                if ($toSynchronize) {
                    FSKS_JOBROLES::Synchronize_JobRoles($toSynchronize);
                }//if_synchronize

                /* Clean Table*/
                //$DB->delete_records('fs_imp_jobroles',array('imported' => '1'));
            }//if_else

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' Finish Job Roles Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog  = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish Job Roles Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//JobRolesFS_Synchronization

    /**
     * @param           $pluginInfo
     * @param           $competenceType
     * @param           $service
     *
     * @throws          Exception
     *
     * @creationDate    11/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronization User Competence Companies
     */
    private static function UserCompetence_Synchronization($pluginInfo,$competenceType,$service) {
        /* Variables    */
        global $DB,$CFG;
        $toSynchronize  = null;
        $response       = null;
        $tblCompetence  = null;
        $dbLog          = null;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' Start  User Competence '  . $competenceType . ' Synchronization . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

        try {
            /* Get Info to Synchronize  */
            $toSynchronize = FSKS_USERS::UserCompetenceToSynchronize($competenceType);

            /* Call Web Service  */
            if ($toSynchronize) {
                $response = self::ProcessKSService($pluginInfo,$service,$toSynchronize);
                if ($response['error'] == '200') {
                    /* Synchronize Users Competence    */
                    FSKS_USERS::Synchronize_UsersCompetenceFS($toSynchronize,$response['usersCompetence'],$competenceType);

                    /* Clean Table*/
                    switch ($competenceType) {
                        case IMP_COMPETENCE_COMP:
                            $tblCompetence = 'fs_imp_users_company';

                            break;
                        case IMP_COMPETENCE_JR;
                            $tblCompetence = 'fs_imp_users_jr';

                            break;
                    }
                    //$DB->delete_records($tblCompetence,array('imported' => '1'));
                }//if_no_error
            }//if_toSynchronize

            /* Log  */
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' Finish  User Competence ' . $competenceType . ' Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");
        }catch (Exception $ex) {
            /* Log  */
            $dbLog  = $ex->getMessage() . "\n" . "\n";
            $dbLog .= userdate(time(),'%d.%m.%Y', 99, false). ' Finish ERROR User Competence ' . $competenceType . ' Synchronization . ' . "\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Fellesdata.log");

            throw $ex;
        }//try_catch
    }//UserCompetence_Synchronization

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
    private static function SendNotifications($type,$toMail,$notifyTo,$source) {
        /* Variables    */
        global $USER,$SITE;
        $urlMapping = null;
        $subject    = null;
        $body       = null;
        $info       = null;
        $to         = null;

        try {
            /* Subject  */
            $subject = (string)new lang_string('subject','local_fellesdata',$SITE->shortname,$USER->lang);

            /* Get Body Message to sent  */
            $info = new stdClass();
            switch ($type) {
                case SYNC_COMP:
                    /* Url Mapping */
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

                    $urlMapping->param('m','jr');
                    $info->mapping  = $urlMapping;

                    $body = (string)new lang_string('body_jr_to_sync','local_fellesdata',$info,$USER->lang);

                    break;
            }//type

            /* Send */
            foreach ($notifyTo as $to) {
                $USER->email    = $to;
                email_to_user($USER, $SITE->shortname, $subject, $body,$body);
            }//for_Each
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SendNotifications
}//Fellesdata_cron

