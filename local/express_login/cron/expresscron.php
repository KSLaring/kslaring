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
        $minimum    = null;
        $digits     = null;
        try {
            mtrace('Express Login Cron - Start');
            /* Plugin Info */
            $pluginInfo     = get_config('local_express_login');

            /* First the correct number of digits   */
            $minimum    = array('4','6','8');
            $digits     = $minimum[$pluginInfo->minimum_digits];

            /* Generate Auto Express Login */
            self::Auto_ExpressLogin($digits);
        }catch (Exception $ex) {
            /* Write Log    */
            mtrace('Express Login Cron Error - Look Log');
            $dbLog  = userdate(time(),'%d.%m.%Y', 99, false). ' Express Login Cron Error . ' . "\n";
            $dbLog .= 'Error: ' . $ex->getMessage() . "\n\n\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Express_Login_Cron.log");

            /* Any Error during the process - Clean Express Login Generated */
            self::Clean_ExpressLogin();

            throw $ex;
        }//try_catch
    }//cron


    /* ***************** */
    /* PRIVATE FUNCTIONS */
    /* ***************** */

    /**
     * @param           $digits
     * @throws          Exception
     *
     * @updateDate      08/07/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate auto express login
     *
     * @updateDate      16/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check the profile filed - auto generated express login
     */
    private static function Auto_ExpressLogin($digits) {
        /* Variables    */
        global $DB,$CFG;
        $sql            = null;
        $rdo            = null;
        $expressInfo    = null;
        $expressId      = null;
        $info           = null;
        $pinCode        = null;
        $dbLog          = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT			u.id,
                                    concat(u.firstname,' ',u.lastname) as 'name',
                                    u.email,
                                    u.mailformat,
                                    u.lang,
                                    '' as 'express'
                     FROM			{user}			    u
                        JOIN		{user_info_data}	uid		ON 	uid.userid		= u.id
                                                                AND	uid.data		= 1
                        JOIN		{user_info_field}	uif		ON	uif.id			= uid.fieldid
                                                                AND	uif.datatype	= 'express'
                        LEFT JOIN	{user_express}	    uex		ON 	uex.userid 		= uid.userid
                     WHERE			u.deleted 	= 0
                        AND			uex.id 		IS NULL
                        AND			u.username 	!= 'guest'
                        AND			u.username 	NOT LIKE '%wsdossier%'
                        AND			u.username 	NOT LIKE '%wsdoskom%'
                        AND			u.email 	IS NOT NULL
                        AND			u.email 	<> ''
                     LIMIT 0,2000 ";

            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Pin Code */
                    $pinCode            = self::Generate_PinCode($digits);
                    $instance->express  = $pinCode;

                    /* Express Info */
                    $expressInfo = new stdClass();
                    $expressInfo->userid       = $instance->id;
                    $expressInfo->express      = self::GenerateHash($pinCode);
                    $expressInfo->remind       = self::generateSecurityPhrase($instance->id);
                    $expressInfo->token        = self::Generate_ExpressToken($expressInfo->remind);
                    $expressInfo->attempt      = 0;
                    $expressInfo->auto         = 1;
                    $expressInfo->sent         = 0;
                    $expressInfo->timecreated  = time();

                    /* Save Express Login   */
                    $expressId = $DB->insert_record('user_express',$expressInfo);
                    /* Send eMail   */
                    self::Send_ExpressLogin($instance,$expressId);
                }//for_user
            }//if_rdo
        }catch (Exception $ex) {
            /* Write Log */
            $dbLog  = userdate(time(),'%d.%m.%Y', 99, false). ' Express Login Cron Error - Auto Express (Insert) . ' . "\n";
            $dbLog .= 'Error: ' . $ex->getMessage() . "\n\n\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Express_Login_Cron.log");

            throw $ex;
        }//try_catch
    }//Auto_ExpressLogin


    /**
     * @param           $digits
     * @return          string
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate an internal pin code for the express login
     */
    private static function Generate_PinCode($digits) {
        /* Variables    */
        $pinCode    = null;

        try {
            $pinCode = str_shuffle(mt_rand());
            $pinCode = substr ($pinCode, 0, $digits);

            return $pinCode;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Generate_PinCode


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
        global $DB,$CFG;
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
            /* Write Log */
            $dbLog  = userdate(time(),'%d.%m.%Y', 99, false). ' Express Login Cron Error - Auto Express (Phrase) . ' . "\n";
            $dbLog .= 'Error: ' . $ex->getMessage() . "\n\n\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Express_Login_Cron.log");

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
     * @param           $expressId
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send by email the express login to the user
     */
    private static function Send_ExpressLogin($user,$expressId) {
        /* Variables    */
        global $DB,$CFG,$SITE;
        $subject        = null;
        $body           = null;
        $expressInfo    = null;
        $dbLog          = null;

        try {

            $subject  = (string)new lang_string('express_subject','local_express_login',$SITE->shortname,$user->lang);
            $body     = (string)new lang_string('express_body','local_express_login',$user,$user->lang) . "</br></br>";

            /* If eMail */
            if (email_to_user($user, $SITE->shortname, $subject, $body,$body)) {
                /* Update   */
                $expressInfo = new stdClass();
                $expressInfo->id    = $expressId;
                $expressInfo->sent  = 1;

                $DB->update_record('user_express',$expressInfo);

                /* Update Deliveries    */
                self::UpdateDeliveries_MicroLearning($user->id);
            }//if_email

        }catch (Exception $ex) {
            /* Write Log */
            $dbLog  = userdate(time(),'%d.%m.%Y', 99, false). ' Express Login Cron Error - Sent Notification . ' . "\n";
            $dbLog .= 'Error: ' . $ex->getMessage() . "\n\n\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Express_Login_Cron.log");

            throw $ex;
        }//try_Catch
    }//Send_ExpressLogin

    /**
     * @param           $userId
     * @throws          Exception
     *
     * @creationDate    16/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update all the deliveries set with the old express login to the status not sent.
     */
    private static function UpdateDeliveries_MicroLearning($userId) {
        /* Variables    */
        global $DB,$CFG;
        $dbMan          = null;
        $time           = null;

        try {
            /* First, it checks if the table exists */
            $dbMan = $DB->get_manager();
            if ($dbMan->table_exists('microlearning_deliveries')) {
                /* Search Criteria  */
                $params = array();
                $params['userid']   = $userId;
                $params['sent']     = 1;

                /* Execute  */
                $rdo = $DB->get_records('microlearning_deliveries',$params,'id','id,sent,message,timemodified');
                if ($rdo) {
                    /* Time modified    */
                    $time = time();

                    /* Update deliveries    */
                    foreach ($rdo as $instance) {
                        /* Update Delivery to not sent  */
                        $instance->sent = 0;
                        $instance->timemodified = $time;
                        $instance->message = get_string('micro_message','local_express_login');

                        /* Execute  */
                        $DB->update_record('microlearning_deliveries',$instance);
                    }//for_each_delivery
                }//if_Rdo
            }//if_table_exists
        }catch (Exception $ex) {
            /* Write Log */
            $dbLog  = userdate(time(),'%d.%m.%Y', 99, false). ' Express Login Cron Error - Auto Express (Micro) . ' . "\n";
            $dbLog .= 'Error: ' . $ex->getMessage() . "\n\n\n";
            error_log($dbLog, 3, $CFG->dataroot . "/Express_Login_Cron.log");

            throw $ex;
        }//try_catch
    }//UpdateDeliveries_MicroLearning

    /**
     * @throws          Exception
     *
     * @creationDate    15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean the express login generated automatically, if there is any error during the process.
     */
    private static function Clean_ExpressLogin() {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $DB->delete_records('user_express',array('auto' => 1));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Clean_ExpressLogin
}//Express_Cron
