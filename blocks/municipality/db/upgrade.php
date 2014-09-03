<?php
/**
 * Block Municipality - Upgrade
 *
 * @package         block
 * @subpackage      municipality
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/08/2013
 * @updateDate      03/09/2014
 * @author          efaktor     (fbv)
 */

function xmldb_block_municipality_upgrade($old_version) {
    global $DB;

    $db_man = $DB->get_manager();

    if ($old_version < 2014082002) {
        /*********************/
        /* mdl_muni_logos    */
        /*********************/
        $table_muni_logos = new xmldb_table('muni_logos');
        //Adding fields
        /* Id           - Primary Key   */
        $table_muni_logos->add_field('id',XMLDB_TYPE_INTEGER,'10',XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
        /* municipality    - Foreign Key   */
        $table_muni_logos->add_field('municipality',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL, null,null);
        /* logo */
        $table_muni_logos->add_field('logo',XMLDB_TYPE_CHAR,'255',null,XMLDB_NOTNULL,null,null);

        //Adding Keys
        $table_muni_logos->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if ($db_man->table_exists('muni_logos')) {
            $db_man->drop_table($table_muni_logos);
        }


        /*********************/
        /* Create the table  */
        /*********************/
        if (!$db_man->table_exists('muni_logos')) {
            $db_man->create_table($table_muni_logos);

            install_logos_Østfold();
            install_logos_Akershus();
            install_logos_Oslo();
            install_logos_Hedmark();
            install_logos_Oppland();
            install_logos_Buskerud();
            install_logos_Vestfold();
            install_logos_Telemark();
            install_logos_Aust_Agder();
            install_logos_Vest_Agder();
            install_logos_Rogaland();
            install_logos_Hordaland();
            install_logos_Sogn_og_Fjordane();
            install_logos_Møre_og_Romsdal();
            install_logos_Sør_Trøndelag();
            install_logos_Nord_Trøndelag();
            install_logos_Nordland();
            install_logos_Troms();
            install_logos_Finnmark();
            install_logos_Svalbard();
        }//if_table_exists
    }//if_old_version
}//xmldb_block_municipality_upgrade

function install_logos_Østfold() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Aremark','Aremark.png')";
    $DB->execute($sql,array());

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Askim','Askim.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Eidsberg','Eidsberg.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Fredrikstad','Fredrikstad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Halden','Halden.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hobøl','Hoboel.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hvaler','Hvaler.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Marker','Marker.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Moss','Moss.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rakkestad','Rakkestad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rygge','Rygge.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rømskog','Roemskog.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Råde','Raade.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sarpsborg','Sarpsborg.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Skiptvet','Skiptvet.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Spydeberg','Spydeberg.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Trøgstad','Troegstad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Våler Østfold','Vaaler_Oestfold.png')";
    $DB->execute($sql);
}//install_logos_Østfold()

function install_logos_Akershus() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Asker','Asker.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Aurskog Høland','Aurskog-Hoeland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bærum','Baerum.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Eidsvoll','Eidsvoll.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Enebakk','Enebakk.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Fet','Fet.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Frogn','Frogn.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gjerdrum','Gjerdrum.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hurdal','Hurdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lørenskog','Loerenskog.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nannestad','Nannestad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nes','Nes_Akershus.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nesodden','Nesodden.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nittedal','Nittedal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Oppegård','Oppegaard.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rælingen','Raelingen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Skedsmo','Skedsmo.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ski','Ski.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sørum','Soerum.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ullensaker','Ullensaker.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vestby','Vestby.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ås','Aas.png')";
    $DB->execute($sql);
}//install_logos_Akershus

function install_logos_Oslo() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Oslo','Oslo.png')";
    $DB->execute($sql);
}//install_logos_Oslo

function install_logos_Hedmark() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Alvdal','Alvdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Eidskog','Eidskog.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Elverum','Elverum.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Engerdal','Engerdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Folldal','Folldal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Grue','Grue.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hamar','Hamar.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kongsvinger','Kongsvinger.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Løten','Loeten.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nord-Odal','Nord-Odal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Os Hedmark','Os_Hedmark.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rendalen','Rendalen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ringsaker','Ringsaker.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Stange','Stange.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Stor-Elvdal','Stor-Elvdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sør-Odal','Soer-Odal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tolga','Tolga.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Trysil','Trysil.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tynset','Tynset.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Våler Hedmark','Vaaler_Hedmark.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Åmot','Aamot.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Åsnes','Aasnes.png')";
    $DB->execute($sql);
}//install_logos_Hedmark

