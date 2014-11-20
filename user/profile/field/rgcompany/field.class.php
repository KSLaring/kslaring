<?php
/**
 * Extra Profile Field Company
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/rgcompany
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    11/11/2014
 * @author          eFaktor     (fbv)
 *
 */

class profile_field_rgcompany extends profile_field_base {
    public function edit_field_add($m_form) {
        global $PAGE;

        $PAGE->requires->js(new moodle_url('/user/profile/field/rgcompany/js/FilterCompany.js'));

        /* County    */
        $options        = self::GetCounties_List();
        $m_form->addElement('select','county',get_string('county','profilefield_rgcompany'),$options);

        /* Level One    */
        $options    = self::GetStructureLevel_By_County(1);
        $m_form->addElement('select','level_one' ,get_string('select_company_structure_level','profilefield_rgcompany',1),$options,'disabled');

        /* Level Two    */
        $options = self::GetStructureLevel_By_Parent(2);
        $m_form->addElement('select','level_two',get_string('select_company_structure_level','profilefield_rgcompany',2),$options,'disabled');

        $m_form->addElement('static', 'rgcompany-description', '', get_string('profile_intro', 'profilefield_rgcompany'));

        /* Level Three  */
        $options = self::GetStructureLevel_By_Parent(3);
        $m_form->addElement('select',$this->inputname,format_string($this->field->name),$options,'disabled');

        /* hidden Level Three  */
        $m_form->addElement('text','hidden_level_three',null,'style="visibility:hidden;height:0px;"');
        $m_form->setType('hidden_level_three',PARAM_TEXT);
        $m_form->setDefault('hidden_level_three',0);
    }//edit_field_add

    /**
     * @param           stdClass $usernew
     * @return          mixed|void
     *
     * @creationDate    11/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save the company/ies selected
     */
    public function edit_save_data($usernew) {
        /* Variables    */
        global $DB;
        $selectedThree  = null;
        $levelThree     = array();
        $companyId      = null;
        $index          = null;


        if (!isset($usernew->{$this->inputname})) {
            // Field not present in form, probably locked and invisible - skip it.
            return;
        }

        /* Get the companies selected   */
        $selectedThree = explode(',',$usernew->hidden_level_three);
        foreach ($selectedThree as $ref) {
            $index      = strripos($ref,"_");
            $companyId  = substr($ref,$index+1);

            $levelThree[$companyId] = $companyId;
        }//for_each_selected

        $data = new stdClass();

        $usernew->{$this->inputname} = implode(',',$levelThree);

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
        $levelThree = null;

        /* Get My Level Three   */
        $levelThree = self::GetMyLevelThree($this->userid,$this->fieldid);
        /* Set the Company and the rest of the data */
        if ($levelThree) {
            $mform->getElement($this->inputname)->setMultiple(true);
            $mform->getElement($this->inputname)->removeAttribute('disabled');
            $mform->setDefault('hidden_level_three',implode(',',$levelThree));
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
        return self::GetNames_MyLevelThree($this->userid,$this->fieldid);
    }//display_data

    /************/
    /* PRIVATE  */
    /************/

    /**
     * @static
     * @param           $user_id
     * @param           $field_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    13/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the names connected to my companies
     */
    private static function GetNames_MyLevelThree($user_id,$field_id) {
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
                    $sql = " SELECT		GROUP_CONCAT(DISTINCT rgc.name ORDER BY rgc.name SEPARATOR ',') as 'names'
                         FROM		{report_gen_companydata}	rgc
                         WHERE		rgc.id IN ($rdo->data) ";

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
    }//GetNames_MyLevelThree

    /**
     * @static
     * @param           $user_id
     * @param           $field_id
     * @return          int
     * @throws          Exception
     *
     * @creationDate    11/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all companies connected to the user
     */
    private static function GetMyLevelThree($user_id,$field_id) {
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
                    return self::GetReferences_LevelThree($rdo->data);
                }else {
                    return 0;
                }
            }else {
                return 0;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetMyLevelThree

    /**
     * @static
     * @param           $levelThree
     * @return          array
     * @throws          Exception
     *
     * @creationDate    11/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Build the references for the level three
     */
    private static function GetReferences_LevelThree($levelThree) {
        /* Variables    */
        global $DB;
        $references_lst = array();

        try {
            /* SQL Instruction  */
            $sql = " SELECT		CONCAT('P',parentid,'_',companyid) as 'ref'
                     FROM		{report_gen_company_relation}
                     WHERE		companyid IN ($levelThree) ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $references_lst[] = $instance->ref . '#';
                }
            }//if_rdo

            return $references_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//BuildReference_LevelThree

    /**
     * @static
     * @return          array
     * @throws          Exception
     *
     * @creationDate    11/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the counties list
     */
    private static function GetCounties_List() {
        /* Variables    */
        global $DB;

        try {
            /* Counties List    */
            $county_lst     = array();
            $county_lst[0]  = get_string('sel_county','profilefield_rgcompany');

            /* Execute  */
            $rdo = $DB->get_records('counties',null,'county','idcounty,county');
            if ($rdo) {
                foreach ($rdo as $county) {
                    $county_lst[$county->idcounty] = $county->county;
                }//for_rdo
            }//if_rdo

            return $county_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCounties_List

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
     * Get the Structure Level sort by county
     */
    private static function GetStructureLevel_By_County($level) {
        /* Variables    */
        global $DB;
        $structure_level = array();

        try {
            $structure_level[0] = get_string('select_level_list','profilefield_rgcompany');

            /* Search Criteria  */
            $params = array();
            $params['level']    = $level;

            /* SQL Instruction  */
            $sql = " SELECT		concat(idcounty,'_',id) as 'ref',
                                name
                     FROM		{report_gen_companydata}
                     WHERE		hierarchylevel = :level
                     ORDER BY	idcounty,name ASC ";


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
    private static function GetStructureLevel_By_Parent($level) {
        /* Variables    */
        global $DB;
        $structure = array();

        try {
            $structure[0] = get_string('select_level_list','profilefield_rgcompany');

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
}//profile_field_rgcompany