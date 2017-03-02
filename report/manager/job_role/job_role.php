<?php

/**
 * Report Competence Manager - Job role.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( 'jobrolelib.php');
require_once('../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* PARAMS */
$url        = new moodle_url('/report/manager/job_role/job_role.php');
$return_url = new moodle_url('/report/manager/index.php');
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
    $PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return_url);
}else {
    $return_url = $url;
}//if_SuperUser
$PAGE->navbar->add(get_string('job_roles', 'report_manager'),$url);


if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'job_roles';
$show_roles = 1;
require('../tabs.php');


/* Get Job Role List */
$job_roles = job_role::JobRole_With_Outcomes($superUser,$myAccess);

/* Add Levels Links */
$url_edit = new moodle_url('/report/manager/job_role/add_job_role.php');
echo $OUTPUT->action_link($url_edit,get_string('add_job_role','report_manager'));

if (empty($job_roles)) {
    /* Print Title */
    echo $OUTPUT->heading(get_string('available_job_roles', 'report_manager'));
    echo '<p>' . get_string('no_job_roles_available', 'report_manager') . '</p>';
}else {
    /* Print Title */
    echo $OUTPUT->heading(get_string('job_roles', 'report_manager'));

    /* Add Levels Links */
    $url_edit = new moodle_url('/report/manager/job_role/add_job_role.php');
    $table = job_role::JobRoles_table($job_roles,$superUser);

    echo html_writer::table($table);
}//if_else

echo $OUTPUT->action_link($url_edit,get_string('add_job_role','report_manager'));

/* Print Footer */
echo $OUTPUT->footer();