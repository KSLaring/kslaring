<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 01/12/14
 * Time: 16:15
 * To change this template use File | Settings | File Templates.
 */

define('ERROR_EXPRESS_LINK_NOT_VALID',1);
define('ERROR_EXPRESS_LINK_ATTEMPTED_EXCEEDED',2);
define('ERROR_EXPRESS_LINK_USER_NOT_VALID',3);
define('ERROR_EXPRESS_PIN_NOT_VALID',4);
define('MAX_ATTEMPTS',3);

class Express_Link {

    /**
     * @param           $frm
     * @return          array
     *
     * @creationDate    01/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate the user express
     */
    public static function Validate_UserExpress($frm) {
        /* Variables    */
        $user_express   = null;
        $pin_code_valid = null;

        /* Try to get The information connected to the user express */
        try {
            $user_express = self::Get_UserExpress($frm->UserName);
            if ($user_express) {
                $pin_code_valid = password_verify($frm->pincode, $user_express);
                if ($pin_code_valid) {
                    return array(true,null);
                }else {
                    return array(false,ERROR_EXPRESS_PIN_NOT_VALID);
                }
            }else {
                return array(false,ERROR_EXPRESS_LINK_USER_NOT_VALID);
            }
        }catch (Exception $ex) {
            return array(false,ERROR_EXPRESS_LINK_USER_NOT_VALID);
        }//try_catch
    }//Validate_UserExpress

    /**
     * @param           $user_id
     * @param           int $er
     * @return          bool
     *
     * @creationDate    01/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the number of attempts connected to user
     */
    public static function Update_Attempts($user_id,$er=0) {
        try {
            if ($er) {
                self::IncrementAttempt($user_id);
            }else {
                self::ResetAttempt($user_id);
            }

            return true;
        }catch (Exception $ex) {
            return false;
        }
    }//Update_Attempts

    /**
     * @param           $user_id
     * @return          bool
     *
     * @creationDate    02/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Complete the Login process
     */
    public static function LoginUser($user_id) {
        try {
            $user = get_complete_user_data('id', $user_id);
            complete_user_login($user,true);

            return true;
        }catch (Exception $ex) {
            return false;
        }//try_catch
    }//LoginUser

    /**
     * @param           $user
     * @return          bool
     *
     * @creationDate    01/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate the number of attemtps
     */
    public static function Validate_UserAttempts($user) {
        /* Variables    */
        $num_attempts = null;

        try {
            $num_attempts = self::Get_UserAttempts($user);

            if ($num_attempts < MAX_ATTEMPTS) {
                return true;
            }else {
                return false;
            }//if_num_attempts
        }catch (Exception $ex) {
            return false;
        }
    }//Validate_UserAttempts



    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $user
     * @return          bool|mixed
     * @throws          Exception
     *
     * @creationDate    01/12/2014
     * @author          eFaktor     (fbv)
     *
     * Get the express link connected to the user
     */
    private static function Get_UserExpress($user) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('user_express',array('userid' => $user),'express');
            if ($rdo) {
                return $rdo->express;
            }else {
                return false;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_UserExpress

    /**
     * @param           $user_id
     * @throws          Exception
     *
     * @creationDate    01/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Increment the number of attempts
     */
    private static function IncrementAttempt($user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user_id'] = $user_id;

            /* SQL Instruction  */
            $sql = " UPDATE	{user_express}
                        SET	attempt = attempt + 1
                     WHERE 	userid = :user_id ";

            /* Execute  */
            $DB->execute($sql,$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IncrementAttempt

    /**
     * @param           $user_id
     * @throws          Exception
     *
     * @creationDate    01/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Reset the number of attempts
     */
    private static function ResetAttempt($user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user_id'] = $user_id;

            /* SQL Instruction  */
            $sql = " UPDATE	{user_express}
                        SET	attempt = 0
                     WHERE 	userid = :user_id ";

            /* Execute  */
            $DB->execute($sql,$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ResetAttempt

    /**
     * @param           $user
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    01/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the number of attempts of user
     */
    private static function Get_UserAttempts($user) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['userid'] = $user;

            /* Execute  */
            $rdo = $DB->get_record('user_express',$params,'attempt');

            return $rdo->attempt;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_UserAttempts
}//Express_Link