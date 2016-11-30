<?php
/**
 * Extra Profile Field Competence - Language settings (Norwegian)
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * A new user profile which includes information about the companies and job roles connected with user
 *
 */

$string['pluginname']           = 'Brukers kompetanseprofil';
$string['pluginname_help']      = 'Brukerens kompetanseprofil inneholder informasjon om arbeidssted og jobbroller.';

$string['competence_profile']   = 'Kompetanseprofil';

$string['profile_desc'] = 'Dine arbeidssteder og jobbroller.<br >Vennligst legg til minst ett arbeidssted med tilhørende jobbrolle. Klikk på "Opprett ny" for å gjøre dette.';
$string['lnk_update']   = 'Oppdater mine arbeidssteder og jobbroller';
$string['lnk_edit']     = 'Rediger';
$string['lnk_delete']   = 'Slett';
$string['lnk_view']     = 'Vis mine kompetanser';

$string['my_companies'] = 'Arbeidssteder';
$string['my_job_roles'] = 'Jobbroller';
$string['jr_generics']  = 'Generiske jobbroller';

$string['lnk_add']      = 'Opprett ny';
$string['lnk_back']     = 'Tilbake til Min profil';

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
$string['edit_competence_desc'] = 'Her kan du oppdatere kompetanseprofilen din.';

$string['level_generic']        = 'Generiske';

$string['btn_edit_users']       = 'Rediger arbeidssted';

$string['manager']  = 'Leder';
$string['reporter'] = 'Rapporttilgang';

$string['msg_subject_manager']  = '{$a->site}: Melding om ny medarbeider for arbeidsstedet: {$a->company}';
$string['msg_body_manager']     = '<p>Vi sender deg denne meldingen siden du er leder for dette arbeidsstedet: <b>{$a->company}</b></p>
                                   <p>Brukeren <b>{$a->user}</b> har nå lagt til seg selv som en av <strong>{$a->employee}</strong> dine medarbeidere.</p>
                                   </br>
                                   <p>Hvis brukeren ikke hører til på ditt arbeidssted kan du fjerne dette arbeidsstedet fra brukerens profil ved å klikke her: {$a->reject}. </p>
                                   </br></br>
                                   <p>Dette er en automatisk generert epost fra {$a->site}. Du kan ikke svare på denne e-posten.</p>';

$string['msg_subject_rejected'] = '{$a->site}: Melding fra {$a->company}';
$string['msg_body_rejected']    = 'Vi vil gjerne informere deg om at arbeidsstedet <strong>{$a->company}</strong> du la til i din brukerprofil er blitt fjernet av lederen for dette arbeidsstedet. Ta kontakt med lederen for arbeidsstedet dersom du mener at dette er feil.';
$string['msg_body_approved']    = 'We would like to inform you that your membership to <strong>{$a->company}</strong> has been approved';

$string['msg_boy_reverted']     = '<p>We send you this notification, because of you are set as manager for the company <strong>{$a->company}</strong></p>
                                   <p>We would like to inform you that you have just rejected the membership for the user <strong>{$a->user}</strong>.</p>
                                   </br>
                                   <p>If you would like to revert this situation, because of a mistake or other reason, please click on this link {$a->revert}</p>';

$string['err_link'] = 'Beklager, men lenken er ikke gyldig. Vennligst ta kontakt med administrator.';

$string['reject_lnk']   = 'Reject';
$string['approve_lnk']  = 'Approve';

$string['err_process']  = 'Beklager, men det har skjedd en feil i prosessen. Vennligst prøv igjen senere eller kontakt administrator om det vedvarer.';

$string['request_rejected'] = 'Arbeidsstedet <strong>{$a->company}</strong> brukeren <strong>{$a->user}</strong> registrerte på seg selv er nå fjernet.';
$string['request_approved'] = 'The request for the company <strong>{$a->company}</strong> and for the user <strong>{$a->user}</strong> has been approved successfully.';


$string['request_just_rejected'] = 'Du har allerede avslått at <strong>{$a->company}</strong> skal registreres på  <strong>{$a->user}</strong>.';
$string['request_just_approved'] = 'The request for the company <strong>{$a->company}</strong> and for the user <strong>{$a->user}</strong> has already been approved.';

$string['alert_approve'] = 'Please be aware that you add yourself to the correct Company. The manager for this company can reject you  if your membership is wrong.';