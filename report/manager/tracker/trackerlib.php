<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
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
     *
     * Description
     * Add all courses connected with the user, where the user is in the waiting list
     *
     * @param           int   $user_id  User id
     *
     * @return          stdClass        Tracker connected with
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      03/11/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_user_tracker($user_id) {
        /* Variables    */
        global $DB;
        $userTracker        = null;

        try {
            //Get info user
            $rdo = $DB->get_record('user',array('id' => $user_id),'firstname,lastname');

            // Info user tracker
            $userTracker = new stdClass();
            $userTracker->id            = $user_id;
            $userTracker->name          = $rdo->firstname . ' ' . $rdo->lastname;
            $userTracker->competence    = self::get_competence_tracker($user_id);

            // Get the outcome tracker
            if ($userTracker->competence) {
                foreach ($userTracker->competence as $competence) {
                    self::get_info_outcome_tracker($user_id,$competence);
                }//for_each_competence_levelThree
            }//if_competence

            // Get Tracker course not connected
            list($userTracker->completed,$userTracker->not_completed,$userTracker->inWaitList) = self::get_tracker_not_connected($user_id,$userTracker->competence);

            return $userTracker;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_user_tracker

    /**
     * Description
     * Unenrol user from the course
     *
     * @param           int $courseId   Course id
     * @param           int $userId     User id
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/11/2015
     * @author          eFaktor     (fbv)
     */
    public static function unenrol_from_course($courseId,$userId) {
        /* Variables    */
        global $DB;
        $trans  = null;
        $sql    = null;
        $params = null;
        $rdo    = null;
        $plugin = null;
        $exit   = null;

        // Start Transaction
        $trans = $DB->start_delegated_transaction();

        try {
            /* Get Instances    */

            // Search Criteria
            $params = array();
            $params['course'] = $courseId;
            $params['user']   = $userId;

            // Sql Instruction
            $sql = " SELECT	DISTINCT 	
                                e.id,
                                e.enrol,
                                e.courseid
                     FROM		  {enrol}				  e
                        JOIN	  {user_enrolments}	      ue  ON  ue.enrolid 	= e.id
                                                              AND ue.status	= 0
                                                              AND ue.userid	= :user
                        LEFT JOIN {course_completions}    cc  ON  cc.course	= e.courseid
                                                              AND cc.userid	= ue.userid
                                                              AND (cc.timecompleted IS NULL
                                                                   OR
                                                                   cc.timecompleted = 0
                                                                  )
                     WHERE		  e.courseid = :course
                        AND		  e.status   = 0 ";

            // Execute
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

            // Commit
            $trans->allow_commit();

            return $exit;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//unenrol_from_course

    /**
     * Description
     * Cancel the enrol request user for a specific course
     *
     * @param           int $courseId   Course id
     * @param           int $userId     User id
     *
     * @return          bool|null
     * @throws          Exception
     * @throws          dml_transaction_exception
     *
     * @creationDate    03/11/2016
     * @author          eFaktor     (fbv)
     */
    public static function unwait_from_course($courseId,$userId) {
        /* Variables */
        global $DB;
        $dbMan  = null;
        $trans  = null;
        $sql    = null;
        $params = null;
        $rdo    = null;
        $plugin = null;
        $exit   = null;

        // Start Transaction
        $trans = $DB->start_delegated_transaction();

        try {
            $dbMan = $DB->get_manager();

            if ($dbMan->table_exists('enrol_waitinglist_queue')) {
                // Search Criteria
                $params = array();
                $params['course']   = $courseId;
                $params['user']     = $userId;
                $params['queue']    = '99999';

                // SQL Instruction
                $sql = " SELECT	      ewq.id
                     FROM		  {enrol_waitinglist_queue}	ewq
                        LEFT JOIN {user_enrolments}			ue 	ON 	ue.enrolid 	= ewq.waitinglistid
                                                                AND	ue.userid 	= ewq.userid
                     WHERE	ewq.userid 		 = :user
                        AND	ewq.courseid 	 = :course
                        AND ewq.queueno 	!= :queue
                        AND ue.id IS NULL ";

                // Execute
                $rdo = $DB->get_record_sql($sql,$params);
                if ($rdo) {
                    // Deleted Instance
                    $DB->delete_records('enrol_waitinglist_queue',array('id' => $rdo->id));
                    $exit = true;
                }else {
                    $exit = false;
                }//if_rdo
            }//if_table_exist

            // Commit
            $trans->allow_commit();

            return $exit;
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//unwait_from_course

    /**
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
     *
     * Description
     * Add all courses connected with the user and where user is in the waiting list
     *
     * @param           Object $trackerUser     Tracker connected with user
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      03/11/2016
     * @author          eFaktor     (fbv)
     *
     */
    public static function print_tracker_info($trackerUser) {
        /* Variables    */
        $out_tracker = '';

        try {
            // Buttons - Download Report
            $out_tracker .= self::get_output_buttons();

            // Print Outcome Tracker
            $out_tracker .= self::print_outcome_tracker($trackerUser->competence);

            // Print Individual Tracker
            $out_tracker .= self::print_individual_tracker($trackerUser->completed,$trackerUser->not_completed,$trackerUser->inWaitList);

            return $out_tracker;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//print_tracker_info

    /**
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
     *
     * @param           Object  $tracker_competence Tracker connected with user
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     */
    public static function print_outcome_tracker($tracker_competence) {
        /* Variables    */
        $out_tracker        = '';
        $outcomeToogle      = null;
        $companyToggle      = null;
        $url_img            = new moodle_url('/pix/t/expanded.png');
        $title              = null;

        try {
            // Print all tracker
            foreach ($tracker_competence as $competence) {
                // Header Company
                $companyToggle = 'YUI_' . $competence->levelThree;

                // Company Name
                $out_tracker .= html_writer::start_tag('div',array('class' => 'header_tracker'));
                    $out_tracker .= '<h5>'. $competence->name . '</h5>';
                $out_tracker .= html_writer::end_tag('div');

                // Job Roles
                $out_tracker .= html_writer::start_tag('div',array('class' => 'header_tracker_jr'));
                    $out_tracker .= '<h6>' . self::get_jobroles_names($competence->job_roles) . '</h6>';
                $out_tracker .= html_writer::end_tag('div');

                // Add Outcome Tracker Info
                if ($competence->outcomes) {
                    foreach ($competence->outcomes as $id=>$outcome) {
                        // Tracker Info
                        $outcomeToogle = $companyToggle . '_' . $id;
                        $out_tracker .= html_writer::start_tag('div',array('class' => 'tracker_list'));
                            // Header Outcome tracker
                            $out_tracker .= html_writer::start_tag('div',array('class' => 'header_outcome_tracker'));
                                $out_tracker .= self::print_header_outcome_tracker($outcome->name,$outcomeToogle,$url_img);
                            $out_tracker .= html_writer::end_tag('div');//header_outcome_tracker

                            // Courses tracker Competence
                            $out_tracker .= html_writer::start_tag('div',array('class' => 'course_list','id' => $outcomeToogle . '_div'));
                                // Header Table
                                $outcomeToogle .= '_table';
                                $out_tracker .= self::add_header_courses_table();
                                // Content Table
                                $out_tracker .= self::add_content_courses_table($outcome);
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
    }//print_outcome_tracker

    /**
     * Description
     * Print the tracker connected to the individual courses - Screen Format
     *
     * Description
     * Add courses where user is in waiting list
     *
     * @param           array $completed        Courses completed
     * @param           array $not_completed    Courses no completed
     * @param           array $inWaitList       Course in waiting list
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      03/11/2016
     * @author          eFaktor     (fbv)
     */
    public static function print_individual_tracker($completed,$not_completed,$inWaitList) {
        /* Variables    */
        $out_tracker = '';
        $individualToogle   = 'YUI_' . '0';
        $url_img            = new moodle_url('/pix/t/expanded.png');
        $title              = get_string('individual_courses','local_tracker_manager');

        try {
            // Title
            $out_tracker .= html_writer::start_tag('div',array('class' => 'header_tracker'));
                $out_tracker .= '<h5>'. $title . '</h5>';
            $out_tracker .= html_writer::end_tag('div');

            // Tracker Info
            $out_tracker .= html_writer::start_tag('div',array('class' => 'tracker_list'));
                // Individual Courses
                $individualToogle .= '_table';
                $out_tracker .= html_writer::start_tag('div',array('class' => 'course_list'));
                    // Header Table
                    $out_tracker .= self::add_header_individual_courses_table($individualToogle,$url_img);
                    // Content Table
                    $out_tracker .= html_writer::start_tag('div',array('class' => 'course_list', 'id' => $individualToogle . '_div'));
                        $out_tracker .= self::add_content_individual_courses_table($completed,$not_completed,$inWaitList);
                    $out_tracker .= html_writer::end_tag('div');//course_list
                $out_tracker .= html_writer::end_tag('div');//course_list
            $out_tracker .= html_writer::end_tag('div');//tracker_list

            return $out_tracker;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//print_individual_tracker

    /**
     * Description
     * Download the Tracker report - Excel Format
     *
     * @param           Object $trackerUser     Tracker competence
     * @throws          Exception
     *
     * @creationDate    08/04/2015
     * @author          eFaktor     (fbv)
     */
    public static function download_tracker_report($trackerUser) {
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
            self::add_sheet_outcome_courses($export,$my_xls,$trackerUser->competence);

            /* Individual Courses   */
            $row = 0;
            self::add_sheet_individual_courses($export,$my_xls,$row,$trackerUser->completed,$trackerUser->not_completed);

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//download_tracker_report


    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
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
     *
     * @param           int     $user_id    User id
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_competence_tracker($user_id) {
        /* Variables    */
        global $DB;
        $myCompetence   = array();
        $competenceInfo = null;

        try {
            // Search Criteria
            $params = array();
            $params['user'] = $user_id;

            // SQL Instruction
            $sql = " SELECT		uicd.companyid,
                                rgc.industrycode,
                                rgc.name,
                                IF(uicd.jobroles,uicd.jobroles,0) as 'jobroles'
                     FROM		{user_info_competence_data} 	uicd
                        JOIN	{report_gen_companydata}		rgc		ON rgc.id = uicd.companyid
                     WHERE		uicd.userid = :user
                     ORDER BY	rgc.industrycode, rgc.name ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Competence Info
                    $info = new stdClass();
                    $info->levelThree   = $instance->companyid;
                    $info->name         = $instance->name;
                    $info->industrycode = $instance->industrycode;
                    $info->job_roles    = $instance->jobroles;
                    $info->outcomes     = self::get_info_outcomes_jobroles($info->job_roles);

                    // Add the company
                    if ($info->outcomes) {
                        $myCompetence[$instance->companyid] = $info;
                    }//if_outcomes
                }//for_instance_competence
            }//if_rdo

            return $myCompetence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//get_competence_tracker

    /**
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
     *
     * @param           string $jr_lst      List of job roles
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_info_outcomes_jobroles($jr_lst) {
        /* Variables    */
        global $DB;
        $outcomes   = array();
        $info       = null;

        try {
            // SQL Instruction
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

            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Outcome Info
                    $info = new stdClass();
                    $info->id               = $instance->id;
                    $info->name             = $instance->fullname;
                    $info->expiration       = $instance->expirationperiod;
                    $info->courses          = $instance->courses;
                    $info->roles            = $instance->job_roles;
                    $info->completed        = null;
                    $info->not_completed    = null;
                    $info->not_enrol        = null;

                    // Add outcome
                    if ($info->courses)  {
                        $outcomes[$instance->id] = $info;
                    }//if_courses
                }//for_instance_outcome
            }//if_rdo

            return $outcomes;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_info_outcomes_jobroles

    /**
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
     *
     * @param           int     $user_id        User id
     * @param           Object  $competence     Competence tracker connected with
     *
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_info_outcome_tracker($user_id,&$competence) {
        /* Variables    */
        $outcomesTracker    = null;
        $coursesEnrol       = null;

        try {
            // Get the outcome tracker
            $outcomesTracker = $competence->outcomes;
            if ($outcomesTracker) {
                foreach ($outcomesTracker as $id=>$outcome) {
                    // Get Courses Completed and Not Completed
                    list($coursesEnrol,$outcome->completed,$outcome->not_completed) = self::get_tracker_courses_enrol($user_id,$outcome->courses);

                    // Get Courses Not Enrol
                    if ($coursesEnrol) {
                        $coursesEnrol = implode(',',$coursesEnrol);
                    }else {
                        $coursesEnrol = 0;
                    }
                    $outcome->not_enrol = self::get_tracker_courses_not_enrol($outcome->courses,$coursesEnrol);

                    // Add competence
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
    }//get_info_outcome_tracker

    /**
     * Description
     * Add the courses completed and not completed connected with the user
     *
     * Completed / Not Completed
     *                          [id]
     *                              --> id
     *                              --> name
     *                              --> completed
     *
     * @param           int     $user_id    User id
     * @param           string  $courses    List of courses
     * @return          array
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_tracker_courses_enrol($user_id,$courses) {
        /* Variables    */
        global $DB;
        $completed      = array();
        $not_completed  = array();
        $enrol          = array();
        $info           = null;

        try {
            // Search Criteria
            $params = array();
            $params['user'] = $user_id;

            // SQL Instruction
            $sql = " SELECT	      c.id,
                                  c.fullname,
                                  IF (cc.timecompleted,cc.timecompleted,0) as 'completed'
                     FROM		  {course}					c
                        JOIN	  {enrol} 					e	ON 	e.courseid 	= c.id
                                                                AND	e.status 	= 0
                        JOIN	  {user_enrolments}			ue	ON 	ue.enrolid 	= e.id
                                                                AND	ue.status	= 0
                                                                AND ue.userid   = :user
                        LEFT JOIN {course_completions}		cc	ON	cc.course 	= e.courseid
                                                                AND cc.userid 	= ue.userid
                     WHERE		  c.id IN ($courses)
                        AND       c.visible = 1
                     ORDER BY	  c.fullname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Course Info
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->fullname;
                    $info->completed    = $instance->completed;

                    // Add course
                    if ($instance->completed) {
                        $completed[$instance->id] = $info;
                    }else {
                        $not_completed[$instance->id] = $info;
                    }//if_time_Completed

                    // Enrol
                    $enrol[$instance->id] = $instance->id;
                }//for_instance_courses
            }//if_rdo

            return array($enrol,$completed,$not_completed);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_tracker_courses_enrol

    /**
     * Description
     * Get the courses that the user are not enrolled
     *
     * Not Enrol
     *      [id]
     *          --> id
     *          --> name
     *
     * @param           string  $courses        List of course
     * @param           string  $coursesEnrol   List of courses enrolled
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_tracker_courses_not_enrol($courses,$coursesEnrol) {
        /* Variables    */
        global $DB;
        $not_enrol  = array();
        $info       = null;

        try {
            // SQL Instruction
            $sql = " SELECT	c.id,
                            c.fullname
                    FROM	{course}		  c
                    WHERE	c.id IN ($courses)
                      AND   c.visible = 1 ";

            // Courses Enrol
            if ($coursesEnrol) {
                $sql .= " AND c.id NOT IN ($coursesEnrol) ";
            }//if_coursesEnrol

            // Order
            $sql .= " ORDER BY	c.fullname ";

            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Course Info
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->fullname;

                    // Add course
                    $not_enrol[$instance->id] = $info;
                }//for_instance
            }//if_rdo

            return $not_enrol;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_tracker_courses_not_enrol

    /**
     * Description
     * Get the info tracker for all the courses not connected with the outcomes
     *
     * Completed / Not Completed
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> completed
     *
     * Check if the user can unenrol from the course
     *
     * Add deadline unenrol and ethodtype for waiting list enrolments, to check if the user
     * can unenrol or not
     *
     * Add all courses where user is in the waiting list
     *
     * @param           int     $user_id     User id
     * @param           array   $competence  Competence courses no connected
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    01/04/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      20/11/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      26/10/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      03/11/2016
     * @author          eFaktor     (fbv)
     *
     */
    private static function get_tracker_not_connected($user_id,$competence) {
        /* Variables    */
        global $DB;
        $dbMan          = null;
        $connected      = 0;
        $completed      = array();
        $not_completed  = array();
        $inWaitList     = null;
        $info           = null;
        $user           = null;
        $params         = null;
        $rdo            = null;
        $sql            = null;
        $sqlWhere       = null;
        $sqlLeft        = null;
        $sqlFrom        = null;

        try {
            $dbMan = $DB->get_manager();

            // Complete user data
            $user = get_complete_user_data('id',$user_id);

            // Get Courses Not Connected
            foreach ($competence as $levelThree) {
                if ($levelThree->outcomes) {
                    foreach ($levelThree->outcomes as $outcome) {
                        if ($outcome->courses) {
                            $connected .= ',' . $outcome->courses;
                        }//if_courses
                    }//for_outomes
                }//if_outcomes
            }//if_levelThree

            // Search Criteria
            $params = array();
            $params['user']     = $user_id;
            $params['ue_user']  = $user_id;

            // SQL Instruction
            $sql = " SELECT	      c.id,
                                  c.fullname,
                                  IF (cc.timecompleted,cc.timecompleted,0)                                        as 'completed',
                                  GROUP_CONCAT(DISTINCT CONCAT(e.enrol,'#',e.id) ORDER BY e.enrol SEPARATOR ',')  as 'enrolments' ";

            $sqlFrom    = "  FROM		  {course}					  c
                                LEFT JOIN {course_completions}  	  cc  ON	cc.course           = c.id
                                                                          AND   cc.userid           = :user
                                JOIN	  {enrol} 					  e	  ON 	e.courseid 	        = c.id
                                                                          AND	e.status 	        = 0
                                JOIN	  {user_enrolments}			  ue  ON 	ue.enrolid 	        = e.id
                                                                          AND	ue.status	        = 0
                                                                          AND   ue.userid           = :ue_user ";
            // Sql Left
            if ($dbMan->table_exists('enrol_waitinglist_method')) {
                $sql .= "  ,ewq.unenrolenddate,
                            ewq.methodtype ";

                $sqlLeft = " LEFT JOIN {enrol_waitinglist_method}  ewq  ON    ewq.waitinglistid   = e.id
													                    AND   ewq.methodtype like 'self'
                                                                        AND   ewq.status		  =  1 ";
            }//if_exist

            // SQL Where
            $sqlWhere = " WHERE		c.id NOT IN ($connected)
                            AND     c.visible = 1
                          GROUP BY	c.id
                          ORDER BY	c.fullname ";

            // Execute
            $sql .= $sqlFrom . $sqlLeft . $sqlWhere;
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Course Info
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->fullname;
                    $info->completed    = $instance->completed;
                    if (!$instance->completed) {
                        if (isset($instance->methodtype)) {
                            $info->unEnrol = self::check_can_unenrol(explode(',',$instance->enrolments),$user,$instance->id,$instance->unenrolenddate,$instance->methodtype);
                        }else {
                            $info->unEnrol = self::check_can_unenrol(explode(',',$instance->enrolments),$user,$instance->id,null,null);
                        }
                    }else {
                        $info->unEnrol = false;
                    }//if_completed

                    // Add course
                    if ($instance->completed) {
                        $completed[$instance->id] = $info;
                    }else {
                        $not_completed[$instance->id] = $info;
                    }//if_time_Completed
                }//for_instance
            }//if_rdo

            /**
             * Get courses connected where user is not enrolled,
             * but the user is in the waiting list
             */
            if ($dbMan->table_exists('enrol_waitinglist_queue')) {
                $inWaitList = self::get_tracker_waitinglist($user_id);
            }

            return array($completed,$not_completed,$inWaitList);
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//get_tracker_not_connected

    /**
     * Description
     * Get all courses connected with user, where the user is in the waiting list
     *
     * @param           int $userId     User id
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    03/11/2016
     * @author          eFaktor     (fbv)
     */
    private static function get_tracker_waitinglist($userId) {
        /* Variables */
        global $DB;
        $inWaitList     = array();
        $info           = null;
        $rdo            = null;
        $sql            = null;
        $params         = null;

        try {
            // Search Criteria
            $params = array();
            $params['user']     = $userId;
            $params['queue']    = '99999';
            $params['visible']  = 1;

            // SQL Instruction
            $sql = " SELECT	DISTINCT 
                                  c.id,
                                  c.fullname
                     FROM		  {course}					c
                        JOIN	  {enrol_waitinglist_queue}	ewq	ON  ewq.courseid  = c.id
                                                                AND	ewq.userid    = :user
                                                                AND ewq.queueno  != :queue
						LEFT JOIN {user_enrolments}		    ue 	ON 	ue.enrolid 	= ewq.waitinglistid
																AND	ue.userid 	= ewq.userid
                     WHERE	c.visible = :visible 
                        AND ue.id IS NULL ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Course Info
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->fullname;

                    // Add course
                    $inWaitList[$instance->id] = $info;
                }//for_instance
            }//if_Rdo

            return $inWaitList;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_tracker_waitinglist

    /**
     * Description
     * Check if the user can enrol by himsef/herslef
     *
     * add deadline unenrol and sub-method type to check if the user
     * can unenrol or not
     *
     * @param           array   $enrolMethods   List of enrol methods
     * @param           Object  $user           User data
     * @param           int     $courseId       Course id
     * @param           int     $unEnrolDate    deadline to unenrol
     * @param           string  $methodType     sub enrol method
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/11/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      26/10/2016
     * @auhtor          eFaktor     (fbv)
     */
    private static function check_can_unenrol($enrolMethods,$user,$courseId,$unEnrolDate,$methodType) {
        /* Variables    */
        global $DB;
        $method         = null;
        $instance       = null;
        $plugin         = null;
        $context        = context_course::instance($courseId);
        $unEnrol        = true;
        $time           = null;
        $ue             = null;
        $sql            = null;
        $rdo            = null;

        try {
            // User enrolment instance
            $sql = " SELECT	  ue.*
                     FROM	  {enrol}				e
                        JOIN  {user_enrolments}	    ue	ON ue.enrolid = e.id
                                                        AND ue.userid = :user
                     WHERE  e.courseid = :course
                        AND e.status = 0 ";

            $rdo = $DB->get_records_sql($sql,array('user' => $user->id,'course' => $courseId));

            // Local time
            $time = time();

            foreach ($enrolMethods as $enrol) {
                $method = explode('#',$enrol);

                // get enrol info
                $plugin = enrol_get_plugin($method[0]);
                $instance = new stdClass();
                $instance->id       = $method[1];
                $instance->courseid = $courseId;
                $instance->enrol    = $method[0];

                $capability = 'enrol/' . $method[0] . ':unenrol';

                if (($method[0] == 'waitinglist') && $methodType == 'self') {
                    if ($unEnrolDate) {
                        if ($time < $unEnrolDate) {
                            $unEnrol = $unEnrol && true;
                        }else {
                            $unEnrol = false;
                        }
                    }else {
                        $unEnrol = $unEnrol && true;
                    }//if_unEnrolDate
                }else {
                    if ($rdo) {
                        foreach ($rdo as $ue) {
                            if ($plugin->allow_unenrol_user($instance,$ue) && has_capability($capability, $context)) {
                        $unEnrol  = $unEnrol && true;
                    }else {
                        $unEnrol = false;
                    }
                        }//for_rdo
                    }//if_rdo
                }//if_waitinglist_self
            }//for

            return $unEnrol;
        }catch (Exception $ex) {
            throw $ex;
        }//try_cathc
    }//check_can_unenrol

    /**
     * Description
     * Get the job roles names
     *
     * @param           string $job_roles   List of jobroles
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    21/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_jobroles_names($job_roles) {
        /* Variables    */
        global $DB;
        $jr_names = null;

        try {
            if ($job_roles) {
                // SQL Instruction
                $sql = " SELECT		id,
                                    CONCAT(industrycode,' - ',name) as 'name'
                         FROM		{report_gen_jobrole}
                         WHERE		id IN ($job_roles)
                         ORDER BY	industrycode, name ";

                // Execute
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
    }//get_jobroles_names

    /**
     * Description
     * Add the output buttons to download the report
     *
     * @return          string
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function get_output_buttons() {
        $url_dwn = new moodle_url('/report/manager/tracker/index.php',array('pdf'=>TRACKER_PDF_DOWNLOAD));
        $send_pdf_btn   = html_writer::start_tag('div',array('class' => 'div_button_tracker'));
            $send_pdf_btn .= html_writer::link($url_dwn,get_string('download_pdf_btn','local_tracker_manager'),array('class' =>"button_tracker"));
        $send_pdf_btn  .= html_writer::end_tag('div');

        return $send_pdf_btn;
    }//get_output_buttons

    /**
     * Description
     * Add the outcome header - Screen Format
     *
     * @param           $outcome
     * @param           $toogle
     * @param           $img
     *
     * @return          string
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function print_header_outcome_tracker($outcome,$toogle,$img) {
        /* Variables    */
        $header     = null;

        $header .= html_writer::start_div('header_outcome_company_rpt');
            // Col One
            $header .= html_writer::start_div('header_col_one');
                $header .= '<button class="toggle_header_tracker" type="image" id="' . $toogle . '">
                                <img id="' . $toogle . '_img' . '" src="' . $img . '">
                            </button>';
            $header .= html_writer::end_div();//header_col_one
            // Col Two
            $header .= html_writer::start_div('header_col_two');
                $header .= '<h6>' . $outcome . '</h6>';
            $header .= html_writer::end_div();//header_col_two
        $header .= html_writer::end_div();//header_outcome_company_rpt

        return $header;
    }//print_header_outcome_tracker

    /**
     * Description
     * Add the header to courses table - Screen Format
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_header_courses_table() {
        /* Variables    */
        $header = '';
        $strCourse         = get_string('course');
        $strState          = get_string('state','local_tracker_manager');
        $strValid          = get_string('outcome_valid_until','local_tracker_manager');
        $strCompletion     = get_string('completion_time','local_tracker_manager');

        try {
            // Build Header
            $header .= html_writer::start_tag('table');
                $header .= html_writer::start_tag('tr',array('class' => 'head'));
                    // Empty Col
                    $header .= html_writer::start_tag('th',array('class' => 'head_first'));
                    $header .= html_writer::end_tag('th');
                    // Course
                    $header .= html_writer::start_tag('th',array('class' => 'head_course'));
                        $header .= $strCourse;
                    $header .= html_writer::end_tag('th');
                    // Status
                    $header .= html_writer::start_tag('th',array('class' => 'head_status'));
                        $header .= $strState;
                    $header .= html_writer::end_tag('th');
                    // Completion
                    $header .= html_writer::start_tag('th',array('class' => 'head_status'));
                        $header .= $strCompletion;
                    $header .= html_writer::end_tag('th');
                    // Valid
                    $header .= html_writer::start_tag('th',array('class' => 'head_status'));
                        $header .= $strValid;
                    $header .= html_writer::end_tag('th');
                    // Last Col
                    $header .= html_writer::start_tag('th',array('class' => 'head_first'));
                        $header .= '&nbsp;';
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('table');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_courses_table

    /**
     * Description
     * Add the content table - Screen Format
     *
     * @param           $outcome
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_content_courses_table($outcome) {
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
            $strCourse         = get_string('course');
            $strState          = get_string('state','local_tracker_manager');
            $strValid          = get_string('outcome_valid_until','local_tracker_manager');
            $strCompletion     = get_string('completion_time','local_tracker_manager');

            $content .= html_writer::start_tag('table');
            // Not Completed
            if ($outcome->not_completed) {
                $not_completed = $outcome->not_completed;
                foreach ($not_completed as $course) {
                    // Course Url
                    $url     = new moodle_url('/course/view.php',array('id'=>$course->id,'start' =>1));

                    $content .= html_writer::start_tag('tr');
                        // Empty Col
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        // Course
                        $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $strCourse));
                            if (strlen($course->name) <= 100) {
                                $nameTruncate = $course->name;
                            }else {
                                $nameTruncate = substr($course->name,0,100);
                                $index = strrpos($nameTruncate,' ');
                                $nameTruncate = substr($nameTruncate,0,$index) . ' ...';
                            }
                            $content .= '<a href="'.$url .'">'. $nameTruncate .'</a>';
                        $content .= html_writer::end_tag('td');
                        // Status
                        $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strState));
                            $content .= get_string('outcome_course_started','local_tracker_manager');
                        $content .= html_writer::end_tag('td');
                        // Completion
                        $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strCompletion));
                            $content .= '-';
                        $content .= html_writer::end_tag('td');
                        // Valid
                        $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strValid));
                            $content .= '&nbsp;';
                        $content .= html_writer::end_tag('td');
                        // Last Col
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_each_course_not_completed
            }//if_not_completed

            // Not Enrol
            if ($outcome->not_enrol) {
                $not_enrol = $outcome->not_enrol;
                foreach ($not_enrol as $course) {
                    // Url Course
                    $url     = new moodle_url('/course/view.php',array('id'=>$course->id));

                    $content .= html_writer::start_tag('tr',array('class' => 'not_enroll'));
                        // Empty Col
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        // Course
                        $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $strCourse));
                            if (strlen($course->name) <= 100) {
                                $nameTruncate = $course->name;
                            }else {
                                $nameTruncate = substr($course->name,0,100);
                                $index = strrpos($nameTruncate,' ');
                                $nameTruncate = substr($nameTruncate,0,$index) . ' ...';
                            }
                            $content .= '<a href="'.$url .'">'. $nameTruncate .'</a>';
                        $content .= html_writer::end_tag('td');
                        // Status
                        $content .= html_writer::start_tag('td',array('class' => 'status not_enroll','data-th' => $strState));
                            $content .= get_string('outcome_course_not_enrolled','local_tracker_manager');
                        $content .= html_writer::end_tag('td');
                        // Completion
                        $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strCompletion));
                            $content .= '-';
                        $content .= html_writer::end_tag('td');
                        // Valid
                        $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strValid));
                            $content .= '&nbsp;';
                        $content .= html_writer::end_tag('td');
                        // Last Col
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_each_course_not_enrol
            }//if_not_enrol

            // Completed
            if ($outcome->completed) {
                $completed = $outcome->completed;
                foreach ($completed as $course) {
                    // Url Course
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
                        // Empty Col
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        // Course
                        $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $strCourse));
                            if (strlen($course->name) <= 100) {
                                $nameTruncate = $course->name;
                            }else {
                                $nameTruncate = substr($course->name,0,100);
                                $index = strrpos($nameTruncate,' ');
                                $nameTruncate = substr($nameTruncate,0,$index) . ' ...';
                            }
                            $content .= '<a href="'.$url .'">'. $nameTruncate .'</a>';
                        $content .= html_writer::end_tag('td');
                        // Status
                        $content .= html_writer::start_tag('td',array('class' => 'status ' . $class,'data-th' => $strState));
                            $content .= $label;
                        $content .= html_writer::end_tag('td');
                        // Completion
                        $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strCompletion));
                            $content .= userdate($course->completed,'%d.%m.%Y', 99, false);
                        $content .= html_writer::end_tag('td');
                        // Valid
                        $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strValid));
                            $content .= userdate($ts,'%d.%m.%Y', 99, false);
                        $content .= html_writer::end_tag('td');
                        // Last Col
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
    }//add_content_courses_table

    /**
     * Description
     * Add the header to individual courses table - Screen Format
     *
     * @param           $toggle
     * @param           $url
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_header_individual_courses_table($toggle,$url) {
        /* Variables    */
        $header = '';
        $strCourse         = get_string('course');
        $strState          = get_string('state','local_tracker_manager');
        $strCompletion     = get_string('completion_time','local_tracker_manager');

        try {
            // Build Header
            $header .= html_writer::start_tag('table');
                $header .= html_writer::start_tag('tr',array('class' => 'head'));
                    // Empty Col
                    $header .= html_writer::start_tag('th',array('class' => 'head_first'));
                        $header .= html_writer::start_tag('button',array('id' => $toggle, 'class' => 'toggle', 'type' => 'image'));
                        $header .= html_writer::start_tag('img',array('src' => $url,'id' => $toggle . '_img'));
                        $header .= html_writer::end_tag('img');
                        $header .= html_writer::end_tag('button');
                    $header .= html_writer::end_tag('th');
                    // Course
                    $header .= html_writer::start_tag('th',array('class' => 'head_course'));
                        $header .= $strCourse;
                    $header .= html_writer::end_tag('th');
                    // Status
                    $header .= html_writer::start_tag('th',array('class' => 'head_status'));
                        $header .= $strState;
                    $header .= html_writer::end_tag('th');
                    // Completion
                    $header .= html_writer::start_tag('th',array('class' => 'head_status'));
                        $header .= $strCompletion;
                    $header .= html_writer::end_tag('th');
                    // Valid
                    $header .= html_writer::start_tag('th',array('class' => 'head_status'));
                        $header .= '&nbsp;';
                    $header .= html_writer::end_tag('th');
                    // Last Col
                    $header .= html_writer::start_tag('th',array('class' => 'head_first'));
                        $header .= '&nbsp;';
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('table');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_individual_courses_table

    /**
     * Description
     * Add the content to individual courses table - Screen Format
     *
     * Add the link to unerol
     *
     * Add courses where user is in the waiting list
     *
     * @param           $completed
     * @param           $not_completed
     * @param           $inWaitList
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      20/11/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      03/11/2016
     * @author          eFaktor     (fbv)
     */
    private static function add_content_individual_courses_table($completed,$not_completed,$inWaitList) {
        /* Variables    */
        $content        = '';
        $url            = null;
        $strUrl         = null;
        $urlUnEnrol     = new moodle_url('/report/manager/tracker/unenrol.php');
        $nameTruncate   = null;
        // Headers
        $strCourse         = get_string('course');
        $strState          = get_string('state','local_tracker_manager');
        $strCompletion     = get_string('completion_time','local_tracker_manager');

        try {
            $content .= html_writer::start_tag('table');
                // Not Completed
                if ($not_completed) {
                    foreach ($not_completed as $course) {
                        // Course Url
                        $url     = new moodle_url('/course/view.php',array('id'=>$course->id,'start'=>1));

                        $content .= html_writer::start_tag('tr');
                            // Empty Col
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            // Course
                            $content .= html_writer::start_tag('td',array('class' => 'course','data-th' =>$strCourse));
                                if (strlen($course->name) <= 100) {
                                    $nameTruncate = $course->name;
                                }else {
                                    $nameTruncate = substr($course->name,0,100);
                                    $index = strrpos($nameTruncate,' ');
                                    $nameTruncate = substr($nameTruncate,0,$index) . ' ...';
                                }
                                $content .= '<a href="'.$url .'">'. $nameTruncate .'</a>';
                            $content .= html_writer::end_tag('td');
                            // Status
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' =>$strState));
                                $content .= get_string('outcome_course_started','local_tracker_manager');
                            $content .= html_writer::end_tag('td');
                            // Completion
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' =>$strCompletion));
                                $content .= '-';
                            $content .= html_writer::end_tag('td');
                            // Valid
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' =>' '));
                                // Unenrol allowed --> add link to unenrol
                                if ($course->unEnrol) {
                                    $urlUnEnrol->param('id',$course->id);
                                    $strUrl  = '<a href="'.$urlUnEnrol .'">'. get_string('unenrol','report_manager') .'</a>';
                                    $content .= $strUrl;
                                }else {
                                    $content .= '&nbsp;';
                                }//if_unenrol
                            $content .= html_writer::end_tag('td');
                            // Last Col
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//for_each_course_not_completed
                }//if_not_completed

                // Completed
                if ($completed) {
                    foreach ($completed as $course) {
                        // Course Url
                        $url     = new moodle_url('/course/view.php',array('id'=>$course->id,'start'=>1));

                        $content .= html_writer::start_tag('tr',array('class' => 'completed'));
                            // Empty Col
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            // Course
                            $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $strCourse));
                                if (strlen($course->name) <= 100) {
                                    $nameTruncate = $course->name;
                                }else {
                                    $nameTruncate = substr($course->name,0,100);
                                    $index = strrpos($nameTruncate,' ');
                                    $nameTruncate = substr($nameTruncate,0,$index) . ' ...';
                                }
                                $content .= '<a href="'.$url .'">'. $nameTruncate .'</a>';
                            $content .= html_writer::end_tag('td');
                            // Status
                            $content .= html_writer::start_tag('td',array('class' => 'status completed','data-th' => $strState));
                                $content .= get_string('outcome_course_finished','local_tracker_manager');
                            $content .= html_writer::end_tag('td');
                            // Completion
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strCompletion));
                                $content .= userdate($course->completed,'%d.%m.%Y', 99, false);;
                            $content .= html_writer::end_tag('td');
                            // Valid
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                // Unenrol allowed --> add link to unenrol
                                $content .= '&nbsp;';
                            $content .= html_writer::end_tag('td');
                            // Last Col
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//for_each_course_completed
                }//if_completed

                // Waiting List
                if ($inWaitList) {
                    foreach ($inWaitList as $course) {
                        // Course Url
                        $url     = new moodle_url('/course/view.php',array('id'=>$course->id,'start'=>1));

                        $content .= html_writer::start_tag('tr',array('class' => 'not_enroll'));
                            // Empty Col
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                                // Course
                                $content .= html_writer::start_tag('td',array('class' => 'course','data-th' => $strCourse));
                                if (strlen($course->name) <= 100) {
                                    $nameTruncate = $course->name;
                                }else {
                                    $nameTruncate = substr($course->name,0,100);
                                    $index = strrpos($nameTruncate,' ');
                                    $nameTruncate = substr($nameTruncate,0,$index) . ' ...';
                                }
                                $content .= '<a href="'.$url .'">'. $nameTruncate .'</a>';
                            $content .= html_writer::end_tag('td');
                            // Status
                            $content .= html_writer::start_tag('td',array('class' => 'status not_enroll','data-th' => $strState));
                                $content .= get_string('tracker_on_wait','report_manager');
                            $content .= html_writer::end_tag('td');
                            // Completion
                            $content .= html_writer::start_tag('td',array('class' => 'status','data-th' => $strCompletion));
                                $content .= '-';
                            $content .= html_writer::end_tag('td');
                            // Valid
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                // Add link to cancel the enrol request
                                $urlUnEnrol->param('id',$course->id);
                                $urlUnEnrol->param('w',1);
                                $strUrl  = '<a href="'.$urlUnEnrol .'">'. get_string('cancel') .'</a>';
                                $content .= $strUrl;
                            $content .= html_writer::end_tag('td');
                            // Last Col
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');//tr
                    }//for_waiting_list
                }//if_waitingList
            $content .= html_writer::end_tag('table');

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_content_individual_courses_table

    /**
     * Description
     * Create a new sheet for the outcome courses. One sheet by company
     *
     * @param           $excel
     * @param           $my_xls
     * @param           $competence
     * @throws          Exception
     *
     * @creationDate    08/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_sheet_outcome_courses(&$excel,&$my_xls,$competence) {
        /* Variables    */
        $outcomes   = null;
        $row        = null;

        try {
            // One Company --> One sheet
            foreach ($competence as $levelThree) {
                $row = 0;
                // Adding the worksheet
                $my_xls = $excel->add_worksheet($levelThree->industrycode . ' - ' . $levelThree->name);

                // Add Header - Outcome Courses
                self::add_header_sheet_outcome_courses($my_xls,$row);

                if ($levelThree->outcomes) {
                    $row ++;
                    $outcomes = $levelThree->outcomes;
                    foreach ($outcomes as $outcome) {
                        // Add Content - Outcome course
                        self::add_content_sheet_outcome_courses($my_xls,$row,$outcome);

                        $my_xls->merge_cells($row,0,$row,17);
                        $row ++;
                    }//for_each_outcome
                }//if_outcomes
            }//for_levelThree
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_sheet_outcome_courses

    /**
     * Description
     * Add the header to the company outcome course sheet
     *
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    08/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_header_sheet_outcome_courses(&$my_xls,&$row) {
        /* Variables    */
        $strOutcome         = get_string('outcome', 'report_manager');
        $strCourse          = get_string('course');
        $strState           = get_string('state','local_tracker_manager');
        $strValid           = get_string('outcome_valid_until','local_tracker_manager');
        $strCompletion      = get_string('completion_time','local_tracker_manager');
        $col                = 0;

        try {
            // Outcome
            $my_xls->write($row, $col, $strOutcome,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // Course
            $col = $col + 6;
            $my_xls->write($row, $col, $strCourse,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // State
            $col = $col + 6;
            $my_xls->write($row, $col, $strState,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Completion
            $col = $col + 2;
            $my_xls->write($row, $col, $strCompletion,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Valid
            $col = $col + 2;
            $my_xls->write($row, $col, $strValid,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_sheet_outcome_courses

    /**
     * Description
     * Add the content to the company outcome courses sheet
     *
     * @param           $my_xls
     * @param           $row
     * @param           $outcome
     * @throws          Exception
     *
     * @creationDate    08/04/2015
     * @author          eFaktor         (fbv)
     */
    private static function add_content_sheet_outcome_courses(&$my_xls,&$row,$outcome) {
        /* Variables    */
        $not_completed  = null;
        $completed      = null;
        $not_enrol      = null;
        $bg_color       = null;
        $state          = null;
        $col            = null;

        try {
            // Not Completed
            if ($outcome->not_completed) {
                $state          = get_string('outcome_course_started','local_tracker_manager');
                $not_completed  = $outcome->not_completed;
                foreach ($not_completed as $course) {
                    $col = 0;

                    // Outcome
                    $my_xls->write($row, $col, $outcome->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    // Course
                    $col = $col + 6;
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    // State
                    $col = $col + 6;
                    $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    // Completion
                    $col = $col + 2;
                    $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    // Valid
                    $col = $col + 2;
                    $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    $row++;
                }//for_course
            }//if_not_completed

            // Not Enrol
            if ($outcome->not_enrol) {
                $state      = get_string('outcome_course_not_enrolled','local_tracker_manager');
                $bg_color   = '#fcf8e3';
                $not_enrol  = $outcome->not_enrol;
                foreach ($not_enrol as $course) {
                    $col = 0;

                    // Outcome
                    $my_xls->write($row, $col, $outcome->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    // Course
                    $col = $col + 6;
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    // State
                    $col = $col + 6;
                    $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    // Completion
                    $col = $col + 2;
                    $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    // Valid
                    $col = $col + 2;
                    $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    $row++;
                }//for_Each_not_enrol
            }//if_not_enrol

            // Completed
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

                    // Outcome
                    $my_xls->write($row, $col, $outcome->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    // Course
                    $col = $col + 6;
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    // State
                    $col = $col + 6;
                    $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    // Completion
                    $col = $col + 2;
                    $my_xls->write($row, $col, userdate($course->completed,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    // Valid
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
    }//add_content_sheet_outcome_courses

    /**
     * Description
     * Add a new sheet for the individual courses
     *
     * @param           $excel
     * @param           $my_xls
     * @param           $row
     * @param           $completed
     * @param           $not_completed
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_sheet_individual_courses(&$excel,&$my_xls,&$row,$completed,$not_completed) {
        try {
            // Adding the worksheet
            $my_xls = $excel->add_worksheet(get_string('individual_courses','local_tracker_manager'));

            // Add Header - Individual Courses
            self::add_header_sheet_individual_courses($my_xls,$row);

            // Add Content - Individual Courses
            $row++;
            self::add_content_sheet_individual_courses($completed,$not_completed,$my_xls,$row);

            $my_xls->merge_cells($row,0,$row,9);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_sheet_individual_courses

    /**
     * Description
     * Add the header to the individual courses sheet
     *
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_header_sheet_individual_courses(&$my_xls,$row) {
        /* Variables    */
        $strCourse         = get_string('course');
        $strState          = get_string('state','local_tracker_manager');
        $strCompletion     = get_string('completion_time','local_tracker_manager');
        $col               = 0;

        try {
            // Course
            $my_xls->write($row, $col, $strCourse,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // State
            $col = $col + 6;
            $my_xls->write($row, $col, $strState,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            // Completion
            $col = $col + 2;
            $my_xls->write($row, $col, $strCompletion,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_sheet_individual_courses

    /**
     * Description
     * Add the content to the individual courses sheet
     *
     * @param           $completed
     * @param           $not_completed
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    07/04/2015
     * @author          eFaktor     (fbv)
     */
    private static function add_content_sheet_individual_courses($completed,$not_completed,&$my_xls,&$row) {
        /* Variables    */
        $col        = null;
        $state      = null;
        $bg_color   = '#dff0d8';

        try {
            // Not Completed
            if ($not_completed) {
                $state = get_string('outcome_course_started','local_tracker_manager');
                foreach ($not_completed as $course) {
                    $col = 0;

                    // Course
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    // State
                    $col = $col + 6;
                    $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    // Completion
                    $col = $col + 2;
                    $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    $row++;
                }//for_Each_not_completed
            }//if_not_completed

            // Completed
            if ($completed) {
                $state = get_string('outcome_course_finished','local_tracker_manager');
                foreach ($completed as $course) {
                    $col = 0;

                    // Course
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    // State
                    $col = $col + 6;
                    $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    // Completion
                    $col = $col + 2;
                    $my_xls->write($row, $col,userdate($course->completed,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+1);
                    $my_xls->set_row($row,20);

                    $row++;
                }//for_Each_completed
            }//if_completed
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_content_sheet_individual_courses
}//TrackerManager