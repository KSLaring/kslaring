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
 * Strings for component 'enrol_waitinglist', language 'en'.
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once('enrol_waitinglist_method_self.php');
 require_once('enrol_waitinglist_method_unnamedbulk.php');
 require_once('enrol_waitinglist_method_namedbulk.php');
 require_once('enrol_waitinglist_method_selfconfirmed.php');
 require_once('enrol_waitinglist_method_paypal.php');
 require_once('enrol_waitinglist_method_manual.php');

 
$string['alterstatus'] = 'Endre status';
$string['altertimeend'] = 'Endre deadline';
$string['altertimestart'] = 'Endre starttidspunkt';
$string['assignrole'] = 'Tildel rolle';
$string['confirmbulkdeleteenrolment'] = 'Er du sikker på at du vil slette disse brukerpåmeldingene?';
$string['confirmedseatsheader'] = 'Bekreftet';
$string['requestedseatsheader'] = 'Forespurte';
$string['defaultperiod'] = 'Standard påmeldngsvarighet';
$string['defaultperiod_desc'] = 'Standard varighet for påmeldingens gyldighet. Dersom -null-, vil påmeldingsvarigheten være ubegrenset.';
$string['defaultperiod_help'] = 'Standard varighet for hvor lenge en påmelding er gyldig, og starter fra kursdeltakeren er innmeldt. Dersom deaktivert vil innmeldingsvarigheten være ubegrenset.';
$string['deleteselectedusers'] = 'Slett valgte brukerpåmeldinger';
$string['editselectedusers'] = 'Endre valgte brukerpåmeldinger';
$string['enrolledincourserole'] = 'Påmeldt i "{$a->course}" som "{$a->role}"';
$string['enrolusers'] = 'Meld inn kursdeltakere';
$string['expiredaction'] = 'Handling når påmeldingen utløper';
$string['expiredaction_help'] = 'Velg hvilken handling som skal brukes når en påmelding utløper. Vennligst merk at noen brukerdata og innstillinger blir slettet fra kurset når de meldes ut.';
$string['expirymessageenrollersubject'] = 'Melding om at påmeldingen utløper';
$string['expirymessageenrollerbody'] = 'Påmelding i kurset \'{$a->course}\' vil utløpe de neste {$a->threshold} for følgende kursdeltakere:

{$a->users}

Hvis du vil forlenge påmeldingstiden, kan du gå til {$a->extendurl}';
$string['expirymessageenrolledsubject'] = 'Din kurspåmelding er i ferd med å utløpe';
$string['expirymessageenrolledbody'] = 'Kjære {$a->user},

Dette er en melding om at kurspåmeldingen din i kurset \'{$a->course}\'  vil utløpe den {$a->timeend}.

Dersom du trenger hjelp, vennligst kontakt: {$a->enroller}.

(Dette er en automatisk generert e-post fra {$a->site} og du kan ikke svare på e-posten.)';
$string['waitinglist:config'] = 'Administrere instanser av ventelister';
$string['waitinglist:enrol'] = 'Innmelding av kursdeltakere';
$string['waitinglist:manage'] = 'Administrere brukerpåmeldinger';
$string['waitinglist:unenrol'] = 'Meld ut brukere fra kurset';
$string['waitinglist:unenrolself'] = 'Meld meg ut av dette kurset';
$string['messageprovider:expiry_notification'] = 'Din kurspåmelding er i ferd med å utløpe';
$string['pluginname'] = 'Kurspåmeldinger';
$string['pluginname_desc'] = 'Kurspåmeldingsmodulen har en venteliste for påmelding til et kurs.';
$string['status'] = 'Aktiver påmelding med venteliste';
$string['status_desc'] = 'Tillat kurstilgang fra internt påmeldte brukere. Dette bør være aktivert i de fleste tilfeller.';
$string['status_help'] = 'Denne innstillingen bestemmer hvorvidt brukere kan meldt på via påmelding med venteliste, via en link i kursadministrasjonsinnstillingene, og av en bruker med tillatelse til dette, f.eks. en lærer.';
$string['statusenabled'] = 'Aktivert';
$string['statusdisabled'] = 'Deaktivert';

$string['wscannotenrol'] = 'Ventelisteinstansen kan ikke melde brukeren inn i kurset med id: {$a->courseid}';
$string['wsnoinstance'] = 'Ventelisteinstansen eksisterer ikke eller er deaktivert for kurset (id = {$a->courseid})';
$string['wsusercannotassign'] = 'Du har ikke tillatelse til å tildele ({$a->roleid}) rollen til denne brukeren: ({$a->userid}) i kurset ({$a->courseid}).';

