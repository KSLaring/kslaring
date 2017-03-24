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
define('APPROVAL_COMPANY_NO_DEMANDED',3);

Class Approval {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Add extra elements to the main form
     *
     * @param           $form       Moodle form
     *
     * @throws          Exception
     *
     * @creationDate    24/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function add_elements_form(&$form) {
        /* Variables */

        try {
            // Approval Info
            $form->addElement('html','<label class="approval_info">' . get_string('approval_info','enrol_waitinglist') . '</label>');

            // Arguments
            $form->addElement('textarea', 'arguments',get_string('arguments', 'enrol_waitinglist'),'cols=75 rows=5');
            $form->addRule('arguments',get_string('required'), 'required', null, 'server');

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_elements_form

    /**
     * Description
     * Get managers connected with user
     *
     * @param           int $userId     User id
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    28/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function get_managers($userId,$instance = null) {
        /* Variables    */
        $myManagers = null;
        $competence = null;

        try {
            // First it gets the competence connected with the user
            $competence = self::get_competence_user($userId,$instance);

            // Get Managers
            $myManagers = self::get_infoManagers_notification_approved($competence);

            return $myManagers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//get_managers

    /**
     * Description
     * Get all managers connected with a specific user and company
     *
     * @param           int $userId     Id user
     * @param           int $three      Id company. Level three
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    12/09/2016
     * @author          eFaktor     (fbv)
     */
    public static function managers_connected($userId,$three) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $params     = null;
        $managers   = array();
        $info       = null;

        try {
            // Search criteria
            $params = array();
            $params['user']     = $userId;
            $params['company']  = $three;

            // First level three
            $sql = self::get_sql_managers_company_user_by_level(3);
            $rdo = $DB->get_records_sql($sql,$params);
            if (!$rdo) {
                // Get level two
                $sql = self::get_sql_managers_company_user_by_level(2);
                $rdo = $DB->get_records_sql($sql,$params);
            }

            // Extract managers
            if ($rdo) {
                foreach ($rdo as $instance) {
                    if (array_key_exists($instance->managerid,$managers)) {
                        $info = $managers[$instance->managerid];
                    }else {
                        $info = new stdClass();
                        $info->id        = $instance->managerid;
                        $info->lang      = $instance->lang;
                        $info->companies = array();
                    }//if_manager_exists

                    // Add Company
                    $info->companies[] = $instance->company;

                    // Add Manager
                    $managers[$instance->managerid] = $info;
                }//for_rdo
            }//if_rdo

            return $managers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//managers_connected

    /**
     * Description
     * Check if the request has been rejected
     *
     * @param           int $userId     Id user
     * @param           int $courseId   Id course
     * @param           int $waitingId  Id enrol waiting list
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    30/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function is_rejected($userId,$courseId,$waitingId) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            // Search Criteria
            $params = array();
            $params['userid']           = $userId;
            $params['courseid']         = $courseId;
            $params['waitinglistid']    = $waitingId;
            $params['rejected']         = 1;

            // Execute
            $rdo = $DB->get_record('enrol_approval',$params);
            if ($rdo) {
                return $rdo->timemodified;
            }else {
                return false;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//is_rejected

    /**
     * Description
     * Create an entry for approval method
     *
     * @updateDate      13/09/2016
     * @auhtor          eFaktor     (fbv)
     *
     * Description
     * Add company
     *
     * @param           Object  $data       Data enrolment
     * @param           int     $userId     Id user
     * @param           int     $courseId   Id Course
     * @param           string  $method     Sub-Method enrol waitinglist
     * @param           int     $seats      Seats
     * @param           int     $waitingId  Id enrol waiting list
     *
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    28/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function add_approval_entry($data,$userId,$courseId,$method,$seats,$waitingId=0) {
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

        //Start Transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Approval Entry
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
            $infoApproval->token            = self::generate_token($userId,$courseId);
            $infoApproval->approved         = 0;
            $infoApproval->rejected         = 0;
            $infoApproval->onwait           = 0;
            $infoApproval->timecreated      = time();


            // Check if already exist
            $params = array();
            $params['userid']   = $userId;
            $params['courseid'] = $courseId;
            $rdo = $DB->get_record('enrol_approval',$params,'id');
            if ($rdo) {
                // Update
                $infoApproval->id = $rdo->id;
                $DB->update_record('enrol_approval',$infoApproval);
            }else {
                // Insert New
                $infoApproval->id = $DB->insert_record('enrol_approval',$infoApproval);
            }//if_rdo

            // Insert Approve Action
            $infoApproveAct = new stdClass();
            $infoApproveAct->approvalid = $infoApproval->id;
            $infoApproveAct->token      = self::generate_token_action($courseId,'approve');
            $infoApproveAct->action     = APPROVED_ACTION;
            $DB->insert_record('enrol_approval_action',$infoApproveAct);

            // Insert Reject Action
            $infoRejectAct = new stdClass();
            $infoRejectAct->approvalid  = $infoApproval->id;
            $infoRejectAct->token       = self::generate_token_action($courseId,'reject');
            $infoRejectAct->action      = REJECTED_ACTION;
            $DB->insert_record('enrol_approval_action',$infoRejectAct);

            // Info To Send
            $infoMail = self::info_notification_approved($userId,$courseId,$waitingId,$data->level_3);
            $infoMail->arguments    = $infoApproval->arguments;
            $infoMail->approvalid   = $infoApproval->id;
            // Approve Link
            $lnkApprove = $CFG->wwwroot . '/enrol/waitinglist/approval/action.php/' . $infoApproval->token . '/' . $infoApproveAct->token;
            $infoMail->approve = $lnkApprove;
            // Reject Link
            $lnkReject  = $CFG->wwwroot . '/enrol/waitinglist/approval/action.php/' . $infoApproval->token . '/' . $infoRejectAct->token;
            $infoMail->reject = $lnkReject;

            // Commit
            $trans->allow_commit();

            return array($infoApproval,$infoMail);
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//add_approval_entry


    /**
     * Description
     * Send notifications for all managers and the user.
     *
     * @param           Object  $user       user
     * @param           Object  $infoMail   Information to send
     * @param           array   $toManagers Manager who receive the email
     *
     * @throws          Exception
     *
     * @creationDate    28/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function send_notifications($user,&$infoMail,$toManagers) {
        /* Variables */
        global $SITE;
        $strBody    = null;
        $strSubject = null;
        $bodyText   = null;
        $bodyHtml   = null;

        try {
            // Extra Info
            $infoMail->user = fullname($user);
            $infoMail->site = $SITE->shortname;

            // Mail for Managers
            self::send_notification_managers($infoMail,$toManagers);

            // Mail for Users
            $strSubject = (string)new lang_string('mng_subject','enrol_waitinglist',$infoMail,$user->lang);
            $strBody    = (string)new lang_string('std_body','enrol_waitinglist',$infoMail,$user->lang);

            // Content Mail
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

            // Send Mail
            email_to_user($user, $SITE->shortname, $strSubject, $bodyText,$bodyHtml);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//send_notifications

    /**
     * Description
     * Get when notification was sent
     *
     * @param           int $userId     Id user
     * @param           int $courseId   Id course
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function get_notification_sent($userId,$courseId) {
        /* Variables    */
        global $DB,$CFG,$SITE;
        $rdo                = null;
        $sql                = null;
        $params             = null;
        $infoNotification   = null;
        $course             = null;

        try {
            // Info Notification
            $infoNotification = new stdClass();
            $infoNotification->site     = $SITE->shortname;
            // Add Info Course
            self::get_infocourse_notification($courseId,$infoNotification);

            // Search Criteria
            $params = array();
            $params['user']     = $userId;
            $params['course']   = $courseId;
            $params['approved'] = 0;
            $params['rejected'] = 0;
            $params['unenrol']  = 0;

            // SQL Instruction
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

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                // Info Notification
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
    }//get_notification_sent

    /**
     * Description
     * Send reminders to the manager
     *
     * @param           Object  $user                user
     * @param           Object  $infoNotification    information to send
     * @param           int     $instanceId          Instance id enrolment method
     * @param           array   $toManagers          Who receives the notification
     *
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function send_reminder($user,&$infoNotification,$instanceId,$toManagers) {
        /* Variables */
        global $SITE,$DB;
        $lnkApprove = null;
        $lnkReject  = null;
        $rdo        = null;
        $params     = null;
        $company    = null;

        try {
            // Extra Info
            $infoNotification->user = fullname($user);
            $infoNotification->site = $SITE->shortname;

            // Get company connected with user and enrolment method
            $rdo = $DB->get_record('enrol_approval',array('userid' => $user->id,'waitinglistid' => $instanceId));
            if ($rdo) {
                $company = $rdo->companyid;
            }

            // Get info notification
            self::get_infouser_notification_approved($user->id,$infoNotification,$instanceId,$company);

            /**
             * Add extra token to the action link
             */
            // Action Tokens
            $params = array();
            $params['approvalid']   = $infoNotification->approvalid;

            // Approve Token
            $params['action']       = APPROVED_ACTION;
            $rdo = $DB->get_record('enrol_approval_action',$params);
            $infoNotification->approve = $infoNotification->approve . '/' . $rdo->token;

            // Reject Token
            $params['action']       = REJECTED_ACTION;
            $rdo = $DB->get_record('enrol_approval_action',$params);
            $infoNotification->reject = $infoNotification->reject . '/' . $rdo->token;;

            // Send Mail
            self::send_notification_managers($infoNotification,$toManagers,true);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//send_reminder

    /**
     * Description
     * Get request connected with the notification
     *
     * @updateDate      13/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add company
     *
     * @param           $args
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function get_notification_request($args) {
        /* Variables */
        global $DB;
        $sql            = null;
        $rdo            = null;
        $params         = null;

        try {
            // Search Criteria
            $params = array();
            $params['approve']  = 0;
            $params['reject']   = 0;
            $params['unenrol']  = 0;
            $params['request']  = $args[0];
            $params['action']   = $args[1];

            // SQL Instruction
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

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_notification_request

    /**
     * Description
     * Get the request connected
     *
     * @updateDate      16/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add company id
     *
     * @param           int $userId     Id User
     * @param           int $courseId   Id course
     * @param           int $waitingId  Id enrol waiting list
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function get_request($userId,$courseId,$waitingId) {
        /* Variables */
        global $DB;
        $sql    = null;
        $params = null;
        $rdo    = null;

        try {
            // Search Criteria
            $params = array();
            $params['user']     = $userId;
            $params['course']   = $courseId;
            $params['waiting']  = $waitingId;

            // SQL Instruction
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
                     FROM	{enrol_approval}	ea
                     WHERE	ea.waitinglistid 	= :waiting
                        AND	ea.courseid 		= :course
                        AND ea.userid 			= :user ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_request

    /**
     * Description
     * Apply the action selected by the manager
     *
     * @param           Object $infoRequest     Request detail
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function apply_action_from_manager($infoRequest) {
        /* Variables */
        $instanceWaiting    = null;
        $exit               = true;

        try {
            // Check Action
            switch ($infoRequest->action) {
                case APPROVED_ACTION:
                    $exit =  self::approve_action($infoRequest);

                    break;
                case REJECTED_ACTION:
                    $exit =  self::reject_action($infoRequest);

                    break;
            }//switch_action

            return $exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//apply_action_from_manager

    /**
     * Description
     * Get all approval requests
     *
     * @param           int $courseId   Id course
     * @param           int $waitingId  Id enrol waiting list
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    30/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function approval_requests($courseId,$waitingId) {
        /* Variables */
        $approvalRequests   = null;
        $isCompanyDemanded  = null;
        
        try {
            // Get basic information for the course instance
            $approvalRequests = self::get_basic_info($courseId,$waitingId);

            // Approval Requests
            $isCompanyDemanded = self::is_company_demanded($waitingId);
            $approvalRequests->requests = self::get_approval_requests($courseId,$waitingId,$isCompanyDemanded);

            return $approvalRequests;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//approval_requests

    /**
     * Description
     * Get the requests report
     *
     * @param           Object $approvalRequests   Request to approve or approved
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     */
    public static function display_approval_requests($approvalRequests) {
        /* Variables */
        global $OUTPUT;
        $content    = '';
        $url        = null;
        $lnkCourse  = null;

        try {
            // Return course
            $url        = new moodle_url('/course/view.php',array('id' => $approvalRequests->id));

            $content .= html_writer::start_div('block_approval');
                // Name Course
                $content .= self::add_name_course($approvalRequests->id,$approvalRequests->name);

                // Basic Info && Requests
                if (!$approvalRequests->requests) {
                    $content .= html_writer::start_tag('label',array('class' => ' label_approval_course'));
                        $content .= get_string('no_request','enrol_waitinglist');
                    $content .= html_writer::end_tag('label');
                }else {
                    // Basic Info
                    $content .= self::add_basic_info($approvalRequests);

                    $content .= '</br>';
                    $content .= $OUTPUT->action_link($url,get_string('rpt_back','enrol_waitinglist'));

                    // Requests
                    $content .= self::add_requests_info($approvalRequests);
                }//if_requests

                // Return to the course
                $content .= $OUTPUT->action_link($url,get_string('rpt_back','enrol_waitinglist'));
            $content .= html_writer::end_div();//block_approval

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//display_approval_requests

    /**
     * Description
     * Get the information to send when a request has been approved
     *
     * @param           int $userId     Id user
     * @param           int $courseId   It course
     *
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    16/02/2016
     * @author          eFaktor     (fbv)
     */
    public static function info_notification_approved($userId,$courseId,$instanceId = 0,$company = 0) {
        /* Variables    */
        global $SITE;
        $infoNotification   = null;
        $course             = null;
        $managers           = null;
        $competenceUser     = null;

        try {
            // Info Notification
            $infoNotification = new stdClass();
            $infoNotification->site = $SITE->shortname;

            // Add Info Course
            self::get_infocourse_notification($courseId,$infoNotification);
            // Add Info User
            self::get_infouser_notification_approved($userId,$infoNotification,$instanceId,$company);

            return $infoNotification;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//info_notification_approved

    /**
     * Description
     * Get course information that has been approved
     *
     * @param           int     $courseId           Id course
     * @param           Object  $infoNotification   Notification
     *
     * @throws          Exception
     *
     * @creationDate    15/02/2016
     * @author          efaktor     (fbv)
     */
    public static function get_infocourse_notification($courseId,&$infoNotification) {
        /* Variables */
        global $DB;
        $sql        = null;
        $params     = null;
        $rdo        = null;
        $instructor = null;
        $urlHome    = null;

        try {
            // Search Criteria
            $params = array();
            $params['course']   = $courseId;
            $params['visible']  = 1;

            // SQL Isntruction
            $sql = " SELECT	        c.id,
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
                      WHERE	        c.id 		= :course
                        AND	        c.visible 	= :visible ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                // Course Info
                $infoNotification->course       = $rdo->fullname;
                $infoNotification->internal     = $rdo->priceinternal;
                $infoNotification->external     = $rdo->priceexternal;
                $infoNotification->summary      = $rdo->summary;
                if (($rdo->homepage) && ($rdo->homevisible)) {
                    // Course Home Page
                    $urlHome = new moodle_url('/local/course_page/home_page.php',array('id' => $courseId));
                }else {
                    // Course Page
                    $urlHome = new moodle_url('/course/view.php',array('id' => $courseId));
                }
                $infoNotification->homepage = '<a href="' . $urlHome . '">'. $rdo->fullname . '</a>';
                $infoNotification->date     = ($rdo->startdate ? userdate($rdo->startdate,'%d.%m.%Y', 99, false) : 'N/A');
                $infoNotification->location = $rdo->location;
                // Instructor
                if ($rdo->instructor) {
                    $instructor = $rdo->firstname . " " . $rdo->lastname . " (" . $rdo->email . ")";
                }//if_instructor
                $infoNotification->instructor = $instructor;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_infocourse_notification

    /**
     * Description
     * Send the notifications for approved request to all managers
     *
     * @param           Object $infoNotification    Notification
     *
     * @throws          Exception
     *
     * @creationDate    16/02/2016
     * @author          eFaktor     (fbv)
     */
    public static function send_approved_notification_managers($infoNotification) {
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
            // Get Managers
            $managers = $infoNotification->managers;

            // Subject
            $strSubject = get_string('mng_approved_subject','enrol_waitinglist',$infoNotification);

            // Send eMail to managers
            if ($managers) {
                foreach ($managers as $manager) {
                    $companies = '';

                    // Get Info Body eMail
                    $strBody = (string)new lang_string('mng_approved_body_two','enrol_waitinglist',$infoNotification,$manager->lang);

                    // Info Manager
                    $companies .= '<ul>';
                    foreach ($manager->companies as $info) {
                        $companies .= '<li>' . $info . '</li>';
                    }
                    $companies .= '</ul>';
                    $strBody .= (string)new lang_string('mng_approved_body_one','enrol_waitinglist',null,$manager->lang) . $companies . "</br>";

                    $strBody .= "</br>" . "</br>" . (string)new lang_string('mng_approved_body_end','enrol_waitinglist',$infoNotification,$manager->lang);

                    // Content Mail
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

                    // Send eMail
                    $user = get_complete_user_data('id',$manager->id);
                    email_to_user($user, $SITE->shortname, $strSubject, $bodyText,$bodyHtml);
                }//for_managers
            }//if_managers
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//send_approved_notification_managers

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * It builds sql to get all managers connected with a specific user and company
     * for a given level
     *
     * @creationDate    10/01/17
     * @author          eFaktor     (fbv)
     *
     * @param       int $level
     *
     * @return          null|string
     * @throws          Exception
     */
    private static function get_sql_managers_company_user_by_level($level) {
        /* Variables */
        $sql = null;

        try {
            switch ($level) {
                case 2:
                    $sql = " SELECT   DISTINCT 
                                          rm.managerid,
                                          CONCAT(co_two.name,'/',co.name) as 'company',
                                          u.lang
                             FROM		  {user_info_competence_data} 		uicd                       
                                JOIN	  {report_gen_companydata}			co 		ON  co.id 					= uicd.companyid
                                -- LEVEL TWO
                                JOIN	  {report_gen_company_relation}  	cr_two	ON 	cr_two.companyid 		= co.id
                                JOIN	  {report_gen_companydata}			co_two	ON 	co_two.id 				= cr_two.parentid
                                                                                    AND co_two.hierarchylevel 	= 2
                                -- CHECK MANAGER LEVEL THREE
                                JOIN	  {report_gen_company_manager}  	rm		ON 	rm.leveltwo 			= co_two.id
                                                                                    AND (rm.levelthree 			IS NULL
                                                                                         OR 
                                                                                         rm.levelthree 			= 0)
                                LEFT JOIN {report_gen_company_manager}      rmo		ON  rmo.managerid 			= rm.managerid 	
                                                                                    AND rmo.levelone 			= rm.levelone
                                                                                    AND (rmo.leveltwo			IS NULL
                                                                                         OR 
                                                                                         rmo.leveltwo 			= 0)
                                JOIN      {user}                          	u   	ON  u.id  					= rm.managerid
                             WHERE		  uicd.userid 	  = :user
                                  AND	  uicd.companyid  = :company
                                  AND 	  rmo.id IS NULL ";
                    break;

                case 3:
                    $sql = " SELECT  DISTINCT
                                          rm.managerid,
                                          CONCAT(co_two.name,'/',co.name) as 'company',
                                          u.lang,
                                          rm.leveltwo,
                                          rm.levelthree
                             FROM		  {user_info_competence_data} 	  uicd                       
                                JOIN	  {report_gen_companydata}		  co 		ON  co.id 					= uicd.companyid
                                -- LEVEL TWO
                                JOIN	  {report_gen_company_relation}   cr_two	ON 	cr_two.companyid 		= co.id
                                JOIN	  {report_gen_companydata}		  co_two	ON 	co_two.id 				= cr_two.parentid
                                                                                    AND co_two.hierarchylevel 	= 2
                                -- CHECK MANAGER LEVEL THREE
                                JOIN	  {report_gen_company_manager}    rm		ON 	rm.levelthree 			= uicd.companyid 
                                LEFT JOIN {report_gen_company_manager}    rmo		ON  rmo.managerid 			= rm.managerid 	
                                                                                    AND rmo.leveltwo 			= co_two.id
                                                                                    AND (rmo.levelthree 		IS NULL
                                                                                         OR 
                                                                                         rmo.levelthree 		= 0)
                                JOIN      {user}                        u   	    ON  u.id  					= rm.managerid 
                              WHERE		  uicd.userid 	  = :user
                                  AND	  uicd.companyid  = :company
                                  AND  	  rmo.id IS NULL ";

                    break;
                default:
                    $sql = null;
            }//switch

            return $sql;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_sql_managers_company_user_by_level

    /**
     * Description
     * Get the competence connected with the user
     *
     * @param           int     $userId     Id user
     * @param           Object  $instance
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    28/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_competence_user($userId,$instance = null) {
        /* Variables    */
        global $DB;
        $competence = null;
        $sql        = null;
        $rdo        = null;
        $params     = null;

        try {
            // Search Criteria
            $params = array();
            $params['user'] = $userId;

            // Competence
            $competence = new stdClass();
            $competence->levelZero  = 0;
            $competence->levelOne   = 0;
            $competence->levelTwo   = 0;
            $competence->levelThree = 0;

            // SQL Instruction
            if ($instance) {
                // Search criteria
                $params['waiting']  = $instance->id;
                $params['course']   = $instance->courseid;

                // Manager only connected with company from enrolment method
                $sql = " SELECT	    uicd.companyid 		as 'levelthree',
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
                $sql = " SELECT	    GROUP_CONCAT(DISTINCT uicd.companyid  	ORDER BY uicd.companyid SEPARATOR ',')		as 'levelthree',
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

            // Execute
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
    }//get_competence_user

    /**
     * Description
     * Get all managers that have to receive an approved notification
     *
     * @param           Object  $competence Competence data
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    15/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function get_infoManagers_notification_approved($competence) {
        /* Variables    */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $managers       = array();
        $infoManager    = null;
        $data           = false;

        try {
            // First Level Three
            $sql = self::get_sql_managers_company_by_hierarchy($competence->levelZero,$competence->levelOne,
                                                               $competence->levelTwo,$competence->levelThree,3);
            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                $data = true;
            }else {
                // Second Level
                $sql = self::get_sql_managers_company_by_hierarchy($competence->levelZero,$competence->levelOne,
                                                                   $competence->levelTwo,$competence->levelThree,2);
                // Execute
                $rdo = $DB->get_records_sql($sql);
                if ($rdo) {
                    $data = true;
                }
            }//if_rdo

            if ($data) {
                foreach ($rdo as $instance) {
                    if (array_key_exists($instance->managerid,$managers)) {
                       $infoManager             = $managers[$instance->managerid];
                    }else {
                        $infoManager = new stdClass();
                        $infoManager->id        = $instance->managerid;
                        $infoManager->lang      = $instance->lang;
                        $infoManager->companies = array();
                    }//if_manager_exists

                    // Add Company
                    $infoManager->companies[] = $instance->company;

                    // Add Manager
                    $managers[$instance->managerid] = $infoManager;
                }//of_rdo
            }//if_data

            return $managers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//get_infoManagers_notification_approved

    /**
     * Description
     * Managers y level of hierarchy
     *
     * @param           int $levelZero      Company level zero
     * @param           int $levelOne       Company level one
     * @param           int $levelTwo       Company level two
     * @param           int $levelThree     Company level three
     * @param           int $hierarchy      Hierarchy level
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    01/06/2016
     * @author          eFaktor     (fbv)
     */
    private static function get_sql_managers_company_by_hierarchy($levelZero,$levelOne,$levelTwo,$levelThree,$hierarchy) {
        /* Variables */
        $sql = null;
        
        try {
            switch ($hierarchy) {
                case 0:
                    $sql = " SELECT		rm.id,
                                        rm.managerid,
                                        co_zero.name as 'company',
                                        u.lang
                             FROM		{report_gen_company_manager}  rm
                                JOIN	{user}						  u 		ON 	u.id 		            = rm.managerid
                                                                                AND	u.deleted 	            = 0
                                -- LEVEL ZERO
                                JOIN 	{report_gen_companydata}	  co_zero	ON 	co_zero.id 				= rm.levelzero
                                                                                AND	co_zero.hierarchylevel 	= 0
                             WHERE	    (rm.levelzero IN ($levelZero) 
                                         AND 
                                         rm.levelone IS NULL 
                                         AND 
                                         rm.leveltwo IS NULL  
                                         AND 
                                         rm.levelthree IS NULL
                                        )
                             ORDER BY   rm.managerid ";

                    break;

                case 1:
                    $sql = " SELECT		rm.id,
                                        rm.managerid,
                                        CONCAT(co_zero.name,'/',co_one.name) as 'company',
                                        u.lang
                             FROM		{report_gen_company_manager}  rm
                                JOIN	{user}						  u 		ON 	u.id 		            = rm.managerid
                                                                                AND	u.deleted 	            = 0
                                -- LEVEL ZERO
                                JOIN 	{report_gen_companydata}	  co_zero	ON 	co_zero.id 				= rm.levelzero
                                                                                AND	co_zero.hierarchylevel 	= 0
                                -- LEVEL ONE
                                JOIN	{report_gen_companydata}	  co_one	ON	co_one.id				= rm.levelone
                                                                                AND	co_one.hierarchylevel	= 1
                             WHERE	    (rm.levelzero IN ($levelZero) 
                                         AND 
                                         rm.levelone IN ($levelOne) 
                                         AND 
                                         rm.leveltwo IS NULL  
                                         AND rm.levelthree IS NULL
                                        )
                             ORDER BY   rm.managerid ";

                    break;

                case 2:
                    $sql = " SELECT	DISTINCT	
                                          rm.id,
                                          rm.managerid,
                                          CONCAT(co_zero.name,'/',co_one.name,'/',co_two.name) as 'company',
                                          u.lang
                             FROM		  {report_gen_company_manager}  rm
                                JOIN	  {user}						u 		ON 	u.id 		            = rm.managerid
                                                                                AND	u.deleted 	            = 0
                                -- LEVEL ZERO
                                JOIN 	  {report_gen_companydata}	  	co_zero	ON 	co_zero.id 				= rm.levelzero
                                                                                AND	co_zero.hierarchylevel 	= 0
                                -- LEVEL ONE
                                JOIN	  {report_gen_companydata}	  	co_one	ON	co_one.id				= rm.levelone
                                                                                AND	co_one.hierarchylevel	= 1
                                -- LEVEL TWO
                                JOIN	  {report_gen_companydata}      co_two	ON	co_two.id				= rm.leveltwo
                                                                                AND co_two.hierarchylevel	= 2
                                LEFT JOIN {report_gen_company_manager}  rmo		ON  rmo.managerid 			= rm.managerid 	
                                                                                AND rmo.levelone 			= rm.levelone
                                                                                AND (rmo.leveltwo			IS NULL
                                                                                     OR 
                                                                                     rmo.leveltwo 			= 0)
                             WHERE	      (rm.levelzero IN ($levelZero) 
                                           AND 
                                           rm.levelone IN ($levelOne) 
                                           AND 
                                           rm.leveltwo IN ($levelTwo)  
                                           AND 
                                           rm.levelthree IS NULL
                                          )
                                  AND 	  rmo.id IS NULL
                             ORDER BY     rm.managerid ";

                    break;

                case 3:
                    $sql = " SELECT   DISTINCT
                                          rm.id,
                                          rm.managerid,
                                          CONCAT(co_zero.name,'/',co_one.name,'/',co_two.name,'/',co_tre.name) as 'company',
                                          u.lang
                             FROM		  {report_gen_company_manager}  rm
                                JOIN	  {user}						u 		ON 	u.id 		            = rm.managerid
                                                                                AND	u.deleted 	            = 0
                                -- LEVEL ZERO
                                JOIN 	  {report_gen_companydata}	    co_zero	ON 	co_zero.id 				= rm.levelzero
                                                                                AND	co_zero.hierarchylevel 	= 0
                                -- LEVEL ONE
                                JOIN	  {report_gen_companydata}	    co_one	ON	co_one.id				= rm.levelone
                                                                                AND	co_one.hierarchylevel	= 1
                                -- LEVEL TWO
                                JOIN	  {report_gen_companydata}      co_two	ON	co_two.id				= rm.leveltwo
                                                                                AND co_two.hierarchylevel	= 2
                                -- LEVEL THREE
                                JOIN	  {report_gen_companydata}	    co_tre  ON 	co_tre.id = rm.levelthree
                                                                                AND co_tre.hierarchylevel 	= 3
                             	LEFT JOIN {report_gen_company_manager}  rmo		ON  rmo.managerid 			= rm.managerid 	
														                        AND rmo.leveltwo 			= co_two.id
														                        AND (rmo.levelthree 		IS NULL
														                             OR 
															                        rmo.levelthree 		= 0)
                             WHERE	    (rm.levelzero IN ($levelZero) 
                                         AND 
                                         rm.levelone IN ($levelOne) 
                                         AND 
                                         rm.leveltwo IN ($levelTwo)  
                                         AND 
                                         rm.levelthree IN ($levelThree)
                                        )
                             ORDER BY   rm.managerid ";

                    break;
            }//hierarchy

            return $sql;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_sql_managers_company_by_hierarchy
    
    /**
     * Description
     * Get user information to send
     *
     * @param           int     $userId             Id user
     * @param           Object  $infoNotification   Notification
     *
     * @throws          Exception
     *
     * @creationDate    16/02/2016
     * @author          eFaktor     (fbv)
     */
    private static function get_infouser_notification_approved($userId,&$infoNotification,$instanceId = 0, $company = 0) {
        /* Variables    */
        global $DB;
        $sql        = null;
        $sqlWhere   = null;
        $params     = null;
        $rdo        = null;
        $companies  = null;

        try {
            // Search criteria
            $params = array();
            $params['user'] = $userId;

            // SQL Instruction
            $sql = " SELECT	  u.id,
                              CONCAT(u.firstname,' ',u.lastname) 								as 'user',
                              GROUP_CONCAT(DISTINCT co.name ORDER BY co.name SEPARATOR '#') 	as 'companies'
                     FROM	  {user} u
                        -- COMPETENCE
                        JOIN  {user_info_competence_data}	ucd		ON ucd.userid 	= u.id
                        JOIN  {report_gen_companydata}	    co 		ON co.id 		= ucd.companyid ";

            if ($company) {
                $params['company'] = $company;
                $sql .= " AND co.id = :company ";
            }
            $sqlWhere = " WHERE 	 u.id = :user
                          GROUP BY u.id ";
            if ($instanceId) {
                $params['waiting'] = $instanceId;

                $sql .= " LEFT JOIN	{enrol_waitinglist_queue}	wq	ON 	wq.userid		    = u.id
                                                                    AND wq.companyid 		= co.id
                                                                    AND wq.waitinglistid 	= :waiting ";
            }//if_instanceid

            // Execute
            $sql .= $sqlWhere;
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                // Companies
                $companies = explode('#',$rdo->companies);

                // Add Info User
                $infoNotification->user             = $rdo->user;
                $infoNotification->companies_user   = '<ul>';
                foreach ($companies as $company) {
                    $infoNotification->companies_user .= '<li>' . $company . '</li>';
                }
                $infoNotification->companies_user .= '</ul>';
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//get_infouser_notification_approved

    /**
     * Description
     * Send  notifications for the managers
     *
     * @param           Object  $infoMail       Mail content
     * @param           array   $toManagers     Who receive the email
     * @param           bool    $reminder       Remainder or not
     *
     * @throws          Exception
     *
     * @creationDate    28/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function send_notification_managers($infoMail,$toManagers,$reminder=false) {
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
            // Local Time
            $time = time();

            // Send Mail
            $lnkApprove = $infoMail->approve;
            $lnkReject  = $infoMail->reject;
            foreach ($toManagers as $managerId => $info) {
                // Approve and Reject links 
                $infoMail->approve = '<a href="' . $lnkApprove. '">' .
                                     (string)new lang_string('approve_lnk','enrol_waitinglist',null,$info->lang) . '</br>';
                $infoMail->reject = '<a href="' . $lnkReject . '">' .
                                     (string)new lang_string('reject_lnk','enrol_waitinglist',null,$info->lang) . '</br>';

                // Mails For Managers
                if ($reminder) {
                    $strSubject = (string)new lang_string('subject_reminder','enrol_waitinglist',$infoMail,$info->lang);
                    $strBody    = (string)new lang_string('body_reminder','enrol_waitinglist',$infoMail,$info->lang);
                }else {
                    $strSubject = (string)new lang_string('mng_subject','enrol_waitinglist',$infoMail,$info->lang);
                    $strBody    = (string)new lang_string('mng_body','enrol_waitinglist',$infoMail,$info->lang);
                }//if_remainder
                
                // Mail content
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

                $manager = get_complete_user_data('id',$managerId);
                if (email_to_user($manager, $SITE->shortname, $strSubject, $bodyText,$bodyHtml)) {
                    // Notification Sent
                    $sent = true;
                }//send_mail
            }//for_Each_manager

            // Update Approval Entry as Sent
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
    }//send_notification_managers

    /**
     * Description
     * Reject action
     *
     * @param           Object $infoRequest     Request to reject
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function reject_action($infoRequest) {
        /* Variables */
        global $DB,$SITE;
        $rdo            = null;
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
            // Local Time
            $time = time();

            // Instance Reject
            $instanceReject = new stdClass();
            $instanceReject->id             = $infoRequest->id;
            $instanceReject->userid         = $infoRequest->userid;
            $instanceReject->courseid       = $infoRequest->courseid;
            $instanceReject->waitinglistid  = $infoRequest->waitinglistid;
            $instanceReject->methodtype     = $infoRequest->methodtype;
            $instanceReject->approved       = 0;
            $instanceReject->rejected       = 1;
            $instanceReject->timemodified   = $time;

            // Execute
            $DB->update_record('enrol_approval',$instanceReject);

            // Send mail to the user
            $user   = get_complete_user_data('id',$infoRequest->userid);

            $rdo = $DB->get_records('enrol', array('courseid'=>$infoRequest->courseid, 'enrol'=>'waitinglist'), 'id ASC');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $plugin = enrol_get_plugin('waitinglist');
                    $plugin->unenrol_user($instance,$infoRequest->userid);
                }//for_rdo
            }//if_Rdo

            // content Mail
            $infoMail = new stdClass();
            $infoMail->user     = fullname($user);
            $infoMail->site     = $SITE->shortname;
            $infoMail->sent     = userdate($time,'%d.%m.%Y', 99, false);
            self::get_infocourse_notification($infoRequest->courseid,$infoMail);

            // Mail for Users
            $strSubject = (string)new lang_string('mng_subject','enrol_waitinglist',$infoMail,$user->lang);
            $strBody    = (string)new lang_string('request_rejected','enrol_waitinglist',$infoMail,$user->lang);

            // Content Mail
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

            // Delete entries from user_enrolments and enrol_waitinglist_queue
            $DB->delete_records('user_enrolments',array('userid'    => $infoRequest->userid,
                                                        'enrolid'   => $infoRequest->waitinglistid));
            $DB->delete_records('enrol_waitinglist_queue',array('userid'        => $infoRequest->userid,
                                                                'courseid'      => $infoRequest->courseid,
                                                                'waitinglistid' => $infoRequest->waitinglistid));
            // Send Mail
            email_to_user($user, $SITE->shortname, $strSubject, $bodyText,$bodyHtml);

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//reject_action

    /**
     * Description
     * Approve Action
     *
     * @param           Object $infoRequest     Request to approve
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function approve_action($infoRequest) {
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
            // Include library to create queue entry and update waitinglist
            require_once($CFG->dirroot . '/enrol/invoice/invoicelib.php');

            // Enrol instance
            $instanceWaiting = self::get_instance_enrolwaiting($infoRequest->courseid,$infoRequest->waitinglistid);

            // Waiting List ENTRY
            $queueEntry = self::add_entry_waitinglist($instanceWaiting,$infoRequest);

            if ($queueEntry) {
                // Activate Invoice Entry
                if (enrol_get_plugin('invoice')) {
                    if ($instanceWaiting->{WAITINGLIST_FIELD_INVOICE}) {
                        Invoices::activate_enrol_invoice($infoRequest->userid,$infoRequest->courseid,$instanceWaiting->id);
                    }//if_invoice_info
                }//if

                // Local Time
                $time = time();

                // Enrol instance
                $params = array();
                $params['userid']   = $infoRequest->userid;
                $params['enrolid']  = $infoRequest->waitinglistid;
                $rdo = $DB->get_record('user_enrolments',$params,'id');

                // Instance Approve
                $instanceApprove = new stdClass();
                $instanceApprove->id             = $infoRequest->id;
                $instanceApprove->userid         = $infoRequest->userid;
                $instanceApprove->courseid       = $infoRequest->courseid;
                if ($rdo) {
                    $instanceApprove->userenrolid    = $rdo->id;
                }//if_Rdo
                $instanceApprove->waitinglistid  = $infoRequest->waitinglistid;
                $instanceApprove->methodtype     = $infoRequest->methodtype;
                $instanceApprove->approved       = 1;
                $instanceApprove->rejected       = 0;
                $instanceApprove->timemodified   = $time;

                // Execute
                $DB->update_record('enrol_approval',$instanceApprove);

                //Send mail to the user
                $user   = get_complete_user_data('id',$infoRequest->userid);

                // Content email
                $infoMail = new stdClass();
                $infoMail->user         = fullname($user);
                $infoMail->site         = $SITE->shortname;
                $infoMail->sent         = userdate($time,'%d.%m.%Y', 99, false);
                self::get_infocourse_notification($infoRequest->courseid,$infoMail);

                // Mail for Users
                $strSubject = (string)new lang_string('mng_subject','enrol_waitinglist',$infoMail,$user->lang);
                $strBody    = (string)new lang_string('request_approved','enrol_waitinglist',$infoMail,$user->lang);

                // Content Mail
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

                // Send Mail
                email_to_user($user, $SITE->shortname, $strSubject, $bodyText,$bodyHtml);

                return true;
            }else {
                return false;
            }//if_else

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//approve_action

    /**
     * Description
     * Add the waiting list entry.
     *
     * @updateDate      13/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add company
     *
     * @param           Object $instanceWaiting     Enrol waitinglist instance
     * @param           Object $infoRequest         Request
     *
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_entry_waitinglist($instanceWaiting,$infoRequest) {
        /* Variables */
        $queueEntry = null;
        $method     = null;
        $class      = null;
        $myClass    = null;

        try {
            // Queue Entry
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

            // Method
            $method = $infoRequest->methodtype;
            // Get Class Method
            $class = '\enrol_waitinglist\method\\' . $method . '\enrolmethod'  . $method ;

            $methods = array();
            if (class_exists($class)){
                $themethod = $class::get_by_course($infoRequest->courseid, $infoRequest->waitinglistid);
                if($themethod){$methods[$method]=$themethod;}

                $queueEntry->id = $methods[$method]->add_to_waitinglist_from_approval($instanceWaiting,$queueEntry);
            }//if_class_exists

            return $queueEntry;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_entry_waitinglist

    /**
     * Description
     * Get waitinglist instance connected with the course
     *
     * @param           int $courseId   Id course
     * @param           int $waitingId  Id waitinglist
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    29/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_instance_enrolwaiting($courseId,$waitingId) {
        /* Variables */
        global $DB;
        $params = null;
        $rdo    = null;

        try {
            // Search Criteria
            $params = array();
            $params['courseid'] = $courseId;
            $params['id']       = $waitingId;
            $params['enrol']    = 'waitinglist';
            $params['status']   = 0;

            // Execute
            $rdo = $DB->get_record('enrol',$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_instance_enrolwaiting

    /**
     * Description
     * Get basic information about the course, name, participants...
     *
     * @param           int $courseId   Id course
     * @param           int $waitingId  Id enrolment waiting list
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    30/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_basic_info($courseId,$waitingId) {
        /* Variables    */
        global $DB;
        $approvalRequests   = null;
        $params             = null;
        $rdo                = null;
        $sql                = null;

        try {
            // Search Criteria
            $params = array();
            $params['course']   = $courseId;
            $params['enrol']    = 'waitinglist';
            $params['status']   = 0;
            $params['waiting']  = $waitingId;

            // SQL Instruction
            $sql = " SELECT	    c.id,
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
                     WHERE        c.id = :course ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                // Basic info
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
    }//get_basic_info

    /**
     * Description
     * Check id the company is demanded or not
     *
     * @param           int $enrolId    Id enrolment instance
     *
     * @return          bool|null
     * @throws          Exception
     *
     * @creationDate    26/10/2016
     * @author          eFaktor     (fbv)
     */
    private static function is_company_demanded($enrolId) {
        /* Variables */
        global $DB;
        $rdo = null;
        $isCompanyDemanded = null;

        try {
            $rdo = $DB->get_record('enrol',array('id' => $enrolId),'customint7');
            if ($rdo) {
                if ($rdo->customint7 != APPROVAL_COMPANY_NO_DEMANDED) {
                    $isCompanyDemanded = true;
                }//if_custom
            }//if_rdo

            return $isCompanyDemanded;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//is_company_demanded

    /**
     * Description
     * Get workplace connected with user. Competence profile
     *
     * @param           int $userId     Id user
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    01/11/2016
     * @author          eFaktor     (fbv)
     */
    private static function get_workplace_connected($userId) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        $workplace  = null;

        try {
            // Search criteria
            $params =array();
            $params['user_id']  = $userId;
            $params['level']    = 3;

            // SQL Instruction
            $sql = " SELECT   uic.userid,
                              GROUP_CONCAT(DISTINCT CONCAT(co.industrycode, ' - ',co.name) 
                                          ORDER BY co.industrycode,co.name SEPARATOR '#SE#') 	as 'workplace'
                     FROM	  {user_info_competence_data}	uic
                        JOIN  {report_gen_companydata}	co	ON co.id = uic.companyid
                     WHERE	  uic.userid = :user_id
                        AND uic.level  = :level ";

            // Execute
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
    }//get_workplace_connected

    /**
     * Description
     * Get all approval requests
     *
     * @updateDate      01/11/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get correct workplace
     *
     * @param           int  $courseId              Id course
     * @param           int  $waitingId             Id enrolment
     * @param           bool $isCompanyDemanded     Company demanded
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    30/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_approval_requests($courseId,$waitingId,$isCompanyDemanded) {
        /* Variables */
        global $DB;
        $params         = null;
        $rdo            = null;
        $ql             = null;
        $infoRequest    = null;
        $requests       = array();

        try {
            // Search Criteria
            $params = array();
            $params['course']   = $courseId;
            $params['waiting']  = $waitingId;

            // SQL Instruction
            $sql = " SELECT	      u.id,
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
                     WHERE	      ea.courseid 		= :course
                        AND       ea.waitinglistid 	= :waiting
                        AND       ea.unenrol        = 0
                     ORDER BY     u.firstname,u.lastname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Info Request
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
                            $infoRequest->arbeidssted = self::get_workplace_connected($instance->id);
                        }
                    }//if_comapnyDemanded

                    // Add Request
                    $requests[$instance->id] = $infoRequest;
                }//foreach_rdo
            }//if_rdo

            return $requests;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_approval_requests

    /**
     * Description
     * Generate token
     *
     * @param           int $userId     Id user
     * @param           int $courseId   Id course
     *
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    24/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function generate_token($userId,$courseId) {
        /* Variables    */
        global $DB;
        $ticket = null;
        $token  = null;

        try {
            // Ticket - Something long and Unique
            $ticket     = uniqid(mt_rand(),1);
            $ticket     = random_string() . $userId . '_' . time() . '_' . $courseId . '_' . $ticket . random_string();
            $token      = str_replace('/', '.', self::generate_hash($ticket));

            // Check if just exists for other user
            while ($DB->record_exists('enrol_approval',array('userid' => $userId,'token' => $token))) {
                // Ticket - Something long and Unique
                $ticket     = uniqid(mt_rand(),1);
                $ticket     = random_string() . $userId . '_' . time() . '_' . $courseId . '_' . $ticket . random_string();
                $token      = str_replace('/', '.', self::generate_hash($ticket));
            }//while

            return $token;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//generate_token

    /**
     * Description
     * Generate the token connected with the action
     *
     * @param           int $courseId   Id course
     * @param               $action
     *
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    24/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function generate_token_action($courseId,$action) {
        /* Variables    */
        global $DB;
        $ticket     = null;
        $token      = null;

        try {
            // Ticket - Something long and Unique
            $ticket     = uniqid(mt_rand(),1);
            $ticket     = random_string() . $action . '_' . time() . '_' . $courseId . '_' . $ticket . random_string();
            $token      = str_replace('/', '.', self::generate_hash($ticket));

            // Check if just exists for other user
            while ($DB->record_exists('enrol_approval_action',array('token' => $token))) {
                // Ticket - Something long and Unique
                $ticket     = uniqid(mt_rand(),1);
                $ticket     = random_string() . $action . '_' . time() . '_' . $courseId . '_' . $ticket . random_string();
                $token      = str_replace('/', '.', self::generate_hash($ticket));
            }//while

            return $token;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//generate_token_action

    /**
     * Description
     * Generate a hash for sensitive values
     *
     * @param           $value
     *
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    24/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function generate_hash($value) {
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
            // Generate hash
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
    }//generate_hash

    /**
     * Description
     * Add the name of the course
     *
     * @param           int     $courseId       Id course
     * @param           string  $courseName     Course name
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_name_course($courseId,$courseName) {
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
    }//add_name_course

    /**
     * Description
     * Add the basic information to the report
     *
     * @param           Object  $approvalRequests   Info request
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_basic_info($approvalRequests) {
        /* Variables */
        $content = '';

        try {
            // Category
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

            // Participants
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

            // Attended
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

            // Approved
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

            // Rejected
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

            // No Attended
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
    }//add_basic_info

    /**
     * Description
     * Add header requests table
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_header_requests_table() {
        /* Variables */
        $header         = '';
        $strName        = null;
        $strMail        = null;
        $strArguments   = null;
        $strAction      = null;

        try {
            // Headers
            $strName        = get_string('rpt_name','enrol_waitinglist');
            $strMail        = get_string('rpt_mail','enrol_waitinglist');
            $strPlace       = get_string('rpt_workplace','enrol_waitinglist');
            $strArguments   = get_string('rpt_arguments','enrol_waitinglist');
            $strAction      = get_string('rpt_action','enrol_waitinglist');

            $header .=  html_writer::start_tag('thead');
                $header .=  html_writer::start_tag('tr',array('class' => 'header_approval'));
                    // User Name
                    $header .= html_writer::start_tag('th',array('class' => 'user'));
                        $header .= $strName;
                    $header .= html_writer::end_tag('th');
                    // Workplace
                    $header .= html_writer::start_tag('th',array('class' => 'user'));
                        $header .= $strPlace;
                    $header .= html_writer::end_tag('th');
                    // Mail
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strMail;
                    $header .= html_writer::end_tag('th');
                    // Arguments
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strArguments;
                    $header .= html_writer::end_tag('th');
                    // Action
                    $header .= html_writer::start_tag('th',array('class' => 'action'));
                        $header .= $strAction;
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('thead');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_requests_table

    /**
     * Description
     * Add content of the requests table
     *
     * @param           array  $approvalRequests   List of  request
     * @param           int    $waitingId          Id enrolment instance
     * @param           int    $courseId           Id course
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_content_request_table($approvalRequests,$waitingId,$courseId) {
        /* Variables */
        $content        = '';
        $lnkUser        = null;
        $lnkAction      = null;
        $classAction    = null;
        $params         = null;

        try {
            // Headers
            $strName        = get_string('rpt_name','enrol_waitinglist');
            $strMail        = get_string('rpt_mail','enrol_waitinglist');
            $strPlace       = get_string('rpt_workplace','enrol_waitinglist');
            $strArguments   = get_string('rpt_arguments','enrol_waitinglist');
            $strAction      = get_string('rpt_action','enrol_waitinglist');

            // Params Link Action
            $params = array();
            $params['co'] = $courseId;
            $params['ea'] = $waitingId;

            // Request
            foreach ($approvalRequests as $request) {
                $classAction    = null;
                $params['act']  = null;
                $content .= html_writer::start_tag('tr');
                    // User Name
                    $content .= html_writer::start_tag('td',array('class' => 'user','data-th' => $strName));
                        $lnkUser = new moodle_url('/user/profile.php',array('id' => $request->user));
                        $content .= '<a href="' . $lnkUser . '">' . $request->name . '</a>';;
                    $content .= html_writer::end_tag('td');
                    // Workplace
                    $content .= html_writer::start_tag('td',array('class' => 'user','data-th' => $strPlace));
                        $content .= $request->arbeidssted;
                    $content .= html_writer::end_tag('td');
                    // Mail
                    $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strMail));
                        $content .= $request->email;
                    $content .= html_writer::end_tag('td');
                    // Arguments
                    $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strArguments));
                        $content .= $request->arguments;
                    $content .= html_writer::end_tag('td');
                    // Action
                    $content .= html_writer::start_tag('td',array('class' => 'action','data-th' => $strAction));
                        // Approve Action
                        $params['id'] = $request->user;
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

                        // Reject Action
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
    }//add_content_request_table

    /**
     * Description
     * Add the request to the report
     *
     * @param           Object $approvalRequests     List of request
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    31/12/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_requests_info($approvalRequests) {
        /* Variables */
        $content = null;

        try {
            $content .= html_writer::start_div('block_requests');
                /* Request Table */
                $content .= html_writer::start_tag('table',array('class' => 'generaltable'));
                    /* Header Table     */
                    $content .= self::add_header_requests_table();
                    $content .= '</br>';
                    /* Content Table    */
                    $content .= self::add_content_request_table($approvalRequests->requests,$approvalRequests->waitingId,$approvalRequests->id);
                $content .= html_writer::end_tag('table');
            $content .= html_writer::end_div();//block_requests

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_requests_info
}//Approval

