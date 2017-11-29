<?php
/**
 * Waiting List - Course search
 *
 * @package         enrol
 * @subpackage      waitinglist
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    14/11/2017
 * @author          efaktor     (fbv)
 *
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('../managerlib.php');
require_once( 'courserptlib.php');

global $PAGE,$USER,$OUTPUT,$CFG;

$search             = required_param('search',PARAM_TEXT);

$data           = null;
$json           = array();
$results        = null;
$context        = context_system::instance();
$url            = new moodle_url('/report/manager/course_report/coursesearch.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}
require_sesskey();

echo $OUTPUT->header();

$courses = course_report::Get_CoursesList($search);
$data = array('courses' => array());

if ($courses) {
    foreach ($courses as $id => $name) {
        // Info coruse
        $info            = new stdClass;
        $info->id        = $id;
        $info->name      = $name;

        // Add course
        $data['courses'][$info->name] = $info;
    }
}

// Encode and send
$json[] = $data;
echo json_encode(array('results' => $json));
