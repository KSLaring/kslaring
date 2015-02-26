<?php
/**
 * Library code for the Company Report Competence Manager.
 *
 * @package     report
 * @subpackage  manager/company_report
 * @copyright   2010 eFaktor
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  17/06/2014
 * @author      eFaktor     (fbv)
 *
 */

define('COMPANY_REPORT_FORMAT_SCREEN', 0);
define('COMPANY_REPORT_FORMAT_SCREEN_EXCEL', 1);
define('COMPANY_REPORT_FORMAT_LIST', 'report_format_list');

class company_report {
    protected static $company;
    protected static $not_allowed;
    private   static $users_filter;

    /* CONSTRUCTOR      */
    public function __construct() {
        global $USER;

        /* My Company           */
        $my_company =  new stdClass();
        $my_company->id = report_manager_getCompanyUser($USER->id);
        $my_company->name   = '';

        if ($my_company->id) {
            $my_company->name = self::GetNames_MyCompanies($my_company->id);
        }//my_company
        self::$company = $my_company;
        self::$not_allowed = self::company_report_UsersNotMyCompanies($my_company->id);
    }

    /* PUBLIC GET       */
    public static function get_MyCompany() {
        return self::$company;
    }//get_Mycompany

    public static function get_UsersFilter() {
        return self::$users_filter;
    }//set_UsersFilter

    /* PUBLIC SET       */
    public static function set_UsersFilter($lst_users) {
        self::$users_filter = $lst_users;
    }//set_UsersFilter


    /* PUBLIC FUNCTIONS */

