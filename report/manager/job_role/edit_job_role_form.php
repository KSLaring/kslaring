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
 * @updateDate      06/11/2014
 * @author          eFaktor     (fbv)
 *
 * Edit Job Role    (Form)
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
$PAGE->requires->js('/report/manager/js/manager.js');

class manager_edit_job_role_form extends moodleform {
    function definition () {
        /* General Settings */
        $text_attr = array(
            'class' => 'text-input',
            'size'  => '50'
        );

        /* Form */
        $m_form     = $this->_form;
        $jr_info    = $this->_customdata;

        /* Job Role */
        $m_form->addElement('header', 'name_area', get_string('job_role_name', 'report_manager'));
        $m_form->addElement('text', 'job_role_name', get_string('job_role_name', 'report_manager'));
        if (isset($_COOKIE['jobRole']) && ($_COOKIE['jobRole']) && ($jr_info->name != $_COOKIE['jobRole'])) {
            $m_form->setDefault('job_role_name',$_COOKIE['jobRole']);
        }else {
            $m_form->setDefault('job_role_name',$jr_info->name);
        }//if_jobRole
        $m_form->setType('job_role_name',PARAM_TEXT);
        $m_form->addRule('job_role_name','required','required', null, 'client');

        /* Add Industry Code (Required) */
        $m_form->addElement('text', 'industry_code', get_string('industry_code','report_manager'), $text_attr);
        if (isset($_COOKIE['industryCode']) && ($_COOKIE['industryCode']) && ($jr_info->industry_code != $_COOKIE['industryCode'])) {
            $m_form->setDefault('industry_code',$_COOKIE['industryCode']);
        }else {
            $m_form->setDefault('industry_code',$jr_info->industry_code);
        }//if_industrycode
        $m_form->setType('industry_code',PARAM_TEXT);
        $m_form->addRule('industry_code', 'required', 'required', null, 'client');
        /* Companies Levels Connected With  */
        $m_form->addElement('header', 'levels_connected', get_string('jr_connected', 'report_manager'));
        $m_form->setExpanded('levels_connected',true);
        /* Level Zero   */
        $this->Add_CompanyLevel(0,$jr_info,$m_form);
        /* Level One    */
        $this->Add_CompanyLevel(1,$jr_info,$m_form);
        /* Level Two    */
        $this->Add_CompanyLevel(2,$jr_info,$m_form);
        /* Level Three  */
        $this->Add_CompanyLevel(3,$jr_info,$m_form);

        /* ADD List with all outcomes */
        $m_form->addElement('header', 'outcomes', get_string('related_outcomes', 'report_manager'));
        $m_form->setExpanded('outcomes',true);
        $m_form->addElement('html', '<div class="level-wrapper">');
            list($out_job_roles,$out_selected) = job_role::Get_Outcomes_ConnectedJobRole($jr_info->id);
            $select = $m_form->addElement('select',
                                          REPORT_JR_MANAGER_OUTCOME_LIST,
                                          get_string('outcome_list', 'report_manager'),
                                          $out_job_roles);

            $select->setMultiple(true);
            $select->setSize(10);
            $m_form->setDefault(REPORT_JR_MANAGER_OUTCOME_LIST, $out_selected);
        $m_form->addElement('html', '</div>');

        $m_form->addElement('hidden','id');
        $m_form->setType('id',PARAM_TEXT);
        $m_form->setDefault('id',$jr_info->id);

        $this->add_action_buttons(true);
        $this->set_data($jr_info->id);
    }//definition

    /**
     * @param           $level
     * @param           $jr_info
     * @param           $form
     *
     * @creationDate    26/01/0215
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Company Level
     */
    function Add_CompanyLevel($level,$jr_info,&$form) {
        /* Add Level X      */
        /* Add Company List */
        $options = $this->getCompanyList($level,$jr_info);
        $select= &$form->addElement('select',
                                    REPORT_JR_COMPANY_STRUCTURE_LEVEL . $level,
                                    get_string('select_company_structure_level','report_manager',$level),
                                    $options);
        if ($level == 3) {
            $select->setMultiple(true);
            $select->setSize(10);
        }//if_level_three

        $this->setLevelDefault($level,$jr_info,$form);
    }//Add_CompanyLevel

