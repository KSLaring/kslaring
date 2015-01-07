<?php
/**
 * Library code for the Outcome Report generator.
 *
 * @package     report
 * @subpackage  generator/outcome_report
 * @copyright   2010 eFaktor
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  09/09/2014
 * @author      eFaktor     (fbv)
 *
 */

define('OUTCOME_REPORT_FORMAT_SCREEN', 0);
define('OUTCOME_REPORT_FORMAT_SCREEN_EXCEL', 1);
define('OUTCOME_REPORT_FORMAT_LIST', 'report_format_list');

class outcome_report {

    /* PUBLIC FUNCTIONS     */
    public static function GetLevelLink_ReportPage($tab,$site_context) {
        /* Variables    */
        global $OUTPUT;

        /* Create links - It's depend on View permissions */
        $out = '<ul class="unlist report-selection">' . "\n";
        if (has_capability('report/generator:viewlevel1', $site_context)) {
            $out .= self::Get_FirstLevelLink($tab);
        }else if(has_capability('report/generator:viewlevel2', $site_context)) {
            $out .= self::Get_SecondLevelLink($tab);
        }else if (has_capability('report/generator:viewlevel3', $site_context)) {
            $out .= self::Get_ThirdLevelLink($tab);
        }//if_capabitity
        $out .= '</ul>' . "\n";

        /* Draw Links */
        echo $OUTPUT->heading($out);
    }//GetLevelLink_ReportPage

    /**
     * @static
     * @param           $user_id
     * @param           $site_context
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get my hierarchy level
     */
    public static function get_MyHierarchyLevel($user_id,$site_context) {
        /* Variables    */
        $my_hierarchy               = new stdClass();
        $my_hierarchy->my_company   = null;
        $my_hierarchy->level_two    = null;
        $my_hierarchy->level_one    = null;
        $my_hierarchy->my_level     = self::Get_MyLevelView($user_id,$site_context);

        try {
            $my_hierarchy->my_company = self::Get_MyCompany($user_id);
            self::Get_MyHierarchy($my_hierarchy);

            return $my_hierarchy;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_MyHierarchyLevel

    /**
     * @static
     * @return          array
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the outcomes list
     */
    public static function Get_OutcomesList() {
        /* Variables    */
        global $DB;
        $outcome_list = array();

        try {
            /* SQL Instruction */
            $sql = " SELECT     id,
                                fullname
                     FROM       {grade_outcomes}
                     ORDER BY   fullname ASC ";

            /* Execute */
            if ($rdo = $DB->get_records_sql($sql)) {
                $outcome_list[0] = get_string('select') . '...';
                foreach ($rdo as $field) {
                    $outcome_list[$field->id] = $field->fullname;
                }
            }

            return $outcome_list;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_OutcomesList

    /**
     * @static
     * @return          array
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the Job Roles List
     */
    public static function Get_JobRolesList() {
        /* Variables    */
        global $DB;
        $job_roles_lst = array();

        try {
            /* SQL Instruction  */
            $sql = " SELECT     DISTINCT id,
                                name
                     FROM       {report_gen_jobrole}
                     ORDER BY   name ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $job_role) {
                    $job_roles_lst[$job_role->id] = $job_role->name;
                }//for_rdo_job_role
            }//if_rdo
            return $job_roles_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRolesList

    /**
     * @static
     * @param           $level
     * @param           null $companies_in
     * @param           null $lst_parent
     * @return          array
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the companies connected with the level
     */
    public static function Get_CompaniesLevel($level,$companies_in=null,$lst_parent=null) {
        /* Variables    */
        global $DB;
        $lst_companies = array();

        try {
            $lst_companies[0] = get_string('select_level_list','report_generator');

            /* Search Criteria */
            $params = array();
            $params['level']    = $level;

            /* SQL Instruction   */
            $sql    = " SELECT     DISTINCT rcd.id,
                                   rcd.name
                        FROM       {report_gen_companydata} rcd ";

            if ($level > 1) {
                $sql .= " JOIN  {report_gen_company_relation} rcr ON    rcr.companyid = rcd.id
                                                                  AND   rcr.parentid  IN ($lst_parent) ";
            }//if_level

            $sql .= " WHERE rcd.hierarchylevel = :level ";

            if ($companies_in) {
                $sql .= " AND rcd.id IN ($companies_in) ";
            }//if_companies_in

            $sql .= " ORDER BY rcd.name ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $company) {
                    $lst_companies[$company->id] = $company->name;
                }//for_rdo_company
            }//if_rdo

            return $lst_companies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompaniesLevel

    /**
     * @static
     * @param               $data_form
     * @return              null|stdClass
     * @throws              Exception
     *
     * @creationDate    18/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome Report Information.
     */
    public static function Get_OutcomeReportLevel($data_form) {
        /* Variables    */
        global $SESSION;
        $companies_report   = null;
        $outcome_report     = null;
        $outcome_id         = null;
        $job_role_list      = null;

        try {
            /* Outcome Report - Basic Information */
            $outcome_id     = $data_form[REPORT_GENERATOR_OUTCOME_LIST];
            $outcome_report = self::Get_OutcomeBasicInfo($outcome_id);

            if ($outcome_report) {
                $outcome_report->rpt                = $data_form['rpt'];
                $outcome_report->completed_before   = $data_form[REPORT_GENERATOR_COMPLETED_LIST];

                /* Check if there are courses connected with */
                if ($outcome_report->courses) {
                    /* All job roles  selected */
                    if (!empty($data_form[REPORT_GENERATOR_JOB_ROLE_LIST])) {
                        $list = join(',',$data_form[REPORT_GENERATOR_JOB_ROLE_LIST]);
                        $outcome_report->job_roles = self::Outcome_JobRole_List($outcome_id,$list);
                    }else {
                        $outcome_report->job_roles = self::Outcome_JobRole_List($outcome_id);
                    }//if_else

                    /* Check if there are job_roles */
                    if ($outcome_report->job_roles) {
                        /* Level One */
                        $outcome_report->level_one = report_generator_get_company_name($data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'1']);

                        /* Check Level  */
                        switch ($data_form['rpt']) {
                            case 1:
                                /* Level One    */
                                $SESSION->job_roles = array_keys($outcome_report->job_roles);
                                setcookie('parentLevelOne',$data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'1']);
                                $level_two = self::Get_CompaniesLevel(2,null,$data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'1']);
                                $output = array_slice($level_two, 0, 1);
                                $level_two = array_diff($level_two,$output);

                                /* Level Two  */
                                if ($level_two) {
                                    self::Get_CompanyReportInfo_LevelOne($outcome_report,$level_two);
                                }else {
                                    $outcome_report = null;
                                }//if_level_two_companies


                                break;
                            case 2:
                                /* Get companies selected */
                                $companies_report = self::Get_CompanyReportList($data_form);
                                if ($companies_report) {
                                    /* Level Two    */
                                    $SESSION->job_roles = array_keys($outcome_report->job_roles);
                                    setcookie('parentLevelTwo',$data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'2']);
                                    $outcome_report->level_two = report_generator_get_company_name($data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'2']);
                                    self::Get_CompanyReportInfo_LevelTwo($outcome_report,$companies_report);
                                }else {
                                    $outcome_report = null;
                                }//if_companies_report

                                break;
                            case 3:
                                /* Get companies selected */
                                $companies_report = self::Get_CompanyReportList($data_form);
                                if ($companies_report) {
                                    /* Level Three  */
                                    $outcome_report->level_two = report_generator_get_company_name($data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'2']);
                                    self::Get_CompanyReportInfo_LevelThree($outcome_report,$companies_report);
                                }else {
                                    $outcome_report = null;
                                }//if_companies_report

                                break;
                            default:
                                break;
                        }//switch_report_level
                    }else {
                        $outcome_report = null;
                    }//if_job_roles
                }else {
                    $outcome_report = null;
                }//if_outcome_courses
            }//if_outcome_report

