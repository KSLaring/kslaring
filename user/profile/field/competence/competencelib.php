<?php
/**
 * Extra Profile Field Competence - Library
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/competence
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/01/2015
 * @author          eFaktor     (fbv)
 *
 */

class Competence {
    /*************/
    /*  PUBLIC   */
    /*************/

    /**
     * @param           $selector
     * @param           $jrSelector
     * @param           $userId
     *
     * @throws          Exception
     *
     * @creationDate    28/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize Company structure selectors
     */
    public static function Init_OrganizationStructure($selector,$jrSelector,$userId) {
        /* Variables    */
        global $PAGE;
        $options    = null;
        $hash       = null;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;
        $sp         = null;

        try {
            /* Initialise variables */
            $name       = 'level_structure';
            $path       = '/user/profile/field/competence/js/competence.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                'fullpath'    => $path,
                'requires'    => $requires,
                'strings'     => $strings
            );

            $PAGE->requires->js_init_call('M.core_user.init_organization',
                                          array($selector,$jrSelector,$userId),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_OrganizationStructure_CourseReport

    /**
     * @param           $company
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    03/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if it is a public or private company
     */
    public static function IsPublic($company) {
        /* Variables    */
        global $DB;
        $rdo    = null;

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
     * @param           $my_companies
     * @return          array
     * @throws          Exception
     *
     * @creationDate    29/01/2015
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
            }//for_each_company

            return array($levelZero,$levelOne,$levelTwo,$levelThree);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetMyCompanies_By_Level

    /**
     * @param           $companies
     * @return          null
     * @throws          Exception
     *
     * @creationDate    29/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company name
     */
    public  static function Get_CompanyName($companies) {
        /* Variables    */
        global $DB;
        $companiesName  = array();
        $sql            = null;
        $rdo            = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		co.id,
                                co.name
                     FROM		{report_gen_companydata} co
                     WHERE		co.id IN ($companies) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $companiesName[$instance->id] = $instance->name;
                }//for_instance
            }//if_rdo

            return $companiesName;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompanyName

    /**
     * @param           $user_id
     * @param      null $competence_data
     * @param      null $competence
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the competence connected to the user.
     *          - Competence Data
     *                  --> data.                 Id Info Competence Data
     *                  --> competence.         Id Info Competence
     *                  --> levelThree
     *                  --> levelTwo
     *                  --> levelOne
     *                  --> levelZero
     *                  --> path
     *                  --> roles.      Array.
     *                                  [id]    --> Job Role Name.
     *
     * @updateDate      21/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add information about if it is manager and/or reporter
     */
    public static function Get_CompetenceData($user_id,$competence_data=null,$competence=null) {
        /* Variables    */
        global $DB;
        $my_competence  = array();
        $info_hierarchy = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user']     = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		uicd.id,
                                uicd.competenceid,
                                co_zero.id 	as 'levelzero',
                                co_one.id 	as 'levelone',
                                co_two.id 	as 'leveltwo',
                                uicd.companyid 		                    as 'levelthree',
                                IF(uicd.jobroles,uicd.jobroles,0) 		as 'jobroles',
                                uicd.editable
                     FROM		{user_info_competence_data} 	uicd
                        -- LEVEL TWO
                        JOIN	{report_gen_company_relation}   cr_two	ON 	cr_two.companyid 		= uicd.companyid
                        JOIN	{report_gen_companydata}		co_two	ON 	co_two.id 				= cr_two.parentid
                                                                        AND co_two.hierarchylevel 	= 2
                        -- LEVEL ONE
                        JOIN	{report_gen_company_relation}   cr_one	ON 	cr_one.companyid 		= cr_two.parentid
                        JOIN	{report_gen_companydata}		co_one	ON 	co_one.id 				= cr_one.parentid
                                                                        AND co_one.hierarchylevel 	= 1
                        -- LEVEL ZERO
                        JOIN	{report_gen_company_relation}   cr_zero	ON 	cr_zero.companyid 		= cr_one.parentid
                        JOIN	{report_gen_companydata}		co_zero	ON 	co_zero.id 				= cr_zero.parentid
                                                                        AND co_zero.hierarchylevel 	= 0
                     WHERE		uicd.userid = :user ";

            if ($competence_data && $competence) {
                $params['competence_data']  = $competence_data;
                $params['competence']       = $competence;
                $sql .= " AND uicd.id           = :competence_data
                          AND uicd.competenceid = :competence ";
            }//if_competence_data_competence

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Hierarchy Info   */
                    $info_hierarchy = new stdClass();
                    $info_hierarchy->data           = $instance->id;
                    $info_hierarchy->competence     = $instance->competenceid;
                    $info_hierarchy->levelThree     = $instance->levelthree;
                    $info_hierarchy->levelTwo       = $instance->leveltwo;
                    $info_hierarchy->levelOne       = $instance->levelone;
                    $info_hierarchy->levelZero      = $instance->levelzero;
                    $info_hierarchy->editable       = $instance->editable;
                    $info_hierarchy->manager        = self::IsManager($user_id,$info_hierarchy);
                    /* Reporter */
                    if ($info_hierarchy->manager) {
                        $info_hierarchy->reporter   = 1;
                    }else {
                        $info_hierarchy->reporter   = self::IsReporter($user_id,$info_hierarchy);
                    }//if_manager

                    /* Hierarchy Path   */
                    $info_hierarchy->path           = self::GetHierarchyPath($info_hierarchy);
                    /* Job Roles        */
                    $info_hierarchy->roles          = self::GetJobRoles($instance->jobroles);

                    /* Add  */
                    $my_competence[$instance->id] = $info_hierarchy;
                }//instance
            }//if_rdo

