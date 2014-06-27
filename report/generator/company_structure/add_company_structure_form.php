<?php


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

/* Form to add a new company into one level */
class generator_add_company_structure_form extends moodleform {
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
            $parent_name = report_generator_get_parent_name($level-1,$parents[$level-1]);
            $m_form->addElement('text','parent_' . $level-1,'Company Parent - Level ' . ($level-1),'size = 50 readonly');
            $m_form->setDefault('parent_' . $level-1,$parent_name);
            $m_form->setType('parent_' . $level-1,PARAM_TEXT);
        }//if_level
        $m_form->addElement('text', 'name', get_string('add_company_level','report_generator'), $text_attr);
        $m_form->setType('name',PARAM_TEXT);

        if ($level > 1) {
            $company_parent = array_flip($parents);
            $companies  = report_generator_get_level_list(1);
            $companies  = array_diff_key($companies,$company_parent);

            if ($level > 2) {
                $parent_lst = implode(',',array_keys($companies));
                $companies  = report_generator_get_level_list($level-1,$parent_lst);
                $companies  = array_diff_key($companies,$company_parent);
            }

            $parent_lst = implode(',',array_keys($companies));
            $options = report_generator_get_level_list($level,$parent_lst);
            $m_form->addElement('select','other_company',get_string('existing_item','report_generator'),$options);
        }//if_level_>_1

        /* Another Company From Parent Level    */
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
        $bln_exist = false;

        if (empty($data['name'])) {
            if (!$data['other_company']) {
                $errors['name'] = get_string('missing_name','report_generator');

                return $errors;
            }//other_company
        }//data_name

        if ($data['name']) {
            $name = $data['name'];
        }else {
            $name = report_generator_get_company_name($data['other_company']);
        }//if_data_name

        if ($level > 1) {
                $index = $level-1;
                $bln_exist = report_generator_exists_company($level,$name,$parents[$index]);
        }else {
                $bln_exist = report_generator_exists_company($level,$name);
        }
        if ($bln_exist) {
            $errors['name'] = get_string('exists_name','report_generator');
            return $errors;
        }//if_exist
    }//validation
}//generator_add_company_structure_form