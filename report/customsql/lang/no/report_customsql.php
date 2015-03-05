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
 * Local language pack from http://amj.local/dvm
 *
 * @package    report
 * @subpackage customsql
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addreport'] = 'Legg til ny spørring';
$string['anyonewhocanveiwthisreport'] = 'Alle som kan se denne rapporten (report/courseoverview:view)';
$string['archivedversions'] = 'Lagrede versjoner av denne spørringen';
$string['at'] = 'ved';
$string['automaticallymonthly'] = 'Kjøres første dag hver måned';
$string['automaticallyweekly'] = 'Kjøres første dag hver uke';
$string['availablereports'] = 'Databasespørringer ved behov';
$string['availableto'] = 'Tilgjengelig til {$a}.';
$string['backtoreportlist'] = 'Tilbake til spørringsoversikten';
$string['changetheparameters'] = 'Endre parametrene';
$string['customsql:definequeries'] = 'Sett opp egendefinerte spørringer';
$string['customsql:view'] = 'Vis egendefinerte spørringer';
$string['daily'] = 'Tidfestet, daglig';
$string['dailynote'] = 'Denne spørringen vil kjøres daglig når du klikker på lenken for å se resultatene.';
$string['dailyqueries'] = 'Daglige spørringer';
$string['deleteareyousure'] = 'Er du sikker på at du vil slette denne spørringen?';
$string['deletethisreport'] = 'Slett denne spørringen';
$string['description'] = 'Beskrivelse';
$string['displayname'] = 'Navn på spørringen';
$string['displaynamerequired'] = 'Du må angi et navn på spørringen';
$string['displaynamex'] = 'Navn på spørring: {$a}';
$string['downloadthisreportascsv'] = 'Last ned disse resultatene som CSV';
$string['editingareport'] = 'Endre en ad-hoc databasespørring';
$string['editthisreport'] = 'Endre denne spørringen';
$string['emailbody'] = 'Kjære {$a}';
$string['emailink'] = 'Klikk på denne lenken for å få tilgang til rapporten: {$a}';
$string['emailnumberofrows'] = 'Bare antall rader og lenken';
$string['emailresults'] = 'Legg inn resultater i epost-teksten';
$string['emailrow'] = 'Rapporten returnerte {$a} rader.';
$string['emailrows'] = 'Rapporten returnerte {$a} rader.';
$string['emailsent'] = 'Det er nå sendt en e-postmelding til {$a}';
$string['emailsentfailed'] = 'Epost kan ikke sendes til {$a}';
$string['emailsubject'] = 'Spørring {$a}';
$string['emailto'] = 'Send epost automatisk til';
$string['emailwhat'] = 'Hva som skal sendes på epost';
$string['enterparameters'] = 'Skriv inn parametre for ad hoc database-spørringen';
$string['errordeletingreport'] = 'Feil ved sletting av spørring.';
$string['errorinsertingreport'] = 'Feil ved opprettelse av spørring.';
$string['errorupdatingreport'] = 'Feil ved oppdatering av spørring.';
$string['invalidreportid'] = 'Ugyldig spørringsID {$a}.';
$string['lastexecuted'] = 'Denne spørringen ble sist kjørt $a->lastrun. Kjøringen tok {$a->lastexecutiontime}.';
$string['manually'] = 'Ved behov';
$string['manualnote'] = 'Disse spørringene kan kjøres ved behov. Spørringer merket -RG- er for rapportgeneratoren.';
$string['messageprovider:notification'] = 'Egendefinerte SQL-rapportmeldinger og varsler';
$string['morethanonerowreturned'] = 'Mer enn en rad ble returnert. Denne spørringen skal bare returnere en rad.';
$string['nodatareturned'] = 'Denne spørringen returnerte ingen data.';
$string['noexplicitprefix'] = 'Vennligst ikke benytt tabellprefix i <tt>{$a}</tt> i SQL-koden. I stedet kan du bruke tabeller uten prefiks innenfor <tt>{}</tt>tegn.';
$string['noreportsavailable'] = 'Ingen spørringer er tilgjengelig';
$string['norowsreturned'] = 'Ingen rader ble returnert. Denne spørringen skal returnere en rad.';
$string['noscheduleifplaceholders'] = 'Spørringer som inneholder plassholdere kan bare kjøres manuelt.';
$string['nosemicolon'] = 'Tegnet ; er ikke tillatt i SQL-koden.';
$string['notallowedwords'] = 'Ordene {$a} er ikke tillatt i SQL-koden.';
$string['note'] = 'Notater';
$string['notrunyet'] = 'Denne spørringen er ikke kjørt ennå.';
$string['onerow'] = 'Spørringen returnerer en rad og akkumulerer en rad om gangen';
$string['parametervalue'] = '{$a->name}: {$a->value}';
$string['pluginname'] = 'Ad hoc database-spørringer';
$string['queryfailed'] = 'Feil ved kjøring av spørringen: {$a}';
$string['querylimit'] = 'Begrens antall returnerte rader';
$string['querylimitrange'] = 'Tallet må være mellom 1 og {$a}';
$string['querynote'] = '<ul> <li>The token <tt>%%WWWROOT%%</tt> in the results will be replaced with <tt>{$a}</tt>.</li> <li>Any value in the output that looks like a URL will automatically be made into a link.</li> <li>If a column name in the results ends with the characters <tt>date</tt>, and the column contains integer values, then they will be treated as Unix time-stamps, and automatically converted to human-readable dates.</li> <li>The token <tt>%%USERID%%</tt> in the query will be replaced with the user id of the user viewing the report, before the report is executed.</li> <li>For scheduled reports, the tokens <tt>%%STARTTIME%%</tt> and <tt>%%ENDTIME%%</tt> are replaced by the Unix timestamp at the start and end of the reporting week/month in the query before it is executed.</li> <li>You can put parameters into the SQL using named placeholders, for example <tt>:parameter_name</tt>. Then, when the report is run, the user can enter values for the parameters to use when running the query.</li> <li>If the <tt>:parameter_name</tt> starts or ends with the characters <tt>date</tt> then a date-time selector will be used to input that value, otherwise a plain text-box will be used.</li> <li>You cannot use the characters <tt>:</tt> or <tt>?</tt> in strings in your query. If you need them, you can use <tt>CHR(58)</tt> and <tt>CHR(63)</tt> respectively, along with string concatenation. (It is <tt>CHR</tt> for Postgres or Oracle, <tt>CHAR</tt> for MySQL or SQL server.)</li> </ul>';
$string['queryparameters'] = 'Spørringsparametre';
$string['queryparams'] = 'Vennligst skriv inn standardverdier for spørringsparametrene.';
$string['queryparamschanged'] = 'Plassholderne i spørringen er endret';
$string['queryrundate'] = 'spørring kjørt';
$string['querysql'] = 'SQL spørring';
$string['querysqlrequried'] = 'Du må legge inn noe SQL';
$string['recordlimitreached'] = 'Denne spørringen nådde grensen på {$a} rader. Noen rader på slutten kan derfor mangle.';
$string['reportfor'] = 'Spørring kjørt {$a}';
$string['requireint'] = 'Heltall påkrevd';
$string['runable'] = 'Kjør';
$string['runablex'] = 'Kjør: {$a}';
$string['schedulednote'] = 'Disse spørringene kjøres automatisk første dag i hver uke eller måned og rapporterer for forrige uke eller måned. Disse lenkene viser deg resultatene som allerede er akkumulert.';
$string['scheduledqueries'] = 'Tidfested spørringer';
$string['typeofresult'] = 'Resultattyper';
$string['unknowndownloadfile'] = 'Ukjent nedlastingsfil';
$string['userhasnothiscapability'] = 'Bruker \'{$a->username}\' har ingen \'{$a->capability}\' rettigheter. Vennligst slett denne brukeren far listen eller endre valgene i \'{$a->whocanaccess}\'.';
$string['userinvalidinput'] = 'Ugyldig inndata, en kommaseparert liste over brukernavn kreves.';
$string['usernotfound'] = 'Bruker med brukernavn \'{$a}\' eksisterer ikke';
$string['userswhocanconfig'] = 'Bare administratorer (moodle/site:config)';
$string['userswhocanviewsitereports'] = 'Brukere kan se systemrapporter (moodle/site:viewreports)';
$string['verifyqueryandupdate'] = 'Verifiser SQL-spørringsteksten og oppdater skjemaet.';
$string['whocanaccess'] = 'Hvem som har tilgang til denne spørringen';