            return $my_competence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompetenceData

    /**
     * @param           $userId
     * @param           $hierarchy
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    21/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user is manager or not
     */
    private static function IsManager($userId,$hierarchy) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;
        $zero   = null;
        $one    = null;
        $two    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user']     = $userId;
            $params['three']    = $hierarchy->levelThree;
            $zero               = $hierarchy->levelZero;
            $one                = $hierarchy->levelOne;
            $two                = $hierarchy->levelTwo;

            /* SQL Instruction */
            $sql = " SELECT	ma.id
                     FROM	{report_gen_company_manager}	ma
                     WHERE	ma.managerid = :user
                            AND
                            (
                             (ma.hierarchylevel = 0	AND	ma.levelzero = '". $zero . "' AND ma.levelone IS NULL AND ma.leveltwo IS NULL AND ma.levelthree IS NULL)
                             OR
                             (ma.hierarchylevel = 1	AND	ma.levelzero = '". $zero . "' AND ma.levelone = '". $one . "'  AND ma.leveltwo IS NULL AND ma.levelthree IS NULL)
                             OR
                             (ma.hierarchylevel = 2	AND	ma.levelzero = '". $zero . "' AND ma.levelone = '". $one . "'  AND ma.leveltwo = '". $two . "'  AND ma.levelthree IS NULL)
                             OR
                             (ma.hierarchylevel = 3	AND	ma.levelzero = '". $zero . "' AND ma.levelone = '". $one . "'  AND ma.leveltwo = '". $two . "'  AND ma.levelthree = :three)
                            ) ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsManager

    /**
     * @param           $userId
     * @param           $hierarchy
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    21/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user is reporter or not
     */
    private static function IsReporter($userId,$hierarchy) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $params = null;
        $zero   = null;
        $one    = null;
        $two    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user']     = $userId;
            $params['three']    = $userId;
            $zero               = $hierarchy->levelZero;
            $one                = $hierarchy->levelOne;
            $two                = $hierarchy->levelTwo;

