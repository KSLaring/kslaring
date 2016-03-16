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
define('MAX_USERS',100);

class Micro_Users {

    /**
     * @param           $courseId
     * @param           $campaignId
     * @param           $addSearch
     * @param           $removeSearch
     *
     * @throws          Exception
     *
     * @creationDate    11/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise the micro user selectors
     */
    public static function Init_MicroUsers_Selector($courseId,$campaignId,$addSearch,$removeSearch) {
        /* Variables    */
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;
        $hashAdd    = null;
        $hashRemove = null;

        try {
            /* Initialise variables */
            $name       = 'micro_user_selector';
            $path       = '/local/microlearning/js/search.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpOne     = array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%');
            $grpTwo     = array('nomatchingusers', 'moodle', '%%SEARCHTERM%%');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpOne,$grpTwo,$grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
                             );

            /* Micro Users -- Add Selector - Potential Users    */
            self::Init_MicroUsers_AddSelector($courseId,$campaignId,$addSearch,$jsModule);
            /* Micro Users -- Remove Selector - Users Campaign  */
            self::Init_MicroUsers_RemoveSelector($courseId,$campaignId,$removeSearch,$jsModule);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_MicroUsers_Selector

    /**
     * @static
     * @param           $users_filter
     * @param           $course_id
     * @param           $mode_learning
     * @param           $campaign_id
     * @param           $started
     * @param           $addSearch
     * @param           $removeSearch
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    13/09/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the users with the filter criteria
     *
     * @updateDate      12/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the search field option
     * Improve the logical to get the users and the filter
     */
    public static function Get_SelectiorUsers_Filter($users_filter,$course_id,$mode_learning,$campaign_id,$started,$addSearch=null,$removeSearch=null) {
        /* Variables    */
        global $DB, $CFG;
        $in             = null;
        $sqlwhere       = null;
        $params         = null;
        $userlist       = null;

        $userCampaign   = null;
        $sqlCampaign    = null;
        $userPotential  = null;
        $sqlPotential   = null;


        try {
            // get the SQL filter
            list($sqlwhere, $params) = $users_filter->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

            /* Users */
            $userlist = array('acount'=>0, 'scount'=>0, 'ausers'=>array(), 'susers'=>array(), 'total_potential'=> 0, 'total_camp' => 0);

            /* Users Campaign */
            $userCampaign           = self::Get_UsersCampaign($course_id,$campaign_id,$removeSearch);
            $userlist['total_camp'] = count($userCampaign);
            if ($userCampaign) {
                $userCampaign = implode(',',$userCampaign);
                if ($sqlwhere) {
                    $sqlCampaign = $sqlwhere . ' AND id IN ('.  $userCampaign . ')';
                }else {
                    $sqlCampaign = ' WHERE id IN ('. $userCampaign . ')';
                }//if_sqlWhere

                $userlist['susers'] = $DB->get_records_select_menu('user', $sqlCampaign, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname',0, MAX_USERS);
                $userlist['scount'] = count($userlist['susers']);
            }//if_users_campaign

            /* Potential Users  */
            $userPotential                  = self::Get_PotentialUsers($course_id,$campaign_id,$addSearch);
            $userlist['total_potential']    = count($userPotential);
            if ($userPotential) {
                $userPotential = implode(',',$userPotential);
                if ($sqlwhere) {
                    $sqlPotential = $sqlwhere . ' AND id IN ('. $userPotential . ')';
                }else {
                    $sqlPotential = ' WHERE id IN ('. $userPotential . ')';
                }//if_sqlWhere

                $userlist['ausers'] = $DB->get_records_select_menu('user', $sqlPotential, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname', 0, MAX_USERS);
                $userlist['acount'] = count($userlist['ausers']);
            }//if_users_potential

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
     * @param           $filter
     * @param           $search
     * @param           $courseId
     * @param           $campaignId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    11/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find all users, that belong to the campaign and meet the criteria
     */
    public static function FindCampaign_MicroUsers_Selector($filter,$search,$courseId,$campaignId) {
        /* Variables    */
        global $DB, $CFG;
        $in             = null;
        $sqlwhere       = null;
        $params         = null;
        $userlist       = null;
        $schoices       = array();

        $userCampaign   = null;
        $total          = null;
        $sqlCampaign    = null;

        try {
            // get the SQL filter
            list($sqlwhere, $params) = $filter->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

            /* Users Campaign */
            $userCampaign   = self::Get_UsersCampaign($courseId,$campaignId,$search);
            $total          = count($userCampaign);
            if ($userCampaign) {
                $userCampaign = implode(',',$userCampaign);
                if ($sqlwhere) {
                    $sqlCampaign = $sqlwhere . ' AND id IN ('.  $userCampaign . ')';
                }else {
                    $sqlCampaign = ' WHERE id IN ('. $userCampaign . ')';
                }

                $userlist = $DB->get_records_select_menu('user', $sqlCampaign, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname',0, MAX_USERS);
            }//if_users_campaign

            /* Users */
            if ($userlist) {
                $a = new stdClass();
                $a->total       = $total;
                $a->count       = count($userlist);
                $schoices[0]    = get_string('allselectedusers', 'bulkusers', $a);
                $schoices       = $schoices + $userlist;
            }else {
                $schoices[-1] = get_string('noselectedusers', 'bulkusers');
            }//if_users_list

            return $schoices;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindCampaign_MicroUsers_Selector

    /**
     * @param           $filter
     * @param           $search
     * @param           $courseId
     * @param           $campaignId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    11/11/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Fin all potential users that meet the criteria
     */
    public static function FindPotential_MicroUsers_Selector($filter,$search,$courseId,$campaignId) {
        /* Variables    */
        global $DB, $CFG;
        $in             = null;
        $sqlwhere       = null;
        $params         = null;
        $userlist       = null;
        $achoices       = array();

        $userPotential  = null;
        $total          = null;
        $sqlPotential   = null;

        try {
            // get the SQL filter
            list($sqlwhere, $params) = $filter->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

            /* Potential Users */
            $userPotential   = self::Get_PotentialUsers($courseId,$campaignId,$search);
            $total          = count($userPotential);
            if ($userPotential) {
                $userPotential = implode(',',$userPotential);
                if ($sqlwhere) {
                    $sqlPotential = $sqlwhere . ' AND id IN ('.  $userPotential . ')';
                }else {
                    $sqlPotential = ' WHERE id IN ('. $userPotential . ')';
                }

                $userlist = $DB->get_records_select_menu('user', $sqlPotential, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname',0, MAX_USERS);
            }//if_userPotential

            /* Users */
            if ($userlist) {
                $a = new stdClass();
                $a->total       = $total;
                $a->count       = count($userlist);
                $achoices[0]    = get_string('allfilteredusers', 'bulkusers', $a);
                $achoices       = $achoices + $userlist;
            }else {
                $achoices[-1]   = get_string('nofilteredusers', 'bulkusers', $total);
            }//if_users_list

            return $achoices;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindPotential_MicroUsers_Selector


    /**
     * @param           $courseId
     * @param           $campaignId
     * @param           $mode
     * @param           $selUsers
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    11/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add users selected to the campaign
     */
    public static function AddUsers_Campaign($courseId,$campaignId,$mode,$selUsers) {
        /* Variables    */
        global $DB;
        $trans              = null;
        $microUser          = null;
        $microDeliveries    = null;
        $newUsers           = array();

        try {
            /* Start Transaction    */
            $trans = $DB->start_delegated_transaction();

            try {
                /* Add Users campaign */
                foreach ($selUsers as $user) {
                    /* New User */
                    $microUser = new stdClass();
                    $microUser->microid    = $campaignId;
                    $microUser->userid     = $user;

                    /* Execute  */
                    $DB->insert_record('microlearning_users',$microUser);

                    /* Save */
                    $newUsers[$user] = $user;
                }//for_eachUser

                /* Add the new users to sent */
                if ($newUsers) {
                    switch ($mode) {
                        case CALENDAR_MODE:
                            /* Get the Deliveries   */
                            $microDeliveries = self::GetInfoDeliveries_CalendarMode($campaignId);
                            /* Add Users        */
                            if ($microDeliveries) {
                                self::AddUsers_CalendarMode($newUsers,$microDeliveries);
                            }//deliveries

                            break;
                        case ACTIVITY_MODE:
                            /* Get the Deliveries   */
                            $microDeliveries = self::GetInfoDeliveries_ActivityMode($campaignId);
                            /* Add Users        */
                            if ($microDeliveries) {
                                self::AddUsers_ActivityMode($newUsers,$microDeliveries,$courseId);
                            }//if_deliveries

                            break;
                        default:
                            break;
                    }//switch_mode
                }//if_newUsers

                /* Commit   */
                $trans->allow_commit();

                return true;
            }catch (Exception $exTrans) {
                /* Rollback */
                $trans->rollback($exTrans);

                throw $exTrans;
            }//try_catch
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddUsers_Campaign

    /**
     * @param           $courseId
     * @param           $campaignId
     * @param           $mode
     *
     * @throws          Exception
     *
     * @creationDate    12/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add all potential users to the campaign
     */
    public static function AddAllUsers_Campaign($courseId,$campaignId,$mode) {
        /* Variables    */
        $usersPotential      = null;

        try {
            /* Get All potential users */
            $usersPotential = self::Get_PotentialUsers($courseId,$campaignId);

            /* Add All Users    */
            self::AddUsers_Campaign($courseId,$campaignId,$mode,$usersPotential);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddAllUsers_Campaign

    /**
     * @param           $campaignId
     * @param           $lstUsers
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    12/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete users selected from the campaign
     */
    public static function DeleteUsers_Campaign($campaignId,$lstUsers) {
        /* Variables    */
        global $DB;
        $sql    = null;
        $params = null;
        $trans  = null;
        $users  = 0;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Users to delete */
            if ($lstUsers) {
                $users = implode(',',$lstUsers);
            }//if_lstUsers


            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaignId;

            /* First delete from microlearning_users    */
            /* SQL Instruction  */
            $sql = " DELETE
                     FROM	{microlearning_users}
                     WHERE	microid = :campaign
                        AND	userid IN ($users) ";

            /* Execute  */
            $DB->execute($sql,$params);

            /* Finally, delete from mirolearning deliveries */
            /* SQL Instruction  */
            $sql = " DELETE
                     FROM	{microlearning_deliveries}
                     WHERE	microid = :campaign
                        AND	userid IN ($users) ";
            /* Execute  */
            $DB->execute($sql,$params);

            /* Commit   */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_Catch
    }//DeleteUsers_Campaign

    /**
     * @param           $campaignId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    11/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete all users from the campaign
     */
    public static function DeleteAllUsers_Campaign($campaignId) {
        /* Variables    */
        global $DB;

        $sql    = null;
        $params = null;
        $trans  = null;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaignId;

            /* First delete from microlearning_users    */
            /* SQL Instruction  */
            $sql = " DELETE
                     FROM	{microlearning_users}
                     WHERE	microid = :campaign ";
            /* Execute  */
            $DB->execute($sql,$params);

            /* Finally, delete from mirolearning deliveries */
            /* SQL Instruction  */
            $sql = " DELETE
                     FROM	{microlearning_deliveries}
                     WHERE	microid = :campaign ";
            /* Execute  */
            $DB->execute($sql,$params);

            /* Commit   */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//DeleteAllUsers_Campaign


    /**
     * @param           $campaignId
     * @param           $courseId
     * @param           $mode
     *
     * @return          int
     * @throws          Exception
     *
     * Description
     * Get status for adding automatically new users
     */
    public static function GetStatus_AutomaticallyUsers($campaignId,$courseId,$mode) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['courseid']     = $courseId;
            $params['id']           = $campaignId;
            $params['type']         = $mode;

            /* Execute  */
            $rdo = $DB->get_record('microlearning',$params,'id,addusers');
            if ($rdo) {
                return $rdo->addusers;
            }else {
                return 0;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetStatus_AutomaticallyUsers

    /************/
    /* PRIVATE */
    /***********/

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
            $usersEnrol = self::GetEnrollment_User($newUsers,$courseId);

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
                     WHERE		ue.userid IN ($usersIn)
                        AND     ue.timestart != 0
                        AND     ue.timestart IS NOT NULL ";

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

    /**
     * @param           $courseId
     * @param           $campaignId
     * @param           $search
     * @param           $jsModule
     *
     * @throws          Exception
     *
     * @creationDate    11/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize Add Micro User Selector
     */
    private static function Init_MicroUsers_AddSelector($courseId,$campaignId,$search,$jsModule) {
        /* Variables    */
        global $USER,$PAGE;
        $options    = null;
        $hash       = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindPotential_MicroUsers_Selector';
            $options['name']        = 'ausers';
            $options['course']      = $courseId;
            $options['campaign']    = $campaignId;

            /* Connect Selector User    */
            $hash                       = md5(serialize($options));
            $USER->userselectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_micro_user_selector',
                array('addselect','ausers',$hash, $search),
                false,
                $jsModule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_MicroUsers_AddSelector

    /**
     * @param           $courseId
     * @param           $campaignId
     * @param           $search
     * @param           $jsModule
     *
     * @throws          Exception
     *
     * @creationDate    11/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise Remove Micro User Selector
     */
    private static function Init_MicroUsers_RemoveSelector($courseId,$campaignId,$search,$jsModule) {
        /* Variables    */
        global $USER,$PAGE;
        $options    = null;
        $hash       = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindCampaign_MicroUsers_Selector';
            $options['name']        = 'susers';
            $options['course']      = $courseId;
            $options['campaign']    = $campaignId;

            /* Connect Selector User    */
            $hash                       = md5(serialize($options));
            $USER->userselectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_micro_user_selector',
                array('removeselect','susers',$hash, $search),
                false,
                $jsModule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_MicroUsers_RemoveSelector

    /**
     * @param           $courseId
     * @param           $campaignId
     * @param           $search
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    10/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the potential users to add to the campaign
     */
    private static function Get_PotentialUsers($courseId,$campaignId,$search=null) {
        /* Variables    */
        global $DB;
        $potentialUsers = array();
        $params         = null;
        $rdo            = null;
        $sql            = null;
        $locate         = '';
        $extra          = null;

        try {
            /* Search criteria  */
            $params = array();
            $params['course']   = $courseId;
            $params['campaign'] = $campaignId;

            /* SQL Instruction  */
            $sql=  " SELECT		DISTINCT u.id
                     FROM		    {user}					u
                          JOIN	    {user_enrolments}		ue	ON	ue.userid 	= u.id
                          JOIN	    {enrol}					e	ON	e.id 		= ue.enrolid
                                                                AND	e.status 	= 0
                                                                AND e.courseid 	= :course
                          LEFT JOIN {microlearning_users}	mu	ON 	mu.userid 	= ue.userid
                                                                AND	mu.microid  = :campaign
                     WHERE		u.deleted = 0
                        AND 	mu.id IS NULL ";

            /* Search Option    */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= ") AND (";
                    }
                    $locate .= " LOCATE('" . $str . "',u.firstname)
                                 OR
                                 LOCATE('" . $str . "',u.lastname)
                                 OR
                                 LOCATE('" . $str . "',CONCAT(u.firstname,' ',u.lastname))
                                 OR
                                 LOCATE('". $str . "',u.email) ";
                }//if_search_opt

                $sql .= " 	AND ($locate) ";
            }//if_search

            /* Execute          */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $potentialUsers[$instance->id] = $instance->id;
                }//for_rdo
            }//if_rdo

            return $potentialUsers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_PotentialUsers

    /**
     * @param           $courseId
     * @param           $campaignId
     * @param           $search
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    10/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the users that are already connected with the campaign
     */
    private static function Get_UsersCampaign($courseId,$campaignId,$search=null) {
        /* variables    */
        global $DB;
        $usersCampaign  = array();
        $params         = null;
        $rdo            = null;
        $sql            = null;
        $locate         = '';
        $extra          = null;

        try {
            /* Search criteria  */
            $params = array();
            $params['course']   = $courseId;
            $params['campaign'] = $campaignId;

            /* SQL Instruction  */
            $sql =  " SELECT	DISTINCT u.id
                      FROM		  {user}				u
                          JOIN	  {user_enrolments}		ue	ON	ue.userid 	= u.id
                          JOIN	  {enrol}				e	ON	e.id 		= ue.enrolid
                                                            AND	e.status 	= 0
                                                            AND e.courseid 	= :course
                          JOIN 	  {microlearning_users}	mu	ON 	mu.userid 	= ue.userid
                                                            AND	mu.microid  = :campaign
                      WHERE		  u.deleted = 0 ";

            /* Search Option    */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= ") AND (";
                    }
                    $locate .= " LOCATE('" . $str . "',u.firstname)
                                 OR
                                 LOCATE('" . $str . "',u.lastname)
                                 OR
                                 LOCATE('" . $str . "',CONCAT(u.firstname,' ',u.lastname))
                                 OR
                                 LOCATE('". $str . "',u.email) ";
                }//if_search_opt

                $sql .= " 	AND ($locate) ";
            }//if_search

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $usersCampaign[$instance->id] = $instance->id;
                }//for_rdo
            }//if_rdo

            return $usersCampaign;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_UsersCampaign
}//class_Micro_Users