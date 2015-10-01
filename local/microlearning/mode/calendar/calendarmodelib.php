<?php
/**
 * Micro Learning  Calendar Mode - Library
 *
 * @package         local/microlearning
 * @subpackage      mode/calendar
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    13/10/2014
 * @author          eFaktor     (fbv)
 *
 */
define('CALENDAR_DATE_TO_SEND',1);
define('CALENDAR_X_DAYS',2);

class Calendar_Mode {
    /**
     * @static
     * @param           $calendar_mode
     * @param           $users_campaign
     * @param           $activities_lst
     * @param           $activities_type
     * @return          bool
     *
     * @creationDate    13/10/2014
     * @author          eFaktor        (fbv)
     *
     * Description
     * Create a new 'Calendar Mode' instance with all its options
     */
    public static function CreateDelivery_CalendarMode($calendar_mode,$users_campaign,$activities_lst,$activities_type) {
        /* Variables    */
        global $DB;

        /* Start Transaction    */
        $transaction = $DB->start_delegated_transaction();
        try {
            /* First    -- Insert Calendar Mode Options     */
            /* Generate Token */
            $calendar_mode->microkey = self::GenerateCalendarMode_MicroKey();
            $calendar_mode_id = $DB->insert_record('microlearning_calendar_mode',$calendar_mode);
            /* Second   -- Insert Activities Selected       */
            self::AddActivities_toDelivery($calendar_mode->microid,$calendar_mode_id,$activities_lst,$activities_type,$calendar_mode->microkey);
            /* Finally  --  Insert Deliveries Users          */
            self::AddUsers_ToDelivery($calendar_mode,$calendar_mode_id,$users_campaign);

            /* Commit Transaction   */
            $transaction->allow_commit();
            return true;
        }catch (Exception $ex) {
            /* Rollback Transaction  */
            $transaction->rollback($ex);

            return false;
        }//try_catch
    }//CreateDelivery_CalendarMode

    /**
     * @param           $calendar_mode
     * @param           $users_campaign
     * @param           $activities_lst
     * @param           $activities_type
     * @return          bool
     *
     * @creationDate    24/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the delivery connected to the campaign
     */
    public static function UpdateDelivery_CalendarMode($calendar_mode,$users_campaign,$activities_lst,$activities_type) {
        /* Variables    */
        global $DB;

        /* Start Transaction    */
        $transaction = $DB->start_delegated_transaction();

        try {
            /* First    -- Update Calendar Mode Options     */
            $calendar_mode->microkey = self::GenerateCalendarMode_MicroKey();
            $DB->update_record('microlearning_calendar_mode',$calendar_mode);

            /* Add Activities Selected  */
            /* 1. Remove old activities selected    */
            $DB->delete_records('microlearning_activities',array('microid' => $calendar_mode->microid,'micromodeid' => $calendar_mode->id));
            /* 2. Add the new activities selected   */
            self::AddActivities_toDelivery($calendar_mode->microid,$calendar_mode->id,$activities_lst,$activities_type,$calendar_mode->microkey);
            /* 3. Delete the old usrs - activities  */
            $DB->delete_records('microlearning_deliveries',array('microid' => $calendar_mode->microid,'micromodeid' => $calendar_mode->id,'sent' => '0'));
            /* Finally  --  Insert Deliveries Users          */
            self::AddUsers_ToDelivery($calendar_mode,$calendar_mode->id,$users_campaign);

            /* Commit Transaction   */
            $transaction->allow_commit();
            return true;
        }catch (Exception $ex) {
            /* Rollback Transaction  */
            $transaction->rollback($ex);

            return false;
        }//try_catch
    }//UpdateDelivery_CalendarMode