            /* SQL Instruction */
            $sql = " SELECT	re.id
                     FROM	{report_gen_company_reporter}	re
                     WHERE	re.reporterid = :user
                            AND
                            (
                             (re.hierarchylevel = 0	AND	re.levelzero = '". $zero . "' AND re.levelone IS NULL AND re.leveltwo IS NULL AND re.levelthree IS NULL)
                             OR
                             (re.hierarchylevel = 1	AND	re.levelzero = '". $zero . "' AND re.levelone = '". $one . "'  AND re.leveltwo IS NULL AND re.levelthree IS NULL)
                             OR
                             (re.hierarchylevel = 2	AND	re.levelzero = '". $zero . "' AND re.levelone = '". $one . "'  AND re.leveltwo = '". $two . "'  AND re.levelthree IS NULL)
                             OR
                             (re.hierarchylevel = 3	AND	re.levelzero = '". $zero . "' AND re.levelone = '". $one . "'  AND re.leveltwo = '". $two . "'  AND re.levelthree = :three)
                            ) ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsReporter

    /**
     * @param           $hierarchy
     * @return          string
     * @throws          Exception
     *
     * @creationDate    29/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the hierarchy path
     */
    private static function GetHierarchyPath($hierarchy) {
        /* Variables    */
        $hierarchyPath      = null;
        $companies_name     = null;
        $levelZero          = null;
        $levelOne           = null;
        $levelTwo           = null;

        try {
            /* Get Companies Name   */
            $companies = $hierarchy->levelThree . ',' . $hierarchy->levelTwo . ',' . $hierarchy->levelOne . ',' . $hierarchy->levelZero;

            $companies_name = self::Get_CompanyName($companies);

            $hierarchyPath   = $companies_name[$hierarchy->levelZero]  . '/' .
                               $companies_name[$hierarchy->levelOne]   . '/' .
                               $companies_name[$hierarchy->levelTwo]   . '/' .
                               $companies_name[$hierarchy->levelThree];

            return $hierarchyPath;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetHierarchyPath

    /**
     * @param           $level
     * @param           int $parent_id
     * @param           $my_companies
     * @return          array
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the companies connected with a specific level and parent
     * ?? Don't show your companies
     */
    public static function GetCompanies_Level($level,$parent_id = 0,$my_companies = null) {
        /* Variables    */
        global $DB;
        $companies  = array();
        $params     = null;
        $sql        = null;
        $rdo        = null;

        try {
            /* Research Criteria */
            $params = array();
            $params['level']    = $level;

            /* SQL Instruction */
            $sql = " SELECT   DISTINCT rcd.id,
                                       rcd.name,
                                       rcd.industrycode
                     FROM     {report_gen_companydata} rcd ";
            /* Join */
            if ($parent_id) {
                $sql .= " JOIN  {report_gen_company_relation} rcr   ON    rcr.companyid = rcd.id
                                                                    AND   rcr.parentid  IN ($parent_id) ";
            }//if_level

            /* Add Condition    */
            $sql .= " WHERE rcd.hierarchylevel = :level ";
            /* Don't display the companies just added in the profile    */
            if ($my_companies) {
                $sql .= " AND rcd.id NOT IN ($my_companies) ";
            }

            /* Add Order    */
            $sql .= " ORDER BY rcd.industrycode, rcd.name ASC ";

            /* Execute  */
            $companies[0] = get_string('select_level_list','report_manager');
            if ($rdo = $DB->get_records_sql($sql,$params)) {
                foreach ($rdo as $instance) {
                    $companies[$instance->id] = $instance->industrycode . ' - '. $instance->name;
                }//foreach
            }//if_rdo

            return $companies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompanies_Level

    /**
     * @param           $user_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the companies connected with the user
     */
    public static function Get_MyCompanies($user_id) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            $params = array();
            $params['user']     = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		GROUP_CONCAT(DISTINCT uicd.companyid ORDER BY uicd.companyid SEPARATOR ',') as 'companies'
                     FROM		{user_info_competence_data}	uicd
                     WHERE		uicd.userid = :user ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->companies;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyCompanies

    /**
     * @param           $options
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the list of generics job roles
     */
    public static function GetJobRoles_Generics(&$options) {
        /* Variables    */
        global $DB;
        $sql = null;
        $rdo = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT  jr.id,
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
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     * @param           $jr_lst
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the job roles connected with the levels
     */
    public static function GetJobRoles_Hierarchy(&$options,$levelZero,$levelOne,$levelTwo, $levelThree,$jr_lst = null) {
        /* Variables    */
        global $DB;
        $sql = null;
        $rdo = null;

        try {
            /* Add Connected with the level */
            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT  jr.id,
                                          jr.name,
                                          jr.industrycode
                     FROM		{report_gen_jobrole}				jr
                        JOIN	{report_gen_jobrole_relation}		jr_rel	ON 	jr_rel.jobroleid = jr.id
                                                                            AND (
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
            if ($jr_lst) {
                $sql .= " WHERE jr.id IN ($jr_lst) ";
            }//if_jr_lst
            $sql .= " ORDER BY jr.industrycode, jr.name ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    if ($jr_lst) {
                        $options[$instance->id] = $instance->name;
                    }else {
                        $options[$instance->id] = $instance->industrycode . ' - ' . $instance->name;
                    }//if_else
                }//for_each
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetJobRoles_Hierarchy

    /**
     * @param           $data
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    29/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the new info competence to the user profile
     *
     * @updateDate      17/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Job role not compulsory
     */
    public static function AddCompetence($data) {
        /* Variables    */
        global $DB;
        $time                   = time();
        $infoCompetence         = null;
        $infoCompetenceData     = null;
        $infoData               = null;
        $myRoles                = null;

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* Check if it exists a competence info instance for the user */
            $infoCompetence = $DB->get_record('user_info_competence',array('userid' => $data->id));
            if ($infoCompetence) {
                $infoCompetence->timemodified  = $time;

                /* Update   */
                $DB->update_record('user_info_competence',$infoCompetence);
            }else {
                /* First    --> Create Instance user_info_competence    */
                $infoCompetence = new stdClass();
                $infoCompetence->userid        = $data->id;
                $infoCompetence->timemodified  = $time;
                /* Execute  */
                $infoCompetence->id = $DB->insert_record('user_info_competence',$infoCompetence);
            }//if_info_competence

            /* Second   --> User Info Competence Data   */
            $infoCompetenceData = new stdClass();
            $infoCompetenceData->competenceid       = $infoCompetence->id;
            $infoCompetenceData->userid             = $infoCompetence->userid;
            $infoCompetenceData->companyid          = $data->level_3;
            $infoCompetenceData->editable           = 1;
            $infoCompetenceData->level              = 3;
            /* Job Roles */
            if (isset($data->job_roles) && $data->job_roles) {
                $infoCompetenceData->jobroles         = implode(',',$data->job_roles);
            }//if_jobroles
            $infoCompetenceData->timemodified     = $time;
            /* Execute  */
            $DB->insert_record('user_info_competence_data',$infoCompetenceData);

            /* Third    --> User Info Data              */
            /* Get the fieldid of competence profile    */
            $field = $DB->get_record('user_info_field',array('datatype' => 'competence'),'id');
            $infoData = $DB->get_record('user_info_data',array('fieldid' => $field->id,'userid' => $infoCompetence->userid));
            if (!$infoData) {
                $infoData = new stdClass();
                $infoData->userid  = $data->id;
                $infoData->fieldid = $field->id;
                $infoData->data    = $infoCompetence->id;
                /* Execute  */
                $DB->insert_record('user_info_data',$infoData);
            }//create_new_entrance

            /* Commit   */
            $trans->allow_commit();

            
            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//AddCompetence

    /**
     * @param           $data
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Edit the competence
     *
     * @updateDate      17/09/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Job Role Not Compulsory
     */
    public static function EditCompetence($data) {
        /* Variables    */
        global $DB;
        $infoCompetenceData = null;

        try {
            /* Info Data    */
            $infoCompetenceData = new stdClass();
            $infoCompetenceData->id           = $data->icd;
            $infoCompetenceData->competenceid = $data->ic;
            $infoCompetenceData->userid       = $data->id;
            /* Job roles    */
            if ($data->job_roles) {
                $infoCompetenceData->jobroles   = implode(',',$data->job_roles);
                if (!$infoCompetenceData->jobroles) {
                    $infoCompetenceData->jobroles   = null;
                }
            }else {
                $infoCompetenceData->jobroles   = null;
            }

            $infoCompetenceData->timemodified = time();

            /* Update       */
            $DB->update_record('user_info_competence_data',$infoCompetenceData);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//EditCompetence


    /**
     * @param           $userId
     * @param           $competenceData
     * @param           $competence
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete the competence from the user profile.
     */
    public static function DeleteCompetence($userId,$competenceData,$competence) {
        /* Variables    */
        global $DB;
        $rdo            = null;
        $field          = null;
        $infoData       = null;

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            $DB->delete_records('user_info_competence_data',array('id' => $competenceData, 'competenceid' => $competence, 'userid' => $userId));
            /* Check if Delete user_info_competence / user_info_data    */
            $rdo = $DB->get_records('user_info_competence_data',array('competenceid' => $competence, 'userid' => $userId));
            if (!$rdo) {
                /* Delete User Info Competence / User Info Data */
                $DB->delete_records('user_info_competence',array('id' => $competence, 'userid' => $userId));
                /* Get ID of User Info Data */
                $field      = $DB->get_record('user_info_field',array('datatype' => 'competence'),'id');
                $infoData   = $DB->get_record('user_info_data',array('fieldid' => $field->id,'userid' => $userId));
                $DB->delete_records('user_info_data',array('id' => $infoData->id,'fieldid' => $field->id,'userid' => $userId));
            }//if_!rdo

            /* Commit   */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_Catch
    }//DeleteCompetence

    /**
     * @param           $myCompetence
     * @param           $userId
     * @return          string
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the table with the competence data connected with the user
     *          - Competence Data
     *                  --> data.               Id Info Competence Data
     *                  --> competence.         Id Info Competence
     *                  --> levelThree
     *                  --> levelTwo
     *                  --> levelOne
     *                  --> levelZero
     *                  --> path
     *                  --> roles.      Array.
     *                                  [id]    --> Job Role Name.
     *
     * @updateDate      23/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Synchronize with Force Profile Plugin
     */
    public static function Get_CompetenceTable($myCompetence,$userId) {
        /* Variables    */
        global          $SESSION;
        $out            = '';

        $return_url     = new moodle_url('/user/profile.php',array('id' =>$userId));
        $url_add        = new moodle_url('/user/profile/field/competence/actions/add_competence.php',array('id' =>$userId));

        try {
            /* Synchronize with Force Profile Plugin    */
            if (isset($SESSION->force_profile) && ($SESSION->force_profile)) {
                $return_url = new moodle_url('/local/force_profile/confirm_profile.php',array('id'=>$userId));
            }//force_profile

            /* Title    */
            $out .= html_writer::start_tag('div');
                $out .= '<h3>' . get_string('pluginname','profilefield_competence'). '</h3>';
                $out .= '<h5>'. get_string('profile_desc','profilefield_competence') . '</h5>';
            $out .= html_writer::end_tag('div');
            $out .= '</br>';

            /* Add the Actions Link */
            $out .= html_writer::start_tag('div',array('class' => 'btn_actions'));
                /* Add New Competence   */
                $out .= '<a href="' . $url_add . '" >' . '<h6>' . get_string('lnk_add','profilefield_competence')  . '</h6>' .'</a>';
            $out .= html_writer::end_tag('div'); //btn_actions

            /* Get Info Competence to display      */
            /* HIERARCHY LEVEL - HEADER TABLE   */
            $out .= self::AddHeader_CompetenceTable();
            if ($myCompetence) {
                $out .= self::AddContent_CompetenceTable($myCompetence,$userId);
            }//if_my_competence

            /* Add the Actions Link */
            $out .= html_writer::start_tag('div',array('class' => 'btn_actions'));
                /* Add New Competence   */
                $out .= '<a href="' . $url_add . '" >' . '<h6>' . get_string('lnk_add','profilefield_competence')  . '</h6>' .'</a>';
            $out .= html_writer::end_tag('div'); //btn_actions

            $out .= html_writer::start_tag('div',array('class' => 'btn_actions'));
                /* Back to profile      */
                $out .= '<a href="' . $return_url . '">'  . '<h6>' . get_string('lnk_back','profilefield_competence') . '</h6>' . '</a>';
            $out .= html_writer::end_tag('div'); //btn_actions

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompetenceTable

    /*************/
    /*  PRIVATE  */
    /*************/

    /**
     * @param               $jr_lst
     * @return              array
     * @throws              Exception
     *
     * @creationDate        02/02/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get detail of job roles
     */
    private static function GetJobRoles($jr_lst) {
        /* Variables    */
        global $DB;
        $jobRoles   = array();
        $sql        = null;
        $rdo        = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT     jr.id,
                                jr.name
                     FROM       {report_gen_jobrole} jr
                     WHERE      jr.id IN ($jr_lst)
                     ORDER BY   jr.industrycode, jr.name ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $jobRoles[$instance->id] =  $instance->name;
                }//for_jobroles
            }//if_rdo

            return $jobRoles;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetJobRoles_Name


    /**
     * @return          string
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the competence table
     *
     * @updateDate      21/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add manager and reporter columns
     */
    private static function AddHeader_CompetenceTable() {
        /* Variables    */
        $header = '';

        try {
            $header .= html_writer::start_tag('div',array('class' => 'competence_table'));
                $header .= html_writer::start_div('competence_table_row title_competence');

                    /* Col One  */
                    $header .= html_writer::start_div('col_one');
                        $header .= '<h6>' . get_string('my_companies','profilefield_competence') . '</h6>';
                    $header .= html_writer::end_div();//col_one
                    /* Col Two  */
                    $header .= html_writer::start_div('col_two');
                        $header .= '<h6>' . get_string('my_job_roles','profilefield_competence') . '</h6>';
                    $header .= html_writer::end_div();//col_ttwo
                    /* Col Manager   */
                    $header .= html_writer::start_div('col_three');
                        $header .= '<h6>' . get_string('manager','profilefield_competence') . '</h6>';
                    $header .= html_writer::end_div();//col_three
                    /* Col Reporter  */
                    $header .= html_writer::start_div('col_three');
                        $header .= '<h6>' . get_string('reporter','profilefield_competence') . '</h6>';
                    $header .= html_writer::end_div();//col_three
                    /* Col Zero -- Toggle   */
                    $header .= html_writer::start_div('col_zero');
                    $header .= html_writer::end_div();//col_zero
                    /* Col Zero -- Toggle   */
                    $header .= html_writer::start_div('col_zero');
                    $header .= html_writer::end_div();//col_zero
                $header .= html_writer::end_div();//competence_table_row
            $header .= html_writer::end_tag('div'); //competence_table

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_CompetenceTable

    /**
     * @param           $my_competence
     * @param           $user_id
     * @return          string
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the competence table
     *          - Competence Data
     *                  --> data.                 Id Info Competence Data
     *                  --> competence.         Id Info Competence
     *                  --> levelThree
     *                  --> levelTwo
     *                  --> levelOne
     *                  --> levelZero
     *                  --> path
     *                  --> roles.      Array.
     *                                  [id]    --> Job Role Name.
     *
     * @updateDate      21/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add manager and reporter column
     */
    private static function AddContent_CompetenceTable($my_competence,$user_id) {
        /* Variables    */
        global $OUTPUT;
        $content        = '';
        $color          = null;
        $url_deleted    = null;
        $url_edit       = null;

        try {
            $content .= html_writer::start_tag('div',array('class' => 'competence_table'));
                if ($my_competence) {
                    foreach ($my_competence as $competence) {
                        $content .= html_writer::start_div('competence_table_row ' . $color);
                            /* Col One  */
                            $content .= html_writer::start_div('col_one');
                                $content .=  $competence->path;
                            $content .= html_writer::end_div();//col_one
                            /* Col Two  */
                            $content .= html_writer::start_div('col_two');
                                $content .= implode(', ',$competence->roles) . '</br>';
                            $content .= html_writer::end_div();//col_ttwo
                            /* Col Manager  */
                            $content .= html_writer::start_div('col_three');
                                if ($competence->manager) {
                                    $content .= html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/grade_correct'),
                                                                       'alt'=>null,
                                                                       'class'=>'iconsmall'));
                                }else {
                                    $content .= html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/grade_incorrect'),
                                                                       'alt'=>null,
                                                                       'class'=>'iconsmall'));
                                }//if_manager
                            $content .= html_writer::end_div();//col_three
                            /* Col Reporter */
                            $content .= html_writer::start_div('col_three');
                                if ($competence->reporter) {
                                    $content .= html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/grade_correct'),
                                                                       'alt'=>null,
                                                                       'class'=>'iconsmall'));
                                }else {
                                    $content .= html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/grade_incorrect'),
                                                                       'alt'=>null,
                                                                       'class'=>'iconsmall'));
                                }//if_reporter
                            $content .= html_writer::end_div();//col_three

                            /* Col Zero -- Edit   */
                            $content .= html_writer::start_div('col_zero');
                                /* Edit Link    */
                                if ($competence->editable) {
                                    $url_edit = new moodle_url('/user/profile/field/competence/actions/edit_competence.php',array('id' =>$user_id,'icd' => $competence->data,'ic' => $competence->competence));
                                    $content .= html_writer::link($url_edit,
                                                                  html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
                                                                                         'alt'=>get_string('btn_edit_users','profilefield_competence'),
                                                                                         'class'=>'iconsmall')),
                                                                  array('title'=>get_string('btn_edit_users','profilefield_competence')));
                                }else {
                                    $content .=  html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
                                                                        'alt'=>get_string('btn_edit_users','profilefield_competence'),
                                                                        'class'=>'iconsmall'));
                                }//if_editable
                            $content .= html_writer::end_div();//col_zero

                            /* Col Zero -- Toggle   */
                            $content .= html_writer::start_div('col_zero');
                                if ($competence->editable) {
                                    $url_deleted = new moodle_url('/user/profile/field/competence/actions/delete_competence.php',array('id' =>$user_id,'icd' => $competence->data,'ic' => $competence->competence));
                                    $content .= html_writer::link($url_deleted,
                                                                  html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'),
                                                                                         'alt'=>get_string('lnk_delete','profilefield_competence'),
                                                                                         'class'=>'iconsmall')),
                                                                  array('title'=>get_string('lnk_delete','profilefield_competence')));
                                }else {
                                    $content .= html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'),
                                                                       'alt'=>get_string('lnk_delete','profilefield_competence'),
                                                                       'class'=>'iconsmall'));
                                }//if_editable
                            $content .= html_writer::end_div();//col_zero
                        $content .= html_writer::end_div();//competence_table_row

                        if ($color == 'r0') {
                            $color = 'r1';
                        }else {
                            $color = 'r0';
                        }//if_color
                    }//for_competence
                }//if_competence
            $content .= html_writer::end_tag('div'); //competence_table


            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_CompetenceTable
}//compentece