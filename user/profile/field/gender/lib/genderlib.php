<?php
/**
 * Gender Profile Field - Library
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/gender
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    04/10/2014
 * @author          eFaktor     (fbv)
 *
 */
define('MAN',1);
define('WOMAN',2);

class Gender {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    04/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the gender profile field has beeen created
     */
    public static function ExistGenderProfile() {
        /* Variables */
        global $DB;
        $rdo = null;

        try {
            /* Check if exist   */
            $rdo = $DB->get_record('user_info_field',array('datatype' => 'gender'));
            if ($rdo) {
                return $rdo->id;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ExistGenderProfile

    //public static function ExistGenderUser($userId) {
        
    //}//ExistGenderUser
    
    /**
     * @throws          Exception
     *
     * @creationDate    04/1072016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Creates gender profiel field instance
     */
    public static function CreateGenderProfile() {
        /* Variables */
        global $DB;
        $field = null;

        try {
            /* Field Instance   */
            $field = new stdClass();
            $field->shortname   = 'gender';
            $field->name        = 'gender';
            $field->datatype    = 'gender';
            $field->locked      = 0;
            $field->visible     = 1;
            $field->categoryid  = 1;
            
            /* Add  */
            $field->id = $DB->insert_record('user_info_field',$field);

            return $field->id;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateGenderProfile

    /**
     * @param           $fieldId
     * @param           $start
     * @param           $limit
     *
     * @throws          Exception
     *
     * @creationDate    04/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the gender to the user
     */
    public static function AddGender_ToUsers($fieldId,$start,$limit) {
        /* Variables */
        $lstUsers   = null;
        $position   = null;
        $remiander  = null;
        $toUpdate   = array();
        $gender     = null;

        try {
            /* Get users without gender         */
            $lstUsers = self::GetUsers_ToUpdate($fieldId,$start,$limit);

            /* Calculate gender for each one    */
            if ($lstUsers) {
                foreach ($lstUsers as $info) {
                    /* Position */
                    if ($info->idnumber) {
                        $position = substr($info->idnumber,8,1);
                    }else {
                        $position = substr($info->username,8,1);
                    }

                    /* Gender Info  */
                    $gender = new stdClass();
                    $gender->userid     = $info->id;
                    $gender->fieldid    = $fieldId;

                    /* Calculate Gender */
                    $remainder      = ($position % 2);
                    $gender->data   = ($remainder != 0 ? MAN : WOMAN);

                    /* Add User */
                    $toUpdate[$info->id] = $gender;
                    
                    $position   = null;
                    $remiander  = null;
                }//for_lstUSers

                if ($toUpdate) {
                    foreach ($toUpdate as  $gender) {
                        self::UpdateGender($gender);
                    }//for_toUpdate
                }//if_toUpdate
            }else {
                echo get_string('no_users','profilefield_gender') . "</br>";
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddGender_ToUsers

    public static function Add_UserGender($userId,$idNumber) {
        /* Variables    */
        global $DB;
        $rdo        = null;
        $position   = null;
        $remiander  = null;
        $gender     = null;

        try {
            /* Get Field Id */
            $rdo = $DB->get_record('user_info_field',array('datatype' => 'gender'));
            if ($rdo) {
                /* Position */
                $position = substr($idNumber,8,1);

                /* Gender Info  */
                $gender = new stdClass();
                $gender->userid     = $userId;
                $gender->fieldid    = $rdo->fieldid;

                /* Calculate Gender */
                $remainder      = ($position % 2);
                $gender->data   = ($remainder != 0 ? MAN : WOMAN);
                
                /* Update Gender    */
                self::UpdateGender($gender);
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_UserGender

    /**
     * @param           $userId
     * @param           $fieldId
     *
     * @return          mixed|null
     * @throws           Exception
     *
     * @creationDate    04/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the gender connected with the user
     */
    public static function GetGender_ByUser($userId,$fieldId) {
        /* Variables */
        global $DB;
        $sql    = null;
        $params = null;
        $rdo    = null;

        try {
            /* Search criteria  */
            $params = array();
            $params['userid']   = $userId;
            $params['fieldid']  = $fieldId;

            /* Execute  */
            $rdo = $DB->get_record('user_info_data',$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }
    }//GetGender_ByUser

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $fieldId
     * @param           $start
     * @param           $limit
     *
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    04/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get users witout gender to update
     */
    private static function GetUsers_ToUpdate($fieldId,$start,$limit) {
        /* Variables */
        global $DB;
        $sql    = null;
        $rdo    = null;
        $params = null;
        $REGEXP = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['field'] = $fieldId;
            $params['term']  = "^-?[0-9]+$";
            $params['term1']  = "^-?[0-9]+$";
            $REGEXP          = $DB->sql_regex(true);

            /* SQL Instruction  */
            $sql = " SELECT	u.id,
                            u.username,
                            IF(u.idnumber,u.idnumber,0) as 'idnumber'
                     FROM			{user}			  u
                        LEFT JOIN	{user_info_data}  uid	ON 	uid.userid 		= u.id
                                                            AND	uid.fieldid		= :field
                     WHERE	u.deleted = 0
                        AND (uid.id 		IS NULL
                             OR
                             uid.data = 0
                            )
	                    AND	((u.username IS NOT NULL AND  u.username != '' AND u.username $REGEXP :term AND length(u.username) = 11) 
		                     OR
                             (u.idnumber IS NOT NULL AND  u.idnumber != '' AND u.idnumber $REGEXP :term1 AND length(u.idnumber) = 11)
                            ) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_ToUpdate

    /**
     * @param           $gender
     *
     * @throws          Exception
     *
     * @creationDate    04/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the gender to the users
     */
    private static function UpdateGender($gender) {
        /* Variables */
        global $DB,$CFG;
        $rdo    = null;
        $gender = null;
        $key    = null;

        try {
            $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' START UPDATE GENDER . ' . "\n";

            /* Check if already exists an entrance  */
            $rdo = $DB->get_record('user_info_data',array('userid' => $gender->userid,'fieldid' => $gender->fieldid));
            if ($rdo) {
                $dbLog .= "1" . "\n";

                /* Update   */
                $rdo->data = $gender->data;
                $DB->update_record('user_info_data',$rdo);
            }else {
                $dbLog .= "2" . "\n";
                $dbLog .= "User:    " . $gender->userid . "\n";
                $dbLog .= "Data:    " . $gender->data . "\n";
                $dbLog .= "FIELD:   " . $gender->fieldid . "\n";

                error_log($dbLog, 3, $CFG->dataroot . "/gender.log");
                /* Insert   */
                $DB->insert_record('user_info_data',$gender);
            }//if_rdo
        }catch (Exception $ex) {


            $dbLog = " ERROR: " . $ex->getTraceAsString() . "\n";
            $dbLog .= "User:    " . $gender->userid . "\n";
            $dbLog .= "Data:    " . $gender->data . "\n";
            $dbLog .= "FIELD:   " . $gender->fieldid . "\n";
            
            error_log($dbLog, 3, $CFG->dataroot . "/gender.log");
            throw $ex;
        }//try_catch
    }//UpdateGender
}//Gender