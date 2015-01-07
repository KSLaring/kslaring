<?php

/**
 *  Post-install script for Municipality extra user profield.
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/municipality
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    19/11/2014
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Report generator upgrade code.
 */
function xmldb_profilefield_municipality_install() {
    global $DB;

    $db_man = $DB->get_manager();


    /* *************************** */
    /* Create tables into database */
    /* *************************** */
    /* Counties */
    if (!$db_man->table_exists('counties')) {
        /* Counties */
        $table_counties = new xmldb_table('counties');
        /* Id - Primary Key */
        $table_counties->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
        /* countyID     - Foreign Key - Counties    */
        $table_counties->add_field('idcounty',XMLDB_TYPE_CHAR,'10',null, XMLDB_NOTNULL);
        /* County     */
        $table_counties->add_field('county',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL,null,null);
        //Adding Keys
        $table_counties->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        /* Create   */
        $db_man->create_table($table_counties);

        /* Add Counties */
        MunicipalityProfile_Install::install_Counties();
    }//if_counties

    /* Municipality */
    if (!$db_man->table_exists('municipality')) {
        /* Municipality */
        $table_municipality = new xmldb_table('municipality');
        /* Id - Primary Key */
        $table_municipality->add_field('id',XMLDB_TYPE_INTEGER,'10',null, XMLDB_NOTNULL, XMLDB_SEQUENCE,null);
        /* countyID     - Foreign Key - Counties    */
        $table_municipality->add_field('idcounty',XMLDB_TYPE_CHAR,'10',null, XMLDB_NOTNULL);
        /* muniId   */
        $table_municipality->add_field('idmuni',XMLDB_TYPE_CHAR,'10',null, XMLDB_NOTNULL);
        /* Municipality     */
        $table_municipality->add_field('municipality',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL,null,null);
        /* Logo     */
        $table_municipality->add_field('logo',XMLDB_TYPE_CHAR,'255',null, XMLDB_NOTNULL,null,null);
        //Adding Keys
        $table_municipality->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table_municipality->add_key('idcounty',XMLDB_KEY_FOREIGN,array('idcounty'), 'counties', array('idcounty'));

        /* Create   */
        $db_man->create_table($table_municipality);

        /* Add Municipalities   */
        MunicipalityProfile_Install::install_Østfold();
        MunicipalityProfile_Install::install_Akershus();
        MunicipalityProfile_Install::install_Oslo();
        MunicipalityProfile_Install::install_Hedmark();
        MunicipalityProfile_Install::install_Oppland();
        MunicipalityProfile_Install::install_Buskerud();
        MunicipalityProfile_Install::install_Vestfold();
        MunicipalityProfile_Install::install_Telemark();
        MunicipalityProfile_Install::install_Aust_Agder();
        MunicipalityProfile_Install::install_Vest_Agder();
        MunicipalityProfile_Install::install_Rogaland();
        MunicipalityProfile_Install::install_Hordaland();
        MunicipalityProfile_Install::install_Sogn_og_Fjordane();
        MunicipalityProfile_Install::install_Møre_og_Romsdal();
        MunicipalityProfile_Install::install_Sør_Trøndelag();
        MunicipalityProfile_Install::install_Nord_Trøndelag();
        MunicipalityProfile_Install::install_Nordland();
        MunicipalityProfile_Install::install_Troms();
        MunicipalityProfile_Install::install_Finnmark();
        MunicipalityProfile_Install::install_Svalbard();
    }//if_municipality

    /* Transfer Users with old Municipality (Extra Field) --> New */
    /* Get the info connected with the old version, if it exists*/
    $info_old_profile = MunicipalityProfile_Install::GetInfo_OldProfile();
    if ($info_old_profile) {
        /* Update the Municipality profile field to the new version  */
        MunicipalityProfile_Install::UpdateProfile_NewVersion($info_old_profile);
    }//if_info_old_profile
}//xmldb_user_profile_field_municipality_install


