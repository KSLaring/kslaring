<?php
/**
 * Express Login  - Cron - Autogenerate Express Login
 *
 * @package         local
 * @subpackage      express_login/cron
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    15/06/2015
 * @author          eFaktor     (fbv)
 */

class Express_Cron {
    /* **************** */
    /* PUBLIC FUNCTIONS */
    /* **************** */

    /**
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Express Login Cron
     */
    public static function cron() {
        /* Variables    */
        global $CFG;
        $noExpress  = null;
        $pluginInfo = null;
        $dbLog      = null;

        try {
            mtrace('Express Login Cron - Start');
            /* Plugin Info */
            $pluginInfo     = get_config('local_express_login');

            /* Get Users without Express Login  */
            mtrace('Express Login -- Get Users without Not Express Login');
            $noExpress = self::GetUsers_NotExpress();

            /* Auto Generate Express Login   */
            if ($noExpress) {
                mtrace('Express Login - Auto Generate Express Login - Start');
                self::Auto_ExpressLogin($noExpress,$pluginInfo);
                mtrace('Express Login Cron - Auto Generate Express Login Finish');
                /* Send eMail */
                mtrace('Express Login Cron - Send eMail - Start');
                foreach ($noExpress as $user) {
                    self::Send_ExpressLogin($user);
                }//for_Each_user
                mtrace('Express Login Cron - Send eMail - Finish');
            }//if_noExpress
            mtrace('Express Login Cron - Finish');
        }catch (Exception $ex) {
            /* Write Log    */
            mtrace('Express Login Cron Error - Look Log');
            $dbLog  = userdate(time(),'%d.%m.%Y', 99, false). ' Express Login Cron Error . ' . "\n";
            $dbLog .= 'Error: ' . $ex->getMessage() . "\n\n\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Express_Login_Cron.log");

            /* Any Error during the process - Clean Express Login Generated */
            self::Clean_ExpressLogin($noExpress);

            throw $ex;
        }//try_Catch
    }//cron

    /* ***************** */
    /* PRIVATE FUNCTIONS */
    /* ***************** */

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all users that have not generated his/her express login yet.
     */
    private static function GetUsers_NotExpress() {
        /* Variables    */
        global $DB;
        $noExpress  = array();
        $info       = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT			u.id,
                                    u.firstname,
                                    u.lastname,
                                    u.email,
                                    u.lang
                     FROM			{user}			u
                        LEFT JOIN	{user_express}	uex		ON uex.userid = u.id
                     WHERE			u.deleted = 0
                        AND			uex.id IS NULL
                        AND			u.username != 'guest'
                        AND			u.username NOT LIKE '%wsdossier%'
                        AND			u.username NOT LIKE '%wsdoskom%'
                        AND			u.email IS NOT NULL
                        AND			u.email <> '' ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info User    */
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->firstname . ' ' . $instance->lastname;
                    $info->email        = $instance->email;
                    $info->mailformat   = 1;
                    $info->lang         = $instance->lang;
                    $info->express      = null;

                    /* Add User */
                    $noExpress[$instance->id] = $info;
                }//for_user
            }//if_Rdo

            return $noExpress;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_NotExpress

    /**
     * @param           $noExpress
     * @param           $pluginInfo
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate automatically the express login
     */
    private static function Auto_ExpressLogin(&$noExpress,$pluginInfo) {
        /* Variables    */
        global      $DB;
        $minimum    = null;
        $digits     = null;
        $pinCode    = null;
        $created    = null;

        /* Start Transaction    */
        $transaction = $DB->start_delegated_transaction();
        try {
            /* Generate Express Login for each user */
            foreach ($noExpress as $user) {
                /* First Generate Pin Code  */
                $user->express = self::Generate_PinCode($pluginInfo);

                /* Auto generate Express Login   */
                $created = self::Generate_ExpressLogin($user->id,$user->express,$DB);

                /* If it has not been created, the user will be removed from the list   */
                if (!$created) {
                    unset($noExpress[$user->id]);
                }//if_not_Created
            }//for_each_user

            /* Commit   */
            $transaction->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $transaction->rollback($ex);

            throw $ex;
        }//try_catch
    }//Auto_ExpressLogin

    /**
     * @param           $pluginInfo
     * @return          string
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate an internal pin code for the express login
     */
    private static function Generate_PinCode($pluginInfo) {
        /* Variables    */
        $minimum    = null;
        $digits     = null;
        $pinCode    = null;

        try {
            /* First the correct number of digits   */
            $minimum    = array('4','6','8');
            $digits     = $minimum[$pluginInfo->minimum_digits];

            $pinCode = str_shuffle(mt_rand());
            $pinCode = substr ($pinCode, 0, $digits);

            return $pinCode;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Generate_PinCode

    /**
     * @param           $userId
     * @param           $express
     * @param           $DB
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create an express login for the user
     */
    private static function Generate_ExpressLogin($userId,$express,$DB) {
        /* Variables    */
        $expressInfo = null;

        try {
            /* Express Info */
            $expressInfo = new stdClass();
            $expressInfo->userid       = $userId;
            $expressInfo->express      = self::GenerateHash($express);
            $expressInfo->remind       = self::generateSecurityPhrase($userId);
            $expressInfo->token        = self::Generate_ExpressToken($expressInfo->remind);
            $expressInfo->attempt      = 0;
            $expressInfo->timecreated  = time();

            /* Save Express Login   */
            /* Check if the user has created his/her own Express Login  during the process */
            if (!$DB->get_record('user_express',array('userid' => $userId))) {
                $DB->insert_record('user_express',$expressInfo);
                return true;
            }else {
                return false;
            }//if_else_exist
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Generate_ExpressLogin

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
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate an internal security phrase
     */
    private static function generateSecurityPhrase($userId) {
        /* Variables        */
        global $DB;
        /* Ticket User      */
        $ticket = null;
        /* Token            */
        $token  = null;
        /* Remind   */
        $remind = null;

        try {
            /* Ticket - Something long and Unique   */
            $token  = uniqid(mt_rand(),1);
            $ticket = random_string() . $userId . '_' . time() . '_' . $token . random_string();
            $remind = self::GenerateHash($ticket);

            /* Check if justs exist for other user  */
            while ($DB->record_exists('user_express',array('userid' => $userId,'remind' => $remind))) {
                /* Ticket - Something long and Unique   */
                $token  = uniqid(mt_rand(),1);
                $ticket = random_string() . $userId . '_' . time() . '_' . $token . random_string();
                $remind = self::GenerateHash($ticket);
            }//while

            return $remind;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//generateSecurityPhrase

    /**
     * @param           $securityPhrase
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate the token
     */
    private static function Generate_ExpressToken($securityPhrase) {
        /* Variables    */
        $token              = '';
        $first_part         = null;
        $second_part        = null;
        $third_part         = null;
        $rand               = null;

        try {
            /* Split the string in three parts    */
            $first_part     = substr($securityPhrase,0,10);
            $second_part    = substr($securityPhrase,10,20);
            $third_part     = substr($securityPhrase,20);

            for ($i=1;$i<=3;$i++) {
                $rand = mt_rand(1,6);
                switch ($rand) {
                    case '1':
                    case '4':
                        $token .= time() . $third_part;
                        break;
                    case '2':
                    case '5':
                        $token .= $first_part . time();
                        break;
                    case '3':
                    case '6':
                        $token .= time() . $second_part;
                        break;
                    default:
                        break;
                }//switch
            }

            $token = str_replace('/','.',self::GenerateHash($token));

            return $token;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Generate_ExpressToken

    /**
     * @param           $user
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send by email the express login to the user
     */
    private static function Send_ExpressLogin($user) {
        /* Variables    */
        global $SITE;
        $subject    = null;
        $body       = null;
        try {
            $subject        = (string)new lang_string('express_subject','local_express_login',$SITE->shortname,$user->lang);
            $body           = (string)new lang_string('express_body','local_express_login',$user,$user->lang) . "</br></br>";

            email_to_user($user, $SITE->shortname, $subject, $body,$body);
        }catch (Exception $ex) {
            echo $ex->getMessage();
            throw $ex;
        }//try_Catch
    }//Send_ExpressLogin

    /**
     * @param           $noExpress
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean the express login generated automatically, if there is any error during the process.
     */
    private static function Clean_ExpressLogin($noExpress) {
        /* Variables    */
        global $DB;

        try {
            if ($noExpress) {
                foreach ($noExpress as $userId=>$user) {
                    /* Execute  */
                    $DB->delete_records('user_express',array('userid' => $userId));
                }//for_Each_user
            }//if_noExpress
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Clean_ExpressLogin
}//Express_Cron
