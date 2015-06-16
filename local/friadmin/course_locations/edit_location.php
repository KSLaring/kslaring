<?php
/**
 * Course Locations - Edit Location
 *
 * @package             local
 * @subpackage          friadmin/course_locations
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        04/05/2015
 * @author              eFaktor     (fbv)
 *
 * @updateDate          16/06/2015
 * @author              eFaktor     (fbv)
 *
 * Description
 * Integrate into friadmin plugin
 *
 */

require_once('../../../config.php');
require_once('locationslib.php');
require_once('locations_form.php');

require_login();

/* PARAMS   */
$locationId     = optional_param('id',0,PARAM_INT);
$page           = optional_param('page', 0, PARAM_INT);
$perpage        = optional_param('perpage', 20, PARAM_INT);        // how many per page
$sort           = optional_param('sort','ASC',PARAM_TEXT);
$url            = new moodle_url('/local/friadmin/course_locations/edit_location.php',array('id' => $locationId));
$return         = new moodle_url('/local/friadmin/course_locations/locations.php',array('page' => $page, 'perpage' => $perpage, 'sort' => $sort));
$index_url      = new moodle_url('/local/friadmin/course_locations/index.php');
$context        = context_system::instance();
$location       = null;
$edit_options   = null;

require_capability('local/friadmin:course_locations_manage',$context);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('plugin_course_locations','local_friadmin'),$index_url);
$PAGE->navbar->add(get_string('lst_locations','local_friadmin'),$return);
$PAGE->navbar->add(get_string('edit_location','local_friadmin'),$url);

/* Get Location */
$location   = CourseLocations::Get_LocationDetail($locationId);

/* Editor Options */
$edit_options   = array('maxfiles' => 0, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true, 'context' => $context);
/* Prepare the editor   */
$location->descriptionformat      = FORMAT_HTML;
$location = file_prepare_standard_editor($location, 'description', $edit_options,$context, 'local', 'course_locations',0);

/* Form */
$form = new edit_location_form(null,array($location,$edit_options));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return);
}else if($data = $form->get_data()) {
    /* Get Data */
    $dataForm = (Array)$data;

    /* Get Editor Info  */
    $location->description_editor   = $dataForm['description_editor'];
    $location                       = file_postupdate_standard_editor($location, 'description', $edit_options, $context, 'local', 'course_locations', 0);
    $dataForm['description']        = $location->description;

    /* Save Location    */
    CourseLocations::Update_Location($dataForm);

    $_POST = array();
    redirect($return);
}//if_cancel

/* Header   */
echo $OUTPUT->header();

$form->display();

/* Footer   */
echo $OUTPUT->footer();