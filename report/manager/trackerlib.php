<?php

/**
 * Library code for the Tracker Module.
 *
 * @package     local
 * @subpackage  tracker
 * @copyright   2010 eFaktor
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  09/10/2012
 * @author      eFaktor     (fbv)
 *
 */

require_once('tracker.php');
require_once('tracker_pdf.php');
require_once('locallib.php');

define('PAGE_TRACKER', 'tracker');
define('TRACKER_FORMAT', 'tracker_index');
define('TRACKER_PDF_DOWNLOAD', 'downloadpdf');
define('TRACKER_PDF_SEND', 'sendpdf');

/**
 * @param           $user_id    User identity.
 * @return      stdClass    User information.
 *
 * @createDate  09/10/2012
 * @author      eFaktor (fbv)
 *
 * Description
 * Return personal information of the user such as name, company, job roles...
 */
function tracker_get_info_user_tracker($user_id) {
    global $DB;

    /* Info Tracker User */
    $tracker_user = new stdClass();
    $tracker_user->user_id      = $user_id;
    $tracker_user->fullname     = null;
    $tracker_user->job_roles    = array();
    $tracker_user->company_id   = null;
    $tracker_user->company_name = null;

    /* Instruction SQL  */
    $sql = " SELECT		CONCAT(u.firstname, ' ', u.lastname) user_name,
                        cd.id as company_id,
                        cd.name as company_name,
                        uid.data job_roles
             FROM		{user}	u
                JOIN	{user_info_data}			uid_c	ON		uid_c.userid 	= u.id
                JOIN	{user_info_field}			uif_c	ON		uif_c.id 		= uid_c.fieldid
                                                            AND		uif_c.datatype	= :ctype
                JOIN	{report_gen_companydata}	cd 		ON		cd.id			= uid_c.data
                JOIN	{user_info_data} 			uid 	ON  	uid.userid    	= uid_c.userid
                JOIN	{user_info_field} 			uif 	ON 		uif.id 	   	    = uid.fieldid
                                                            AND		uif.datatype  	= :jtype
             WHERE		u.id = :user ";

    /* Search Criteria  */
    $params = array();
    $params['user']     = $user_id;
    $params['jtype']    = 'rgjobrole';
    $params['ctype']    = 'rgcompany';

    /* Execute  */
    if ($rdo = $DB->get_record_sql($sql,$params)) {
        $tracker_user->fullname     = $rdo->user_name;
        $tracker_user->job_roles    = $rdo->job_roles;
        $tracker_user->company_id   = $rdo->company_id;
        $tracker_user->company_name = $rdo->company_name;
    }//if_rdo

    return $tracker_user;
}//tracker_get_info_user_tracker


/**
 * @param           $tracker_user       User information
 * @param      bool $company_report
 * @return          null               Tracker information.
 *
 * @creationDate    09/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return the tracker information that is connected with the user.
 *
 * Tracker Info - Structure.
 *          [job role name][outcome id] Array
 *                                          --> name
 *                                          --> shortname
 *                                          --> expiration
 *                                          --> completed       Array
 *                                                  [id_course]
 *                                                              --> fullaname
 *                                                              --> shortname
 *                                                              --> summary
 *                                                              --> timecompleted
 *                                          --> not_completed   Array
 *                                                              --> fullname
 *                                                              --> shortname
 *                                                              --> summary
 *                                          --> not_enrolled    Array
 *                                                              --> fullname
 *                                                              --> shortname
 *                                                              --> summary
 */
function tracker_get_tracker_page_user_info($tracker_user,$company_report = false) {
    global $DB;

    /* Tracker Info */
    $tracker_info = new stdClass();
    $tracker_info->not_connected_completed      = array();
    $tracker_info->not_connected_not_completed  = array();
    $tracker_info->connected = array();
    $tracker_info->total_connected = 0;
    $tracker_info->total_not_connected = 0;

    $courses_outcomes = '';

    try {
        if ($tracker_user->job_roles) {
            /* Job ROLES */
            $job_list = explode(',',$tracker_user->job_roles);
            if (!$job_list[0]) {
                array_shift($job_list);
            }
            $job_list = implode(',',$job_list);

            $sql = " SELECT	CONCAT(o.id,'_',jr.id) as 'o_jr_id',
                            o.id,
                            o.fullname,
                            o.shortname,
                            jr.name job_role_name,
                            rgo.expirationperiod
                FROM		{grade_outcomes}              o
                    JOIN 	{grade_outcomes_courses}      oucu	    ON 	  oucu.outcomeid    = o.id
                    JOIN 	{report_gen_outcome_exp}      rgo	  	ON 	  rgo.outcomeid     = oucu.outcomeid
                    JOIN 	{report_gen_outcome_jobrole}  oj	  	ON 	  oj.outcomeid      = rgo.outcomeid
                    JOIN 	{report_gen_jobrole}          jr	  	ON 	  jr.id 		    = oj.jobroleid
                                                                    AND   jr.id 		    IN (" . $job_list . ")
                GROUP BY 	o.id, jr.id
                ORDER BY   o.fullname , jr.name ASC ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $outcome) {
                    /* Outcome Information  */
                    $outcome_info                   = new stdClass();

                    /* Outcome Info */
                    $outcome_info->name             = $outcome->fullname;
                    $outcome_info->shortname        = $outcome->shortname;
                    $outcome_info->expiration       = $outcome->expirationperiod;
                    /*
                        Completed Course Info
                       Not Completed Courses Info
                    */
                    list($outcome_info->completed,$outcome_info->not_completed) = tracker_get_completed_and_not_course_info_user_outcome($tracker_user->user_id,$outcome->id);

                    /* Get the courses connected with outcomes */
                    if ($outcome_info->completed) {
                        $courses_outcomes .= implode(',',array_keys($outcome_info->completed));
                    }//if_outcome_info->completed
                    if ($outcome_info->not_completed) {
                        if ($courses_outcomes) {
                            $courses_outcomes .= ',';
                        }
                        $courses_outcomes .= implode(',',array_keys($outcome_info->not_completed));
                    }//if_outcome_info->not_completed

                    /* Not Enrolled Course          */
                    $outcome_info->not_enrolled     = tracker_get_not_enrolled_course_info_user_outcome($tracker_user->user_id,$outcome->id);

                    $tracker_info->total_connected +=  count($outcome_info->completed) + count($outcome_info->not_completed) + count($outcome_info->not_enrolled);
                    if (!$company_report) {
                        $tracker_info->connected[$outcome->fullname][$outcome->job_role_name] =  $outcome_info;
                    }else {
                        $tracker_info->connected[$outcome->job_role_name][$outcome->id] =  $outcome_info;
                    }//if_else
                }//for_each
            }//if_rdo
        }//if_job_list

        /* Completed and Not Completed  */
        list($tracker_info->not_connected_completed,$tracker_info->not_connected_not_completed) = tracker_get_completed_and_not_completed_not_outcome($tracker_user->user_id,$courses_outcomes);
        $tracker_info->total_not_connected += count($tracker_info->not_connected_completed) + count($tracker_info->not_connected_not_completed);

        return $tracker_info;

    }catch(Exception $ex) {
        return null;
    }//try_catch
}//tracker_get_tracker_page_user_info