$string['cutoffdate'] = 'Frist for påmelding';
$string['maxenrolments'] = 'Maks antall påmeldinger';
$string['maxenrolments_help']   = 'Angir maksgrensen for hvor mange som kan melde seg på. -0- betyr ubegrenset antall plasser.';
$string['waitlistsize'] = 'Maks størrelse på ventelisten';
$string['waitlistsize_help']    = 'Angir størrelsen på ventelisten. -0- betyr ubegrenset.';
$string['enrolmethods'] = 'Påmeldingsmetoder';
$string['managequeue'] = 'Administrere ventelisten';
$string['managemethods'] = 'Administrere påmeldingsmetoder';
$string['nomoreseats'] = 'Det ønskede antall kursplasser er ikke tilgjengelig';
$string['canthavemoreseats'] = 'Du kan ikke øke antall reservasjoner hvis du ikke er den siste på ventelisten.';
$string['noroomonlist'] = 'Ingen plasser på ventelisten';
$string['listisempty'] = 'Ventelisten er tom';
$string['alreadyonlist'] = 'Allerede på ventelisten.';
$string['yourqueuedetails'] = 'Du er nummer: <strong>{$a->queueposition}</strong> av <strong>{$a->queuetotal}</strong> på ventelisten.';
$string['removeconfirmmessage'] = 'Vil du virkelig slette denne søknaden fra ventelisten?';
$string['methodheader'] = 'Metode';
$string['seatsheader'] = 'Forespurte';
$string['allocseatsheader'] = 'Bekreftet';
$string['updownheader'] = 'Opp/Ned';
$string['qentryupdated'] = 'Ventelisteoppføringen er oppdatert.';
$string['qentryremoved'] = 'Ventelisteoppføringen er slettet.';
$string['qmovefailed'] = 'Endring av plass i køen feilet!';
$string['qremovefailed'] = 'Sletting fra køen feilet!';
$string['waitinglisttask'] = 'Påmelding med venteliste oppgave';
$string['insufficientpermissions'] = 'Du har ikke anledning til å bruke denne ventelistemetoden';
$string['sendcoursewelcomemessage'] = 'Send epost ved kurspåmeldinger';
$string['sendcoursewelcomemessage_help'] = 'Det kan sendes en epost til brukeren når de melder seg på kurset';
$string['customwelcomemessage'] = 'Egendefinert velkomstmelding';
$string['customwelcomemessage_help'] = 'En egendefinert velkomstmelding kan opprettes som ren tekst eller i Moodles auto-format, inkludert HTML-tagger og flerspråktagger.

Følgende plassholdere kan legges inn i meldingen:

* Kursnavn {$a->coursename}
* Link til brukerens profilside {$a->profileurl}';
$string['welcometocourse'] = 'Du er påmeldt {$a} - velkommen!';
$string['welcometocoursetext'] = '<p>Du er påmeldt {$a->coursename} – velkommen!</p> <p>Vi anbefaler at du legger inn en påminnelse om kurset i din kalender.</p>
<p>Dette er en automatisk generert e-post. Du kan ikke svare på e-posten.</p>';
$string['customwaitlistmessage'] = 'Egendefinert melding for påmelding med venteliste';
$string['customwaitlistmessage_help'] = 'Du kan legge til en egendefinert melding som ren tekst eller i Moodles auto-format, inkludert HTML-tagger og flerspråktagger.

Følgende plassholdere kan legges inn i meldingen:

* Kursnavn {$a->coursename}
* Plass på ventelisten {$a->queueno}
* Antall plasser i ventelisten {$a->queueseats}
* Link ttil kurset {$a->courseurl}
* Link til påmeldingssiden {$a->editenrolurl}';
$string['welcome_ical_attach'] = "<p>Vedlagt kalenderfil. Trykk på denne for å oppdatere kalenderen din. <strong>OBS! Du må selv sette inn korrekte klokkeslett for arrangementet.</strong></p>";
$string['sendcoursewaitlistmessage'] = 'Send epost når brukeren blir lagt til i ventelisten';
$string['sendcoursewaitlistmessage_help'] = 'Du kan sende en epost til brukeren når de legges til på kursets venteliste.';

