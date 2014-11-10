<?php
/**
 * First Access - Library / CLass
 *
 * Description
 *
 * @package         local
 * @subpackage      force_profile
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      21/08/2014
 * @author          eFaktor     (fbv)
 *
 */

class FirstAccess {
    /**
     * @static
     * @param           $user_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    10/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if it's the first time that the user log in.
     */
    public static function IsFirstAccess($user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('user',array('id' => $user_id),'firstaccess,lastaccess,lastlogin');
            if ($rdo) {
                if (($rdo->firstaccess != $rdo->lastaccess) && ($rdo->lastlogin)) {
                    return true;
                }else {
                    return false;
                }
            }else {
                return false;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsFirstAccess
}//FirstAccess