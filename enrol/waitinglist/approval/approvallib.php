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
    public static function GetManagers($userId) {
        /* Variables    */
        $myManagers = null;
        $competence = null;

        try {
            /* First it gets the competence connected with the user */
            $competence = self::GetCompetence_User($userId);

            /* Get Managers */
            $myManagers = self::GetManager_User($competence);

            return $myManagers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetManagers

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
     */
    public static function Add_ApprovalEntry($data,$userId,$courseId,$method,$seats,$waitingId=0) {
        /* Variables */
        global $DB,$CFG;
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

            /* Execute  */
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

            /* Insert OnWait Action  */

            /* Info To Send */
            $course = get_course($courseId);
            $infoMail = new stdClass();
            $infoMail->approvalid   = $infoApproval->id;
            $infoMail->course       = $course->fullname;

            $infoMail->arguments    = $infoApproval->arguments;
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
        global $DB,$CFG;
        $rdo                = null;
        $sql                = null;
        $params             = null;
        $infoNotification   = null;
        $course             = null;

        try {
            /* Info Course */
            $course = get_course($courseId);

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
                $infoNotification = new stdClass();
                $infoNotification->approvalid   = $rdo->id;
                $infoNotification->course       = $course->fullname;
                $infoNotification->arguments    = $rdo->arguments;
                $infoNotification->timesent     = userdate($rdo->timesent,'%d.%m.%Y', 99, false);
                $infoNotification->user         = null;
                $infoNotification->site         = null;
                $infoNotification->approve      = $CFG->wwwroot . '/enrol/waitinglist/approval/action.php/' . $rdo->token;
                $infoNotification->reject       = $CFG->wwwroot . '/enrol/waitinglist/approval/action.php/' . $rdo->token;
            }//if_rdo

            return $infoNotification;
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
    private static function GetCompetence_User($userId) {
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

            /* SQL Instruction */
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

        try {
            /* Get levels of Managers   */
            $sql = " SELECT		u.id
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
                             rm.leveltwo IN ($competence->levelTwo) AND rm.levelthree IS NULL)
                            OR
                            (rm.levelzero IN ($competence->levelZero) AND  rm.levelone IN ($competence->levelOne)
                             AND
                             rm.leveltwo IN ($competence->levelTwo) AND rm.levelthree IN ($competence->levelThree)) ";

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
            foreach ($toManagers as $managerId) {
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
            $instanceReject->rejected       = 1;
            $instanceReject->timemodified   = $time;

            /* Execute */
            $DB->update_record('enrol_approval',$instanceReject);

            /* Send mail to the user */
            $user   = get_complete_user_data('id',$infoRequest->userid);
            $course = get_course($infoRequest->courseid);

            $infoMail = new stdClass();
            $infoMail->user     = fullname($user);
            $infoMail->course   = $course->fullname;
            $infoMail->site     = $SITE->shortname;
            $infoMail->sent     = userdate($time,'%d.%m.%Y', 99, false);

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
                $instanceApprove->timemodified   = $time;

                /* Execute */
                $DB->update_record('enrol_approval',$instanceApprove);

                /* Send mail to the user */
                $user   = get_complete_user_data('id',$infoRequest->userid);
                $course = get_course($infoRequest->courseid);

                $infoMail = new stdClass();
                $infoMail->user     = fullname($user);
                $infoMail->course   = $course->fullname;
                $infoMail->site     = $SITE->shortname;
                $infoMail->sent     = userdate($time,'%d.%m.%Y', 99, false);

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
}//Approval

