<?php

/**
 * Report Competence Manager - Module
 *
 * Description
 *
 * @package         report
 * @subpackage      manager
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate      06/09/2012
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../config.php');
require_once( 'locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/gradelib.php');

require_login();

/* PARAMS */
$url = new moodle_url('/report/manager/index.php');
$return_url = new moodle_url('/report/manager/index.php');

/* Start the page */
$site_context = CONTEXT_SYSTEM::instance();
//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('company_report','report_manager'),$url);


/* ADD require_capability */
require_capability('report/manager:viewlevel4', $site_context);

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_loginhttps

/* Clean Cookies */
setcookie('parentLevelOne',0);
setcookie('parentLevelTwo',0);
setcookie('parentLevelThree',0);
setcookie('courseReport',0);
setcookie('outcomeReport',0);

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'company_report';
$show_roles = 1;
require('tabs.php');

/* Company Report   */
echo $OUTPUT->heading(get_string('company_report', 'report_manager'));

echo '<h5>' . get_string('underconstruction','report_manager') . '</h5>';

//$url_company    = new moodle_url('/report/manager/company_report/company_report.php');
//$url_employee   = new moodle_url($CFG->wwwroot.'/report/manager/employee_report/employee_report.php');
//echo '<p class="note">' . get_string('company_report_note', 'report_manager') . '</p>';
//echo '<ul class="unlist report-selection">' . "\n";
//    echo '<li class="first last">' . "\n";
//        echo '<a href="' . $url_employee . '">' . get_string('employee_report_link', 'report_manager') . '</a>';
//    echo '</li>' . "\n";
//    echo '<li class="first last">' . "\n";
//        echo '<a href="' . $url_company . '">' . get_string('company_report_link', 'report_manager') . '</a>';
//        echo '</li>' . "\n";
//echo '</ul>' . "\n" . "</br>";

/* Print Footer */
echo $OUTPUT->footer();