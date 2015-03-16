<?php
/**
 * Events Completion Course - Completion Activity  Course
 *
 * @package         local
 * @subpackage      completion/db
 * @copyright       2014 eFaktor    {@link https://www.efaktor.no}
 *
 * @creationDate    22/04/2014
 * @author          eFaktor     (fbv)
 *
 */

require_once($CFG->dirroot . '/completion/cron.php');
require_once($CFG->dirroot . '/lib/completionlib.php');
require_once($CFG->dirroot . '/completion/completion_completion.php');
require_once($CFG->dirroot . '/completion/completion_criteria_completion.php');
require_once($CFG->dirroot . '/completion/criteria/completion_criteria_date.php');
require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');
require_once($CFG->dirroot . '/completion/criteria/completion_criteria_duration.php');
require_once($CFG->dirroot . '/completion/criteria/completion_criteria_grade.php');
require_once($CFG->dirroot . '/completion/criteria/completion_criteria_course.php');
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/querylib.php';

require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/cronlib.php');


/**
 * @param           $event_data
 * @throws          Exception
 *
 * @creationDate    23/09/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Capture and handle the 'course_completion_updated' event
 */
function local_completion_handle_course_completion_updated($event_data) {
    /* Variables    */
    global $COURSE,$DB,$SESSION;
    $rdo_update         = null;

    try {
        if (!isset($SESSION->send)) {
            $SESSION->send = false;
        }

        local_completion_cron_mark_started($COURSE->id);

        $completion_criteria_date = new completion_criteria_date();
        $completion_criteria_activity = new completion_criteria_activity();
        $completion_criteria_duration = new completion_criteria_duration();
        $completion_criteria_grade = new completion_criteria_grade();
        $completion_criteria_course = new completion_criteria_course();

        $completion_criteria_date->cron();
        $completion_criteria_activity->cron();
        $completion_criteria_duration->cron();
        $completion_criteria_grade->cron();
        $completion_criteria_course->cron();


        $users_enrol = get_enrolled_users(context_course::instance($COURSE->id));
        foreach ($users_enrol as $user) {
            /* To complete      */
            $to_completed = local_completion_getCriteriasToComplete($COURSE->id);
            /* User Completions */
            $user_completions = local_completion_getCompletionUser($COURSE->id,$user->id);

            if ($to_completed == $user_completions) {
                /* Info User Completion     */
                $completion_info = local_completion_getCompletionInfoUser($COURSE->id,$event_data->userid);
                /* Update Completion Date   */
                if ($completion_info) {
                    if ($completion_info->reaggregate) {
                        $completion_info->timecompleted = $completion_info->reaggregate;
                        $completion_info->reaggregate = 0;
                        $rdo_update = $DB->update_record('course_completions',$completion_info);
                    }//if_completion_reaggregate
                }//if_completion_info

                /* Call Web Service */
                if ($rdo_update) {
                    if (!$SESSION->send) {
                        local_completion_sendCompletionToDossier($completion_info);
                        $SESSION->send = true;
                    }else {
                        unset($SESSION->send);
                    }
                }//if_rdo_update
            }//if_to_completed
        }//for_users
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_completion_handle_course_completion_updated

/**
 * @param           $event_data
 * @throws          Exception
 *
 * @creationDate    23/09/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Capture and handle the 'activity_completion_changed' event
 */
function local_completion_handle_activity_completion_changed($event_data) {
    /* Variables    */
    global $COURSE,$DB,$SESSION;
    $rdo_update         = null;

    try {
        if (!isset($SESSION->send)) {
            $SESSION->send = false;
        }

        /* Activities to complete   */
        $to_completed = local_completion_getCriteriasToComplete($COURSE->id);

        if ($to_completed) {
            local_completion_cron_mark_started($COURSE->id);

            /* Get User Completions */
            $user_completions = local_completion_getCompletionUser($COURSE->id,$event_data->userid);

            if ($to_completed == $user_completions) {
                /* Completion Criteria Date Cron        */
                local_completion_criteria_date_cron($COURSE->id,$event_data->userid);
                /* Completion Criteria Activity Cron    */
                local_completion_criteria_activity_cron($COURSE->id,$event_data->userid);
                /* Completion Criteria Duration Cron    */
                local_completion_criteria_duration_cron($COURSE->id,$event_data->userid);
                /* Completion Criteria Grade Cron       */
                local_completion_criteria_grade_cron($COURSE->id,$event_data->userid);
                /* Completion Criteria Course           */
                local_completion_criteria_course_cron($COURSE->id,$event_data->userid);

                /* Update Completion Date   */
                /* Info User Completion     */
                $completion_info = local_completion_getCompletionInfoUser($COURSE->id,$event_data->userid);
                if ($completion_info) {
                    if ($completion_info->reaggregate) {
                        $completion_info->timecompleted = $completion_info->reaggregate;
                        $completion_info->reaggregate = 0;
                        $rdo_update = $DB->update_record('course_completions',$completion_info);
                    }//if_completion_reaggregate
                }//if_completion_info

                /* Call Web Service */
                if ($rdo_update) {
                    if (!$SESSION->send) {
                        local_completion_sendCompletionToDossier($completion_info);
                        $SESSION->send = true;
                    }else {
                        unset($SESSION->send);
                    }
                }//if_rdo_update
            }//to_completed = user_completions
        }//if_to_compelted
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_completion_handle_activity_completion_changed

/**
 * @param           $event_data
 * @throws          Exception
 *
 * @creationDate    09/02/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Capture the event quiz attempt submitted. To update the completion date
 */
function local_completion_handle_quiz_attempt_submitted($event_data) {
    /* Variables    */
    global $DB,$COURSE;
    $completion_info    = null;
    $expiration_period  = null;
    $expiration_date    = null;

    try {
        /* Activity Completion          */
        $event = $DB->get_record('course_modules_completion',array('coursemoduleid' => $event_data->cmid,'userid' => $event_data->userid));

        /* Get Completion Info  */
        $completion_info = local_completion_getCompletionInfoUser($COURSE->id,$event_data->userid);
        if ($completion_info) {
            if ($completion_info->timecompleted) {
                $completion_info->timecompleted = 0;
                $rdo_update = $DB->update_record('course_completions',$completion_info);

                /* Grade Criteria   */
                $completion_criteria = local_completion_getGradeCriteria($COURSE->id);
                $criteria_compl = $DB->get_record('course_completion_crit_compl',array('userid' => $event_data->userid,'course' => $COURSE->id,'criteriaid' => $completion_criteria));
                if ($criteria_compl) {
                    /* Delete Grade */
                    $DB->delete_records('course_completion_crit_compl',array('id' => $criteria_compl->id));
                }//if_Grade
            }//completed
        }//completion_nfo

        local_completion_handle_activity_completion_changed($event);
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_completion_handle_quiz_attempt_submitted

/**
 * @param           $user_id
 * @param           $expiration_period
 * @param           $completion_info
 * @throws          Exception
 *
 * @creationDate    10/02/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Update the status for the expired courses.
 */
function local_completion_UpdateStatusCourseExpired($user_id,$expiration_period,&$completion_info) {
    /* Variables */
    global $COURSE,$DB;
    $expiration_date    = null;

    try {
        if ($completion_info) {
            if ($completion_info->timecompleted) {
                /* Check if the course has expired  */
                $expiration_date = $completion_info->timecompleted;
                $ts = strtotime($expiration_period  . ' month', $expiration_date);
                if ($ts < time()) {
                    $completion_info->timecompleted = 0;
                    $rdo_update = $DB->update_record('course_completions',$completion_info);

                    /* Grade Criteria   */
                    $completion_criteria = local_completion_getGradeCriteria($COURSE->id);
                    $criteria_compl = $DB->get_record('course_completion_crit_compl',array('userid' => $user_id,'course' => $COURSE->id,'criteriaid' => $completion_criteria));
                    if ($criteria_compl) {
                        /* Delete Grade */
                        $DB->delete_records('course_completion_crit_compl',array('id' => $criteria_compl->id));
                    }//if_Grade
                }
            }//completed_expiration_period
        }//completion_info
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_completion_UpdateStatusCourseExpired

/**
 * @param           $course_id
 * @return          null
 * @throws          Exception
 *
 * @creationDate    09/02/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the expiration period connected with the course
 */
function local_completion_getExpirationPeriod($course_id) {
    /* Variables    */
    global $DB;

    try {
        /* First, check if exists the table */
        if (in_array('report_gen_outcome_exp',$DB->get_tables())) {
            /* Search Criteria  */
            $params = array();
            $params['course'] = $course_id;

            /* SQL Instruction  */
            $sql = " SELECT		goc.id,
                                goc.outcomeid,
                                oex.expirationperiod
                    FROM		{grade_outcomes_courses}		goc
                      JOIN	    {report_gen_outcome_exp}		oex		ON oex.outcomeid = goc.outcomeid
                    WHERE		goc.courseid = :course ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->expirationperiod;
            }else {
                return null;
            }//if_rdo
        }else {
            return null;
        }//if_else
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_completion_getExpirationPeriod

/**
 * @param           $course_id
 * @return          null
 * @throws          Exception
 *
 * @creationDate    09/02/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the criteria id connected with the final grade and course
 */
function local_completion_getGradeCriteria($course_id) {
    global $DB;

    try {
        /* Search Criteria  */
        $params = array();
        $params['course_id'] = $course_id;

        /* SQL Instruction  */
        $sql = " SELECT     id
                 FROM       {course_completion_criteria}
                 WHERE      course = :course_id
                    AND     gradepass IS NOT NULL ";

        /* Execute      */
        $rdo = $DB->get_record_sql($sql,$params);
        if ($rdo) {
            return $rdo->id;
        }else {
            return null;
        }//if_rdo
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_completion_getGradeCompletion

/**
 * @param           $course_id
 * @throws          Exception
 *
 * @creationDate    23/09/2014
 * @auhtor          eFaktor     (fbv)
 *
 * Description
 * Mark the course
 */
function local_completion_cron_mark_started($course_id) {
    /* Variables    */
    global $DB,$CFG;

    try {
        if (!empty($CFG->gradebookroles)) {
            $roles = ' AND ra.roleid IN ('.$CFG->gradebookroles.')';
        } else {
            // This causes it to default to everyone (if there is no student role)
            $roles = '';
        }

/**
         * A quick explaination of this horrible looking query
         *
         * It's purpose is to locate all the active participants
         * of a course with course completion enabled.
 *
         * We also only want the users with no course_completions
         * record as this functions job is to create the missing
         * ones :)
 *
         * We want to record the user's enrolment start time for the
         * course. This gets tricky because there can be multiple
         * enrolment plugins active in a course, hence the possibility
         * of multiple records for each couse/user in the results
 */
        $sql = "
        SELECT
            c.id AS course,
            u.id AS userid,
            crc.id AS completionid,
            ue.timestart AS timeenrolled,
            ue.timecreated
        FROM
            {user} u
        INNER JOIN
            {user_enrolments} ue
         ON ue.userid = u.id
        INNER JOIN
            {enrol} e
         ON e.id = ue.enrolid
        INNER JOIN
            {course} c
         ON c.id = e.courseid
        INNER JOIN
            {role_assignments} ra
         ON ra.userid = u.id
        LEFT JOIN
            {course_completions} crc
         ON crc.course = c.id
        AND crc.userid = u.id
        WHERE c.id = ?
        AND c.enablecompletion = 1
        AND crc.timeenrolled IS NULL
        AND ue.status = 0
        AND e.status = 0
        AND u.deleted = 0
        AND ue.timestart < ?
        AND (ue.timeend > ? OR ue.timeend = 0)
            $roles
        ORDER BY
            course,
            userid
    ";

        $now = time();
        $rs = $DB->get_recordset_sql($sql, array($course_id,$now, $now, $now, $now));

        // Check if result is empty
        if (!$rs->valid()) {
            $rs->close(); // Not going to iterate (but exit), close rs
            return;
        }

/**
         * An explaination of the following loop
         *
         * We are essentially doing a group by in the code here (as I can't find
         * a decent way of doing it in the sql).
 *
         * Since there can be multiple enrolment plugins for each course, we can have
         * multiple rows for each particpant in the query result. This isn't really
         * a problem until you combine it with the fact that the enrolment plugins
         * can save the enrol start time in either timestart or timeenrolled.
 *
         * The purpose of this loop is to find the earliest enrolment start time for
         * each participant in each course.
 */
        $prev = null;
        while ($rs->valid() || $prev) {

            $current = $rs->current();

            if (!isset($current->course)) {
                $current = false;
            }
            else {
                // Not all enrol plugins fill out timestart correctly, so use whichever
                // is non-zero
                $current->timeenrolled = max($current->timecreated, $current->timeenrolled);
            }

            // If we are at the last record,
            // or we aren't at the first and the record is for a diff user/course
            if ($prev &&
                (!$rs->valid() ||
                    ($current->course != $prev->course || $current->userid != $prev->userid))) {

                $completion = new completion_completion();
                $completion->userid = $prev->userid;
                $completion->course = $prev->course;
                $completion->timeenrolled = (string) $prev->timeenrolled;
                $completion->timestarted = 0;
                $completion->reaggregate = time();

                if ($prev->completionid) {
                    $completion->id = $prev->completionid;
                }

                $completion->mark_enrolled();

            }
            // Else, if this record is for the same user/course
            elseif ($prev && $current) {
                // Use oldest timeenrolled
                $current->timeenrolled = min($current->timeenrolled, $prev->timeenrolled);
            }

            // Move current record to previous
            $prev = $current;

            // Move to next record
            $rs->next();
        }

        $rs->close();

    }catch (Exception $ex) {
        throw $ex;
    }
}//local_completion_cron_mark_started

/**
 * @param           $course_id
 * @param           $user_id
 * @return          bool
 *
 * @creationDate    12/08/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Completion criteria date cron for a specific course and user
 */
function local_completion_criteria_date_cron($course_id,$user_id) {
    /* Variables    */
    global $DB;

    /* Search Criteria  */
    $params = array();
    $params['course_id']            = $course_id;
    $params['user_id']              = $user_id;
    $params['time_end']         = time();
    $params['criteria_type']    = COMPLETION_CRITERIA_TYPE_DATE;
    $params['context_level']    = CONTEXT_COURSE;

    /* SQL Instruction  */
    $sql = " SELECT DISTINCT  c.id        AS 'course',
                              cr.timeend  AS 'timeend',
                              cr.id       AS 'criteriaid',
                              ra.userid   AS 'userid'
             FROM			{course_completion_criteria} 	cr
                  JOIN		{course}	 					c		ON 	cr.course 		= c.id
                  JOIN		{context} 						con		ON 	con.instanceid 	= c.id
                  JOIN		{role_assignments}				ra		ON 	ra.contextid 	= con.id
                  JOIN		{course_completion_crit_compl}	cc		ON 	cc.criteriaid 	= cr.id
                                                                    AND cc.userid 		= ra.userid
             WHERE			c.id                = :course_id
                  AND       ra.userid           = :user_id
                  AND		cr.criteriatype 	= :criteria_type
                  AND 		con.contextlevel 	= :context_level
                  AND 		c.enablecompletion  = 1
                  AND 		cc.id IS NULL
                  AND       cr.timeend < :time_end ";


    /* Execute  */
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $record) {
        $completion = new completion_criteria_completion((array) $record, DATA_OBJECT_FETCH_BY_KEY);
        $completion->mark_complete($record->timeend);
    }
    $rs->close();

    return true;
}//local_completion_criteria_date_cron

/**
 * @param           $course_id
 * @param           $user_id
 * @return          bool
 *
 * @creationDate    12/08/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Completion criteria activity cron connected with a specific course and user
 */
function local_completion_criteria_activity_cron($course_id,$user_id) {
    /* Variables    */
    global $DB;

    /* Search Criteria  */
    $params = array();
    $params['course_id']            = $course_id;
    $params['user_id']              = $user_id;
    $params['criteria_type']        = COMPLETION_CRITERIA_TYPE_ACTIVITY;//4
    $params['context_level']        = CONTEXT_COURSE;
    $params['state_complete']       = COMPLETION_COMPLETE;//1
    $params['state_complete_pass']  = COMPLETION_COMPLETE_PASS;//2

    /* SQL Instruction  */
    $sql = " SELECT 	DISTINCT	c.id            AS 'course',
                                    cr.id           AS 'criteriaid',
                                    ra.userid       AS 'userid',
                                    mc.timemodified AS 'timecompleted'
             FROM		{course_completion_criteria} 	cr
                JOIN		{course} 							c		ON 	cr.course 			= c.id
                JOIN	    {context} 						    con 	ON 	con.instanceid 		= c.id
                JOIN		{role_assignments} 				    ra		ON 	ra.contextid 		= con.id
                JOIN		{course_modules_completion} 		mc		ON 	mc.coursemoduleid 	= cr.moduleinstance
                                                                        AND mc.userid 	        = ra.userid
                LEFT JOIN	{course_completion_crit_compl} 	cc	ON 	cc.criteriaid 	= cr.id
                                                                        AND cc.userid 		    = ra.userid
             WHERE			c.id                = :course_id
                AND         ra.userid           = :user_id
                AND			cr.criteriatype     = :criteria_type
                AND 		con.contextlevel    = :context_level
                AND 		c.enablecompletion  = 1
                AND   cc.id IS NULL
                AND 		( mc.completionstate = :state_complete
                              OR
                              mc.completionstate = :state_complete_pass
                            ) ";

    /* Execute  */
    $rs = $DB->get_recordset_sql($sql,$params);
    foreach ($rs as $record) {
        $completion = new completion_criteria_completion((array) $record, DATA_OBJECT_FETCH_BY_KEY);
        $completion->mark_complete($record->timecompleted);
    }
    $rs->close();

    return true;
}//local_completion_criteria_activity_cron

/**
 * @param           $course_id
 * @param           $user_id
 * @return          bool
 *
 * @creationDate    12/08/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Completion criteria duration cron connected with a specific course and user
 */
function local_completion_criteria_duration_cron($course_id,$user_id) {
    /* Variables    */
    global $DB;
    $time = time();

    /* Search Criteria  */
    $params = array();
    $params['course_id']        = $course_id;
    $params['user_id']          = $user_id;
    $params['criteria_type']    = COMPLETION_CRITERIA_TYPE_DURATION;//5
    $params['time_start']       = $time;
    $params['time_created']     = $time;

    /* SQL Instruction  */
    $sql = " SELECT		c.id 								AS 'course',
                        cr.id 								AS 'criteriaid',
                        u.id 								AS 'userid',
                        ue.timestart 						AS 'otimestart',
                        (ue.timestart + cr.enrolperiod) 	AS 'ctimestart',
                        ue.timecreated 						AS 'otimeenrolled',
                        (ue.timecreated + cr.enrolperiod) 	AS 'ctimeenrolled'
             FROM		{user} 							u
                JOIN	{user_enrolments}	 				ue		ON ue.userid 		= u.id
                JOIN	{enrol} 							e		ON e.id 			= ue.enrolid
                JOIN	{course} 							c		ON c.id 			= e.courseid
                JOIN	{course_completion_criteria} 		cr		ON c.id 			= cr.course
                JOIN	{course_completion_crit_compl} 	    cc		ON cc.criteriaid 	= cr.id
                                                                    AND cc.userid 		= u.id
             WHERE		u.id                = :user_id
                AND		c.id                = :course_id
                AND		cr.criteriatype     = :criteria_type
                AND		c.enablecompletion  = 1
                AND   cc.id IS NULL
                AND		(
                         (ue.timestart > 0 AND ue.timestart + cr.enrolperiod < :time_start)
                      OR
                       (ue.timecreated > 0 AND ue.timecreated + cr.enrolperiod < :time_created)
                      )";


    /* Execute  */
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $record) {
        $completion = new completion_criteria_completion((array) $record, DATA_OBJECT_FETCH_BY_KEY);

        // Use time start if not 0, otherwise use timeenrolled
        if ($record->otimestart) {
            $completion->mark_complete($record->ctimestart);
            } else {
            $completion->mark_complete($record->ctimeenrolled);
        }
    }
    $rs->close();

    return true;
}//local_completion_criteria_duration_cron

/**
 * @param           $course_id
 * @param           $user_id
 * @return          bool
 *
 * @creationDate    12/08/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Completion criteria grade cron connected with a specific course and user
 */
function local_completion_criteria_grade_cron($course_id,$user_id) {
    /* Variables    */
    global $DB;

    /* Search Criteria  */
    $params = array();
    $params['course_id']            = $course_id;
    $params['user_id']              = $user_id;
    $params['criteria_type']        = COMPLETION_CRITERIA_TYPE_GRADE;//6
    $params['context_level']        = CONTEXT_COURSE;//50

    /* SQL Instruction  */
    $sql = "  SELECT 	DISTINCT	c.id 				AS 'course',
                                    cr.id 				AS 'criteriaid',
                                    ra.userid 			AS 'userid',
                                    gg.finalgrade 		AS 'gradefinal',
                                    gg.timemodified 	AS 'timecompleted'
             FROM			{course_completion_criteria} 	cr
                  JOIN		{course} 						c		ON 	cr.course 		= c.id
                  JOIN		{context}						con		ON 	con.instanceid 	= c.id
                  JOIN		{role_assignments}				ra		ON 	ra.contextid 	= con.id
                  JOIN		{grade_items} 					gi		ON 	gi.courseid 	= c.id
                                                                    AND gi.itemtype 	= 'course'
                  JOIN		{grade_grades} 					gg		ON 	gg.itemid 		= gi.id
                                                                    AND gg.userid 		= ra.userid
                LEFT JOIN	{course_completion_crit_compl} 	cc	ON 	cc.criteriaid 		= cr.id
                                                                    AND cc.userid 		= ra.userid
             WHERE			c.id 				= :course_id
                  AND		ra.userid 			= :user_id
                  AND		cr.criteriatype 	= :criteria_type
                  AND 		con.contextlevel 	= :context_level
                  AND 		c.enablecompletion 	= 1
                AND cc.id IS NULL
                  AND 		gg.finalgrade >= cr.gradepass ";



    /* Execute  */
    $rs = $DB->get_recordset_sql($sql,$params);
    foreach ($rs as $record) {
        $completion = new completion_criteria_completion((array) $record, DATA_OBJECT_FETCH_BY_KEY);
        $completion->mark_complete($record->timecompleted);
    }
    $rs->close();

    return true;
}//local_completion_criteria_grade_cron

/**
 * @param           $course_id
 * @param           $user_id
 * @return          bool
 *
 * @creationDate    12/08/2014
 * @author          eFaktor     (fbV)
 *
 * Description
 * Completion criteria course cron connected with a specific course and user.
 */
function local_completion_criteria_course_cron($course_id,$user_id) {
    /* Variables    */
    global $DB;

    /* Search Criteria  */
    $params = array();
    $params['course_id']        = $course_id;
    $params['user_id']          = $user_id;
    $params['criteria_type']        = COMPLETION_CRITERIA_TYPE_COURSE;//8
    $params['context_level']        = CONTEXT_COURSE;//50

    /* SQL Instruction  */
    $sql = " SELECT 	DISTINCT	c.id 				AS 'course',
                                    cr.id 				AS 'criteriaid',
                                    ra.userid 			AS 'userid',
                                    cc.timecompleted 	AS 'timecompleted'
             FROM			{course_completion_criteria} 	cr
                JOIN		{course} 						c	ON 	cr.course 		= c.id
                JOIN		{context} 						con	ON 	con.instanceid 	= c.id
                JOIN		{role_assignments}				ra	ON 	ra.contextid 	= con.id
                JOIN		{course_completions} 			cc	ON 	cc.course 		= cr.courseinstance
                                                                AND cc.userid 		= ra.userid
                LEFT JOIN	{course_completion_crit_compl} 	ccc	ON 	ccc.criteriaid 	= cr.id
                                                                AND ccc.userid 		= ra.userid
             WHERE		c.id 				= :course_id
                AND		ra.userid 			= :user_id
                AND		cr.criteriatype 	= :criteria_type
                AND 	con.contextlevel 	= :context_level
                AND 	c.enablecompletion 	= 1
                AND 	ccc.id 				IS NULL
                AND 	cc.timecompleted 	IS NOT NULL ";


    /* Execute  */
    $rs = $DB->get_recordset_sql($sql,$params);
    foreach ($rs as $record) {
        $completion = new completion_criteria_completion((array) $record, DATA_OBJECT_FETCH_BY_KEY);
        $completion->mark_complete($record->timecompleted);
    }
    $rs->close();

    return true;
}//local_completion_criteria_course_cron

/**
 * @param           $completion_info
 * @return          bool
 * @throws          Exception
 *
 * @creationDate    12/08/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Call Web Service from Dossier and send all information about completion course.
 */
function local_completion_sendCompletionToDossier($completion_info) {
    /* Variables    */
    global $DB;
    $accomplishment         = array();
    $accomplishment_attr    = array();
    $accomplishment_str     = null;
    $plugin_info            = null;
    $urlCompletionEvent     = null;

    try {
        /* Plugins Info */
        $plugin_info     = get_config('local_completion');

        /* Check if completion event, real time, is activated   */
        if ($plugin_info->completion_activate) {
            /* Prepare the data to send */
            $accomplishment['accomplishment'] = array();

            /* Create the ID - Something Unique */
            $accomplishment_attr['accomplishmentId'] = $completion_info->id . '_' . array_sum(str_split($completion_info->timecompleted));
            /* Get Id User  */
            $user_id = $DB->get_field('user','secret',array('id' => $completion_info->userid));
            if (!$user_id) {
                $user_id = 0;
            }//if_user_id
            $accomplishment_attr['userId']           = $user_id;
            $accomplishment_attr['courseId']         = $completion_info->course;
            $accomplishment_attr['accomplishedDate'] = userdate($completion_info->timecompleted,'%Y.%m.%d', 99, false);;
            $accomplishment_attr['addToCv'] = 'true';

            /* Build Url End Point  */
            $urlCompletionEvent = $plugin_info->completion_end_point;

            /* Prepare the data */
            $accomplishment['accomplishment']   = $accomplishment_attr;
            $accomplishment_str                 = json_encode($accomplishment);

            /* Call Web Service */
            $ch = curl_init($urlCompletionEvent);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,     "POST");
            curl_setopt($ch, CURLOPT_POST,              true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,        $accomplishment_str);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,        array(
                                                                'User-Agent: Moodle 1.0',
                                                                'Content-Type: application/json ',
                                                                'Content-Length: '      . strlen($accomplishment_str),
                                                                'DOSSIER_USER: '        . $plugin_info->completion_username,
                                                                'DOSSIER_PASSWORD: '    . $plugin_info->completion_password)
            );


            $response = curl_exec( $ch );
            curl_close( $ch );
        }//if_completion_activate

    }catch (Exception $ex) {
        throw $ex;
    }//try_catch

    return true;
}//local_completion_sendCompletionDossier

/**
 * @param           $course_id
 * @param           $user_id
 * @return          bool|mixed
 * @throws          Exception
 *
 * @creationDate    22/04/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get completion information connected with user
 */
function local_completion_getCompletionInfoUser($course_id,$user_id) {
    /* Variables    */
    global $DB;

    try {
        /* Search Criteria  */
        $params = array();
        $params['course_id']    = $course_id;
        $params['user_id']      = $user_id;

        /* SQL Instruction  */
        $sql = " SELECT *
                 FROM   {course_completions}
                 WHERE  userid = :user_id
                    AND course = :course_id ";

    /* Execute  */
        $rdo = $DB->get_record_sql($sql,$params);
    if ($rdo) {
            return $rdo;
        }else {
            return false;
        }//if_rdo
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_completion_getCompletionInfoUser

/**
 * @param           $course_id
 * @return          bool
 * @throws          Exception
 *
 * @creationDate    22/04/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the activities to complete the course.
 */
function local_completion_getCriteriasToComplete($course_id) {
    /* Variables    */
    global $DB;

    try {
        /* Search Criteria  */
        $params = array();
        $params['course_id']   = $course_id;

        /* SQL Instruction  */
        $sql = " SELECT 	GROUP_CONCAT(DISTINCT moduleinstance ORDER BY moduleinstance SEPARATOR ',') as 'criterias'
                 FROM	  	{course_completion_criteria}
                 WHERE  	course = :course_id
                 AND        gradepass IS NULL";


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
 * @param           $course_id
 * @param           $user_id
 * @return          bool
 * @throws          Exception
 *
 * @creationDate    22/04/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the activities have been completed by the user
 */
function local_completion_getCompletionUser($course_id,$user_id) {
    /* Varaibles    */
    global $DB;

    try {
        /* Search Criteria  */
        $params = array();
        $params['course_id']    = $course_id;
        $params['user_id']      = $user_id;
        $params['state_fail']   = COMPLETION_COMPLETE_FAIL;

        /* SQL Instruction  */
        $sql = " SELECT 	GROUP_CONCAT(DISTINCT cm.id ORDER BY cm.id SEPARATOR ',') as 'completions'
                 FROM	  	{course_modules}				cm
                    JOIN	{course_completion_criteria} 	ccc		ON 	ccc.moduleinstance 	= cm.id
                    JOIN	{course_modules_completion}	    cmc		ON	cmc.coursemoduleid 	= cm.id
                                                                    AND cmc.completionstate != :state_fail
                                                                    AND cmc.completionstate != 0
                 WHERE  	cm.course   = :course_id
                 AND		cmc.userid 	= :user_id ";

        /* Execute      */
        $rdo = $DB->get_record_sql($sql,$params);
        if ($rdo->completions) {
            return $rdo->completions;
        }else {
            return false;
        }//if_rdo
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_completion_getCompletionUser

