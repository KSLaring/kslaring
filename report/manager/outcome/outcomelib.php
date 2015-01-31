<?php
/**
 * Library code for the Outcome .
 *
 * @package         report
 * @subpackage      manager/outcome
 * @copyright       2010 eFaktor
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    08/10/2014
 * @author          eFaktor     (fbv)
 *
 */

class outcome {
    /*********************/
    /* PUBLIC FUNCTIONS  */
    /*********************/

    /**
     * @static
     * @return          array       List of all outcomes and their job roles connected with them.
     * @throws          Exception
     *
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get a list of all outcomes and their job roles connected with them.
     */
    public static function Outcomes_With_JobRoles() {
        /* Variables    */
        global $DB;
        $outcome_list = array();

        try {
            /* SQL Instruction */
            $sql = " SELECT       go.id,
                                  go.fullname,
                                  jr.jobrolename  as 'jobroles',
                                  oex.id          as 'expirationid',
                                  oex.expirationperiod
                     FROM         {grade_outcomes}  go
                        LEFT JOIN (SELECT   GROUP_CONCAT(job.name
                                                         ORDER BY job.name ASC
                                                         SEPARATOR ', ') as 'jobrolename',
                                            ojrel.outcomeid
                                   FROM     {report_gen_outcome_jobrole} ojrel
                                      JOIN  {report_gen_jobrole}         job    ON  ojrel.jobroleid = job.id
                                   GROUP BY ojrel.outcomeid
                                  ) jr
                                    ON go.id = jr.outcomeid
                        LEFT JOIN {report_gen_outcome_exp} oex  ON   go.id = oex.outcomeid
                     WHERE    go.courseid IS NULL
                        OR    go.courseid = 0
                     ORDER BY go.fullname ASC ";

            /* Execute */
            if ($rdo = $DB->get_records_sql($sql)) {
                foreach ($rdo as $field) {
                    $outcome_list[$field->id] = $field;
                }//for
            }//if_rdo

            return $outcome_list;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Outcomes_With_JobRoles

    /**
     * @static
     * @param           $exp_id
     * @return          bool
     * @throws          Exception
     *
     * @creationdate    14/09/2012
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the expiration period connected with a specific outcome.
     */
    public static  function Outcome_Expiration($exp_id) {
        /* Variables    */
        global $DB;

        try {
            if ($rdo = $DB->get_record('report_gen_outcome_exp',array('id'=>$exp_id))) {
                return $rdo->expirationperiod;
            }else {
                return false;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Outcome_Expiration

    /**
     * @static
     * @param           $outcome_id     Outcome Identity
     * @return          array           Job Role List
     * @throws          Exception
     *
     * @updateDate      08/10/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get a list of all job roles available and which of them are connected with a specific outcome.
     */
    public static function Get_JobRoles_ConnectedOutcome($outcome_id) {
        /* Variables    */
        global $DB;
        $job_roles_list = array();
        $roles_selected = array();

        try {
            /* Params  */
            $params = array();
            $params['outcome'] = $outcome_id;

            /* SQL Instruction */
            $sql = " SELECT        	jr.id,
                                    jr.name,
                                    ojr.jobroleid
                     FROM          	{report_gen_jobrole}  		  jr
                        LEFT JOIN	{report_gen_outcome_jobrole}  ojr   ON 	ojr.jobroleid = jr.id
                                                                        AND	ojr.outcomeid = :outcome
                     ORDER BY jr.name ASC ";

            /* Execute */
            if ($rdo = $DB->get_records_sql($sql,$params)) {
                foreach ($rdo as $field) {
                    $job_roles_list[$field->id] = $field->name;
                    if ($field->jobroleid) {
                        $roles_selected[] = $field->id;
                    }
                }//for
            }//if_Rdo

            return array($job_roles_list,$roles_selected);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_JobRoles_ConnectedOutcome

    /**
     * @static
     * @param           $outcome        Outcome Data.
     * @param           $role_list      Job role are connected with
     * @throws          Exception
     *
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Insert a new outcome
     */
    public static function Insert_Outcome($outcome,$role_list){
        /* Variables    */
        global $DB;

        try {
            $url = new moodle_url('/report/manager/outcome/edit_outcome.php');
            if ($outcome->id = $DB->insert_record('report_gen_outcome_exp',$outcome)) {
                $job_role_sel               = new stdClass();
                $job_role_sel->modified     = $outcome->modified;
                $job_role_sel->outcomeid    = $outcome->outcomeid;

                /* First --> Clean old relations */
                $DB->delete_records_select('report_gen_outcome_jobrole','outcomeid='.$outcome->outcomeid);
                /* Second --> Add new relations. */
                foreach ($role_list as $rol) {
                    $job_role_sel->jobroleid = $rol;
                    if (!$DB->insert_record('report_gen_outcome_jobrole',$job_role_sel)) {
                        print_error('error_updating_outcome_job_role', 'report_manager', $url);
                    }
                }//for
            }else {
                print_error('error_updating_outcome_job_role', 'report_manager', $url);
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Insert_Outcome

    /**
     * @static
     * @param           $outcome        Outcome data
     * @param           $role_list      Job Roles are connected with the outcome
     * @return          bool
     * @throws          Exception
     *
     * @updateDate      08/10/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Update outcome data
     */
    public static function Update_Outcome($outcome,$role_list){
        /* Variables    */
        global $DB;

        try {
            if ($DB->update_record('report_gen_outcome_exp',$outcome)) {
                /* First --> Clean old relations */
                $DB->delete_records_select('report_gen_outcome_jobrole','outcomeid='.$outcome->outcomeid);

                /* Second --> Add new relations */
                $job_role_sel               = new stdClass();
                $job_role_sel->modified     = $outcome->modified;
                $job_role_sel->outcomeid    = $outcome->outcomeid;

                $url = new moodle_url('/report/manager/outcome/edit_outcome.php');

                foreach ($role_list as $rol) {
                    $job_role_sel->jobroleid = $rol;
                    if (!$DB->insert_record('report_gen_outcome_jobrole',$job_role_sel)) {
                        print_error('error_updating_outcome_job_role', 'report_manager', $url);
                    }
                }//for

                return true;
            }else {
                return false;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Update_Outcome

    /**
     * @static
     * @param           $outcome_list       Outcome list
     * @return          html_table
     *
     * @updateDate      08/10/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Draw a table which contains all outcomes available
     */
    public static function Outcomes_Table($outcome_list) {
        /* Variables    */
        $context = CONTEXT_SYSTEM::instance();
        $can_edit = has_capability('report/manager:edit', $context);
        /* Column Table */
        $str_fullname           = get_string('fullname');
        $str_expiration_period  = get_string('expiration_period', 'report_manager');
        $str_job_roles          = get_string('job_roles_for_outcome', 'report_manager');
        $str_edit               = get_string('edit');

        /* Create Table */
        $table = new html_table();
        $table->head        = array($str_fullname, $str_expiration_period, $str_job_roles, $str_edit);
        $table->colclasses  = array($str_fullname, $str_expiration_period, $str_job_roles, $str_edit);
        $table->attributes  = array('width' => '60%');

        foreach ($outcome_list as $outcome) {
            global $OUTPUT;

            /* Rows */
            $row = array();
            /* Buttons */
            $buttons = array();

            /* Fullname Column */
            $row[] = $outcome->fullname;
            /* Expiration Period Col */
            $row[] = $outcome->expirationperiod;
            /* Job Roles Col */
            $row[] = $outcome->jobroles;
            /* Edit Col */
            if ($can_edit) {
                /* Edit Button */
                $url_edit = new moodle_url('/report/manager/outcome/edit_outcome.php',array('id'=>$outcome->id,'expid'=>$outcome->expirationid));
                $buttons[] = html_writer::link($url_edit,
                                               html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
                                               'alt'=>get_string('edit'),
                                               'class'=>'iconsmall')),
                                               array('title'=>get_string('edit')));

                $row[] = implode('',$buttons);
            }else {
                $row[] = '';
            }//if_can_edit

            /* Add Row */
            $table->data[] = $row;
        }//for

        return $table;
    }//Outcomes_Table
}//class_outcome