            return $outcome_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_OutcomeReportLevel


    /**
     * @static
     * @param           $outcome_report
     * @return          string
     * @throws          Exception
     *
     * @creationDate    11/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the outcome report data - Format Screen
     */
    public static function Print_OutcomeReport_Screen($outcome_report) {
        /* Variables    */
        $out_report         = '';

        try {
            switch ($outcome_report->rpt) {
                case 1:
                    $out_report = self::Print_OutcomeReport_Screen_LevelOne($outcome_report);

                    break;
                case 2:
                    $out_report = self::Print_OutcomeReport_Screen_LevelTwo($outcome_report);

                    break;
                case 3:
                    $out_report = self::Print_OutcomeReport_Screen_LevelThree($outcome_report);

                    break;
                default:
                    break;
            }//switch_my_level

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen

    /**
     * @static
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    17/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Outcome Report - Excel
     */
    public static function Download_OutcomeReport($outcome_report) {
        try {
            switch ($outcome_report->rpt) {
                case 1:
                    self::Download_OutcomeReport_LevelOne($outcome_report);

                    break;
                case 2:
                    self::Download_OutcomeReport_LevelTwo($outcome_report);

                    break;
                case 3:
                    self::Download_OutcomeReport_LevelThree($outcome_report);

                    break;
                default:
                    break;
            }//switch_report_level
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport


    /* *******************  */
    /* PROTECTED FUNCTIONS  */
    /* *******************  */


    /* *****************    */
    /* PRIVATE FUNCTIONS    */
    /* *****************    */

    /**
     * @static
     * @param           $data_form
     * @return          array|bool
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbV)
     *
     * Description
     * Get the companies to show in the report
     */
    private static function Get_CompanyReportList($data_form) {
        /* Variables */
        $company_list = array();

        /* Get companies selected */
        $report_level = $data_form['rpt'];
        switch ($report_level) {
            case 1:
                $parent_list = self::Get_CompaniesLevel(2,null,$data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'1']);
                if (!$parent_list) {
                    return false;
                }//if_empty
                $parent_list = join(',',array_keys($parent_list));
                $company_list = self::Get_CompaniesLevel(3,null,$parent_list);

                $output = array_slice($company_list, 0, 1);
                $company_list = array_diff($company_list,$output);

                break;
            case 2:
                $company_list = self::Get_CompaniesLevel(3,null,$data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'2']);
                $output = array_slice($company_list, 0, 1);
                $company_list = array_diff($company_list,$output);

                if (!$company_list) {
                    return false;
                }//if_empty
                break;
            case 3:
                $company_list = self::Get_CompaniesLevel(3,null,$data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'2']);
                if (!empty($data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'3'])) {
                    $company_keys   = array_keys($company_list);
                    $companies      = array_intersect($data_form[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'3'],$company_keys);
                    $companies      = array_fill_keys($companies,null);
                    $company_list   = array_intersect_key($company_list,$companies);
                }
                if (!$company_list){
                    return false;
                }//if_empty
                break;
        }//switch report_level

        return $company_list;
    }//Get_CompanyReportList

