<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Course Home Page  - Main Page
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
$start              = optional_param('start',1,PARAM_INT);
$course             = get_course($course_id);
$context            = context_course::instance($course_id);
$url                = new moodle_url('/local/course_page/home_page.php',array('id' => $course_id));
$str_edit_settings  = get_string("editcoursesettings");

if (isloggedin()) {
    if (has_capability('moodle/course:update', $context)) {
        require_login($course);
    }//if_permission
}//if_loggin

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($course->shortname . ': ' . get_string('home_page', 'local_course_page'));
$PAGE->set_pagelayout('coursehomepage');

// Clean cookies
setcookie('homepage_changed',0);
setcookie('ratings_changed',0);
setcookie('participant_changed',0);

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

    $form = new home_page_form(null,array('course' => $COURSE));
    if ($form->is_cancelled()) {
        $return = clone($PAGE->url);
        $return->param('sesskey', sesskey());
        $return->param('edit', 'off');

        redirect($return);
    }else if ($data = $form->get_data()) {
        /* Update Course    */
        course_page::update_course_home_page($data,$COURSE);

        $return = clone($PAGE->url);
        $return->param('sesskey', sesskey());
        $return->param('edit', 'off');

        redirect($return);
    }//if_get_data

    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}else {
    if ($start) {
        if (course_page::is_user_enrol($course->id,$USER->id)) {
            $url = new moodle_url('/course/view.php',array('id'=>$course->id,'start' =>1));
            redirect($url);
        }else {
            $format_options = course_page::get_format_fields($course->id);
            $format_options = course_page::get_available_seats_format_option($course->id, $format_options);
            $renderer = $PAGE->get_renderer('local_course_page');
            echo $renderer->display_home_page($course,$format_options);
            echo $renderer->footer();
        }
    }else {
        $format_options = course_page::get_format_fields($course->id);
        $format_options = course_page::get_available_seats_format_option($course->id, $format_options);
        $renderer = $PAGE->get_renderer('local_course_page');
        echo $renderer->display_home_page($course,$format_options);
        echo $renderer->footer();
    }
}//if_Edit