/**
 * @param           $user_id
 * @param           $outcome_id
 * @return          array
 * @throws          Exception
 *
 * @creationDate    25/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the courses completed and not completed connected with the user and outcome
 */
function tracker_get_completed_and_not_course_info_user_outcome($user_id,$outcome_id) {
    global $DB;

    /* Variables    */
    $completed          = array();
    $not_completed      = array();

    try {
        /* Search Criteria  */
        $params = array();
        $params['user']     = $user_id;
        $params['outcome']  = $outcome_id;

        /* SQL Instruction  */
        $sql = " SELECT		DISTINCT  c.id,
			                          c.fullname,
                                      c.shortname,
                                      c.summary,
                                      cc.timecompleted
                 FROM		    {grade_outcomes_courses}	oc
                    JOIN	    {course}					c	ON 		c.id 			= oc.courseid
                                                                AND		oc.outcomeid 	= :outcome
                                                                AND     c.visible       = 1
                    JOIN	    {enrol}						e	ON		e.courseid   	= c.id
                    JOIN	    {user_enrolments}			ue	ON		ue.enrolid 		= e.id
                                                                AND		ue.userid		= :user
                    LEFT JOIN	{course_completions}		cc	ON		cc.course 		= e.courseid
                                                                AND		cc.userid 		= ue.userid
                 ORDER BY	c.fullname ASC ";

        /* Execute  */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            foreach ($rdo as $course) {
                /* Course Information   */
                $course_info                = new stdClass();

                $course_info->fullname      = $course->fullname;
                $course_info->shortname     = $course->shortname;
                $course_info->summary       = $course->summary;
                if ($course->timecompleted) {
                    $course_info->timecompleted = $course->timecompleted;
                    $completed[$course->id] = $course_info;
                }else {
                    $not_completed[$course->id] = $course_info;
                }//if_else_timecompleted
            }//for_course
        }//if_else

        return array($completed,$not_completed);
    }catch (Exception $ex){
        throw $ex;
    }//try_catch
}//tracker_get_completed_and_not_course_info_user_outcome

/**
 * @param           $user_id
 * @param           $courses_outcomes
 * @return          array
 * @throws          Exception
 *
 * @creationDate    25/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the courses completed and not completed connected with the user and not connected with outcome
 */
function tracker_get_completed_and_not_completed_not_outcome($user_id,$courses_outcomes) {
    global $DB;

    /* Variables    */
    $completed      = array();
    $not_completed  = array();

    try {
        /* Search Criteria  */
        $params = array();
        $params['user']     = $user_id;

        /* SQL Instruction  */
        $sql = " SELECT		DISTINCT c.id,
                                     c.fullname,
                                     c.shortname,
                                     c.summary,
                                     FROM_UNIXTIME(cc.timecompleted,'%d.%m.%Y') as 'timecompleted'
                 FROM		    {course}					c
                    JOIN	    {enrol}						e	ON		e.courseid   	= c.id
                    JOIN	    {user_enrolments}			ue	ON		ue.enrolid 		= e.id
                                                                AND		ue.userid		= :user
                    LEFT JOIN	{course_completions}		cc	ON		cc.course 		= e.courseid
                                                                AND		cc.userid 		= ue.userid
                 WHERE     c.visible = 1 ";

        if ($courses_outcomes) {
            $sql .= " AND  c.id NOT IN ($courses_outcomes) ";
        }//if_courses_outcome

        $sql .=  " ORDER BY  c.fullname ASC ";

        /* Execute  */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            foreach ($rdo as $course) {
                /* Course Information   */
                $course_info                = new stdClass();

                $course_info->fullname      = $course->fullname;
                $course_info->shortname     = $course->shortname;
                $course_info->summary       = $course->summary;
                if ($course->timecompleted) {
                    $course_info->timecompleted = $course->timecompleted;
                    $completed[$course->id] = $course_info;
                }else {
                    $not_completed[$course->id] = $course_info;
                }//if_else_timecompleted
            }//for_course
        }//if_else

        return array($completed,$not_completed);
    }catch(Exception $ex) {
        throw $ex;
    }//try_catch
}//tracker_get_completed_and_not_completed_not_outcome

