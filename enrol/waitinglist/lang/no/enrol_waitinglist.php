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

 
$string['alterstatus'] = 'Endre status';
$string['altertimeend'] = 'Endre deadline';
$string['altertimestart'] = 'Endre starttidspunkt';
$string['assignrole'] = 'Tildel rolle';
$string['confirmbulkdeleteenrolment'] = 'Er du sikker på at du vil slette disse brukerpåmeldingene?';
$string['confirmedseatsheader'] = 'Bekreftet';
$string['requestedseatsheader'] = 'Forespurte';
$string['defaultperiod'] = 'Standard påmeldngsvarighet';
$string['defaultperiod_desc'] = 'Standard varighet for påmeldingens gyldighet. Dersom -null-, vil påmeldingsvarigheten være ubegrenset.';
$string['defaultperiod_help'] = 'Standar varighet for hvor lenge en påmelding er gyldig, og starter fra kursdeltakeren er innmeldt. Dersom deaktivert vil innmeldingsvarigheten være ubegrenset.';
$string['deleteselectedusers'] = 'Slett valge brukerpåmeldinger';
$string['editselectedusers'] = 'Endre valge brukerpåmeldinger';
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

Dersom du trenger hjelp, vennligst kontakt: {$a->enroller}.';
$string['waitinglist:config'] = 'Administrere instanser av ventelister';
$string['waitinglist:enrol'] = 'Innmelding av kursdeltakere';
$string['waitinglist:manage'] = 'Administrere brukerpåmeldinger';
$string['waitinglist:unenrol'] = 'Meld ut brukere fra kurset';
$string['waitinglist:unenrolself'] = 'Meld meg ut av dette kurset';
$string['messageprovider:expiry_notification'] = 'Din kurspåmelding er i ferd med å utløpe';
$string['pluginname'] = 'Ventelistepåmeldinger';
$string['pluginname_desc'] = 'Ventelistemodulen har en venteliste for påmelding til et kurs.';
$string['status'] = 'Aktiver påmelding med venteliste';
$string['status_desc'] = 'Tillat kurstilgang fra internt påmeldte brukere. Dette bør være aktivert i de fleste tilfeller.';
$string['status_help'] = 'Denne innstillingen bestemmer hvorvidt brukere kan meldt på via påmelding med venteliste, via en link i kursadministrasjonsinnstillingene, og av en bruker med tillatelse til dette, f.eks. en lærer.';
$string['statusenabled'] = 'Aktivert';
$string['statusdisabled'] = 'Deaktivert';
/*
$string['unenrol'] = 'Unenrol user';
$string['unenrolselectedusers'] = 'Unenrol selected users';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['unenroluser'] = 'Do you really want to unenrol "{$a->user}" from course "{$a->course}"?';
$string['unenrolusers'] = 'Unenrol users';
*/
$string['wscannotenrol'] = 'Ventelisteinstansen kan ikke melde brukeren inn i kurset med id: {$a->courseid}';
$string['wsnoinstance'] = 'Ventelisteinstansen eksisterer ikke eller er deaktivert for kurset (id = {$a->courseid})';
$string['wsusercannotassign'] = 'Du har ikke tillatelse til å tildele ({$a->roleid}) rollen til denne brukeren: ({$a->userid}) i kurset ({$a->courseid}).';

$string['cutoffdate'] = 'Frist for påmelding';
$string['maxenrolments'] = 'Maks antall påmeldinger';
$string['waitlistsize'] = 'Maks størrelse på ventelisten';
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
$string['seatsheader'] = 'Ekstra plasser';
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
$string['welcometocourse'] = 'Velkommen til {$a}';
$string['welcometocoursetext'] = 'Velkommen til {$a->coursename}!

Hvis du ikke allerede har gjort det, bør du redigere brukerprofilen din slik at vi kan bli bedre kjent med deg:
  {$a->profileurl}';
$string['customwaitlistmessage'] = 'Egendefinert melding for påmelding med venteliste';
$string['customwaitlistmessage_help'] = 'Du kan legge til en egendefinert melding som ren tekst eller i Moodles auto-format, inkludert HTML-tagger og flerspråktagger.

Følgende plassholdere kan legges inn i meldingen:

* Kursnavn {$a->coursename}
* Plass på ventelisten {$a->queueno}
* Antall plasser i ventelisten {$a->queueseats}
* Link ttil kurset {$a->courseurl}
* Link til påmeldingssiden {$a->editenrolurl}';
$string['sendcoursewaitlistmessage'] = 'Send epost når brukeren blir lagt til i ventelisten';
$string['sendcoursewaitlistmessage_help'] = 'Du kan sende en epost til brukeren når de legges til på kursets venteliste.';

$string['manageconfirmed'] = 'Bekreftede bulk kursplasser';
$string['unconfirmfailed'] = 'Fjerning fra bekreftede kursplasser mislykkes!';
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




