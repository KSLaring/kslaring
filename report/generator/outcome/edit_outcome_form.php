<?php

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once('../locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

/* Form to edit a outcome. */
class generator_edit_outcome_form extends moodleform {
    function definition() {
        /* General Settings */
        $level_select_attr = array(
            'class' => REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL,
            'size'  => '20'
        );

        $m_form = $this->_form;

        list($outcome_id,$expiration_id) = $this->_customdata;

        $m_form->addElement('header', 'name_area', get_string('expiration_period', 'report_generator'));
        $m_form->addElement('text', 'expiration_period', get_string('expiration_period', 'report_generator'));
        $m_form->setType('expiration_period',PARAM_INT);

        if ($expiration_id) {
            $m_form->setDefault('expiration_period',report_generator_get_outcome_expiration_period($expiration_id));
        }

        $m_form->addElement('header', 'job_roles', get_string('related_job_roles', 'report_generator'));
        $m_form->addElement('html', '<div class="level-wrapper">');
            list($job_role_list, $roles_selected) = report_generator_get_role_list_with_selected($outcome_id);
            $select = &$m_form->addElement('select',
                                           REPORT_GENERATOR_JOB_ROLE_LIST,
                                           get_string(REPORT_GENERATOR_JOB_ROLE_LIST, 'report_generator'),
                                           $job_role_list,
                                           $level_select_attr);

        $select->setMultiple(true);
        $m_form->setDefault(REPORT_GENERATOR_JOB_ROLE_LIST, $roles_selected);
        $m_form->addElement('html', '</div>');

        $m_form->addElement('hidden','id');
        $m_form->setDefault('id',$outcome_id);
        $m_form->setType('id',PARAM_INT);

        $m_form->addElement('hidden','expid');
        $m_form->setDefault('expid',$expiration_id);
        $m_form->setType('expid',PARAM_INT);

        $this->add_action_buttons();
    }//definition
}//generator_edit_outcome_form