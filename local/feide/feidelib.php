<?php
/**
 * Feide Integration WebService - Library
 *
 * @package         local
 * @subpackage      feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    21/09/2015
 * @author          eFaktor     (fbv)
 *
 */

class WS_FEIDE {
    /**********/
    /* PUBLIC */
    /**********/


    /**
     * @param           $userId
     * @return          string
     * @throws          Exception
     *
     * @creationDate    22/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate the response where the user has to be redireced
     */
    public static function GenerateResponse($userId) {
        /* Variables */
        $myTicket = null;

        try {
            /* Generate Ticket User */
            $myTicket   = self::GenerateTicket($userId);

            /* Generate Response    */
            $urlKS      = self::GetKSResponse($userId,$myTicket);

            return $urlKS;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GenerateResponse

    /**
     * @param           $userId
     * @param           $userTicket
     * @param           $result
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate user
     */
    public static function ValidateUser($userId,$userTicket,&$result) {
        /* Variables    */

        try {
            /* Check if the user is valid   */
            if ($userId && $userTicket) {
                self::ValidateTicketUser($userId,$userTicket,$result);
                /* Get User Info    */
                if ($result['valid']) {
                    self::GetUserInfo($userId,$userTicket,$result);
                }
            }//if_userId_ticket
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ValidateUser


    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $userId
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    21/09/2015
     * @author          efaktor     (fbv)
     *
     * Description
     * Generate the private ticket connected with the user.
     */
    private static function GenerateTicket($userId) {
        /* Variables        */
        global $DB;
        $ticket     = null;
        $token      = null;
        $key        = null;
        $validUntil = null;
        $remind     = null;


        try {
            /* First Clean OLD Tickets  */
            $DB->delete_records('user_private_key',array('userid' => $userId,'script' => 'wsfeide'));

            /* Valid until  */
            $validUntil = time() + 60*10;

            /* Ticket - Something long and Unique   */
            $token  = uniqid(mt_rand(),1);
            $ticket = random_string() . $userId . '_' . time() . '_' . $token . random_string();
            $remind = self::GenerateHash($ticket);
            $remind = str_replace('/', '.', $remind);

            /* Key */
            $key = new stdClass();
            $key->script        = 'wsfeide';
            $key->userid        = $userId;
            $key->validuntil    = $validUntil;
            $key->timecreated   = time();
            $key->value         = $remind;
            while ($DB->record_exists('user_private_key', array('value' => $key->value))) {
                /* Ticket - Something long and Unique   */
                $token          = uniqid(mt_rand(),1);
                $ticket         = random_string() . $userId . '_' . time() . '_' . $token . random_string();
                $remind         = self::GenerateHash($ticket);
                $remind         = str_replace('/', '.', $remind);
                $key->value     = $remind;
            }//while

            $DB->insert_record('user_private_key', $key);

            return $remind;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GenerateTicket

    /**
     * @param           $userId
     * @param           $myTicket
     * @return          string
     * @throws          Exception
     *
     * @creationDate    22/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the url where the user should be redirected
     */
    private static function GetKSResponse($userId,$myTicket) {
        /* Variables    */
        $urlKS      = null;
        $pluginInfo = null;


        try {
            /* Plugin Info */
            $pluginInfo = get_config('local_feide');

            /* Make KS URL Response */
            $urlKS = $pluginInfo->ks_point . "/local/wsks/feide/login.php/" . $userId . "/" . $myTicket;

            return $urlKS;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetKSResponse

    /**
     * @param           $userId
     * @param           $userTicket
     * @param           $result
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    21/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate if it's a valid user or not
     */
    private static function ValidateTicketUser($userId,$userTicket,&$result) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['script']   = 'wsfeide';
            $params['user_id']  = $userId;
            $params['ticket']   = $userTicket;
            $params['valid']    = time();

            /* SQL Instruction  */
            $sql = " SELECT		upk.id
                     FROM		{user_private_key} upk
                     WHERE		upk.script       = :script
                        AND		upk.userid       = :user_id
                        AND		upk.value        = :ticket
                        -- AND		upk.validuntil  >= :valid ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->id) {
                    $result['valid'] = 1;
                }else {
                    $result['valid'] = 0;
                }
            }else {
                $result['valid'] = 0;
            }//if_rdo
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . " -- Validate Ticket";

            throw $ex;
        }//try_catch
    }//ValidateTicketUser

    /**
     * @param           $userId
     * @param           $userTicket
     * @param           $result
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    22/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get user info
     */
    private static function GetUserInfo($userId,$userTicket,&$result) {
        /* Variables    */
        global $DB;
        $params     = null;
        $sql        = null;
        $rdo        = null;
        $user       = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['user']     = $userId;
            $params['script']   = 'wsfeide';
            $params['ticket']   = $userTicket;
            $params['valid']    = time();

            /* SQL Instruction  */
            $sql = " SELECT	  u.username,
                              u.firstname,
                              u.lastname,
                              u.email,
                              u.city,
                              u.country,
                              u.lang
                     FROM		{user}				u
                        JOIN	{user_private_key}	upk		ON 	upk.userid 		= u.id
                                                            AND	upk.script		= :script
                                                            AND	upk.value		= :ticket
                                                            -- AND upk.validuntil  >= :valid
                     WHERE	  u.id 		= :user
                        AND   u.deleted = 0 ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* User Info    */
                $userInfo = new stdClass();
                $userInfo->username   = $rdo->username;
                $userInfo->firstname  = $rdo->firstname;
                $userInfo->lastname   = $rdo->lastname;
                $userInfo->email      = $rdo->email;
                $userInfo->city       = $rdo->city;
                $userInfo->country    = $rdo->country;
                $userInfo->lang       = $rdo->lang;

                /* Add User */
                $user[0]        = $userInfo;
                $result['user'] = $user;
            }//if_rdo
        }catch (Exception $ex) {
            $result['error']        = 409;
            $result['msg_error']    = $ex->getMessage() . ' - ' . " -- Get User Info";

            throw $ex;
        }//try_Catch
    }//GetUserInfo

    /**
     * @param           $value
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    21/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate hash
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
}//WS_FEIDE