/**
 * @param           $user_id        User identity.
 * @param           $outcome_id     Outcome identity.
 * @return          array|null      Not enrolled course list.
 *
 * @creationDate    09/10/2012
 * @author          eFaktor     (fbV)
 *
 * Description
 * Return all the courses where the user hasn't enrolled yet anf´ they are connected with a specific outcome.
 */
function tracker_get_not_enrolled_course_info_user_outcome($user_id,$outcome_id) {
    global $DB;

    $course_not_enrol = array();

    /* Instruction SQL  */
    $sql = " SELECT     c.id,
                        c.fullname,
                        c.shortname,
                        c.summary
             FROM		{course}  c
                JOIN	{grade_outcomes_courses} 	oc	ON	oc.courseid 	= c.id
                                                        AND oc.outcomeid	= :outcome
             WHERE		c.category <> 0
                AND     c.visible   = 1
                AND     c.id NOT IN (
                                      SELECT	e.courseid
                                      FROM		{enrol}		      e
                                          JOIN	{user_enrolments} ue	ON	ue.enrolid		= e.id
                                                                        AND	ue.userid		= :user
                                    )
             ORDER BY	c.fullname ASC ";


    /* Search Criteria  */
    $params = array();
    $params['user']     = $user_id;
    $params['outcome']  = $outcome_id;

    /* Execute  */
    if ($rdo = $DB->get_records_sql($sql,$params)) {
        foreach ($rdo as $course) {
            /* Course Information   */
            $course_info            = new stdClass();

            $course_info->fullname  = $course->fullname;
            $course_info->shortname = $course->shortname;
            $course_info->summary   = $course->summary;

            $course_not_enrol[$course->id] = $course_info;
        }//for_course

        return $course_not_enrol;
    }else {
        return null;
    }//if_else_rdo
}//tracker_get_not_enrolled_course_info_user_outcome

/*************************/
/*    FUNCTIONS PAGE     */
/*************************/

/**
 * @return      string
 *
 * @updateDate  10/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Return the buttons that will be shown in Tracker page.
 */
function tracker_prepare_buttons_output(){
    $url_send = new moodle_url('/report/manager/tracker/index.php',array('pdf'=>TRACKER_PDF_SEND));
    $url_dwn = new moodle_url('/report/manager/tracker/index.php',array('pdf'=>TRACKER_PDF_DOWNLOAD));
    $send_pdf_btn   = html_writer::start_tag('div',array('class' => 'div_button_tracker'));
        $send_pdf_btn .= html_writer::link($url_send,get_string('send_pdf_btn','local_tracker_manager'),array('class' =>"button_tracker"));
        $send_pdf_btn .= html_writer::link($url_dwn,get_string('download_pdf_btn','local_tracker_manager'),array('class' =>"button_tracker"));
    $send_pdf_btn  .= html_writer::end_tag('div');

    return $send_pdf_btn;
}//prepare_page_output

/**
 * @param            $tracker_info      Tracker information.
 * @param            $tracker_user      User information.
 * @param       bool $send_pdf          Boolean. If the user want to send the report by email
 * @return      bool|string             Report.
 *
 * @updateDate      10/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Download or send the report by email. Create the content from the tracker information.
 */
function tracker_download_pdf_tracker($tracker_info,$tracker_user, $send_pdf = false) {
    $out_tracker = tracker_print_pdf_tracker_info($tracker_info);
    $report = new Tracker($out_tracker,$tracker_user);

    return $report->prepare_and_send_pdf($send_pdf);
}//tracker_download_pdf_tracker


/**
 * @param       $tracker_info       Tracker information
 * @return      array               Tracker PDF table.
 *
 * @updateDate  10/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Create the tables that contain all information od the report. The format of the tables is specific to PDF file.
 */
