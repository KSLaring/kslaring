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
     * @param           $outcome_id
     * @param           $addSearch
     * @param           $removeSearch
     * @param           $removeSelected
     *
     * @throws          Exception
     *
     * @creationDate    26/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise the job roles selectors
     */
    public static function Init_JobRoles_Selectors($outcome_id,$addSearch,$removeSearch,$removeSelected) {
        /* Variables    */
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;
        $hashAdd    = null;
        $hashRemove = null;

        try {
            /* Initialise variables */
            $name       = 'job_role_selector';
            $path       = '/report/manager/outcome/js/search.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpOne     = array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%');
            $grpTwo     = array('nomatchingusers', 'moodle', '%%SEARCHTERM%%');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpOne,$grpTwo,$grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
                             );

            /* Super Users - Add Selector       */
            self::Init_JobRoles_AddSelector($outcome_id,$addSearch,$jsModule);
            /* Super Users - Remove Selector    */
            self::Init_JobRoles_RemoveSelector($outcome_id,$removeSearch,$jsModule,$removeSelected);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_JobRoles_Selectors

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
                        LEFT JOIN (SELECT   GROUP_CONCAT(CONCAT(job.industrycode,' - ',job.name) ORDER BY job.industrycode,job.name ASC SEPARATOR ', ') as 'jobrolename',
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
     * @param           $outcome
     * @param           $search
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get job roles connected with outcome
     */
    public static function FindJobRoles_Selector($outcome,$search) {
        /* Variables    */
        global $DB;
        $params     = null;
        $sql        = null;
        $rdo        = null;
        $jobRoles   = array();
        $locate     = '';
        $extra      = null;

        try {
            /* Params  */
            $params = array();
            $params['outcome'] = $outcome;

            /* SQL Instruction  */
            $sql = " SELECT  jr.id,
                             jr.name,
                             jr.industrycode,
                             ojr.jobroleid
                     FROM    	{report_gen_jobrole}  		  jr
                        JOIN	{report_gen_outcome_jobrole}  ojr  ON 	ojr.jobroleid = jr.id
                                                                   AND	ojr.outcomeid = :outcome ";

            /* Search   */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= ") AND (";
                    }
                    $locate .= " LOCATE('" . $str . "',jr.name)
                                 OR
                                 LOCATE('" . $str . "',jr.industrycode) ";
                }//if_search_opt

                $sql .= " 	WHERE ($locate) ";
            }//if_search

            /* Order */
            $sql .= " ORDER BY jr.industrycode, jr.name ASC ";

            /* Execute */
            if ($rdo = $DB->get_records_sql($sql,$params)) {
                $jobRoles[0] = get_string('selected_jobroles','report_manager');
                foreach ($rdo as $field) {
                    $jobRoles[$field->id] = $field->industrycode . ' - ' . $field->name;
                }//for
            }else {
                $jobRoles[0] = get_string('not_sel_jobroles','report_manager');
            }//if_Rdo

            return $jobRoles;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindJobRoles_Selector

    /**
     * @param           $selected
     * @param           $search
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    26/10/2015
     * @author          eFaktor     (Fbv)
     *
     * Description
     * Get potential job roles
     */
    public static function FindPotentialJobRole_Selector($selected,$search) {
        /* Variables    */
        global $DB;
        $params     = null;
        $sql        = null;
        $rdo        = null;
        $jobRoles   = array();
        $locate     = '';
        $extra      = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT  jr.id,
                             jr.name,
                             jr.industrycode
                     FROM    	{report_gen_jobrole}  	jr
                     WHERE   jr.id NOT IN (" . $selected .")";

            /* Search   */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= ") AND (";
                    }
                    $locate .= " LOCATE('" . $str . "',jr.name)
                                 OR
                                 LOCATE('" . $str . "',jr.industrycode) ";
                }//if_search_opt

                $sql .= " 	AND ($locate) ";
            }//if_search

            /* Order */
            $sql .= " ORDER BY jr.industrycode, jr.name ASC ";

            /* Execute */
            if ($rdo = $DB->get_records_sql($sql,$params)) {
                $jobRoles[0] = get_string('av_jobroles','report_manager');
                foreach ($rdo as $field) {
                    $jobRoles[$field->id] = $field->industrycode . ' - ' . $field->name;
                }//for
            }else {
                $jobRoles[0] = get_string('not_sel_jobroles','report_manager');
            }//if_Rdo

            return $jobRoles;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindPotentialJobRole_Selector

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
     *
     * @updateDate      26/10/2015
     * @author          eFaktor     (fbv)
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

                /* Second --> Add new relations. */
                if ($role_list) {
                    foreach ($role_list as $rol) {
                        $job_role_sel->jobroleid = $rol;
                        if (!$DB->insert_record('report_gen_outcome_jobrole',$job_role_sel)) {
                            print_error('error_updating_outcome_job_role', 'report_manager', $url);
                        }
                    }//for
                }//if_role_list
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
     *
     * @updateDate      26/10/2015
     * @author          eFaktor     (fbv)
     */
    public static function Update_Outcome($outcome,$role_list){
        /* Variables    */
        global $DB;

        try {
            if ($DB->update_record('report_gen_outcome_exp',$outcome)) {

                /* Second --> Add new relations */
                $job_role_sel               = new stdClass();
                $job_role_sel->modified     = $outcome->modified;
                $job_role_sel->outcomeid    = $outcome->outcomeid;

                $url = new moodle_url('/report/manager/outcome/edit_outcome.php');

                if ($role_list) {
                    foreach ($role_list as $rol) {
                        $job_role_sel->jobroleid = $rol;
                        if (!$DB->insert_record('report_gen_outcome_jobrole',$job_role_sel)) {
                            print_error('error_updating_outcome_job_role', 'report_manager', $url);
                        }
                    }//for
                }//if_role_list

                return true;
            }else {
                return false;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Update_Outcome

    /**
     * @param           $outcomeId
     * @param           $jobRoles
     * @throws          Exception
     *
     * @creationDate    26/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete job roles connected with outcome
     */
    public static function Delete_JR_Outcome($outcomeId,$jobRoles) {
        /* Variables    */
        global $DB;

        try {
            foreach ($jobRoles as $rol) {
                $DB->delete_records('report_gen_outcome_jobrole',array('outcomeid' => $outcomeId,'jobroleid' => $rol));
            }//for
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Delete_JR_Outcome

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

    /* PRIVATE */

    /**
     * @param           $outcome
     * @param           $search
     * @param           $jsModule
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    22/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise the selector to add job roles
     */
    private static function Init_JobRoles_AddSelector($outcome,$search,$jsModule) {
        /* Variables    */
        global $USER,$PAGE;
        $options    = null;
        $hash       = null;


        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindPotentialJobRole_Selector';
            $options['name']        = 'addselect';
            $options['multiselect'] = true;

            /* Connect Selector User    */
            $hash                     = md5(serialize($options));
            $USER->jrselectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_job_role_selector',
                                          array('addselect',$hash, $outcome,$search,null),
                                          false,
                                          $jsModule
                                         );

            return $hash;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_JobRoles_AddSelector


    /**
     * @param           $outcome
     * @param           $search
     * @param           $jsModule
     * @param           $removeSelected
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    26/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialise the selector to remove job roles
     */
    private static function Init_JobRoles_RemoveSelector($outcome,$search,$jsModule,$removeSelected) {
        /* Variables    */
        global $USER,$PAGE;
        $options    = null;
        $hash       = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindJobRoles_Selector';
            $options['name']        = 'removeselect';
            $options['multiselect'] = true;

            /* Connect Selector User    */
            $hash                     = md5(serialize($options));
            $USER->jrselectors[$hash] = $options;

            /* Supers Users selected to delete  */
            if ($removeSelected) {
                $removeSelected = implode(',',$removeSelected);
            }
            $PAGE->requires->js_init_call('M.core_user.init_job_role_selector',
                                          array('removeselect',$hash, $outcome,$search,$removeSelected),
                                          false,
                                          $jsModule
                                         );

            return $hash;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_JobRoles_RemoveSelector
}//class_outcome