<?php
/**
 * Course Template - Teachers Search
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    20/06/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Course create form template. Adding teachers
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('lib/coursetemplatelib.php');


/* PARAMS   */
$course     = required_param('course',PARAM_INT);
$search     = required_param('search',PARAM_TEXT);
$selectorId = required_param('selectorid',PARAM_ALPHANUM);

$optSelector    = null;
$class          = null;
$json           = array();
$groupName      = null;
$groupData      = null;
$parents        = array();

$context        = context_system::instance();
$url            = new moodle_url('/local/friadmin/course_template/search.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Validate if exits the selector   */
if (!isset($USER->teacher_selectors[$selectorId])) {
    print_error('unknownuserselector');
}//if_userselector

/* Get the options connected with the selector  */
$optSelector = $USER->teacher_selectors[$selectorId];

/* Get Class    */
$class = $optSelector['class'];

$results = CourseTemplate::$class($course,$search);

foreach ($results as $groupName => $teachers) {
    $groupData = array('name' => $groupName, 'teachers' => array());

    unset($teachers[0]);

    foreach ($teachers as $id=>$user) {
        $output     = new stdClass;
        $output->id     = $id;
        $output->name   = $user;

        if (!empty($user->disabled)) {
            $output->disabled = true;
        }
        if (!empty($user->infobelow)) {
            $output->infobelow = $user->infobelow;
        }
        $groupData['teachers'][$output->name] = $output;
    }

    $json[] = $groupData;
}

echo json_encode(array('results' => $json));