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
$PAGE->requires->js('/report/manager/js/manager.js');

class manager_employee_report_form extends moodleform {
    function  definition(){
        $form = $this->_form;

        $my_hierarchy  = $this->_customdata;

        /* Company Hierarchy - Levels */
        $form->addElement('header', 'company', get_string('company', 'report_manager'));
        $form->setExpanded('company',true);
        for ($i = 0; $i <= 3; $i++) {
            $this->AddLevel($form,$i,$my_hierarchy);
        }//for_levels

        /* Outcome List */
        $form->addElement('header', 'outcome', 'Filter');

        $form->addElement('html', '<div class="level-wrapper">');
            if (isset($_COOKIE['parentLevelThree']) && ($_COOKIE['parentLevelThree'])) {
                $outcome_lst = EmployeeReport::GetOutcomes_EmployeeReport($my_hierarchy->competence[$_COOKIE['parentLevelThree']]);
            }else {
                $outcome_lst    = array();
                $outcome_lst[0] = get_string('select') . '...';
            }//IF_COOKIE

            $form->addElement('select',REPORT_MANAGER_OUTCOME_LIST,get_string('select_outcome_to_report', 'report_manager'),$outcome_lst);

            if (isset($_COOKIE['outcomeReport'])) {
                $form->setDefault(REPORT_MANAGER_OUTCOME_LIST,$_COOKIE['outcomeReport']);
            }//if_cookie

            $form->addRule(REPORT_MANAGER_OUTCOME_LIST, null, 'required', null, 'client');
            $form->addRule(REPORT_MANAGER_OUTCOME_LIST, 'required', 'nonzero', null, 'client');

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
     * @param           $my_hierarchy
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Level Company Structure
     */
    function AddLevel(&$form,$level,$my_hierarchy){
        $form->addElement('html', '<div class="level-wrapper">');
            /* Add Company List */
            $options = $this->getCompanyList($level,$my_hierarchy);
            $form->addElement('select',EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level,
                              get_string('select_company_structure_level', 'report_manager', $level),
                              $options
            );
            $this->setLevelDefault($form,$level);

            $form->addRule(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level, null, 'required', null, 'client');
            $form->addRule(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level, 'required', 'nonzero', null, 'client');
        $form->addElement('html', '</div>');
    }//AddLevel

    /**
     * @param           $level
     * @param           $my_hierarchy
     * @return          array
     *
     * @creationDate    14/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company list connected to the level
     */
    function getCompanyList($level,$my_hierarchy) {
        /* Variables    */
        $levelThree     = null;
        $levelTwo       = null;
        $levelOne       = null;
        $levelZero      = null;
        $companies_in   = null;
        $options        = array();

        /* Get My Companies by Level    */
        list($levelZero,$levelOne,$levelTwo,$levelThree) = CompetenceManager::GetMyCompanies_By_Level($my_hierarchy->competence);

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

                if (isset($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero'])) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$_COOKIE['parentLevelZero'],$companies_in);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE

                break;
            case 2:
                /* Only My Companies    */
                if ($levelTwo) {
                    $companies_in = implode(',',$levelTwo);
                }//if_level_Two

                if (isset($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne'])) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$_COOKIE['parentLevelOne'],$companies_in);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE

                break;
            case 3:
                if ($levelThree) {
                    $companies_in = implode(',',$levelThree);
                }//if_level_Two

                if (isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'])) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$_COOKIE['parentLevelTwo'],$companies_in);
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
        switch ($level) {
            case 0:
                if (isset($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero'])) {
                    $form->setDefault(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level,$_COOKIE['parentLevelZero']);
                }else {
                    $form->setDefault(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level,0);
                }//if_cookie

                break;
            case 1:
                if (isset($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne'])) {
                    $form->setDefault(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level,$_COOKIE['parentLevelOne']);
                }else {
                    $form->setDefault(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level,0);
                }//if_cookie

                break;
            case 2:
                if (isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'])) {
                    $form->setDefault(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level,$_COOKIE['parentLevelTwo']);
                }else {
                    $form->setDefault(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level,0);
                }//if_cookie

                break;
            case 3:
                if (isset($_COOKIE['parentLevelThree']) && ($_COOKIE['parentLevelThree'])) {
                    $form->setDefault(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level,$_COOKIE['parentLevelThree']);
                }//if_cookie

                break;
        }//switch

        if ($level) {
            $form->disabledIf(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level ,EMPLOYEE_REPORT_STRUCTURE_LEVEL . ($level - 1),'eq',0);
        }//if_level
    }//setLevelDefault
}//manager_employee_report_form