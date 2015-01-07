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
function xmldb_profilefield_municipality_upgrade($old_version) {
    global $DB;

    $db_man = $DB->get_manager();

    if ($old_version < 2014112000) {
        /* Transfer Users with old Municipality (Extra Field) --> New */
        /* Get the info connected with the old version, if it exists*/
        $info_old_profile = MunicipalityProfile_Upgrade::GetInfo_OldProfile();
        if ($info_old_profile) {
            /* Update the Municipality profile field to the new version  */
            MunicipalityProfile_Upgrade::UpdateProfile_NewVersion($info_old_profile);
        }//if_info_old_profile
    }

    return true;
}//xmldb_user_profile_field_municipality_install

class MunicipalityProfile_Upgrade {
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
}//MunicipalityProfile_Upgrade