function tracker_print_pdf_tracker_info($tracker_info) {

    /**
     * $tracker_info - Structure
     *
     *              [id_job_role]
     *                              --> job_role_name
     *                              --> outcomes            array
     *                                      [id_outcome]
     *                                                  --> name
     *                                                  --> shortname
     *                                                  --> expiration
     *                                                  --> completed       array
     *                                                          [id_course]
     *                                                                  --> fullname
     *                                                                  --> shortname
     *                                                                  --> summary
     *                                                                  --> timecompleted
     *                                                  --> not_completed   array
     *                                                          [id_course]
     *                                                                  --> fullname
     *                                                                  --> shortname
     *                                                                  --> summary
     *                                                  --> not_enrolled    array
     *                                                          [id_course]
     *                                                                  --> fullname
     *                                                                  --> shortname
     *                                                                  --> summary
     *
     */

    $out_tracker = array();
    $out_table = array();
    if ($tracker_info) {
        /* Courses Connected    */
        if ($tracker_info->connected) {
            $connected = $tracker_info->connected;
            foreach ($connected as $name=>$outcomes) {
                /*
                * Sort :
                *          1.- Not Completed
                *          2.- Not Enrolled
                *          3.- Expired
                *          4.- Finished
                */
                foreach ($outcomes as $jr_name=>$out) {
                    $str_job_role = get_string('outcome_job_role_tracker','local_tracker_manager',format_string($jr_name));

                    $table_tracker = tracker_create_table_tracker_info();

                    /* Not Completed    */
                    if ($out->not_completed) {
                        tracker_add_not_completed_table($out->not_completed,$table_tracker);
                    }//if_not_completed

                    /* Not Enrolled     */
                    if ($out->not_enrolled) {
                        tracker_add_not_enrolled_table($out->not_enrolled, $table_tracker);
                    }//if_not_enrolled

                    /* Completed        */
                    if ($out->completed) {
                        $expired    = tracker_get_expired_courses($out->completed,$out->expiration);
                        $completed  = tracker_get_finished_courses($out->completed,$out->expiration);

                        if ($expired) {
                            tracker_add_expired_table($expired,$table_tracker);
                        }//if_expired
                        if ($completed) {
                            tracker_add_finished_table($completed,$table_tracker);
                        }//if_completed
                    }//if_completed

                    if ($table_tracker) {
                        $out_table[$jr_name . '_' . $name]['job_role'] = $str_job_role;
                        $out_table[$jr_name . '_' . $name]['outcome']  = $out->name;
                        $out_table[$jr_name . '_' . $name]['table']    = $table_tracker;
                    }

                }//for_outcomes
            }//for_job

            $out_tracker['tables'] = $out_table;
        }//if_connected

        /* Courses Not Connected    */
        /**
         * Sort:
         *      1.- Not Completed
         *      2.- Not Enrolled
         *      3.- Completed
         */
        $table_tracker = tracker_create_table_tracker_info(true);
        /* Not Completed    */
        if ($tracker_info->not_connected_not_completed) {
            tracker_add_not_completed_table($tracker_info->not_connected_not_completed,$table_tracker);
        }//if_not_completed

        /* Completed        */
        if ($tracker_info->not_connected_completed) {
            tracker_add_finished_table($tracker_info->not_connected_completed,$table_tracker,true);
        }//if_compelted

        if ($table_tracker) {
            $out_tracker['not_connected'] = $table_tracker;
        }
    }//if_tracker_info

    return $out_tracker;
}//tracker_print_pdf_tracker_info

/**
 * @param           $outcome
 * @param           $toggle_outcome
 * @param           $url
 * @return          string
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the tag for the Outcome title
 */
function tracker_getTagTitleOutcome($outcome,$toggle_outcome,$url) {
    /* Variables    */
    $tag_header = '';

    $tag_header .= html_writer::start_tag('div',array('class' => 'header_tracker'));
        $tag_header .= '<h3>'. $outcome . '&nbsp;' . '</h3>';
    $tag_header .= html_writer::end_tag('div');

    return $tag_header;
}//block_tracker_getTitleOutcome

/**
 * @param           $job_role
 * @return          string
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the tag for the Job Role title.
 */
function tracker_getTagTitleJobRole($job_role) {
    /* Variables    */
    $tag_header = '';

    $tag_header .= html_writer::start_tag('table');
        $tag_header .= html_writer::start_tag('thead',array('class' => 'head_role'));
            $tag_header .= html_writer::start_tag('tr');
                /* Empty Col    */
                $tag_header .= html_writer::start_tag('th',array('class' => 'first'));
                    $tag_header .= '&nbsp;';
                $tag_header .= html_writer::end_tag('th');
                /* JobRole     */
                $tag_header .= html_writer::start_tag('th',array('class' => 'course'));
                    $tag_header .= $job_role;
                $tag_header .= html_writer::end_tag('th');
                /* Status Col    */
                $tag_header .= html_writer::start_tag('th',array('class' => 'status'));
                    $tag_header .= '&nbsp;';
                $tag_header .= html_writer::end_tag('th');
                /* Completion Col    */
                $tag_header .= html_writer::start_tag('th',array('class' => 'status'));
                    $tag_header .= '&nbsp;';
                $tag_header .= html_writer::end_tag('th');
                /* Valid Col    */
                $tag_header .= html_writer::start_tag('th',array('class' => 'status'));
                    $tag_header .= '&nbsp;';
                $tag_header .= html_writer::end_tag('th');
                /* Start Col    */
                $tag_header .= html_writer::start_tag('th',array('class' => 'start'));
                    $tag_header .= '&nbsp;';
                $tag_header .= html_writer::end_tag('th');
            $tag_header .= html_writer::end_tag('tr');
        $tag_header .= html_writer::end_tag('thead');
    $tag_header .= html_writer::end_tag('table');

    return $tag_header;
}//tracker_getTagTitleJobRole

/**
 * @param           $toggle
 * @param           $url
 * @param           $individual
 * @return          string
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the header for the Courses table
 */
