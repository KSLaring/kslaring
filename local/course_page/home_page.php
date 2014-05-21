<?php
/**
 * Course Home Page  - Main Page
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    28/04/2014
 * @author          eFaktor     (fbv)
 */
require_once('../../config.php');
require_once('locallib.php');

/* PARAMS   */
$course_id          = required_param('id',PARAM_INT);
$edit               = optional_param('edit', -1, PARAM_BOOL);
$show               = optional_param('show', 0, PARAM_INT);
$course             = get_course($course_id);
$category           = $DB->get_record('course_categories',array('id' => $course->category));
$context            = CONTEXT_COURSE::instance($course_id);
$url                = new moodle_url('/local/course_page/home_page.php',array('id' => $course_id));
$str_edit_settings  = get_string("editcoursesettings");

if (!has_capability('moodle/course:update', $context)) {
    require_login();
}else {
    require_login($course);
}//if_else_capability


$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');

$course_page = new course_page($course,$category);

if (!isset($USER->editing)) {
    $USER->editing = 0;
}//user_editing

if (($edit == 1) && confirm_sesskey()) {
    $USER->editing = 1;
    // Redirect to site root if Editing is toggled on frontpage
    if ($course->id == SITEID) {
        redirect($CFG->wwwroot .'/?redirect=0');
    }else {
        $url = clone($PAGE->url);
        $url->param('notifyeditingon',1);
        $url->param('show',1);
        redirect($url);
    }
}else if (($edit == 0) && confirm_sesskey()) {
    $USER->editing = 0;
    // Redirect to site root if Editing is toggled on frontpage
    if ($course->id == SITEID) {
        redirect($CFG->wwwroot .'/?redirect=0');
    }else {
        $url = clone($PAGE->url);
        $url->param('notifyeditingon',1);
        redirect($url);
    }
}//if_else_edit

if ($PAGE->user_allowed_editing()) {
    $buttons = $OUTPUT->edit_button($PAGE->url);
    $PAGE->set_button($buttons);
}//if_page_user_allowed_editind

if ($show) {
    require_capability('moodle/course:update', $context);

    $form = new home_page_form(null,array('course_page' => $course_page));
    if ($form->is_cancelled()) {
        $return = clone($PAGE->url);
        $return->param('sesskey', sesskey());
        $return->param('edit', 'off');

        redirect($return);
    }else if ($data = $form->get_data()) {
        /* Update Course    */
        $course_page->updateCourseHomePage($course_id,$data);
        update_course($data,$course_page->get_edit_options());

        $return = clone($PAGE->url);
        $return->param('sesskey', sesskey());
        $return->param('edit', 'off');

        redirect($return);
    }//if_get_data

    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}else {
    $renderer = $PAGE->get_renderer('local_course_page');
    echo $renderer->display_home_page($course);
    echo $renderer->footer();
}//if_Edit


