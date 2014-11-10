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
    public static function IsFirstAccess($user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('user',array('id' => $user_id),'firstaccess');
            if ($rdo) {
                if ($rdo->firstaccess) {
                    return false;
                }else {
                    return true;
                }
            }else {
                return true;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsFirstAccess
}//FirstAccess