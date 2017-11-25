<?php

/**
 * Related Courses (local) - Main Page
 *
 * @package         local
 * @subpackage      related_courses
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      24/04/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once( '../../config.php');
require_once('locallib.php');
require_once('related_courses_form.php');

global $USER,$PAGE,$OUTPUT,$CFG;

// Params
$course_id          = optional_param('id',1,PARAM_INT);
$course             = get_course($course_id);
$context_course     = context_course::instance($course_id);
$return_url         = new moodle_url('/course/view.php',array('id' => $course_id));

// Checking access
// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}else {
    if (!has_capability('local/participants:manage',$context_course)) {
        require_login();
    }else {
        require_login($course);
    }//if_capabilities
}
require_login($course);
require_capability('moodle/course:update',$context_course);

// Start page
$url = new moodle_url('/local/related_courses/related_courses.php',array('id' => $course_id));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context_course);

// Get my related courses
$my_related = local_related_courses_getMyRelatedCourses($course_id);
// Get all available courses
$available_courses = local_related_courses_getAllAvailableCourses($course_id,$my_related);

// Form
$form           = new related_courses_form(null,array($course_id,$my_related,$available_courses));
if ($data = $form->get_data()) {
    if (!empty($data->addsel)) {
        local_related_courses_AddCourse($course_id,$data->add_fields);
    }//if_add
    if (!empty($data->removesel)) {
        local_related_courses_RemoveCourse($course_id,$data->sel_fields);
    }//if_remove

    // Get my related courses
    $my_related = local_related_courses_getMyRelatedCourses($course_id);
    // Get all available courses
    $available_courses = local_related_courses_getAllAvailableCourses($course_id,$my_related);

    $form           = new related_courses_form(null,array($course_id,$my_related,$available_courses));
}//if_form

// Footer form
$form_footer    = new related_courses_footer(null,$course_id);
if ($data = $form_footer->get_data()) {
    $_POST = array();
    redirect($return_url);
}//if_form_footer

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('title','local_related_courses'));

$form->display();
$form_footer->display();

// Footer
echo $OUTPUT->footer();