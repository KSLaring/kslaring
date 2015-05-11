<?php
/**
 * Course Locations - Delete Location
 *
 * @package             local
 * @subpackage          course_locations
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        04/05/2015
 * @author              eFaktor     (fbv)
 *
 */
require_once('../../config.php');
require_once('locationslib.php');

require_login();

/* PARAMS   */
$locationId     = optional_param('id',0,PARAM_INT);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT);        // how many per page
$sort           = optional_param('sort','ASC',PARAM_TEXT);
$confirmed      = optional_param('confirm', false, PARAM_BOOL);
$url            = new moodle_url('/local/course_locations/delete_location.php');
$return         = new moodle_url('/local/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage, 'sort' => $sort));
$confirm_url    = null;
$index_url      = new moodle_url('/local/course_locations/index.php');
$context        = context_system::instance();

require_capability('local/course_locations:manage',$context);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname','local_course_locations'),$index_url);
$PAGE->navbar->add(get_string('lst_locations','local_course_locations'),$return);
$PAGE->navbar->add(get_string('del_location','local_course_locations'));


/* Header   */
echo $OUTPUT->header();

if ($confirmed) {
    /* Check if the location can be removed */
    if (CourseLocations::Has_CoursesConnected($locationId)) {
        /* Not Remove */
        echo $OUTPUT->notification(get_string('error_deleting_location','local_course_locations'), 'notifysuccess');
        echo $OUTPUT->continue_button($return);
    }else {
        /* Deleted  */
        if (CourseLocations::Delete_Location($locationId)) {
            echo $OUTPUT->notification(get_string('deleted_location','local_course_locations'), 'notifysuccess');
            echo $OUTPUT->continue_button($return);
        }
    }
}else {
    /* First Confirm    */
    $location   = CourseLocations::Get_LocationDetail($locationId);
    $a = new stdClass();
    $a->muni = $location->muni;
    $a->name = $location->name;
    $a->address = $location->street . ", " . $location->postcode . ' ' . $location->city;
    $confirm_url    = new moodle_url('/local/course_locations/delete_location.php',array('page' => $page, 'perpage' => $perpage, 'confirm' => true,'id' => $locationId));
    echo $OUTPUT->confirm(get_string('delete_location_are_you_sure','local_course_locations',$a),$confirm_url,$return);
}//if_confirmed

/* Footer   */
echo $OUTPUT->footer();