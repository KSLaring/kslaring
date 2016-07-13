<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 20/11/15
 * Time: 13:01
 * To change this template use File | Settings | File Templates.
 */
require( '../../config.php' );
require_once('cron/expresscron.php');

require_login();


$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_url('/local/express_login/Test.php');

/* Print Header */
echo $OUTPUT->header();

echo "Test Express Login Cron - Ini " . "</br>";
Express_Cron::cron();
echo "Test Express Login Cron - Fin " . "</br>";

/* Print Footer */
echo $OUTPUT->footer();