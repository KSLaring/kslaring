<?php
/**
 * KS Læring Integration - Login via Feide - Library
 *
 * @package         local
 * @subpackage      wsks/feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/09/2015
 * @author          eFaktor     (fbv)
 */
define('FEIDE_NOT_VALID',0);
define('FEIDE_ERR_PROCESS',1);
define('FEIDE_NON_ERROR',2);

class KS_FEIDE {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $args
     * @return          array
     * @throws          Exception
     *
     * @creationDate    22/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate the user that wants log in
     */
    public static function ValidateUser($args) {
        /* Variables    */
        $infoUser   = null;
        $errCode    = null;

        try {
            /* Validate user to log in  */
            list($infoUser,$errCode) = self::ValidateUserFeideService($args);

            return array($infoUser,$errCode);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ValidateUser

    /**
     * @param           $userFeide
     * @return          int|mixed
     *
     * @creationDate    22/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Log in the user
     */
    public static function LoginUser($userFeide) {
        /* Variables    */
        $userId = null;

        try {
            /* Check if the user exists */
            $userId = self::ExistUser($userFeide['username']);

            /* User already exists  --> Update && Log In    */
            if ($userId) {
                self::UpdateUser($userFeide,$userId);

            }else {
                /* User Not Exist       --> Create && Log In    */
                $userId = self::CreateUser($userFeide);
            }//if_userId

            $user = get_complete_user_data('id', $userId);
            complete_user_login($user);

            return $user;
        }catch (Exception $ex) {
            return FEIDE_ERR_PROCESS;
        }//try_catch
    }//LoginUser

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param       $args
     * @return      null
     * @throws      Exception
     *
     * @creationDate    22/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate user to log in via feide and get his/her information
     */
    private static function ValidateUserFeideService($args) {
        /* Variables    */
        $userRequest    = null;
        $pluginInfo     = null;
        $domain         = null;
        $token          = null;
        $service        = null;
        $server         = null;
        $client         = null;
        $response       = null;
        $userInfo       = null;
        $errCode        = null;

        try {
            /* Plugin Info      */
            $pluginInfo = get_config('local_wsks');

            /* User to Validate */
            $userRequest = array();
            $userRequest['id']     = $args[0];
            $userRequest['ticket'] = $args[1];

            /* Data to call Service */
            $domain     = $pluginInfo->feide_point;
            $token      = $pluginInfo->feide_token;
            $service    = $pluginInfo->feide_service;

            /* Build end Point Service  */
            $server     = $domain . '/webservice/soap/server.php?wsdl=1&wstoken=' . $token;

            /* Call service */
            $client     = new SoapClient($server);
            $response   = $client->$service($userRequest);

            if ($response['error'] == '200') {
                if ($response['valid']) {
                    $errCode = FEIDE_NON_ERROR;
                }else {
                    $errCode = FEIDE_NOT_VALID;
                }//if_valid

                $userInfo = $response['user'][0];
            }else {
                $errCode = FEIDE_ERR_PROCESS;
            }//if_no_error

            return array($userInfo,$errCode);
        }catch (Exception $ex) {
            print_r($ex);
            throw $ex;
        }//try_catch
    }//ValidateUserFeideService

    /**
     * @param           $user
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    22/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user already exists
     */
    private static function ExistUser($user) {
        /* Variables    */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['username'] = $user;

            /* Execute  */
            $rdo = $DB->get_record('user',$params,'id');
            if ($rdo) {
                return $rdo->id;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ExistExist

    /**
     * @param           $user
     * @param           $userId
     * @throws          Exception
     *
     * @creationDate    22/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update user information
     */
    private static function UpdateUser($user,$userId) {
        /* Variables    */
        global $DB;
        $instance = null;

        try {
            /* Info to update  */
            $instance = new stdClass();
            $instance->id           = $userId;
            $instance->username     = $user['username'];
            $instance->firstname    = $user['firstname'];
            $instance->lastname     = $user['lastname'];
            $instance->email        = $user['email'];
            $instance->city         = $user['city'];
            $instance->country      = $user['country'];
            $instance->lang         = $user['lang'];
            $instance->timemodified = time();

            /* Execute  */
            $DB->update_record('user',$instance);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UpdateUser

    /**
     * @param           $user
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    22/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create a new user
     */
    private static function CreateUser($user) {
        /* Variables    */
        global $DB,$CFG;
        $newUser    = null;

        try {
            /* New User */
            $newUser = new stdClass();

            /* Username     */
            $newUser->username     = $user['username'];
            /* Auth method  */
            $newUser->auth         = 'saml';
            /* Password     */
            $newUser->password     = AUTH_PASSWORD_NOT_CACHED;

            /* First name   */
            $newUser->firstname    = $user['firstname'];
            /* Last name    */
            $newUser->lastname     = $user['lastname'];
            /* eMail        */
            $newUser->email        = $user['email'];

            /* City         */
            $newUser->city         = $user['city'];
            /* Country      */
            $newUser->country      = $user['country'];

            /* Lang */
            $newUser->lang = 'no';
            if ($user['lang']) {
                $newUser->lang     = $user['lang'];
            }//lang


            $newUser->confirmed    = '1';
            $newUser->firstaccess  = time();
            $newUser->calendartype = $CFG->calendartype;
            $newUser->timecreated = time();
            $newUser->timemodified = $newUser->timecreated;
            $newUser->mnethostid   = $CFG->mnet_localhost_id;

            /* Execute  */
            $newUser->id = $DB->insert_record('user',$newUser);

            return $newUser->id;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateUser
}//KS_FEIDE