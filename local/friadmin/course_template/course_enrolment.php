<?php

/**
 * Course Template - Enrolment Methods
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/01/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Course create form template. Enrolment Methods
 */

require_once('../../../config.php');
require_once('lib/coursetemplatelib.php');
require_once('classes/ct_enrolment_form.php');
require_once('../../../course/lib.php');

require_login();

/* PARAMS   */
$courseId       = required_param('id',PARAM_INT);
$courseTemplate = required_param('ct',PARAM_INT);
$waitinglist    = optional_param('waitinglist',0,PARAM_INT);
$contextCourse  = context_course::instance($courseId);
$url            = new moodle_url('/local/friadmin/course_template/course_enrolment.php',array('id' => $courseId,'ct' => $courseTemplate));
$returnUrl      = new moodle_url('/local/friadmin/course_template/course_teacher.php',array('id' => $courseId,'ct' => $courseTemplate));

$course         = get_course($courseId);
$strTitle       = get_string('coursetemplate_title', 'local_friadmin');
$strSubTitle    = get_string('course_enrolment', 'local_friadmin');
$instance       = null;
$action         = null;

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

/* Form */
/**
 * @updateDate      27/06/2016
 * @author          eFaktor     (fbv)
 * 
 * Description
 * Different enrolment method based on course format
 */
switch ($course->format) {
    case 'classroom':
    case 'classroom_frikomport':
        if ($waitinglist) {
            /* Get Enrol Instance */
            $instance   = CourseTemplate::get_enrol_instance($courseId,$courseTemplate,$course->format);
            $form       = new ct_enrolment_settings_form(null,array($courseId,$waitinglist,$instance,$courseTemplate));
        }else {
            $form = new ct_enrolment_form(null,array($courseId,$courseTemplate));
        }

        break;
    case 'elearning_frikomport':
    case 'netcourse':
        /* Get Enrol Instance */
        $instance   = CourseTemplate::get_enrol_instance($courseId,$courseTemplate,$course->format);
        $form       = new ct_self_enrolment_settings_form(null,array($courseId,$instance,$courseTemplate));

        break;
}


if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if ($data = $form->get_data()) {
    switch ($course->format) {
        case 'classroom':
        case 'classroom_frikomport':
            if ($waitinglist) {
                if ($data->instanceid) {
                    /* Update   */
                    CourseTemplate::update_waiting_enrolment($data);
                }else {
                    /* New      */
                    CourseTemplate::create_waiting_enrolment($data);
                }
                redirect($returnUrl);
            }
            break;
        case 'elearning_frikomport':
        case 'netcourse':
            if ($data->instanceid) {
                $action = 'update';
            }else {
                $action = 'add';
            }
            /* Update /Create Instance */
            CourseTemplate::self_enrolment($data,$action);

            redirect($returnUrl);

            break;
    }//course_format
}

/* Header   */
echo $OUTPUT->header();

echo $OUTPUT->heading($strTitle,2);
echo $OUTPUT->heading($strSubTitle,3);

$form->display();

/* Footer   */
echo $OUTPUT->footer();


