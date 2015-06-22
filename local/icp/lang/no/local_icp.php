<?php
/**
 * Inconsistencies Course Completions  - Language Settings (Norwegian)
 *
 * @package         local
 * @subpackage      course_completions/lang
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    25/05/2015
 * @author          eFaktor     (fbv)
 */

$string['pluginname']                   = 'Sjekk inkonsistens';
$string['icp:manage']               = 'Administrer kursfullføringer';
$string['delete_are_you_sure']      = 'Er du sikker på at du vil fjerne alle inkonsistente fullføringsdata?';
$string['none_inconsistencies']     = 'Gratulerer! Det er ingen inkonsistente fullføringsdata i dette kurset. Alle aktivitets- og kursfullføringer er korrekte.';
$string['inconsistencies_cleaned']  = '<p>De inkonsistente fullføringene er nå fjernet.</p><p>Du bør vente ca 30 minutter før du sjekker kursfullføringsrapporten, fordi</p><p>bakgrunnjobben som tar seg av kursfullføringer må kjøres først.</p>';

$string['total_users']          = 'Antall brukere';
$string['description']          = 'Beskrivelse';
$string['completed_with']       = 'Registrert som "Fullført" til tross for inkonsistente data';
$string['not_completed_with']   = 'Ikke registrert som "Fullført" på grunn av inkonsistente data';

$string['clean']            = 'Rens databasen for inkonsistente data';

$string['start']    = 'Start';

$string['title_index']           = 'Sjekk inkonsistenser';
$string['users_inconsistencies'] = 'Brukere med inkonstente fullføringsdata';

$string['still_inconsistencies'] = 'Det er fortsatt brukere med inkonsistente fullføringsdata. Før du starter søket etter nye inkonsistenser bør du kjøre renseprosedyren.';

$string['err_process']  = 'Det har skjedd en feil under scriptkjøringen. Vennligst prøv igjen senere.';
$string['info_icp']     = '<p><strong>Denne modulen sjekker om det er inkonsistente fullføringsdata i dette kurset.</strong></p><p>Dette kan oppstå når du har kurs med mange deltakere og endrer på aktivitets- og kursfullføringsinnstillingene ETTER at kursdeltakerne har startet og mange allerede har fått kurset registrert som fullført. HVIS du har metalenkede kurs koblet til dette kurset, må du først kjøre denne rutinen i hvert av dem og deretter vente i minst 30 minutter før du fortsetter. Dette er fordi du må fikse mulige underliggende feil før du retter aktivitets- og kursfullføringer i hovedkurset.</p><p>Hvis du har åpnet denne modulen ved en misforståelse eller bare lurer  på hva dette er for noe, vennligst klikk Avbryt nå. Kjøring av dette retteskriptet gjør ingen skade, men hvis alt allerede er ok trenger du ikke å fortsette.</p>';