    /**
     * @static
     * @param           $outcome_id
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    18/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the main information connected with outcome
     */
    private static function Get_OutcomeBasicInfo($outcome_id) {
        /* Variables    */
        $outcome_report = null;

        try {
            /* Outcome Report   */
            $outcome_report = new stdClass();
            $outcome_report->id             = $outcome_id;
            $outcome_report->name           = null;
            $outcome_report->description    = null;
            $outcome_report->expiration     = 0;
            $outcome_report->courses        = null;
            $outcome_report->job_roles      = null;
            $outcome_report->level_one      = null;
            $outcome_report->level_two      = null;
            $outcome_report->level_three    = array();

            /* Outcome Name && Description && Expiration Period  */
            self::Get_OutcomeDetails($outcome_id,$outcome_report);
            /* Courses Connected    */
            $outcome_report->courses = self::Get_CoursesOutcome($outcome_id);

            return $outcome_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_OutcomeBasicInfo

    /**
     * @static
     * @param           $outcome_id
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    11/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome details. Name and Expiration period
     */
    private static function Get_OutcomeDetails($outcome_id,&$outcome_report) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['outcome'] =  $outcome_id;

            /* SQL Instruction  */
            $sql = " SELECT		o.fullname,
                                o.description,
                                oe.expirationperiod
                     FROM			{grade_outcomes}			o
                        LEFT JOIN	{report_gen_outcome_exp}	oe	ON oe.outcomeid = o.id
                     WHERE	o.id = :outcome ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $outcome_report->name           = $rdo->fullname;
                $outcome_report->description    = $rdo->description;
                $outcome_report->expiration     = $rdo->expirationperiod;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_OutcomeDetails

    /**
     * @static
     * @param           $outcome_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Courses connected with outcome
     */
    private static function Get_CoursesOutcome($outcome_id) {
        /* Variables    */
        global $DB;
        $courses_lst = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['outcome']  = $outcome_id;

            /* SQL Instruction  */
            $sql = " SELECT	DISTINCT	c.id,
                                        c.fullname
                     FROM		{course}			        c
                        JOIN	{grade_outcomes_courses}	gc 	ON 	gc.courseid   = c.id
                                                                AND gc.outcomeid  = :outcome
                     WHERE		c.visible = 1
                     ORDER BY 	c.fullname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $course) {
                    $courses_lst[$course->id] = $course->fullname;
                }//for_Rdo_course
            }//if_rdo

            return $courses_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CoursesOutcome

    /**
     * @static
     * @param           $outcome_report
     * @param           $company_list
     * @throws          Exception
     *
     * @creationDate    18/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the outcome report data. Structure Level Two
     * Outcome Report  (Object)
     *                          --> name.       Outcome Name.
     *                          --> expiration. Expiration time for the outcome.
     *                          --> job_roles. Job Roles Connected with. Array
     *                          --> courses.    Array.
     *                                  -> Name course
     *                                  -> Id.
     *                          --> level_one.  Company name. Level One
     *                          --> level_two.  Company name. Level Two
     *                          --> level_three. Array. Companies Level Three
     *                                  --> Name Company
     *                                  --> completed.      Array. Total users completed by course
     *                                  --> not_completed.  Array. Total users not completed by course
     *                                  --> not_enrol.      Array. Total users not enrol by course.
     */
    private static function Get_CompanyReportInfo_LevelTwo(&$outcome_report,$company_list) {
        /* Variables    */
        $courses_keys   = null;
        $job_keys       = null;

        try {
            /* Courses Keys */
            $courses_keys = implode(',',array_keys($outcome_report->courses));
            /* Job Roles    */
            $job_keys = implode(',',array_keys($outcome_report->job_roles));

            /* Get Information Level Three    */
            if ($company_list) {
                foreach ($company_list as $id=>$company) {
                    /* Company Info */
                    $company_info = new stdClass();
                    $company_info->name             = $company;
                    $company_info->total            = self::Get_TotalUsers_JR_Company($id,$job_keys);
                    if ($company_info->total) {
                        $company_info->completed            = self::Get_TotalUsers_Completed($id,$job_keys,$courses_keys);
                        $company_info->not_completed        = self::Get_TotalUsers_NotCompleted($id,$job_keys,$courses_keys);
                        $company_info->not_enrol            = self::Get_TotalUsers_NotEnrol($outcome_report->courses,$company_info->completed,$company_info->not_completed,$company_info->total);
                        $outcome_report->level_three[$id]   = $company_info;
                    }
                }//for_company
            }//if_company_list

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyReportInfo_LevelTwo

    /**
     * @static
     * @param           $outcome_report
     * @param           $parent_lst
     * @throws          Exception
     *
     * @creationDate    19/09/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get Outcome Report. Level One
     * Outcome Report (Object)
     *                          --> name.       Outcome Name.
     *                          --> expiration. Expiration time for the outcome.
     *                          --> job_roles. Job Roles Connected with. Array
     *                          --> courses.    Array.
     *                                  -> Name course
     *                                  -> Id.
     *                          --> level_one.  Company name. Level One
     *                          --> level_two.  Level Two
     *                                  --> Name Company.
     *                                  --> level_three. Array. Companies Level Three
     *                                      --> Name Company
     *                                      --> completed.      Array. Total users completed by course
     *                                      --> not_completed.  Array. Total users not completed by course
     *                                      --> not_enrol.      Array. Total users not enrol by course.
     */
    private static function Get_CompanyReportInfo_LevelOne(&$outcome_report,$parent_lst) {
        /* Variables    */
        $level_two      = null;
        $level_three    = null;
        $company_list   = null;

        try {
            /* Get Information Level Two    */
            $outcome_report->level_two = array();
            foreach ($parent_lst as $id=>$company) {
                $company_list   = self::Get_CompaniesLevel(3,null,$id);
                $output         = array_slice($company_list, 0, 1);
                $company_list   = array_diff($company_list,$output);

                if ($company_list) {
                    $level_two = new stdClass();
                    $level_two->name        = report_generator_get_company_name($id);
                    /* Add Level Three  */
                    self::Get_CompanyReportInfo_LevelTwo($outcome_report,$company_list);
                    if ($outcome_report->level_three) {
                        $level_two->level_three = $outcome_report->level_three;
                        /* Add Level Two    */
                        $outcome_report->level_two[$id] = $level_two;
                    }//if_level_three
                    $outcome_report->level_three = array();
                }//if_company_list

            }//for_level_two
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyReportInfo_LevelOne

    /**
     * @static
     * @param           $company_id
     * @param           $job_roles
     * @return          int
     * @throws          Exception
     *
     * @creationDate    19/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get how many users there are connected with job roles for a specific company
     */
    private static function Get_TotalUsers_JR_Company($company_id,$job_roles) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['rgcompany'] = 'rgcompany';
            $params['rgjobrole'] = 'rgjobrole';
            $params['company']   = $company_id;

            /* SQL Instruction  */
            $sql = " SELECT		count(u.id) as 'total'
                     FROM		{user}	u
                        JOIN	{user_info_data}		uid		ON 	uid.userid 		= u.id
                                                                AND uid.data		= :company
                        JOIN	{user_info_field}		uif		ON 	uif.id 			= uid.fieldid
                                                                AND	uif.datatype 	= :rgcompany
                        JOIN	{user_info_data}		uid_jr	ON 	uid_jr.userid 	= u.id
                                                                AND uid_jr.data		IN  ($job_roles)
                        JOIN	{user_info_field}		uif_jr	ON 	uif_jr.id 		= uid_jr.fieldid
                                                                AND	uif_jr.datatype = :rgjobrole
                     WHERE 	u.deleted = 0 ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return 0;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TotalUsers_Company

    /**
     * @static
     * @param           $company_id
     * @param           $job_roles
     * @param           $courses
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    18/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get total users have been completed each course
     */
    private static function Get_TotalUsers_Completed($company_id,$job_roles,$courses) {
        /* Variables */
        global $DB;
        $lst_completed = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['rgcompany'] = 'rgcompany';
            $params['rgjobrole'] = 'rgjobrole';
            $params['company']   = $company_id;

            /* SQL Instruction  */
            $sql = " SELECT     cc.course,
                                count(cc.userid) as 'total_completed'
                     FROM       {course_completions} 	cc
                        JOIN	{user}				u		ON 	u.id 			    = cc.userid
                                                                AND	u.deleted		= 0
                        JOIN	{user_info_data}		uid		ON 	uid.userid 		= u.id
                                                                AND uid.data		= :company
                        JOIN	{user_info_field}		uif		ON 	uif.id 			= uid.fieldid
                                                                AND	uif.datatype 	= :rgcompany
                        JOIN	{user_info_data}		uid_jr	ON 	uid_jr.userid 	= u.id
                                                                AND uid_jr.data		IN  ($job_roles)
                        JOIN	{user_info_field}		uif_jr	ON 	uif_jr.id 		= uid_jr.fieldid
                                                                AND	uif_jr.datatype = :rgjobrole
                     WHERE      cc.course IN ($courses)
                        AND    	cc.timecompleted IS NOT NULL
                     GROUP BY cc.course ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $course) {
                    $lst_completed[$course->course] = $course->total_completed;
                }//for_courses
            }//if_rdo

            return $lst_completed;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TotalUsers_Completed

    /**
     * @static
     * @param           $company_id
     * @param           $job_roles
     * @param           $courses
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    18/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get total users haven't been completed each course
     */
    private static function Get_TotalUsers_NotCompleted($company_id,$job_roles,$courses) {
        /* Variables */
        global $DB;
        $lst_not_completed = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['rgcompany'] = 'rgcompany';
            $params['rgjobrole'] = 'rgjobrole';
            $params['company']   = $company_id;

            /* SQL Instruction  */
            $sql = " SELECT     cc.course,
                                count(cc.userid) as 'total_not_completed'
                     FROM       {course_completions} 	cc
                        JOIN	{user}				u		ON 	u.id 			    = cc.userid
                                                                AND	u.deleted		= 0
                        JOIN	{user_info_data}		uid		ON 	uid.userid 		= u.id
                                                                AND uid.data		= :company
                        JOIN	{user_info_field}		uif		ON 	uif.id 			= uid.fieldid
                                                                AND	uif.datatype 	= :rgcompany
                        JOIN	{user_info_data}		uid_jr	ON 	uid_jr.userid 	= u.id
                                                                AND uid_jr.data		IN  ($job_roles)
                        JOIN	{user_info_field}		uif_jr	ON 	uif_jr.id 		= uid_jr.fieldid
                                                                AND	uif_jr.datatype = :rgjobrole
                     WHERE      cc.course IN ($courses)
                        AND    	cc.timecompleted IS NULL
                     GROUP BY cc.course ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $course) {
                    $lst_not_completed[$course->course] = $course->total_not_completed;
                }//for_courses
            }//if_rdo

            return $lst_not_completed;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TotalUsers_NotCompleted

    /**
     * @static
     * @param           $courses
     * @param           $completed
     * @param           $not_completed
     * @param           $total
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    18/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get total users are still not enrolled
     */
    private static function Get_TotalUsers_NotEnrol($courses,&$completed,&$not_completed,$total) {
        /* Variables */
        $lst_not_enrol          = null;

        try {
            foreach ($courses as $id=>$course) {
                $lst_not_enrol[$id] = $total;

                /* Subtract Total Completed    */
                if ($completed && array_key_exists($id,$completed)) {
                    $lst_not_enrol[$id] = $lst_not_enrol[$id] - $completed[$id];
                }else {
                    $completed[$id] = 0;
                }//completed

                /* Subtract Total Not Completed */
                if ($not_completed && array_key_exists($id,$not_completed)) {
                    $lst_not_enrol[$id] = $lst_not_enrol[$id] - $not_completed[$id];
                }else {
                    $not_completed[$id] = 0;
                }//not_completed
            }//for_courses

            return $lst_not_enrol;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_TotalUsers_NotEnrol

    /**
     * @static
     * @param           $outcome_report
     * @param           $company_list
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the outcome report data. Structure Level three
     * Outcome Report  (Object)
     *                          --> name.       Outcome Name.
     *                          --> expiration. Expiration time for the outcome.
     *                          --> job_roles. Job Roles Connected with. Array
     *                          --> courses.    Array.
     *                                  -> Name course
     *                                  -> Id.
     *                          --> level_one.  Company name. Level One
     *                          --> level_two.  Company name. Level Two
     *                          --> level_three. Array. Companies Level Three
     *                                  --> Name Company
     *                                  --> Users. Array. Employees for this company
     *                                      --> name.           Name of user.
     *                                      --> job_roles.      Job roles connected with user.
     *                                      --> completed.      Array. Courses completed
     *                                      --> not_completed.  Array. Courses not completed yet.
     *                                      --> not_enrol.      Array. Courses not enrolled.
     */
    private static function Get_CompanyReportInfo_LevelThree(&$outcome_report,$company_list) {
        /* Variables    */
        global $DB;
        $courses_keys   = null;
        $job_keys       = null;


        try {
            /* Courses Keys */
            $courses_keys = implode(',',array_keys($outcome_report->courses));

            /* Search Criteria  */
            $params = array();
            $params['rgcompany'] = 'rgcompany';
            $params['rgjobrole'] = 'rgjobrole';
            $job_keys = implode(',',array_keys($outcome_report->job_roles));

            foreach ($company_list as $id=>$company) {
                /* Company Info */
                $company_info = new stdClass();
                $company_info->name     = $company;
                $company_info->users    = array();

                $params['company'] = $id;

                /* SQL Instruction  */
                $sql = " SELECT   	DISTINCT 	u.id,
                                                CONCAT(u.firstname, ' ', u.lastname) as name,
                                                uid_jr.data as 'job_roles'
                         FROM	 	{user}			u
                            JOIN	{user_info_data}	uid			ON 	uid.userid 		= u.id
                                                                    AND uid.data		= :company
                            JOIN	{user_info_field}	uif			ON 	uif.id 			= uid.fieldid
                                                                    AND	uif.datatype 	= :rgcompany
                            JOIN	{user_info_data}	uid_jr		ON 	uid_jr.userid 	= u.id
                                                                    AND uid_jr.data		IN  ($job_keys)
                            JOIN	{user_info_field}	uif_jr		ON 	uif_jr.id 		= uid_jr.fieldid
                                                                    AND	uif_jr.datatype = :rgjobrole
                         WHERE 		u.deleted = 0
                         ORDER BY 	u.firstname, u.lastname ASC ";

                /* Execute  */
                $rdo = $DB->get_records_sql($sql,$params);
                if ($rdo) {
                    foreach ($rdo as $user) {
                        /* User Info    */
                        $user_info = new stdClass();
                        $user_info->name            = $user->name;
                        $user_info->job_roles       = self::get_JobRoleNames_List($user->job_roles);

                        $user_info->completed       = self::Get_CoursesCompleted($user->id,$courses_keys);
                        $user_info->not_completed   = self::Get_CoursesNotCompleted($user->id,$courses_keys);
                        $user_info->not_enrol       = self::Get_CoursesNotEnrol($courses_keys,$user_info->completed ,$user_info->not_completed );

                        $company_info->users[$user->id] = $user_info;
                    }//for_rdo_user
                }//if_rdo

                $outcome_report->level_three[$id] = $company_info;
            }//for_Each company
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyReportInfo_LevelThree

    /**
     * @static
     * @param           $user_id
     * @param           $courses_lst
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the courses have been completed by user
     */
    private static function Get_CoursesCompleted($user_id,$courses_lst) {
        /* Variables    */
        global $DB;
        $completed = array();

        try {
            /* SQL Instruction  */
            $sql = " SELECT     DISTINCT    course,
                                            timecompleted
                     FROM       {course_completions}
                     WHERE      course IN ($courses_lst)
                        AND     timecompleted IS NOT NULL
                        AND     userid = :userid ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,array('userid' => $user_id));
            if ($rdo) {
                foreach ($rdo as $course) {
                    $completed[$course->course] = $course->timecompleted;
                }//for_rdo_course
            }//if_rdo

            return $completed;
        }catch (Exception   $ex) {
            throw $ex;
        }//try_catch
    }//Get_CoursesCompleted

    /**
     * @static
     * @param           $user_id
     * @param           $courses_lst
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the courses haven't been completed by user yet.
     */
    private static function Get_CoursesNotCompleted($user_id,$courses_lst) {
        /* Variables    */
        global $DB;
        $not_completed = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT     DISTINCT    course
                     FROM       {course_completions}
                     WHERE      course IN ($courses_lst)
                        AND     timecompleted IS NULL
                        AND     userid = :userid ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,array('userid' => $user_id));
            if ($rdo) {
                foreach ($rdo as $course) {
                    $not_completed[$course->course] = $course->course;
                }//for_rdo_course
            }//if_rdo

            return $not_completed;
        }catch (Exception   $ex) {
            throw $ex;
        }//try_catch
    }//Get_CoursesNotCompleted

    /**
     * @static
     * @param           $courses
     * @param           $completed
     * @param           $not_completed
     * @return          array|null
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * get the courses not enrolled
     */
    private static function Get_CoursesNotEnrol($courses,$completed,$not_completed) {
        /* Variables    */
        $enrol          = array();
        $not_enrol      = null;
        $lst_courses    = explode(',',$courses);

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

        $not_enrol = array_diff($lst_courses,$enrol);

        return $not_enrol;
    }//Get_CoursesNotEnrol

    /**
     * @param           $outcome_id
     * @param           null $list
     * @return          array
     * @throws          moodle_exception
     *
     * @updateDate      21/09/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return all job roles connected with a specific outcome.
     */
    private static function Outcome_JobRole_List($outcome_id, $list = null) {
        global $DB;

        /* Job Roles & Course */
        $job_role_list = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['outcome_id'] = $outcome_id;

            /* SQL Instruction  */
            $sql = " SELECT		jr.id,
                                jr.name
                     FROM		{report_gen_jobrole} 			jr
                        JOIN	{report_gen_outcome_jobrole}	jro		ON  	jro.jobroleid 	= jr.id
                                                                      AND		jro.outcomeid	= :outcome_id
                     ";
            if ($list) {
                $sql = $sql . "WHERE		jr.id IN ({$list}) ";
            }
            $sql = $sql . " ORDER BY 	jr.name ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $job_role) {
                    $report_info = new stdClass();
                    $report_info->job_role          = $job_role->name;

                    $job_role_list[$job_role->id] = $report_info;
                }//
            }//if_rdo

            return $job_role_list;
        }catch (Exception $ex) {
            throw new moodle_exception($ex->getMessage());
        }
    }//Outcome_JobRole_List

    /**
     * @static
     * @param           $job_role_lst
     * @return          null
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the names of the job roles
     */
    private static function get_JobRoleNames_List($job_role_lst){
        /* Variables    */
        global $DB;

        try {
            /* SQL Instruction  */
            $sql = " SELECT	GROUP_CONCAT(DISTINCT name ORDER BY name SEPARATOR ',') as 'names'
                     FROM 	{report_gen_jobrole}
                     WHERE	id IN ($job_role_lst) ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                return $rdo->names;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_JobRoleNames_List


    /**
     * @static
     * @param           $user_id
     * @param           $site_context
     * @return          int
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbV)
     *
     * Description
     * Get the report/generator view permissions
     */
    private static function Get_MyLevelView($user_id,$site_context) {
        /* Variables    */
        $my_level = 0;

        if (has_capability('report/generator:viewlevel1', $site_context,$user_id) &&
            has_capability('report/generator:viewlevel2', $site_context,$user_id) &&
            has_capability('report/generator:viewlevel3', $site_context,$user_id)) {

            $my_level = 1;
        }else {
            if (!has_capability('report/generator:viewlevel1', $site_context,$user_id) &&
                has_capability('report/generator:viewlevel2', $site_context,$user_id) &&
                has_capability('report/generator:viewlevel3', $site_context,$user_id)) {

                $my_level = 2;
            }else {
                if (!has_capability('report/generator:viewlevel1', $site_context,$user_id) &&
                    !has_capability('report/generator:viewlevel2', $site_context,$user_id) &&
                    has_capability('report/generator:viewlevel3', $site_context,$user_id)) {

                    $my_level = 3;
                }
            }//level_Three
        }//level_One

        return $my_level;
    }//Get_MyLevelView

    /**
     * @static
     * @param           $user_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company connected with user
     */
    private static function Get_MyCompany($user_id) {
        /* Variables    */
        global $DB;

        /* Search Criteria  */
        $params = array();
        $params['user_id']  = $user_id;
        $params['rg']       = 'rgcompany';

        try {
            /* SQL Instruction  */
            $sql = " SELECT		uid.data
                     FROM		{user_info_data}	uid
	                    JOIN	{user_info_field}	uif		ON		uid.fieldid 	= uif.id
											                AND 	uif.datatype 	= :rg
                     WHERE		uid.userid = :user_id ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->data;
            }else {
                return null;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyCompany

    /**
     * @static
     * @param           $my_hierarchy
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get my hierarchy level of my company
     */
    private static function Get_MyHierarchy(&$my_hierarchy) {
        /* Variables    */

        try {
            /* Level Two  */
            $level_two = self::Get_ParentsCompany($my_hierarchy->my_company,2);
            if ($level_two) {
                $my_hierarchy->level_two = implode(',',$level_two);
                foreach ($level_two as $company) {
                    /* Level One    */
                    $level_one = self::Get_ParentsCompany($company,1);

                    if ($level_one) {
                        if ($my_hierarchy->level_one) {
                            $my_hierarchy->level_one .= ',' . implode(',',$level_one);
                        }else {
                            $my_hierarchy->level_one = implode(',',$level_one);
                        }//$my_hierarchy->level_one
                    }//if_level_one
                }
            }//level_two
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyHierarchy

    /**
     * @static
     * @param           $company
     * @param           $level
     * @return          array
     * @throws          Exception
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the parent companies for a specific company
     */
    private static function Get_ParentsCompany($company,$level) {
        /* Variables    */
        global $DB;
        $lst_companies = array();

        try {
            /* Search Criteria  */
            $params             = array();
            $params['level']    = $level;
            $params['company']  = $company;

            /* SQL Instruction  */
            $sql = " SELECT     DISTINCT co.id
                     FROM       mdl_report_gen_company_relation   cr
                        JOIN    mdl_report_gen_companydata        co  ON    co.id             = cr.parentid
                                                                      AND   co.hierarchylevel = :level
                     WHERE      cr.companyid = :company ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $company) {
                    $lst_companies[$company->id] = $company->id;
                }//for_rdo
            }//if_rdo

            return $lst_companies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_ParentsCompany


    /**
     * @static
     * @param           $tab
     * @return          string
     *
     * @creationDate    09/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Build the report link to the third level.
     */
    private static function Get_ThirdLevelLink($tab) {
        $url_level_3 = new moodle_url('/report/generator/' . $tab .'/' . $tab .'_level.php',array('rpt'=>3));

        $out = '<li class="last">' . "\n";
        $out .= '<a href="'.$url_level_3 .'">'. get_string('level_report','report_generator',3) .'</a>';
        $out .= '</li>' . "\n";

        return $out;
    }//Get_ThirdLevelLink

    /**
     * @static
     * @param           $tab
     * @return          string
     *
     * @creationDate    09/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Build the report link to the second level.
     */
    private static function Get_SecondLevelLink($tab) {
        $url_level_2 = new moodle_url('/report/generator/' . $tab .'/' . $tab .'_level.php',array('rpt'=>2));

        $out  = '<li>' . "\n";
        $out .= '<a href="'.$url_level_2 .'">'. get_string('level_report','report_generator',2) .'</a>';
        $out .= '</li>' . "\n";
        $out .= self::Get_ThirdLevelLink($tab);

        return $out;
    }//Get_SecondLevelLink

    /**
     * @static
     * @param           $tab
     * @return          string
     *
     * @creationDate    09/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Build the report link to the first level.
     */
    private static function Get_FirstLevelLink($tab) {
        $url_level_1 = new moodle_url('/report/generator/' . $tab .'/' . $tab .'_level.php',array('rpt'=>1));

        $out = '<li class="first">' . "\n";
        $out .= '<a href="'.$url_level_1 .'">'. get_string('level_report','report_generator',1) .'</a>';
        $out .= '</li>' . "\n";
        $out .= self::Get_SecondLevelLink($tab);

        return $out;
    }//Get_FirstLevelLink

    /**
     * @static
     * @param           $outcome_report
     * @return          string
     * @throws          Exception
     *
     * @creationDate    19/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome Report - Level One - Format Screen
     */
    private static function Print_OutcomeReport_Screen_LevelOne($outcome_report) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggle_level    = null;
        $return_url         = null;

        try {
            $return_url     = new moodle_url('/report/generator/outcome_report/outcome_report_level.php',array('rpt' => $outcome_report->rpt));
            $out_report .= html_writer::start_div('outcome_rpt_div');
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    /* Name Outcome         */
                    $out_report .= '<h2>';
                        $out_report .= get_string('outcome', 'report_generator') . ' "' . $outcome_report->name . '"';
                    $out_report .= '</h2>';
                    /* Outcome Description  */
                    if ($outcome_report->description) {
                        $out_report .= '<h6>' . format_text($outcome_report->description) . '</h6>';
                    }//outcome_report_description

                    /* Companies Levels */
                    $out_report .= '<ul class="level-list unlist">';
                        /* Level One Company    */
                        $out_report .= '<li>';
                            $out_report .= '<h2>'. get_string('company_structure_level', 'report_generator', 1) . ': ' . $outcome_report->level_one . '</h2';
                        $out_report .= '</li>';
                    $out_report .= '</ul>';

                    /* Expiration Before    */
                    $options = report_generator_get_completed_list();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next','report_generator') . ': ' .  $options[$outcome_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_generator'),array('class' => 'link_return'));

                /* Level Two    */
                foreach ($outcome_report->level_two as $id=>$level_two) {
                    if ($level_two->level_three) {
                        /* Toggle   */
                        $url_img  = new moodle_url('/pix/t/expanded.png');
                        $id_toggle = 'YUI_' . $id;
                        $out_report .= self::Add_CompanyHeader_Screen($level_two->name,$id_toggle,$url_img);
                        $out_report .= html_writer::start_tag('div',array('class' => 'level_two_list','id'=> $id_toggle . '_div'));
                            foreach ($level_two->level_three as $id_three=>$level_three) {
                                $id_toggle_level = $id_toggle . '_' . $id_three;
                                $out_report .= html_writer::start_tag('div',array('class' => 'level_div'));
                                    $out_report .= self::Add_CompanyHeader_Screen($level_three->name,$id_toggle_level,$url_img,$id_three,$id);

                                    /* Courses List */
                                    $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggle_level . '_div'));
                                        $out_report .= self::Add_HeaderCourseTable_LevelTwo_Screen();
                                        $out_report .= self::Add_ContentCourseTable_LevelTwo_Screen($outcome_report->courses,$level_three);
                                    $out_report .= html_writer::end_tag('div');//courses_list
                                $out_report .= html_writer::end_tag('div');//level_div
                            }//for_level_three
                        $out_report .= html_writer::end_tag('div');//level_two_list
                    }//if_level_three
                }//for_level_two
            $out_report .= html_writer::end_div();//outcome_rpt_div

            $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_generator'),array('class' => 'link_return'));

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen_LevelOne

    /**
     * @static
     * @param           $outcome_report
     * @return          string
     * @throws          Exception
     *
     * @creationDate    18/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome Report - Level Two - Format Screen
     */
    private static function Print_OutcomeReport_Screen_LevelTwo($outcome_report) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $return_url         = null;


        try {
            $return_url     = new moodle_url('/report/generator/outcome_report/outcome_report_level.php',array('rpt' => $outcome_report->rpt));
            $out_report .= html_writer::start_div('outcome_rpt_div');
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    /* Name Outcome         */
                    $out_report .= '<h2>';
                        $out_report .= get_string('outcome', 'report_generator') . ' "' . $outcome_report->name . '"';
                    $out_report .= '</h2>';
                    /* Outcome Description  */
                    if ($outcome_report->description) {
                        $out_report .= '<h6>' . format_text($outcome_report->description) . '</h6>';
                    }//outcome_report_description

                    /* Companies Levels */
                    $out_report .= '<ul class="level-list unlist">';
                        /* Level One Company    */
                        $out_report .= '<li>';
                            $out_report .= '<h2>'. get_string('company_structure_level', 'report_generator', 1) . ': ' . $outcome_report->level_one . '</h2';
                        $out_report .= '</li>';
                        /* Level Two Company    */
                        if ($outcome_report->rpt > 1) {
                            $out_report .= '<li>';
                                $out_report .= '<h2>' . get_string('company_structure_level', 'report_generator', 2) . ': ' . $outcome_report->level_two . '</h2>';
                            $out_report .= '</li>';
                        }//if_level_2
                    $out_report .= '</ul>';

                    /* Expiration Before    */
                    $options = report_generator_get_completed_list();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next','report_generator') . ': ' .  $options[$outcome_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_generator'),array('class' => 'link_return'));

                /* Level Three Companies    */
                foreach ($outcome_report->level_three as $id=>$company_info) {
                    /* Toggle   */
                    $url_img  = new moodle_url('/pix/t/expanded.png');
                    $id_toggle = 'YUI_' . $id;
                    $out_report .= self::Add_CompanyHeader_Screen($company_info->name,$id_toggle,$url_img,$id);
                    $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggle . '_div'));
                        $out_report .= self::Add_HeaderCourseTable_LevelTwo_Screen();
                        $out_report .= self::Add_ContentCourseTable_LevelTwo_Screen($outcome_report->courses,$company_info);
                    $out_report .= html_writer::end_tag('div');//course_list
                }//for_companies
            $out_report .= html_writer::end_div();//outcome_rpt_div
            $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_generator'),array('class' => 'link_return'));

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen_LevelTwo

    /**
     * @static
     * @param           $outcome_report
     * @return          string
     * @throws          Exception
     *
     * @creationDate    17/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome Report - Level Three - Format Screen
     */
    private static function Print_OutcomeReport_Screen_LevelThree($outcome_report) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggle_user     = null;
        $return_url         = null;

        try {
            $return_url     = new moodle_url('/report/generator/outcome_report/outcome_report_level.php',array('rpt' => $outcome_report->rpt));
            $out_report .= html_writer::start_div('outcome_rpt_div');
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    /* Name Outcome         */
                    $out_report .= '<h2>';
                        $out_report .= get_string('outcome', 'report_generator') . ' "' . $outcome_report->name . '"';
                    $out_report .= '</h2>';
                    /* Outcome Description  */
                    if ($outcome_report->description) {
                        $out_report .= '<h6>' . format_text($outcome_report->description) . '</h6>';
                    }//outcome_report_description

                    /* Companies Levels */
                    $out_report .= '<ul class="level-list unlist">';
                        /* Level One Company    */
                        $out_report .= '<li>';
                            $out_report .= '<h2>'. get_string('company_structure_level', 'report_generator', 1) . ': ' . $outcome_report->level_one . '</h2';
                        $out_report .= '</li>';
                        /* Level Two Company    */
                        if ($outcome_report->rpt > 1) {
                            $out_report .= '<li>';
                                $out_report .= '<h2>' . get_string('company_structure_level', 'report_generator', 2) . ': ' . $outcome_report->level_two . '</h2>';
                            $out_report .= '</li>';
                        }//if_level_2
                    $out_report .= '</ul>';

                    /* Expiration Before    */
                    $options = report_generator_get_completed_list();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next','report_generator') . ': ' .  $options[$outcome_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_generator'),array('class' => 'link_return'));

