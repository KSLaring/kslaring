<?php
/**
 * Invoice Approval Users
 *
 * @package         enrol/waitinglist
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    28/09/2016
 * @author          efaktor     (fbv)
 *
 */
require('../../../config.php');
require_once('invoicefilterlib.php');
require_once('invoiceusers_form.php');
require_once('filter/lib.php');

require_login();

/* PARAMS   */
$courseId   = required_param('id',PARAM_INT);

$course     = $DB->get_record('course',array('id' => $courseId), '*', MUST_EXIST);
$context    = context_course::instance($courseId);
$url        = new moodle_url('/enrol/waitinglist/invoice/invoiceusers.php',array('id' => $courseId));
$returnUrl  = new moodle_url('/enrol/index.php',array('id' => $courseId));
$one            = 0;
$resourceNumber = '';

/* PAGE */
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managemethods', 'enrol_waitinglist'));
$PAGE->set_heading($course->fullname);

if (!isset($SESSION->bulk_users)) {
    $SESSION->bulk_users = array();
}

if (isset($SESSION->resource_number)) {
    $SESSION->resource_number = null;
}

if (isset($_COOKIE['level_1']) && $_COOKIE['level_1']) {
    $one = $_COOKIE['level_1'];
}
/* Get Industry Code connected  */
$industryCode = $DB->get_record('report_gen_companydata',array('id' => $one,'hierarchylevel' => 1),'industrycode');

/* Create the user filter   */
$user_filter = new waitinglist_filtering(null,$url,null);
if ($industryCode) {
    $user_filter->industry = $industryCode->industrycode;
}

/* Filter Users */
$lstUsers = InvoiceFilter::GetSelection_InvoiceFilter($user_filter);

/* Show Form */
$form = new invoice_users_form(null,array($courseId,$lstUsers));
if ($form->is_cancelled()) {
    unset($SESSION->bulk_users);
    unset($SESSION->resource_number);

    $_POST = array();
    redirect($returnUrl);
}else if ($data = $form->get_data()) {
    $SESSION->resource_number = InvoiceFilter::GetResourceNumber_ByUser($data->invoice_user);
    
    redirect($returnUrl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('find_resource_number','enrol_waitinglist'));
/* Add the filters  */
$user_filter->display_add();
$user_filter->display_active();
flush();

/* Form */
$form->display();

echo $OUTPUT->footer();