<?php
/**
 * Express Login  - Language Settings (Norwegian)
 *
 * @package         local
 * @subpackage      express_login
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    26/11/2014
 * @author          eFaktor     (fbv)
 */

$string['pluginname']           = 'Hurtigtilgang';

$string['title_info']               = 'Her kan du administrere din egen hurtigtilgang for {$a}. </br> Her kan du generere din egen personlige lenke';
$string['title_link']               = 'Kopier din unike og personlige hurtigtilgangslenke for {$a} til utklippstavlen og/eller legg den til som et bokmerke';
$string['title_regenerate_link']    = 'Her kan du generere en ny personlig lenke for hurtigtilgang.';
$string['regenerate_link']          = 'For å generere en ny personlig lenke, må du skrive inn en ny sikkerhetsfrase';
$string['warning_regenerate']       = 'Du har aldri opprettet en hurtigtilgang for: {$a->site} før. Hvis dette er din første gang, klikk på <strong>{$a->url} </strong>.';
$string['title_change']             = 'Her kan du endre hurtigtilgangen din for {$a}.';
$string['header_new_code']          = 'Endre hurtigtilgang';

$string['pin_code']             = 'Min PIN-kode';
$string['pin_old_code']         = 'Nåværende PIN-kode';
$string['pin_new_code']         = 'Ny PIN-kode';
$string['pin_new_code_again']   = 'Gjenta ny PIN-kode';
$string['pin_code_help']        = 'Påminnelse om at du må skrive inne en PIN-kode med 4, 6 eller 8 sifre.';
$string['pin_code_min']         = 'PIN-koden må være et nummer med {$a} sifre';

$string['pin_question']         = 'Krypteringsnøkkel';
$string['pin_new_question']     = 'Ny krypteringsnøkkel';
$string['pin_question_help']    = 'Krypteringsnøkkelen må inneholde eksakt 25 tegn. Den brukes for å generere din unike hurtigtilgangslenke. Du trenger ikke huske krypteringsnøkkelen';
$string['pin_security_err']     = 'Krypteringsnøkkelen må inneholde eksakt 25 tegn';
$string['pin_identical_err']    = 'PIN-koden er ikke gyldig, sifrene er like.';
$string['pin_consecutive_err']  = 'PIN-koden inneholder sifre i rekkefølge';
$string['pin_code_err']         = 'PIN-koden er ikke sikker nok';
$string['pin_percentage_err']   = 'PIN-koden er ikke gyldig. Tallet {$a} repeteres';
$string['pin_numeric_err']      = 'PIN-koden kan bare inneholde numeriske tegn';
$string['pin_code_expired']     = 'Utløpt PIN-kode.';

$string['err_generic']          = 'Det har skjedd en feil. Vennligst prøv igjen eller kontakt en administrator';
$string['err_micro_lnk']        = 'Mikrolæringslenken er ugyldig. Vennligst kontakt kurslæreren din';

$string['pin_new_diff_err']         = 'PIN-kodene er forskjellige';
$string['pin_new_not_diff_current'] = 'Den nye og nåværende PIN-koden er like';
$string['pin_current_diff_err']     = 'Ikke gyldig nåværende PIN-kode';

$string['err_remind']               = 'Ikke gyldig. Dette er den samme som den gamle, du må lage en ny';

$string['btn_copy_link']        = 'Hent hurtigtilgangslenke';
$string['btn_save_link']        = 'Lagre som bokmerke';
$string['btn_generate_link']    = 'Generer lenke';
$string['btn_regenerate_link']  = 'Generer ny lenke';
$string['btn_change_pin_code']  = 'Endre PIN-kode';

$string['settings_desc']        = 'Hurtigtilgangsmodulen er en funksjon som gir brukerne direkte innlogging ved kun å taste inn en pin-kode på 4-8 siffer. Hurtigtilgangsmodulen genererer en unik personlig lenke til hver bruker som de kan lagre i nettleserens Favoritter/Bokmerker.
                                   Når en bruker klikker på denne lenken åpnes en dialogboks hvor personlig pin-kode må tastes inn. En har kun tre forsøk før en blir omdirigert til standard innloggingsmetode med brukernavn og passord.
                                   Pinkoden opprettes i din egen brukerprofil og du kan når som helst bytte den ut med en ny. ';

$string['set_activate']         = 'Aktiver hurtigtilgang';
$string['set_activate_desc']    = 'Aktiver hurtigtilgang';

$string['set_deny']             = 'Forhindre identiske sifre';
$string['set_deny_desc']        = 'Forhindre identiske sifre';

$string['set_expire']           = 'Utløper etter';
$string['set_expire_desc']      = 'Utløper etter';

$string['set_force']            = 'Tving ny token for hurtigtilgang';
$string['set_force_desc']       = 'Tving ny token for hurtigtilgang';

$string['set_minimum']          = 'Minste antall sifre';
$string['set_minimum_dec']      = 'Minste antall sifre';

$string['set_encryption']       = 'Krypteringsfrase';
$string['set_encryption_desc']  = 'Krypteringsfrase (50 tegn)';

$string['ERROR_EXPRESS_LINK_NOT_VALID']             = 'Hurtigtilgangslenke er ugyldig. Vennligst generer en ny lenke eller ta kontakt med en administrator.';
$string['ERROR_EXPRESS_LINK_ATTEMPTED_EXCEEDED']    = 'Ingen gjenstående forsøk';
$string['ERROR_EXPRESS_LINK_USER_NOT_VALID']        = 'Ikke gyldig bruker';
$string['ERROR_EXPRESS_PIN_NOT_VALID']              = 'Ugyldig PIN-kode. Du har igjen {$a} forsøk';

$string['clipboardDiv'] = 'Hurtigtilgangslenken din har blitt kopiert til utklippstavlen din. Alt du trenger å gjøre er å trykke på ctrl+v for å lime den inn der du ønsker.';
$string['bookmarkDiv']  = 'For å legge til hurtigtilgang som et bokmerke, vennligst dra og slipp <strong>{$a}</strong> til bokmerkene.';

$string['err_express_access'] = 'Du har ikke høye nok rettigheter til å generere nye pin-koder.';

$string['cron_settings']            = 'Cron-innstillinger';
$string['cron_activate']            = 'Aktivert';
$string['cron_deactivate']          = 'Deaktivert';

$string['express_subject']        = '{$a}: Pin- og hurtiglogin-lenker generert';
$string['express_body']           = '<p>Hei {$a->name},</p>
<p>Kursportalen utvikles og forbedres kontinuerlig. Vi har nå forenklet innloggingen, slik at det ikke er nødvendig å bruke MinId eller brukernavn/passord hver gang du skal bruke portalen.</p>
<p>Den forenklede innloggingen til kursportalen baserer seg på at du benytter en personlig snarvei (URL) som kan lagres som favoritt i nettleseren din, samt en pinkode som er automatisk generert til deg. Du kan selv bytte pinkoden i din brukerprofil.</p>
 
<p>Din automatisk genererte pinkode er: <strong>{$a->express}</strong></p>
 
<p>Med vennlig hilsen<br />
Portaladministrator</p>';

$string['micro_message']          = '<p>Siden du nå har byttet pin-kode (og dermed også hurtigloginlenken) sender vi deg om igjen alle mikrolærings-eposter du har fått tidligere hvor du IKKE har gjort aktivitene ennå.</p>';

$string['bulk_action']      = 'Generer hurtigtilgang';
$string['bulk_succesful']   = 'Hurtigtilgangen vil genereres i løpet av de neste 30 minuttene.';

$string['crontask']         = 'Express login cron task';

$string['express_disable'] = 'Express login is disable';