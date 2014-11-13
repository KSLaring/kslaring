<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/generator/js/muni.js');

/* Form to add a new company into one level */
class generator_edit_company_structure_form extends moodleform {
    function definition() {
        global $SESSION;
        $county = null;
        $muni   = null;

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

        $m_form->addElement('header', 'level_' . $level, 'Company Structure - Level ' .$level);


        /* County           */
        $options        = report_generator_GetCounties_List();
        $m_form->addElement('select','county',get_string('county','report_generator'),$options);
        $m_form->setDefault('county',$company_info->idcounty);
        $m_form->addRule('county','','required', null, 'server');

        /* Municipality     */
        $options = report_generator_GetMunicipalities_List();
        $m_form->addElement('select','munis',get_string('municipality','report_generator'),$options);
        $m_form->addRule('munis','','required', null, 'server');


        /* Add reference's parents */
        if ($level > 1) {
            for ($i = 1; $i < $level; $i++) {
                $parent_name = company_structure::Get_Company_ParentName($i,$parents[$i]);
                $m_form->addElement('text','parent_' . $i,'Company Parent - Level ' . ($i),'size = 50 readonly');
                $m_form->setDefault('parent_' . $i,$parent_name);
                $m_form->setType('parent_' . $i,PARAM_TEXT);
            }//for
        }//if_level


        $m_form->addElement('text', 'name', get_string('edit_company_level','report_generator'), $text_attr);
        $m_form->setDefault('name',$company_info->name);
        $m_form->setType('name',PARAM_TEXT);

        /* Industry Code        */
        $m_form->addElement('text', 'industry_code', get_string('industry_code','report_generator'), $text_attr);
        $m_form->setDefault('industry_code',$company_info->industrycode);
        $m_form->setType('industry_code',PARAM_TEXT);
        $m_form->addRule('industry_code','','required', null, 'server');

        $m_form->addElement('hidden','level');
        $m_form->setDefault('level',$level);
        $m_form->setType('level',PARAM_INT);

        $m_form->addElement('hidden','company');
        $m_form->setDefault('company',$parents[$level]);
        $m_form->setType('company',PARAM_INT);

        $m_form->addElement('text','hidden_munis',null,'style="visibility:hidden;height:0px;"');
        $m_form->setType('hidden_munis',PARAM_TEXT);
        $m_form->setDefault('hidden_munis',$company_info->idmuni);

        $this->add_action_buttons(true);
        $this->set_data($level);
    }//definition

    function validation($data, $files) {
        global $DB, $CFG, $SESSION;
        $errors = parent::validation($data, $files);

        $level = $this->_customdata;
        $parents = $SESSION->parents;

        if (empty($data['name'])) {
            $errors['name'] = get_string('missing_name','report_generator');
        }else {
            $bln_exist = false;
            if ($level > 1) {
                $index = $level-1;
                $bln_exist = company_structure::Exists_Company($level,$data,$parents[$index]);
            }else {
                $bln_exist = company_structure::Exists_Company($level,$data);
            }
            if ($bln_exist) {
                $errors['name'] = get_string('exists_name','report_generator');
            }//if_exist
        }//if_empty

        return $errors;
    }//validation
}//generator_edit_company_structure_form