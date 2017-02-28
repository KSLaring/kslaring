<?php
/**
 * Created by PhpStorm.
 * User: paqui
 * Date: 24/02/17
 * Time: 09:27
 */
require( '../../config.php' );
require_once('cron/statuscron.php');
require_once('lib/statuslib.php');
require_once('../fellesdata/lib/fellesdatalib.php');
require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/fellesdata_status/test.php');


/* Print Header */
echo $OUTPUT->header();

echo "Start STATUS .... " . "</br>";

$plugin     = get_config('local_fellesdata');
STATUS_CRON::test($plugin);

echo "Finish STATUS .... " . "</br>";

/* Print Footer */
echo $OUTPUT->footer();