<?php
/**
 * Extra Profile Field Job Role
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/rgjobrole
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    11/11/2014
 * @author          eFaktor     (fbv)
 *
 */

class profile_field_rgjobrole extends profile_field_base {
    public function edit_field_add($m_form) {
        global $PAGE;

        $m_form->addElement('static', 'rgjobrole-description', '', get_string('profile_intro', 'profilefield_rgjobrole'));

        /* Job Role */
        $options = self::GetJobRoles();
        $m_form->addElement('select',$this->inputname,format_string($this->field->name),$options,'disabled');

        /* hidden Level Three  */
        $m_form->addElement('text','hidden_job_role',null,'style="visibility:hidden;height:0px;"');
        $m_form->setType('hidden_job_role',PARAM_TEXT);
        $m_form->setDefault('hidden_job_role',0);
    }//edit_field_add

    /**
     * @param           stdClass $usernew
     * @return          mixed|void
     *
     * @creationDate    12/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save the job role/s selected
     */
    public function edit_save_data($usernew) {
        /* Variables    */
        global $DB;
        $selectedJobRoles   = null;
        $jobRole            = array();
        $jobRoleId          = null;
        $index              = null;


        if (!isset($usernew->{$this->inputname})) {
            // Field not present in form, probably locked and invisible - skip it.
            return;
        }

        /* Get the companies selected   */
        $selectedJobRoles = explode(',',$usernew->hidden_job_role);
        foreach ($selectedJobRoles as $ref) {
            $index      = strripos($ref,"#JR");
            $jobRoleId  = substr($ref,0,$index);
            $index      = strripos($jobRoleId,"#");
            $jobRoleId  = substr($jobRoleId,$index+1);

            $jobRole[$jobRoleId] = $jobRoleId;
        }//for_each_selected

        $data = new stdClass();

        $usernew->{$this->inputname} = implode(',',$jobRole);

        $data->userid  = $usernew->id;
        $data->fieldid = $this->field->id;
        $data->data    = $usernew->{$this->inputname};

        if ($dataid = $DB->get_field('user_info_data', 'id', array('userid' => $data->userid, 'fieldid' => $data->fieldid))) {
            $data->id = $dataid;
            $DB->update_record('user_info_data', $data);
        } else {
            $DB->insert_record('user_info_data', $data);
        }
    }//edit_save_data

    /**
     * @param       mixed       $data
     * @param       stdClass    $datarecord
     * @return      mixed
     *
     * @updateDate  11/11/2014
     * @author      eFaktor     (fbv)
     *
     * Description
     * The data from the form returns the key. This should be converted to the respective option string to be saved in database
     * Overwrites base class accessor method
     */
    public function edit_save_data_preprocess($data,$datarecord) {
        return $data;
    }

    /**
     * @param       $mform
     *
     * @updateDate  23/12/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Set the default value for this field instance
     * Overwrites the base class method
     */
    public function edit_field_set_default($mform) {
        /* Variables    */
        global $USER;
        $myJobRoles = null;

        /* Get My Level Three   */
        $myJobRoles = self::GetMy_JobRoles($this->userid,$this->fieldid);

        /* Set the Company and the rest of the data */
        if ($myJobRoles) {
            $mform->getElement($this->inputname)->setMultiple(true);
            $mform->getElement($this->inputname)->removeAttribute('disabled');
            $mform->setDefault('hidden_job_role',implode(',',$myJobRoles));
        }//if_levelThree
    }//edit_field_set_default

    /**
     * @return mixed|string
     *
     * @creationDate    13/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Display the correct value
     */
    function display_data() {
       return self::GetName_MyJobRoles($this->userid,$this->fieldid);
    }//display_data

    /*************/
    /* PRIVATE  */
    /************/

