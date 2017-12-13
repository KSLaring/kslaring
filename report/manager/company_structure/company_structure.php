<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
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

global $CFG,$SESSION,$PAGE,$SITE,$OUTPUT,$USER;

require_once('../../../config.php');
require_once( '../managerlib.php');
require_once('company_structurelib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('company_structure_form.php');

// Params
$url            = new moodle_url('/report/manager/company_structure/company_structure.php');
$return_url     = new moodle_url('/report/manager/index.php');
$redirect_url   = null;
$superUser      = false;
$myAccess       = null;
$site_context = context_system::instance();

// Page settigns
$PAGE->https_required();
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

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
$superUser  = CompetenceManager::is_super_user($USER->id);
$myAccess   = CompetenceManager::get_my_access($USER->id);

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

// Form
$form = new manager_company_structure_form(null,$myAccess);

if ($form->is_cancelled()) {
    unset($SESSION->onlyCompany);

    $_POST = array();
    redirect($return_url);
}else if ($data = $form->get_data()) {
    // Get action
    list($action, $level) = company_structure::get_action_level($data);

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

// Header
echo $OUTPUT->header();
// Tabs
$current_tab = 'company_structure';
$show_roles = 1;
require('../tabs.php');


echo $OUTPUT->heading(get_string('company_structure', 'report_manager'));

$form->display();

// Initialise Organization Structure
CompetenceManager::init_company_structure(COMPANY_STRUCTURE_LEVEL,REPORT_MANAGER_EMPLOYEE_LIST,$superUser,$myAccess,true);

/* Print Footer */
echo $OUTPUT->footer();