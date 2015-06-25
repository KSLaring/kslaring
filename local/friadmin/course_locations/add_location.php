<?php
/**
 * Course Locations - Add Location
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
 * Integrate into friadmin plugin
 *
 */

require_once('../../../config.php');
require_once('locationslib.php');
require_once('locations_form.php');

require_login();

/* PARAMS   */
$url            = new moodle_url('/local/friadmin/course_locations/add_location.php');
$return_url     = new moodle_url('/local/friadmin/course_locations/index.php');
$context        = context_system::instance();
$edit_options   = null;
$myCompetence   = null;
$strTitle       = null;

/**
 * @updateDate  22/06/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Check if the user is super user
 */
if (!has_capability('local/friadmin:course_locations_manage',$context)) {
    if (!CourseLocations::CheckCapability_FriAdmin()) {
        print_error('nopermissions', 'error', '', 'block/frikomport:view');
    }//if_superuser
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('plugin_course_locations','local_friadmin'),$return_url);
$PAGE->navbar->add(get_string('new_location','local_friadmin'),$url);

/* Get My Competence Locations  */
$myCompetence = CourseLocations::Get_MyCompetence($USER->id);

/* Editor Options */
$edit_options   = array('maxfiles' => 0, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true, 'context' => $context);
/* Prepare the editor   */
$location_info = new stdClass();
$location_info->description            = '';
$location_info->descriptionformat      = FORMAT_HTML;
$location_info = file_prepare_standard_editor($location_info, 'description', $edit_options,$context, 'local', 'course_locations',0);
/* Form */
$form = new add_location_form(null,array($myCompetence,$edit_options));
if ($form->is_cancelled()) {
    setcookie('parentCounty',0);
    setcookie('parentMunicipality',0);
    setcookie('parentActivate',1);

    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Get Data */
    $dataForm = (Array)$data;

    /* Get Editor Info  */
    $location_info->description_editor   = $dataForm['description_editor'];
    $location_info->description          = '';
    $location_info           = file_postupdate_standard_editor($location_info, 'description', $edit_options, $context, 'local', 'course_locations', 0);
    $dataForm['description'] = $location_info->description;

    /* Add New Location */
    CourseLocations::Add_NewLocation($dataForm,$USER->id);

    setcookie('parentCounty',0);
    setcookie('parentMunicipality',0);
    setcookie('parentActivate',1);

    $_POST = array();
    redirect($url);
}//if_cancel

/* Header   */
echo $OUTPUT->header();

$form->display();

/* Footer   */
echo $OUTPUT->footer();