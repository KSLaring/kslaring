<?php
/**
 * Report Competence Manager - Outcome report Level.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/outcome_report
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * @creationDate    26/03/2015
 * @author          eFaktor     (fbv)
 *
 * @updateDate      15/06/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Companies connected with my level and/or my competence
 *
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

/* Outcome Report Level - Form  */
class manager_outcome_report_level_form extends moodleform {
    function definition() {
        /* General Settings */
        $level_select_attr = array('class' => REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL,
            'size' => '10'
        );

        $form = $this->_form;
        list($report_level,$my_hierarchy,$IsReporter) = $this->_customdata;

        /* Outcome List */
        $form->addElement('header', 'outcome', get_string('outcome', 'report_manager'));
        $form->addElement('html', '<div class="level-wrapper">');
            $options = outcome_report::Get_OutcomesList();
            $form->addElement('select',REPORT_MANAGER_OUTCOME_LIST,get_string('select_outcome_to_report', 'report_manager'),$options);
            $form->addRule(REPORT_MANAGER_OUTCOME_LIST, get_string('required','report_manager'), 'required', 'nonzero', 'client');
            $form->addRule(REPORT_MANAGER_OUTCOME_LIST, get_string('required','report_manager'), 'nonzero', null, 'client');
        $form->addElement('html', '</div>');

        /* Company Hierarchy - Levels */
        $form->addElement('header', 'company', get_string('company', 'report_manager'));
        $form->setExpanded('company',true);
        for ($i = 0; $i <= $report_level; $i++) {
            $this->AddLevel($form,$i,$my_hierarchy,$IsReporter);
        }//for_levels

        /* Job Roles    */
        $options    = array();
        $options[0] = get_string('select_level_list','report_manager');
        $form->addElement('header', 'job_role', get_string('job_role', 'report_manager'));
        $form->setExpanded('job_role',true);
        $form->addElement('html', '<div class="level-wrapper">');
            $select =& $form->addElement('select',REPORT_MANAGER_JOB_ROLE_LIST,get_string('select_job_role', 'report_manager'),$options,$level_select_attr);
            $select->setMultiple(true);
            $form->addElement('html', '<p class="helptext">' . get_string('help_multi_select', 'report_manager') . '</p>');
            $this->Add_JobRoleLevel($form,$report_level);
        $form->addElement('html', '</div>');

        /* Reports - Screen/Excel   */
        $form->addElement('header', 'report', get_string('report'));
        $form->setExpanded('report',true);
        $form->addElement('html', '<div class="level-wrapper">');
            /* Completed List   */
            $options = CompetenceManager::GetCompletedList();
            $form->addElement('select',REPORT_MANAGER_COMPLETED_LIST,get_string('expired_next', 'report_manager'),$options);
            $form->setDefault(REPORT_MANAGER_COMPLETED_LIST, 4);

            /* Format Report */
            $list = array(
                            OUTCOME_REPORT_FORMAT_SCREEN        => get_string('preview', 'report_manager'),
                            OUTCOME_REPORT_FORMAT_SCREEN_EXCEL  => get_string('excel', 'report_manager')
                        );
            /* Format Report */
            $form->addElement('select',OUTCOME_REPORT_FORMAT_LIST,get_string('report_format_list', 'report_manager'),$list);
        $form->addElement('html', '</div>');

        $form->addElement('hidden','rpt');
        $form->setDefault('rpt',$report_level);
        $form->setType('rpt',PARAM_INT);

        $this->add_action_buttons(true, get_string('create_report', 'report_manager'));
    }//definition

    /**
     * @param           $form
     * @param           $level
     * @param           $my_hierarchy
     * @param           $IsReporter
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Level Company Structure
     */
    function AddLevel(&$form,$level,$my_hierarchy,$IsReporter){

        $form->addElement('html', '<div class="level-wrapper">');
            /* Add Company List */
            $options = $this->getCompanyList($level,$my_hierarchy,$IsReporter);
            $select = &$form->addElement('select',
                                         COMPANY_STRUCTURE_LEVEL . $level,
                                         get_string('select_company_structure_level', 'report_manager', $level),
                                         $options);
            $this->setLevelDefault($form,$level);

            /* Multiple Selection - Level 3 */
            if ($level == 3) {
                $select->setMultiple(true);
                $select->setSize(10);
                $form->addElement('html', '<p class="helptext">' . get_string('help_multi_select', 'report_manager') . '</p>');
            }else {
                $form->addRule(COMPANY_STRUCTURE_LEVEL . $level, get_string('required','report_manager'), 'required', null, 'client');
                $form->addRule(COMPANY_STRUCTURE_LEVEL . $level, get_string('required','report_manager'), 'nonzero', null, 'client');
            }//if_level_three
        $form->addElement('html', '</div>');
    }//AddLevel

