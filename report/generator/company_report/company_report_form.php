<?php

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once('../locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

    /* Company Report - Form  */
class generator_company_report_form extends moodleform {
    function  definition(){
        $m_form = $this->_form;
        $advanced   = $this->_customdata;

        $m_form->addElement('header', 'report', get_string('report'));
        $m_form->addElement('html', '<div class="level-wrapper">');

        /* Format Report */
        $list = array(
            COMPANY_REPORT_FORMAT_SCREEN        => get_string('preview', 'report_generator'),
            COMPANY_REPORT_FORMAT_SCREEN_EXCEL  => get_string('excel', 'report_generator')
        );

        $m_form->addElement('select',
                            COMPANY_REPORT_FORMAT_LIST,
                            get_string('report_format_list', 'report_generator'),
                            $list);

        $m_form->addElement('html', '</div>');

        $this->add_action_buttons(true, get_string('create_report', 'report_generator'));

        $m_form->addElement('hidden','advanced');
        $m_form->setType('advanced',PARAM_INT);
        $m_form->setDefault('advanced',$advanced);
    }//definition
}//company_report_form