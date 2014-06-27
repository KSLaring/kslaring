<?php

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once('../locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

/* Form to add or edit a job role. */
class generator_edit_job_role_form extends moodleform {
    function definition () {
        /* General Settings */
        $level_select_attr = array(
            'class' => REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL,
            'size'  => '20'
        );

        $m_form = $this->_form;

        $job_role_id = $this->_customdata;

        $m_form->addElement('header', 'name_area', get_string('job_role_name', 'report_generator'));
        $m_form->addElement('text', 'job_role_name', get_string('job_role_name', 'report_generator'));
        $m_form->setType('job_role_name',PARAM_TEXT);
        if ($job_role_id) {
            $m_form->setDefault('job_role_name',report_generator_get_job_role_name($job_role_id));
        }//if_id

        /* ADD List with all outcomes */
        $m_form->addElement('header', 'outcomes', get_string('related_outcomes', 'report_generator'));
        $m_form->addElement('html', '<div class="level-wrapper">');
            list($out_job_roles,$out_selected) = report_generator_get_outcome_list_with_selected($job_role_id);
            $select = $m_form->addElement('select',
                                           REPORT_GENERATOR_OUTCOME_LIST,
                                           get_string(REPORT_GENERATOR_OUTCOME_LIST, 'report_generator'),
                                           $out_job_roles,
                                           $level_select_attr);

            $select->setMultiple(true);
            $m_form->setDefault(REPORT_GENERATOR_OUTCOME_LIST, $out_selected);

        $m_form->addElement('html', '</div>');
        $m_form->addElement('hidden','id');
        $m_form->setDefault('id',$job_role_id);
        $m_form->setType('id',PARAM_INT);

        $this->add_action_buttons();
        $this->set_data($job_role_id);
    }//definition

    function validation($data, $files) {
        global $DB, $CFG, $SESSION;
        $errors = parent::validation($data, $files);

        $job_role_id = $this->_customdata;

        /* Can't be empty */
        if (empty($data['job_role_name'])) {
            $errors['job_role_name'] = get_string('missing_job_role_name','report_generator');
        }else {
            if (!$job_role_id) {
                $bln_exist = report_generator_exists_jobrole($data['job_role_name']);

                if ($bln_exist) {
                    $errors['job_role_name'] = get_string('exists_job_role','report_generator');
                }//if_exits
            }
        }

        return $errors;
    }//validation
}//generator_edit_job_role_form