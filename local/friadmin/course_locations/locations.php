<?php
/**
 * Course Locations - Locations List
 *
 * @package             local
 * @subpackage          friadmin/course_locations
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        27/04/2015
 * @author              eFaktor     (fbv)
 *
 * @updateDate          16/06/2015
 * @author              eFaktor     (fbv)
 *
 * Description
 * Integrate into Friadmin plugin
 *
 */
require_once('../../../config.php');
require_once('locationslib.php');

require_login();

/* PARAMS   */
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT);        // how many per page
$sort           = optional_param('sort','ASC',PARAM_TEXT);
$act            = optional_param('act',0,PARAM_INT);
$locationId     = optional_param('id',0,PARAM_INT);
$format         = optional_param('format', 0, PARAM_INT);
$mycounty       = optional_param('mycounty', '', PARAM_TEXT);
$colocationname = optional_param('colocationname', '', PARAM_TEXT);
$url            = new moodle_url('/local/friadmin/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage));
$return_url     = new moodle_url('/local/friadmin/course_locations/index.php');
$context        = context_system::instance();
$filter         = array();
$county         = null;
$locations      = null;
$totalLocations = 0;
$fieldSort      = null;

/**
 * @updateDate  22/06/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Check if the user is super user
 */
if (!has_capability('local/friadmin:course_locations_manage',$context)) {
    if (!local_friadmin_helper::CheckCapabilityFriAdmin()) {
        print_error('nopermissions', 'error', '', 'block/frikomport:view');
    }//if_superuser
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('plugin_course_locations','local_friadmin'));
$PAGE->navbar->add(get_string('lst_locations','local_friadmin'), $return_url);
$PAGE->requires->js('/local/friadmin/course_locations/js/locations.js');

/* Filter   */
$filter['county']   = $SESSION->county;
$filter['muni']     = $SESSION->muni;
$filter['activate'] = $SESSION->act;

/* Activate or Deactivate the Location  */
if ($act) {
    /* Change Status */
    CourseLocations::ChangeStatus_Location($locationId);
    $url            = new moodle_url('/local/friadmin/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage,'sort' => $sort,'act' => 0));
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

    // Download in excel
    if ($format == 1) {
        ob_end_clean();

        if ($SESSION->muni) {
            CourseLocations::download_all_locations_data($mycounty, $SESSION->muni);
        } else {
            CourseLocations::download_all_locations_data($mycounty, null);
        }

        die;
    }else if ($format == 2) {
        ob_end_clean();
        CourseLocations::download_one_location_data($mycounty, $colocationname);

        die;
    }

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
