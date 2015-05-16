<?php
/**
 * Course Locations
 *
 * @package         local
 * @subpackage      course_locations
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      27/04/2015
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../config.php');
require_once('locationslib.php');
require_once('locations_form.php');

require_login();

/* PARAMS   */
$url            = new moodle_url('/local/course_locations/index.php');
$url_view       = new moodle_url('/local/course_locations/locations.php');
$context        = context_system::instance();
$myCompetence   = null;

require_capability('local/course_locations:manage',$context);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname','local_course_locations'));
$PAGE->navbar->add(get_string('lst_locations','local_course_locations'),$url);

if (isset($SESSION->county)) {
    $SESSION->county = null;
}//county

if (isset($SESSION->muni)) {
    $SESSION->muni = null;
}

if (isset($SESSION->act)) {
    $SESSION->act = null;
}

/* Clean Cookies    */
setcookie('dir','ASC');
setcookie('field','');

/* Get My Competence Locations  */
$myCompetence = CourseLocations::Get_MyCompetence($USER->id);

/* Form */
$form = new locations_search_form(null,array($myCompetence));
if($data = $form->get_data()) {
    /* Get Data */
    $dataForm = (Array)$data;

    /* Get the filter - Search Criteria */
    $SESSION->county   = $dataForm[COURSE_LOCATION_COUNTY];
    $SESSION->muni     = $dataForm[COURSE_LOCATION_MUNICIPALITY];

    if (isset($dataForm['activate']) && ($dataForm['activate'])) {
        $SESSION->act = 1;
    }else {
        $SESSION->act = 0;
    }//if_checkbox_Activate

    redirect($url_view);
}//if_cancel

/* Header   */
echo $OUTPUT->header();

/* Table with locations */
echo $OUTPUT->heading(get_string('filter','local_course_locations'));

$form->display();

/* Footer   */
echo $OUTPUT->footer();