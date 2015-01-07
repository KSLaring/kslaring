<?php
/**
 * Micro Learning  Calendar Mode Cron- Library
 *
 * @package         local/microlearning
 * @subpackage      mode/calendar
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    04/12/2014
 * @author          eFaktor     (fbv)
 *
 */
class Calendar_ModeCron {

    /**
     * @return bool
     *
     * @creationDate    25/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Calendar Mode Cron
     */
    public static function cron() {
        /* Variables    */
        $calendar_campaigns = null;
        $calendars_sent     = null;

        try {
            mtrace('Start Calendar Mode Cron Campaigns: ' . time() );

            /* Get all the information connected with the campaigns */
            $calendar_campaigns = self::GetCampaigns_ToCron();

            if ($calendar_campaigns) {
                /* Send Deliveries          */
                $calendars_sent = self::SendDeliveries_To_Users($calendar_campaigns);
                /* Update Status Deliveries */
                if ($calendars_sent) {
                    self::UpdateStatus_CalendarDeliveries($calendars_sent);
                }//if_calendars_sent
            }//if_calendar_campaigns

            mtrace(' Finish Calendar Mode Cron Campaigns:' . time() );

            return true;
        }catch (Exception $ex) {
            return false;
        }//try_catch
    }//cron

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    25/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the calendar campaigns to send to the users
     */
    private static function GetCampaigns_ToCron() {
        /* Variables    */
        global $DB;
        $campaigns_cron = array();
        $to_sent        = time();

        try {
            /* Search Criteria  */
            $params = array();
            $params['to_sent'] = $to_sent;

            /* SQL Instruction  */
            $sql = " SELECT		mi.id															as 	'campaign',
                                c.id															as 	'course',
                                c.fullname														as 	'course_name',
                                mi.name															as	'campaign_name',
                                GROUP_CONCAT(DISTINCT mi_cm.id ORDER BY mi_cm.id SEPARATOR ',') as 	'deliveries'
                     FROM		{microlearning}					mi
                        JOIN	{course}						c		ON		c.id 				= mi.courseid
                                                                        AND		c.visible 			= 1
                        JOIN	{microlearning_calendar_mode}	mi_cm	ON		mi_cm.microid		= mi.id
                        JOIN	{microlearning_users}			mi_u	ON		mi_u.microid		= mi_cm.microid
                        JOIN	{user_express}					uep		ON		uep.userid			= mi_u.userid
                        JOIN	{user}							u		ON		u.id				= uep.userid
                                                                        AND		u.deleted			= 0
                        JOIN	{user_enrolments}				ue		ON		ue.userid			= u.id
                        JOIN	{enrol}							e		ON		e.id				= ue.enrolid
                                                                        AND		e.status			= 0
                                                                        AND		e.courseid			= c.id
                        JOIN	{microlearning_deliveries}		mi_d	ON		mi_d.microid		= mi_u.microid
                                                                        AND		mi_d.micromodeid	= mi_cm.id
                                                                        AND		mi_d.userid			= ue.userid
                                                                        AND		mi_d.sent			= 0
                                                                        AND		mi_d.timetosend		<= :to_sent

                     WHERE		mi.activate = 1
                        AND		mi.type		= 1
                     GROUP BY	mi.id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Campaign    */
                    $info = new stdClass();
                    $info->campaign         = $instance->campaign;
                    $info->campaign_name    = $instance->campaign_name;
                    $info->course_name      = $instance->course_name;
                    /* Get Deliveries Info  */
                    $info->deliveries       = self::GetDeliveriesCampaign_ToCron($instance->campaign,$instance->deliveries,$instance->course,$to_sent);

                    $campaigns_cron[$instance->campaign] = $info;
                }//for_rdo_campaign
            }//if_rdo

            return $campaigns_cron;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCampaigns_ToCron

