<?php
/**
 * Fellesdata Integration - Language Settings (English)
 *
 * @package         local/fellesdata
 * @subpackage      lang
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
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
$string['body_company_to_sync'] = '<p>We would like to inform you that there are companies that have to be synchronized manually.</p>
                                   <p>Companies such as {$a->companies}</p>
                                   </br>
                                   <p>Please, you should take a look on <strong>{$a->mapping}</strong></p>';
$string['body_jr_to_sync']      = '<p>We would like to inform you that there are job roles that have to be synchronized manually.</p>
                                   <p>Job roles such as {$a->jobroles}</p>
                                   </br>
                                   <p>Please, you should take a look on <strong>{$a->mapping}</strong></p>';

$string['nav_mapping']          = 'Mapping';
$string['header_fellesdata']    = 'Fellesdata Mapping';

$string['nav_map_org']          = 'Organization Mapping';
$string['nav_map_org_new']      = 'Organization Mapping - New Companies';
$string['nav_map_jr']           = 'Job Roles Mapping';

$string['level_map']            = 'Level to map';
$string['pattern']              = 'Sector';
$string['pattern_help']         = 'For example: Schoole. It ill mean that you are going to map all companies that belong to the school sector. The name of company will contain school';
$string['to_match']             = 'To Match';
$string['remain_match']         = '{$a} Remaining to map';
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


$string['to_unmapp']    = 'To Unmap';
$string['mapped_with']  = 'Mapped with';
$string['fs_company']   = 'FS Company';
$string['none_mapped']  = 'There is no company connected with the search';
$string['no_selection'] = 'There is no company selected';