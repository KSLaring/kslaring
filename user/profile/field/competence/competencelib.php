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
                $levelZero[$company->levelZero] = $company->levelZero;
                $levelOne[$company->levelOne]   = $company->levelOne;
                $levelTwo[$company->levelTwo]   = $company->levelTwo;
                $levelThree[$company->levelThree] = $company->levelThree;
            }

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
        $companies_name = array();

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
                    $companies_name[$instance->id] = $instance->name;
                }//for_instance
            }//if_rdo

            return $companies_name;
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
     */
    public static function Get_CompetenceData($user_id,$competence_data=null,$competence=null) {
        /* Variables    */
        global $DB;
        $my_competence  = array();
        $info_hierarchy = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user']     = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		uicd.id,
                                uicd.competenceid,
                                level_two.parentid 	as 'leveltwo',
                                level_one.parentid 	as 'levelone',
                                level_zero.parentid as 'levelzero',
                                uicd.companyid 		as 'levelthree',
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
     * @param           $user_id
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    02/02/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the generics competence connected to the user.
     */

    public static function GetCompetence_Generics($user_id) {
        /* Variables    */
        global $DB;
        $info_generics  = null;

        try {

            /* Execute  */
            $rdo = $DB->get_record('user_info_competence_data',array('userid' => $user_id,'companyid' => 0));
            if ($rdo) {
                /* Info Generics    */
                $info_generics = new stdClass();
                $info_generics->data           = $rdo->id;
                $info_generics->competence     = $rdo->competenceid;
                $info_generics->levelThree     = $rdo->companyid;
                /* Job Roles        */
                $info_generics->roles          = self::GetJobRoles($rdo->jobroles);
            }else {
                $rdo = $DB->get_record('user_info_competence',array('userid' => $user_id));
                /* Info Generics    */
                $info_generics = new stdClass();
                $info_generics->data           = 0;
                $info_generics->competence     = 0;
                $info_generics->levelThree     = 0;
                /* Job Roles        */
                $info_generics->roles          = null;
            }

            return $info_generics;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompetence_Generics

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
        $hierarchyPath     = null;
        $companies_name    = null;

        try {
            /* Get Companies Name   */
            $companies      = $hierarchy->levelThree . ',' . $hierarchy->levelTwo . ',' . $hierarchy->levelOne . ',' . $hierarchy->levelZero;
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
        $companies = array();

        try {
            /* Research Criteria */
            $params = array();
            $params['level']    = $level;

            /* SQL Instruction */
            $sql_Select = " SELECT     DISTINCT rcd.id,
                                       rcd.name,
                                       rcd.industrycode
                            FROM       {report_gen_companydata} rcd ";
            /* Join */
            $sql_Join = " ";
            if ($parent_id) {
                $sql_Join = " JOIN  {report_gen_company_relation} rcr   ON    rcr.companyid = rcd.id
                                                                        AND   rcr.parentid  IN ($parent_id) ";
            }//if_level

            $sql_Where = " WHERE rcd.hierarchylevel = :level ";
            /* Don't display the companies just added in the profile    */
            if ($my_companies) {
                $sql_Where .= " AND rcd.id NOT IN ($my_companies) ";
            }
            $sql_Order = " ORDER BY rcd.industrycode, rcd.name ASC ";

            /* SQL */
            $sql = $sql_Select . $sql_Join . $sql_Where . $sql_Order;

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
     */
    public static function AddCompetence($data) {
        /* Variables    */
        global $DB;
        $time               = time();
        $info_competence        = null;
        $info_competence_data   = null;
        $info_data              = null;
        $my_roles               = null;

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* Check if it exists a competence info instance for the user */
            $info_competence = $DB->get_record('user_info_competence',array('userid' => $data->id));
            if ($info_competence) {
                $info_competence->timemodified  = $time;

                /* Update   */
                $DB->update_record('user_info_competence',$info_competence);
            }else {
                /* First    --> Create Instance user_info_competence    */
                $info_competence = new stdClass();
                $info_competence->userid        = $data->id;
                $info_competence->timemodified  = $time;
                /* Execute  */
                $info_competence->id = $DB->insert_record('user_info_competence',$info_competence);
            }//if_info_competence

            /* Second   --> User Info Competence Data   */
            $info_competence_data = new stdClass();
            $info_competence_data->competenceid     = $info_competence->id;
            $info_competence_data->userid           = $info_competence->userid;
            $info_competence_data->companyid        = $data->level_3;
            $info_competence_data->jobroles         = implode(',',$data->job_roles);
            $info_competence_data->timemodified     = $time;
            /* Execute  */
            $DB->insert_record('user_info_competence_data',$info_competence_data);

            /* Third    --> User Info Data              */
            /* Get the fieldid of competence profile    */
            $field = $DB->get_record('user_info_field',array('datatype' => 'competence'),'id');
            $info_data = $DB->get_record('user_info_data',array('fieldid' => $field->id,'userid' => $info_competence->userid));
            if (!$info_data) {
                $info_data = new stdClass();
                $info_data->userid  = $data->id;
                $info_data->fieldid = $field->id;
                $info_data->data    = $info_competence->id;
                /* Execute  */
                $DB->insert_record('user_info_data',$info_data);
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
     */
    public static function EditCompetence($data) {
        /* Variables    */
        global $DB;
        $info_competence_data    = null;
        $time = time();

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* Info Data    */
            $info_competence_data = new stdClass();
            $info_competence_data->competenceid = $data->ic;
            $info_competence_data->userid       = $data->id;
            $info_competence_data->jobroles     = implode(',',$data->job_roles);
            $info_competence_data->timemodified = time();

            if ($data->icd) {
                $info_competence_data->id           = $data->icd;

                /* Update       */
                $DB->update_record('user_info_competence_data',$info_competence_data);
            }else {
                if ($data->ge) {
                    $info_competence_data->companyid = 0;
                }
                if ($data->ic) {
                    $info_competence = new stdClass();
                    $info_competence->userid        = $data->id;
                    $info_competence->timemodified  = $time;
                    /* Execute  */
                    $info_competence->id = $DB->insert_record('user_info_competence',$info_competence);
                    $info_competence_data->competence = $info_competence->id;
                    /* Update       */
                    $DB->insert_record('user_info_competence_data',$info_competence_data);

                    /* Get the fieldid of competence profile    */
                    $field = $DB->get_record('user_info_field',array('datatype' => 'competence'),'id');
                    $info_data = $DB->get_record('user_info_data',array('fieldid' => $field->id,'userid' => $info_competence->userid));
                    if (!$info_data) {
                        $info_data = new stdClass();
                        $info_data->userid  = $data->id;
                        $info_data->fieldid = $field->id;
                        $info_data->data    = $info_competence->id;
                        /* Execute  */
                        $DB->insert_record('user_info_data',$info_data);
                    }//create_new_entrance
                }else {
                    $DB->insert_record('user_info_competence_data',$info_competence_data);
                }
            }//if_data_icd

            /* Commit   */
            $trans->allow_commit();

        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//EditCompetence


    /**
     * @param           $user_id
     * @param           $competence_data
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
    public static function DeleteCompetence($user_id,$competence_data,$competence) {
        /* Variables    */
        global $DB;
        $info_competence = null;
        $companies       = null;
        $job_roles       = null;

        try {
            $DB->delete_records('user_info_competence_data',array('id' => $competence_data, 'competenceid' => $competence, 'userid' => $user_id));
            /* Check if Delete user_info_competence / user_info_data    */
            $rdo = $DB->get_records('user_info_competence_data',array('competenceid' => $competence, 'userid' => $user_id));
            if (!$rdo) {
                /* Delete User Info Competence / User Info Data */
                $DB->delete_records('user_info_competence',array('id' => $competence, 'userid' => $user_id));
                /* Get ID of User Info Data */
                $field      = $DB->get_record('user_info_field',array('datatype' => 'competence'),'id');
                $info_data  = $DB->get_record('user_info_data',array('fieldid' => $field->id,'userid' => $user_id));
                $DB->delete_records('user_info_data',array('id' => $info_data->id,'fieldid' => $field->id,'userid' => $user_id));
            }//if_!rdo

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//DeleteCompetence

    /**
     * @param           $my_competence
     * @param           $my_generics
     * @param           $user_id
     * @return          string
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the table with the competence data connected with the user
     *          - Competence Data
     *                  --> data.     Id Info Competence Data
     *                  --> competence.         Id Info Competence
     *                  --> levelThree
     *                  --> levelTwo
     *                  --> levelOne
     *                  --> levelZero
     *                  --> path
     *                  --> roles.      Array.
     *                                  [id]    --> Job Role Name.
     */
    public static function Get_CompetenceTable($my_competence,$my_generics,$user_id) {
        /* Variables    */
        $out                = '';
        $content_comp       = '';
        $content_generics   = '';

        $return_url     = new moodle_url('/user/profile.php',array('id' =>$user_id));
        $url_add        = new moodle_url('/user/profile/field/competence/actions/add_competence.php',array('id' =>$user_id));

        try {
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
            if ($my_competence) {
                $content_comp     .= self::AddContent_CompetenceTable($my_competence,$user_id);
            }//if_my_competence

            /* HIERARCHY LEVEL - HEADER TABLE   */
            $out .= self::AddHeader_CompetenceTable();
            /* Content Hierarchy Level          */
            $out .= $content_comp;

            /* GENERICS */
            if ($my_generics) {
                $content_generics .= self::AddContent_GenericsCompetenceTable($user_id,$my_generics);
            }//if_my_generics

            $out .= '</br>';
            $out .= self::AddHeader_GenericsCompetenceTable();
            /* Generics Content     */
            $out .= $content_generics;

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
     */
    private static function AddHeader_CompetenceTable() {
        /* Variables    */
        $header = '';

        try {
            $header .= html_writer::start_tag('div',array('class' => 'competence_table'));
                $header .= html_writer::start_div('competence_table_row title_competence');
                    /* Col Zero -- Toggle   */
                    $header .= html_writer::start_div('col_zero');
                    $header .= html_writer::end_div();//col_zero
                    /* Col One  */
                    $header .= html_writer::start_div('col_one');
                        $header .= '<h6>' . get_string('my_companies','profilefield_competence') . '</h6>';
                    $header .= html_writer::end_div();//col_one
                    /* Col Two  */
                    $header .= html_writer::start_div('col_two');
                        $header .= '<h6>' . get_string('my_job_roles','profilefield_competence') . '</h6>';
                    $header .= html_writer::end_div();//col_ttwo
                    /* Col Three  */
                    $header .= html_writer::start_div('col_three');
                    $header .= html_writer::end_div();//col_three
                $header .= html_writer::end_div();//competence_table_row
            $header .= html_writer::end_tag('div'); //competence_table

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_CompetenceTable

    /**
     * @return              string
     * @throws              Exception
     *
     * @creationDate        28/01/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Add the header of the table for the generics job roles.
     */
    private static function AddHeader_GenericsCompetenceTable() {
        /* Variables    */
        $header = '';

        try {
            $header .= html_writer::start_tag('div',array('class' => 'competence_table'));
                $header .= html_writer::start_div('competence_table_row title_competence');
                    /* Col Zero -- Toggle   */
                    $header .= html_writer::start_div('col_zero');
                    $header .= html_writer::end_div();//col_zero
                    /* Col One  */
                    $header .= html_writer::start_div('col_one');
                    $header .= html_writer::end_div();//col_one
                    /* Col Two  */
                    $header .= html_writer::start_div('col_two');
                        $header .= '<h6>' . get_string('level_generic','profilefield_competence') . '</h6>';
                    $header .= html_writer::end_div();//col_ttwo
                    /* Col Three  */
                    $header .= html_writer::start_div('col_three');
                    $header .= html_writer::end_div();//col_three
                $header .= html_writer::end_div();//competence_table_row
            $header .= html_writer::end_tag('div'); //competence_table

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_GenericsCompetenceTable

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
                            /* Col Zero -- Toggle   */
                            $content .= html_writer::start_div('col_zero');
                                /* Edit Link    */
                                $url_edit = new moodle_url('/user/profile/field/competence/actions/edit_competence.php',array('id' =>$user_id,'icd' => $competence->data,'ic' => $competence->competence));
                                $content .= html_writer::link($url_edit,
                                                              html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
                                                                                     'alt'=>get_string('btn_edit_users','local_microlearning'),
                                                                                     'class'=>'iconsmall')),
                                                              array('title'=>get_string('btn_edit_users','local_microlearning')));
                            $content .= html_writer::end_div();//col_zero
                            /* Col One  */
                            $content .= html_writer::start_div('col_one');
                                $content .=  $competence->path;
                            $content .= html_writer::end_div();//col_one
                            /* Col Two  */
                            $content .= html_writer::start_div('col_two');
                                $content .= implode(', ',$competence->roles) . '</br>';
                            $content .= html_writer::end_div();//col_ttwo
                            /* Col Three  */
                            $content .= html_writer::start_div('col_three');
                                /* URL Deleted  */
                                $url_deleted = new moodle_url('/user/profile/field/competence/actions/delete_competence.php',array('id' =>$user_id,'icd' => $competence->data,'ic' => $competence->competence));
                                $content .= '<a href="' . $url_deleted . '" class="lnk_col">' . get_string('lnk_delete','profilefield_competence')  . '</a>';
                            $content .= html_writer::end_div();//col_three
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

    /**
     * @param           $user_id
     * @param           $my_generics
     * @return          string
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Display the job roles that are generics
     */
    private static function AddContent_GenericsCompetenceTable($user_id,$my_generics) {
        /* Variables    */
        global $OUTPUT;
        $content        = '';
        $color          = 'r0';
        $url_deleted    = null;
        $url_edit       = null;
        $lnk_class      = 'lnk_col';

        try {

            if (!$my_generics->roles) {
                $lnk_class = 'lnk_col lnk_disabled';
            }

            $content .= html_writer::start_tag('div',array('class' => 'competence_table'));
                $content .= html_writer::start_div('competence_table_row ' . $color);
                    /* Col Zero -- Toggle   */
                    $content .= html_writer::start_div('col_zero');
                    /* Edit Link    */
                    $url_edit = new moodle_url('/user/profile/field/competence/actions/edit_competence.php',array('id' =>$user_id,'icd' => $my_generics->data,'ic' => $my_generics->competence,'ge' => 1));
                    $content .= html_writer::link($url_edit,
                        html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
                            'alt'=>get_string('btn_edit_users','local_microlearning'),
                            'class'=>'iconsmall')),
                        array('title'=>get_string('btn_edit_users','local_microlearning')));
                    $content .= html_writer::end_div();//col_zero
                    /* Col One  */
                    $content .= html_writer::start_div('col_one');
                    $content .= html_writer::end_div();//col_one
                    /* Col Two  */
                    $content .= html_writer::start_div('col_two');
                        if ($my_generics->roles) {
                            $content .= implode(',',$my_generics->roles);
                        }
                    $content .= html_writer::end_div();//col_ttwo
                    /* Col Three  */
                    $content .= html_writer::start_div('col_three');
                        /* URL Deleted  */
                        $url_deleted = new moodle_url('/user/profile/field/competence/actions/delete_competence.php',array('id' =>$user_id,'icd' => $my_generics->data,'ic' => $my_generics->competence,'ge' => 1));
                        $content .= '<a href="' . $url_deleted . '" class="' . $lnk_class . ' ">' . get_string('lnk_delete','profilefield_competence')  . '</a>';
                    $content .= html_writer::end_div();//col_three
                    $content .= html_writer::end_div();//competence_table_row
            $content .= html_writer::end_tag('div'); //competence_table

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_GenericsCompetenceTable
}//compentece