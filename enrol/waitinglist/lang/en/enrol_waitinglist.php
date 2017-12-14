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
 require_once('enrol_waitinglist_method_manual.php');
 
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

$string['wscannotenrol'] = 'Waitlinglist instance cannot enrol a user in the course id = {$a->courseid}';
$string['wsnoinstance'] = 'Waitinglist enrolment plugin instance doesn\'t exist or is disabled for the course (id = {$a->courseid})';
$string['wsusercannotassign'] = 'You don\'t have the permission to assign this role ({$a->roleid}) to this user ({$a->userid}) in this course({$a->courseid}).';

$string['cutoffdate'] = 'Application Deadline';
$string['maxenrolments'] = 'Max. Enrolments';
$string['maxenrolments_help']   = 'Specifies the maximum number of users that can enrol. 0 means no limit';
$string['waitlistsize'] = 'Waitlist Size';
$string['waitlistsize_help']    = 'Specifies the size of the wait list. 0 means no limit';
$string['enrolmethods'] = 'Enrolment Methods';
$string['managequeue'] = 'Manage Queue';
$string['managemethods'] = 'Manage Enrolment Methods';
$string['canthavemoreseats'] = 'You cannot increase the number of reservations if you are not the last on the waiting list.';
$string['noroomonlist'] = 'No room on the waiting list';
$string['listisempty'] = 'The waiting list is empty';
$string['alreadyonlist'] = 'Already on waiting list.';
$string['yourqueuedetails'] = 'You are number: <strong>{$a->queueposition}</strong> of <strong>{$a->queuetotal}</strong> on the waiting list.';
$string['removeconfirmmessage'] = 'Do you really want to remove this entry from the waiting list?';
$string['methodheader'] = 'Method';
$string['seatsheader'] = 'Seats';
$string['allocseatsheader'] = 'Allocated';
$string['confirmedseatsheader'] = 'Confirmed';
$string['requestedseatsheader'] = 'Requested';
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
$string['welcome_ical_attach'] = "Attached iCal file with the course start date.";
$string['customwaitlistmessage'] = 'Custom waitlist message';
$string['customwaitlistmessage_help'] = 'A custom waiting list notification message may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags.

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Waiting list position {$a->queueno}
* Total seats {$a->totalseats}
* Waiting seats {$a->waitingseats}
* Allocated seats {$a->allocatedseats}
* Link to course {$a->courseurl}
* Link to enrolpage {$a->editenrolurl}';
$string['sendcoursewaitlistmessage'] = 'Send email when added to waitlist';
$string['sendcoursewaitlistmessage_help'] = 'An email can be sent to the user when they are added to a course waiting list.';


$string['manageconfirmed'] = 'Manage Confirmed';
$string['unconfirmfailed'] = 'Removal from Confirmed list failed!';
$string['waitinglistisempty'] = 'The Waiting List is Empty';
$string['confirmedlistisempty'] = 'The Confirmed List is Empty';
$string['unconfirm'] = 'Unconfirm';
$string['unconfirmwarning'] = 'Do you really want to return these seats to the waiting list?';
$string['noroomonlist'] = 'We are very sorry, but the waitinglist is full.';
$string['enrolmentsnotyet'] = 'Waitinglist Enrolment is not open yet.';
$string['enrolmentsclosed'] = 'Waitinglist Enrolments have closed.';
$string['alreadyenroled'] = 'You are already enroled in this course.';
$string['qentrynothingchanged'] = 'No update to your reservations was required.';
$string['onlyoneenrolmethodallowed'] = '';//'You can only be on the waitinglist once.'; //strange to show this to user
$string['nomoreseats'] = 'The requested number of places are not available. There are {$a->available} more waitinglist places available. There are {$a->vacancies} more vacancies on the course currently available.';
$string['entercoursenow'] = 'Enter Course Now';
$string['exportexcel'] = 'Export as Excel';
$string['nodataavailable'] = 'No data available for display';
$string['returntoreports'] = 'Return to Reports';
$string['exportprint'] = 'Print Friendly Version';
$string['manageconfirmedheading']='Confirmed Seats in Course: {$a}'; 
$string['totalcell']='Total: {$a}';
$string['printdate']='Print Date: {$a}'; 

