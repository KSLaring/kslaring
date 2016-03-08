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
     * @throws          Exception
     *
     * @creationDate    05/12/2014
     * @author          eFaktor     (fbv)
     *
     * @updateDate      11/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Activity Mode
     * Optimize code and queries
     */
    public static function cron() {
        /* Variables    */
        $activitiesDeliveries   = null;
        $campaignSent           = null;
        $deliveriesSent         = null;

        try {
            mtrace('Start Activity Mode Cron Campaigns: ' . time() );

            /* Get Deliveries Activity Mode   */
            $activitiesDeliveries = self::GetDeliveriesActivity();
            if ($activitiesDeliveries) {
                /* Send Deliveries  */
                list($campaignSent,$deliveriesSent) = self::SendDeliveries($activitiesDeliveries);

                /* Update Status    */
                self::UpdateStatusActivityDeliveries($campaignSent,$deliveriesSent);
            }//if_ActivitiesDeliveries

            mtrace('Finish Activity Mode Cron Campaigns: ' . time() );
            return true;
        }catch (Exception $ex) {
            throw $ex;
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
     * @updateDate      11/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all deliveries with users to notify
     */
    private static function GetDeliveriesActivity() {
        /* Variables    */
        global $DB;
        $time               = null;
        $params             = null;
        $sql                = null;
        $rdo                = null;
        $deliveriesActivity = array();
        $activitiesDelivery = null;
        $usersDelivery      = null;

        try {
            /* Local Time   */
            $time = time();

            /* Search Criteria  */
            $params = array();
            $params['sent']     = 0;
            $params['time']     = $time;
            $params['activate'] = 1;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT	mi_am.id,
                                            mi_am.microid,
                                            mi_am.microkey,
                                            mi_am.aftercompletion,
                                            cc.id as 'criteria_tocomplete',
                                            mi_am.afternotcompletion,
                                            cc_n.id as 'criteria_notcomplete',
                                            mi_am.subject,
                                            mi_am.body,
                                            GROUP_CONCAT(DISTINCT mi_a.activityid ORDER BY mi_a.activityid SEPARATOR ',') 	as 	'activities',
                                            mi.courseid
                     FROM			{microlearning_activity_mode}	mi_am
                        JOIN		{microlearning}					mi		ON		mi.id 				= mi_am.microid
                                                                            AND		mi.activate			= :activate
                        JOIN		{microlearning_activities}		mi_a	ON		mi_a.microid		= mi.id
                                                                            AND		mi_a.micromodeid	= mi_am.id
                        JOIN		{microlearning_deliveries}		mi_d	ON		mi_d.microid		= mi_a.microid
                                                                            AND		mi_d.micromodeid	= mi_a.micromodeid
                        JOIN		{user_express}					uep		ON		uep.userid			= mi_d.userid
                        -- AFTER COMLPETION
                        LEFT JOIN	{course_completion_criteria}	cc		ON		cc.moduleinstance	= mi_am.tocomplete
                                                                            AND		cc.course			= mi.courseid
                        -- AFTER NOT COMPLETE
                        LEFT JOIN	{course_completion_criteria}	cc_n	ON		cc_n.moduleinstance	= mi_am.notcomplete
                                                                            AND		cc_n.course			= mi.courseid
                     WHERE		mi_d.sent 		 = :sent
                        AND		(
                                 mi_d.timetosend <= :time
                                 OR
                                 mi_d.timetosend IS NULL
                                )
                     GROUP BY mi_am.id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach($rdo as $instance) {
                    /* Get Activities   */
                    $activitiesDelivery = self::GetInfoActivities_Delivery($instance->microid,$instance->id,$instance->activities);
                    /* Get Users        */
                    $usersDelivery      = self::GetUsersDelivery($instance,$activitiesDelivery,$time);

                    /* Add Delivery */
                    if ($usersDelivery) {
                        $deliveriesCalendar[$instance->id] = $usersDelivery;
                    }//if_users
                }//for_rdo
            }//if_rdo

            return $deliveriesActivity;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetDeliveriesActivity

    /**
     * @param           $campaignId
     * @param           $deliveryId
     * @param           $activities
     * @return          array
     * @throws          Exception
     *
     * @creationDate    11/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the information connected with the activities of the delivery
     */
    private static function GetInfoActivities_Delivery($campaignId,$deliveryId,$activities) {
        /* Variables    */
        global $DB;
        $params             = null;
        $rdo                = null;
        $sql                = null;
        $activitiesDelivery = array();
        $info               = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaignId;
            $params['delivery'] = $deliveryId;

            /* SQL Instruction  */
            $sql = " SELECT		activityid,
                                name,
                                microkey
                     FROM		{microlearning_activities}
                     WHERE		microid 		= :campaign
                        AND 	micromodeid 	= :delivery
                        AND		activityid IN ($activities) ";


            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach($rdo as $instance) {
                    /* Info Activity    */
                    $info = new stdClass();
                    $info->name     = $instance->name;
                    $info->token    = $instance->microkey;

                    /* Add activity */
                    $activitiesDelivery[$instance->activityid] = $info;
                }//for_Rdo
            }//if_rdo
            return $activitiesDelivery;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfoActivities_Delivery

    /**
     * @param           $infoDelivery
     * @param           $activitiesDelivery
     * @param           $time
     * @return          array
     * @throws          Exception
     *
     * @creationDate    05/12/2014
     * @author          eFaktor     (fbv)
     *
     * @updateDate      11/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all users to send the campaign
     */
    private static function GetUsersDelivery($infoDelivery,$activitiesDelivery,$time) {
        /* Variables    */
        global $DB,$CFG;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $daysAfter      = null;
        $usersDelivery  = array();
        $infoUser       = null;
        $activity       = null;
        $link           = null;
        $strLink        = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['sent']     = 0;
            $params['time']     = $time;
            $params['campaign'] = $infoDelivery->microid;
            $params['delivery'] = $infoDelivery->id;


            /* SQL Instruction */
            if ($infoDelivery->criteria_tocomplete) {
                /* Days After   */
                $daysAfter = $infoDelivery->aftercompletion * (24*3600);
                /* Search Criteria  */
                $params['course']   = $infoDelivery->courseid;
                $params['criteria'] = $infoDelivery->criteria_tocomplete;

                /* AFTER COMPLETE ACTIVITY  */
                $sql = " SELECT		mi_d.id,
                                    mi_d.userid,
                                    uep.token,
                                    mi_d.timetosend,
                                    mi_d.message
                         FROM		{microlearning_deliveries}		mi_d
                            JOIN	{user_express}					uep		ON	uep.userid 					 = mi_d.userid
                            JOIN	{course_completion_crit_compl}	ccc		ON 	ccc.userid 					 = uep.userid
                                                                            AND ccc.course 					 = :course
                                                                            AND ccc.criteriaid 				 = :criteria
                                                                            AND (ccc.timecompleted + $daysAfter) <= :time
                         WHERE		mi_d.microid        = :campaign
                            AND		mi_d.micromodeid 	= :delivery
                            AND		mi_d.sent			= :sent ";
            }else if ($infoDelivery->criteria_notcomplete) {
                /* Search Criteria  */
                $params['course']   = $infoDelivery->courseid;
                $params['criteria'] = $infoDelivery->criteria_notcomplete;

                /* After Activity Not Done  */
                $sql = " SELECT		mi_d.id,
                                    mi_d.userid,
                                    uep.token,
                                    mi_d.timetosend,
                                    mi_d.message
                         FROM			{microlearning_deliveries}		mi_d
                            JOIN		{user_express}					uep		ON	uep.userid 		= mi_d.userid
                            LEFT JOIN	{course_completion_crit_compl}	ccc		ON 	ccc.userid 		= uep.userid
                                                                                AND ccc.course 		= :course
                                                                                AND ccc.criteriaid 	= :criteria
                         WHERE		mi_d.microid        = :campaign
                            AND		mi_d.micromodeid 	= :delivery
                            AND		mi_d.sent			= :sent
                            AND		mi_d.timetosend		<= :time
                            AND		ccc.id IS NULL ";
            }else {
                $sql = " SELECT			mi_d.id,
                                        mi_d.userid,
                                        uep.token,
                                        mi_d.timetosend,
                                        mi_d.message
                         FROM			{microlearning_deliveries}		mi_d
                            JOIN		{user_express}					uep		ON	uep.userid 		= mi_d.userid
                         WHERE		mi_d.microid        = :campaign
                            AND		mi_d.micromodeid 	= :delivery
                            AND		mi_d.sent			= :sent
                            AND		mi_d.timetosend		<= :time ";
            }//if_Else

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach($rdo as $instance) {
                    /* Info User    */
                    $infoUser = new stdClass();
                    $infoUser->user         = $instance->userid;
                    $infoUser->express      = $CFG->wwwroot . '/local/express_login/loginExpress.php/' . $instance->token . '/' . $infoDelivery->microkey;
                    $infoUser->toSend       = true;
                    $infoUser->subject      = $infoDelivery->subject;
                    /* Body */
                    if ($instance->message) {
                        $infoUser->body =  $instance->message . '</br></br>';
                        $infoUser->body .= $infoDelivery->body;
                    }else {
                        $infoUser->body = $infoDelivery->body;
                    }
                    /* Add Link Activities  */
                    foreach ($activitiesDelivery as $activity) {
                        /* Build the url    */
                        $link = $infoUser->express . '/' . $activity->token;
                        $strLink  = '<a href="' . $link. '">' . $activity->name . '</a>';
                        $strLink .= '</br>';

                        $infoUser->body .= '</br></br>' . $strLink;
                    }//for_each_act

                    /* Add User */
                    $usersDelivery[$instance->id] = $infoUser;
                }//for_eachUser
            }//if_rdo

            return $usersDelivery;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getUsersDelivery

    /**
     * @param           $deliveriesActivities
     * @return          array
     * @throws          Exception
     *
     * @creationDate    25/11/2014
     * @author          eFaktor     (fbv)
     *
     * @updateDate      11/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send deliveries to the users
     */
    private static function SendDeliveries($deliveriesActivities) {
        /* Variables    */
        global $SITE;
        $deliveriesSent = array();
        $campaignSent   = array();
        $usersDelivery  = null;
        $delivery       = null;

        try {
            /* Send Deliveries  */
            foreach ($deliveriesActivities as $mi_am => $usersDelivery) {
                foreach ($usersDelivery as $mi_d => $delivery) {
                    /* Get Info User */
                    $user = get_complete_user_data('id',$delivery->user);
                    /* Send Mail    */
                    $message = $delivery->body;
                    $messagetext = null;
                    $messagehtml = null;
                    if (strpos($message, '<') === false) {
                        // Plain text only.
                        $messagetext = $message;
                        $messagehtml = text_to_html($messagetext, null, false, true);
                    } else {
                        // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                        $messagehtml = format_text($message, FORMAT_MOODLE);
                        $messagetext = html_to_text($messagehtml);
                    }

                    if (email_to_user($user, $SITE->shortname, $delivery->subject, $messagetext,$messagehtml)) {
                        /* Deliveries Sent  */
                        $deliveriesSent[$mi_d] = $mi_d;
                    }//send_mail
                }//for_userDeliveries

                /* Activity Sent    */
                $campaignSent[$mi_am] = $mi_am;
            }//for_each_delivery

            return array($campaignSent,$deliveriesSent);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SendDeliveries

    /**
     * @param           $campaignSent
     * @param           $deliveriesSent
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    24/11/2014
     * @author          eFaktor     (fbv)
     *
     * @updateDate      11/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update status to sent
     */
    private static function UpdateStatusActivityDeliveries($campaignSent,$deliveriesSent) {
        /* Variables    */
        global $DB;
        $trans  = null;
        $caKeys = null;
        $mdKeys = null;
        $sql    = null;
        $params = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Params   */
            $params = array();
            $params['time'] = time();

            /* Update Status Activity Mode  */
            /* Keys */
            $caKeys = implode(',',$campaignSent);
            /* SQL Instruction  */
            $sql = " UPDATE {microlearning_activity_mode}
                        SET timesent = :time
                     WHERE  id IN ($caKeys) ";
            /* Execute  */
            $DB->execute($sql,$params);

            /* Update Status Deliveries */
            $params['mod'] = time();
            /* Keys */
            $mdKeys = implode(',',$deliveriesSent);
            /* SQL Instruction  */
            $sql = " UPDATE {microlearning_deliveries}
                        SET sent          = 1,
                            message       = null,
                            timesent      = :time,
                            timemodified  = :mod
                     WHERE id IN ($mdKeys) ";
            /* Execute  */
            $DB->execute($sql,$params);

            /* Commit   */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//UpdateStatusActivityDeliveries
}//Activity_ModeCron
