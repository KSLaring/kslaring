<?php
/**
 * Library code for the Job Role .
 *
 * @package         report
 * @subpackage      generator/job_role
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    08/10/2014
 * @author          eFaktor     (fbv)
 *
 */

class job_role {
    /*********************/
    /* PUBLIC FUNCTIONS  */
    /*********************/


    /**
     * @static
     * @param           $level
     * @param           $county
     * @return          array
     * @throws          Exception
     *
     * @creationDate    05/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the Structure Level sort by county
     */
    public static function GetStructureLevel_By_County($level,$county = null) {
        /* Variables    */
        global $DB;
        $structure_level = array();

        try {
            $structure_level[0] = get_string('select_level_list','report_generator');

            /* Search Criteria  */
            $params = array();
            $params['level']    = $level;
            $params['county']   = $county;

            /* SQL Instruction  */
            $sql = " SELECT		concat(idcounty,'_',id) as 'ref',
                                name
                     FROM		{report_gen_companydata}
                     WHERE		hierarchylevel = :level
                      ";

            if ($county) {
                $sql .= " AND idcounty = :county ";
            }//if_county

            /* Order By     */
            $sql .= " ORDER BY	idcounty,name ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $company) {
                    $structure_level[$company->ref] = $company->name;
                }//for_company
            }//if_rdo

            return $structure_level;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetFirstLevel_By_County

    /**
     * @static
     * @param           $level
     * @return          array
     * @throws          Exception
     *
     * @creationDate    05/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the structure level sort by parent
     */
    public static function GetStructureLevel_By_Parent($level) {
        /* Variables    */
        global $DB;
        $structure = array();

        try {
            $structure[0] = get_string('select_level_list','report_generator');

            /* Search Criteria  */
            $params = array();
            $params['level']    = $level;

            /* SQL Instruction  */
            $sql = " SELECT		concat('P',cr.parentid,'_',c.id) as 'ref',
                                c.name
                     FROM		{report_gen_companydata}			c
                        JOIN	{report_gen_company_relation}		cr		ON cr.companyid = c.id
                     WHERE		c.hierarchylevel = :level
                     ORDER BY	cr.parentid, c.name ASC";

            /* Execute      */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $company) {
                    $structure[$company->ref] = $company->name;
                }//for_company
            }//if_rdo

            return $structure;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetStructureLevel_By_Parent

