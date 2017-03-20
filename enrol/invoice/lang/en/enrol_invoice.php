<?php
/**
 * Invoice Enrolment Method - Language Strings (English)
 *
 * @package         enrol/invoice
 * @subpackage      lang/en
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    24/09/2014
 * @author          efaktor     (fbv)
 */

$string['pluginname']           = 'Invoice Enrolment';
$string['pluginname_desc']      = 'The invoice enrolment plugin allows users to choose which courses they want to participate in.
                                   The courses may be protected by an enrolment key. Internally the enrolment is done via the manual enrolment plugin which has to be enabled in the same course.';

$string['invoice:config']       = 'Configure invoice enrol instances';
$string['invoice:manage']       = 'Manage enrolled users';
$string['invoice:unenrol']      = 'Unenrol users from course';
$string['invoice:unenrolself']  = 'Unenrol invoice from the course';

$string['report_link']          = 'Users Invoices';
$string['report_title']         = 'Invoices List';
$string['return_course']        = 'Back to Course';
$string['not_invoices']         = 'There is none invoice';
$string['participants']         = 'Maximum number of participants';

$string['rpt_name']             = 'Name';
$string['rpt_work']             = 'Workplace';
$string['rpt_mail']             = 'Mail';
$string['rpt_invoice']          = 'Type';
$string['rpt_details']          = 'Invoice';
$string['rpt_muni']             = 'Municipality';
$string['rpt_sector']           = 'Sector';
$string['rpt_location']         = 'Location';

$string['require_password']                 = 'Require enrolment key';
$string['require_password_desc']            = 'Require enrolment key in new courses and prevent removing of enrolment key from existing courses.';
$string['use_password_policy']              = 'Use password policy';
$string['use_password_policy_desc']         = 'Use standard password policy for enrolment keys.';
$string['show_hint']                        = 'Show hint';
$string['show_hint_desc']                   = 'Show first letter of the guest access key.';
$string['expired_action']                   = 'Enrolment expiration action';
$string['expired_action_help']              = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['status']                           = 'Enable existing enrolments';
$string['status_desc']                      = 'Enable invoice enrolment method in new courses.';
$string['status_help']                      = 'If disabled all existing invoice enrolments are suspended and new users can not enrol.';
$string['new_enrols']                       = 'Allow new enrolments';
$string['new_enrols_desc']                  = 'Allow users to invoice enrol into new courses by default.';
$string['new_enrols_help']                  = 'This setting determines whether a user can enrol into this course.';
$string['group_key']                        = 'Use group enrolment keys';
$string['group_key_desc']                   = 'Use group enrolment keys by default.';
$string['group_key_help']                   = 'In addition to restricting access to the course to only those who know the key, use of group enrolment keys means users are automatically added to groups when they enrol in the course.

Note: An enrolment key for the course must be specified in the invoice enrolment settings as well as group enrolment keys in the group settings.';
$string['default_role']                     = 'Default role assignment';
$string['default_role_desc']                = 'Select role which should be assigned to users during invoice enrolment';
$string['enrol_period']                     = 'Enrolment duration';
$string['enrol_period_desc']                = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrol_period_help']                = 'Length of time that the enrolment is valid, starting with the moment the user enrols themselves. If disabled, the enrolment duration will be unlimited.';
$string['long_time_no_see']                 = 'Unenrol inactive after';
$string['long_time_no_see_help']            = 'If users haven\'t accessed a course for a long time, then they are automatically unenrolled. This parameter specifies that time limit.';
$string['max_enrolled']                     = 'Max enrolled users';
$string['max_enrolled_help']                = 'Specifies the maximum number of users that can invoice enrol. 0 means no limit.';
$string['max_enrolled_reached']             = 'Maximum number of users allowed to invoice-enrol was already reached.';
$string['send_course_welcome_message']      = 'Send course welcome message';
$string['send_course_welcome_message_help'] = 'If enabled, users receive a welcome message via email when they invoice-enrol in a course.';

