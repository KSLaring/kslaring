<?php
/**
 * Invoice Enrolment Plugin - Unenrol Implementation
 *
 * @package         enrol
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    25/09/2014
 * @author          efaktor     (fbv)
 */

require('../../config.php');
require_once('lib.php');

$enrolid = required_param('enrolid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$instance = $DB->get_record('enrol', array('id'=>$enrolid, 'enrol'=>'invoice'), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login();
if (!is_enrolled($context)) {
    redirect(new moodle_url('/'));
}
require_login($course);

$plugin = enrol_get_plugin('invoice');

// Security defined inside following function.
if (!$plugin->get_unenrolself_link($instance)) {
    redirect(new moodle_url('/course/view.php', array('id'=>$course->id)));
}

$PAGE->set_url('/enrol/invoice/unenrolself.php', array('enrolid'=>$instance->id));
$PAGE->set_title($plugin->get_instance_name($instance));

if ($confirm and confirm_sesskey()) {
        enrol_invoice_plugin::Unerol_UserInvoiceInfo($instance->id,$USER->id);
        $plugin->unenrol_user($instance, $USER->id);
        redirect(new moodle_url('/index.php'));
}

echo $OUTPUT->header();

$yesurl = new moodle_url($PAGE->url, array('confirm'=>1, 'sesskey'=>sesskey()));
$nourl = new moodle_url('/course/view.php', array('id'=>$course->id));
$message = get_string('unenrolselfconfirm', 'enrol_invoice', format_string($course->fullname));
echo $OUTPUT->confirm($message, $yesurl, $nourl);

echo $OUTPUT->footer();