function tracker_getTagHeaderCoursesTable($toggle,$url,$individual=false) {
    /* Variables    */
    $tag_header = '';
    $str_course         = get_string('course');
    $str_state          = get_string('state','local_tracker_manager');
    $str_valid          = get_string('outcome_valid_until','local_tracker_manager');
    $str_completion     = get_string('completion_time','local_tracker_manager');

    /* Build Header */
    $tag_header .= html_writer::start_tag('table');
        $tag_header .= html_writer::start_tag('thead',array('class' => 'head'));
            $tag_header .= html_writer::start_tag('tr');
                /* Empty Col    */
                $tag_header .= html_writer::start_tag('th',array('class' => 'first'));
                    $tag_header .= html_writer::start_tag('button',array('id' => $toggle, 'class' => 'toggle', 'type' => 'image'));
                    $tag_header .= html_writer::start_tag('img',array('class' => 'swicth_img','src' => $url,'id' => $toggle . '_img'));
                    $tag_header .= html_writer::end_tag('img');
                    $tag_header .= html_writer::end_tag('button');
                $tag_header .= html_writer::end_tag('th');
                /* Course Col    */
                $tag_header .= html_writer::start_tag('th',array('class' => 'course'));
                    $tag_header .= $str_course;
                $tag_header .= html_writer::end_tag('th');
                /* State Col    */
                $tag_header .= html_writer::start_tag('th',array('class' => 'status'));
                    $tag_header .= $str_state;
                $tag_header .= html_writer::end_tag('th');
                /* Completion Col    */
                $tag_header .= html_writer::start_tag('th',array('class' => 'status'));
                    $tag_header .= $str_completion;
                $tag_header .= html_writer::end_tag('th');
                /* Valid Col    */
                $tag_header .= html_writer::start_tag('th',array('class' => 'status'));
                if (!$individual) {
                    $tag_header .= $str_valid;
                }//if_individual
                $tag_header .= html_writer::end_tag('th');
                /* Empty Col    */
                $tag_header .= html_writer::start_tag('th',array('class' => 'start'));
                    $tag_header .= '&nbsp;';
                $tag_header .= html_writer::end_tag('th');
            $tag_header .= html_writer::end_tag('tr');
        $tag_header .= html_writer::end_tag('thead');
    $tag_header .= html_writer::end_tag('table');

    return $tag_header;
}//tracker_getTagHeaderCoursesTable

/**
 * @param           $not_completed
 * @param           $individual
 * @return          string
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the content of the 'Not Completed' courses.
 */
function tracker_getContentNotCompletedCourses($not_completed,$individual=false) {
    /* Variables */
    $content = '';

    foreach ($not_completed as $id=>$co) {
        $content .= html_writer::start_tag('tr', array('class' => 'not_completed'));
            /* Empty Col    */
            $content .= html_writer::start_tag('td',array('class' => 'first'));
                $content .= '&nbsp;';
            $content .= html_writer::end_tag('td');
            /* Course Col    */
            $content .= html_writer::start_tag('td',array('class' => 'course'));
                $content .= $co->fullname;
            $content .= html_writer::end_tag('td');
            /* State Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= get_string('outcome_course_started','local_tracker_manager');
            $content .= html_writer::end_tag('td');
            /* Completion Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= '-';
            $content .= html_writer::end_tag('td');
            /* Valid Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
            if (!$individual) {
                $content .= '-';
            }
            $content .= html_writer::end_tag('td');
            /* Empty Col    */
            $url = new moodle_url('/course/view.php',array('id'=>$id));
            $str_url = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
            $content .= html_writer::start_tag('td',array('class' => 'start'));
                $content .= $str_url;
            $content .= html_writer::end_tag('td');
        $content .= html_writer::end_tag('tr');
    }//for_non_completed

    return $content;
}//tracker_getContentNotCompletedCourses

/**
 * @param           $not_enrol
 * @return          string
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the content of the 'Not Enrol' courses
 */
function tracker_getContentNotEnrolCourses($not_enrol) {
    /* Variables */
    $content = '';

    foreach ($not_enrol as $id=>$co) {
        $content .= html_writer::start_tag('tr', array('class' => 'not_enroll'));
            /* Empty Col    */
            $content .= html_writer::start_tag('td',array('class' => 'first'));
                $content .= '&nbsp;';
            $content .= html_writer::end_tag('td');
            /* Course Col    */
            $content .= html_writer::start_tag('td',array('class' => 'course'));
                $content .= $co->fullname;
            $content .= html_writer::end_tag('td');
            /* State Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= get_string('outcome_course_not_enrolled','local_tracker_manager');
            $content .= html_writer::end_tag('td');
            /* Completion Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= '-';
            $content .= html_writer::end_tag('td');
            /* Valid Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= '-';
            $content .= html_writer::end_tag('td');
            /* Empty Col    */
            $url = new moodle_url('/course/view.php',array('id'=>$id));
            $str_url = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
            $content .= html_writer::start_tag('td',array('class' => 'start'));
                $content .= $str_url;
            $content .= html_writer::end_tag('td');
        $content .= html_writer::end_tag('tr');
    }//for_not_enroll

    return $content;
}//tracker_getContentNotEnrolCourses

/**
 * @param           $expired
 * @return          string
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the content of the expired courses
 */
