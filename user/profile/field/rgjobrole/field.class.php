<?php

class profile_field_rgjobrole extends profile_field_base {
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
    function profile_field_rgjobrole($fieldid=0, $userid=0) {
        //first call parent constructor
        $this->profile_field_base($fieldid, $userid);

        /// get the actual companylist for menu
        $options = $this->get_report_jobroles();
        $this->options = array();
        if ($this->field->required){
            $this->options[''] = get_string('choose').'...';
        }

        if ($options) {
            foreach ($options as $key => $option) {
                $this->options[$key] = format_string($option); //multilang formatting
            }
        }//if_options


        /// Set the data key
        if ($this->data !== NULL) {
            $this->datakey = $this->data;
        }
    }

    /**
     * @param   $mform
     *
     * @author  eFaktor
     *
     * Description
     * Create the code snippet for this field instance
     * Overwrites the base class method
     */
    function edit_field_add($mform) {
        $mform->addElement('static', 'rgjobrole-description', '', get_string('profilefieldintrojobrole', 'profilefield_rgjobrole'));
        $select = &$mform->addElement('select', $this->inputname, format_string($this->field->name), $this->options);
        $select->setMultiple(true);
    }

    function setOptions($new_options) {
        $this->options = $new_options;
    }//setOptions

    /**
     * @param       $mform
     *
     * @updateDate  23/11/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Set the default value for this field instance
     * Overwrites the base class method
     */
    function edit_field_set_default($mform) {
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
     * @return      mixed|      string
     *
     * @updateDate  23/11/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * The data from the form returns the key. This should be converted to the
     * respective option string to be saved in database
     * Overwrites base class accessor method
     */
    function edit_save_data_preprocess($data, $datarecord) {
        if (is_array($data)) {
            $data = implode(',',$data);
        }
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
    function edit_load_user_data($user) {
        $user->{$this->inputname} = $this->datakey;
    }

    /**
     * @param   $mform
     *
     * @author  eFaktor
     *
     * Description
     * HardFreeze the field if locked.
     */
    function edit_field_set_locked($mform) {
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
    function display_data() {
        $options = new stdClass();
        $options->para = false;
        $id = $this->data;
        if ($entry = $this->get_report_jobrole_entry($id)) {
            return $entry;
        } else {
            return $id;
        }
    }

    /**
     * @return      array|bool      list of id/name entries
     *
     * @updateDate  12/09/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Get the jobrole list from report_gen_jobrole table.
     */
    function get_report_jobroles() {
        global $DB;

        $job_roles_list = array();

        /* SQL Instruction */
        $sql = " SELECT     id,
                            name
                 FROM       {report_gen_jobrole}
                 ORDER BY   name ASC ";

        /* Execute */
        if ($rdo = $DB->get_records_sql($sql)) {
            foreach($rdo as $data) {
                $job_roles_list[$data->id] = $data->name;
            }//rdo

            return $job_roles_list;
        }else {
            return false;
        }//if_else
    }//get_report_jobroles

    /**
     * @param       $id         jobrole ids
     * @return      string      string with jobrole names
     *
     * @updateDate  12/09/2012
     * @author      eFaktor     (fbv)
     *
     * Description
     * Get jobrole names as list
     */
    protected function get_report_jobrole_entry($id) {
        global $DB;

        $job_roles = array();
        
        // avoid error
        if( $id == 'Array' ) return $id;

        /* SQL Instruction */
        $sql = "
        SELECT 
            id, 
            name 
        FROM 
            {report_gen_jobrole}
        WHERE 
            id IN ({$id})
        ";

        /* Execute */
        if ($rdo = $DB->get_records_sql($sql)) {
            foreach($rdo as $row )
            {
                $job_roles[] = $row->name;
            }//for
        }

        return join( ', ', $job_roles );
    }//get_report_jobrole_entry
}
