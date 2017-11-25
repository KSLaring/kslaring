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
 * Extra Profile Field Competence - Reject Competence
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    15/09/2017
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../../../config.php');

global $PAGE,$CFG,$OUTPUT,$SITE,$USER;

$act1 = required_param('t',PARAM_RAW);
$act2 = required_param('m',PARAM_RAW);

$contextSystem      = context_system::instance();
$returnUrl          = $CFG->wwwroot . '/index.php';
$url                = new moodle_url('/user/profile/field/competence/actions/reject.php');

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($contextSystem);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

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
// Header
echo $OUTPUT->header();
$relativePath = $CFG->wwwroot . '/user/profile/field/competence/actions/reject.php/1/' . $act1 . "/" . $act2;
redirect($relativePath);
// Footer
echo $OUTPUT->footer();