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