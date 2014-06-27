<?php

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once('../locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

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
        $m_form->setDefault('name',report_generator_get_company_name($parents[$level]));
        $m_form->setType('name',PARAM_TEXT);

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
                $bln_exist = report_generator_exists_company($level,$data['name'],$parents[$index]);
            }else {
                $bln_exist = report_generator_exists_company($level,$data['name']);
            }
            if ($bln_exist) {
                $errors['name'] = get_string('exists_name','report_generator');
            }//if_exist
        }//if_empty

        return $errors;
    }//validation
}//generator_edit_company_structure_form