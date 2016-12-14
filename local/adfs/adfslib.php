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
     * Description
     * Check if it's a valid user
     *
     * @param           $user
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    12/06/2016
     * @author          eFaktor     (fbv)
     */
    public static function is_valid_user($user) {
        /* Variables */
        $valid = true;

        try {
            if (empty($user->firstname) ||
                empty($user->lastname) ||
                empty($user->email)    ) {
                $valid = false;
            }else if (empty($user->idnumber)) {
                $valid = false;
            }else if (strlen($user->idnumber) != 11) {
                $valid = false;
            }

            return $valid;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsValidUser

    /**
     * Description
     * Get the url where redirect the user
     *
     * @return          moodle_url
     * @throws          Exception
     *
     * @creationDate    12/06/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_error_url() {
        /* Variables */
        $pluginInfo = null;
        $redirect   = null;

        try {
            // Plugin info
            $pluginInfo     = get_config('local_adfs');

            // url
            if ($pluginInfo->idporten) {
                $redirect = $pluginInfo->idporten;
            }else {
                $redirect = $pluginInfo->ks_point . '/local/wsks/adfs/error.php';
                $redirect = new moodle_url($redirect,array('er' => 1));
            }

            return $redirect;
        }catch (Exception $ex) {
            throw $ex;
        }
    }//get_error_url

    /**
     * Description
     * Get Log in url for the user
     * Add course/activity link information
     *
     * @param           $userId
     *
     * @return          mixed
     *
     * @throws          Exception
     *
     * @creationDate    02/11/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      15/08/2016
     * @author          eFaktor     (fbv)
     */
    public static function login_user_adfs($userId,$modLnk = null,$modId = null) {
        /* Variables    */
        $urlRedirect = null;

        // Plugin info
        $pluginInfo     = get_config('local_adfs');

        try {
            // Log in url
            $urlRedirect = self::process_user_adfs_service($userId,$pluginInfo,$modLnk,$modId);

            return $urlRedirect;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//login_user_adfs


    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Process the ADFS USER.
     * - Create/Update the user in KS Læring
     * - Get Log in url
     * Add course/activity link information
     *
     * @param           $userId
     * @param           $pluginInfo
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    31/10/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      15/08/2016
     * @author          eFaktor     (fbv)
     */
    private static function process_user_adfs_service($userId,$pluginInfo,$modLnk = '',$modId = '') {
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
            // Validate user
            $userRequest = self::get_user_adfs($userId);

            if ($userRequest) {
                // Course/activity link
                $userRequest->modlnk  = $modLnk;
                $userRequest->modid   = $modId;

                // Prepare data for web service
                $domain     = $pluginInfo->ks_point;
                $token      = $pluginInfo->adfs_token;

                $service    = $pluginInfo->adfs_service;

                // Build end Point Service
                $params = array('user' => $userRequest);
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
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields );

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

                if ($result['error'] == '200') {
                    $urlRedirect =   $result['url'];
                }else {
                    $urlRedirect = $result['url'];
                }//if_no_error
            }

            return $urlRedirect;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//process_user_adfs_service

    /**
     * Description
     * Get user data to send
     * 
     * @param           $userId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    31/10/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_user_adfs($userId) {
        /* Variables    */
        global $DB;
        $rdo        = null;
        $params     = null;
        $userADFS   = null;

        try {


            // Search criteria
            $params = array();
            $params['id'] = $userId;

            // Execute
            $rdo = $DB->get_record('user',$params,'idnumber,firstname,lastname,email,city,country,lang');
            if ($rdo) {
                // User adfs
                $userADFS = new stdClass();
                $userADFS->username   = $rdo->idnumber;
                $userADFS->firstname  = $rdo->firstname;
                $userADFS->lastname   = $rdo->lastname;
                $userADFS->email      = $rdo->email;
                $userADFS->city       = $rdo->city;
                $userADFS->country    = $rdo->country;
                $userADFS->lang       = $rdo->lang;
            }//if_rdo

            return $userADFS;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_user_adfs
}//KS_ADFS