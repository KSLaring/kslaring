<?php
/**
 * Approval Request - Library
 *
 * @package         enrol/waitinglist
 * @subpackage      approval
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    24/12/2015
 * @author          efaktor     (fbv)
 *
 * Description
 */
define('APPROVED_ACTION',1);
define('REJECTED_ACTION',2);
define('ONWAIT_ACTION',3);
define('WAITINGLIST_FIELD_INVOICE','customint8');
define('WAITINGLIST_FIELD_APPROVAL','customint7');
define('ENROLPASSWORD','customtext1');

Class Approval {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $form
     *
     * @throws          Exception
     *
     * @creationDate    24/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add extra elements to the main form
     */
    public static function AddElements_ToForm(&$form) {
        /* Variables */
        global $USER,$COURSE;

        try {
            /* Approval Info    */
            $form->addElement('html','<label class="approval_info">' . get_string('approval_info','enrol_waitinglist') . '</label>');

            /* Arguments            */
            $form->addElement('textarea', 'arguments',get_string('arguments', 'enrol_waitinglist'),'cols=75 rows=5');
            $form->addRule('arguments',get_string('required'), 'required', null, 'server');

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddElements_ToForm

    /**
     * @param           $userId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    28/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get managers connected with user
     */
    public static function GetManagers($userId,$instance = null) {
        /* Variables    */
        $myManagers = null;
        $competence = null;

        try {
            /* First it gets the competence connected with the user */
            $competence = self::GetCompetence_User($userId,$instance);

            /* Get Managers */
            $myManagers = self::GetInfoManagers_NotificationApproved($competence);//self::GetManager_User($competence);

            return $myManagers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetManagers

    /**
     * @param           $userId
     * @param           $three
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    12/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all managers connected with a specific user and company
     */
    public static function ManagersConnected($userId,$three) {
        /* Variables */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $params         = null;
        $myManagers     = array();
        $infoManager    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user']     = $userId;
            $params['company']  = $three;

            /* SQL Instruction  */
            $sql = " SELECT	  DISTINCT rm.managerid,
                                       CONCAT(co_two.name,'/',co.name) as 'company'
                     FROM		{user_info_competence_data} 	uicd
                        JOIN	{report_gen_companydata}		co 		ON 	co.id = uicd.companyid
                        -- LEVEL TWO
                        JOIN	{report_gen_company_relation}  	cr_two	ON 	cr_two.companyid 		= co.id
                        JOIN	{report_gen_companydata}		co_two	ON 	co_two.id 				= cr_two.parentid
                                                                        AND co_two.hierarchylevel 	= 2
                        -- CHECK MANAGER LEVEL TWO/ LEVEL THREE
                        JOIN	{report_gen_company_manager}  	rm		ON 	(
                                                                             (rm.levelthree = uicd.companyid
                                                                              AND 
                                                                              rm.leveltwo = co_two.id)
                                                                             OR 
                                                                             (rm.leveltwo = co_two.id
                                                                              AND
                                                                              rm.levelthree IS NULL)
                                                                             )
                     WHERE		uicd.userid 	= :user
                        AND		uicd.companyid  = :company ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    if (array_key_exists($instance->managerid,$myManagers)) {
                        $infoManager = $myManagers[$instance->managerid];
                    }else {
                        $infoManager = new stdClass();
                        $infoManager->id        = $instance->managerid;
                        $infoManager->companies = array();
                    }//if_manager_exists

                    /* Add Company  */
                    $infoManager->companies[] = $instance->company;

                    /* Add Manager */
                    $myManagers[$instance->managerid] = $infoManager;
                }//of_rdo
            }//if_rdo

            return $myManagers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ManagersConnected

    /**
     * @param           $userId
     * @param           $courseId
     * @param           $waitingId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    30/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the request has been rejected
     */
    public static function IsRejected($userId,$courseId,$waitingId) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            /* Search Criteria */
            $params = array();
            $params['userid']           = $userId;
            $params['courseid']         = $courseId;
            $params['waitinglistid']    = $waitingId;
            $params['rejected']         = 1;

            /* Execute */
            $rdo = $DB->get_record('enrol_approval',$params);
            if ($rdo) {
                return $rdo->timemodified;
            }else {
                return false;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsRejected

    /**
     * @param           $data
     * @param           $userId
     * @param           $courseId
     * @param           $method
     * @param           $seats
     * @param           int $waitingId
     *
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    28/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create an entry for approval method
     * 
     * @updateDate      13/09/2016
     * @auhtor          eFaktor     (fbv)
     * 
     * Description
     * Add company
     */
    public static function Add_ApprovalEntry($data,$userId,$courseId,$method,$seats,$waitingId=0) {
        /* Variables */
        global $DB,$CFG;
        $rdo            = null;
        $infoApproval   = null;
        $infoApproveAct = null;
        $infoRejectAct  = null;
        $infoMail       = null;
        $user           = null;
        $course         = null;
        $lnkApprove     = null;
        $lnkReject      = null;
        $trans          = null;

        /* Start Transaction */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Approval Entry */
            $infoApproval = new stdClass();
            $infoApproval->userid           = $userId;
            $infoApproval->companyid        = $data->level_3;
            $infoApproval->courseid         = $courseId;
            $infoApproval->methodtype       = $method;
            $infoApproval->unenrol          = 0;
            $infoApproval->userenrolid      = 0;
            $infoApproval->waitinglistid    = $waitingId;
            $infoApproval->arguments        = $data->arguments;
            $infoApproval->seats            = $seats;
            $infoApproval->token            = self::GenerateToken($userId,$courseId);
            $infoApproval->approved         = 0;
            $infoApproval->rejected         = 0;
            $infoApproval->onwait           = 0;
            $infoApproval->timecreated      = time();

            /* Execute */
            $infoApproval->id = $DB->insert_record('enrol_approval',$infoApproval);

            /* Insert Approve Action */
            $infoApproveAct = new stdClass();
            $infoApproveAct->approvalid = $infoApproval->id;
            $infoApproveAct->token = self::GenerateToken_Action($courseId,'approve');
            $infoApproveAct->action = APPROVED_ACTION;
            $DB->insert_record('enrol_approval_action',$infoApproveAct);

            /* Insert Reject Action  */
            $infoRejectAct = new stdClass();
            $infoRejectAct->approvalid = $infoApproval->id;
            $infoRejectAct->token   = self::GenerateToken_Action($courseId,'reject');
            $infoRejectAct->action  = REJECTED_ACTION;
            $DB->insert_record('enrol_approval_action',$infoRejectAct);

            /* Info To Send */
            $infoMail = self::Info_NotificationApproved($userId,$courseId,$waitingId);
            $infoMail->arguments    = $infoApproval->arguments;
            $infoMail->approvalid   = $infoApproval->id;
            /* Approve Link */
            $lnkApprove = $CFG->wwwroot . '/enrol/waitinglist/approval/action.php/' . $infoApproval->token . '/' . $infoApproveAct->token;
            $infoMail->approve = '<a href="' . $lnkApprove . '">' . get_string('approve_lnk','enrol_waitinglist') . '</br>';
            /* Reject Link  */
            $lnkReject  = $CFG->wwwroot . '/enrol/waitinglist/approval/action.php/' . $infoApproval->token . '/' . $infoRejectAct->token;
            $infoMail->reject = '<a href="' . $lnkReject . '">' . get_string('reject_lnk','enrol_waitinglist') . '</br>';

            /* Commit */
            $trans->allow_commit();

            return array($infoApproval,$infoMail);
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Add_ApprovalEntry


    /**
     * @param           $user
     * @param           $infoMail
     * @param           $toManagers
     *
     * @throws          Exception
     *
     * @creationDate    28/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send notifications for all managers and the user.
     */
    public static function SendNotifications($user,&$infoMail,$toManagers) {
        /* Variables */
        global $SITE;
        $strBody    = null;
        $strSubject = null;
        $bodyText   = null;
        $bodyHtml   = null;

        try {

            /* Extra Info   */
            $infoMail->user = fullname($user);
            $infoMail->site = $SITE->shortname;

            /* Mail for Managers    */
            self::SendNotification_Managers($infoMail,$toManagers);

            /* Mail for Users       */
            $strSubject = get_string('mng_subject','enrol_waitinglist',$infoMail);
            $strBody    = get_string('std_body','enrol_waitinglist',$infoMail);

            /* Content Mail         */
            $bodyText = null;
            $bodyHtml = null;
            if (strpos($strBody, '<') === false) {
                // Plain text only.
                $bodyText = $strBody;
                $bodyHtml = text_to_html($bodyText, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $bodyHtml = format_text($strBody, FORMAT_MOODLE);
                $bodyText = html_to_text($bodyHtml);
            }

            /* Send Mail    */
            email_to_user($user, $SITE->shortname, $strSubject, $bodyText,$bodyHtml);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SendNotifications

    /**
     * @param           $userId
     * @param           $courseId
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get when notification was sent
     */
    public static function GetNotificationSent($userId,$courseId) {
        /* Variables    */
        global $DB,$CFG,$SITE;
        $rdo                = null;
        $sql                = null;
        $params             = null;
        $infoNotification   = null;
        $course             = null;

        try {
            /* Info Notification    */
            $infoNotification = new stdClass();
            $infoNotification->site     = $SITE->shortname;
            /* Add Info Course      */
            self::GetInfoCourse_Notification($courseId,$infoNotification);

            /* Search Criteria  */
            $params = array();
            $params['user']     = $userId;
            $params['course']   = $courseId;
            $params['approved'] = 0;
            $params['rejected'] = 0;
            $params['unenrol']  = 0;

            /* SQL Instruction */
            $sql = " SELECT	ea.id,
                            ea.timesent,
                            ea.token,
                            ea.arguments
                     FROM	{enrol_approval}	ea
                     WHERE	ea.userid 	= :user
                        AND	ea.courseid = :course
                        AND ea.timesent IS NOT NULL
                        AND ea.timesent != 0
                        AND	ea.approved = :approved
                        AND ea.rejected = :rejected
                        AND ea.unenrol	= :unenrol ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Info Notification */
                $infoNotification->approvalid   = $rdo->id;
                $infoNotification->arguments    = $rdo->arguments;
                $infoNotification->timesent     = userdate($rdo->timesent,'%d.%m.%Y', 99, false);
                $infoNotification->approve      = $CFG->wwwroot . '/enrol/waitinglist/approval/action.php/' . $rdo->token;
                $infoNotification->reject       = $CFG->wwwroot . '/enrol/waitinglist/approval/action.php/' . $rdo->token;

                return $infoNotification;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetNotificationSent

    /**
     * @param           $user
     * @param           $infoNotification
     * @param           $toManagers
     *
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send reminders to the manager
     */
    public static function SendReminder($user,&$infoNotification,$toManagers) {
        /* Variables */
        global $SITE,$DB;
        $lnkApprove = null;
        $lnkReject  = null;
        $rdo        = null;
        $params     = null;


        try {
            /* Extra Info   */
            $infoNotification->user = fullname($user);
            $infoNotification->site = $SITE->shortname;

            /* Action Tokens */
            $params = array();
            $params['approvalid']   = $infoNotification->approvalid;

            /* Approve Token    */
            $params['action']       = APPROVED_ACTION;
            $rdo = $DB->get_record('enrol_approval_action',$params);
            $lnkApprove =   $infoNotification->approve . '/' . $rdo->token;
            $infoNotification->approve = '<a href="' . $lnkApprove . '">' . get_string('approve_lnk','enrol_waitinglist') . '</br>';

            /* Reject Token */
            $params['action']       = REJECTED_ACTION;
            $rdo = $DB->get_record('enrol_approval_action',$params);
            $lnkReject  = $infoNotification->reject . '/' . $rdo->token;
            $infoNotification->reject = '<a href="' . $lnkReject . '">' . get_string('reject_lnk','enrol_waitinglist') . '</br>';

            /* Send Mail */
            self::SendNotification_Managers($infoNotification,$toManagers,true);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SendReminder

    /**
     * @param           $args
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get request connected with the notification
     * 
     * @updateDate      13/09/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Add company
     */
    public static function Get_NotificationRequest($args) {
        /* Variables */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $params         = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['approve']  = 0;
            $params['reject']   = 0;
            $params['unenrol']  = 0;
            $params['request']  = $args[0];
            $params['action']   = $args[1];

            /* SQL Instruction  */
            $sql = " SELECT 	ea.id,
                                ea.userid,
                                ea.companyid,
                                ea.courseid,
                                ea.userenrolid,
                                ea.waitinglistid,
                                ea.methodtype,
                                ea.seats,
                                eact.action,
                                ea.approved,
                                ea.rejected
                     FROM		{enrol_approval}		  ea
                        JOIN	{enrol_approval_action}	  eact	ON 	eact.approvalid = ea.id
                                                                AND eact.token		= :action
                     WHERE	ea.token    = :request
                        AND	ea.approved = :approve
                        AND	ea.rejected = :reject
                        AND ea.unenrol  = :unenrol
                        AND (ea.timesent IS NOT NULL OR ea.timereminder IS NOT NULL)
                        AND (ea.timesent != 0 OR ea.timereminder != 0) ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Check_NotificationRequest

    /**
     * @param           $userId
     * @param           $courseId
     * @param           $waitingId
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the request connected
     * 
     * @updateDate      16/09/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Add company id
     */
    public static function Get_Request($userId,$courseId,$waitingId) {
        /* Variables */
        global $DB;
        $sql    = null;
        $params = null;
        $rdo    = null;

        try {
            /* Search Criteria */
            $params = array();
            $params['user']     = $userId;
            $params['course']   = $courseId;
            $params['waiting']  = $waitingId;

            /* SQL Instruction */
            $sql = " SELECT ea.id,
                            ea.userid,
                            ea.companyid,
                            ea.courseid,
                            ea.userenrolid,
                            ea.waitinglistid,
                            ea.methodtype,
                            ea.seats,
                            ea.arguments,
                            ea.approved,
                            ea.rejected
                     FROM	  {enrol_approval}	ea
                     WHERE	ea.waitinglistid 	= :waiting
                        AND	ea.courseid 		= :course
                        AND ea.userid 			= :user ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_Request

    /**
     * @param           $infoRequest
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Apply the action selected by the manager
     */
    public static function ApplyAction_FromManager($infoRequest) {
        /* Variables */
        $instanceWaiting    = null;
        $exit               = true;

        try {
            /* Check Action */
            switch ($infoRequest->action) {
                case APPROVED_ACTION:
                    $exit =  self::ApproveAction($infoRequest);

                    break;
                case REJECTED_ACTION:
                    $exit =  self::RejectAction($infoRequest);

                    break;
            }//switch_action

            return $exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ApplyAction_FromManager

    /**
     * @param           $courseId
     * @param           $waitingId
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    30/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all approval requests
     */
    public static function ApprovalRequests($courseId,$waitingId) {
        /* Variables */
        $approvalRequests   = null;
        $isCompanyDemanded  = null;
        
        try {
            /* Get basic information for the course instance*/
            $approvalRequests = self::GetBasicInfo($courseId,$waitingId);

            /* Approval Requests */
            $isCompanyDemanded = self::IsCompanyDemanded($waitingId);
            $approvalRequests->requests = self::Get_ApprovalRequests($courseId,$waitingId,$isCompanyDemanded);

            return $approvalRequests;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_InfoInstance

    /**
     * @param           $approvalRequests
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the requests report
     */
    public static function Display_ApprovalRequests($approvalRequests) {
        /* Variables */
        global $OUTPUT;
        $content    = '';
        $url        = null;
        $lnkCourse  = null;

        try {
            $content .= html_writer::start_div('block_approval');
                /* Name Course  */
                $content .= self::AddNameCourse($approvalRequests->id,$approvalRequests->name);

                /* Basic Info && Requests */
                if (!$approvalRequests->requests) {
                    $content .= html_writer::start_tag('label',array('class' => ' label_approval_course'));
                        $content .= get_string('no_request','enrol_waitinglist');
                    $content .= html_writer::end_tag('label');
                }else {
                    /* Basic Info   */
                    $content .= self::AddBasicInfo($approvalRequests);

                    /* Requests     */
                    $content .= self::AddRequestsInfo($approvalRequests);
                }//if_requests

                /* Return to the course */
                $url        = new moodle_url('/course/view.php',array('id' => $approvalRequests->id));
                $content .= '</br>';
                $content .= $OUTPUT->action_link($url,get_string('rpt_back','enrol_waitinglist'));
            $content .= html_writer::end_div();//block_approval

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Display_ApprovalRequests

    /**
     * @param           $userId
     * @param           $courseId
     *
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    16/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the information to send when a request has been approved
     */
    public static function Info_NotificationApproved($userId,$courseId,$instanceId = 0) {
        /* Variables    */
        global $SITE;
        $infoNotification   = null;
        $course             = null;
        $managers           = null;
        $competenceUser     = null;

        try {
            /* Info Notification    */
            $infoNotification       = new stdClass();
            $infoNotification->site = $SITE->shortname;
            /* Add Info Course      */
            self::GetInfoCourse_Notification($courseId,$infoNotification);

            /* Add Info User        */
            self::GetInfoUser_NotificationApproved($userId,$infoNotification,$instanceId);

            return $infoNotification;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfo_NotificationApproved

    /**
     * @param           $courseId
     * @param           $infoNotification
     *
     * @throws          Exception
     *
     * @creationDate    15/02/2016
     * @author          efaktor     (fbv)
     *
     * Description
     * Get course information that has been approved
     */
    public static function GetInfoCourse_Notification($courseId,&$infoNotification) {
        /* Variables */
        global $DB;
        $sql        = null;
        $params     = null;
        $rdo        = null;
        $instructor = null;
        $urlHome    = null;

        try {


            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseId;
            $params['visible']  = 1;

            /* SQL Isntruction  */
            $sql = " SELECT	c.id,
                            c.fullname,
                            c.summary,
                            c.startdate,
                            lo.name   as 'location',
                            ci.value  as 'instructor',
                            u.firstname,
                            u.lastname,
                            u.email,
                            hp.value as 'homepage',
                            hv.value as 'homevisible',
                            e.customtext3 as 'priceinternal',
                            e.customtext4 as 'priceexternal'
                     FROM			{course}					c
                        JOIN        {enrol}                     e   ON  e.courseid = c.id
                                                                    AND e.status   = 0
                                                                    AND e.enrol    = 'waitinglist'
                        -- Instructors
                        LEFT JOIN	{course_format_options}		ci 	ON 	ci.courseid = c.id
                                                                    AND	ci.name		= 'manager'
                        LEFT JOIN	{user}						u  	ON 	u.id 		= ci.value
                        -- Location
                        LEFT JOIN	{course_format_options}		cl 	ON 	cl.courseid = c.id
                                                                    AND	cl.name like 'course_location'
                        LEFT JOIN   {course_locations}			lo	ON	lo.id = cl.value
                        -- HOME PAGE
                        LEFT JOIN	{course_format_options}		hp	ON  hp.courseid = c.id
                                                                    AND hp.name 	= 'homepage'
                        -- HOME PAGE VISIBLE
                        LEFT JOIN	{course_format_options}		hv	ON	hv.courseid = c.id
                                                                    AND hv.name 	= 'homevisible'
                     WHERE	c.id 		= :course
                        AND	c.visible 	= :visible ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Course Info  */
                $infoNotification->course       = $rdo->fullname;
                $infoNotification->internal     = $rdo->priceinternal;
                $infoNotification->external     = $rdo->priceexternal;
                $infoNotification->summary      = $rdo->summary;
                if (($rdo->homepage) && ($rdo->homevisible)) {
                    /* Course Home Page */
                    $urlHome = new moodle_url('/local/course_page/home_page.php',array('id' => $courseId));
                }else {
                    /* Course Page  */
                    $urlHome = new moodle_url('/course/view.php',array('id' => $courseId));
                }
                $infoNotification->homepage = '<a href="' . $urlHome . '">'. $rdo->fullname . '</a>';
                $infoNotification->date     = ($rdo->startdate ? userdate($rdo->startdate,'%d.%m.%Y', 99, false) : 'N/A');
                $infoNotification->location = $rdo->location;
                /* Add Info instructor  */
                if ($rdo->instructor) {
                    $instructor = $rdo->firstname . " " . $rdo->lastname . " (" . $rdo->email . ")";
                }//if_instructor
                $infoNotification->instructor = $instructor;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfoCourse_Notification

    /**
     * @param           $infoNotification
     *
     * @throws          Exception
     *
     * @creationDate    16/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send the notifications for approved request to all managers
     */
    public static function SendApprovedNotification_Managers($infoNotification) {
        /* Variables    */
        global $SITE;
        $bodyText   = null;
        $bodyHtml   = null;
        $strSubject = null;
        $strBody    = null;
        $managers   = null;
        $companies  = null;
        $user       = null;

        try {
            /* Get Managers */
            $managers = $infoNotification->managers;

            /* Subject  */
            $strSubject = get_string('mng_approved_subject','enrol_waitinglist',$infoNotification);

            /* Send eMail to managers   */
            if ($managers) {
                foreach ($managers as $manager) {
                    $companies = '';

                    /* Get Info Body eMail  */
                    /* Info User Courses    */
                    $strBody = get_string('mng_approved_body_two','enrol_waitinglist',$infoNotification);

                    /* Info Manager */
                    $companies .= '<ul>';
                    foreach ($manager->companies as $info) {
                        $companies .= '<li>' . $info . '</li>';
                    }
                    $companies .= '</ul>';
                    $strBody .= get_string('mng_approved_body_one','enrol_waitinglist') . $companies . "</br>";

                    $strBody .= "</br>" . "</br>" . get_string('mng_approved_body_end','enrol_waitinglist',$infoNotification);

                    /* Content Mail */
                    $bodyText = null;
                    $bodyHtml = null;
                    if (strpos($strBody, '<') === false) {
                        // Plain text only.
                        $bodyText = $strBody;
                        $bodyHtml = text_to_html($bodyText, null, false, true);
                    } else {
                        // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                        $bodyHtml = format_text($strBody, FORMAT_MOODLE);
                        $bodyText = html_to_text($bodyHtml);
                    }

                    /* Send eMail   */
                    $user = get_complete_user_data('id',$manager->id);
                    email_to_user($user, $SITE->shortname, $strSubject, $bodyText,$bodyHtml);
                }//for_managers
            }//if_managers
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//SendApprovedNotification_Managers

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $userId
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    28/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the competence connected with the user
     */
    private static function GetCompetence_User($userId,$instance = null) {
        /* Variables    */
        global $DB;
        $competence = null;
        $sql        = null;
        $rdo        = null;
        $params     = null;

        try {
            /* Search Criteria */
            $params = array();
            $params['user'] = $userId;

            /**
             * Competence
             */
            $competence = new stdClass();
            $competence->levelZero  = 0;
            $competence->levelOne   = 0;
            $competence->levelTwo   = 0;
            $competence->levelThree = 0;

            /* SQL Instruction */
            if ($instance) {
                /* Search criteria */
                $params['waiting']  = $instance->id;
                $params['course']   = $instance->courseid;

                /**
                 * Manager only connected with company from enrolment method
                 */
                $sql = " SELECT	uicd.companyid 		as 'levelthree',
                                cr_two.parentid		as 'leveltwo',
                                cr_one.parentid 	as 'levelone',
                                cr_zero.parentid	as 'levelzero'
                         FROM		{user_info_competence_data} 		uicd
                            JOIN	{enrol_waitinglist_queue}			ew		ON ew.userid 			= uicd.userid
                                                                                AND ew.companyid 		= uicd.companyid
                                                                                AND ew.waitinglistid 	= :waiting
                                                                                AND ew.courseid			= :course
                            -- LEVEL TWO
                            JOIN	{report_gen_company_relation}  	    cr_two	ON 	cr_two.companyid 		= uicd.companyid
                            JOIN	{report_gen_companydata}			co_two	ON 	co_two.id 				= cr_two.parentid
                                                                                AND co_two.hierarchylevel 	= 2
                            -- LEVEL ONE
                            JOIN	{report_gen_company_relation}   	cr_one	ON 	cr_one.companyid 		= cr_two.parentid
                            JOIN	{report_gen_companydata}			co_one	ON 	co_one.id 				= cr_one.parentid
                                                                                AND co_one.hierarchylevel 	= 1
                            -- LEVEL ZERO
                            JOIN	{report_gen_company_relation}		cr_zero	ON 	cr_zero.companyid 		= cr_one.parentid
                            JOIN	{report_gen_companydata}	  		co_zero	ON 	co_zero.id 				= cr_zero.parentid
                                                                                AND co_zero.hierarchylevel 	= 0
                         WHERE		uicd.userid 	= :user ";
            }else {
                $sql = " SELECT	GROUP_CONCAT(DISTINCT uicd.companyid  	ORDER BY uicd.companyid SEPARATOR ',')		as 'levelthree',
                                GROUP_CONCAT(DISTINCT cr_two.parentid  	ORDER BY cr_two.parentid SEPARATOR ',') 	as 'leveltwo',
                                GROUP_CONCAT(DISTINCT cr_one.parentid  	ORDER BY cr_one.parentid SEPARATOR ',') 	as 'levelone',
                                GROUP_CONCAT(DISTINCT cr_zero.parentid  ORDER BY cr_zero.parentid SEPARATOR ',') 	as 'levelzero'
                         FROM		{user_info_competence_data} 	uicd
                            -- LEVEL TWO
                            JOIN	{report_gen_company_relation}   	cr_two	ON 	cr_two.companyid 		= uicd.companyid
                            JOIN	{report_gen_companydata}			co_two	ON 	co_two.id 				= cr_two.parentid
                                                                                AND co_two.hierarchylevel 	= 2
                            -- LEVEL ONE
                            JOIN	{report_gen_company_relation}   	cr_one	ON 	cr_one.companyid 		= cr_two.parentid
                            JOIN	{report_gen_companydata}			co_one	ON 	co_one.id 				= cr_one.parentid
                                                                                AND co_one.hierarchylevel 	= 1
                            -- LEVEL ZERO
                            JOIN	{report_gen_company_relation}		cr_zero	ON 	cr_zero.companyid 		= cr_one.parentid
                            JOIN	{report_gen_companydata}	  		co_zero	ON 	co_zero.id 				= cr_zero.parentid
                                                                                AND co_zero.hierarchylevel 	= 0
                         WHERE		uicd.userid = :user ";
            }//if_else


            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $competence = new stdClass();
                $competence->levelZero  = ($rdo->levelzero  ? $rdo->levelzero   : 0);
                $competence->levelOne   = ($rdo->levelone   ? $rdo->levelone    : 0);
                $competence->levelTwo   = ($rdo->leveltwo   ? $rdo->leveltwo    : 0);
                $competence->levelThree = ($rdo->levelthree ? $rdo->levelthree  : 0);
            }//if_rdo

            return $competence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompetence_User

    /**
     * @param           $competence
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    28/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all managers connected with user
     */
    private static function GetManager_User($competence) {
        /* Variables */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $myManagers     = array();
        $infoManager    = null;

        try {

            /* Get levels of Managers   */
            $sql = " SELECT		DISTINCT u.id
                     FROM		{report_gen_company_manager}  rm
                        JOIN	{user}						  u 	ON 	u.id 		= rm.managerid
                                                                    AND	u.deleted 	= 0
                     WHERE	(rm.levelzero IN ($competence->levelZero) AND rm.levelone IS NULL
                             AND
                             rm.leveltwo IS NULL AND rm.levelthree IS NULL)
                            OR
                            (rm.levelzero IN ($competence->levelZero) AND rm.levelone IN ($competence->levelOne)
                             AND
                             rm.leveltwo IS NULL AND rm.levelthree IS NULL)
                            OR
                            (rm.levelzero IN ($competence->levelZero) AND rm.levelone IN ($competence->levelOne)
                             AND
                             rm.leveltwo IN ($competence->levelTwo)   AND rm.levelthree IS NULL)
                            OR
                            (rm.levelzero IN ($competence->levelZero) AND  rm.levelone IN ($competence->levelOne)
                             AND
                             rm.leveltwo IN ($competence->levelTwo)   AND rm.levelthree IN ($competence->levelThree)) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Add Manager  */
                    $myManagers[$instance->id] = $instance->id;
                }//for_Rdo
            }//if_rdo

            return $myManagers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetManager_User

    /**
     * @param           $competence
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    15/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all managers that have to receive an approved notification
     */
    private static function GetInfoManagers_NotificationApproved($competence) {
        /* Variables    */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $managers       = array();
        $infoManager    = null;
        $data           = false;

        try {

            /* First Level Three */
            $sql = self::GetSQLManagersCompanyByHierarchy($competence->levelZero,$competence->levelOne,$competence->levelTwo,$competence->levelThree,3);
            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                $data = true;
            }else {
                /* Second Level */
                $sql = self::GetSQLManagersCompanyByHierarchy($competence->levelZero,$competence->levelOne,$competence->levelTwo,$competence->levelThree,2);
                /* Execute  */
                $rdo = $DB->get_records_sql($sql);
                if ($rdo) {
                    $data = true;
                }
            }//if_rdo

            if ($data) {
                foreach ($rdo as $instance) {
                    if (array_key_exists($instance->managerid,$managers)) {
                       $infoManager = $managers[$instance->managerid];
                    }else {
                        $infoManager = new stdClass();
                        $infoManager->id        = $instance->managerid;
                        $infoManager->companies = array();
                    }//if_manager_exists

                    /* Add Company  */
                    $infoManager->companies[] = $instance->company;

                    /* Add Manager */
                    $managers[$instance->managerid] = $infoManager;
                }//of_rdo
            }//if_data

            return $managers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetInfoManagers_NotificationApproved

    /**
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     * @param           $hierarchy
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    01/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Managers y level of hierachy
     */
    private static function GetSQLManagersCompanyByHierarchy($levelZero,$levelOne,$levelTwo,$levelThree,$hierarchy) {
        /* Variables */
        $sql = null;
        
        try {
            switch ($hierarchy) {
                case 0:
                    $sql = " SELECT		rm.id,
                                        rm.managerid,
                                        co_zero.name as 'company'
                             FROM		{report_gen_company_manager}  rm
                                JOIN	{user}						  u 		ON 	u.id 		            = rm.managerid
                                                                                AND	u.deleted 	            = 0
                                -- LEVEL ZERO
                                JOIN 	{report_gen_companydata}	  co_zero	ON 	co_zero.id 				= rm.levelzero
                                                                                AND	co_zero.hierarchylevel 	= 0
                             WHERE	(rm.levelzero IN ($levelZero) AND rm.levelone IS NULL AND rm.leveltwo IS NULL  AND rm.levelthree IS NULL)
                             ORDER BY rm.managerid ";

                    break;
                case 1:
                    $sql = " SELECT		rm.id,
                                        rm.managerid,
                                        CONCAT(co_zero.name,'/',co_one.name) as 'company'
                             FROM		{report_gen_company_manager}  rm
                                JOIN	{user}						  u 		ON 	u.id 		            = rm.managerid
                                                                                AND	u.deleted 	            = 0
                                -- LEVEL ZERO
                                JOIN 	{report_gen_companydata}	  co_zero	ON 	co_zero.id 				= rm.levelzero
                                                                                AND	co_zero.hierarchylevel 	= 0
                                -- LEVEL ONE
                                JOIN	{report_gen_companydata}	  co_one	ON	co_one.id				= rm.levelone
                                                                                AND	co_one.hierarchylevel	= 1
                             WHERE	(rm.levelzero IN ($levelZero) AND rm.levelone IN ($levelOne) AND rm.leveltwo IS NULL  AND rm.levelthree IS NULL)
                             ORDER BY rm.managerid ";

                    break;
                case 2:
                    $sql = " SELECT		rm.id,
                                        rm.managerid,
                                        CONCAT(co_zero.name,'/',co_one.name,'/',co_two.name) as 'company'
                             FROM		{report_gen_company_manager}  rm
                                JOIN	{user}						  u 		ON 	u.id 		            = rm.managerid
                                                                                AND	u.deleted 	            = 0
                                -- LEVEL ZERO
                                JOIN 	{report_gen_companydata}	  co_zero	ON 	co_zero.id 				= rm.levelzero
                                                                                AND	co_zero.hierarchylevel 	= 0
                                -- LEVEL ONE
                                JOIN	{report_gen_companydata}	  co_one	ON	co_one.id				= rm.levelone
                                                                                AND	co_one.hierarchylevel	= 1
                                -- LEVEL TWO
                                JOIN	{report_gen_companydata}      co_two	ON	co_two.id				= rm.leveltwo
                                                                                AND co_two.hierarchylevel	= 2
                             WHERE	(rm.levelzero IN ($levelZero) AND rm.levelone IN ($levelOne) AND rm.leveltwo IN ($levelTwo)  AND rm.levelthree IS NULL)
                             ORDER BY rm.managerid ";

                    break;
                case 3:
                    $sql = " SELECT		rm.id,
                                        rm.managerid,
                                        CONCAT(co_zero.name,'/',co_one.name,'/',co_two.name,'/',co_tre.name) as 'company'
                             FROM		{report_gen_company_manager}  rm
                                JOIN	{user}						  u 		ON 	u.id 		            = rm.managerid
                                                                                AND	u.deleted 	            = 0
                                -- LEVEL ZERO
                                JOIN 	{report_gen_companydata}	  co_zero	ON 	co_zero.id 				= rm.levelzero
                                                                                AND	co_zero.hierarchylevel 	= 0
                                -- LEVEL ONE
                                JOIN	{report_gen_companydata}	  co_one	ON	co_one.id				= rm.levelone
                                                                                AND	co_one.hierarchylevel	= 1
                                -- LEVEL TWO
                                JOIN	{report_gen_companydata}      co_two	ON	co_two.id				= rm.leveltwo
                                                                                AND co_two.hierarchylevel	= 2
                                -- LEVEL THREE
                                JOIN	{report_gen_companydata}	  co_tre    ON 	co_tre.id = rm.levelthree
                                                                                AND co_tre.hierarchylevel 	= 3
                             WHERE	(rm.levelzero IN ($levelZero) AND rm.levelone IN ($levelOne) AND rm.leveltwo IN ($levelTwo)  AND rm.levelthree IN ($levelThree))
                             ORDER BY rm.managerid ";

                    break;
            }//hierarchy

            return $sql;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetSQLManagersCompanyByHierarchy
    
    /**
     * @param           $userId
     * @param           $infoNotification
     *
     * @throws          Exception
     *
     * @creationDate    16/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get user information to send
     */
    private static function GetInfoUser_NotificationApproved($userId,&$infoNotification,$instanceId = 0) {
        /* Variables    */
        global $DB;
        $sql        = null;
        $sqlWhere   = null;
        $params     = null;
        $rdo        = null;
        $companies  = null;

        try {
            /* Search criteria  */
            $params = array();
            $params['user'] = $userId;

            /* SQL Instruction  */
            $sql = " SELECT	u.id,
                            CONCAT(u.firstname,' ',u.lastname) 								as 'user',
                            GROUP_CONCAT(DISTINCT co.name ORDER BY co.name SEPARATOR '#') 	as 'companies'
                     FROM	{user} u
                        -- COMPETENCE
                        JOIN	{user_info_competence_data}	ucd		ON ucd.userid 	= u.id
                        JOIN	{report_gen_companydata}	co 		ON co.id 		= ucd.companyid ";

            $sqlWhere = " WHERE 	 u.id = :user
                          GROUP BY u.id ";
            if ($instanceId) {
                $params['waiting'] = $instanceId;

                $sql .= " JOIN	{enrol_waitinglist_queue}	wq	ON 	wq.userid		    = u.id
                                                                AND wq.companyid 		= co.id
                                                                AND wq.waitinglistid 	= :waiting ";
            }
            /* Execute  */
            $sql .= $sqlWhere;
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Add Info User    */
                $infoNotification->user = $rdo->user;
                $companies = explode('#',$rdo->companies);
                $infoNotification->companies_user = '<ul>';
                foreach ($companies as $company) {
                    $infoNotification->companies_user .= '<li>' . $company . '</li>';
                }
                $infoNotification->companies_user .= '</ul>';
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetInfoUser_NotificationApproved

    /**
     * @param           $infoMail
     * @param           $toManagers
     * @param           $reminder
     *
     * @throws          Exception
     *
     * @creationDate    28/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send  notifications for the managers
     */
    private static function SendNotification_Managers($infoMail,$toManagers,$reminder=false) {
        /* Variables */
        global $SITE,$DB;
        $strBody    = null;
        $strSubject = null;
        $bodyText   = null;
        $bodyHtml   = null;
        $manager    = null;
        $sent       = false;
        $time       = null;

        try {
            /* Local Time   */
            $time = time();

            /* Mails For Managers */
            if ($reminder) {
                $strSubject = get_string('subject_reminder','enrol_waitinglist',$infoMail);
                $strBody = get_string('body_reminder','enrol_waitinglist',$infoMail);
            }else {
                $strSubject = get_string('mng_subject','enrol_waitinglist',$infoMail);
                $strBody    = get_string('mng_body','enrol_waitinglist',$infoMail);
            }//if_remainder

            /* Content Mail */
            $bodyText = null;
            $bodyHtml = null;
            if (strpos($strBody, '<') === false) {
                // Plain text only.
                $bodyText = $strBody;
                $bodyHtml = text_to_html($bodyText, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $bodyHtml = format_text($strBody, FORMAT_MOODLE);
                $bodyText = html_to_text($bodyHtml);
            }

            /* Send Mail    */
            foreach ($toManagers as $managerId => $info) {
                $manager = get_complete_user_data('id',$managerId);
                if (email_to_user($manager, $SITE->shortname, $strSubject, $bodyText,$bodyHtml)) {
                    /* Notification Sent  */
                    $sent = true;
                }//send_mail
            }//for_Each_manager

            /* Update Approval Entry as Sent    */
            if ($sent) {
                $infoApproval = new stdClass();
                $infoApproval->id           = $infoMail->approvalid;
                if ($reminder) {
                    $infoApproval->timereminder = $time;
                }else {
                    $infoApproval->timesent     = $time;
                }
                $infoApproval->timemodified = $time;
                /* Execute */
                $DB->update_record('enrol_approval',$infoApproval);
            }//if_sent
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SendNotification_Managers

    /**
     * @param           $infoRequest
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Reject action
     */
    private static function RejectAction($infoRequest) {
        /* Variables */
        global $DB,$SITE;
        $instanceReject = null;
        $infoMail       = null;
        $strBody        = null;
        $strSubject     = null;
        $bodyText       = null;
        $bodyHtml       = null;
        $user           = null;
        $course         = null;
        $time           = null;

        try {
            /* Local Time   */
            $time = time();

            /* Instance Reject  */
            $instanceReject = new stdClass();
            $instanceReject->id             = $infoRequest->id;
            $instanceReject->userid         = $infoRequest->userid;
            $instanceReject->courseid       = $infoRequest->courseid;
            $instanceReject->waitinglistid  = $infoRequest->waitinglistid;
            $instanceReject->methodtype     = $infoRequest->methodtype;
            $instanceReject->approved       = 0;
            $instanceReject->rejected       = 1;
            $instanceReject->timemodified   = $time;

            /* Execute */
            $DB->update_record('enrol_approval',$instanceReject);

            /* Send mail to the user */
            $user   = get_complete_user_data('id',$infoRequest->userid);

            $instances = $DB->get_records('enrol', array('courseid'=>$infoRequest->courseid, 'enrol'=>'waitinglist'), 'id ASC');
            foreach ($instances as $instance) {
                $plugin = enrol_get_plugin('waitinglist');
                $plugin->unenrol_user($instance,$infoRequest->userid);
            }

            $infoMail = new stdClass();
            $infoMail->user     = fullname($user);
            $infoMail->site     = $SITE->shortname;
            $infoMail->sent     = userdate($time,'%d.%m.%Y', 99, false);
            self::GetInfoCourse_Notification($infoRequest->courseid,$infoMail);

            /* Mail for Users       */
            $strSubject = get_string('mng_subject','enrol_waitinglist',$infoMail);
            $strBody    = get_string('request_rejected','enrol_waitinglist',$infoMail);

            /* Content Mail         */
            $bodyText = null;
            $bodyHtml = null;
            if (strpos($strBody, '<') === false) {
                // Plain text only.
                $bodyText = $strBody;
                $bodyHtml = text_to_html($bodyText, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $bodyHtml = format_text($strBody, FORMAT_MOODLE);
                $bodyText = html_to_text($bodyHtml);
            }

            /* Delete entries from user_enrolments and enrol_waitinglist_queue */
            $DB->delete_records('user_enrolments',array('userid'    => $infoRequest->userid,
                                                        'enrolid'   => $infoRequest->waitinglistid));
            $DB->delete_records('enrol_waitinglist_queue',array('userid'        => $infoRequest->userid,
                                                                'courseid'      => $infoRequest->courseid,
                                                                'waitinglistid' => $infoRequest->waitinglistid));
            /* Send Mail    */
            email_to_user($user, $SITE->shortname, $strSubject, $bodyText,$bodyHtml);

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//RejectAction

    /**
     * @param           $infoRequest
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Approve Action
     */
    private static function ApproveAction($infoRequest) {
        /* Variables */
        global $CFG,$SITE,$DB;
        $instanceWaiting    = null;
        $queueEntry         = null;
        $instanceApprove    = null;
        $infoMail           = null;
        $strBody            = null;
        $strSubject         = null;
        $bodyText           = null;
        $bodyHtml           = null;
        $user               = null;
        $course             = null;
        $urlCourse          = null;
        $time               = null;

        try {
            /* Include library to create queue entry and update waitinglist */
            require_once($CFG->dirroot . '/enrol/invoice/invoicelib.php');

            /* Enrol instance   */
            $instanceWaiting = self::GetInstance_EnrolWaiting($infoRequest->courseid,$infoRequest->waitinglistid);

            /* Waiting List ENTRY  */
            $queueEntry = self::Add_EntryWaitingList($instanceWaiting,$infoRequest);

            if ($queueEntry) {
                /* Activate Invoice Entry */
                if (enrol_get_plugin('invoice')) {
                    if ($instanceWaiting->{WAITINGLIST_FIELD_INVOICE}) {
                        Invoices::activate_enrol_invoice($infoRequest->userid,$infoRequest->courseid,$instanceWaiting->id);
                    }//if_invoice_info
                }//if

                /* Local Time   */
                $time = time();

                /* Instance Approve  */
                $params = array();
                $params['userid']   = $infoRequest->userid;
                $params['enrolid']  = $infoRequest->waitinglistid;
                $rdo = $DB->get_record('user_enrolments',$params,'id');

                $instanceApprove = new stdClass();
                $instanceApprove->id             = $infoRequest->id;
                $instanceApprove->userid         = $infoRequest->userid;
                $instanceApprove->courseid       = $infoRequest->courseid;
                if ($rdo) {
                    $instanceApprove->userenrolid    = $rdo->id;
                }

                $instanceApprove->waitinglistid  = $infoRequest->waitinglistid;
                $instanceApprove->methodtype     = $infoRequest->methodtype;
                $instanceApprove->approved       = 1;
                $instanceApprove->rejected       = 0;
                $instanceApprove->timemodified   = $time;

                /* Execute */
                $DB->update_record('enrol_approval',$instanceApprove);

                /* Send mail to the user */
                $user   = get_complete_user_data('id',$infoRequest->userid);

                $infoMail = new stdClass();
                $infoMail->user         = fullname($user);
                $infoMail->site         = $SITE->shortname;
                $infoMail->sent         = userdate($time,'%d.%m.%Y', 99, false);
                self::GetInfoCourse_Notification($infoRequest->courseid,$infoMail);

                /* Mail for Users       */
                $strSubject = get_string('mng_subject','enrol_waitinglist',$infoMail);
                $strBody    = get_string('request_approved','enrol_waitinglist',$infoMail);

                /* Content Mail         */
                $bodyText = null;
                $bodyHtml = null;
                if (strpos($strBody, '<') === false) {
                    // Plain text only.
                    $bodyText = $strBody;
                    $bodyHtml = text_to_html($bodyText, null, false, true);
                } else {
                    // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                    $bodyHtml = format_text($strBody, FORMAT_MOODLE);
                    $bodyText = html_to_text($bodyHtml);
                }

                /* Send Mail    */
                email_to_user($user, $SITE->shortname, $strSubject, $bodyText,$bodyHtml);

                return true;
            }else {
                return false;
            }//if_else

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ApproveAction

    /**
     * @param           $instanceWaiting
     * @param           $infoRequest
     *
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the waiting list entry.
     * 
     * @updateDate      13/09/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Add company
     */
    private static function Add_EntryWaitingList($instanceWaiting,$infoRequest) {
        /* Variables */
        $queueEntry = null;
        $method     = null;
        $class      = null;
        $myClass    = null;

        try {
            /* Queue Entry */
            $queueEntry = new stdClass();
            $queueEntry->waitinglistid      = $infoRequest->waitinglistid;
            $queueEntry->courseid           = $infoRequest->courseid;
            $queueEntry->userid             = $infoRequest->userid;
            $queueEntry->companyid          = $infoRequest->companyid;
            $queueEntry->methodtype         = $infoRequest->methodtype;
            $queueEntry->timecreated        = time();
            $queueEntry->queueno            = 0;
            $queueEntry->seats              = $infoRequest->seats;
            $queueEntry->allocseats         = 0;
            $queueEntry->confirmedseats     = 0;
            $queueEntry->enroledseats       = 0;
            $queueEntry->offqueue           = 0;
            $queueEntry->timemodified       = $queueEntry->timecreated;


            /* Method               */
            $method = $infoRequest->methodtype;
            /* Get Class Method     */
            $class = '\enrol_waitinglist\method\\' . $method . '\enrolmethod'  . $method ;

            $methods = array();
            if (class_exists($class)){
                $themethod = $class::get_by_course($infoRequest->courseid, $infoRequest->waitinglistid);
                if($themethod){$methods[$method]=$themethod;}

                $queueEntry->id = $methods[$method]->add_to_waitinglist_from_approval($instanceWaiting,$queueEntry);
            }

            return $queueEntry;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_EntryQueue

    /**
     * @param           $courseId
     * @param           $waitingId
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get waitinglist instance connected with the course
     */
    private static function GetInstance_EnrolWaiting($courseId,$waitingId) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['courseid'] = $courseId;
            $params['id']       = $waitingId;
            $params['enrol']    = 'waitinglist';
            $params['status']   = 0;

            /* Execute  */
            $rdo = $DB->get_record('enrol',$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInstance_EnrolWaiting

    /**
     * @param           $courseId
     * @param           $waitingId
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    30/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get basic information about the course, name, participants...
     */
    private static function GetBasicInfo($courseId,$waitingId) {
        /* Variables    */
        global $DB;
        $approvalRequests = null;
        $params = null;
        $rdo    = null;
        $sql    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseId;
            $params['enrol']    = 'waitinglist';
            $params['status']   = 0;
            $params['waiting']  = $waitingId;

            /* SQL Instruction  */
            $sql = " SELECT	c.id,
                            c.fullname,
                            cc.name			as 'category',
                            e.customint2 	as 'participants',
                            e.id 			as 'waiting',
                            count(ea.id) 	as 'total',
                            count(eaa.id) 	as 'approved',
                            count(ear.id) 	as 'rejected'
                     FROM		{course}			c
                        JOIN	{course_categories}	cc	ON	cc.id 				= c.category
                        JOIN	{enrol}			    e	ON 	e.courseid 			= c.id
                                                        AND	e.status			= :status
                                                        AND e.enrol				= :enrol
                                                        AND e.id                = :waiting
                        JOIN	{enrol_approval}	ea	ON	ea.waitinglistid 	= e.id
                                                        AND	ea.courseid			= e.courseid
                                                        AND	ea.unenrol			= 0
                        -- APPROVED
                        LEFT JOIN {enrol_approval} eaa 	ON 	eaa.id 			= ea.id
                                                        AND eaa.approved 	= 1
                                                        AND	eaa.unenrol	    = 0
                        -- REJECTED
                        LEFT JOIN {enrol_approval} ear 	ON 	ear.id 			= ea.id
                                                        AND ear.rejected 	= 1
                                                        AND	ear.unenrol	    = 0
                     WHERE c.id = :course ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Basic info */
                $approvalRequests = new stdClass();
                $approvalRequests->id           = $courseId;
                $approvalRequests->name         = $rdo->fullname;
                $approvalRequests->category     = $rdo->category;
                $approvalRequests->participants = $rdo->participants;
                $approvalRequests->waitingId    = $waitingId;
                $approvalRequests->requests     = null;
                $approvalRequests->attended     = $rdo->total;
                $approvalRequests->approved     = $rdo->approved;
                $approvalRequests->rejected     = $rdo->rejected;
                $approvalRequests->noAttended   = $approvalRequests->attended - ($rdo->approved + $rdo->rejected);
            }//if_rdo

            return $approvalRequests;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetBasicInfo

    /**
     * @param           $enrolId
     *
     * @return          bool|null
     * @throws          Exception
     *
     * @creationDate    26/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check id the company is demanded or not
     */
    private static function IsCompanyDemanded($enrolId) {
        /* Variables */
        global $DB;
        $rdo = null;
        $isCompanyDemanded = null;

        try {
            $rdo = $DB->get_record('enrol',array('id' => $enrolId),'customint7');
            if ($rdo) {
                if ($rdo->customint7 != ENROL_COMPANY_NO_DEMANDED) {
                    $isCompanyDemanded = true;
                }//if_custom
            }//if_rdo

            return $isCompanyDemanded;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsCompanyDemanded

    /**
     * @param           $userId
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    01/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get workplace connected with user. Competence profile
     */
    private static function GetWorkplaceConnected($userId) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        $workplace  = null;

        try {
            /* Search criteria  */
            $params =array();
            $params['user_id']  = $userId;
            $params['level']    = 3;

            /* SQL Instruction */
            $sql = " SELECT 	uic.userid,
                                GROUP_CONCAT(DISTINCT CONCAT(co.industrycode, ' - ',co.name) ORDER BY co.industrycode,co.name SEPARATOR '#SE#') 	as 'workplace'
                     FROM		{user_info_competence_data}	uic
                        JOIN	{report_gen_companydata}	co	ON co.id = uic.companyid
                     WHERE	uic.userid = :user_id
                        AND uic.level  = :level ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->workplace) {
                    $workplace =     str_replace('#SE#','</br>',$rdo->workplace);
                }
            }//if_Rdo

            return $workplace;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetWorkplaceConnected

    /**
     * @param           $courseId
     * @param           $waitingId
     * @param           $isCompanyDemanded
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    30/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all approval requests
     * 
     * @updateDate      01/11/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Get correct workpalce
     */
    private static function Get_ApprovalRequests($courseId,$waitingId,$isCompanyDemanded) {
        /* Variables */
        global $DB;
        $params         = null;
        $rdo            = null;
        $ql             = null;
        $infoRequest    = null;
        $requests       = array();

        try {
            /* Search Criteria */
            $params = array();
            $params['course']   = $courseId;
            $params['waiting']  = $waitingId;

            /* SQL Instruction */
            $sql = " SELECT	u.id,
                            CONCAT(u.firstname,' ',u.lastname) as 'name',
                            u.email,
                            ea.arguments,
                            ea.seats,
                            ea.approved,
                            ea.rejected,
                            ea.companyid,
                            co.industrycode,
                            co.name         as 'company'
                     FROM		  {enrol_approval}	        ea
                        JOIN 	  {user}			        u	ON u.id 		= ea.userid
                                                                AND u.deleted 	= 0
                        LEFT JOIN {report_gen_companydata}  co	ON	co.id	= 	ea.companyid
                     WHERE	ea.courseid 		= :course
                        AND ea.waitinglistid 	= :waiting
                     ORDER BY u.firstname,u.lastname ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Info Request */
                    $infoRequest = new stdClass();
                    $infoRequest->user      = $instance->id;
                    $infoRequest->name      = $instance->name;
                    $infoRequest->email     = $instance->email;
                    $infoRequest->arguments = $instance->arguments;
                    $infoRequest->seats     = $instance->seats;
                    $infoRequest->approved  = $instance->approved;
                    $infoRequest->rejected  = $instance->rejected;
                    if ($isCompanyDemanded) {
                        $infoRequest->arbeidssted      = $instance->industrycode . ' - ' . $instance->company;
                    }else {
                        if ($instance->companyid) {
                            $infoRequest->arbeidssted      = $instance->industrycode . ' - ' . $instance->company;
                        }else {
                            $infoRequest->arbeidssted = self::GetWorkplaceConnected($instance->id);
                        }
                    }//if_comapnyDemanded

                    /* Add Request */
                    $requests[$instance->id] = $infoRequest;
                }//foreach_rdo
            }//if_rdo

            return $requests;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_ApprovalRequests

    /**
     * @param           $userId
     * @param           $courseId
     *
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    24/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate token
     */
    private static function GenerateToken($userId,$courseId) {
        /* Variables    */
        global $DB;
        $ticket = null;
        $token  = null;

        try {
            /* Ticket - Something long and Unique   */
            $ticket     = uniqid(mt_rand(),1);
            $ticket     = random_string() . $userId . '_' . time() . '_' . $courseId . '_' . $ticket . random_string();
            $token      = str_replace('/', '.', self::GenerateHash($ticket));

            /* Check if justs exist for other user  */
            while ($DB->record_exists('enrol_approval',array('userid' => $userId,'token' => $token))) {
                /* Ticket - Something long and Unique   */
                $ticket     = uniqid(mt_rand(),1);
                $ticket     = random_string() . $userId . '_' . time() . '_' . $courseId . '_' . $ticket . random_string();
                $token      = str_replace('/', '.', self::GenerateHash($ticket));
            }//while

            return $token;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GenerateToken

    /**
     * @param           $courseId
     * @param           $action
     *
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    24/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate the token connected with the action
     */
    private static function GenerateToken_Action($courseId,$action) {
        /* Variables    */
        global $DB;
        $ticket     = null;
        $token      = null;

        try {
            /* Ticket - Something long and Unique   */
            $ticket     = uniqid(mt_rand(),1);
            $ticket     = random_string() . $action . '_' . time() . '_' . $courseId . '_' . $ticket . random_string();
            $token      = str_replace('/', '.', self::GenerateHash($ticket));

            /* Check if just exists for other user  */
            while ($DB->record_exists('enrol_approval_action',array('token' => $token))) {
                /* Ticket - Something long and Unique   */
                $ticket     = uniqid(mt_rand(),1);
                $ticket     = random_string() . $action . '_' . time() . '_' . $courseId . '_' . $ticket . random_string();
                $token      = str_replace('/', '.', self::GenerateHash($ticket));
            }//while

            return $token;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GenerateToken_Action

    /**
     * @param           $value
     *
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    24/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate a hash for sensitive values
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

    /**
     * @param           $courseId
     * @param           $courseName
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the name of the course
     */
    private static function AddNameCourse($courseId,$courseName) {
        /* Variables */
        $header = '';

        try {
            $url        = new moodle_url('/course/view.php',array('id' => $courseId));
            $lnkCourse  = '<a href="' . $url. '">' . $courseName . '</a>';

            $header .= html_writer::start_tag('label',array('class' => ' header_course'));
                $header .= $lnkCourse ;
            $header .= html_writer::end_tag('label');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddNameCourse

    /**
     * @param           $approvalRequests
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the basic information to the report
     */
    private static function AddBasicInfo($approvalRequests) {
        /* Variables */
        $content = '';

        try {
            /* Category */
            $content .= html_writer::start_div('block_basic_info approval_r0');
                $content .= html_writer::start_tag('label',array('class' => ' label_approval_course'));
                    $content .= get_string('category');
                $content .= html_writer::end_tag('label');
            $content .= html_writer::end_div();//block_basic_info

            $content .= html_writer::start_div('block_basic_info ');
                $content .= html_writer::start_tag('p',array('class' => ' approval_course_value'));
                    $content .= $approvalRequests->category;
                $content .= html_writer::end_tag('p');
            $content .= html_writer::end_div();//block_basic_info

            /* Participants */
            $content .= html_writer::start_div('block_basic_info approval_r0');
                $content .= html_writer::start_tag('label',array('class' => ' label_approval_course'));
                    $content .= get_string('rpt_participants','enrol_waitinglist');
                $content .= html_writer::end_tag('label');
            $content .= html_writer::end_div();//block_basic_info

            $content .= html_writer::start_div('block_basic_info ');
                $content .= html_writer::start_tag('p',array('class' => ' approval_course_value'));
                    $content .= $approvalRequests->participants;
                $content .= html_writer::end_tag('p');
            $content .= html_writer::end_div();//block_basic_info

            /* Attended */
            $content .= html_writer::start_div('block_basic_info approval_r0');
                $content .= html_writer::start_tag('label',array('class' => ' label_approval_course'));
                    $content .= get_string('rpt_attended','enrol_waitinglist');
                $content .= html_writer::end_tag('label');
            $content .= html_writer::end_div();//block_basic_info

            $content .= html_writer::start_div('block_basic_info ');
                $content .= html_writer::start_tag('p',array('class' => ' approval_course_value'));
                    $content .= $approvalRequests->attended;
                $content .= html_writer::end_tag('p');
            $content .= html_writer::end_div();//block_basic_info

            /* Approved     */
            $content .= html_writer::start_div('block_basic_info approval_r2');
                $content .= html_writer::start_tag('label',array('class' => ' label_approval_course to_right'));
                    $content .= get_string('rpt_approved','enrol_waitinglist');
                $content .= html_writer::end_tag('label');
            $content .= html_writer::end_div();//block_basic_info

            $content .= html_writer::start_div('block_basic_info ');
                $content .= html_writer::start_tag('p',array('class' => ' approval_course_value to_right'));
                    $content .= $approvalRequests->approved;
                $content .= html_writer::end_tag('p');
            $content .= html_writer::end_div();//block_basic_info

            /* Rejected     */
            $content .= html_writer::start_div('block_basic_info approval_r2');
                $content .= html_writer::start_tag('label',array('class' => ' label_approval_course to_right'));
                    $content .= get_string('rpt_rejected','enrol_waitinglist');
                $content .= html_writer::end_tag('label');
            $content .= html_writer::end_div();//block_basic_info

            $content .= html_writer::start_div('block_basic_info ');
                $content .= html_writer::start_tag('p',array('class' => ' approval_course_value to_right'));
                    $content .= $approvalRequests->rejected;
                $content .= html_writer::end_tag('p');
            $content .= html_writer::end_div();//block_basic_info

            /* No Attended */
            $content .= html_writer::start_div('block_basic_info approval_r0');
                $content .= html_writer::start_tag('label',array('class' => ' label_approval_course'));
                    $content .= get_string('rpt_not_attended','enrol_waitinglist');
                $content .= html_writer::end_tag('label');
            $content .= html_writer::end_div();//block_basic_info

            $content .= html_writer::start_div('block_basic_info ');
                $content .= html_writer::start_tag('p',array('class' => ' approval_course_value'));
                    $content .= $approvalRequests->noAttended;
                $content .= html_writer::end_tag('p');
            $content .= html_writer::end_div();//block_basic_info

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddBasicInfo

    /**
     * @return          string
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add header requests table
     */
    private static function AddHeader_RequestsTable() {
        /* Variables */
        $header         = '';
        $strName        = null;
        $strMail        = null;
        $strArguments   = null;
        $strSeats       = null;
        $strAction      = null;

        try {
            /* Headers */
            $strName        = get_string('rpt_name','enrol_waitinglist');
            $strMail        = get_string('rpt_mail','enrol_waitinglist');
            $strPlace       = get_string('rpt_workplace','enrol_waitinglist');
            $strArguments   = get_string('rpt_arguments','enrol_waitinglist');
            $strSeats       = get_string('rpt_seats','enrol_waitinglist');
            $strAction      = get_string('rpt_action','enrol_waitinglist');

            $header .=  html_writer::start_tag('thead');
                $header .=  html_writer::start_tag('tr',array('class' => 'header_approval'));
                    /* User Name    */
                    $header .= html_writer::start_tag('th',array('class' => 'user'));
                        $header .= $strName;
                    $header .= html_writer::end_tag('th');
                    /* Workplace    */
                    $header .= html_writer::start_tag('th',array('class' => 'user'));
                        $header .= $strPlace;
                    $header .= html_writer::end_tag('th');
                    /* Mail         */
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strMail;
                    $header .= html_writer::end_tag('th');
                    /* Arguments       */
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strArguments;
                    $header .= html_writer::end_tag('th');
                    /* Seats Confirmed  */
                    //$header .= html_writer::start_tag('th',array('class' => 'seats'));
                    //    $header .= $strSeats;
                    //$header .= html_writer::end_tag('th');
                    /* Action */
                    $header .= html_writer::start_tag('th',array('class' => 'action'));
                        $header .= $strAction;
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('thead');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_RequestsTable

    /**
     * @param           $approvalRequests
     * @param           $waitingId
     * @param           $courseId
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add content of the requests table
     */
    private static function AddContent_RequestTable($approvalRequests,$waitingId,$courseId) {
        /* Variables */
        $content        = '';
        $lnkUser        = null;
        $lnkAction      = null;
        $classAction    = null;
        $params         = null;

        try {
            /* Params Link Action */
            $params = array();
            $params['co'] = $courseId;
            $params['ea'] = $waitingId;

            foreach ($approvalRequests as $request) {
                $classAction    = null;
                $params['act']  = null;
                $content .= html_writer::start_tag('tr');
                    /* User Name    */
                    $content .= html_writer::start_tag('td',array('class' => 'user'));
                        $lnkUser = new moodle_url('/user/profile.php',array('id' => $request->user));
                        $content .= '<a href="' . $lnkUser . '">' . $request->name . '</a>';;
                    $content .= html_writer::end_tag('td');
                    /* Workplace    */
                    $content .= html_writer::start_tag('td',array('class' => 'user'));
                        $content .= $request->arbeidssted;
                    $content .= html_writer::end_tag('td');
                    /* Mail         */
                    $content .= html_writer::start_tag('td',array('class' => 'info'));
                        $content .= $request->email;
                    $content .= html_writer::end_tag('td');
                    /* Arguments       */
                    $content .= html_writer::start_tag('td',array('class' => 'info'));
                        $content .= $request->arguments;
                    $content .= html_writer::end_tag('td');
                    /* Seats Confirmed  */
                    /* Action */
                    $content .= html_writer::start_tag('td',array('class' => 'action'));
                        $params['id'] = $request->user;

                        /* Approve Action */
                        $params['act'] = APPROVED_ACTION;
                        $lnkAction = new moodle_url('/enrol/waitinglist/approval/act_request.php',$params);
                        if ($request->approved) {
                            $classAction = 'lnk_disabled';
                        }else {
                            $classAction = 'approved';
                        }//if_approved
                        $content .= html_writer::link($lnkAction,
                                                      get_string('act_approve','enrol_waitinglist'),
                                                      array('class'=>$classAction));
                        $content .= '&nbsp;&nbsp;';
                        /* Reject Action */
                        $params['act'] = REJECTED_ACTION;
                        $lnkAction = new moodle_url('/enrol/waitinglist/approval/act_request.php',$params);
                        if ($request->rejected) {
                            $classAction = 'lnk_disabled';
                        }else {
                            $classAction = 'rejected';
                        }//if_rejected
                        $content .= html_writer::link($lnkAction,
                                                      get_string('act_reject','enrol_waitinglist'),
                                                      array('class'=>$classAction));
                    $content .= html_writer::end_tag('td');
                $content .= html_writer::end_tag('tr');
            }//for_requests

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_RequestTable

    /**
     * @param           $approvalRequests
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the request to the report
     */
    private static function AddRequestsInfo($approvalRequests) {
        /* Variables */
        $content = null;

        try {
            $content .= html_writer::start_div('block_requests');
                /* Request Table */
                $content .= html_writer::start_tag('table',array('class' => 'generaltable'));
                    /* Header Table     */
                    $content .= self::AddHeader_RequestsTable();
                    $content .= '</br>';
                    /* Content Table    */
                    $content .= self::AddContent_RequestTable($approvalRequests->requests,$approvalRequests->waitingId,$approvalRequests->id);
                $content .= html_writer::end_tag('table');
            $content .= html_writer::end_div();//block_requests

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddRequestsInfo
}//Approval

