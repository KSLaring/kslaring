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
 * Fellesdata Suspicious Integration - Action (Approve - Reject)
 *
 * @package         local/fellesdata
 * @subpackage      suspicious
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    28/12/2016
 * @author          eFaktor     (fbv)
 *
 */
require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('../lib/suspiciouslib.php');
require_once('../lib/fellesdatalib.php');

/* PARAMS */
$action         = required_param('a',PARAM_INT);
$suspuciousId   = required_param('id',PARAM_INT);
$url            = new moodle_url('/local/fellesdata/suspicious/result.php');
$return         = new moodle_url('/local/fellesdata/suspicious/index.php');
$args           = null;
$strMessage     = null;
$error          = NONE_ERROR;

/* Start the page */
$siteContext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context($siteContext);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('suspicious_header','local_fellesdata'));

// Header
echo $OUTPUT->header();

echo $OUTPUT->notification($strMessage, 'notifysuccess');
echo $OUTPUT->continue_button($return);

// Footer
echo $OUTPUT->footer();