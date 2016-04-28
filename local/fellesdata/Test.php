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


$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/fellesdata/Test.php');

/* Print Header */
echo $OUTPUT->header();


echo " TESTING FELLESDATA CRON " . "</br>";
echo " Import KS Data " . "</br>";
echo "Start ... " . "</br>";

//$response = '{"changeType": "add", "newRecord": {"ORG_ENHET_ID": 18, "ORG_NIVAA": 2, "ORG_NAVN": "Byrådsleders avdeling", "ORG_ENHET_OVER": 10}}
//             {"changeType": "add", "newRecord": {"ORG_ENHET_ID": 19, "ORG_NIVAA": 2, "ORG_NAVN": "Byrådsavd. for finans, eiendom og eierskap", "ORG_ENHET_OVER": 10}}
//             {"changeType": "add", "newRecord": {"ORG_ENHET_ID": 20, "ORG_NIVAA": 2, "ORG_NAVN": "Byrådsavd. for helse og omsorg", "ORG_ENHET_OVER": 10}}
//             {"changeType": "add", "newRecord": {"ORG_ENHET_ID": 21, "ORG_NIVAA": 2, "ORG_NAVN": "Byrådsavd. for barnehage, skole og idrett", "ORG_ENHET_OVER": 10}} ';

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

FELLESDATA_CRON::cron(false);

/* Plugin Info      */
//$pluginInfo     = get_config('local_fellesdata');

/* Data to call Service */
//$domain     = $pluginInfo->ks_point;
//$token      = $pluginInfo->kss_token;

/* Build end Point Service  */
//$server     = $domain . '/webservice/soap/server.php?wsdl=1&wstoken=' . $token;

//echo $server;

echo " Finish ... " . "</br>";

//echo "Testing WEB LOGIN - FELLESDATA";
//echo "</br>-----</br>";

//$today = getdate();
//echo "Today : "     . date('c',mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])) . "</br></br>";


//$yesterday = getdate(strtotime('-1 day'));
//echo "Yesterday : " . date('c',mktime(0,0,0,$yesterday['mon'],$yesterday['mday'],$yesterday['year'])) . "</br></br>";

//echo "Past : " . date('c',0);

/* Print Footer */
echo $OUTPUT->footer();