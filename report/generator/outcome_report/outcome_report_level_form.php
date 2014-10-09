<?php

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once('../locallib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/generator/js/libdev.js');

/* Outcome Report Level - Form  */
class generator_outcome_report_level_form extends moodleform {
    function definition() {
        /* General Settings */
        $level_select_attr = array('class' => REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL,
            'size' => '5'
        );

        $m_form = $this->_form;
        list($report_level,$my_hierarchy) = $this->_customdata;

        /* Outcome List */
        $m_form->addElement('header', 'outcome', get_string('outcome', 'report_generator'));
        $m_form->addElement('html', '<div class="level-wrapper">');
                $options = outcome_report::Get_OutcomesList();
                $m_form->addElement('select',REPORT_GENERATOR_OUTCOME_LIST,get_string('select_outcome_to_report', 'report_generator'),$options,'onChange=saveOutcome("outcome_list")');

        if (isset($_COOKIE['outcomeReport'])) {
            $m_form->setDefault(REPORT_GENERATOR_OUTCOME_LIST,$_COOKIE['outcomeReport']);
        }//if_cookie
        $m_form->addElement('html', '</div>');

        /* Company Hierarchy - Levels */
        $m_form->addElement('header', 'company', get_string('company', 'report_generator'));
        $m_form->setExpanded('company',true);
        for ($i = 1; $i <= $report_level; $i++) {
            /* Attributes */
            $name_select = REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . $i;
            $title = get_string('select_company_structure_level', 'report_generator', $i);
            $event = $this->getEvent($i,$report_level);

            /* Select */
            $options = $this->getCompanies_Level($i,$my_hierarchy);
            $m_form->addElement('html', '<div class="level-wrapper">');
                $select = &$m_form->addElement('select',$name_select, $title,$options,$event);
                /* Multiple Selection - Level 3 */
                if ($i == 3) {
                    $select->setMultiple(true);
                    $select->setSize(10);
                    $m_form->addElement('html', '<p class="helptext">' . get_string('help_multi_select', 'report_generator') . '</p>');
                }

            /* Default Values */
            $m_form->setDefault($name_select,$this->getDefault_Value($i));

            /* Disabled Selects */
            if ($i >1) {
                $name_parent = REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . ($i-1);
                $m_form->disabledIf($name_select,$name_parent,'eq',0);
            }//if_>_1
            $m_form->addElement('html', '</div>');
        }//for_level

        /* Job Role */
        $m_form->addElement('header', 'job_role', get_string('job_role', 'report_generator'));
        $m_form->setExpanded('job_role',true);
        $m_form->addElement('html', '<div class="level-wrapper">');
            $options = outcome_report::Get_JobRolesList();
            $select =& $m_form->addElement('select',REPORT_GENERATOR_JOB_ROLE_LIST,get_string('select_job_role', 'report_generator'),$options,$level_select_attr);
            $select->setMultiple(true);
            $m_form->addElement('html', '<p class="helptext">' . get_string('help_multi_select', 'report_generator') . '</p>');
        $m_form->addElement('html', '</div>');

        /* Reports */
        $m_form->addElement('header', 'report', get_string('report'));
        $m_form->setExpanded('report',true);
        $m_form->addElement('html', '<div class="level-wrapper">');
            /* Completed List   */
            $options = report_generator_get_completed_list();
            $m_form->addElement('select',REPORT_GENERATOR_COMPLETED_LIST,get_string('completed_list', 'report_generator'),$options);
            $m_form->setDefault(REPORT_GENERATOR_COMPLETED_LIST, 4);

            /* Format Report */
            $list = array(
                OUTCOME_REPORT_FORMAT_SCREEN        => get_string('preview', 'report_generator'),
                OUTCOME_REPORT_FORMAT_SCREEN_EXCEL  => get_string('excel', 'report_generator')
            );
            /* Format Report */
            $m_form->addElement('select',OUTCOME_REPORT_FORMAT_LIST,get_string('report_format_list', 'report_generator'),$list);
        $m_form->addElement('html', '</div>');


        $m_form->addElement('hidden','rpt');
        $m_form->setDefault('rpt',$report_level);
        $m_form->setType('rpt',PARAM_INT);

        $this->add_action_buttons(true, get_string('create_report', 'report_generator'));
    }//definition

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        /* Check Outcome */
        if (!$data[REPORT_GENERATOR_OUTCOME_LIST]){
            $errors[REPORT_GENERATOR_OUTCOME_LIST] = get_string('missing_outcome','report_generator');
        }//if_outcome_list

