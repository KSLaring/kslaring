<?php

/**
 * Report Competence Manager - Job Role.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/job_role
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
$PAGE->requires->js('/report/manager/js/manager.js');

class manager_add_job_role_form extends moodleform {
    function definition () {
        /* General Settings */
        $text_attr = array(
            'class' => 'text-input',
            'size'  => '50'
        );

        /* Form */
        $m_form         = $this->_form;

        /* Job Role */
        $m_form->addElement('header', 'name_area', get_string('job_role_name', 'report_manager'));
        $m_form->addElement('text', 'job_role_name', get_string('job_role_name', 'report_manager'));
        if (isset($_COOKIE['jobRole']) && ($_COOKIE['jobRole'])) {
            $m_form->setDefault('job_role_name',$_COOKIE['jobRole']);
        }//if_jobRole
        $m_form->setType('job_role_name',PARAM_TEXT);
        $m_form->addRule('job_role_name','required','required', null, 'client');

        /* Add Industry Code (Required) */
        $m_form->addElement('text', 'industry_code', get_string('industry_code','report_manager'), $text_attr);
        if (isset($_COOKIE['industryCode']) && ($_COOKIE['industryCode'])) {
            $m_form->setDefault('industry_code',$_COOKIE['industryCode']);
        }//if_industrycode
        $m_form->setType('industry_code',PARAM_TEXT);
        $m_form->addRule('industry_code','required','required', null, 'client');

        /* Companies Levels Connected With  */
        $m_form->addElement('header', 'levels_connected', get_string('jr_connected', 'report_manager'));
        $m_form->setExpanded('levels_connected',true);
        /* Level Zero   */
        $this->Add_CompanyLevel(0,$m_form);
        /* Level One    */
        $this->Add_CompanyLevel(1,$m_form);
        /* Level Two    */
        $this->Add_CompanyLevel(2,$m_form);
        /* Level Three  */
        $this->Add_CompanyLevel(3,$m_form);

        /* ADD List with all outcomes */
        $m_form->addElement('header', 'outcomes', get_string('related_outcomes', 'report_manager'));
        $m_form->setExpanded('outcomes',true);
        $m_form->addElement('html', '<div class="level-wrapper">');
            list($out_job_roles,$out_selected) = job_role::Get_Outcomes_ConnectedJobRole();
            $select = $m_form->addElement('select',
                                          REPORT_JR_MANAGER_OUTCOME_LIST,
                                          get_string('outcome_list', 'report_manager'),
                                          $out_job_roles);

            $select->setMultiple(true);
            $select->setSize(10);
            $m_form->setDefault(REPORT_JR_MANAGER_OUTCOME_LIST, $out_selected);
        $m_form->addElement('html', '</div>');

        $this->add_action_buttons();
    }//definition

    /**
     * @param           $level
     * @param           $form
     *
     * @creationDate    26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the company selector for a specific level
     */
    function Add_CompanyLevel($level,&$form) {
        /* Add Level X      */
        /* Add Company List */
        $options = $this->getCompanyList($level);
        $select= &$form->addElement('select',
                                    REPORT_JR_COMPANY_STRUCTURE_LEVEL . $level,
                                    get_string('select_company_structure_level','report_manager',$level),
                                    $options);
        if ($level == 3) {
            $select->setMultiple(true);
            $select->setSize(10);
        }//if_level_three

        $this->setLevelDefault($level,$form);
    }//Add_CompanyLevel

    /**
     * @param           $level
     * @return          array
     *
     * @creationDate    26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List
     */
    function getCompanyList($level) {
        /* Variables    */
        $options = array();

        switch ($level) {
            case 0:
                $options = job_role::Get_CompanyList($level);

                break;
            case 1:
                if (isset($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero'])) {
                    $options = job_role::Get_CompanyList(1,$_COOKIE['parentLevelZero']);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE

                break;
            case 2:
                if (isset($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne'])) {
                    $options = job_role::Get_CompanyList(2,$_COOKIE['parentLevelOne']);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE

                break;
            case 3:
                if (isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'])) {
                    $options = job_role::Get_CompanyList(3,$_COOKIE['parentLevelTwo']);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//IF_COOKIE

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $level
     * @param           $form
     * @return          mixed
     *
     * @creationDate    26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the company selected
     */
    function setLevelDefault($level,&$form) {

        switch ($level) {
            case 0:
                if (isset($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero'])) {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . $level,$_COOKIE['parentLevelZero']);
                }else {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . $level,0);
                }//if_cookie

                break;
            case 1:
                if (isset($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne'])) {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . $level,$_COOKIE['parentLevelOne']);
                }else {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . $level,0);
                }//if_cookie

                break;
            case 2:
                if (isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'])) {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . $level,$_COOKIE['parentLevelTwo']);
                }else {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . $level,0);
                }//if_cookie

                break;
            case 3:
                if (isset($_COOKIE['parentLevelThree']) && ($_COOKIE['parentLevelThree'])) {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . $level,$_COOKIE['parentLevelThree']);
                }else {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . $level,0);
                }//if_cookie

                break;
        }//switch

        if ($level) {
            $form->disabledIf(REPORT_JR_COMPANY_STRUCTURE_LEVEL . $level ,REPORT_JR_COMPANY_STRUCTURE_LEVEL . ($level - 1),'eq',0);
        }//if_elvel
    }//setLevelDefault

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        /* New Function to check if the Job Role just exists*/
        /* Same Name and Industry Code  */
        if (job_role::JobRole_Exists($data['job_role_name'],$data['industry_code'])) {
            $errors['job_role_name']  = get_string('err_job_role','report_manager');
        }

        return $errors;
    }//validation
}//manager_add_job_role_form