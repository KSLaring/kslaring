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

        try {
            /* Search Criteria  */
            $params = array();
            $params['jr_id'] = $job_role_id;

            /* SQL Instruction  */
            $sql = " SELECT			jr.id,
                                    jr.name,
                                    m.idcounty,
                                    m.idmuni
                     FROM			{report_gen_jobrole}	jr
                        LEFT JOIN	{municipality}		    m     ON m.idmuni = jr.idmuni
                     WHERE          jr.id = :jr_id";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//JobRole_Info

    /**
     * @static
     * @param           $job_role_id        Job Role Identity
     * @return          array               Outcome List
     * @throws          Exception
     *
     * @updateDate      08/10/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get a list of all outcomes available and which of them are connected with a specific job role.
     */
    public static function Get_Outcomes_ConnectedJobRole($job_role_id){
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
     * @param           $job_role           Job Role Data
     * @param           $outcome_list       Outcomes are connected with the job role
     * @throws          Exception
     *
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Insert a new job role
     */
    public static function Insert_JobRole($job_role,$outcome_list) {
        /* Variables    */
        global $DB;

        try {
            $url = new moodle_url('/report/generator/job_role/edit_job_role.php');
            if ($job_role->id = $DB->insert_record('report_gen_jobrole',$job_role)) {
                /* Create all relations */
                $outcome_rel = new stdClass();
                $outcome_rel->modified = $job_role->modified;
                $outcome_rel->jobroleid = $job_role->id;
                $url = new moodle_url('/report/generator/job_role/edit_job_role.php',array('id'=>$job_role->id));

                if ($outcome_list) {
                    foreach ($outcome_list as $outcome) {
                        $outcome_rel->outcomeid = $outcome;
                        if (!$DB->insert_record('report_gen_outcome_jobrole',$outcome_rel)) {
                            print_error('error_insert_job_role', 'report_generator', $url);
                        }
                    }//for_select_outcomes
                }//if_outcome_list
            }else {
                print_error('error_insert_job_role', 'report_generator', $url);
            }//if-else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Insert_JobRole

    /**
     * @static
     * @param           $job_role           Job Role Data
     * @param           $outcome_list       Outcomes are connected to job role.
     * @return          bool
     * @throws          Exception
     *
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update job role data
     */
    public static function Update_JobRole($job_role,$outcome_list) {
        /* Variables    */
        global $DB;

        try {
            if ($DB->update_record('report_gen_jobrole',$job_role)) {
                /* If it has outcomes selected
                   First   --> Delete all relations
                Second  --> Create new relations */
                $DB->delete_records_select('report_gen_outcome_jobrole','jobroleid='.$job_role->id);

                $outcome_rel = new stdClass();
                $outcome_rel->modified = $job_role->modified;
                $outcome_rel->jobroleid = $job_role->id;
                $url = new moodle_url('/report/generator/job_role/edit_job_role.php',array('id'=>$job_role->id));

                if ($outcome_list) {
                    foreach ($outcome_list as $outcome) {
                        $outcome_rel->outcomeid = $outcome;
                        if (!$DB->insert_record('report_gen_outcome_jobrole',$outcome_rel)) {
                            print_error('error_updating_job_role', 'report_generator', $url);
                        }
                    }//for_select_outcomes
                }//if_outcomelist

                return true;
            }else {
                return false;
            }
        }catch (Exception $ex) {
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

        try {
            $url = new moodle_url('/report/generator/job_role/job_role.php');
            if ($DB->delete_records('report_gen_jobrole',array('id'=>$job_role_id))) {
                /* Remove all outcomes connected */
                $DB->delete_records_select('report_gen_outcome_jobrole','jobroleid='.$job_role_id);
            }else {
                print_error('error_deleting_job_role', 'report_generator', $url);
            }//if_else
        }catch (Exception $ex) {
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
}//class_job_role

