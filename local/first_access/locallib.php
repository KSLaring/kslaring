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
     * Check if the user has to update his/her profile
     *
     * @updateDate      14/04/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Exclude the DOSKOM users
     */
    public static function HasToUpdate_Profile($user_id) {
        try {
            /* Exclude DOSKOM Users */
            if (self::IsDoskomUser($user_id)) {
                return false;
            }else {
                /* Check First Access   */
                if (self::IsFirstAccess($user_id)) {
                    return true;
                }else {
                    /* Check User Extra Profile Fields - Obligatory   */
                    if (self::ExtraProfileFields_Completed($user_id)) {
                        /* Check User Profile Completed */
                        if (self::ProfileFields_Completed($user_id)) {
                            return false;
                        }else {
                            return true;
                        }
                    }else {
                        return true;
                    }///if_profile
                }//if_first_access
            }//if_doskom_user
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//HasToUpdate_Profile

    /**
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    18/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the structure for the municipality extra user profile
     */
    public static function GetMunicipalityProfile() {
        /* Variables    */
        global $DB;
        $muniProfile = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['datatype'] = 'municipality';

            /* Execute  */
            $muniProfile = $DB->get_record('user_info_field',$params);

            return $muniProfile;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetMunicipalityProfile

    public static function Update_UserProfile($data) {
        /* Variables    */
        global $DB;
        $userInfo    = null;

        try {
            /* Info to Update   */
            $userInfo = new stdClass();
            $userInfo->id           = $data->id;
            $userInfo->firstname    = $data->firstname;
            $userInfo->lastname     = $data->lastname;
            $userInfo->email        = $data->email;
            $userInfo->city         = $data->city;
            if (isset($data->country) && ($data->country)) {
                $userInfo->country      = $data->country;
            }//if_data_country

            /* Execute  */
            $DB->update_record('user',$userInfo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Update_UserProfile

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @param           $user_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    14/04/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Check if the user comes from DOSKOM
     */
    private static function IsDoskomUser($user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('user',array('id' => $user_id),'source');
            if ($rdo) {
                if (strtoupper($rdo->source == 'KOMMIT')) {
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
    }//IsDoskomUser

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
    private static function IsFirstAccess($user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('user',array('id' => $user_id),'firstaccess,lastaccess,lastlogin');
            if ($rdo) {
                if (($rdo->firstaccess == $rdo->lastaccess) && (!$rdo->lastlogin)) {
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
     * Check if the user has been completed all the extra profile fields that are obligatory
     */
    private static function ExtraProfileFields_Completed($user_id) {
        /* Variables    */
        global $DB;
        $params     = array();

        try {
            /* First, it gets all the fields that are compulsory    */
            /* Search Criteria  */
            $params['required'] = 1;
            /* SQL Instruction  */
            $sql = " SELECT		GROUP_CONCAT(DISTINCT uif.id ORDER BY uif.id SEPARATOR ',') as 'obligatory'
                     FROM		{user_info_field} uif
                     WHERE		uif.required = :required ";
            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Sencond, it gets all the user fields profile that are compulsory and that have been completed by the user    */
                /* Search Criteria  */
                $params['user_id'] = $user_id;
                /* SQL Instruction  */
                $sql = " SELECT		GROUP_CONCAT(DISTINCT uid.fieldid ORDER BY uid.fieldid SEPARATOR ',') as 'completed'
                         FROM		{user_info_data}		uid
                            JOIN	{user_info_field}		uif		ON 	uif.id        = uid.fieldid
                                                                    AND uif.required  = :required
                         WHERE		uid.userid = :user_id ";

                /* Execute  */
                $rdo_user = $DB->get_record_sql($sql,$params);
                if ($rdo_user) {
                    if ($rdo_user->completed == $rdo->obligatory) {
                        return true;
                    }else {
                        /* Not Completed    */
                        return false;
                    }//if_else_completed_obligatory
                }else {
                    /* Not Completed        */
                    return false;
                }//if_else_rdo_user
            }else {
                return true;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ExtraProfileFields_Completed

    /**
     * @static
     * @param           $user_id
     * @return          bool|null
     * @throws          Exception
     *
     * @creationDate    10/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the profile fields have been completed
     */
    private static function ProfileFields_Completed($user_id) {
        /* Variables    */
        global $DB;
        $completed = null;

        try {
            /* Execute  */
            $rdo = $DB->get_record('user',array('id' => $user_id),'username,firstname,lastname,email');

            $completed = $rdo->username && $rdo->firstname && $rdo->lastname && $rdo->email;

            return $completed;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ProfileFields_Completed
}//FirstAccess