<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Library code for the Job Role .
 *
 * @package         report
 * @subpackage      manager/job_role
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    08/10/2014
 * @author          eFaktor     (fbv)
 *
 * @updateDate      26/01/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Update to Level Zero
 * Add the connection between the Job role and the Level zero, One, Two and Three
 *
 */
define('REPORT_MANAGER_JOB_ROLE_FIELD', 'rgjobrole');
define('REPORT_JR_COMPANY_STRUCTURE_LEVEL','level_');
define('REPORT_JR_MANAGER_OUTCOME_LIST', 'jr_outcome_list');

class job_role {
    /*********************/
    /* PUBLIC FUNCTIONS  */
    /*********************/

    /**
     * @param           $myAccess
     * @param           $jobRole
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    15/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user has access to this job role
     */
    public static function CheckJobRoleAccess($myAccess,$jobRole) {
        /* Variables    */
        global $DB;
        $params     = null;
        $sql        = null;
        $sqlLevel   = null;
        $sqlExtra   = null;
        $rdo        = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['jobrole'] = $jobRole;

            /* SQL Instruction  */
            $sql = " SELECT		jr.id
                     FROM  		{report_gen_jobrole}		    jr
                        JOIN	{report_gen_jobrole_relation}	jr_rel	ON jr_rel.jobroleid = jr.id";

            /* CRITERIA */
            /* Add search criteria  */
            foreach ($myAccess as $infoLevel) {
                /* Add Level Zero   */
                $sqlLevel = "jr_rel.levelzero IN ($infoLevel->levelZero) ";

                /* Level One    */
                if ($infoLevel->levelOne) {
                    /* Add Level One */
                    $sqlLevel .= " AND jr_rel.levelone IN ($infoLevel->levelOne)";

                    /* Level Two*/
                    if ($infoLevel->levelTwo) {
                        /* Add Level Two */
                        $sqlLevel .= " AND jr_rel.leveltwo IN ($infoLevel->levelTwo)";

                        /* Level Three */
                        if ($infoLevel->levelThree) {
                            /* Add Level Three  */
                            $sqlLevel .= " AND jr_rel.levelthree IN ($infoLevel->levelThree)";
                        }//levelThree
                    }//levelTwo

                    /* Add Criteria */
                    if ($sqlExtra) {
                        $sqlExtra .= ' OR ';
                    }

                    $sqlExtra .= '(' . $sqlLevel . ')';
                }//levelOne
            }//for

            $sql .= " AND (" . $sqlExtra . ")";
            $sql .= " WHERE jr.id = :jobrole ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//MyJobroles

    /**
     * @static
     * @param       $superUser
     * @param       $myAccess
     *
     * @return      array       List of all job roles and their outcomes connected with them.
     * @throws      Exception
     *
     * @updateDate  08/10/2014
     * @author      efaktor     (fbv)
     *
     * Description
     * Get a list of all job roles and their outcomes connected with them.
     */
    public static function JobRole_With_Outcomes($superUser=false,$myAccess = null){
        /* Variables    */
        global $DB;
        $job_roles  = array();
        $sql        = null;
        $rdo        = null;
        $info       = null;
        $sqlExtra   = null;
        $sqlLevel   = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		jr.id,
                                jr.name,
                                jr.industrycode,
                                oc.outcomename as 'outcome_name'
                     FROM  		{report_gen_jobrole}				jr
                        -- OUTCOMES
                        LEFT JOIN (SELECT   GROUP_CONCAT(go.fullname ORDER BY go.fullname ASC SEPARATOR ', ') as 'outcomename',
                                            ojrel.jobroleid
                                   FROM     	{report_gen_outcome_jobrole}  ojrel
                                        JOIN  	{grade_outcomes}              go    ON  ojrel.outcomeid = go.id
                                   GROUP BY ojrel.jobroleid 
                                   ) oc ON jr.id = oc.jobroleid
                         ";

            /* Only Job Roles Connected with super user */
            if ($superUser) {
                $sql .= " JOIN	{report_gen_jobrole_relation}	jr_rel	ON jr_rel.jobroleid = jr.id ";

                /* Add search criteria  */
                foreach ($myAccess as $infoLevel) {
                    /* Add Level Zero   */
                    $sqlLevel = "jr_rel.levelzero IN ($infoLevel->levelZero) ";

                    /* Level One    */
                    if ($infoLevel->levelOne) {
                        /* Add Level One */
                        $sqlLevel .= " AND jr_rel.levelone IN ($infoLevel->levelOne)";

                        /* Level Two*/
                        if ($infoLevel->levelTwo) {
                            /* Add Level Two */
                            $sqlLevel .= " AND jr_rel.leveltwo IN ($infoLevel->levelTwo)";

                            /* Level Three */
                            if ($infoLevel->levelThree) {
                                /* Add Level Three  */
                                $sqlLevel .= " AND jr_rel.levelthree IN ($infoLevel->levelThree)";
                            }//levelThree
                        }//levelTwo

                        /* Add Criteria */
                        if ($sqlExtra) {
                            $sqlExtra .= ' OR ';
                        }

                        $sqlExtra .= '(' . $sqlLevel . ')';
                    }//levelOne
                }//for

                if ($sqlExtra) {
                    $sql .= " AND (" . $sqlExtra . ")";
                }
            }//if_superUser


            /* Order By */
            $sql .= " ORDER BY	jr.industrycode, jr.name ASC ";


            /* Execute */
            if ($rdo = $DB->get_records_sql($sql)) {
                foreach ($rdo as $instance) {
                    /* Info Job Role    */
                    $info = new stdClass();
                    $info->id           = $instance->id;
                    $info->name         = $instance->name;
                    $info->industrycode = $instance->industrycode;
                    $info->outcome_name = $instance->outcome_name;

                    /* Add */
                    $job_roles[$instance->id] = $info;
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
     *
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
        global $DB,$USER;
        $jrInfo     = null;
        $params     = null;
        $sql        = null;
        $rdo        = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['jr_id']    = $job_role_id;
            $params['user']     = $USER->id;

            /* SQL Instruction  */
            $sql = " SELECT		jr.id,
                                jr.name,
                                jr.industrycode,
                                jr_rel.levelzero,
                                GROUP_CONCAT(DISTINCT jr_rel.levelone ORDER BY jr_rel.levelone SEPARATOR ',') 		as 'levelone',
                                GROUP_CONCAT(DISTINCT jr_rel.leveltwo ORDER BY jr_rel.leveltwo SEPARATOR ',') 		as 'leveltwo',
                                GROUP_CONCAT(DISTINCT jr_rel.levelthree ORDER BY jr_rel.levelthree SEPARATOR ',') 	as 'levelthree'
                     FROM		{report_gen_jobrole}				jr
                        JOIN	{report_gen_jobrole_relation}		jr_rel	ON	jr_rel.jobroleid = jr.id
                     WHERE      jr.id = :jr_id
                     GROUP BY   jr_rel.levelzero,jr.id ";


            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                /* Job Role Info    */
                $jrInfo = new stdClass();
                $jrInfo->id                = $rdo->id;
                $jrInfo->name              = $rdo->name;
                $jrInfo->industry_code     = $rdo->industrycode;
                $jrInfo->levelZero         = $rdo->levelzero;
                $jrInfo->levelOne          = $rdo->levelone;
                $jrInfo->levelTwo          = $rdo->leveltwo;
                $jrInfo->levelThree        = $rdo->levelthree;
            }//if_rdo

            return $jrInfo;
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
                        $out_selected[$field->outcomeid] = $field->outcomeid;
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
     * @param           $job_role_name
     * @param           $industry_code
     * @param           null $job_rol_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    08/01/2013
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return if the job role already exists.
     *
     * $updateDate      26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Industry Code
     */
    public static function JobRole_Exists($job_role_name,$industry_code,$job_rol_id=null) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria */
            $params = array();
            $params['job_role']         = $job_role_name;
            $params['industry_code']    = $industry_code;

            /* SQL Instruction */
            $sql = " SELECT   id
                     FROM     {report_gen_jobrole}
                     WHERE    name          = :job_role
                        AND   industrycode  = :industry_code";

            /* Add extra checking   */
            if ($job_rol_id) {
                $params['jr_id'] = $job_rol_id;
                $sql .= ' AND id != :jr_id ';
            }//if_job_role_id

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
     * @param           $jrId
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    26/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if there are users connected with the job role.
     * L5
     */
    public static function Users_Connected($jrId) {
        /* Variables    */
        global $DB;
        $params = null;
        $rdo    = null;
        $sql    = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['jr'] = $jrId;

            /* SQL Instruction  */
            $sql = " SELECT *
                     FROM   {user_info_competence_data}
                     WHERE  FIND_IN_SET(:jr,jobroles) ";
            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//Users_Connected

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
        $set_outcome        = REPORT_JR_MANAGER_OUTCOME_LIST;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* Get the data to save */
            /* First Job Role       */
            $job_role = new stdClass();
            $job_role->name         = $data->job_role_name;
            $job_role->industrycode = $data->industry_code;
            $job_role->modified     = time();

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
            self::AddRelation_JobRoleLevel($job_role->id,$data);

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
        $set_outcome        = REPORT_JR_MANAGER_OUTCOME_LIST;

        /* Start Transaction    */
        $trans = $DB->start_delegated_transaction();
        try {
            /* Get the data to save */
            /* First Job Role       */
            $job_role = new stdClass();
            $job_role->id           = $data->id;
            $job_role->name         = $data->job_role_name;
            $job_role->industrycode = $data->industry_code;
            $job_role->modified     = time();

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
            self::AddRelation_JobRoleLevel($job_role->id,$data);

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
     * @param           $superUser
     *
     * @return          html_table
     *
     * @updateDate      12/09/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Draw a table which contains all job roles available.
     */
    public static function JobRoles_table($job_roles,$superUser=false){
        /* Variables    */
        global $CFG,$OUTPUT;
        $context        = context_system::instance();
        $can_edit       = has_capability('report/manager:edit', $context);
        /* Column Tables    */
        $str_fullname  = get_string('fullname');
        $str_outcomes  = get_string('outcomes_for_job_role', 'report_manager');
        $str_edit      = get_string('edit');

        /* Create Table */
        $table              = new html_table();
        $table->head        = array($str_fullname, $str_outcomes, $str_edit);
        $table->colclasses  = array($str_fullname, $str_outcomes, $str_edit);
        $table->attributes  = array('width' => '60%');

        foreach ($job_roles as $job_role) {
            /* Rows */
            $row = array();
            /* Buttons */
            $buttons = array();

            /* Fullname Col */
            $row[] = $job_role->industrycode . ' - ' . $job_role->name;
            /* Outcomes Col */
            $row[] = $job_role->outcome_name;
            /* Edit Col */
            if (($can_edit) || ($superUser)) {
                /* Edit Button */
                $url_edit = new moodle_url('/report/manager/job_role/edit_job_role.php',array('id' => $job_role->id));
                $buttons[] = html_writer::link($url_edit,
                                               html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
                                               'alt'=>get_string('edit'),
                                               'class'=>'iconsmall')),
                                               array('title'=>get_string('edit_this_job_role', 'report_manager')));

                /* Delete Button */
                $url_delete = new moodle_url('/report/manager/job_role/delete_job_role.php',array('id' => $job_role->id));
                $buttons[] = html_writer::link($url_delete,
                                               html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'),
                                               'alt'=>get_string('delete'),
                                               'class'=>'iconsmall')),
                                               array('title'=>get_string('delete_this_job_role', 'report_manager')));

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
     * @param           $data
     * @throws          Exception
     *
     * @creationDate    06/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create the connection between the job role and the company structure
     */
    private static function AddRelation_JobRoleLevel($job_role,$data) {
        /* Variables    */
        global $DB;
        $level      = null;
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;

        try {
            /* Get Level Zero   */
            $level = REPORT_JR_COMPANY_STRUCTURE_LEVEL . 0;
            if (isset($data->$level) && ($data->$level)) {
                $levelZero = $data->$level;
            }//if_levelZero

            /* Get Level One    */
            $level = REPORT_JR_COMPANY_STRUCTURE_LEVEL . 1;
            if (isset($data->$level) && ($data->$level)) {
                $levelOne = $data->$level;
            }//if_levelOne

            /* Get Level Two    */
            $level = REPORT_JR_COMPANY_STRUCTURE_LEVEL . 2;
            if (isset($data->$level) && ($data->$level)) {
                $levelTwo = $data->$level;
            }//if_levelTwo

            /* Get Level Three  */
            $level = REPORT_JR_COMPANY_STRUCTURE_LEVEL . 3;
            if (isset($data->$level) && ($data->$level)) {
                $levelThree = $data->$level;
            }//if_levelThree

            /* New Instance */
            $instance = new stdClass();
            $instance->modified     = time();
            $instance->jobroleid    = $job_role;
            $instance->levelzero    = $levelZero;
            $instance->levelone     = $levelOne;
            $instance->leveltwo     = $levelTwo;
            $instance->levelthree   = null;
            if ($levelThree) {
                foreach ($levelThree as $company) {
                    if ($company) {
                        $instance->levelthree    = $company;
                    }
                    /* Insert   */
                    $DB->insert_record('report_gen_jobrole_relation',$instance);
                }//for_company
            }else {
                $DB->insert_record('report_gen_jobrole_relation',$instance);
            }//if_levelThree
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddRelation_JobRoleLevel
}//class_job_role

