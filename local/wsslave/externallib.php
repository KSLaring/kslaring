<?php
/**
 * Slaves Integration - Web Services External Lib
 *
 * @package         local
 * @subpackage      wsslave
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/11/2016
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
require_once ('lib/wsslavelib.php');

class local_wsslave_external extends external_api {
    /************************************/
    /* wsUpdateMainService_parameters   */
    /************************************/

    /**
     * @return          external_function_parameters
     *
     * @creationDate    07/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Input parameters connected with service
     */
    public static function wsUpdateMainService_parameters() {
        /* Service Info */
        $name   = new external_value(PARAM_TEXT,'Name of the service to update');
        $parent = new external_value(PARAM_TEXT, 'Main system');
        $token  = new external_value(PARAM_TEXT,'New token connected with the service');

        /* Service */
        $service = new external_single_structure(array('name'  => $name,
                                                       'main'  => $parent,
                                                       'token' => $token));

        return new external_function_parameters(array('service'=> $service));
    }//wsUpdateMainService_parameters

    /************************************/
    /* wsUpdateMainService_return       */
    /************************************/

    /**
     * @return          external_single_structure
     *
     * @creationDate    07/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Web Service response
     */
    public static function wsUpdateMainService_return() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msg_error  = new external_value(PARAM_TEXT,'Error Description');
        $valid      = new external_value(PARAM_INT,'Updated or not');


        $exist_return = new external_single_structure(array('error'         => $error,
                                                            'msg_error'     => $msg_error,
                                                            'updated'       => $valid));

        return $exist_return;
    }//wsUpdateMainService_return

    /***********************/
    /* wsUpdateMainService */
    /***********************/

    /**
     * @param           $service
     * 
     * @return          array
     * 
     * @throws          Exception
     * @throws          invalid_parameter_exception
     * @throws          moodle_exception
     * 
     * @creationDate    07/11/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Update the service  with new token
     */
    public static function wsUpdateMainService($service) {
        /* Variables    */
        $result     = array();

        /* Parameter Validation */
        $params = self::validate_parameters(self::wsUpdateMainService_parameters(), array('service' => $service));

        /* Execute  */
        $result['error']        = 200;
        $result['msg_error']    = '';
        $result['updated']      = 1;

        try {
            /* Updated Service */
            WS_SLAVE::Process_UpdateMainService($service,$result);

            return $result;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//wsUpdateMainService

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
}//local_wsslave_external