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
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the competence connected to the user.
     *          - Competence Data
     *                  --> dataid.     Id Info Competence
     *                  --> companies.  Array.
     *                                  [level three]
     *                                          --> levelThree
     *                                          --> levelTwo
     *                                          --> levelOne
     *                                          --> levelOne
     *                                          --> roles   .   Array
     *                                                          [id]    --> Job Role Name
     *                                          --> path    .   Hierarchy Structure Path
     *                  --> generics.   Array.
     *                                  [id]    --> Job Role Name.
     */
    public static function Get_CompetenceData($user_id) {
        /* Variables    */
        global $DB;
        $my_competence = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user']     = $user_id;
            $params['datatype'] = 'competence';

            /* SQL Instruction  */
            $sql = " SELECT		uic.id,
                                uic.companyid,
                                uic.jobroleid
                     FROM		{user_info_data}	        uid
                        JOIN	{user_info_field}			uif		ON	uif.id			= uid.fieldid
                                                                    AND uif.datatype	= :datatype
                        JOIN	{user_info_competence}		uic 	ON 	uic.id 			= uid.data
                                                                    AND	uic.userid 		= uid.userid
                     WHERE		uid.userid 		= :user ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Extract Info */
                $my_competence = new stdClass();
                $my_competence->dataid      = $rdo->id;
                /* Get My Company Structure */
                if ($rdo->companyid) {
                    $my_competence->companies   = self::Get_MyHierarchy($rdo->companyid,$rdo->jobroleid);
                }else {
                    $my_competence->companies   = null;
                }//if_company

                /* Job Roles not connected with any level  */
                if ($rdo->jobroleid) {
                    $my_competence->generics    = self::Get_JobRoles_Generics($rdo->jobroleid);
                    $my_competence->my_roles    = $rdo->jobroleid;
                }else {
                    $my_competence->generics    = null;
                    $my_competence->my_roles    = null;
                }//if_job_roles

            }//if_rdo

            return $my_competence;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_CompetenceData

    /**
     * @param           $levelThee
     * @param           $job_roles
     * @return          array
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get hierarchy connected with the user
     *      - Hierarchy     . Array
     *                          [level three]
     *                                  --> levelThree
     *                                  --> levelTwo
     *                                  --> levelOne
     *                                  --> levelOne
     *                                  --> roles   .   Array
     *                                                  [id]    --> Job Role Name
     *                                  --> path    .   Hierarchy Structure Path
     */
    public static function Get_MyHierarchy($levelThee,$job_roles) {
        /* Variables    */
        global $DB;
        $my_hierarchy   = array();
        $companies_name = null;
        $companies      = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		co.id               as 'levelthree',
                                level_two.parentid 	as 'leveltwo',
                                level_one.parentid 	as 'levelone',
                                level_zero.parentid as 'levelzero'
                     FROM		{report_gen_companydata}	co
                        JOIN	(
                                    SELECT		cr.companyid,
                                                cr.parentid
                                    FROM		{report_gen_companydata}			co
                                        JOIN	{report_gen_company_relation}		cr	ON cr.parentid = co.id
                                    WHERE		co.hierarchylevel = 2
                                ) level_two ON level_two.companyid = co.id
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
                     WHERE		co.hierarchylevel = 3
                        AND		co.id IN ($levelThee)
                     ORDER BY 	co.name ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Get Companies Name   */
                    $companies      = $instance->levelthree . ',' . $instance->leveltwo . ',' . $instance->levelone . ',' . $instance->levelzero;
                    $companies_name = self::Get_CompanyName($companies);
                    /* Structure Info   */
                    $info = new stdClass();
                    /* Level Three  */
                    $info->levelThree   = $instance->levelthree;
                    /* Level Two    */;
                    $info->levelTwo     = $instance->leveltwo;
                    /* Level One    */
                    $info->levelOne     = $instance->levelone;
                    /* Level Zero   */
                    $info->levelZero    = $instance->levelzero;
                    /* Path         */
                    $info->path         = self::GetHierarchyPath($info,$companies_name);
                    /* Job Roles    */
                    $info->roles = self::Get_JobRoles_Level($job_roles,$info);

                    $my_hierarchy[$instance->levelthree] = $info;
                }//for_rdo
            }//if_rdo

            return $my_hierarchy;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyHierarchy

    /**
     * @param           $hierarchy
     * @param           $companies_name
     * @return          string
     * @throws          Exception
     *
     * @creationDate    29/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the hierarchy path
     */
    private static function GetHierarchyPath($hierarchy,$companies_name) {
        /* Variables    */
        $hierarchyPath     = null;

        try {

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
            $params['datatype'] = 'competence';

            /* SQL Instruction  */
            $sql = " SELECT		uic.companyid
                     FROM		{user_info_data}	        uid
                        JOIN	{user_info_field}			uif		ON	uif.id			= uid.fieldid
                                                                    AND uif.datatype	= :datatype
                        JOIN	{user_info_competence}		uic 	ON 	uic.id 			= uid.data
                                                                    AND	uic.userid 		= uid.userid
                     WHERE		uid.userid 		= :user ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->companyid;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyCompanies

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
    public static function Get_MyJobRoles($user_id) {
        /* Variables    */
        global $DB;

        try {
            $params = array();
            $params['user']     = $user_id;
            $params['datatype'] = 'competence';

            /* SQL Instruction  */
            $sql = " SELECT		uic.jobroleid
                     FROM		{user_info_data}	        uid
                        JOIN	{user_info_field}			uif		ON	uif.id			= uid.fieldid
                                                                    AND uif.datatype	= :datatype
                        JOIN	{user_info_competence}		uic 	ON 	uic.id 			= uid.data
                                                                    AND	uic.userid 		= uid.userid
                     WHERE		uid.userid 		= :user ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->jobroleid;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyJobRoles

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
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the job roles connected with the levels
     */
    public static function GetJobRoles_Hierarchy(&$options,$levelZero,$levelOne,$levelTwo, $levelThree) {
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
                                                                                  )
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
        $info_competence    = null;
        $info_data          = null;
        $my_roles           = null;

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* Check if it exists a competence info instance for the user */
            $info_competence = $DB->get_record('user_info_competence',array('userid' => $data->id));
            if ($info_competence) {
                /* Add Companies    */
                if ($info_competence->companyid) {
                    $info_competence->companyid .= ','. implode(',',$data->level_3);
                }else {
                    $info_competence->companyid  = implode(',',$data->level_3);
                }//if_companies
                /* Add Job Roles    */
                if ($info_competence->jobroleid) {
                    $my_roles   = explode(',',$info_competence->jobroleid);
                    $new_roles  = array_diff($data->job_roles,$my_roles);
                    $info_competence->jobroleid .= ',' . implode(',',$new_roles);
                }else {
                    $info_competence->jobroleid  = implode(',',$data->job_roles);
                }//if_job_roles

                $info_competence->timemodified  = $time;

                /* Update   */
                $DB->update_record('user_info_competence',$info_competence);
            }else {
                /* First    --> Create Instance user_info_competence    */
                $info_competence = new stdClass();
                $info_competence->userid        = $data->id;
                $info_competence->companyid     = implode(',',$data->level_3);
                $info_competence->jobroleid     = implode(',',$data->job_roles);
                $info_competence->timemodified  = $time;
                /* Execute  */
                $info_competence->id = $DB->insert_record('user_info_competence',$info_competence);

                /* Second   --> Create Instance user_info_data          */
                /* Get the fieldid of competence profile    */
                $field = $DB->get_record('user_info_field',array('datatype' => 'competence'),'id');
                $info_data = new stdClass();
                $info_data->userid  = $data->id;
                $info_data->fieldid = $field->id;
                $info_data->data    = $info_competence->id;
                /* Execute  */
                $DB->insert_record('user_info_data',$info_data);
            }//if_info_competence

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
        $info_competence    = null;
        $my_roles           = null;
        $my_three           = null;

        try {
            /* Check if it exists a competence info instance for the user */
            $info_competence = $DB->get_record('user_info_competence',array('userid' => $data->id));
            $info_competence->timemodified  = time();

            /* Add Companies    */
            if (!$data->ge) {
                $my_three   = explode(',',$info_competence->companyid);
                $my_three   = array_diff($my_three,explode(',',$data->my_ini_three));
                if ($my_three) {
                    $info_competence->companyid  = implode(',',$data->level_3) . ',' . implode(',',$my_three);
                }else {
                    $info_competence->companyid  = implode(',',$data->level_3);
                }//if_companies
            }//if_not_generics

            /* Add Job Roles    */
            $my_roles   = explode(',',$info_competence->jobroleid);
            $my_roles   = array_diff($my_roles,explode(',',$data->my_ini_roles));
            if ($my_roles) {
                $info_competence->jobroleid  = implode(',',$data->job_roles) . ',' . implode(',',$my_roles);
            }else {
                $info_competence->jobroleid  = implode(',',$data->job_roles);
            }//if_roles

            /* Update   */
            $DB->update_record('user_info_competence',$info_competence);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//EditCompetence


    /**
     * @param           $user_id
     * @param           $competence_id
     * @param           $to_delete
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete the competence from the user profile.
     */
    public static function DeleteCompetence($user_id,$competence_id,$to_delete) {
        /* Variables    */
        global $DB;
        $info_competence = null;
        $companies       = null;
        $job_roles       = null;

        try {
            /* Get the instance */
            $params = array();
            $params['userid'] = $user_id;
            $params['id']     = $competence_id;
            /* Execute  */
            $rdo = $DB->get_record('user_info_competence',$params);
            if ($rdo) {
                /* Info Competence to update/delete */
                $info_competence = new stdClass();
                $info_competence->id            = $rdo->id;
                $info_competence->userid        = $rdo->userid;
                $info_competence->timemodified  = time();

                /* Delete the company   */
                if ($to_delete->levelThree) {
                    $companies = explode(',',$rdo->companyid);
                    $companies = array_flip($companies);
                    if (array_key_exists($to_delete->levelThree,$companies)) {
                        unset($companies[$to_delete->levelThree]);
                    }//if_company_exists
                    $companies = array_flip($companies);
                    $info_competence->companyid = implode(',',$companies);
                }//if_exists_levelThree

                /* Delete Job roles */
                $job_roles  = explode(',',$rdo->jobroleid);
                $job_roles  = array_flip($job_roles);
                foreach ($to_delete->roles as $jr_id => $jr) {
                    if (array_key_exists($jr_id,$job_roles)) {
                        unset($job_roles[$jr_id]);
                    }//if_jr_exists
                }//job_roles
                $job_roles  = array_flip($job_roles);
                $info_competence->jobroleid = implode(',',$job_roles);

                /* Update or Delete the user info competence    */
                /* Update   */
                $DB->update_record('user_info_competence',$info_competence);
                $rdo = $DB->get_record('user_info_competence',$params);
                if (!$rdo->companyid) {
                    /* Delete   */
                    self::Delete_UserInfoCompetence($user_id,$competence_id);
                }//if_company_job_role
            }//if_rdo

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//DeleteCompetence

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
     * Get the table with the competence data connected with the user
     *          - My Competence
     *                  --> dataid.     Id Info Competence
     *                  --> companies.  Array.
     *                                  [level three]
     *                                          --> levelThree
     *                                          --> levelTwo
     *                                          --> levelOne
     *                                          --> levelOne
     *                                          --> roles   .   Array
     *                                                          [id]    --> Job Role Name
     *                                          --> path    .   Hierarchy Structure Path
     *                  --> generics.   Array.
     *                                  [id]    --> Job Role Name.
     */
    public static function Get_CompetenceTable($my_competence,$user_id) {
        /* Variables    */
        $out                = '';
        $content_comp       = '';
        $content_generics   = '';

        $return_url     = new moodle_url('/user/editadvanced.php',array('id' =>$user_id));
        $url_add        = new moodle_url('/user/profile/field/competence/actions/add_competence.php',array('id' =>$user_id));
        $url_edit       = new moodle_url('/user/profile/field/competence/actions/edit_competence.php',array('id' =>$user_id));

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
                /* Edit */
                if ($my_competence) {
                    $out .= '<a href="' . $url_edit . '" >' . '<h6>' . get_string('lnk_edit','profilefield_competence')  . '</h6>' .'</a>';
                }else {
                    $out .= '<a href="' . $url_edit . '" class="lnk_disabled" >' . '<h6>' . get_string('lnk_edit','profilefield_competence')  . '</h6>' .'</a>';
                }

                /* Back to profile      */
                $out .= '<a href="' . $return_url . '">'  . '<h6>' . get_string('lnk_back','profilefield_competence') . '</h6>' . '</a>';
            $out .= html_writer::end_tag('div'); //btn_actions

            /* Get Info Competence to display      */
            if ($my_competence) {
                $content_comp     .= self::AddContent_CompetenceTable($my_competence,$user_id);
                $content_generics .= self::AddContent_GenericsCompetenceTable($user_id,$my_competence);
            }//if_my_competence

            /* HIERARCHY LEVEL - HEADER TABLE   */
            $out .= self::AddHeader_CompetenceTable();
            /* Content Hierarchy Level          */
            $out .= $content_comp;

            /* GENERICS */
            $out .= '</br>';
            $out .= self::AddHeader_GenericsCompetenceTable();
            /* Generics Content     */
            $out .= $content_generics;

            /* Add the Actions Link */
            $out .= html_writer::start_tag('div',array('class' => 'btn_actions'));
                /* Add New Competence   */
                $out .= '<a href="' . $url_add . '" >' . '<h6>' . get_string('lnk_add','profilefield_competence')  . '</h6>' .'</a>';
                /* Edit */
                if ($my_competence) {
                    $out .= '<a href="' . $url_edit . '" >' . '<h6>' . get_string('lnk_edit','profilefield_competence')  . '</h6>' .'</a>';
                }else {
                    $out .= '<a href="' . $url_edit . '" class="lnk_disabled" >' . '<h6>' . get_string('lnk_edit','profilefield_competence')  . '</h6>' .'</a>';
                }

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
     * @param           $jr_lst
     * @param           $my_hierarchy
     * @return          array
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the job roles connected with a specific level
     */
    private static function Get_JobRoles_Level($jr_lst,$my_hierarchy) {
        /* Variables    */
        global $DB;
        $hierarchy = array();

        try {
            /* Search Criteria  */
            $levelZero  = $my_hierarchy->levelZero;
            $levelOne   = $my_hierarchy->levelOne;
            $levelTwo   = $my_hierarchy->levelTwo;
            $levelThree = $my_hierarchy->levelThree;

            /* SQL Instruction */
            $sql = " SELECT			DISTINCT 	jr.id,
                                                jr.name
                     FROM			{report_gen_jobrole}			jr
                        JOIN		{report_gen_jobrole_relation}	jr_rel 	ON 	jr_rel.jobroleid 	= jr.id
                                                                            AND ((jr_rel.levelzero    IN ($levelZero)
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
                                                                                 )
                                                                                 OR
                                                                                 (jr_rel.levelzero    IN ($levelZero)
                                                                                  AND
                                                                                  jr_rel.levelone     IN ($levelOne)
                                                                                 )
                                                                                 OR
                                                                                 (jr_rel.levelzero    IN ($levelZero)
                                                                                 )
                                                                                ) ";

            /* Search Criteria and Sort */
            $sql .= " WHERE	    jr.id IN ($jr_lst)
                      ORDER BY  jr.name ";
            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $hierarchy[$instance->id] = $instance->name;
                }//for_rdo
            }//if_Rdo

            return $hierarchy;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRoles_Level

    /**
     * @param           $jr_lst
     * @return          array
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the job roles that are generics for all levels
     */
    private static function Get_JobRoles_Generics($jr_lst) {
        /* Variables    */
        global $DB;
        $jr_generics    = array();

        try {
            /* SQL Instruction */
            $sql = " SELECT		DISTINCT jr.id,
                                         jr.name
                     FROM		{report_gen_jobrole}				jr
                        JOIN	{report_gen_jobrole_relation}		jr_rel	ON 	 jr_rel.jobroleid = jr.id
                                                                            AND  jr_rel.levelzero   IS NULL
                                                                            AND	 jr_rel.levelone    IS NULL
                                                                            AND  jr_rel.leveltwo    IS NULL
                                                                            AND	 jr_rel.levelthree  IS NULL


                     WHERE jr.id IN ($jr_lst) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $jr_generics[$instance->id] = $instance->name;
                }//for_rdo
            }//if_Rdo

            return $jr_generics;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRoles_Generics


    /**
     * @param           $user_id
     * @param           $competence_id
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete all the user info competence from the user profile
     */
    private static function Delete_UserInfoCompetence($user_id,$competence_id) {
        /* Variables    */
        global $DB;
        $info_competence = null;

        /* Begin Transaction   */
        $trans = $DB->start_delegated_transaction();
        try {
            /* First Remove form user_info_competence   */
            $DB->delete_records('user_info_competence',array('id' => $competence_id,'userid' => $user_id));

            /* Second remove from user_info_data        */
            /* Get the instance from user_info_data to remote   */
            /* Search Criteria  */
            $params = array();
            $params['user']     = $user_id;
            $params['data_id']  = $competence_id;
            /* SQL Instruction  */
            $sql = " SELECT		uid.id,
                                uid.userid,
                                uid.data
                     FROM		{user_info_data}	        	uid
                        JOIN	{user_info_field}				uif		ON	uif.id			= uid.fieldid
                                                                        AND uif.datatype	= 'competence'
                     WHERE		uid.userid 		= :user
                        AND		uid.data		= :data_id ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            $DB->delete_records('user_info_data',array('id' => $rdo->id,'userid' => $rdo->userid));

            /* Commit   */
            $trans->allow_commit();
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//Delete_UserInfoCompetence

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
     *          - My Competence
     *                  --> dataid.     Id Info Competence
     *                  --> companies.  Array.
     *                                  [level three]
     *                                          --> levelThree
     *                                          --> levelTwo
     *                                          --> levelOne
     *                                          --> levelOne
     *                                          --> roles   .   Array
     *                                                          [id]    --> Job Role Name
     *                                          --> path    .   Hierarchy Structure Path
     *                  --> generics.   Array.
     *                                  [id]    --> Job Role Name.
     */
    private static function AddContent_CompetenceTable($my_competence,$user_id) {
        /* Variables    */
        $content        = '';
        $color          = null;
        $url_deleted    = null;

        try {
            $content .= html_writer::start_tag('div',array('class' => 'competence_table'));
                if ($my_competence->companies) {
                    foreach ($my_competence->companies as $company) {
                        $content .= html_writer::start_div('competence_table_row ' . $color);
                            /* Col Zero -- Toggle   */
                            $content .= html_writer::start_div('col_zero');
                            $content .= html_writer::end_div();//col_zero
                            /* Col One  */
                            $content .= html_writer::start_div('col_one');
                                $content .=  $company->path;
                            $content .= html_writer::end_div();//col_one
                            /* Col Two  */
                            $content .= html_writer::start_div('col_two');
                                $content .= implode(', ',$company->roles) . '</br>';
                            $content .= html_writer::end_div();//col_ttwo
                            /* Col Three  */
                            $content .= html_writer::start_div('col_three');
                                /* URL Deleted  */
                                $url_deleted = new moodle_url('/user/profile/field/competence/actions/delete_competence.php',array('id' =>$user_id,'uc' =>$my_competence->dataid,'co' =>$company->levelThree));
                                $content .= '<a href="' . $url_deleted . '" class="lnk_col">' . get_string('lnk_delete','profilefield_competence')  . '</a>';
                            $content .= html_writer::end_div();//col_three
                        $content .= html_writer::end_div();//competence_table_row

                        if ($color == 'r0') {
                            $color = 'r1';
                        }else {
                            $color = 'r0';
                        }//if_color
                    }//for_companies
                }//if_companies
            $content .= html_writer::end_tag('div'); //competence_table


            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_CompetenceTable

    /**
     * @param           $user_id
     * @param           $my_competence
     * @return          string
     * @throws          Exception
     *
     * @creationDate    28/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Display the job roles that are generics
     */
    private static function AddContent_GenericsCompetenceTable($user_id,$my_competence) {
        /* Variables    */
        $content        = '';
        $color          = 'r0';
        $url_deleted    = null;
        $url_edit       = null;
        $lnk_del        = '';
        $generics       = '';

        try {
            /* Get the correct content and attributes   */
            if ($my_competence->generics) {
                $lnk_del    = 'lnk_col';
                $generics   = implode(', ',$my_competence->generics);
            }else {
                $lnk_del    = 'lnk_col lnk_disabled';
                $generics   = '';
            }//if_generics

            $content .= html_writer::start_tag('div',array('class' => 'competence_table'));
                $content .= html_writer::start_div('competence_table_row ' . $color);
                    /* Col Zero -- Toggle   */
                    $content .= html_writer::start_div('col_zero');
                    $content .= html_writer::end_div();//col_zero
                    /* Col One  */
                    $content .= html_writer::start_div('col_one');
                    $content .= html_writer::end_div();//col_one
                    /* Col Two  */
                    $content .= html_writer::start_div('col_two');
                        $content .= $generics;
                    $content .= html_writer::end_div();//col_ttwo
                    /* Col Three  */
                    $content .= html_writer::start_div('col_three');
                        /* URL Edit     */
                        $url_edit    = new moodle_url('/user/profile/field/competence/actions/edit_competence.php',array('id' =>$user_id,'ge' => 1));
                        $content .= '<a href="' . $url_edit . '" class="lnk_col">' . get_string('lnk_edit','profilefield_competence')  . '</a>';
                        /* URL Deleted  */
                        $url_deleted = new moodle_url('/user/profile/field/competence/actions/delete_competence.php',array('id' =>$user_id,'uc' =>$my_competence->dataid,'co' =>0));
                        $content .= '<a href="' . $url_deleted . '" class="' . $lnk_del. '">' . get_string('lnk_delete','profilefield_competence')  . '</a>';
                    $content .= html_writer::end_div();//col_three
                    $content .= html_writer::end_div();//competence_table_row
            $content .= html_writer::end_tag('div'); //competence_table

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_GenericsCompetenceTable
}//compentece