    /**
     * @param           $campaign_id
     * @param           $deliveries_lst
     * @param           $course_id
     * @param           $to_sent
     * @return          array
     * @throws          Exception
     *
     * @creationDate    25/11/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the information of all the deliveries to sent connected to the campaign
     */
    private static function GetDeliveriesCampaign_ToCron($campaign_id,$deliveries_lst,$course_id,$to_sent) {
        /* Variables    */
        global $DB;
        $deliveries_cron = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaign_id;

            /* SQL Instruction  */
            $sql = " SELECT		mi_cm.id 			as 'delivery',
                                mi_cm.microkey 		as 'modecalendar',
                                mi_cm.activityafter,
                                mi_cm.subject,
                                mi_cm.body,
                                GROUP_CONCAT(DISTINCT mi_a.activityid ORDER BY mi_a.activityid SEPARATOR ',') as 	'activities'
                     FROM		{microlearning_calendar_mode}	mi_cm
                        JOIN	{microlearning_activities}		mi_a	ON	mi_a.microid 		= mi_cm.microid
                                                                        AND	mi_a.micromodeid	= mi_cm.id
                     WHERE		mi_cm.microid = :campaign
                        AND		mi_cm.id IN ($deliveries_lst)
                     GROUP BY	mi_cm.id ";

            /* Execute          */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Delivery    */
                    $info = new stdClass();
                    $info->delivery         = $instance->delivery;
                    $info->subject          = $instance->subject;
                    $info->body             = $instance->body;
                    $info->activityafter    = $instance->activityafter;
                    /* Info Activities  */
                    $info->activities       = self::GetActivitiesDelivery_ToCron($campaign_id,$instance->delivery);
                    /* Info Users       */
                    $info->users            = self::GetUsersDelivery_ToCron($campaign_id,$instance,$course_id,$to_sent);

                    $deliveries_cron[$instance->delivery] = $info;
                }//for_rdo_delivery
            }//if_rdo

            return $deliveries_cron;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetDeliveriesCampaign_ToCron

    /**
     * @param           $campaign_id
     * @param           $delivery_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    25/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the information connected with the activities of the delivery
     */
    private static function GetActivitiesDelivery_ToCron($campaign_id,$delivery_id) {
        /* Variables    */
        global $DB;
        $activities_cron = array();

        try {
            /* Search Criteria  */
            $params['microid']      = $campaign_id;
            $params['micromodeid']  = $delivery_id;

            /* Execute  */
            $rdo = $DB->get_records('microlearning_activities',$params,'name','activityid,name,microkey');
            if ($rdo) {
                foreach ($rdo as $instance){
                    $info = new stdClass();
                    $info->name     = $instance->name;
                    $info->microkey = $instance->microkey;

                    $activities_cron[$instance->activityid] = $info;
                }//for_rdo
            }//if_rdo

            return $activities_cron;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetActivitiesDelivery_ToCron


    /**
     * @param           $campaign_id
     * @param           $delivery_info
     * @param           $course_id
     * @param           $to_sent
     * @return          array
     * @throws          Exception
     *
     * @creationDate    25/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the users to send the campaign
     */
    private static function GetUsersDelivery_ToCron($campaign_id,$delivery_info,$course_id,$to_sent) {
        /* Variables    */
        global $DB,$CFG;
        $users_cron = array();
        $act_completed = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign']     = $campaign_id;
            $params['delivery']     = $delivery_info->delivery;
            $params['course']       = $course_id;
            $params['ccc_course']   = $course_id;
            $params['to_sent']      = $to_sent;

            /* SQL Instruction  */
            $sql = " SELECT			mi_d.id,
                                    mi_d.userid  as 'user',
                                    GROUP_CONCAT(DISTINCT u_cc.moduleinstance ORDER BY u_cc.moduleinstance SEPARATOR ',') as 'activities_completed',
                                    uep.token    as 'express',
                                    mi_d.timetosend
                     FROM			{microlearning_deliveries}		mi_d
                        JOIN		{user_express}					uep		ON		uep.userid			= mi_d.userid
                        JOIN		{user}							u		ON		u.id				= uep.userid
                                                                            AND		u.deleted			= 0
                        JOIN		{user_enrolments}			    ue		ON		ue.userid			= u.id
                        JOIN		{enrol}							e		ON		e.id				= ue.enrolid
                                                                            AND		e.status			= 0
                                                                            AND		e.courseid			= :course
                        LEFT JOIN	(
                                        SELECT		ccc.userid,
                                                    cc.moduleinstance
                                        FROM		{course_completion_crit_compl}	    ccc
                                            JOIN	{course_completion_criteria}		cc		ON 	cc.id 				= ccc.criteriaid
                                                                                                AND	cc.course 			= ccc.course
                                                                                                AND	cc.moduleinstance 	IN ($delivery_info->activities)

                                        WHERE		ccc.course = :ccc_course
                                        GROUP BY 	ccc.userid
                                    ) u_cc ON u_cc.userid = ue.userid
                     WHERE		mi_d.microid        = :campaign
                        AND		mi_d.micromodeid 	= :delivery
                        AND		mi_d.sent			= 0
                        AND		mi_d.timetosend		<= :to_sent
                     GROUP BY	mi_d.userid ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* User Info    */
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->user         = $instance->user;
                    $info->express      = $CFG->wwwroot . '/local/express_login/loginExpress.php/' . $instance->express . '/' . $delivery_info->modecalendar;
                    $info->toSend       = true;

                    if ($delivery_info->activityafter) {
                        $act_completed = explode(',',$instance->activities_completed);
                        if (in_array($delivery_info->activityafter,$act_completed)) {
                            $info->toSend    = false;
                        }//if_activity_completed
                    }

                    $users_cron[$instance->user] = $info;
                }//for_rdo
            }//if_rdo

            return $users_cron;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsersDelivery_ToCron

    /**
     * @param               $calendar_campaigns
     * @return              array
     * @throws              Exception
     *
     * @creationDate        04/12/2014
     * @author              eFaktor         (fbv)
     *
     * Description
     * Send the deliveries to the users
     */
    private static function SendDeliveries_To_Users($calendar_campaigns) {
        /* Variables    */
        global $SITE;
        $deliveries_lst = null;
        $activities_lst = null;
        $users_lst      = null;
        $subject        = null;
        $body           = null;
        $calendar_sent  = array();
        $users_sent     = null;

        try {
            mtrace('...... Start SendDeliveries_To_Users ' );

            foreach($calendar_campaigns as $campaign) {
                /* Get the deliveries   */
                $deliveries_lst = $campaign->deliveries;
                foreach ($deliveries_lst as $delivery) {
                    /* To Update Status */
                    $users_sent = array();

                    /* Info to send     */
                    $subject        = $campaign->course_name . ' (' . $campaign->campaign_name . ') ' . $delivery->subject;

                    $activities_lst = $delivery->activities;
                    /* Get the Users    */
                    $users_lst = $delivery->users;
                    foreach ($users_lst as $user_info) {
                        /* Info to send */
                        $body           = $delivery->body;

                        /* ACTIVITY COMPLETED AFTER */
                        if (($delivery->activityafter) && (!$user_info->toSend)) {
                            /* Completed Task -- Update Status && Not send eMail    */
                            $sent_info = new stdClass();
                            $sent_info->id          = $user_info->id;
                            $sent_info->userid      = $user_info->user;

                            $users_sent[$user_info->user]  = $sent_info;
                        }else if ($user_info->toSend) {
                            foreach ($activities_lst as $activity) {
                                /* Build the url    */
                                $user_info->express .= '/' . $activity->microkey;
                                $html  = html_writer::link($user_info->express,$activity->name);
                                $html .= '</br>';

                                $body .= '</br></br>' . $html;
                            }//for_each_act

                            /* Send eMail to the User   */
                            $user = get_complete_user_data('id',$user_info->user);
                            if (email_to_user($user, $SITE->shortname, $subject, $body,$body)) {
                                /* Save the users that are received the delivery    */
                                $sent_info = new stdClass();
                                $sent_info->id          = $user_info->id;
                                $sent_info->userid      = $user->id;

                                $users_sent[$user->id]  = $sent_info;
                            }//if_email_sent
                        }//if_else
                    }//for_users

                    /* To Update Status */
                    if ($users_sent) {
                        $calendar_sent_info                 = new stdClass();
                        $calendar_sent_info->microid        = $campaign->campaign;
                        $calendar_sent_info->micromodeid    = $delivery->delivery;
                        $calendar_sent_info->users          = $users_sent;
                        $calendar_sent_info->timesent       = time();

                        /* Save the Calendar Deliveries to update their status  */
                        $calendar_sent[]                = $calendar_sent_info;
                    }//if_users_sent
                }//foreach_delivery
            }//for_each_campaign

            mtrace('...... Finish SendDeliveries_To_Users ' );

            return $calendar_sent;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SendDeliveries_To_Users

    /**
     * @param           $calendar_sent
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    05/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the Calendar Deliveries and User Deliveries Status
     */
    private static function UpdateStatus_CalendarDeliveries($calendar_sent) {
        /* Variables    */
        global $DB;
        /* Users to update their status*/
        $users_lst = null;

        $transaction = $DB->start_delegated_transaction();
        try {
            /* First Update Status Calendar Deliveries  */
            foreach ($calendar_sent as $calendar) {
                mtrace('...... ... Update Calendar Delivery Status ' );
                /* Calendar Delivery Status */
                $calendar_mode              = new stdClass();
                $calendar_mode->id          = $calendar->micromodeid;
                $calendar_mode->microid     = $calendar->microid;
                $calendar_mode->timesent    = $calendar->timesent;
                /* Execute  */
                $DB->update_record('microlearning_calendar_mode',$calendar_mode);

                /* Delivery Users Status    */
                $delivery_user = new stdClass();
                $delivery_user->microid         = $calendar->microid;
                $delivery_user->micromodeid     = $calendar->micromodeid;
                $delivery_user->sent            = 1;
                $delivery_user->timesent        = $calendar->timesent;
                $delivery_user->timemodified    = time();
                /* Finally, update the delivery status of each user  */
                $users_lst = $calendar->users;
                foreach ($users_lst as $user) {
                    mtrace('...... ... ...... ... Update Delivery User Status ' );
                    $delivery_user->id      = $user->id;
                    $delivery_user->userid  = $user->userid;

                    /* Execute  */
                    $DB->update_record('microlearning_deliveries',$delivery_user);
                }//foreach_user
            }//for_each_calendar_delivery

            /* Commit   */
            $transaction->allow_commit();
            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $transaction->rollback($ex);
            throw $ex;
        }//try_catch
    }//UpdateStatus_CalendarDeliveries
}//Calendar_ModeCron