    /**
     * @static
     * @param           $calendar_id
     * @param           $course_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    13/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete 'Calendar Mode' Instance
     */
    public static function Delete_CalendarMode($calendar_id,$course_id) {
        /* Variables    */
        global $DB;

        /* Start Transaction    */
        $transaction = $DB->start_delegated_transaction();
        try {
            /* First    --  Deleted Activities                      */
            $DB->delete_records('microlearning_activities',array('microid' => $calendar_id));
            /* Second   --  Deleted Calendar Mode Instance          */
            $DB->delete_records('microlearning_calendar_mode',array('microid' => $calendar_id));
            /* Third    --  Deleted Calendar Mode Deliveries        */
            $DB->delete_records('microlearning_deliveries',array('microid' => $calendar_id));
            /* Fourth    --  Deleted Calendar Mode Users            */
            $DB->delete_records('microlearning_users',array('microid' => $calendar_id));
            /* Finally  --  Deleted Microlearning Instance          */
            $DB->delete_records('microlearning',array('id' => $calendar_id,'courseid' => $course_id));

            /* Commit Transaction   */
            $transaction->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback Transaction  */
            $transaction->rollback($ex);

            throw $ex;
        }//try_catch
    }//Delete_CalendarMode

    /**
     * @static
     * @param               $campaign_id
     * @param               $sort
     * @param               $limit_from
     * @param               $limit_num
     * @return              array|null
     * @throws              Exception
     *
     * @creationDate        16/10/2014
     * @author              eFaktor         (fbv)
     *
     * Description
     * Get all the deliveries (eMails) connected with the campaign
     */
    public static function Get_CalendarDeliveries($campaign_id,$sort,$limit_from,$limit_num) {
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
                     FROM		{microlearning_calendar_mode}	cm
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
    }//Get_CalendarDeliveries

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
    public static function Get_TotalCalendarDeliveries($campaign_id) {
        /* Variables    */
        global $DB;

        try {
            return $DB->count_records('microlearning_calendar_mode',array('microid' => $campaign_id));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TotalCalendarDeliveries

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
     * Add the actions buttons in the 'Calendar Mode Deliveries' page
     */
    public static function AddButtons_CalendarDeliveries_Menu($course_id,$mode_learning,$campaign_id) {
        /* Variables    */
        $out            = '';
        $add_url        = new moodle_url('/local/microlearning/mode/calendar/calendar.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));;
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
    }//AddButtons_CalendarDeliveries_Menu

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
                     FROM		{microlearning_calendar_mode}
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
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the detail connected with a specific delivery
     */
    public static function GetDeliveryInfo_CalendarMode($campaign_id,$delivery_id) {
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
            $sql = " SELECT 	mcm.id,
                                mcm.microid,
                                mcm.datesend,
                                mcm.dateafter,
                                mcm.daysafter,
                                mcm.activityafter,
                                mcm.subject,
                                mcm.body,
                                GROUP_CONCAT(DISTINCT mact.activityid ORDER BY mact.name) as 'activities'
                     FROM		{microlearning_calendar_mode} 	mcm
                        JOIN	{microlearning_activities}		mact	ON		mact.micromodeid 	= mcm.id
                                                                        AND		mact.microid		= mcm.microid
                     WHERE		mcm.id 		= :delivery
                        AND		mcm.microid = :campaign ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $delivery_info = new stdClass();
                $delivery_info->campaign        = $rdo->microid;
                $delivery_info->delivery        = $rdo->id;
                $delivery_info->date_send       = $rdo->datesend;
                $delivery_info->date_after      = $rdo->dateafter;
                $delivery_info->x_days          = $rdo->daysafter;
                $delivery_info->act_not_done    = $rdo->activityafter;
                $delivery_info->subject         = $rdo->subject;
                $delivery_info->body            = $rdo->body;
                $delivery_info->bodyformat      = FORMAT_HTML;
                $delivery_info->activities      = explode(',',$rdo->activities);

                if ($rdo->datesend) {
                    $delivery_info->sel_date        = CALENDAR_DATE_TO_SEND;
                }else {
                    $delivery_info->sel_date        = CALENDAR_X_DAYS;
                }
            }//if_rdo

            return $delivery_info;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetDeliveryInfo_CalendarMode


    /**
     * @param           $data
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    01/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Duplicate campaign. Calendar Mode special behaviour
     * Don't duplicate user deliveries
     * Send options all null
     * Deactivate by default
     */
    public static function DuplicateCampaign($data) {
        /* Variables    */
        global $DB;
        $trans          = null;
        $newCampaign    = null;
        $delCampaign    = null;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* First Create New Campaign    */
            /* New Campaign         */
            $newCampaign = new stdClass();
            $newCampaign->courseid          = $data->id;
            $newCampaign->name              = $data->campaign;
            $newCampaign->type              = $data->type;
            $newCampaign->activate          = 0;
            $newCampaign->duplicated_from   = $data->cp;
            $newCampaign->timecreated       = time();

            /* Execute  */
            $newCampaign->id = $DB->insert_record('microlearning',$newCampaign);

            /* Duplicate Users Campaigns    */
            self::DuplicateUsers_NewCampaign($newCampaign->id,$data->cp,$DB);

            /* Duplicate Deliveries For New Campaign        */
            $delCampaign = self::DuplicateDeliveries_NewCampaign($newCampaign->id,$data->cp,$DB);

            /* Duplicate Activities for the new campaign    */
            self::DuplicateActivitiesDeliveries_NewCampaign($newCampaign->id,$data->cp,$delCampaign,$DB);

            /* Commit   */
            $trans->allow_commit();

            return $newCampaign->id;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_Catch
    }//DuplicateCampaign

    /**
     * @param           $campaignId
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    01/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the campaign can be activated or not
     */
    public static function CanBeActivated($campaignId) {
        /* Variables    */
        global $DB;
        $boolAct    = true;
        $params     = null;
        $sql        = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['id'] = $campaignId;

            /* Get campaign */
            $rdo = $DB->get_record('microlearning',$params);
            if ($rdo) {
                if ($rdo->duplicated_from) {
                    /* SQL Instruction  */
                    $sql = " SELECT	mi_cm.id
                             FROM	{microlearning_calendar_mode}	mi_cm
                             WHERE	mi_cm.microid = :id
                                AND	(mi_cm.datesend IS NULL OR mi_cm.datesend = 0)
                                AND (mi_cm.dateafter IS NULL OR mi_cm.dateafter = 0) ";

                    /* Execute  */
                    $rdo = $DB->get_records_sql($sql,$params);
                    if ($rdo) {
                        $boolAct = false;
                    }//if_Rdo
                }else {
                    $boolAct = true;
                }//if_Rdo
            }//if_Rdo

            return $boolAct;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CanBeActivated

    /************/
    /* PRIVATE */
    /************/

    /**
     * @param           $campaign_id
     * @param           $delivery_id
     * @param           $activities_lst
     * @param           $activities_type
     * @param           $modeCalendar
     * @throws          Exception
     *
     * @creationDate    24/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the activities to the delivery
     */
    private static function AddActivities_toDelivery($campaign_id,$delivery_id,$activities_lst,$activities_type,$modeCalendar) {
        /* Variables    */
        global $DB;
        $activity_selected = null;

        try {
            /* Insert Activities Selected */
            foreach ($activities_lst as $key=>$name) {
                $activity_selected = new stdClass();
                $activity_selected->microid      = $campaign_id;
                $activity_selected->micromodeid  = $delivery_id;
                $activity_selected->microkey     = self::GenerateActivitiesCalendarMode_MicroKey($modeCalendar);
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
     * @param           $calendar_mode
     * @param           $delivery_id
     * @param           $users_campaign
     * @throws          Exception
     *
     * @creationDate    24/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the users to the new Delivery
     */
    private static function AddUsers_ToDelivery($calendar_mode,$delivery_id,$users_campaign) {
        /* Variables    */
        global $DB;
        $days       = null;
        $date       = null;
        $delivery   = null;

        try {
            /* Insert Deliveries Users */
            foreach ($users_campaign as $key=>$user) {
                $delivery = new stdClass();
                $delivery->microid      = $calendar_mode->microid;
                $delivery->micromodeid  = $delivery_id;
                $delivery->userid       = $key;
                $delivery->sent         = 0;
                /* Calculate    */
                if ($calendar_mode->datesend) {
                    $delivery->timetosend   = $calendar_mode->datesend;
                }else {
                    $days = 60*60*24*$calendar_mode->daysafter;
                    $date = $calendar_mode->dateafter +  $days;

                    $delivery->timetosend = $date;
                }//$calendar_mode->date_send

                /* Execute  */
                $DB->insert_record('microlearning_deliveries',$delivery);
            }//foreach_user
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddUsers_ToDelivery

    /**
     * @param           $newCampaign
     * @param           $oldCampaign
     * @param           $DB
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    01/10/2015
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
     * @creationDate    01/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Duplicate deliveries for the new campaign
     */
    private static function DuplicateDeliveries_NewCampaign($newCampaign,$oldCampaign,$DB) {
        /* Variables    */
        $rdo            = null;
        $params         = null;
        $sql            = null;
        $infoDelivery   = null;
        $delCampaign    = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['microid'] = $oldCampaign;

            /* SQL Instruction  */
            $sql = " SELECT	  mi_cm.id,
                              mi_cm.daysafter,
                              mi_cm.activityafter,
                              mi_cm.subject,
                              mi_cm.body
                     FROM	  {microlearning_calendar_mode}	  mi_cm
                     WHERE	  mi_cm.microid = :microid ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Delivery Calendar Mode  */
                    $infoDelivery = new stdClass();
                    $infoDelivery->microid          = $newCampaign;
                    $infoDelivery->microkey         = self::GenerateCalendarMode_MicroKey();
                    $infoDelivery->datesend         = null;
                    $infoDelivery->dateafter        = null;
                    $infoDelivery->daysafter        = $instance->daysafter;
                    $infoDelivery->activityafter    = $instance->activityafter;
                    $infoDelivery->subject          = $instance->subject;
                    $infoDelivery->body             = $instance->body;

                    /* Duplicate Delivery Campaign  */
                    $infoDelivery->id = $DB->insert_record('microlearning_calendar_mode',$infoDelivery);

                    /* Add delivery */
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
     * @creationDate    01/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Duplicate activities connected with each delivery for the new campaign
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
                        $infoActDelivery->microkey     = self::GenerateActivitiesCalendarMode_MicroKey($newDelivery->microkey);
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
     * @return          string
     * @throws          Exception
     *
     * @creationDate    25/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate Micro Calendar Key
     */
    private static function GenerateCalendarMode_MicroKey() {
        /* Variables    */
        $modeCalendar = null;

        try {
            $modeCalendar = uniqid(mt_rand(),1) . '_' . time() . '_' . uniqid(mt_rand(),1);
            $modeCalendar = str_replace('/','.',Micro_Learning::GenerateHash_MicroLearning($modeCalendar));

            return $modeCalendar;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GenerateCalendarMode_MicroKey

    /**
     * @param           $modeCalendar
     * @return          string
     * @throws          Exception
     *
     * @creationDate    25/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate Micro Activity Calendar Key
     */
    private static function GenerateActivitiesCalendarMode_MicroKey($modeCalendar) {
        /* Variables    */
        $microActivity  = null;

        try {
            $microActivity = time() . '_' . uniqid(mt_rand(),1) . '_' . $modeCalendar . '_' . uniqid(mt_rand(),1);
            $microActivity = str_replace('/','.',Micro_Learning::GenerateHash_MicroLearning($microActivity));

            return $microActivity;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GenerateActivitiesCalendarMode_MicroKey
}//class_Calendar_Mode