/**
 * @updateDate      28/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * New strings to add invoice information option
 */
$string['invoice']      = 'Invoice information required';
$string['invoice_help'] = 'The user must fill in all information about the invoice before the enrolment will be done.';

$string['seats_occupied'] = 'At the moment, all available seats are occupied. Do you want to set on the wait list for this course?';

$string['title_approval']   = 'Requests List';
$string['lnk_approval']     = 'Moderated Approval Requests';

$string['approval']         = 'Approval required';
$string['approval_help']    = 'The user must wait the approval to be enrolled in the course';

$string['none_approval']    = 'None';
$string['approval_message'] = 'Send email to the manager when enrolled in course';

$string['approval_info']    = 'Please, you must fill in your reasons to apply for the course';
$string['arguments']        = 'Arguments';

$string['not_managers'] = 'Sorry, you cannot apply for the course because of there is no manager connected with you.';
$string['not_managers_company'] = 'Sorry, you cannot apply for the course because of there is no manager connected with you for the company <strong>{$a}</strong>.';

$string['mng_subject']  = '{$a->site}: Application for enrolment in course {$a->course}';
$string['mng_body']     = '<p>We would like to inform you that you are manager for <b>{$a->user}</b>, which belongs to the next companies: </p>
                              {$a->companies_user}, has just applied for the course <b>{$a->course}</b>.</p><p>The arguments of the user are:</p><p>{$a->arguments}</p>
                           </br>
                           <p>Course information:</p>
                           <ul>
                                <li><u>Course date</u>: {$a->date}</li>
                                <li><u>Instructor</u>: {$a->instructor}</li>
                                <li><u>Location</u>: {$a->location}</li>
                                <li><u>Internal price: {$a->internal}</u></li>
                                <li><u>External price: {$a->external}</u></li>
                                <li>More information about the course in {$a->homepage}</li>
                           </ul>
                           </br>
                           <p>To approve it You must use this link: {$a->approve}.</p>
                           <p>To reject it You must use this link: {$a->reject}.</p>';


$string['subject_reminder'] = '{$a->site}: Application for enrolment in course {$a->course}. REMINDER';
$string['body_reminder']    = '<p>We would like to <b><u>reminder</u></b> you that you are manager for <b>{$a->user}</b>, which belongs to the next companies: </p>
                              {$a->companies_user},has just applied for the course <b>{$a->course}</b>.</p><p>The arguments of the user are:</p><p>{$a->arguments}</p>
                               </br>
                               <p>Course information:</p>
                               <ul>
                                    <li><u>Course date</u>: {$a->date}</li>
                                    <li><u>Instructor</u>: {$a->instructor}</li>
                                    <li><u>Location</u>: {$a->location}</li>
                                    <li><u>Internal price: {$a->internal}</u></li>
                                    <li><u>External price: {$a->external}</u></li>
                                    <li>More information about the course in {$a->homepage}</li>
                               </ul>
                               </br>
                               <p>You should approve or reject the request as soon as possible.</p>
                               <p>To approve it You must use this link: {$a->approve}.</p>
                               <p>To reject it You must use this link: {$a->reject}.</p>';


$string['std_body']     = 'Your application will be reviewed as soon as possible. We will send you another email when your enrolement is activated.';

$string['approve_lnk']  = 'Approve Request';
$string['reject_lnk']   = 'Reject Request';

$string['request_sent']         = 'Your application will be reviewed as soon as possible. We will send you an email when your request has been processed.';
$string['request_remainder']    = 'Your request was applied on <b>{$a}</b>. It has not been processed yet. Do you want to send a reminder?';

$string['err_link'] = 'Sorry, link not valid. Please, contact with administrator. ';

$string['request_approved']     = 'Your request for the course {$a->homepage} was approved on {$a->sent}.';
$string['request_rejected']     = '<p>Your request for the course {$a->homepage} was rejected on {$a->sent}.</p>
                                   <p>Please contact with your manager if there is wrong and if you can apply again if it is necessary.</p>';
$string['request_rejected_enrol'] = '<p>Your request for the course was rejected on {$a->sent}.</p>
                                     <p>Please contact with your manager if there is wrong and if you can apply again if it is necessary.</p>';