                /* Level Three Companies    */
                foreach ($outcome_report->level_three as $id=>$company_info) {
                    if ($company_info->users) {
                        /* Toggle   */
                        $url_img  = new moodle_url('/pix/t/expanded.png');
                        $id_toggle = 'YUI_' . $id;
                        $out_report .= self::Add_CompanyHeader_Screen($company_info->name,$id_toggle,$url_img);
                        $out_report .= html_writer::start_tag('div',array('class' => 'user_list','id'=> $id_toggle . '_div'));
                            /* Users    */
                            foreach ($company_info->users as $id_user=>$user) {
                                if ($user->completed || $user->not_completed || $user->not_enrol) {
                                    $id_toggle_user = $id_toggle . '_' . $id_user;
                                    $out_report .= html_writer::start_tag('div',array('class' => 'user_div'));
                                        $out_report .= self::Add_UserHeader_Screen($user->name,$user->job_roles,$id_toggle_user,$url_img);

                                        /* Courses  */
                                        $out_report .= html_writer::start_tag('div',array('class' => 'outcome_course_list','id' => $id_toggle_user . '_div'));
                                            $out_report .= self::Add_HeaderCourseTable_Screen();
                                            $out_report .= self::Add_ContentCourseTable_Screen($outcome_report->courses,$user,$outcome_report->expiration);
                                        $out_report .= html_writer::end_tag('div');//courses_list
                                    $out_report .= html_writer::end_tag('div');//user_div
                                }//if
                            }//users
                        $out_report .= html_writer::end_tag('div');//user_list
                    }//if_users
                }//for_company
            $out_report .= html_writer::end_div();//outcome_rpt_div
            $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_generator'),array('class' => 'link_return'));

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen_LevelThree

