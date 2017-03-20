<?php
/**
 * Invoice Enrolment Method - Language Strings (Norwegian)
 *
 * @package         enrol/invoice
 * @subpackage      lang/en
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    24/09/2014
 * @author          efaktor     (fbv)
 */

$string['pluginname']           = 'Fakturainnmelding';
$string['pluginname_desc']      = 'Fakturainnmeldingsmodulen tillater brukere å velge hvilke kurs de skal delta på. Tilgang til kursene kan beskyttes med en påmeldingsnøkkel. Manuell innmelding må også være aktivert i kurset.';

$string['invoice:config']       = 'Konfigurer fakturainnmeld';
$string['invoice:manage']       = 'Administrer påmeldte brukere';
$string['invoice:unenrol']      = 'Meld av brukere fra kurs';
$string['invoice:unenrolself']  = 'Meld deg selv av kurset';

$string['report_link']          = 'Brukernes fakturaer';
$string['report_title']         = 'Fakturaliste';
$string['return_course']        = 'Tilbake til kurset';
$string['not_invoices']         = 'Det er ingen faktura';
$string['participants']         = 'Maksimalt antall deltakere';

$string['rpt_name']             = 'Navn';
$string['rpt_work']             = 'Arbeidssted';
$string['rpt_mail']             = 'Epost';
$string['rpt_invoice']          = 'Type';
$string['rpt_details']          = 'Faktura';
$string['rpt_muni']             = 'Kommune';
$string['rpt_sector']           = 'Sektor';
$string['rpt_location']         = 'kurssted';

$string['require_password']                 = 'Påmeldingsnøkkel er påkrevd';
$string['require_password_desc']            = 'Påmeldingsnøkkel er påkrevd i nye kurs og hindre fjerning av påmeldingsnøkkel fra eksisterende kurs.';
$string['use_password_policy']              = 'Bruk passordregler';
$string['use_password_policy_desc']         = 'Bruk standard passordregler for påmeldingsnøkler.';
$string['show_hint']                        = 'Vis hint';
$string['show_hint_desc']                   = 'Vis første bokstav på gjestetilgangsnøkkel.';
$string['expired_action']                   = 'Handling ved utløp av påmelding';
$string['expired_action_help']              = 'Velg handlingen som skal skje når innmeldingen utløper. Vennligst husk at enkelte brukerdata og innstillinger blir fjernet fra kurset for godt ved utmelding fra kurset.';
$string['status']                           = 'Behold eksisterende innmeldinger';
$string['status_desc']                      = 'Tillat registrering via fakturering i nye kurs.';
$string['status_help']                      = 'Ved deaktivering vil alle eksisterende fakturainnmeldinger suspenderes og nye deltakere kan ikke melde seg på.';
$string['new_enrols']                       = 'Tillate nye innmeldinger';
$string['new_enrols_desc']                  = 'Aktiver fakturainnmelding som standard i nye kurs.';
$string['new_enrols_help']                  = 'Denne innstillingen avgjør om brukeren kan melde seg på kurset.';
$string['group_key']                        = 'Bruk gruppepåmeldingsnøkler';
$string['group_key_desc']                   = 'Bruk gruppepåmeldingsnøkler som standard.';
$string['group_key_help']                   = 'I tillegg til å begrense tilgangen til kurset til de som kjenner til påmeldingsnøkkelen, vil bruk av gruppepåmeldingsnøkler automatisk plassere kursdeltakerne i grupper når de melder seg på kurset. 

Merk: Det må spesifiseres en vanlig påmeldingsnøkkel i kurset for at gruppepåmeldingsnøkler skal fungere.';
$string['default_role']                     = 'Standard rolletilordning';
$string['default_role_desc']                = 'Velg rolle som skal være tilordnet brukere under faktura registrering.';
$string['enrol_period']                     = 'Varighet for innmelding';
$string['enrol_period_desc']                = 'Standard varighet når innmelding er gyldig. Dersom den settes til 0 vil innmeldingsvarigheten være ubegrenset.';
$string['enrol_period_help']                = 'Tidsperioden en innmelding er gyldig, regnet fra tidspunktet brukeren melder seg på kurset. Ved deaktivering vil innmeldingen vare evig.';
$string['long_time_no_see']                 = 'Meld av inaktive etter';
$string['long_time_no_see_help']            = 'Om en bruker ikke har besøøkt kurssiden på lang tid, vil brukeren automisk meldes av kurset. Denne innstillingen setter denne tidsgrenser.';
$string['max_enrolled']                     = 'Maks påmeldte brukere';
$string['max_enrolled_help']                = 'Spesifiserer maks antall brukere som kan registrere seg via fakturainnmelding. 0 betyr ubegrenset';
$string['max_enrolled_reached']             = 'Maks antall brukere som kan melde seg på via faktura registrering er allerede nådd.';
$string['send_course_welcome_message']      = 'Send velkomstmelding til kurs';
$string['send_course_welcome_message_help'] = 'Når aktivert vil brukere få en velkomstmelding på epost når de melder seg på kurset.';

