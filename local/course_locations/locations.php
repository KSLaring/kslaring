<?php
/**
 * Course Locations - Locations List
 *
 * @package             local
 * @subpackage          course_locations
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        27/04/2015
 * @author              eFaktor     (fbv)
 *
 */

require_once('../../config.php');
require_once('locationslib.php');

require_login();

/* PARAMS   */
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT);        // how many per page
$sort           = optional_param('sort','ASC',PARAM_TEXT);
$act            = optional_param('act',0,PARAM_INT);
$locationId     = optional_param('id',0,PARAM_INT);
$url            = new moodle_url('/local/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage));
$return_url     = new moodle_url('/local/course_locations/index.php');
$context        = context_system::instance();
$filter         = array();
$county         = null;
$locations      = null;
$totalLocations = 0;
$fieldSort      = null;

require_capability('local/course_locations:manage',$context);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname','local_course_locations'));
$PAGE->navbar->add(get_string('lst_locations','local_course_locations'),$return_url);
$PAGE->requires->js('/local/course_locations/js/locations.js');

/* Filter   */
$filter['county']   = $SESSION->county;
$filter['muni']     = $SESSION->muni;
$filter['activate'] = $SESSION->act;

/* Activate or Deactivate the Location  */
if ($act) {
    /* Change Status */
    CourseLocations::ChangeStatus_Location($locationId);
    $url            = new moodle_url('/local/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage,'sort' => $sort,'act' => 0));
    redirect($url);
}else {
    /* Get Total Locations  */
    $totalLocations = CourseLocations::Get_TotalLocationsList($filter);

    if (($totalLocations <= $page*$perpage) && $page) {
        $page --;
    }

    /* Get the Sort Order       */
    if (isset($_COOKIE['dir'])) {
        $sort = $_COOKIE['dir'];
    }else {
        $sort = 'ASC';
    }//if_dir
    /* Get the Field by Sort    */
    if (isset($_COOKIE['field'])) {
        $fieldSort = $_COOKIE['field'];
    }else {
        $fieldSort = '';
    }//if_dir

    /* Get locations    */
    $locations = CourseLocations::Get_LocationsList($filter,$page*$perpage,$perpage,$sort,$fieldSort);
    /* Get County Name      */
    $county = CourseLocations::Get_CompanyLevelName($filter['county']);
    /* Get Table Locations  */
    $out = CourseLocations::Print_LocationsList($county,$locations,$totalLocations,$page,$perpage,$sort,$fieldSort);

    /* Header   */
    echo $OUTPUT->header();

    /* Print Locations Table    */
    echo $out;

    /* Footer   */
    echo $OUTPUT->footer();
}//if_Act


