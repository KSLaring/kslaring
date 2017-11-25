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



/* Info Super Users */
$superUser  = CompetenceManager::is_super_user($USER->id);
$isReporter = CompetenceManager::is_reporter($USER->id);
if ($superUser) {
    if ($isReporter) {
        /* Create Tabs */
        $top_row[] = new tabobject('manager_reports',
                                   new moodle_url($CFG->wwwroot. '/report/manager/index.php'),
                                   get_string('reports_manager', 'report_manager'));
    }

    $top_row[] = new tabobject('company_structure',
                               new moodle_url($CFG->wwwroot.'/report/manager/company_structure/company_structure.php'),
                               get_string('company_structure','report_manager'));

    $top_row[] = new tabobject('job_roles',
                               new moodle_url($CFG->wwwroot.'/report/manager/job_role/job_role.php'),
                               get_string('job_roles','report_manager'));
}else {
    /* Create Tabs */
    $top_row[] = new tabobject('manager_reports',
                               new moodle_url($CFG->wwwroot. '/report/manager/index.php'),
                               get_string('reports_manager', 'report_manager'));

    /* Only for admins */
    if (has_capability('report/manager:manage', $site_context)) {
        $top_row[] = new tabobject('company_structure',
                                   new moodle_url($CFG->wwwroot.'/report/manager/company_structure/company_structure.php'),
                                   get_string('company_structure','report_manager'));

        $top_row[] = new tabobject('job_roles',
                                   new moodle_url($CFG->wwwroot.'/report/manager/job_role/job_role.php'),
                                   get_string('job_roles','report_manager'));

        $top_row[] = new tabobject('outcomes',
                                   new moodle_url($CFG->wwwroot.'/report/manager/outcome/outcome.php'),
                                   get_string('outcome','report_manager'));

        $top_row[] = new tabobject('spuser',
                                   new moodle_url($CFG->wwwroot.'/report/manager/super_user/spuser.php'),
                                   get_string('spuser','report_manager'));
    }//if_admin
}

if (!empty($second_row)) {
    $tabs = array($top_row, $second_row);
} else {
    $tabs = array($top_row);
}

/* Print tabs */
print_tabs($tabs, $current_tab, $inactive, $active_two);
