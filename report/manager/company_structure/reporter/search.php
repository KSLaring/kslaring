<?php
/**
 * Report Competence Manager - Reporters - Search Process
 *
 * Description
 *
 * @package         report/reporter
 * @subpackage      company_structure/reporter
 * @copyright       2010 eFaktor
 *
 * @creationDate    21/12/2015
 * @author          eFaktor     (fbv)
 *
 */

define('AJAX_SCRIPT', true);

require_once('../../../../config.php');
require_once( 'reporterlib.php');

/* PARAMS   */
$level      = required_param('level',PARAM_INT);
$levelZero  = required_param('levelzero',PARAM_INT);
$levelOne   = required_param('levelone',PARAM_INT);
$levelTwo   = required_param('leveltwo',PARAM_INT);
$levelThree = required_param('levelthree',PARAM_INT);
$search     = required_param('search',PARAM_TEXT);
$selectorId = required_param('selectorid',PARAM_ALPHANUM);

$optSelector    = null;
$class          = null;
$json           = array();
$groupName      = null;
$groupData      = null;
$parents        = array();

$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/report/manager/company_structure/reporter/search.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Get Companies by Level */
switch ($level) {
    case 0:
        $parents[0] = $levelZero;

        break;
    case 1:
        $parents[0] = $levelZero;
        $parents[1] = $levelOne;

        break;
    case 2:
        $parents[0] = $levelZero;
        $parents[1] = $levelOne;
        $parents[2] = $levelTwo;

        break;
    case 3:
        $parents[0] = $levelZero;
        $parents[1] = $levelOne;
        $parents[2] = $levelTwo;
        $parents[3] = $levelThree;

        break;
}//switch_level

/* Validate if exits the selector   */
if (!isset($USER->reporter_selectors[$selectorId])) {
    print_error('unknownuserselector');
}//if_userselector

/* Get the options connected with the selector  */
$optSelector = $USER->reporter_selectors[$selectorId];

/* Get Class    */
$class = $optSelector['class'];

$results = Reporters::$class($search,$parents,$level);

foreach ($results as $groupName => $reporters) {
    $groupData = array('name' => $groupName, 'users' => array());

    unset($reporters[0]);

    foreach ($reporters as $id=>$user) {
        $output     = new stdClass;
        $output->id     = $id;
        $output->name   = $user;

        if (!empty($user->disabled)) {
            $output->disabled = true;
        }
        if (!empty($user->infobelow)) {
            $output->infobelow = $user->infobelow;
        }
        $groupData['users'][] = $output;
    }

    $json[] = $groupData;
}

echo json_encode(array('results' => $json));