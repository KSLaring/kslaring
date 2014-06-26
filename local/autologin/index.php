<?php

/**
 * Autologin page
 * User: Urs Hunkler
 * Date: 2011-07-22
 *
 * When the Autologin page is called with a valid course id Moodle creates a new user,
 * enrolls the user in the selected course and redirects to the course.
 * The user just opens the course without any interim pages.
 */

require('../../config.php');
require_once($CFG->dirroot.'/local/autologin/locallib.php');

/* PARAMS */
$course_id      = optional_param('courseid',0,PARAM_INT );
$category_id    = optional_param('categoryid',0,PARAM_INT);
$return_url     = new moodle_url('/local/autologin/logout.php');


$SESSION->autologin = new moodle_url('/local/autologin/logout.php');

$context = context_system::instance();

/* Page Settings */
$PAGE->set_url('/local/autologin/index.php' );
$PAGE->set_context($context);
$PAGE->set_pagelayout('login');

// check if course with courseid exisits
global $DB;

/* Category !== 0 */
if ($category_id > 0) {
    /* Create User */
    $user_new = local_autologin_CreateUser();

    if ($user_new) {
        if ($course_id > 1) {
            $error = local_autologin_EnrolUserCourse($user_new,$course_id);
        }//if_course_id
        if ($error) {
            echo $OUTPUT->header();
            echo $OUTPUT->notification($error . ' ' . $course_id, 'notifysuccess');
            echo $OUTPUT->continue_button($return_url);
            echo $OUTPUT->footer();
            die;
        }else {
            local_autologin_redirectToCategory($user_new,$category_id,$return_url);
        }//if_error

    }else {
        $PAGE->set_title($SITE->fullname);
        $PAGE->set_heading($SITE->fullname);

        echo $OUTPUT->header();
        echo $OUTPUT->error_text(get_string('createusererrortext', 'local_autologin') . '2');
        echo $OUTPUT->footer();
        die;
    }//if_else_autologin
}else if (($course_id < 2) && (!$DB->get_field('course','id',array('id' => $course_id)))) {
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('loginerrortext','local_autologin') . '3', 'notifysuccess');
    echo $OUTPUT->continue_button($return_url);
    echo $OUTPUT->footer();
    die;
}else {
    /* Create User */
    $user_new = local_autologin_CreateUser();

    if ($user_new) {
        /* Enroll User */
        $error = local_autologin_EnrolUserCourse($user_new,$course_id);
        if (!$error) {
            local_autologin_redirectToCourse($user_new,$course_id,$return_url);
        }else {
            $PAGE->set_title($SITE->fullname);
            $PAGE->set_heading($SITE->fullname);

            echo $OUTPUT->header();
            echo $OUTPUT->notification($error . '4', 'notifysuccess');
            echo $OUTPUT->continue_button($return_url);
            echo $OUTPUT->footer();
            die;
        }//if_else_error
    }else {
        $PAGE->set_title($SITE->fullname);
        $PAGE->set_heading($SITE->fullname);

        echo $OUTPUT->header();
        echo $OUTPUT->error_text(get_string('createusererrortext', 'local_autologin') .'5');
        echo $OUTPUT->footer();
        die;
    }//if_else_autologin
}//if_category_else_course_else
