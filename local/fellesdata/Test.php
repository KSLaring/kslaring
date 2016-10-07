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
echo "Start ... " . "</br>";

try {
    if (!isset($SESSION->manual)) {
        $SESSION->manual = true;
    }

    if ($option) {
        FELLESDATA_CRON::cron_manual(true,$option);
    }else {
    }
}catch (Exception $ex) {
    throw $ex;
}//try_catch

echo "</br> Finish ... " . "</br>";


/* Print Footer */
echo $OUTPUT->footer();