<?php
/**
 * Sigle Sign On WebService - Language Strings (Norwegian)
 *
 * @package         local
 * @subpackage      doskom
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    20/02/2015
 * @author          eFaktor     (fbv)
 */

$string['pluginname']               = 'DOSKOM SSO';
$string['doskom:manage']            = 'Manage DOSKOM';

$string['err_authenticate']         = 'Beklager, men du er ingen godkjent bruker. Vennligst kontakt systemadministrator.';
$string['exists_username']          = 'Brukernavnet ditt er allerede i bruk.<br />Vennligst kontakt systemadministrator.';
$string['user_deleted']             = 'Brukeren  <strong>{$a->username}</strong> er blitt deaktivert. Vennligst kontakt systmeadministrator.';
$string['application_subject']      = 'Velkommen til KS Læring {$a->site}';
$string['application_body']         = '<p>Hei, <strong>{$a->name}</strong>, velkommen til KS Læring på <strong>{$a->site}</strong></p>Vi har laget en ny brukerkonto til deg. Brukerinformasjonen som er lagret på deg er:<p>p><b>username:</b> {$a->username}</p><b>password:</b> {$a->pwd}<p></p><p>Vennligst <b>bytt passsord!!</b>. Du kan gjøre det her: <b>{$a->change}</b></p>';
$string['sel_company']              = 'Velg et firma...';
$string['company']                  = 'Firma';

$string['cron_activate']            = 'Aktiver';
$string['cron_deactivate']          = 'Deaktiver';
$string['cron_wsso']                = 'DOSKOM SSO - Cron';

$string['end_point']                        = 'API for import av brukere';
$string['end_point_production']             = 'Produksjon';
$string['end_point_production_desc']        = 'Produksjons- eller Dev-site';

$string['crontask']             = 'Doskom synkroniseringsoppgave';

$string['sources']      = 'Sources';
$string['sources_desc'] = 'Sources information (APIs End points)';
$string['viewsources']  = 'View sources';
$string['addsource']    = 'Add source';

$string['companies']        = 'Companies';
$string['companies_desc']   = 'Companies information';
$string['viewcomp']         = 'View companies';
$string['addcomp']          = 'Add company';

$string['strsource']    = 'Source';
$string['strlabel']     = 'Label';
$string['stractions']   = 'Actions';
$string['strcompany']   = 'Company';
$string['stractive']    = 'Active';

$string['headersource']     = 'DOSKOM Sources';
$string['headercompany']    = 'DOSKOM Companies';

$string['delete_endpoint_are_you_sure']   = 'Are you sure you want to delete the <strong>{$a}</strong> end point?';
$string['delete_endpoint_companies_are_you_sure']   = 'There are companies connected with .Are you sure you want to delete the <strong>{$a}</strong> end point?';
$string['delete_company_are_you_sure']    = 'Are you sure you want to delete the <strong>{$a}</strong> company?';

$string['deletedendpoint'] = 'The <strong>{$a}</strong> end point has been deleted';
$string['deletedcompany'] = 'The <strong>{$a}</strong> company has been deleted';
$string['error_deleted'] = 'Sorry, there has been a problem during the process. Please, try again or contact with administrator ';

$string['strcoid']      = 'Company ID';
$string['strconame']    = 'Company name';
$string['srtcouser']    = 'User';
$string['strcotoken']   = 'Token';
$string['strselone']    = 'Select one...';

$string['errexits'] = 'Already exists';

$string['basic_notify'] = 'Notify by email to';

$string['errorws_subject'] = ' {$a}: Integration DOSKOM  - ERROR RESPONSE';
$string['errorws_body']    = ' <p>We would like to inform you, that there has been a problem during the connection/communication with <strong>DOSKOM</strong> services.</p> 
                               <p>So, the process has got an invalid response at <strong>{$a}</strong>.</p> ';

$string['errorprocess_subject'] = ' {$a}: Integration DOSKOM  - ERROR';
$string['errorprocess_body']    = ' <p>We would like to inform you, that there has been a problem during the process so the <strong>DOSKOM</strong> could no bet carried out at <strong>{$a}</strong>.</p>';
