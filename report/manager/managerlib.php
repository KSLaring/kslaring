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
 * Library for the Report Competence Manager
 *
 */
define('REPORT_MANAGER_COMPANY_CANCEL','rg_cancel');
define('REPORT_MANAGER_COMPANY_FIELD', 'rgcompany');
define('REPORT_MANAGER_ADD_ITEM', 'add_item');
define('REPORT_MANAGER_RENAME_SELECTED', 'rename_selected');
define('REPORT_MANAGER_DELETE_SELECTED', 'delete_selected');
define('REPORT_MANAGER_UNLINK_SELECTED', 'unlink_selected');
define('REPORT_MANAGER_GET_LEVEL', 'get_level');
define('REPORT_MANAGER_GET_UNCONNECTED', 'get_unconnected');
define('REPORT_MANAGER_REMOVE_SELECTED', 'remove_selected');
define('REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL', 'company_structure_level');
define('REPORT_MANAGER_COMPANY_LIST', 'company_list');
define('REPORT_MANAGER_EMPLOYEE_LIST', 'employee_list');
define('REPORT_MANAGER_JOB_ROLE_LIST', 'job_role_list');
define('REPORT_MANAGER_OUTCOME_LIST', 'outcome_list');
define('REPORT_MANAGER_COURSE_LIST', 'course_list');
define('REPORT_MANAGER_USER_LIST', 'user_list');
define('REPORT_MANAGER_COMPLETED_LIST', 'completed_list');
define('REPORT_MANAGER_EXPIRE_NEXT_LIST', 'expire_next_list');
define('REPORT_MANAGER_REPORT_FORMAT_LIST', 'report_format_list');
define('REPORT_MANAGER_COURSE_REPORT_SELECT_DATA', 'course_report_format_list');
define('REPORT_MANAGER_OUTCOME_REPORT_SELECT_DATA', 'outcome_report_format_list');
define('REPORT_MANAGER_IN_PROGRESS', 'in_progress');
define('REPORT_MANAGER_COMPLETED', 'completed');
define('REPORT_MANAGER_COMPLETED_BEFORE', 'completed_before');
define('REPORT_MANAGER_REP_FORMAT_SCREEN', 0);
define('REPORT_MANAGER_REP_FORMAT_PDF', 1);
define('REPORT_MANAGER_REP_FORMAT_PDF_MAIL', 2);
define('REPORT_MANAGER_REP_FORMAT_CSV', 3);
define('REPORT_MANAGER_ERROR', 0);
define('REPORT_MANAGER_SUCCESS', 1);
define('REPORT_MANAGER_ERROR_NO_USER_PROFILE_DATA', 0);

define('REPORT_MANAGER_IMPORT_0',0);
define('REPORT_MANAGER_IMPORT_1',1);
define('REPORT_MANAGER_IMPORT_2',2);
define('REPORT_MANAGER_IMPORT_3',3);

define('CSV_LOAD_ERROR','csv_load_error');
define('CSV_EMPTY_FILE','csv_empty_file');
define('CANNOT_READ_TMP_FILE','cannot_read_tmp_file');
define('CSV_FEW_COLUMNS','csv_few_columns');
define('INVALID_FILE_NAME','invalid_field_name');
define('DUPLICATE_FIELD_NAME','duplicate_field_name');
define('NON_ERROR','non_error');

if (!defined('MAX_BULK_USERS')) {
    define('MAX_BULK_USERS', 2000);
}

class CompetenceManager {
    /********************/
    /* PUBLIC FUNCTIONS */
    /********************/

    /**
     * @param           $tab
     * @param           $site_context
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the Level links to the main page
     */
    public static function GetLevelLink_ReportPage($tab,$site_context) {
        /* Variables    */
        global $OUTPUT;

        /* Create links - It's depend on View permissions */
        $out = '<ul class="unlist report-selection">' . "\n";
        if (has_capability('report/manager:viewlevel0', $site_context)) {
            $out .= self::Get_ZeroLevelLink($tab);
        }else if (has_capability('report/manager:viewlevel1', $site_context)) {
            $out .= self::Get_FirstLevelLink($tab);
        }else if(has_capability('report/manager:viewlevel2', $site_context)) {
            $out .= self::Get_SecondLevelLink($tab);
        }else if (has_capability('report/manager:viewlevel3', $site_context)) {
            $out .= self::Get_ThirdLevelLink($tab);
        }//if_capabitity
        $out .= '</ul>' . "\n";

        /* Draw Links */
        echo $out;
    }//GetLevelLink_ReportPage

