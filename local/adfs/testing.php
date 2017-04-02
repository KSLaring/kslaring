<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 02/11/15
 * Time: 10:57
 * To change this template use File | Settings | File Templates.
 */

require_once('../../config.php');
require_once('adfslib.php');


//$urlKS = KS_ADFS::login_user_adfs(4);

//header('Location: ' . urldecode($urlKS));
//require_logout();

$directlink='course/index.php?categoryid=1';

/* User to Validate */

//try {
    /* User ADFS    */
//    $userRequest['username']   = 'esme';//$rdo->idnumber;
//    $userRequest['firstname']  = 'esme';//$rdo->firstname;
//    $userRequest['lastname']   = 'testing';//$rdo->lastname;
//    $userRequest['email']      = 'fbv@efaktor.no';//$rdo->email;
//    $userRequest['city']       = 'Lillehammer';//$rdo->city;
//    $userRequest['country']    = 'no';//$rdo->country;
//    $userRequest['lang']       = 'en';//$rdo->lang;

    /* Data to call Service */
//    $domain     = 'http://kommitdev.kursportal.net'; //$pluginInfo->adfs_point;
//    $token      = 'f1bc4ded0550da9f8e4a6172bea6f913'; //$pluginInfo->adfs_token;
//    $service    = 'wsUserADFS';//$pluginInfo->adfs_service;

    /* Build end Point Service  */
   // $server     = $domain . '/webservice/soap/server.php?wsdl=1&wstoken=' . $token;

    /* Call service */
    //$client     = new SoapClient($server);
    //$response   = $client->$service($userRequest);

    //if ($response['error'] == '200') {
    //    $urlRedirect =   urlencode('http://kommitdev.kursportal.net/local/wsks/adfs/autologin.php?id=30107');//$response['url'];
    //}else {
    //    $urlRedirect = urlencode('http://kommitdev.kursportal.net/local/wsks/adfs/error.php');//$response['url'];
    //}//if_no_error

    //header('Location: ' . urldecode($urlRedirect));
    //die;
//}catch (Exception $ex) {
  //  print_r($ex);
    //throw $ex;
//}