$string['approved_mnd'] = 'The request for the course {$a->homepage} and for the user <b>{$a->user}</b> has been approved successfully.';
$string['rejected_mnd'] = 'The request for the course {$a->homepage} and for the user <b>{$a->user}</b> has been rejected successfully.';

$string['err_process']  = 'Sorry, There has been an error during the process. Please, try it later or contact with administrator.';

$string['no_request']     = 'There is no request';
$string['act_approve']    = 'Approve';
$string['act_reject']     = 'Reject';

$string['rpt_name']         = 'Name';
$string['rpt_username']     = 'Username';
$string['rpt_mail']         = 'Mail';
$string['rpt_arguments']    = 'Arguments';
$string['rpt_seats']        = 'Seats';
$string['rpt_action']       = 'Action';
$string['rpt_attended']     = 'Attended';
$string['rpt_not_attended'] = 'Not Attended';
$string['rpt_approved']     = 'Approved';
$string['rpt_rejected']     = 'Rejected';
$string['rpt_participants'] = 'Maximum number of participants';
$string['rpt_back']         = 'Back';

$string['mng_approved_subject']  = '{$a->site}: Application for enrolment in course {$a->course}';
$string['mng_approved_body_one'] = '<p>We send you this confirmation, because of you are set as manager for the next companies: </p>';
$string['mng_approved_body_two'] = '<p>We would like to inform you that the <b>{$a->user}</b>, which belongs to the next companies: </p>
                                    {$a->companies_user}
                                    <p> has just been enrolled to the course <b>{$a->course}</b>.</p>
                                    <p>Course information:</p>
                                    <ul>
                                        <li><u>Course date</u>: {$a->date}</li>
                                        <li><u>Instructor</u>: {$a->instructor}</li>
                                        <li><u>Location</u>: {$a->location}</li>
                                        <li><u>Internal price: {$a->internal}</u></li>
                                        <li><u>External price: {$a->external}</u></li>
                                        <li>More information about the course in {$a->homepage}</li>
                                    </ul>';


$string['mng_approved_body_end'] = '<p>This is an automatic generated email from {$a->site} and you cannot answer this email.';
$string['home_page']    = 'Course home page';

$string['approval_occupied'] = 'At the moment, all available seats are occupied.So, your application will be processed as soon as there are available seats.';

$string['price'] = 'Price';

$string['in_price']     = 'Internal price';
$string['ext_price']    = 'External price';
$string['ical_path']    = 'iCal path';

$string['company_sel']     = 'Company';
$string['users_connected'] = 'Users connected';
$string['no_competence']   = 'Sorry, you cannot enroll because there is no workplace connected with your profile. Please, update your profile before enrolling to the course.
                              <p>You can update your profile click on <strong>{$a}</strong></p>';

$string['company_demanded']        = 'No demand company';
$string['company_demanded_manual'] = 'Company not demanded. All users will be available to select';

$string['find_resource_number'] = " Find Resource Number";
$string['no_users_invoice']     = " No users invoice approval";
$string['users_matching']       = " Users invoice approval matching";
$string['please_use_filter']    = " Please use the filter";

$string['unenrol_link']         = 'If you want to unenrol by yourself, please click on <strong>{$a}</strong>';
$string['unenrol_me']           = 'Unenrol me';
$string['user_unenrolled']      = 'You have already been unenrolled';
$string['user_not_enrolled']    = 'Sorry, you can be unenrolled because you are not a member of this course';

$string['unenrol_subject'] = 'Course {$a}.Unenrol confirmation.';
$string['unenrol_body']    = 'We would like to inform you, that you have been unenrolled from the course <strong>{$a->name}</strong>';

$string['rpt_workplace']   = 'Workplace';

$string['rpt_by']           = 'By';
$string['rpt_when']         = 'When';

$string['confirm_approve'] = 'Are you sure that you want to approve the request for <strong>{$a->user}</strong> user and <strong>{$a->course}</strong> course?' ;
$string['confirm_reject'] = ' Are you sure that you want to reject the request for <strong>{$a->user}</strong> user and <strong>{$a->course}</strong> course?';

$string['myreservations'] = 'My bulk reservations';

$string['confirm_unrol'] = ' Are you sure that you want to unrol from the <strong>{$a->course}</strong> course?';
