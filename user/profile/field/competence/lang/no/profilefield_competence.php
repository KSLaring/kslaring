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
 * Extra Profile Field Competence - Language settings (Norwegian)
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * A new user profile which includes information about the companies and job roles connected with user
 *
 */

$string['pluginname']           = 'Brukers arbeidssteder og jobbroller';
$string['pluginname_help']      = 'Her finner du brukerens arbeidssteder og jobbroller';

$string['competence_profile']   = 'Arbeidsteder og jobbroller';

$string['profile_desc'] = 'Dine arbeidssteder og jobbroller.<br >Vennligst legg til minst ett arbeidssted med tilhørende jobbrolle. Klikk på "Opprett ny" for å gjøre dette.';
$string['comptence_desc'] = 'Your information about your companies and job roles';
$string['lnk_update']   = 'Endre mine arbeidssteder og jobbroller';
$string['lnk_edit']     = 'Rediger';
$string['lnk_delete']   = 'Slett';
$string['lnk_view']     = 'Arbeidssteder og jobbroller';

$string['my_companies'] = 'Arbeidssteder';
$string['my_job_roles'] = 'Jobbroller';
$string['jr_generics']  = 'Generiske jobbroller';

$string['lnk_add']      = 'Opprett ny';
$string['lnk_back']     = 'Tilbake til Min profil';
$string['lnk_startpage']    = 'Go to my startpage';

$string['delete_competence']            = 'Slett kompetanse';
$string['delete_competence_are_sure']   = '<p> Du kommer til å slette følgende fra din profil: </p>
                                                    <li>{$a->company}:</li>
                                                    <p> {$a->roles}</p>
                                           <p> Er du sikker?</p>';

$string['add_competence']    = 'Legg til nytt arbeidssted og jobbrolle';
$string['add_competence_desc']  = 'Her kan du legge til arbeidssteder og jobbroller som du har.';
$string['btn_add']           = 'Legg til';

$string['edit_competence']      = 'Rediger kompetanser';
$string['btn_save']             = 'Lagre';
$string['edit_competence_desc'] = 'Her kan du oppdatere dine arbeidssteder med jobbroller';

$string['level_generic']        = 'Generiske';

$string['btn_edit_users']       = 'Rediger arbeidssted';

$string['manager']  = 'Leder';
$string['reporter'] = 'Rapporttilgang';

$string['msg_subject_manager']  = '{$a->site}: Melding om ny medarbeider for arbeidsstedet: {$a->company}';
$string['msg_body_manager']     = '<p>Vi sender deg denne meldingen siden du er leder for dette arbeidsstedet: <b>{$a->company}</b></p>
                                   <p>Brukeren <b>{$a->user}</b> har nå lagt til seg selv som medarbeider ved <strong>{$a->employee}</strong>.</p>
                                   </br>
                                   <p>Hvis brukeren ikke hører til her kan du fjerne dette arbeidsstedet fra brukerens profil ved å klikke på linken: {$a->reject}. </p>
                                   </br></br>
                                   <p>Dette er en automatisk generert epost fra {$a->site}. Du kan ikke svare på denne e-posten.</p>';

$string['msg_subject_rejected'] = '{$a->site}: Melding fra {$a->company}';
$string['msg_body_rejected']    = 'Vi vil gjerne informere deg om at arbeidsstedet <strong>{$a->company}</strong> du la til i din brukerprofil er blitt fjernet av lederen for dette arbeidsstedet. Ta kontakt med lederen for arbeidsstedet dersom du mener at dette er feil.';
$string['msg_body_approved']    = 'Vi vil gjerne informere deg om at arbeidsstedet <strong>{$a->company}</strong>, som du la til i egen proil, er godkjent.';

$string['msg_boy_reverted']     = '<p>Vi sender deg denne meldingen fordi du er oppført som leder for arbeidsstedet:  <strong>{$a->company}</strong>.</p>
                                   <p><strong>{$a->user}</strong> har lagt til arbeidsstedet i sin profil (hvor du er leder).</p>
                                   </br>
                                   <p>Hvis du ønsker tilbakestille dette, f.eks. hvis du ser at brukeren må ha lagt seg til ved en misforståelse eller annet, kan du klikke på lenken for å fjerne arbeiddstedet igjen fra denne brukerens profil: {$a->revert}</p>';

$string['err_link'] = 'Beklager, men lenken er ikke gyldig. Vennligst ta kontakt med administrator.';

$string['reject_lnk']   = 'Avslå';
$string['approve_lnk']  = 'Godkjenn';

$string['err_process']  = 'Beklager, men det har skjedd en feil i prosessen. Vennligst prøv igjen senere eller kontakt administrator om det vedvarer.';

$string['request_rejected'] = 'Arbeidsstedet <strong>{$a->company}</strong> brukeren <strong>{$a->user}</strong> registrerte på seg selv er nå fjernet.';
$string['request_approved'] = 'Forespørselen for arbeidsstedet <strong>{$a->company}</strong> og for brukeren  <strong>{$a->user}</strong> er nå godkjent.';


$string['request_just_rejected'] = 'Du har allerede avslått at <strong>{$a->company}</strong> skal registreres på  <strong>{$a->user}</strong>.';
$string['request_just_approved'] = 'Forespørselen for arbeidsstedet <strong>{$a->company}</strong> og for brukeren <strong>{$a->user}</strong> er allerede godkjent.';

$string['alert_approve'] = 'Vennligst vær oppmerksom på at du legger til korrekt arbeidssted på deg selv. Lederen for valgt arbeidssted blir automatisk varslet og kan avslå at du legger til arbeidsstedet hvis dette er feil.';

$string['comp_delete'] = 'Denne brukeren er allerede fjernet fra dette arbeidsstedet.';

$string['confirm_reject'] = ' Are you sure that you want to reject <strong>{$a->user}</strong> employee for the company <strong>{$a->company}</strong>?';

$string['msgsbj_rejected_manager']  = '{$a->site}: Notification employee rejected in {$a->company}';
$string['msgbody_rejeted_manager']  = '<p>We send you this notification, because of you are set as manager for the company <strong>{$a->company}</strong></p>
			                           <p>We would like to inform you that the user <strong>{$a->user}</strong> has been rejected for <strong>{$a->company}</strong>.</p>
			                           </br></br>
			                           <p>This is an automatic generated email from {$a->site} and you cannot answer this email.</p>';