<?php

/**
 * Report Competence Manager - Company structure.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/company_structure
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( '../managerlib.php');
require_once('company_structurelib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('company_structure_form.php');

/* PARAMS */
$url            = new moodle_url('/report/manager/company_structure/company_structure.php');
$return_url     = new moodle_url('/report/manager/index.php');
$redirect_url   = null;
$superUser      = false;
$myAccess       = null;

/* Start the page */
$site_context = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

/* ADD require_capability */
$superUser  = CompetenceManager::IsSuperUser($USER->id);
$myAccess   = CompetenceManager::Get_MyAccess($USER->id);

if (!$superUser) {
    require_capability('report/manager:edit', $site_context);
    $PAGE->navbar->add(get_string('report_manager','report_manager'),$return_url);
}else {
    $return_url = $url;
}//if_SuperUser
$PAGE->navbar->add(get_string('company_structure','report_manager'),$url);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_loginhttps

/* Show Form */
$form = new manager_company_structure_form(null,$myAccess);

if ($form->is_cancelled()) {
    unset($SESSION->onlyCompany);

    $_POST = array();
    redirect($return_url);
}else if ($data = $form->get_data()) {
    /* Get Action   */
    list($action, $level) = company_structure::Get_ActionLevel($data);

    $parent = array();
    for ($i = 0; $i <= $level; $i++) {
        $select      = COMPANY_STRUCTURE_LEVEL . $i;
        $parent[$i]  = $data->$select;
    }//for
    $SESSION->parents   = $parent;

    switch ($action) {
        case REPORT_MANAGER_COMPANY_CANCEL:
            $_POST = array();
            redirect($return_url);

            break;
        case REPORT_MANAGER_ADD_ITEM:
            $redirect_url    = new moodle_url('/report/manager/company_structure/add_company_structure.php',array('level'=>$level));

            break;
        case REPORT_MANAGER_RENAME_SELECTED:
            $redirect_url    = new moodle_url('/report/manager/company_structure/edit_company_structure.php',array('level'=>$level));

            break;
        case REPORT_MANAGER_DELETE_SELECTED:
            $select     = COMPANY_STRUCTURE_LEVEL . $level;
            $company_id = $data->$select;

            $redirect_url    = new moodle_url('/report/manager/company_structure/delete_company_structure.php',array('id'=>$company_id, 'level'=>$level));

            break;
        case REPORT_MANAGER_MANAGERS_SELECTED:
            $redirect_url    = new moodle_url('/report/manager/company_structure/manager/manager.php',array('le'=>$level));

            break;
        case REPORT_MANAGER_REPORTERS_SELECTED:
            $redirect_url    = new moodle_url('/report/manager/company_structure/reporter/reporter.php',array('le'=>$level));

            break;
        case REPORT_MANAGER_MOVED_SELECTED:
            $select     = COMPANY_STRUCTURE_LEVEL . $level;
            $company_id = $data->$select;

            $redirect_url    = new moodle_url('/report/manager/company_structure/move_company_structure.php',array('id'=>$company_id,'le' => $level));

            break;
        default:

            break;
    }//$action

    if (!is_null($redirect_url)) {
        redirect($redirect_url);
    }
}//form_cancelled

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'company_structure';
$show_roles = 1;
require('../tabs.php');

/* Print Title */
echo $OUTPUT->heading(get_string('company_structure', 'report_manager'));

$form->display();

/* Initialise Organization Structure    */
CompetenceManager::Init_Organization_Structure(COMPANY_STRUCTURE_LEVEL,REPORT_MANAGER_EMPLOYEE_LIST,null,$superUser,$myAccess,true);

/* Print Footer */
echo $OUTPUT->footer();