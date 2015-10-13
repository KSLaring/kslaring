<?php
/**
 * Feide Integration WebService - External Lib
 *
 * @package         local
 * @subpackage      feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    21/09/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Implements all functions that the Web Service supply
 * We have to implement the next structure for each function:
 * - function xxxx
 * - function xxxx_parameters
 * - function xxxx_return
 */

require_once('../../config.php');
require_once ($CFG->libdir.'/externallib.php');
require_once ('feidelib.php');

class local_feide_external extends external_api {
    /***********************/
    /* wsValidateUserFeide */
    /***********************/

    /**
     * @return          external_function_parameters
     *
     * @creationDate    21/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Describe the parameters connected with the service
     * Array.
     *  - id.       User id. Internal form Feide site
     *  - ticket.   token connected with the user. Generate in Feide site.
     */
    public static function wsValidateUserFeide_parameters() {
        $userID     = new external_value(PARAM_TEXT,'User ID - From Feide');
        $userTicket = new external_value(PARAM_TEXT,'Ticket');


        /* USER FEIDE */
        $userFeide    = new external_single_structure(array('id'        =>  $userID,
                                                            'ticket'    =>  $userTicket));

        return new external_function_parameters(array('user'=> $userFeide));
    }//wsValidateUserFeide_parameters

    /**
     * @return          external_single_structure
     *
     * @creationDate    21/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Describe the response from the service.
     * Array.
     *  - error.        Kind of error.
     *  - msg_error.    Description error.
     *  - valid.        User valid or not.
     *  - user.         Array. User info.
     *                  - username.
     *                  - firstname
     *                  - lastname
     *                  - email
     *                  - city
     *                  - country
     *                  - lang
     */
    public static function wsValidateUserFeide_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msg_error  = new external_value(PARAM_TEXT,'Error Description');
        $valid      = new external_value(PARAM_INT,'Valid User');

        /* User Info    */
        $userName   = new external_value(PARAM_TEXT,'username. Personal number');
        $firstName  = new external_value(PARAM_TEXT,'First name');
        $lastName   = new external_value(PARAM_TEXT,'Last name');
        $eMail      = new external_value(PARAM_TEXT,'eMail');
        $city       = new external_value(PARAM_TEXT,'city');
        $country    = new external_value(PARAM_TEXT,'country');
        $language   = new external_value(PARAM_TEXT,'language');

        $user = new external_single_structure(array('username'  => $userName,
                                                    'firstname' => $firstName,
                                                    'lastname'  => $lastName,
                                                    'email'     => $eMail,
                                                    'city'      => $city,
                                                    'country'   => $country,
                                                    'lang'      => $language));

        $exist_return = new external_single_structure(array('error'         => $error,
                                                            'msg_error'     => $msg_error,
                                                            'valid'         => $valid,
                                                            'user'          => new external_multiple_structure($user)));

