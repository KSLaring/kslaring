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
 * Report Competence Manager - Module
 *
 * Description
 *
 * @package         report
 * @subpackage      manager
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    01/02/2015
 * @author          eFaktor     (fbv)
 *
 */

require_once('../../config.php');
require_once( 'managerlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/gradelib.php');

require_login();

/* PARAMS */
$url        = new moodle_url('/report/manager/index.php');
$return_url = new moodle_url('/report/manager/index.php');
$IsReporter = false;

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

unset($SESSION->parents);
unset($SESSION->selection);

/* ADD require_capability */
$site_context = CONTEXT_SYSTEM::instance();
if (!is_siteadmin($USER->id)) {
    $IsReporter = CompetenceManager::IsReporter($USER->id);
    if (!$IsReporter) {
        require_capability('report/manager:viewlevel4', $site_context,$USER->id);
    }
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_loginhttps

$PAGE->verify_https_required();

/* Print Header */
echo $OUTPUT->header();
/* Print tabs at the top */
$current_tab = 'manager_reports';
$show_roles = 1;
require('tabs.php');

/* Competence Manager Reports   */
echo $OUTPUT->heading(get_string('reports_manager', 'report_manager'));

/* Reports  */
$urlUser        = new moodle_url('/report/manager/user_report/user_report.php');
$urlCompany     = new moodle_url('/report/manager/company_report/company_report.php');
$urlEmployee    = new moodle_url('/report/manager/employee_report/employee_report.php');
$courseReport   = new moodle_url('/report/manager/course_report/course_report.php');
$outcomeReport  = new moodle_url('/report/manager/outcome_report/outcome_report.php');

/* Add Reports */
echo '<p class="note">' . get_string('company_report_note', 'report_manager') . '</p>';

echo '<ul class="unlist report-selection">' . "\n";
    // User report
    echo '<li class="first last">' . "\n";
        echo '<a href="' . $urlUser . '">' . get_string('user_report_link', 'report_manager') . '</a>';
    echo '</li>' . "\n";

    // Employee Report
    echo '<li class="first last">' . "\n";
        echo '<a href="' . $urlEmployee . '">' . get_string('employee_report_link', 'report_manager') . '</a>';
    echo '</li>' . "\n";
    // Company Report
    echo '<li class="first last">' . "\n";
        echo '<a href="' . $urlCompany . '">' . get_string('company_report_link', 'report_manager') . '</a>';
    echo '</li>' . "\n";

    echo "</br>";

    // Course Report
    if (($IsReporter) || (has_capability('report/manager:viewlevel3', $site_context))) {
    echo '<li class="first last">' . "\n";
            echo '<a href="' . $courseReport . '">' . get_string('course_report', 'report_manager') . '</a>';
    echo '</li>' . "\n";
    // Outcome Report
    echo '<li class="first last">' . "\n";
            echo '<a href="' . $outcomeReport . '">' . get_string('outcome_report', 'report_manager') . '</a>';
        echo '</li>' . "\n";
    }//if_capability
echo '</ul>' . "\n" . "</br>";

/* Print Footer */
echo $OUTPUT->footer();