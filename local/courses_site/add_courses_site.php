<?php
/**
 * Local Block Courses Site  - Add Course to Bock Courses Site
 *
 * @package         local
 * @subpackage      courses_site
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    23/05/2014
 * @author          efaktor     (fbv)
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('courses_site.php');

/* PARAMS */
$site_context = context_system::instance();
$url          = new moodle_url('/local/courses_site/add_courses_site.php');

/* SET PAGE */
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('name','block_courses_site'));
$PAGE->navbar->add(get_string('title_add','local_courses_site'),$url);
$PAGE->requires->js('/local/courses_site/js/courses_site.js');

/* SET FORM */
setcookie('parentCategory',0);
$form = new add_course_site_form(null);
if ($form->is_cancelled()) {
    setcookie('parentCategory',0);
    $_POST = array();
    redirect($CFG->wwwroot);
}else if ($data = $form->get_data()) {
    /* Add a new Instance   */
    courses_site::courses_site_AddCourseToBlockSite($data);

    redirect($url);
}//if_else_form

/* Print Header */
echo $OUTPUT->header();

echo $form->display();

/* Print Footer */
echo $OUTPUT->footer();
