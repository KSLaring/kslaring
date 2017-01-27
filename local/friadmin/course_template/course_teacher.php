<?php
/**
 * Course Template - Teachers
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    20/06/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Course create form template. Adding teachers
 */

require_once('../../../config.php');
require_once('lib/coursetemplatelib.php');
require_once('classes/ct_teacher_form.php');
require_once('../../../course/lib.php');

require_login();

/* PARAMS   */
$courseId       = required_param('id',PARAM_INT);
$courseTemplate = required_param('ct',PARAM_INT);
$addSearch      = optional_param('addselect_searchtext', '', PARAM_RAW);
$removeSearch   = optional_param('removeselect_searchtext', '', PARAM_RAW);
$contextCourse  = context_course::instance($courseId);
$url            = new moodle_url('/local/friadmin/course_template/course_teacher.php',array('id' => $courseId,'ct' => $courseTemplate));
$redirectUrl    = new moodle_url('/local/friadmin/course_template/course_noed_teacher.php',array('id' => $courseId,'ct' => $courseTemplate));
$returnUrl      = new moodle_url('/local/friadmin/course_template/course_template.php',array('id' => $courseId));

$course         = get_course($courseId);
$strTitle       = get_string('coursetemplate_title', 'local_friadmin');
$strSubTitle    = get_string('course_teachers', 'local_friadmin');
$instance       = null;

/* Check Permissions/Capability */
if (!has_capability('local/friadmin:view',context_system::instance())) {
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

/* FORM */
$form = new ct_enrolment_teachers_form(null,array($courseId,$courseTemplate,$addSearch,$removeSearch));
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if($data = $form->get_data()) {
    if (isset($data->submitbutton) && ($data->submitbutton)) {
        $_POST = array();
        redirect($redirectUrl);
    }else {
        /* Add Teachers     */
        if (!empty($data->add_sel)) {
            if (isset($data->addselect)) {
                CourseTemplate::assign_teacher($courseId,$data->addselect);
            }//if_addselect
        }//if_add

        /* Remove Teachers  */
        if (!empty($data->remove_sel)) {
            if (isset($data->removeselect)) {
                CourseTemplate::unassign_teacher($courseId,$data->removeselect);
            }//if_removeselect
        }//if_remove
    }//if_continues
}//if_form

/* Header   */
echo $OUTPUT->header();

echo $OUTPUT->heading($strSubTitle,3);

$form->display();

/* Initialise Selectors */
CourseTemplate::init_teachers_selectors($addSearch,$removeSearch,$courseId);

/* Footer   */
echo $OUTPUT->footer();