class MunicipalityProfile_Install {
    /**
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    20/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the information connected with the old version of Municipality profile field
     */
    public static function GetInfo_OldProfile() {
        /* Variables    */
        global $DB;


        try {
            /* Get the information from the old version of Municipality extra profile field */
            /* SQL Instruction  */
            $sql = " SELECT		id,
                            shortname,
                            name,
                            description,
                            descriptionformat,
                            categoryid,
                            sortorder,
                            required,
                            locked,
                            visible,
                            forceunique,
                            signup
                 FROM		{user_info_field}
                 WHERE		(name = 'Kommune'
                             OR
                             name = 'Municipality')
                    AND     datatype = 'menu' ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                /* Info Old Version */
                $info = new stdClass();
                $info->id                   = $rdo->id;
                $info->shortname            = $rdo->shortname;
                $info->name                 = $rdo->name;
                $info->description          = $rdo->description;
                $info->descriptionformat    = $rdo->descriptionformat;
                $info->categoryid           = $rdo->categoryid;
                $info->sortorder            = $rdo->sortorder;
                $info->required             = $rdo->required;
                $info->locked               = $rdo->locked;
                $info->visible              = $rdo->visible;
                $info->forceunique          = $rdo->forceunique;
                $info->signup               = $rdo->signup;
                /* Get Users Connected  */
                $info->users                = self::GetUsersConnected($rdo->id);

                return $info;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfo_OldProfile


    /**
     * @param           $field_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the users connected to the old version
     */
    public static function GetUsersConnected($field_id) {
        /* Variables    */
        global $DB;
        $users_lst = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['field_id'] = $field_id;

            /* SQL Instruction  */
            $sql = " SELECT		uid.id,
                            uid.userid,
                            mu.idmuni
                 FROM		{user_info_data}	uid
                    JOIN	{municipality}		mu		ON 		mu.municipality = uid.data
                 WHERE		uid.fieldid = :field_id
                 ORDER BY 	uid.userid ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* User Info    */
                    $user_info = new stdClass();
                    $user_info->id      = $instance->id;
                    $user_info->userid  = $instance->userid;
                    $user_info->muni    = $instance->idmuni;

                    $users_lst[$instance->userid] = $user_info;
                }//for_rdo
            }//if_rdo

            return $users_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsersConnected


    /**
     * @param           $info_old_profile
     * @throws          Exception
     *
     * @creationDate    20/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the Municipality profile field to the new version
     */
    public static function UpdateProfile_NewVersion($info_old_profile) {
        /* Variables    */
        global $DB;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* First Create the new one */
            $new_profile = new stdClass();
            $new_profile->id                    = $info_old_profile->id;
            $new_profile->datatype              = 'municipality';
            $new_profile->shortname             = $info_old_profile->shortname;
            $new_profile->name                  = $info_old_profile->name;
            $new_profile->description           = $info_old_profile->description;
            $new_profile->descriptionformat     = $info_old_profile->descriptionformat;
            $new_profile->categoryid            = $info_old_profile->categoryid;
            $new_profile->sortorder             = $info_old_profile->sortorder;
            $new_profile->required              = $info_old_profile->required;
            $new_profile->locked                = $info_old_profile->locked;
            $new_profile->visible               = $info_old_profile->visible;
            $new_profile->forceunique           = $info_old_profile->forceunique;
            $new_profile->signup                = $info_old_profile->signup;
            $new_profile->param1                = null;

            /* Execute  */
            $DB->update_record('user_info_field',$new_profile);

            /* Update the Users    */
            if ($info_old_profile->users) {
                foreach($info_old_profile->users as $data) {
                    /* New Instance */
                    $info_data = new stdClass();
                    $info_data->id      = $data->id;
                    $info_data->userid  = $data->userid;
                    $info_data->fieldid = $new_profile->id;
                    $info_data->data    = $data->muni;

                    /* Execute  */
                    $DB->update_record('user_info_data',$info_data);
                }//for_users
            }//if_users

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);
            throw $ex;
        }//try_catch
    }//UpdateProfile_NewVersion

    /* *********************************** */
    /* INSTALL COUNTIES && MUNICIPALITIES  */
    /* *********************************** */

    public static function install_Counties() {
        /* Variables    */
        global $DB;

        $sql = " INSERT INTO {counties} (idcounty, county) VALUES ('01','Østfold'),";
        $sql .= "  ('02','Akershus'),";
        $sql .= "  ('03','Oslo'),";
        $sql .= "  ('04','Hedmark'),";
        $sql .= "  ('05','Oppland'),";
        $sql .= "  ('06','Buskerud'),";
        $sql .= "  ('07','Vestfold'),";
        $sql .= "  ('08','Telemark'),";
        $sql .= "  ('09','Aust-Agder'),";
        $sql .= "  ('10','Vest-Agder'),";
        $sql .= "  ('11','Rogaland'),";
        $sql .= "  ('12','Hordaland'),";
        $sql .= "  ('14','Sogn og Fjordane'),";
        $sql .= "  ('15','Møre og Romsdal'),";
        $sql .= "  ('16','Sør-Trøndelag'),";
        $sql .= "  ('17','Nord-Trøndelag'),";
        $sql .= "  ('18','Nordland'),";
        $sql .= "  ('19','Troms'),";
        $sql .= "  ('20','Finnmark'),";
        $sql .= "  ('21','Svalbard')";

        $DB->execute($sql);
    }//install_Counties

    public static function install_Østfold() {
        /* Variables    */
        global $DB;

        $sql = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('01','0118','Aremark','Aremark.png'),";
        $sql .= "  ('01','0124','Askim','Askim.png'),";
        $sql .= "  ('01','0125','Eidsberg','Eidsberg.png'),";
        $sql .= "  ('01','0106','Fredrikstad','Fredrikstad.png'),";
        $sql .= "  ('01','0101','Halden','Halden.png'),";
        $sql .= "  ('01','0138','Hobøl','Hoboel.png'),";
        $sql .= "  ('01','0111','Hvaler','Hvaler.png'),";
        $sql .= "  ('01','0119','Marker','Marker.png'),";
        $sql .= "  ('01','0104','Moss','Moss.png'),";
        $sql .= "  ('01','0128','Rakkestad','Rakkestad.png'),";
        $sql .= "  ('01','0136','Rygge','Rygge.png'),";
        $sql .= "  ('01','0121','Rømskog','Roemskog.png'),";
        $sql .= "  ('01','0135','Råde','Raade.png'),";
        $sql .= "  ('01','0105','Sarpsborg','Sarpsborg.png'),";
        $sql .= "  ('01','0127','Skiptvet','Skiptvet.png'),";
        $sql .= "  ('01','0123','Spydeberg','Spydeberg.png'),";
        $sql .= "  ('01','0122','Trøgstad','Troegstad.png'),";
        $sql .= "  ('01','0137','Våler','Vaaler_Oestfold.png'),";
        $sql .= "  ('01','01','Østfold Fylkeskommune','ostfold_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Østfold

    public static function install_Akershus() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('02','0220','Asker','Asker.png'),";
        $sql .= "  ('02','0221','Aurskog-Høland','Aurskog-Hoeland.png'),";
        $sql .= "  ('02','0219','Bærum','Baerum.png'),";
        $sql .= "  ('02','0237','Eidsvoll','Eidsvoll.png'),";
        $sql .= "  ('02','0229','Enebakk','Enebakk.png'),";
        $sql .= "  ('02','0227','Fet','Fet.png'),";
        $sql .= "  ('02','0215','Frogn','Frogn.png'),";
        $sql .= "  ('02','0234','Gjerdrum','Gjerdrum.png'),";
        $sql .= "  ('02','0239','Hurdal','Hurdal.png'),";
        $sql .= "  ('02','0230','Lørenskog','Loerenskog.png'),";
        $sql .= "  ('02','0238','Nannestad','Nannestad.png'),";
        $sql .= "  ('02','0236','Nes','Nes_Akershus.png'),";
        $sql .= "  ('02','0216','Nesodden','Nesodden.png'),";
        $sql .= "  ('02','0233','Nittedal','Nittedal.png'),";
        $sql .= "  ('02','0217','Oppegård','Oppegaard.png'),";
        $sql .= "  ('02','0228','Rælingen','Raelingen.png'),";
        $sql .= "  ('02','0231','Skedsmo','Skedsmo.png'),";
        $sql .= "  ('02','0213','Ski','Ski.png'),";
        $sql .= "  ('02','0226','Sørum','Soerum.png'),";
        $sql .= "  ('02','0235','Ullensaker','Ullensaker.png'),";
        $sql .= "  ('02','0211','Vestby','Vestby.png'),";
        $sql .= "  ('02','0214','Ås','Aas.png'), ";
        $sql .= "  ('02','02','Akershus Fylkeskommune','Akershus_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Akershus

    public static function install_Oslo() {
        /* Variables    */
        global $DB;

        $sql = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('03','0301','Oslo','Oslo.png'), ";
        $sql .= " ('03','03','Oslo Fylkeskommune','oslo_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Oslo

    public static function install_Hedmark() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('04','0438','Alvdal','Alvdal.png'),";
        $sql .= "  ('04','0420','Eidskog','Eidskog.png'),";
        $sql .= "  ('04','0427','Elverum','Elverum.png'),";
        $sql .= "  ('04','0434','Engerdal','Engerdal.png'),";
        $sql .= "  ('04','0439','Folldal','Folldal.png'),";
        $sql .= "  ('04','0423','Grue','Grue.png'),";
        $sql .= "  ('04','0403','Hamar','Hamar.png'),";
        $sql .= "  ('04','0402','Kongsvinger','Kongsvinger.png'),";
        $sql .= "  ('04','0415','Løten','Loeten.png'),";
        $sql .= "  ('04','0418','Nord-Odal','Nord-Odal.png'),";
        $sql .= "  ('04','0441','Os','Os_Hedmark.png'),";
        $sql .= "  ('04','0432','Rendalen','Rendalen.png'),";
        $sql .= "  ('04','0412','Ringsaker','Ringsaker.png'),";
        $sql .= "  ('04','0417','Stange','Stange.png'),";
        $sql .= "  ('04','0430','Stor-Elvdal','Stor-Elvdal.png'),";
        $sql .= "  ('04','0419','Sør-Odal','Soer-Odal.png'),";
        $sql .= "  ('04','0436','Tolga','Tolga.png'),";
        $sql .= "  ('04','0428','Trysil','Trysil.png'),";
        $sql .= "  ('04','0437','Tynset','Tynset.png'),";
        $sql .= "  ('04','0426','Våler','Vaaler_Hedmark.png'),";
        $sql .= "  ('04','0429','Åmot','Aamot.png'),";
        $sql .= "  ('04','0425','Åsnes','Aasnes.png'), ";
        $sql .= "  ('04','04','Hedmark Fylkeskommune','hedmark_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Hedmark

    public static function install_Oppland() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('05','0511','Dovre','Dovre.png'),";
        $sql .= "  ('05','0541','Etnedal','Etnedal.png'),";
        $sql .= "  ('05','0522','Gausdal','Gausdal.png'),";
        $sql .= "  ('05','0502','Gjøvik','Gjoevik.png'),";
        $sql .= "  ('05','0534','Gran','Gran.png'),";
        $sql .= "  ('05','0532','Jevnaker','Jevnaker.png'),";
        $sql .= "  ('05','0512','Lesja','Lesja.png'),";
        $sql .= "  ('05','0501','Lillehammer','Lillehammer.png'),";
        $sql .= "  ('05','0514','Lom','Lom.png'),";
        $sql .= "  ('05','0533','Lunner','Lunner.png'),";
        $sql .= "  ('05','0542','Nord-Aurdal','Nord-Aurdal.png'),";
        $sql .= "  ('05','0516','Nord-Fron','Nord-Fron.png'),";
        $sql .= "  ('05','0538','Nordre Land','Nordre_Land.png'),";
        $sql .= "  ('05','0520','Ringebu','Ringebu.png'),";
        $sql .= "  ('05','0517','Sel','Sel.png'),";
        $sql .= "  ('05','0513','Skjåk','Skjaak.png'),";
        $sql .= "  ('05','0536','Søndre Land','Søndre_Land.png'),";
        $sql .= "  ('05','0540','Sør-Aurdal','Soer-Aurdal.png'),";
        $sql .= "  ('05','0519','Sør-Fron','Soer-Fron.png'),";
        $sql .= "  ('05','0545','Vang','Vang.png'),";
        $sql .= "  ('05','0543','Vestre Slidre','Vestre_Slidre.png'),";
        $sql .= "  ('05','0529','Vestre Toten','Vestre_Toten.png'),";
        $sql .= "  ('05','0515','Vågå','Vaagaa.png'),";
        $sql .= "  ('05','0528','Østre Toten','Oestre_Toten.png'),";
        $sql .= "  ('05','0521','Øyer','Oeyer.png'),";
        $sql .= "  ('05','0544','Øystre Slidre','Oeystre_Slidre.png'), ";
        $sql .= "  ('05','05','Oppland Fylkeskommune','oppland_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Oppland

    public static function install_Buskerud() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('06','0602','Drammen','Drammen.png'),";
        $sql .= "  ('06','0631','Flesberg','Flesberg.png'),";
        $sql .= "  ('06','0615','Flå','Flaa.png'),";
        $sql .= "  ('06','0617','Gol','Gol.png'),";
        $sql .= "  ('06','0618','Hemsedal','Hemsedal.png'),";
        $sql .= "  ('06','0620','Hol','Hol.png'),";
        $sql .= "  ('06','0612','Hole','Hole.png'),";
        $sql .= "  ('06','0628','Hurum','Hurum.png'),";
        $sql .= "  ('06','0604','Kongsberg','Kongsberg.png'),";
        $sql .= "  ('06','0622','Krødsherad','Kroedsherad.png'),";
        $sql .= "  ('06','0626','Lier','Lier.png'),";
        $sql .= "  ('06','0623','Modum','Modum.png'),";
        $sql .= "  ('06','0625','Nedre Eiker','Nedre_Eiker.png'),";
        $sql .= "  ('06','0616','Nes','Nes_Buskerud.png'),";
        $sql .= "  ('06','0633','Nore og Uvdal','Nore_og_Uvdal.png'),";
        $sql .= "  ('06','0605','Ringerike','Ringerike.png'),";
        $sql .= "  ('06','0632','Rollag','Rollag.png'),";
        $sql .= "  ('06','0627','Røyken','Roeyken.png'),";
        $sql .= "  ('06','0621','Sigdal','Sigdal.png'),";
        $sql .= "  ('06','0624','Øvre Eiker','Oevre_Eiker.png'),";
        $sql .= "  ('06','0619','Ål','Aal.png') , ";
        $sql .= "  ('06','06','Buskerud Fylkeskommune','buskerud_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Buskerud

    public static function install_Vestfold() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('07','0719','Andebu','Andebu.png'),";
        $sql .= "  ('07','0714','Hof','Hof.png'),";
        $sql .= "  ('07','0702','Holmestrand','Holmestrand.png'),";
        $sql .= "  ('07','0701','Horten','Horten_komm.png'),";
        $sql .= "  ('07','0728','Lardal','Lardal.png'),";
        $sql .= "  ('07','0709','Larvik','Larvik.png'),";
        $sql .= "  ('07','0722','Nøtterøy','Noetteroey.png'),";
        $sql .= "  ('07','0716','Re','Re.png'),";
        $sql .= "  ('07','0713','Sande','Sande_Vestfold.png'),";
        $sql .= "  ('07','0706','Sandefjord','Sandefjord.png'),";
        $sql .= "  ('07','0720','Stokke','Stokke.png'),";
        $sql .= "  ('07','0711','Svelvik','Svelvik.png'),";
        $sql .= "  ('07','0723','Tjøme','Tjoeme.png'),";
        $sql .= "  ('07','0704','Tønsberg','Toensberg.png'), ";
        $sql .= "  ('07','07','Vestfold Fylkeskommune','vestfold_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Vestfold

    public static function install_Telemark() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('08','0814','Bamble','Bamble.png'),";
        $sql .= "  ('08','0821','Bø','Boe_Telemark.png'),";
        $sql .= "  ('08','0817','Drangedal','Drangedal.png'),";
        $sql .= "  ('08','0831','Fyresdal','Fyresdal.png'),";
        $sql .= "  ('08','0827','Hjartdal','Hjartdal.png'),";
        $sql .= "  ('08','0815','Kragerø','Krageroe.png'),";
        $sql .= "  ('08','0829','Kviteseid','Kviteseid.png'),";
        $sql .= "  ('08','0830','Nissedal','Nissedal.png'),";
        $sql .= "  ('08','0819','Nome','Nome.png'),";
        $sql .= "  ('08','0807','Notodden','Notodden.png'),";
        $sql .= "  ('08','0805','Porsgrunn','Porsgrunn.png'),";
        $sql .= "  ('08','0822','Sauherad','Sauherad.png'),";
        $sql .= "  ('08','0828','Seljord','Seljord.png'),";
        $sql .= "  ('08','0811','Siljan','Siljan.png'),";
        $sql .= "  ('08','0806','Skien','Skien.png'),";
        $sql .= "  ('08','0826','Tinn','Tinn.png'),";
        $sql .= "  ('08','0833','Tokke','Tokke.png'),";
        $sql .= "  ('08','0834','Vinje','Vinje.png'), ";
        $sql .= "  ('08','08','Telemark Fylkeskommune','telemark_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Telemark

    public static function install_Aust_Agder() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('09','0906','Arendal','Arendal.png'),";
        $sql .= "  ('09','0928','Birkenes','Birkenes.png'),";
        $sql .= "  ('09','0938','Bygland','Bygland.png'),";
        $sql .= "  ('09','0941','Bykle','Bykle.png'),";
        $sql .= "  ('09','0937','Evje og Hornnes','Evje_og_Hornnes.png'),";
        $sql .= "  ('09','0919','Froland','Froland.png'),";
        $sql .= "  ('09','0911','Gjerstad','Gjerstad.png'),";
        $sql .= "  ('09','0904','Grimstad','Grimstad.png'),";
        $sql .= "  ('09','0935','Iveland','Iveland.png'),";
        $sql .= "  ('09','0926','Lillesand','Lillesand.png'),";
        $sql .= "  ('09','0901','Risør','Risoer.png'),";
        $sql .= "  ('09','0914','Tvedestrand','Tvedestrand.png'),";
        $sql .= "  ('09','0940','Valle','Valle.png'),";
        $sql .= "  ('09','0912','Vegårshei','Vegaarshei.png'),";
        $sql .= "  ('09','0929','Åmli','Aamli.png'), ";
        $sql .= "  ('09','09','Aust-Agder Fylkeskommune','aust-agder_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Aust_Agder

    public static function install_Vest_Agder() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('10','1027','Audnedal','Audnedal.png'),";
        $sql .= "  ('10','1003','Farsund','Farsund.png'),";
        $sql .= "  ('10','1004','Flekkefjord','Flekkefjord.png'),";
        $sql .= "  ('10','1034','Hægebostad','Haegebostad.png'),";
        $sql .= "  ('10','1001','Kristiansand','Kristiansand.png'),";
        $sql .= "  ('10','1037','Kvinesdal','Kvinesdal.png'),";
        $sql .= "  ('10','1029','Lindesnes','Lindesnes.png'),";
        $sql .= "  ('10','1032','Lyngdal','Lyngdal.png'),";
        $sql .= "  ('10','1002','Mandal','Mandal.png'),";
        $sql .= "  ('10','1021','Marnardal','Marnardal.png'),";
        $sql .= "  ('10','1046','Sirdal','Sirdal.png'),";
        $sql .= "  ('10','1017','Songdalen','Songdalen.png'),";
        $sql .= "  ('10','1018','Søgne','Soegne.png'),";
        $sql .= "  ('10','1014','Vennesla','Vennesla.png'),";
        $sql .= "  ('10','1026','Åseral','Aaseral.png'), ";
        $sql .= "  ('10','10','Vest-Agder Fylkeskommune','vest-agder_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Vest_Agder

    public static function install_Rogaland() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('11','1114','Bjerkreim','Bjerkreim.png'),";
        $sql .= "  ('11','1145','Bokn','Bokn.png'),";
        $sql .= "  ('11','1101','Eigersund','Eigersund.png'),";
        $sql .= "  ('11','1141','Finnøy','Finnoey.png'),";
        $sql .= "  ('11','1129','Forsand','Forsand.png'),";
        $sql .= "  ('11','1122','Gjesdal','Gjesdal.png'),";
        $sql .= "  ('11','1106','Haugesund','Haugesund.png'),";
        $sql .= "  ('11','1133','Hjelmeland','Hjelmeland.png'),";
        $sql .= "  ('11','1119','Hå','Haa.png'),";
        $sql .= "  ('11','1149','Karmøy','Karmoey.png'),";
        $sql .= "  ('11','1120','Klepp','Klepp.png'),";
        $sql .= "  ('11','1144','Kvitsøy','Kvitsoey.png'),";
        $sql .= "  ('11','1112','Lund','Lund.png'),";
        $sql .= "  ('11','1127','Randaberg','Randaberg.png'),";
        $sql .= "  ('11','1142','Rennesøy','Rennesoey.png'),";
        $sql .= "  ('11','1102','Sandnes','Sandnes.png'),";
        $sql .= "  ('11','1135','Sauda','Sauda.png'),";
        $sql .= "  ('11','1111','Sokndal','Sokndal.png'),";
        $sql .= "  ('11','1124','Sola','Sola.png'),";
        $sql .= "  ('11','1103','Stavanger','Stavanger.png'),";
        $sql .= "  ('11','1130','Strand','Strand.png'),";
        $sql .= "  ('11','1134','Suldal','Suldal.png'),";
        $sql .= "  ('11','1121','Time','Time.png'),";
        $sql .= "  ('11','1146','Tysvær','Tysvaer.png'),";
        $sql .= "  ('11','1151','Utsira','Utsira.png'),";
        $sql .= "  ('11','1160','Vindafjord','Vindafjord.png'), ";
        $sql .= "  ('11','11','Rogaland Fylkeskommune','rogaland_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Rogaland

    public static function install_Hordaland() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('12','1247','Askøy','Askoey.png'),";
        $sql .= "  ('12','1244','Austevoll','Austevoll.png'),";
        $sql .= "  ('12','1264','Austrheim','Austrheim.png'),";
        $sql .= "  ('12','1201','Bergen','Bergen.png'),";
        $sql .= "  ('12','1219','Bømlo','Boemlo.png'),";
        $sql .= "  ('12','1232','Eidfjord','Eidfjord.png'),";
        $sql .= "  ('12','1211','Etne','Etne.png'),";
        $sql .= "  ('12','1265','Fedje','Fedje.png'),";
        $sql .= "  ('12','1222','Fitjar','Fitjar.png'),";
        $sql .= "  ('12','1246','Fjell','Fjell.png'),";
        $sql .= "  ('12','1241','Fusa','Fusa.png'),";
        $sql .= "  ('12','1234','Granvin','Granvin.png'),";
        $sql .= "  ('12','1227','Jondal','Jondal.png'),";
        $sql .= "  ('12','1238','Kvam','Kvam.png'),";
        $sql .= "  ('12','1224','Kvinnherad','Kvinnherad.png'),";
        $sql .= "  ('12','1263','Lindås','Lindaas.png'),";
        $sql .= "  ('12','1266','Masfjorden','Masfjorden.png'),";
        $sql .= "  ('12','1256','Meland','Meland.png'),";
        $sql .= "  ('12','1252','Modalen','Modalen.png'),";
        $sql .= "  ('12','1228','Odda','Odda.png'),";
        $sql .= "  ('12','1243','Os','Os_Hordaland.png'),";
        $sql .= "  ('12','1253','Osterøy','Osteroey.png'),";
        $sql .= "  ('12','1260','Radøy','Radoey.png'),";
        $sql .= "  ('12','1242','Samnanger','Samnanger.png'),";
        $sql .= "  ('12','1221','Stord','Stord.png'),";
        $sql .= "  ('12','1245','Sund','Sund.png'),";
        $sql .= "  ('12','1216','Sveio','Sveio.png'),";
        $sql .= "  ('12','1223','Tysnes','Tysnes.png'),";
        $sql .= "  ('12','1231','Ullensvang','Ullensvang.png'),";
        $sql .= "  ('12','1233','Ulvik','Ulvik.png'),";
        $sql .= "  ('12','1251','Vaksdal','Vaksdal.png'),";
        $sql .= "  ('12','1235','Voss','Voss.png'),";
        $sql .= "  ('12','1259','Øygarden','Oeygarden.png'), ";
        $sql .= "  ('12','12','Hordaland Fylkeskommune','hordaland_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Hordaland

    public static function install_Sogn_og_Fjordane() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('14','1428','Askvoll','Askvoll.png'),";
        $sql .= "  ('14','1421','Aurland','Aurland.png'),";
        $sql .= "  ('14','1418','Balestrand','Balestrand.png'),";
        $sql .= "  ('14','1438','Bremanger','Bremanger.png'),";
        $sql .= "  ('14','1443','Eid','Eid.png'),";
        $sql .= "  ('14','1429','Fjaler','Fjaler.png'),";
        $sql .= "  ('14','1401','Flora','Flora.png'),";
        $sql .= "  ('14','1432','Førde','Foerde.png'),";
        $sql .= "  ('14','1430','Gaular','Gaular.png'),";
        $sql .= "  ('14','1445','Gloppen','Gloppen.png'),";
        $sql .= "  ('14','1411','Gulen','Gulen.png'),";
        $sql .= "  ('14','1444','Hornindal','Hornindal.png'),";
        $sql .= "  ('14','1413','Hyllestad','Hyllestad.png'),";
        $sql .= "  ('14','1416','Høyanger','Hoeyanger.png'),";
        $sql .= "  ('14','1431','Jølster','Joelster.png'),";
        $sql .= "  ('14','1419','Leikanger','Leikanger.png'),";
        $sql .= "  ('14','1426','Luster','Luster.png'),";
        $sql .= "  ('14','1422','Lærdal','Laerdal.png'),";
        $sql .= "  ('14','1433','Naustdal','Naustdal.png'),";
        $sql .= "  ('14','1441','Selje','Selje.png'),";
        $sql .= "  ('14','1420','Sogndal','Sogndal.png'),";
        $sql .= "  ('14','1412','Solund','Solund.png'),";
        $sql .= "  ('14','1449','Stryn','Stryn.png'),";
        $sql .= "  ('14','1417','Vik','Vik.png'),";
        $sql .= "  ('14','1439','Vågsøy','Vaagsoey.png'),";
        $sql .= "  ('14','1424','Årdal','Aardal.png'), ";
        $sql .= "  ('14','14','Sogn og Fjordane Fylkeskommune','sogn_og_fjordane_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Sogn_og_Fjordane

    public static function install_Møre_og_Romsdal() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('15','1547','Aukra','Aukra.png'),";
        $sql .= "  ('15','1576','Aure','Tustna_Aure.png'),";
        $sql .= "  ('15','1554','Averøy','Averoey.png'),";
        $sql .= "  ('15','1551','Eide','Eide.png'),";
        $sql .= "  ('15','1548','Fræna','Fraena.png'),";
        $sql .= "  ('15','1532','Giske','Giske.png'),";
        $sql .= "  ('15','1557','Gjemnes','Gjemnes.png'),";
        $sql .= "  ('15','1571','Halsa','Halsa.png'),";
        $sql .= "  ('15','1534','Haram','Haram.png'),";
        $sql .= "  ('15','1517','Hareid','Hareid.png'),";
        $sql .= "  ('15','1515','Herøy','Heroey_Moere_og_Romsdal.png'),";
        $sql .= "  ('15','1505','Kristiansund','Kristiansund_vapen.svg'),";
        $sql .= "  ('15','1545','Midsund','Midsund.png'),";
        $sql .= "  ('15','1502','Molde','Molde.png'),";
        $sql .= "  ('15','1543','Nesset','Nesset.png'),";
        $sql .= "  ('15','1524','Norddal','Norddal.png'),";
        $sql .= "  ('15','1539','Rauma','Rauma.png'),";
        $sql .= "  ('15','1567','Rindal','Rindal.png'),";
        $sql .= "  ('15','1514','Sande','Sande_Moere_og_Romsdal.png'),";
        $sql .= "  ('15','1546','Sandøy','Sandoey.png'),";
        $sql .= "  ('15','1529','Skodje','Skodje.png'),";
        $sql .= "  ('15','1573','Smøla','Smoela.png'),";
        $sql .= "  ('15','1526','Stordal','Stordal.png'),";
        $sql .= "  ('15','1525','Stranda','Stranda.png'),";
        $sql .= "  ('15','1531','Sula','Sula.png'),";
        $sql .= "  ('15','1563','Sunndal','Sunndal.png'),";
        $sql .= "  ('15','1566','Surnadal','Surnadal.png'),";
        $sql .= "  ('15','1528','Sykkylven','Sykkylven.png'),";
        $sql .= "  ('15','1560','Tingvoll','Tingvoll.png'),";
        $sql .= "  ('15','1516','Ulstein','Ulstein.png'),";
        $sql .= "  ('15','1511','Vanylven','Vanylven.png'),";
        $sql .= "  ('15','1535','Vestnes','Vestnes.png'),";
        $sql .= "  ('15','1519','Volda','Volda.png'),";
        $sql .= "  ('15','1523','Ørskog','Orskog.png'),";
        $sql .= "  ('15','1520','Ørsta','Orsta.png'),";
        $sql .= "  ('15','1504','Ålesund','Aalesund.png'), ";
        $sql .= "  ('15','15','Møre og Romsdal Fylkeskommune','more_og_romsdal_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Møre_og_Romsdal

    public static function install_Sør_Trøndelag() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('16','1622','Agdenes','Agdenes.png'),";
        $sql .= "  ('16','1627','Bjugn','Bjugn.png'),";
        $sql .= "  ('16','1620','Frøya','Froeya.png'),";
        $sql .= "  ('16','1612','Hemne','Hemne.png'),";
        $sql .= "  ('16','1617','Hitra','Hitra.png'),";
        $sql .= "  ('16','1644','Holtålen','Holtaalen.png'),";
        $sql .= "  ('16','1662','Klæbu','Klaebu.png'),";
        $sql .= "  ('16','1663','Malvik','Malvik.png'),";
        $sql .= "  ('16','1636','Meldal','Meldal.png'),";
        $sql .= "  ('16','1653','Melhus','Melhus.png'),";
        $sql .= "  ('16','1648','Midtre Gauldal','Midtre_Gauldal.png'),";
        $sql .= "  ('16','1634','Oppdal','Oppdal.png'),";
        $sql .= "  ('16','1638','Orkdal','Orkdal.png'),";
        $sql .= "  ('16','1633','Osen','Osen.png'),";
        $sql .= "  ('16','1635','Rennebu','Rennebu.png'),";
        $sql .= "  ('16','1624','Rissa','Rissa.png'),";
        $sql .= "  ('16','1632','Roan','Roan.png'),";
        $sql .= "  ('16','1640','Røros','Roeros.png'),";
        $sql .= "  ('16','1664','Selbu','Selbu.png'),";
        $sql .= "  ('16','1657','Skaun','Skaun.png'),";
        $sql .= "  ('16','1613','Snillfjord','Snillfjord.png'),";
        $sql .= "  ('16','1601','Trondheim','Trondheim.png'),";
        $sql .= "  ('16','1665','Tydal','Tydal.png'),";
        $sql .= "  ('16','1621','Ørland','Oerland.png'),";
        $sql .= "  ('16','1630','Åfjord','Aafjord.png'), ";
        $sql .= "  ('16','16','Sør-Trøndelag Fylkeskommune','sor-tronderlag_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Sør_Trøndelag

    public static function install_Nord_Trøndelag() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('17','1749','Flatanger','Flatanger.png'),";
        $sql .= "  ('17','1748','Fosnes','Fosnes.png'),";
        $sql .= "  ('17','1717','Frosta','Frosta.png'),";
        $sql .= "  ('17','1742','Grong','Grong.png'),";
        $sql .= "  ('17','1743','Høylandet','Hoeylandet.png'),";
        $sql .= "  ('17','1756','Inderøy','Inderoey.png'),";
        $sql .= "  ('17','1755','Leka','Leka.png'),";
        $sql .= "  ('17','1718','Leksvik','Leksvik.png'),";
        $sql .= "  ('17','1719','Levanger','Levanger.png'),";
        $sql .= "  ('17','1738','Lierne','Lierne.png'),";
        $sql .= "  ('17','1711','Meråker','Meraaker.png'),";
        $sql .= "  ('17','1725','Namdalseid','Namdalseid.png'),";
        $sql .= "  ('17','1703','Namsos','Namsos.png'),";
        $sql .= "  ('17','1740','Namsskogan','Namsskogan.png'),";
        $sql .= "  ('17','1751','Nærøy','Naeroey.png'),";
        $sql .= "  ('17','1744','Overhalla','Overhalla.png'),";
        $sql .= "  ('17','1739','Røyrvik','Roeyrvik.png'),";
        $sql .= "  ('17','1736','Snåsa','Snaasa.png'),";
        $sql .= "  ('17','1702','Steinkjer','Steinkjer.png'),";
        $sql .= "  ('17','1714','Stjørdal','Stjoerdal.png'),";
        $sql .= "  ('17','1721','Verdal','Verdal.png'),";
        $sql .= "  ('17','1724','Verran','Verran.png'),";
        $sql .= "  ('17','1750','Vikna','Vikna.png'), ";
        $sql .= "  ('17','17','Nord-Trøndelag Fylkeskommune','nord-tronderlag_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Nord_Trøndelag

    public static function install_Nordland() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('18','1820','Alstahaug','Alstahaug.png'),";
        $sql .= "  ('18','1871','Andøy','Andoey.png'),";
        $sql .= "  ('18','1854','Ballangen','Ballangen.png'),";
        $sql .= "  ('18','1839','Beiarn','Beiarn.png'),";
        $sql .= "  ('18','1811','Bindal','Bindal.png'),";
        $sql .= "  ('18','1804','Bodø','Bodo.png'),";
        $sql .= "  ('18','1813','Brønnøy','Broennoey.png'),";
        $sql .= "  ('18','1867','Bø','Boe_Nordland.png'),";
        $sql .= "  ('18','1827','Dønna','Doenna.png'),";
        $sql .= "  ('18','1853','Evenes','Evenes.png'),";
        $sql .= "  ('18','1841','Fauske','Fauske.png'),";
        $sql .= "  ('18','1859','Flakstad','Flakstad.png'),";
        $sql .= "  ('18','1838','Gildeskål','Gildeskaal.png'),";
        $sql .= "  ('18','1825','Grane','Grane.png'),";
        $sql .= "  ('18','1866','Hadsel','Hadsel.png'),";
        $sql .= "  ('18','1849','Hamarøy','Hamaroey.png'),";
        $sql .= "  ('18','1826','Hattfjelldal','Hattfjelldal.png'),";
        $sql .= "  ('18','1832','Hemnes','Hemnes.png'),";
        $sql .= "  ('18','1818','Herøy','Heroey_Nordland.png'),";
        $sql .= "  ('18','1822','Leirfjord','Leirfjord.png'),";
        $sql .= "  ('18','1834','Lurøy','Luroey.png'),";
        $sql .= "  ('18','1851','Lødingen','Loedingen.png'),";
        $sql .= "  ('18','1837','Meløy','Meloey.png'),";
        $sql .= "  ('18','1874','Moskenes','Moskenes.png'),";
        $sql .= "  ('18','1805','Narvik','Narvik.png'),";
        $sql .= "  ('18','1828','Nesna','Nesna.png'),";
        $sql .= "  ('18','1833','Rana','Rana.png'),";
        $sql .= "  ('18','1836','Rødøy','Roedoey.png'),";
        $sql .= "  ('18','1856','Røst','Roest.png'),";
        $sql .= "  ('18','1840','Saltdal','Saltdal.png'),";
        $sql .= "  ('18','1870','Sortland','Sortland.png'),";
        $sql .= "  ('18','1848','Steigen','Steigen.png'),";
        $sql .= "  ('18','1812','Sømna','Soemna.png'),";
        $sql .= "  ('18','1845','Sørfold','Soerfold.png'),";
        $sql .= "  ('18','1852','Tjeldsund','Tjeldsund.png'),";
        $sql .= "  ('18','1835','Træna','Traena.png'),";
        $sql .= "  ('18','1850','Tysfjord','Tysfjord.png'),";
        $sql .= "  ('18','1824','Vefsn','Vefsn.png'),";
        $sql .= "  ('18','1815','Vega','Vega.png'),";
        $sql .= "  ('18','1860','Vestvågøy','Vestvaagoey.png'),";
        $sql .= "  ('18','1816','Vevelstad','Vevelstad.png'),";
        $sql .= "  ('18','1857','Værøy','Vaeroey.png'),";
        $sql .= "  ('18','1865','Vågan','Vaagan.png'),";
        $sql .= "  ('18','1868','Øksnes','Oeksnes.png'), ";
        $sql .= "  ('18','18','Nordland Fylkeskommune','nordland_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Nordland

    public static function install_Troms() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('19','1933','Balsfjord','Balsfjord.png'),";
        $sql .= "  ('19','1922','Bardu','Bardu.png'),";
        $sql .= "  ('19','1929','Berg','Berg.png'),";
        $sql .= "  ('19','1926','Dyrøy','Dyroey.png'),";
        $sql .= "  ('19','1919','Gratangen','Gratangen.png'),";
        $sql .= "  ('19','1903','Harstad','Harstad.png'),";
        $sql .= "  ('19','1917','Ibestad','Ibestad.png'),";
        $sql .= "  ('19','1936','Karlsøy','Karlsoey.png'),";
        $sql .= "  ('19','1911','Kvæfjord','Kvaefjord.png'),";
        $sql .= "  ('19','1943','Kvænangen','Kvaenangen.png'),";
        $sql .= "  ('19','1940','Kåfjord','Kaafjord.png'),";
        $sql .= "  ('19','1920','Lavangen','Lavangen.png'),";
        $sql .= "  ('19','1931','Lenvik','Lenvik.png'),";
        $sql .= "  ('19','1938','Lyngen','Lyngen.png'),";
        $sql .= "  ('19','1924','Målselv','Maalselv.png'),";
        $sql .= "  ('19','1942','Nordreisa','Nordreisa.png'),";
        $sql .= "  ('19','1923','Salangen','Salangen.png'),";
        $sql .= "  ('19','1941','Skjervøy','Skjervoey.png'),";
        $sql .= "  ('19','1913','Skånland','Skaanland.png'),";
        $sql .= "  ('19','1939','Storfjord','Storfjord.png'),";
        $sql .= "  ('19','1925','Sørreisa','Soerreisa.png'),";
        $sql .= "  ('19','1928','Torsken','Torsken.png'),";
        $sql .= "  ('19','1927','Tranøy','Tranoey.png'),";
        $sql .= "  ('19','1902','Tromsø','Tromsoe.png'),";
        $sql .= "  ('19','19','Troms Fylkeskommune','troms_fylkeskommune.png') ";

        $DB->execute($sql);
    }//install_Troms

    public static function install_Finnmark() {
        /* Variables    */
        global $DB;

        $sql  = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('20','2012','Alta','Alta.png'),";
        $sql .= "  ('20','2024','Berlevåg','Berlevaag.png'),";
        $sql .= "  ('20','2028','Båtsfjord','Baatsfjord.png'),";
        $sql .= "  ('20','2023','Gamvik','Gamvik.png'),";
        $sql .= "  ('20','2004','Hammerfest','Hammerfest_-_kommunevaapen.svg'),";
        $sql .= "  ('20','2015','Hasvik','Hasvik.png'),";
        $sql .= "  ('20','2021','Karasjok','Karasjok.png'),";
        $sql .= "  ('20','2011','Kautokeino','Kautokeino.png'),";
        $sql .= "  ('20','2017','Kvalsund','Kvalsund.png'),";
        $sql .= "  ('20','2022','Lebesby','Lebesby.png'),";
        $sql .= "  ('20','2014','Loppa','Loppa.png'),";
        $sql .= "  ('20','2018','Måsøy','Maasoey.png'),";
        $sql .= "  ('20','2027','Nesseby','Nesseby.png'),";
        $sql .= "  ('20','2019','Nordkapp','Nordkapp.png'),";
        $sql .= "  ('20','2020','Porsanger','Porsanger.png'),";
        $sql .= "  ('20','2030','Sør-Varanger','Soer-Varanger.png'),";
        $sql .= "  ('20','2025','Tana','Tana.png'),";
        $sql .= "  ('20','2003','Vadsø','Vadsoe.png'),";
        $sql .= "  ('20','2002','Vardø','Vardoe.png'), ";
        $sql .= "  ('20','20','Finnmark Fylkeskommune','finnmark_fylkeskommune.png')";

        $DB->execute($sql);
    }//install_Finnmark

    public static function install_Svalbard() {
        /* Variables    */
        global $DB;

        $sql = " INSERT INTO {municipality} (idcounty, idmuni,municipality,logo) VALUES ('21','2111','Longyearbyen lokalstyre','Svalbard.png')";

        $DB->execute($sql);
    }//install_Svalbard
}//MunicipalityProfile_Install


