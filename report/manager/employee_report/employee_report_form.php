<?php
/**
 * Report Competence Manager - Course report Level.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/employee_report
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * @creationDate    14/04/2015
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

class manager_employee_report_form extends moodleform {
    function  definition(){
        $form = $this->_form;

        list($my_hierarchy,$IsReporter)  = $this->_customdata;

        /* Company Hierarchy - Levels */
        $form->addElement('header', 'company', get_string('company', 'report_manager'));
        $form->setExpanded('company',true);
        for ($i = 0; $i <= 3; $i++) {
            $this->AddLevel($form,$i,$my_hierarchy,$IsReporter);
        }//for_levels

        /* Outcome List */
        $form->addElement('header', 'outcome', 'Filter');

        $form->addElement('html', '<div class="level-wrapper">');
            $levelZero  = optional_param(COMPANY_STRUCTURE_LEVEL . 0, 0, PARAM_INT);
            $levelOne   = optional_param(COMPANY_STRUCTURE_LEVEL . 1, 0, PARAM_INT);
            $levelTwo   = optional_param(COMPANY_STRUCTURE_LEVEL . 2, 0, PARAM_INT);
            $levelThree = optional_param(COMPANY_STRUCTURE_LEVEL . 3, 0, PARAM_INT);
            if ($levelThree) {
                $outcome_lst = EmployeeReport::GetOutcomes_EmployeeReport($levelZero,$levelOne,$levelTwo,$levelThree);
            }else {
                $outcome_lst    = array();
                $outcome_lst[0] = get_string('select') . '...';
            }//IF_COOKIE

            $form->addElement('select',REPORT_MANAGER_OUTCOME_LIST,get_string('select_outcome_to_report', 'report_manager'),$outcome_lst);
            $form->addRule(REPORT_MANAGER_OUTCOME_LIST, get_string('required','report_manager'), 'required', null, 'client');
            $form->addRule(REPORT_MANAGER_OUTCOME_LIST, get_string('required','report_manager'), 'nonzero', null, 'client');

            /* Completed List   */
            $options = CompetenceManager::GetCompletedList();
            $form->addElement('select',REPORT_MANAGER_COMPLETED_LIST,get_string('expired_next', 'report_manager'),$options);
            $form->setDefault(REPORT_MANAGER_COMPLETED_LIST, 4);
        $form->addElement('html', '</div>');

        $this->add_action_buttons(true, get_string('btn_search', 'report_manager'));
    }//definition

    /**
     * @param           $form
     * @param           $level
     * @param           $IsReporter
     * @param           $my_hierarchy
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Level Company Structure
     */
    function AddLevel(&$form,$level,$my_hierarchy,$IsReporter){
        $form->addElement('html', '<div class="level-wrapper">');
            /* Add Company List */
            $options = $this->getCompanyList($level,$my_hierarchy,$IsReporter);
            $form->addElement('select',COMPANY_STRUCTURE_LEVEL . $level,
                              get_string('select_company_structure_level', 'report_manager', $level),
                              $options
            );
            $this->setLevelDefault($form,$level);

            $form->addRule(COMPANY_STRUCTURE_LEVEL . $level, get_string('required','report_manager'), 'required', null, 'client');
            $form->addRule(COMPANY_STRUCTURE_LEVEL . $level, get_string('required','report_manager'), 'nonzero', null, 'client');
        $form->addElement('html', '</div>');
    }//AddLevel

    /**
     * @param           $level
     * @param           $myHierarchy
     * @param           $IsReporter
     * @return          array
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company list connected to the level
     *
     * @updateDate      15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Companies connected with my level and/or my competence
     */
    function getCompanyList($level,$myHierarchy,$IsReporter) {
        /* Variables    */
        $levelThree     = null;
        $levelTwo       = null;
        $levelOne       = null;
        $levelZero      = null;
        $companies_in   = null;
        $options        = array();

        /* Get My Companies by Level    */
        if (!$IsReporter) {
            list($levelZero,$levelOne,$levelTwo,$levelThree) = CompetenceManager::GetMyCompanies_By_Level($myHierarchy->competence,$myHierarchy->my_level);
        }else {
            $levelZero  = $myHierarchy->competence->levelZero;
            $levelOne   = $myHierarchy->competence->levelOne;
            $levelTwo   = $myHierarchy->competence->levelTwo;
            $levelThree = $myHierarchy->competence->levelThree;
        }//if_IsReporter

        /* Parent*/
        $parent     = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
        switch ($level) {
            case 0:
                /* Only My Companies    */
                if ($levelZero) {
                    $companies_in = implode(',',$levelZero);
                }//if_level_zero

                $options = CompetenceManager::GetCompanies_LevelList($level,null,$companies_in);

                break;
            case 1:
                /* Only My Companies    */
                if ($levelOne) {
                    $companies_in = implode(',',$levelOne);
                }//if_level_One

                if ($parent) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$parent,$companies_in);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE
                break;
            case 2:
                /* Only My Companies    */
                if ($levelTwo) {
                    $companies_in = implode(',',$levelTwo);
                }//if_level_Two

                if ($parent) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$parent,$companies_in);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE

                break;
            case 3:
                if ($levelThree) {
                    $companies_in = implode(',',$levelThree);
                }//if_level_Two

                if ($parent) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$parent,$companies_in);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE
                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $form
     * @param           $level
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the company selected
     */
    function setLevelDefault(&$form,$level) {
        /* Variables    */
        $default    = null;
        $parent     = null;

        /* Get Default Value    */
        $default = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

        /* Set Default  */
        $form->setDefault(COMPANY_STRUCTURE_LEVEL . $level,$default);
    }//setLevelDefault
}//manager_employee_report_form