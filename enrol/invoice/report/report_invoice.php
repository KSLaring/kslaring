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

/* PARAMS   */
$course_id          = required_param('courseid',PARAM_INT);
$enrol_id           = required_param('id',PARAM_INT);
$format             = optional_param('format',null,PARAM_TEXT);

$course             = get_course($course_id);
$context_course     = context_course::instance($course_id);
$return_url         = new moodle_url('/course/view.php',array('id' => $course_id));
$url                = new moodle_url('/enrol/invoice/report/report_invoice.php',array('id' => $enrol_id,'courseid' => $course_id));

require_login($course);

/* Capability   */
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

/* Invoices List        */
$invoices_lst   = Invoices::Get_InvoicesUsers($course_id,$enrol_id);
/* Course Information   */
$course_info    = Invoices::Get_InfoCourse($course_id,$enrol_id);

/* Download             */
if ($format) {
    Invoices::Download_RequestCourses($invoices_lst,$course_info);
}//if_format

/* Print Header */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report_title','enrol_invoice'));

/* DISPLAY THE INVOICES  */
echo Invoices::Display_InvoicesCourse($invoices_lst,$course_info,$enrol_id);

/* Print Footer */
echo $OUTPUT->footer();

