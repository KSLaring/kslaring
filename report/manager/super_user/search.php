<?php
/**
 * Report Competence Manager - Search Process - Users Selector
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/super_user
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    16/10/2015
 * @author          eFaktor     (fbv)
 *
 */

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('spuserlib.php');

/* PARAMS   */
$search             = required_param('search',PARAM_RAW);
$selectorId         = required_param('selectorid',PARAM_ALPHANUM);
$levelZero          = optional_param('levelZero',0,PARAM_INT);
$levelOne           = optional_param('levelOne',0,PARAM_INT);
$levelTwo           = optional_param('levelTwo',0,PARAM_INT);
$levelThree         = optional_param('levelThree',0,PARAM_TEXT);

$optSelector    = null;
$class          = null;
$json           = array();
$groupName      = null;
$groupData      = null;

$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/report/manager/super_user/search.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Validate if exits the selector   */
if (!isset($USER->userselectors[$selectorId])) {
   print_error('unknownuserselector');
}//if_userselector

/* Get the options connected with the selector  */
$optSelector = $USER->userselectors[$selectorId];

/* Get Class    */
$class = $optSelector['class'];

if ($levelThree) {
    $levelThree = str_replace('#',',',$levelThree);
}

/* Find Users   */
$results = SuperUser::$class($search,$levelZero,$levelOne,$levelTwo,$levelThree);

foreach ($results as $groupName => $users) {
    $groupData = array('name' => $groupName, 'users' => array());

    unset($users[0]);

    foreach ($users as $id=>$user) {
        $output     = new stdClass;
        $output->id     = $id;
        $output->name   = $user;

        if (!empty($user->disabled)) {
            $output->disabled = true;
        }
        if (!empty($user->infobelow)) {
            $output->infobelow = $user->infobelow;
        }
        $groupData['users'][$output->name] = $output;
    }

    $json[] = $groupData;
}

echo json_encode(array('results' => $json));
