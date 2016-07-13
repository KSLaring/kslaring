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
define('REQUEST_APPROVED',0);
define('REQUEST_REJECTED',1);

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
                     WHERE		uicd.userid   = :user
                        AND     (uicd.rejected = 0
                                 OR
                                 uicd.rejected IS NULL)
                        AND     uicd.approved = 1 ";

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
        $managers               = null;
        $levelZero              = null;
        $levelOne               = null;
        $levelTwo               = null;
        $levelThree             = null;

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
            $infoCompetenceData->approved           = 1;
            $infoCompetenceData->rejected           = 0;
            $infoCompetenceData->level              = 3;
            $infoCompetenceData->token              = self::GenerateToken($data->id,$data->level_3);

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

            /* Send Mail Manager to reject it if it's necessary */
            $levelZero  = $data->level_0;
            $levelOne   = $data->level_1;
            $levelTwo   = $data->level_2;
            $levelThree = $data->level_3;

            if (self::ManagersConnected($levelZero,$levelOne,$levelTwo,$levelThree)) {
                $managers = self::GetManagersCompany($levelZero,$levelOne,$levelTwo,$levelThree);
                /* Send Notification    */
                foreach($managers as $manager) {
                    self::SendNotificationManager($manager,$infoCompetenceData);
                }//if_managers
            }//if_manager

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
     * @param           $token
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    26/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get competence request connected with ticket
     */
    public static function CompetenceRequest($token) {
        /* Variables */

        try {
            return self::GetCompetenceRequest($token);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CompetenceRequest

    /**
     * @param           $competenceRequest
     * @param           $managerId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    26/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Reject the competence
     */
    public static function RejectCompetence(&$competenceRequest,$managerId) {
        /* Variables    */
        global $DB;
        $time = null;

        try {
            /* Local time   */
            $time = time();

            /* Reject   */
            $competenceRequest->rejected = 1;
            $competenceRequest->approved = 0;
            $competenceRequest->timerejected   = $time;
            $competenceRequest->timemodified   = $time;

            /* Execute  */
            $DB->update_record('user_info_competence_data',$competenceRequest);

            /* Send Notification to the user    */
            self::SendNotificationUser($competenceRequest,REQUEST_REJECTED);

            /* Send Notification Manager to revert the situation    */
            self::SendNotification_ToRevert($competenceRequest,$managerId);

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//RejectCompetence

    /**
     * @param           $competenceRequest
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    09/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Approve competence
     */
    public static function ApproveCompetence(&$competenceRequest) {
        /* Variables    */
        global $DB;
        $time = null;

        try {
            /* Local time   */
            $time = time();

            /* Reject   */
            $competenceRequest->rejected = 0;
            $competenceRequest->approved = 1;
            $competenceRequest->timerejected   = $time;
            $competenceRequest->timemodified   = $time;

            /* Execute  */
            $DB->update_record('user_info_competence_data',$competenceRequest);

            /* Send Notification to the user    */
            self::SendNotificationUser($competenceRequest,REQUEST_APPROVED);

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ApproveCompetence

    /**
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    24/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if there are managers conencted
     */
    public static function ManagersConnected($levelZero,$levelOne,$levelTwo,$levelThree) {
        /* Variables */
        global $DB;
        $sql = null;
        $rdo = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT	  DISTINCT 	u.id
                     FROM	    {report_gen_company_manager} rm
                        JOIN	{user}						 u        ON 	u.id 					= rm.managerid
                                                                      AND	u.deleted 				= 0
                     WHERE    (rm.levelzero = $levelZero AND  rm.levelone = $levelOne  AND rm.leveltwo = $levelTwo AND rm.levelthree = $levelThree) 
                              OR 
                              (rm.levelzero = $levelZero AND  rm.levelone = $levelOne  AND rm.leveltwo = $levelTwo AND rm.levelthree IS NULL) ";


            /* Execute */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ManagersConnected

    /**
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the managers connected with the user to send a notification
     *
     * @updateDate      24/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * The managers from the ast level of the hierarchy or from the previous level
     */
    public static function GetManagersCompany($levelZero,$levelOne,$levelTwo,$levelThree) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $managers       = array();

        try {
            /* First find managers in level three */
            /* no mangers level three -_> find managers in level two */

            /* Search Criteria  */
            $params = array();
            $params['hz']       = 0;
            $params['ho']       = 1;
            $params['ht']       = 2;
            $params['hth']      = 3;

            /* First Find Managers in Level Three */
            /* SQL Instruction  */
            $sql = self::GetSQLManagersCompanyByHierarchy($levelZero,$levelOne,$levelTwo,$levelThree,3);
            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Add Manager  */
                    $managers[$instance->id] = $instance;
                }//for_rdo
            }else {
                /* Managers In Level Two */
                $sql = self::GetSQLManagersCompanyByHierarchy($levelZero,$levelOne,$levelTwo,$levelThree,2);
                $rdo = $DB->get_records_sql($sql,$params);
                foreach ($rdo as $instance) {
                    /* Add Manager  */
                    $managers[$instance->id] = $instance;
                }//for_rdo
            }//if_Rdo

            return $managers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetManagersCompany

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
     * @param           $levelZero
     * @param           $levelOne
     * @param           $levelTwo
     * @param           $levelThree
     * @param           $hierarchy
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    24/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the right sql to get all manages connected with a specific level of the hierarchy
     */
    private static function GetSQLManagersCompanyByHierarchy($levelZero,$levelOne,$levelTwo,$levelThree,$hierarchy) {
        /* Variables */
        $sql = null;

        try {
            /* SQL by Hierarchy */
            switch ($hierarchy) {
                case 0:
                    /* SQL Instruction */
                    $sql = " SELECT	  DISTINCT 	u.id,
                                                co_zero.name as 'company'
                             FROM	    {report_gen_company_manager} rm
                                JOIN	{user}						 u        ON 	u.id 					= rm.managerid
                                                                              AND	u.deleted 				= 0
                                -- LEVEL ZERO
                                JOIN 	{report_gen_companydata}	 co_zero  ON 	co_zero.id 				= rm.levelzero
                                                                              AND	co_zero.hierarchylevel 	= :hz
                                -- LEVEL ONE
                                JOIN	{report_gen_companydata}	 co_one	  ON	co_one.id				= rm.levelone
                                                                              AND	co_one.hierarchylevel	= :ho
                             WHERE    (rm.levelzero = $levelZero AND  rm.levelone IS NULL  AND rm.leveltwo IS NULL AND rm.levelthree IS NULL) ";

                    break;
                case 1:
                    /* SQL Instruction */
                    $sql = " SELECT	  DISTINCT 	u.id,
                                        CONCAT(co_zero.name,'/',co_one.name) as 'company'
                             FROM	    {report_gen_company_manager} rm
                                JOIN	{user}						 u        ON 	u.id 					= rm.managerid
                                                                              AND	u.deleted 				= 0
                                -- LEVEL ZERO
                                JOIN 	{report_gen_companydata}	 co_zero  ON 	co_zero.id 				= rm.levelzero
                                                                              AND	co_zero.hierarchylevel 	= :hz
                                -- LEVEL ONE
                                JOIN	{report_gen_companydata}	 co_one	  ON	co_one.id				= rm.levelone
                                                                              AND	co_one.hierarchylevel	= :ho
                             WHERE    (rm.levelzero = $levelZero AND  rm.levelone = $levelOne  AND rm.leveltwo IS NULL AND rm.levelthree IS NULL) ";

                    break;
                case 2:
                    /* SQL Instruction */
                    $sql = " SELECT	  DISTINCT 	u.id,
                                        CONCAT(co_zero.name,'/',co_one.name,'/',co_two.name) as 'company'
                             FROM	    {report_gen_company_manager} rm
                                JOIN	{user}						 u        ON 	u.id 					= rm.managerid
                                                                              AND	u.deleted 				= 0
                                -- LEVEL ZERO
                                JOIN 	{report_gen_companydata}	 co_zero  ON 	co_zero.id 				= rm.levelzero
                                                                              AND	co_zero.hierarchylevel 	= :hz
                                -- LEVEL ONE
                                JOIN	{report_gen_companydata}	 co_one	  ON	co_one.id				= rm.levelone
                                                                              AND	co_one.hierarchylevel	= :ho
                                -- LEVEL TWO
                                JOIN	{report_gen_companydata}     co_two	  ON	co_two.id				= rm.leveltwo
                                                                              AND   co_two.hierarchylevel	= :ht
                             WHERE    (rm.levelzero = $levelZero AND  rm.levelone = $levelOne  AND rm.leveltwo = $levelTwo AND rm.levelthree IS NULL) ";
                    break;
                case 3:
                    /* SQL Instruction */
                    $sql = " SELECT	  DISTINCT 	u.id,
                                        CONCAT(co_zero.name,'/',co_one.name,'/',co_two.name,'/',co_tre.name) as 'company'
                             FROM	    {report_gen_company_manager} rm
                                JOIN	{user}						 u        ON 	u.id 					= rm.managerid
                                                                              AND	u.deleted 				= 0
                                -- LEVEL ZERO
                                JOIN 	{report_gen_companydata}	 co_zero  ON 	co_zero.id 				= rm.levelzero
                                                                              AND	co_zero.hierarchylevel 	= :hz
                                -- LEVEL ONE
                                JOIN	{report_gen_companydata}	 co_one	  ON	co_one.id				= rm.levelone
                                                                              AND	co_one.hierarchylevel	= :ho
                                -- LEVEL TWO
                                JOIN	{report_gen_companydata}     co_two	  ON	co_two.id				= rm.leveltwo
                                                                              AND   co_two.hierarchylevel	= :ht
                                -- LEVEL THREE
                                JOIN	{report_gen_companydata}	 co_tre   ON 	co_tre.id 				= rm.levelthree
                                                                              AND   co_tre.hierarchylevel 	= :hth
                             WHERE    (rm.levelzero = $levelZero AND  rm.levelone = $levelOne  AND rm.leveltwo = $levelTwo AND rm.levelthree = $levelThree) ";

                    break;
            }//hierarchy

            return $sql;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetManagersCompanyByHierarchy

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
            $params['three']    = $hierarchy->levelThree;
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
     * @param           $manager
     * @param           $infoCompetenceData
     *
     * @throws          Exception
     *
     * @creationDate    26/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send Notification to the manager
     */
    private static function SendNotificationManager($manager,$infoCompetenceData) {
        /* Variables    */
        global $SITE,$CFG;
        $strBody    = null;
        $strSubject = null;
        $bodyText   = null;
        $bodyHtml   = null;
        $infoMail   = null;
        $lnkReject  = null;
        $user       = null;

        try {
            /* Manager  */
            $user = get_complete_user_data('id',$infoCompetenceData->userid);

            /* Extra Info   */
            $infoMail = new stdClass();
            $infoMail->company  = $manager->company;
            $infoMail->user     = fullname($user);
            $infoMail->site     = $SITE->shortname;
            /* Reject Link  */
            $lnkReject  = $CFG->wwwroot . '/user/profile/field/competence/actions/reject.php/' . $infoCompetenceData->token . '/' . $manager->id;
            $infoMail->reject = '<a href="' . $lnkReject . '">' . get_string('reject_lnk','profilefield_competence') . '</br>';

            /* Mail */
            $strSubject = get_string('msg_subject_manager','profilefield_competence',$infoMail);
            $strBody    = get_string('msg_body_manager','profilefield_competence',$infoMail);

            /* Content Mail         */
            $bodyText = null;
            $bodyHtml = null;
            if (strpos($strBody, '<') === false) {
                // Plain text only.
                $bodyText = $strBody;
                $bodyHtml = text_to_html($bodyText, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $bodyHtml = format_text($strBody, FORMAT_MOODLE);
                $bodyText = html_to_text($bodyHtml);
            }

            /* Send Mail    */
            $manager = get_complete_user_data('id',$manager->id);
            email_to_user($manager, $SITE->shortname, $strSubject, $bodyText,$bodyHtml);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SendNotificationManager



    /**
     * @param           $competenceRequest
     * @param           $action
     *
     * @throws          Exception
     *
     * @creationDate    26/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send notification to the user
     */
    private static function SendNotificationUser($competenceRequest,$action) {
        /* Variables    */
        global $SITE,$CFG;
        $strBody    = null;
        $strSubject = null;
        $bodyText   = null;
        $bodyHtml   = null;
        $infoMail   = null;
        $user       = null;

        try {
            /* Get Info User    */
            $user = get_complete_user_data('id',$competenceRequest->userid);

            /* Extra Info   */
            $infoMail = new stdClass();
            $infoMail->company  = $competenceRequest->company;
            $infoMail->user     = fullname($user);
            $infoMail->site     = $SITE->shortname;

            /* Mail */
            switch ($action) {
                case REQUEST_APPROVED:
                    $strSubject = get_string('msg_subject_rejected','profilefield_competence',$infoMail);
                    $strBody    = get_string('msg_body_approved','profilefield_competence',$infoMail);

                    break;
                case REQUEST_REJECTED:
                    $strSubject = get_string('msg_subject_rejected','profilefield_competence',$infoMail);
                    $strBody    = get_string('msg_body_rejected','profilefield_competence',$infoMail);

                    break;
            }//switch

            /* Content Mail         */
            $bodyText = null;
            $bodyHtml = null;
            if (strpos($strBody, '<') === false) {
                // Plain text only.
                $bodyText = $strBody;
                $bodyHtml = text_to_html($bodyText, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $bodyHtml = format_text($strBody, FORMAT_MOODLE);
                $bodyText = html_to_text($bodyHtml);
            }

            /* Send Mail    */
            email_to_user($user, $SITE->shortname, $strSubject, $bodyText,$bodyHtml);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SendNotificationUser

    /**
     * @param           $competenceRequest
     * @param           $managerId
     *
     * @throws          Exception
     *
     * @creationDate    08/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Send a notification to revert the situation
     */
    private static function SendNotification_ToRevert($competenceRequest,$managerId) {
        /* Variables */
        global $SITE,$CFG;
        $strBody    = null;
        $strSubject = null;
        $bodyText   = null;
        $bodyHtml   = null;
        $infoMail   = null;
        $user       = null;
        $manager    = null;
        $lnkRevert  = null;

        try {
            /* Get Info User    */
            $user   = get_complete_user_data('id',$competenceRequest->userid);

            /* Get Info Manager */
            $manager = get_complete_user_data('id',$managerId);

            /* Extra Info   */
            $infoMail = new stdClass();
            $infoMail->company  = $competenceRequest->company;
            $infoMail->user     = fullname($user);
            $infoMail->site     = $SITE->shortname;
            /* Revert Link  */
            $lnkRevert  = $CFG->wwwroot . '/user/profile/field/competence/actions/approve.php/' . $competenceRequest->token . '/' . $managerId;
            $infoMail->revert = '<a href="' . $lnkRevert . '">' . get_string('approve_lnk','profilefield_competence') . '</br>';

            /* Mail */
            $strSubject = get_string('msg_subject_rejected','profilefield_competence',$infoMail);
            $strBody    = get_string('msg_boy_reverted','profilefield_competence',$infoMail);

            /* Content Mail         */
            $bodyText = null;
            $bodyHtml = null;
            if (strpos($strBody, '<') === false) {
                // Plain text only.
                $bodyText = $strBody;
                $bodyHtml = text_to_html($bodyText, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $bodyHtml = format_text($strBody, FORMAT_MOODLE);
                $bodyText = html_to_text($bodyHtml);
            }

            /* Send Mail    */
            email_to_user($manager, $SITE->shortname, $strSubject, $bodyText,$bodyHtml);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SendNotification_ToRevert

    /**
     * @param           $token
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    26/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get competence request connected with token
     */
    private static function GetCompetenceRequest($token) {
        /* Variables */
        global $DB;
        $competenceRequest  = null;
        $companies          = null;

        try {
            /* Execute  */
            $competenceRequest = $DB->get_record('user_info_competence_data',array('token' => $token));

            /* Get Company Name */
            $companies = self::Get_CompanyName($competenceRequest->companyid);
            $competenceRequest->company   = $companies[$competenceRequest->companyid];

            return $competenceRequest;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompetenceRequest

    /**
     * @param           $userId
     * @param           $company
     *
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    26/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate the token connected with
     */
    private static function GenerateToken($userId,$company) {
        /* Variables    */
        global $DB;
        $ticket = null;
        $token  = null;


        try {
            /* Ticket - Something long and Unique   */
            $ticket     = uniqid(mt_rand(),1);
            $ticket     = random_string() . $userId . '_' . time() . '_' . $company . '_' . $ticket . random_string();
            $token      = str_replace('/', '.', self::GenerateHash($ticket));

            /* Check if justs exist for other user  */
            while ($DB->record_exists('user_info_competence_data',array('companyid' => $company,'token' => $token))) {
                /* Ticket - Something long and Unique   */
                $ticket     = uniqid(mt_rand(),1);
                $ticket     = random_string() . $userId . '_' . time() . '_' . $company . '_' . $ticket . random_string();
                $token      = str_replace('/', '.', self::GenerateHash($ticket));
            }//while

            return $token;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GenerateToken

    /**
     * @param           $value
     *
     * @return          bool|string
     * @throws          Exception
     *
     * @creationDate    24/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Generate a hash for sensitive values
     */
    private static function GenerateHash($value) {
        /* Variables    */
        $cost               = 10;
        $required_salt_len  = 22;
        $buffer             = '';
        $buffer_valid       = false;
        $hash_format        = null;
        $salt               = null;
        $ret                = null;
        $hash               = null;

        try {
            /* Generate hash    */
            $hash_format        = sprintf("$2y$%02d$", $cost);
            $raw_length         = (int) ($required_salt_len * 3 / 4 + 1);

            if (function_exists('mcrypt_create_iv')) {
                $buffer = mcrypt_create_iv($raw_length, MCRYPT_DEV_URANDOM);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }

            if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
                $buffer = openssl_random_pseudo_bytes($raw_length);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }

            if (!$buffer_valid && file_exists('/dev/urandom')) {
                $f = @fopen('/dev/urandom', 'r');
                if ($f) {
                    $read = strlen($buffer);
                    while ($read < $raw_length) {
                        $buffer .= fread($f, $raw_length - $read);
                        $read = strlen($buffer);
                    }
                    fclose($f);
                    if ($read >= $raw_length) {
                        $buffer_valid = true;
                    }
                }
            }

            if (!$buffer_valid || strlen($buffer) < $raw_length) {
                $bl = strlen($buffer);
                for ($i = 0; $i < $raw_length; $i++) {
                    if ($i < $bl) {
                        $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                    } else {
                        $buffer .= chr(mt_rand(0, 255));
                    }
                }
            }

            $salt = str_replace('+', '.', base64_encode($buffer));

            $salt = substr($salt, 0, $required_salt_len);

            $hash = $hash_format . $salt;

            $ret = crypt($value, $hash);

            if (!is_string($ret) || strlen($ret) <= 13) {
                return false;
            }

            return $ret;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GenerateHash

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