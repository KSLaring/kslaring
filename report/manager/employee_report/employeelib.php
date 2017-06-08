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
 * Library code for the Employee Report .
 *
 * @package         report
 * @subpackage      manager/employee_report
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    14/04/2015
 * @author          eFaktor     (fbv)
 *
 */


define('EMPLOYEE_REPORT_STRUCTURE_LEVEL','level_');

class EmployeeReport {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    /**
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the outcomes connected to the company and the report
     *
     *
     * @updateDate      15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send the correct parameters
     */
    public static function GetOutcomes_EmployeeReport($levelZero,$levelOne,$levelTwo,$levelThree) {
        /* Variables    */
        $outcomes   = null;
        $jr_keys    = null;
        $job_roles  = array();

        try {

            /* Job Roles connected to the level Three    */
            if (CompetenceManager::IsPublic($levelThree)) {
                CompetenceManager::GetJobRoles_Generics($job_roles);
            }//if_isPublic
            CompetenceManager::GetJobRoles_Hierarchy($job_roles,3,$levelZero,$levelOne,$levelTwo,$levelThree);

            /* Outcome Courses  */
            if ($job_roles) {
                $jr_keys            = implode(',',array_keys($job_roles));
                $outcomes           = self::GetOutcomes($jr_keys);
            }//if_job_roles

            return $outcomes;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetOutcomes_EmployeeReport


    /**
     * @param           $company
     * @param           $outcome
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the employee info tracker
     *
     * Employee Tracker
     *
     *      --> levelThree
     *      --> name
     *      --> users.      Array
     *              --> id
     *              --> name
     *              --> courses.    Array
     *      --> outcome.
     *              --> id
     *              --> name
     *              --> description
     *              --> expiration
     *              --> job_roles
     *              --> course.     Array
     *                          --> id
     *                          --> name
     */
    public static function Get_EmployeeTracker($company,$outcome) {
        /* Variables    */
        global $USER;
        $employeeTracker    = null;
        $my_users           = null;
        $job_roles          = array();

        try {
            /* My Users     */
            $my_users = CompetenceManager::GetUsers_MyCompanies($company,$USER->id);

            /* Employee Tracker */
            $employeeTracker = new stdClass();
            $employeeTracker->levelThree         = $company;
            $employeeTracker->name               = CompetenceManager::GetCompany_Name($company);
            $employeeTracker->users              = null;
            $employeeTracker->outcome            = self::Get_DetailOutcome($outcome);

            /* Outcome --> Info Users   */
            if ($employeeTracker->outcome && $my_users) {
                $employeeTracker->users = self::GetUsers_EmployeeTracker($company,$my_users,$employeeTracker->outcome->courses,$employeeTracker->outcome->job_roles);
            }//if_outcome

            return $employeeTracker;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_EmployeeTracker

    /**
     * @param           $employeeTracker
     * @param           $completed_list
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the employee tracker report - Format Screen
     *
     * Employee Tracker
     *
     *      --> levelThree
     *      --> name
     *      --> users.          Array
     *              --> id
     *              --> name
     *              --> courses. Array
     *      --> outcome.
     *              --> id
     *              --> name
     *              --> job_roles
     *              --> expiration
     *              --> course.     Array
     *                          --> id
     *                          --> name
     */
    public static function Print_EmployeeTracker($employeeTracker,$completed_list) {
        /* Variables    */
        $out_report = '';
        $return_url = null;

        try {
            /* Url To Back  */
            $return_url     = new moodle_url('/report/manager/employee_report/employee_report.php');

            $out_report .= html_writer::start_tag('div',array('class' => 'employee_div'));
                $out_report .= html_writer::start_tag('div',array('class' => 'employee_detail_rpt'));
                    /* Title */
                    $out_report .= '<h3>';
                        $out_report .= $employeeTracker->name;
                    $out_report .= '</h3>';

                    /* Outcome Title */
                    $out_report .= '</br>';
                    $out_report .= '<h4>';
                        $out_report .= $employeeTracker->outcome->name;
                    $out_report .= '</h4>';
                    /* Outcome Description  */
                    $out_report .= '<h5>';
                        $out_report .= $employeeTracker->outcome->description;
                    $out_report .= '</h5>';

                    /* Expiration Before    */
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next', 'report_manager') . ': ' .  $options[$completed_list];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_tag('div');//employee_detail_rpt

                /* Not Users    */
                if (!$employeeTracker->users) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    /* Return To Selection Page */
                    $out_report .= html_writer::link($return_url,get_string('company_overview_return_selection','report_manager'),array('class' => 'link_return'));
                }//if_not_users

                /* Employee Report - Content      */
                if ($employeeTracker->users) {
                    $out_report .= html_writer::start_tag('div',array('class' => 'employee_content'));
                        $out_report .= html_writer::start_tag('table');
                            /* HEADER   */
                            $out_report .= self::AddHeader_EmployeeReport($employeeTracker->outcome->courses);
                            /* BODY     */
                            $out_report .= self::AddContent_EmployeeReport($employeeTracker->users,$employeeTracker->outcome->expiration,$completed_list);
                        $out_report .= html_writer::end_tag('table');
                    $out_report .= html_writer::end_tag('div');//employee_content
                }//if_users
            $out_report .= html_writer::end_tag('div');//employee_div

            /* Return To Selection Page */
            $out_report .= html_writer::link($return_url,get_string('company_overview_return_selection','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_EmployeeTracker

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @param           $job_roles
     * @return          array
     * @throws          Exception
     *
     * @creationDate    14/04/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the outcomes connected with the job roles
     */
    private static function GetOutcomes($job_roles) {
        /* Variables    */
        global $DB;
        $outcomes = array();

        try {
            /* Outcomes */
            $outcomes[0] = get_string('select');

            /* SQL Instruction  */
            $sql = " SELECT			DISTINCT 	go.id,
                                                go.fullname
                     FROM		   	{grade_outcomes}			    go
                        JOIN	    {grade_outcomes_courses}	    goc	    ON 		goc.outcomeid 	= go.id
                        LEFT JOIN 	{report_gen_outcome_exp}	    oe	    ON		oe.outcomeid	= go.id
                        JOIN		{report_gen_outcome_jobrole}	jro		ON		jro.outcomeid	= go.id
                                                                            AND		jro.jobroleid   IN ($job_roles)
                     ORDER BY	go.fullname ASC ";


            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $outcomes[$instance->id] = $instance->fullname;
                }//for_instance_outcome
            }//if_Rdo

            return $outcomes;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetOutcomes

    /**
     * @param           $outcome_id
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get detail connected with the outcome
     *
     * Outcome
     *          --> id
     *          --> name
     *          --> description
     *          --> expiration
     *          --> job_roles
     *          --> courses.    Array
     *                      --> id
     *                      --> name
     */
    private static function Get_DetailOutcome($outcome_id) {
        /* Variables    */
        global $DB;
        $outcomeInfo = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['outcome'] = $outcome_id;

            /* SQL Instruction  */
            $sql = " SELECT			go.id,
                                    go.fullname,
                                    go.description,
                                    GROUP_CONCAT(DISTINCT c.id ORDER BY c.fullname SEPARATOR ',') as 'courses',
                                    GROUP_CONCAT(DISTINCT roj.jobroleid ORDER BY roj.jobroleid SEPARATOR ',') as 'jobroles',
                                    oe.expirationperiod
                     FROM		   	{grade_outcomes}			    go
                        JOIN	    {grade_outcomes_courses}	    goc	    ON 		goc.outcomeid 	= go.id
                        JOIN	    {course}					    c	    ON		c.id 			= goc.courseid
                                                                            AND		c.visible 		= 1
                        LEFT JOIN 	{report_gen_outcome_exp}	    oe	    ON		oe.outcomeid	= go.id
                        JOIN 		{report_gen_outcome_jobrole}	roj		ON 		roj.outcomeid  	= go.id
                     WHERE		go.id = :outcome
                     GROUP BY	go.id ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Outcome info */
                if ($rdo->courses) {
                    $outcomeInfo = new stdClass();
                    $outcomeInfo->id            = $rdo->id;
                    $outcomeInfo->name          = $rdo->fullname;
                    $outcomeInfo->description   = $rdo->description;
                    $outcomeInfo->expiration    = $rdo->expirationperiod;
                    $outcomeInfo->courses       = self::Get_InfoCourses($rdo->courses);
                    $outcomeInfo->job_roles     = $rdo->jobroles;
                }//if_courses
            }//if_rdo

            return $outcomeInfo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_DetailOutcome

    /**
     * @param           $courses_lst
     * @return          array
     * @throws          Exception
     *
     * @creationDate    14/04/2015
     * @author          efaktor     (fbv)
     *
     * Description
     * Get the detail for all the courses
     *
     * Courses
     *      [id]
     *          --> id
     *          --> name
     */
    private static function Get_InfoCourses($courses_lst) {
        /* Variables    */
        global $DB;
        $courses    = array();
        $info       = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		c.id,
                                c.fullname
                     FROM		{course}		c
                     WHERE		c.id IN ($courses_lst)
                     ORDER BY	c.fullname ";

            /* Execute          */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Course Info  */
                    $info       = new stdClass();
                    $info->id   = $instance->id;
                    $info->name = $instance->fullname;

                    $courses[$instance->id] = $info;
                }//for_rdo_courses
            }//if_rdo

            return $courses;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_InfoCourses

    /**
     * @param           $company
     * @param           $my_users
     * @param           $outcomeCourses
     * @param           $job_roles
     * @return          array
     * @throws          Exception
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the users connected with the Employee report
     *
     * Users
     *      [id]
     *              --> id
     *              --> name
     *              --> courses
     */
    private static function GetUsers_EmployeeTracker($company,$my_users,$outcomeCourses,$job_roles) {
        /* Variables    */
        global $DB;
        $users              = array();
        $info               = null;
        $courses            = implode(',',array_keys($outcomeCourses));
        $job_keys           = array_flip(explode(',',$job_roles));
        $jr_users           = null;
        $completed          = null;
        $not_completed      = null;
        $not_enrol          = null;
        $coursesEnrol       = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['company'] = $company;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT 	u.id,
                                            CONCAT(u.firstname, ' ', u.lastname) as 'name',
                                            uicd.jobroles
                     FROM		{user}	    					u
                        JOIN	{user_info_competence_data}	  	uicd 	ON  	uicd.userid    	= u.id
                                                                        AND 	uicd.companyid  = :company
                     WHERE		u.deleted 	 = 0
                        AND     u.id 		IN ($my_users)
                     ORDER BY   u.firstname, u.lastname ";


            /* Execute          */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $jr_users = array_flip(explode(',',$instance->jobroles));
                    if (array_intersect_key($job_keys,$jr_users)) {

                            /* User Info    */
                            $info       = new stdClass();
                            $info->id               = $instance->id;
                            $info->name             = $instance->name;
                            $info->courses          = array_flip(array_keys($outcomeCourses));
                            $coursesEnrol = self::GetInfo_CoursesEnrol($instance->id,$courses);

                            if ($outcomeCourses) {
                                foreach ($outcomeCourses as $courseOut) {
                                    if ($coursesEnrol) {
                                        if (array_key_exists($courseOut->id,$coursesEnrol)) {
                                            $courseInfo = new stdClass();
                                            $courseInfo->completion = $coursesEnrol[$courseOut->id];
                                            $info->courses[$courseOut->id] = $courseInfo;
                                        }else {
                                            $info->courses[$courseOut->id] = null;
                                        }//if_courses_enrol
                                    }else {
                                        $info->courses[$courseOut->id] = null;
                                    }//if_courseEnrol
                                }//for_coursesOutcome
                            }//if_courses

                            /* Add User     */
                            $users[$instance->id] = $info;
                    }//if_job_roles
                }//for_each
            }//if_rdo

            return $users;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_EmployeeTracker


    /**
     * @param           $user_id
     * @param           $courses
     * @return          array
     * @throws          Exception
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the courses where the user is enrolled
     *
     * Completed \ Not Completed
     *      [id]    --> time completed
     */
    private static function GetInfo_CoursesEnrol($user_id,$courses) {
        /* Variables    */
        global $DB;
        $courseEnrol    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT	DISTINCT c.id,
                            IF (cc.timecompleted,cc.timecompleted,0) as 'completed'
                     FROM			{course}				c
                        JOIN		{enrol}				    e	ON		e.courseid 	= c.id
                                                                AND		e.status	= 0
                        JOIN		{user_enrolments}		ue	ON 		ue.enrolid	= e.id
                                                                AND		ue.userid	= :user
                        LEFT JOIN	{course_completions}	cc	ON	    cc.course 	= e.courseid
                                                                AND		cc.userid	= ue.userid
                     WHERE		c.id IN ($courses)
                     ORDER BY	c.fullname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $courseEnrol[$instance->id] = $instance->completed;
                }//for_each_course
            }//if_rdo

            return $courseEnrol;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInfo_CoursesEnrol

    /**
     * @param           $courses
     * @return          string
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header to the employee report
     */
    private static function AddHeader_EmployeeReport($courses) {
        /* Variables    */
        $header = '';

        $header .= html_writer::start_tag('thead',array('class' => 'head_employee'));
            $header .= html_writer::start_tag('tr');
                /* User Col     */
                $header .= html_writer::start_tag('th',array('class' => 'user_head'));
                $header .= html_writer::end_tag('th');
                /* Course Col   */
                foreach ($courses as $course) {
                    $header .= html_writer::start_tag('th',array('class' => 'col_head'));
                        $header .= html_writer::start_tag('div',array('class' => 'rotate'));
                            $header .= html_writer::start_tag('p',array('class' => 'vertical_text'));
                                $header .= $course->name;
                            $header .= html_writer::end_tag('p');
                        $header .= html_writer::end_tag('div');
                    $header .= html_writer::end_tag('th');
                }//for_courses
            $header .= html_writer::end_tag('tr');
        $header .= html_writer::end_tag('thead');

        return $header;
    }//AddHeader_EmployeeReport

    /**
     * @param           $users
     * @param           $expiration
     * @param           $completed_time
     * @return          string
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content to the employee report
     */
    private static function AddContent_EmployeeReport($users,$expiration,$completed_time) {
        /* Variables    */
        $content = '';

        $content .= html_writer::start_tag('tbody');
            foreach ($users as $user) {
                $content .= html_writer::start_tag('tr');
                    /* USer  Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'user'));
                        $content .=  $user->name;
                    $content .= html_writer::end_tag('td');

                    $courses = $user->courses;
                    foreach ($courses as $course) {
                        /* Course Col     */
                        if ($course) {
                            if ($course->completion) {
                                $ts = strtotime($expiration  . ' month', $course->completion);

                                if ($ts < time()) {
                                    $content .= html_writer::start_tag('td',array('class' => 'expired'));
                                        $content .= '<u>' . get_string('outcome_course_expired','local_tracker_manager') . '</u>';
                                    $content .= html_writer::end_tag('td');
                                }else {
                                    $expiration_time = CompetenceManager::Get_CompletedDate_Timestamp($completed_time,true);
                                    if ($ts < $expiration_time) {
                                        $content .= html_writer::start_tag('td',array('class' => 'valid'));
                                            $content .= '<u>' . get_string('outcome_valid_until','local_tracker_manager') . '</u>: ' . '</br>' . userdate($ts,'%d.%m.%Y',99,false);
                                        $content .= html_writer::end_tag('td');
                                    }else {
                                        $content .= html_writer::start_tag('td',array('class' => 'completed'));
                                            $content .= '<u>' . get_string('outcome_course_finished','local_tracker_manager') . '</u>';
                                        $content .= html_writer::end_tag('td');
                                    }//if_valid_finis
                                }
                            }else {
                                $content .= html_writer::start_tag('td',array('class' => 'not_completed'));
                                    $content .= get_string('outcome_course_started','local_tracker_manager');
                                $content .= html_writer::end_tag('td');
                            }//if_else_course_completion
                        }else {
                            $content .= html_writer::start_tag('td',array('class' => 'not_enroll'));
                                $content .= get_string('outcome_course_not_enrolled','local_tracker_manager');
                            $content .= html_writer::end_tag('td');
                        }
                    }//courses
                $content .= html_writer::end_tag('tr');
            }//for_users
        $content .= html_writer::end_tag('tbody');

        return $content;
    }//AddContent_EmployeeReport
}//EmployeeReport



