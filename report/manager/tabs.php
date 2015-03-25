<?php

/**
 * Report Competence Manager - Tabs Page
 *
 * Description
 *
 * @package     report
 * @subpackage  manager
 * @copyright   2010    eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page
}

$inactive   = NULL;
$active_two = NULL;
$tabs       = array();
$top_row    = array();


/* Create Tabs */
$top_row[] = new tabobject('company_report',
                           new moodle_url($CFG->wwwroot. '/report/manager/index.php'),
                           get_string('company_report', 'report_manager'));

if (has_capability('report/manager:viewlevel3', $site_context)) {
    $top_row[] = new tabobject('outcome_report',
                                new moodle_url($CFG->wwwroot.'/report/manager/outcome_report/outcome_report.php'),
                                get_string('outcome_report','report_manager'));


    if ($current_tab == 'outcome_report_level') {
        $second_row = array();
        $second_row[] = new tabobject('levels',
                                      new moodle_url($CFG->wwwroot.'/report/manager/outcome_report/outcome_report.php'),
                                      get_string('select_report_levels','report_manager'));
        $current_tab = 'outcome_report';
    }

    $top_row[] = new tabobject('course_report',
                               new moodle_url($CFG->wwwroot.'/report/manager/course_report/course_report.php'),
                               get_string('course_report','report_manager'));

    if ($current_tab == 'course_report_level') {
        $second_row = array();
        $second_row[] = new tabobject('levels',
                                      new moodle_url($CFG->wwwroot.'/report/manager/course_report/course_report.php'),
                                      get_string('select_report_levels','report_manager'));
        $current_tab = 'course_report';

    }
}//if_level3

/* Only for admins */
if (has_capability('report/manager:edit', $site_context)) {
    $top_row[] = new tabobject('company_structure',
                               new moodle_url($CFG->wwwroot.'/report/manager/company_structure/company_structure.php'),
                               get_string('company_structure','report_manager'));

    $top_row[] = new tabobject('job_roles',
                               new moodle_url($CFG->wwwroot.'/report/manager/job_role/job_role.php'),
                               get_string('job_roles','report_manager'));

    $top_row[] = new tabobject('outcomes',
                               new moodle_url($CFG->wwwroot.'/report/manager/outcome/outcome.php'),
                               get_string('outcome','report_manager'));
}//if_admin

if (!empty($second_row)) {
    $tabs = array($top_row, $second_row);
} else {
    $tabs = array($top_row);
}

/* Print tabs */
print_tabs($tabs, $current_tab, $inactive, $active_two);
