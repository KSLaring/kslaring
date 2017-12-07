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
 * Unenrol Action - confirma ction
 *
 * @package         enrol/waitinglist
 * @subpackage      unenrol
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    29/12/2015
 * @author          efaktor     (fbv)
 *
 */
require_once('../../../config.php');

global $PAGE,$CFG,$OUTPUT,$SITE,$USER;

$act1 = required_param('u',PARAM_RAW);
$act2 = required_param('tu',PARAM_RAW);
$act3 = required_param('c',PARAM_RAW);
$act4 = required_param('tc',PARAM_RAW);

$contextSystem      = context_system::instance();
$returnUrl          = $CFG->wwwroot . '/index.php';
$url                = new moodle_url('/enrol/waitinglist/unenrol/unenrol.php');

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($contextSystem);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

// Checking access
if (isguestuser($USER)) {
    require_logout();

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('guestsarenotallowed','error'), 'notifysuccess');
    echo $OUTPUT->continue_button($CFG->wwwroot);
    echo $OUTPUT->footer();

    die();
}
// Header
echo $OUTPUT->header();
$relativePath = $CFG->wwwroot . '/enrol/waitinglist/unenrol/unenrol.php/1/' . $act1 . "/" . $act2 . "/" . $act3 . "/" . $act4;
redirect($relativePath);
// Footer
echo $OUTPUT->footer();