$string['cannt_enrol']                          = 'Enrolment is disabled or inactive';
$string['cohort_non_member_info']               = 'Only members of cohort \'{$a}\' can invoice-enrol.';
$string['no_password']                          = 'No enrolment key required.';
$string['password']                             = 'Enrolment key';
$string['password_help']                        = 'An enrolment key enables access to the course to be restricted to only those who know the key.

If the field is left blank, any user may enrol in the course.

If an enrolment key is specified, any user attempting to enrol in the course will be required to supply the key. Note that a user only needs to supply the enrolment key ONCE, when they enrol in the course.';
$string['password_invalid']                     = 'Incorrect enrolment key, please try again';
$string['password_invalid_hint']                = 'That enrolment key was incorrect, please try again<br />
(Here\'s a hint - it starts with \'{$a}\')';
$string['welcome_to_course']                    = 'Welcome to {$a}';
$string['welcome_to_course_text']               = 'Welcome to {$a->coursename}!

If you have not done so already, you should edit your profile page so that we can learn more about you:

  {$a->profileurl}';

$string['enrol_me']                             = 'Enrol me';
$string['unenrol']                              = 'Unenrol user';
$string['unenrolselfconfirm']                   = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['unenroluser']                          = 'Do you really want to unenrol "{$a->user}" from course "{$a->course}"?';
$string['role']                                 = 'Default assigned role';
$string['enrol_start_date']                     = 'Start date';
$string['enrol_start_date_help']                = 'If enabled, users can enrol themselves from this date onward only.';
$string['enrol_end_date']                       = 'End date';
$string['enrol_end_date_help']                  = 'If enabled, users can enrol themselves until this date only.';
$string['enrol_end_dat_error']                  = 'Enrolment end date cannot be earlier than start date';
$string['cohort_only']                          = 'Only cohort members';
$string['cohort_only_help']                     = 'Invoice enrolment may be restricted to members of a specified cohort only. Note that changing this setting has no effect on existing enrolments.';
$string['custom_welcome_message']               = 'Custom welcome message';
$string['custom_welcome_message_help']          = 'A custom welcome message may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags.

The following placeholders may be included in the message:

* Course name {$a->coursename}
* Link to user\'s profile page {$a->profileurl}';

$string['address_invoice']                      = 'Address';
$string['account_invoice']                      = 'Account';
$string['invoice_street']                       = 'Street';
$string['invoice_post_code']                    = 'Post Code';
$string['invoice_city']                         = 'City';
$string['invoice_bil']                          = 'Invoice marked with';
$string['invoice_resp']                         = 'Responsibility number';
$string['invoice_service']                      = 'Service number';
$string['invoice_project']                      = 'Project number';
$string['invoice_act']                          = 'Activity number';


$string['invoice_info']                         = 'Please, you must choose and fill in all information about the invoice before the enrolment will be done.';
$string['account_required']                     = 'The account is required';
$string['resp_required']                        = 'The Responsibility number is required';
$string['service_required']                     = 'The Service number is required';
$string['project_required']                     = 'The Project number is required';
$string['act_required']                         = 'The Activity number is required';
$string['street_required']                      = 'The street is required';
$string['post_code_required']                   = 'The post code is required';
$string['city_required']                        = 'The city is required';

$string['report_invoice']                       = 'Invoices';

$string['csvdownload']      = 'Download in spreadsheet format (.xls)';

$string['rpt_course_info']      = 'Course Info';
$string['rpt_invoices_info']    = 'Invoices Info';

$string['rpt_seats'] = 'Seats';
$string['rpt_price'] = 'Price';

$string['invoice_approval'] = 'Invoice approval';
$string['search_approval']  = 'Search approval';

$string['rpt_resource']     = 'Resource';

$string['price_int'] = 'Internal price';
$string['price_ext'] = 'External price';

$string['rpt_completed'] = 'Completed';
