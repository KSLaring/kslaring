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

$string['header_jobroles']      = 'Overordnet koblet med';
$string['jr_to_connect']        = 'Jobbroller som skal mappes';

$string['fellesdata_days']          = 'Antall døgn å importere';
$string['fellesdata_default_days']  = '4';

$string['nav_unmap']                = 'Fjern mapping';
$string['nav_unmap_org']            = 'Fjern org-elementer';
$string['header_unmap_fellesdata']  = 'Fellesdata - Fjern mapping';
$string['unmap_opt']                = 'Innstillinger for Fjern mapping';
$string['level_unmap']              = 'Nivå hvor mapping skal fjernes';

$string['to_unmapp']    = 'Skal fjernes';
$string['mapped_with']  = 'Mappet med';
$string['fs_company']   = 'Fellesdata org-element';
$string['none_unmapped']    = 'Det finnes ingen org-elementer som passer til søket';
$string['no_selection'] = 'Det er ikke valgt noe org-element';

$string['suspicious_header']        = 'Mistenkelige import-data';
$string['suspicious_folder']        = 'Mistenkelige import-data i';
$string['suspicious_notification']  = 'Mistenkelige import-data skal rapporteres via e-post til';
$string['suspicious_remainder']     = 'Send en påminnelse hver';

$string['subj_suspicious']              = '{$a}: Integrasjon TARDIS - KS Læring. Mistenkelige import-data ';
$string['subj_suspicious_remainder']    = '{$a}: Integrasjon TARDIS - KS Læring. Mistenkelige import-data: PÅMINNELSE ';
$string['body_suspicious']              =   '<p>Det ser ut til at disse filene inneholder mistenkelige data som har flere slettinger enn varslingsverdiene som er satt:</p>
                                             </br>
                                             <ul>';
$string['body_suspicious_end']          = '</ul>';
$string['body_suspicious_middle']       = '<li><u><strong>{$a->file}</strong></u> markert som mistenkelige importdata <strong>{$a->marked}</strong>. For å godkjenne: {$a->approve} For å nekte: {$a->reject} </li>';

$string['approve']  = 'Godkjenn';
$string['reject']   = 'Nekt';

$string['approved'] = 'Filen {$a} er godkjent';
$string['rejected'] = 'Filen {$a} er nektet og blir ikke importert';

$string['err_params']   = 'Beklager, men linken er ikke gyldig. Vennligst kontakt administrator';
$string['err_file']     = 'Beklager, filen er allerede behandlet eller fjernet. Vennligst kontakt administrator';
$string['err_process']  = 'Beklager, men det har oppstått et problem under behandlingen. Vennligst prøv igjen senere eller kontakt administrator.';

$string['from'] = 'Fra';
$string['to']   = 'Til';

$string['sync_users']           = 'Synkronisering av brukerkontoer';
$string['sync_competence']      = 'Synkronisering av brukeres arbeidssteder';
$string['sync_company']         = 'Synkronisering av org-elementer';
$string['sync_jobroles']        = 'Synkronisering av jobbroller';
$string['sync_managers']        = 'Synkronisering av ledere på arbeidssteder';

$string['status_app']   = 'Godkjent';
$string['status_rej']   = 'Avslått';
$string['status_wait']  = 'Venter';

$string['big_date'] = 'Datoen kan ikke være etter i dag.';
$string['from_to']  = 'Fra-dato kan ikke være nyere enn Til-dato';

$string['no_data'] = 'Fant ingen mistenkelige import-data';

$string['rpt_file']         = 'Fil';
$string['rpt_since']        = 'Ventet siden';
$string['rpt_connected']    = 'Koblet med';
$string['rpt_status']       = 'Status';
$string['rpt_act']          = 'Handling';

$string['max_suspicious_users']         = 'Maksgrense for mistenkelige verdier koblet med brukere';
$string['max_suspicious_competence']    = 'Maksgrense for mistenkelige verdier koblet med brukeres arbeidssteder';
$string['max_suspicious_rest']          = 'Maksgrense for mistenkelige verdier for resten av importfilene';

$string['map_header']       = 'Mappingsinnstillinger';
$string['map_one']          = 'KS Læring L1';
$string['map_one_desc']     = 'TARDIS mapping til KS Læring L1';
$string['map_two']          = 'KS Læring L2';
$string['map_two_desc']     = 'TARDIS mapping til KS Læring L2';
$string['map_three']        = 'KS Læring L3';
$string['map_three_desc']   = 'TARDIS mapping til KS Læring L3';

$string['downloaded']       = 'Filen <strong>{$a}</strong> er lasted ned';
$string['to_download']      = 'Klikk på <strong>{$a}</strong> for å laste ned filen';