    /**
     * @static
     * @param           $user_id
     * @param           $field_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    13/14/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the Job Roles Names connected to the user
     */
    private static function GetName_MyJobRoles($user_id,$field_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['userid']   = $user_id;
            $params['fieldid']  = $field_id;

            /* Execute  */
            $rdo = $DB->get_record('user_info_data',$params,'data');
            if ($rdo) {
                if ($rdo->data) {
                    /* SQL Instruction  */
                    $sql = " SELECT		GROUP_CONCAT(DISTINCT jr.name ORDER BY jr.name SEPARATOR ',') as 'names'
                             FROM		{report_gen_jobrole}	jr
                             WHERE		jr.id IN ($rdo->data) ";

                    /* Execute  */
                    $rdo_name = $DB->get_record_sql($sql);
                    if ($rdo_name) {
                        return $rdo_name->names;
                    }//if_rdo_name
                }else {
                    return null;
                }
            }//if_rdo

            return null;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetName_MyJobRoles

    /**
     * @static
     * @param           $user_id
     * @param           $field_id
     * @return          array|int
     * @throws          Exception
     *
     * @creationDate    12/14/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the Job Roles Connected to the user
     */
    private static function GetMy_JobRoles($user_id,$field_id) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['userid']   = $user_id;
            $params['fieldid']  = $field_id;

            /* Execute  */
            $rdo = $DB->get_record('user_info_data',$params,'data');
            if ($rdo) {
                if ($rdo->data) {
                    return self::GetReference_JobRole($rdo->data);
                }else {
                    return 0;
                }

            }else {
                return 0;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetMy_JobRoles

    /**
     * @static
     * @param           $jr_lst
     * @return          array
     * @throws          Exception
     *
     * @creationDate    12/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Build the reference of the Job Roles
     */
    private static function GetReference_JobRole($jr_lst) {
        /* Variables    */
        global $DB;
        $references_lst = array();
        $ref            = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		CONCAT('#',jr.id,'#JR') as 'jr',
                                CONCAT('#',jr_rel.idcounty,'#C') as 'county',
                                IF(jr_rel.levelone,CONCAT('I1#',jr_rel.levelone,'#L1'),0) as 'levelone',
                                IF(jr_rel.leveltwo,CONCAT('I2#',jr_rel.leveltwo,'#L2'),0) as 'leveltwo',
                                IF(jr_rel.levelthree,GROUP_CONCAT(DISTINCT CONCAT('I3#',jr_rel.levelthree,'#L3') ORDER BY jr_rel.jobroleid SEPARATOR '_'),0) as 'levelthree'
                     FROM		{report_gen_jobrole}				jr
                        JOIN	{report_gen_jobrole_relation}		jr_rel	ON jr_rel.jobroleid = jr.id
                     WHERE		jr.id IN ($jr_lst)
                     GROUP BY	jr.id
                     ORDER BY	jr.name ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Build the reference  */
                    $ref = $instance->jr         . '_' .
                           $instance->county     . '_' .
                           $instance->levelone   . '_' .
                           $instance->leveltwo   . '_' .
                           $instance->levelthree;

                    $references_lst[] = $ref;
                }//for_each_job_role
            }//if_rdo

            return $references_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetReference_JobRole

    /**
     * @static
     * @return          array
     * @throws          Exception
     *
     * @creationDate    12/14/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the job roles availables
     */
    private static function GetJobRoles() {
        /* Variables    */
        global $DB;
        $jr_lst     = array();
        $jr_ref     = null;

        try {
            $jr_lst[0] = get_string('choose').'...';

            /* SQL Instruction  */
            $sql = " SELECT		CONCAT('#',jr.id,'#JR') as 'jr',
                                jr.name,
                                CONCAT('#',jr_rel.idcounty,'#C') as 'county',
                                IF(jr_rel.levelone,CONCAT('I1#',jr_rel.levelone,'#L1'),0) as 'levelone',
                                IF(jr_rel.leveltwo,CONCAT('I2#',jr_rel.leveltwo,'#L2'),0) as 'leveltwo',
                                IF(jr_rel.levelthree,GROUP_CONCAT(DISTINCT CONCAT('I3#',jr_rel.levelthree,'#L3') ORDER BY jr_rel.jobroleid SEPARATOR '_'),0) as 'levelthree'
                     FROM		{report_gen_jobrole}				jr
                        JOIN	{report_gen_jobrole_relation}		jr_rel	ON jr_rel.jobroleid = jr.id
                     GROUP BY	jr.id
                     ORDER BY	jr.name  ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Build the reference  */
                    $jr_ref = $instance->jr         . '_' .
                              $instance->county     . '_' .
                              $instance->levelone   . '_' .
                              $instance->leveltwo   . '_' .
                              $instance->levelthree;

                    $jr_lst[$jr_ref] = $instance->name;
                }//for_each_job_role
            }//if_rdo

            return $jr_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetJobRoles
}//profile_field_rgjobrole