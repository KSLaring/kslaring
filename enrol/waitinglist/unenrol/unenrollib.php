<?php
/**
 * Unenrol Action   - Library
 *
 * @package         enrol/waitinglist
 * @subpackage      unenrol
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    29/12/2015
 * @author          efaktor     (fbv)
 *
 * Description
 */
Class Unenrol_Waiting {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $userId
     * @param           $courseId
     * @param           $waitingId
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    02/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get link to unenrol
     */
    public static function UnenrolLink($userId,$courseId,$waitingId) {
        /* Variables */
        global $CFG;
        $lnkUnenrol     = null;
        $unenrolInfo    = null;
        
        try {
            /* Get Unenrol info */
            $unenrolInfo = self::Generate_UnenrolInstance($userId,$courseId,$waitingId);
            
            /* Generate link    */
            $lnkUnenrol = $CFG->wwwroot . '/enrol/waitinglist/unenrol/unenrol.php';
            $lnkUnenrol .= '/' . $unenrolInfo->userid . '/' . $unenrolInfo->tokenus;
            $lnkUnenrol .= '/' . $unenrolInfo->courseid . '/' . $unenrolInfo->tokenco;

            return $lnkUnenrol;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UnenrolLink

    /**
     * @param           $lnkUnenrol
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    02/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check unenrol link
     */
    public static function Check_UnenrolLink($lnkUnenrol) {
        /* Variables */
        global $DB;
        $sql    = null;
        $rdo    = null;
        $params = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user']         = $lnkUnenrol[0];
            $params['tkn_user']     = $lnkUnenrol[1];
            $params['course']       = $lnkUnenrol[2];
            $params['tkn_course']   = $lnkUnenrol[3];

            /* SQL Instruction  */
            $sql = " SELECT		ewu.id,
                                ewu.waitingid,
                                ewu.courseid
                     FROM		{enrol_waitinglist_unenrol}	ewu
                        JOIN	{user}						u	ON	u.id		= ewu.userid
                        JOIN	{enrol}						e	ON	e.id		= ewu.waitingid
                                                                AND	e.courseid	= ewu.courseid
                     WHERE	  ewu.userid 	= :user
                        AND   ewu.tokenus 	= :tkn_user
                        AND	  ewu.courseid 	= :course
                        AND   ewu.tokenco 	= :tkn_course ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Check_UnenrolLink

    /**
     * @param           $lnkUnenrol
     *
     * @return          bool
     * @throws          Exception
     */
    public static function IsUnenrolled($lnkUnenrol) {
        /* Variables */
        $context = null;
        
        try {
            $context = context_course::instance($lnkUnenrol[2]);
            
            if (is_enrolled($context,$lnkUnenrol[0])) {
                return false;
            }else {
                return true;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsEnrolled

    /**
     * @param           $lnkUnenrol
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    03/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unenrol user
     */
    public static function UnenrolUser($lnkUnenrol) {
        /* Variables    */
        global $DB;
        $wlPlugin   = null;
        $params     = null;
        $instance   = null;

        try {
            /* Plugin   */
            $wlPlugin         = enrol_get_plugin('waitinglist');

            /* Instance Plugins */
            $params = array();
            $params['courseid'] = $lnkUnenrol[2];
            $params['enrol']    = 'waitinglist';
            /* Execute */
            $instance = $DB->get_record('enrol',$params);
            
            /* Unenrol User */
            if ($instance) {
                $wlPlugin->unenrol_user($instance,$lnkUnenrol[0]);

                /* Delete UnEnrol Action */
                $params = array();
                $params['userid']   = $lnkUnenrol[0];
                $params['tokenus']  = $lnkUnenrol[1];
                $params['courseid'] = $lnkUnenrol[2];
                $params['tokenco']  = $lnkUnenrol[3];
                /* Execute */
                $DB->delete_records('enrol_waitinglist_unenrol',$params);

                return true;
            }else {
                return false;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UnenrolUser

    /**
     * @param           $courseId
     * @param           $waitingId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    17/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user can unenrol from the course based on deadline
     */
    public static function Can_Unenrol($courseId,$waitingId) {
        /* Variables */
        global $DB;
        $sql    = null;
        $rdo    = null;
        $params = null;

        try {
            /* Criteria */
            $params = array();
            $params['course']   = $courseId;
            $params['wait']     = $waitingId;

            /* SQL Instruction */
            $sql = " SELECT id,
                            unenrolenddate 
                     FROM   {enrol_waitinglist_method} 
                     WHERE  courseid      = :course
                        AND waitinglistid = :wait
                        AND methodtype  like '%self%'";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->unenrolenddate != 0 and $rdo->unenrolenddate < time()) {
                    return false;
                }else {
                    return true;
                }
            }else {
                return true;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Can_Unenrol

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $userId
     * @param           $courseId
     * @param           $waitingId
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    02/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate all tokens and information for  unenrol action
     */
    private static function Generate_UnenrolInstance($userId,$courseId,$waitingId) {
        /* Variables */
        global $DB;
        $unenrolInfo = null;

        try {
            /* Unenrol Info */
            $unenrolInfo = new stdClass();
            $unenrolInfo->waitingid     = $waitingId;
            $unenrolInfo->courseid      = $courseId;
            $unenrolInfo->tokenco       = self::generateCourseToken($courseId);
            $unenrolInfo->userid        = $userId;
            $unenrolInfo->tokenus       = self::generateUserToken($userId);
            $unenrolInfo->timecreated   = time();

            /* Save */
            $unenrolInfo->id = $DB->insert_record('enrol_waitinglist_unenrol',$unenrolInfo);

            return $unenrolInfo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Generate_UnenrolInstance

    /**
     * @param           $value
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate a hash for sensitive values
     */
    private static function GenerateHash($value) {
        /* Variables    */
        $cost               = 10;
        $required_salt_len  = 22;
        $buffer             = '';
        $buffer_valid       = false;
        $hash_format        = null;
        $salt               = null;
        $ret                = null;
        $hash               = null;

        try {
            /* Generate hash    */
            $hash_format        = sprintf("$2y$%02d$", $cost);
            $raw_length         = (int) ($required_salt_len * 3 / 4 + 1);

            if (function_exists('mcrypt_create_iv')) {
                $buffer = mcrypt_create_iv($raw_length, MCRYPT_DEV_URANDOM);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }

            if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
                $buffer = openssl_random_pseudo_bytes($raw_length);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }

            if (!$buffer_valid && file_exists('/dev/urandom')) {
                $f = @fopen('/dev/urandom', 'r');
                if ($f) {
                    $read = strlen($buffer);
                    while ($read < $raw_length) {
                        $buffer .= fread($f, $raw_length - $read);
                        $read = strlen($buffer);
                    }
                    fclose($f);
                    if ($read >= $raw_length) {
                        $buffer_valid = true;
                    }
                }
            }

            if (!$buffer_valid || strlen($buffer) < $raw_length) {
                $bl = strlen($buffer);
                for ($i = 0; $i < $raw_length; $i++) {
                    if ($i < $bl) {
                        $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                    } else {
                        $buffer .= chr(mt_rand(0, 255));
                    }
                }
            }

            $salt = str_replace('+', '.', base64_encode($buffer));

            $salt = substr($salt, 0, $required_salt_len);

            $hash = $hash_format . $salt;

            $ret = crypt($value, $hash);

            if (!is_string($ret) || strlen($ret) <= 13) {
                return false;
            }

            return $ret;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GenerateHash

    /**
     * @param           $userId
     *
     * @return          bool|null|string
     * @throws          Exception
     *
     * @creationDate    01/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate token for user
     */
    private static function generateUserToken($userId) {
        /* Variables        */
        global $DB;
        /* Ticket           */
        $ticket = null;
        /* Token            */
        $token  = null;
        /* User Token       */
        $security = null;

        try {
            /* Ticket - Something long and Unique   */
            $token      = uniqid(mt_rand(),1);
            $ticket     = random_string() . $userId . '_' . time() . '_' . $token . random_string();
            $security   = self::GenerateHash($ticket);
            $security   = str_replace('/','.',$security);

            /* Check if justs exist for other user  */
            while ($DB->record_exists('enrol_waitinglist_unenrol',array('userid' => $userId,'tokenus' => $security))) {
                /* Ticket - Something long and Unique   */
                $token      = uniqid(mt_rand(),1);
                $ticket     = random_string() . $userId . '_' . time() . '_' . $token . random_string();
                $security   = self::GenerateHash($ticket);
                $security   = str_replace('/','.',$security);
            }//while

            return $security;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//generateUserToken

    /**
     * @param           $courseId
     *
     * @return          bool|null|string
     * @throws          Exception
     *
     * @creationDate    01/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate token connected with the course
     */
    private static function generateCourseToken($courseId) {
        /* Variables        */
        global $DB;
        /* Ticket           */
        $ticket = null;
        /* Token            */
        $token  = null;
        /* Course Token     */
        $security = null;

        try {
            /* Ticket - Something long and Unique   */
            $token      = uniqid(mt_rand(),1);
            $ticket     = random_string() . $token. '_' . time() . '_' . $courseId . random_string();
            $security   = self::GenerateHash($ticket);
            $security   = str_replace('/','.',$security);
            /* Check if justs exist for other user  */
            while ($DB->record_exists('enrol_waitinglist_unenrol',array('courseid' => $courseId,'tokenco' => $security))) {
                /* Ticket - Something long and Unique   */
                $token      = uniqid(mt_rand(),1);
                $ticket     = random_string() . $token. '_' . time() . '_' . $courseId . random_string();
                $security   = self::GenerateHash($ticket);
                $security   = str_replace('/','.',$security);
            }//while

            return $security;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//generateUserToken
}//Unenrol_Waiting