<?php
/**
 * Friadmin Plugin - New Course
 *
 * @package             local
 * @subpackage          friadmin
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        24/06/2015
 * @author              eFaktor     (fbv)
 *
 */

require_once('../../config.php');
require_once('classes/newcourse_form.php');
require_login();

/* PARAMS   */
$url            = new moodle_url('/local/friadmin/newcourse.php');
$return_url     = new moodle_url('/my/index.php');
$context        = context_system::instance();
$category       = null;
$urlEdit        = null;

/* Set PAge */
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname','local_friadmin'));
$PAGE->navbar->add(get_string('naddcourse','local_friadmin'),$url);


/* Form     */
$form = new local_friadmin_newcourse_form(null);
if ($form->is_cancelled()) {
    $_POST = array();
    redirect($return_url);
}else if ($data = $form->get_data()) {
    /* Get Category Id  */
    $category = $data->category;

    /* Create Url   */
    $urlEdit = new moodle_url('/course/edit.php',array('category' => $category));
    redirect($urlEdit);
}//if_else

/* Header   */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('naddcourse','local_friadmin'));

$form->display();

/* Footer   */
echo $OUTPUT->footer();