function install_logos_Oppland() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Dovre','Dovre.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Etnedal','Etnedal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gausdal','Gausdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gjøvik','Gjoevik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gran','Gran.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Jevnaker','Jevnaker.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lesja','Lesja.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lillehammer','Lillehammer.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lom','Lom.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lunner','Lunner.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nord-Aurdal','Nord-Aurdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nord-Fron','Nord-Fron.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nordre Land','Nordre_Land.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ringebu','Ringebu.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sel','Sel.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Skjåk','Skjaak.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Søndre Land','Søndre_Land.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sør-Aurdal','Soer-Aurdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sør-Fron','Soer-Fron.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vang','Vang.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vestre Slidre','Vestre_Slidre.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vestre Toten','Vestre_Toten.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vågå','Vaagaa.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Østre Toten','Oestre_Toten.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Øyer','Oeyer.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Øystre Slidre','Oeystre_Slidre.png')";
    $DB->execute($sql);
}//install_logos_Oppland

function install_logos_Buskerud() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Drammen','Drammen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Flesberg','Flesberg.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Flå','Flaa.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gol','Gol.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hemsedal','Hemsedal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hol','Hol.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hole','Hole.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hurum','Hurum.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kongsberg','Kongsberg.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Krødsherad','Kroedsherad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lier','Lier.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Modum','Modum.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nedre Eiker','Nedre_Eiker.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nes Buskerud','Nes_Buskerud.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nore og Uvdal','Nore_og_Uvdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ringerike','Ringerike.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rollag','Rollag.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Røyken','Roeyken.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sigdal','Sigdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Øvre Eiker','Oevre_Eiker.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ål','Aal.png')";
    $DB->execute($sql);
}//install_logos_Buskerud

function install_logos_Vestfold() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Andebu','Andebu.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hof','Hof.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Holmestrand','Holmestrand.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Horten','Horten_komm.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lardal','Lardal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Larvik','Larvik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nøtterøy','Noetteroey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Re','Re.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sande i Vestfold','Sande_Vestfold.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sandefjord','Sandefjord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Stokke','Stokke.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Svelvik','Svelvik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tjøme','Tjoeme.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tønsberg','Toensberg.png')";
    $DB->execute($sql);
}//install_logos_Vestfold

function install_logos_Telemark() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bamble','Bamble.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bø Telemark','Boe_Telemark.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Drangedal','Drangedal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Fyresdal','Fyresdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hjartdal','Hjartdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kragerø','Krageroe.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kviteseid','Kviteseid.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nissedal','Nissedal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nome','Nome.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Notodden','Notodden.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Porsgrunn','Porsgrunn.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sauherad','Sauherad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Seljord','Seljord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Siljan','Siljan.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Skien','Skien.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tinn','Tinn.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tokke','Tokke.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vinje','Vinje.png')";
    $DB->execute($sql);
}//install_logos_Telemark

function install_logos_Aust_Agder() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Arendal','Arendal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Birkenes','Birkenes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bygland','Bygland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bykle','Bykle.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Evje og Hornnes','Evje_og_Hornnes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Froland','Froland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gjerstad','Gjerstad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Grimstad','Grimstad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Iveland','Iveland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lillesand','Lillesand.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Risør','Risoer.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tvedestrand','Tvedestrand.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Valle','Valle.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vegårshei','Vegaarshei.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Åmli','Aamli.png')";
    $DB->execute($sql);
}//install_logos_Aust_Agder

function install_logos_Vest_Agder() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Audnedal','Audnedal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Farsund','Farsund.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Flekkefjord','Flekkefjord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hægebostad','Haegebostad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kristiansand','Kristiansand.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kvinesdal','Kvinesdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lindesnes','Lindesnes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lyngdal','Lyngdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Mandal','Mandal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Marnardal','Marnardal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sirdal','Sirdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Songdalen','Songdalen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Søgne','Soegne.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vennesla','Vennesla.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Åseral','Aaseral.png')";
    $DB->execute($sql);
}//install_logos_Vest_Agder

