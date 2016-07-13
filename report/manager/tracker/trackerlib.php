<?php
/**
 * Report Competence Manager  - Library code for Tracker.
 *
 * @package         report
 * @subpackage      manager/tracker
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    01/04/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Tracker Library
 *
 */
define('PAGE_TRACKER', 'tracker');
define('TRACKER_FORMAT', 'tracker_index');
define('TRACKER_PDF_DOWNLOAD', 'downloadpdf');
define('TRACKER_PDF_SEND', 'sendpdf');

class TrackerManager {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    /**
     * @param           $user_id
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the tracker connected with the user
     *
     * Tracker User
     *          --> id
     *          --> name
     *          --> competence.     Array
     *                                  --> levelThree
     *                                  --> name
     *                                  --> industrycode
     *                                  --> job_roles
     *                                  --> outcomes.       Array
     *                                                          [id]
     *                                                              --> name
     *                                                              --> expiration
     *                                                              --> courses
     *                                                              --> roles.          Array
     *                                                                     [id]    --> name
     *                                                              --> completed.      Array
     *                                                                                      --> id
     *                                                                                      --> name
     *                                                                                      --> completed
     *                                                              --> not_completed.  Array
     *                                                                                      --> id
     *                                                                                      --> name
     *                                                                                      --> completed
     *                                                              --> not_enrol.      Array
     *                                                                                      --> id
     *                                                                                      --> name
     *          --> completed.      Array
     *                                  [id]
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     *          --> not_completed.  Array
     *                                  [id]
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     */
    public static function GetUserTracker($user_id) {
        /* Variables    */
        global $DB;
        $userTracker        = null;

        try {
            /* Get Info User        */
            $rdo = $DB->get_record('user',array('id' => $user_id),'firstname,lastname');

            /* Info Tracker User    */
            $userTracker = new stdClass();
            $userTracker->id            = $user_id;
            $userTracker->name          = $rdo->firstname . ' ' . $rdo->lastname;
            $userTracker->competence    = self::Get_CompetenceTracker($user_id);

            /* Get the outcome tracker  */
            if ($userTracker->competence) {
                foreach ($userTracker->competence as $competence) {
                    self::GetInfoOutcomeTracker($user_id,$competence);
                }//for_each_competence_levelThree
            }//if_competence

            /* Get Tracker course not connected */
            list($userTracker->completed,$userTracker->not_completed) = self::GetTrackerNotConnected($user_id,$userTracker->competence);

            return $userTracker;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUserTracker

    /**
     * @param           $courseId
     * @param           $userId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unenrol user from the course
     */
    public static function Unenrol_FromCourse($courseId,$userId) {
        /* Variables    */
        global $DB;
        $trans  = null;
        $sql    = null;
        $params = null;
        $rdo    = null;
        $plugin = null;
        $exit   = true;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Get Instances    */
            /* Search Criteria  */
            $params = array();
            $params['course'] = $courseId;
            $params['user']   = $userId;

            /* Sql Instruction  */
            $sql = " SELECT		DISTINCT 	e.id,
                                            e.enrol,
                                            e.courseid
                     FROM		{enrol}				e
                        JOIN	{user_enrolments}	ue	ON 	ue.enrolid 	= e.id
                                                        AND	ue.status	= 0
                                                        AND	ue.userid	= :user
                     WHERE		e.courseid = :course
                        AND		e.status   = 0 ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach($rdo as $instance) {
                    $plugin     = enrol_get_plugin($instance->enrol);
                    $plugin->unenrol_user($instance,$userId);
                }

                $exit = true;
            }else {
                $exit = false;
            }//if_rdo

            /* Commit   */
            $trans->allow_commit();

            return $exit;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Unenrol_FromCourse

    /**
     * @param           $trackerUser
     * @return          string
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the tracker - Screen Format
     *
     *      * Tracker User
     *          --> id
     *          --> name
     *          --> competence.     Array
     *                                  --> levelThree
     *                                  --> name
     *                                  --> industrycode
     *                                  --> job_roles
     *                                  --> outcomes.       Array
     *                                                          [id]
     *                                                              --> name
     *                                                              --> expiration
     *                                                              --> courses
     *                                                              --> roles.          Array
     *                                                                      [id] --> name
     *                                                              --> completed.      Array
     *                                                                                      --> id
     *                                                                                      --> name
     *                                                                                      --> completed
     *                                                              --> not_completed.  Array
     *                                                                                      --> id
     *                                                                                      --> name
     *                                                                                      --> completed
     *                                                              --> not_enrol.      Array
     *                                                                                      --> id
     *                                                                                      --> name
     *          --> completed.      Array
     *                                  [id]
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     *          --> not_completed.  Array
     *                                  [id]
     *                                      --> id
     *                                      --> name
     *                                      --> completed
     */
    public static function Print_TrackerInfo($trackerUser) {
        /* Variables    */
        $out_tracker = '';

        try {
            /* Buttons - Download Report    */
            $out_tracker .= self::Get_OutputButtons();

            /* Print Outcome Tracker        */
            $out_tracker .= self::Print_OutcomeTracker($trackerUser->competence);

            /* Print Individual Tracker     */
            $out_tracker .= self::Print_IndividualTracker($trackerUser->completed,$trackerUser->not_completed);

            return $out_tracker;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_TrackerInfo

    /**
     * @param           $tracker_competence
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the tracker connected to the outcomes
     *
     * Tracker Competence
     *
     *          --> levelThree
     *          --> name
     *          --> industrycode
     *          --> job_roles
     *          --> outcomes.       Array
     *                                  [id]
     *                                      --> name
     *                                      --> expiration
     *                                      --> courses
     *                                      --> completed.      Array
     *                                                              --> id
     *                                                              --> name
     *                                                              --> completed
     *                                      --> not_completed.  Array
     *                                                              --> id
     *                                                              --> name
     *                                                              --> completed
     *                                      --> not_enrol.      Array
     *                                                              --> id
     *                                                              --> name
     */
    public static function Print_OutcomeTracker($tracker_competence) {
        /* Variables    */
        $out_tracker        = '';
        $outcomeToogle      = null;
        $companyToggle      = null;
        $url_img            = new moodle_url('/pix/t/expanded.png');
        $title              = null;

        try {
            foreach ($tracker_competence as $competence) {
                /* Header Company   */
                $companyToggle = 'YUI_' . $competence->levelThree;
                /* Company Name */
                $out_tracker .= html_writer::start_tag('div',array('class' => 'header_tracker'));
                    $out_tracker .= '<h5>'. $competence->name . '</h5>';
                $out_tracker .= html_writer::end_tag('div');

                /* Job Roles    */
                $out_tracker .= html_writer::start_tag('div',array('class' => 'header_tracker_jr'));
                    $out_tracker .= '<h6>' . self::Get_JobRolesNames($competence->job_roles) . '</h6>';
                $out_tracker .= html_writer::end_tag('div');

                /* Tracker Info */
                /* Add Outcome Tracker Info */
                if ($competence->outcomes) {
                    foreach ($competence->outcomes as $id=>$outcome) {
                        /* Tracker Info */
                        $outcomeToogle = $companyToggle . '_' . $id;
                        $out_tracker .= html_writer::start_tag('div',array('class' => 'tracker_list'));
                            $out_tracker .= html_writer::start_tag('div',array('class' => 'header_outcome_tracker'));
                                $out_tracker .= self::PrintHeader_OutcomeTracker($outcome->name,$outcomeToogle,$url_img);
                            $out_tracker .= html_writer::end_tag('div');//header_outcome_tracker

                            $out_tracker .= html_writer::start_tag('div',array('class' => 'course_list','id' => $outcomeToogle . '_div'));
                                /* Header Table     */
                                $outcomeToogle .= '_table';
                                $out_tracker .= self::AddHeader_CoursesTable($outcomeToogle,$url_img,false);
                                /* Content Table    */
                                $out_tracker .= self::AddContent_CoursesTable($outcome);
                            $out_tracker .= html_writer::end_tag('div');//course_list
                        $out_tracker .= html_writer::end_tag('div');//tracker_list
                    }//for_each_outcome
                }//if_outcomes

                $out_tracker .= '<hr class="line_rpt">';
            }//for_each_competence

            return $out_tracker;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeTracker

    /**
     * @param           $completed
     * @param           $not_completed
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the tracker connected to the individual courses - Screen Format
     */
    public static function Print_IndividualTracker($completed,$not_completed) {
        /* Variables    */
        $out_tracker = '';
        $individualToogle   = 'YUI_' . '0';
        $url_img            = new moodle_url('/pix/t/expanded.png');
        $title              = get_string('individual_courses','local_tracker_manager');

        try {
            /* Title    */
            $out_tracker .= html_writer::start_tag('div',array('class' => 'header_tracker'));
                $out_tracker .= '<h5>'. $title . '</h5>';
            $out_tracker .= html_writer::end_tag('div');

            /* Tracker Info */
            $out_tracker .= html_writer::start_tag('div',array('class' => 'tracker_list'));
                /* Individual Courses   */
                $individualToogle .= '_table';
                $out_tracker .= html_writer::start_tag('div',array('class' => 'course_list'));
                    /* Header Table     */
                    $out_tracker .= self::AddHeader_IndividualCoursesTable($individualToogle,$url_img);
                    /* Content Table    */
                    $out_tracker .= html_writer::start_tag('div',array('class' => 'course_list', 'id' => $individualToogle . '_div'));
                        $out_tracker .= self::AddContent_IndividualCoursesTable($completed,$not_completed);
                    $out_tracker .= html_writer::end_tag('div');//course_list
                $out_tracker .= html_writer::end_tag('div');//course_list
            $out_tracker .= html_writer::end_tag('div');//tracker_list

            return $out_tracker;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_IndividualTracker

    /**
     * @param           $trackerUser
     * @throws          Exception
     *
     * @creationDate    08/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download the Tracker report - Excel Format
     */
    public static function Download_TrackerReport($trackerUser) {
        /* Variables    */
        global $CFG;
        $row        = null;
        $my_xls     = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename(get_string('name','local_tracker_manager') . '_' . $time . ".xls");

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* Outcome Courses      */
            self::AddSheet_OutcomeCourses($export,$my_xls,$trackerUser->competence);

            /* Individual Courses   */
            $row = 0;
            self::AddSheet_IndividualCourses($export,$my_xls,$row,$trackerUser->completed,$trackerUser->not_completed);

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_TrackerReport


    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @param           $user_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the competence data connected to the user
     *
     * Competence
     *      [id]
     *          --> levelThree
     *          --> name
     *          --> industrycode
     *          --> job_roles
     *          --> outcomes.   Array
     *                              [id]
     *                                  --> id
     *                                  --> name
     *                                  --> expiration
     *                                  --> courses
     *                                  --> roles
     *                                  --> completed.
     *                                  --> not_completed
     *                                  --> not_enrol
     */
    private static function Get_CompetenceTracker($user_id) {
        /* Variables    */
        global $DB;
        $myCompetence   = array();
        $competenceInfo = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		uicd.companyid,
                                rgc.industrycode,
                                rgc.name,
                                IF(uicd.jobroles,uicd.jobroles,0) as 'jobroles'
                     FROM		{user_info_competence_data} 	uicd
                        JOIN	{report_gen_companydata}		rgc		ON rgc.id = uicd.companyid
                     WHERE		uicd.userid = :user
                     ORDER BY	rgc.industrycode, rgc.name ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Competence Info  */
                    $info = new stdClass();
                    $info->levelThree   = $instance->companyid;
                    $info->name         = $instance->name;
                    $info->industrycode = $instance->industrycode;
                    $info->job_roles    = $instance->jobroles;
                    $info->outcomes     = self::GetInfoOutcomes_JobRoles($info->job_roles);

                    /* Add the company */
                    if ($info->outcomes) {
                        $myCompetence[$instance->companyid] = $info;
                    }//if_outcomes

                }//for_instance_competence
            }//if_rdo

            return $myCompetence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//Get_CompetenceTracker

    /**
     * @param           $jr_lst
     * @return          array
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the outcomes connected to the job roles
     *
     * Outcomes
     *          [id]
     *              --> id
     *              --> name
     *              --> expiration
     *              --> courses
     *              --> roles
     *              --> completed
     *              --> not_completed
     *              --> not_enrol
     */
    private static function GetInfoOutcomes_JobRoles($jr_lst) {
        /* Variables    */
        global $DB;
        $outcomes   = array();
        $info       = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT	    o.id,
                                o.fullname,
                                GROUP_CONCAT(DISTINCT oucu.courseid ORDER BY oucu.courseid SEPARATOR ',') as 'courses',
                                GROUP_CONCAT(DISTINCT jr.name ORDER BY jr.name SEPARATOR ',')             as 'job_roles',
                                rgo.expirationperiod
                     FROM		{grade_outcomes}              o
                        JOIN 	{grade_outcomes_courses}      oucu	    ON 	  	oucu.outcomeid  = o.id
                        JOIN 	{report_gen_outcome_exp}      rgo	  	ON 	  	rgo.outcomeid   = oucu.outcomeid
                        JOIN 	{report_gen_outcome_jobrole}  oj	  	ON 	  	oj.outcomeid    = rgo.outcomeid
                        JOIN 	{report_gen_jobrole}          jr	  	ON 	  	jr.id 		  	= oj.jobroleid
                                                                        AND   	jr.id 		    IN ($jr_lst)
                     GROUP BY 	o.id
                     ORDER BY   o.fullname ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Outcome Info */
                    $info = new stdClass();
                    $info->id               = $instance->id;
                    $info->name             = $instance->fullname;
                    $info->expiration       = $instance->expirationperiod;
                    $info->courses          = $instance->courses;
                    $info->roles            = $instance->job_roles;
                    $info->completed        = null;
                    $info->not_completed    = null;
                    $info->not_enrol        = null;

                    /* Add outcome  */
                    if ($info->courses)  {
                        $outcomes[$instance->id] = $info;
                    }//if_courses
                }//for_instance_outcome
            }//if_rdo

            return $outcomes;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfoOutcomes_JobRoles

    /**
     * @param           $user_id
     * @param           $competence
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the info tracker for each outcome connected to the level three and user
     *
     * Competence
     *          --> levelThree
     *          --> name
     *          --> industrycode
     *          --> job_roles
     *          --> outcomes.       Array
     *                                  [id]
     *                                      --> name
     *                                      --> expiration
     *                                      --> courses
     *                                      --> roles
     *                                      --> completed.      Array
     *                                                              --> id
     *                                                              --> name
     *                                                              --> completed
     *                                      --> not_completed.  Array
     *                                                              --> id
     *                                                              --> name
     *                                                              --> completed
     *                                      --> not_enrol.      Array
     *                                                              --> id
     *                                                              --> name
     */
    private static function GetInfoOutcomeTracker($user_id,&$competence) {
        /* Variables    */
        $outcomesTracker    = null;
        $coursesEnrol       = null;

        try {
            /* Get the outcome tracker  */
            $outcomesTracker = $competence->outcomes;
            if ($outcomesTracker) {
                foreach ($outcomesTracker as $id=>$outcome) {
                    /* Get Courses Completed and Not Completed       */
                    list($coursesEnrol,$outcome->completed,$outcome->not_completed) = self::GetTracker_CoursesEnrol($user_id,$outcome->courses);

                    /* Get Courses Not Enrol        */
                    if ($coursesEnrol) {
                        $coursesEnrol = implode(',',$coursesEnrol);
                    }else {
                        $coursesEnrol = 0;
                    }
                    $outcome->not_enrol = self::GetTracker_CoursesNotEnrol($outcome->courses,$coursesEnrol);

                    if (!$outcome->completed && !$outcome->not_completed && !$outcome->not_enrol) {
                        unset($competence->outcomes[$id]);
                    }else {
                        $competence->outcomes[$id] = $outcome;
                    }//if_completed_not_completed_not_Enrol
                }//for_each_outcome
            }//ifOutcomes
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfoOutcomeTracker

    /**
     * @param           $user_id
     * @param           $courses
     * @return          array
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the courses completed and not completed connected with the user
     *
     * Completed / Not Completed
     *                          [id]
     *                              --> id
     *                              --> name
     *                              --> completed
     */
    private static function GetTracker_CoursesEnrol($user_id,$courses) {
        /* Variables    */
        global $DB;
        $completed      = array();
        $not_completed  = array();
        $enrol          = array();
        $info           = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT	c.id,
                            c.fullname,
                            IF (cc.timecompleted,cc.timecompleted,0) as 'completed'
                     FROM			{course}					c
                        JOIN		{enrol} 					e	ON 	e.courseid 	= c.id
                                                                    AND	e.status 	= 0
                        JOIN		{user_enrolments}			ue	ON 	ue.enrolid 	= e.id
                                                                    AND	ue.status	= 0
                                                                    AND ue.userid   = :user
                        LEFT JOIN	{course_completions}		cc	ON	cc.course 	= e.courseid
                                                                    AND cc.userid 	= ue.userid
                     WHERE		c.id IN ($courses)
                        AND     c.visible = 1
                     ORDER BY	c.fullname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Course Info  */
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->fullname;
                    $info->completed    = $instance->completed;

                    /* Add course   */
                    if ($instance->completed) {
                        $completed[$instance->id] = $info;
                    }else {
                        $not_completed[$instance->id] = $info;
                    }//if_time_Completed

                    /* Enrol */
                    $enrol[$instance->id] = $instance->id;
                }//for_instance_courses
            }//if_rdo

            return array($enrol,$completed,$not_completed);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetTracker_CoursesEnrol

    /**
     * @param           $courses
     * @param           $coursesEnrol
     * @return          array
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the courses that the user are not enrolled
     *
     * Not Enrol
     *      [id]
     *          --> id
     *          --> name
     */
    private static function GetTracker_CoursesNotEnrol($courses,$coursesEnrol) {
        /* Variables    */
        global $DB;
        $not_enrol  = array();
        $info       = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		c.id,
                                c.fullname
                    FROM		{course}		  c
                    WHERE		c.id IN ($courses)
                      AND       c.visible = 1 ";

            /* Courses Enrol    */
            if ($coursesEnrol) {
                $sql .= " AND c.id NOT IN ($coursesEnrol) ";
            }//if_coursesEnrol

            /* Order    */
            $sql .= " ORDER BY	c.fullname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Course Info  */
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->fullname;

                    /* Add course   */
                    $not_enrol[$instance->id] = $info;
                }//for_instance
            }//if_rdo

            return $not_enrol;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetTracker_CoursesNotEnrol

    /**
     * @param           $user_id
     * @param           $competence
     * @return          array
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the info tracker for all the courses not connected with the outcomes
     *
     * Completed / Not Completed
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> completed
     *
     * @updateDate      20/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user can unenrol from the course
     */
    private static function GetTrackerNotConnected($user_id,$competence) {
        /* Variables    */
        global $DB;
        $connected      = 0;
        $completed      = array();
        $not_completed  = array();
        $info           = null;
        $user           = null;

        try {
            $user = get_complete_user_data('id',$user_id);

            /* Get Courses Not Connected    */
            foreach ($competence as $levelThree) {
                if ($levelThree->outcomes) {
                    foreach ($levelThree->outcomes as $outcome) {
                        if ($outcome->courses) {
                            $connected .= ',' . $outcome->courses;
                        }//if_courses
                    }//for_outomes
                }//if_outcomes
            }//if_levelThree

            /* Search Criteria */
            $params = array();
            $params['user']     = $user_id;
            $params['ue_user']  = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT	c.id,
                            c.fullname,
                            IF (cc.timecompleted,cc.timecompleted,0)                                        as 'completed',
                            GROUP_CONCAT(DISTINCT CONCAT(e.enrol,'#',e.id) ORDER BY e.enrol SEPARATOR ',')  as 'enrolments'
                     FROM			{course}					c
                        LEFT JOIN	{course_completions}  	    cc	ON	cc.course   = c.id
                                                                    AND cc.userid   = :user
                        JOIN		{enrol} 					e	ON 	e.courseid 	= c.id
                                                                    AND	e.status 	= 0
                        JOIN		{user_enrolments}			ue	ON 	ue.enrolid 	= e.id
                                                                    AND	ue.status	= 0
                                                                    AND ue.userid   = :ue_user
                     WHERE		c.id NOT IN ($connected)
                        AND     c.visible = 1
                     GROUP BY	c.id
                     ORDER BY	c.fullname ";

            /* Execute   */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Course Info  */
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->fullname;
                    $info->completed    = $instance->completed;
                    $info->unEnrol      = self::Check_CanUnenrol(explode(',',$instance->enrolments),$user,$instance->id);

                    /* Add course   */
                    if ($instance->completed) {
                        $completed[$instance->id] = $info;
                    }else {
                        $not_completed[$instance->id] = $info;
                    }//if_time_Completed
                }//for_instance
            }//if_rdo

            return array($completed,$not_completed);
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetTrackerNotConnected

    /**
     * @param           $enrolMethods
     * @param           $user
     * @param           $courseId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user can enrol by himsef/herslef
     */
    private static function Check_CanUnenrol($enrolMethods,$user,$courseId) {
        /* Variables    */
        global $DB;
        $method         = null;
        $instance       = null;
        $plugin         = null;
        $context        = CONTEXT_COURSE::instance($courseId);
        $unEnrol        = true;

        try {
            foreach ($enrolMethods as $enrol) {
                $method = explode('#',$enrol);

                $plugin = enrol_get_plugin($method[0]);
                $instance = new stdClass();
                $instance->id       = $method[1];
                $instance->courseid = $courseId;
                $instance->enrol    = $method[0];

                $capability = 'enrol/' . $method[0] . ':unenrol';
                if ($plugin->allow_unenrol_user($instance,$user) && has_capability($capability, $context)) {
                    $unEnrol  = $unEnrol && true;
                }else {
                    $unEnrol = false;
                }

            }

            return $unEnrol;
        }catch (Exception $ex) {
            throw $ex;
        }//try_cathc
    }//Check_CanUnenrol

    /**
     * @param           $job_roles
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    21/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the job roles names
     */
    private static function Get_JobRolesNames($job_roles) {
        /* Variables    */
        global $DB;
        $jr_names = null;

        try {
            if ($job_roles) {
                /* SQL Instruction  */
                $sql = " SELECT		id,
                                    CONCAT(industrycode,' - ',name) as 'name'
                         FROM		{report_gen_jobrole}
                         WHERE		id IN ($job_roles)
                         ORDER BY	industrycode, name ";

                /* Execute  */
                $rdo = $DB->get_records_sql($sql);
                if ($rdo) {
                    foreach ($rdo as $instance) {
                        if ($jr_names) {
                            $jr_names .= ', ' . $instance->name;
                        }else {
                            $jr_names = $instance->name;
                        }//if_jr_names

                    }//for_each_job_role
                }//if_rdo
            }//if_job_roles

            return $jr_names;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRolesNames

    /**
     * @return          string
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the output buttons to download the report
     */
    private static function Get_OutputButtons() {
        $url_dwn = new moodle_url('/report/manager/tracker/index.php',array('pdf'=>TRACKER_PDF_DOWNLOAD));
        $send_pdf_btn   = html_writer::start_tag('div',array('class' => 'div_button_tracker'));
            $send_pdf_btn .= html_writer::link($url_dwn,get_string('download_pdf_btn','local_tracker_manager'),array('class' =>"button_tracker"));
        $send_pdf_btn  .= html_writer::end_tag('div');

        return $send_pdf_btn;
    }//Get_OutputButtons

    /**
     * @param           $outcome
     * @param           $toogle
     * @param           $img
     * @return          string
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the outcome header - Screen Format
     */
    private static function PrintHeader_OutcomeTracker($outcome,$toogle,$img) {
        /* Variables    */
        $header     = null;

        $header .= html_writer::start_div('header_outcome_company_rpt');
            /* Col One  */
            $header .= html_writer::start_div('header_col_one');
                $header .= '<button class="toggle_header_tracker" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header .= html_writer::end_div('');//header_col_one
            /* Col Two  */
            $header .= html_writer::start_div('header_col_two');
                $header .= '<h6>' . $outcome . '</h6>';
            $header .= html_writer::end_div('');//header_col_two
        $header .= html_writer::end_div('');//header_outcome_company_rpt

        return $header;
    }//PrintHeader_OutcomeTracker

    /**
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header to courses table - Screen Format
     */
    private static function AddHeader_CoursesTable() {
        /* Variables    */
        $header = '';
        $strCourse         = get_string('course');
        $strState          = get_string('state','local_tracker_manager');
        $strValid          = get_string('outcome_valid_until','local_tracker_manager');
        $strCompletion     = get_string('completion_time','local_tracker_manager');

        try {
            /* Build Header */
            $header .= html_writer::start_tag('table');
                $header .= html_writer::start_tag('tr',array('class' => 'head'));
                    /* Empty Col   */
                    $header .= html_writer::start_tag('td',array('class' => 'head_first'));
                    $header .= html_writer::end_tag('td');
                    /* Course           */
                    $header .= html_writer::start_tag('td',array('class' => 'head_course'));
                        $header .= $strCourse;
                    $header .= html_writer::end_tag('td');
                    /* Status        */
                    $header .= html_writer::start_tag('td',array('class' => 'head_status'));
                        $header .= $strState;
                    $header .= html_writer::end_tag('td');
                    /* Completion    */
                    $header .= html_writer::start_tag('td',array('class' => 'head_status'));
                        $header .= $strCompletion;
                    $header .= html_writer::end_tag('td');
                    /* Valid        */
                    $header .= html_writer::start_tag('td',array('class' => 'head_status'));
                        $header .= $strValid;
                    $header .= html_writer::end_tag('td');
                    /* Empty Col    */
                    //$header .= html_writer::start_tag('td',array('class' => 'head_start'));
                    //    $header .= '&nbsp;';
                    //$header .= html_writer::end_tag('td');
                    /* Last Col     */
                    $header .= html_writer::start_tag('td',array('class' => 'head_first'));
                        $header .= '&nbsp;';
                    $header .= html_writer::end_tag('td');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('table');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_CoursesTable

    /**
     * @param           $outcome
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content table - Screen Format
     */
    private static function AddContent_CoursesTable($outcome) {
        /* Variables    */
        $content        = '';
        $url            = null;
        $strUrl         = null;
        $not_completed  = null;
        $completed      = null;
        $not_enrol      = null;
        $class          = null;
        $label          = null;
        $nameTruncate   = null;

        try {
            $content .= html_writer::start_tag('table');
            /* Not Completed    */
            if ($outcome->not_completed) {
                $not_completed = $outcome->not_completed;
                foreach ($not_completed as $course) {
                    /* Course Url   */
                    $url     = new moodle_url('/course/view.php',array('id'=>$course->id,'start' =>1));

                    $content .= html_writer::start_tag('tr');
                        /* Empty Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        /* Course           */
                        $content .= html_writer::start_tag('td',array('class' => 'course'));
                            if (strlen($course->name) <= 100) {
                                $nameTruncate = $course->name;
                            }else {
                                $nameTruncate = substr($course->name,0,100);
                                $index = strrpos($nameTruncate,' ');
                                $nameTruncate = substr($nameTruncate,0,$index) . ' ...';
                            }
                            $content .= '<a href="'.$url .'">'. $nameTruncate .'</a>';
                        $content .= html_writer::end_tag('td');
                        /* Status        */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= get_string('outcome_course_started','local_tracker_manager');
                        $content .= html_writer::end_tag('td');
                        /* Completion    */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= '-';
                        $content .= html_writer::end_tag('td');
                        /* Valid        */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= '&nbsp;';
                        $content .= html_writer::end_tag('td');
                        /* Empty Col    */
                        //$strUrl  = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
                        //$content .= html_writer::start_tag('td',array('class' => 'start'));
                        //    $content .= $strUrl;
                        //$content .= html_writer::end_tag('td');
                        /* Last Col */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_each_course_not_completed
            }//if_not_completed

            /* Not Enrol        */
            if ($outcome->not_enrol) {
                $not_enrol = $outcome->not_enrol;
                foreach ($not_enrol as $course) {
                    /* Url Course */
                    $url     = new moodle_url('/course/view.php',array('id'=>$course->id));

                    $content .= html_writer::start_tag('tr',array('class' => 'not_enroll'));
                        /* Empty Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        /* Course           */
                        $content .= html_writer::start_tag('td',array('class' => 'course'));
                            if (strlen($course->name) <= 100) {
                                $nameTruncate = $course->name;
                            }else {
                                $nameTruncate = substr($course->name,0,100);
                                $index = strrpos($nameTruncate,' ');
                                $nameTruncate = substr($nameTruncate,0,$index) . ' ...';
                            }
                            $content .= '<a href="'.$url .'">'. $nameTruncate .'</a>';
                        $content .= html_writer::end_tag('td');
                        /* Status        */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= get_string('outcome_course_not_enrolled','local_tracker_manager');
                        $content .= html_writer::end_tag('td');
                        /* Completion    */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= '-';
                        $content .= html_writer::end_tag('td');
                        /* Valid        */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= '&nbsp;';
                        $content .= html_writer::end_tag('td');
                        /* Empty Col    */
                        //$strUrl  = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
                        //$content .= html_writer::start_tag('td',array('class' => 'start'));
                        //    $content .= $strUrl;
                        //$content .= html_writer::end_tag('td');
                        /* Last Col */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_each_course_not_enrol
            }//if_not_enrol

            /* Completed        */
            if ($outcome->completed) {
                $completed = $outcome->completed;
                foreach ($completed as $course) {
                    /* Url Course */
                    $url     = new moodle_url('/course/view.php',array('id'=>$course->id,'start'=>1));

                    $ts = strtotime($outcome->expiration  . ' month', $course->completed);
                    if ($ts < time()) {
                        $class = 'expired';
                        $label = get_string('outcome_course_expired','local_tracker_manager');
                    }else {
                        $class = 'completed';
                        $label = get_string('outcome_course_finished','local_tracker_manager');
                    }

                    $content .= html_writer::start_tag('tr',array('class' => $class));
                        /* Empty Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        /* Course           */
                        $content .= html_writer::start_tag('td',array('class' => 'course'));
                            if (strlen($course->name) <= 100) {
                                $nameTruncate = $course->name;
                            }else {
                                $nameTruncate = substr($course->name,0,100);
                                $index = strrpos($nameTruncate,' ');
                                $nameTruncate = substr($nameTruncate,0,$index) . ' ...';
                            }
                            $content .= '<a href="'.$url .'">'. $nameTruncate .'</a>';
                        $content .= html_writer::end_tag('td');
                        /* Status        */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= $label;
                        $content .= html_writer::end_tag('td');
                        /* Completion    */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= userdate($course->completed,'%d.%m.%Y', 99, false);
                        $content .= html_writer::end_tag('td');
                        /* Valid        */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= userdate($ts,'%d.%m.%Y', 99, false);
                        $content .= html_writer::end_tag('td');
                        /* Empty Col    */
                        //$strUrl  = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
                        //$content .= html_writer::start_tag('td',array('class' => 'start'));
                        //    $content .= $strUrl;
                        //$content .= html_writer::end_tag('td');
                        /* Last Col */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_each_course_completed
            }//if_completed
            $content .= html_writer::end_tag('table');

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_CoursesTable

    /**
     * @param           $toggle
     * @param           $url
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header to individual courses table - Screen Format
     */
    private static function AddHeader_IndividualCoursesTable($toggle,$url) {
        /* Variables    */
        $header = '';
        $strCourse         = get_string('course');
        $strState          = get_string('state','local_tracker_manager');
        $strCompletion     = get_string('completion_time','local_tracker_manager');

        try {
            /* Build Header */
            $header .= html_writer::start_tag('table');
                $header .= html_writer::start_tag('tr',array('class' => 'head'));
                    /* Empty Col   */
                    $header .= html_writer::start_tag('td',array('class' => 'head_first'));
                        $header .= html_writer::start_tag('button',array('id' => $toggle, 'class' => 'toggle', 'type' => 'image'));
                        $header .= html_writer::start_tag('img',array('src' => $url,'id' => $toggle . '_img'));
                        $header .= html_writer::end_tag('img');
                        $header .= html_writer::end_tag('button');
                    $header .= html_writer::end_tag('td');
                    /* Course           */
                    $header .= html_writer::start_tag('td',array('class' => 'head_course'));
                        $header .= $strCourse;
                    $header .= html_writer::end_tag('td');
                    /* Status        */
                    $header .= html_writer::start_tag('td',array('class' => 'head_status'));
                        $header .= $strState;
                    $header .= html_writer::end_tag('td');
                    /* Completion    */
                    $header .= html_writer::start_tag('td',array('class' => 'head_status'));
                        $header .= $strCompletion;
                    $header .= html_writer::end_tag('td');
                    /* Valid        */
                    $header .= html_writer::start_tag('td',array('class' => 'head_status'));
                        $header .= '&nbsp;';
                    $header .= html_writer::end_tag('td');
                    /* Empty Col    */
                    //$header .= html_writer::start_tag('td',array('class' => 'head_start'));
                    //    $header .= '&nbsp;';
                    //$header .= html_writer::end_tag('td');
                    /* Last Col     */
                    $header .= html_writer::start_tag('td',array('class' => 'head_first'));
                        $header .= '&nbsp;';
                    $header .= html_writer::end_tag('td');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('table');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_IndividualCoursesTable

    /**
     * @param           $completed
     * @param           $not_completed
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content to individual courses table - Screen Format
     *
     * @updateDate      20/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the link to unerol
     */
    private static function AddContent_IndividualCoursesTable($completed,$not_completed) {
        /* Variables    */
        $content        = '';
        $url            = null;
        $strUrl         = null;
        $urlUnEnrol     = new moodle_url('/report/manager/tracker/unenrol.php');
        $nameTruncate   = null;

        try {
            $content .= html_writer::start_tag('table');
                /* Not Completed    */
                if ($not_completed) {
                    foreach ($not_completed as $course) {
                        /* Course Url   */
                        $url     = new moodle_url('/course/view.php',array('id'=>$course->id,'start'=>1));

                        $content .= html_writer::start_tag('tr');
                            /* Empty Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            /* Course           */
                            $content .= html_writer::start_tag('td',array('class' => 'course'));
                                if (strlen($course->name) <= 100) {
                                    $nameTruncate = $course->name;
                                }else {
                                    $nameTruncate = substr($course->name,0,100);
                                    $index = strrpos($nameTruncate,' ');
                                    $nameTruncate = substr($nameTruncate,0,$index) . ' ...';
                                }
                                $content .= '<a href="'.$url .'">'. $nameTruncate .'</a>';
                            $content .= html_writer::end_tag('td');
                            /* Status        */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                $content .= get_string('outcome_course_started','local_tracker_manager');
                            $content .= html_writer::end_tag('td');
                            /* Completion    */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                $content .= '-';
                            $content .= html_writer::end_tag('td');
                            /* Valid        */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                /* Unenrol allowed --> add link to unenrol */
                                if ($course->unEnrol) {
                                    $urlUnEnrol->param('id',$course->id);
                                    $strUrl  = '<a href="'.$urlUnEnrol .'">'. get_string('unenrol','report_manager') .'</a>';
                                    $content .= $strUrl;
                                }else {
                                    $content .= '&nbsp;';
                                }//if_unenrol
                            $content .= html_writer::end_tag('td');
                            /* Empty Col    */
                            //$strUrl  = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
                            //$content .= html_writer::start_tag('td',array('class' => 'start'));
                            //    $content .= $strUrl;
                            //$content .= html_writer::end_tag('td');
                            /* Last Col */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//for_each_course_not_completed
                }//if_not_completed

                /* Completed        */
                if ($completed) {
                    foreach ($completed as $course) {
                        /* Course Url */
                        $url     = new moodle_url('/course/view.php',array('id'=>$course->id,'start'=>1));

                        $content .= html_writer::start_tag('tr',array('class' => 'completed'));
                            /* Empty Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            /* Course           */
                            $content .= html_writer::start_tag('td',array('class' => 'course'));
                                if (strlen($course->name) <= 100) {
                                    $nameTruncate = $course->name;
                                }else {
                                    $nameTruncate = substr($course->name,0,100);
                                    $index = strrpos($nameTruncate,' ');
                                    $nameTruncate = substr($nameTruncate,0,$index) . ' ...';
                                }
                                $content .= '<a href="'.$url .'">'. $nameTruncate .'</a>';
                            $content .= html_writer::end_tag('td');
                            /* Status        */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                $content .= get_string('outcome_course_finished','local_tracker_manager');
                            $content .= html_writer::end_tag('td');
                            /* Completion    */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                $content .= userdate($course->completed,'%d.%m.%Y', 99, false);;
                            $content .= html_writer::end_tag('td');
                            /* Valid        */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                /* Unenrol allowed --> add link to unenrol */
                                if ($course->unEnrol) {
                                    $urlUnEnrol->param('id',$course->id);
                                    $strUrl  = '<a href="'.$urlUnEnrol .'">'. get_string('unenrol','report_manager') .'</a>';
                                    $content .= $strUrl;
                                }else {
                                    $content .= '&nbsp;';
                                }//if_unenrol
                            $content .= html_writer::end_tag('td');
                            /* Empty Col    */
                            //$strUrl  = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
                            //$content .= html_writer::start_tag('td',array('class' => 'start'));
                            //    $content .= $strUrl;
                            //$content .= html_writer::end_tag('td');
                            /* Last Col */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//for_each_course_completed
                }//if_completed
            $content .= html_writer::end_tag('table');

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_IndividualCoursesTable

    /**
     * @param           $excel
     * @param           $my_xls
     * @param           $competence
     * @throws          Exception
     *
     * @creationDate    08/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create a new sheet for the outcome courses. One sheet by company
     */
    private static function AddSheet_OutcomeCourses(&$excel,&$my_xls,$competence) {
        /* Variables    */
        $outcomes   = null;
        $row        = null;

        try {
            /* One Company --> One sheet    */
            foreach ($competence as $levelThree) {
                $row = 0;
                // Adding the worksheet
                $my_xls = $excel->add_worksheet($levelThree->industrycode . ' - ' . $levelThree->name);

                /* Add Header - Outcome Courses */
                self::AddHeaderSheet_OutcomeCourses($my_xls,$row);

                if ($levelThree->outcomes) {
                    $row ++;
                    $outcomes = $levelThree->outcomes;
                    foreach ($outcomes as $outcome) {
                        /* Add Content - Outcome course */
                        self::AddContentSheet_OutcomeCourses($my_xls,$row,$outcome);
                        $my_xls->merge_cells($row,0,$row,17);
                        $row ++;
                    }//for_each_outcome
                }//if_outcomes
            }//for_levelThree
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddSheet_OutcomeCourses

    /**
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    08/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header to the company outcome course sheet
     */
    private static function AddHeaderSheet_OutcomeCourses(&$my_xls,&$row) {
        /* Variables    */
        $strOutcome         = get_string('outcome', 'report_manager');
        $strCourse          = get_string('course');
        $strState           = get_string('state','local_tracker_manager');
        $strValid           = get_string('outcome_valid_until','local_tracker_manager');
        $strCompletion      = get_string('completion_time','local_tracker_manager');
        $col                = 0;

        try {
            /* Outcome      */
            $my_xls->write($row, $col, $strOutcome,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Course       */
            $col = $col + 6;
            $my_xls->write($row, $col, $strCourse,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* State    */
            $col = $col + 6;
            $my_xls->write($row, $col, $strState,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* Completion  */
            $col = $col + 2;
            $my_xls->write($row, $col, $strCompletion,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* Valid        */
            $col = $col + 2;
            $my_xls->write($row, $col, $strValid,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeaderSheet_OutcomeCourses

    /**
     * @param           $my_xls
     * @param           $row
     * @param           $outcome
     * @throws          Exception
     *
     * @creationDate    08/04/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Add the content to the company outcome courses sheet
     */
    private static function AddContentSheet_OutcomeCourses(&$my_xls,&$row,$outcome) {
        /* Variables    */
        $not_completed  = null;
        $completed      = null;
        $not_enrol      = null;
        $bg_color       = null;
        $state          = null;
        $col            = null;

        try {
            /* Not Completed    */
            if ($outcome->not_completed) {
                $state          = get_string('outcome_course_started','local_tracker_manager');
                $not_completed  = $outcome->not_completed;
                foreach ($not_completed as $course) {
                    $col = 0;

                    /* Outcome  */
                    $my_xls->write($row, $col, $outcome->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* Course   */
                    $col = $col + 6;
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State     */
                    $col = $col + 6;
                    $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    /* Completion        */
                    $col = $col + 2;
                    $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    /* Valid            */
                    $col = $col + 2;
                    $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    $row++;
                }//for_course
            }//if_not_completed

            /* Not Enrol        */
            if ($outcome->not_enrol) {
                $state      = get_string('outcome_course_not_enrolled','local_tracker_manager');
                $bg_color   = '#fcf8e3';
                $not_enrol  = $outcome->not_enrol;
                foreach ($not_enrol as $course) {
                    $col = 0;

                    /* Outcome  */
                    $my_xls->write($row, $col, $outcome->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* Course   */
                    $col = $col + 6;
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State     */
                    $col = $col + 6;
                    $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    /* Completion        */
                    $col = $col + 2;
                    $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    /* Valid            */
                    $col = $col + 2;
                    $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    $row++;
                }//for_Each_not_enrol
            }//if_not_enrol

            /* Completed        */
            if ($outcome->completed) {
                $completed = $outcome->completed;
                foreach ($completed as $course) {
                    $col = 0;

                    $ts = strtotime($outcome->expiration  . ' month', $course->completed);
                    if ($ts < time()) {
                        $bg_color = '#f2dede';
                        $state = get_string('outcome_course_expired','local_tracker_manager');
                    }else {
                        $bg_color = '#dff0d8';
                        $state = get_string('outcome_course_finished','local_tracker_manager');
                    }

                    /* Outcome  */
                    $my_xls->write($row, $col, $outcome->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* Course   */
                    $col = $col + 6;
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State     */
                    $col = $col + 6;
                    $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    /* Completion        */
                    $col = $col + 2;
                    $my_xls->write($row, $col, userdate($course->completed,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    /* Valid            */
                    $col = $col + 2;
                    $my_xls->write($row, $col, userdate($ts,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    $row++;
                }//for_Each_completed
            }//if_completed
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContentSheet_OutcomeCourses

    /**
     * @param           $excel
     * @param           $my_xls
     * @param           $row
     * @param           $completed
     * @param           $not_completed
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a new sheet for the individual courses
     */
    private static function AddSheet_IndividualCourses(&$excel,&$my_xls,&$row,$completed,$not_completed) {
        try {
            // Adding the worksheet
            $my_xls = $excel->add_worksheet(get_string('individual_courses','local_tracker_manager'));

            /* Add Header - Individual Courses  */
            self::AddHeaderSheet_IndividualCourses($my_xls,$row);

            /* Add Content - Individual Courses */
            $row++;
            self::AddContentSheet_IndividualCourses($completed,$not_completed,$my_xls,$row);

            $my_xls->merge_cells($row,0,$row,9);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddSheet_IndividualCourses

    /**
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header to the individual courses sheet
     */
    private static function AddHeaderSheet_IndividualCourses(&$my_xls,$row) {
        /* Variables    */
        $strCourse         = get_string('course');
        $strState          = get_string('state','local_tracker_manager');
        $strCompletion     = get_string('completion_time','local_tracker_manager');
        $col               = 0;

        try {
            /* Course       */
            $my_xls->write($row, $col, $strCourse,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* State    */
            $col = $col + 6;
            $my_xls->write($row, $col, $strState,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* Completion  */
            $col = $col + 2;
            $my_xls->write($row, $col, $strCompletion,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeaderSheet_IndividualCourses

    /**
     * @param           $completed
     * @param           $not_completed
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content to the individual courses sheet
     */
    private static function AddContentSheet_IndividualCourses($completed,$not_completed,&$my_xls,&$row) {
        /* Variables    */
        $col        = null;
        $state      = null;
        $bg_color   = '#dff0d8';

        try {
            /* Not Completed    */
            if ($not_completed) {
                $state = get_string('outcome_course_started','local_tracker_manager');
                foreach ($not_completed as $course) {
                    $col = 0;

                    /* Course  */
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State     */
                    $col = $col + 6;
                    $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    /* Completion        */
                    $col = $col + 2;
                    $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    $row++;
                }//for_Each_not_completed
            }//if_not_completed

            /* Completed    */
            if ($completed) {
                $state = get_string('outcome_course_started','local_tracker_manager');
                foreach ($completed as $course) {
                    $col = 0;

                    /* Course  */
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State     */
                    $col = $col + 6;
                    $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    /* Completion        */
                    $col = $col + 2;
                    $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    $row++;
                }//for_Each_completed
            }//if_completed
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContentSheet_IndividualCourses
}//TrackerManager