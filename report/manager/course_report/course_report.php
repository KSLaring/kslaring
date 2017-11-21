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

global $CFG,$PAGE,$SESSION,$SITE,$OUTPUT,$USER;

require_once('../../../config.php');
require_once( 'courserptlib.php');
require_once( '../managerlib.php');
require_once($CFG->libdir . '/adminlib.php');

// Params
$url        = new moodle_url('/report/manager/course_report/course_report.php');
$return_url = new moodle_url('/report/manager/index.php');

$site_context = CONTEXT_SYSTEM::instance();
$site = get_site();

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

// Page settings
$PAGE->https_required();
$PAGE->set_context($site_context);

$PAGE->set_pagelayout('report');
$PAGE->set_url($url);

$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('report_manager','local_tracker_manager'),$return_url);
$PAGE->navbar->add(get_string('course_report', 'report_manager'),$url);


if (empty($CFG->loginhttps)) {
    $secure_www_root = $CFG->wwwroot;
} else {
    $secure_www_root = str_replace('http:','https:',$CFG->wwwroot);
}//if_security


$PAGE->verify_https_required();

unset($SESSION->parents);
unset($SESSION->selection);

// Capability
if (!CompetenceManager::is_reporter($USER->id)) {
    require_capability('report/manager:viewlevel3', $site_context);
}


// Header
echo $OUTPUT->header();

// Tabs at the top
$current_tab = 'manager_reports';
$show_roles = 1;
require('../tabs.php');

// Heading - title
echo $OUTPUT->heading(get_string('course_report', 'report_manager'));

// Levels report links
course_report::CleanTemporary();
CompetenceManager::get_level_link_report_page('course_report',$site_context);

echo "</br>";
echo "<a href='" . $return_url ."' class='button_reports'>" . get_string('back') . "</a>";

// Footer
echo $OUTPUT->footer();
