<?php
/**
 * Library code for the Outcome Report Competence Manager.
 *
 * @package         report
 * @subpackage      manager/outcome_report
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    26/03/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Library for the Outcome Report
 *
 */

define('OUTCOME_REPORT_FORMAT_SCREEN', 0);
define('OUTCOME_REPORT_FORMAT_SCREEN_EXCEL', 1);
define('OUTCOME_REPORT_FORMAT_LIST', 'report_format_list');
define('MANAGER_OUTCOME_STRUCTURE_LEVEL','level_');

class outcome_report {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    /**
     * @static
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/03/2015
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
     * @param           $outcome_id
     * @param           null $list
     * @return          array
     * @throws          Exception
     *
     * @updateDate      26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return all job roles connected with a specific outcome.
     */
    public static function Outcome_JobRole_List($outcome_id, $list = null) {
        global $DB;

        /* Job Roles & Course */
        $job_role_list = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['outcome_id'] = $outcome_id;

            /* SQL Instruction  */
            $sql = " SELECT		jr.id,
                                jr.name,
                                jr.industrycode
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
                    $job_role_list[$job_role->id] = $job_role->industrycode . ' - '. $job_role->name;
                }//
            }//if_rdo

            return $job_role_list;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Outcome_JobRole_List


    /**
     * @param           $data_form
     * @param           $my_hierarchy
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the outcome report information to display
     *
     *
     * Outcome Report
     *          - id.
     *          - name
     *          - description
     *          - expiration
     *          - courses.      Array
     *                              --> [id]     --> name
     *          - job_roles     Array
     *                              --> [id]    --> name
     *          - levelZero.    Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelOne.     Array.
     *          - levelOne.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelTwo.     Array
     *          - levelTwo.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelThree.   Array
     *          - levelThree.   Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - courses.      Array
     *                                                          [id]
     *                                                              - name
     *                                                              - completed.        Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                                                          - completed
     *                                                              - not_completed.    Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                              - not_enrol.        Array
     *                                                                                      [id]
     *                                                                                          - name
     *
     *
     * @updateDate      16/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Companies connected with my level and/or my competence
     *
     */
    public static function Get_OutcomeReportLevel($data_form,$my_hierarchy) {
        /* Variables    */
        global $USER;
        $companies_report   = null;
        $outcome_report     = null;
        $outcome_id         = null;
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
            /* Outcome Report - Basic Information */
            $outcome_id     = $data_form[REPORT_MANAGER_OUTCOME_LIST];
            $outcome_report = self::Get_OutcomeBasicInfo($outcome_id);

            /* Clean Temporary */
            self::CleanTemporary($outcome_id);

            if ($outcome_report) {
                $outcome_report->rpt                = $data_form['rpt'];
                $outcome_report->completed_before   = $data_form[REPORT_MANAGER_COMPLETED_LIST];

                /* Get My Companies by Level    */
                list($inZero,$inOne,$inTwo,$inThree) = CompetenceManager::GetMyCompanies_By_Level($my_hierarchy->competence,$my_hierarchy->my_level);
                $inZero     = implode(',',$inZero);
                $inOne      = implode(',',$inOne);
                $inTwo      = implode(',',$inTwo);
                $inThree    = implode(',',$inThree);

                /* Job Roles Selected   */
                $outcome_report->job_roles = self::Get_JobRolesOutcome_Report($outcome_id,$data_form);

                /* Check if there are job_roles */
                if ($outcome_report->job_roles) {
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
                        $outcome_report->levelZero  = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'];
                        $USER->levelZero            = $outcome_report->levelZero;
                        $USER->outcomeReport        = $outcome_id;

                        /* Get Info courses */
                        if ($outcome_report->courses) {
                            /* Courses  */
                            $courses = implode(',',array_keys($outcome_report->courses));
                            if (isset($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3']) && $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3']) {
                                $levelThree = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3'];
                            }
                            self::GetUsers_EnrolledIn($outcome_id,$courses,$outcome_report->job_roles,$levelThree);
                            self::GetUsers_NotEnrolIn($outcome_id,$outcome_report->courses,$outcome_report->job_roles,$levelThree);
                        }//if_courses

                        /* Check Level  */
                        switch ($data_form['rpt']) {
                            case 0:
                                /* Level Zero    */
                                /* Get info connected with Level Zero */
                                $levelOne   = CompetenceManager::GetCompanies_LevelList(1,$data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'],$inOne);
                                unset($levelOne[0]);
                                if ($levelOne) {
                                    self::Get_CompanyReportInfo_LevelOne($outcome_report,$levelOne,$inTwo,$inThree);
                                }else {
                                    $outcome_report->levelOne = null;
                                }//if_levelZero_Companies

                                break;
                            case 1:
                                /* Level One    */
                                $levelOne = new stdClass();
                                $levelOne->id           = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];
                                $levelOne->name         = CompetenceManager::GetCompany_Name($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1']);
                                $levelOne->levelTwo     = null;

                                /* GEt info connected with Level One */
                                $levelTwo   = CompetenceManager::GetCompanies_LevelList(2,$data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'],$inTwo);
                                unset($levelTwo[0]);
                                if ($levelTwo) {
                                    $levelOne->levelTwo      = self::Get_CompanyReportInfo_LevelTwo($outcome_report,$levelTwo,$inThree);
                                    if ($levelOne->levelTwo) {
                                        $outcome_report->levelOne[$levelOne->id]  = $levelOne;
                                    }else {
                                        $levelOne->levelTwo = null;
                                        $outcome_report->levelOne[$levelOne->id]  = $levelOne;
                                    }
                                }else {
                                    $levelOne->levelTwo = null;
                                    $outcome_report->levelOne[$levelOne->id] = $levelOne;
                                }//if_level_two_companies

                                break;
                            case 2:
                                /* Level One    */
                                $levelOne = new stdClass();
                                $levelOne->id                               = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];
                                $levelOne->name                             = CompetenceManager::GetCompany_Name($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1']);
                                $levelOne->levelTwo                         = null;
                                $outcome_report->levelOne[$levelOne->id]    = $levelOne;

                                /* Level Two    */
                                $levelTwo = new stdClass();
                                $levelTwo->id           = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2'];
                                $levelTwo->name         = CompetenceManager::GetCompany_Name($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2']);
                                $levelTwo->levelThree   = null;

                                /* GEt info connected with Level Two */
                                $levelThree     = CompetenceManager::GetCompanies_LevelList(3,$data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2'],$inThree);
                                unset($levelThree[0]);
                                if ($levelThree) {
                                    $levelTwo->levelThree      = self::Get_CompanyReportInfo_LevelThree($outcome_report,$levelThree);
                                    if ($levelTwo->levelThree) {
                                        $outcome_report->levelTwo[$levelTwo->id] = $levelTwo;
                                    }else {
                                        $levelTwo->levelThree = null;
                                        $outcome_report->levelTwo[$levelTwo->id] = $levelTwo;
                                    }
                                }else {
                                    $levelTwo->levelThree = null;
                                    $outcome_report->levelTwo[$levelTwo->id] = $levelTwo;
                                }//if_level_two_companies

                                break;
                            case 3:
                                /* Level One    */
                                $levelOne = new stdClass();
                                $levelOne->id                               = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];
                                $levelOne->name                             = CompetenceManager::GetCompany_Name($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1']);
                                $levelOne->levelTwo                         = null;
                                $outcome_report->levelOne[$levelOne->id]    = $levelOne;

                                /* Level Two    */
                                $levelTwo = new stdClass();
                                $levelTwo->id                               = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2'];
                                $levelTwo->name                             = CompetenceManager::GetCompany_Name($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2']);
                                $levelTwo->levelThree                       = null;
                                $outcome_report->levelTwo[$levelTwo->id]    = $levelTwo;

                                /* Get Info connected with the level three  */
                                $levelThree = CompetenceManager::GetCompanies_LevelList(3,$data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2'],$inThree);
                                unset($levelThree[0]);
                                $selectorThree = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3'];
                                unset($selectorThree[0]);
                                if ($selectorThree) {
                                    $company_keys   = array_keys($levelThree);
                                    $companies      = array_intersect($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3'],$company_keys);
                                    $companies      = array_fill_keys($companies,null);
                                    $levelThree     = array_intersect_key($levelThree,$companies);
                                }

                                /* Level Three  */
                                if ($levelThree) {
                                    $outcome_report->levelThree = self::Get_CompanyReportInfo_LevelThree($outcome_report,$levelThree);
                                }else {
                                    $outcome_report->levelThree = null;
                                }//if_levelThree

                                break;
                            default:
                                $outcome_report = null;

                                break;
                        }//switch_level
                    }//if_companiesEmployees
                }//if_job_roles
            }//if_outcome_report

            return $outcome_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_OutcomeReportLevel

    /**
     * @param       null $outcomeId
     *
     * @throws          Exception
     *
     * @creationDate    30/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Clean temporary table
     */
    public static function CleanTemporary($outcomeId = null) {
        /* Variables    */
        global $DB;
        $params = array();

        try {
            /* Criteria */
            $params['manager']  = $_SESSION['USER']->sesskey;
            $params['report']   = 'outcome';
            if ($outcomeId) {
                $params['outcomeid'] = $outcomeId;
            }//if_outcome

            /* Execute  */
            $DB->delete_records('report_gen_temp',$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CleanTemporary

    /**
     * @param           $outcome_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the outcome report data - Format Screen
     *
     * Outcome Report
     *          - id.
     *          - name
     *          - description
     *          - expiration
     *          - courses.      Array
     *                              --> [id]     --> name
     *          - job_roles     Array
     *                              --> [id]    --> name
     *          - levelZero.    Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelOne.     Array.
     *          - levelOne.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelTwo.     Array
     *          - levelTwo.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelThree.   Array
     *          - levelThree.   Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - courses.      Array
     *                                                          [id]
     *                                                              - name
     *                                                              - completed.        Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                                                          - completed
     *                                                              - not_completed.    Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                              - not_enrol.        Array
     *                                                                                      [id]
     *                                                                                          - name
     */
    public static function Print_OutcomeReport_Screen($outcome_report,$completed_option) {
        /* Variables    */
        $out_report         = '';

        try {
            switch ($outcome_report->rpt) {
                case 0:
                    $out_report = self::Print_OutcomeReport_Screen_LevelZero($outcome_report,$completed_option);

                    break;
                case 1:
                    $out_report = self::Print_OutcomeReport_Screen_LevelOne($outcome_report,$completed_option);

                    break;
                case 2:
                    $out_report = self::Print_OutcomeReport_Screen_LevelTwo($outcome_report,$completed_option);

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
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the outcome report data - Format Excel
     *
     * Outcome Report
     *          - id.
     *          - name
     *          - description
     *          - expiration
     *          - courses.      Array
     *                              --> [id]     --> name
     *          - job_roles     Array
     *                              --> [id]    --> name
     *          - levelZero.    Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelOne.     Array.
     *          - levelOne.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelTwo.     Array
     *          - levelTwo.     Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - levelThree.   Array
     *          - levelThree.   Array
     *                              [id]
     *                                      - id
     *                                      - name
     *                                      - courses.      Array
     *                                                          [id]
     *                                                              - name
     *                                                              - completed.        Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                                                          - completed
     *                                                              - not_completed.    Array
     *                                                                                      [id]
     *                                                                                          - name
     *                                                              - not_enrol.        Array
     *                                                                                      [id]
     *                                                                                          - name
     */
    public static function Download_OutcomeReport($outcome_report) {
        try {
            switch ($outcome_report->rpt) {
                case 0:
                    self::Download_OutcomeReport_LevelZero($outcome_report);

                    break;
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


    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @param           $outcome_id
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the information connected to the outcome
     *
     * Outcome Report
     *          - id.
     *          - name
     *          - description
     *          - expiration
     *          - course.       Array
     *                  -> [id] --> name
     *          - job_roles
     *          - levelZero
     *          - levelOne
     *          - levelTwo
     *          - levelThree
     */
    private static function Get_OutcomeBasicInfo($outcome_id) {
        /* Variables    */
        global $DB;
        $outcome_report  = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['outcome'] =  $outcome_id;

            /* SQL Instruction  */
            $sql = " SELECT		    o.id,
                                    o.fullname,
                                    o.description,
                                    IF(oe.expirationperiod,oe.expirationperiod,0) as 'expiration',
                                    GROUP_CONCAT(DISTINCT oc.courseid ORDER BY oc.courseid SEPARATOR ',') as 'coursesid'
                     FROM			{grade_outcomes}			    o
                        JOIN		{grade_outcomes_courses}	    oc	ON  oc.outcomeid    = o.id
                        LEFT JOIN	{report_gen_outcome_exp}	    oe	ON  oe.outcomeid    = oc.outcomeid
                     WHERE			o.id = :outcome ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Outcome Report   */
                $outcome_report = new stdClass();
                $outcome_report->id             = $rdo->id;
                $outcome_report->name           = $rdo->fullname;
                $outcome_report->description    = $rdo->description;
                $outcome_report->expiration     = $rdo->expiration;
                $outcome_report->courses        = self::Get_CourseDetail($rdo->coursesid);
                $outcome_report->job_roles      = null;
                $outcome_report->levelZero      = null;
                $outcome_report->levelOne       = null;
                $outcome_report->levelTwo       = null;
                $outcome_report->levelThree     = null;


            }//if_rdo

            return $outcome_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_OutcomeBasicInfo

    /**
     * @param           $outcomeId
     * @param           $courses
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
    private static function GetUsers_EnrolledIn($outcomeId,$courses,$jobRoles,$companies = null) {
        /* Variables    */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $jrUsers        = null;
        $jobKeys        = null;
        $infoTempReport = null;

        try {
            /* Extra Info   */
            $jobKeys    = array_flip(array_keys($jobRoles));
            $managerKey = $_SESSION['USER']->sesskey;

            /* SQL Instruction  */
            $sql = " SELECT	CONCAT(cc.id,'_',uic.id),
                            cc.course,
                            u.id 			                      as 'user',
                            CONCAT(u.firstname, ' ', u.lastname)  as 'name',
                            uic.companyid,
                            uic.jobroles,
                            cc.timecompleted
                     FROM		{course_completions}		cc
                        JOIN	{user_info_competence_data}	uic		ON 	uic.userid 	= cc.userid
                        JOIN	{user}						u		ON 	u.id 		= uic.userid
                                                                    AND u.deleted 	= 0
                     WHERE	  cc.course IN ($courses) ";

            /* Companies Criteria    */
            if ($companies) {
                $companies = implode(',',array_keys($companies));
                $sql .= " AND uic.companyid IN ($companies) ";
            }//if_companies

            /* ORDER BY */
            $sql .= " ORDER BY cc.course,u.id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $jrUsers = array_flip(explode(',',$instance->jobroles));
                    if (array_intersect_key($jobKeys,$jrUsers)) {
                        /* Info Outcome Report  */
                        $infoTempReport = new stdClass();
                        $infoTempReport->manager    = $managerKey;
                        $infoTempReport->report     = 'outcome';
                        $infoTempReport->userid     = $instance->user;
                        $infoTempReport->name       = $instance->name;
                        $infoTempReport->companyid  = $instance->companyid;
                        $infoTempReport->courseid   = $instance->course;
                        $infoTempReport->outcomeid  = $outcomeId;
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
                    }//if_job_roles
                }//for_rdo
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_EnrolledIn

    /**
     * @param           $outcomeId
     * @param           $courses
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
    private static function GetUsers_NotEnrolIn($outcomeId,$courses,$jobRoles,$companies = null) {
        /* Variables    */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $jrUsers        = null;
        $jobKeys        = null;
        $infoTempReport = null;

        try {
            /* Extra Info   */
            $jobKeys    = array_flip(array_keys($jobRoles));
            $managerKey = $_SESSION['USER']->sesskey;

            /* Search Criteria  */
            $params = array();
            $params['outcome']  = $outcomeId;

            /* SQL Instruction  */
            $sql = " SELECT		CONCAT(u.id,'_',uic.id),
                                u.id,
                                CONCAT(u.firstname, ' ', u.lastname)  as 'name',
                                uic.companyid,
                                uic.jobroles
                     FROM			{user} 						  u
                        JOIN		{user_info_competence_data}	  uic		ON 	uic.userid 		= u.id
                        LEFT JOIN	{report_gen_temp}			  tmp		ON 	tmp.userid 		= uic.userid
                                                                            AND	tmp.outcomeid	= :outcome
                                                                            AND tmp.courseid 	= :course
                                                                            AND tmp.report      = 'outcome'
                     WHERE	u.deleted 	= 0
                        AND	u.username != 'guest'
                        AND	tmp.id IS NULL ";

            /* Companies Criteria    */
            if ($companies) {
                $companies = implode(',',array_keys($companies));
                $sql .= " AND uic.companyid IN ($companies) ";
            }//if_companies


            /* Get users not enroll for each course */
            foreach ($courses as $id => $info) {
                $params['course'] = $id;

                /* Execute  */
                $rdo = $DB->get_records_sql($sql,$params);
                if ($rdo) {
                    foreach ($rdo as $instance) {
                        $jrUsers = array_flip(explode(',',$instance->jobroles));
                        if (array_intersect_key($jobKeys,$jrUsers)) {
                            /* Info Outcome Report  */
                            $infoTempReport = new stdClass();
                            $infoTempReport->manager        = $managerKey;
                            $infoTempReport->report         = 'outcome';
                            $infoTempReport->userid         = $instance->id;
                            $infoTempReport->name           = $instance->name;
                            $infoTempReport->companyid      = $instance->companyid;
                            $infoTempReport->courseid       = $id;
                            $infoTempReport->outcomeid      = $outcomeId;
                            $infoTempReport->completed      = 0;
                            $infoTempReport->notcompleted   = 0;
                            $infoTempReport->notenrol       = 1;
                            $infoTempReport->timecompleted  = null;

                            /* Execute  */
                            $DB->insert_record('report_gen_temp',$infoTempReport);
                        }//if_job_roles
                    }//for_rdo
                }//if_rdo
            }//for_course
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_NotEnrolIn

    /**
     * @param           $courses
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the detail of the courses
     */
    private static function Get_CourseDetail($courses) {
        /* Variables    */
        global $DB;
        $courses_lst    = array();
        $course_info    = null;

        try {
            if ($courses) {
                /* SQL Instruction  */
                $sql = " SELECT		c.id,
                                    c.fullname
                         FROM		{course}			        c
                         WHERE		c.visible = 1
                            AND     c.id IN ($courses)
                         ORDER BY 	c.fullname ";

                /* Execute  */
                $rdo = $DB->get_records_sql($sql);
                if ($rdo) {
                    foreach ($rdo as $course) {
                        $courses_lst[$course->id] = $course->fullname;
                    }//for_Rdo_course
                }//if_rdo
            }//if_courses

            return $courses_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CourseDetail

    /**
     * @param           $outcome_id
     * @param           $data_form
     * @return          array
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the job roles connected to the outcome and the company level
     */
    private static function Get_JobRolesOutcome_Report($outcome_id,$data_form) {
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
                $job_roles = self::Outcome_JobRole_List($outcome_id,$list);
            }else {
                /* Job Roles - Outcome          */
                $job_roles = self::Outcome_JobRole_List($outcome_id);
            }//if_else

            /* Save Job Roles Selected  */
            $SESSION->job_roles = array_keys($job_roles);

            /* Job Roles - Outcome Level    */
            switch ($data_form['rpt']) {
                case 0:
                    /* Get Level        */
                    $levelZero = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'];

                    /* Get Job Roles    */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($jr_level);
                    }//if_public
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,0,$levelZero);

                    break;
                case 1:
                    /* Get Level        */
                    $levelZero = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'];
                    $levelOne  = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];

                    /* Get Job Roles    */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($jr_level);
                    }//if_public
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,0,$levelZero);
                    CompetenceManager::GetJobRoles_Hierarchy($jr_level,1,$levelZero,$levelOne);

                    break;
                case 2:
                    /* Get Level        */
                    $levelZero = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'];
                    $levelOne  = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];
                    $levelTwo  = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2'];

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
                    $levelZero  = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'0'];
                    $levelOne   = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'1'];
                    $levelTwo   = $data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'2'];

                    /* Get Job Roles    */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($jr_level);
                    }//if_public
                    if (isset($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3']) && ($data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3'])) {
                        $levelThree = implode(',',$data_form[MANAGER_OUTCOME_STRUCTURE_LEVEL .'3']);
                        /* Get Job Roles    */
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,3,$levelZero,$levelOne,$levelTwo,$levelThree);
                    }else {
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,0,$levelZero);
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,1,$levelZero,$levelOne);
                        CompetenceManager::GetJobRoles_Hierarchy($jr_level,2,$levelZero,$levelOne,$levelTwo);
                    }//if_levelThree

                    break;
            }//switch_level

            if (array_intersect_key($job_roles,$jr_level)) {
                $job_roles = array_intersect_key($job_roles,$jr_level);
                return $job_roles;
            }else {
                return $job_roles;
            }//if_intersect
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRolesOutcome_Report

    /**
     * @param           $outcome_report
     * @param           $parent_lst
     * @param           $inTwo
     * @param           $inThree
     * @throws          Exception
     *
     * @creationDate    27/03/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the information connected to the level one
     *
     * Level One
     *          [id]
     *              --> id
     *              --> name
     *              --> levelTwo.   Array
     *                                  [id]
     *                                      --> id
     *                                      --> name
     *                                      --> levelThree.     Array
     *                                                              [id]
     *                                                                  --> id
     *                                                                  --> name
     *                                                                  --> courses.    Array
     *                                                                                       [id]
     *                                                                                              --> name
     *                                                                                              --> completed.      Array
     *                                                                                              --> not_completed.  Array
     *                                                                                              --> not_enrol.      Array
     */
    private static function Get_CompanyReportInfo_LevelOne(&$outcome_report,$parent_lst,$inTwo,$inThree) {
        /* Variables    */
        $levelTwo      = null;
        $company_list  = null;

        try {
            /* Get Information Level One    */
            foreach ($parent_lst as $id=>$company) {
                /* Get Level Two connected with   */
                $company_list   = CompetenceManager::GetCompanies_LevelList(2,$id,$inTwo);
                unset($company_list[0]);

                /* Level Two */
                if ($company_list) {
                   /* Get Info Level Two  */
                    if ($company_list) {
                        $levelTwo = self::Get_CompanyReportInfo_LevelTwo($outcome_report,$company_list,$inThree);
                        if ($levelTwo) {
                            /* Level One Info   */
                            $companyInfo = new stdClass();
                            $companyInfo->name      = $company;
                            $companyInfo->id        = $id;
                            $companyInfo->levelTwo  = $levelTwo;

                            $outcome_report->levelOne[$id] = $companyInfo;
                        }//if_levelTwo
                    }
                }//if_company_list
            }//for_companies_level_One
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyReportInfo_LevelOne


    /**
     * @param           $outcome_report
     * @param           $parent_lst
     * @param           $inThree
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the information connected to the level two
     *
     * Level Two
     *          [id]
     *                  --> id
     *                  --> name
     *                  --> levelThree.     Array
     *                                          [id]
     *                                              --> id
     *                                              --> name
     *                                              --> courses.    Array
     *                                                                  [id]
     *                                                                      --> name
     *                                                                      --> completed
     *                                                                      --> not_completed
     *                                                                      --> not_enrol
     */
    private static function Get_CompanyReportInfo_LevelTwo($outcome_report,$parent_lst,$inThree) {
        /* Variables    */
        $levelTwo      = array();
        $companyInfo   = null;
        $levelThree    = null;
        $company_list  = null;

        try {
            /* Get Information Level Two    */
            foreach ($parent_lst as $id=>$company) {
                /* Get Level Three connected with   */
                $company_list   = CompetenceManager::GetCompanies_LevelList(3,$id,$inThree);
                unset($company_list[0]);

                /* Level Three */
                if ($company_list) {
                    /* Get Info Level Three  */
                    if ($company_list) {
                        $levelThree = self::Get_CompanyReportInfo_LevelThree($outcome_report,$company_list);
                        if ($levelThree) {
                            /* Level two Info   */
                            $companyInfo = new stdClass();
                            $companyInfo->name       = $company;
                            $companyInfo->id         = $id;
                            $companyInfo->levelThree = $levelThree;

                            $levelTwo[$id] = $companyInfo;
                        }//if_levelTwo
                    }//if_company_list
                }//if_company_list
            }//for_companies_level_Two

            return $levelTwo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyReportInfo_LevelTwo


    /**
     * @param           $outcome_report
     * @param           $company_list
     * @return          array
     * @throws          Exception
     *
     * @creationDate    27/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the information connected to the level three
     *
     * Level Three
     *          [id]
     *                  --> id
     *                  --> name
     *                  --> courses.    Array
     *                                      [id]
     *                                          --> name
     *                                          --> completed.      Array
     *                                                                  [id]
     *                                                                      --> name
     *                                                                      --> completed
     *                                          --> not_completed.  Array
     *                                                                  [id]
     *                                                                      --> name
     *                                          --> not_enrol.      Array
     *                                                                  [id]
     *                                                                      --> name
     */
    private static function Get_CompanyReportInfo_LevelThree($outcome_report,$company_list) {
        /* Variables    */
        $levelThree     = array();
        $course_info    = null;

        try {
            /* Get Information Level Three  */
            if ($company_list) {
                foreach ($company_list as $id=>$company) {
                    /* Company Info */
                    $company_info = new stdClass();
                    $company_info->name       = $company;
                    $company_info->id         = $id;
                    $company_info->courses    = array();
                    $course_info              = null;

                    /* Get Info Courses     */
                    foreach ($outcome_report->courses as $id_course=>$course) {
                        /* Course info  */
                        $course_info = new stdClass();
                        $course_info->name          = $course;
                        /* Completed,Not Completed, Not Enrol      */
                        list($course_info->completed,$course_info->not_completed,$course_info->not_enrol) = self::GetUsers_CompanyCourse($id,$id_course,$outcome_report->id);

                        /* Add Course Info  */
                        if ($course_info->completed || $course_info->not_completed || $course_info->not_enrol) {
                            $company_info->courses[$id_course] = $course_info;;
                        }//if_uses
                    }//for_courses

                    /* Add Level Three  */
                    if ($company_info->courses) {
                        $levelThree[$id] = $company_info;
                    }//if_courses
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
     * @param           $outcome
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
    private static function GetUsers_CompanyCourse($company,$course,$outcome) {
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
            $params['outcomeid']    = $outcome;
            $params['report']       = 'outcome';

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
     * @param           $outcome_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome report - Level Zero - Format Screen
     */
    private static function Print_OutcomeReport_Screen_LevelZero($outcome_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggleOne       = null;
        $id_toggleTwo       = null;
        $return_url         = null;
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;

        try {
            /* Url To Back  */
            $return_url     = new moodle_url('/report/manager/outcome_report/outcome_report_level.php',array('rpt' => $outcome_report->rpt));

            /* Outcome Report   */
            $out_report .= html_writer::start_div('outcome_rpt_div');
                /* Outcome Report Header    */
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    /* Outcome Title */
                    $out_report .= '<h3>';
                        $out_report .= get_string('outcome', 'report_manager') . ' "' . $outcome_report->name . '"';
                    $out_report .= '</h3>';
                    /* Outcome Description  */
                    $out_report .= '<h6>';
                        $out_report .= $outcome_report->description;
                    $out_report .= '</h6>';

                    /* Job Roles    */
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($outcome_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$outcome_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    /* Company Levels   */
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        /* Level Zero       */
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . CompetenceManager::GetCompany_Name($outcome_report->levelZero) . '</h3>';
                        $out_report .= '</li>';
                    $out_report .= '</ul>';
                    /* Expiration Before    */
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next', 'report_manager') .  $options[$outcome_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                /* Level One    */
                $levelOne = $outcome_report->levelOne;
                if (!$levelOne) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    /* Return To Selection Page */
                    $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));

                    /* Report Info */
                    /* Toggle   */
                    $url_img  = new moodle_url('/pix/t/expanded.png');
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelOne as $idOne=>$one) {
                            /* Level Two    */
                            $levelTwo = $one->levelTwo;
                            if ($levelTwo) {
                                $id_toggle   = 'YUI_' . $idOne;
                                $out_report .= self::Add_CompanyHeader_LevelZero_Screen($one->name,$id_toggle,$url_img);
                                $out_report .= html_writer::start_tag('div',array('class' => 'level_one_list','id'=> $id_toggle . '_div'));
                                    /* Level Two List   */
                                    foreach ($levelTwo as $idTwo=>$companyTwo) {
                                        if ($companyTwo->levelThree) {
                                            /* Toggle */
                                            $id_toggleOne = $id_toggle . '_' . $idTwo;
                                            $out_report .= self::Add_CompanyHeader_LevelOne_Screen($companyTwo->name,$id_toggleOne,$url_img);

                                            /* Level Two List   */
                                            $levelThree = $companyTwo->levelThree;
                                            $out_report .= html_writer::start_tag('div',array('class' => 'level_two_list','id'=> $id_toggleOne . '_div'));
                                                foreach ($levelThree as $id_Three=>$company) {
                                                    if ($company->courses) {
                                                        /* Toggle   */
                                                        $id_toggleThree = $id_toggleOne . '_'. $id_Three;
                                                        /* Header Company   - Level Three   */
                                                        $url_levelThree = new moodle_url('/report/manager/outcome_report/outcome_report_level.php',
                                                                                         array('rpt' => '3','co' => $id_Three,'lt' => $idTwo,'lo'=>$idOne,'opt' => $completed_option));
                                                        $out_report .= self::Add_CompanyHeader_Screen($company->name,$id_toggleThree,$url_img,$url_levelThree);

                                                        /* Info company - Courses */
                                                        $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggleThree . '_div'));
                                                            $out_report .= html_writer::start_tag('table');
                                                                $out_report .= self::Add_HeaderCourseTable_LevelTwo_Screen();
                                                                $out_report .= self::Add_ContentCourseTable_LevelTwo_Screen($company->courses);
                                                            $out_report .= html_writer::end_tag('table');
                                                        $out_report .= html_writer::end_tag('div');//courses_list
                                                    }//if_courses
                                            }//for_levelThree
                                            $out_report .= html_writer::end_tag('div');//level_two_list
                                        }//if_levelThree
                                    }//for_level_two
                                $out_report .= html_writer::end_tag('div');//level_one_list
                            }//if_levelTwo
                        }//for_levelOne
                    $out_report .= html_writer::end_tag('div');//outcome_content
                }//if_levelOne

            $out_report .= html_writer::end_div();//outcome_rpt_div

            /* Return To Selection Page */
            $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen_LevelZero

    /**
     * @param           $outcome_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome report - Level One - Format Screen
     */
    private static function Print_OutcomeReport_Screen_LevelOne($outcome_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $id_toggleThree     = null;
        $return_url         = null;
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;

        try {
            /* Url To Back  */
            $return_url     = new moodle_url('/report/manager/outcome_report/outcome_report_level.php',array('rpt' => $outcome_report->rpt));

            /* Outcome Report   */
            $out_report .= html_writer::start_div('outcome_rpt_div');
                /* Outcome Report Header    */
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    /* Outcome Title */
                    $out_report .= '<h3>';
                        $out_report .= get_string('outcome', 'report_manager') . ' "' . $outcome_report->name . '"';
                    $out_report .= '</h3>';
                    /* Outcome Description  */
                    $out_report .= '<h6>';
                        $out_report .= $outcome_report->description;
                    $out_report .= '</h6>';

                    /* Job Roles    */
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($outcome_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$outcome_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    /* Company Levels   */
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        /* Level Zero       */
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . CompetenceManager::GetCompany_Name($outcome_report->levelZero) . '</h3>';
                        $out_report .= '</li>';
                        /* Level One        */
                        $levelOne = array_shift($outcome_report->levelOne);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                    $out_report .= '</ul>';

                    /* Expiration Before    */
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next', 'report_manager') .  $options[$outcome_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                /* Level Two    */
                $levelTwo = $levelOne->levelTwo;
                if (!$levelTwo) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    /* Return To Selection Page */
                    $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));

                    /* Report Info  */
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelTwo as $id_Two=>$companyTwo) {
                            /* Level Three  */
                            $levelThree = $companyTwo->levelThree;
                            if ($levelThree) {
                                /* Toggle   */
                                $url_img  = new moodle_url('/pix/t/expanded.png');
                                $id_toggle = 'YUI_' . $id_Two;
                                /* Header Company - Level Two   */
                                $out_report .= self::Add_CompanyHeader_LevelZero_Screen($companyTwo->name,$id_toggle,$url_img);

                                /* Level Two List   */
                                $out_report .= html_writer::start_tag('div',array('class' => 'level_two_list','id'=> $id_toggle . '_div'));
                                    foreach ($levelThree as $id_Three=>$company) {
                                        if ($company->courses) {
                                            /* Toggle   */
                                            $id_toggleThree = $id_toggle . '_'. $id_Three;
                                            /* Header Company   - Level Three   */
                                            $url_levelThree = new moodle_url('/report/manager/outcome_report/outcome_report_level.php',
                                                                             array('rpt' => '3','co' => $id_Three,'lt' => $id_Two,'lo'=>$levelOne->id,'opt' => $completed_option));
                                            $out_report .= self::Add_CompanyHeader_Screen($company->name,$id_toggleThree,$url_img,$url_levelThree);

                                            /* Info company - Courses */
                                            $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggleThree . '_div'));
                                                $out_report .= html_writer::start_tag('table');
                                                    $out_report .= self::Add_HeaderCourseTable_LevelTwo_Screen();
                                                    $out_report .= self::Add_ContentCourseTable_LevelTwo_Screen($company->courses);
                                                $out_report .= html_writer::end_tag('table');
                                            $out_report .= html_writer::end_tag('div');//courses_list
                                        }//if_courses
                                    }//for_levelThree
                                $out_report .= html_writer::end_tag('div');//level_two_list
                            }//if_levelThree
                        }//for_levelTwo
                    $out_report .= html_writer::end_tag('div');//outcome_content
                }//if_levelTwo
            $out_report .= html_writer::end_div();//outcome_rpt_div

            /* Return To Selection Page */
            $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen_LevelOne

    /**
     * @param           $outcome_report
     * @param           $completed_option
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get Outcome Report - Level Two - Format Screen
     */
    private static function Print_OutcomeReport_Screen_LevelTwo($outcome_report,$completed_option) {
        /* Variables    */
        $out_report         = '';
        $url_img            = null;
        $id_toggle          = null;
        $return_url         = null;
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;
        $url_levelThree     = null;

        try {
            /* Url To Back  */
            $return_url     = new moodle_url('/report/manager/outcome_report/outcome_report_level.php',array('rpt' => $outcome_report->rpt));

            /* Outcome Report   */
            $out_report .= html_writer::start_div('outcome_rpt_div');
                /* Outcome Report Header    */
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    /* Outcome Title */
                    $out_report .= '<h3>';
                        $out_report .= get_string('outcome', 'report_manager') . ' "' . $outcome_report->name . '"';
                    $out_report .= '</h3>';
                    /* Outcome Description  */
                    $out_report .= '<h6>';
                        $out_report .= $outcome_report->description;
                    $out_report .= '</h6>';

                    /* Job Roles    */
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($outcome_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$outcome_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    /* Company Levels   */
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        /* Level Zero       */
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . CompetenceManager::GetCompany_Name($outcome_report->levelZero) . '</h3>';
                        $out_report .= '</li>';
                        /* Level One        */
                        $levelOne = array_shift($outcome_report->levelOne);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                        /* Level Two    */
                        $levelTwo = array_shift($outcome_report->levelTwo);
                        if ($levelTwo) {
                            $out_report .= '<li>';
                                $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 2) . ': ' . $levelTwo->name . '</h3>';
                            $out_report .= '</li>';
                        }//if_level_two
                    $out_report .= '</ul>';

                    /* Expiration Before    */
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next', 'report_manager') .  $options[$outcome_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                /* Level Three  */
                $levelThree = $levelTwo->levelThree;
                if (!$levelThree) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    /* Return To Selection Page */
                    $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));

                    /* Report Info  */
                    if ($levelThree) {
                        $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                            foreach ($levelThree as $id=>$company) {
                                if ($company->courses) {
                                    /* Toggle   */
                                    $url_img  = new moodle_url('/pix/t/expanded.png');
                                    $id_toggle = 'YUI_' . $id;
                                    /* Header Company   - Level Three   */
                                    $url_levelThree = new moodle_url('/report/manager/outcome_report/outcome_report_level.php',
                                                                     array('rpt' => '3','co' => $id,'lt' => $levelTwo->id,'lo'=>$levelOne->id,'opt' => $completed_option));
                                    $out_report .= self::Add_CompanyHeader_Screen($company->name,$id_toggle,$url_img,$url_levelThree);

                                    /* Info company - Courses */
                                    $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggle . '_div'));
                                        $out_report .= html_writer::start_tag('table');
                                            $out_report .= self::Add_HeaderCourseTable_LevelTwo_Screen();
                                            $out_report .= self::Add_ContentCourseTable_LevelTwo_Screen($company->courses);
                                        $out_report .= html_writer::end_tag('table');
                                    $out_report .= html_writer::end_tag('div');//courses_list
                                }//if_courses
                            }//for_level_three
                        $out_report .= html_writer::end_tag('div');//outcome_content
                    }//if_level_three
                }//if_levelTwo
            $out_report .= html_writer::end_div();//outcome_rpt_div

            /* Return To Selection Page */
            $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen_LevelTwo

    /**
     * @param           $outcome_report
     * @return          string
     * @throws          Exception
     *
     * @creationDate    30/03/2015
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
        $id_toggle_course   = null;
        $return_url         = null;
        $levelOne           = null;
        $levelTwo           = null;
        $levelThree         = null;
        $courses            = null;

        try {
            /* Url To Back  */
            $return_url     = new moodle_url('/report/manager/outcome_report/outcome_report_level.php',array('rpt' => $outcome_report->rpt));

            /* Outcome Report   */
            $out_report .= html_writer::start_div('outcome_rpt_div');
                /* Outcome Report Header    */
                $out_report .= html_writer::start_div('outcome_detail_rpt');
                    /* Outcome Title */
                    $out_report .= '<h3>';
                        $out_report .= get_string('outcome', 'report_manager') . ' "' . $outcome_report->name . '"';
                    $out_report .= '</h3>';
                    /* Outcome Description  */
                    $out_report .= '<h6>';
                        $out_report .= $outcome_report->description;
                    $out_report .= '</h6>';

                    /* Job Roles    */
                    $out_report .= '<h5>';
                        $out_report .= get_string('job_roles', 'report_manager');
                    $out_report .= '</h5>';
                    if ($outcome_report->job_roles) {
                        $out_report .= '<h6>';
                            $out_report .= implode(', ',$outcome_report->job_roles);
                        $out_report .= '</h6>';
                    }//if_job_roles

                    /* Company Levels   */
                    $out_report .= '</br>';
                    $out_report .= '<ul class="level-list unlist">';
                        /* Level Zero       */
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 0) . ': ' . CompetenceManager::GetCompany_Name($outcome_report->levelZero) . '</h3>';
                        $out_report .= '</li>';
                        /* Level One        */
                        $levelOne = array_shift($outcome_report->levelOne);
                        $out_report .= '<li>';
                            $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name . '</h3>';
                        $out_report .= '</li>';
                        /* Level Two    */
                        $levelTwo = array_shift($outcome_report->levelTwo);
                        if ($levelTwo) {
                            $out_report .= '<li>';
                                $out_report .= '<h3>'. get_string('company_structure_level', 'report_manager', 2) . ': ' . $levelTwo->name . '</h3>';
                            $out_report .= '</li>';
                        }//if_level_two
                    $out_report .= '</ul>';

                    /* Expiration Before    */
                    $options = CompetenceManager::GetCompletedList();
                    $out_report .= html_writer::start_div('expiration');
                        $out_report .= get_string('expired_next', 'report_manager') .  $options[$outcome_report->completed_before];
                    $out_report .= html_writer::end_div();//expiration
                $out_report .= html_writer::end_div();//outcome_detail_rpt

                /* Level Three  */
                $levelThree = $outcome_report->levelThree;
                if (!$levelThree) {
                    $out_report .= '<h3>';
                        $out_report .= get_string('no_data', 'report_manager');
                    $out_report .= '</h3>';
                }else {
                    /* Return To Selection Page */
                    $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));

                    /* REport Info  */
                    $out_report .= html_writer::start_tag('div',array('class' => 'outcome_content'));
                        foreach ($levelThree as $id=>$company) {
                            /* Company  Info    */
                            if ($company->courses) {
                                /* Toggle   */
                                $url_img  = new moodle_url('/pix/t/expanded.png');
                                $id_toggle = 'YUI_' . $id;

                                /* Header Company  - Level Two  */
                                $out_report .= self::Add_CompanyHeader_Screen($company->name,$id_toggle,$url_img);

                                /* Info company - Users */
                                $out_report .= html_writer::start_tag('div',array('class' => 'course_list','id'=> $id_toggle . '_div'));
                                    $courses = $company->courses;
                                    foreach ($courses as $id_course=>$course) {
                                        $id_toggle_course = $id_toggle . '_'. $id_course;
                                        /* Header Table     */
                                        $out_report .= self::Add_CourseHeader_Screen($course->name,$id_toggle_course,$url_img);
                                        /* Users            */
                                        $out_report .= html_writer::start_tag('div',array('class' => 'user_list','id'=> $id_toggle_course . '_div'));
                                            $out_report .= html_writer::start_tag('table');
                                                /* Header Table     */
                                                $out_report .= self::Add_HeaderTable_LevelThree_Screen();
                                                /* Content Table    */
                                                $out_report .= self::Add_ContentTable_LevelThree_Screen($course,$outcome_report->expiration);
                                            $out_report .= html_writer::end_tag('table');
                                        $out_report .= html_writer::end_tag('div');//user_list
                                    }//for_courses
                                $out_report .= html_writer::end_tag('div');//courses_list
                            }//if_courses
                        }//for_level_three
                    $out_report .= html_writer::end_tag('div');//company_content
                }//if_levelThree
            $out_report .= html_writer::end_div();//outcome_rpt_div

            /* Return To Selection Page */
            $out_report .= html_writer::link($return_url,get_string('outcome_return_to_selection','report_manager'),array('class' => 'link_return'));

            $out_report .= '<hr class="line_rpt_lnk">';

            return $out_report;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Print_OutcomeReport_Screen_LevelThree


    /**
     * @param           $company
     * @param           $toogle
     * @param           $img
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header for the level Zero
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
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header for the level One
     */
    private static function Add_CompanyHeader_LevelOne_Screen($company,$toogle,$img) {
        /* Variables    */
        $header_company     = null;
        $title_company      = null;

        $header_company .= html_writer::start_div('header_outcome_company_rpt_levelOne');
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
     * @param       null $url_levelThree
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the company header
     */
    private static function Add_CompanyHeader_Screen($company,$toogle,$img,$url_levelThree = null) {
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
                if ($url_levelThree) {
                    $header_company .= '<a href="' . $url_levelThree . '">' . '<h5>' . $company . '</h5>' . '</a>';
                }else {
                    $header_company .= '<h5>' . $company . '</h5>';
                }//if_levelThree

            $header_company .= html_writer::end_div('');//header_col_two
        $header_company .= html_writer::end_div('');//header_outcome_company_rpt

        return $header_company;
    }//Add_CompanyHeader_Screen

    /**
     * @param           $course
     * @param           $toogle
     * @param           $img
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the course header
     */
    private static function Add_CourseHeader_Screen($course,$toogle,$img) {
        /* Variables    */
        $header_course     = null;
        $title_company     = null;

        $header_course .= html_writer::start_div('header_outcome_company_rpt_levelCourse');
            /* Col One  */
            $header_course .= html_writer::start_div('header_col_one');
                $header_course .= '<button class="toggle_outcome_company_rpt" type="image" id="' . $toogle . '"><img id="' . $toogle . '_img' . '" src="' . $img . '">' . '</button>';
            $header_course .= html_writer::end_div('');//header_col_one

            /* Col Two  */
            $header_course .= html_writer::start_div('header_col_two');
                $header_course .= '<h5>' . $course . '</h5>';
            $header_course .= html_writer::end_div('');//header_col_two
        $header_course .= html_writer::end_div('');//header_outcome_company_rpt

        return $header_course;
    }//Add_CompanyHeader_Screen

    /**
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header table for the level Two
     */
    private static function Add_HeaderCourseTable_LevelTwo_Screen() {
        /* Variables    */
        $header_table = null;

        $str_course         = get_string('course');
        $str_not_enrol      = get_string('not_start','report_manager');
        $str_not_completed  = get_string('progress','report_manager');
        $str_completed      = get_string('completed','report_manager');
        $str_total          = get_string('count','report_manager');

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

        return $header_table;
    }//Add_HeaderCourseTable_LevelTwo_Screen

    /**
     * @param           $courses_lst
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content for the level Two
     */
    private static function Add_ContentCourseTable_LevelTwo_Screen($courses_lst) {
        /* Variables    */
        $content    = null;

        foreach ($courses_lst as $id=>$course) {
            $content .= html_writer::start_tag('tr');
                /* Empty Col   */
                $content .= html_writer::start_tag('td',array('class' => 'first'));
                $content .= html_writer::end_tag('td');
                /* Course           */
                $content .= html_writer::start_tag('td',array('class' => 'course'));
                    $content .= $course->name;
                $content .= html_writer::end_tag('td');
                /* Not Enrol        */
                $content .= html_writer::start_tag('td',array('class' => 'status'));
                    $content .= count($course->not_enrol);
                $content .= html_writer::end_tag('td');
                /* Not Completed    */
                $content .= html_writer::start_tag('td',array('class' => 'status'));
                    $content .= count($course->not_completed);
                $content .= html_writer::end_tag('td');
                /* Completed        */
                $content .= html_writer::start_tag('td',array('class' => 'status'));
                    $content .= count($course->completed);
                $content .= html_writer::end_tag('td');
                /* Total            */
                $content .= html_writer::start_tag('td',array('class' => 'status'));
                    $content .= count($course->not_enrol) + count($course->not_completed) + count($course->completed);
                $content .= html_writer::end_tag('td');
            $content .= html_writer::end_tag('tr');
        }

        return $content;
    }//Add_ContentCourseTable_LevelTwo_Screen

    /**
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header for the level three
     */
    private static function Add_HeaderTable_LevelThree_Screen() {
        /* Variables    */
        $header_table = null;

        $str_user           = get_string('user');
        $str_state          = get_string('state','local_tracker_manager');
        $str_completion     = get_string('completion_time','local_tracker_manager');
        $str_valid          = get_string('outcome_valid_until','local_tracker_manager');

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

            /* Valid Until   */
            $header_table .= html_writer::start_tag('td',array('class' => 'head_status'));
                $header_table .= $str_valid;
            $header_table .= html_writer::end_tag('td');
        $header_table .= html_writer::end_tag('tr');

        return $header_table;
    }//Add_HeaderTable_LevelThree_Screen

    /**
     * @param           $course_info
     * @param           $expiration
     * @return          string
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content for the level three
     */
    private static function Add_ContentTable_LevelThree_Screen($course_info,$expiration) {
        /* Variables    */
        $content        = null;
        $class          = null;
        $label          = null;
        $completed      = null;
        $not_completed  = null;
        $not_enrol      = null;

        /* Completed    */
        $completed = $course_info->completed;
        if ($completed) {
            foreach ($completed as $user) {

                $ts = strtotime($expiration  . ' month', $user->completed);
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
                    /* User Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'course'));
                        $content .= $user->name;
                    $content .= html_writer::end_tag('td');
                    /* Status Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                        $content .= $label;
                    $content .= html_writer::end_tag('td');

                    /* Completion Col   */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                        $content .= userdate($user->completed,'%d.%m.%Y', 99, false);
                    $content .= html_writer::end_tag('td');

                    /* Valid Until  */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                        $content .= userdate($ts,'%d.%m.%Y', 99, false);
                    $content .= html_writer::end_tag('td');
                $content .= html_writer::end_tag('tr');
            }//for_completed
        }//if_completed

        /* Not Completed - In progress  */
        $not_completed = $course_info->not_completed;
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

                    /* Valid Until  */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                        $content .= '-';
                    $content .= html_writer::end_tag('td');
                $content .= html_writer::end_tag('tr');
            }//for_not_enrol
        }//if_not_completed

        /* Not Enrol    */
        $not_enrol = $course_info->not_enrol;
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

                    /* Valid Until  */
                    $content .= html_writer::start_tag('td',array('class' => 'status'));
                        $content .= '-';
                    $content .= html_writer::end_tag('td');
                $content .= html_writer::end_tag('tr');
            }//for_not_enrol
        }//if_not_enrol

        return $content;
    }//Add_ContentTable_LevelThree_Screen

    /**
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Outcome Report - Level Zero
     */
    private static function Download_OutcomeReport_LevelZero($outcome_report) {
        /* Variables    */
        global $CFG;
        $row                = null;
        $time               = null;
        $fileName           = null;
        $options            = null;
        $completedBefore    = null;
        $levelOne           = null;
        $levelTwo           = null;
        $company            = null;
        $export             = null;
        $myXls              = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $fileName = clean_filename($outcome_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completedBefore    = $options[$outcome_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($fileName);

            /* One Sheet By Level twoo  */
            if ($outcome_report->levelOne) {
                foreach ($outcome_report->levelOne as $levelOne) {
                    foreach ($levelOne->levelTwo as $levelTwo) {
                        $row = 0;
                        // Adding the worksheet
                        $myXls = $export->add_worksheet($levelTwo->name);

                        /* Add Header - Company Outcome Report  - Level One */
                        self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->levelZero,$levelOne,$levelTwo,null,$completedBefore,$myXls,$row);
                        /* Ad Level Two */
                        if ($levelTwo->levelThree) {
                            /* Add Header Table */
                            $row++;
                            self::AddHeader_LevelTwo_TableCourse($myXls,$row);

                            /* Add Content Table    */
                            $row++;
                            foreach ($levelTwo->levelThree as $company) {
                                if ($company->courses) {
                                    self::AddContent_LevelTwo_TableCourse($myXls,$row,$company);

                                    $myXls->merge_cells($row,0,$row,13);
                                    $row++;
                                }//if_courses
                            }//for_each_company
                        }//if_level_three
                    }//for_levelTwo
                }//for_elvel_one
            }else {
                $row = 0;
                // Adding the worksheet
                $myXls = $export->add_worksheet($outcome_report->levelZero);

                /* Add Header - Company Outcome Report  - Level One */
                self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->levelZero,null,null,get_string('no_data', 'report_manager'),$completedBefore,$myXls,$row);
            }//if_levelOne

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport_LevelZero

    /**
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Outcome Report - Level One
     */
    private static function Download_OutcomeReport_LevelOne($outcome_report) {
        /* Variables    */
        global $CFG;
        $time               = null;
        $fileName           = null;
        $options            = null;
        $completedBefore    = null;
        $levelOne           = null;
        $levelTwo           = null;
        $company            = null;
        $row                = null;
        $export             = null;
        $myXls              = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $fileName = clean_filename($outcome_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completedBefore    = $options[$outcome_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($fileName);

            /* One Sheet by Level Two   */
            $levelOne = array_shift($outcome_report->levelOne);
            if ($levelOne->levelTwo) {
                foreach ($levelOne->levelTwo as $levelTwo) {
                    $row = 0;
                    // Adding the worksheet
                    $myXls = $export->add_worksheet($levelTwo->name);

                    /* Add Header - Company Outcome Report  - Level One */
                    self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->levelZero,$levelOne,$levelTwo,null,$completedBefore,$myXls,$row);

                    /* Ad Level Two */
                    if ($levelTwo->levelThree) {
                        /* Add Header Table */
                        $row++;
                        self::AddHeader_LevelTwo_TableCourse($myXls,$row);

                        /* Add Content Table    */
                        $row++;
                        foreach ($levelTwo->levelThree as $company) {
                            if ($company->courses) {
                                self::AddContent_LevelTwo_TableCourse($myXls,$row,$company);

                                $myXls->merge_cells($row,0,$row,13);
                                $row++;
                            }//if_courses
                        }//for_each_company
                    }//if_level_three
                }//for_levelTwo
            }else {
                $row = 0;
                // Adding the worksheet
                $myXls = $export->add_worksheet($levelOne->name);

                /* Add Header - Company Outcome Report  - Level One */
                self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->levelZero,$levelOne,null,get_string('no_data', 'report_manager'),$completedBefore,$myXls,$row);
            }//if_levelTwo


            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport_LevelOne

    /**
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Course Report - Level Two
     */
    private static function Download_OutcomeReport_LevelTwo($outcome_report) {
        /* Variables    */
        global $CFG;
        $time               = null;
        $fileName           = null;
        $options            = null;
        $completedBefore    = null;
        $levelOne           = null;
        $levelTwo           = null;
        $company            = null;
        $export             = null;
        $myXls              = null;
        $row                = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $fileName = clean_filename($outcome_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completedBefore    = $options[$outcome_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($fileName);

            /* Level One   */
            $levelOne = array_shift($outcome_report->levelOne);
            /* Level Two    */
            $levelTwo = array_shift($outcome_report->levelTwo);

            /* One Sheet by Level Two   */
            $row = 0;
            // Adding the worksheet
            $myXls    = $export->add_worksheet($levelTwo->name);


            /* Ad Level Two */
            if ($levelTwo->levelThree) {
                /* Add Header - Company Outcome Report  - Level One */
                self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->levelZero,$levelOne,$levelTwo,null,$completedBefore,$myXls,$row);

                /* Add Header Table */
                $row++;
                self::AddHeader_LevelTwo_TableCourse($myXls,$row);

                /* Add Content Table    */
                $row++;
                foreach ($levelTwo->levelThree as $company) {
                    if ($company->courses) {
                        self::AddContent_LevelTwo_TableCourse($myXls,$row,$company);

                        $myXls->merge_cells($row,0,$row,13);
                        $row++;
                    }//if_courses
                }//for_each_company
            }else {
                /* Add Header - Company Outcome Report  - Level One */
                self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->levelZero,$levelOne,$levelTwo,get_string('no_data', 'report_manager'),$completedBefore,$myXls,$row);
            }//if_level_three

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport_LevelTwo

    /**
     * @param           $outcome_report
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Download Outcome Report - Level Three
     */
    private static function Download_OutcomeReport_LevelThree($outcome_report) {
        /* Variables    */
        global $CFG;
        $time               = null;
        $fileName           = null;
        $options            = null;
        $completedBefore    = null;
        $levelOne           = null;
        $levelTwo           = null;
        $company            = null;
        $row                = null;
        $export             = null;
        $myXls              = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            /* File Name    */
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $fileName = clean_filename($outcome_report->name . '_' . $time . ".xls");

            /* Get Expiration Period            */
            $options            = CompetenceManager::GetCompletedList();
            $completedBefore    = $options[$outcome_report->completed_before];

            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($fileName);

            /* Level One   */
            $levelOne = array_shift($outcome_report->levelOne);
            /* Level Two    */
            $levelTwo = array_shift($outcome_report->levelTwo);

            /* Ad Level Two */
            if ($outcome_report->levelThree) {
                foreach ($outcome_report->levelThree as $company) {
                    /* One Sheet by Level Three   */
                    $row = 0;
                    // Adding the worksheet
                    $myXls    = $export->add_worksheet($company->name);

                    /* Add Header - Company Outcome Report  - Level One */
                    self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->levelZero,$levelOne,$levelTwo,$company->name,$completedBefore,$myXls,$row);

                    /* Add Header Table     */
                    $row++;
                    self::AddHeader_LevelThree_TableCourse($myXls,$row);
                    ///* Add Content Table    */
                    $row++;
                    self::AddContent_LevelThree_TableCourse($myXls,$row,$company,$outcome_report->expiration);

                    $myXls->merge_cells($row,0,$row,16);
                }//for_each_company
            }else {
                /* One Sheet by Level Three   */
                $row = 0;
                // Adding the worksheet
                $myXls    = $export->add_worksheet($levelTwo->name);

                /* Add Header - Company Outcome Report  - Level One */
                self::AddHeader_CompanySheet($outcome_report->name,$outcome_report->description,$outcome_report->job_roles,$outcome_report->levelZero,$levelOne,$levelTwo,get_string('no_data', 'report_manager'),$completedBefore,$myXls,$row);
            }//if_level_three

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_OutcomeReport_LevelThree

    /**
     * @param           $out_name
     * @param           $out_desc
     * @param           $job_roles
     * @param           $levelZero
     * @param       null $levelOne
     * @param       null $levelTwo
     * @param       null $levelThree
     * @param           $completed_before
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Company Header
     */
    private static function AddHeader_CompanySheet($out_name,$out_desc,$job_roles,$levelZero,$levelOne=null,$levelTwo=null,$levelThree=null,$completed_before,&$my_xls,&$row) {
        /* Variables    */
        $col = 0;
        $title_out              = get_string('outcome', 'report_manager')  . ' - ' . $out_name;
        $title_jr               = get_string('job_roles', 'report_manager');
        $str_job_roles          = null;
        $title_expiration       = get_string('expired_next', 'report_manager') . $completed_before;
        $title_level_zero       = get_string('company_structure_level', 'report_manager', 0) . ': ' . $levelZero;
        $title_level_one        = null;
        if ($levelOne) {
            $title_level_one    = get_string('company_structure_level', 'report_manager', 1) . ': ' . $levelOne->name;
        }
        $title_level_two        = null;
        if ($levelTwo) {
            $title_level_two    = get_string('company_structure_level', 'report_manager', 2) . ': ' . $levelTwo->name;
        }//if_level_two

        try {
            /* Outcome Name && Description  */
            $my_xls->write($row, $col, $title_out,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);
            $row++;
            $my_xls->write($row, $col, $out_desc,array('size'=>10, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);

            /* Job Roles    */
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $title_jr,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+10);
            $my_xls->set_row($row,25);
            $row++;
            $str_job_roles = implode(', ',$job_roles);
            $my_xls->write($row, $col, $str_job_roles ,array('size'=>10, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'center'));
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
            if ($levelThree) {
                /* Merge Cells  */
                $row++;
                $my_xls->merge_cells($row,$col,$row,$col+10);
                $row++;
                $my_xls->merge_cells($row,$col,$row,$col+10);

                $row++;
                $col = 0;
                $my_xls->write($row, $col, $levelThree,array('size'=>14, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'center'));
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
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header table for the levels zero, one and two
     */
    private static function AddHeader_LevelTwo_TableCourse(&$my_xls,$row) {
        /* Variables    */
        $str_company        = strtoupper(get_string('company','report_manager'));
        $str_course         = strtoupper(get_string('course'));
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
     * @param           $my_xls
     * @param           $row
     * @param           $company_info
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table for the levels zero, one and two
     */
    private static function AddContent_LevelTwo_TableCourse(&$my_xls,&$row,$company_info) {
        /* Variables    */
        $col    = 0;
        $total  = 0;

        try {
            foreach ($company_info->courses as $id=>$course) {
                /* Company      */
                $my_xls->write($row, $col, $company_info->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,20);

                /* Courses      */
                $col = $col + 6;
                $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,20);

                /* Not Enrol    */
                $col = $col + 6;
                $my_xls->write($row, $col, count($course->not_enrol),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                /* In Progress  */
                $col = $col + 2;
                $my_xls->write($row, $col, count($course->not_completed),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                /* Completed    */
                $col = $col + 2;
                $my_xls->write($row, $col, count($course->completed),array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
                $my_xls->merge_cells($row,$col,$row,$col+1);
                $my_xls->set_row($row,20);

                /* Total        */
                $col = $col + 2;
                $total = count($course->not_enrol) + count($course->not_completed) + count($course->completed);
                $my_xls->write($row, $col, $total,array('size'=>12, 'name'=>'Arial','align'=>'center','v_align'=>'center'));
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
     * @param           $my_xls
     * @param           $row
     * @throws          Exception
     *
     * @creationDate    30/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the table for the level three
     */
    private static function AddHeader_LevelThree_TableCourse(&$my_xls,$row) {
        /* Variables    */
        $str_course         = strtoupper(get_string('course'));
        $str_user           = strtoupper(get_string('user'));
        $str_state          = strtoupper(get_string('state','local_tracker_manager'));
        $str_completion     = strtoupper(get_string('completion_time','local_tracker_manager'));
        $col                = 0;

        try {
            /* Course       */
            $my_xls->write($row, $col, $str_course,array('size'=>12, 'name'=>'Arial','bold'=>'1','color' => '#004b93','bg_color'=>'#efefef','align'=>'left','v_align'=>'center'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* User         */
            $col = $col + 6;
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
     * @param           $expiration
     * @throws          Exception
     *
     * @creationDate    300/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table for the level three
     */
    private static function AddContent_LevelThree_TableCourse(&$my_xls,&$row,$company_info,$expiration) {
        /* Variables    */
        $col        = null;
        $courses    = null;

        try {
            $courses = $company_info->courses;
            if ($courses) {
                foreach ($courses as $course) {
                    /* Completed    */
                    if ($course->completed) {
                        foreach ($course->completed as $id=>$user_info) {
                            $col = 0;
                            $ts = strtotime($expiration  . ' month', $user_info->completed);
                            if ($ts < time()) {
                                $bg_color = '#f2dede';
                                $label = get_string('outcome_course_expired','local_tracker_manager');
                            }else {
                                $bg_color = '#dff0d8';
                                $label = get_string('outcome_course_finished','local_tracker_manager');
                            }

                            /* Course  */
                            $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                            $my_xls->merge_cells($row,$col,$row,$col+5);
                            $my_xls->set_row($row,20);

                            /* User     */
                            $col = $col + 6;
                            $my_xls->write($row, $col, $user_info->name,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'left','v_align'=>'center'));
                            $my_xls->merge_cells($row,$col,$row,$col+5);
                            $my_xls->set_row($row,20);

                            /* State        */
                            $col = $col + 6;
                            $my_xls->write($row, $col, $label,array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                            $my_xls->merge_cells($row,$col,$row,$col+2);
                            $my_xls->set_row($row,20);

                            /* Completion   */
                            $col = $col + 3;
                            $my_xls->write($row, $col, userdate($user_info->completed,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bg_color'=>$bg_color,'align'=>'center','v_align'=>'center'));
                            $my_xls->merge_cells($row,$col,$row,$col+2);
                            $my_xls->set_row($row,20);

                            $row++;
                        }//courses_completed
                    }//if_completed

                    /* In Progress  */
                    if ($course->not_completed) {
                        foreach ($course->not_completed as $user_info) {
                            $col = 0;
                            /* Course  */
                            $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
                            $my_xls->merge_cells($row,$col,$row,$col+5);
                            $my_xls->set_row($row,20);

                            /* User     */
                            $col = $col + 6;
                            $my_xls->write($row, $col, $user_info->name,array('size'=>12, 'name'=>'Arial','align'=>'left','v_align'=>'center'));
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
                        }//courses_not_completed
                    }//if_not_completed

                    /* Not Enrol    */
                    if ($course->not_enrol) {
                        foreach ($course->not_enrol as $user_info) {
                            $col = 0;
                            /* Course  */
                            $my_xls->write($row, $col, $course->name,array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'left','v_align'=>'center'));
                            $my_xls->merge_cells($row,$col,$row,$col+5);
                            $my_xls->set_row($row,20);

                            /* User     */
                            $col = $col + 6;
                            $my_xls->write($row, $col, $user_info->name,array('size'=>12, 'name'=>'Arial','bg_color'=>'#fcf8e3','align'=>'left','v_align'=>'center'));
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

                    $my_xls->merge_cells($row,0,$row,16);
                    $row ++;
                }//for_courses
            }//if_courses
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_LevelThree_TableCourse
}//outcome_report