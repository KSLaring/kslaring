<?php

/**
 * Report generator - Employee report.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator/company_report/
 * @copyright   2014 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  21/02/2014
 * @author      eFaktor     (fbv)
 *
 */

require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/generator/js/libdev.js');

class generator_employee_report_form extends moodleform {
    function  definition(){
        $form = $this->_form;

        /* Outcome List */
        $form->addElement('header', 'outcome', 'Filter');

        $form->addElement('html', '<div class="level-wrapper">');
                $outcome_lst = report_generator_EmployeeReport_getOutcomes();

            $form->addElement('select',REPORT_GENERATOR_OUTCOME_LIST,get_string('select_outcome_to_report', 'report_generator'),$outcome_lst);
            $form->addRule(REPORT_GENERATOR_OUTCOME_LIST, null, 'required');

            $form->addElement('select',REPORT_GENERATOR_COMPLETED_LIST,get_string('expired_next', 'report_generator'),report_generator_get_completed_list());
            $form->setDefault(REPORT_GENERATOR_COMPLETED_LIST, 4);
        $form->addElement('html', '</div>');

        $this->add_action_buttons(true, get_string('btn_search', 'report_generator'));
    }//definition

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        /* Check Outcome */
        if (!$data[REPORT_GENERATOR_OUTCOME_LIST]){
            $errors[REPORT_GENERATOR_OUTCOME_LIST] = get_string('required');
        }//if_outcome_list

        return $errors;
    }//validation
}//generator_employee_report_form