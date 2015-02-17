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

 require_once('enrol_waitinglist_method_self.php');
 require_once('enrol_waitinglist_method_unnamedbulk.php');
 require_once('enrol_waitinglist_method_namedbulk.php');
 require_once('enrol_waitinglist_method_selfconfirmed.php');
 require_once('enrol_waitinglist_method_paypal.php');

 
$string['alterstatus'] = 'Alter status';
$string['altertimeend'] = 'Alter end time';
$string['altertimestart'] = 'Alter start time';
$string['assignrole'] = 'Assign role';
$string['confirmbulkdeleteenrolment'] = 'Are you sure you want to delete these users enrolments?';
$string['defaultperiod'] = 'Default enrolment duration';
$string['defaultperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['defaultperiod_help'] = 'Default length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited by default.';
$string['deleteselectedusers'] = 'Delete selected user enrolments';
$string['editselectedusers'] = 'Edit selected user enrolments';
$string['enrolledincourserole'] = 'Enrolled in "{$a->course}" as "{$a->role}"';
$string['enrolusers'] = 'Enrol users';
$string['expiredaction'] = 'Enrolment expiration action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['expirymessageenrollersubject'] = 'Enrolment expiry notification';
$string['expirymessageenrollerbody'] = 'Enrolment in the course \'{$a->course}\' will expire within the next {$a->threshold} for the following users:

{$a->users}

To extend their enrolment, go to {$a->extendurl}';
$string['expirymessageenrolledsubject'] = 'Enrolment expiry notification';
$string['expirymessageenrolledbody'] = 'Dear {$a->user},

This is a notification that your enrolment in the course \'{$a->course}\' is due to expire on {$a->timeend}.

If you need help, please contact {$a->enroller}.';
$string['waitinglist:config'] = 'Configure waitinglist enrol instances';
$string['waitinglist:enrol'] = 'Enrol users';
$string['waitinglist:manage'] = 'Manage user enrolments';
$string['waitinglist:unenrol'] = 'Unenrol users from the course';
$string['waitinglist:unenrolself'] = 'Unenrol self from the course';
$string['waitinglist:canbulkenrol'] = 'Access bulk enrol item in course->user menu';
$string['messageprovider:expiry_notification'] = 'Waitinglist enrolment expiry notifications';
$string['pluginname'] = 'Waitinglist enrolments';
$string['pluginname_desc'] = 'The waitinglist enrolments plugin maintains a waiting list for enrolments to a course.';
$string['status'] = 'Enable waitinglist enrolments';
$string['status_desc'] = 'Allow course access of internally enrolled users. This should be kept enabled in most cases.';
$string['status_help'] = 'This setting determines whether users can be enrolled waitinglistly, via a link in the course administration settings, by a user with appropriate permissions such as a teacher.';
$string['statusenabled'] = 'Enabled';
$string['statusdisabled'] = 'Disabled';
/*
$string['unenrol'] = 'Unenrol user';
$string['unenrolselectedusers'] = 'Unenrol selected users';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['unenroluser'] = 'Do you really want to unenrol "{$a->user}" from course "{$a->course}"?';
$string['unenrolusers'] = 'Unenrol users';
*/
$string['wscannotenrol'] = 'Waitlinglist instance cannot enrol a user in the course id = {$a->courseid}';
$string['wsnoinstance'] = 'Waitinglist enrolment plugin instance doesn\'t exist or is disabled for the course (id = {$a->courseid})';
$string['wsusercannotassign'] = 'You don\'t have the permission to assign this role ({$a->roleid}) to this user ({$a->userid}) in this course({$a->courseid}).';

$string['cutoffdate'] = 'Application Deadline';
$string['maxenrolments'] = 'Max. Enrolments';
$string['waitlistsize'] = 'Waitlist Size';
$string['enrolmethods'] = 'Enrolment Methods';
$string['managequeue'] = 'Manage Queue';
$string['managemethods'] = 'Manage Enrolment Methods';
$string['nomoreseats'] = 'The requested number of seats are not available';
$string['canthavemoreseats'] = 'You cannot increase the number of reservations if you are not the last on the waiting list.';
$string['noroomonlist'] = 'No room on the waiting list';
$string['listisempty'] = 'The waiting list is empty';
$string['alreadyonlist'] = 'Already on waiting list.';
$string['yourqueuedetails'] = 'You are number: <strong>{$a->queueposition}</strong> of <strong>{$a->queuetotal}</strong> on the waiting list.';
$string['removeconfirmmessage'] = 'Do you really want to remove this entry from the waiting list?';
$string['methodheader'] = 'Method';
$string['seatsheader'] = 'Seats';
$string['allocseatsheader'] = 'Allocated';
$string['updownheader'] = 'Up/Down';
$string['qentryupdated'] = 'Waiting list entry updated.';
$string['qentryremoved'] = 'Waiting list entry removed.';
$string['qmovefailed'] = 'Queue position change failed!';
$string['qremovefailed'] = 'Remove from Queue operation failed!';
$string['waitinglisttask'] = 'Waitinglist Enrolment Task';
$string['insufficientpermissions'] = 'You do not have the ability to use this waitinglist enrolment method';
$string['sendcoursewelcomemessage'] = 'Send email when enroled in course';
$string['sendcoursewelcomemessage_help'] = 'An email can be sent to the user when they are enroled in the course';
$string['customwelcomemessage'] = 'Custom welcome message';
$string['customwelcomemessage_help'] = 'A custom welcome message may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags.

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Link to user\'s profile page {$a->profileurl}';
$string['welcometocourse'] = 'Welcome to {$a}';
$string['welcometocoursetext'] = 'Welcome to {$a->coursename}!

If you have not done so already, you should edit your profile page so that we can learn more about you:

  {$a->profileurl}';
$string['customwaitlistmessage'] = 'Custom waitlist message';
$string['customwaitlistmessage_help'] = 'A custom waiting list notification message may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags.

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Waiting list position {$a->queueno}
* Waiting list seats {$a->queueseats}
* Link to course {$a->courseurl}
* Link to enrolpage {$a->editenrolurl}';
$string['sendcoursewaitlistmessage'] = 'Send email when added to waitlist';
$string['sendcoursewaitlistmessage_help'] = 'An email can be sent to the user when they are added to a course waiting list.';



