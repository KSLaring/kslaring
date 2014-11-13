<?php

/**
 * Report generator - Job Role.
 *
 * Description
 *
 * @package         report
 * @subpackage      generator/job_role
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    06/11/2014
 * @author          eFaktor     (fbv)
 *
 * Add Job Role (Form)
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/generator/js/jobrole.js');

class generator_add_job_role_form extends moodleform {
    function definition () {
        /* General Settings */
        $level_select_attr = array(
            'class' => REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL,
            'size'  => '10'
        );

        /* Form */
        $m_form         = $this->_form;

        /* Job Role */
        $m_form->addElement('header', 'name_area', get_string('job_role_name', 'report_generator'));
        $m_form->addElement('text', 'job_role_name', get_string('job_role_name', 'report_generator'));
        $m_form->setType('job_role_name',PARAM_TEXT);
        $m_form->addRule('job_role_name','','required', null, 'server');

        /* Company Structure    */
        $m_form->addElement('header', 'company_structure', get_string('company_structure', 'report_generator'));
        $m_form->setExpanded('company_structure',true);
        /* County    */
        $options        = report_generator_GetCounties_List();
        $m_form->addElement('select','county',get_string('county','report_generator'),$options);
        $m_form->addRule('county','','required', null, 'server');

        /* Level One    */
        $options    = job_role::GetStructureLevel_By_County(1);
        $m_form->addElement('select','level_one' ,get_string('select_company_structure_level','report_generator',1),$options);

        /* Level Two    */
        $options = job_role::GetStructureLevel_By_Parent(2);
        $m_form->addElement('select','level_two',get_string('select_company_structure_level','report_generator',2),$options);

        /* Level Three  */
        $options = job_role::GetStructureLevel_By_Parent(3);
        $m_form->addElement('select','level_three',get_string('select_company_structure_level','report_generator',3),$options);


        /* ADD List with all outcomes */
        $m_form->addElement('header', 'outcomes', get_string('related_outcomes', 'report_generator'));
        $m_form->setExpanded('outcomes',true);
        $m_form->addElement('html', '<div class="level-wrapper">');
            list($out_job_roles,$out_selected) = job_role::Get_Outcomes_ConnectedJobRole();
            $select = $m_form->addElement('select',
                                          REPORT_GENERATOR_OUTCOME_LIST,
                                          get_string(REPORT_GENERATOR_OUTCOME_LIST, 'report_generator'),
                                          $out_job_roles,
                                          $level_select_attr);

            $select->setMultiple(true);
            $m_form->setDefault(REPORT_GENERATOR_OUTCOME_LIST, $out_selected);
        $m_form->addElement('html', '</div>');

        /* hidden Level Three  */
        $m_form->addElement('text','hidden_level_three',null,'style="visibility:hidden;height:0px;"');
        $m_form->setType('hidden_level_three',PARAM_TEXT);
        $m_form->setDefault('hidden_level_three',0);

        $this->add_action_buttons();
    }//definition

    function validation($data, $files) {
        $errors = parent::validation($data, $files);


        /* New Function to check if the Job Role just exists*/

        return $errors;
    }//validation
}//generator_add_job_role_form