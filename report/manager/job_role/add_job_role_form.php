<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
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
 * @updateDate      26/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Update to Super User
 * Update to new java script to load the companies
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class manager_add_job_role_form extends moodleform {
    function definition () {
        /* General Settings */
        $text_attr = array(
            'class' => 'text-input',
            'size'  => '50'
        );

        /* Form */
        $m_form         = $this->_form;

        /* My Access    */
        $myAccess = $this->_customdata;

        /* Job Role */
        $m_form->addElement('header', 'name_area', get_string('job_role_name', 'report_manager'));
        $m_form->addElement('text', 'job_role_name', get_string('job_role_name', 'report_manager'));

        $m_form->setType('job_role_name',PARAM_TEXT);
        $m_form->addRule('job_role_name',get_string('required','report_manager'),'required', null, 'client');

        /* Add Industry Code (Required) */
        $m_form->addElement('text', 'industry_code', get_string('industry_code','report_manager'), $text_attr);
        $m_form->setType('industry_code',PARAM_TEXT);
        $m_form->addRule('industry_code',get_string('required','report_manager'),'required', null, 'client');

        /* Companies Levels Connected With  */
        $m_form->addElement('header', 'levels_connected', get_string('jr_connected', 'report_manager'));
        $m_form->setExpanded('levels_connected',true);
        /* Level Zero   */
        /* Companies connected with super user  */
        $myCompanies    = null;
        $myCompanies = $this->Get_MyLevelAccess($myAccess,0);
        $this->Add_CompanyLevel(0,$m_form,$myCompanies);
        /* Level One    */
        /* Companies connected with super user  */
        $myCompanies    = null;
        $myCompanies = $this->Get_MyLevelAccess($myAccess,1);
        $this->Add_CompanyLevel(1,$m_form,$myCompanies);
        /* Level Two    */
        /* Companies connected with super user  */
        $myCompanies    = null;
        $myCompanies    = $this->Get_MyLevelAccess($myAccess,2);
        $this->Add_CompanyLevel(2,$m_form,$myCompanies);
        /* Level Three  */
        /* Companies connected with super user  */
        $myCompanies    = null;
        $myCompanies = $this->Get_MyLevelAccess($myAccess,3);
        $this->Add_CompanyLevel(3,$m_form,$myCompanies);

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
     * @param               $myAccess
     * @param               $level
     *
     * @return          null|string
     * @throws              Exception
     *
     * @creationDate        23/10/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get companies connected with super user
     */
    function Get_MyLevelAccess($myAccess,$level) {
        /* Variables    */
        $myLevelAccess  = null;
        $parent         = null;

        try {
            if ($myAccess) {
                $parent     = optional_param(COMPANY_STRUCTURE_LEVEL . 0, 0, PARAM_INT);

                switch ($level) {
                    case 0:
                        $myLevelAccess = implode(',',array_keys($myAccess));

                        break;
                    case 1:
                        if ($parent) {
                            $myLevelAccess = $myAccess[$parent]->levelOne;
                        }//if_parent

                        break;
                    case 2:
                        if ($parent) {
                            $myLevelAccess = $myAccess[$parent]->levelTwo;
                        }//if_parent

                        break;
                    case 3:
                        if ($parent) {
                            $myLevelAccess = $myAccess[$parent]->levelThree;
                        }//if_parent

                        break;
                }//switch_level
            }//if_myAccess

            return $myLevelAccess;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyLevelAccess

    /**
     * @param           $level
     * @param           $form
     * @param           $myCompanies
     *
     * @creationDate    26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the company selector for a specific level
     *
     * @updateDate      26/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update to super user
     */
    function Add_CompanyLevel($level,&$form,$myCompanies) {
        /* Add Level X      */
        /* Add Company List */
        $options = $this->getCompanyList($level,$myCompanies);
        $select= &$form->addElement('select',
                                    COMPANY_STRUCTURE_LEVEL . $level,
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
     * @param           $myCompanies
     *
     * @return          array
     *
     * @creationDate    26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List
     *
     * @updateDate      26/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update to super user
     * Update to new java script to load the companies
     */
    function getCompanyList($level,$myCompanies) {
        /* Variables    */
        $options        = array();

        /* Get Company List */
        switch ($level) {
            case 0:
                /* Companies for Level Zero */
                $options    = CompetenceManager::get_companies_level_list($level,0,$myCompanies);

                break;
            default:
                /* Parent*/
                $parent     = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);

                /* Companies for the current level */
                if ($parent) {
                    $options = CompetenceManager::get_companies_level_list($level,$parent,$myCompanies);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//if_parent

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $level
     * @param           $form
     *
     * @creationDate    26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the company selected
     *
     * @updateDate      26/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update to new java script to load the companies
     */
    function setLevelDefault($level,&$form) {
        /* Variables    */
        $default    = null;
        $parent     = null;

        /* Get Default Value    */
        if ($level != 3) {
            $default = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
        }else {
            $default = optional_param_array(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
        }

        /* Set Default  */
        $form->setDefault(COMPANY_STRUCTURE_LEVEL . $level,$default);
    }//setLevelDefault

    /**
     * @param       array $data
     * @param       array $files
     *
     * @return      array
     *
     * @updateDate  26/10/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Update to super user
     */
    function validation($data, $files) {
        /* Variables    */
        $selZero    = null;
        $levelZero  = null;
        $selOne     = null;
        $selTwo     = null;
        $selThree   = null;

        $errors = parent::validation($data, $files);

        /* My Access    */
        $myAccess = $this->_customdata;
        if ($myAccess) {
            $selZero = COMPANY_STRUCTURE_LEVEL . 0;
            if (!$data[$selZero]) {
                $errors[$selZero]  = get_string('required','report_manager');
            }else {
                $levelZero  = $myAccess[$data[$selZero]];
                if ($levelZero->levelOne) {
                    $selOne     = COMPANY_STRUCTURE_LEVEL . 1;
                    if (!$data[$selOne]) {
                        $errors[$selOne]  = get_string('required','report_manager');
                    }else {
                        if ($levelZero->levelTwo) {
                            $selTwo     = COMPANY_STRUCTURE_LEVEL . 2;
                            if (!$data[$selTwo]) {
                                $errors[$selTwo]  = get_string('required','report_manager');
                            }else {
                                if ($levelZero->levelThree) {
                                    $selThree     = COMPANY_STRUCTURE_LEVEL . 3;
                                    if (!$data[$selThree]) {
                                        $errors[$selTwo]  = get_string('required','report_manager');
                                    }
                                }
                            }
                        }//levelTwo
                    }//sel_levelOne
                }
            }
        }


        /* New Function to check if the Job Role just exists*/
        /* Same Name and Industry Code  */
        if (job_role::JobRole_Exists($data['job_role_name'],$data['industry_code'])) {
            $errors['job_role_name']  = get_string('err_job_role','report_manager');
        }

        return $errors;
    }//validation
}//manager_add_job_role_form