<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('cron/manager_cron.php');
require_once('managerlib.php');
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
//Manager_Cron::cron();

//$orgnumber = 991939490;
//$brregdata = file_get_contents('http://w2.brreg.no/enhet/sok/detalj.jsp?orgnr=991939490');
//echo $brregdata;

/* Print Footer */
//$competence = new stdClass();
//$competence->competence = CompetenceManager::get_myreporter_competence(30404);

//$hierarchy = $competence->competence->hierarchytwo;

//echo "</br>";
//echo "Zero : " . implode(',',$hierarchy->zero) . "</br>";

//echo "Zero - One : " . implode(',',array_keys($hierarchy->one)) . "</br>";

//list($inZero,$inOne,$inTwo,$inThree) = CompetenceManager::extract_reporter_competence_by_level($competence,2,12,233);

//echo "Zero : " . $inZero . "</br>";
//echo "One : " . $inOne . "</br>";
//echo "Two: " . $inTwo . "</br>";
//echo "Three: " . $inThree . "</br>";

echo $OUTPUT->footer();