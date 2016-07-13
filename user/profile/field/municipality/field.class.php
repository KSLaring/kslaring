<?php
/**
 * Extra Profile Field Municipality
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/municipality
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    19/11/2014
 * @author          eFaktor     (fbv)
 *
 */

class profile_field_municipality extends profile_field_base {

    public function edit_field_add($m_form) {
        /* Variables    */
        global $PAGE;

        $PAGE->requires->js(new moodle_url('/user/profile/field/municipality/js/FilterMunicipality.js'));

        /* County       */
        $options        = self::GetCounties_List();
        $m_form->addElement('select','sel_county',get_string('county','profilefield_municipality'),$options);

        /* Municipality */
        $options        = self::GetMunicipalities();
        $m_form->addElement('select',$this->inputname,format_string($this->field->name),$options,'disabled');


        /* hidden Input Name  */
        $m_form->addElement('text','input_name',null,'style="visibility:hidden;height:0px;"');
        $m_form->setType('input_name',PARAM_TEXT);
        $m_form->setDefault('input_name',$this->inputname);

        /* hidden Muni  */
        $m_form->addElement('text','hidden_muni',null,'style="visibility:hidden;height:0px;"');
        $m_form->setType('hidden_muni',PARAM_TEXT);
        $m_form->setDefault('hidden_muni',0);
    }//edit_field_data

    /**
     * @param           stdClass $usernew
     * @return          mixed|void
     * @throws          Exception
     *
     * @creationDate    19/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Save the municipality selected
     */
    public function edit_save_data($usernew) {
        /* Variables    */
        global $DB;
        $selectedMuni   = null;
        $idMuni         = null;
        $index          = null;
        $data           = null;

        try {
            if (!isset($usernew->{$this->inputname})) {
                // Field not present in form, probably locked and invisible - skip it.
                return;
            }

            /* Get the Id Muni  */
            $selectedMuni   = $usernew->{$this->inputname};
            $index          = strripos($selectedMuni,"_");
            $idMuni         = substr($selectedMuni,$index+1);

            $data = new stdClass();

            $usernew->{$this->inputname} = $idMuni;

            $data->userid  = $usernew->id;
            $data->fieldid = $this->field->id;
            $data->data    = $usernew->{$this->inputname};

            if ($dataid = $DB->get_field('user_info_data', 'id', array('userid' => $data->userid, 'fieldid' => $data->fieldid))) {
                $data->id = $dataid;
                $DB->update_record('user_info_data', $data);
            } else {
                $DB->insert_record('user_info_data', $data);
            }//if_insert_update
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//edit_save_data

    /**
     * @param       mixed       $data
     * @param       stdClass    $datarecord
     * @return      mixed
     *
     * @updateDate  19/11/2014
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
     * @param           $mform
     *
     * @creationDate    19/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the default value for this field instance
     * Overwrites the base class method
     */
    public function edit_field_set_default($mform) {
        /* Variables    */
        $muniRef = null;

        /* Get My Level Three   */
        $muniRef = self::GetReference_MyMunicipality($this->userid,$this->fieldid);
        /* Set the Company and the rest of the data */
        if ($muniRef) {
            $mform->setDefault('hidden_muni',$muniRef);
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
        return self::GetName_MyMunicipality($this->userid,$this->fieldid);
    }//display_data

    /************/
    /* PRIVATE  */
    /************/

    private static function GetReference_MyMunicipality($user_id,$field_id) {
        /* Variables    */
        global $DB;
        $ref_municipality   = 0;
        $params             = null;
        $sql                = null;
        $rdo                = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['userid']   = $user_id;
            $params['fieldid']  = $field_id;

            /* SQL Instruction  */
            $sql = " SELECT		m.idcounty,
                                m.idmuni
                     FROM		{municipality}	    m
                        JOIN	{user_info_data}	uid		ON 		uid.data 	= m.idmuni
                                                            AND		uid.userid 	= :userid
                                                            AND		uid.fieldid = :fieldid ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $ref_municipality = $rdo->idcounty . '_' . $rdo->idmuni;
            }//if_rdo

            return $ref_municipality;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetReference_MyMunicipality

    /**
     * @param           $user_id
     * @param           $field_id
     * @return          null
     * @throws          Exception
     *
     * @creationDate    19/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the name of my municipality
     */
    private static function GetName_MyMunicipality($user_id,$field_id) {
        /* Variables    */
        global $DB;
        $str_municipality   = null;
        $params             = null;
        $sql                = null;
        $rdo                = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['userid']   = $user_id;
            $params['fieldid']  = $field_id;

            /* SQL Instruction  */
            $sql = " SELECT		m.municipality
                     FROM		{municipality}	    m
                        JOIN	{user_info_data}	uid		ON 		uid.data 	= m.idmuni
                                                            AND		uid.userid 	= :userid
                                                            AND		uid.fieldid = :fieldid ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->municipality) {
                    $str_municipality = $rdo->municipality;
                }
            }//if_rdo

            return $str_municipality;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetName_MyMunicipality

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
        $county_lst     = array();
        $rdo            = null;

        try {
            /* Counties List    */
            $county_lst[0]  = get_string('sel_county','profilefield_municipality');

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
     * @return          array
     * @throws          Exception
     *
     * @creationDate    19/11/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the municipality list
     */
    private static function GetMunicipalities() {
        /* Variables    */
        global $DB;
        $municipality_lst   = array();
        $rdo                = null;

        try {
            /* Municipality List    */
            $municipality_lst[0]  = get_string('sel_muni','profilefield_municipality');

            /* Execute  */
            $rdo = $DB->get_records('municipality',null,'idcounty,municipality','id,idcounty,idmuni,municipality');
            if ($rdo) {
                foreach ($rdo as $municipality) {
                    $municipality_lst[$municipality->idcounty . '_' . $municipality->idmuni] = $municipality->municipality;
                }//for_rdo
            }//if_rdo

            return $municipality_lst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetMunicipalities_By_County
}//profile_field_municipality