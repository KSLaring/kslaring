<?php
/**
 * Report Invoices Enrolment Method - Main Page
 *
 * @package         enrol/invoice
 * @subpackage      report
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      29/09/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once( '../../../config.php');
require_once('../invoicelib.php');
require_once($CFG->dirroot.'/lib/excellib.class.php');
require_once($CFG->dirroot . '/local/course_page/locallib.php');

// Params
$course_id          = required_param('courseid',PARAM_INT);
$enrol_id           = required_param('id',PARAM_INT);
$format             = optional_param('format',null,PARAM_TEXT);

$coordinator        = null;
$instructors        = null;
$notin              = null;
$course             = get_course($course_id);
$context_course     = context_course::instance($course_id);
$return_url         = new moodle_url('/course/view.php',array('id' => $course_id));
$url                = new moodle_url('/enrol/invoice/report/report_invoice.php',array('id' => $enrol_id,'courseid' => $course_id));

require_login($course);

// Capability
require_capability('enrol/invoice:manage',$context_course);

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($context_course);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

// Invoices list
$invoices_lst   = Invoices::get_invoices_users($course_id,$enrol_id);
// Course information
$course_info    = Invoices::get_info_course($course_id,$enrol_id);

// Get nstructors
$coordinator              = course_page::get_courses_manager($course_id);
if ($coordinator) {
    $notin = $coordinator->id;
}else {
    $notin = 0;
}
$course_info->instructors = course_page::get_courses_teachers($course_id,$notin);

// Download xls
if ($format) {
    Invoices::download_request_courses($invoices_lst,$course_info);
}//if_format

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report_title','enrol_invoice'));

// Invoices table
echo Invoices::display_invoices_course($invoices_lst,$course_info,$enrol_id);

// Footer
echo $OUTPUT->footer();

