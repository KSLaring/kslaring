<?php

/**
 * Report generator - Company structure.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator/comany_structure
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  11/09/2012
 * @author      eFaktor     (fbv)
 *
 * Delete a company into a specific level
 *
 */

require_once('../../../config.php');
require_once('../locallib.php');
require_once('company_structurelib.php');
require_once($CFG->libdir . '/adminlib.php');


/* Params */
$company_id  = required_param('id',PARAM_INT);
$level  = optional_param('level', 1, PARAM_INT);
$return_url = new moodle_url('/report/generator/company_structure/company_structure.php',array('level'=>$level));

/* Start the page */
$site_context = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url('/report/generator/company_structure/delete_company_structure.php');

/* ADD require_capability */
require_capability('report/generator:edit', $site_context);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

/* Check If the company can be removed */
if (report_generator_company_has_employees($company_id)) {
    /* Not Remove */
    echo $OUTPUT->notification(get_string('error_deleting_company_structure','report_generator'), 'notifysuccess');
    echo $OUTPUT->continue_button($return_url);
}else {
    /* Remove */
    if (report_generator_company_has_child($company_id)) {
        /* Not Remove */
        echo $OUTPUT->notification(get_string('error_deleting_company_structure','report_generator'), 'notifysuccess');
        echo $OUTPUT->continue_button($return_url);
    }else {
        /* Remove */
        if (report_generator_delete_company($company_id)) {
            echo $OUTPUT->notification(get_string('deleted_company_structure','report_generator'), 'notifysuccess');
            echo $OUTPUT->continue_button($return_url);
        }
    }//if_deleted
}//if_deleted

/* Print Footer */
echo $OUTPUT->footer();