    /**
     * @static
     * @param           $company
     * @param           $toogle
     * @param           $img
     * @param           $level_three
     * @param           $level_two
     * @return          null|string
     *
     * @creationDate    17/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the company Header
     */
    private static function Add_CompanyHeader_Screen($company,$toogle,$img,$level_three=null,$level_two=null) {
        /* Variables    */
        $header_company     = null;
        $url_level_three    = null;
        $title_company      = null;

        $header_company .= html_writer::start_div('header_outcome_company_rpt');
            /* Col One  */
            $header_company .= html_writer::start_div('header_col_one');
                $header_company .= '<button class="toggle" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header_company .= html_writer::end_div('');//header_col_one
            /* Col Two  */
            $header_company .= html_writer::start_div('header_col_two');
                if ($level_two) {
                    setcookie('parentLevelTwo',$level_two);
                    $title_company = '<h4>' . $company . '</h4>';
                }else {
                    $title_company = '<h3>' . $company . '</h3>';
                }//if_level_two

                if ($level_three) {
                    $url_level_three = new moodle_url('/report/generator/outcome_report/outcome_report_level.php',array('rpt' => '3','co' => $level_three));
                    $title_company = '<a href="' . $url_level_three . '">' . $title_company . '</a>';
                }//if_company_id
                $header_company .= $title_company;
            $header_company .= html_writer::end_div('');//header_col_two
        $header_company .= html_writer::end_div('');//header_outcome_company_rpt

        return $header_company;
    }//Add_CompanyHeader_Screen

