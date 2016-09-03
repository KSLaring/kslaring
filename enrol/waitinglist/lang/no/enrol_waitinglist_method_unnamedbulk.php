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

$string['unnamedbulk_displayname'] = 'Bulkpåmelding';
$string['unnamedbulk_menutitle'] = 'Reserver kursplasser for deg selv og andre';
$string['waitlistmessagetitle_unnamedbulk'] = 'Kursplasser er lagt til i ventelisten for: {$a}';
$string['waitlistmessagetitle_unnamedbulk_changed'] = 'Seats added to Waitlist for: {$a}. Changed seats';
$string['waitlistmessagetext_unnamedbulk'] = 'Du har reservert {$a->queueseats} kursplasser for: {$a->coursename}. Det var dessverre ikke {$a->queueseats} ledige plasser på dette kurset, og plassene du ikke er tildelt er derfor satt opp på ventelisten.

Dine reserverte kursplasser er i øyeblikket nummer {$a->queueno} på ventelisten.

Du kan når som helst redigere reservasjonen din her: {$a->editenrolurl}

Ta derfor vare på denne eposten!';
$string['waitlistmessagetitleconfirmation_unnamedbulk'] = 'Bekreftede bulk kursplasser for kurset: {$a}';
$string['waitlistmessagetitleconfirmation_unnamedbulk_changed'] = 'Seats confirmed for course: {$a}. . Changed seats\'';
$string['sendconfirmmessage'] ='Send epost når kursplassene er bekreftet';
$string['sendconfirmmessage_help'] ='Når kursplasser blir ledige i kurset, og de er lagt til i køen for denne påmeldingsmetoden, og de har tilgang til å reservere kursplasser med denne metoden, send en epost til vedkommende som har reservert kursplassene på seg.';
$string['confirmedmessage_unnamedbulk'] = 'Tildelte kursplasser for: {$a->coursename}';
$string['confirmedmessagetext_unnamedbulk'] = 'Tildelte kursplasser for: {$a->coursename}. Du har blitt tildelt {$a->allocatedseats} av dine {$a->queueseats} forespurte kursplasser for {$a->coursename}.

 Du kan se og endre antall plasser ved å følge denne lenken: {$a->editenrolurl}';
$string['customconfirmedmessage'] = 'Melding ved bekreftet kursplass';
$string['customconfirmedmessage_help'] = 'En egendefinert melding kan legges til som ren tekst eller i Moodles auto-format, inkludert HTML-tagger og flerspråk-tagger.

Meldingen vil bli sent brukeren som er ansvarlig for reservasjonen av kursplassene, når det blir ledige plasser på kurset.

Følgende plassholdere kan brukes i meldingen:

* Kursets navn {$a->coursename}
* Plasseringen på ventelisten {$a->queueno}
* Antall plasser i ventelisten {$a->queueseats}
* Bekreftede bulk kursplasser {$a->allocatedseats}
* Link til kurset {$a->courseurl}
* Link til påmeldingssiden {$a->editenrolurl}';
$string['reserveseatcount'] = 'Ønsket antall kursplasser';
$string['reserveseats'] = 'Lagre endringer';
$string['unnamedbulk_enrolformintro'] = 'Bruk dette skjemaet til å reservere/endre reserverte kursplasser på dette kurset. Du vil bli varslet straks plassene er bekreftet. Du kan når som helst gå tilbake hit for å endre antall kursplasser.';
$string['unnamedbulk_enrolformqueuestatus'] = 'Du  er tildelt {$a->assignedseats} og du har {$a->waitingseats} plasser på ventelisten.';
$string['unnamedbulk_enrolformqueuestatus_label'] = 'Din reservasjon';
$string['unnamedbulk_enrolformqueuestatus_all'] = 'Du har blitt tildelt alle dine forespurte plasser';