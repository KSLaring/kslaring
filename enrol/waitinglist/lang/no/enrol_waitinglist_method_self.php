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
$string['self_displayname'] = 'Egenpåmelding til arrangement';
$string['self_menutitle'] = 'Egenpåmelding til arrangement';
$string['waitlistmessage_self'] = 'Lagt til venteliste for: {$a}';
$string['waitlistmessagetitle_self'] = 'Lagt til venteliste for: {$a}';
$string['waitlistmessagetext_self'] = 'Du er nå lagt til i ventelisten for kurset: {$a->coursename}!

I øyeblikket er du nummer {$a->queueno} på ventelisten.

Du kan sjekke her hvor du er på ventelisten:  {$a->courseurl}';
$string['self_queuewarning_label'] ='Dette kurset er allerede fullt.';
$string['self_queuewarning'] = 'Dersom du fortsetter vil du bli plassert på ventelisten. Du vil automatisk bli varslet når det blir ledige plasser. 

	Du er nå nummer: {$a} på ventelisten.';

$string['cannot_unenrol_date']    = 'Beklager, du kan ikke melde deg av kurset etter avmeldingsfristen';
$string['unenrolenddate']         = 'Avmeldingsfrist';
$string['unenrolenddate_help']    = 'Hvis aktivert, er dette den første datoen deltakerne ikke lengre kan melde seg av kurset.';
$string['unenrolenddate_err']     = 'Avmeldingsfristen må settes til en senere dato enn dagens dato.';
