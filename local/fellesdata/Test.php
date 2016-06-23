<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 02/02/16
 * Time: 12:45
 * To change this template use File | Settings | File Templates.
 */

require( '../../config.php' );
require_once('cron/fellesdatacron.php');
require_once('lib/fellesdatalib.php');

require_login();

/* PARAMS */
$option = optional_param('op',0,PARAM_INT);

$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/fellesdata/Test.php');

/* Print Header */
echo $OUTPUT->header();


echo " TESTING FELLESDATA CRON " . "</br>";
echo " Import KS Data " . "</br>";
echo "Start ... " . "</br>";

try {
    if (!isset($SESSION->manual)) {
        $SESSION->manual = true;
    }

    if ($option) {
        //FELLESDATA_CRON::cron_manual(true,$option);
    }else {
        //FELLESDATA_CRON::cron(true);
    }
}catch (Exception $ex) {
    throw $ex;
}//try_catch



//try {
//    $response = "[" . $response . "]";
//    echo $response . "</br>";
//    $response = str_replace('{"change',',{"change',$response);
//    $response = str_replace('[,{','[{',$response);
//    echo "</br>---</br>";
 //   echo $response . "</br>";

//    $response = json_decode($response);

//    foreach ($response as $request) {
//        echo $request->changeType . "</br>";
//    }
//}catch (Exception $ex) {
//throw $ex;
//}
//echo $response->changeType . "</br>";
//echo $response->newRecord->ORG_ENHET_ID . "</br>";





/* Plugin Info      */
//$pluginInfo     = get_config('local_fellesdata');

/* Data to call Service */
//$domain     = $pluginInfo->ks_point;
//$token      = $pluginInfo->kss_token;

/* Build end Point Service  */
//$server     = $domain . '/webservice/soap/server.php?wsdl=1&wstoken=' . $token;

//echo $server;

echo "</br> Finish ... " . "</br>";

//echo "Testing WEB LOGIN - FELLESDATA";
//echo "</br>-----</br>";

//$today = getdate();
//echo "Today : "     . date('c',mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])) . "</br></br>";


//$yesterday = getdate(strtotime('-1 day'));
//echo "Yesterday : " . date('c',mktime(0,0,0,$yesterday['mon'],$yesterday['mday'],$yesterday['year'])) . "</br></br>";

//echo "Past : " . date('c',0);

/* Print Footer */
echo $OUTPUT->footer();