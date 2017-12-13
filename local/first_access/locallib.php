<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * First access - Library
 *
 * @package
 * @subpackage
 * @copyright       2012    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    10/11/2014
 * @author          eFaktor     (fbv)
 *
 * @updateDate      12/06/2017
 * @author          eFaktor     (fbv)
 */

class FirstAccess {
    /**
     * Description
     * Check if the user has to update his/her profile
     * Exclude the DOSKOM users - 14/04/2015
     *
     * @static
     * @param           $user_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    10/11/2014
     * @author          eFaktor     (fbv)
     *
     * @updateDate      14/04/2015
     * @author          eFaktor         (fbv)
     */
    public static function has_to_update_profile($user_id) {
        /* Variables    */
        global $CFG;
        $updateProfile      = null;
        $userDoskom         = null;

        try {
            // Exclude DOSKOM users
            if (file_exists($CFG->dirroot.'/local/doskom')) {
                $userDoskom = self::is_doskom_user($user_id);
            }

            // user Doskom excluded
            if ($userDoskom) {
                $updateProfile = false;
            }else {
                // Check first access
                if (self::is_first_access($user_id)) {
                    $updateProfile = true;
                }else {
                    // User profile completed
                    if (self::has_completed_all_user_profile($user_id)) {
                        // Completed all extra user profile
                        if (self::has_completed_all_extra_profile($user_id)) {
                            // Completed competence profile
                            // to update
                            $updateProfile = false;
                        }else {
                            // to update
                            $updateProfile = true;
                        }//if_else_AllExtraProfile
                    }else {
                        // to update
                        $updateProfile = true;
                    }//if_else_allUseProfile
                }//if_first_access
            }//if_doskom_user

            return $updateProfile;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//has_to_update_profile

    /**
     * Description
     * Check if the user has completed all his/her profile
     * username, firstname, lastname and email
     *
     * @param           integer $userId
     *
     * @return                  bool
     * @throws                  Exception
     *
     * @creationDate    14/10/2015
     * @author          eFaktor     (fbv)
     */
    public static function has_completed_all_user_profile($userId) {
        /* Variables    */
        global $DB;
        $rdo    = null;
        $params = null;
        $sql    = null;

        try {
            // Search criteria
            $params = array();
            $params['user'] = $userId;

            // SQL Instruction
            $sql = " SELECT		u.*
                     FROM		{user}	u
                     WHERE		u.id = :user
                        AND 	(u.username  IS NOT NULL 	AND 	u.username  != '')
                        AND		(u.firstname IS NOT NULL 	AND 	u.firstname != '')
                        AND		(u.lastname  IS NOT NULL	AND		u.lastname 	!= '')
                        AND		(u.email     IS NOT NULL	AND		u.email		!= '') ";


            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                // All completed
                return true;
            }else {
                // No completed
                return false;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//has_completed_all_user_profile

    /**
     * Description
     * Check if the user has completed all the extra profile fields that are compulsory
     *
     * @param           integer $userId
     *
     * @return                  bool
     * @throws                  Exception
     *
     * @creationDate    14/10/2015
     * @author          eFaktor     (fbv)
     */
    public static function has_completed_all_extra_profile($userId) {
        /* Variables    */
        global $DB;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $extraProfile   = null;

        try {
            // First check, it there are extra profile fields to update
            $extraProfile = self::get_extra_profile_fields();
            if ($extraProfile) {
                // Check if has completed all extra profile
                // Search criteria
                $params = array();
                $params['user']         = $userId;
                $params['required']     = 1;
                $params['competence']   = 'competence';

                // SQL Isntruction
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
                        // All completed
                        return true;
                    }else {
                        // No completed
                        return false;
                    }
                }else {
                    // No completed
                    return false;
                }//if_else_rdo
            }else {
                // There is nothing to update
                return true;
            }//if_extraProfile
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//has_completed_all_extra_profile

    /**
     * Description
     * Check if the users has completed his/her competence profile
     *
     * @param           integer $userId
     *
     * @return                  bool
     * @throws                  Exception
     *
     * @creationDate    14/10/2015
     * @author          eFaktor     (fbv)
     */
    public static function has_completed_competence_profile($userId) {
        /* Variables    */
        global $DB;
        $rdo    = null;

        try {
            // First check if exist competence profile to update
            $rdo = $DB->get_record('user_info_field',array('datatype' => 'competence'),'id');
            if ($rdo) {
                // Check if user has completed
                $rdo = $DB->get_records('user_info_competence_data',array('userid' => $userId),'id');
                if ($rdo) {
                    // Completed
                    return true;
                }else {
                    // Not completed
                    return false;
                }//if_else
            }else {
                // Nothing to complete
                return true;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//has_completed_competence_profile

    /**
     * Description
     * Get the structure for the municipality extra user profile
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    18/06/2015
     * @author          eFaktor     (fbv)
     */
    public static function get_municipality_profile() {
        /* Variables    */
        global $DB;
        $muniProfile = null;
        $params      = null;

        try {
            // Search criteria
            $params = array();
            $params['datatype'] = 'municipality';

            // Execute
            $muniProfile = $DB->get_record('user_info_field',$params);

            return $muniProfile;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_municipality_profile

    /**
     * Description
     * Update user profile
     * Remove city          (12/02/17)
     *
     * @param           $data
     * @throws          Exception
     *
     * @creationDate    18/06/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      12/02/2017
     * @author          eFaktor     (fbv)
     */
    public static function update_user_profile($data) {
        /* Variables    */
        global $DB;
        $userInfo    = null;

        try {
            // Data to update
            $userInfo = new stdClass();
            $userInfo->id           = $data->id;
            $userInfo->firstname    = $data->firstname;
            $userInfo->lastname     = $data->lastname;
            $userInfo->email        = $data->email;
            $userInfo->timemodified = time();
            if (isset($data->country) && ($data->country)) {
                $userInfo->country      = $data->country;
            }//if_data_country

            // Execute
            $DB->update_record('user',$userInfo);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//update_user_profile

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * Description
     * Check if the user comes from DOSKOM
     *
     * @param           $user_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    14/04/2015
     * @author          eFaktor         (fbv)
     */
    private static function is_doskom_user($user_id) {
        /* Variables    */
        global $DB;
        $rdo = null;

        try {
            // Execute
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
    }//is_doskom_user

    /**
     * Description
     * Check if it's the first time that the user log in.
     *
     * @static
     * @param           $user_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    10/11/2014
     * @author          eFaktor     (fbv)
     */
    private static function is_first_access($user_id) {
        /* Variables    */
        global $DB;
        $rdo = null;

        try {
            // Execute
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
    }//is_first_access

    /**
     * Description
     * Get all extra profile fields that are compulsory
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    14/10/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_extra_profile_fields() {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            // Search criteria
            $params = array();
            $params['required']     = 1;
            $params['competence']   = 'competence';

            // SQL Isntruction
            $sql = " SELECT		GROUP_CONCAT(DISTINCT id ORDER BY id SEPARATOR ',') as 'obligatory'
                     FROM		{user_info_field}
                     WHERE		required  = :required
                        AND		datatype != :competence ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->obligatory;
            }else {
                return null;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_extra_profile_fields
}//FirstAccess