<?php
/**
 * Express Login - Library
 *
 * @package         local
 * @subpackage      express_login
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      26/11/2014
 * @author          eFaktor     (fbv)
 *
 */

define('ERROR_LINK_NOT_VALID',1);

class Express_Login {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $users
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    16/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Activate Auto Generated Express Login
     */
    public static function Activate_AutoExpressLogin($users) {
        // Variables
        global $DB;
        $infoprofile    = null;
        $instance       = null;
        $params         = null;
        $myusers        = null;
        $user           = null;

        try {
            // Get profile id
            $infoprofile = $DB->get_record('user_info_field',array('datatype' => 'express'));
            if ($infoprofile) {
                // Create/Update the entry
                $myusers = explode(',',$users);
                if ($myusers) {
                    // Criteria
                    $params = array();
                    $params['fieldid'] = $infoprofile->id;
                    foreach ($myusers as $user) {
                        // First check if already exist
                        $params['userid'] = $user;
                        // Get record
                        $rdo = $DB->get_record('user_info_data',$params);

                        if ($rdo) {
                            // Update
                            $rdo->data = 1;

                            // Execute
                            $DB->update_record('user_info_data',$rdo);
                        }else {
                            //Create
                            $instance = new stdClass();
                            $instance->data     = 1;
                            $instance->fieldid  = $infoprofile->id;
                            $instance->userid   = $user;

                            // Execute
                            $DB->insert_record('user_info_data',$instance);
                        }//if_Else
                    }//for_rdo
                }//if_users
            }//if_infoProfile

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Activate_AutoExpressLogin

    /**
     * @return          bool
     *
     * @creationDate    26/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * The 'Express Login' is activated.
     */
    public static function IsActivate() {
        /* Variables    */
        $plugin     = get_config('local_express_login');

        if ($plugin->activate_express) {
            return true;
        }else {
            return false;
        }//if_else
    }//IsActivate

    /**
     * @param           $user_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    26/1/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user just create his/her express login
     */
    public static function Exists_ExpressLogin($user_id) {
        /* Variables    */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['userid'] = $user_id;

            /* Execute  */
            $rdo = $DB->get_record('user_express',$params,'id');
            if ($rdo) {
                return true;
            }else {
                return false;
            }
        }catch (Exception $ex) {
            return false;
        }//try_catch
    }//Exists_ExpressLogin

    /**
     * @param           $plugin_info
     * @param           $user
     * @return          bool
     *
     * @creationDate    06/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the Express Login has just expired and the user has to create a new one
     */
    public static function Force_NewExpressLogin($plugin_info,$user) {
        /* Variables    */
        $force      = false;
        $days       = null;
        $created    = null;

        try {
            if ($plugin_info->force_token) {
                /* Expired Days */
                $days       = $plugin_info->expiry_after/86400;
                $created    = self::Get_TimeCreatedExpress($user);

                if (time() > ($created+$days*60*60*24)) {
                    $force = true;
                    /* Update the Status of all his/her deliveries to 0 */
                }else {
                    $force = false;
                }
            }//if_force

            return $force;
        }catch (Exception $ex) {
            return false;
        }//try_catch
    }//Force_NewExpressLogin

    /**
     * @param           $pin_code
     * @param           $plugin_info
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the PIN code is valid or not. (good enough)
     */
    public static function CheckPinCode($pin_code,$plugin_info) {
        /* Variables    */
        $pin_valid      = true;
        $pin_err        = null;

        try {
            /* Check if there are too much identical digits    */
            if ($plugin_info->deny_identical) {
            list($pin_valid,$pin_err) = self::Check_IdenticalDigits($pin_code,$plugin_info->deny_identical);
            }else {
                $pin_valid = false;
            }

            /* Check Consecutive Digits */
            if (!$pin_valid) {
                list($pin_valid,$pin_err) = self::CheckPinCode_NotConsecutiveDigits($pin_code);
            }//if_CheckPinCode_NotConsecutiveDigits

            /* Check the percentage of each digit   */
            if (!$pin_valid) {
                list($pin_valid,$pin_err) = self::Check_PercentageDigits($pin_code);
            }//if_pin_valid

            return array($pin_valid,$pin_err);
        }catch (Exception $ex) {
            return false;
        }//try_catch
    }//CheckPinCode

    /**
     * @param           $data
     * @param           $exist
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    28/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate the Express Link for user
     */
    public static function Generate_ExpressLink($data,$exist=false) {

        try {
            if (!$exist) {
                return self::CreateExpressLogin_user($data);
            }else {
                return self::UpdateExpressLogin_User($data);
            }
        }catch (Exception $ex) {
            throw $ex;
            //return false;
        }//try_catch
    }//Generate_ExpressLogin

    /**
     * @param           $user_id
     * @return          moodle_url|null
     * @throws          Exception
     *
     * @creationDate    01/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate the express link to login connected to user
     */
    public static function Get_ExpressLink($user_id){

        try {
            return self::Express_Link($user_id);
        }catch (Exception $ex) {
            return false;
        }//try_catch
    }//Get_ExpressLink

    /**
     * @param           $link
     * @return          bool
     *
     * @creationDate    01/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check the link is valid
     */
    public static function Check_ExpressLink($link) {

        try {
            return self::IsLink_Valid($link);
        }catch (Exception $ex) {
            return false;
        }//try_cathc
    }//Check_ExpressLink

    /**
     * @param           $user
     * @param           $express
     * @return          bool
     *
     * @creationDate    02/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the current express login is valid
     */
    public static function ValidateExpressLogin_User($user,$express) {
        /* Variables    */
        $user_express   = null;
        $pin_code_valid = null;

        try {
            /* Get the current Express Login of the user to validate    */
            $user_express = self::GetExpressLogin_User($user);

            if ($user_express) {
                $pin_code_valid = password_verify($express, $user_express);
                if ($pin_code_valid) {
                    return true;
                }else {
                    return false;
                }
            }else {
                return false;
            }//if_user_express
        }catch (Exception $ex) {
            return false;
        }//try_catch
    }//ValidateExpressLogin_User

    /**
     * @param           $user
     * @param           $remind
     * @return          bool
     *
     * @creationDate    03/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate the new reminded of the user
     */
    public static function ValidateExpressRemind($user,$remind) {
        /* Variables    */
        $user_reminded  = null;
        $reminded_same  = null;

        try {
            /* Get the reminded    */
            $user_reminded = self::GetExpressRemind_User($user);

            if ($user_reminded) {
                $reminded_same = password_verify($remind, $user_reminded);
                if ($reminded_same) {
                    return false;
                }else {
                    return true;
                }//if_reminded_same
            }else {
                return false;
            }//if_else_user_reminded
        }catch (Exception $ex) {
            return false;
        }//try_catch
    }//ValidateExpressRemind

    /**
     * @param           $data
     * @return          bool
     *
     * @creationDate    03/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Regenerate the new Express Link
     */
    public static function ReGenerate_ExpressLink($data) {
        try {
            return self::Update_ExpressLink($data);
        }catch (Exception $ex) {
            return false;
        }//try_catch
    }//ReGenerate_ExpressLink

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $link
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    01/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the Express Link is valid
     */
    private static function IsLink_Valid($link) {
        /* Variables    */
        global $DB;
        $rdo    = null;

        try {
            /* Execute  */
            $rdo = $DB->get_record('user_express',array('token' => $link),'userid');
            if ($rdo) {
                return $rdo->userid;
            }else {
                return false;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsLink_Valid

    /**
     * @param           $user_id
     * @return          moodle_url|null
     * @throws          Exception
     *
     * @creationDate    01/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the express link to login
     */
    private static function Express_Link($user_id) {
        /* Variables    */
        global $DB;
        $express_link   = null;
        $rdo            = null;

        try {
            /* Search Criteria */
            $rdo = $DB->get_record('user_express',array('userid' => $user_id),'token');
            if ($rdo) {
                $express_link = new moodle_url('/local/express_login/loginExpress.php/' . $rdo->token);
            }

            return $express_link;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//ExpressLogin_Detail

    /**
     * @param           $pin_code
     * @param           $full
     * @return          array
     *
     * @creationDate    27/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if there are too much identical digits
     */
    private static function Check_IdenticalDigits($pin_code,$full) {
        /* Variables    */
        $pin_valid      = true;
        $pin_err        = null;

        if ($full) {
            list($pin_valid,$pin_err) = self::Check_FullIdenticalDigits($pin_code);
        }//if_full

        /* Check Half Identical Pattern         */
        if (!$pin_valid) {
            list($pin_valid,$pin_err) = self::Check_HalfIdenticalDigits($pin_code);
        }//if_Check_HalfIdenticalDigits


        /* Check Couple Identical Digits        */
        if (!$pin_valid) {
            list($pin_valid,$pin_err) = self::Check_CoupleIdenticalDigits($pin_code);
        }//if_Check_CoupleIdenticalDigits

        /* Check if the first half part is the same that the second one */
        if (!$pin_valid) {
            list($pin_valid,$pin_err) = self::Check_HalfPartsIdentical($pin_code);
        }//if_Check_HalfPartsIdentical

        return array($pin_valid,$pin_err);
    }//Check_IdenticalDigits

    /**
     * @param           $pin_code
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the digits of the pin are identical
     */
    private static function Check_FullIdenticalDigits($pin_code) {
        /* Variables    */
        $pin_len        = null;
        $pin_patter     = null;
        $patter_count   = null;
        $i              = 1;
        $identical      = false;
        $err_msg        = null;


        try {
            /* Length of the PIN code   */
            $pin_len = strlen($pin_code);

            /* Check if all the digits are identical    */
            do {
                /* Extract the digit    */
                $pin_patter     = substr($pin_code,$i-1,1);
                /* Count the digit      */
                $patter_count   = substr_count($pin_code,$pin_patter);
                if ($patter_count == $pin_len) {
                    $identical  = true;
                    $err_msg    = get_string('pin_identical_err','local_express_login');
                }//if_identical

                $i++;
            }while ((!$identical) && ($i<$pin_len));

            return array($identical,$err_msg);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Check_FullIdenticalDigits

    /**
     * @param           $pin_code
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the PIN code contains too much identical digits
     */
    private static function Check_HalfIdenticalDigits($pin_code) {
        /* Variables    */
        $pin_len            = null;
        $first_half         = null;
        $second_half        = null;
        $identical          = false;
        $identical_first    = null;
        $identical_second   = null;
        $err_msg            = null;

        try {
            /* Length of the PIN code   */
            $pin_len = strlen($pin_code);

            /* Split the PIN Code in two parts  */
            $first_half     = substr($pin_code,0,($pin_len/2));
            $second_half    = substr($pin_code,($pin_len/2));

            /* Check if all the digits for each part are identical      */
            /* Check if all digits for one part are identical           */
            list($identical_first,$err_msg)     = self::Check_FullIdenticalDigits($first_half);
            list($identical_second,$err_msg)    = self::Check_FullIdenticalDigits($second_half);
            if (($identical_first) && ($identical_second)) {
                /* All the digits for each parts are identical  */
                $identical  = true;
                $err_msg    = get_string('pin_code_err','local_express_login');
            }elseif (((!$identical_first) && ($identical_second))
                     ||
                     (($identical_first) && (!$identical_second))) {
                /* All the digits in one part are identical    */
                $identical  = true;
                $err_msg    = get_string('pin_code_err','local_express_login');
            }//if_is_valid

            return array($identical,$err_msg);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Check_HalfIdenticalDigits

    /**
     * @param           $pin_code
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the PIN code is like 223344.
     */
    private static function Check_CoupleIdenticalDigits($pin_code) {
        /* Variables    */
        $pin_len            = null;
        $num_couples        = null;
        $pin_patterns       = array();
        $identical_pat      = array();
        $couple_identical   = true;
        $i                  = 1;
        $start              = 0;
        $identical          = false;
        $err_msg            = null;

        try {
            /* Length of the PIN code   */
            $pin_len        = strlen($pin_code);
            /* Get how many couples they are in the string  */
            $num_couples    = ($pin_len/2);

            /* Get the patterns */
            do {
                $pin_patterns[$i] = substr($pin_code,$start,2);

                $i ++;
                $start += 2;
            }while ($i <= $num_couples);

            /* Check if each couple of digits are identical */
            foreach ($pin_patterns as $patter) {
                list($identical_pat[],$err_msg) = self::Check_FullIdenticalDigits($patter);
            }//for_each
            foreach ($identical_pat as $valid) {
                $couple_identical = $couple_identical && $valid;
            }//if_identical_pat

            if ($couple_identical) {
                $identical  = true;
                $err_msg    = get_string('pin_code_err','local_express_login');
            }//if_identical

            return array($identical,$err_msg);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Check_CoupleIdenticalDigits

    /**
     * @param           $pin_code
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the first half part of the PIN code is the same that the second half.
     */
    private static function Check_HalfPartsIdentical($pin_code) {
        /* Variables    */
        $pin_len            = null;
        $first_half         = null;
        $second_half        = null;
        $identical          = false;
        $err_msg            = null;

        try {
            /* Length of the PIN code   */
            $pin_len = strlen($pin_code);

            /* Split the PIN Code in two parts  */
            $first_half     = substr($pin_code,0,($pin_len/2));
            $second_half    = substr($pin_code,($pin_len/2));

            if ($first_half == $second_half) {
                $identical  = true;
                $err_msg    = get_string('pin_code_err','local_express_login');
            }//if_first_equal_to_second

            return array($identical,$err_msg);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Check_HalfPartsIdentical

    /**
     * @param           $pin_code
     * @return          array
     *
     * @creationDate    27/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check that the PIN code does not contain consecutive digits
     */
    private static function CheckPinCode_NotConsecutiveDigits($pin_code) {
        /* Variables    */
        $pin_valid      = true;
        $pin_err        = null;

        /* All digits --> Consecutive   */
        list($pin_valid,$pin_err) = self::Check_ConsecutiveDigits($pin_code);

        /* Both parts --> Consecutive   */
        //if (!$pin_valid) {
            //list($pin_valid,$pin_err) = self::Check_HalfConsecutiveDigits($pin_code);
        //}//if_Check_HalfConsecutiveDigits

        return array($pin_valid,$pin_err);
    }//CheckPinCode_NotConsecutiveDigits

    /**
     * @param           $pin_code
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if all the digits are consecutive
     */
    private static function Check_ConsecutiveDigits($pin_code) {
        /* Variables    */
        $pin_len            = null;
        $pin_patter         = null;
        $pin_patter_next    = null;
        $i                  = 1;
        $consecutive        = true;
        $err_msg            = null;

        try {
            /* Length of the PIN code   */
            $pin_len = strlen($pin_code);

            /* Check if the PIN code has consecutive digits */
            do {
                /* Extract the digits to compare */
                if ($i < $pin_len) {
                    $pin_patter         = (Int)substr($pin_code,$i-1,1);
                    $pin_patter_next    = (Int)substr($pin_code,$i,1);

                    if ($pin_patter_next == ($pin_patter + 1)) {
                        $consecutive = $consecutive && true;
                    }elseif ($pin_patter_next == ($pin_patter - 1)) {
                        $consecutive = $consecutive && true;
                    }else {
                        $consecutive = false;
                    }//if_else
                }//if_not_last_digit

                $i ++;
            }while ($i < $pin_len);

            if ($consecutive) {
                $err_msg = get_string('pin_consecutive_err','local_express_login');
            }//if_consecutive

            return array($consecutive,$err_msg);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Check_ConsecutiveDigits

    /**
     * @param           $pin_code
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * When you split the PIN code in two parts, check that any part are consecutive digits
     */
    private static function Check_HalfConsecutiveDigits($pin_code) {
        /* Variables    */
        $pin_len            = null;
        $first_half         = null;
        $second_half        = null;
        $identical          = false;
        $consecutive        = true;
        $identical_first    = null;
        $identical_second   = null;
        $err_msg            = null;

        try {
            /* Length of the PIN code   */
            $pin_len = strlen($pin_code);

            /* Split the PIN Code in two parts  */
            $first_half     = substr($pin_code,0,($pin_len/2));
            $second_half    = substr($pin_code,($pin_len/2));

            /* Check if each part contain consecutive digits    */
            list($identical_first,$err_msg)     = self::Check_ConsecutiveDigits($first_half);
            list($identical_second,$err_msg)    = self::Check_ConsecutiveDigits($second_half);
            if (($identical_first) && ($identical_second)) {
                /* All the digits for each parts are identical  */
                $identical  = true;
            }else {
                if ($second_half == ((Int)$first_half + 1)) {
                    $consecutive = $consecutive && true;
                }elseif ($second_half == ((Int)$first_half - 1)) {
                    $consecutive = $consecutive && true;
                }else {
                    $consecutive = false;
                }

                if ($consecutive) {
                    $identical  = $consecutive;
                    $err_msg    = get_string('pin_consecutive_err','local_express_login');
                }//if_consecutive
            }//if_identical_first_second

            return array($identical,$err_msg);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Check_HalfConsecutiveDigits

    /**
     * @param           $pin_code
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/11/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Check the percentage of each digit.
     */
    private static function Check_PercentageDigits($pin_code) {
        /* Variables    */
        $pin_len                = null;
        $pin_patter             = null;
        $patter_count           = null;
        $max_percentage         = null;
        $i                      = 1;
        $percentage_not_valid   = false;
        $err_msg                = null;


        try {
            /* Length of the PIN code   */
            $pin_len = strlen($pin_code);

            do {
                /* Extract the digit            */
                $pin_patter     = substr($pin_code,$i-1,1);
                /* Get the maximum percentage   */
                $max_percentage = ($pin_len/2);
                /* Count the digit              */
                $patter_count   = substr_count($pin_code,$pin_patter);

                if ($patter_count > $max_percentage) {
                    $percentage_not_valid = true;
                    $err_msg = get_string('pin_percentage_err','local_express_login',$pin_patter);
                }//if_max_percentage

                $i ++;
            }while ((!$percentage_not_valid) && ($i < $pin_len));

            return array($percentage_not_valid,$err_msg);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Check_PercentageDigits

    /**
     * @param           $value
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    28/11/2014
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
     * @creationDate    11/06/2015
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
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    28/11/2014
     * @author          eFaktor         (fbv)
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
     * @param               $user
     * @return              bool|mixed
     * @throws              Exception
     *
     * @creationDate        02/12/2014
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get the Express Login connected to the user
     */
    private static function GetExpressLogin_User($user) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('user_express',array('userid' => $user),'express');
            if ($rdo) {
                return $rdo->express;
            }else {
                return false;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetExpressLogin_User

    /**
     * @param           $user
     * @return          bool|mixed
     * @throws          Exception
     *
     * @creationDate    03/12/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the reminded connected to user
     */
    private static function GetExpressRemind_User($user) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('user_express',array('userid' => $user),'remind');
            if ($rdo) {
                return $rdo->remind;
            }else {
                return false;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetExpressRemind_User

    /**
     * @param           $data
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    02/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create a new Express Login for the user
     *
     * @updateDate      11/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Security Phrase will be something internal
     */
    private static function CreateExpressLogin_user($data) {
        /* Variables    */
        global $DB;
        $express_info   = null;

        try {
            /* Express Info */
            $express_info = new stdClass();
            $express_info->userid       = $data->id;
            $express_info->express      = self::GenerateHash($data->pin_code);
            $express_info->remind       = self::generateSecurityPhrase($data->id);
            $express_info->token        = self::Generate_ExpressToken($express_info->remind);
            $express_info->attempt      = 0;
            $express_info->timecreated  = time();

            /* Save Express Login   */
            $DB->insert_record('user_express',$express_info);

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CreateExpressLogin_user

    /**
     * @param           $data
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    02/12/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * update the current Express Login for the new one
     *
     * @updateDate      11/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Security phrase will be something internal
     */
    private static function UpdateExpressLogin_User($data) {
        /* Variables    */
        global $DB;
        $express_info   = null;

        try {
            /* First Get the ID */
            $rdo = $DB->get_record('user_express',array('userid' => $data->id),'id');
            /* Express Info */
            $express_info = new stdClass();
            $express_info->id           = $rdo->id;
            $express_info->userid       = $data->id;
            $express_info->express      = self::GenerateHash($data->pin_code);
            $express_info->remind       = self::generateSecurityPhrase($data->id);
            $express_info->token        = self::Generate_ExpressToken($express_info->remind);
            $express_info->attempt      = 0;
            $express_info->timemodified  = time();

            /* Save Express Login   */
            $DB->update_record('user_express',$express_info);

            /* Update the Microlearning deliveries with the old express login */
            self::UpdateDeliveries_MicroLearning($data->id);

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UpdateExpressLogin_User

    /**
     * @param           $data
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    03/12/2014
     * @author          eFkator     (fbv)
     *
     * Description
     * Update the Express Link
     *
     * @updateDate      11/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Security Phrase will be something internal
     */
    private static function Update_ExpressLink($data) {
        /* Variables    */
        global $DB;
        $express_info   = null;

        try {
            /* First Get the ID */
            $rdo = $DB->get_record('user_express',array('userid' => $data->id),'id');
            /* Express Info */
            $express_info = new stdClass();
            $express_info->id           = $rdo->id;
            $express_info->userid       = $data->id;
            $express_info->remind       = self::generateSecurityPhrase($data->id);
            $express_info->token        = self::Generate_ExpressToken($express_info->remind);
            $express_info->attempt      = 0;
            $express_info->timemodified  = time();

            /* Save Express Login   */
            $DB->update_record('user_express',$express_info);

            /* Update the Microlearning deliveries with the old express login */
            self::UpdateDeliveries_MicroLearning($data->id);

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Update_ExpressLink

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
        global $DB;
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
            throw $ex;
        }//try_catch
    }//UpdateDeliveries_MicroLearning

    /**
     * @param           $user
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    06/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return when the Express Login was created
     */
    private static function Get_TimeCreatedExpress($user) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('user_express',array('userid' => $user),'timecreated');
            return $rdo->timecreated;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TimeCreatedExpress

    /**
     * @param           $user_id
     * @throws          Exception
     */
    private static function UpdateStatusDeliveries_ExpressExpired($user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $user_id;

            /* SQL Instruction  */
            $sql = " UPDATE     {microlearning_deliveries}
                        SET     sent = 0
                     WHERE      userid = :user ";

            /* Execute  */
            $DB->execute($sql,$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UpdateStatusDeliveries_ExpressExpired
}//Express_Login
