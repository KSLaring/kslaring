<?php
/**
 * Report Competence Manager - Course report.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/course_report
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 * @updateDate  17/03/2015
 * @author      rFaktor     (fbv)
 *
 */

require_once('../../../config.php');
require_once( 'courserptlib.php');
require_once( '../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

/* Params */
$url        = new moodle_url('/report/manager/course_report/course_report.php');
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
$PAGE->navbar->add(get_string('course_report', 'report_manager'),$url);

/* ADD require_capability */
require_capability('report/manager:viewlevel3', $site_context);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

/* Clean Cookies */
setcookie('parentLevelZero',0);
setcookie('parentLevelOne',0);
setcookie('parentLevelTwo',0);
setcookie('parentLevelThree',0);
setcookie('courseReport',0);
setcookie('outcomeReport',0);

/* Start the page */
$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'course_report';
$show_roles = 1;
require('../tabs.php');

/* Print Title */
echo $OUTPUT->heading(get_string('course_report', 'report_manager'));

/* Report Levels Links  */
CompetenceManager::GetLevelLink_ReportPage($current_tab,$site_context);

/* Print Fo>r */
echo $OUTPUT->footer();
