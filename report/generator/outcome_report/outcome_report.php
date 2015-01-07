<?php

/**
 * Report generator - Outcome report.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator/outcome_report/
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( 'outcomerptlib.php');
require_once('outcome_report_level_form.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* Params */
$url        = new moodle_url('/report/generator/outcome_report/outcome_report.php');
$return_url = new moodle_url('/report/generator/index.php');

$site_context = CONTEXT_SYSTEM::instance();
$site = get_site();

$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_generator','local_tracker'),$return_url);
$PAGE->navbar->add(get_string('outcome_report', 'report_generator'),$url);

/* ADD require_capability */
require_capability('report/generator:viewlevel3', $site_context);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

/* Clean Cookies */
setcookie('parentLevelOne',0);
setcookie('parentLevelTwo',0);
setcookie('parentLevelTree',0);
setcookie('courseReport',0);
setcookie('outcomeReport',0);

/* Start the page */
$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'outcome_report';
$show_roles = 1;
require('../tabs.php');

/* Print Title */
echo $OUTPUT->heading(get_string('outcome_report', 'report_generator'));

/* Report Levels Links  */
//outcome_report::GetLevelLink_ReportPage($current_tab,$site_context);

echo '<h5>' . get_string('underconstruction','report_generator') . '</h5>';

/* Print Fo>r */
echo $OUTPUT->footer();