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
     * @throws          Exception
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
        $sqlwhere       = null;
        $params         = null;
        $total          = null;
        $acount         = null;
        $scount         = null;
        $userlist       = null;

        try {
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
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
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
        $users_lst  = null;
        $params     = null;
        $sql        = null;
        $rdo        = null;

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
     * @throws          Exception
     *
     * @creationDate    13/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the users to the selection form
     */
    public static function AddSelectionAll($users_filter) {
        /* Variables    */
        global $SESSION, $DB, $CFG;
        $enrol_users    = null;
        $sqlwhere       = null;
        $params         = null;
        $rs             = null;

        try {
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
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddSelectionAll

    /**
     * @static
     * @param           $course_id
     * @param           $campaign_id
     * @param           $mode
     * @param           $lst_users
     * @throws          Exception
     *
     * @updateDate      07/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save the users selected to the campaign
     */
    public static function SaveUsers_Campaign($course_id,$campaign_id,$mode,$lst_users) {
        /* Variables    */
        global $DB;
        $transaction    = null;
        $micro_delivery = null;
        $deliveries_lst = null;
        $presentUsers   = null;
        $newUsers       = array();

        try {
            $transaction = $DB->start_delegated_transaction();

            try {
                /* Get Present Users    */
                $rdo = $DB->get_records('microlearning_users',array('microid' => $campaign_id),'userid');
                if ($rdo) {
                    foreach($rdo as $instance) {
                      $presentUsers[$instance->userid] = $instance->userid;
                    }
                }//if_Rdo

                /* Second Add the new users */
                /* Execute  */
                foreach ($lst_users as $user) {
                    /* New User*/
                    if ($presentUsers) {
                        if (!in_array($user,$presentUsers)) {
                            /* New User */
                            $micro_delivery = new stdClass();
                            $micro_delivery->microid    = $campaign_id;
                            $micro_delivery->userid     = $user;

                            /* Execute  */
                            $DB->insert_record('microlearning_users',$micro_delivery);

                            /* Save */
                            $newUsers[$user] = $user;
                        }//if_user_not_exist
                    }else {
                        /* New User */
                        $micro_delivery = new stdClass();
                        $micro_delivery->microid    = $campaign_id;
                        $micro_delivery->userid     = $user;

                        /* Execute  */
                        $DB->insert_record('microlearning_users',$micro_delivery);

                        /* Save */
                        $newUsers[$user] = $user;
                    }//if_presentUseres
                }//for_users

                /* Add the new users to sent */
                if ($newUsers) {
                    switch ($mode) {
                        case CALENDAR_MODE:
                            /* Get the Deliveries   */
                            $deliveries_lst = self::GetInfoDeliveries_CalendarMode($campaign_id);
                            /* Add Users        */
                            if ($deliveries_lst) {
                                self::AddUsers_CalendarMode($newUsers,$deliveries_lst);
                            }//deliveries

                            break;
                        case ACTIVITY_MODE:
                            /* Get the Deliveries   */
                            $deliveries_lst = self::GetInfoDeliveries_ActivityMode($campaign_id);
                            /* Add Users        */
                            if ($deliveries_lst) {
                                self::AddUsers_ActivityMode($newUsers,$deliveries_lst,$course_id);
                            }//if_deliveries

                            break;
                        default:
                            break;
                    }//switch_mode
                }//if_newUsers

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
        $rdo = null;

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
     * @param           $campaignId
     * @return          array
     * @throws          Exception
     *
     * @creationDate    07/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get deliveries connected with the calendar campaign
     */
    private static function GetInfoDeliveries_CalendarMode($campaignId) {
        /* Variables    */
        global $DB;
        $params     = null;
        $rdo        = null;
        $sql        = null;
        $deliveries = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaignId;

            /* SQL Instruction  */
            $sql = " SELECT	cm.id,
                            cm.microid,
                            cm.datesend,
                            cm.dateafter,
                            cm.daysafter,
                            cm.activityafter
                     FROM	{microlearning_calendar_mode} cm
                     WHERE	cm.microid = :campaign ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Add Delivery */
                    $deliveries[$instance->id] = $instance;
                }//if_Rdo
            }//if_rdo

            return $deliveries;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetInfoDeliveries_CalendarMode

    /**
     * @param           $newUsers
     * @param           $deliveries
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add users to the calendar campaign
     */
    private static function AddUsers_CalendarMode($newUsers,$deliveries) {
        /* Variables    */
        global $DB;
        $days       = null;
        $date       = null;
        $delivery   = null;
        $trans      = null;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Add new Users for each delivery  */
            foreach ($deliveries as $infoDelivery) {
                foreach ($newUsers as $user) {
                    /* New Delivery - User  */
                    $delivery = new stdClass();
                    $delivery->microid      = $infoDelivery->microid;
                    $delivery->micromodeid  = $infoDelivery->id;
                    $delivery->userid       = $user;
                    $delivery->sent         = 0;
                    /* Calculate    */
                    if ($infoDelivery->datesend) {
                        $delivery->timetosend   = $infoDelivery->datesend;
                    }else {
                        $days = (60*60*24)*$infoDelivery->daysafter;
                        $date = $infoDelivery->dateafter +  $days;

                        $delivery->timetosend = $date;
                    }//$calendar_mode->date_send

                    /* Execute  */
                    $DB->insert_record('microlearning_deliveries',$delivery);
                }//for_users
            }//for_deliveries

            /* Commit   */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//AddUsers_CalendarMode

    /**
     * @param           $campaignId
     * @return          array
     * @throws          Exception
     *
     * @creationDate    07/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get deliveries for activity campaign
     */
    private static function GetInfoDeliveries_ActivityMode($campaignId) {
        /* Variables    */
        global $DB;
        $params     = null;
        $rdo        = null;
        $sql        = null;
        $deliveries = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaignId;

            /* SQL Instruction  */
            $sql = " SELECT	am.id,
                            am.microid,
                            am.afterenrol,
                            am.aftercompletion,
                            am.tocomplete,
                            am.afternotcompletion,
                            am.notcomplete
                     FROM	{microlearning_activity_mode} am
                     WHERE	am.microid = :campaign ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Add Delivery */
                    $deliveries[$instance->id] = $instance;
                }//if_Rdo
            }//if_rdo

            return $deliveries;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfoDeliveries_ActivityMode

    /**
     * @param           $newUsers
     * @param           $deliveries
     * @param           $courseId
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add users to the activity campaign
     */
    private static function AddUsers_ActivityMode($newUsers,$deliveries,$courseId) {
        global $DB;
        $rdoEnrol       = null;
        $days           = null;
        $date           = null;
        $usersEnrol     = null;
        $delivery       = null;
        $trans          = null;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Get Time Enrol   */
            foreach ($newUsers as $user) {
                /* Get time enrol   */
                $usersEnrol = self::GetEnrollment_User($newUsers,$courseId);
            }//for_users

            /* Add new Users for each delivery  */
            foreach ($deliveries as $infoDelivery) {
                foreach ($newUsers as $user) {
                    /* New Delivery */
                    $delivery = new stdClass();
                    $delivery->microid      = $infoDelivery->microid;
                    $delivery->micromodeid  = $infoDelivery->id;
                    $delivery->userid       = $user;
                    $delivery->sent         = 0;

                    if ($infoDelivery->afterenrol) {
                        if (array_key_exists($user,$usersEnrol)) {
                            /* Date to send the delivery    */
                            $days = (60*60*24)*$infoDelivery->afterenrol;
                            $date = $usersEnrol[$user] +  $days;

                            $delivery->timetosend = $date;
                        }//if_exists
                    }////if_afterenrol

                    if ($infoDelivery->afternotcompletion) {
                        if (array_key_exists($user,$usersEnrol)) {
                            /* Date to send the delivery    */
                            $days = (60*60*24)*$infoDelivery->afternotcompletion;
                            $date = $usersEnrol[$user] +  $days;

                            $delivery->timetosend = $date;
                        }//if_exists
                    }//if_aftercompletion

                    /* Execute  */
                    $DB->insert_record('microlearning_deliveries',$delivery);
                }//for_users
            }//for_Deliveries

            /* Allow Commit */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//AddUsers_ActivityMode

    /**
     * @param           $users
     * @param           $course
     * @return          array
     * @throws          Exception
     *
     * @creationDate    07/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the time enrol
     */
    private static function GetEnrollment_User($users,$course){
        /* Variables    */
        global $DB;
        $usersEnrol = array();
        $usersIn    = null;
        $params      = null;
        $sql         = null;
        $rdo         = null;

        try {
            /* Get Users In     */
            $usersIn = implode(',',$users);

            /* Search Criteria  */
            $params = array();
            $params['course'] = $course;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT ue.userid,
                                ue.timestart
                     FROM		{user_enrolments}	ue
                        JOIN	{enrol}				e	ON 	e.id 		= ue.enrolid
                                                        AND	e.courseid 	= :course
                                                        AND	e.status	= 0
                     WHERE		ue.userid IN ($usersIn) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $user) {
                    $usersEnrol[$user->userid] = $user->timestart;
                }//for_each_user
            }//if_rdo

            return $usersEnrol;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetEnrollment_User
}//class_Micro_Users