function tracker_getContentExpiredCourses($expired) {
    /* Variables    */
    $content = '';

    /* Expired      */
    foreach ($expired as $id=>$co) {
        $content .= html_writer::start_tag('tr', array('class' => 'expired'));
            /* Empty Col    */
            $content .= html_writer::start_tag('td',array('class' => 'first'));
                $content .= '&nbsp;';
            $content .= html_writer::end_tag('td');
            /* Course Col    */
            $content .= html_writer::start_tag('td',array('class' => 'course'));
                $content .= $co->fullname;
            $content .= html_writer::end_tag('td');
            /* Status Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= get_string('outcome_course_expired','local_tracker_manager');
            $content .= html_writer::end_tag('td');
            /* Completion Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= $co->timecompleted;
            $content .= html_writer::end_tag('td');
            /* Valid Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= '-';
            $content .= html_writer::end_tag('td');
            /* Start Col    */
            $url = new moodle_url('/course/view.php',array('id'=>$id));
            $str_url = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
            $content .= html_writer::start_tag('td',array('class' => 'start'));
                $content .= $str_url;
            $content .= html_writer::end_tag('td');
        $content .= html_writer::end_tag('tr');
    }//for_expired

    return $content;
}//tracker_getContentExpiredCourses

/**
 * @param           $completed
 * @param           $individual
 * @return          string
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the content of the completed courses
 */
function tracker_getContentCompletedCourses($completed,$individual=false) {
    /* Variables    */
    $content = '';

    /* Completed    */
    foreach ($completed as $id=>$co) {
        $content .= html_writer::start_tag('tr', array('class' => 'completed'));
            /* Empty Col    */
            $content .= html_writer::start_tag('td',array('class' => 'first'));
                $content .= '&nbsp;';
            $content .= html_writer::end_tag('td');
            /* Course Col    */
            $content .= html_writer::start_tag('td',array('class' => 'course'));
                $content .= $co->fullname;
            $content .= html_writer::end_tag('td');
            /* Status Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= get_string('outcome_course_finished','local_tracker_manager');
            $content .= html_writer::end_tag('td');
            /* Completion Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= $co->timecompleted;
            $content .= html_writer::end_tag('td');
            /* Valid Col    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
            if (!$individual) {
                $content .= $co->validuntil;
            }
            $content .= html_writer::end_tag('td');
            /* Start Col    */
            $url = new moodle_url('/course/view.php',array('id'=>$id));
            $str_url = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
            $content .= html_writer::start_tag('td',array('class' => 'start'));
                $content .= $str_url;
            $content .= html_writer::end_tag('td');
        $content .= html_writer::end_tag('tr');
    }//for_completed

    return $content;
}//tracker_getContentCompletedCourses

/**
 * @param       $tracker_info       Tracker information.
 * @return      string              Tracker Table information.
 *
 * @updateDate  10/10/2012
 * @author      eFaktor     (fbv)
 *
 * Description
 * Create the tables that contain all information of the report. The format of the table is only valid to screen output.
 */
