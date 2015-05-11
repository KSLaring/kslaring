<?php
/**
 * Course Locations - View Location Detail
 *
 * @package             local
 * @subpackage          course_locations
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        06/05/2015
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
$url            = new moodle_url('/local/course_locations/view.php',array('id' => $locationId));
$return         = new moodle_url('/local/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage,'sort' => $sort));
$index_url      = new moodle_url('/local/course_locations/index.php');
$context        = context_system::instance();
$location       = null;

require_capability('local/course_locations:manage',$context);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname','local_course_locations'),$index_url);
$PAGE->navbar->add(get_string('lst_locations','local_course_locations'),$return);
$PAGE->navbar->add(get_string('view_location','local_course_locations'),$url);

/* Get Location */
$location   = CourseLocations::Get_LocationDetail($locationId);

/* Header   */
echo $OUTPUT->header();

echo CourseLocations::Print_LocationView($location,$page,$perpage,$sort);

/* Footer   */
echo $OUTPUT->footer();