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

require_once($CFG->libdir.'/formslib.php');

/* Course Report Level - Form  */
class manager_course_report_level_form extends moodleform {
    function definition() {
        global $SESSION;
        $default = 0;
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
            if (isset($SESSION->selection)) {
                $default = $SESSION->selection[REPORT_MANAGER_COURSE_LIST];
            }//if_selection
            $form->setDefault(REPORT_MANAGER_COURSE_LIST,$default);
            $form->addRule(REPORT_MANAGER_COURSE_LIST, get_string('required','report_manager'), 'required', 'nonzero', 'client');
            $form->addRule(REPORT_MANAGER_COURSE_LIST, get_string('required','report_manager'), 'nonzero', null, 'client');
        $form->addElement('html', '</div>');

        /* Company Hierarchy - Levels */
        $form->addElement('header', 'company', get_string('company', 'report_manager'));
        $form->setExpanded('company',true);
        for ($i = 0; $i <= $report_level; $i++) {
            $this->AddLevel($form,$i,$myHierarchy,$IsReporter,$report_level);
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

        // Add selected levels
        // Level Zero
        self::add_hide_selection($form,0);
        // Level one
        self::add_hide_selection($form,1);
        // Level two
        self::add_hide_selection($form,2);
        // Level three
        self::add_hide_selection($form,3);
    }//definition

    /**
     * @param           $form
     * @param           $level
     * @param           $myHierarchy
     * @param           $IsReporter
     * @param           $report_level
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Level Company Structure
     */
    function AddLevel(&$form,$level,$myHierarchy,$IsReporter,$report_level){
        $form->addElement('html', '<div class="level-wrapper">');
            /* Add Company List */
            $options = $this->getCompanyList($level,$myHierarchy,$IsReporter,$report_level);
            $select = &$form->addElement('select',
                                         MANAGER_COURSE_STRUCTURE_LEVEL . $level,
                                         get_string('select_company_structure_level', 'report_manager', $level),
                                         $options);

            /* Check Only One Company */
            $this->SetOnlyOneCompany($level,$options);

            /* Set default value    */
            $this->setLevelDefault($form,$level);

            /* Multiple Selection - Level 3 */
            if ($level == 3) {
                $select->setMultiple(true);
                $select->setSize(10);
                $form->addElement('html', '<p class="helptext">' . get_string('help_multi_select', 'report_manager') . '</p>');
            }else {
                $form->addRule(MANAGER_COURSE_STRUCTURE_LEVEL . $level, get_string('required','report_manager'), 'required', null, 'client');
                $form->addRule(MANAGER_COURSE_STRUCTURE_LEVEL . $level, get_string('required','report_manager'), 'nonzero', null, 'client');
            }//if_level_three

        $form->addElement('html', '</div>');
    }//AddLevel

    /**
     * @param           $level
     * @param           $companiesLst
     *
     * @throws          Exception
     *
     * @creationDate    14/04/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * If there is only one company connected, saved it because it will be selected by default.
     */
    function SetOnlyOneCompany($level,$companiesLst) {
        /* Variables    */
        global $SESSION;
        $aux            = null;
        $onlyCompany    = null;

        try {
            /* Check if there is only one company   */
            $aux = $companiesLst;
            unset($aux[0]);
            if (count($aux) == 1) {
                $onlyCompany = implode(',',array_keys($aux));
            }

            /* Save Company */
            if ($onlyCompany) {
                if (!isset($SESSION->onlyCompany)) {
                    $SESSION->onlyCompany = array();
                }

                /* Set the company */
                $SESSION->onlyCompany[$level] = $onlyCompany;
            }else {
                unset($SESSION->onlyCompany);
            }//if_oneCompany
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SetOnlyOneCompany

    /**
     * @param           $level
     * @param           $myHierarchy
     * @param           $IsReporter
     * @param           $report_level
     *
     * @return          array
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List
     */
    function getCompanyList($level,$myHierarchy,$IsReporter,$report_level) {
        /* Variables    */
        global $USER,$SESSION;
        $levelThree     = null;
        $levelTwo       = null;
        $levelOne       = null;
        $levelZero      = null;
        $companies_in   = null;
        $options        = array();
        $parentZero     = null;
        $parent         = null;

        /* Get My Companies by Level    */
        if ($IsReporter) {
            $levelZero  = array_keys($myHierarchy->competence);

            /* Get the right companies based on the report level access */
            $aux = array();
            foreach ($levelZero as $zero) {
                if ($myHierarchy->competence[$zero]->level <= $report_level) {
                    $aux[$zero] = $zero;
                }
            }//for_each
            if ($aux) {
                $levelZero = $aux;
            }//if_aux

            /* If only one company */
            if (count($levelZero) == 1) {
                $parentZero = implode(',',$levelZero);
            }else {
                $parentZero = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . 0, 0, PARAM_INT);
                if ((!$parentZero) && isset($SESSION->selection)) {
                    $parentZero = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . 0];
                }
            }//if_onlyOne

            if ($parentZero) {
                if ($myHierarchy->competence[$parentZero]->level <= $report_level) {
                    $levelOne   = $myHierarchy->competence[$parentZero]->levelOne;
                    $levelTwo   = $myHierarchy->competence[$parentZero]->levelTwo;
                    $levelThree = $myHierarchy->competence[$parentZero]->levelThree;
                }
            }
        }else {
            list($levelZero,$levelOne,$levelTwo,$levelThree) = CompetenceManager::GetMyCompanies_By_Level($myHierarchy->competence,$myHierarchy->my_level);
        }//if_IsReporter

        /* Parent*/
        if ($level) {
            $parent     = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
            if ((!$parent) && isset($SESSION->selection)) {
                $parent = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . ($level-1)];
            }
        }//if_level

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
                    /* If there is only one company */
                    if (isset($SESSION->onlyCompany)) {
                        $options = CompetenceManager::GetCompanies_LevelList($level,$SESSION->onlyCompany[$level-1],$companies_in);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }
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
                    /* If there is only one company */
                    if (isset($SESSION->onlyCompany)) {
                        $options = CompetenceManager::GetCompanies_LevelList($level,$SESSION->onlyCompany[$level-1],$companies_in);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }
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
                    /* If there is only one company */
                    if (isset($SESSION->onlyCompany)) {
                        $options = CompetenceManager::GetCompanies_LevelList($level,$SESSION->onlyCompany[$level-1],$companies_in);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }
                }//if_parent

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $form
     * @param           $level
     *
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
        global $SESSION;
        $default    = null;
        $parent     = null;

