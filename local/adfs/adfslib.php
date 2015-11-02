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

define('ERR_LOG_IN','/local/wsks/error.php');

class KS_ADFS {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $userId
     *
     * @return          string
     *
     * @creationDate    02/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Log in url for the user
     */
    public static function LogIn_UserADFS($userId) {
        /* Variables    */
        $urlRedirect = null;

        /* Plugin Info      */
        $pluginInfo     = get_config('local_adfs');

        try {
            $urlRedirect = self::ProcessUserADFSService($userId,$pluginInfo);

            return $urlRedirect;
        }catch (Exception $ex) {
            $urlRedirect = urlencode($pluginInfo->ks_point . ERR_LOG_IN);
            return $urlRedirect;
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
     */
    private static function ProcessUserADFSService($userId,$pluginInfo) {
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

            /* Data to call Service */
            $domain     = $pluginInfo->adfs_point;
            $token      = $pluginInfo->adfs_token;
            $service    = $pluginInfo->adfs_service;

            /* Build end Point Service  */
            $server     = $domain . '/webservice/soap/server.php?wsdl=1&wstoken=' . $token;

            /* Call service */
            $client     = new SoapClient($server);
            $response   = $client->$service($userRequest);

            if ($response['error'] == '200') {
                $urlRedirect =   $response['url'];
            }else {
                $urlRedirect = $response['url'];
            }//if_no_error

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