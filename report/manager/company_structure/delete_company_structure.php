<?php

/**
 * Report Competence Manager - Company structure.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/comany_structure
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
require_once( '../managerlib.php');
require_once('company_structurelib.php');
require_once($CFG->libdir . '/adminlib.php');


/* Params */
$company_id     = required_param('id',PARAM_INT);
$level          = optional_param('level', 0, PARAM_INT);
$confirmed      = optional_param('confirm', false, PARAM_BOOL);
$url            = new moodle_url('/report/manager/company_structure/delete_company_structure.php',array('level' => $level,'id' => $company_id));
$returnUrl      = new moodle_url('/report/manager/company_structure/company_structure.php');
$parents        = $SESSION->parents;
$params         = array();

/* Start the page */
$site_context = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','report_manager'),new moodle_url('/report/manager/index.php'));
$PAGE->navbar->add(get_string('company_structure','report_manager'),$returnUrl);
$PAGE->navbar->add(get_string('delete_company_level','report_manager'));

/* ADD require_capability */
if (!CompetenceManager::IsSuperUser($USER->id)) {
    require_capability('report/manager:edit', $site_context);
}//if_SuperUser

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Return Url   */
$levelZero  = COMPANY_STRUCTURE_LEVEL . 0;
$levelOne   = COMPANY_STRUCTURE_LEVEL . 1;
$levelTwo   = COMPANY_STRUCTURE_LEVEL . 2;
$levelThree = COMPANY_STRUCTURE_LEVEL . 3;
if (isset($parents[0]) && $parents[0]) {
    $params[$levelZero] = $parents[0];
}
if (isset($parents[1]) && $parents[1]) {
    $params[$levelOne] = $parents[1];
}
if (isset($parents[2]) && $parents[2]) {
    $params[$levelTwo] = $parents[2];
}
if (isset($parents[3]) && $parents[3]) {
    $params[$levelThree] = $parents[3];
}
$returnUrl = new moodle_url('/report/manager/company_structure/company_structure.php',$params);

/* Print Header */
echo $OUTPUT->header();

if ($confirmed) {
    /* Remove */
    if (company_structure::Company_HasChildren($company_id)) {
        /* Not Remove */
        echo $OUTPUT->notification(get_string('error_deleting_company_structure','report_manager'), 'notifysuccess');
        echo $OUTPUT->continue_button($returnUrl);
    }else {
        /* Remove */
        if (company_structure::Delete_Company($company_id)) {
            echo $OUTPUT->notification(get_string('deleted_company_structure','report_manager'), 'notifysuccess');
            echo $OUTPUT->continue_button($returnUrl);
        }
    }//if_deleted
}else {
    /* First Confirm    */
    $strMessages = null;

    $company_name   = company_structure::Get_CompanyName($company_id);
    $confirm_url    = new moodle_url('/report/manager/company_structure/delete_company_structure.php',array('level' => $level,'id' => $company_id, 'confirm' => true));

    /* With/Without employees */
    if (company_structure::Company_HasEmployees($company_id)) {
        $strMessages = get_string('delete_company_structure_employees_are_you_sure','report_manager',$company_name);
    }else {
        $strMessages = get_string('delete_company_structure_are_you_sure','report_manager',$company_name);
    }

    echo $OUTPUT->confirm($strMessages,$confirm_url,$returnUrl);
}//if_confirm_delte_company

/* Print Footer */
echo $OUTPUT->footer();

