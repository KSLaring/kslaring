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
 * Report Competence Manager - Job Role.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/job_role
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    06/11/2014
 * @author          eFaktor     (fbv)
 *
 * Add Job Role
 *
 * @updateDate      26/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Update Level Zero, One, Two and Three.
 * Add Industry Code
 *
 */

require_once('../../../config.php');
require_once( 'jobrolelib.php');
require_once('../managerlib.php');
require_once('add_job_role_form.php');
require_once($CFG->libdir . '/adminlib.php');


/* Params */
$return_url     = new moodle_url('/report/manager/job_role/job_role.php');
$url            = new moodle_url('/report/manager/job_role/add_job_role.php');
$return         = new moodle_url('/report/manager/index.php');
$superUser      = false;
$myAccess       = null;

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);


/* Info Super Users */
$superUser  = CompetenceManager::IsSuperUser($USER->id);
$myAccess   = CompetenceManager::Get_MyAccess($USER->id);

if (!$superUser) {
    require_capability('report/manager:edit', $site_context);
    $PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return);
}//if_SuperUser
$PAGE->navbar->add(get_string('job_roles', 'report_manager'),$return_url);
$PAGE->navbar->add(get_string('add_job_role', 'report_manager'));

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

/* Show Form */
$form = new manager_add_job_role_form(null,$myAccess);

if ($form->is_cancelled()) {

    $_POST = array();
    redirect($return_url);
}else if($data = $form->get_data()) {
    /* Insert New Job Role */
    job_role::Insert_JobRole($data);

    $_POST = array();
    redirect($return_url);
}//if_else

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();

$form->display();

/* Initialise Organization Structure    */
CompetenceManager::Init_Organization_Structure(COMPANY_STRUCTURE_LEVEL,null,null,$superUser,$myAccess,false);

/* Print Footer */
echo $OUTPUT->footer();