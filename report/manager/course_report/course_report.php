<?php

/**
 * Report Cometence Manager - Course report.
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
require_once( '../locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/gradelib.php');

/* Params */
require_login();
$return_url = new moodle_url('/report/manager/index.php');
$url        = new moodle_url('/report/manager/course_report/course_report.php');

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
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

$PAGE->verify_https_required();

/* Clean Cookies */
setcookie('parentLevelOne',0);
setcookie('parentLevelTwo',0);
setcookie('parentLevelTree',0);
setcookie('courseReport',0);
setcookie('outcomeReport',0);

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'course_report';
$show_roles = 1;
require('../tabs.php');

/* Print Title */
echo $OUTPUT->heading(get_string('course_report', 'report_manager'));

/* Report Levels Links  */
echo '<h5>' . get_string('underconstruction','report_manager') . '</h5>';
//report_manager_print_report_page($current_tab,$site_context);

/* Print Footer */
echo $OUTPUT->footer();
