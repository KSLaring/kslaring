<?php
/**
 * Created by PhpStorm.
 * User: paqui
 * Date: 22/09/16
 * Time: 13:44
 */

require( '../../config.php' );
require_once('cron/wsssocron.php');

$url = new moodle_url('/local/doskom/Test.php');

$PAGE->https_required();

$PAGE->set_url($url);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->verify_https_required();
$PAGE->set_pagelayout('login');

/* Print Header */
echo $OUTPUT->header();

echo "START";

WSDOSKOM_Cron::cron();

echo "FINISH";
/* Print Footer */
echo $OUTPUT->footer();
