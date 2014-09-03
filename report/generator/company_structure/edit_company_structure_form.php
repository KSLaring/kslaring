<?php

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once('../locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/generator/js/muni.js');

/* Form to add a new company into one level */
class generator_edit_company_structure_form extends moodleform {
    function definition() {
        global $SESSION;

        $m_form = $this->_form;

        /* General Settings */
        $text_attr = array(
            'class' => 'text-input',
            'size'  => '50'
        );

        $level= $this->_customdata;

        $m_form->addElement('header', 'level_' . $level, 'Company Structure - Level ' .$level);

        /* Add reference's parents */
        $parents = $SESSION->parents;
        if ($level > 1) {
            for ($i = 1; $i < $level; $i++) {
                $parent_name = report_generator_get_parent_name($i,$parents[$i]);
                $m_form->addElement('text','parent_' . $i,'Company Parent - Level ' . ($i),'size = 50 readonly');
                $m_form->setDefault('parent_' . $i,$parent_name);
                $m_form->setType('parent_' . $i,PARAM_TEXT);
            }//for
        }//if_level
        $m_form->addElement('text', 'name', get_string('edit_company_level','report_generator'), $text_attr);
        $company_info = report_generator_getCompany_Detail($parents[$level]);
        if ($company_info) {
            $m_form->setDefault('name',$company_info->name);
        }//company_info

        $m_form->setType('name',PARAM_TEXT);

        /* Level == 3 */
        if ($level == 3) {
            /* County           */
            $options        = report_generator_GetCounties_List();
            $m_form->addElement('select','county',get_string('county','report_generator'),$options);
            $m_form->addRule('county','','required', null, 'server');

            /* Municipality     */
            $options    = array();
            if ($company_info && $company_info->idcounty) {
                $m_form->setDefault('county',$company_info->idcounty);
                $options = report_generator_GetMunicipalities_List($company_info->idcounty);
            }else {
                $options[0] = get_string('sel_municipality','report_generator');
            }//company_info_&&idmuni


            $m_form->addElement('select','munis',get_string('municipality','report_generator'),$options);
            $m_form->addRule('munis','','required', null, 'server');
            if ($company_info && $company_info->idmuni) {
                $m_form->setDefault('munis',$company_info->idmuni);
            }//company_info_&&_idcounty

            /* Municipality hidden */
            $options = report_generator_GetMunicipalities_List();
            $m_form->addElement('select','hidden_munis','',$options,'style="visibility:hidden;height:0px;"');

            /* Municipality hidden */
            $m_form->addElement('text','municipality_id',null,'style="visibility:hidden;height:0px;"');
            $m_form->setType('municipality_id',PARAM_TEXT);
        }//level_3

        $m_form->addElement('hidden','level');
        $m_form->setDefault('level',$level);
        $m_form->setType('level',PARAM_INT);

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
                $bln_exist = report_generator_exists_company($level,$data,$parents[$index]);
            }else {
                $bln_exist = report_generator_exists_company($level,$data);
            }
            if ($bln_exist) {
                $errors['name'] = get_string('exists_name','report_generator');
            }//if_exist
        }//if_empty

        return $errors;
    }//validation
}//generator_edit_company_structure_form