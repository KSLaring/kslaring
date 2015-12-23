<?php
/**
 * Report Competence Manager - Course report Level.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/course_report
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * @creationDate    17/03/2015
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

/* Course Report Level - Form  */
class manager_course_report_level_form extends moodleform {
    function definition() {
        /* General Settings */
        $level_select_attr = array('class' => REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL,
                                   'size' => '10'
                                  );

        $form = $this->_form;
        list($report_level,$myHierarchy,$IsReporter) = $this->_customdata;

        /* Course List  */
        $form->addElement('header', 'course', get_string('course'));
        $form->addElement('html', '<div class="level-wrapper">');
            $options = course_report::Get_CoursesList();
            $form->addElement('select',REPORT_MANAGER_COURSE_LIST,get_string('select_course_to_report', 'report_manager'),$options);
            $form->setDefault(REPORT_MANAGER_COURSE_LIST,0);
            $form->addRule(REPORT_MANAGER_COURSE_LIST, 'required', 'required', 'nonzero', 'client');
            $form->addRule(REPORT_MANAGER_COURSE_LIST, 'required', 'nonzero', null, 'client');
        $form->addElement('html', '</div>');

        /* Company Hierarchy - Levels */
        $form->addElement('header', 'company', get_string('company', 'report_manager'));
        $form->setExpanded('company',true);
        for ($i = 0; $i <= $report_level; $i++) {
            $this->AddLevel($form,$i,$myHierarchy,$IsReporter);
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
            $form->addElement('select',REPORT_MANAGER_COMPLETED_LIST,str_replace(' ...',' : ',get_string('completed_list','report_manager')),$options);
            $form->setDefault(REPORT_MANAGER_COMPLETED_LIST, 4);

            /* Format Report */
            $list = array(
                          COURSE_REPORT_FORMAT_SCREEN        => get_string('preview', 'report_manager'),
                          COURSE_REPORT_FORMAT_SCREEN_EXCEL  => get_string('excel', 'report_manager')
                         );
            /* Format Report */
            $form->addElement('select',COURSE_REPORT_FORMAT_LIST,get_string('report_format_list', 'report_manager'),$list);
        $form->addElement('html', '</div>');

        $form->addElement('hidden','rpt');
        $form->setDefault('rpt',$report_level);
        $form->setType('rpt',PARAM_INT);

        $this->add_action_buttons(true, get_string('create_report', 'report_manager'));
    }//definition

    /**
     * @param           $form
     * @param           $level
     * @param           $myHierarchy
     * @param           $IsReporter
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Level Company Structure
     */
    function AddLevel(&$form,$level,$myHierarchy,$IsReporter){

        $form->addElement('html', '<div class="level-wrapper">');
            /* Add Company List */
            $options = $this->getCompanyList($level,$myHierarchy,$IsReporter);
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
                $form->addRule(COMPANY_STRUCTURE_LEVEL . $level, null, 'required', null, 'client');
                $form->addRule(COMPANY_STRUCTURE_LEVEL . $level, 'required', 'nonzero', null, 'client');
            }//if_level_three

        $form->addElement('html', '</div>');
    }//AddLevel

    /**
     * @param           $level
     * @param           $myHierarchy
     * @param           $IsReporter
     *
     * @return          array
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List
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
                /* Get Only My Companies */
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
     * @creationDate    17/03/2015
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
     * @creationDate    17/03/2015
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

        $form->getElement(REPORT_MANAGER_JOB_ROLE_LIST)->load($options);

    }//Add_JobRoleLevel
}//manager_course_report_level_form