    /**
     * @param           $level
     * @param           $jr_info
     * @return          array
     *
     * @creationDate    26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List
     */
    function getCompanyList($level,$jr_info) {
        /* Variables    */
        $options = array();

        switch ($level) {
            case 0:
                $options = CompetenceManager::GetCompanies_LevelList($level);

                break;
            case 1:
                if (isset($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero'] != $jr_info->levelZero)) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$_COOKIE['parentLevelZero']);
                }else {
                    if ($jr_info->levelZero) {
                        $options = CompetenceManager::GetCompanies_LevelList($level,$jr_info->levelZero);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }//if_levelInfo
                }//IF_COOKIE

                break;
            case 2:
                if (isset($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne'] != $jr_info->levelOne)) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$_COOKIE['parentLevelOne']);
                }else {
                    if ($jr_info->levelOne) {
                        $options = CompetenceManager::GetCompanies_LevelList($level,$jr_info->levelOne);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }//if_levelInfo
                }//IF_COOKIE

                break;
            case 3:
                if (isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'] != $jr_info->levelTwo)) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$_COOKIE['parentLevelTwo']);
                }else {
                    if ($jr_info->levelTwo) {
                        $options = CompetenceManager::GetCompanies_LevelList($level,$jr_info->levelTwo);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }//if_levelInfo

                }//IF_COOKIE

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $level
     * @param           $jr_info
     * @param           $form
     * @return          mixed
     *
     * @creationDate    26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the company selected
     */
    function setLevelDefault($level,$jr_info,&$form) {

        switch ($level) {
            case 0:
                if (isset($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero']) && ($_COOKIE['parentLevelZero'] != $jr_info->levelZero)) {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '0',$_COOKIE['parentLevelZero']);
                }else {
                    if ($jr_info->levelZero) {
                        $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '0',$jr_info->levelZero);
                    }else {
                        $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '0',0);
                    }//if_levelInfo
                }//if_cookie

                break;
            case 1:
                if (isset($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne']) && ($_COOKIE['parentLevelOne'] != $jr_info->levelOne)) {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '1',$_COOKIE['parentLevelOne']);
                }else {
                    if ($jr_info->levelOne) {
                        $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '1',$jr_info->levelOne);
                    }else {
                        $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '1',0);
                    }//if_levelInfo
                }//if_cookie

                break;
            case 2:
                if (isset($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo']) && ($_COOKIE['parentLevelTwo'] != $jr_info->levelTwo)) {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '2',$_COOKIE['parentLevelTwo']);
                }else {
                    if ($jr_info->levelTwo) {
                        $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '2',$jr_info->levelTwo);
                    }else {
                        $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '2',0);
                    }//if_levelInfo
                }//if_cookie

                break;
            case 3:
                if (isset($_COOKIE['parentLevelThree']) && ($_COOKIE['parentLevelThree']) && ($_COOKIE['parentLevelThree'] != $jr_info->levelThree)) {
                    $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '3',$_COOKIE['parentLevelThree']);
                }else {
                    if ($jr_info->levelThree) {
                        $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '3',$jr_info->levelThree);
                    }else {
                        $form->setDefault(REPORT_JR_COMPANY_STRUCTURE_LEVEL . '3',0);
                    }//if_levelInfo
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
        if (job_role::JobRole_Exists($data['job_role_name'],$data['industry_code'],$data['id'])) {
            $errors['job_role_name']  = get_string('err_job_role','report_manager');
        }

        return $errors;
    }//validation
}//manager_edit_job_role_form