        /* Check Level Company */
        if (!$data[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '1']) {
            $errors[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'1'] = get_string('missing_level','report_generator');
        }else if ($data['rpt']>1){
            if (!$data[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '2']) {
                $errors[REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL .'2'] = get_string('missing_level','report_generator');
            }
        }//if_company_level

        return $errors;
    }//validation

    function definition_after_data() {
        global $SESSION;
        $m_form = $this->_form;

        /* Select the last level    */
        if (isset($SESSION->level_three) && $SESSION->level_three) {
            $m_form->getElement(REPORT_GENERATOR_COMPANY_STRUCTURE_LEVEL . '3')->setSelected($SESSION->level_three);
            unset($SESSION->level_three);
        }//set_default_level_three

        /* Select the job roles */
        if (isset($SESSION->job_roles) && $SESSION->job_roles) {
            $m_form->getElement(REPORT_GENERATOR_JOB_ROLE_LIST)->setSelected($SESSION->job_roles);
            unset($SESSION->job_roles);
        }//job_roles_set_defautl
    }

    /**
     * @param           $level
     * @param           $my_hierarchy
     * @return          array
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbV)
     *
     * Description
     * Get the companies list connected with level
     */
    function getCompanies_Level($level,$my_hierarchy) {
        /* Companies List */
        $lst_companies = array();
        $in_one     = null;
        $in_two     = null;

        switch ($my_hierarchy->my_level) {
            case 2:
                $in_one = $my_hierarchy->level_one;

                break;
            case 3:
                $in_one = $my_hierarchy->level_one;
                $in_two = $my_hierarchy->level_two;

                        break;
            default:
                break;
        }//switch_my_level

        switch ($level) {
            case 1:
                $lst_companies = outcome_report::Get_CompaniesLevel($level,$in_one);

                break;
            case 2:
                if (isset($_COOKIE['parentLevelOne'])) {
                    $lst_companies = outcome_report::Get_CompaniesLevel($level,$in_two,$_COOKIE['parentLevelOne']);
                }else {
                    $lst_companies[0] = get_string('select_level_list','report_generator');
                }//IF_COOKIE

                break;
            case 3:
                if (isset($_COOKIE['parentLevelTwo'])) {
                    $lst_companies = outcome_report::Get_CompaniesLevel($level,null,$_COOKIE['parentLevelTwo']);
                }//IF_COOKIE

                break;
            default:
                break;
        }//switch_level

        return $lst_companies;
    }//getCompanies_Level

    /**
     * @param           $level
     * @return          int
     *
     * @creationDate    10/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the default value
     */
    function getDefault_Value($level){
        $str_value = 0;

        /* Get Default Value */
        switch ($level) {
            case 1:
                if (isset($_COOKIE['parentLevelOne']) && isset($_COOKIE['parentLevelOne']) != 0) {
                    $str_value = $_COOKIE['parentLevelOne'];
                }
                break;
            case 2:
                if (isset($_COOKIE['parentLevelTwo'])) {
                    $str_value = $_COOKIE['parentLevelTwo'];
                }
                break;
            case 3:
                $str_value = -1;
                break;
        }//switch

        return $str_value;
    }//setDefault_Value

    /**
     * @param           $parent_level       Parent Level
     * @param           $report_level       Report Level
     * @return          string
     *
     * @creationDate    14/09/2012
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the function It must be called with onchange event is triggered
     */
    function getEvent($parent_level,$report_level) {
        $str_event = '';

        /* Select Event */
        switch ($report_level) {
            case 2:
                if ($parent_level == 1) {
                    $str_event .= 'onChange=GetLevelTwo("company_structure_level1");';
                }
                break;
            case 3:
                switch ($parent_level) {
                    case 1:
                        $str_event .= 'onchange=GetLevelTwo("company_structure_level1");';
                        break;
                    case 2:
                        $str_event .= 'onchange=GetLevelTree("company_structure_level2");';
                        break;
                }//switch_parent
                break;
        }//switch

        return $str_event;
    }//getEvent
}//generator_outcome_report_level_form