        /* Get Default Value    */
        if (isset($SESSION->selection)) {
            $default = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . $level];
        }else if (isset($SESSION->onlyCompany)) {
            $default = $SESSION->onlyCompany[$level];
        }else {
            if ($level == 3) {
                $default = optional_param_array(MANAGER_COURSE_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
            }else {
                $default = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
            }
        }

        /* Set Default  */
        $form->setDefault(MANAGER_COURSE_STRUCTURE_LEVEL . $level,$default);
    }//setLevelDefault

    /**
     * Description
     * Add hide selectors
     * 
     * @param       $form
     * @param       $level
     * 
     * @creationDate    23/03/2017
     * @author          eFaktor     (fbv)
     */
    function add_hide_selection(&$form,$level) {
        /* Variables */
        global $SESSION;
        $default = null;
        
        // Hidde selected levels
        $form->addElement('text','h' . $level,'','style="display:none;"');
        $form->setType('h' . $level,PARAM_TEXT);
        
        // Get default value
        if (isset($SESSION->selection)) {
            $default = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . $level];
        }else if (isset($SESSION->onlyCompany)) {
            if (isset($SESSION->onlyCompany[$level])) {
                $default = $SESSION->onlyCompany[$level];    
            }
        }else {
            $default = 0;
        }

        // Set default value
        $form->setDefault('h' . $level,$default);
    }//add_hide_selection


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
        global $SESSION;
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;
        $options    = array();

        /* Job Roles    */
        switch ($level) {
            case 0:
                /* Level Zero   */
                $levelZero = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

                /* Check old selection */
                if ((!$levelZero) && isset($SESSION->selection)) {
                    $levelZero = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . $level];
                }else if ((!$levelZero) && isset($SESSION->onlyCompany)) {
                    $levelZero = $SESSION->onlyCompany[$level];
                }

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
                $levelZero = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
                /* Level One */
                $levelOne = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

                /* Check old selection */
                if (isset($SESSION->selection)) {
                    if (!$levelZero) {
                        $levelZero = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . ($level - 1)];
                    }//if_levelZero

                    if (!$levelOne) {
                        $levelOne = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . $level];
                    }//if_levleone
                }else if (isset($SESSION->onlyCompany)) {
                    if (!$levelZero) {
                        $levelZero = $SESSION->onlyCompany[$level - 1];
                    }//if_levelZero

                    if (!$levelOne) {
                        $levelOne = $SESSION->onlyCompany[$level];
                    }//if_levleone
                }//if_session


                /* Add Generics --> Only Public Job Roles   */
                if (CompetenceManager::IsPublic($levelZero)) {
                    CompetenceManager::GetJobRoles_Generics($options);
                }//if_isPublic

                /* Job Roles connected with level   */
                if ($levelOne) {
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-1,$levelZero);
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne);
                }//if_level_One

                break;
            case 2:
                /* Level Zero   */
                $levelZero = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-2), 0, PARAM_INT);
                /* Level One    */
                $levelOne = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
                /* Level Two    */
                $levelTwo = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

                /* Check old selection */
                if (isset($SESSION->selection)) {
                    if (!$levelZero) {
                        $levelZero = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . ($level - 2)];
                    }//if_levelZero

                    if (!$levelOne) {
                        $levelOne = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . ($level - 1)];
                    }//if_levleone

                    if (!$levelTwo) {
                        $levelTwo = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . $level];
                    }//if_levelTwo
                }else if (isset($SESSION->onlyCompany)) {
                    if (!$levelZero) {
                        $levelZero = $SESSION->onlyCompany[$level - 2];
                    }//if_levelZero

                    if (!$levelOne) {
                        $levelOne = $SESSION->onlyCompany[$level - 1];
                    }//if_levleone

                    if (!$levelTwo) {
                        $levelTwo = $SESSION->onlyCompany[$level];
                    }//if_levelTwo
                }//if_session

                /* Add Generics --> Only Public Job Roles   */
                if (CompetenceManager::IsPublic($levelOne)) {
                    CompetenceManager::GetJobRoles_Generics($options);
                }//if_isPublic

                /* Job Roles connected with level   */
                if ($levelTwo) {
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-2,$levelZero);
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-1,$levelZero,$levelOne);
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne,$levelTwo);
                }//if_level_Two

                break;
            case 3:
                /* Level Zero   */
                $levelZero  = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-3), 0, PARAM_INT);
                /* Level One    */
                $levelOne   = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-2), 0, PARAM_INT);
                /* Level Two    */
                $levelTwo   = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
                /* Level Three  */
                $levelThree = (Array)optional_param_array(MANAGER_COURSE_STRUCTURE_LEVEL . $level, 0,PARAM_RAW);

                /* Check old selection */
                if (isset($SESSION->selection)) {
                    if (!$levelZero) {
                        $levelZero = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . ($level - 3)];
                    }//if_levelZero

                    if (!$levelOne) {
                        $levelOne = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . ($level - 2)];
                    }//if_levleone

                    if (!$levelTwo) {
                        $levelTwo = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . ($level - 1)];
                    }//if_levelTwo

                    /* Level Three  */
                    if (is_array($levelThree)) {
                        if (in_array('0',$levelThree)) {
                            $levelThree = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . $level];
                        }//if_levleThree
                    }//if_levelThree
                }else if (isset($SESSION->onlyCompany)) {
                    if (!$levelZero) {
                        $levelZero = $SESSION->onlyCompany[$level - 3];
                    }//if_levelZero

                    if (!$levelOne) {
                        $levelOne = $SESSION->onlyCompany[$level - 2];
                    }//if_levleone

                    if (!$levelTwo) {
                        $levelTwo = $SESSION->onlyCompany[$level - 1];
                    }//if_levelTwo

                    if (is_array($levelThree)) {
                        if (in_array('0',$levelThree)) {
                            $levelThree[$SESSION->onlyCompany[3]] = $SESSION->onlyCompany[$level];
                            unset($levelThree[0]);
                        }//if_levleThree
                    }//if_levelThree
                }//if_session

                /* Add Generics --> Only Public Job Roles   */
                if (CompetenceManager::IsPublic($levelTwo)) {
                    CompetenceManager::GetJobRoles_Generics($options);
                }//if_isPublic

                /* Job roles connected with the level */
                if (is_array($levelThree)) {
                    if (!in_array('0',$levelThree)) {
                        $levelThree = implode(',',$levelThree);
                        CompetenceManager::GetJobRoles_Hierarchy($options,$level,$levelZero,$levelOne,$levelTwo,$levelThree);
                    }else {
                        CompetenceManager::GetJobRoles_Hierarchy($options,$level-3,$levelZero);
                        CompetenceManager::GetJobRoles_Hierarchy($options,$level-2,$levelZero,$levelOne);
                        CompetenceManager::GetJobRoles_Hierarchy($options,$level-1,$levelZero,$levelOne,$levelTwo);
                    }//if_level_Three
                }else {
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-3,$levelZero);
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-2,$levelZero,$levelOne);
                    CompetenceManager::GetJobRoles_Hierarchy($options,$level-1,$levelZero,$levelOne,$levelTwo);
                }
                
                break;
        }//switch_level

        $form->getElement(REPORT_MANAGER_JOB_ROLE_LIST)->load($options);
    }//Add_JobRoleLevel
}//manager_course_report_level_form
