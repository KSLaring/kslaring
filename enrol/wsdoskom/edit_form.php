<?php

/**
 * Single Sign On Enrolment Plugin - Edit Form
 *
 * @package         enrol
 * @subpackage      wsdoskom
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    26/02/2015
 * @author          efaktor     (fbv)
 *
 * Description
 *  - Add a new instance of Single Sign On enrollment to specified course or edits current instance.
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class enrol_wsdoskom_edit_form extends moodleform {
    function definition() {
        global $OUTPUT,$SESSION;

        $m_form = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        /* Company List         */
        $company_lst    = WS_DOSKOM::getCompanyList();

        /* Selected Companies   */
        $schoices       = array();
        $schoices[0]    = get_string('not_sel_company','enrol_wsdoskom');
        /* Available Companies  */
        $achoices       = null;
        $achoices       = $company_lst;
        $achoices[0]    = get_string('sel_company','enrol_wsdoskom');

        $m_form->addElement('header', 'header', get_string('pluginname', 'enrol_wsdoskom'));

        $m_form->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $m_form->setType('name',PARAM_TEXT);
        if ($instance->id) {
            $roles = $this->extend_assignable_roles($context, $instance->roleid);
        } else {
            $roles = $this->extend_assignable_roles($context, $plugin->roleid);
        }//if_instance_id


        $m_form->addElement('select', 'roleid', get_string('role', 'enrol_wsdoskom'), $roles);
        $m_form->setDefault('roleid', $plugin->roleid);

        $m_form->addElement('duration', 'enrolperiod', get_string('enrol_period', 'enrol_wsdoskom'), array('optional' => true, 'defaultunit' => 86400));
        $m_form->setDefault('enrolperiod', $plugin->enrolperiod);
        $m_form->addHelpButton('enrolperiod', 'enrol_period', 'enrol_wsdoskom');

        $m_form->addElement('date_selector', 'enrolstartdate', get_string('enrol_start_date', 'enrol_wsdoskom'), array('optional' => true));
        $m_form->setDefault('enrolstartdate', 0);
        $m_form->addHelpButton('enrolstartdate', 'enrol_start_date', 'enrol_wsdoskom');

        $m_form->addElement('date_selector', 'enrolenddate', get_string('enrol_end_date', 'enrol_wsdoskom'), array('optional' => true));
        $m_form->setDefault('enrolenddate', 0);
        $m_form->addHelpButton('enrolenddate', 'enrol_end_date', 'enrol_wsdoskom');

        $m_form->addElement('text', 'participants', get_string('maxenrolled', 'enrol_wsdoskom'));
        $m_form->addHelpButton('participants', 'maxenrolled', 'enrol_wsdoskom');
        $m_form->addHelpButton('participants', 'maxenrolled', 'enrol_wsdoskom');
        $m_form->setType('participants', PARAM_INT);
        if ($instance->id && $instance->customint3) {
            $m_form->setDefault('participants', $instance->customint3);
        }else {
            $m_form->setDefault('participants', 0);
        }//if_else_company

        /* SELECTOR COMPANIES   */
        $m_form->addElement('header', 'header_company', get_string('company', 'enrol_wsdoskom'));

        /* REMOVE ALL COMPANIES */
        if (isset($SESSION->removeAll) && $SESSION->removeAll) {
            $schoices       = array();
            $schoices[0]    = get_string('not_sel_company','enrol_wsdoskom');
            $achoices       = $company_lst;
            $achoices[0]    = get_string('sel_company','enrol_wsdoskom');
        }//if_remove_all

        /* ADD ALL COMPANIES    */
        if (isset($SESSION->addAll) && $SESSION->addAll) {
            $schoices       = $company_lst;
            $schoices[0]    = get_string('selected_company','enrol_wsdoskom');
            $achoices       = array();
            $achoices[0]    = get_string('sel_company','enrol_wsdoskom');
        }//if_remove_all

        /* Companies Selected   */
        if (isset($SESSION->selCompanies) && $SESSION->selCompanies) {
            $schoices       = array();
            $schoices[0]    = get_string('selected_company','enrol_wsdoskom');

            foreach ($SESSION->selCompanies as $company) {
                if (isset($company_lst[$company])) {
                    $schoices[$company] = $company_lst[$company];
                    unset($achoices[$company]);
                }
            }
        }//if_selCompanies

        /* Available companies  */
        if (isset($SESSION->Companies) && $SESSION->Companies) {
            foreach ($SESSION->Companies as $company) {
                $achoices[$company] = $company_lst[$company];
            }
        }//if_addCompanies

        /* Companies Connected to the Enrolment Method  */
        $m_form->addElement('html','<div class="wsdoskom_company">');
            /* Selected Companies   */
            $m_form->addElement('html','<div class="sel_companies_left">');
                $m_form->addElement('select','scompanies','',$schoices,'multiple size="15"');
                $m_form->addElement('text','search_sel_companies',get_string('search'));
                $m_form->setType('search_sel_companies',PARAM_TEXT);
            $m_form->addElement('html','</div>');//sel_companies_left

            /* Buttons          */
            $m_form->addElement('html','<div class="sel_companies_buttons">');
                /* Add Company     */
                $add_btn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $m_form->addElement('submit','add_sel',$add_btn);
                /* Remove Company  */
                $remove_btn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                $m_form->addElement('submit','remove_sel',$remove_btn);

                $m_form->addElement('html','</br>');

                /* Add All Companies     */
                $add_all_btn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add_all', 'enrol_wsdoskom'));
                $m_form->addElement('submit','add_all',$add_all_btn);
                /* Remove All Companies  */
                $remove_all_btn = html_to_text(get_string('remove_all', 'enrol_wsdoskom') . '&nbsp;' . $OUTPUT->rarrow());
                $m_form->addElement('submit','remove_all',$remove_all_btn);
            $m_form->addElement('html','</div>');//sel_companies_buttons

            /* Company List */
            $m_form->addElement('html','<div class="sel_companies_right">');
                $m_form->addElement('select','acompanies', '',$achoices,'multiple size="15"');
                $m_form->addElement('text','search_add_companies',get_string('search'));
                $m_form->setType('search_add_companies',PARAM_TEXT);
            $m_form->addElement('html','</div>');//sel_companies_right
        $m_form->addElement('html','</div>');//wsdoskom_company

        $m_form->addElement('hidden', 'id');
        $m_form->setType('id', PARAM_INT);
        $m_form->addElement('hidden', 'courseid');
        $m_form->setType('courseid', PARAM_INT);
        $m_form->addElement('hidden','status');
        $m_form->setType('status',PARAM_INT);
        $m_form->setDefault('status',0);


        /* BUTTONS  */
        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }//definition

    function validation($data, $files) {
        global $DB, $CFG,$SESSION;
        $errors = parent::validation($data, $files);

        list($instance, $plugin, $context) = $this->_customdata;

        if ((isset($data['submitbutton']) && $data['submitbutton'])) {
            if (isset($data['enrolenddate']) && $data['enrolenddate']) {
                if (isset($data['enrolstartdate']) && $data['enrolstartdate']) {
                    if ($data['enrolenddate'] < $data['enrolstartdate']) {
                        $errors['enrolenddate'] = get_string('enrol_end_date_error', 'enrol_wsdoskom');
                    }
                }
            }//if_end_date

            /* Sel Companies */
            if (!isset($SESSION->selCompanies) || !$SESSION->selCompanies) {
                $errors['scompanies'] = get_string('required');
            }//sel_activities
        }//if_submit_create_instance


        return $errors;
    }//validation

    /**
     * Gets a list of roles that this user can assign for the course as the default for licence-enrolment
     *
     * @param context $context the context.
     * @param integer $defaultrole the id of the role that is set as the default for licence-enrolement
     * @return array index is the role id, value is the role name
     */
    function extend_assignable_roles($context, $defaultrole) {
        global $DB;

        $roles = get_assignable_roles($context, ROLENAME_BOTH);
        if (!isset($roles[$defaultrole])) {
            if ($role = $DB->get_record('role', array('id'=>$defaultrole))) {
                $roles[$defaultrole] = role_get_name($role, $context, ROLENAME_BOTH);
            }
        }
        return $roles;
    }
}//enrol_wsdoskom_edit_form
