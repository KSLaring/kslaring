<?php
/**
 * Extra Profile Field Competence - Language settings (English)
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * A new user profile which includes information about the companies and job roles connected with user
 *
 */

$string['pluginname']           = 'Competence User Profile';
$string['pluginname_help']      = 'Competence User Profile contains information about the companies and job roles connected with.';

$string['competence_profile']   = 'Competence Profile';

$string['profile_desc']   = 'Your information about your companies and job roles';
$string['comptence_desc'] = 'Your information about your companies and job roles';
$string['lnk_update']   = 'Update';
$string['lnk_edit']     = 'Edit';
$string['lnk_delete']   = 'Delete';
$string['lnk_view']     = 'View My competence';

$string['my_companies'] = 'Companies';
$string['my_job_roles'] = 'Job roles';
$string['jr_generics']  = 'Roles generics';

$string['lnk_add']      = 'Add New';
$string['lnk_back']     = 'Back to My profile';

$string['delete_competence']            = 'Delete competence';
$string['delete_competence_are_sure']   = '<p> You are going to delete from your profile the next: </p>
                                                    <li>{$a->company}:</li>
                                                    <p> {$a->roles}</p>
                                           <p> Are you sure?</p>';

$string['add_competence']       = 'Add new competence';
$string['btn_add']              = 'Add';
$string['add_competence_desc']  = 'Here you can add a new competence profile. You should select one or more companies and job roles, which have to be connected with.';

$string['edit_competence']      = 'Edit competence';
$string['btn_save']             = 'Save';
$string['edit_competence_desc'] = 'Here you can update your current competence profile.';

$string['level_generic']        = 'Generics ';

$string['btn_edit_users']       = 'Edit company';

$string['manager']  = 'Manager';
$string['reporter'] = 'Reporter';

$string['msg_subject_manager']  = '{$a->site}: Notification new employee in {$a->company}';
$string['msg_body_manager']     = '<p>We send you this notification, because of you are set as manager for the company <strong>{$a->company}</strong></p>
                                   <p>We would like to inform you that the user <strong>{$a->user}</strong> is a new employee of <strong>{$a->employee}</strong>.</p>
                                   </br>
                                   <p>If the user does not belong to your company, you must reject it by this link {$a->reject}. </p>
                                   </br></br>
                                   <p>This is an automatic generated email from {$a->site} and you cannot answer this email.</p>';

$string['msg_subject_rejected'] = '{$a->site}: Notification from {$a->company}';
$string['msg_body_rejected']    = 'We would like to inform you that your membership to <strong>{$a->company}</strong> has been rejected';
$string['msg_body_approved']    = 'We would like to inform you that your membership to <strong>{$a->company}</strong> has been approved';

$string['msg_boy_reverted']     = '<p>We send you this notification, because of you are set as manager for the company <strong>{$a->company}</strong></p>
                                   <p>We would like to inform you that you have just rejected the membership for the user <strong>{$a->user}</strong>.</p>
                                   </br>
                                   <p>If you would like to revert this situation, because of a mistake or other reason, please click on this link {$a->revert}</p>';

$string['err_link']     = 'Sorry, link not valid. Please, contact with administrator. ';
$string['reject_lnk']   = 'Reject';
$string['approve_lnk']  = 'Approve';

$string['err_process']  = 'Sorry, There has been an error during the process. Please, try it later or contact with administrator.';

$string['request_rejected']      = 'The request for the company <strong>{$a->company}</strong> and for the user <strong>{$a->user}</strong> has been rejected successfully.';
$string['request_approved']      = 'The request for the company <strong>{$a->company}</strong> and for the user <strong>{$a->user}</strong> has been approved successfully.';

$string['request_just_rejected'] = 'The request for the company <strong>{$a->company}</strong> and for the user <strong>{$a->user}</strong> has already been rejected.';
$string['request_just_approved'] = 'The request for the company <strong>{$a->company}</strong> and for the user <strong>{$a->user}</strong> has already been approved.';

$string['alert_approve'] = 'Please be aware that you add yourself to the correct Company. The manager for this company can reject you  if your membership is wrong.';

$string['comp_delete'] = 'This user has already been removed from this workplace.';