function tracker_print_tables_tracker_info($tracker_info) {
    /* Variables    */
    $out_tracker = '';

    $out_tracker .= tracker_prepare_buttons_output();

    /**
     * $tracker_info - Structure
     *
     *              [id_job_role]
     *                              --> job_role_name
     *                              --> outcomes            array
     *                                      [id_outcome]
     *                                                  --> name
     *                                                  --> shortname
     *                                                  --> expiration
     *                                                  --> completed       array
     *                                                          [id_course]
     *                                                                  --> fullname
     *                                                                  --> shortname
     *                                                                  --> summary
     *                                                                  --> timecompleted
     *                                                  --> not_completed   array
     *                                                          [id_course]
     *                                                                  --> fullname
     *                                                                  --> shortname
     *                                                                  --> summary
     *                                                  --> not_enrolled    array
     *                                                          [id_course]
     *                                                                  --> fullname
     *                                                                  --> shortname
     *                                                                  --> summary
     *
     */
    /* Outcomes */
    $url_img        = new moodle_url('/pix/t/expanded.png');
    $toggle_outcome = 1;
    $toggle_job     = 1;
    if ($tracker_info) {

        /* Courses Connected    */
        if ($tracker_info->connected) {
            $connected = $tracker_info->connected;
            foreach ($connected as $name=>$outcomes) {
                $id_toggle = 'YUI_' . $toggle_outcome;
                $out_tracker .= tracker_getTagTitleOutcome($name,$id_toggle,$url_img);
                /*
                * Sort :
                *          1.- Not Completed
                *          2.- Not Enrolled
                *          3.- Expired
                *          4.- Finished
                */
                $out_tracker .= html_writer::start_tag('div',array('class' => 'tracker_list','id'=> $id_toggle . '_div'));
                    foreach ($outcomes as $jr_name=>$out) {
                        $id_toogle = 'YUI_' . $toggle_outcome . '_' . $toggle_job;
                        $out_tracker .= html_writer::start_tag('div',array('class' => 'job_list'));
                            $out_tracker .= html_writer::start_tag('div',array('class' => 'job_list header_job'));
                                /* Job Role Title (Header)  */
                                $out_tracker .= tracker_getTagTitleJobRole($jr_name);

                                /* Header Courses Table     */
                                $out_tracker .= tracker_getTagHeaderCoursesTable($id_toogle,$url_img);

                                /* Content Courses Table    */
                                $out_tracker .= html_writer::start_tag('div',array('id' => $id_toogle . '_div', 'class' => 'body_job'));
                                    $out_tracker .= html_writer::start_tag('table');
                                    /* Not Completed    */
                                    if (isset($out->not_completed)) {
                                        $out_tracker .= tracker_getContentNotCompletedCourses($out->not_completed);
                                    }//not_completed

                                    /* Not Enrolled     */
                                    if (isset($out->not_enrolled)) {
                                        $out_tracker .= tracker_getContentNotEnrolCourses($out->not_enrolled);
                                    }//not_enrolled

                                    /* Completed    */
                                    if (isset($out->completed)) {
                                        $expired    = tracker_get_expired_courses($out->completed,$out->expiration);
                                        $completed  = tracker_get_finished_courses($out->completed,$out->expiration);

                                        if ($expired) {
                                            $out_tracker .= tracker_getContentExpiredCourses($expired);
                                        }//if_expored

                                        if ($completed) {
                                            $out_tracker .= tracker_getContentCompletedCourses($completed);
                                        }//if_completed
                                    }//if_completed_expired
                                    $out_tracker .= html_writer::end_tag('table');
                                $out_tracker .= html_writer::end_tag('div');//div_body_job
                            $out_tracker .= html_writer::end_tag('div');//div_job_list_header_job
                        $out_tracker .= html_writer::end_tag('div');//div_job_list

                    $toggle_job += 1;
                }//for_jobroles
                $out_tracker .= html_writer::end_tag('div');//tracker_list

                $out_tracker .= '<hr style="border: 0; border-top: 1px solid #CCCCCC;width: 99.5%; margin-bottom: 25px;">';
                $toggle_outcome += 1;
            }//for_outcomes
        }//if_connected

        /* Courses Not Connected */
        /* Title    */
        $toggle_outcome = 'YUI_' . '0';
        $title = get_string('individual_courses','local_tracker_manager');
        $out_tracker .= tracker_getTagTitleOutcome($title,$toggle_outcome,$url_img);
        /* Courses */
        /**
         * Sort:
         *      1.- Not Completed
         *      2.- Not Enrolled
         *      3.- Completed
         */
        $out_tracker .= html_writer::start_tag('div',array('class' => 'tracker_list','id'=> $toggle_outcome . '_div'));
            $out_tracker .= html_writer::start_tag('div',array('class' => 'job_list'));
                $out_tracker .= html_writer::start_tag('div',array('class' => ' job_list header_job'));
                    /* Header Courses Table     */
                    $toggle_outcome .= '_table';
                    $out_tracker .= tracker_getTagHeaderCoursesTable($toggle_outcome ,$url_img,true);

                    /* Content Courses Table    */
                    $out_tracker .= html_writer::start_tag('div',array('id' => $toggle_outcome . '_div', 'class' => 'body_job'));
                        $out_tracker .= html_writer::start_tag('table');
                            /* Not Completed    */
                            if ($tracker_info->not_connected_not_completed) {
                                $out_tracker .= tracker_getContentNotCompletedCourses($tracker_info->not_connected_not_completed,true);
                            }//if_not_connected_not_completed

                            /* Completed        */
                            if ($tracker_info->not_connected_completed) {
                                $out_tracker .= tracker_getContentCompletedCourses($tracker_info->not_connected_completed,true);
                            }//if_not_connected_completed
                        $out_tracker .= html_writer::end_tag('table');
                    $out_tracker .= html_writer::end_tag('div');//body_job
                $out_tracker .= html_writer::end_tag('div');//job_list_header_job
            $out_tracker .= html_writer::end_tag('div');//job_list
        $out_tracker .= html_writer::end_tag('div');//tracker_list
    }//if_tracker_info

    return $out_tracker;
}//tracker_print_tables_tracker_info

/**
 * @param           $courses_list       Courses List
 * @param           $table              Tracker Report Table
 *
 * @creationDate    15/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add the not completed courses to the tracker table.
 */
function tracker_add_not_completed_table($courses_list, &$table) {
    foreach ($courses_list as $id=>$course) {
        /* Add Row */
        $row = array();

        /* Course Name Col  */
        $row[] = $course->fullname;
        /* State Col        */
        $row[] = get_string('outcome_course_started','local_tracker_manager');
        /* Completion Time  */
        $row[] = '-';
        /* Valid Col        */
        $row[] = '-';
        /* Start Course     */
        $url = new moodle_url('/course/view.php',array('id'=>$id));
        $str_url = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
        $row[]   = $str_url;

       $table->data[] = $row;
    }//for_course
}//tracker_add_not_completed_table

/**
 * @param           $courses_list       Courses List
 * @param           $table              Tracker Report Table
 *
 * @creationDate    15/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add the not enrolled courses to the tracker table.
 */
function tracker_add_not_enrolled_table($courses_list, &$table) {
    /* Add Empty Row */
    if ($table->data) {
        $row = array();

        /* Course Name Col  */
        $row[] = '';
        /* State Col        */
        $row[] = '';
        /* Completion Time  */
        $row[] = '';
        /* Valid Col        */
        $row[] = '';
        /* Start Course     */
        $row[] = '';

        $table->data[] = $row;
    }//if_data

    foreach ($courses_list as $id=>$course) {
        /* Add Row */
        $row = array();

        /* Course Name Col  */
        $row[] = $course->fullname;

        /* State Col        */
        $row[] = get_string('outcome_course_not_enrolled','local_tracker_manager');
        /* Completion Time  */
        $row[] = '-';
        /* Valid Col        */
        $row[] = '-';
        /* Start Course     */
        $url = new moodle_url('/course/view.php',array('id'=>$id));
        $str_url = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
        $row[]   = $str_url;

        $table->data[] = $row;
    }//for_course
}//tracker_add_not_enrolled_table