    /**
     * @param           $ufiltering
     * @return          array
     *
     * @creationDate    19/03/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the users with the filter criteria
     */
    public static function company_report_getSelectionDate($ufiltering) {
        global $SESSION, $DB, $CFG;

        // get the SQL filter
        list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

        $total  = $DB->count_records_select('user', "id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

        $acount = $DB->count_records_select('user', $sqlwhere, $params);
        $scount = count($SESSION->bulk_users);

        $userlist = array('acount'=>$acount, 'scount'=>$scount, 'ausers'=>false, 'susers'=>false, 'total'=>$total);
        $userlist['ausers'] = $DB->get_records_select_menu('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname', 0, MAX_BULK_USERS);

        if ($scount) {
            if ($scount < MAX_BULK_USERS) {
                $in = implode(',', $SESSION->bulk_users);
            } else {
                $bulkusers = array_slice($SESSION->bulk_users, 0, MAX_BULK_USERS, true);
                $in = implode(',', $bulkusers);
            }

            $userlist['susers'] = $DB->get_records_select_menu('user', "id in ($in) ", null, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
        }

        return $userlist;
    }//company_report_getSelectionDate

    /**
     * @param           $ufiltering
     * @creationDate    19/03/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the users to the selection form
     */
    public static function company_report_AddSelectionAll($ufiltering) {
        global $SESSION, $DB, $CFG;

        list($sqlwhere, $params) = $ufiltering->get_sql_filter("id<>:exguest AND deleted <> 1", array('exguest'=>$CFG->siteguest));

        $rs = $DB->get_recordset_select('user', $sqlwhere, $params, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');
        foreach ($rs as $user) {
            if (!isset($SESSION->bulk_users[$user->id])) {
                $SESSION->bulk_users[$user->id] = $user->id;
            }
        }
        $rs->close();
    }//company_report_AddSelectionAll

    /**
     * @static
     * @param           $company
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    26/04/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the users that are not connected to my companies
     */
    public static function company_report_UsersNotMyCompanies($company) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['rgcompany'] = 'rgcompany';

            if (!$company) {
                $company = 0;
            }

            /* SQL Instruction  */
            $sql = " SELECT		u.id
                     FROM		{user}              u
                        JOIN    {user_info_data}	uid ON uid.userid 		= u.id
                        JOIN	{user_info_field}	uif ON uif.id			=	uid.fieldid
                                                        AND	uif.datatype 	=  :rgcompany
                     WHERE		uid.data NOT IN ($company)
                        AND     u.deleted = 0 ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $users = implode(',',array_keys($rdo));
                return $users;
            }else {
                return null;
            }//if_rdo
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_UsersNotMyCompanies

    /**
     * @static
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all information connected with the company
     */
    public static function company_report_GetTracker() {
        try {
            /* Report Info */
            $report = new stdClass();
            $report->company    = self::$company->name;
            $report->my_users   = self::company_report_getMyUsers();
            $report->outcomes   = self::company_report_getOutcomesCourses();

            return $report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_GetTracker

    /**
     * @static
     * @param           $report
     * @return          string
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the report on screen
     */
    public static function company_report_PrintTracker($report) {
        /* Variables    */
        $out_report = '';
        $out_content        = '';
        $courses_outcome    = '';
        $individual         = '';

        try {
            $out_report .= html_writer::start_div('company_rpt_div');
                $out_report .= html_writer::link($report->return,get_string('return_to_selection','local_tracker_manager'));
                    $out_report .= '</br></br>';
                        /* ADD USERS    */
                        if ($report->my_users) {
                            /* Toggle   */
                            $url_img  = new moodle_url('/pix/t/expanded.png');
                            foreach($report->my_users as $user) {
                                /* My Outcomes  - My Courses */
                                $id_toggle = 'YUI_' . $user->id;
                                $out_report .= self::company_report_AddHeaderUser($user->name,$id_toggle,$url_img);

                                $out_report .= html_writer::start_tag('div',array('class' => 'company_list','id'=> $id_toggle . '_div'));
                                    /* OUTCOMES - COURSES   */
                                    if ($report->outcomes) {
                                        $user_jr = explode(',',$user->job_roles);
                                        foreach ($report->outcomes as $outcome) {
                                            if ($outcome->courses && !empty($outcome->courses)) {
                                                $out_jr  = explode(',',$outcome->job_roles);
                                                $job_roles= array_intersect($user_jr,$out_jr);
                                                if ($job_roles) {
                                                $id_toggle_outcome = $id_toggle . '_' . $outcome->id;
                                                $out_report .= html_writer::start_tag('div',array('class' => 'outcome_div'));
                                                    /* Header Outcome */
                                                    $out_report .= self::company_report_AddHeaderOutcomeIndividual($outcome->name);

                                                    /* Job Role - Header    */
                                                    if ($outcome->job_roles) {
                                                        $out_report .= html_writer::start_tag('div',array('class' => 'jr_header'));
                                                            $jr_id = implode(',',$job_roles);
                                                            $out_report .= self::company_report_getJobRolesNames($jr_id);
                                                        $out_report .= html_writer::end_tag('div');//jr_header
                                                    }//if_job_roles

                                                    /* Table Header */
                                                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_list'));
                                                        $out_report .= self::company_report_AddHeaderTable($id_toggle_outcome,$url_img);
                                                    $out_report .= html_writer::end_tag('div');//outcome_list

                                                    /* Table Content    */
                                                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_list','id' => $id_toggle_outcome . '_div'));
                                                            $out_report .= self::company_report_AddTableContentUser($outcome->courses,$user->id,$outcome->expiration,$user->courses_outcomes);
                                                    $out_report .= html_writer::end_tag('div');//outcome_list
                                                $out_report .= html_writer::end_tag('div');//outcome_div
                                                }//if_job_roles
                                            }//if_courses
                                        }//outcomes
                                    }//if_outcomes

                                    /* Individual - COURSES     */
                                    $individual = self::company_report_getIndividualCourses($user->courses_outcomes,$user->id);
                                    if ($individual) {
                                        $id_toggle_individual = $id_toggle . '_0_';
                                        $out_report .= '</br></br>';
                                        $out_report .= html_writer::start_tag('div',array('class' => 'outcome_div'));
                                            /* Header Individual    */
                                            $out_report .= self::company_report_AddHeaderOutcomeIndividual(get_string('individual_courses','local_tracker_manager'));

                                            /* Table    - Header */
                                            $out_report .= html_writer::start_tag('div',array('class' => 'outcome_list'));
                                                $out_report .= self::company_report_AddHeaderTable($id_toggle_individual,$url_img,true);
                                            $out_report .= html_writer::end_tag('div');//outcome_list

                                            /* Table Content    */
                                            $out_report .= html_writer::start_tag('div',array('class' => 'outcome_list','id' => $id_toggle_individual . '_div'));
                                                $out_report .= self::company_report_AddTableContentUserIndividual($individual,$user->id);
                                            $out_report .= html_writer::end_tag('div');//outcome_list
                                        $out_report .= html_writer::end_tag('div');//outcome_div

                                    }//if_individual_course
                                $out_report .= html_writer::end_tag('div');//company_list
                                $out_report .= '<hr class="line_rpt">';
                            }//users
                        }//if_my_users
                    $out_report .= '</br></br>';
                $out_report .= html_writer::link($report->return,get_string('return_to_selection','local_tracker_manager'));
            $out_report .= html_writer::end_div();//company_rpt_div

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_PrintTracker


    /**
     * @static
     * @param           $report
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Company Report Into Excel File
     */
    public static function company_report_DownloadCompanyReport($report) {
        /* Variables    */
        global $CFG;


        try {
            $individual         = array();
            $outcomes_courses   = array();

            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($report->company . '_' . $time . ".xls");

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* One Outcome -- One Sheet    */
            if ($report->my_users) {
                foreach($report->my_users as $user) {
            if ($report->outcomes) {
                        $user_jr = explode(',',$user->job_roles);
                foreach($report->outcomes as $outcome) {
                            $outcomes_courses[$outcome->id][$user->id] = null;
                            $info = new stdClass();
                            $info->not_completed    = null;
                            $info->completed        = null;
                            $info->not_enrol        = null;

                            if ($outcome->courses && !empty($outcome->courses)) {
                                $out_jr  = explode(',',$outcome->job_roles);
                                $job_roles= array_intersect($user_jr,$out_jr);
                                if ($job_roles) {

                                    /* Not Completed    */
                                    $info->not_completed = self::company_report_getEnrolNotCompleted($outcome->courses,$user->id);
                                    if ($info->not_completed) {
                                        if ($user->courses_outcomes) {
                                            $user->courses_outcomes .= ',';
                                        }
                                        $user->courses_outcomes .= implode(',',array_keys($info->not_completed));
                                    }//if_not_completed

                                    /* Completed        */
                                    $info->completed = self::company_report_getEnrolCompleted($outcome->courses,$user->id);
                                    if ($info->completed) {
                                        if ($user->courses_outcomes) {
                                            $user->courses_outcomes .= ',';
                                        }
                                        $user->courses_outcomes .= implode(',',array_keys($info->completed));
                                    }//if_completed

                                    /* Not Enrol        */
                                    $info->not_enrol = self::company_report_getNotEnrol($outcome->courses,$info->not_completed,$info->completed);

                                    $outcomes_courses[$outcome->id][$user->id] = $info;
                                }//job_roles
                            }//if_courses
                }//for_outcomes
                    }//if_report_outcome

            /* Individual Courses   */
                    $individual[$user->id] = self::company_report_getIndividualCourses($user->courses_outcomes,$user->id);
                }//for_users
            }//if_my_users

            /* Add Outcome Courses      */
            self::company_report_CreateExcelSheet($report->company,$report->outcomes,$outcomes_courses,$report->my_users,$export);
            /* Add Individual Courses   */
            self::company_report_AddIndividualCourses($report->company,$individual,$report->my_users,$export);

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_DownloadCompanyReport

    /* PROTECTED FUNCTIONS  */

    protected static function company_report_getJobRolesNames($job_roles) {
        global $DB;

        try {
            /* SQL Instruction  */
            $sql = " SELECT   jr.name
                     FROM     {report_gen_jobrole}  jr
                     WHERE    jr.id in ($job_roles)
                     ORDER BY jr.name ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                $lst_job_roles = array();
                foreach ($rdo as $instance) {
                    $lst_job_roles[] = $instance->name;
                }//for_rdo

                return implode(',',$lst_job_roles);
            }else {
                return null;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_getJobRolesNames

    /**
     * @static
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get information about the outcomes
     */
    protected static function company_report_getOutcomesCourses() {
        /* Variables    */
        global $DB;

        try {
            /* Outcomes         */
            $lst_outcomes = array();

            /* SQL Instruction  */
            $sql = " SELECT		go.id,
                                go.fullname,
                                GROUP_CONCAT(DISTINCT c.id ORDER BY c.fullname SEPARATOR ',') as 'courses',
                                GROUP_CONCAT(DISTINCT jr.id ORDER BY jr.name SEPARATOR ',') as 'job_roles',
                                oe.expirationperiod
                     FROM		    {grade_outcomes}			    go
                        JOIN	    {grade_outcomes_courses}	    goc	    ON 		goc.outcomeid 	= go.id
                        JOIN	    {course}					    c	    ON		c.id 			= goc.courseid
                                                                            AND		c.visible 		= 1
                        LEFT JOIN 	{report_gen_outcome_exp}	    oe	    ON		oe.outcomeid	= go.id
                        LEFT JOIN	{report_gen_outcome_jobrole}	jro		ON		jro.outcomeid	= go.id
                        LEFT JOIN	{report_gen_jobrole}			jr		ON		jr.id 			= jro.jobroleid
                     GROUP BY	go.id
                     ORDER BY	go.fullname ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $outcome) {
                    $info = new stdClass();
                    $info->id           = $outcome->id;
                    $info->name         = $outcome->fullname;
                    $info->courses      = $outcome->courses;
                    $info->expiration   = $outcome->expirationperiod;
                    $info->job_roles    = $outcome->job_roles;
                    $lst_outcomes[$outcome->id] = $info;
                }//for_rdo
            }//if_rdo

            return $lst_outcomes;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_getOutcomesCourses

    /**
     * @static
     * @param           $courses_outcome
     * @param           $user_id
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the courses are not connected with outcomes
     */
    protected static function company_report_getIndividualCourses($courses_outcome,$user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT c.id
                     FROM		{course}                    c
                        JOIN	{enrol}						e	ON		e.courseid   	= c.id
                        JOIN	{user_enrolments}			ue	ON		ue.enrolid 		= e.id
                                                                AND		ue.userid		= :user
                     WHERE		c.visible = 1
                        AND 	c.id 	!= 1 ";

            if ($courses_outcome) {
                $sql .= " AND c.id NOT IN ($courses_outcome) ";
            }
            $sql .= " ORDER BY	c.fullname ASC ";

            /* Execute          */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $lst_courses = array();
                foreach ($rdo as $course) {
                    $lst_courses[] = $course->id;
                }

                return implode(',',$lst_courses);
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_getIndividualCourses

    /**
     * @static
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get information about all the user connected with my company
     */
    protected static function company_report_getMyUsers() {
        /* Variables    */
        global $DB, $USER;
        $filter_users   = null;
        $lst_users      = array();

        try {
            /* Get Users Filter */
            $filter_users = self::get_UsersFilter();
            if ($filter_users) {
                $filter_users = implode(',',$filter_users);
            }//if_SESSION

            /* Search Params    */
            $params = array();
            $params['user'] = $USER->id;
            $params['jtype']    = 'rgjobrole';

            /* Sql Instruction  */
            $sql = " SELECT		DISTINCT 	u.id,
                                            CONCAT(u.firstname, ' ', u.lastname) as 'user_name',
                                            uid.data as 'job_roles'
                     FROM		{user}	    u
                        JOIN	{user_info_data} 	  uid 	ON  	uid.userid    	= u.id
                        JOIN	{user_info_field} 	  uif 	ON 		uif.id 	   	    = uid.fieldid
                                                            AND		uif.datatype  	= :jtype
                     WHERE		u.deleted = 0
                        AND     u.id <> :user
                        AND     u.id <> 1 ";

            /* Remove not allowed   */
            $not_allowed = self::$not_allowed;
            if (self::$not_allowed) {
                $sql .= " AND u.id NOT IN ($not_allowed) ";
            }//if_not_allowed

            /* Only My users    */
            if ($filter_users) {
                $sql .= " AND u.id IN ($filter_users) ";
            }//if_filter_users

            /* Order    */
            $sql .= '   GROUP BY 	u.id
                        ORDER BY user_name ';

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $my_user) {
                    $user_info = new stdClass();
                    $user_info->id              = $my_user->id;
                    $user_info->name            = $my_user->user_name;
                    $user_info->job_roles       = $my_user->job_roles;
                    $user_info->courses_outcomes    = null;

                    $lst_users[$my_user->id] = $user_info;
                }
            }//if_rdo

            return $lst_users;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_getMyUsers

    /**
     * @static
     * @param           $courses_outcomes
     * @param           $user_id
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all courses that the user hasn't completed yet
     */
    protected static function company_report_getEnrolNotCompleted($courses_outcomes,$user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user']     = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT c.id,
                                c.fullname
                      FROM		{course}		      c
                         JOIN	{enrol}				  e		ON 		e.courseid  = c.id
                                                            AND		e.status    = 0
                         JOIN	{user_enrolments}	  ue	ON		ue.enrolid	= e.id
                                                            AND		ue.userid	= :user
                         JOIN 	{course_completions}  cc	ON		cc.course   = c.id
                                                            AND		cc.userid   = ue.userid
                                                            AND		cc.timecompleted IS NULL
                      WHERE		c.id IN ($courses_outcomes)
                      ORDER BY	c.fullname ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $lst_courses = array();
                foreach ($rdo as $course) {
                    $info = new stdClass();
                    $info->name         = $course->fullname;

                    $lst_courses[$course->id] = $info;
                }//for_rdo

                return $lst_courses;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_getEnrolNotCompleted

    /**
     * @static
     * @param           $courses_outcomes
     * @param           $user_id
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all courses that the users has already completed
     */
    protected static function company_report_getEnrolCompleted($courses_outcomes,$user_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user']     = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT c.id,
                                c.fullname,
                                cc.timecompleted
                      FROM		{course}		      c
                         JOIN	{enrol}				  e		ON 		e.courseid  = c.id
                                                            AND		e.status    = 0
                         JOIN	{user_enrolments}	  ue	ON		ue.enrolid	= e.id
                                                            AND		ue.userid	= :user
                         JOIN 	{course_completions}  cc	ON		cc.course   = c.id
                                                            AND		cc.userid   = ue.userid
                                                            AND		cc.timecompleted IS NOT NULL
                      WHERE		c.id IN ($courses_outcomes)
                      ORDER BY	c.fullname ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $lst_courses = array();
                foreach ($rdo as $course) {
                    $info = new stdClass();
                    $info->name         = $course->fullname;
                    $info->completed    = $course->timecompleted;

                    $lst_courses[$course->id] = $info;
                }//for_rdo

                return $lst_courses;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_getEnrolCompleted

    /**
     * @static
     * @param           $courses
     * @param           $not_completed
     * @param           $completed
     * @return          array|null
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all courses that the user is not enrolled yet
     */
    protected static function company_report_getNotEnrol($courses,$not_completed,$completed) {
        /* Variables    */
        global $DB;
        $lst_courses    = array();
        $enrol          = array();
        $lst_enrol      = null;

        if ($not_completed) {
            $enrol = array_merge(array_keys($not_completed));
        }//if_not_completed

        if ($completed) {
            if ($enrol) {
                $enrol = array_merge($enrol,array_keys($completed));
            }else {
                $enrol = array_merge(array_keys($completed));
            }//if_enrol
        }//if_completed

        if ($enrol) {
            $lst_enrol = implode(',',$enrol);
        }//if_enrol


        /* SQL Instruction  */
        $sql = " SELECT		c.id,
                            c.fullname
                 FROM	    {course}				c
                 WHERE      c.id IN ($courses) ";

        if ($lst_enrol) {
            $sql .= " AND c.id NOT IN ($lst_enrol) ";
        }//if_lst_enrol

        $sql .= " ORDER BY c.fullname ASC ";
        /* Execute  */
        $rdo = $DB->get_records_sql($sql);
        if ($rdo) {
            foreach ($rdo as $course) {
                $info = new stdClass();
                $info->name         = $course->fullname;

                $lst_courses[$course->id] = $info;
            }//for_rdo

            return $lst_courses;
        }else {
            return null;
        }//if_rdo
    }//company_report_getNotEnrol

    /**
     * @static
     * @param           $user_name
     * @param           $id_toggle
     * @param           $url_img
     * @return          string
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the User name to the report
     */
    protected static function company_report_AddHeaderUser($user_name,$id_toggle,$url_img) {
        /* Variables   */
        $header_user = '';

        $header_user .= html_writer::start_div('header_user_rpt');
            /* Col One  */
            $header_user .= html_writer::start_div('header_col_one');
                $header_user .= '<button class="toggle" type="image" id="' . $id_toggle . '"><img id="' . $id_toggle . '_img' . '" src="' . $url_img . '">' . '</button>';
            $header_user .= html_writer::end_div('');
            /* Col Two  */
            $header_user .= html_writer::start_div('header_col_two');
                $header_user .= '<h3>'. $user_name . '</h3>';
            $header_user .= html_writer::end_div('');
        $header_user .= html_writer::end_div('');

        return $header_user;
    }//company_report_AddHeaderUser

    /**
     * @static
     * @param           $header
     * @return          string
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbV)
     *
     * Description
     * Add the title for the outcome table and Individual table
     */
    protected static function company_report_AddHeaderOutcomeIndividual($header) {
        /* Varaibles    */
        $header_out = '';

        $header_out .= html_writer::start_tag('div',array('class' => 'outcome_header'));
            /* Col One  */
            $header_out .= html_writer::start_div('header_col_one');
            $header_out .= html_writer::end_div('');
            /* Col Two  */
            $header_out .= html_writer::start_div('header_col_two');
                $header_out .= '<h3>'. $header . '</h3>';
            $header_out .= html_writer::end_div('');
        $header_out .= html_writer::end_tag('div');//outcome_header

        return $header_out;
    }//company_report_AddHeaderOutcome

    /**
     * @static
     * @param           $id_toggle
     * @param           $url
     * @param           bool $individual
     * @return          string
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the courses table
     */
    protected static function company_report_AddHeaderTable($id_toggle,$url,$individual = false) {
        /* Varaibale    */
        $header_table = '';

        $str_course         = get_string('course');
        $str_state          = get_string('state','local_tracker_manager');
        $str_valid          = get_string('outcome_valid_until','local_tracker_manager');
        $str_completion     = get_string('completion_time','local_tracker_manager');

        $header_table .= html_writer::start_tag('table');
            $header_table .= html_writer::start_tag('tr',array('class' => 'head'));
                /* Button Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'first'));
                    $header_table .= html_writer::start_tag('button',array('id' => $id_toggle, 'class' => 'toggle', 'type' => 'image'));
                        $header_table .= html_writer::start_tag('img',array('class' => 'swicth_img','src' => $url,'id' => $id_toggle . '_img'));
                        $header_table .= html_writer::end_tag('img');
                    $header_table .= html_writer::end_tag('button');
                $header_table .= html_writer::end_tag('td');

                /* Course Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'course'));
                    $header_table .= $str_course;
                $header_table .= html_writer::end_tag('td');

                /* Status Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'status'));
                    $header_table .= $str_state;
                $header_table .= html_writer::end_tag('td');

                /* Completion Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'status'));
                    $header_table .= $str_completion;
                $header_table .= html_writer::end_tag('td');

                /* Valid Col    */
                $header_table .= html_writer::start_tag('td',array('class' => 'status'));
                    if (!$individual) {
                        $header_table .= $str_valid;
                    }//individual
                $header_table .= html_writer::end_tag('td');
            $header_table .= html_writer::end_tag('tr');
        $header_table .= html_writer::end_tag('table');

        return $header_table;
    }//company_report_AddCoursesOutcome

    /**
     * @static
     * @param           $courses_outcome
     * @param           $user_id
     * @param           $expiration
     * @param           $user_courses_out
     * @return          string
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table
     */
    protected static function company_report_AddTableContentUser($courses_outcome,$user_id,$expiration,&$user_courses_out) {
        /* Variables    */
        global $DB;
        $content = '';
        $class   = '';

        try {
            /* Not Completed    */
            $not_completed = self::company_report_getEnrolNotCompleted($courses_outcome,$user_id);
            /* Completed        */
            $completed = self::company_report_getEnrolCompleted($courses_outcome,$user_id);

            $content .= html_writer::start_tag('table');
                /* Not Completed    */
                if ($not_completed) {
                    foreach ($not_completed as $course) {
                        $content .= html_writer::start_tag('tr');
                            /* Button Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            /* Course Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'course'));
                                $content .= $course->name;
                            $content .= html_writer::end_tag('td');
                            /* Status Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                $content .= get_string('outcome_course_started','local_tracker_manager');
                            $content .= html_writer::end_tag('td');

                            /* Completion Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                $content .= '-';
                            $content .= html_writer::end_tag('td');
                            /* Valid Col    */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                    $content .= '-';
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//not_completed

                    if ($user_courses_out) {
                        $user_courses_out .= ',';
                    }
                    $user_courses_out .= implode(',',array_keys($not_completed));
                }//not_completed

                /* Not Enrol        */
                $not_enrol = self::company_report_getNotEnrol($courses_outcome,$not_completed,$completed);
                if ($not_enrol) {
                    foreach ($not_enrol as $course) {
                        $content .= html_writer::start_tag('tr',array('class' => 'not_enroll'));
                            /* Button Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            /* Course Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'course'));
                                $content .= $course->name;
                            $content .= html_writer::end_tag('td');
                            /* Status Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                $content .= get_string('outcome_course_not_enrolled','local_tracker_manager');
                            $content .= html_writer::end_tag('td');

                            /* Completion Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                $content .= '-';
                            $content .= html_writer::end_tag('td');
                            /* Valid Col    */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                    $content .= '-';
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//not_enrol
                }//not_enrol


                /* Completed        */
                if ($completed) {
                    $state = get_string('outcome_course_finished','local_tracker_manager');
                    $valid = ' - ';
                    $class = '';
                    foreach ($completed as $course) {
                            $ts = strtotime($expiration  . ' month', $course->completed);
                            if ($ts < time()) {
                                $class = 'expired';
                                $state = get_string('outcome_course_expired','local_tracker_manager');
                                $valid = ' - ';
                            }else {
                                $state = get_string('outcome_course_finished','local_tracker_manager');
                                $valid = userdate($ts,'%d.%m.%Y', 99, false);
                                $class = 'completed';
                            }//if_ts

                        $content .= html_writer::start_tag('tr',array('class' => $class));
                            /* Button Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'first'));
                            $content .= html_writer::end_tag('td');
                            /* Course Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'course'));
                                $content .= $course->name;
                            $content .= html_writer::end_tag('td');
                            /* Status Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                $content .= $state;
                            $content .= html_writer::end_tag('td');

                            /* Completion Col   */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                $content .= userdate($course->completed,'%d.%m.%Y', 99, false);
                            $content .= html_writer::end_tag('td');

                            /* Valid Col    */
                            $content .= html_writer::start_tag('td',array('class' => 'status'));
                                    $content .= $valid;
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//completed

                    if ($user_courses_out) {
                        $user_courses_out .= ',';
                    }
                    $user_courses_out .= implode(',',array_keys($completed));
                }//if_completed
            $content .= html_writer::end_tag('table');

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_AddTableContentUser

    /**
     * @static
     * @param           $courses_outcome
     * @param           $user_id
     * @return          string
     * @throws          Exception
     *
     * @creationDate    15/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content table for Individual courses
     */
    protected static function company_report_AddTableContentUserIndividual($courses_outcome,$user_id) {
        /* Variables    */
        global $DB;
        $content = '';
        $class   = '';

        try {
            /* Not Completed    */
            $not_completed = self::company_report_getEnrolNotCompleted($courses_outcome,$user_id);
            /* Completed        */
            $completed = self::company_report_getEnrolCompleted($courses_outcome,$user_id);

            $content .= html_writer::start_tag('table');
            /* Not Completed    */
            if ($not_completed) {
                foreach ($not_completed as $course) {
                    $content .= html_writer::start_tag('tr');
                    /* Button Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'first'));
                    $content .= html_writer::end_tag('td');
                    /* Course Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'course'));
                    $content .= $course->name;
                    $content .= html_writer::end_tag('td');
                    /* Status Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                    $content .= get_string('outcome_course_started','local_tracker_manager');
                    $content .= html_writer::end_tag('td');

                    /* Completion Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                    $content .= '-';
                    $content .= html_writer::end_tag('td');
                    /* Valid Col    */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                    $content .= '-';
                    $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//not_completed

            }//not_completed


            /* Completed        */
            if ($completed) {
                $state = get_string('outcome_course_finished','local_tracker_manager');
                $valid = ' - ';
                $class = '';
                foreach ($completed as $course) {
                    $content .= html_writer::start_tag('tr',array('class' => $class));
                    /* Button Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'first'));
                    $content .= html_writer::end_tag('td');
                    /* Course Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'course'));
                    $content .= $course->name;
                    $content .= html_writer::end_tag('td');
                    /* Status Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                    $content .= $state;
                    $content .= html_writer::end_tag('td');

                    /* Completion Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                    $content .= userdate($course->completed,'%d.%m.%Y', 99, false);
                    $content .= html_writer::end_tag('td');

                    /* Valid Col    */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                    $content .= $valid;
                            $content .= html_writer::end_tag('td');
                        $content .= html_writer::end_tag('tr');
                    }//completed
                }//if_completed
            $content .= html_writer::end_tag('table');

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_AddTableContentUser

    /**
     * @static
     * @param           $company
     * @param           $outcomes
     * @param           $outcomes_courses
     * @param           $my_users
     * @param           $export
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create a new Sheet of Excel book
     */
    protected static function company_report_CreateExcelSheet($company,$outcomes,$outcomes_courses,$my_users,&$export) {
        /* Variables    */

        try {
            foreach ($outcomes as $outcome) {
                $row = 0;
            // Adding the worksheet
            $my_xls = $export->add_worksheet($outcome->name);

            /* Courses */
            /* Header Table */
                self::company_report_AddExcelHeaderTable($company,$outcome->name,$row,$my_xls,false);

            foreach ($my_users as $user) {
                    if ($outcomes_courses[$outcome->id][$user->id]) {
                        $my_courses = $outcomes_courses[$outcome->id][$user->id];
                /* Add Not Completed    */
                        if ($my_courses->not_completed) {
                            self::company_report_AddExcelNotCompletedTable($my_courses->not_completed,$user->name,$row,$my_xls,false);
                }//if_not_completed

                /* Add Not Enrol        */
                        if ($my_courses->not_enrol) {
                            self::company_report_AddExcelNotEnrolTable($my_courses->not_enrol,$user->name,$row,$my_xls,false);
                }//if_not_enrol

                /* Add Completed        */
                        if ($my_courses->completed) {
                            self::company_report_AddExcelCompletedTable($my_courses->completed,$user->name,$outcome->expiration,$row,$my_xls,false);
                }//if_compelted

                        $my_xls->merge_cells($row,0,$row,17);
                $row ++;
                    }
            }//for_my_users
            }//for_outcome
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_CreateExcelSheet

    /**
     * @static
     * @param           $company
     * @param           $courses
     * @param           $my_users
     * @param           $export
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the individual courses
     */
    protected static function company_report_AddIndividualCourses($company,$courses,$my_users,&$export) {
        /* Variables */
        $row = 0;

        // Adding the worksheet
        $my_xls = $export->add_worksheet(get_string('individual_courses','local_tracker_manager'));
        /* Header Table */
        self::company_report_AddExcelHeaderTable($company,null,$row,$my_xls,true);

        foreach ($my_users as $user) {
            $my_courses = $courses[$user->id];
            if ($my_courses) {
            /* Not Completed    */
                $not_completed = self::company_report_getEnrolNotCompleted($my_courses,$user->id);
            /* Completed        */
                $completed = self::company_report_getEnrolCompleted($my_courses,$user->id);

            /* Add Not Completed    */
            if ($not_completed) {
                self::company_report_AddExcelNotCompletedTable($not_completed,$user->name,$row,$my_xls,true);
            }//if_not_completed

            /* Add Completed        */
            if ($completed) {
                self::company_report_AddExcelCompletedTable($completed,$user->name,0,$row,$my_xls,true);
            }//if_compelted

                $my_xls->merge_cells($row,0,$row,14);
                $row ++;
            }//my_courses
        }//for_my_users
    }//company_report_AddIndividualCourses

    /**
     * @static
     * @param           $job_roles
     * @param           $row
     * @param           $my_xls
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add job_roles connected with the outcome
     */
    protected static function company_report_AddExcelJob_Role($job_roles,&$row,&$my_xls) {

        try {
            $str_job_role = strtoupper(get_string('job_roles','report_manager'));
            $my_xls->write_string(0, 0, $str_job_role,array('size'=>12, 'name'=>'Arial','bold'=>'1'));

            $lst_job_roles = explode(',',$job_roles);
            $col = 1;
            foreach ($lst_job_roles as $jr) {
                $my_xls->write_string($row, $col, $jr);

                $row ++;
            }//job_roles

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_AddExcelJob_Role

    /**
     * @static
     * @param           $company
     * @param           null | $outcome
     * @param           $row
     * @param           $my_xls
     * @param           bool $individual
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the table
     */
    protected static function company_report_AddExcelHeaderTable($company,$outcome=null,&$row,&$my_xls,$individual = false) {
        /* Varaibles    */
        $str_user           = strtoupper(get_string('user'));
        $str_course         = strtoupper(get_string('course'));
        $str_state          = strtoupper(get_string('state','local_tracker_manager'));
        $str_valid          = strtoupper(get_string('outcome_valid_until','local_tracker_manager'));
        $str_completion     = strtoupper(get_string('completion_time','local_tracker_manager'));

        try {
            /* Company Name  */
            $col = 0;
            $my_xls->write($row, $col, $company,array('size'=>14, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Outcome Name */
            if ($outcome) {
                $row++;
                $title_out          = get_string('outcome', 'report_manager')  . ' - ' . $outcome;
                $my_xls->write($row, $col, $title_out,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $my_xls->set_row($row,25);
            }//if_outcome

            /* Merge Cells  */
            $row ++;
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $row++;
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $row ++;

            /* User     */
            $my_xls->write($row, $col, $str_user,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            /* Course   */
            $col = $col + 3;
            $my_xls->write($row, $col, $str_course,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* State    */
            $col = $col + 6;
            $my_xls->write($row, $col, $str_state,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            /* Completion   */
            $col = $col + 3;
            $my_xls->write($row, $col, $str_completion,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            /* Valid Until  */
            if (!$individual) {
                $col = $col + 3;
                $my_xls->write($row, $col, $str_valid,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);
            }

            $row ++;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_AddExcelHeaderTable

    /**
     * @static
     * @param           $not_completed
     * @param           $user_name
     * @param           $row
     * @param           $my_xls
     * @param           bool $individual
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the courses have not completed by user yet
     */
    protected static function company_report_AddExcelNotCompletedTable($not_completed,$user_name,&$row,&$my_xls,$individual = false) {
        try {
            foreach ($not_completed as $course) {
                $col = 0;
                /* User     */
                $my_xls->write($row, $col, $user_name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                /* Course   */
                $col = $col + 3;
                $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,20);

                /* State    */
                $col = $col + 6;
                $my_xls->write($row, $col, get_string('outcome_course_started','local_tracker_manager'),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                /* Completion   */
                $col = $col + 3;
                $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                /* Valid Until  */
                if (!$individual) {
                    $col = $col + 3;
                    $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);
                }//if_individual

                $row ++;
            }//for_not_completed
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_AddExcelNotCompletedTable

    /**
     * @static
     * @param           $not_enrol
     * @param           $user_name
     * @param           $row
     * @param           $my_xls
     * @param           bool $individual
     * @throws          Exception
     *
     * @completionDate  20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add not enrol courses
     */
    protected static function company_report_AddExcelNotEnrolTable($not_enrol,$user_name,&$row,&$my_xls,$individual = false) {
        try {
            foreach ($not_enrol as $course) {
                $col = 0;
                /* User     */
                $my_xls->write($row, $col, $user_name,array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                /* Course   */
                $col = $col + 3;
                $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,20);

                /* State    */
                $col = $col + 6;
                $my_xls->write($row, $col, get_string('outcome_course_not_enrolled','local_tracker_manager'),array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                /* Completion   */
                $col = $col + 3;
                $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                /* Valid Until  */
                if (!$individual) {
                    $col = $col + 3;
                    $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);
                }//if_individual

                $row ++;
            }//for_not_completed
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_AddExcelNotEnrolTable

    /**
     * @static
     * @param           $completed
     * @param           $user_name
     * @param           $expiration
     * @param           $row
     * @param           $my_xls
     * @param           bool $individual
     * @throws          Exception
     *
     * @creationDate    20/06/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the courses have been completed by user
     */
    protected static function company_report_AddExcelCompletedTable($completed,$user_name,$expiration,&$row,&$my_xls,$individual = false) {
        try {
            $state = get_string('outcome_course_finished','local_tracker_manager');
            $valid = ' - ';
            foreach ($completed as $course) {
                $col = 0;
                if (!$individual) {
                    $ts = strtotime($expiration  . ' month', $course->completed);
                    if ($ts < time()) {
                        $bg_color = '#f2dede';
                        $state = get_string('outcome_course_expired','local_tracker_manager');
                        $valid = ' - ';
                    }else {
                        $state = get_string('outcome_course_finished','local_tracker_manager');
                        $valid = userdate($ts,'%d.%m.%Y', 99, false);
                        $bg_color = '#dff0d8';
                    }//if_ts


                /* User     */
                $my_xls->write($row, $col, $user_name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                /* Course   */
                $col = $col + 3;
                $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,20);

                /* State    */
                $col = $col + 6;
                $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                /* Completion   */
                $col = $col + 3;
                $my_xls->write($row, $col, userdate($course->completed,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+2);
                $my_xls->set_row($row,20);

                /* Valid Until  */
                    $col = $col + 3;
                    $my_xls->write($row, $col, $valid,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);
                }else {
                    /* User     */
                    $my_xls->write($row, $col, $user_name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Course  */
                    $col = $col + 3;
                    $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State        */
                    $col = $col + 6;
                    $my_xls->write($row, $col, $state,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Completion   */
                    $col = $col + 3;
                    $my_xls->write($row, $col, userdate($course->completed,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);
                }//if_not_individual

                $row ++;
            }//for_not_completed
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//company_report_AddExcelCompletedTable

    /* PRIVATE */


    /**
     * @static
     * @param           $my_companies
     * @return          null
     * @throws          Exception
     *
     * @creationDate    13/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the name of my companies
     */
    private static function GetNames_MyCompanies($my_companies) {
        /* Variables    */
        global $DB;

        try {
            /* SQL Instruction  */
            $sql = " SELECT     GROUP_CONCAT(DISTINCT rgc.name ORDER BY rgc.name SEPARATOR '</br>') as 'names'
                     FROM       {report_gen_companydata} rgc
                      WHERE     rgc.id IN ($my_companies) ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                return $rdo->names;
            }//if_rdo

            return null;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetNames_MyCompanies
}//company_report