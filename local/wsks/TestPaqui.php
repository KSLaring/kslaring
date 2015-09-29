<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 29/09/15
 * Time: 10:42
 * To change this template use File | Settings | File Templates.
 */

require_once('../../config.php');

$url = new moodle_url('/local/wsks/TestPaqui.php');
$PAGE->set_url($url);
$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_pagelayout('admin');


$token = '707b497d0b53021b64db79698afc26e5';
$domain = 'https://feidedev.weblogin.no';

$service = 'HelloWorld';

$server_url     = $domain . '/webservice/soap/server.php?wsdl=1&wstoken=' . $token;

echo $OUTPUT->header();

try {
    libxml_disable_entity_loader(false);
    $client = new SoapClient($server_url,array('exceptions' => 0, 'trace'=>true, "cache_wsdl" => WSDL_CACHE_NONE));

    $message = array();

    $resp = $client->$service('Perico de los Palotes');


    if ($resp['error'] == '200') {
        echo " OK -- > " . $resp['MyMessage'];
    }else {
        print($resp['error'] . '-' . $resp['msg_error']);
    }
}catch (Exception $ex) {
    throw $ex;
}//try_catch

echo $OUTPUT->footer();