$string['manageconfirmed'] = 'Bekreftede kursplasser';
$string['unconfirmfailed'] = 'Fjerning fra bekreftede kursplasser mislyktes!';
$string['waitinglistisempty'] = 'Ventelisten er tom';
$string['confirmedlistisempty'] = 'Listen er tom';
$string['unconfirm'] = 'Fjern';
$string['unconfirmwarning'] = 'Ønsker du virkelig å returnere disse kursplassene til ventelisten igjen?';
$string['noroomonlist'] = 'Vi beklager, men ventelisten er full.';
$string['enrolmentsnotyet'] = 'Påmelding med venteliste har ikke åpnet ennå.';
$string['enrolmentsclosed'] = 'Påmelding med venteliste er nå stengt.';
$string['alreadyenroled'] = 'Du har allerede meldt deg  på dette kurset.';
$string['qentrynothingchanged'] = 'Det trengs ingen oppdatering av reservasjonen din.';
$string['onlyoneenrolmethodallowed'] = '';//'You can only be on the waitinglist once.'; //strange to show this to user
$string['nomoreseats'] = 'Det ønskede antall kursplasser er ikke tilgjengelig. Det er {$a->available} ledige plasser på ventelisten.';
$string['entercoursenow'] = 'Gå til kurset';
$string['exportexcel'] = 'Eksport til Excel';
$string['nodataavailable'] = 'Ingen informasjon å vise her';
$string['returntoreports'] = 'Gå tilbake til rapporter';
$string['exportprint'] = 'Utskriftsvennling versjon';
$string['manageconfirmedheading']='Bekreftede plasser i kurset: {$a}'; 
$string['totalcell']='Totalt: {$a}'; 

/**
 * @updateDate      28/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * New strings to add invoice information option
 */
$string['invoice']      = 'Krev fakturainformasjon';
$string['invoice_help'] = 'Brukeren må fylle inn all nødvendig fakturainformasjon før påmeldingen skjer.';

$string['seats_occupied'] = 'Det er i øyeblikket ingen ledige plasser. Ønsker du å bli plassert på en venteliste for dette kurset?';

$string['title_approval']   = 'Godkjenningsforespørsler';
$string['lnk_approval']     = 'Forespørsler om godkjenning av påmelding';

$string['approval']         = 'Godkjenning fra leder er påkrevd';
$string['approval_help']    = 'Brukeren må vente på godkjenning fra leder før påmeldingen blir fullført';

$string['none_approval']    = 'Verken ledergodkjenning eller varsling til leder';
$string['approval_message'] = 'Send en epost til lederen når brukeren meldes inn i kurset';

$string['approval_info']    = 'Vennligst fyll inn en begrunnelse for at du vil melde deg på dette kurset';
$string['arguments']        = 'Begrunnelse';

$string['not_managers'] = 'Beklager, men du kan ikke melde deg på dette kurset siden vi ikke vet hvem som er lederen din. Vennligst kontakt din lokale administrator.';
$string['not_managers_company'] = 'Beklager, men du kan ikke melde deg på dette kurset fordi det er ikke er lagt til leder for arbeidsstedet ditt: {$a}.';

$string['mng_subject']  = '{$a->site}: Søknad om kursplass for kurset {$a->course}';
$string['mng_body']     = '<p>Du får denne forespørselsen om godkjenning siden du er oppført som lederen for <b>{$a->user}</b> som hører til følgende arbeidssted: </p>
                              {$a->companies_user}, nettopp har søkt om plass på kurset  <b>{$a->course}</b>.</p><p>Begrunnelsen for søknaden om plass på kurset er:</p><p>{$a->arguments}</p>
                           </br>
                           <p>Kursinformasjon:</p>
                           <ul>
                                <li><u>Kursdato</u>: {$a->date}</li>
                                <li><u>Sted</u>: {$a->location}</li>
                                <li><u>Intern pris: {$a->internal}</u></li>
                                <li><u>Ekstern pris: {$a->external}</u></li>
                                <li>Mer informasjon om kurset kan du få på kursets hjemmeside: {$a->homepage}</li>
                           </ul>
                           </br>
                           <p>For å godkjenne denne søknaden kan du klikke her: {$a->approve}.</p>
                           <p>For å avslå denne søknaden kan du klikke her: {$a->reject}.</p><p>Dette er en automatisk generert e-post fra {$a->site} og du kan ikke svare på e-posten.</p>';


