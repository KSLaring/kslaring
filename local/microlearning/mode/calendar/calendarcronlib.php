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
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    25/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Calendar Mode Cron
     *
     * @updateDate      12/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Optimize code - queries
     */
    public static function cron() {
        /* Variables    */
        $calendarDeliveries  = null;
        $calendarSent       = null;
        $deliveriesSent     = null;


        try {
            mtrace('Start Calendar Mode Cron Campaigns: ' . time() );

            /* Get Deliveries Calendar Campaign */
            $calendarDeliveries = self::GetDeliveriesCalendar();
            if ($calendarDeliveries) {
                /* Send Deliveries  */
                list($calendarSent,$deliveriesSent) = self::SendDeliveries($calendarDeliveries);

                /* Update Status    */
                self::UpdateStatusCalendarDeliveries($calendarSent,$deliveriesSent);
            }//if_calendarDeliveries

            mtrace(' Finish Calendar Mode Cron Campaigns:' . time() );
            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//cron

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    11/09/2015
     * @author          eFaktor (fbv)
     *
     * Description
     * Get all the deliveries by calendar, that have users to notify
     */
    private static function GetDeliveriesCalendar() {
        /* Variables    */
        global $DB;
        $time               = null;
        $params             = null;
        $sql                = null;
        $rdo                = null;
        $deliveriesCalendar = array();
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
            $sql = " SELECT		DISTINCT	mi_cm.id,
                                            mi_cm.microid,
                                            mi_cm.microkey,
                                            cc.id as 'criteria_after',
                                            mi_cm.subject,
                                            mi_cm.body,
                                            GROUP_CONCAT(DISTINCT mi_a.activityid ORDER BY mi_a.activityid SEPARATOR ',') 	as 	'activities',
                                            mi.courseid
                     FROM			{microlearning_calendar_mode}	mi_cm
                        JOIN		{microlearning}					mi		ON		mi.id 				= mi_cm.microid
                                                                            AND		mi.activate			= :activate
                        JOIN		{microlearning_activities}		mi_a	ON		mi_a.microid		= mi.id
                                                                            AND		mi_a.micromodeid	= mi_cm.id
                        JOIN		{microlearning_deliveries}		mi_d	ON		mi_d.microid		= mi_a.microid
                                                                            AND		mi_d.micromodeid	= mi_a.micromodeid
                        JOIN		{user_express}					uep		ON		uep.userid			= mi_d.userid
                        LEFT JOIN	{course_completion_criteria}	cc		ON		cc.moduleinstance	= mi_cm.activityafter
                                                                            AND		cc.course			= mi.courseid
                     WHERE		mi_d.sent 		 = :sent
                        AND		mi_d.timetosend <= :time
                     GROUP BY mi_cm.id ";


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
            }//if_Rdo

            return $deliveriesCalendar;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetDeliveriesCalendar

    /**
     * @param           $calendarId
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
    private static function GetInfoActivities_Delivery($calendarId,$deliveryId,$activities) {
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
            $params['calendar'] = $calendarId;
            $params['delivery'] = $deliveryId;

            /* SQL Instruction  */
            $sql = " SELECT		activityid,
                                name,
                                microkey
                     FROM		{microlearning_activities}
                     WHERE		microid 		= :calendar
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
     * @creationDate    25/11/2014
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
        $usersDelivery  = array();
        $infoUser       = null;
        $activity       = null;
        $link           = null;
        $strLink        = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['sent']     = 0;
            $params['time']   = $time;
            $params['calendar'] = $infoDelivery->microid;
            $params['delivery'] = $infoDelivery->id;

            /* SQL Instruction  */
            if ($infoDelivery->criteria_after) {
                /* Search Criteria  */
                $params['course']   = $infoDelivery->courseid;
                $params['criteria'] = $infoDelivery->criteria_after;

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
                     WHERE		mi_d.microid        = :calendar
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
                         WHERE		mi_d.microid        = :calendar
                            AND		mi_d.micromodeid 	= :delivery
                            AND		mi_d.sent			= :sent
                            AND		mi_d.timetosend		<= :time ";
            }//if_else

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
    }//GetUsersDelivery

    /**
     * @param           $deliveriesCalendar
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
    private static function SendDeliveries($deliveriesCalendar) {
        /* Variables    */
        global $SITE;
        $deliveriesSent = array();
        $calendarSent   = array();
        $usersDelivery  = null;
        $delivery       = null;

        try {
            /* Send Deliveries  */
            foreach ($deliveriesCalendar as $mi_cm => $usersDelivery) {
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

                /* Calendar Sent    */
                $calendarSent[$mi_cm] = $mi_cm;
            }//for_each_delivery

            return array($calendarSent,$deliveriesSent);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SendDeliveries

    /**
     * @param           $calendarSent
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
    private static function UpdateStatusCalendarDeliveries($calendarSent,$deliveriesSent) {
        /* Variables    */
        global $DB;
        $trans  = null;
        $cmKeys = null;
        $mdKeys = null;
        $sql    = null;
        $params = null;

        /* Start transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Params   */
            $params = array();
            $params['time'] = time();

            /* Update Status Calendar Mode  */
            /* Keys */
            $cmKeys = implode(',',$calendarSent);
            /* SQL Instruction  */
            $sql = " UPDATE {microlearning_calendar_mode}
                        SET timesent = :time
                     WHERE  id IN ($cmKeys) ";
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
    }//UpdateStatusCalendarDeliveries
}//Calendar_ModeCron