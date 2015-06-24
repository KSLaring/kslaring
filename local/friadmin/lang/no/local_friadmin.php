<?php
/**
 * Local Fridadmin  - Language Settings (Norwegian)
 *
 * @package         local
 * @subpackage      fridamin/lang
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @author          eFaktor     (Urs Hunkler {@link urs.hunkler@unodo.de})
 *
 * @updateDate      16/06/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Integrate 'Course Locations' plugin into FriAdmin plugin
 */
$string['pluginname']           = 'Friadmin';

$string['courselist_title']     = 'Friadmin tilgjengelige kurs';
$string['coursetable_title']    = 'Kursoversikt';
$string['course_courseid']      = 'Kurs ID';
$string['course_name']          = 'Navn';
$string['course_summary']       = 'Beskrivelse';
$string['course_targetgroup']   = 'Målgruppe';
$string['course_date']          = 'Dato';
$string['course_time']          = 'Tid fra - til';
$string['course_seats']         = 'Tilgjengelige plasser';
$string['course_deadline']      = 'Påmeldingsfrist';
$string['course_length']        = 'Varighet';
$string['course_municipality']  = 'Kommune';
$string['course_sector']        = 'Sektor';
$string['course_location']      = 'Kurssted';
$string['course_responsible']   = 'Ansvarlig';
$string['course_teacher']       = 'Kurslærer';
$string['course_priceinternal'] = 'Internpris';
$string['course_priceexternal'] = 'Eksternpris';
$string['course_link']          = 'Lenke';
$string['course_edit']          = '';

$string['coursedetail_title']           = 'Friadmin kursdetaljer';
$string['coursedetail_back']            = 'Tilbake til kursoversikten';
$string['coursedetail_go']              = 'Gå til kurset';
$string['coursedetail_settings']        = 'Kursinnstillinger';
$string['coursedetail_completion']      = 'Kursfullføringer';
$string['coursedetail_statistics']      = 'Statistikk';
$string['coursedetail_users']           = 'Påmeldte kursdeltakere';
$string['coursedetail_confirmed']       = 'Administrere bekreftede';
$string['coursedetail_waitlist']        = 'Administrere venteliste';
$string['coursedetail_participantlist'] = 'Last ned deltakerliste';
$string['coursedetail_duplicate']       = 'Dupliser';
$string['coursedetail_email']           = 'Send epost';

$string['coursetemplate_title']         = 'Friadmin - Legg til fra kursmal';
$string['coursetemplate_subtitle']      = 'Opprett et kurs fra en kursmal.';
$string['coursetemplate_cat']           = 'Kurskategori for kursmaler';
$string['coursetemplate_cat_desc']      = 'Vennligst velg kurskategorien hvor alle kursmaler skal lagres.';
$string['coursetemplate_cat_select']    = 'Velg kurskategori for kursmaler ...';
$string['coursetemplate_go']            = 'Gå til kurset';
$string['coursetemplate_another']       = 'Opprett enda et kurs';
$string['coursetemplate_settings']      = 'Kursinnstillinger';
$string['coursetemplate_overview']      = 'Kursmaloversikt';
$string['coursetemplate_result']        = 'Kurset er opprettet -
id: <strong>{$a->id}</strong>, kortnavn: "<strong>{$a->shortname}</strong>",
Fullt navn: "<strong>{$a->fullname}</strong>".';
$string['coursetemplate_error']         = 'Kurset kunne ikke opprettes.';

$string['location']             = 'Sted: ';
$string['fromto']               = 'Fra - til: ';
$string['coursename']           = 'Kursnavn: ';
$string['selmunicipality']      = 'Søk overalt';
$string['selsector']            = 'Alle sektorer';
$string['sellocation']          = 'Alle kurssteder';
$string['selname']              = 'Kursnavn';
$string['selcategory']          = 'Lagres i kategorien';
$string['missingselcategory']   = 'Mangler målkategori';
$string['seltemplate']          = 'Kursmal';
$string['missingseltemplate']   = 'Mangler kursmal';
$string['selsubmit']            = 'Søk';
$string['selsubmitcreate']      = 'Opprett kurs';

$string['edit'] = 'Endre kurs';
$string['show'] = 'Vis detaljer';

$string['naddcourse']           = 'Opprett nytt kurs';
$string['my_categories']        = 'Your course categories';
$string['my_categories_help']   = 'This setting determines the category in which the course will appear in the list of courses.';
$string['info_new_course']      = 'Before starting to edit a new course, you must determine in which category the course will belong to. You are only allowed create new courses in the categories from the list.';
$string['sel_category']         = 'Select one category ...';

/* ********************** */
/* Course Location Plugin */
/* ********************** */
$string['plugin_course_locations']          = 'Kurssteder';
$string['friadmin:course_locations_manage'] = 'Administrere kurssteder';

$string['lst_locations']        = 'Vis liste over kurssteder';
$string['new_location']         = 'Nytt kurssted';
$string['edit_location']        = 'Rediger kurssted';
$string['edit']                 = 'Endre';
$string['del_location']         = 'Slett kurssted';
$string['view_location']        = 'Vis kurssted';
$string['courses_locations']    = 'Liste over kurssteder';

$string['title_locations']          = 'Kurssteder';
$string['title_courses_locations']  = 'Tilgjengelige kurs';
$string['title_general']            = 'Generelt';

$string['exist_locations']      = 'Eksisterende kurssteder';
$string['location']             = 'Kurssted';
$string['filter']               = 'Filter';
$string['municipality']         = 'Kommuner';
$string['counties']             = 'Fylker';
$string['sectors']              = 'Sektorer';
$string['select_level_list']    = 'Velg element';
$string['activate']             = 'Aktiver';
$string['deactivate']           = 'Deaktiver';

$string['location_county']      = 'Fylke';
$string['location_muni']        = 'Kommune';
$string['location_name']        = 'Navn';
$string['location_desc']        = 'Beskrivelse';
$string['location_url']         = 'URL til mer informasjon';
$string['location_floor']       = 'Etasje';
$string['location_room']        = 'Rom';
$string['location_seats']       = 'Maks antall plasser';
$string['location_detail']      = 'Detaljer';
$string['location_address']     = 'Adresse';
$string['location_street']      = 'Gate';
$string['location_post_code']   = 'Postnmmer';
$string['location_city']        = 'Sted';
$string['location_map']         = 'URL til kart';
$string['location_post']        = 'Postadresse';
$string['location_contact']     = 'Kontaktperson';
$string['location_phone']       = 'Kontakt-tlf';
$string['location_mail']        = 'Komtakt epost';
$string['location_contact_inf'] = 'Kontakt';
$string['location_comments']    = 'Kommentarer';

$string['sel_location']     = 'Velg kurssted ...';
$string['sel_sector']       = 'Velg sektor ...';

$string['no_data']              = 'Fant ingen resultater for utvalget ditt.';
$string['return_to_selection']  = 'Tilbake til siden for kurssteder';

$string['error_deleting_location']      = 'Beklager, men kursstedet kunne ikke slettes fordi det er i bruk i ett eller flere kurs.';
$string['deleted_location']             = 'Kursstedet er slettet';
$string['delete_location_are_you_sure'] = '<p>Er du sikker på at du vil slette kursstedet?</p>
<li><strong>Kommune: </strong>{$a->muni}</li>
<li><strong>Kurssted: </strong>{$a->name}</li>
<li><strong>Adresse: </strong>{$a->address}</li>';

$string['btn_save'] = 'Lagre';
$string['lnk_back'] = 'Tilbake';

$string['only_classroom'] = 'Vis bare klasseromskurs';


