<?php
/**
 * Local Block Courses Site  - Delete Course from Bock Courses Site
 *
 * @package         local
 * @subpackage      courses_site
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    29/05/2014
 * @author          efaktor     (fbv)
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('courses_site.php');

/* PARAMS */
$course_id = required_param('id',PARAM_INT);

$site_context = context_system::instance();
$url          = new moodle_url('/local/courses_site/delete_courses_site.php');

/* SET PAGE */
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('name','block_courses_site'));
$PAGE->navbar->add(get_string('title_del','local_courses_site'),$url);

/* Course Site Info */
$course_site = $DB->get_record('block_courses_site',array('course_id' => $course_id));
/* Course Info      */
$course = get_course($course_id);

/* Print Header */
echo $OUTPUT->header();

if (courses_site::courses_site_DeleteCourseFromBlockSite($course_site)) {
    /* OK */
    echo $OUTPUT->notification(get_string('delete_course','local_courses_site',$course->fullname), 'notifysuccess');
    echo '<br>';
    echo $OUTPUT->continue_button($CFG->wwwroot);
}else {
    /* KO */
    echo $OUTPUT->notification(get_string('error_delete','local_courses_site',$course->fullname), 'notifysuccess');
    echo '<br>';
    echo $OUTPUT->continue_button($CFG->wwwroot);
}//if_else

/* Print Footer */
echo $OUTPUT->footer();