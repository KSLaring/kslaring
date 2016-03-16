<?php
/**
 * Micro Learning  Activity Mode - Library
 *
 * @package         local/microlearning
 * @subpackage      mode/activity
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    13/10/2014
 * @author          eFaktor     (fbv)
 *
 */

define('ACTIVITY_X_DAYS_AFTER_ENROL',1);
define('ACTIVITY_X_DAYS_AFTER_ACT',2);
define('ACTIVITY_NOT_DONE_AFTER',3);

class Activity_Mode {
    /**
     * @static
     * @param           $activity_mode
     * @param           $users_campaign
     * @param           $activities_lst
     * @param           $activities_type
     * @param           $course
     * @return          bool
     *
     * @creationDate    13/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create a new 'Activity Mode' instance with all its options
     */
    public static function CreateDelivery_ActivityMode($activity_mode,$users_campaign,$activities_lst,$activities_type,$course) {
        /* Variables    */
        global $DB;

        /* Start Transaction    */
        $transaction = $DB->start_delegated_transaction();
        try {
            /* First    -- Insert Activity Mode Options     */
            /* Generate Token */
            $activity_mode->microkey = self::GenerateActivityMode_MicroKey();
            $activity_mode_id = $DB->insert_record('microlearning_activity_mode',$activity_mode);
            /* Second   -- Insert Activities Selected       */
            self::AddActivities_toDelivery($activity_mode->microid,$activity_mode_id,$activities_lst,$activities_type,$activity_mode->microkey);
            /* Finally  --  Insert Deliveries Users          */
            self::AddUsers_ToDelivery($activity_mode,$activity_mode_id,$users_campaign,$course);

            /* Commit Transaction   */
            $transaction->allow_commit();
            return true;
        }catch (Exception $ex) {
            /* Rollback Transaction  */
            $transaction->rollback($ex);
            return false;
        }//try_catch
    }//CreateDelivery_ActivityMode

    /**
     * @param           $activity_mode
     * @param           $users_campaign
     * @param           $activities_lst
     * @param           $activities_type
     * @param           $course
     * @return          bool
     *
     * @creationDate    25/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update 'Activity Mode' instance with the new information
     */
    public static function UpdateDelivery_ActivityMode($activity_mode,$users_campaign,$activities_lst,$activities_type,$course) {
        /* Variables    */
        global $DB;

        /* Start Transaction    */
        $transaction = $DB->start_delegated_transaction();
        try {
            /* First    -- Update Activity Mode Options     */
            $DB->update_record('microlearning_activity_mode',$activity_mode);
            /* Get Micro Key                                */
            $rdo = $DB->get_record('microlearning_activity_mode',array('id' => $activity_mode->id, 'microid' => $activity_mode->microid),'microkey');
            /* Add Activities Selected  */
            /* 1. Remove old activities selected    */
            $DB->delete_records('microlearning_activities',array('microid' => $activity_mode->microid,'micromodeid' => $activity_mode->id));
            /* 2. Add the new activities selected   */
            self::AddActivities_toDelivery($activity_mode->microid,$activity_mode->id,$activities_lst,$activities_type,$rdo->microkey);
            /* 3. Delete the old usrs - activities  */
            $DB->delete_records('microlearning_deliveries',array('microid' => $activity_mode->microid,'micromodeid' => $activity_mode->id,'sent' => '0'));
            /* Finally  --  Insert Deliveries Users          */
            self::AddUsers_ToDelivery($activity_mode,$activity_mode->id,$users_campaign,$course);

            /* Commit Transaction   */
            $transaction->allow_commit();
            return true;
        }catch (Exception $ex) {
            /* Rollback Transaction  */
            $transaction->rollback($ex);
            return false;
        }//try_catch
    }//UpdateDelivery_ActivityMode

