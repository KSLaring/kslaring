<?php
/**
 * Micro Learning  Activity Mode Cron - Library
 *
 * @package         local/microlearning
 * @subpackage      mode/activity
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    04/12/2014
 * @author          eFaktor     (fbv)
 *
 */

class Activity_ModeCron {

    /**
     * @return          bool
     *
     * @creationDate    05/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Activity Mode Cron
     */
    public static function cron() {
        /* Variables    */
        $activity_campaigns     = null;
        $activities_sent        = null;

        try {
            mtrace('Start Activity Mode Cron Campaigns: ' . time() );

            /* Get all the information connected with the campaigns */
            $activity_campaigns = self::GetCampaigns_ToCron();
            if ($activity_campaigns) {
                /* Send Deliveries          */
                $activities_sent = self::SendDeliveries_To_Users($activity_campaigns);
                /* Update Status Deliveries */
                if ($activities_sent) {
                    self::UpdateStatus_ActivityDeliveries($activities_sent);
                }//if_calendars_sent
            }//if_activity_campaigns

            mtrace('Finish Activity Mode Cron Campaigns: ' . time() );

            return true;
        }catch (Exception $ex) {
            return false;
        }//try_Catch
    }//cron

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    05/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the activity campaigns to send to the users
     */
    private static function GetCampaigns_ToCron() {
        /* Variables    */
        global $DB;
        $campaigns_cron = array();
        $to_sent        = time();
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $info           = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['to_sent'] = $to_sent;

            /* SQL Instruction  */
            $sql = " SELECT		mi.id															as 	'campaign',
                                c.id															as 	'course',
                                c.fullname														as 	'course_name',
                                mi.name															as	'campaign_name',
                                GROUP_CONCAT(DISTINCT mi_am.id ORDER BY mi_am.id SEPARATOR ',') as 	'deliveries'
                     FROM		{microlearning}					mi
                        JOIN	{course}						c		ON		c.id 				= mi.courseid
                                                                        AND		c.visible 			= 1
                        JOIN	{microlearning_activity_mode}	mi_am	ON		mi_am.microid		= mi.id
                        JOIN	{microlearning_users}			mi_u	ON		mi_u.microid		= mi_am.microid
                        JOIN	{user_express}					uep		ON		uep.userid			= mi_u.userid
                        JOIN	{user}							u		ON		u.id				= uep.userid
                                                                        AND		u.deleted			= 0
                        JOIN	{user_enrolments}				ue		ON		ue.userid			= u.id
                        JOIN	{enrol}							e		ON		e.id				= ue.enrolid
                                                                        AND		e.status			= 0
                                                                        AND		e.courseid			= c.id
                        JOIN	{microlearning_deliveries}		mi_d	ON		mi_d.microid		= mi_u.microid
                                                                        AND		mi_d.micromodeid	= mi_am.id
                                                                        AND		mi_d.userid			= ue.userid
                                                                        AND		mi_d.sent			= 0
                                                                        AND		(
                                                                                 mi_d.timetosend	<= :to_sent
                                                                                 OR
                                                                                 mi_d.timetosend	IS NULL
                                                                                )

                     WHERE		mi.activate = 1
                       AND		mi.type		= 2
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
     * @creationDate    05/12/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the information of all the deliveries to sent connected to the campaign
     */
    private static function GetDeliveriesCampaign_ToCron($campaign_id,$deliveries_lst,$course_id,$to_sent) {
        /* Variables    */
        global $DB;
        $deliveries_cron    = array();
        $params             = null;
        $sql                = null;
        $rdo                = null;
        $info               = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaign_id;

            /* SQL Instruction  */
            $sql = " SELECT		mi_am.id 			as 'delivery',
                                mi_am.microkey 		as 'modeactivity',
                                mi_am.aftercompletion,
                                mi_am.tocomplete,
                                mi_am.afternotcompletion,
                                mi_am.notcomplete,
                                mi_am.subject,
                                mi_am.body,
                                GROUP_CONCAT(DISTINCT mi_a.activityid ORDER BY mi_a.activityid SEPARATOR ',') as 	'activities'
                     FROM		{microlearning_activity_mode}	mi_am
                        JOIN	{microlearning_activities}		mi_a		ON	mi_a.microid 		= mi_am.microid
                                                                            AND	mi_a.micromodeid	= mi_am.id
                     WHERE		mi_am.microid = :campaign
                        AND		mi_am.id IN ($deliveries_lst)
                     GROUP BY	mi_am.id ";

            /* Execute      */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Delivery    */
                    $info = new stdClass();
                    $info->delivery             = $instance->delivery;
                    $info->subject              = $instance->subject;
                    $info->body                 = $instance->body;
                    $info->afternotcompletion   = $instance->afternotcompletion;
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
        $activities_cron    = array();
        $params             = null;
        $rdo                = null;
        $info               = null;

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
     * @creationDate    05/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the users to send the campaign
     */
    private static function GetUsersDelivery_ToCron($campaign_id,$delivery_info,$course_id,$to_sent) {
        /* Variables    */
        global $DB,$CFG;
        $users_cron     = array();
        $act_completed  = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $info           = null;

        try {
            /* Search Criteria      */
            $params = array();
            $params['campaign']     = $campaign_id;
            $params['delivery']     = $delivery_info->delivery;
            $params['course']       = $course_id;
            $params['ccc_course']   = $course_id;
            $params['to_sent']      = $to_sent;

            /* SQL Instruction      */
            $sql = " SELECT			mi_d.id,
                                    mi_d.userid   as 'user',
                                    GROUP_CONCAT(DISTINCT u_cc.moduleinstance ORDER BY u_cc.moduleinstance SEPARATOR ',') as 	'activities_completed',
                                    uep.token     as 'express',
                                    mi_d.message
                     FROM			{microlearning_deliveries}		mi_d
                        JOIN		{user_express}					uep		ON		uep.userid			= mi_d.userid
                        JOIN		{user}							u		ON		u.id				= uep.userid
                                                                            AND		u.deleted			= 0
                        JOIN		{user_enrolments}				ue		ON		ue.userid			= u.id
                        JOIN		{enrol}							e		ON		e.id				= ue.enrolid
                                                                            AND		e.status			= 0
                                                                            AND		e.courseid			= :course
                        LEFT JOIN	(
                                        SELECT		ccc.userid,
                                                    cc.moduleinstance
                                        FROM		{course_completion_crit_compl}	ccc
                                            JOIN	{course_completion_criteria}	cc		ON 	cc.id 				= ccc.criteriaid
                                                                                            AND	cc.course 			= ccc.course
                                                                                            AND	cc.moduleinstance 	IN ($delivery_info->activities)

                                        WHERE		ccc.course = :ccc_course
                                        GROUP BY 	ccc.userid
                                    ) u_cc ON u_cc.userid = ue.userid
                     WHERE		mi_d.microid 		= :campaign
                       AND		mi_d.micromodeid 	= :delivery
                       AND		mi_d.sent			= 0
                       AND		(
                                 mi_d.timetosend    <= :to_sent
                                 OR
                                 mi_d.timetosend	IS NULL
                                )
                     GROUP BY	mi_d.userid ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* User Info    */
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->user         = $instance->user;
                    $info->express      = $CFG->wwwroot . '/local/express_login/loginExpress.php/' . $instance->express . '/' . $delivery_info->modeactivity;
                    $info->toSend       = true;
                    $info->message      = $instance->message;

                    /* AFTER COMPLETION */
                    if ($delivery_info->aftercompletion) {
                        $act_completed = explode(',',$instance->activities_completed);
                        if (!in_array($delivery_info->tocomplete,$act_completed)) {
                            $info->toSend    = false;
                        }//if_activity_completed
                    }//if_delivery_after_completion

                    /* AFTER NO COMPLETED   */
                    if ($delivery_info->afternotcompletion) {
                        $act_completed = explode(',',$instance->activities_completed);
                        if (in_array($delivery_info->notcomplete,$act_completed)) {
                            $info->toSend    = false;
                        }//if_activity_completed
                    }//if_delivery_after_not_completed

                    $users_cron[$instance->user] = $info;
                }//for_rdo
            }//if_rdo

            return $users_cron;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsersDelivery_ToCron

    /**
     * @param           $activity_campaigns
     * @return          array
     * @throws          Exception
     *
     * @creationDate    05/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send the deliveries to the users
     */
    private static function SendDeliveries_To_Users($activity_campaigns) {
        /* Variables    */
        global $SITE;
        $deliveries_lst     = null;
        $activities_lst     = null;
        $users_lst          = null;
        $subject            = null;
        $body               = null;
        $activities_sent    = array();
        $users_sent         = null;

        try {
            mtrace('...... Start SendDeliveries_To_Users ' );
            foreach($activity_campaigns as $campaign) {
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
                        /* Add the extra message    */
                        if ($user_info->message) {
                            $body  = $user_info->message . '</br></br>';
                            $body .= $delivery->body;
                        }else {
                            $body  = $delivery->body;
                        }//if_message


                        /* AFTER NO COMPLETED   */
                        if (($delivery->afternotcompletion) && (!$user_info->toSend)) {
                            /* Completed Task -- Update Status && Not send eMail    */
                            $sent_info = new stdClass();
                            $sent_info->id                  = $user_info->id;
                            $sent_info->userid              = $user_info->user;

                            $users_sent[$user_info->user]   = $sent_info;
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
                    }//for_each_user

                    /* To Update Status */
                    if ($users_sent) {
                        $activity_sent_info                 = new stdClass();
                        $activity_sent_info->microid        = $campaign->campaign;
                        $activity_sent_info->micromodeid    = $delivery->delivery;
                        $activity_sent_info->users          = $users_sent;
                        $activity_sent_info->timesent       = time();

                        /* Save the Activity Mode Deliveries to update their status  */
                        $activities_sent[]                  = $activity_sent_info;
                    }//if_users_sent
                }//for_each_delivery
            }//for_Each_campaign
            mtrace('...... Finish SendDeliveries_To_Users ' );

            return $activities_sent;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SendDeliveries_To_Users

    /**
     * @param           $activities_sent
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    05/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the Activity Mode Deliveries and User Deliveries Status
     */
    private static function UpdateStatus_ActivityDeliveries($activities_sent) {
        /* Variables    */
        global $DB;
        /* Users to update their status*/
        $users_lst      = null;
        $activity_mode  = null;
        $delivery_user  = null;

        $transaction = $DB->start_delegated_transaction();
        try {
            /* First Update Status Activity Mode Deliveries  */
            foreach ($activities_sent as $activity) {
                mtrace('...... ... Update Activity Mode Delivery Status ' );
                /* Activity Delivery Status */
                $activity_mode              = new stdClass();
                $activity_mode->id          = $activity->micromodeid;
                $activity_mode->microid     = $activity->microid;
                $activity_mode->timesent    = $activity->timesent;
                /* Execute  */
                $DB->update_record('microlearning_activity_mode',$activity_mode);

                /* Delivery Users Status    */
                $delivery_user = new stdClass();
                $delivery_user->microid         = $activity->microid;
                $delivery_user->micromodeid     = $activity->micromodeid;
                $delivery_user->sent            = 1;
                $delivery_user->message         = null;
                $delivery_user->timesent        = $activity->timesent;
                $delivery_user->timemodified    = time();
                /* Finally, update the delivery status of each user  */
                $users_lst = $activity->users;
                foreach ($users_lst as $user) {
                    mtrace('...... ... ...... ... Update Delivery User Status ' );
                    $delivery_user->id      = $user->id;
                    $delivery_user->userid  = $user->userid;

                    /* Execute  */
                    $DB->update_record('microlearning_deliveries',$delivery_user);
                }//foreach_user
            }//for_each_activity_delivery

            /* Commit   */
            $transaction->allow_commit();
            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $transaction->rollback($ex);
            throw $ex;
        }//try_catch
    }//UpdateStatus_ActivityDeliveries
}//Activity_ModeCron
