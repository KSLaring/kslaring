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
//Manager_Cron::cron();

//$orgnumber = 991939490;
//$brregdata = file_get_contents('http://w2.brreg.no/enhet/sok/detalj.jsp?orgnr=991939490');
//echo $brregdata;

/* Print Footer */
$str = " /1,/3,/5,/7,/8,/9,/14,/15,/16,/18,/19/20,/24,/25,/21/26/27/29,/21/26/27/30,/21/26/27/31,/21/26/27/32,/21/26/27/35,/21/37/38,/21/26/27/40,/21/26/27/41,/21/44/42,/21/37/43,/21/44/45,/46,/47,/21/37/49,/21/37/50,/21/37/51,/21/37/53,/21/37/57,/21/28/58,/21/55/56/61,/21/26/62,/21/63/64,/21/63/64/67,/21/37/68,/21/70/71,/21/72/73,/21/26/62/75,/21/72/76,/21/55/56/90,/1/92,/21/28/93,/21/44/94,/21/72/96,/21/37/97,/21/72/98,/19/20/99,/19/20/101,/21/28/108,/21/37/109,/21/37/110,/21/37/113,/21/37/114,/21/26/62/116,/21/26/62/123,/21/72/127,/21/55/56/60/129,/21/55/56/90/132,/21/55/56/90/133,/21/28/143,/21/145/146,/21/28/147,/21/148/149,/21/26/150,/21/44/152,/21/153/154,/21/72/156,/21/26/27/158,/19/159,/19/160,/21/70/163,/21/54/164,/21/54/165,/21/55/56/61/170,/21/55/56/61/171,/21/55/56/61/173,/1/92/175,/21/70/176,/21/63/177,/21/37/178,/21/28/179,/21/55/56/61/181,/21/72/182,/21/70/115/183,/21/28/184,/21/26/27/185,/21/44/186,/21/44/187,/21/44/188,/21/44/189,/21/145/190,/21/70/191,/21/70/192,/21/70/194,/21/70/195,/21/70/196,/21/";

$str = str_replace(',/',',',$str);
echo "FIRST REPLACEMENT: " . $str . "</br>";
$str = str_replace('/',',',$str);
echo "SECOND REPLACEMENT: " . $str . "</br>";
$first = substr($str,1,1);
echo "FIRST: " . $first . "</br>";
if ($first == ',') {
    $str = substr($str,2);
}
echo "FIRST CLEANED: " . $str . "</br>";

// Clean last element
$length = strlen($str);
$last = substr($str,$length-1,1);
echo "LAST : " . $last . "</br>";
if ($last == ',') {
    $str = substr($str,0,$length-1);
}
echo "LAST CLEANED: " . $str . "</br>";

echo $OUTPUT->footer();