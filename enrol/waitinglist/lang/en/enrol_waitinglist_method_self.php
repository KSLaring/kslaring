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
 * Strings for component 'enrol_waitinglist', language 'en'.
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['self_displayname'] = 'Self Enrolment';
$string['self_menutitle'] = 'Enrol Self';
$string['waitlistmessage_self'] = 'Added to Waitlist for: {$a}';
$string['waitlistmessagetitle_self'] = 'Added to Waitlist for: {$a}';
$string['waitlistmessagetext_self'] = 'You are on the {$a->coursename} waiting list!

You are currently number {$a->queueno}.

You can check your position here:  {$a->courseurl}';
$string['self_queuewarning_label'] ='This course is presently booked';
$string['self_queuewarning'] = 'If you proceed you will be placed on a waiting list and will be enrolled automatically and informed by email when a place becomes available.

Number of persons waiting in front of you: {$a}';

$string['cannot_unenrol_date']    = 'Sorry, you cannot unenrol it after the deadline';
$string['unenrolenddate']         = 'Deadline to unenrol';
$string['unenrolenddate_help']    = 'If enabled, users can unenrol themselves until this date only.';
$string['unenrolenddate_err']     = 'The unenrol deadline cannot be less than the currernt one.';
