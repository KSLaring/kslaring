<?php
/**
 * Micro Learning - Selector Users - Search
 *
 * @package         local
 * @subpackage      microlearning
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      10/11/2015
 * @author          eFaktor     (fbv)
 *
 */

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('microuserslib.php');
require_once('filter/lib.php');

/* PARAMS   */
$search             = required_param('search',PARAM_RAW);
$selectorId         = required_param('selectorid',PARAM_ALPHANUM);
$courseId           = null;
$campaignId         = null;


$results            = null;
$infoUser           = null;
$usersSelector      = array();

$optSelector        = null;
$class              = null;
$json               = array();

$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/local/microlearning/users/search.php');

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

/* Find Users   */
/* Create the user filter   */
$user_filter = new microlearning_users_filtering(null,$url,null);
$courseId   = $user_filter->course_id = $optSelector['course'];
$campaignId = $optSelector['campaign'];

/* Get Users Selector   */
$results        = Micro_Users::$class($user_filter,$search,$courseId,$campaignId);

/* Get Data */
$data       = array('users' => array());
foreach ($results as $key=>$user) {
    $infoUser = new stdClass();
    $infoUser->id = $key;
    $infoUser->name = $user;

    $data['users'][] = $infoUser;
}//for_user

/* Encode and Send */
$json[] = $data;
echo json_encode(array('results' => $json));