    /**
     * @static
     * @param           $activity_mode_id
     * @param           $course_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    13/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete 'Activity Mode' instance
     */
    public static function Delete_ActivityMode($activity_mode_id,$course_id) {
        /* Variables    */
        global $DB;

        /* Start Transaction    */
        $transaction = $DB->start_delegated_transaction();
        try {
            /* First    --  Deleted Activities                      */
            $DB->delete_records('microlearning_activities',array('microid' => $activity_mode_id));
            /* Second   --  Deleted Activity Mode Instance          */
            $DB->delete_records('microlearning_activity_mode',array('microid' => $activity_mode_id));
            /* Third    --  Deleted Activity Mode Deliveries        */
            $DB->delete_records('microlearning_deliveries',array('microid' => $activity_mode_id));
            /* Fourth    --  Deleted Activity Mode Users            */
            $DB->delete_records('microlearning_users',array('microid' => $activity_mode_id));
            /* Finally  --  Deleted Microlearning Instance          */
            $DB->delete_records('microlearning',array('id' => $activity_mode_id,'courseid' => $course_id));

            /* Commit Transaction   */
            $transaction->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback Transaction  */
            $transaction->rollback($ex);

            throw $ex;
        }//try_catch
    }//Delete_ActivityMode

    /**
     * @static
     * @param           $campaign_id
     * @param           $sort
     * @param           $limit_from
     * @param           $limit_num
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    17/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the deliveries (eMails) connected with the campaign
     */
    public static function Get_ActivityDeliveries($campaign_id,$sort,$limit_from,$limit_num) {
        /* Variables    */
        global $DB;
        $delivery       = null;
        $delivery_lst   = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['microid'] = $campaign_id;

            /* SQL Instruction  */
            $sql = " SELECT		cm.id,
                                cm.microid,
                                cm.subject,
                                cm.body,
                                GROUP_CONCAT(DISTINCT act.name ORDER BY act.name SEPARATOR ', ') as 'act_included',
                                IF(cm.timesent,cm.timesent,0) as 'time_sent'
                     FROM		{microlearning_activity_mode}	cm
                        JOIN	{microlearning_activities}		act	ON 	act.micromodeid = cm.id
                                                                    AND	act.microid		= cm.microid
                     WHERE		cm.microid = :microid
                     GROUP BY	cm.id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params,$limit_from,$limit_num);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $delivery = new stdClass();
                    $delivery->id           = $instance->id;
                    $delivery->campaign     = $instance->microid;
                    $delivery->subject      = $instance->subject;
                    $delivery->body         = $instance->body;
                    $delivery->activities   = $instance->act_included;
                    $delivery->timesent     = $instance->time_sent;