$string['subject_reminder'] = '{$a->site}: Søknad om påmelding til kurset: {$a->course}. PÅMINNELSE';
$string['body_reminder']    = '<p>Vi vil bare minne deg om at du er lederen for <b>{$a->user}</b>, som hører til følgende arbeidssted: </p>
                              {$a->companies_user}, nylig har søkt om plass på kurset: <b>{$a->course}</b>.</p><p>Brukerens begrunnelse for søknaden er:</p><p>{$a->arguments}</p>
                               </br>
                               <p>Kursinformasjon:</p>
                               <ul>
                                    <li><u>Kursdato</u>: {$a->date}</li>
                                    <li><u>Sted</u>: {$a->location}</li>
                                    <li><u>Intern pris: {$a->internal}</u></li>
                                    <li><u>Ekstern pris: {$a->external}</u></li>
                                    <li>Mer informasjon om kurset kan du få på kursets hjemmeside: {$a->homepage}</li>
                               </ul>
                               </br>
                               <p>Du bør ta stilling til denne søknaden så raskt som mulig.</p>
                               <p>For å godkjenne denne søknaden kan du klikke her:  {$a->approve}.</p>
                               <p>For å avslå denne søknaden kan du klikke her:  {$a->reject}.</p><p>Dette er en automatisk generert e-post fra {$a->site} og du kan ikke svare på e-posten.</p>';


$string['std_body']     = 'Søknaden din om plass på kurset er sendt din leder for behandling. Vi sender deg en epost med varsel om resultatet av behandlingen fra lederen din. <p>Dette er en automatisk generert e-post fra {$a->site} og du kan ikke svare på e-posten.</p>';

$string['approve_lnk']  = 'Godkjenn søknaden';
$string['reject_lnk']   = 'Avslå søknaden';

$string['request_sent']         = 'Søknaden din om plass på kurset er sendt lederen din for behandling. Vi sender deg en epost når søknaden din er behandlet.';
$string['request_remainder']    = 'Søknaden din ble innsendt <b>{$a}</b>. Den er ennå ikke behandlet av lederen din. Ønsker du å sende en påminnelse til lederen din?';

$string['err_link'] = 'OOPS - søknaden er allerede behandlet. Lenken du klikket på kan bare benyttes en gang.';

$string['request_approved']       = '<p>Din søknad om kurset {$a->homepage} ble godkjent av lederen din {$a->sent}.<p><p>Dette er en automatisk generert e-post fra {$a->site} og du kan ikke svare på e-posten.</p>';
$string['request_rejected']       = '<p>Din søknad om kurset {$a->homepage} ble avslått av lederen din {$a->sent}.</p>
                                    <p>Om søknaden ble avslått ved en feil, bør du ta kontakt med lederen din og søke på nytt.</p><p>(Dette er en automatisk generert e-post fra {$a->site} og du kan ikke svare på e-posten.)</p>';
$string['request_rejected_enrol'] = '<p>Din søknad om plass på kurset {$a->homepage} ble avslått av lederen din {$a->sent}.</p>
                                <p>(Dette er en automatisk generert e-post fra {$a->site} og du kan ikke svare på e-posten.)</p>';

$string['approved_mnd'] = 'Søknaden om plass på kurset: {$a->homepage} for brukeren: <b>{$a->user}</b> er nå godkjent.';
$string['rejected_mnd'] = 'Søknaden om plass på kurset: {$a->homepage} for brukeren: <b>{$a->user}</b> er nå avslått.';

$string['err_process']  = 'Beklager, men det har skjedd en feil under behandlingen. Vennligst prøv igjen senere eller kontakt administrator.';

$string['no_request']     = 'Det foreligger ingen søknader om kursplass';
$string['act_approve']    = 'Godkjenn';
$string['act_reject']     = 'Avslå';

$string['rpt_name']         = 'Navn';
$string['rpt_username']     = 'Brukernavn';
$string['rpt_mail']         = 'Epost';
$string['rpt_arguments']    = 'Begrunnelse';
$string['rpt_seats']        = 'Plasser';
$string['rpt_action']       = 'Handling';
$string['rpt_attended']     = 'Deltatt';
$string['rpt_not_attended'] = 'Ikke deltatt';
$string['rpt_approved']     = 'Godkjent';
$string['rpt_rejected']     = 'Avslått';
$string['rpt_participants'] = 'Maks antall deltakere';
$string['rpt_back']         = 'Tilbake';

$string['mng_approved_subject']  = '{$a->site}: Søknad om plass på kurset: {$a->course}';
$string['mng_approved_body_one'] = '<p>Vi sender deg denne bekreftelsen fordi du er oppført som leder for følgende arbeidssted(er): </p>';
$string['mng_approved_body_two'] = '<p>Vi vil gjerne informere deg om at brukeren <b>{$a->user}</b>, som hører til følgende arbeidssted(er): </p>
                                    {$a->companies_user}
                                    <p> nettopp er blitt påmeldt kurset: <b>{$a->course}</b>.</p>
                                    <p>Kursinformasjon:</p>
                                    <ul>
                                        <li><u>Kursdato</u>: {$a->date}</li>
                                        <li><u>Sted</u>: {$a->location}</li>
                                        <li><u>Intern pris: {$a->internal}</u></li>
                                        <li><u>Ekstern pris: {$a->external}</u></li>
                                        <li>Mer informasjon om kurset kan du få på kursets hjemmeside: {$a->homepage}</li>
                                    </ul>';

