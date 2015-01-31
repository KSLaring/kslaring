<?php
/**
 * Library code for the Employee Report .
 *
 * @package     report
 * @subpackage  manager/employee_report
 * @copyright   2010 eFaktor
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  21/03/2014
 * @author      eFaktor     (fbv)
 *
 */

/**
 * @param       null $job_role
 * @return           array
 * @throws           Exception
 *
 * @creationDate    27/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the outcomes list
 */
function report_manager_EmployeeReport_getOutcomes($job_role=null) {
    global $DB;

    /* Variables    */
    $outcome_lst = array();

    try {
        $outcome_lst[0] = get_string('select') . '...';
        /* Search Criteria  */
        $params = array();

        if ($job_role) {
            $params['job'] = $job_role;

            /* SQL Instruction  */
            $sql = " SELECT		o.id,
                                o.fullname
                     FROM		{grade_outcomes}				o
                         JOIN	{report_gen_outcome_jobrole} 	oj	ON	oj.outcomeid = o.id
                                                                    AND	oj.jobroleid = :job
                     ORDER BY 	o.fullname ";
        }else {
            /* SQL Instruction */
            $sql = " SELECT		o.id,
                                o.fullname
                     FROM		{grade_outcomes}	o
                     ORDER BY 	o.fullname ";
        }//if_job_role

        /* Execute  */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            foreach ($rdo as $field) {
                $outcome_lst[$field->id] = $field->fullname;
            }
        }//if_rdo
        return $outcome_lst;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_EmployeeReport_getOutcomes

/**
 * @param           $company_id
 * @param           $outcome
 * @return          stdClass
 * @throws          Exception
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the information to show in 'Employee Report'
 *
 * Employee Report:
 *      - outcome.      Outcome fullname.
 *      - courses_id.   ID list.
 *      - courses.      courses list (fullname)
 *      - users.        Array
 *                          - user. Firstname + lastname,
 *                          - courses. ID courses list
 *                          - completion. Completion list
 *                          - certificate. Certificate list.
 */
function report_manager_EmployeeReport_getInfo($company_id,$outcome) {
    global $DB;

    /* Report Info  */
    $employee_rpt = new stdClass();
    $employee_rpt->outcome      = null;
    $employee_rpt->courses_id   = null;
    $employee_rpt->courses      = null;
    $employee_rpt->users        = null;

    try {
        /* Outcome Info */
        $out_info = report_manager_EmployeeReport_getOutcomeInfo($outcome);
        $employee_rpt->outcome      = $out_info->fullname;
        $employee_rpt->expiration   = $out_info->expirationperiod;
        $employee_rpt->courses_id   = $out_info->courses_id;
        $employee_rpt->courses      = $out_info->courses;
        $employee_rpt->job_roles    = $out_info->jobroles;
        /* Users Info   */
        $employee_rpt->users        = report_manager_EmployeeReport_getOutcomeUsersInfo($out_info->courses_id,$out_info->jobroles,$company_id);

        return $employee_rpt;
    }catch(Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_EmployeeReport_getInfo

/**
 * @param           $outcome
 * @return          mixed|null
 * @throws          Exception
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the Courses connected with outcome
 */
function report_manager_EmployeeReport_getOutcomeInfo($outcome) {
    global $DB;

    try {
        /* Search Criteria  */
        $params = array();
        $params['out'] = $outcome;

        /* SQL Instruction  */
        $sql = " SELECT		oc.fullname,
                            oe.expirationperiod,
			                GROUP_CONCAT(DISTINCT c.id ORDER BY c.fullname SEPARATOR ',')       as 'courses_id',
			                GROUP_CONCAT(DISTINCT c.fullname ORDER BY c.fullname SEPARATOR ',') as 'courses',
			                GROUP_CONCAT(DISTINCT roj.jobroleid ORDER BY roj.jobroleid SEPARATOR ',') as 'jobroles'
                 FROM		  {grade_outcomes}			oc
	                JOIN	  {grade_outcomes_courses}	occ		ON 	occ.outcomeid = oc.id
	                JOIN	  {course}					c		ON	c.id		  = occ.courseid
	                                                            AND c.visible     = 1
	                LEFT JOIN {report_gen_outcome_exp}		oe		ON 	oe.outcomeid  = occ.outcomeid
	                LEFT JOIN {report_gen_outcome_jobrole}	roj		ON roj.outcomeid  = oc.id
                 WHERE		oc.id = :out ";

        /* Execute  */
        $rdo = $DB->get_record_sql($sql,$params);
        if ($rdo) {
            return $rdo;
        }else {
            return null;
        }//if_else
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_EmployeeReport_getOutcomeInfo

/**
 * @param               $courses_id
 * @param               $job_roles
 * @param               $company_id
 * @return              array|null
 * @throws              Exception
 *
 * @creationDate        21/02/2014
 * @author              eFaktor     (fbv)
 *
 * Description
 * Get all the users connected with the company and extra information with the courses given.
 */
function report_manager_EmployeeReport_getOutcomeUsersInfo($courses_id,$job_roles,$company_id) {
    global $DB;

    try {
        /* Sarch Criteria */
        $params = array();
        $params['company']  = $company_id;
        $params['rgc']      = 'rgcompany';
        $params['rgj']      = 'rgjobrole';

        /* First Get Users  */
        $sql = " SELECT		u.id,
                            CONCAT(u.firstname, ', ', u.lastname) as 'user',
                            uid_j.data
                FROM			{user}				u
                    JOIN		{user_info_data}  	uid		ON 		uid.userid      = u.id
                                                            AND 	uid.data 	 	= :company
                    JOIN		{user_info_field} 	uif		ON		uif.id          = uid.fieldid
                                                            AND 	uif.datatype 	= :rgc
                	JOIN		{user_info_data}  	uid_j	ON 		uid_j.userid    = u.id
	                JOIN		{user_info_field} 	uif_j	ON		uif_j.id        = uid_j.fieldid
												            AND 	uif_j.datatype 	= :rgj
                 WHERE      u.deleted = 0 ";
        /* Users not allowed to see */
        $not_allowed = report_manager_getUsersNotAllowed();
        if ($not_allowed) {
            $sql .= " AND     u.id NOT IN ($not_allowed) ";
        }//if_not_allowed

        /* Execute  */
        $sql .= " ORDER BY   u.firstname ";
        $rdo_users = $DB->get_records_sql($sql,$params);
        if ($rdo_users) {
            $users_lst = array();
            /* Get info of user */
            foreach ($rdo_users as $rdo) {
                $user_jr    = explode(',',$rdo->data);
                $out_jr     = explode(',',$job_roles);
                if (array_intersect($user_jr,$out_jr)) {
                $user = new stdClass();
                $user->user         = $rdo->user;
                $user->courses      = array_flip(explode(',',$courses_id));

                if ($courses_id) {
                    $courses_completion     = report_manager_EmployeeReport_getCompletion($rdo->id,$courses_id);

                    foreach($user->courses as $key=>$value) {
                        if ($courses_completion) {
                            if (array_key_exists($key,$courses_completion)) {
                                $course_info = new stdClass();
                                $course_info->completion = $courses_completion[$key];
                                $user->courses[$key] = $course_info;
                            }else {
                                $user->courses[$key] = null;
                            }
                        }else {
                            $user->courses[$key] = null;
                        }
                    }//for_courses

                }//if_courses_id

                $users_lst[$rdo->id] = $user;
                }//if_job_roles
            }//foreach_user

            return $users_lst;
        }else {
            return null;
        }//if_rdo_users
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_EmployeeReport_getOutcomeUsersInfo

/**
 * @param           $user_id
 * @param           $course_lst
 * @return          array|null
 * @throws          Exception
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get completion times for the user and courses
 */
function report_manager_EmployeeReport_getCompletion($user_id,$course_lst) {
    global $DB;

    try {
        /* Searh Params */
        $params = array();
        $params['user_id'] = $user_id;

        /* SQL Instruction  */
        $sql = "  SELECT		DISTINCT e.courseid,
								cc.timecompleted
                  FROM			{user_enrolments}		ue
                    JOIN		{enrol}				    e			ON		e.id		  		= ue.enrolid
																	AND		e.courseid			IN (" . $course_lst . ")
																	AND		e.status			= 0
                    LEFT JOIN	{course_completions}	    cc			ON 		cc.course	    = e.courseid
																    AND		cc.userid			= ue.userid
				  WHERE		ue.userid = :user_id ";

        /* Execute */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            $completion = array();
            foreach($rdo as $instance) {
                $completion[$instance->courseid] = $instance->timecompleted;
            }

            return $completion;
        }else {
            return null;
        }
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_EmployeeReport_getCompletion

/**
 * @param           $user_id
 * @param           $course_lst
 * @return          array|null
 * @throws          Exception
 *
 * @creationDate    21/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the certification times for the user and courses
 */
function report_manager_EmployeeReport_getCertificate($user_id,$course_lst) {
    global $DB;

    try {
        /* Searh Params */
        $params = array();
        $params['user_id'] = $user_id;

        /* SQL Instruction  */

        $sql = " SELECT	    ce.course,
                            GROUP_CONCAT(DISTINCT IF(cei.timecreated,cei.timecreated,0) ORDER BY cei.id SEPARATOR ',') as 'certificate'
                FROM		{certificate}			ce
                    JOIN	{certificate_issues}	cei			ON		cei.certificateid	= ce.id
                                                                AND		cei.userid			= :user_id
                WHERE		ce.course IN (" . $course_lst . ")
                GROUP BY 	ce.course ";

        /* Execute */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            $certificate = array();
            foreach($rdo as $instance) {
                $certificate[$instance->course] = explode(',',$instance->certificate);
            }

            return $certificate;
        }else {
            return null;
        }
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//report_manager_EmployeeReport_getCertificate

/**
 * @param           $outcome
 * @return          string
 *
 * @creationDate    27/02/2014
 * @author          eFaktor     (fbV)
 *
 * Description
 * Get the title for the report.
 */
function report_manager_EmployeeReport_getTagTitleOutcome($outcome) {
    /* Variables    */
    $tag_header = '';

    $tag_header .= html_writer::start_tag('div',array('class' => 'header_info'));
    $tag_header .= '<h3>'. $outcome . '&nbsp;' . '</h3>';
    $tag_header .= html_writer::end_tag('div');

    return $tag_header;
}//report_manager_EmployeeReport_getTagTitleOutcome

/**
 * @param           $courses_lst
 * @param           $expiration
 * @param           $users_lst
 * @param           $completed_time
 * @return          string
 *
 * @creationDate    27/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get the content of the report
 */
function report_manager_EmployeeReport_geContentReport($courses_lst,$expiration,$users_lst,$completed_time) {
    /* Variables    */
    $content = '';

    $content .= html_writer::start_tag('div',array('class' => 'employee_content'));
        $content .= html_writer::start_tag('table');
            /* HEADER   */
            $content .= report_manager_EmployeeReport_getHeaderContent($courses_lst);
            /* BODY     */
            $content .= report_manager_EmployeeReport_getBodyContent($expiration,$users_lst,$completed_time);
        $content .= html_writer::end_tag('table');
    $content .= html_writer::end_tag('div');

    return $content;
}//report_manager_EmployeeReport_geContentReport

    /**
     * @param           $courses_lst
     * @return          string
     *
     * @creationDate    27/02/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the header of the table, which will contain all the data.
     */
function report_manager_EmployeeReport_getHeaderContent($courses_lst) {
    /* Variables    */
    $header_content = '';

    $header_content .= html_writer::start_tag('thead',array('class' => 'head_employee'));
        $header_content .= html_writer::start_tag('tr');
            /* User Col     */
            $header_content .= html_writer::start_tag('th',array('class' => 'user_head'));
            $header_content .= html_writer::end_tag('th');
            /* Course Col   */
            foreach ($courses_lst as $course) {
                $header_content .= html_writer::start_tag('th',array('class' => 'col_head'));
                $header_content .= html_writer::start_tag('div',array('class' => 'rotate'));
                $header_content .= html_writer::start_tag('p',array('class' => 'vertical_text'));
                $header_content .= $course;
                $header_content .= html_writer::end_tag('p');
                $header_content .= html_writer::end_tag('div');
                $header_content .= html_writer::end_tag('th');
            }//for_courses
        $header_content .= html_writer::end_tag('tr');
    $header_content .= html_writer::end_tag('thead');

    return $header_content;
}//report_manager_EmployeeReport_getHeaderContent

/**
 * @param           $expiration
 * @param           $users_lst
 * @param           $completed_time
 * @return          string
 *
 * @creationDate    27/02/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get and add the content to the table, which shows all the data
 */
function report_manager_EmployeeReport_getBodyContent($expiration,$users_lst,$completed_time) {
    /* Variables    */
    $body_content = '';

    $body_content .= html_writer::start_tag('tbody');
    foreach ($users_lst as $user) {
        $body_content .= html_writer::start_tag('tr');
        /* USer  Col   */
        $body_content .= html_writer::start_tag('td',array('class' => 'user'));
        $body_content .=  $user->user;
        $body_content .= html_writer::end_tag('td');

        $courses = $user->courses;
        foreach ($courses as $course) {
            /* Course Col     */
            if ($course) {
                if ($course->completion) {
                    $expiration_date = $course->completion;
                    $ts = strtotime($expiration  . ' month', $expiration_date);

                    if ($ts < time()) {
                        $body_content .= html_writer::start_tag('td',array('class' => 'expired'));
                        $body_content .= '<u>' . get_string('outcome_course_expired','local_tracker') . '</u>';
                        $body_content .= html_writer::end_tag('td');
                    }else {
                        $expiration_time = report_manager_get_completed_date_timestamp($completed_time,true);
                        if ($ts < $expiration_time) {
                            $body_content .= html_writer::start_tag('td',array('class' => 'valid'));
                                $body_content .= '<u>' . get_string('outcome_valid_until','local_tracker') . '</u>: ' . '</br>' . userdate($ts,'%d.%m.%Y',99,false);
                            $body_content .= html_writer::end_tag('td');
                        }else {
                            $body_content .= html_writer::start_tag('td',array('class' => 'completed'));
                            $body_content .= '<u>' . get_string('outcome_course_finished','local_tracker') . '</u>';
                            $body_content .= html_writer::end_tag('td');
                        }//if_valid_finis
                    }

                }else {
                    $body_content .= html_writer::start_tag('td',array('class' => 'not_completed'));
                    $body_content .= get_string('outcome_course_started','local_tracker');
                    $body_content .= html_writer::end_tag('td');
                }//if_else_course_completion
            }else {
                $body_content .= html_writer::start_tag('td',array('class' => 'not_enroll'));
                $body_content .= get_string('outcome_course_not_enrolled','local_tracker');
                $body_content .= html_writer::end_tag('td');
            }

        }//courses
        $body_content .= html_writer::end_tag('tr');
    }//for_users
    $body_content .= html_writer::end_tag('tbody');
    return $body_content;
}//report_generato_EmployeeReport_getBodyContent


