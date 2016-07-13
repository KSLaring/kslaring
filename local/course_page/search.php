<?php
/**
 * Course Home Page - Search Process - Manager Selector
 *
 * Description
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    05/11/2015
 * @author          eFaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once('locallib.php');

/* PARAMS   */
$search         = required_param('search',PARAM_RAW);
$courseId       = required_param('course',PARAM_INT);
$results        = array();
$lstManagers    = array();
$manager        = null;
$info           = null;
$context        = CONTEXT_SYSTEM::instance();
$url            = new moodle_url('/local/course_page/search.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

/* Check the correct access */
require_login();
require_sesskey();

echo $OUTPUT->header();

/* Find Managers    */
$results    = course_page::getCourseManager($search);
$assigned   = course_page::GetManagerAssigned($courseId);

/* Data to Send */
$data       = array('managers' => array(),'selected' => $assigned);
foreach ($results as $id => $manager) {
    /* Info Company */
    $info            = new stdClass;
    $info->id        = $id;
    $info->name      = $manager;

    /* Add Company*/
    $lstManagers[$id] = $info;
}

/* Encode and Send */
$data['managers'] = $lstManagers;
$json[] = $data;
echo json_encode(array('results' => $json));