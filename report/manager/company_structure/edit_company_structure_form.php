<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/* Form to add a new company into one level */
class manager_edit_company_structure_form extends moodleform {
    function definition() {
        global $SESSION;
        $parent_info    = null;
        $attr           = '';
        $default        = 1;

        $m_form = $this->_form;

        /* General Settings */
        $text_attr = array(
            'class' => 'text-input',
            'size'  => '50'
        );

        $level= $this->_customdata;
        /* Company Info */
        $parents = $SESSION->parents;
        $company_info = company_structure::Get_CompanyInfo($parents[$level]);

        $m_form->addElement('header', 'level_' . $level, get_string('company_structure','report_manager') . ' - ' . get_string('company_structure_level','report_manager',$level));
        /* Add Parents */
        for ($i = 0; $i < $level; $i++) {
            $parent_info = company_structure::Get_CompanyInfo($parents[$i]);
            $m_form->addElement('text','parent_' . $i,get_string('comp_parent','report_manager', $i),'size = 50 readonly');
            $m_form->setDefault('parent_' . $i,$parent_info->name);
            $m_form->setType('parent_' . $i,PARAM_TEXT);
        }//for

        /* Company Name */
        $m_form->addElement('text', 'name', get_string('edit_company_level','report_manager'), $text_attr);
        $m_form->setDefault('name',$company_info->name);
        $m_form->setType('name',PARAM_TEXT);
        $m_form->addRule('name',get_string('required','report_manager'),'required', null, 'client');

        /* Industry Code        */
        $m_form->addElement('text', 'industry_code', get_string('industry_code','report_manager'), $text_attr);
        $m_form->setDefault('industry_code',$company_info->industrycode);
        $m_form->setType('industry_code',PARAM_TEXT);
        $m_form->addRule('industry_code',get_string('required','report_manager'),'required', null, 'client');

        /* Public o private */
        $m_form->addElement('checkbox', 'public','',get_string('public', 'report_manager'));
        $m_form->setDefault('public',$company_info->public);
        if ($parent_info) {
            $m_form->addElement('hidden','public_parent');
            $m_form->setDefault('public_parent',$parent_info->public);
            $m_form->setType('public_parent',PARAM_INT);
        }

        $m_form->addElement('hidden','level');
        $m_form->setDefault('level',$level);
        $m_form->setType('level',PARAM_INT);

        $m_form->addElement('hidden','company');
        $m_form->setDefault('company',$parents[$level]);
        $m_form->setType('company',PARAM_INT);

        $this->add_action_buttons(true);
        $this->set_data($level);
    }//definition



    function validation($data, $files) {
        global $DB, $CFG, $SESSION;
        $errors = parent::validation($data, $files);

        $level = $this->_customdata;
        $parents = $SESSION->parents;

        if (empty($data['name'])) {
            $errors['name'] = get_string('missing_name','report_manager');
        }else {
            $bln_exist = false;
            if ($level) {
                $index = $level-1;
                $bln_exist = company_structure::Exists_Company($level,$data,$parents[$index]);
            }else {
                $bln_exist = company_structure::Exists_Company($level,$data);
            }
            if ($bln_exist) {
                $errors['name'] = get_string('exists_name','report_manager');
            }//if_exist
        }//if_empty

        return $errors;
    }//validation
}//manager_edit_company_structure_form