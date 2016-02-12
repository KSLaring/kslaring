<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paqui
 * Date: 28/01/16
 * Time: 10:23
 * To change this template use File | Settings | File Templates.
 */

require( '../../config.php' );
require_once('fellesdata/wsfellesdatalib.php');

require_login();


$PAGE->set_context(CONTEXT_SYSTEM::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/wsks/test.php');

/* Print Header */
echo $OUTPUT->header();

echo "TEST ING Function Web SErvices " . "</br>";

$top = array();
$top['company'] = 'Bergen';
$top['level'] = 0;
$top['notIn'] = 0;

$result = array();

WS_FELLESDATA::OrganizationStructureByTop($top,$result);

$response = $result['structure'];
echo "TOTAL : " . count($response). "</br>";
foreach ($response as $company) {
    echo "HI --> " . $company->id  . " - " . $company->name . " - Parent : " . $company->parent . "</br>";
}

echo "</br>-----</br>";

/* Print Footer */
echo $OUTPUT->footer();