function install_logos_Rogaland() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bjerkreim','Bjerkreim.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bokn','Bokn.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Eigersund','Eigersund.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Finnøy','Finnoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Forsand','Forsand.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gjesdal','Gjesdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Haugesund','Haugesund.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hjelmeland','Hjelmeland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hå','Haa.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Karmøy','Karmoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Klepp','Klepp.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kvitsøy','Kvitsoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lund','Lund.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Randaberg','Randaberg.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rennesøy','Rennesoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sandnes','Sandnes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sauda','Sauda.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sokndal','Sokndal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sola','Sola.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Stavanger','Stavanger.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Strand','Strand.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Suldal','Suldal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Time','Time.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tysvær','Tysvaer.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Utsira','Utsira.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vindafjord','Vindafjord.png')";
    $DB->execute($sql);
}//install_logos_Rogaland

function install_logos_Hordaland() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Askøy','Askoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Austevoll','Austevoll.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Austrheim','Austrheim.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bergen','Bergen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bømlo','Boemlo.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Eidfjord','Eidfjord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Etne','Etne.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Fedje','Fedje.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Fitjar','Fitjar.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Fjell','Fjell.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Fusa','Fusa.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Granvin','Granvin.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Jondal','Jondal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kvam','Kvam.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kvinnherad','Kvinnherad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lindås','Lindaas.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Masfjorden','Masfjorden.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Meland','Meland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Modalen','Modalen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Odda','Odda.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Os Hordaland','Os_Hordaland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Osterøy','Osteroey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Radøy','Radoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Samnanger','Samnanger.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Stord','Stord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sund','Sund.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sveio','Sveio.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tysnes','Tysnes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ullensvang','Ullensvang.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ulvik','Ulvik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vaksdal','Vaksdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Voss','Voss.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Øygarden','Oeygarden.png')";
    $DB->execute($sql);
}//install_logos_Hordaland

function install_logos_Sogn_og_Fjordane() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Askvoll','Askvoll.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Aurland','Aurland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Balestrand','Balestrand.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bremanger','Bremanger.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Eid','Eid.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Fjaler','Fjaler.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Flora','Flora.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Førde','Foerde.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gaular','Gaular.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gloppen','Gloppen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gulen','Gulen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hornindal','Hornindal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hyllestad','Hyllestad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Høyanger','Hoeyanger.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Jølster','Joelster.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Leikanger','Leikanger.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Luster','Luster.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lærdal','Laerdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Naustdal','Naustdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Selje','Selje.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sogndal','Sogndal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Solund','Solund.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Stryn','Stryn.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vik','Vik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vågsøy','Vaagsoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Årdal','Aardal.png')";
    $DB->execute($sql);
}//install_logos_Sogn_og_Fjordane

function install_logos_Møre_og_Romsdal() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Aukra','Aukra.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Aure','Tustna_Aure.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Averøy','Averoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Eide','Eide.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Fræna','Fraena.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Giske','Giske.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gjemnes','Gjemnes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Halsa','Halsa.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Haram','Haram.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hareid','Hareid.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Herøy i Møre og Romsdal','Heroey_Moere_og_Romsdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kristiansund','Kristiansund_vapen.svg')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Midsund','Midsund.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Molde','Molde.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nesset','Nesset.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Norddal','Norddal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rauma','Rauma.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rindal','Rindal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sande i Møre og Romsdal','Sande_Moere_og_Romsdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sandøy','Sandoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Skodje','Skodje.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Smøla','Smoela.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Stordal','Stordal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Stranda','Stranda.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sula','Sula.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sunndal','Sunndal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Surnadal','Surnadal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sykkylven','Sykkylven.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tingvoll','Tingvoll.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ulstein','Ulstein.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vanylven','Vanylven.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vestnes','Vestnes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Volda','Volda.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ørskog','Orskog.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ørsta','Orsta.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ålesund','Aalesund.png')";
    $DB->execute($sql);
}//install_logos_Møre_og_Romsdal

function install_logos_Sør_Trøndelag() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Agdenes','Agdenes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bjugn','Bjugn.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Frøya','Froeya.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hemne','Hemne.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hitra','Hitra.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Holtålen','Holtaalen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Klæbu','Klaebu.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Malvik','Malvik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Meldal','Meldal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Melhus','Melhus.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Midtre Gauldal','Midtre_Gauldal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Oppdal','Oppdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Orkdal','Orkdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Osen','Osen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rennebu','Rennebu.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rissa','Rissa.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Roan','Roan.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Røros','Roeros.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Selbu','Selbu.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Skaun','Skaun.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Snillfjord','Snillfjord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Trondheim','Trondheim.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tydal','Tydal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ørland','Oerland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Åfjord','Aafjord.png')";
    $DB->execute($sql);
}//install_logos_Sør_Trøndelag

