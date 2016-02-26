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

$string['msg_subject_manager']  = '{$a->site}: Notification new employee in {$a->company}';
$string['msg_body_manager']     = '<p>We send you this notification, because of you are set as manager for the company <b>{$a->company}</b></p>
                                   <p>We would like to inform you that the user <b>{$a->user}</b> is a new employee.</p>
                                   </br>
                                   <p>If the user does not belong to your company, you must reject it by this link {$a->reject}. </p>
                                   </br></br>
                                   <p>This is an automatic generated email from {$a->site} and you cannot answer this email.</p>';

$string['msg_subject_rejected'] = '{$a->site}: Notification from {$a->company}';
$string['msg_body_rejected']    = 'We would like to inform you that your membership to <strong>{$a->company}</strong> has been rejected';

$string['err_link'] = 'Sorry, link not valid. Please, contact with administrator. ';

$string['err_process']  = 'Sorry, It has been an error during the process. Please, try it later or contact with administrator.';

$string['request_rejected'] = 'The request for the company <strong>{$a->company}</strong> and for the user <strong>{$a->user}</strong> has been rejected successfully.';

$string['request_just_rejected'] = 'The request for the company <strong>{$a->company}</strong> and for the user <strong>{$a->user}</strong> has already been rejected.';