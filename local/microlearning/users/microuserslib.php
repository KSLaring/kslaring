<?php
/**
 * Micro Learning  Users - Library
 *
 * @package         local/microlearning
 * @subpackage      users
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    16/10/2014
 * @author          eFaktor     (fbv)
 *
 */

class Micro_Users {
    private   static    $users_filter;

    public static function get_UsersFilter() {
        return self::$users_filter;
    }//set_UsersFilter

    /* PUBLIC SET       */
    public static function set_UsersFilter($lst_users) {
        self::$users_filter = $lst_users;
    }//set_UsersFilter

    /**
     * @static
     * @param           $users_filter
     * @param           $course_id
     * @param           $mode_learning
     * @param           $campaign_id
     * @param           $started
     * @return          array
     *
     * @creationDate    13/09/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the users with the filter criteria
     */
    public static function Get_SelectiorUsers_Filter($users_filter,$course_id,$mode_learning,$campaign_id,$started) {
        /* Variables    */
        global $SESSION, $DB, $CFG;
        $enrol_users    = null;
        $users_campaign = null;
        $in             = null;

        // get the SQL filter
        list($sqlwhere, $params) = $users_filter->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

        /* Get Users Enrolled   */
        $enrol_users = self::Get_UsersEnrolled($users_filter->course_id);
        if ($enrol_users) {
            if ($sqlwhere) {
                $sqlwhere .= ' AND id IN ('. implode(',',$enrol_users) . ')';
            }else {
                $sqlwhere .= ' WHERE id IN ('. implode(',',$enrol_users) . ')';
            }
        }

        $total  = count($enrol_users);
        $acount = $DB->count_records_select('user', $sqlwhere, $params);

        /* Get the users connected to campagin*/
        if (!$SESSION->removeAll) {
            self::AddUsersCampaign_To_UsersSelector($campaign_id);
        }//if_removeAll
        $scount = count($SESSION->bulk_users);

        if ($scount) {
            if ($scount < MAX_BULK_USERS) {
                $in .= implode(',', $SESSION->bulk_users);
            } else {
                $bulkusers = array_slice($SESSION->bulk_users, 0, MAX_BULK_USERS, true);
                $in .= implode(',', $bulkusers);
            }
        }//if_scount

        /* Add Selector */
        if ($in) {
            if ($sqlwhere) {
                $sqlwhere .= ' AND id NOT IN ('. $in . ')';
            }else {
                $sqlwhere .= ' WHERE id NOT IN ('. $in. ')';
            }
        }//if_in

        $userlist = array('acount'=>$acount, 'scount'=>$scount, 'ausers'=>false, 'susers'=>false, 'total'=>$total);
        $userlist['ausers'] = $DB->get_records_select_menu('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname', 0, MAX_BULK_USERS);

        /* Users Selected   */
        if ($in) {
            $userlist['susers'] = $DB->get_records_select_menu('user', "id in ($in) ", null, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
        }//if_in

        $userlist['course']     = $course_id;
        $userlist['mode']       = $mode_learning;
        $userlist['campaign']   = $campaign_id;
        $userlist['started']    = $started;

        return $userlist;
    }//Get_SelectiorUsers_Filter

    /**
     * @static
     * @param           $course_id
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    13/09/2014
     * @auhtor          eFaktor     (fbv)
     *
     * Description
     * Get all the users connected with the course
     */
    public static function Get_UsersEnrolled($course_id) {
        /* Variables    */
        global $DB;
        $users_lst = null;

        try {
            /* Search Criteria  */
            $params                 = array();
            $params['course_id']    = $course_id;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT u.id
                     FROM		{user}				u
                        JOIN	{user_enrolments}	ue	ON	ue.userid 	= u.id
                        JOIN	{enrol}				e	ON	e.id 		= ue.enrolid
                                                        AND	e.status 	= 0
                                                        AND e.courseid 	= :course_id
                     WHERE		u.deleted = 0 ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $user) {
                    $users_lst[$user->id] = $user->id;
                }//for_rdo
            }//if_rdo

            return $users_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_SelectionUsersData

    /**
     * @static
     * @param           $users_filter
     *
     * @creationDate    13/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the users to the selection form
     */
    public static function AddSelectionAll($users_filter) {
        global $SESSION, $DB, $CFG;
        $enrol_users = null;

        list($sqlwhere, $params) = $users_filter->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

        /* Get Users Enrolled   */
        $enrol_users = self::Get_UsersEnrolled($users_filter->course_id);
        if ($enrol_users) {
            if ($sqlwhere) {
                $sqlwhere .= ' AND id IN ('. implode(',',$enrol_users) . ')';
            }else {
                $sqlwhere .= ' WHERE id IN ('. implode(',',$enrol_users) . ')';
            }
        }

        $rs = $DB->get_recordset_select('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
        foreach ($rs as $user) {
            if (!isset($SESSION->bulk_users[$user->id])) {
                $SESSION->bulk_users[$user->id] = $user->id;
            }
        }
        $rs->close();
    }//AddSelectionAll

    /**
     * @static
     * @param           $campaign_id
     * @param           $lst_users
     * @throws          Exception
     *
     * Description
     * Save the users selected to the campaign
     */
    public static function SaveUsers_Campaign($campaign_id,$lst_users) {
        /* Variables    */
        global $DB;

        try {
            $transaction = $DB->start_delegated_transaction();

            try {
                /* First Deleted the actual users   */
                $DB->delete_records('microlearning_users',array('microid' => $campaign_id));

                /* Second Add the new users */
                /* Execute  */
                foreach ($lst_users as $user) {
                    $micro_delivery = new stdClass();
                    $micro_delivery->microid    = $campaign_id;
                    $micro_delivery->userid     = $user;

                    $micro_delivery->id = $DB->insert_record('microlearning_users',$micro_delivery);
                }//for_users

                /* Add the new users to sent */
                /* Get the Deliveries   */
                $deliveries_lst = self::GetDeliveries_Campaign($campaign_id);
                if ($deliveries_lst) {
                    /* Remove the actual users deliveries   */
                    self::Delete_UsersDeliveries($campaign_id);
                    /* Add the new users deliveries */
                    self::AddNew_UsersDeliveries($deliveries_lst,$lst_users,$campaign_id);
                }//if_deliveries_lst

                $transaction->allow_commit();
            }catch (Exception $ex_trans) {
                $transaction->rollback($ex_trans);
            }//try_catch
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SaveUsers_Campaign

    /************/
    /* PRIVATE */
    /***********/

    /**
     * @param           $campaign_id
     * @throws          Exception
     *
     * @creationDate    22/11/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Add the users that just exist to selector users
     */
    private static function AddUsersCampaign_To_UsersSelector($campaign_id) {
        /* Variables    */
        global $DB,$SESSION;

        try {
            /* Execute  */
            $rdo = $DB->get_records('microlearning_users',array('microid' => $campaign_id),'userid');
            if ($rdo) {
                foreach ($rdo as $user) {
                    if (!array_key_exists($user->userid,$SESSION->bulk_users) &&
                        !array_key_exists($user->userid,$SESSION->to_remove)) {
                        $SESSION->bulk_users[$user->userid] = $user->userid;
                    }//if_user_not_exists
                }//for_Each_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_Campaign

    /**
     * @param           $campaign_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    21/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the deliveries connected to the campaign
     */
    private static function GetDeliveries_Campaign($campaign_id) {
        /* Variables    */
        global $DB;
        $deliveries_lst = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaign_id;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT md.micromodeid
                     FROM		{microlearning_deliveries}	md
                     WHERE		md.microid = :campaign ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $delivery) {
                    $deliveries_lst[$delivery->micromodeid] = $delivery->micromodeid;
                }//for_deliveries
            }//if_rdo

            return $deliveries_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetDeliveries_Campaign

    /**
     * @param           $campaign_id
     * @throws          Exception
     *
     * @creationDate    21/11/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Delete the users connected to the delivery.
     * Only that users that haven't received any mail
     */
    private static function Delete_UsersDeliveries($campaign_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaign_id;

            /* SQL Instruction  */
            $sql = " DELETE
                     FROM		{microlearning_deliveries}
                     WHERE		microid = :campaign
                        AND		sent != 0 ";

            /* Execute  */
            $DB->execute($sql,$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Delete_UsersDeliveries

    /**
     * @param           $deliveries_lst
     * @param           $users_lst
     * @param           $campaign_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    21/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the new users to the deliveries
     */
    private static function AddNew_UsersDeliveries($deliveries_lst,$users_lst,$campaign_id) {
        /* Variables    */
        global $DB;

        try {
            /* Add all the users for each delivery  */
            foreach ($deliveries_lst as $delivery) {
                foreach ($users_lst as $user) {
                    /* New Delivery */
                    $delivery_info = new stdClass();
                    $delivery_info->microid     = $campaign_id;
                    $delivery_info->micromodeid = $delivery;
                    $delivery_info->userid      = $user;
                    $delivery_info->sent        = 0;

                    /* Execute  */
                    $DB->insert_record('microlearning_deliveries',$delivery_info);
                }//for_each_user
            }//for_deliveries

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddNew_UsersDeliveries
}//class_Micro_Users