function install_logos_Nord_Trøndelag() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Flatanger','Flatanger.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Fosnes','Fosnes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Frosta','Frosta.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Grong','Grong.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Høylandet','Hoeylandet.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Inderøy','Inderoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Leka','Leka.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Leksvik','Leksvik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Levanger','Levanger.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lierne','Lierne.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Meråker','Meraaker.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Namdalseid','Namdalseid.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Namsos','Namsos.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Namsskogan','Namsskogan.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nærøy','Naeroey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Overhalla','Overhalla.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Røyrvik','Roeyrvik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Snåsa','Snaasa.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Steinkjer','Steinkjer.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Stjørdal','Stjoerdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Verdal','Verdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Verran','Verran.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vikna','Vikna.png')";
    $DB->execute($sql);
}//install_logos_Nord_Trøndelag

function install_logos_Nordland() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Alstahaug','Alstahaug.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Andøy','Andoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ballangen','Ballangen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Beiarn','Beiarn.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bindal','Bindal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bodø','Bodo.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Brønnøy','Broennoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bø Nordland','Boe_Nordland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Dønna','Doenna.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Evenes','Evenes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Fauske','Fauske.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Flakstad','Flakstad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gildeskål','Gildeskaal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Grane','Grane.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hadsel','Hadsel.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hamarøy','Hamaroey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hattfjelldal','Hattfjelldal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hemnes','Hemnes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Herøy i Nordland','Heroey_Nordland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Leirfjord','Leirfjord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lurøy','Luroey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lødingen','Loedingen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Meløy','Meloey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Moskenes','Moskenes.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Narvik','Narvik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nesna','Nesna.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rana','Rana.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Rødøy','Roedoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Røst','Roest.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Saltdal','Saltdal.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sortland','Sortland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Steigen','Steigen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sømna','Soemna.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sørfold','Soerfold.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tjeldsund','Tjeldsund.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Træna','Traena.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tysfjord','Tysfjord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vefsn','Vefsn.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vega','Vega.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vestvågøy','Vestvaagoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vevelstad','Vevelstad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Værøy','Vaeroey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vågan','Vaagan.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Øksnes','Oeksnes.png')";
    $DB->execute($sql);
}//install_logos_Nordland

function install_logos_Troms() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Balsfjord','Balsfjord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Bardu','Bardu.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Berg','Berg.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Dyrøy','Dyroey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gratangen','Gratangen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Harstad','Harstad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Ibestad','Ibestad.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Karlsøy','Karlsoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kvæfjord','Kvaefjord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kvænangen','Kvaenangen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kåfjord','Kaafjord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lavangen','Lavangen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lenvik','Lenvik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lyngen','Lyngen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Målselv','Maalselv.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nordreisa','Nordreisa.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Salangen','Salangen.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Skjervøy','Skjervoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Skånland','Skaanland.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Storfjord','Storfjord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sørreisa','Soerreisa.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Torsken','Torsken.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tranøy','Tranoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tromsø','Tromsoe.png')";
    $DB->execute($sql);
}//install_logos_Troms

function install_logos_Finnmark() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Alta','Alta.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Berlevåg','Berlevaag.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Båtsfjord','Baatsfjord.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Gamvik','Gamvik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hammerfest','Hammerfest_-_kommunevaapen.svg')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Hasvik','Hasvik.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Karasjok','Karasjok.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kautokeino','Kautokeino.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Kvalsund','Kvalsund.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Lebesby','Lebesby.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Loppa','Loppa.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Måsøy','Maasoey.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nesseby','Nesseby.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Nordkapp','Nordkapp.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Porsanger','Porsanger.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Sør-Varanger','Soer-Varanger.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Tana','Tana.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vadsø','Vadsoe.png')";
    $DB->execute($sql);

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Vardø','Vardoe.png')";
    $DB->execute($sql);
}//install_logos_Finnmark

function install_logos_Svalbard() {
    /* Variables    */
    global $DB;

    $sql = " INSERT INTO {muni_logos} (municipality,logo) VALUES ('Longyearbyen lokalstyre','Svalbard.png')";
    $DB->execute($sql);
}//install_logos_Svalbard