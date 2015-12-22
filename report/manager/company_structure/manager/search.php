<?php
/**
 * Report Competence Manager - Managers - Search process
 *
 * Description
 *
 * @package         report/manager
 * @subpackage      company_structure/manager
 * @copyright       2010 eFaktor
 *
 * @creationDate    21/12/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../../config.php');
require_once( 'managerslib.php');

/* PARAMS   */
$level      = required_param('level',PARAM_INT);
$company    = required_param('company',PARAM_INT);
$search     = required_param('search',PARAM_TEXT);
$selectorId = required_param('selectorid',PARAM_ALPHANUM);

$optSelector    = null;
$class          = null;
$json           = array();
$groupName      = null;
$groupData      = null;

$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/report/manager/company_structure/manager/search.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Validate if exits the selector   */
if (!isset($USER->manager_selectors[$selectorId])) {
    print_error('unknownuserselector');
}//if_userselector

/* Get the options connected with the selector  */
$optSelector = $USER->manager_selectors[$selectorId];

/* Get Class    */
$class = $optSelector['class'];

$results = Managers::$class($search,$company,$level);

foreach ($results as $groupName => $managers) {
    $groupData = array('name' => $groupName, 'users' => array());

    unset($managers[0]);

    foreach ($managers as $id=>$user) {
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
