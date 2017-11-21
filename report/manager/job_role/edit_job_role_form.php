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
 * @updateDate      06/11/2014
 * @author          eFaktor     (fbv)
 *
 * Edit Job Role    (Form)
 *
 * @updateDate      26/10/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Update to super users
 * Update to new java script to load the companies
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class manager_edit_job_role_form extends moodleform {
    function definition () {
        /* General Settings */
        $text_attr = array(
            'class' => 'text-input',
            'size'  => '50'
        );

        /* Form */
        $m_form                     = $this->_form;
        list($jr_info,$myAccess)    = $this->_customdata;

        /* Job Role */
        $m_form->addElement('header', 'name_area', get_string('job_role_name', 'report_manager'));
        $m_form->addElement('text', 'job_role_name', get_string('job_role_name', 'report_manager'));
        $m_form->setDefault('job_role_name',$jr_info->name);
        $m_form->setType('job_role_name',PARAM_TEXT);
        $m_form->addRule('job_role_name',get_string('required','report_manager'),'required', null, 'client');

        /* Add Industry Code (Required) */
        $m_form->addElement('text', 'industry_code', get_string('industry_code','report_manager'), $text_attr);
        $m_form->setDefault('industry_code',$jr_info->industry_code);
        $m_form->setType('industry_code',PARAM_TEXT);
        $m_form->addRule('industry_code', get_string('required','report_manager'), 'required', null, 'client');
        /* Companies Levels Connected With  */
        $m_form->addElement('header', 'levels_connected', get_string('jr_connected', 'report_manager'));
        $m_form->setExpanded('levels_connected',true);
        /* Level Zero   */
        /* Companies connected with super user  */
        $myCompanies    = null;
        $myCompanies = $this->Get_MyLevelAccess($myAccess,$jr_info,0);
        $this->Add_CompanyLevel(0,$jr_info,$myCompanies,$m_form);
        /* Level One    */
        /* Companies connected with super user  */
        $myCompanies    = null;
        $myCompanies = $this->Get_MyLevelAccess($myAccess,$jr_info,1);
        $this->Add_CompanyLevel(1,$jr_info,$myCompanies,$m_form);
        /* Level Two    */
        /* Companies connected with super user  */
        $myCompanies    = null;
        $myCompanies = $this->Get_MyLevelAccess($myAccess,$jr_info,2);
        $this->Add_CompanyLevel(2,$jr_info,$myCompanies,$m_form);
        /* Level Three  */
        /* Companies connected with super user  */
        $myCompanies    = null;
        $myCompanies = $this->Get_MyLevelAccess($myAccess,$jr_info,3);
        $this->Add_CompanyLevel(3,$jr_info,$myCompanies,$m_form);

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
     * @param               $myAccess
     * @param               $jr_info
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
    function Get_MyLevelAccess($myAccess,$jr_info,$level) {
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
                        }else {
                            if ($jr_info->levelZero) {
                                $myLevelAccess = $myAccess[$jr_info->levelZero]->levelOne;
                            }
                        }//if_parent

                        break;
                    case 2:
                        if ($parent) {
                            $myLevelAccess = $myAccess[$parent]->levelTwo;
                        }else {
                            if ($jr_info->levelZero) {
                                $myLevelAccess = $myAccess[$jr_info->levelZero]->levelTwo;
                            }
                        }//if_parent

                        break;
                    case 3:
                        if ($parent) {
                            $myLevelAccess = $myAccess[$parent]->levelThree;
                        }else {
                            if ($jr_info->levelZero) {
                                $myLevelAccess = $myAccess[$jr_info->levelZero]->levelThree;
                            }
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
     * @param           $jr_info
     * @param           $myCompanies
     * @param           $form
     *
     * @creationDate    26/01/0215
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Company Level
     *
     * @updateDate      26/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update Super User
     * Update to new java script to load the companies
     */
    function Add_CompanyLevel($level,$jr_info,$myCompanies,&$form) {
        /* Add Level X      */
        /* Add Company List */
        $options = $this->getCompanyList($level,$jr_info,$myCompanies);
        $select= &$form->addElement('select',
                                    COMPANY_STRUCTURE_LEVEL . $level,
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
     * @param           $myCompanies
     *
     * @return          array
     *
     * @creationDate    26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List
     */
    function getCompanyList($level,$jr_info,$myCompanies) {
        /* Variables    */
        $options        = array();
        /* Level Zero   */
        $parent         = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
        $levelZero      = $jr_info->levelZero;
        $levelOne       = $jr_info->levelOne;
        $levelTwo       = $jr_info->levelTwo;
        $levelThree     = $jr_info->levelThree;

        /* Get Company List */
        switch ($level) {
            case 0:
                $options    = CompetenceManager::get_companies_level_list($level,0,$myCompanies);

                break;
            case 1:
                /* Get Correct Parent       */
                if (!$parent) {
                    $parent = $levelZero;
                }

                if (!$parent) {
                    $options[0] = get_string('select_level_list','report_manager');
                }else {
                    if ($myCompanies) {
                        $options = CompetenceManager::get_companies_level_list($level,$parent,$myCompanies);
                    }else {
                        $options = CompetenceManager::get_companies_level_list($level,$parent);
                    }//if_companies
                }//if_parent

                break;
            case 2:
                /* Get Correct Parent       */
                if (!$parent) {
                    $parent = $levelOne;
                }

                if (!$parent) {
                    $options[0] = get_string('select_level_list','report_manager');
                }else {
                    if ($myCompanies) {
                        $options = CompetenceManager::get_companies_level_list($level,$parent,$myCompanies);
                    }else {
                        $options = CompetenceManager::get_companies_level_list($level,$parent);
                    }//if_companies
                }//if_parent

                break;
            case 3:
                /* Get Correct Parent       */
                if (!$parent) {
                    $parent = $levelTwo;
                }

                if (!$parent) {
                    $options[0] = get_string('select_level_list','report_manager');
                }else {
                    if ($myCompanies) {
                        $options = CompetenceManager::get_companies_level_list($level,$parent,$myCompanies);
                    }else {
                        $options = CompetenceManager::get_companies_level_list($level,$parent);
                    }//if_companies
                }//if_parent

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $level
     * @param           $jr_info
     * @param           $form
     *
     * @creationDate    26/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the company selected
     */
    function setLevelDefault($level,$jr_info,&$form) {
        /* Variables    */
        $default    = null;
        $parent     = null;

        /* Get Default Value    */
        switch ($level) {
            case 0:
                $default = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
                if ($jr_info->levelZero != $default) {
                    $default = $jr_info->levelZero;
                }

                break;
            case 1:
                $default = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
                if ($jr_info->levelOne != $default) {
                    $default = $jr_info->levelOne;
                }

                break;
            case 2:
                $default = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
                if ($jr_info->levelTwo != $default) {
                    $default = $jr_info->levelTwo;
                }

                break;
            case 3:
                $default = optional_param_array(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
                if ($jr_info->levelThree != $default) {
                    $default = $jr_info->levelThree;
                }

                break;
        }//switch_levle


        /* Set Default  */
        $form->setDefault(COMPANY_STRUCTURE_LEVEL . $level,$default);
    }//setLevelDefault

    function validation($data, $files) {
        /* Variables    */
        $selZero    = null;
        $levelZero  = null;
        $selOne     = null;
        $selTwo     = null;
        $selThree   = null;

        $errors = parent::validation($data, $files);

        /* My Access    */
        list($jr_info,$myAccess)    = $this->_customdata;
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
        if (job_role::JobRole_Exists($data['job_role_name'],$data['industry_code'],$data['id'])) {
            $errors['job_role_name']  = get_string('err_job_role','report_manager');
        }

        return $errors;
    }//validation
}//manager_edit_job_role_form