/**
 * @param           $courses_list       Courses List
 * @param           $expiration         Expiration Period
 * @return          array               Expired Courses
 *
 * @creationDate    15/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get a list of all expired courses.
 */
function tracker_get_expired_courses($courses_list,$expiration) {
    $expired_courses = array();

    foreach ($courses_list as $id=>$course) {
        $expiration_date = $course->timecompleted;
        $ts = strtotime($expiration  . ' month', $expiration_date);

        if ($ts < time()) {
            $expired_info = new stdClass();

            $expired_info->fullname         = $course->fullname;
            $expired_info->shortname        = $course->shortname;
            $expired_info->timecompleted    = userdate($course->timecompleted,'%d.%m.%Y', 99, false);

            $expired_courses[$id] = $expired_info;
        }//if
    }

    return $expired_courses;
}//tracker_get_expired_courses

/**
 * @param           $courses_list       Courses List
 * @param           $expiration         Expiration Period
 * @return          array               Finished Courses
 *
 * @creationDate    15/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get a list of all finished courses.
 */
function tracker_get_finished_courses($courses_list,$expiration) {
    $finished_courses = array();

    foreach ($courses_list as $id=>$course) {
        $expiration_date = $course->timecompleted;
        $ts = strtotime($expiration  . ' month', $expiration_date);

        if ($ts >= time()) {
            $finished_info = new stdClass();

            $finished_info->fullname        = $course->fullname;
            $finished_info->shortname       = $course->shortname;
            $finished_info->timecompleted   = userdate($course->timecompleted,'%d.%m.%Y', 99, false);
            $finished_info->validuntil      = userdate($ts,'%d.%m.%Y', 99, false);
            $finished_courses[$id] = $finished_info;
        }//if
    }

    return $finished_courses;
}//tracker_get_finished_courses

/**
 * @param           $courses_list       Courses List
 * @param           $table              Tracker Report Table
 *
 * @creationDate    15/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add the expired courses to the tracker table.
 */
function tracker_add_expired_table($courses_list, &$table) {
    /* Add Empty Row */
    if ($table->data) {
        $row = array();

        /* Course Name Col  */
        $row[] = '';
        /* State Col        */
        $row[] = '';
        /* Completion Time  */
        $row[] = '';
        /* Valid Col        */
        $row[] = '';
        /* Start Course     */
        $row[] = '';

        $table->data[] = $row;
    }//if_data

    foreach ($courses_list as $id=>$course) {
        /* Add Row */
        $row = array();

        /* Course Name Col  */
        $row[] = $course->fullname;
        /* State Col        */
        $row[] = get_string('outcome_course_expired','local_tracker_manager');
        /* Completion Time  */
        $row[] = $course->timecompleted;
        /* Valid Col        */
        $row[] = '-';
        /* Start Course     */
        $url = new moodle_url('/course/view.php',array('id'=>$id));
        $str_url = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
        $row[]   = $str_url;

        $table->data[] = $row;
    }//for_course
}//tracker_add_expired_table

/**
 * @param           $individual
 * @param           $courses_list       Courses List
 * @param           $table              Tracker Report Table
 *
 * @creationDate    15/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Add the finished courses to the tracker table.
 */
function tracker_add_finished_table($courses_list, &$table,$individual=false) {
    /* Add Empty Row */
    if ($table->data) {
        $row = array();

        /* Course Name Col  */
        $row[] = '';
        /* State Col        */
        $row[] = '';
        /* Completion Time  */
        $row[] = '';
        /* Valid Col        */
        $row[] = '';
        /* Start Course     */
        $row[] = '';

        $table->data[] = $row;

    }//if_data

    foreach ($courses_list as $id=>$course) {
        /* Add Row */
        $row = array();

        /* Course Name Col  */
        $row[] = $course->fullname;
        /* State Col        */
        $row[] = get_string('outcome_course_finished','local_tracker_manager');
        /* Completion Time  */
        $row[] = $course->timecompleted;
        if (!$individual) {
        /* Valid Col        */
        $row[] = $course->validuntil;
        }else {
            $row[] = '';
        }//if_else_individual
        /* Start Course     */
        $url = new moodle_url('/course/view.php',array('id'=>$id));
        $str_url = '<a href="'.$url .'">'. get_string('start_course','local_tracker_manager') .'</a>';
        $row[]   = $str_url;

        $table->data[] = $row;
    }//for_course
}//tracker_add_finished_table

/**
 * @param           $individual
 * @return          html_table              Tracker Report Structure Table.
 *
 * @creationDate    15/10/2012
 * @author          eFaktor     (fbv)
 *
 * Description
 * Return the table structure to the company or tracker report.
 */
function tracker_create_table_tracker_info($individual = false) {
    /* Head     */
    $str_course         = get_string('course');
    $str_state          = get_string('state','local_tracker_manager');
    if (!$individual) {
    $str_valid          = get_string('outcome_valid_until','local_tracker_manager');
    }else {
        $str_valid = '';
    }//if_individual

    $str_completion     = get_string('completion_time','local_tracker_manager');
    $str_user           = get_string('user');

    /* Create Table */
    $table = new html_table();

    /* ADD header   */
        $table->head  = array($str_course,$str_state,$str_completion,$str_valid,'');
        $table->align = array('left','center','center','center','center');

    return $table;
}//tracker_create_table_tracker_info