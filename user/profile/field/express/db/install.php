<?php
/**
 * Extra Profile Field Auto Generated Express Login - Post script Install
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/express
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    16/11/2015
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_profilefield_express_install() {
    /* Variables    */
    global $DB;
    $db_man         = $DB->get_manager();
    $infoProfile    = null;
    $infoData       = null;

    try {
        /* Get Profile Field */
        $infoProfile = ExpressInstall::Get_ProfileField();

        /* Create entry for all users   */
        if ($infoProfile) {
            //ExpressInstall::Create_EntryProfile_Users($infoProfile);
        }//if_infoProfile


    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//xmldb_profilefield_express_install

class ExpressInstall {
    /**
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    16/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the profile field id
     */
    public static function Get_ProfileField() {
        /* Variables    */
        global $DB;
        $infoProfile  = null;

        try {
            /* Get Profile Field    */
            $infoProfile = $DB->get_record('user_info_field',array('datatype' => 'express'));
            if (!$infoProfile) {
                /* Create Info profile  */
                $infoProfile = new stdClass();
                $infoProfile->shortname     = 'express';
                $infoProfile->name          = 'express';
                $infoProfile->datatype      = 'express';
                $infoProfile->visible       = 0;
                $infoProfile->categoryid    = 1;

                /* Execute  */
                $infoProfile->id = $DB->insert_record('user_info_field',$infoProfile);
            }//if_infoProfile

            return $infoProfile->id;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_ProfileField

    /**
     * @param           $infoProfile
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    16/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the new profile field to all users
     */
    public static function Create_EntryProfile_Users($infoProfile) {
        /* Variables    */
        global $DB;
        $trans      = null;
        $usersLst   = null;
        $limit      = 2000;
        $indexIni   = 0;
        $totalUsers = null;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Get all users    */
            $totalUsers = $DB->count_records('user',array('deleted' => 0));

            if ($totalUsers) {
                do {
                    $usersLst = $DB->get_records('user',array('deleted' => 0),null,'id',$indexIni,$limit);
                    if ($usersLst) {
                        /* Create entry for all users   */
                        foreach ($usersLst as $user) {
                            $infoData = new stdClass();
                            $infoData->userid   = $user->id;
                            $infoData->fieldid  = $infoProfile;
                            $infoData->data     = 1;

                            $DB->insert_record('user_info_data',$infoData);
                        }
                    }//if_usersLst
                    $indexIni = $indexIni + $limit;
                }while ($indexIni < $totalUsers);
            }//if_totalUsers

            /* Commit */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Create_EntryProfile_Users
}//ExpressInstall