$string['mng_approved_body_end'] = '<p>Dette er en automatisk generert epost fra {$a->site} og du kan ikke svare på eposten.';
$string['home_page']    = 'Hjemmesider';

$string['approval_occupied'] = 'Det er i øyeblikket ingen ledige plasser. Søknaden om kursplass vil derfor bli behandlet straks det er ledige plasser på kurset.';

$string['price'] = 'Pris';

$string['in_price']     = 'Intern pris';
$string['ext_price']    = 'Ekstern pris';
$string['ical_path']    = 'iCal mappe';

$string['company_sel']     = 'Arbeidssted';
$string['users_connected'] = 'Tilkoblede brukere';
$string['no_competence']   = 'Beklager, men du kan ikke melde deg på dette kurset når du ikke har oppgitt arbeidssted i din egen profil. Vennligst oppdater profilen din før du prøver igjen.  <p>Du kan oppdatere profilen din med et arbeidssted ved å klikke her: <strong>{$a}</strong></p>';

$string['company_demanded']        = 'Ikke krev arbeidssted ved påmelding';
$string['company_demanded_manual'] = 'Arbeidssted ikke påkrevd for påmelding. Brukere uten arbeidssted i egen profil kan dermed melde seg på.';

$string['find_resource_number'] = " Finn ressursnummer";
$string['no_users_invoice']     = " Ingen matchende brukere";
$string['users_matching']       = " Matchende brukere";
$string['please_use_filter']    = " Vennligst bruk filteret";

$string['unenrol_link']         = '<p>Hvis du ønsker å melde deg av kurset, vennligst klikk på følgende lenke <strong>{$a}</strong></p>';
$string['unenrol_me']           = 'Meld meg ut';
$string['user_unenrolled']      = 'Du har blitt meldt ut av kurset';
$string['user_not_enrolled']    = 'Beklager, du kan ikke melde deg ut fordi du ikke er meldt på kurset.';

$string['unenrol_subject'] = 'Avmeldingsbekreftelse for kurset {$a}.';
$string['unenrol_body']    = 'Dette er en bekreftelse på at du er blitt utmeldt fra kurset <strong>{$a}</strong>. 
	
	Dette er en automatisk generert e-post fra {$a->site} og du kan ikke svare på e-posten.';

$string['rpt_workplace']   = 'Arbeidssted';

$string['msg_teacher']      = '{$a->site}: Instruktør i kurset {$a->course}';
$string['body_teacher']     = '<p>Vi ønsker å informere deg om at du har blitt satt opp som instruktør i kurset {$a->course}</p>
                               </br>
                               <p>Dette er en automatisk generert epost fra {$a->site} og du kan ikke svare på denne eposten.</p>';
$string['body_unteacher']   = '<p>Vi ønsker å informere deg om at du er fjernet som instruktør i kurset {$a->course}</p>
                               </br>
                               <p>Dette er en automatisk generert epost fra {$a->site} og du kan ikke svare på denne eposten.</p>';

$string['msg_instructor']   = '{$a->site}: Kursansvarlig for kurset {$a->course}';
$string['body_instructor']  = '<p>Vi ønsker å informere deg om at du er satt opp som kursansvarlig for kurset {$a->course}</p>
                               </br>
                               <p>Dette er en automatisk generert epost fra {$a->site} og du kan ikke svare på denne eposten.</p>';
$string['body_uninstructor'] = '<p>Vi ønsker å informere deg om at du er fjernet som kursansvarlig for kurset {$a->course}</p>
                                </br>
                                <p>Dette er en automatisk generert epost fra {$a->site} og du kan ikke svare på denne eposten.</p>';

$string['rpt_by']           = 'By';
$string['rpt_when']         = 'When';


$string['confirm_approve'] = 'Are you sure that you want to approve the request for <strong>{$a->user}</strong> user and <strong>{$a->course}</strong> course?' ;
$string['confirm_reject'] = ' Are you sure that you want to reject the request for <strong>{$a->user}</strong> user and <strong>{$a->course}</strong> course?';