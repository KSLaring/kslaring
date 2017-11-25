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
 * Report Competence Manager - Edit Company structure.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/company_structure
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  11/09/2012
 * @author      eFaktor     (fbv)
 *
 * Edit a company into a specific level
 *
 */
global $CFG,$SESSION,$PAGE,$SITE,$OUTPUT,$USER;

require_once('../../../config.php');
require_once( '../managerlib.php');
require_once('company_structurelib.php');
require_once('edit_company_structure_form.php');
require_once($CFG->libdir . '/adminlib.php');

// Params
$level      = required_param('level', PARAM_INT);
$url        = new moodle_url('/report/manager/company_structure/edit_company_structure.php',array('level'=>$level));
$returnUrl  = new moodle_url('/report/manager/company_structure/company_structure.php');
$params     = array();
$site_context = context_system::instance();

// Page settings
$PAGE->https_required();
$PAGE->set_context($site_context);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','report_manager'),new moodle_url('/report/manager/index.php'));
$PAGE->navbar->add(get_string('company_structure','report_manager'),$returnUrl);
$PAGE->navbar->add(get_string('edit_company_level','report_manager'));

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
if (!CompetenceManager::is_super_user($USER->id)) {
    require_capability('report/manager:edit', $site_context);
}//if_SuperUser


if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

// Parents
$parents    = $SESSION->parents;

// Return url
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


// Form
$form = new manager_edit_company_structure_form(null,$level);

if ($form->is_cancelled()) {
    $_POST = array();
    redirect($returnUrl);
}else if($data = $form->get_data()) {
    $parents = $SESSION->parents;

    // Update
    company_structure::update_company_level($data,$level);

    $_POST = array();
    redirect($returnUrl);
}//if_else

$PAGE->verify_https_required();

// Header
echo $OUTPUT->header();

$form->display();

// Footer
echo $OUTPUT->footer();