                    $delivery_lst[$instance->id] = $delivery;
                }//for_rdo
            }//if_rdo

            return $delivery_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_ActivityDeliveries

    /**
     * @static
     * @param           $campaign_id
     * @return          int
     * @throws          Exception
     *
     * @creationDate    17/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get how many deliveries (eMails) are connected with the campaign
     */
    public static function Get_TotalActivityDeliveries($campaign_id) {
        /* Variables    */
        global $DB;

        try {
            return $DB->count_records('microlearning_activity_mode',array('microid' => $campaign_id));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TotalActivityDeliveries

    /**
     * @static
     * @param           $course_id
     * @param           $mode_learning
     * @param           $campaign_id
     * @return          string
     * @throws          Exception
     *
     * @creationDate    17/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the actions buttons in the 'Activity Mode Deliveries' page
     */
    public static function AddButtons_ActivityDeliveries_Menu($course_id,$mode_learning,$campaign_id) {
        /* Variables    */
        $out            = '';
        $add_url        = new moodle_url('/local/microlearning/mode/activity/activity.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));;
        $campaign_url   = new moodle_url('/local/microlearning/index.php',array('id'=>$course_id));
        $course_url     = new moodle_url('/course/view.php',array('id' => $course_id));

        try {
            $out .= html_writer::start_div('micro_deliveries_table');
                /* Button New Delivery  */
                $out .= html_writer::link($add_url,get_string('btn_new_delivery','local_microlearning'),array('class' => 'lnk_button'));
                /* Button Return Campaign Main Menu */
                $out .= html_writer::link($campaign_url,get_string('btn_campaign_return','local_microlearning'),array('class' => 'lnk_button'));
                /* Button Return Course             */
                $out .= html_writer::link($course_url,get_string('btn_course_return','local_microlearning'),array('class' => 'lnk_button'));
            $out .= html_writer::end_div();//micro_deliveries_table

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddButtons_ActivityDeliveries_Menu

    /**
     * @param           $campaign_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    21/11/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Return if the campaign has started or not
     */
    public static function HasStarted_Campaign($campaign_id) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaign_id;

            /* SQL Instruction  */
            $sql = " SELECT		count(timesent) as 'total'
                     FROM		{microlearning_activity_mode}
                     WHERE		microid = :campaign
                        AND		timesent IS NOT NULL ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->total) {
                    return true;
                }else {
                    return false;
                }//if_total
            }else {
                return false;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//HasStarted_Campaign

    /**
     * @param           $campaign_id
     * @param           $delivery_id
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    24/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the detail connected to a specific delivery
     */
    public static function GetDeliveryInfo_ActivityMode($campaign_id,$delivery_id) {
        /* Variables    */
        global $DB;
        $delivery_info  = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaign_id;
            $params['delivery'] = $delivery_id;

            /* SQL Instruction  */
            $sql = " SELECT 	mam.id,
                                mam.microid,
                                mam.afterenrol,
                                mam.aftercompletion,
                                mam.tocomplete,
                                mam.afternotcompletion,
                                mam.notcomplete,
                                mam.subject,
                                mam.body,
                                GROUP_CONCAT(DISTINCT mact.activityid ORDER BY mact.name) as 'activities'
                     FROM		{microlearning_activity_mode} 	mam
                        JOIN	{microlearning_activities}		mact	ON		mact.micromodeid 	= mam.id
                                                                        AND		mact.microid		= mam.microid
                     WHERE		mam.id 		= :delivery
                        AND		mam.microid = :campaign ";


            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $delivery_info = new stdClass();
                $delivery_info->campaign                = $rdo->microid;
                $delivery_info->delivery                = $rdo->id;
                $delivery_info->x_days_after_enrol      = $rdo->afterenrol;
                $delivery_info->x_days_after_completion = $rdo->aftercompletion;
                $delivery_info->act_after_completion    = $rdo->tocomplete;
                $delivery_info->x_days_not_done         = $rdo->afternotcompletion;
                $delivery_info->act_not_done            = $rdo->notcomplete;
                $delivery_info->subject                 = $rdo->subject;
                $delivery_info->body                    = $rdo->body;
                $delivery_info->bodyformat              = FORMAT_HTML;
                $delivery_info->activities              = explode(',',$rdo->activities);

                if ($rdo->afterenrol) {
                    $delivery_info->sel_opt         = ACTIVITY_X_DAYS_AFTER_ENROL;
                }elseif ($rdo->aftercompletion) {
                    $delivery_info->sel_opt         = ACTIVITY_X_DAYS_AFTER_ACT;
                }else {
                    $delivery_info->sel_opt         = ACTIVITY_NOT_DONE_AFTER;
                }
            }//if_rdo

            return $delivery_info;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetDeliveryInfo_ActivityMode

    /**
     * @param           $data
     * @return          bool|int
     * @throws          Exception
     *
     * @creationDate    30/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Duplicate campaign
     */
    public static function DuplicateCampaign($data) {
        /* Variables    */
        global $DB;
        $trans          = null;
        $newCampaign    = null;
        $delCampaign    = null;

        /* Start Transaction*/
        $trans = $DB->start_delegated_transaction();

        try {
            /* New Campaign         */
            $newCampaign = new stdClass();
            $newCampaign->courseid         = $data->id;
            $newCampaign->name             = $data->campaign;
            $newCampaign->type             = $data->type;
            $newCampaign->timecreated      = time();
            /* Execute  */
            $newCampaign->id = $DB->insert_record('microlearning',$newCampaign);

            /* Duplicate Users Campaign */
            self::DuplicateUsers_NewCampaign($newCampaign->id,$data->cp,$DB);

            /* Duplicate Deliveries for new Campaign    */
            $delCampaign = self::DuplicateDeliveries_NewCampaign($newCampaign->id,$data->cp,$DB);

            /* Duplicate Activities Deliveries for the New Campaign */
            self::DuplicateActivitiesDeliveries_NewCampaign($newCampaign->id,$data->cp,$delCampaign,$DB);

            /* Duplicate Users od each delivery for the new campaign    */
            self::DuplicateUsersDelivery_NewCampaign($newCampaign->id,$data->cp,$delCampaign,$DB);

            /* Commit   */
            $trans->allow_commit();

            return $newCampaign->id;
        }catch (Exception $ex) {
            /* Exception    */
            $trans->rollback($ex);

            throw $ex;
        }//try_Catch
    }//DuplicateCampaign

    /************/
    /* PRIVATE */
    /************/

    /**
     * @param           $campaign_id
     * @param           $delivery_id
     * @param           $activities_lst
     * @param           $activities_type
     * @param           $modeActivity
     * @throws          Exception
     *
     * @creationDate    24/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the activities to the delivery
     */
    private static function AddActivities_toDelivery($campaign_id,$delivery_id,$activities_lst,$activities_type,$modeActivity) {
        /* Variables    */
        global $DB;
        $activity_selected = null;

        try {
            /* Insert Activities Selected */
            foreach ($activities_lst as $key=>$name) {
                $activity_selected = new stdClass();
                $activity_selected->microid      = $campaign_id;
                $activity_selected->micromodeid  = $delivery_id;
                $activity_selected->microkey     = self::GenerateActivitiesActivityMode_MicroKey($modeActivity);
                $activity_selected->activityid   = $key;
                $activity_selected->name         = $name;
                $activity_selected->module       = $activities_type[$key];

                $DB->insert_record('microlearning_activities',$activity_selected);
            }//for_activities
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddActivities_toDelivery

    /**
     * @param           $activity_mode
     * @param           $delivery_id
     * @param           $users_campaign
     * @param           $course
     * @throws          Exception
     *
     * @creationDate    24/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the users to the new Delivery
     */
    private static function AddUsers_ToDelivery($activity_mode,$delivery_id,$users_campaign,$course) {
        /* Variables    */
        global $DB;
        $days           = null;
        $date           = null;
        $users_enrol    = null;
        $delivery       = null;

        try {
            /* Get the time enrol   */
            if ($activity_mode->afterenrol || $activity_mode->afternotcompletion) {
                $users_enrol = self::GetEnrollment_User($users_campaign,$course);
            }//if_days_afterenrol

            /* Insert Deliveries Users */
            foreach ($users_campaign as $key=>$user) {
                $delivery = new stdClass();
                $delivery->microid      = $activity_mode->microid;
                $delivery->micromodeid  = $delivery_id;
                $delivery->userid       = $key;
                $delivery->sent         = 0;

                if ($activity_mode->afterenrol) {
                    if (array_key_exists($key,$users_enrol)) {
                        /* Date to send the delivery    */
                        $days = 60*60*24*$activity_mode->afterenrol;
                        $date = $users_enrol[$key] +  $days;

                        $delivery->timetosend = $date;
                    }//if_exists
                }////if_afterenrol

                if ($activity_mode->afternotcompletion) {
                    if (array_key_exists($key,$users_enrol)) {
                        /* Date to send the delivery    */
                        $days = 60*60*24*$activity_mode->afternotcompletion;
                        $date = $users_enrol[$key] +  $days;

                        $delivery->timetosend = $date;
                    }//if_exists
                }//if_aftercompletion

                $DB->insert_record('microlearning_deliveries',$delivery);
            }//foreach_user
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddUsers_ToDelivery

    /**
     * @param           $user_lst
     * @param           $course
     * @return          null
     * @throws          Exception
     *
     * @creationDate    05/12/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get when the users were enrolled to the course
     */
    private static function GetEnrollment_User($user_lst,$course){
        /* Variables    */
        global $DB;
        $users_enrol = null;
        $lst_users   = implode(',',array_keys($user_lst));
        $params      = null;
        $sql         = null;
        $rdo         = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course_id'] = $course;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT  ue.id,
                                          ue.userid,
                                          ue.timestart
                     FROM		{user_enrolments}	ue
                        JOIN	{enrol}				e	ON 	e.id 		= ue.enrolid
                                                        AND	e.courseid 	= :course_id
                                                        AND	e.status	= 0
                     WHERE		ue.userid IN ($lst_users) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $user) {
                    $users_enrol[$user->userid] = $user->timestart;
                }//for_each_user
            }//if_rdo

            return $users_enrol;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetEnrollment_User

    /**
     * @param           $newCampaign
     * @param           $oldCampaign
     * @param           $DB
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    30/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Duplicate users campaign
     */
    private static function DuplicateUsers_NewCampaign($newCampaign,$oldCampaign,$DB) {
        /* Variables    */
        $rdo            = null;
        $infoMicroUser  = null;

        try {
            /* Get Users from old Campaign   */
            $rdo = $DB->get_records('microlearning_users',array('microid' => $oldCampaign),'userid','userid');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Campaign User   */
                    $infoMicroUser = new stdClass();
                    $infoMicroUser->microid = $newCampaign;
                    $infoMicroUser->userid = $instance->userid;

                    /* Duplicate User   */
                    $DB->insert_record('microlearning_users',$infoMicroUser);

                    /* Add User    */
                    $usersCampaign[$instance->userid] = $instance->userid;
                }//for_Rdo
            }//if_Rdo

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//DuplicateUsers_NewCampaign

    /**
     * @param           $newCampaign
     * @param           $oldCampaign
     * @param           $DB
     * @return          array
     * @throws          Exception
     *
     * @creationDate    30/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Duplicate deliveries for the new campaign
     */
    private static function DuplicateDeliveries_NewCampaign($newCampaign,$oldCampaign,$DB) {
        /* Variables    */
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $delCampaign    = array();
        $infoDelivery   = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['microid'] = $oldCampaign;

            /* SQL Instruction  */
            $sql = " SELECT	  mi_am.id,
                              mi_am.afterenrol,
                              mi_am.aftercompletion,
                              mi_am.tocomplete,
                              mi_am.afternotcompletion,
                              mi_am.notcomplete,
                              mi_am.subject,
                              mi_am.body
                     FROM		{microlearning_activity_mode}	mi_am
                     WHERE	  mi_am.microid = :microid ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Delivery Activity Mode */
                    $infoDelivery = new stdClass();
                    $infoDelivery->microid              = $newCampaign;
                    $infoDelivery->microkey             = self::GenerateActivityMode_MicroKey();
                    $infoDelivery->afterenrol           = $instance->afterenrol;
                    $infoDelivery->aftercompletion      = $instance->aftercompletion;
                    $infoDelivery->tocomplete           = $instance->tocomplete;
                    $infoDelivery->afternotcompletion   = $instance->afternotcompletion;
                    $infoDelivery->notcomplete          = $instance->notcomplete;
                    $infoDelivery->subject              = $instance->subject;
                    $infoDelivery->body                 = $instance->body;

                    /* Duplicate Delivery Campaign  */
                    $infoDelivery->id = $DB->insert_record('microlearning_activity_mode',$infoDelivery);

                    /* Add Delivery */
                    $delCampaign[$instance->id] = $infoDelivery;
                }//for_Rdo
            }//if_rdo

            return $delCampaign;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//DuplicateDeliveries_NewCampaign

    /**
     * @param           $newCampaign
     * @param           $oldCampaign
     * @param           $delCampaign
     * @param           $DB
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    30/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Duplicate the activities connected with each delivery for the new campaign
     */
    private static function DuplicateActivitiesDeliveries_NewCampaign($newCampaign,$oldCampaign,$delCampaign,$DB) {
        /* Variables    */
        $rdo                = null;
        $params             = null;
        $sql                = null;
        $infoActDelivery    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['microid'] = $oldCampaign;

            /* SQL Instruction */
            $sql = " SELECT	mi_act.activityid,
                            mi_act.name,
                            mi_act.module
                     FROM	{microlearning_activities}	mi_act
                     WHERE	mi_act.microid 		= :microid
                        AND	mi_act.micromodeid	= :micromodeid ";


            /* Duplicate the activities for each delivery   */
            foreach ($delCampaign as $oldDelId => $newDelivery) {
                $params['micromodeid']= $oldDelId;

                /* Execute  */
                $rdo = $DB->get_records_sql($sql,$params);
                if ($rdo) {
                    foreach ($rdo as $instance) {
                        /* New Activity Delivery    */
                        $infoActDelivery = new stdClass();
                        $infoActDelivery->microid      = $newCampaign;
                        $infoActDelivery->micromodeid  = $newDelivery->id;
                        $infoActDelivery->microkey     = self::GenerateActivitiesActivityMode_MicroKey($newDelivery->microkey);
                        $infoActDelivery->activityid   = $instance->activityid;
                        $infoActDelivery->name         = $instance->name;
                        $infoActDelivery->module       = $instance->module;

                        /* Duplicate Activity Delivery*/
                        $DB->insert_record('microlearning_activities',$infoActDelivery);
                    }
                }//if_Rdo
            }//for_deliveries

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//DuplicateActivitiesDeliveries_NewCampaign


    /**
     * @param           $newCampaign
     * @param           $oldCampaign
     * @param           $delCampaign
     * @param           $DB
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    30/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Duplicate users from each delivery for the new campaign
     */
    private static function DuplicateUsersDelivery_NewCampaign($newCampaign,$oldCampaign,$delCampaign,$DB) {
        /* Variables    */
        $rdo                = null;
        $params             = null;
        $sql                = null;
        $infoUserDelivery   = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['microid'] = $oldCampaign;

            /* SQL Instruction  */
            $sql = " SELECT	mi_d.userid,
                            mi_d.timetosend
                     FROM	{microlearning_deliveries}		mi_d
                     WHERE	mi_d.microid 		= :microid
                        AND	mi_d.micromodeid	= :micromodeid ";

            /* Duplicate users for each delivery   */
            foreach ($delCampaign as $oldDelId => $newDelivery) {
                $params['micromodeid']= $oldDelId;

                /* Execute  */
                $rdo = $DB->get_records_sql($sql,$params);
                if ($rdo) {
                    foreach ($rdo as $instance) {
                        /* New Info User Delivery   */
                        $infoUserDelivery = new stdClass();
                        $infoUserDelivery->microid      = $newCampaign;
                        $infoUserDelivery->micromodeid  = $newDelivery->id;
                        $infoUserDelivery->userid       = $instance->userid;
                        $infoUserDelivery->sent         = 0;
                        $infoUserDelivery->timetosend   = $instance->timetosend;
                        $infoUserDelivery->timesent     = null;
                        $infoUserDelivery->timemodified = null;

                        /* Duplicate Users Delivery*/
                        $DB->insert_record('microlearning_deliveries',$infoUserDelivery);
                    }
                }//if_Rdo
            }//for_deliveries

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//DuplicateUsersDelivery_NewCampaign

    /**
     * @return          string
     * @throws          Exception
     *
     * @creationDate    25/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate Micro Activity Mode Key
     */
    private static function GenerateActivityMode_MicroKey() {
        /* Variables    */
        $modeActivity = null;

        try {
            $modeActivity = uniqid(mt_rand(),1) . '_' . time() . '_' . uniqid(mt_rand(),1);
            $modeActivity = str_replace('/','.',Micro_Learning::GenerateHash_MicroLearning($modeActivity));

            return $modeActivity;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GenerateActivityMode_MicroKey

    /**
     * @param           $modeActivity
     * @return          string
     * @throws          Exception
     *
     * @creationDate    25/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate Micro Activity Activity Mode Key
     */
    private static function GenerateActivitiesActivityMode_MicroKey($modeActivity) {
        /* Variables    */
        $microActivity  = null;

        try {
            $microActivity = time() . '_' . uniqid(mt_rand(),1) . '_' . $modeActivity . '_' . uniqid(mt_rand(),1);
            $microActivity = str_replace('/','.',Micro_Learning::GenerateHash_MicroLearning($microActivity));

            return $microActivity;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GenerateActivitiesActivityMode_MicroKey
}//Activity_Mode