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
require_once('company_structurelib.php');
require_once($CFG->libdir . '/adminlib.php');


/* Params */
$company_id  = required_param('id',PARAM_INT);
$level  = optional_param('level', 1, PARAM_INT);
$confirmed      = optional_param('confirm', false, PARAM_BOOL);
$return_url = new moodle_url('/report/generator/company_structure/company_structure.php',array('level'=>$level));
$url            = new moodle_url('/report/generator/company_structure/delete_company_structure.php',array('level' => $level,'id' => $company_id));
/* Start the page */
$site_context = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_generator','report_generator'),new moodle_url('/report/generator/index.php'));
$PAGE->navbar->add(get_string('company_structure','report_generator'),$return_url);
$PAGE->navbar->add(get_string('delete_company_level','report_generator'));

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

if ($confirmed) {
    /* Check If the company can be removed */
    if (company_structure::Company_HasEmployees($company_id)) {
        /* Not Remove */
        echo $OUTPUT->notification(get_string('error_deleting_company_employees','report_generator'), 'notifysuccess');
        echo $OUTPUT->continue_button($return_url);
    }else {
        /* Remove */
        if (company_structure::Company_HasChildren($company_id)) {
            /* Not Remove */
            echo $OUTPUT->notification(get_string('error_deleting_company_structure','report_generator'), 'notifysuccess');
            echo $OUTPUT->continue_button($return_url);
        }else {
            /* Remove */
            if (company_structure::Delete_Company($company_id)) {
                echo $OUTPUT->notification(get_string('deleted_company_structure','report_generator'), 'notifysuccess');
                echo $OUTPUT->continue_button($return_url);
            }
        }//if_deleted
    }//if_deleted
}else {
    /* First Confirm    */
    $company_name   = company_structure::Get_CompanyName($company_id);
    $confirm_url    = new moodle_url('/report/generator/company_structure/delete_company_structure.php',array('level' => $level,'id' => $company_id, 'confirm' => true));
    echo $OUTPUT->confirm(get_string('delete_company_structure_are_you_sure','report_generator',$company_name),$confirm_url,$return_url);
}//if_confirm_delte_company

/* Print Footer */
echo $OUTPUT->footer();

