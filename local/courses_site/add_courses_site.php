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
global $CFG,$USER,$PAGE,$OUTPUT,$SITE;

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('courses_site.php');

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}

// Params
$site_context = context_system::instance();
$url          = new moodle_url('/local/courses_site/add_courses_site.php');

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('name','block_courses_site'));
$PAGE->navbar->add(get_string('title_add','local_courses_site'),$url);
$PAGE->requires->js('/local/courses_site/js/courses_site.js');

// Form
setcookie('parentCategory',0);
$form = new add_course_site_form(null);
if ($form->is_cancelled()) {
    setcookie('parentCategory',0);
    $_POST = array();
    redirect($CFG->wwwroot);
}else if ($data = $form->get_data()) {
    // New course
    courses_site::courses_site_AddCourseToBlockSite($data);

    redirect($url);
}//if_else_form

// Header
echo $OUTPUT->header();

echo $form->display();

// Footer
echo $OUTPUT->footer();
