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
        /* Variables    */
        $updateProfile      = false;

        try {
            /* Exclude DOSKOM Users */
            if (self::IsDoskomUser($user_id)) {
                $updateProfile = false;
            }else {
                /* Check First Access   */
                if (self::IsFirstAccess($user_id)) {
                    $updateProfile = true;
                }else {
                    /* Completed User Profile    */
                    if (self::HasCompleted_AllUserProfile($user_id)) {
                        /* Completed all Extra Profile  */
                        if (self::HasCompleted_AllExtraProfile($user_id)) {
                            /* Completed Competence Profile */
                            if (self::HasCompleted_CompetenceProfile($user_id)) {
                                /* No to update */
                                $updateProfile = false;
                            }else {
                                /* To Update    */
                                $updateProfile = true;
                            }//if_competenceProfile
                        }else {
                            /* To Update    */
                            $updateProfile = true;
                        }//if_else_AllExtraProfile
                    }else {
                        /* To Update    */
                        $updateProfile = true;
                    }//if_else_allUseProfile
                }//if_first_access
            }//if_doskom_user

            return $updateProfile;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//HasToUpdate_Profile

    /**
     * @param           $userId
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    14/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user has completed all his/her profile
     * username, firstname, lastname and email
     */
    public static function HasCompleted_AllUserProfile($userId) {
        /* Variables    */
        global $DB;
        $rdo    = null;
        $params = null;
        $sql    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $userId;

            /* SQL Instruction  */
            $sql = " SELECT		u.*
                     FROM		{user}	u
                     WHERE		u.id = :user
                        AND 	(u.username  IS NOT NULL 	AND 	u.username  != '')
                        AND		(u.firstname IS NOT NULL 	AND 	u.firstname != '')
                        AND		(u.lastname  IS NOT NULL	AND		u.lastname 	!= '')
                        AND		(u.email     IS NOT NULL	AND		u.email		!= '') ";


            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* All user profile completed   */
                return true;
            }else {
                /* Not Completed    */
                return false;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//HasCompleted_AllUserProfile

    /**
     * @param           $userId
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    14/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user has completed all the extra profile fields that are compulsory
     */
    public static function HasCompleted_AllExtraProfile($userId) {
        /* Variables    */
        global $DB;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $extraProfile   = null;

        try {
            /* First Check if there are extra profile fields to update  */
            $extraProfile = self::Get_ExtraProfileFields();
            if ($extraProfile) {
                /* Check if has completed all extra profile */
                /* Search Criteria  */
                $params = array();
                $params['user']         = $userId;
                $params['required']     = 1;
                $params['competence']   = 'competence';

                /* SQL Instruction  */
                $sql = " SELECT 	GROUP_CONCAT(DISTINCT uid.fieldid ORDER BY uid.fieldid SEPARATOR ',') as 'completed'
                         FROM		{user_info_data}	uid
                            JOIN	{user_info_field}	uif	  ON 	uif.id 			= uid.fieldid
                                                              AND	uif.required	= :required
                                                              AND   uif.datatype   != :competence
                         WHERE		uid.userid = :user
                            AND		(uid.data IS NOT NULL 	AND uid.data != '') ";

                /* Execute  */
                $rdo = $DB->get_record_sql($sql,$params);
                if ($rdo) {
                    if ($rdo->completed == $extraProfile) {
                        /* All Completed    */
                        return true;
                    }else {
                        /* Not Completed    */
                        return false;
                    }
                }else {
                    /* Not completed    */
                    return false;
                }//if_else_rdo
            }else {
                /* There is nothing to update   */
                return true;
            }//if_extraProfile
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//HasCompleted_AllExtraProfile

    /**
     * @param           $userId
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    14/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the users has completed his/her competence profile
     */
    public static function HasCompleted_CompetenceProfile($userId) {
        /* Variables    */
        global $DB;
        $rdo    = null;

        try {
            /* First check if exist competence profile to update    */
            $rdo = $DB->get_record('user_info_field',array('datatype' => 'competence'),'id');
            if ($rdo) {
                /* Check if user has completed  */
                $rdo = $DB->get_records('user_info_competence_data',array('userid' => $userId),'id');
                if ($rdo) {
                    /* Completed    */
                    return true;
                }else {
                    /* Not Completed    */
                    return false;
                }//if_else
            }else {
                /* Nothing to complete  */
                return true;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//HasCompleted_CompetenceProfile

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
        $params      = null;

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

    /**
     * @param           $data
     * @throws          Exception
     *
     * @creationDate    18/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update user profile
     */
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
            $userInfo->timemodified = time();
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
        $rdo = null;

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
        $rdo = null;

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
     * @return          null
     * @throws          Exception
     *
     * @creationDate    14/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all extra profile fields that are compulsory
     */
    private static function Get_ExtraProfileFields() {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['required']     = 1;
            $params['competence']   = 'competence';

            /* SQL Instruction  */
            $sql = " SELECT		GROUP_CONCAT(DISTINCT id ORDER BY id SEPARATOR ',') as 'obligatory'
                     FROM		{user_info_field}
                     WHERE		required  = :required
                        AND		datatype != :competence ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->obligatory;
            }else {
                return null;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_ExtraProfileFields
}//FirstAccess