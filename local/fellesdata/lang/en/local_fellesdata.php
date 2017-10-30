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
 * Fellesdata Integration - Language Settings (English)
 *
 * @package         local/fellesdata
 * @subpackage      lang
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    01/02/2016
 * @author          eFaktor     (fbv)
 *
 */

$string['pluginname']           = 'Fellesdata Integration';

$string['crontask']             = 'Fellesdata Synchronization Cron Task';
$string['fellesdata:manage']    = 'Manage Fellesdata Integration';

$string['fellesdata_settigns']      = 'Fellesdata Services';
$string['fellesdata_end']           = 'API Import Fellesdata';
$string['idnumber_end']             = 'Personal Number End Point';
$string['fellesdata_source']        = 'Source';
$string['fellesdata_source_desc']   = 'TARDIS, Agresso, Visma';

$string['ks_settings']  = 'KS Læring services';
$string['ks_end_point'] = 'KS Læring Site';
$string['ks_token']     = 'KS Token';

$string['ks_municipality']  = 'Top Municipality';
$string['ks_hierarchy']     = 'Hierarchy Municipality';

$string['cron_activate']            = 'Enabled';
$string['cron_deactivate']          = 'Disabled';

$string['basic_notify']             = 'Notify by email to';

$string['subject']              = '{$a}: Integration FELLESDATA KS';
$string['body_company_to_sync'] = '<p>We would like to inform you that there are levels that contain companies to synchronize manually.</p>
                                   <p>Levels such as </p><p>{$a->companies}</p>
                                   </br>
                                   <p>Please, you should take a look on <strong>{$a->mapping}</strong></p>';

$string['body_jr_to_sync']      = '<p>We would like to inform you that there are job roles that have to be synchronized manually.</p>
                                   <p>Job roles such as {$a->jobroles}</p>
                                   </br>
                                   <p>Please, you should take a look on <strong>{$a->mapping}</strong></p>';
$string['body_automatic']       = '<p>We would like to inform you that automatic mapping cannot be applied. It must to be manually mapped at least first level.</p>
                                   </br>
                                   <p>Please, you should take a look on <strong>{$a}</strong></p>';

$string['nav_mapping']          = 'Mapping';
$string['header_fellesdata']    = 'Fellesdata Mapping';

$string['nav_map_org']          = 'Organization Mapping';
$string['nav_map_org_new']      = 'Organization Mapping - New Companies';
$string['nav_map_jr']           = 'Job Roles Mapping';

$string['level_map']            = 'Level to map';
$string['pattern']              = 'Sector';
$string['pattern_help']         = 'For example: Schoole. It ill mean that you are going to map all companies that belong to the school sector. The name of company will contain school';
$string['to_match']             = 'To Match';
$string['remain_match']         = '{$a->of}/{$a->total} to map';
$string['possible_matches']     = 'Possible Matches';

$string['no_match']             = 'No Sure';
$string['new_comp']             = 'New Company';
$string['new_jr']               = 'New Job Roles';

$string['type_map']         = 'Type of Mapping';
$string['map_opt']          = 'Mapping Options';
$string['opt_org']          = 'Company Structure';
$string['opt_jr']           = 'Job Roles';
$string['opt_generics']     = 'Generics';
$string['opt_no_generics']  = 'No Generics';

$string['no_companies_to_map']  = 'There is none company to map.';
$string['no_jr_to_map']         = 'There is none job role to map.';

$string['btn_match']            = 'Match';

$string['menu_title']           = 'Fellesdata';
$string['map_org']              = 'Mapping Organizations';
$string['map_jr']               = 'Mapping Job Roles';

$string['sel_parent']           = 'Select one...';
$string['header_parent']        = 'Parent connected with the new companies';
$string['parent']               = 'Parent';

$string['to_connect']           = 'Companies to connect';

$string['header_jobroles']      = 'Parent connected with';
$string['jr_to_connect']        = 'Job roles to connect';

$string['fellesdata_days']          = 'Import days';
$string['fellesdata_default_days']  = '4';

$string['nav_unmap']                = 'Unmap';
$string['nav_unmap_org']            = 'Unmap organizations';
$string['header_unmap_fellesdata']  = 'Fellesdata Unmap';
$string['unmap_opt']                = 'Unmap Options';
$string['level_unmap']              = 'Level to unmap';


$string['to_unmapp']        = 'To Unmap';
$string['mapped_with']      = 'Mapped with';
$string['fs_company']       = 'FS Company';
$string['none_unmapped']    = 'There is no company connected with the search';
$string['no_selection']     = 'There is no company selected';

