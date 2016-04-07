<?php
/**
 * Report Competence Manager  - Library code for the Course Report.
 *
 * @package         report
 * @subpackage      manager/course_report
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    17/03/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Library for the Course Report
 *
 */

define('COURSE_REPORT_FORMAT_SCREEN', 0);
define('COURSE_REPORT_FORMAT_SCREEN_EXCEL', 1);
define('COURSE_REPORT_FORMAT_LIST', 'report_format_list');
define('MANAGER_COURSE_STRUCTURE_LEVEL','level_');

class course_report {

    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/


    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the courses available
     */
    public static function Get_CoursesList() {
        /* Variables    */
        global $DB;
        $courses_list = array();

        try {
            /* Get Courses  */
            $rdo = $DB->get_records('course',array('visible' => 1),'fullname','id,fullname');
            if ($rdo) {
                $courses_list[0] = get_string('select') . '...';
                foreach ($rdo as $course) {
                    if ($course->id > 1) {
                        $courses_list[$course->id] =  $course->fullname;
                    }
                }//for_rdo
            }//if_Rdo

            return $courses_list;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CoursesList

    /**
     * @param               $data_form
     * @param               $my_hierarchy
     * @param               $IsReporter
     *
     * @return              null|stdClass
     * @throws              Exception
     *
     * @creationDate        17/03/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get the course report information to display
     *
     * Course Report.
     *      - id
     *      - name
     *      - job_roles.    Array
     *                      [id]    --> industrycode + name
     *      - outcomes.     Array
     *                      [id]
     *                              --> name
     *                              --> expiration
     *      - rpt
     *      - completed_before
     *      - levelZero.    Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> levelOne.   Array
     *
     *      - levelOne. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelTwo.   Array
     *      - levelTwo. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelThree. Array
     *
     *
     *      - levelThree.   Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> completed.      Array
     *                                              --> name
     *                                              --> completed
     *                          --> not_completed.  Array
     *                                              --> name
     *                          --> not_enrol.      Array
     *                                              --> name
     */
    public static function Get_CourseReportLevel($data_form,$my_hierarchy,$IsReporter) {
        /* Variables    */
        global $USER;
        $companies_report   = null;
        $course_report      = null;
        $course_id          = null;
        $job_role_list      = null;
        $levelZero          = null;
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;
        $selectorThree      = null;
        $inZero             = null;
        $inOne              = null;
        $inTwo              = null;
        $inThree            = null;

        try {
            /* Course Report - Basic Information */
            $course_id     = $data_form[REPORT_MANAGER_COURSE_LIST];
            $course_report = self::Get_CourseBasicInfo($course_id);

            /* Clean Temporary */
            self::CleanTemporary($course_id);

            /* Get the rest of data to display          */
            /* Users and status of each user by company */
            if ($course_report) {
                $course_report->rpt                = $data_form['rpt'];
                $course_report->completed_before   = $data_form[REPORT_MANAGER_COMPLETED_LIST];

                /* Get My Companies by Level    */
                if (($IsReporter) && !is_siteadmin($USER->id)) {
                    $inZero  = $my_hierarchy->competence->levelZero;
                    $inOne   = $my_hierarchy->competence->levelOne;
                    $inTwo   = $my_hierarchy->competence->levelTwo;
                    $inThree = $my_hierarchy->competence->levelThree;
                }else {
                    list($inZero,$inOne,$inTwo,$inThree) = CompetenceManager::GetMyCompanies_By_Level($my_hierarchy->competence,$my_hierarchy->my_level);
                    $inZero     = implode(',',$inZero);
                    $inOne      = implode(',',$inOne);
                    $inTwo      = implode(',',$inTwo);
                    $inThree    = implode(',',$inThree);
                }//if_reporter

                /* Job Roles Selected   */
                $course_report->job_roles = self::Get_JobRolesCourse_Report($data_form);

                /* Get Companies with Employees  */
                $companiesEmployees         = CompetenceManager::GetCompanies_WithEmployees();
                if ($companiesEmployees) {
                    /* Level One    */
                    if ($inOne) {
                        $inOne = array_intersect($inOne,array_keys($companiesEmployees->levelOne));
                    }else {
                        $inOne = $companiesEmployees->levelOne;
                    }
                    $inOne = implode(',',$inOne);

                    /* Level Two   */
                    if ($inTwo) {
                        $inTwo = array_intersect($inTwo,array_keys($companiesEmployees->levelTwo));
                    }else {
                        $inTwo = $companiesEmployees->levelTwo;
                    }
                    $inTwo = implode(',',$inTwo);

                    /* Level Three  */
                    if ($inThree) {
                        $inThree = array_intersect($inThree,array_keys($companiesEmployees->levelThree));
                    }else {
                        $inThree = $companiesEmployees->levelThree;
                    }
                    $inThree = implode(',',$inThree);

                    /* Get information to display by level          */
                    /* Level zero    - That's common for all levels  */
                    $course_report->levelZero = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'];
                    $USER->levelZero    = $course_report->levelZero;
                    $USER->courseReport = $course_id;

                    /* Get Info Course   */
                    if (isset($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3'])) {
                        if (!in_array(0,$data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3'])) {
                            $levelThree = array_flip($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3']);
                        }
                    }

                    self::GetUsers_EnrolledIn($course_id,$course_report->job_roles,$levelThree);
                    self::GetUsers_NotEnrolIn($course_id,$course_report->job_roles,$levelThree);

                    /* Check Level  */
                    switch ($data_form['rpt']) {
                        case 0:
                            /* Level Zero    */
                            /* Get info connected with Level Zero */
                            $levelOne   = CompetenceManager::GetCompanies_LevelList(1,$data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'],$inOne);
                            unset($levelOne[0]);
                            if ($levelOne) {
                                self::Get_CompanyReportInfo_LevelOne($course_report,$levelOne,$inTwo,$inThree);
                            }else {
                                $course_report->levelOne = null;
                            }//if_levelZero_Companies

                            break;
                        case 1:
                            /* Level One    */
                            $levelOne = new stdClass();
                            $levelOne->id           = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];
                            $levelOne->name         = CompetenceManager::GetCompany_Name($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1']);
                            $levelOne->levelTwo     = null;

                            /* GEt info connected with Level One */
                            $levelTwo   = CompetenceManager::GetCompanies_LevelList(2,$data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'],$inTwo);
                            unset($levelTwo[0]);
                            if ($levelTwo) {
                                $levelOne->levelTwo      = self::Get_CompanyReportInfo_LevelTwo($course_report,$levelTwo,$inThree);
                                if ($levelOne->levelTwo) {
                                    $course_report->levelOne[$levelOne->id]  = $levelOne;
                                }else {
                                    $levelOne->levelTwo = null;
                                    $course_report->levelOne[$levelOne->id] = $levelOne;
                                }
                            }else {
                                $levelOne->levelTwo = null;
                                $course_report->levelOne[$levelOne->id] = $levelOne;
                            }//if_level_two_companies

                            break;
                        case 2:
                            /* Level One    */
                            $levelOne = new stdClass();
                            $levelOne->id                               = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];
                            $levelOne->name                             = CompetenceManager::GetCompany_Name($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1']);
                            $levelOne->levelTwo                         = null;
                            $course_report->levelOne[$levelOne->id]     = $levelOne;

                            /* Level Two    */
                            $levelTwo = new stdClass();
                            $levelTwo->id           = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2'];
                            $levelTwo->name         = CompetenceManager::GetCompany_Name($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2']);
                            $levelTwo->levelThree   = null;

                            /* GEt info connected with Level Two */
                            $levelThree     = CompetenceManager::GetCompanies_LevelList(3,$data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2'],$inThree);
                            unset($levelThree[0]);
                            if ($levelThree) {
                                $levelTwo->levelThree      = self::Get_CompanyReportInfo_LevelThree($course_report,$levelThree);
                                if ($levelTwo->levelThree) {
                                    $course_report->levelTwo[$levelTwo->id] = $levelTwo;
                                }else {
                                    $levelTwo->levelThree = null;
                                    $course_report->levelTwo[$levelTwo->id] = $levelTwo;
                                }
                            }else {
                                $levelTwo->levelThree = null;
                                $course_report->levelTwo[$levelTwo->id] = $levelTwo;
                            }//if_level_two_companies

                            break;
                        case 3:
                            /* Level One    */
                            $levelOne = new stdClass();
                            $levelOne->id                               = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];
                            $levelOne->name                             = CompetenceManager::GetCompany_Name($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1']);
                            $levelOne->levelTwo                         = null;
                            $course_report->levelOne[$levelOne->id]     = $levelOne;

                            /* Level Two    */
                            $levelTwo = new stdClass();
                            $levelTwo->id                               = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2'];
                            $levelTwo->name                             = CompetenceManager::GetCompany_Name($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2']);
                            $levelTwo->levelThree                       = null;
                            $course_report->levelTwo[$levelTwo->id]     = $levelTwo;

                            /* Get Info connected with the level three  */
                            $levelThree = CompetenceManager::GetCompanies_LevelList(3,$data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2'],$inThree);
                            unset($levelThree[0]);
                            $selectorThree = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3'];
                            unset($selectorThree[0]);
                            if ($selectorThree) {
                                $company_keys   = array_keys($levelThree);
                                $companies      = array_intersect($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3'],$company_keys);
                                $companies      = array_fill_keys($companies,null);
                                $levelThree     = array_intersect_key($levelThree,$companies);
                            }

                            /* Level Three  */
                            if ($levelThree) {
                                $course_report->levelThree   = self::Get_CompanyReportInfo_LevelThree($course_report,$levelThree);
                            }else {
                                $course_report->levelThree = null;
                            }//if_levelThree

                            break;
                        default:
                            $course_report = null;

                            break;
                    }//switch_level
                }
            }//if_course_report

            return $course_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CourseReportLevel

    /**
     * @param       null $courseId
     *
     * @throws          Exception
     *
     * @creationDate    30/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean temporary table
     */
    public static function CleanTemporary($courseId = null) {
        /* Variables    */
        global $DB;
        $params = array();

        try {
            /* Criteria */
            $params['manager']  = $_SESSION['USER']->sesskey;
            $params['report']   = 'course';
            if ($courseId) {
                $params['courseid'] = $courseId;
            }//if_outcome

            /* Execute  */
            $DB->delete_records('report_gen_temp',$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CleanTemporary

    /**
     * @param           $course_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the course report data - Format Screen
     *
     * Course Report.
     *      - id
     *      - name
     *      - job_roles.    Array
     *                      [id]    --> industrycode + name
     *      - outcomes.     Array
     *                      [id]
     *                              --> name
     *                              --> expiration
     *      - rpt
     *      - completed_before
     *      - levelZero.    Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> levelOne.   Array
     *
     *      - levelOne. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelTwo.   Array
     *      - levelTwo. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelThree. Array
     *
     *
     *      - levelThree.   Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> completed.      Array
     *                                              --> name
     *                                              --> completed
     *                          --> not_completed.  Array
     *                                              --> name
     *                          --> not_enrol.      Array
     *                                              --> name
     *
     */
    public static function Print_CourseReport_Screen($course_report,$completed_option) {
        /* Variables    */
        $out_report = '';

        try {
            /* Select the level to display  */
            switch ($course_report->rpt) {
                case 0:
                    $out_report = self::Print_CourseReport_Screen_LevelZero($course_report,$completed_option);

                    break;
                case 1:
                    $out_report = self::Print_CourseReport_Screen_LevelOne($course_report,$completed_option);

                    break;
                case 2:
                    $out_report = self::Print_CourseReport_Screen_LevelTwo($course_report,$completed_option);

                    break;
                case 3:
                    $out_report = self::Print_CourseReport_Screen_LevelThree($course_report);

                    break;
                default:
                    break;
            }//switch_my_level

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_CourseReport_Screen

    /**
     * @param            $course_report
     * @throws           Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the course report data - Excel Format
     *
     * Course Report.
     *      - id
     *      - name
     *      - job_roles.    Array
     *                      [id]    --> industrycode + name
     *      - outcomes.     Array
     *                      [id]
     *                              --> name
     *                              --> expiration
     *      - rpt
     *      - completed_before
     *      - levelZero.    Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> levelOne.   Array
     *
     *      - levelOne. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelTwo.   Array
     *      - levelTwo. Array
     *                  [id]
     *                          --> id
     *                          --> name
     *                          --> levelThree. Array
     *
     *
     *      - levelThree.   Array
     *                      [id]
     *                          --> id
     *                          --> name
     *                          --> completed.      Array
     *                                              --> name
     *                                              --> completed
     *                          --> not_completed.  Array
     *                                              --> name
     *                          --> not_enrol.      Array
     *                                              --> name
     */
    public static function Download_CourseReport($course_report) {
        try {
            switch ($course_report->rpt) {
                case 0:
                    self::Download_CourseReport_LevelZero($course_report);

                    break;

                case 1:
                    self::Download_CourseReport_LevelOne($course_report);

                    break;
                case 2:
                    self::Download_CourseReport_LevelTwo($course_report);

                    break;
                case 3:
                    self::Download_CourseReport_LevelThree($course_report);

                    break;
                default:
                    break;
            }//switch_report_level
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_CourseReport

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/


    /**
     * @param           $course_id
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get course basic information.
     * Full name, outcomes connected with ...
     */
    private static function Get_CourseBasicInfo($course_id) {
        /* Variables    */
        global $DB;
        $course_report  = null;
        $params         = array();

        try {
            /* Search Criteria  */
            $params['course_id'] = $course_id;

            /* SQL Instruction   */
            $sql = " SELECT			DISTINCT c.id,
                                             c.fullname,
                                             GROUP_CONCAT(DISTINCT go.id ORDER BY go.fullname SEPARATOR ',') as 'outcomesid'
                     FROM 			{course}						c
                        LEFT JOIN	{grade_outcomes_courses}		oc		ON 		oc.courseid 	= c.id
                        LEFT JOIN	{grade_outcomes}				go		ON		go.id			= oc.outcomeid
                     WHERE		c.id = :course_id ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Course Report    */
                $course_report = new stdClass();
                $course_report->id             = $rdo->id;
                $course_report->name           = $rdo->fullname;
                $course_report->job_roles      = null;
                $course_report->outcomes       = null;
                if ($rdo->outcomesid) {
                    $course_report->outcomes   = self::Get_OutcomeDetail($rdo->outcomesid);
                }//if_outcomes

                $course_report->levelZero       = null;
                $course_report->levelOne        = null;
                $course_report->levelTwo        = null;
                $course_report->levelThree      = null;
            }//if_rdo

            return $course_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CourseBasicInfo


    /**
     * @param           $courseId
     * @param           $jobRoles
     * @param           null $companies
     *
     * @throws          Exception
     *
     * @creationDate    30/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all users enrolled in the courses
     */
    private static function GetUsers_EnrolledIn($courseId,$jobRoles,$companies = null) {
        /* Variables    */
        global $DB,$SESSION;
        $rdo            = null;
        $sql            = null;
        $jrUsers        = null;
        $jobKeys        = null;
        $infoTempReport = null;
        $params         = null;

        try {
            /* Extra Info   */
            $jobKeys    = $jobRoles ? array_flip(array_keys($jobRoles)) : null;
            $managerKey = $_SESSION['USER']->sesskey;

            /* Criteria */
            $params = array();
            $params['course'] = $courseId;

            /* SQL Instruction  */
            $sql = " SELECT	CONCAT(ue.id,'_',uic.id) as 'id',
                            u.id 			                      as 'user',
                            CONCAT(u.firstname, ' ', u.lastname)  as 'name',
                            uic.companyid,
                            uic.jobroles,
                            IF (cc.timecompleted,cc.timecompleted,0) as 'timecompleted'
                     FROM			{user_enrolments}			ue
                        JOIN		{enrol}						e	ON 	e.id 	    = ue.enrolid
                                                                    AND	e.courseid 	= :course
                                                                    AND	e.status	= 0
                        JOIN		{user}						u	ON 	u.id 		= ue.userid
                                                                    AND	u.deleted	= 0
                        JOIN		{user_info_competence_data}	uic	ON 	uic.userid 	= u.id
                        LEFT JOIN	{course_completions}		cc	ON 	cc.userid	= uic.userid
                                                                    AND cc.id 		= e.courseid ";

            /* Companies Criteria    */
            if ($companies) {
                $companies = implode(',',array_keys($companies));
                $sql .= " WHERE uic.companyid IN ($companies) ";
            }//if_companies

            /* ORDER BY */
            $sql .= " ORDER BY e.courseid,u.id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    if (isset($SESSION->job_roles) && $SESSION->job_roles && $jobRoles) {
                        $jrUsers = array_flip(explode(',',$instance->jobroles));
                        if (array_intersect_key($jobKeys,$jrUsers)) {
                            /* Info Course Report  */
                            $infoTempReport = new stdClass();
                            $infoTempReport->manager    = $managerKey;
                            $infoTempReport->report     = 'course';
                            $infoTempReport->userid     = $instance->user;
                            $infoTempReport->name       = $instance->name;
                            $infoTempReport->companyid  = $instance->companyid;
                            $infoTempReport->courseid   = $courseId;
                            $infoTempReport->outcomeid  = null;
                            if ($instance->timecompleted) {
                                $infoTempReport->completed      = 1;
                                $infoTempReport->notcompleted   = 0;
                                $infoTempReport->notenrol       = 0;
                                $infoTempReport->timecompleted  = $instance->timecompleted;
                            }else {
                                $infoTempReport->completed      = 0;
                                $infoTempReport->notcompleted   = 1;
                                $infoTempReport->notenrol       = 0;
                                $infoTempReport->timecompleted  = null;
                            }//if_completed

                            /* Execute  */
                            $DB->insert_record('report_gen_temp',$infoTempReport);
                        }//if_job_role
                    }else {
                        /* Info Course Report  */
                        $infoTempReport = new stdClass();
                        $infoTempReport->manager    = $managerKey;
                        $infoTempReport->report     = 'course';
                        $infoTempReport->userid     = $instance->user;
                        $infoTempReport->name       = $instance->name;
                        $infoTempReport->companyid  = $instance->companyid;
                        $infoTempReport->courseid   = $courseId;
                        $infoTempReport->outcomeid  = null;
                        if ($instance->timecompleted) {
                            $infoTempReport->completed      = 1;
                            $infoTempReport->notcompleted   = 0;
                            $infoTempReport->notenrol       = 0;
                            $infoTempReport->timecompleted  = $instance->timecompleted;
                        }else {
                            $infoTempReport->completed      = 0;
                            $infoTempReport->notcompleted   = 1;
                            $infoTempReport->notenrol       = 0;
                            $infoTempReport->timecompleted  = null;
                        }//if_completed

                        /* Execute  */
                        $DB->insert_record('report_gen_temp',$infoTempReport);
                    }//if_jobroles_selected
                }//for_rdo
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_EnrolledIn

    /**
     * @param           $courseId
     * @param           $jobRoles
     * @param           null $companies
     *
     * @throws          Exception
     *
     * @creationDate    30/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all users not enrolled
     */
    private static function GetUsers_NotEnrolIn($courseId,$jobRoles,$companies = null) {
        /* Variables    */
        global $DB,$SESSION;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $jrUsers        = null;
        $jobKeys        = null;
        $infoTempReport = null;

        try {
            /* Extra Info   */
            $jobKeys    = $jobRoles ? array_flip(array_keys($jobRoles)) : null;
            $managerKey = $_SESSION['USER']->sesskey;

            /* Search Criteria  */
            $params = array();
            $params['course']  = $courseId;

            /* SQL Instruction  */
            $sql = " SELECT		CONCAT(u.id,'_',uic.id),
                                u.id,
                                CONCAT(u.firstname, ' ', u.lastname)  as 'name',
                                uic.companyid,
                                uic.jobroles
                     FROM			{user} 						  u
                        JOIN		{user_info_competence_data}	  uic		ON 	uic.userid 		= u.id
                        LEFT JOIN	{report_gen_temp}			  tmp		ON 	tmp.userid 		= uic.userid
                                                                            AND	tmp.outcomeid	IS NULL
                                                                            AND tmp.report      = 'course'
                                                                            AND tmp.courseid 	= :course
                     WHERE	u.deleted 	= 0
                        AND	u.username != 'guest'
                        AND	tmp.id IS NULL ";

            /* Companies Criteria    */
            if ($companies) {
                $companies = implode(',',array_keys($companies));
                $sql .= " AND uic.companyid IN ($companies) ";
            }//if_companies

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    if (isset($SESSION->job_roles) && $SESSION->job_roles && $jobRoles) {
                        $jrUsers = array_flip(explode(',',$instance->jobroles));
                        if (array_intersect_key($jobKeys,$jrUsers)) {
                            /* Info Course Report  */
                            $infoTempReport = new stdClass();
                            $infoTempReport->manager        = $managerKey;
                            $infoTempReport->report         = 'course';
                            $infoTempReport->userid         = $instance->id;
                            $infoTempReport->name           = $instance->name;
                            $infoTempReport->companyid      = $instance->companyid;
                            $infoTempReport->courseid       = $courseId;
                            $infoTempReport->outcomeid      = null;
                            $infoTempReport->completed      = 0;
                            $infoTempReport->notcompleted   = 0;
                            $infoTempReport->notenrol       = 1;
                            $infoTempReport->timecompleted  = null;

                            /* Execute  */
                            $DB->insert_record('report_gen_temp',$infoTempReport);
                        }//if_job_role
                    }else {
                        /* Info Course Report  */
                        $infoTempReport = new stdClass();
                        $infoTempReport->manager        = $managerKey;
                        $infoTempReport->report         = 'course';
                        $infoTempReport->userid         = $instance->id;
                        $infoTempReport->name           = $instance->name;
                        $infoTempReport->companyid      = $instance->companyid;
                        $infoTempReport->courseid       = $courseId;
                        $infoTempReport->outcomeid      = null;
                        $infoTempReport->completed      = 0;
                        $infoTempReport->notcompleted   = 0;
                        $infoTempReport->notenrol       = 1;
                        $infoTempReport->timecompleted  = null;

                        /* Execute  */
                        $DB->insert_record('report_gen_temp',$infoTempReport);
                    }//if_jobroles_selected
                }//for_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_NotEnrolIn

    /**
     * @param           $outcomes
     * @return          array
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the detail of the outcomes list
     */
    private static function Get_OutcomeDetail($outcomes) {
        /* Variables    */
        global $DB;
        $outcomes_lst = array();
        $outcome_info = null;

        try {
            /* SQL Instruction */
            $sql = " SELECT			DISTINCT  o.id,
                                              o.fullname,
                                              oe.expirationperiod
                     FROM			{grade_outcomes}		    o
                        LEFT JOIN	{report_gen_outcome_exp}	oe	ON oe.outcomeid = o.id
                     WHERE			o.id IN ($outcomes)
                     ORDER BY		o.fullname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $outcome) {
                    /* Outcome Info */
                    $outcome_info = new stdClass();
                    $outcome_info->name         = $outcome->fullname;
                    $outcome_info->expiration   = $outcome->expirationperiod;

                    $outcomes_lst[$outcome->id] = $outcome_info;
                }//for_outcomes
            }//if_rdo

            return $outcomes_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_OutcomeDetail

    /**
     * @param           $data_form
     * @return          array
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Job roles connected to the level
     */
    private static function Get_JobRolesCourse_Report($data_form) {
        /* Variables    */
        global $SESSION;
        $job_roles  = null;
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;
        $jr_level   = array();

        try {
            if (!empty($data_form[REPORT_MANAGER_JOB_ROLE_LIST])) {
                $list = join(',',$data_form[REPORT_MANAGER_JOB_ROLE_LIST]);
                $job_roles = CompetenceManager::Get_JobRolesList($list);
                /* Save Job Roles Selected  */
                $SESSION->job_roles = array_keys($job_roles);
            }else {
                /* Job Roles - Outcome          */
                $job_roles = CompetenceManager::Get_JobRolesList();
                $SESSION->job_roles = null;
            }//if_else

            /* Job Roles - Company Level    */
            switch ($data_form['rpt']) {
                case 0:
                    /* Get Level        */
                    $levelZero = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'];
                    /* Get Job Roles    */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($jr_level);
                    }//if_public
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,0,$levelZero);

                    break;
                case 1:
                    /* Get Level        */
                    $levelZero = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'];
                    $levelOne  = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];

                    /* Get Job Roles    */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($jr_level);
                    }//if_public
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,0,$levelZero);
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,1,$levelZero,$levelOne);

                    break;
                case 2:
                    /* Get Level        */
                    $levelZero = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'];
                    $levelOne  = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];
                    $levelTwo  = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2'];

                    /* Get Job Roles    */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($jr_level);
                    }//if_public
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,0,$levelZero);
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,1,$levelZero,$levelOne);
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,2,$levelZero,$levelOne,$levelTwo);

                    break;
                case 3:
                    /* Get Level        */
                    $levelZero  = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'0'];
                    $levelOne   = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'1'];
                    $levelTwo   = $data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'2'];

                    /* Get Job Roles    */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($jr_level);
                    }//if_public
                    if (isset($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3']) && ($data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3'])) {
                        $levelThree = implode(',',$data_form[MANAGER_COURSE_STRUCTURE_LEVEL .'3']);

                        /* Get Job Roles    */
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,3,$levelZero,$levelOne,$levelTwo,$levelThree);
                    }else {
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,0,$levelZero);
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,1,$levelZero,$levelOne);
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,2,$levelZero,$levelOne,$levelTwo);
                    }//if_levelThree

                    break;
            }//switch_level

            if (empty($data_form[REPORT_MANAGER_JOB_ROLE_LIST])) {
                return null;
            }else {
                if (array_intersect_key($job_roles,$jr_level)) {
                    $job_roles = array_intersect_key($job_roles,$jr_level);
                    return $job_roles;
                }else {
                    return $jr_level;
                }//if_intersect
            }//if_selected_levelThree
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRolesCourse_Report

    /**
     * @param           $course_report
     * @param           $parent_lst
     * @param           $inTwo
     * @param           $inThree
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the information connected with the level One
     *
     * Level One
     *      [id]
     *          --> id
     *          --> name
     *          --> level Two.  Array
     *                          [id]
     *                              --> id
     *                              --> name
     *                              --> levelThree. Array
     *                                              [id]
     *                                                  --> id
     *                                                  --> name
     *                                                  --> completed
     *                                                  --> not_completed
     *                                                  --> not_enrol
     */
    private static function Get_CompanyReportInfo_LevelOne(&$course_report,$parent_lst,$inTwo,$inThree) {
        /* Variables    */
        $levelTwo       = null;
        $company_list   = null;
        $companyInfo    = null;
        $company        = null;
        $output         = null;

        try {
            /* Get Information Level One    */
            foreach ($parent_lst as $id=>$company) {
                /* Get Level Two connected with   */
                $company_list   = CompetenceManager::GetCompanies_LevelList(2,$id,$inTwo);
                $output         = array_slice($company_list, 0, 1);
                $company_list   = array_diff($company_list,$output);

                /* Level Two */
                if ($company_list) {
                    /* Get Info Level Two  */
                    $levelTwo = self::Get_CompanyReportInfo_LevelTwo($course_report,$company_list,$inThree);
                    if ($levelTwo) {
                        /* Level One Info   */
                        $companyInfo = new stdClass();
                        $companyInfo->name      = $company;
                        $companyInfo->id        = $id;
                        $companyInfo->levelTwo  = $levelTwo;

                        $course_report->levelOne[$id] = $companyInfo;
                    }//if_levelTwo
                }//if_company_list
            }//for_companies_level_One
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyReportInfo_LevelOne

    /**
     * @param           $course_report
     * @param           $parent_lst
     * @param           $inThree
     * @return          array
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the information connected to the level two
     *
     * Level Two
     *      [id]
     *          --> id
     *          --> name
     *          --> levelThree. Array
     *                          [id]
     *                              --> id
     *                              --> name
     *                              --> completed
     *                              --> not_completed
     *                              --> not_enrol
     */
    private static function Get_CompanyReportInfo_LevelTwo($course_report,$parent_lst,$inThree) {
        /* Variables    */
        $levelTwo       = array();
        $companyInfo    = null;
        $levelThree     = null;
        $company_list   = null;
        $company        = null;
        $output         = null;

        try {
            /* Get Information Level Two    */
            foreach ($parent_lst as $id=>$company) {
                /* Get Level Three connected with   */
                $company_list   = CompetenceManager::GetCompanies_LevelList(3,$id,$inThree);
                $output         = array_slice($company_list, 0, 1);
                $company_list   = array_diff($company_list,$output);

                /* Level Three */
                if ($company_list) {
                    /* Get Info Level Three  */
                    $levelThree = self::Get_CompanyReportInfo_LevelThree($course_report,$company_list);
                    if ($levelThree) {
                        /* Level two Info   */
                        $companyInfo = new stdClass();
                        $companyInfo->name       = $company;
                        $companyInfo->id         = $id;
                        $companyInfo->levelThree = $levelThree;

                        $levelTwo[$id] = $companyInfo;
                    }//if_levelTwo
                }//if_company_list
            }//for_companies_level_Two

            return $levelTwo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyReportInfo_LevelTwo

    /**
     * @param           $course_report
     * @param           $company_list
     * @return          array
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the information connected to the level three
     *
     * Level Three
     *          [id]
     *              --> id
     *              --> name
     *              --> completed
     *              --> not_completed
     *              --> not_enrol
     */
    private static function Get_CompanyReportInfo_LevelThree($course_report,$company_list) {
        /* Variables    */
        $levelThree     = array();

        try {
            /* Get Information Level Three  */
            if ($company_list) {
                foreach ($company_list as $id=>$company) {
                    /* Company Info */
                    $company_info = new stdClass();
                    $company_info->name       = $company;
                    $company_info->id         = $id;
                    /* Users Completed, Not Completed && Not Enrol          */
                    list($company_info->completed,$company_info->not_completed,$company_info->not_enrol) = self::GetUsers_CompanyCourse($id,$course_report->id);

                    /* Add Level Three  */
                    if ($company_info->completed || $company_info->not_completed || $company_info->not_enrol) {
                        $levelThree[$id] = $company_info;
                    }//if_uses
                }//for_company
            }//if_company_list

            return $levelThree;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyReportInfo_LevelThree

    /**
     * @param           $company
     * @param           $course
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    30/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get users by Company and Course. Classified completed, not completed and not enrol.
     */
    private static function GetUsers_CompanyCourse($company,$course) {
        /* Variables    */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $infoUser       = null;
        $completed      = array();
        $notCompleted   = array();
        $notEnrol       = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['manager']      = $_SESSION['USER']->sesskey;
            $params['courseid']     = $course;
            $params['companyid']    = $company;
            $params['report']       = 'course';

            /* Execute  */
            $rdo = $DB->get_records('report_gen_temp',$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $infoUser = new stdClass();
                    $infoUser->name = $instance->name;

                    if ($instance->timecompleted) {
                        $infoUser->completed = $instance->timecompleted;
                        $completed[$instance->userid] = $infoUser;
                    }else {
                        if ($instance->notenrol) {
                            $notEnrol[$instance->userid] = $infoUser;
                        }else {
                            $notCompleted[$instance->userid] = $infoUser;
                        }
                    }
                }//for_rdo
            }//if_rdo

            return array($completed,$notCompleted,$notEnrol);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_CompanyCourse

    /**
     * @param               $course_report
     * @param               $completed_option
     * @return              string
     * @throws              Exception
     *
     * @creationDate        17/03/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get CourseReport Level Zero - Format Screen
     */
    private static function Print_CourseReport_Screen_LevelZero($course_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggle_one      = null;
        $id_toggle_level    = null;
        $return_url         = null;
        $outcomes           = null;
        $str_outcomes       = array();
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;

        try {
            /* Url to back */
            $return_url  = new moodle_url('/report/manager/course_report/course_report_level.php',array('rpt' => $course_report->rpt));

            /* Course Report    */
            $out_report .= html_writer::start_div('outcome_rpt_div');
                /* Course Report Header */
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    /* Course Title */
                    $out_report .= '<h3>';
                        $out_report .= get_string('course') . ' "' . $course_report->name . '"';
                    $out_report .= '</h3>';

                    /* Outcomes Connected         */
                    $out_report .= '<h5>';
                        $out_report .= get_string('outcomes', 'report_manager');
                    $out_report .= '</h5>';
                    $outcomes = $course_report->outcomes;
                    if ($outcomes) {
                        foreach ($outcomes as $outcome) {
                            $str_outcomes[] = $outcome->name;
                        }//for_outcomes
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$str_outcomes);
                        $out_report .= '</h6>';
                    }else {
                        $out_report .= '<h6>-</h6>';
                    }//if_outcomes

                    /* Job Roles    */
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($course_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$course_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    /* Company Levels   */
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        /* Level Zero       */
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . CompetenceManager::GetCompany_Name($course_report->levelZero)  . '</h3>';
                        $out_report .= '</li>';
                    $out_report .= '</ul>';

                    /* Expiration Before    */
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= str_replace(' ...',' : ',get_string('completed_list','report_manager')) .  $options[$course_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                /* Level One    */
                $levelOne = $course_report->levelOne;
                if (!$levelOne) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    /* Return Selection Page    */
                    $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));

                    /* Toggle   */
                    $url_img  = new moodle_url('/pix/t/expanded.png');
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelOne as $idOne=>$one) {
                            $levelTwo = $one->levelTwo;
                            if ($levelTwo) {
                                $id_toggle_one   = 'YUI_' . $idOne;
                                /* Header Level One    */
                                $out_report .= self::Add_CompanyHeader_LevelZero_Screen($one->name,$id_toggle_one,$url_img);
                                /* Content Level One   */
                                $out_report .= html_writer::start_tag('div',array('class' => 'level_one_list','id'=> $id_toggle_one . '_div'));
                                    foreach ($levelTwo as $id=>$level) {
                                        $color = 'r0';
                                        $levelThree = $level->levelThree;
                                        if ($levelThree) {
                                            $id_toggle = 'YUI_' . $id;
                                            /* Header Level Two     */
                                            $out_report .= self::Add_CompanyHeader_Screen($level->name,$id_toggle,$url_img);
                                            /* Content Level Two    */
                                            $out_report .= html_writer::start_tag('div',array('class' => 'level_two_list','id'=> $id_toggle . '_div'));
                                                $out_report .= html_writer::start_tag('div',array('class' => 'company_level'));
                                                    /* Header Table     */
                                                    $out_report .= self::Add_HeaderTable_LevelTwo_Screen();
                                                    /* Content Table    */
                                                    $out_report .= html_writer::start_tag('table');
                                                        foreach ($levelThree as $id_three=>$company) {
                                                            $url_level_three = new moodle_url('/report/manager/course_report/course_report_level.php',
                                                                                              array('rpt' => '3','co' => $id_three,'lt' => $level->id,'lo'=>$idOne,'opt' => $completed_option));
                                                            $out_report .= self::Add_ContentTable_LevelTwo_Screen($url_level_three,$company,$color);

                                                            /* Change Color */
                                                            if ($color == 'r0') {
                                                                $color = 'r2';
                                                            }else {
                                                                $color = 'r0';
                                                            }
                                                        }//for_level_Three
                                                    $out_report .= html_writer::end_tag('table');
                                                $out_report .= html_writer::end_tag('div');//company_level
                                            $out_report .= html_writer::end_tag('div');//level_two_list
                                        }//if_level_three
                                    }//for_level_two
                                $out_report .= html_writer::end_tag('div');//level_one_list
                            }//if_levelTwo
                        }//for_levelOne
                    $out_report .= html_writer::end_tag('div');//outcome_content
                    /* Report Info  */
                }//if_levelOne
            $out_report .= html_writer::end_div();//outcome_rpt_div

            /* Return selection page    */
            $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_CourseReport_Screen_LevelZero

    /**
     * @param           $course_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Course Report Level One - Format Screen
     */
    private static function Print_CourseReport_Screen_LevelOne($course_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggle_level    = null;
        $return_url         = null;
        $outcomes           = null;
        $str_outcomes       = array();
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;

        try {
            /* Url to back */
            $return_url  = new moodle_url('/report/manager/course_report/course_report_level.php',array('rpt' => $course_report->rpt));

            /* Course Report    */
            $out_report .= html_writer::start_div('outcome_rpt_div');
                /* Course Report Header */
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    /* Course Title */
                    $out_report .= '<h3>';
                        $out_report .= get_string('course') . ' "' . $course_report->name . '"';
                    $out_report .= '</h3>';

                    /* Outcomes Connected         */
                    $out_report .= '<h5>';
                        $out_report .= get_string('outcomes', 'report_manager');
                    $out_report .= '</h5>';
                    $outcomes = $course_report->outcomes;
                    if ($outcomes) {
                        foreach ($outcomes as $outcome) {
                            $str_outcomes[] = $outcome->name;
                        }//for_outcomes
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$str_outcomes);
                        $out_report .= '</h6>';
                    }else {
                        $out_report .= '<h6>-</h6>';
                    }//if_outcomes

                    /* Job Roles    */
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($course_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$course_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    /* Company Levels   */
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        /* Level Zero       */
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . CompetenceManager::GetCompany_Name($course_report->levelZero) . '</h3>';
                        $out_report .= '</li>';
                        /* Level One        */
                        $levelOne = array_shift($course_report->levelOne);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                    $out_report .= '</ul>';

                    /* Expiration Before    */
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= str_replace(' ...',' : ',get_string('completed_list','report_manager')) .  $options[$course_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                /* Level Two    */
                $levelTwo = $levelOne->levelTwo;
                if (!$levelTwo) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    /* Return Selection Page    */
                    $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));

                    /* Report Info  */
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelTwo as $id=>$level) {
                            $color = 'r0';
                            $levelThree = $level->levelThree;
                            if ($levelThree) {
                                /* Toggle   */
                                $url_img  = new moodle_url('/pix/t/expanded.png');
                                $id_toggle = 'YUI_' . $id;
                                /* Header Company  - Level Two */
                                $out_report .= self::Add_CompanyHeader_Screen($level->name,$id_toggle,$url_img);

                                /* Level Two List   */
                                $out_report .= html_writer::start_tag('div',array('class' => 'level_two_list','id'=> $id_toggle . '_div'));
                                    $out_report .= html_writer::start_tag('div',array('class' => 'company_level'));
                                        /* Header Table     */
                                        $out_report .= self::Add_HeaderTable_LevelTwo_Screen();
                                        /* Content Table    */
                                        $out_report .= html_writer::start_tag('table');
                                            foreach ($levelThree as $id_three=>$company) {
                                                $url_level_three = new moodle_url('/report/manager/course_report/course_report_level.php',
                                                                                  array('rpt' => '3','co' => $id_three,'lt' => $level->id,'lo'=>$levelOne->id,'opt' => $completed_option));

                                                /* Company Header   */
                                                $out_report .= self::Add_ContentTable_LevelTwo_Screen($url_level_three,$company,$color);

                                                /* Change Color */
                                                if ($color == 'r0') {
                                                    $color = 'r2';
                                                }else {
                                                    $color = 'r0';
                                                }
                                            }//for_level_Three
                                        $out_report .= html_writer::end_tag('table');
                                    $out_report .= html_writer::end_tag('div');//company_level
                                $out_report .= html_writer::end_tag('div');//level_two_list
                            }//if_level_three
                        }//for_level_two
                    $out_report .= html_writer::end_tag('div');//outcome_content
                }//if_levelTwo
            $out_report .= html_writer::end_div();//outcome_rpt_div

            /* Return selection page    */
            $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_CourseReport_Screen_LevelOne

    /**
     * @param           $course_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Course Report Level wo - Format Screen
     */
    private static function Print_CourseReport_Screen_LevelTwo($course_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggle_level    = null;
        $return_url         = null;
        $outcomes           = null;
        $str_outcomes       = array();
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;
        $color              = null;

        try {
            /* Url to back */
            $return_url  = new moodle_url('/report/manager/course_report/course_report_level.php',array('rpt' => $course_report->rpt));

            /* Course Report    */
            $out_report .= html_writer::start_div('outcome_rpt_div');
                /* Course Report Header */
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    /* Course Title */
                    $out_report .= '<h3>';
                        $out_report .= get_string('course') . ' "' . $course_report->name . '"';
                    $out_report .= '</h3>';

                    /* Outcomes Connected         */
                    $out_report .= '<h5>';
                        $out_report .= get_string('outcomes', 'report_manager');
                    $out_report .= '</h5>';
                    $outcomes = $course_report->outcomes;
                    if ($outcomes) {
                        foreach ($outcomes as $outcome) {
                            $str_outcomes[] = $outcome->name;
                        }//for_outcomes
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$str_outcomes);
                        $out_report .= '</h6>';
                    }else {
                        $out_report .= '<h6>-</h6>';
                    }//if_outcomes

                    /* Job Roles    */
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($course_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$course_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    /* Company Levels   */
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        /* Level Zero       */
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . CompetenceManager::GetCompany_Name($course_report->levelZero) . '</h3>';
                        $out_report .= '</li>';
                        /* Level One        */
                        $levelOne = array_shift($course_report->levelOne);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                        /* Level Two    */
                        $levelTwo = array_shift($course_report->levelTwo);
                        if ($levelTwo) {
                            $out_report .= '<li>';
                                $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 2) . ': ' . $levelTwo->name . '</h3>';
                            $out_report .= '</li>';
                        }//if_level_two
                    $out_report .= '</ul>';

                    /* Expiration Before    */
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= str_replace(' ...',' : ',get_string('completed_list','report_manager')) .  $options[$course_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                /* Level Three  */
                $levelThree = $levelTwo->levelThree;
                if (!$levelThree) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    /* Return Selection Page    */
                    $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));

                    /* Report Info  */
                    $color = 'r0';
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        $out_report .= html_writer::start_tag('div',array('class' => 'company_level'));
                            /* Header Table     */
                            $out_report .= self::Add_HeaderTable_LevelTwo_Screen();
                            /* Content Table    */
                            $out_report .= html_writer::start_tag('table');
                                foreach ($levelThree as $id_three=>$company) {
                                    $url_level_three = new moodle_url('/report/manager/course_report/course_report_level.php',
                                                                      array('rpt' => '3','co' => $id_three,'lt' => $levelTwo->id,'lo'=>$levelOne->id,'opt' => $completed_option));
                                    $out_report .= self::Add_ContentTable_LevelTwo_Screen($url_level_three,$company,$color);

                                    /* Change Color */
                                    if ($color == 'r0') {
                                        $color = 'r2';
                                    }else {
                                        $color = 'r0';
                                    }
                                }//for_level_Three
                            $out_report .= html_writer::end_tag('table');
                        $out_report .= html_writer::end_tag('div');//company_level
                    $out_report .= html_writer::end_tag('div');//outcome_content
                }//if_levelThree
            $out_report .= html_writer::end_div();//outcome_rpt_div

            /* Return selection page    */
            $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_CourseReport_Screen_LevelTwo


    /**
     * @param           $course_report
     * @return          string
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbV)
     *
     * Description
     * Get Course Report Level Three - Screen Format
     */
    private static function Print_CourseReport_Screen_LevelThree($course_report) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggle_level    = null;
        $return_url         = null;
        $outcomes           = null;
        $str_outcomes       = array();
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;

        try {
            /* Url to back */
            $return_url  = new moodle_url('/report/manager/course_report/course_report_level.php',array('rpt' => $course_report->rpt));

            /* Course Report    */
            $out_report .= html_writer::start_div('outcome_rpt_div');
                /* Course Report Header */
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    /* Course Title */
                    $out_report .= '<h3>';
                        $out_report .= get_string('course') . ' "' . $course_report->name . '"';
                    $out_report .= '</h3>';

                    /* Outcomes Connected         */
                    $out_report .= '<h5>';
                        $out_report .= get_string('outcomes', 'report_manager');
                    $out_report .= '</h5>';
                    $outcomes = $course_report->outcomes;
                    if ($outcomes) {
                        foreach ($outcomes as $outcome) {
                            $str_outcomes[] = $outcome->name;
                        }//for_outcomes
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$str_outcomes);
                        $out_report .= '</h6>';
                    }else {
                        $out_report .= '<h6>-</h6>';
                    }//if_outcomes

                    /* Job Roles    */
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($course_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$course_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    /* Company Levels   */
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        /* Level Zero       */
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . CompetenceManager::GetCompany_Name($course_report->levelZero) . '</h3>';
                        $out_report .= '</li>';
                        /* Level One        */
                        $levelOne = array_shift($course_report->levelOne);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                        /* Level Two    */
                        $levelTwo = array_shift($course_report->levelTwo);
                        if ($levelTwo) {
                            $out_report .= '<li>';
                                $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 2) . ': ' . $levelTwo->name . '</h3>';
                            $out_report .= '</li>';
                        }//if_level_two
                    $out_report .= '</ul>';

                    /* Expiration Before    */
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= str_replace(' ...',' : ',get_string('completed_list','report_manager')) .  $options[$course_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                /* Level Three  */
                $levelThree = $course_report->levelThree;
                if (!$levelThree) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    /* Return Selection Page    */
                    $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));

                    /* Report Info  */
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelThree as $id=>$company) {
                            /* Toggle   */
                            $url_img  = new moodle_url('/pix/t/expanded.png');
                            $id_toggle = 'YUI_' . $id;
                            $out_report .= self::Add_CompanyHeader_Screen($company->name,$id_toggle,$url_img);

                            /* Info company - Users */
                            $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggle . '_div'));
                                /* Header Table     */
                                $out_report .= self::Add_HeaderTable_LevelThree_Screen();
                                /* Content Table    */
                                $out_report .= self::Add_ContentTable_LevelThree_Screen($company);
                            $out_report .= html_writer::end_tag('div');//courses_list
                        }//for_level_three
                    $out_report .= html_writer::end_tag('div');//outcome_content
                }//if_levelThree
            $out_report .= html_writer::end_div();//outcome_rpt_div

            /* Return selection page    */
            $out_report .= html_writer::link($return_url,get_string('course_return_to_selection','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_CourseReport_Screen_LevelThree

    /**
     * @param           $company
     * @param           $toogle
     * @param           $img
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header for the level zero
     */
    private static function Add_CompanyHeader_LevelZero_Screen($company,$toogle,$img) {
        /* Variables    */
        $header_company     = null;
        $title_company      = null;

        $header_company .= html_writer::start_div('header_outcome_company_rpt_levelZero');
            /* Col One  */
            $header_company .= html_writer::start_div('header_col_one');
                $header_company .= '<button class="toggle_outcome_company_rpt" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header_company .= html_writer::end_div('');//header_col_one

            /* Col Two  */
            $header_company .= html_writer::start_div('header_col_two');
                $header_company .= '<h4>' . $company . '</h4>';
            $header_company .= html_writer::end_div('');//header_col_two
        $header_company .= html_writer::end_div('');//header_outcome_company_rpt

        return $header_company;
    }//Add_CompanyHeader_Screen

    /**
     * @param           $company
     * @param           $toogle
     * @param           $img
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add header for level one, two and three
     */
    private static function Add_CompanyHeader_Screen($company,$toogle,$img) {
        /* Variables    */
        $header_company     = null;
        $title_company      = null;

        $header_company .= html_writer::start_div('header_outcome_company_rpt');
            /* Col One  */
            $header_company .= html_writer::start_div('header_col_one');
                $header_company .= '<button class="toggle_outcome_company_rpt" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header_company .= html_writer::end_div('');//header_col_one

            /* Col Two  */
            $header_company .= html_writer::start_div('header_col_two');
                $header_company .= '<h5>' . $company . '</h5>';
            $header_company .= html_writer::end_div('');//header_col_two
        $header_company .= html_writer::end_div('');//header_outcome_company_rpt

        return $header_company;
    }//Add_CompanyHeader_Screen

    /**
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add table header for level two
     */
    private static function Add_HeaderTable_LevelTwo_Screen() {
        /* Variables    */
        $header_table = null;

        $str_company        = get_string('company','report_manager');
        $str_not_enrol      = get_string('not_start','report_manager');
        $str_not_completed  = get_string('progress','report_manager');
        $str_completed      = get_string('completed','report_manager');
        $str_total          = get_string('count','report_manager');

        $header_table .= html_writer::start_tag('table');
            $header_table .= html_writer::start_tag('tr',array('class' => 'head'));
                /* Empty Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_first'));
                $header_table .= html_writer::end_tag('td');
                /* Company          */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_company'));
                    $header_table .= $str_company;
                $header_table .= html_writer::end_tag('td');
                /* Not Enrol        */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_status'));
                    $header_table .= $str_not_enrol;
                $header_table .= html_writer::end_tag('td');
                /* Not Completed    */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_status'));
                    $header_table .= $str_not_completed;
                $header_table .= html_writer::end_tag('td');
                /* Completed        */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_status'));
                    $header_table .= $str_completed;
                $header_table .= html_writer::end_tag('td');
                /* Total            */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_status'));
                    $header_table .= $str_total;
                $header_table .= html_writer::end_tag('td');
            $header_table .= html_writer::end_tag('tr');
        $header_table .= html_writer::end_tag('table');

        return $header_table;
    }//Add_HeaderTable_LevelTwo_Screen

    /**
     * @param           $url_level_three
     * @param           $company_info
     * @param           $color
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table for level two
     */
    private static function Add_ContentTable_LevelTwo_Screen($url_level_three,$company_info,$color) {
        /* Variables    */
        $content    = null;
        //$url_level_three    = null;

        $content .= html_writer::start_tag('tr',array('class' => $color));
            /* Empty Col   */
            $content .= html_writer::start_tag('td',array('class' => 'first'));
            $content .= html_writer::end_tag('td');
            /* Company          */
            $content .= html_writer::start_tag('td',array('class' => 'company'));
                $content .= '<a href="' . $url_level_three . '">' . $company_info->name . '</a>';
            $content .= html_writer::end_tag('td');
            /* Not Enrol        */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= count($company_info->not_enrol);
            $content .= html_writer::end_tag('td');
            /* Not Completed    */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= count($company_info->not_completed);
            $content .= html_writer::end_tag('td');
            /* Completed        */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= count($company_info->completed);
            $content .= html_writer::end_tag('td');
            /* Total            */
            $content .= html_writer::start_tag('td',array('class' => 'status'));
                $content .= count($company_info->completed) + count($company_info->not_completed) + count($company_info->not_enrol);
            $content .= html_writer::end_tag('td');
        $content .= html_writer::end_tag('tr');

        return $content;
    }//Add_ContentTable_LevelTwo_Screen

    /**
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the table header for level three
     */
    private static function Add_HeaderTable_LevelThree_Screen() {
        /* Variables    */
        $header_table = null;

        $str_user           = get_string('user');
        $str_state          = get_string('state','local_tracker_manager');
        $str_completion     = get_string('completion_time','local_tracker_manager');

        $header_table .= html_writer::start_tag('table');
            $header_table .= html_writer::start_tag('tr',array('class' => 'head'));
                /* Empty Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_first'));
                $header_table .= html_writer::end_tag('td');

                /* Course Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_course'));
                    $header_table .= $str_user;
                $header_table .= html_writer::end_tag('td');

                /* Status Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_status'));
                    $header_table .= $str_state;
                $header_table .= html_writer::end_tag('td');

                /* Completion Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_status'));
                    $header_table .= $str_completion;
                $header_table .= html_writer::end_tag('td');
            $header_table .= html_writer::end_tag('tr');
        $header_table .= html_writer::end_tag('table');

        return $header_table;
    }//Add_HeaderTable_LevelThree_Screen

    /**
     * @param           $company_info
     * @return          string
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table for level three
     */
    private static function Add_ContentTable_LevelThree_Screen($company_info) {
        /* Variables    */
        $content        = null;
        $class          = null;
        $label          = null;
        $completed      = null;
        $not_completed  = null;
        $not_enrol      = null;

        $content .= html_writer::start_tag('table');
            /* Completed    */
            $completed = $company_info->completed;
            if ($completed) {
                foreach ($completed as $user) {

                    $content .= html_writer::start_tag('tr',array('class' => 'completed'));
                        /* Empty Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        /* User Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'course'));
                            $content .= $user->name;
                        $content .= html_writer::end_tag('td');
                        /* Status Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= get_string('outcome_course_finished','local_tracker_manager');;
                        $content .= html_writer::end_tag('td');

                        /* Completion Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= userdate($user->completed,'%d.%m.%Y', 99, false);
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_completed
            }//if_completed

            /* Not Completed - In progress  */
            $not_completed = $company_info->not_completed;
            if ($not_completed) {
                foreach ($not_completed as $user) {
                    $content .= html_writer::start_tag('tr');
                        /* Empty Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        /* User Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'course'));
                            $content .= $user->name;
                        $content .= html_writer::end_tag('td');
                        /* Status Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= get_string('outcome_course_started','local_tracker_manager');
                        $content .= html_writer::end_tag('td');

                        /* Completion Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= '-';
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_not_enrol
            }//if_not_completed

            /* Not Enrol    */
            $not_enrol = $company_info->not_enrol;
            if ($not_enrol) {
                foreach ($not_enrol as $user) {
                    $content .= html_writer::start_tag('tr',array('class' => 'not_enroll'));
                        /* Empty Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        /* User Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'course'));
                            $content .= $user->name;
                        $content .= html_writer::end_tag('td');
                        /* Status Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= get_string('outcome_course_not_enrolled','local_tracker_manager');
                        $content .= html_writer::end_tag('td');

                        /* Completion Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= '-';
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_not_enrol
            }//if_not_enrol
        $content .= html_writer::end_tag('table');

        return $content;
    }//Add_ContentTable_LevelThree_Screen

    /**
     * @param           $course_report
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Course Report - Level Zero
     */
    private static function Download_CourseReport_LevelZero($course_report) {
        /* Variables    */
        global $CFG;
        $row        = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($course_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completed_before   = $options[$course_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* One Sheet By Level two  */
            if ($course_report->levelOne) {
                foreach ($course_report->levelOne as $levelOne) {
                    foreach ($levelOne->levelTwo as $levelTwo) {
                        $row = 0;
                        // Adding the worksheet
                        $my_xls = $export->add_worksheet($levelTwo->name);

                        /* Add Header - Company Course Report  - Level One */
                        self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->levelZero,$levelOne,$levelTwo,null,$completed_before,$my_xls,$row);

                        /* Ad Level Two */
                        if ($levelTwo->levelThree) {
                            /* Add Header Table */
                            $row++;
                            self::AddHeader_LevelTwo_TableCourse($my_xls,$row);

                            /* Add Content Table    */
                            $row++;
                            foreach ($levelTwo->levelThree as $company) {
                                self::AddContent_LevelTwo_TableCourse($my_xls,$row,$company);

                                $my_xls->merge_cells($row,0,$row,13);
                                $row++;
                            }//for_each_company
                        }//if_level_three
                    }//for_levelTwo
                }//for_levelOne
            }else {
                $row = 0;
                // Adding the worksheet
                $my_xls = $export->add_worksheet($course_report->levelZero);

                /* Add Header - Company Course Report  - Level One */
                self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->levelZero,null,null,get_string('no_data', 'report_manager'),$completed_before,$my_xls,$row);
            }//if_levelOne


            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_CourseReport_LevelZero

    /**
     * @param           $course_report
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Course Report - Level One
     */
    private static function Download_CourseReport_LevelOne($course_report) {
        /* Variables    */
        global $CFG;
        $levelOne   = null;
        $row        = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($course_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completed_before   = $options[$course_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* One Sheet by Level Two   */
            $levelOne = array_shift($course_report->levelOne);
            if ($levelOne->levelTwo) {
                foreach ($levelOne->levelTwo as $levelTwo) {
                    $row = 0;
                    // Adding the worksheet
                    $my_xls = $export->add_worksheet($levelTwo->name);

                    /* Add Header - Company Course Report  - Level One */
                    self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->levelZero,$levelOne,$levelTwo,null,$completed_before,$my_xls,$row);

                    /* Ad Level Two */
                    if ($levelTwo->levelThree) {
                        /* Add Header Table */
                        $row++;
                        self::AddHeader_LevelTwo_TableCourse($my_xls,$row);

                        /* Add Content Table    */
                        $row++;
                        foreach ($levelTwo->levelThree as $company) {
                            self::AddContent_LevelTwo_TableCourse($my_xls,$row,$company);

                            $my_xls->merge_cells($row,0,$row,13);
                            $row++;
                        }//for_each_company
                    }//if_level_three
                }//for_levelTwo
            }else {
                $row = 0;
                // Adding the worksheet
                $my_xls = $export->add_worksheet($levelOne->name);

                /* Add Header - Company Course Report  - Level One */
                self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->levelZero,$levelOne,null,get_string('no_data', 'report_manager'),$completed_before,$my_xls,$row);
            }//if_levelTwo


            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_CourseReport_LevelOne

    /**
     * @param           $course_report
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Download Course Report - Level Two
     */
    private static function Download_CourseReport_LevelTwo($course_report) {
        /* Variables    */
        global $CFG;
        $levelOne   = null;
        $levelTwo   = null;
        $row        = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($course_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completed_before   = $options[$course_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* Level One   */
            $levelOne = array_shift($course_report->levelOne);
            /* Level Two    */
            $levelTwo = array_shift($course_report->levelTwo);

            /* One Sheet by Level Two   */
            $row = 0;
            // Adding the worksheet
            $my_xls    = $export->add_worksheet($levelTwo->name);

            /* Ad Level Two */
            if ($levelTwo->levelThree) {
                /* Add Header - Company Course Report  - Level One */
                self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->levelZero,$levelOne,$levelTwo,null,$completed_before,$my_xls,$row);

                /* Add Header Table */
                $row++;
                self::AddHeader_LevelTwo_TableCourse($my_xls,$row);

                /* Add Content Table    */
                $row++;
                foreach ($levelTwo->levelThree as $company) {
                    self::AddContent_LevelTwo_TableCourse($my_xls,$row,$company);

                    $my_xls->merge_cells($row,0,$row,13);
                    $row++;
                }//for_each_company
            }else {
                /* Add Header - Company Course Report  - Level One */
                self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->levelZero,$levelOne,$levelTwo,get_string('no_data', 'report_manager'),$completed_before,$my_xls,$row);
            }//if_level_three

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_CourseReport_LevelTwo

    /**
     * @param           $course_report
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Course Report - Level Three
     */
    private static function Download_CourseReport_LevelThree($course_report) {
        /* Variables    */
        global $CFG;
        $levelOne   = null;
        $levelTwo   = null;
        $row        = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($course_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completed_before   = $options[$course_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            //Sending HTTP headers
            $export->send($file_name);

            /* Level One   */
            $levelOne = array_shift($course_report->levelOne);
            /* Level Two    */
            $levelTwo = array_shift($course_report->levelTwo);

            /* Ad Level Two */
            if ($course_report->levelThree) {
                foreach ($course_report->levelThree as $company) {
                    /* One Sheet by Level Three   */
                    $row = 0;
                    // Adding the worksheet
                    $my_xls    = $export->add_worksheet($company->name);

                    /* Add Header - Company Course Report  - Level One */
                    self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->levelZero,$levelOne,$levelTwo,$company->name,$completed_before,$my_xls,$row);

                    /* Add Header Table     */
                    $row++;
                    self::AddHeader_LevelThree_TableCourse($my_xls,$row);
                    /* Add Content Table    */
                    $row++;
                    self::AddContent_LevelThree_TableCourse($my_xls,$row,$company);

                    $my_xls->merge_cells($row,0,$row,10);
                }//for_each_company
            }else {
                /* One Sheet by Level Three   */
                $row = 0;
                // Adding the worksheet
                $my_xls    = $export->add_worksheet($levelTwo->name);

                /* Add Header - Company Course Report  - Level One */
                self::AddHeader_CompanySheet($course_report->name,$course_report->outcomes,$course_report->levelZero,$levelOne,$levelTwo,get_string('no_data', 'report_manager'),$completed_before,$my_xls,$row);
            }//if_level_three

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_CourseReport_LevelThree

    /**
     * @param           $course
     * @param           $outcomes
     * @param           $level_zero
     * @param           $level_one
     * @param           null $level_two
     * @param           null $level_three
     * @param           $completed_before
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Header of the Company Sheet
     */
    private static function AddHeader_CompanySheet($course,$outcomes,$level_zero,$level_one=null,$level_two = null,$level_three = null,$completed_before,&$my_xls,&$row) {
        /* Variables    */
        $col = 0;
        $title_course           = get_string('course');
        $title_outcomes         = get_string('outcomes', 'report_manager');
        $str_outcomes           = null;
        $title_expiration       = str_replace(' ...',' : ',get_string('completed_list','report_manager')) . $completed_before;
        $title_level_zero       = get_string('company_structure_level', 'report_manager', 0) . ': ' . $level_zero;
        $title_level_one        = null;
        if ($level_one) {
            $title_level_one    = get_string('company_structure_level', 'report_manager', 1) . ': ' . $level_one->name;
        }
        $title_level_two        = null;
        if ($level_two) {
            $title_level_two    = get_string('company_structure_level', 'report_manager', 2) . ': ' . $level_two->name;
        }//if_level_two

        try {
            /* Course Title && Course Name*/
            /* Course Name  */
            $my_xls->write($row, $col, $title_course,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);
            $row++;
            $my_xls->write($row, $col, $course,array('size'=>10, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Outcome Title && Outcome Names   */
            $row++;
            $my_xls->write($row, $col, $title_outcomes,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);
            $row++;
            if ($outcomes) {
                foreach ($outcomes as $outcome) {
                    $str_outcomes[] = $outcome->name;
                    $str_outcomes = implode(', ',$str_outcomes);
                }//for_outcomes
            }
            $my_xls->write($row, $col, $str_outcomes,array('size'=>10, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Level Zero    */
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $title_level_zero,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Level One    */
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $title_level_one,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Level Two    */
            if ($title_level_two) {
                $row++;
                $col = 0;
                $my_xls->write($row, $col, $title_level_two,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $my_xls->set_row($row,25);
            }//if_level_two

            /* Level Three  */
            if ($level_three) {
                /* Merge Cells  */
                $row++;
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $row++;
                $my_xls->merge_cells($row,$col,$row,$col+10);

                $row++;
                $col = 0;
                $my_xls->write($row, $col, $level_three,array('size'=>14, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $my_xls->set_row($row,25);
            }

            /* Expiration Time */
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $title_expiration,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Merge Cells  */
            $row++;
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $row++;
            $my_xls->merge_cells($row,$col,$row,$col+10);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_CompanySheet

    /**
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the table - Level One && Two
     */
    private static function AddHeader_LevelTwo_TableCourse(&$my_xls,$row) {
        /* Variables    */
        $str_company        = strtoupper(get_string('company','report_manager'));
        $str_not_enrol      = strtoupper(get_string('not_start','report_manager'));
        $str_not_completed  = strtoupper(get_string('progress','report_manager'));
        $str_completed      = strtoupper(get_string('completed','report_manager'));
        $str_total          = strtoupper(get_string('count','report_manager'));
        $col                = 0;

        try {
            /* Company      */
            $my_xls->write($row, $col, $str_company,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Not Enrol    */
            $col = $col + 6;
            $my_xls->write($row, $col, $str_not_enrol,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* In Progress  */
            $col = $col + 2;
            $my_xls->write($row, $col, $str_not_completed,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* Completed    */
            $col = $col + 2;
            $my_xls->write($row, $col, $str_completed,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* Total        */
            $col = $col + 2;
            $my_xls->write($row, $col, $str_total,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_LevelTwo_TableCourse

    /**
     * @param           $my_xls
     * @param           $row
     * @param           $company_info
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table - Level One && Two
     */
    private static function AddContent_LevelTwo_TableCourse(&$my_xls,&$row,$company_info) {
        /* Variables    */
        $col    = 0;
        $total  = 0;

        try {
            /* Company      */
            $my_xls->write($row, $col, $company_info->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Not Enrol    */
            $col = $col + 6;
            $my_xls->write($row, $col, count($company_info->not_enrol),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* In Progress  */
            $col = $col + 2;
            $my_xls->write($row, $col, count($company_info->not_completed),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* Completed    */
            $col = $col + 2;
            $my_xls->write($row, $col, count($company_info->completed),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            /* Total        */
            $col = $col + 2;
            $total = count($company_info->completed) + count($company_info->not_completed) + count($company_info->not_enrol);
            $my_xls->write($row, $col, $total,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+1);
            $my_xls->set_row($row,20);

            $row++;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_LevelTwo_TableCourse

    /**
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the table - Level Three
     */
    private static function AddHeader_LevelThree_TableCourse(&$my_xls,$row) {
        /* Variables    */
        $str_user           = strtoupper(get_string('user'));
        $str_state          = strtoupper(get_string('state','local_tracker_manager'));
        $str_completion     = strtoupper(get_string('completion_time','local_tracker_manager'));
        $col                = 0;

        try {
            /* User         */
            $my_xls->write($row, $col, $str_user,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* State        */
            $col = $col + 6;
            $my_xls->write($row, $col, $str_state,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            /* Completion   */
            $col = $col + 3;
            $my_xls->write($row, $col, $str_completion,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'center','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_LevelThree_TableCourse

    /**
     * @param           $my_xls
     * @param           $row
     * @param           $company_info
     * @throws          Exception
     *
     * @creationDate    19/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table - Level Three
     */
    private static function AddContent_LevelThree_TableCourse(&$my_xls,&$row,$company_info) {
        /* Variables    */
        $col = null;

        try {
            /* Completed        */
            if ($company_info->completed) {
                foreach ($company_info->completed as $user) {
                    $col = 0;

                    /* User     */
                    $my_xls->write($row, $col, $user->name,array('size'=>12, 'name'=>'Arial','bg_color'=>'#dff0d8','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State        */
                    $col = $col + 6;
                    $my_xls->write($row, $col, get_string('outcome_course_finished','local_tracker_manager'),array('size'=>12, 'name'=>'Arial','bg_color'=>'#dff0d8','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Completion   */
                    $col = $col + 3;
                    $my_xls->write($row, $col, userdate($user->completed,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bg_color'=>'#dff0d8','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    $row++;
                }//courses_completed
            }//if_completed

            /* In Progress      */
            if ($company_info->not_completed) {
                foreach ($company_info->not_completed as $user) {
                    $col = 0;
                    /* User     */
                    $my_xls->write($row, $col, $user->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State        */
                    $col = $col + 6;
                    $my_xls->write($row, $col, get_string('outcome_course_started','local_tracker_manager'),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Completion   */
                    $col = $col + 3;
                    $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    $row++;
                }//courses_completed
            }//if_not_completed

            /* Not Enrol        */
            if ($company_info->not_enrol) {
                foreach ($company_info->not_enrol as $user) {
                    $col = 0;
                    /* User     */
                    $my_xls->write($row, $col, $user->name,array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State        */
                    $col = $col + 6;
                    $my_xls->write($row, $col, get_string('outcome_course_not_enrolled','local_tracker_manager'),array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Completion   */
                    $col = $col + 3;
                    $my_xls->write($row, $col, ' - ',array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    $row++;
                }//not_enrol
            }//if_not_enrol
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_LevelThree_TableCourse
}//course_report