<?php

/**
 * Report Competence Manager - Company structure.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/company_structure
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  10/09/2012
 * @author      eFaktor     (fbv)
 *
 * Add a new company into a specific level
 *
 * @updateDate  24/01/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Update to Level Zero.
 * - Remove Counties and Municipalities
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/manager/js/manager.js');

/* Form to add a new company into one level */
class manager_add_company_structure_form extends moodleform {
    function definition() {
        /* Variables    */
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


        $m_form->addElement('header', 'header_level_' . $level, get_string('company_structure','report_manager') . ' - ' . get_string('company_structure_level','report_manager',$level));
        /* Add reference's parents */
        $parents = $SESSION->parents;
        /* Add Parents */
        for ($i = 0; $i < $level; $i++) {
            $parent_info = company_structure::Get_CompanyInfo($parents[$i]);
            $m_form->addElement('text','parent_' . $i,get_string('comp_parent','report_manager', $i),'size = 50 readonly');
            $m_form->setDefault('parent_' . $i,$parent_info->name);
            $m_form->setType('parent_' . $i,PARAM_TEXT);
        }//for

        /* New Item / Company */
        $m_form->addElement('text', 'name', get_string('add_company_level','report_manager'), $text_attr);
        $m_form->setType('name',PARAM_TEXT);

        /* Link Other Company   */
        if ($level > 0) {
            $this->Link_OtherCompany($level,$parents,$m_form);
        }else {
            $m_form->addRule('name','required','required', null, 'client');
        }//if_level

        /* Industry Code        */
        $m_form->addElement('text', 'industry_code', get_string('industry_code','report_manager'), $text_attr);
        $m_form->setType('industry_code',PARAM_TEXT);
        $m_form->addRule('industry_code','required','required', null, 'client');

        /* Public Check Box     */
        if ($parent_info) {
            $attr = 'disabled';
            $default = $parent_info->public;
        }
        $m_form->addElement('checkbox', 'public','',get_string('public', 'report_manager'),$attr);
        $m_form->setDefault('public',$default);
        /* Public Parent Hide   */
        if ($parent_info) {
            $m_form->addElement('hidden','public_parent');
            $m_form->setDefault('public_parent',$parent_info->public);
            $m_form->setType('public_parent',PARAM_INT);
        }

        $m_form->addElement('hidden','level');
        $m_form->setDefault('level',$level);
        $m_form->setType('level',PARAM_INT);

        $this->add_action_buttons(true);
        $this->set_data($level);
    }//definition

    function Link_OtherCompany($level,$parents,&$form) {
        /* Variables    */
        $company_parent = null;
        $companies      = null;
        $parent_lst     = null;
        $my_companies   = null;

        /* Level Parent Zero   */
        $company_parent = array_flip($parents);
        $companies  = CompetenceManager::GetCompanies_LevelList(0);
        $companies  = array_diff_key($companies,$company_parent);

        switch ($level) {
            case 1:
                /* My Companies             */
                $my_companies = CompetenceManager::GetCompanies_LevelList($level,$parents[$level-1]);
                unset($my_companies[0]);

                /* Add Companies to Link    */
                $parent_lst = implode(',',array_keys($companies));
                $options = CompetenceManager::GetCompanies_LevelList($level,$parent_lst);
                $options = array_diff_key($options,$my_companies);
                $form->addElement('select','other_company',get_string('existing_item','report_manager'),$options);

                break;
            case 2:
                /* My Companies             */
                $my_companies = CompetenceManager::GetCompanies_LevelList($level,$parents[$level-1]);
                unset($my_companies[0]);

                /* Level One    */
                $company_parent = array_flip($parents);
                $parent_lst = implode(',',array_keys($companies));
                $companies  = CompetenceManager::GetCompanies_LevelList(1,$parent_lst);
                $companies  = array_diff_key($companies,$company_parent);

                /* Add Companies to Link    */
                $parent_lst = implode(',',array_keys($companies));
                $options    = CompetenceManager::GetCompanies_LevelList($level,$parent_lst);
                $options    = array_diff_key($options,$my_companies);
                $form->addElement('select','other_company',get_string('existing_item','report_manager'),$options);

                break;
            case 3:
                /* My Companies             */
                $my_companies = CompetenceManager::GetCompanies_LevelList($level,$parents[$level-1]);
                unset($my_companies[0]);

                /* Level One    */
                $parent_lst = implode(',',array_keys($companies));
                $companies  = CompetenceManager::GetCompanies_LevelList(1,$parent_lst);
                $companies  = array_diff_key($companies,$company_parent);

                /* Level Two    */
                $parent_lst = implode(',',array_keys($companies));
                $companies  = CompetenceManager::GetCompanies_LevelList(2,$parent_lst);
                $companies  = array_diff_key($companies,$company_parent);

                /* Add Companies to Link    */
                $parent_lst = implode(',',array_keys($companies));
                $options    = CompetenceManager::GetCompanies_LevelList($level,$parent_lst);
                $options    = array_diff_key($options,$my_companies);
                $form->addElement('select','other_company',get_string('existing_item','report_manager'),$options);

                break;
        }//switch_level
    }//Link_OtherCompany

    function validation($data, $files) {
        global $DB, $CFG, $SESSION;
        $errors = parent::validation($data, $files);

        $level = $this->_customdata;
        $parents = $SESSION->parents;
        $bln_exist = false;

        if (empty($data['name'])) {
            if (isset($data['other_company']) && !$data['other_company']) {
                $errors['name'] = get_string('missing_name','report_manager');
            }//other_company
        }else {
            if ($level) {
                $index = $level-1;
                $bln_exist = company_structure::Exists_Company($level,$data,$parents[$index]);
            }else {
                $bln_exist = company_structure::Exists_Company($level,$data);
            }
            if ($bln_exist) {
                $errors['name'] = get_string('exists_name','report_manager');
            }//if_exist
        }//data_name

        return $errors;
    }//validation
}//manager_add_company_structure_form