$string['cannt_enrol']                          = 'Påmelding er deaktivert eller inaktiv';
$string['cohort_non_member_info']               = 'Bare medlemmer av brukergruppen \'{$a}\' kan registrere seg via fakturerinsmodulen.';
$string['no_password']                          = 'Ingen påmeldingsnøkkel er påkrevd.';
$string['password']                             = 'påmeldingsnøkkel';
$string['password_help']                        = 'Påmeldingsnøkkelen sørger for tilgangen til kurset begrenses til de som kjenner nøkkelen.

Hvis dette felt er tomt, kan alle brukere melde seg på kurset. 

Hvis det er satt en påmeldingsnøkkel må alle brukere som prøver å melde seg på kurset oppgi nøkkelen før de får meldt seg på. Merk at brukeren bare trenger å oppgi nøkkelen ved påmelding, ikke for senere tilgang til kurset';
$string['password_invalid']                     = 'Ugyldig påmeldingsnøkkel, vennligst prøv på nytt';
$string['password_invalid_hint']                = 'Påmeldingsnøkkelen var ugyldig, vennligst prøv på nytt<br />
(Ett lite hint - den starter med \'{$a}\')';
$string['welcome_to_course']                    = 'Velkommen til {$a}';
$string['welcome_to_course_text']               = 'Velkommen til {$a->coursename}!

Hvis du ikke har gjort det allerede ber vi deg om å redigere profilen din slik at vi kan bli bedre kjent med deg

  {$a->profileurl}';

$string['enrol_me']                             = 'Meld meg på';
$string['unenrol']                              = 'Meld av bruker';
$string['unenrolselfconfirm']                   = 'Er du sikker du vil melde deg av fra kurset "{$a}"?';
$string['unenroluser']                          = 'Er du sikker du vil melde av "{$a->user}" fra kurset "{$a->course}"?';
$string['role']                                 = 'Standard tildelt rolle';
$string['enrol_start_date']                     = 'Startdato';
$string['enrol_start_date_help']                = 'Hvis aktivert, kan brukere bare melde seg på fra og med denne datoen.';
$string['enrol_end_date']                       = 'Sluttdato';
$string['enrol_end_date_help']                  = 'Hvis aktivert, kan brukere bare melde seg på til og med denne datoen. .';
$string['enrol_end_dat_error']                  = 'Sluttdato for påmelding kan ikke være tidligere enn startdato. ';
$string['cohort_only']                          = 'Bare medlemmer av brukergruppen';
$string['cohort_only_help']                     = 'Fakturainnmelding kan begrenses til medlemmer av en spesifikk brukergruppe. Merk at endring av dette valget ikke vil påvirke brukere som allerede er påmeldt.';
$string['custom_welcome_message']               = 'Tilpasset velkomstmelding';
$string['custom_welcome_message_help']          = 'En tilpasset velkomstmelding kan legges til som ren tekst eller Moodle-auto format, inkludert HTML tagger og tagger for flere språk.

De følgende plassholderne kan inkluderes i meldingen:

* Kursnavn {$a->coursename}
* Lenke til brukerens profil {$a->profileurl}';

$string['address_invoice']                      = 'Fakturainformasjon (for eksterne deltakere)';
$string['account_invoice']                      = 'Ansvar- og tjenestenummer (for ansatte)';
$string['invoice_street']                       = 'Gateadresse';
$string['invoice_post_code']                    = 'Postnummer';
$string['invoice_city']                         = 'Sted';
$string['invoice_bil']                          = 'Faktura merkes med';
$string['invoice_resp']                         = 'Ansvarsnummer';
$string['invoice_service']                      = 'Tjenestenummer';
$string['invoice_project']                      = 'Prosjektnummer';
$string['invoice_act']                          = 'Aktivitetsnummer';

$string['invoice_info']                         = 'Påmeldingen kan ikke fullføres før all fakturainformasjo.';
$string['account_required']                     = 'Vennligst fyll inn feltet Ansvar- og tjenestenummer';
$string['resp_required']                        = 'Vennligst fyll inn feltet Ansvarsnummer';
$string['service_required']                     = 'Vennligst fyll inn feltet Tjenestenummer';
$string['project_required']                     = 'Vennligst fyll inn feltet Prosjektnummer';
$string['act_required']                         = 'Vennligst fyll inn feltet Aktivitetsnummer';
$string['street_required']                      = 'Vennligst fyll inn feltet Gateadresse';
$string['post_code_required']                   = 'Vennligst fyll inn feltet Postnummer';
$string['city_required']                        = 'Vennligst fyll inn feltet Sted';

$string['report_invoice']                       = 'Faktura';

$string['csvdownload']      = 'Last ned påmeldinger fra dette kurset i et regneark (.xls)';

$string['rpt_course_info']      = 'Kursinfo';
$string['rpt_invoices_info']    = 'Fakturainformasjon';

$string['rpt_seats'] = 'Forespurte';
$string['rpt_price'] = 'Pris';

$string['invoice_approval'] = 'Fakturagodkjenner';
$string['search_approval']  = 'Søk etter fakturagodkjenner';

$string['rpt_resource']  = 'Ressursnummer';

$string['price_int'] = 'Intern pris';
$string['price_ext'] = 'Ekstern pris';

$string['rpt_completed'] = 'Fullført';