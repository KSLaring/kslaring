<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 08/09/15
 * Time: 08:22
 * To change this template use File | Settings | File Templates.
 */

require_once('../../config.php');

global $PAGE,$CFG,$USER,$OUTPUT,$SITE;

$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_url('/local/microlearning/micro.php');

require_once('mode/calendar/calendarcronlib.php');
require_once('mode/activity/activitycronlib.php');

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

/* Print Header */
echo $OUTPUT->header();

echo "Start Cron Micro " . "</br>";
Calendar_ModeCron::cron();
Activity_ModeCron::cron();
echo "Finish Cron Micro ..." . "</br>";

/* Print Footer */
echo $OUTPUT->footer();