        return $exist_return;
    }//wsValidateUserFeide_returns

    /**
     * @param           $userFeide
     * @return          array
     *
     * @creationDate    21/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate user. Check if it's a valid user form feide.
     */
    public static function wsValidateUserFeide($userFeide) {
        /* Variables    */
        global $DB;
        $result     = array();
        $userID     = null;
        $userTicket = null;
        $valid      = 0;
        $user       = array();
        $userInfo   = null;
        /* Parameter Validation */
        $params = self::validate_parameters(self::wsValidateUserFeide_parameters(), array('user' => $userFeide));


        /* Execute  */
        $result['error']        = 200;
        $result['msg_error']    = '';
        $result['valid']        = 0;
        $result['user']         = null;

        try {
            /* Get Attributes   */
            $userId     = $userFeide['id'];
            $userTicket = $userFeide['ticket'];

            /* Validate User && Get User Info  */
            WS_FEIDE::ValidateUser($userId,$userTicket,$result);

            return $result;
        }catch (Exception $ex) {
            if ($result['error']        == '200') {
                $result['error']        = 500;
                $result['msg_error']    = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            return $result;
        }//try_catch
    }//wsValidateUserFeide

    /*****************/
    /* wsLogOutFeide */
    /*****************/
    public static function wsLogOutFeide_parameters() {
        $idNumber       = new external_value(PARAM_TEXT,'User ID - From Feide');


        /* USER FEIDE */
        $userFeide    = new external_single_structure(array('id' =>  $idNumber));

        return new external_function_parameters(array('user'=> $userFeide));
    }//wsLogOutFeide_parameters

    public static function wsLogOutFeide_returns() {
        return null;
    }//wsLogOutFeide_returns

    public static function wsLogOutFeide() {
        require_logout();

        $authplugin = get_auth_plugin('saml');
        $authplugin->logoutpage_hook();
    }//wsLogOutFeide

    /*****************************/
    /*****************************/
    /*****************************/

    /**
     * @static
     * @param           external_description $description
     * @param           mixed $params
     * @return          array|bool|mixed|null
     * @throws          moodle_exception
     * @throws          invalid_parameter_exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate parameters received
     */
    public static function validate_parameters(external_description $description, $params) {
        if ($description instanceof external_value) {
            if (is_array($params) or is_object($params)) {
                throw new invalid_parameter_exception('Scalar type expected, array or object received.');
            }

            if ($description->type == PARAM_BOOL) {
                // special case for PARAM_BOOL - we want true/false instead of the usual 1/0 - we can not be too strict here ;-)
                if (is_bool($params) or $params === 0 or $params === 1 or $params === '0' or $params === '1') {
                    return (bool)$params;
                }
            }
            $debuginfo = 'Invalid external api parameter: the value is "' . $params .
                '", the server was expecting "' . $description->type . '" type';
            return self::validate_param($params, $description->type, $description->allownull, $debuginfo);
        } else if ($description instanceof external_single_structure) {
            if (!is_array($params)) {
                throw new moodle_exception('generalexceptionmessage','error',null,'Only arrays accepted. The bad value is: \''
                    . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($description->keys as $key=>$subdesc) {
                if (!array_key_exists($key, $params)) {
                    if ($subdesc->required == VALUE_REQUIRED) {
                        throw new moodle_exception('generalexceptionmessage','error',null,'Missing required key in single structure: '. $key);
                    }
                    if ($subdesc->required == VALUE_DEFAULT) {
                        try {
                            $result[$key] = self::validate_parameters($subdesc, $subdesc->default);
                        } catch (invalid_parameter_exception $e) {
                            //we are only interested by exceptions returned by validate_param() and validate_parameters()
                            //(in order to build the path to the faulty attribut)
                            throw new moodle_exception('generalexceptionmessage','error',null,$key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                        }
                    }
                } else {
                    try {
                        $result[$key] = self::validate_parameters($subdesc, $params[$key]);
                    } catch (invalid_parameter_exception $e) {
                        //we are only interested by exceptions returned by validate_param() and validate_parameters()
                        //(in order to build the path to the faulty attribut)
                        throw new moodle_exception('generalexceptionmessage','error',null,$key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                    }
                }
                unset($params[$key]);
            }
            if (!empty($params)) {
                throw new moodle_exception('generalexceptionmessage','error',null,'Unexpected keys (' . implode(', ', array_keys($params)) . ') detected in parameter array.');
            }
            return $result;

        } else if ($description instanceof external_multiple_structure) {
            if (!is_array($params)) {
                throw new moodle_exception('generalexceptionmessage','error',null,'Only arrays accepted. The bad value is: \''
                    . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($params as $param) {
                $result[] = self::validate_parameters($description->content, $param);
            }
            return $result;

        } else {
            throw new moodle_exception('generalexceptionmessage','error',null,'Last Step');
        }
    }//validate_parameters

    /**
     * @static
     * @param           $param
     * @param           $type
     * @param           bool $allownull
     * @param           string $debuginfo
     * @return          mixed|null
     * @throws          moodle_exception
     *
     * @creationDate    20/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate the parameter
     */
    public static function validate_param($param, $type, $allownull=NULL_NOT_ALLOWED, $debuginfo='') {
        if (is_null($param)) {
            if ($allownull == NULL_ALLOWED) {
                return null;
            } else {
                throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
            }
        }
        if (is_array($param) or is_object($param)) {
            throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
        }

        $cleaned = clean_param($param, $type);

        if ($type == PARAM_FLOAT) {
            // Do not detect precision loss here.
            if (is_float($param) or is_int($param)) {
                // These always fit.
            } else if (!is_numeric($param) or !preg_match('/^[\+-]?[0-9]*\.?[0-9]*(e[-+]?[0-9]+)?$/i', (string)$param)) {
                throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
            }
        } else if ((string)$param !== (string)$cleaned) {
            // Conversion to string is usually lossless.
            throw new moodle_exception('generalexceptionmessage','error',null,$debuginfo . " PARAM: " . $param);
        }

        return $cleaned;
    }
}//local_feide_external