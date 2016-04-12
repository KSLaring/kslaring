<?php

/**
 * Report Competence Manager - Outcome report.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/outcome_report/
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    26/03/2015
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( 'outcomerptlib.php');
require_once( '../managerlib.php');
require_once('outcome_report_level_form.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* Params */
$url        = new moodle_url('/report/manager/outcome_report/outcome_report.php');
$return_url = new moodle_url('/report/manager/index.php');

$site_context = CONTEXT_SYSTEM::instance();
$site = get_site();

$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return_url);
$PAGE->navbar->add(get_string('outcome_report', 'report_manager'),$url);

unset($SESSION->parents);
unset($SESSION->selection);

/* ADD require_capability */
if (!CompetenceManager::IsReporter($USER->id)) {
    require_capability('report/manager:viewlevel3', $site_context);
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

/* Start the page */
$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'manager_reports';
$show_roles = 1;
require('../tabs.php');

/* Print Title */
echo $OUTPUT->heading(get_string('outcome_report', 'report_manager'));

/* Report Levels Links  */
outcome_report::CleanTemporary();
CompetenceManager::GetLevelLink_ReportPage('outcome_report',$site_context);

echo "</br>";
echo "<a href='" . $return_url ."' class='button_reports'>" . get_string('back') . "</a>";

/* Print Foot */
echo $OUTPUT->footer();