<?php

class profile_field_rgcompany extends profile_field_base {
     var $options;
     var $datakey;

    /**
     * @param   int $fieldid
     * @param   int $userid
     *
     * @author  eFaktor
     *
     * Description
     * Constructor method.
     * Pulls out the options for the menu from the database and sets the
     * the corresponding key for the data if it exists
     */
    function profile_field_rgcompany($fieldid=0, $userid=0) {
        //first call parent constructor
        $this->profile_field_base($fieldid, $userid);

        /// get the actual companylist for menu
        $options = $this->get_report_companydata(3);
        $this->options = array();
        if ($this->field->required){
            $this->options[''] = get_string('choose').'...';
        }

        if ($options) {
            foreach($options as $key => $option) {
                $this->options[$key] = format_string($option); //multilang formatting
            }
        }//if_options

        /// Set the data key
        if ($this->data !== NULL) {
            $this->datakey = $this->data;
        }
    }

    public function edit_field_company($mform) {
        if ($this->field->visible != PROFILE_VISIBLE_NONE
            or has_capability('moodle/user:update', context_system::instance())) {

            $this->edit_field_add_company($mform);
            $this->edit_field_set_default($mform);
            $this->edit_field_set_required($mform);
            return true;
        }
        return false;
    }

    public function edit_field_add_company($mform) {
        global $USER,$PAGE;


        /* filter field right version */
        $mform->addElement('select', $this->inputname, format_string($this->field->name), $this->options,'disabled');
        $this->edit_field_set_default($mform);
    }

    /**
     * @param       $mform
     *
     * @updateDate  23/11/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Create the code snippet for this field instance
     * Overwrites the base class method
     */
    public function edit_field_add($mform) {
        global $USER,$PAGE;

        $PAGE->requires->js(new moodle_url('/user/profile/field/rgcompany/FilterCompany.js'));
        $site_context = CONTEXT_SYSTEM::instance();

        /* filter field right version */
        $mform->addElement('static', 'rgcompany-description', '', get_string('filtercompanylist', 'profilefield_rgcompany'));
        $mform->addElement('html', '<div class="rgcompany-wrapper clearfix"><div class="companylist-fitem">');

        $mform->addElement('select', $this->inputname, format_string($this->field->name), $this->options);

        $mform->addElement('html', '</div><div class="rgcompany-fitem">');

        $attributes = 'size="25"';
        $mform->addElement('text', 'input_rgcompany', get_string('search'), $attributes);
        $mform->setType('input_rgcompany',PARAM_TEXT);


        $mform->addElement('html', '</div></div>');
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
        if (FALSE !==array_search($this->field->defaultdata, $this->options)){
           $defaultkey = (int)array_search($this->field->defaultdata, $this->options);
        } else {
            $defaultkey = '';
        }
        $mform->setDefault($this->inputname, $defaultkey);
    }

    /**
     * @param       mixed       $data
     * @param       stdClass    $datarecord
     * @return      mixed
     *
     * @updateDate  23/11/2012
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
     * @param   $user
     *
     * @author  eFaktor
     *
     * Description
     * When passing the user object to the form class for the edit profile page
     * we should load the key for the saved data
     * Overwrites the base class method
     */
    public function edit_load_user_data($user) {
        $user->{$this->inputname} = $this->datakey;
    }

    /**
     * @param   $mform
     *
     * @author  eFaktor (fbv)
     *
     * Description
     * HardFreeze the field if locked.
     */
    public function edit_field_set_locked($mform) {
        if (!$mform->elementExists($this->inputname)) {
            return;
        }
        if ($this->is_locked() and !has_capability('moodle/user:update', CONTEXT_SYSTEM::instance())) {
            $mform->hardFreeze($this->inputname);
            $mform->setConstant($this->inputname, $this->datakey);
        }
    }

    /**
     * @return  string
     *
     * @author  eFaktor
     *
     * Description
     * Display the data for this field
     */
    public function display_data() {
        $options = new stdClass();
        $options->para = false;
        $id = $this->data;
        if ($entry = $this->get_report_company_entry($id)) {
            return $entry;
        } else {
            return $id;
        }
    }

    /**
     * @param       $hierarchy_level        list of id/name entries
     * @return      array|bool              with company entries
     *
     * @updateDate  12/09/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Get the company list from report_gen_companydata table with the given hierarchylevel.
     */
    protected function get_report_companydata($hierarchy_level) {
        global $DB;

        $company_data = array();

        /* SQL Instruction */
        $sql = " SELECT     cd.id,
                            cd.name
                 FROM       {report_gen_companydata} cd
                 WHERE      cd.hierarchylevel = :level
                 ORDER BY   cd.name ASC ";

        /* Research Criteria */
        $params = array();
        $params['level'] = $hierarchy_level;

        /* Execute */
        if ($rdo = $DB->get_records_sql($sql,$params)) {
            foreach($rdo as $data) {
                $company_data[$data->id] = $data->name;
            }//for

            return $company_data;
        }else {
            return false;
        }
    }//get_report_companydata

    /**
     * @param       $id     company id
     * @return      bool    with company name or false
     *
     * @updateDate  12/09/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Get one company name
     */
    protected function get_report_company_entry($id) {
        global $DB;

        /* SQL Instruction */
        $sql = " SELECT     name
                 FROM       {report_gen_companydata}
                 WHERE      id = :company ";

        /* Research Criteria */
        $params = array();
        $params['company'] = $id;

        if ($rdo = $DB->get_record('report_gen_companydata',array('id'=>$id))) {
            return $rdo->name;
        }else {
            return false;
        }//if_else
    }//get_report_company_entry
}
