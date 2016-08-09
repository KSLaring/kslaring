<?php
/**
 * Micro Learning - Library
 *
 * @package         local
 * @subpackage      microlearning
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      12/09/2014
 * @author          eFaktor     (fbv)
 *
 */

define('CALENDAR_MODE',1);
define('ACTIVITY_MODE',2);
define('NEW_CAMPAIGN',1);
define('EDIT_CAMPAIGN',2);
define('DUPLICATE_CAMPAIGN',3);
define('DELETE_CAMPAIGN',4);

class Micro_Learning {

    /**
     * @param           $courseId
     * @param           $userId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    09/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if it has permissions
     */
    public static function HasPermissions($courseId,$userId) {
        /* Variables    */
        global $DB;
        $contextCat     = null;
        $contextCourse  = null;
        $contextSystem  = null;
        $params         = null;
        $rdo            = null;
        $sql            = null;

        try {
            if (is_siteadmin($userId)) {
                return true;
            }
            /* Search Criteria  */
            $params = array();
            $params['user']         = $userId;
            $contextCat             = CONTEXT_COURSECAT;
            $contextCourse          = CONTEXT_COURSE;
            $contextSystem          = CONTEXT_SYSTEM;

            /* SQL Instruction  */
            $sql = " SELECT		ra.id
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 		r.id			= ra.roleid
                                                            AND		r.archetype		IN ('manager','coursecreator','editingteacher','teacher')
                        JOIN	{context}		    ct		ON		ct.id			= ra.contextid
                                                            AND		ct.contextlevel	IN ($contextCat,$contextCourse,$contextSystem)
                     WHERE		ra.userid 		= :user ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//HasPermissions
    
    /**
     * @static
     * @param           $course_id
     * @param           $sort
     * @param           $limit_from
     * @param           $limit_num
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    12/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get a list of all campaigns connected with a specific course
     */
    public static function Get_MicrolearningCampaigns($course_id,$sort,$limit_from,$limit_num) {
        /* Variables    */
        global $DB;
        $campaign_lst   = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $info           = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course_id'] = $course_id;

            /* SQL Instruction  */
            $sql = " SELECT     id,
                                name,
                                type,
                                activate,
                                duplicated_from
                     FROM       {microlearning}
                     WHERE      courseid = :course_id
                     ORDER BY   type, name " . $sort;

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params,$limit_from,$limit_num);
            if ($rdo) {
                foreach ($rdo as $campaign) {
                    $info = new stdClass();
                    $info->id           = $campaign->id;
                    $info->name         = $campaign->name;
                    $info->type         = $campaign->type;
                    $info->activate     = $campaign->activate;
                    $info->duplicate    = $campaign->duplicated_from;

                    $campaign_lst[$campaign->id] = $info;
                }//for_rdo
            }//if_rdo

            return $campaign_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MicrolearningCampaigns

    /**
     * @static
     * @param           $course_id
     * @return          int
     * @throws          Exception
     *
     * @creationDate    13/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get how many campaigns are connected to the course
     */
    public static function Get_TotalCampaings_Course($course_id) {
        /* Variables    */
        global $DB;

        try {
            return $DB->count_records('microlearning',array('courseid' => $course_id));
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//Get_TotalCampaings_Course

    /**
     * @static
     * @param           $campaign_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    13/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the name of campaign
     */
    public static function Get_NameCampaign($campaign_id) {
        /* Variables    */
        global $DB;
        $rdo = null;

        try {
            /* Execute  */
            $rdo = $DB->get_record('microlearning',array('id' => $campaign_id),'name');
            if ($rdo) {
                return $rdo->name;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_NameCampaign


    /**
     * @static
     * @param           $micro_id
     * @param           $course_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    12/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all information connected with the campaign
     */
    public static function Get_DetailCampaign($micro_id,$course_id) {
        /* Variables    */
        global $DB;
        $campaign_info  = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            /* Subject, Message, Activities Included, Time Created   */

            /* Search Criteria  */
            $params = array();
            $params['micro_id']     = $micro_id;
            $params['course_id']    = $course_id;

            /* SQL Instruction  */
            $sql = " SELECT		ml.name,
                                ml.type,
                                ml.subject,
                                ml.body,
                                ml.timecreated
                     FROM		{microlearning}	            ml
                     WHERE		ml.courseid = :course_id
                        AND		ml.id		= :micro_id ";


            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                    $campaign_info = new stdClass();
                    $campaign_info->name = null;
            }//if_rdo

            return $campaign_info;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_DetailCampaign

    /**
     * @static
     * @param           $data
     * @return          bool|int|null
     * @throws          Exception
     *
     * @creationDate    13/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create a new Microlearning Campaign
     */
    public static function Create_MicrolearningCampaign($data) {
        /* Variables    */
        global $DB;
        $campaign       = null;
        $campaign_id    = null;

        try {
            /* New Campaign Instance    */
            $campaign = new stdClass();
            $campaign->courseid         = $data->id;
            $campaign->name             = $data->campaign;
            $campaign->type             = $data->type;
            if (isset($data->activate) && ($data->activate)) {
                $campaign->activate     = 1;
            }else {
                $campaign->activate     = 0;
            }

            $campaign->timecreated      = time();

            /* Execute  */
            $campaign_id = $DB->insert_record('microlearning',$campaign);

            return $campaign_id;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Create_MicrolearningCampaign

    /**
     * @static
     * @param           $campaign_id
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    14/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the users connected with campaign
     */
    public static function GetUsers_Campaign($campaign_id) {
        /* Variables    */
        global $DB;
        $users_campaign = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            /* Search Criteria  */
            $params             = array();
            $params['microid']  = $campaign_id;

            /* SQL Instruction  */
            $sql = " SELECT     u.id,
                                CONCAT(u.firstname,' ',u.lastname) as 'name'
                     FROM       {user}                 u
                        JOIN    {microlearning_users}  md    ON    md.userid   = u.id
                                                             AND   md.microid  = :microid
                     ORDER BY   u.firstname, u.lastname ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $user) {
                    $users_campaign[$user->id] = $user->name;
                }
            }//if_rdo

            return $users_campaign;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_Campaign

    /**
     * @static
     * @param           $campaign_id
     * @return          int
     * @throws          Exception
     *
     * @creationDate    20/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get how many users are connected to campaign
     */
    public static function GetTotalUsers_Campaign($campaign_id) {
        /* Variables    */
        global $DB;

        try {
           return $DB->count_records('microlearning_users',array('microid' => $campaign_id));
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetTotalUsers_Campaign

    /**
     * @static
     * @param           $campaign_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/10/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the status of campaign
     */
    public static function GetStatus_Campaign($campaign_id) {
        /* Variables    */
        global $DB;
        $rdo    = null;

        try {
            $rdo = $DB->get_record('microlearning',array('id' => $campaign_id),'activate');
            if ($rdo) {
                return $rdo->activate;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetStatus_Campaign

    /**
     * @static
     * @param           $course
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    14/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the activities/resources connected with the course
     */
    public static function Get_ActivitiesList($course) {
        /* Variables    */
        global $DB;
        $lst_activities         = null;
        $mod_info               = null;
        $activities             = array();
        $criterias_toComplete   = null;
        $toComplete             = null;

        try {
            $lst_activities[0]  = get_string('sel_activity','local_microlearning');

            /* Get Completion Info  */
            $mod_info = get_fast_modinfo($course);
            foreach ($mod_info->get_cms() as $cm) {
                $activities[$cm->id] = $cm;
            }

            /* Get Criterias to Complete  */
            $criterias_toComplete = self::GetCriterias_ToComplete($course);
            if ($criterias_toComplete) {
                $toComplete = explode(',',$criterias_toComplete);

                /* Get Activities       */
                foreach ($activities as $activity) {
                    if (in_array($activity->id,$toComplete)) {
                        $lst_activities[$activity->id] = $activity->name;
                    }//if_in_array

                }//activities

            }//if_criterias_toComplete

            return $lst_activities;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_ActivitiesList

    /**
     * @param           $course_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    04/12/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the type of each activity
     */
    public static function Get_ActivitiesType($course_id) {
        /* Variables    */
        $activities_type        = null;
        $mod_info               = null;
        $activities             = array();

        try {
            $lst_activities[0]  = get_string('sel_activity','local_microlearning');

            /* Get Completion Info  */
            $mod_info = get_fast_modinfo($course_id);
            foreach ($mod_info->get_cms() as $cm) {
                if ($cm->completion != COMPLETION_TRACKING_NONE) {
                    $activities[$cm->id] = $cm;
                }
            }

            /* Get Activities       */
            foreach ($activities as $activity) {
                $activities_type[$activity->id] = $activity->modname;
            }//activities

            return $activities_type;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_ActivitiesType

    /**
     * @param           $campaign
     * @param           $course
     * @return          bool
     *
     * @creationDate    06/12/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Activate/Deactivate the campaign
     */
    public static function ChangeStatus_Campaign($campaign,$course) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['campaign'] = $campaign;
            $params['course']   = $course;

            /* SQL Instruction  */
            $sql = " UPDATE	{microlearning}
                        SET	activate = !activate
                     WHERE	id 			= :campaign
                        AND	courseid 	= :course ";

            /* Execute  */
            $DB->execute($sql,$params);

            return true;
        }catch (Exception $ex) {
            return false;
        }//try_catch
    }//ChangeStatus_Campaign

    /**
     * @param           $campaignId
     * @param           $courseId
     * @param           $mode
     * @param           $add
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    16/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add new users automatically
     */
    public static function Activate_AddNewUsers($campaignId,$courseId,$mode,$add) {
        /* Variables */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseId;
            $params['campaign'] = $campaignId;
            $params['type']     = $mode;

            /* SQL Instruction */
            $sql = " UPDATE {microlearning}
                        SET addusers = $add
                     WHERE  id        = :campaign
                        AND courseid  = :course
                        AND type      = :type ";

            /* Execute */
            $DB->execute($sql,$params);

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Activate_AddNewUsers



    /* ******************* */
    /* TABLES              */
    /* ******************* */

    /**
     * @param           $course_id
     * @param           $sort
     * @param           $limit_from
     * @param           $limit_num
     * @return          string
     * @throws          Exception
     *
     * @creationDate    12/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the table to show all the campaigns connected to the course
     */
    public static function Get_MicrolearningCampaigns_Table($course_id,$sort,$limit_from,$limit_num) {
        /* Variables    */
        $out = '';
        $campaign_lst   = null;
        $strAlert       = null;
        $classAlert     = null;

        try {
            $out .= '<h3>' . get_string('title_campaign','local_microlearning'). '</h3>';
            $out .= "</br>";

            /* Header   */
            $out .= html_writer::start_div('micro_campaign_table');
                $out .= html_writer::start_div('micro_campaign_table_row title_campaigns');
                    /* Col One  */
                    $out .= html_writer::start_div('col_one');
                        $out .= '<h6>' . get_string('rpt_campaign','local_microlearning') . '</h6>';
                    $out .= html_writer::end_div();//col_one
                    /* Col Two  */
                    $out .= html_writer::start_div('col_two');
                        $out .= '<h6>' . get_string('rpt_mode','local_microlearning') . '</h6>';
                    $out .= html_writer::end_div();//col_ttwo
                    /* Col Three  */
                    $out .= html_writer::start_div('col_three');
                    $out .= html_writer::end_div();//col_three
                $out .= html_writer::end_div();//micro_campaign_table_row
            $out .= html_writer::end_div();//micro_campaign_table

            $campaign_lst = self::Get_MicrolearningCampaigns($course_id,$sort,$limit_from,$limit_num);
            if ($campaign_lst) {
                $color = 'r0';
                $out .= html_writer::start_div('micro_campaign_table');
                foreach ($campaign_lst as $campaign) {
                    $mode           = null;
                    $delete_lnk     = null;
                    $duplicate_lnk  = null;
                    $edit_lnk       = null;
                    $classAlert     = '';
                    $strAlert       = null;

                    switch ($campaign->type) {
                        case CALENDAR_MODE:
                            $mode           = get_string('rpt_type_calendar','local_microlearning');
                            $edit_lnk       = new moodle_url('/local/microlearning/mode/calendar/calendar_deliveries.php',array('id'=>$course_id,'mode' => CALENDAR_MODE,'cp' => $campaign->id));
                            $delete_lnk     = new moodle_url('/local/microlearning/mode/calendar/delete.php',array('id' => $course_id,'cp' => $campaign->id,'cp_name' => $campaign->name));
                            $duplicate_lnk  = new moodle_url('/local/microlearning/mode/calendar/duplicate.php',array('id' => $course_id,'cp' => $campaign->id));

                            if ((!$campaign->activate) && ($campaign->duplicate)) {
                                $classAlert = "alert_campaign";
                                $strAlert   = get_string('alert_campaign','local_microlearning');
                            }

                            break;
                        case ACTIVITY_MODE:
                            $mode           = get_string('rpt_type_activity','local_microlearning');
                            $edit_lnk       = new moodle_url('/local/microlearning/mode/activity/activity_deliveries.php',array('id'=>$course_id,'mode' => ACTIVITY_MODE,'cp' => $campaign->id));
                            $delete_lnk     = new moodle_url('/local/microlearning/mode/activity/delete.php',array('id' => $course_id,'cp' => $campaign->id,'cp_name' => $campaign->name));
                            $duplicate_lnk  = new moodle_url('/local/microlearning/mode/activity/duplicate.php',array('id' => $course_id,'cp' => $campaign->id));

                            break;
                    }//switch_campaign_type

                    $out .= html_writer::start_div('micro_campaign_table_row ' . $color . ' ' . $classAlert);
                        /* Col One  */
                        $out .= html_writer::start_div('col_one');
                            $out .= '<a href="' . $edit_lnk . '">' . $campaign->name . '</a>';
                        $out .= html_writer::end_div();//col_one
                        /* Col Two  */
                        $out .= html_writer::start_div('col_two');
                            $out .= $mode ;
                        $out .= html_writer::end_div();//col_two
                        /* Col Three  */
                        $out .= html_writer::start_div('col_three');
                            $out .= '<a href="' . $edit_lnk . '" class="lnk_col">' . get_string('edit') . '</a>';
                            $out .= '<a href="' . $duplicate_lnk . '" class="lnk_col">' . get_string('duplicate') . '</a>';
                            $out .= '<a href="' . $delete_lnk . '" class="lnk_col">' . get_string('delete') . '</a>';
                            $out .= '<a href="#" class="lnk_col lnk_disabled">' . get_string('report') . '</a>';

                            if ($strAlert) {
                                $out .= html_writer::start_div('alert_text');
                                    $out .= '<h6>' . $strAlert . '</h6>';
                                $out .= html_writer::end_div();//alert_text
                            }//if_strAlert
                        $out .= html_writer::end_div();//col_three
                    $out .= html_writer::end_div();//micro_campaign_table_row

                    if ($color == 'r0') {
                        $color = 'r2';
                    }else {
                        $color = 'r0';
                    }//if_else_color
                }//for_each_campaign
                $out .= html_writer::end_div();//micro_campaign_table
            }else {
                $out .= '<h5>' .get_string('no_campaigns','local_microlearning') . '</h5>';
            }

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MicrolearningCampaigns_Table

    /**
     * @static
     * @param           $campaign_id
     * @param           $campaign_name
     * @param           $deliveries_lst
     * @param           $mode_learning
     * @param           $course_id
     * @param           $started
     * @param           $strAlert
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    16/07/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the table with all the eMails connected with the campaign
     */
    public static function Get_CampaignDeliveries_Table($campaign_id,$campaign_name,$deliveries_lst,$mode_learning,$course_id,$started,$strAlert=null) {
        /* Variables    */
        global $OUTPUT;
        $url_edit_users     = null;
        $url_edit_delivery  = null;
        $out                = '';
        $disabled           = '';
        $classUrl           = '';

        try {
            /* Build the links to edit  */
            $params = array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id,'st' => $started);
            switch ($mode_learning) {
                case CALENDAR_MODE:
                    $url_edit_users     = new moodle_url('/local/microlearning/users/users.php',$params);
                    $url_edit_delivery  = new moodle_url('/local/microlearning/mode/calendar/calendar.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
                    if ($strAlert) {
                        $url_activate = '#';
                        $disabled = true;
                    }else {
                        $url_activate       = new moodle_url('/local/microlearning/mode/calendar/calendar_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id,'act' => 1));
                    }


                    break;
                case ACTIVITY_MODE:
                    $url_edit_users     = new moodle_url('/local/microlearning/users/users.php',$params);
                    $url_edit_delivery  = new moodle_url('/local/microlearning/mode/activity/activity.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id));
                    $url_activate       = new moodle_url('/local/microlearning/mode/activity/activity_deliveries.php',array('id'=>$course_id,'mode' => $mode_learning,'cp' => $campaign_id,'act' => 1));;

                    break;
                default:
                    $url_edit_delivery  = '#';
                    $url_edit_users     = '#';
                    $url_activate       = '#';
            }//mode_learning

            /* Build the table  */
            $str_header = get_string('rpt_campaign','local_microlearning') . ' ' . $campaign_name;

            $out .= '<h3>' . $str_header. '</h3>';
            $out .= "</br>";

            /* Info Campaign    */
            $out .= html_writer::start_div('micro_deliveries_info');
                /* Total Users  */
                $out .= html_writer::start_div('micro_info_table');
                    /* Title    */
                    $out .= html_writer::start_div('info_one');
                        $out .= get_string('total_users','local_microlearning');
                        /* Edit Link    */
                        $out .= html_writer::start_div('lnk_edit');
                            $out .= html_writer::link($url_edit_users,
                                                      html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
                                                                                          'alt'=>get_string('btn_edit_users','local_microlearning'),
                                                                                          'class'=>'iconsmall')),
                                                      array('title'=>get_string('btn_edit_users','local_microlearning')));
                        $out .= html_writer::end_div();//lnk_edit
                    $out .= html_writer::end_div();//info_one

                    /* Total    */
                    $out .= html_writer::start_div('info_two');
                        $out .= self::GetTotalUsers_Campaign($campaign_id);
                    $out .= html_writer::end_div();//info_two
                $out .= html_writer::end_div();//micro_info_table
            $out .= html_writer::end_div();//micro_deliveries_info

            /* Total Deliveries */
            $out .= html_writer::start_div('micro_deliveries_info');
                $out .= html_writer::start_div('micro_info_table');
                    $out .= html_writer::start_div('info_one');
                        $out .= get_string('total_deliveries','local_microlearning');
                    $out .= html_writer::end_div();//info_one
                    $out .= html_writer::start_div('info_two');
                        $out .= count($deliveries_lst);
                    $out .= html_writer::end_div();//info_two
                $out .= html_writer::end_div();//micro_info_table
            $out .= html_writer::end_div();//micro_deliveries_info

            /* Status   */
            $status = self::GetStatus_Campaign($campaign_id);
            $out .= html_writer::start_div('micro_deliveries_info');
                $out .= html_writer::start_div('micro_info_table');
                    $out .= html_writer::start_div('info_one');
                        $out .= get_string('rpt_status','local_microlearning');

                        /* Activate/Deactivate Link    */
                        $out .= html_writer::start_div('lnk_edit');
                            if ($status) {
                                $alt = get_string('action_deactivate','local_microlearning');
                                $src = $OUTPUT->pix_url('t/hide');
                            }else {
                                $alt = get_string('action_activate','local_microlearning');
                                $src = $OUTPUT->pix_url('t/show');
                            }//if_status

                            /* Check if the link is enable or not   */
                            if ($disabled) {
                                $classUrl = 'lnk_disabled';
                            }

                            $out .= html_writer::link($url_activate,
                                                      html_writer::empty_tag('img', array('src'=>$src,'alt'=>$alt,'class'=>'iconsmall')),
                                                      array('title'=>$alt, 'class' => $classUrl));
                        $out .= html_writer::end_div();//lnk_edit

                    $out .= html_writer::end_div();//info_one
                    $out .= html_writer::start_div('info_two');
                        if ($status) {
                            $out .= get_string('rpt_activated','local_microlearning');
                        }else {
                            $out .= get_string('rpt_deactivated','local_microlearning');
                        }//if_else_status
                    $out .= html_writer::end_div();//info_two
                $out .= html_writer::end_div();//micro_info_table
            $out .= html_writer::end_div();//micro_deliveries_info

            if ($strAlert) {
                $out .= "</br>";
                $out .= html_writer::start_div('alert_campaign alert_text');
                $out .= '<h5>' . $strAlert . '</h5>';
                $out .= html_writer::end_div();//alert_text
            }//if_strAlert

            $out .= "</br>";
            $out .= "</br>";

            /* Header   */
            $out .= html_writer::start_div('micro_deliveries_table');
                $out .= html_writer::start_div('micro_deliveries_table_row title_deliveries ');
                    /* Col Subject  */
                    $out .= html_writer::start_div('col_desc');
                        $out .= '<h6>' . get_string('email_sub','local_microlearning') . '</h6>';
                    $out .= html_writer::end_div();//col_desc_subject
                    /* Col Message  */
                    $out .= html_writer::start_div('col_desc');
                        $out .=  '<h6>' . get_string('email_body','local_microlearning') . '</h6>';
                    $out .= html_writer::end_div();//col_desc_message
                    /* Col Activities  */
                    $out .= html_writer::start_div('col_desc');
                        $out .=  '<h6>' . get_string('rpt_act','local_microlearning') . '</h6>';
                    $out .= html_writer::end_div();//col_desc_activities
                    /* Col Time  */
                    $out .= html_writer::start_div('col_time');
                        $out .=  '<h6>' . get_string('rpt_sent','local_microlearning') . '</h6>';
                    $out .= html_writer::end_div();//col_time_sent
                $out .= html_writer::end_div();//micro_deliveries_table_row
            $out .= html_writer::end_div();//micro_deliveries_table

            /* Get all the eMails to deliver    */
            if ($deliveries_lst) {
                $color = 'r0';
                $out .= html_writer::start_div('micro_deliveries_table');
                foreach ($deliveries_lst as $delivery) {
                    if (($delivery->timesent) && ($mode_learning == CALENDAR_MODE)) {
                        $disabled = true;
                    }else {
                        $disabled = false;
                    }//if_delivery_sent

                    $out .= html_writer::start_div('micro_deliveries_table_row ' . $color);
                        /* Col Subject  */
                        $out .= html_writer::start_div('col_content_desc');
                            $out .= $delivery->subject;

                            /* Edit Link    */
                            if (($url_edit_delivery != '#') && (!$disabled)) {
                                $url_edit_delivery->param('cm',$delivery->id);
                                $out .= html_writer::start_div('lnk_edit');
                                    $out .= html_writer::link($url_edit_delivery,
                                                              html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
                                                                                                  'alt'=>get_string('btn_edit_delivery','local_microlearning'),
                                                                                                  'class'=>'iconsmall')),
                                                              array('title'=>get_string('btn_edit_delivery','local_microlearning')));
                                $out .= html_writer::end_div();//lnk_edit
                            }//if_is_editable
                        $out .= html_writer::end_div();//col_desc_subject

                        /* Col Message  */
                        $out .= html_writer::start_div('col_content_desc');
                            $out .= $delivery->body;
                        $out .= html_writer::end_div();//col_desc_message

                        /* Col Activities  */
                        $out .= html_writer::start_div('col_content_desc');
                            $out .= str_replace(',','</br>',$delivery->activities);
                        $out .= html_writer::end_div();//col_desc_activities

                        /* Col Time  */
                        $out .= html_writer::start_div('col_content_time');
                            if ($delivery->timesent) {
                                $out .= userdate($delivery->timesent,'%d.%m.%Y',99,false);
                            }//if_delivery_timesent

                        $out .= html_writer::end_div();//col_time_sent
                    $out .= html_writer::end_div();//micro_deliveries_table

                    if ($color == 'r0') {
                        $color = 'r2';
                    }else {
                        $color = 'r0';
                    }//if_else_color
                }//for_delivery
                $out .= html_writer::end_div();//micro_deliveries_table
            }//if_deliveries_lst

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CampaignDeliveries_Table

    /**
     * @param           $value
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    05/112/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate the hash connected to the campaign and deliveries
     */
    public static function GenerateHash_MicroLearning($value) {
        try {
            return self::GenerateHash($value);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GenerateHash_MicroLearning

    /************/
    /* PRIVATE */
    /************/

    /**
     * @param           $course_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    09/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the activities to complete the course.
     */
    private static function GetCriterias_ToComplete($course_id) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $sql    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course_id']   = $course_id;

            /* SQL Instruction  */
            $sql = " SELECT 	GROUP_CONCAT(DISTINCT moduleinstance ORDER BY moduleinstance SEPARATOR ',') as 'criterias'
                     FROM	  	{course_completion_criteria}
                     WHERE  	course = :course_id
                     AND        gradepass IS NULL ";


            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo->criterias) {
                return $rdo->criterias;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//local_completion_getCriteriasToComplete

    /**
     * @param           $value
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    05/12/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the hash connected with campaign and deliveries
     */
    private static function GenerateHash($value) {
        /* Variables    */
        $cost               = 10;
        $required_salt_len  = 22;
        $buffer             = '';
        $buffer_valid       = false;
        $hash_format        = null;
        $salt               = null;
        $ret                = null;
        $hash               = null;

        try {
            /* Generate hash    */
            $hash_format        = sprintf("$2y$%02d$", $cost);
            $raw_length         = (int) ($required_salt_len * 3 / 4 + 1);

            if (function_exists('mcrypt_create_iv')) {
                $buffer = mcrypt_create_iv($raw_length, MCRYPT_DEV_URANDOM);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }

            if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
                $buffer = openssl_random_pseudo_bytes($raw_length);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }

            if (!$buffer_valid && file_exists('/dev/urandom')) {
                $f = @fopen('/dev/urandom', 'r');
                if ($f) {
                    $read = strlen($buffer);
                    while ($read < $raw_length) {
                        $buffer .= fread($f, $raw_length - $read);
                        $read = strlen($buffer);
                    }
                    fclose($f);
                    if ($read >= $raw_length) {
                        $buffer_valid = true;
                    }
                }
            }

            if (!$buffer_valid || strlen($buffer) < $raw_length) {
                $bl = strlen($buffer);
                for ($i = 0; $i < $raw_length; $i++) {
                    if ($i < $bl) {
                        $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                    } else {
                        $buffer .= chr(mt_rand(0, 255));
                    }
                }
            }

            $salt = str_replace('+', '.', base64_encode($buffer));

            $salt = substr($salt, 0, $required_salt_len);

            $hash = $hash_format . $salt;

            $ret = crypt($value, $hash);

            if (!is_string($ret) || strlen($ret) <= 13) {
                return false;
            }

            return $ret;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GenerateHash
}//Micro_Learning