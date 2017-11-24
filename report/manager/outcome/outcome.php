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
 * Report Competence Manager - Outcome.
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

global $CFG,$SESSION,$PAGE,$USER,$SITE,$OUTPUT;

require_once('../../../config.php');
require_once( 'outcomelib.php');
require_once($CFG->libdir . '/adminlib.php');

// Params
$url        = new moodle_url('/report/manager/outcome/outcome.php');
$return_url = new moodle_url('/report/manager/index.php');
$site_context = CONTEXT_SYSTEM::instance();

// Page settings
$PAGE->https_required();
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($site_context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return_url);
$PAGE->navbar->add(get_string('outcome', 'report_manager'),$url);

unset($SESSION->parents);

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}
if (!has_capability('report/manager:edit', $site_context)) {
    print_error('nopermissions', 'error', '', 'report/manager:edit');
}

if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security

$PAGE->verify_https_required();

// Header
echo $OUTPUT->header();
// Tabs
$current_tab = 'outcomes';
$show_roles = 1;
require('../tabs.php');

// Outcome list
$outcome_list = outcome::Outcomes_With_JobRoles();

if (empty($outcome_list)) {
    echo $OUTPUT->heading(get_string('available_outcomes', 'report_manager'));
    echo '<p>' . get_string('no_outcomes_available', 'report_manager') . '</p>';
}else {
    echo $OUTPUT->heading(get_string('outcome', 'report_manager'));
    $table = outcome::Outcomes_Table($outcome_list);

    echo html_writer::table($table);
}//if_else

// Footer
echo $OUTPUT->footer();