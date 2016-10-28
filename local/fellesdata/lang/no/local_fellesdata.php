<?php
/**
 * Fellesdata Integration - Language Settings (Norwegian)
 *
 * @package         local/fellesdata
 * @subpackage      lang
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    01/02/2016
 * @author          eFaktor     (fbv)
 *
 */

$string['pluginname']           = 'Fellesdata-integrasjon';

$string['crontask']             = 'Fellesdata Synchronization Cron Task';
$string['fellesdata:manage']    = 'Administrere Fellesdata-integrasjonen';

$string['fellesdata_settings']      = 'Fellesdata-innstillinger';
$string['fellesdata_end']           = 'API for import fra Fellesdata';
$string['idnumber_end']             = 'Personnummer-endepunkt';
$string['fellesdata_source']        = 'Kilde';
$string['fellesdata_source_desc']   = 'TARDIS, Agresso, Visma';

$string['ks_settings']  = 'KS Læring-innstillinger';
$string['ks_end_point'] = 'KS Læring-adresse';
$string['ks_token']     = 'KS Læring-token';

$string['ks_municipality']  = 'Toppnivå kommune';
$string['ks_hierarchy']     = 'Kommunehierarki';

$string['cron_activate']            = 'Aktivert';
$string['cron_deactivate']          = 'Deaktivert';

$string['basic_notify']             = 'Varsle med e-post til';

$string['subject']              = '{$a}: Integrasjon mellom FELLESDATA og KS Læring';
$string['body_company_to_sync'] = '<p>Vi vil gjerne informere deg om at det nå er dukket opp org-elementer som må mappes manuelt.</p>
                                   <p>Org-elementer det gjelder er: {$a->companies}</p>
                                   </br>
                                   <p>Vi ber deg se over: <strong>{$a->mapping}</strong></p>';
$string['body_jr_to_sync']      = '<p>Vi vil gjerne informere deg om  at det nå er dukket opp jobbroller som må mappes manuelt.</p>
                                   <p>Jobbrollene det gjelder er: {$a->jobroles}</p>
                                   </br>
                                   <p>Vi ber deg se over: <strong>{$a->mapping}</strong></p>';

$string['nav_mapping']          = 'Mapping';
$string['header_fellesdata']    = 'Fellesdata-mapping';

$string['nav_map_org']          = 'Org-struktur mapping';
$string['nav_map_org_new']      = 'Mapping av org-strukturen - nye org-elementer';
$string['nav_map_jr']           = 'Jobbrolle-mapping';

$string['level_map']            = 'Nivå (0-1-2-3) som skal mappes';
$string['pattern']              = 'Tekstmønster';
$string['pattern_help']         = 'For eksempel "Skole". Det betyr at du vil mappe alle org-elementer som hører til f.eks. "Skole og oppvekst".';
$string['to_match']             = 'Å mappe fra Fellesdata';
$string['remain_match']         = '{$a} igjen å mappe';
$string['possible_matches']     = 'Mulige treff i KS Læring';

$string['no_match']             = 'Ikke sikker';
$string['new_comp']             = 'Nytt org-element';
$string['new_jr']               = 'Ny jobbrolle';

$string['type_map']         = 'Mappings-type';
$string['map_opt']          = 'Mappingsopsjoner';
$string['opt_org']          = 'Org-struktur';
$string['opt_jr']           = 'Jobbroller';
$string['opt_generics']     = 'Offentlig';
$string['opt_no_generics']  = 'Ikke offentlig';

$string['no_companies_to_map']  = 'Det finnes ingen org-elementer å mappe.';
$string['no_jr_to_map']         = 'Det finnes ingen jobbroller å mappe.';

$string['btn_match']        = 'Treff';

$string['menu_title']           = 'Fellesdata';
$string['map_org']              = 'Mapper org-enheter';
$string['map_jr']               = 'Mapper jobbroller';

$string['sel_parent']           = 'Velg en...';
$string['header_parent']        = 'Overordnet nivå for det nye org-elementet';
$string['parent']               = 'Overordnet nivå';

$string['to_connect']           = 'Arbeidssteder som skal mappes';

$string['header_jobroles']      = 'Parent connected with';
$string['jr_to_connect']        = 'Job roles to connect';

$string['fellesdata_days']          = 'Import days';
$string['fellesdata_default_days']  = '4';