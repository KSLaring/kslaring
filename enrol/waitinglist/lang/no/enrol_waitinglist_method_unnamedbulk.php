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

$string['unnamedbulk_displayname'] = 'Bulk-påmelding til arrangement';
$string['unnamedbulk_menutitle'] = 'Reserver plasser';
$string['waitlistmessagetitle_unnamedbulk'] = 'Kursplasser er lagt til i ventelisten for: {$a}';
$string['waitlistmessagetitle_unnamedbulk_changed'] = 'Plasser lagt til i ventelisten for: {$a}. Endret plasser';
$string['waitlistmessagetext_unnamedbulk'] = 'Du har reservert {$a->queueseats} kursplasser for: {$a->coursename}. Det var dessverre ikke {$a->queueseats} ledige plasser på dette kurset, og plassene du ikke er tildelt er derfor satt opp på ventelisten.

Dine reserverte kursplasser er i øyeblikket nummer {$a->queueno} på ventelisten.

Du kan når som helst redigere reservasjonen din her: {$a->editenrolurl}

Ta derfor vare på denne eposten!

Dette er en automatisk generert e-post fra {$a->site} og du kan ikke svare på e-posten.';
$string['waitlistmessagetitleconfirmation_unnamedbulk'] = 'Du er tildelt plasser i kurset {$a}';
$string['waitlistmessagetitleconfirmation_unnamedbulk_changed'] = 'Bekreftelse på endring av ønsket antall plasser i kurset {$a}';
$string['sendconfirmmessage'] ='Send epost når kursplassene er bekreftet';
$string['sendconfirmmessage_help'] ='Når kursplasser blir ledige i kurset, og de er lagt til i køen for denne påmeldingsmetoden, og de har tilgang til å reservere kursplasser med denne metoden, send en epost til vedkommende som har reservert kursplassene på seg.';
$string['confirmedmessage_unnamedbulk'] = 'Plasser tildelt på: {$a->coursename}';
$string['confirmedmessagetext_unnamedbulk'] = 'Plasser reservert for: {$a->coursename}. {$a->allocatedseats} av dine forespurte {$a->totalseats} plasser på ventelisten for {$a->coursename} er tildelt deg. 

Du kan se og endre antall plasser ved å følge denne lenken: {$a->editenrolurl}

OBS! Har din kommune Single Sign On til KS Læring må du logge inn i KS Læring før du klikker på lenken over. Da unngår du å måtte logge inn manuelt via ID-porten.'
;
$string['customconfirmedmessage'] = 'Bekreftelse på din reservasjon';
$string['customconfirmedmessage_help'] = 'Du kan legge til en egendefinert bekreftelsesmelding i ren tekst eller i Moodles auto-format, inkludert html-og flerspråktagger. Meldingen vil bli sendt til den brukeren som har reservert plasser på ventelisten når det blir ledige plasser på kurset.

Følgende plassholdere kan benyttes i meldingen:

* Kursnavn {$a->coursename} 
* Plass på ventelisten {$a->queueno} 
* Totalt antall plasser {$a->totalseats} 
* Plasser på venteliste {$a->waitingseats} 
* Tildelte plasser {$a->allocatedseats} 
* Lenke til kurset {$a->courseurl} 
* Lenke til reservasjonssiden {$a->editenrolurl}';
$string['reserveseatcount'] = 'Ønsket antall kursplasser';
$string['reserveseats'] = 'Lagre endringer';
$string['unnamedbulk_enrolformintro'] = 'Bruk dette skjemaet til å reservere plasser på kurset. Du blir varslet via e-post når du er tildelt plasser. Du kan når som helst gå tilbake hit for å endre reservasjonen din.';
$string['unnamedbulk_enrolformqueuestatus'] = 'Du er tildelt {$a->assignedseats} og du har {$a->waitingseats} plasser på ventelisten.';
$string['unnamedbulk_enrolformqueuestatus_label'] = 'Din reservasjon';
$string['unnamedbulk_enrolformqueuestatus_all'] = 'Du har blitt tildelt alle dine forespurte plasser';
$string['no_seats'] = 'Du må reservere minst en plass';