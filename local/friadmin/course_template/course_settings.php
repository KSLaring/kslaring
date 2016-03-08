<?php

/**
 * Course Template - Edit Course Settings
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/01/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Course create form template. General settings
 */

require_once('../../../config.php');
require_once('lib/coursetemplatelib.php');
require_once('classes/ct_settings_form.php');
require_once('../../../course/lib.php');

/* PARAMS   */
$courseId       = required_param('id',PARAM_INT);
$contextCourse  = CONTEXT_COURSE::instance($courseId);
$url            = new moodle_url('/local/friadmin/course_template/course_settings.php',array('id' => $courseId));
$returnUrl      = new moodle_url('/local/friadmin/courselist.php');
$enrolUrl       = new moodle_url('/local/friadmin/course_template/course_enrolment.php',array('id' => $courseId));

$strTitle       = get_string('coursetemplate_title', 'local_friadmin');
$strSubTitle    = get_string('coursetemplate_settings', 'local_friadmin');

$course         = get_course($courseId);
$course         = course_get_format($course)->get_course();
$category       = null;
$editorOpt      = null;
$fileOpt        = null;

require_login($course);

/* Check Permissions/Capability */
if (!has_capability('local/friadmin:view',CONTEXT_SYSTEM::instance())) {
    if (!local_friadmin_helper::CheckCapabilityFriAdmin()) {
        print_error('nopermissions', 'error', '', 'block/frikomport:view');
    }//if_superuser
}

$PAGE->set_url($url);
$PAGE->set_context($contextCourse);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_friadmin'));
$PAGE->navbar->add($strTitle);
$PAGE->navbar->add($strSubTitle);

/* Prepare Editor       */
$editorOpt  = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true,'context' => $contextCourse);
$course     = file_prepare_standard_editor($course, 'summary', $editorOpt, $contextCourse, 'course', 'summary', 0);

/* Prepare File Editor  */
$fileOpt = course_overviewfiles_options($course);
$fileOpt['subdirs']         = 0;
$fileOpt['maxfiles']        = 1;
$fileOpt['accepted_types']  = 'web_image';
if ($fileOpt) {
    file_prepare_standard_filemanager($course, 'overviewfiles', $fileOpt, $contextCourse, 'course', 'overviewfiles', 0);
}

$fakePermission = null;
if (!CourseTemplate::HasCorrectPermissions()) {
    /* Create a Fake Permission */
    $fakePermission = new stdClass();
    $fakePermission->id = CourseTemplate::Add_FakePermission_To_User();

    $SESSION->fakepermission = $fakePermission->id;
}//if_Has_not_permissions

/* Category Name */
$category = CourseTemplate::GetCategoryName($course->category);

/* Form */
try {
    $form = new ct_settings_form(null, array($course,$category,$editorOpt));
    if ($form->is_cancelled()) {
        if ($fakepermission) {
            CourseTemplate::Delete_FakePermission($fakepermission->id);
        }

        $_POST = array();
        redirect($returnUrl);
    }else if ($data = $form->get_data()) {
        /* Update Course */
        update_course($data, $editorOpt);

        /* Redirect Enrolment Method*/
        redirect($enrolUrl);
    }//if_form
}catch (Exception $ex) {
    if ($fakepermission) {
        CourseTemplate::Delete_FakePermission($fakepermission->id);
    }

    throw $ex;
}


/* Header   */
echo $OUTPUT->header();

echo $OUTPUT->heading($strTitle,2);
echo $OUTPUT->heading($strSubTitle,3);

$form->display();

/* Footer   */
echo $OUTPUT->footer();

