<?php
/**
 * Course Locations - View Location Detail
 *
 * @package             local
 * @subpackage          friadmin/course_locations
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        06/05/2015
 * @author              eFaktor     (fbv)
 *
 * @updateDate          16/06/2015
 * @author              eFaktor     (fbv)
 *
 * Description
 * Integrate nto Friadmin plugin
 *
 */
require_once('../../../config.php');
require_once('locationslib.php');

require_login();

/* PARAMS   */
$locationId     = optional_param('id',0,PARAM_INT);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT);        // how many per page
$sort           = optional_param('sort','ASC',PARAM_TEXT);
$url            = new moodle_url('/local/friadmin/course_locations/view.php',array('id' => $locationId));
$return         = new moodle_url('/local/friadmin/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage,'sort' => $sort));
$index_url      = new moodle_url('/local/friadmin/course_locations/index.php');
$context        = context_system::instance();
$location       = null;

/**
 * @updateDate  22/06/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Check if the user is super user
 */
if (!CourseLocations::CheckCapability_FriAdmin()) {
    print_error('nopermissions', 'error', '', 'block/frikomport:view');
}//if_superuser

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('plugin_course_locations','local_friadmin'),$index_url);
$PAGE->navbar->add(get_string('lst_locations','local_friadmin'),$return);
$PAGE->navbar->add(get_string('view_location','local_friadmin'),$url);

/* Get Location */
$location   = CourseLocations::Get_LocationDetail($locationId);

/* Header   */
echo $OUTPUT->header();

echo CourseLocations::Print_LocationView($location,$page,$perpage,$sort);

/* Footer   */
echo $OUTPUT->footer();