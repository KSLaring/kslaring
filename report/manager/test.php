<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('cron/manager_cron.php');
require_login();

/* PARAMS */
$url        = new moodle_url('/report/manager/test.php');

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

echo "TEST " . "</br>";

// Create view profile - empty
Manager_Cron::cron();
/* Print Footer */
echo $OUTPUT->footer();