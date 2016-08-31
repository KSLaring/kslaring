<?php
/**
 * ADFS Integration WebService - Library
 *
 * @package         local
 * @subpackage      adfs
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    31/10/2015
 * @author          eFaktor     (fbv)
 *
 */

define('ERR_LOG_IN','/local/wsks/adfs/error.php');

class KS_ADFS {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $user
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    12/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if it's a valid user
     */
    public static function IsValidUser($user) {
        /* Variables */
        global $CFG;
        $valid = true;

        try {
            $dbLog = $user->id . ' -- ' . $user->idnumber . "\n";

            if (empty($user->firsname) ||
                empty($user->lastname) ||
                empty($user->email)    ) {
                $valid =false;
            }else if (empty($user->idnumber)) {
                $dbLog .= "Empty???" . "\n";
                $valid = false;
            }

            error_log($dbLog, 3, $CFG->dataroot . "/SSO_LNK.log");
            return $valid;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsValidUser

    /**
     * @return          moodle_url
     * @throws          Exception
     *
     * @creationDate    12/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the url where redirect the user
     */
    public static function GetErrorURL() {
        /* Variables */
        $pluginInfo = null;
        $redirect   = null;

        try {
            /* Plugin Info      */
            $pluginInfo     = get_config('local_adfs');

            /* URL */
            $redirect = $pluginInfo->ks_point . '/local/wsks/adfs/error.php';
            $redirect = new moodle_url($redirect,array('er' => 1));

            return $redirect;
        }catch (Exception $ex) {
            throw $ex;
        }
    }//GetErrorURL

    /**
     * @param           $userId
     *
     * @return          mixed
     *
     * @throws          Exception
     *
     * @creationDate    02/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Log in url for the user
     *
     * @updateDate      15/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add course/activity link information
     */
    public static function LogIn_UserADFS($userId,$modLnk = null,$modId = null) {
        /* Variables    */
        $urlRedirect = null;

        /* Plugin Info      */
        $pluginInfo     = get_config('local_adfs');

        try {
            $urlRedirect = self::ProcessUserADFSService($userId,$pluginInfo,$modLnk,$modId);

            return $urlRedirect;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//LogIn_UserADFS


    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $userId
     * @param           $pluginInfo
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    31/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Process the ADFS USER.
     * - Create/Update the user in KS Læring
     * - Get Log in url
     *
     * @updateDate      15/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add course/activity link information
     */
    private static function ProcessUserADFSService($userId,$pluginInfo,$modLnk = '',$modId = '') {
        /* Variables    */
        $userRequest    = null;
        $urlRedirect    = null;
        $domain         = null;
        $token          = null;
        $service        = null;
        $server         = null;
        $client         = null;
        $response       = null;
        $userInfo       = null;
        $errCode        = null;


        try {
            /* User to Validate */
            $userRequest = self::GetUserADFS($userId);

            /* Course/Activity Link */
            $userRequest['modlnk']  = $modLnk;
            $userRequest['modid']   = $modId;

            /* Data to call Service */
            $domain     = $pluginInfo->ks_point;
            $token      = $pluginInfo->adfs_token;

            $service    = $pluginInfo->adfs_service;

            /* Build end Point Service  */
            $server     = $domain . '/webservice/soap/server.php?wsdl=1&wstoken=' . $token;

            /* Call service */
            $client     = new SoapClient($server);
            $response   = $client->$service($userRequest);

            if (is_array($response)) {
                if ($response['error'] == '200') {
                    $urlRedirect =   $response['url'];
                }else {
                    $urlRedirect = $response['url'];
                }//if_no_error
            }else {
                /* DEV Site */
                $aux = (array)$response;
                $urlRedirect =   $aux['url'];
            }


            return $urlRedirect;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ProcessUserADFSService

    /**
     * @param           $userId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    31/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get user data to send
     */
    private static function GetUserADFS($userId) {
        /* Variables    */
        global $DB;
        $rdo        = null;
        $params     = null;
        $userADFS   = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['id'] = $userId;

            /* Execute  */
            $rdo = $DB->get_record('user',$params,'idnumber,firstname,lastname,email,city,country,lang');
            if ($rdo) {
                /* User ADFS    */
                $userADFS['username']   = $rdo->idnumber;
                $userADFS['firstname']  = $rdo->firstname;
                $userADFS['lastname']   = $rdo->lastname;
                $userADFS['email']      = $rdo->email;
                $userADFS['city']       = $rdo->city;
                $userADFS['country']    = $rdo->country;
                $userADFS['lang']       = $rdo->lang;
            }//if_rdo

            return $userADFS;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUserADFS

}//KS_ADFS