    /**
     * @static
     * @param           $user_id
     * @param           $site_context
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get my hierarchy level
     */
    public static function get_MyHierarchyLevel($user_id,$site_context) {
        /* Variables    */
        $my_hierarchy   = null;

        try {
            /* Build my hierarchy   */
            $my_hierarchy               = new stdClass();
            $my_hierarchy->competence   = self::Get_MyCompetence($user_id);
            $my_hierarchy->my_level     = self::Get_MyLevelView($user_id,$site_context);

            return $my_hierarchy;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_MyHierarchyLevel

    /**
     * @param           $my_companies
     * @return          array
     * @throws          Exception
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get companies split by level
     */
    public static function GetMyCompanies_By_Level($my_companies) {
        /* Variables    */
        $levelThree = null;
        $levelTwo   = null;
        $levelOne   = null;
        $levelZero  = null;

        try {
            foreach ($my_companies as $company) {
                $levelZero[$company->levelZero]     = $company->levelZero;
                $levelOne[$company->levelOne]       = $company->levelOne;
                $levelTwo[$company->levelTwo]       = $company->levelTwo;
                $levelThree[$company->levelThree]   = $company->levelThree;
            }

            return array($levelZero,$levelOne,$levelTwo,$levelThree);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetMyCompanies_By_Level

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get completed list
     */
    public static function GetCompletedList() {
        /* Variables    */
        $list = null;

        try {
            $list = array(
                            0 => get_string('numdays', '', 1),
                            1 => get_string('numweeks', '', 1),
                            2 => get_string('numweeks', '', 2),
                            3 => get_string('numweeks', '', 3),
                            4 => get_string('nummonths', '', 1),
                            5 => get_string('nummonths', '', 2),
                            6 => get_string('nummonths', '', 3),
                            7 => get_string('nummonths', '', 4),
                            8 => get_string('nummonths', '', 5),
                            9 => get_string('nummonths', '', 6),
                            10 => get_string('numyears', '', 1),
                            11 => get_string('numyears', '', 2)
                        );

            return $list;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompletedList

    /**
     * @param           $company
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the company is public or private
     */
    public static function IsPublic($company) {
        /* Variables    */
        global $DB;

        try {
            /* Get Public Field */
            $rdo = $DB->get_record('report_gen_companydata',array('id' => $company),'public');
            if ($rdo->public) {
                return true;
            }else {
                return false;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsPublic

    /**
     * @static
     * @param           $jr_lst
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the Job Roles List
     */
    public static function Get_JobRolesList($jr_lst = null) {
        /* Variables    */
        global $DB;
        $job_roles_lst = array();

        try {
            /* SQL Instruction  */
            $sql = " SELECT     DISTINCT id,
                                         name,
                                         industrycode
                     FROM       {report_gen_jobrole} ";

            /* Search Criteria  */
            if ($jr_lst) {
                $sql .= " WHERE id IN ($jr_lst) ";
            }//if_jr_lst

            /* ORDER    */
            $sql .= " ORDER BY   industrycode, name ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $job_role) {
                    $job_roles_lst[$job_role->id] = $job_role->industrycode . ' - '. $job_role->name;
                }//for_rdo_job_role
            }//if_rdo
            return $job_roles_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRolesList


    /**
     * @param           $options
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the job roles that are generics
     */
    public static function GetJobRoles_Generics(&$options) {
        /* Variables    */
        global $DB;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT      jr.id,
                                              jr.name,
                                              jr.industrycode
                     FROM		{report_gen_jobrole}				jr
                        JOIN	{report_gen_jobrole_relation}		jr_rel	ON 	jr_rel.jobroleid = jr.id
                                                                            AND jr_rel.levelzero IS NULL
                     ORDER BY jr.industrycode, jr.name ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $options[$instance->id] = $instance->industrycode . ' - ' . $instance->name;
                }//for_each
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetJobRoles_Generics

    /**
     * @param           $options
     * @param           $level
     * @param           $levelZero
     * @param      null $levelOne
     * @param      null $levelTwo
     * @param      null $levelThree
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the job roles connected with the levels
     */
    public static function GetJobRoles_Hierarchy(&$options,$level,$levelZero,$levelOne=null,$levelTwo=null, $levelThree=null) {
        /* Variables    */
        global $DB;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT      jr.id,
                                              jr.name,
                                              jr.industrycode
                     FROM		{report_gen_jobrole}				jr
                        JOIN	{report_gen_jobrole_relation}		jr_rel	ON 	jr_rel.jobroleid = jr.id ";

            switch ($level) {
                case 0:
                    $sql .= "  AND  jr_rel.levelzero    IN ($levelZero) ";

                    break;
                case 1:
                    $sql .= "   AND  jr_rel.levelzero    IN ($levelZero)
                                AND  jr_rel.levelone     IN ($levelOne)
                            ";

                    break;
                case 2:
                    $sql .= "  AND  jr_rel.levelzero    IN ($levelZero)
                               AND  jr_rel.levelone     IN ($levelOne)
                               AND  jr_rel.leveltwo     IN ($levelTwo)
                            ";
                    break;
                case 3:
                    $sql .= "  AND (
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IN ($levelOne)
                                     AND
                                     jr_rel.leveltwo     IN ($levelTwo)
                                     AND
                                     jr_rel.levelthree   IN ($levelThree)
                                    )
                                    OR
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IN ($levelOne)
                                     AND
                                     jr_rel.leveltwo     IN ($levelTwo)
                                     AND
                                     jr_rel.levelthree   IS NULL
                                    )
                                    OR
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IN ($levelOne)
                                     AND
                                     jr_rel.leveltwo     IS NULL
                                     AND
                                     jr_rel.levelthree   IS NULL
                                    )
                                    OR
                                    (jr_rel.levelzero    IN ($levelZero)
                                     AND
                                     jr_rel.levelone     IS NULL
                                     AND
                                     jr_rel.leveltwo     IS NULL
                                     AND
                                     jr_rel.levelthree   IS NULL
                                    )
                               ) ";
                    break;
            }//switch_level

            $sql .= " ORDER BY jr.industrycode, jr.name ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $options[$instance->id] = $instance->industrycode . ' - ' . $instance->name;
                }//for_each
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetJobRoles_Hierarchy

    /**
     * @param               $level
     * @param       int     $parent_id
     * @param       null    $companies_in
     * @return              array
     * @throws              Exception
     *
     * @creationDate        26/03/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get a list of all the companies are connected a specific level.
     */
    public static function GetCompanies_LevelList($level, $parent_id = 0,$companies_in = null) {
        /* Variables */
        global $DB;
        $levels = array();

        try {
            /* List Companies   */
            $levels[0] = get_string('select_level_list','report_manager');

            /* Research Criteria */
            $params = array();
            $params['level']    = $level;

            /* SQL Instruction */
            $sql = " SELECT     DISTINCT  rcd.id,
                                          rcd.name,
                                          rcd.industrycode
                     FROM       {report_gen_companydata} rcd ";

            /* Parents  */
            if ($parent_id) {
                $sql .= " JOIN  {report_gen_company_relation} rcr   ON    rcr.companyid = rcd.id
                                                                    AND   rcr.parentid  IN ($parent_id) ";
            }//if_level

            /* Conditions   */
            $sql .= " WHERE     rcd.hierarchylevel = :level ";
            /* Companies In */
            if ($companies_in) {
                $sql .= " AND rcd.id IN ($companies_in) ";
            }//if_companies_in

            /* Order    */
            $sql .= " ORDER BY  rcd.industrycode, rcd.name ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $field) {
                    $levels[$field->id] = $field->industrycode . ' - '. $field->name;
                }//foreach
            }//if_rdo

            return $levels;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_Companies_LevelList


    /**
     * @param           $company
     * @return          null
     * @throws          Exception
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company name
     */
    public static function GetCompany_Name($company) {
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['company'] = $company;

            /* SQL Instruction   */
            $sql = " SELECT     rgc.name
                     FROM       {report_gen_companydata} rgc
                     WHERE      rgc.id = :company";


            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompany_Name

    public static function GetUsers_MyCompanies($my_companies,$user_id) {
        /* Variables    */
        global $DB;
        $my_users = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT     DISTINCT	u.id
                     FROM		{user}						u
                        JOIN	{user_info_competence_data}	uicd	ON 	uicd.userid = u.id
                                                                    AND uicd.companyid  IN ($my_companies)
                     WHERE		u.id != :user
                        AND		u.deleted = 0 ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $my_users = implode(',',array_keys($rdo));
            }//if_rdo

            return $my_users;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetUsers_MyCompanies

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/


    /**
     * @param           $tab
     * @return          string
     *
     * @creationDate    26/03/2015
     * @author          eFktor      (fbv)
     *
     * Description
     * Add links to level Zero
     */
    private static function Get_ZeroLevelLink($tab) {
        /* Variables    */
        $out        = null;
        $url_zero   = new moodle_url('/report/manager/' . $tab .'/' . $tab .'_level.php',array('rpt'=>0));

        $out  = '<li>' . "\n";
        $out .= '<a href="'.$url_zero .'">'. get_string('level_report','report_manager',0) .'</a>';
        $out .= '</li>' . "\n";
        $out .= self::Get_FirstLevelLink($tab);

        return $out;
    }//Get_ZeroLevelLink

    /**
     * @param           $tab
     * @return          string
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the links to level one
     */
    private static function Get_FirstLevelLink($tab) {
        /* Variables    */
        $out            = null;
        $url_first      = new moodle_url('/report/manager/' . $tab .'/' . $tab .'_level.php',array('rpt'=>1));

        $out  = '<li>' . "\n";
        $out .= '<a href="'.$url_first .'">'. get_string('level_report','report_manager',1) .'</a>';
        $out .= '</li>' . "\n";
        $out .= self::Get_SecondLevelLink($tab);

        return $out;
    }//Get_FirstLevelLink

    /**
     * @param           $tab
     * @return          string
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add links to level two
     */
    private static function Get_SecondLevelLink($tab) {
        /* Variables    */
        $out                = null;
        $url_second         = new moodle_url('/report/manager/' . $tab .'/' . $tab .'_level.php',array('rpt'=>2));

        $out  = '<li>' . "\n";
        $out .= '<a href="'.$url_second .'">'. get_string('level_report','report_manager',2) .'</a>';
        $out .= '</li>' . "\n";
        $out .= self::Get_ThirdLevelLink($tab);

        return $out;
    }//Get_SecondLevelLink

    /**
     * @param           $tab
     * @return          string
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add link to the third level
     */
    private static function Get_ThirdLevelLink($tab) {
        /* Variables    */
        $out            = null;
        $url_third      = new moodle_url('/report/manager/' . $tab .'/' . $tab .'_level.php',array('rpt'=>3));

        $out = '<li class="last">' . "\n";
        $out .= '<a href="'.$url_third .'">'. get_string('level_report','report_manager',3) .'</a>';
        $out .= '</li>' . "\n";

        return $out;
    }//Get_ThirdLevelLink

    /**
     * @static
     * @param           $user_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    13/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get competence data connected with the user
     */
    private static function Get_MyCompetence($user_id) {
        /* Variables    */
        global $DB;
        $my_competence      = array();
        $info_hierarchy     = null;


        try {
            /* Search Criteria  */
            $params = array();
            $params['user_id']  = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		uicd.companyid 		as 'levelthree',
                                level_two.parentid 	as 'leveltwo',
                                level_one.parentid 	as 'levelone',
                                level_zero.parentid as 'levelzero',
                                uicd.jobroles
                     FROM		{user_info_competence_data} 	uicd
                        JOIN	(
                                    SELECT		cr.companyid,
                                                cr.parentid
                                    FROM		{report_gen_companydata}			co
                                        JOIN	{report_gen_company_relation}		cr	ON cr.parentid = co.id
                                    WHERE		co.hierarchylevel = 2
                                ) level_two ON level_two.companyid = uicd.companyid
                        JOIN	(
                                    SELECT		cr.companyid,
                                                cr.parentid
                                    FROM		{report_gen_companydata}			co
                                        JOIN	{report_gen_company_relation}		cr	ON cr.parentid = co.id
                                    WHERE		co.hierarchylevel = 1
                                ) level_one	ON level_one.companyid = level_two.parentid
                        JOIN	(
                                    SELECT		cr.companyid,
                                                cr.parentid
                                    FROM		{report_gen_companydata}			co
                                        JOIN	{report_gen_company_relation}		cr	ON cr.parentid = co.id
                                    WHERE		co.hierarchylevel = 0

                                ) level_zero ON level_zero.companyid = level_one.parentid
                     WHERE		uicd.userid = :user_id ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Hierarchy Info   */
                    $info_hierarchy = new stdClass();
                    $info_hierarchy->levelThree     = $instance->levelthree;
                    $info_hierarchy->levelTwo       = $instance->leveltwo;
                    $info_hierarchy->levelOne       = $instance->levelone;
                    $info_hierarchy->levelZero      = $instance->levelzero;
                    /* Job Roles        */
                    $info_hierarchy->roles          = $instance->jobroles;

                    /* Add  */
                    $my_competence[$instance->levelthree] = $info_hierarchy;
                }//for_companies
            }//if_rdo

            return $my_competence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyCompetence

    /**
     * @static
     * @param           $user_id
     * @param           $site_context
     * @return          int
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbV)
     *
     * Description
     * Get the report/manager view permissions to see the reports
     */
    private static function Get_MyLevelView($user_id,$site_context) {
        /* Variables    */
        $my_level = 4;

        /* Level Zero   */
        if (has_capability('report/manager:viewlevel0', $site_context,$user_id) &&
            has_capability('report/manager:viewlevel1', $site_context,$user_id) &&
            has_capability('report/manager:viewlevel2', $site_context,$user_id) &&
            has_capability('report/manager:viewlevel3', $site_context,$user_id) &&
            has_capability('report/manager:viewlevel4', $site_context,$user_id)) {
            $my_level = 0;
        }else {
            /* Level One    */
            if (!has_capability('report/manager:viewlevel0', $site_context,$user_id) &&
                has_capability('report/manager:viewlevel1', $site_context,$user_id) &&
                has_capability('report/manager:viewlevel2', $site_context,$user_id) &&
                has_capability('report/manager:viewlevel3', $site_context,$user_id) &&
                has_capability('report/manager:viewlevel4', $site_context,$user_id)) {
                $my_level = 1;
            }else {
                /* Level Two    */
                if (!has_capability('report/manager:viewlevel0', $site_context,$user_id) &&
                    !has_capability('report/manager:viewlevel1', $site_context,$user_id) &&
                    has_capability('report/manager:viewlevel2', $site_context,$user_id) &&
                    has_capability('report/manager:viewlevel3', $site_context,$user_id) &&
                    has_capability('report/manager:viewlevel4', $site_context,$user_id)) {
                    $my_level = 2;
                }else {
                    /* Level Third  */
                    if (!has_capability('report/manager:viewlevel0', $site_context,$user_id) &&
                        !has_capability('report/manager:viewlevel1', $site_context,$user_id) &&
                        !has_capability('report/manager:viewlevel2', $site_context,$user_id) &&
                        has_capability('report/manager:viewlevel3', $site_context,$user_id) &&
                        has_capability('report/manager:viewlevel4', $site_context,$user_id)) {
                        $my_level = 3;
                    }else {
                        /* Level Four   */
                        if (!has_capability('report/manager:viewlevel0', $site_context,$user_id) &&
                            !has_capability('report/manager:viewlevel1', $site_context,$user_id) &&
                            !has_capability('report/manager:viewlevel2', $site_context,$user_id) &&
                            !has_capability('report/manager:viewlevel3', $site_context,$user_id) &&
                            has_capability('report/manager:viewlevel4', $site_context,$user_id)) {
                            $my_level = 4;
                        }//if_level_four
                    }//if_level_third
                }//if_level_two
            }//if_level_one
        }//if_else_level_zero

        return $my_level;
    }//Get_MyLevelView
}//CompetenceManager

