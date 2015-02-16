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
$string['unnamedbulk_menutitle'] = 'Reserver kursplasser';
$string['waitlistmessagetitle_unnamedbulk'] = 'Kursplasser er lagt til i ventelisten for: {$a}';
$string['waitlistmessagetext_unnamedbulk'] = '{$a->queueseats} kursplasser er lagt til i ventelisten for: {$a->coursename}

Dine reserverte kursplasser er i øyeblikket nummer {$a->queueno} på ventelisten.

Du kan når som helst redigere reservasjonen din her: {$a->editenrolurl}';
$string['waitlistmessagetitleconfirmation_unnamedbulk'] = 'Bekreftede kursplasser for kurset: {$a}';
$string['sendconfirmmessage'] ='Send epost når kursplassene er bekreftet';
$string['sendconfirmmessage_help'] ='Når kursplasser blir ledige i kurset, og de er lagt til i køen for denne påmeldingsmetoden, og de har tilgang til å reservere kursplasser med denne metoden, send en epost til vedkommende som har reservert kursplassene på seg.';
$string['confirmedmessage_unnamedbulk'] = 'Kursplasser reservert for: {$a->coursename}';
$string['confirmedmessagetext_unnamedbulk'] = 'Kursplasser reservert for: {$a->coursename}

{$a->allocatedseats} av dine {$a->queueseats} reserverte kursplasser på ventelisten for kurset {$a->coursename} er nå blitt bekreftet.

Vennlist meld brukerne inn i kurset og juster antall kursplasser tilsvarende her: {$a->editenrolurl}';
$string['customconfirmedmessage'] = 'Melding ved bekreftet kursplass';
$string['customconfirmedmessage_help'] = 'En egendefinert melding kan legges til som ren tekst eller i Moodles auto-format, inkludert HTML-tagger og flerspråk-tagger.

Meldingen vil bli sent brukeren som er ansvarlig for reservasjonen av kursplassene, når det blir ledige plasser på kurset.

Følgende plassholdere kan brukes i meldingen:

* Kursets navn {$a->coursename}
* Plasseringen på ventelisten {$a->queueno}
* Antall plasser i ventelisten {$a->queueseats}
* Bekreftede plasser {$a->allocatedseats}
* Link til kurset {$a->courseurl}
* Link til påmeldingssiden {$a->editenrolurl}';
$string['reserveseatcount'] = 'Antall kursplasser som kan reserveres';
$string['reserveseats'] = 'Reservere kursplasser på kurset';
$string['unnamedbulk_enrolformintro'] = 'Bruk dette skjemaet til å reservere kursplasser på dette kurset. Du vil bli varslet straks plassene er bekreftet. Når du har meldt inn brukerne kan du gå tilbake her for å justere ned antall kursplasser.';
$string['unnamedbulk_enrolformqueuestatus'] = 'Du har søkt om å reservere {$a->seats} kursplasser. Du er blitt tildelt {$a->assignedseats} kursplasser. 

Dine reservasjoner er i øyeblikket nummer {$a->queueposition} på ventelisten.';
$string['unnamedbulk_enrolformqueuestatus_label'] = 'Gjeldende reservasjon';