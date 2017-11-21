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
 * Report Competence Manager - Tracker Module
 *
 * Description
 *
 * @package         report/manager
 * @subpackage      tracker
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    01/04/2015
 * @author          eFaktor     (fbv)
 *
 */
global $CFG,$SITE,$PAGE,$OUTPUT,$USER;

require_once('../../../config.php');
require_once('trackerlib.php');
require_once($CFG->libdir . '/adminlib.php');

// Params
$edit           = optional_param('edit', -1, PARAM_BOOL);
$block_action   = optional_param('blockaction', '', PARAM_ALPHA);
$pdf            = optional_param('pdf', '', PARAM_ALPHA);
$out            = null;
$url = new moodle_url('/report/manager/tracker/index.php');
$site_context   = context_system::instance();

// Page settings
$PAGE->set_context($site_context);
$PAGE->set_url($url);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->requires->js(new moodle_url('/report/manager/js/tracker.js'));

// Checking access
require_login();
if (isguestuser($USER)) {
    require_logout();
    print_error('guestsarenotallowed');
    die();
}

// Tracker user
$trackerUser = TrackerManager::get_user_tracker($USER->id);
switch ($pdf) {
    case TRACKER_PDF_DOWNLOAD:
        TrackerManager::download_tracker_report($trackerUser);
        break;
    default:
        /* Print Tracker User   */
        $out = TrackerManager::print_tracker_info($trackerUser);
        break;
}//switch_pdf

// Header
echo $OUTPUT->header();
echo $OUTPUT->heading($out);

// Footer
echo $OUTPUT->footer();
