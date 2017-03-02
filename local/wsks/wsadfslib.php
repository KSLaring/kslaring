<?php
/**
 * Kommit ADFS Integration WebService - Library
 *
 * @package         local
 * @subpackage      wsks
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    30/10/2015
 * @author          eFaktor     (fbv)
 *
 */

class WS_ADFS {
    /***********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $userADFS
     * @param           $result
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    30/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Process the user from ADFS. Create or update.
     */
    public static function process_user_adfs($userADFS,&$result) {
        /* Variables    */
        global $CFG;
        $userId     = null;
        $response   = null;

        try {
            if (!is_array($userADFS)) {
                $userADFS = (Array)$userADFS;
            }
            
            /* Check if user exists */
            $userId = self::ExistsUser($userADFS['username']);
            if ($userId) {
                /* Update   */
                self::UpdateUser($userADFS,$userId);
            }else {
                /* Create New One   */
                $userId = self::CreateUser($userADFS);
            }//if_exist

            if (($userId) && $result['valid']) {
                /**
                 * Add the gender
                 */
                if (is_numeric($userADFS['username']) && (strlen($userADFS['username']) == 11)) {
                    Gender::Add_UserGender($userId,$userADFS['username']);
                }
                $response = self::GenerateResponse($userId,$userADFS['modlnk'],$userADFS['modid']);
            }

            return $response;
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['valid']        = 0;
            $result['url']          = urlencode($CFG->wwwroot . '/local/wsks/adfs/error.php');
            $result['msg_error']    = $ex->getMessage();

            throw $ex;
        }//try_catch
    }//process_user_adfs

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $username
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    30710/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user already exists.
     */
    private static function ExistsUser($username) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $params = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['username']     = $username;
            $params['deleted']      = 0;

            /* Execute  */
            $rdo = $DB->get_record('user',$params);
            if ($rdo) {
                return $rdo->id;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ExistsUser

    /**
     * @param           $userADFS
     * @param           $userId
     *
     * @throws          Exception
     *
     * @creationDate    30/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the user data
     */
    private static function UpdateUser($userADFS,$userId) {
        /* Variables    */
        global $DB;
        $infoUser = null;

        try {
            /* Info to update  */
            $infoUser = new stdClass();
            $infoUser->id           = $userId;
            $infoUser->username     = $userADFS['username'];
            $infoUser->firstname    = $userADFS['firstname'];
            $infoUser->lastname     = $userADFS['lastname'];
            $infoUser->email        = $userADFS['email'];
            $infoUser->city         = $userADFS['city'];
            $infoUser->country      = $userADFS['country'];
            $infoUser->lang         = $userADFS['lang'];
            $infoUser->timemodified = time();

            /* Execute  */
            $DB->update_record('user',$infoUser);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UpdateUser

    /**
     * @param           $userADFS
     *
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    30/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create a new user.
     */
    private static function CreateUser($userADFS) {
        /* Variables    */
        global $DB,$CFG;
        $newUser    = null;

        try {
            /* New User */
            $newUser = new stdClass();

            /* Username     */
            $newUser->username     = $userADFS['username'];
            /* Auth method  */
            $newUser->auth         = 'saml';
            /* Password     */
            $newUser->password     = AUTH_PASSWORD_NOT_CACHED;

            /* First name   */
            $newUser->firstname    = $userADFS['firstname'];
            /* Last name    */
            $newUser->lastname     = $userADFS['lastname'];
            /* eMail        */
            $newUser->email        = $userADFS['email'];

            /* City         */
            $newUser->city         = $userADFS['city'];
            /* Country      */
            $newUser->country      = $userADFS['country'];

            /* Lang */
            $newUser->lang = 'no';
            if ($userADFS['lang']) {
                $newUser->lang     = $userADFS['lang'];
            }//lang


            $newUser->confirmed     = '1';
            $newUser->firstaccess   = time();
            $newUser->calendartype  = $CFG->calendartype;
            $newUser->timecreated   = time();
            $newUser->timemodified  = $newUser->timecreated;
            $newUser->mnethostid    = $CFG->mnet_localhost_id;

            /* Execute  */
            $newUser->id = $DB->insert_record('user',$newUser);

            return $newUser->id;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateUser

    /**
     * @param           $userId
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generates the url where the user will be redirected
     *
     * @updateDate      15/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add course/activity link if it's necessary
     */
    private static function GenerateResponse($userId,$modLnk=null,$modId=null) {
        /* Variables    */
        global $CFG;
        $response       = null;
        $urlResponse    = null;

        try {
            /* Build URL Response */
            $urlResponse = $CFG->wwwroot . '/local/wsks/adfs/autologin.php?id=' . $userId;

            /* Check if the user has to be redirected to course/activity */
            if ($modLnk && $modId) {
                $urlResponse .= "&modlnk=" . $modLnk . "&modid=" . $modId;
            }////course/activity link

            /* URL Response */
            $response = urlencode($urlResponse);

            return $response;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GenerateResponse
}//WS_ADFS