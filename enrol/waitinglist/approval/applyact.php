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
 * Approval Request - Action Manager
 *
 * @package         enrol/waitinglist
 * @subpackage      approval
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    29/12/2015
 * @author          efaktor     (fbv)
 *
 * Description
 */
require('../../../config.php');

global $PAGE,$CFG,$OUTPUT;

$act1 = required_param('r',PARAM_RAW);
$act2 = required_param('a',PARAM_RAW);
$act3 = required_param('t',PARAM_RAW);

$contextSystem      = context_system::instance();
$returnUrl          = $CFG->wwwroot . '/index.php';
$url                = new moodle_url('/enrol/waitinglist/approval/action.php');

// Page settings
$PAGE->set_url($url);
$PAGE->set_context($contextSystem);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

// Header
echo $OUTPUT->header();
$relativePath = $CFG->wwwroot . '/enrol/waitinglist/approval/action.php/1/' . $act1 . "/" . $act2 . "/" . $act3;
redirect($relativePath);
// Footer
echo $OUTPUT->footer();