    /**
     * @param           $level
     * @param           $myHierarchy
     * @param           $IsReporter
     * @return          array
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List
     */
    function getCompanyList($level,$myHierarchy,$IsReporter) {
        /* Variables    */
        global $USER;
        $levelThree     = null;
        $levelTwo       = null;
        $levelOne       = null;
        $levelZero      = null;
        $companies_in   = null;
        $options        = array();

        /* Get My Companies by Level    */
        if (($IsReporter) && (!is_siteadmin($USER->id))) {
            $levelZero  = $myHierarchy->competence->levelZero;
            $levelOne   = $myHierarchy->competence->levelOne;
            $levelTwo   = $myHierarchy->competence->levelTwo;
            $levelThree = $myHierarchy->competence->levelThree;
        }else {
            list($levelZero,$levelOne,$levelTwo,$levelThree) = CompetenceManager::GetMyCompanies_By_Level($myHierarchy->competence,$myHierarchy->my_level);
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
                }//if_parent

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
                }//if_parent

                break;
            case 3:
                if ($levelThree) {
                    $companies_in = implode(',',$levelThree);
                }//if_level_Two

                if ($parent) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$parent,$companies_in);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//if_parent

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $form
     * @param           $level
     * @return          string
     *
     * @creationDate    26/03/2015
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
        if ($level == 3) {
            $default = optional_param_array(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
        }else {
            $default = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
        }

        /* Set Default  */
        $form->setDefault(COMPANY_STRUCTURE_LEVEL . $level,$default);
    }//setLevelDefault

    /**
     * @param           $form
     * @param           $level
     *
     * @creationDate    26/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the job role selector to the form
     */
    function Add_JobRoleLevel(&$form,$level) {
        /* Variables    */
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;
        $options    = array();
        $jrOutcomes = array();
        $outcome    = null;

        /* Job Roles    */
        switch ($level) {
            case 0:
                /* Level Zero   */
                $levelZero = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

                /* Job Roles connected with level   */
                if ($levelZero) {
                    /* Add Generics --> Only Public Job Roles   */
                    if (CompetenceManager::IsPublic($levelZero)) {
                        CompetenceManager::GetJobRoles_Generics($options);
                    }//if_isPublic

                    CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero);
                }//if_level_Zero

                break;
            case 1:
                /* Level Zero   */
                $levelZero = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
                /* Level One */
                $levelOne = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

                /* Job Roles connected with level   */
                if ($levelOne) {
                    /* Add Generics --> Only Public Job Roles   */
                    if (CompetenceManager::IsPublic($levelOne)) {
                        CompetenceManager::GetJobRoles_Generics($options);
                    }//if_isPublic

                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-1,$levelZero);
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne);
                }//if_level_One

                break;
            case 2:
                /* Level Zero   */
                $levelZero = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-2), 0, PARAM_INT);
                /* Level One    */
                $levelOne = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
                /* Level Two    */
                $levelTwo = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

                /* Job Roles connected with level   */
                if ($levelTwo) {
                    /* Add Generics --> Only Public Job Roles   */
                    if (CompetenceManager::IsPublic($levelTwo)) {
                        CompetenceManager::GetJobRoles_Generics($options);
                    }//if_isPublic

                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-2,$levelZero);
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-1,$levelZero,$levelOne);
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne,$levelTwo);
                }//if_level_Two

                break;
            case 3:
                /* Level Zero   */
                $levelZero  = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-3), 0, PARAM_INT);
                /* Level One    */
                $levelOne   = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-2), 0, PARAM_INT);
                /* Level Two    */
                $levelTwo   = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
                /* Level Three  */
                $levelThree = optional_param_array(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

                if ($levelThree) {
                    /* Add Generics --> Only Public Job Roles   */
                    if (CompetenceManager::IsPublic($levelTwo)) {
                        CompetenceManager::GetJobRoles_Generics($options);
                    }//if_isPublic

                    $levelThree = implode(',',$levelThree);
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne,$levelTwo,$levelThree);
                }else {
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-3,$levelZero);
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-2,$levelZero,$levelOne);
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-1,$levelZero,$levelOne,$levelTwo);
                }//if_level_Three

                break;
        }//switch_level

        /* Only the Job Roles connected to the outcome and level    */
        $outcome = optional_param(REPORT_MANAGER_OUTCOME_LIST,0,PARAM_INT);
        if (isset($outcome)) {
            $jrOutcomes = outcome_report::Outcome_JobRole_List($outcome);
            if ($jrOutcomes) {
                $jrOutcomes[0] = 0;
                $options = array_intersect_key($options,$jrOutcomes);
            }//if_jr_outcomes
        }//if_outcome_selected

        $form->getElement(REPORT_MANAGER_JOB_ROLE_LIST)->load($options);
    }//Add_JobRoleLevel
}//manager_outcome_report_level_form