    /**
     * @static
     * @param           $county_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    06/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the companies connected to a specific county
     */
    public static function GetCompaniesCounty($county_id) {
        /* Variables    */
        global $DB;
        $company_lst = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['idcounty'] =$county_id;

            /* Execute  */
            $rdo = $DB->get_records('report_gen_companydata',$params,'name','id,name');
            if ($rdo) {
                foreach ($rdo as $company) {
                    $company_lst[$company->id] = $company->name;
                }//for_company
            }//if_rdo

            return $company_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCompaniesCounty

    /**
     * @static
     * @return      array       List of all job roles and their outcomes connected with them.
     * @throws      Exception
     *
     * @updateDate  08/10/2014
     * @author      efaktor     (fbv)
     *
     * Description
     * Get a list of all job roles and their outcomes connected with them.
     */
    public static function JobRole_With_Outcomes(){
        /* Variables    */
        global $DB;
        $job_roles = array();

        try {
            /* SQL Instruction */
            $sql = " SELECT		jr.id,
                                jr.name,
                                oc.outcomename as 'outcome_name'
                     FROM  		{report_gen_jobrole} 			jr
                        LEFT JOIN (SELECT     GROUP_CONCAT(go.fullname
                                                           ORDER BY go.fullname ASC
                                                           SEPARATOR ', '
                                                           ) as 'outcomename',
                                              ojrel.jobroleid
                                   FROM     {report_gen_outcome_jobrole}  ojrel
                                      JOIN  {grade_outcomes}              go    ON  ojrel.outcomeid = go.id
                                   GROUP BY ojrel.jobroleid
                                  ) oc
                                    ON jr.id = oc.jobroleid
                     ORDER BY	jr.name ASC ";

            /* Execute */
            if ($rdo = $DB->get_records_sql($sql)) {
                foreach ($rdo as $field) {
                    $job_roles[$field->id] = $field;
                }//for_rdo
            }//if_rdo

            return $job_roles;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//JobRole_With_Outcomes

    /**
     * @static
     * @param           $job_role_id
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    21/08/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the information connected with the Job Role
     */
    public static function JobRole_Info($job_role_id) {
        /* Variables    */
        global $DB;
        $jr_info        = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['jr_id'] = $job_role_id;

            /* SQL Instruction  */
            $sql = " SELECT			jr.id,
                                    jr.name,
                                    jr_rel.idcounty,
                                    jr_rel.levelone,
                                    jr_rel.leveltwo,
                                    GROUP_CONCAT(DISTINCT jr_rel.levelthree ORDER BY jr_rel.levelthree SEPARATOR ',') as 'levelthree'
                     FROM			{report_gen_jobrole}				jr
                        JOIN		{report_gen_jobrole_relation}		jr_rel	ON	jr_rel.jobroleid = jr.id
                     WHERE          jr.id = :jr_id ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Job Role Info    */
                $jr_info = new stdClass();
                $jr_info->id            = $rdo->id;
                $jr_info->name          = $rdo->name;
                $jr_info->county        = $rdo->idcounty;
                $jr_info->levelOne      = $rdo->levelone;
                $jr_info->levelTwo      = $rdo->leveltwo;
                $jr_info->levelThree    = $rdo->levelthree;
            }//if_rdo

            return $jr_info;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//JobRole_Info

    /**
     * @static
     * @param           int $job_role_id
     * @return              array
     * @throws              Exception
     *
     * @updateDate          06/11/2014
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get a list of all outcomes available and which of them are connected with a specific job role.
     */
    public static function Get_Outcomes_ConnectedJobRole($job_role_id = 0){
        /* Variables    */
        global $DB;
        $out_job_roles  = array();
        $out_selected   = array();

        try {
            /* Params  */
            $params = array();
            $params['jobrole'] = $job_role_id;

            /* SQL Instruction */
            $sql = " SELECT 	   	go.id,
                                    go.fullname,
                                    ojr.outcomeid
                     FROM	  	   	{grade_outcomes} 				go
                        LEFT JOIN	{report_gen_outcome_jobrole}	ojr ON 	ojr.outcomeid = go.id
                                                                        AND	ojr.jobroleid = :jobrole
                     ORDER BY		go.fullname ASC ";

            /* Execute */
            if ($rdo = $DB->get_records_sql($sql,$params)) {
                foreach ($rdo as $field) {
                    $out_job_roles[$field->id] = $field->fullname;
                    if ($field->outcomeid) {
                        $out_selected[] = $field->id;
                    }//if_selected
                }//for
            }//if_rdo

            return array($out_job_roles,$out_selected);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_Outcomes_ConnectedJobRole

    /**
     * @static
     * @param           $job_role
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    08/01/2013
     * @updateDate      08/10/2014
     * @auhtor          eFaktor     (fbv)
     *
     * Description
     * Return if the job role already exists.
     */
    public static function JobRole_Exists($job_role) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria */
            $params = array();
            $params['job_role'] = $job_role;

            /* SQL Instruction */
            $sql = " SELECT   id
                     FROM     {report_gen_jobrole}
                     WHERE    name = :job_role ";

            /* Execute */
            if ($rdo = $DB->get_records_sql($sql,$params)) {
                return true;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//JobRole_Exists

    /**
     * @static
     * @param               $job_role_id        Job Role identity
     * @param               string $field       Type of Company
     * @return              int                 Number of users
     * @throws              Exception
     *
     * @updateDate          08/10/2014
     * @author              eFaktor         (fbv)
     *
     * Description
     * Return the number of users that are connected with a specific job role.
     */
    public static function Users_Connected_JobRole($job_role_id, $field = REPORT_GENERATOR_COMPANY_FIELD) {
        /* Variables    */
        global $DB;
        $count = 0;

        try {
            /* Research Criteria */
            $params = array();
            $params['job_role_id'] = $job_role_id;
            $params['field'] = $field;

            /* SQL Instruction   */
            $sql = " SELECT 	COUNT(DISTINCT uid.id) as 'count'
                     FROM		{user_info_data} 	uid
                        JOIN	{user_info_field} 	uif ON uid.fieldid = uif.id
                     WHERE 		uid.data     = :job_role_id
                        AND     uif.datatype = :field ";

            /* Execute */
            if ($rdo = $DB->get_record_sql($sql,$params)) {
                $count = $rdo->count;
            }//if_Rdo

            return $count;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Users_Connected_JobRole

    /**
     * @static
     * @param           $data
     * @throws          Exception
     *
     * @updateDate      06/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Insert a new job role
     */
    public static function Insert_JobRole($data) {
        /* Variables    */
        global $DB;
        $job_role           = null;
        $outcome_rel        = null;
        $set_outcome        = REPORT_GENERATOR_OUTCOME_LIST;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* Get the data to save */
            /* First Job Role       */
            $job_role = new stdClass();
            $job_role->name     = $data->job_role_name;
            $job_role->modified = time();

            /* Create Job Role  */
            $job_role->id = $DB->insert_record('report_gen_jobrole',$job_role);

            /* Outcome List     */
            if (isset($data->$set_outcome) && ($data->$set_outcome)) {
                /* Create all relations */
                $outcome_rel = new stdClass();
                $outcome_rel->modified  = $job_role->modified;
                $outcome_rel->jobroleid = $job_role->id;
                foreach ($data->$set_outcome as $outcome) {
                    $outcome_rel->outcomeid = $outcome;
                    $DB->insert_record('report_gen_outcome_jobrole',$outcome_rel);
                }//for_select_outcomes
            }//if_outcome_list

            /* Company Relations    */
            /* Level One */
            $levelOne   = null;
            if (isset($data->level_one) && ($data->level_one)) {
                $levelOne = $data->level_one;
            }//if_levelOne
            /* Level Two    */
            $levelTwo   = null;
            if (isset($data->level_two) && ($data->level_two)) {
                $levelTwo = $data->level_two;
            }//if_levelOne
            /* Level Three      */
            $levelThree = null;
            if (isset($data->hidden_level_three) && ($data->hidden_level_three)) {
                $levelThree = explode(',',$data->hidden_level_three);
            }//if_levelOne

            self::AddRelation_JobRoleLevel($job_role->id,$data->county,$levelThree,$levelTwo,$levelOne);

            $trans->allow_commit();
        }catch (Exception $ex) {
            $trans->rollback($ex);
            throw $ex;
        }//try_catch
    }//Insert_JobRole

    /**
     * @static
     * @param           $data
     * @throws          Exception
     *
     * @updateDate      07/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update job role data
     */
    public static function Update_JobRole($data) {
        /* Variables    */
        global $DB;
        $job_role           = null;
        $outcome_rel        = null;
        $set_outcome        = REPORT_GENERATOR_OUTCOME_LIST;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* Get the data to save */
            /* First Job Role       */
            $job_role = new stdClass();
            $job_role->id       = $data->id;
            $job_role->name     = $data->job_role_name;
            $job_role->modified = time();

            /* Update Job Role  */
            $DB->update_record('report_gen_jobrole',$job_role);

            /* Outcome List */
            if (isset($data->$set_outcome) && ($data->$set_outcome)) {
                /* First Deleted all the relations between Job role and Outcomes    */
                $DB->delete_records_select('report_gen_outcome_jobrole','jobroleid='.$job_role->id);
                /* Create all relations */
                $outcome_rel = new stdClass();
                $outcome_rel->modified  = $job_role->modified;
                $outcome_rel->jobroleid = $job_role->id;
                foreach ($data->$set_outcome as $outcome) {
                    $outcome_rel->outcomeid = $outcome;
                    $DB->insert_record('report_gen_outcome_jobrole',$outcome_rel);
                }//for_select_outcomes
            }//outcome_list

            /* Company Relations    */
            /* First Deleted all the relations between Job Role anc Companies   */
            $DB->delete_records_select('report_gen_jobrole_relation','jobroleid='.$job_role->id);
            /* Create the Company relations */
            /* Level One */
            $levelOne   = null;
            if (isset($data->level_one) && ($data->level_one)) {
                $levelOne = $data->level_one;
            }//if_levelOne
            /* Level Two    */
            $levelTwo   = null;
            if (isset($data->level_two) && ($data->level_two)) {
                $levelTwo = $data->level_two;
            }//if_levelOne
            /* Level Three      */
            $levelThree = null;
            if (isset($data->hidden_level_three) && ($data->hidden_level_three)) {
                $levelThree = explode(',',$data->hidden_level_three);
            }//if_levelOne

            self::AddRelation_JobRoleLevel($job_role->id,$data->county,$levelThree,$levelTwo,$levelOne);

            $trans->allow_commit();
        }catch (Exception $ex) {
            $trans->rollback($ex);
            throw $ex;
        }//try_catch
    }//Update_JobRole

    /**
     * @static
     * @param       $job_role_id        Job Role Identity
     * @throws      Exception
     *
     * @updateDate  08/10/2014
     * @author      eFaktor     (fbv)
     *
     * Description
     * Delete the job role from database.
     */
    public static function Delete_JobRole($job_role_id){
        /* Variables    */
        global $DB;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* First Delete Job Roles                   */
            $DB->delete_records('report_gen_jobrole',array('id'=>$job_role_id));

            /* Delete Jbo Roles and Outcome Connections */
            $DB->delete_records_select('report_gen_outcome_jobrole','jobroleid='.$job_role_id);

            /* Delete Job Roles and Company Relations   */
            $DB->delete_records_select('report_gen_jobrole_relation','jobroleid='.$job_role_id);

            $trans->allow_commit();
        }catch (Exception $ex) {
            $trans->rollback($ex);
            throw $ex;
        }//try_catch
    }//Delete_JobRole

    /**
     * @static
     * @param           $job_roles      Job roles list
     * @return          html_table
     *
     * @updateDate      12/09/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Draw a table which contains all job roles available.
     */
    public static function JobRoles_table($job_roles){
        /* Variables    */
        global $CFG;
        $context        = CONTEXT_SYSTEM::instance();
        $can_edit       = has_capability('report/generator:edit', $context);
        /* Column Tables    */
        $str_fullname  = get_string('fullname');
        $str_outcomes  = get_string('outcomes_for_job_role', 'report_generator');
        $str_edit      = get_string('edit');

        /* Create Table */
        $table              = new html_table();
        $table->head        = array($str_fullname, $str_outcomes, $str_edit);
        $table->colclasses  = array($str_fullname, $str_outcomes, $str_edit);
        $table->attributes  = array('width' => '60%');

        foreach ($job_roles as $job_role) {
            global $OUTPUT;

            /* Rows */
            $row = array();
            /* Buttons */
            $buttons = array();

            /* Fullname Col */
            $row[] = $job_role->name;
            /* Outcomes Col */
            $row[] = $job_role->outcome_name;
            /* Edit Col */
            if ($can_edit) {
                /* Edit Button */
                $url_edit = new moodle_url('/report/generator/job_role/edit_job_role.php',array('id'=>$job_role->id));
                $buttons[] = html_writer::link($url_edit,
                                               html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
                                               'alt'=>get_string('edit'),
                                               'class'=>'iconsmall')),
                                               array('title'=>get_string('edit_this_job_role', 'report_generator')));

                /* Delete Button */
                $url_delete = new moodle_url('/report/generator/job_role/delete_job_role.php',array('id'=>$job_role->id));
                $buttons[] = html_writer::link($url_delete,
                                               html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'),
                                               'alt'=>get_string('delete'),
                                               'class'=>'iconsmall')),
                                               array('title'=>get_string('delete_this_job_role', 'report_generator')));

                $row[] = implode(' ',$buttons);
            }else {
                $row[] = '';
            }//if_can_edit

            /* Add row */
            $table->data[] = $row;
        }//for_job_roles

        return $table;
    }//JobRoles_table

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @static
     * @param           $job_role
     * @param           $county
     * @param           $levelThree
     * @param           $levelTwo
     * @param           $levelOne
     * @throws          Exception
     *
     * @creationDate    06/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the connection between the job role and the company structure
     */
    private static function AddRelation_JobRoleLevel($job_role,$county,$levelThree,$levelTwo,$levelOne) {
        /* Variables    */
        global $DB;
        $index = 0;
        $ref = null;

        try {
            /* New Instance */
            $instance = new stdClass();
            $instance->jobroleid    = $job_role;
            $instance->idcounty     = $county;
            $instance->modified     = time();
            /* Level One        */
            if ($levelOne) {
                $index = strripos($levelOne,"_");
                $instance->levelone = substr($levelOne,$index+1);
            }///if_levelOne
            /* Level Two        */
            if ($levelTwo) {
                $index = strripos($levelTwo,"_");
                $instance->leveltwo = substr($levelTwo,$index+1);
            }//if_levelTwo

            /* Level Three      */
            if ($levelThree) {
                foreach ($levelThree as $company) {
                    $ref = str_replace('#','',$company);
                    /* Get Company ID   */
                    $index = strripos($ref,"_");
                    $instance->levelthree    = substr($ref,$index+1);

                    /* Insert   */
                    $DB->insert_record('report_gen_jobrole_relation',$instance);
                }//for_company
            }else {
                /* Insert   */
                $DB->insert_record('report_gen_jobrole_relation',$instance);
            }//if_levelThree
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddRelation_JobRoleLevel

}//class_job_role