$string['suspicious_header']        = 'Suspicious Data';
$string['suspicious_folder']        = 'Suspicious data in';
$string['suspicious_notification']  = 'Suspicious data notify by email to';
$string['suspicious_remainder']     = 'Send remainder each';

$string['subj_suspicious']              = '{$a}: Integration TARDIS - KS. Suspicious Data ';
$string['subj_suspicious_remainder']    = '{$a}: Integration TARDIS - KS. Suspicious Data. REMAINDER ';
$string['body_suspicious']              =   '<p>We would like that the next files contain suspicious data: </p>
                                             </br>
                                             <ul>';
$string['body_suspicious_end']          = '</ul>';
$string['body_suspicious_middle']       = '<li><u><strong>{$a->file}</strong></u> marked as suspicious on <strong>{$a->marked}</strong>. To process: {$a->approve} To reject: {$a->reject} </li>';

$string['approve']  = 'Approve';
$string['reject']   = 'Reject';

$string['approved'] = 'The file {$a} has been approved';
$string['rejected'] = 'The file {$a} has been rejected';

$string['err_params']   = 'Sorry, link no valid. Please, contact to administrator';
$string['err_file']     = 'Sorry, file corrupt or no found it. Please, contact to administrator';
$string['err_process']  = 'Sorry, there has been an error during the process. Please, try it later or contact to administrator';

$string['from'] = 'From';
$string['to']   = 'To';

$string['sync_users']           = 'Users accounts synchronization';
$string['sync_competence']      = 'Users competence synchronization';
$string['sync_company']         = 'Companies synchronization';
$string['sync_jobroles']        = 'Job roles synchronization';
$string['sync_managers']        = 'Managers synchronization';

$string['status_app']   = 'Approved';
$string['status_rej']   = 'Rejected';
$string['status_wait']  = 'Waiting';

$string['big_date'] = 'It cannot be bigger than the present date';
$string['from_to']  = 'From date cannot be bigger than To date';

$string['no_data'] = 'None suspicious data found';

$string['rpt_file']         = 'File';
$string['rpt_since']        = 'Waiting since';
$string['rpt_connected']    = 'Connected with';
$string['rpt_status']       = 'Status';
$string['rpt_act']          = 'Action';

$string['max_suspicious_users']         = 'Maximum suspicious data connected with users';
$string['max_suspicious_competence']    = 'Maximum suspicious data connected with competence';
$string['max_suspicious_rest']          = 'Maximum suspicious connected with the rest';

$string['map_header']       = 'Mapping settings';
$string['map_one']          = 'Level one';
$string['map_one_desc']     = 'Mapping level one';
$string['map_two']          = 'Level two';
$string['map_two_desc']     = 'Mapping level two';
$string['map_three']        = 'Level three';
$string['map_three_desc']   = 'Mapping level three';

$string['downloaded']       = 'The file <strong>{$a}</strong> has been downloaded';
$string['to_download']      = 'Click on <strong>{$a}</strong> to download the file';

$string['nav_unconnected']  = 'Unconnected KS Organizations';
$string['unconnected']      = 'Unconnected';
$string['sel_level']        = 'Level';

$string['no_mapped'] = 'No mapped yet';
$string['to_delete'] = 'To delete from KS';

$string['status']       = 'Get last status';
$string['day']          = 'Day to get last status';
$string['stweekly']     = 'Weekly';
$string['stmonthly']    = 'Monthly';

$string['error_response_subject']           = ' {$a}: Integration TARDIS - ERROR RESPONSE';
$string['error_response_status_subject']    = ' {$a}: Integration STATUS TARDIS - ERROR RESPONSE';
$string['error_reponse_body']               = ' <p>We would like to inform you, that there has been a problem during the connection/communication with <strong>TARDIS</strong> services.</p> 
                                                <p>So, the process <strong>{$a}</strong> has got an invalid response.</p> ';
$string['error_process_subject'] = '{$a->SITE}: {$a->type} Integration - ERROR ';
$string['error_process_body']    = '<p>We would like to inform you, that there has been a problem during the integration between {$a} and KS.</p>
                                    <p>So, the {$a} cron has been de-activated meanwhile the problem is being fixed.Please contact the Weblogin support for assistance.</p>';

$string['map_automatically']        = 'Map automatically';
$string['map_automatically_desc']   = 'Map automatically will not applied for the first execution. The first time the mapping has to be done manually';

$string['parentlevel']  = 'Parent ';
$string['leveltomap']   = 'Level to map: {$a}';
$string['errorpaernt']  = 'Please, you must select a parent to map level {$a}';

$string['strback'] = 'Back';