<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 29/09/15
 * Time: 10:24
 * To change this template use File | Settings | File Templates.
 */

require_once('../../config.php');
require_once("$CFG->libdir/externallib.php");

class local_wstest_external extends external_api {
    public static function HelloWorld_parameters() {
        $strMessage     = new external_value(PARAM_TEXT,'Message');

        return new external_function_parameters(array('msg'=> $strMessage));
    }//wsValidateUserFeide_parameters


    public static function HelloWorld_returns() {
        $error      = new external_value(PARAM_INT,'Error. True/False');
        $msg_error  = new external_value(PARAM_TEXT,'Error Description');
        $myMessage  = new external_value(PARAM_TEXT,'My Message');


        $exist_return = new external_single_structure(array('error'         => $error,
            'msg_error'     => $msg_error,
            'MyMessage'     => $myMessage));

        return $exist_return;
    }

    public static function HelloWorld($message) {
        $result     = array();
        /* Parameter Validation */
        $params = self::validate_parameters(self::HelloWorld_parameters(), array('msg' => $message));

        /* Execute  */
        $result['error']        = 200;
        $result['msg_error']    = '';

        try {
            $result['MyMessage'] = 'I have received <strong>' . $message. '</strong>. And today it will be a good day.';

            return $result;
        }catch (Exception $ex) {
            if ($result['error']        == '200') {
                $result['error']        = 500;
                $result['msg_error']    = $ex->getMessage() . ' ' . $ex->getTraceAsString();
            }//if_error

            return $result;
        }//try_catch
    }
}//localwstest