    /**
     * @static
     * @param           $user_name
     * @param           $job_roles
     * @param           $toogle
     * @param           $img
     * @return          null|string
     *
     * @creationDate    17/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Users header
     */
    private static function Add_UserHeader_Screen($user_name,$job_roles,$toogle,$img) {
        /* Variables    */
        $header_user = null;

        $header_user .= html_writer::start_div('user_header');
            /* Col One  */
            $header_user .= html_writer::start_div('header_col_one');
                $header_user .= '<button class="toggle" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header_user .= html_writer::end_div('');//header_col_one
            /* Col Two  */
            $header_user .= html_writer::start_div('header_col_two');
                $header_user .= '<h4>'. $user_name . '</h4>';
            $header_user .= html_writer::end_div('');//header_col_two
        $header_user .= html_writer::end_div('');//user_header

        /* Add the job roles connected with */
        if ($job_roles) {
            $header_user .= html_writer::start_div('job_header');
                /* Col One  */
                $header_user .= '<h5>' . $job_roles . '</h5>';
            $header_user .= html_writer::end_div('');//user_jr_header
        }//if_job_roles

        return $header_user;
    }//Add_UserHeader_Screen

    /**
     * @static
     * @return          null|string
     *
     * @creationDate    17/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the Courses table
     */
    private static function Add_HeaderCourseTable_Screen() {
        /* Variables    */
        $header_table = null;

        $str_course         = get_string('course');
        $str_state          = get_string('state','local_tracker');
        $str_completion     = get_string('completion_time','local_tracker');

        $header_table .= html_writer::start_tag('table');
            $header_table .= html_writer::start_tag('tr',array('class' => 'head'));
                /* Empty Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_first'));
                $header_table .= html_writer::end_tag('td');

                /* Course Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_course'));
                    $header_table .= $str_course;
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
    }//Add_HeaderCourseTable_Screen

    /**
     * @static
     * @param           $courses_lst
     * @param           $user_info
     * @param           $expiration
     * @return          null|string
     *
     * @creationDate    17/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content to the Course table
     */
    private static function Add_ContentCourseTable_Screen($courses_lst,$user_info,$expiration) {
        /* Variables    */
        $content    = null;
        $class      = null;
        $label      = null;

        $content .= html_writer::start_tag('table');
            /* Completed    */
            if ($user_info->completed) {
                foreach ($user_info->completed as $id=>$completed) {
                    $ts = strtotime($expiration  . ' month', $user_info->completed[$id]);
                    if ($ts < time()) {
                        $class = 'expired';
                        $label = get_string('outcome_course_expired','local_tracker');
                    }else {
                        $class = 'completed';
                        $label = get_string('outcome_course_finished','local_tracker');
                    }
                    $content .= html_writer::start_tag('tr',array('class' => $class));
                        /* Button Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        /* Course Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'course'));
                            $content .= $courses_lst[$id];
                        $content .= html_writer::end_tag('td');
                        /* Status Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= $label;
                        $content .= html_writer::end_tag('td');

                        /* Completion Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= userdate($user_info->completed[$id],'%d.%m.%Y', 99, false);;
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_completed
            }//if_completed

            /* In progress  */
            if ($user_info->not_completed) {
                foreach ($user_info->not_completed as $id=>$not_completed) {
                    $content .= html_writer::start_tag('tr');
                    /* Button Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        /* Course Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'course'));
                            $content .= $courses_lst[$id];
                        $content .= html_writer::end_tag('td');
                        /* Status Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= get_string('outcome_course_started','local_tracker');
                        $content .= html_writer::end_tag('td');

                        /* Completion Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= '-';
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_not_completed
            }//if_not_completed

            /* Not Enrol    */
            if ($user_info->not_enrol) {
                foreach ($user_info->not_enrol as $id=>$not_enrol) {
                    $content .= html_writer::start_tag('tr',array('class' => 'not_enroll'));
                        /* Button Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'first'));
                        $content .= html_writer::end_tag('td');
                        /* Course Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'course'));
                            $content .= $courses_lst[$not_enrol];
                        $content .= html_writer::end_tag('td');
                        /* Status Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= get_string('outcome_course_not_enrolled','local_tracker');
                        $content .= html_writer::end_tag('td');

                        /* Completion Col   */
                        $content .= html_writer::start_tag('td',array('class' => 'status'));
                            $content .= '-';
                        $content .= html_writer::end_tag('td');
                    $content .= html_writer::end_tag('tr');
                }//for_completed
            }//if_not_enrol
        $content .= html_writer::end_tag('table');

        return $content;
    }//Add_ContentCourseTable_Screen

    /**
     * @static
     * @return          null|string
     *
     * @creationDate    18/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the table - Level Two
     */
    private static function Add_HeaderCourseTable_LevelTwo_Screen() {
        /* Variables    */
        $header_table = null;

        $str_course         = get_string('course');
        $str_not_enrol      = get_string('not_start','report_generator');
        $str_not_completed  = get_string('progress','report_generator');
        $str_completed      = get_string('completed','report_generator');
        $str_total          = get_string('count','report_generator');

        $header_table .= html_writer::start_tag('table');
            $header_table .= html_writer::start_tag('tr',array('class' => 'head'));
                /* Empty Col   */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_first'));
                $header_table .= html_writer::end_tag('td');
                /* Course           */
                $header_table .= html_writer::start_tag('td',array('class' => 'head_course'));
                    $header_table .= $str_course;
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
    }//Add_HeaderCourseTable_LevelTwo_Screen

    /**
     * @static
     * @param           $courses_lst
     * @param           $company_info
     * @return          null|string
     *
     * @creationDate    18/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table - Level two
     */
    private static function Add_ContentCourseTable_LevelTwo_Screen($courses_lst,$company_info) {
        /* Variables    */
        $content    = null;

        $content .= html_writer::start_tag('table');
            foreach ($courses_lst as $id=>$course) {
                $content .= html_writer::start_tag('tr');
                    /* Empty Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'first'));
                    $content .= html_writer::end_tag('td');
                    /* Course           */
                    $content .= html_writer::start_tag('td',array('class' => 'course'));
                        $content .= $course;
                    $content .= html_writer::end_tag('td');
                    /* Not Enrol        */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                        $content .= $company_info->not_enrol[$id];
                    $content .= html_writer::end_tag('td');
                    /* Not Completed    */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                        $content .= $company_info->not_completed[$id];
                    $content .= html_writer::end_tag('td');
                    /* Completed        */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                        $content .= $company_info->completed[$id];
                    $content .= html_writer::end_tag('td');
                    /* Total            */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                        $content .= $company_info->total;
                    $content .= html_writer::end_tag('td');
                $content .= html_writer::end_tag('tr');
            }
        $content .= html_writer::end_tag('table');

        return $content;
    }//Add_ContentCourseTable_LevelTwo_Screen


    /**
     * @static
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    19/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Outcome Report - Level One
     */
    private static function Download_OutcomeReport_LevelOne($outcome_report) {
        /* Variables    */
        global $CFG;
        $row = 0;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($outcome_report->name . '_' . $time . ".xls");

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* Get Info Basic   -- Header Sheet */
            $out_name   = $outcome_report->name;
            $out_desc   = $outcome_report->description;
            $level_one  = $outcome_report->level_one;
            /* Get Expiration Period            */
            $options            = report_generator_get_completed_list();
            $completed_before   = $options[$outcome_report->completed_before];

            /* Level Two -- New Sheet   */
            foreach ($outcome_report->level_two as $level_two) {
                $row = 0;

                if ($level_two->level_three) {
                    // Adding the worksheet
                    $my_xls = $export->add_worksheet($level_two->name);

                    /* Add Header - Company Outcome Report  - Level One */
                    self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->level_one,null,null,$completed_before,$my_xls,$row);
                    /* Add Header Table */
                    $row++;
                    self::AddHeader_LevelTwo_TableCourse($my_xls,$row);

                    /* Add Content Table    */
                    $row++;
                    foreach ($level_two->level_three as $company) {
                        self::AddContent_LevelTwo_TableCourse($my_xls,$row,$company,$outcome_report->courses);

                        $my_xls->merge_cells($row,0,$row,19);
                        $row ++;
                    }//for_each_company
                }//if_level_three

            }//for_level_Two

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport_LevelOne

    /**
     * @static
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    19/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Outcome Report - Level Two
     */
    private static function Download_OutcomeReport_LevelTwo($outcome_report) {
        /* Variables    */
        global $CFG;
        $row = 0;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($outcome_report->name . '_' . $time . ".xls");

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* Get Expiration Period    */
            $options            = report_generator_get_completed_list();
            $completed_before   = $options[$outcome_report->completed_before];

            // Adding the worksheet
            $my_xls = $export->add_worksheet($outcome_report->name);

            /* Add Header   - Outcome Report    */
            self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->level_one,$outcome_report->level_two,null,$completed_before,$my_xls,$row);
            /* Add Header Table */
            $row++;
            self::AddHeader_LevelTwo_TableCourse($my_xls,$row);

            /* Add Content Table    */
            $row++;
            foreach ($outcome_report->level_three as $company) {
                self::AddContent_LevelTwo_TableCourse($my_xls,$row,$company,$outcome_report->courses);

                $my_xls->merge_cells($row,0,$row,19);
                $row ++;
            }//for_each_company

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport_LevelTwo

    /**
     * @static
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    19/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the table - Level Two
     */
    private static function AddHeader_LevelTwo_TableCourse(&$my_xls,$row) {
        /* Variables    */
        $str_company        = strtoupper(get_string('company','report_generator'));
        $str_course         = strtoupper(get_string('course'));
        $str_not_enrol      = strtoupper(get_string('not_start','report_generator'));
        $str_not_completed  = strtoupper(get_string('progress','report_generator'));
        $str_completed      = strtoupper(get_string('completed','report_generator'));
        $str_total          = strtoupper(get_string('count','report_generator'));
        $col                = 0;

        try {
            /* Company      */
            $my_xls->write($row, $col, $str_company,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Course       */
            $col = $col + 6;
            $my_xls->write($row, $col, $str_course,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
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
     * @static
     * @param           $my_xls
     * @param           $row
     * @param           $company_info
     * @param           $courses_lst
     * @throws          Exception
     *
     * @creationDate    19/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table - Level Two
     */
    private static function AddContent_LevelTwo_TableCourse(&$my_xls,&$row,$company_info,$courses_lst) {
        /* Variables    */
        $col    = 0;

        try {
            foreach ($courses_lst as $id=>$course) {
                /* Company      */
                $my_xls->write($row, $col, $company_info->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,20);

                /* Courses      */
                $col = $col + 6;
                $my_xls->write($row, $col, $course,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,20);

                /* Not Enrol    */
                $col = $col + 6;
                $my_xls->write($row, $col, $company_info->not_enrol[$id],array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                /* In Progress  */
                $col = $col + 2;
                $my_xls->write($row, $col, $company_info->not_completed[$id],array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                /* Completed    */
                $col = $col + 2;
                $my_xls->write($row, $col, $company_info->completed[$id],array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                /* Total        */
                $col = $col + 2;
                $my_xls->write($row, $col, $company_info->total,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                $row++;
                $col = 0;
            }//for_course
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_LevelTwo_TableCourse

    /**
     * @static
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    17/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Outcome Report - Excel - Level Three
     */
    private static function Download_OutcomeReport_LevelThree($outcome_report) {
        /* Variables    */
        global $CFG;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $file_name = clean_filename($outcome_report->name . '_' . $time . ".xls");

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($file_name);

            /* Company --> New Sheet    */
            $out_name   = $outcome_report->name;
            $out_desc   = $outcome_report->description;
            $level_one  = $outcome_report->level_one;
            $level_two  = $outcome_report->level_two;
            $courses    = $outcome_report->courses;
            /* Get Expiration Period    */
            $options            = report_generator_get_completed_list();
            $completed_before   = $options[$outcome_report->completed_before];
            foreach ($outcome_report->level_three as $company) {
                $row = 0;

                // Adding the worksheet
                $my_xls = $export->add_worksheet($company->name);

                /* Add Header - Company Outcome Report  */
                self::AddHeader_CompanySheet($out_name,$out_desc,$level_one,$level_two,$company->name,$completed_before,$my_xls,$row);
                /* Add Header Table                     */
                $row++;
                self::AddHeader_TableCourse($my_xls,$row);
                /* Add Content Table                    */
                if ($company->users) {
                    $row++;
                    foreach ($company->users as $user_info) {
                        self::AddContent_TableCourse($my_xls,$row,$user_info,$courses,$outcome_report->expiration);

                        $my_xls->merge_cells($row,0,$row,14);
                        $row ++;
                    }//for_users
                }//if_company_users
            }//for_Each_company

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport_LevelThree

    /**
     * @static
     * @param           $out_name
     * @param           $out_desc
     * @param           $level_one
     * @param           $level_two
     * @param           $level_three
     * @param           $completed_before
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    17/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Header of the Company Sheet
     */
    private static function AddHeader_CompanySheet($out_name,$out_desc,$level_one,$level_two=null,$level_three=null,$completed_before,&$my_xls,&$row) {
        /* Variables    */
        $title_out          = get_string('outcome', 'report_generator')  . ' - ' . $out_name;
        $title_level_one    = get_string('company_structure_level', 'report_generator', 1) . ': ' . $level_one;
        $title_level_two    = null;
        if ($level_two) {
            $title_level_two    = get_string('company_structure_level', 'report_generator', 2) . ': ' . $level_two;
        }//if_level_two

        $title_expiration   = get_string('expired_next','report_generator') . ': ' . $completed_before;
        $col = 0;

        try {
            /* Outcome Name && Description  */
            $my_xls->write($row, $col, $title_out,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);
            $row++;
            $my_xls->write($row, $col, $out_desc,array('size'=>10, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Level One */
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
            }

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
     * @static
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    17/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the table
     */
    private static function AddHeader_TableCourse(&$my_xls,$row) {
        /* Variables    */
        $str_user           = strtoupper(get_string('user'));
        $str_course         = strtoupper(get_string('course'));
        $str_state          = strtoupper(get_string('state','local_tracker'));
        $str_completion     = strtoupper(get_string('completion_time','local_tracker'));
        $col                = 0;

        try {
            /* User         */
            $my_xls->write($row, $col, $str_user,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);

            /* Course       */
            $col = $col + 3;
            $my_xls->write($row, $col, $str_course,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
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
    }//AddHeader_TableCourse

    /**
     * @static
     * @param           $my_xls
     * @param           $row
     * @param           $user_info
     * @param           $courses
     * @param           $expiration
     * @throws          Exception
     *
     * @creationDate    17/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table
     */
    private static function AddContent_TableCourse(&$my_xls,&$row,$user_info,$courses,$expiration) {
        /* Variables    */
        $col = null;

        try {
            /* Completed        */
            if ($user_info->completed) {
                foreach ($user_info->completed as $id=>$completed) {
                    $col = 0;
                    $ts = strtotime($expiration  . ' month', $user_info->completed[$id]);
                    if ($ts < time()) {
                        $bg_color = '#f2dede';
                        $label = get_string('outcome_course_expired','local_tracker');
                    }else {
                        $bg_color = '#dff0d8';
                        $label = get_string('outcome_course_finished','local_tracker');
                    }

                    /* User     */
                    $my_xls->write($row, $col, $user_info->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Course  */
                    $col = $col + 3;
                    $my_xls->write($row, $col, $courses[$id],array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State        */
                    $col = $col + 6;
                    $my_xls->write($row, $col, $label,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Completion   */
                    $col = $col + 3;
                    $my_xls->write($row, $col, userdate($user_info->completed[$id],'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    $row++;
                }//courses_completed
            }//if_completed

            /* In Progress      */
            if ($user_info->not_completed) {
                foreach ($user_info->not_completed as $id=>$not_completed) {
                    $col = 0;
                    /* User     */
                    $my_xls->write($row, $col, $user_info->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Course  */
                    $col = $col + 3;
                    $my_xls->write($row, $col, $courses[$id],array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State        */
                    $col = $col + 6;
                    $my_xls->write($row, $col, get_string('outcome_course_started','local_tracker'),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
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
            if ($user_info->not_enrol) {
                foreach ($user_info->not_enrol as $id=>$not_enrol) {
                    $col = 0;
                    /* User     */
                    $my_xls->write($row, $col, $user_info->name,array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    /* Course  */
                    $col = $col + 3;
                    $my_xls->write($row, $col, $courses[$not_enrol],array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'left','v_align'=>'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* State        */
                    $col = $col + 6;
                    $my_xls->write($row, $col, get_string('outcome_course_not_enrolled','local_tracker'),array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'center','v_align'=>'center'));
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
    }///AddContent_TableCourse
}//outcome_report