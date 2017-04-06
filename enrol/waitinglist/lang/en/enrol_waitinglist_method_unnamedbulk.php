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

$string['unnamedbulk_displayname'] = 'Unnamed Bulk';
$string['unnamedbulk_menutitle'] = 'Reserve Seats';
$string['waitlistmessagetitle_unnamedbulk'] = 'Seats added to Waitlist for: {$a}';
$string['waitlistmessagetitle_unnamedbulk_changed'] = 'Seats added to Waitlist for: {$a}. Changed seats';
$string['waitlistmessagetext_unnamedbulk'] = '{$a->totalseats} Seats Added to Waitlist for: {$a->coursename}

Your reserved seats are at postion: {$a->queueno} on the waitinglist.

You can edit your reservation here:  {$a->editenrolurl}';
$string['waitlistmessagetitleconfirmation_unnamedbulk'] = 'Seats confirmed for course: {$a}';
$string['waitlistmessagetitleconfirmation_unnamedbulk_changed'] = 'Seats confirmed for course: {$a}. . Changed seats\'';
$string['sendconfirmmessage'] ='Send mail when seats are confirmed';
$string['sendconfirmmessage_help'] ='When places become available on the course, and they are assigned to queue seats for this method, email the user responsible for the seats';
$string['confirmedmessage_unnamedbulk'] = 'Seats allocated for: {$a->coursename}';
$string['confirmedmessagetext_unnamedbulk'] = 'Seats allocated for: {$a->coursename}

{$a->allocatedseats} of your {$a->totalseats} reserved seats on the waitinglist for {$a->coursename} have been allocated.

View and adjust your seat reservations here:  {$a->editenrolurl}';
$string['customconfirmedmessage'] = 'Reservation confirmed message';
$string['customconfirmedmessage_help'] = 'A custom confirmation message may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags.

It will be sent the user responsible for the seat reservations when their vacancies become available on the course.

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Waiting list position {$a->queueno}
* Total seats {$a->totalseats}
* Waiting seats {$a->waitingseats}
* Allocated seats {$a->allocatedseats}
* Link to course {$a->courseurl}
* Link to enrolpage {$a->editenrolurl}';
$string['reserveseatcount'] = 'Number of seats to reserve';
$string['reserveseats'] = 'Reserve Seats on Course';
$string['unnamedbulk_enrolformintro'] = 'Use this form to reserve seats on this course. You will be notified by email when your places in the course are assigned to you. After enroling users, please return here to release the reserved seats.';
$string['unnamedbulk_enrolformqueuestatus'] = 'You are currently reserving {$a->seats} seats. You have been assigned {$a->assignedseats} seats. You have {$a->waitingseats} on the waitinglist.

Your seats are currently at position {$a->queueposition} on the waiting list.';
$string['unnamedbulk_enrolformqueuestatus_label']   = 'Current Reservation';
$string['unnamedbulk_enrolformqueuestatus_all']     = 'You have been assigned all your seats';

$string['no_seats'] = 'You must reserve at least one seat';