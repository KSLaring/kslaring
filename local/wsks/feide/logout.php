<?php
/**
 * KS Læring Integration - Logout
 *
 * @package         local
 * @subpackage      wsks/feide
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/09/2015
 * @author          eFaktor     (fbv)
 */

require_once('../../../config.php');

/* PARAMS   */
$logout = optional_param('lg',0,PARAM_INT);

$PAGE->set_url('/local/wsks/feide/logout.php');
$PAGE->set_context(context_system::instance());

//$PAGE->set_pagetype('site-index');
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);




//if ($logout) {

//    require_logout();

//    /* Get End Point    */
//    $pluginInfo = get_config('local_wsks');
//    $redirect = $pluginInfo->feide_point . '/local/feide/logout.php';

//    redirect($redirect);

//    die;
//}else {
//    /* Guess USer -- Logout */
//    if (isguestuser($USER)) {
//        require_logout();
//    }//if_guestuser

/* Variables    */
$userRequest    = null;
$pluginInfo     = null;
$domain         = null;
$token          = null;
$service        = null;
$server         = null;
$client         = null;
$response       = null;
$userInfo       = null;
$errCode        = null;

try {
    /* Plugin Info      */
    $pluginInfo = get_config('local_wsks');

    /* User to Validate */
    $userRequest = array();
    $userRequest['id']     = '45400';

    /* Data to call Service */
    $domain     = $pluginInfo->feide_point;
    $token      = $pluginInfo->feide_token;
    $service    = 'wsLogOutFeide';//$pluginInfo->feide_service;

    /* Build end Point Service  */
    $server     = $domain . '/webservice/soap/server.php?wsdl=1&wstoken=' . $token;

    /* Call service */
    $client     = new SoapClient($server);
    $response   = $client->$service($userRequest);
}catch (Exception $ex) {
    print_r($ex);
    throw $ex;
}


    //if ($response['error'] == '200') {
    //    if ($response['valid']) {
    //        $errCode = FEIDE_NON_ERROR;
    //    }else {
    //        $errCode = FEIDE_NOT_VALID;
    //    }//if_valid

    //    $userInfo = $response['user'][0];
    //}else {
    //    $errCode = FEIDE_ERR_PROCESS;
    //}//if_no_error


    //echo $OUTPUT->header();


    //$urltogo = $CFG->wwwroot;

    //echo $OUTPUT->notification('POPOPOO', 'notifysuccess');
    //echo $OUTPUT->continue_button($urltogo);


    //echo $OUTPUT->footer();
//    die;
//}


//redirect($CFG->wwwroot);
//unset($SESSION->ksSource);

//if (isguestuser($USER)) {
//   require_logout();

//}
