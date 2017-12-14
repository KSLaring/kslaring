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

        // General settings
        $level_select_attr = array('class' => REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL,
                                   'size' => '10'
                                  );

        $form = $this->_form;
        list($report_level,$myHierarchy,$IsReporter,$search) = $this->_customdata;

        // Course list
        $form->addElement('header', 'course', get_string('course'));
        $form->addElement('html', '<div class="level-wrapper">');
            $options = course_report::Get_CoursesList($search);
            $form->addElement('select',REPORT_MANAGER_COURSE_LIST,get_string('select_course_to_report', 'report_manager'),$options);
            if (isset($SESSION->selection)) {
                $default = $SESSION->selection[REPORT_MANAGER_COURSE_LIST];
            }//if_selection
            $form->setDefault(REPORT_MANAGER_COURSE_LIST,$default);
            $form->addRule(REPORT_MANAGER_COURSE_LIST, get_string('required','report_manager'), 'required', 'nonzero', 'client');
            $form->addRule(REPORT_MANAGER_COURSE_LIST, get_string('required','report_manager'), 'nonzero', null, 'client');

            // Search field
            $form->addElement('static', 'search-notification', '', get_string('search_notification', 'report_manager'));
            $form->addElement('text','search',null,'id="id_search"');
            $form->setType('search',PARAM_TEXT);
        $form->addElement('html', '</div>');

        // Company hierarchy levels
        $form->addElement('header', 'company', get_string('company', 'report_manager'));
        $form->setExpanded('company',true);
        for ($i = 0; $i <= $report_level; $i++) {
            $this->AddLevel($form,$i,$myHierarchy,$IsReporter,$report_level);
        }//for_levels

        // Job roles
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

        // Reports - Screen/Excel
        $form->addElement('header', 'report', get_string('report'));
        $form->setExpanded('report',true);
        $form->addElement('html', '<div class="level-wrapper">');
            // Completed list
            $options = CompetenceManager::get_completed_list();
            $form->addElement('select',REPORT_MANAGER_COMPLETED_LIST,str_replace(' ...',' : ',get_string('completed_list','report_manager')),$options);
            $form->setDefault(REPORT_MANAGER_COMPLETED_LIST, 4);

            // Format report
            $list = array(
                          COURSE_REPORT_FORMAT_SCREEN        => get_string('preview', 'report_manager'),
                          COURSE_REPORT_FORMAT_SCREEN_EXCEL  => get_string('excel', 'report_manager')
                         );
            // Format report
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

        // Hide course selected
        $form->addElement('text','hcourse',null,'style=visibility:hidden;height:0px;');
        $form->setType('hcourse',PARAM_INT);
        $form->setDefault('hcourse',0);
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
            // Add company list
            $options = $this->getCompanyList($level,$myHierarchy,$IsReporter,$report_level);
            $select = &$form->addElement('select',
                                         MANAGER_COURSE_STRUCTURE_LEVEL . $level,
                                         get_string('select_company_structure_level', 'report_manager', $level),
                                         $options);

            // Check only one company
            $this->SetOnlyOneCompany($level,$options);

            // Set default value
            $this->setLevelDefault($form,$level);

            // Multiple selection - level 3
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
     * Description
     * If there is only one company connected, saved it because it will be selected by default.
     *
     * @param           $level
     * @param           $companiesLst
     *
     * @throws          Exception
     *
     * @creationDate    14/04/2016
     * @author          eFaktor     (fbv)
     */
    function SetOnlyOneCompany($level,$companiesLst) {
        /* Variables    */
        global $SESSION;
        $aux            = null;
        $onlyCompany    = null;

        try {
            // Check if there is only one company
            $aux = $companiesLst;
            unset($aux[0]);
            if (count($aux) == 1) {
                $onlyCompany = implode(',',array_keys($aux));
            }

            // Save company
            if ($onlyCompany) {
                if (!isset($SESSION->onlyCompany)) {
                    $SESSION->onlyCompany = array();
                }

                // Set the company
                $SESSION->onlyCompany[$level] = $onlyCompany;
            }else {
                unset($SESSION->onlyCompany);
            }//if_oneCompany
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SetOnlyOneCompany

    /**
     * Description
     * Get the company List
     *
     * @param           $level
     * @param           $myHierarchy
     * @param           $IsReporter
     * @param           $report_level
     *
     * @return          array
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     */
    function getCompanyList($level,$myHierarchy,$IsReporter,$report_level) {
        /* Variables    */
        global $SESSION;
        $levelThree     = null;
        $levelTwo       = null;
        $levelOne       = null;
        $levelZero      = null;
        $zero           = null;
        $companies_in   = null;
        $options        = array();
        $parentZero     = null;
        $parent         = null;

        // Get my companies by level
        if ($IsReporter) {
            $hierarchy = null;
            switch ($report_level) {
                case 0:
                    $hierarchy  = $myHierarchy->competence->hierarchyzero;
                    $levelZero  = implode(',',$hierarchy->zero);

                    break;
                case 1:
                    $hierarchy  = $myHierarchy->competence->hierarchyone;
                    $levelZero  = implode(',',$hierarchy->zero);

                    break;
                case 2:
                    $hierarchy = $myHierarchy->competence->hierarchytwo;
                    $levelZero  = implode(',',$hierarchy->zero);

                    break;
                case 3:
                    $hierarchy = $myHierarchy->competence->hierarchythree;
                    $levelZero  = implode(',',$hierarchy->zero);

                    break;
            }//switch_report_level

            if (count($hierarchy->zero) == 1) {
                $levelOne = implode(',',$hierarchy->one[$levelZero]);
                if (count($hierarchy->one[$levelZero]) == 1) {
                    $levelTwo = implode(',',$hierarchy->two[$levelOne]);
                    if (count($hierarchy->two[$levelOne]) == 1) {
                        $levelThree = implode(',',$hierarchy->three[$levelTwo]);
                    }
                }
            }
        }else {
            list($levelZero,$levelOne,$levelTwo,$levelThree) = CompetenceManager::get_my_companies_by_Level($myHierarchy->competence);
        }//if_IsReporter

        // Parent
        if ($level) {
            $parent     = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
            if ((!$parent) && isset($SESSION->selection)) {
                $parent = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . ($level-1)];
            }
        }//if_level

        switch ($level) {
            case 0:
                $options = CompetenceManager::get_companies_level_list($level,null,$levelZero);

                break;
            case 1:
                if ($parent) {
                    $options = CompetenceManager::get_companies_level_list($level,$parent,$levelOne);
                }else {
                    // If only one company
                    if (isset($SESSION->onlyCompany)) {
                        $options = CompetenceManager::get_companies_level_list($level,$SESSION->onlyCompany[$level-1],$levelOne);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }
                }//if_parent

                break;
            case 2:
                if ($parent) {
                    $options = CompetenceManager::get_companies_level_list($level,$parent,$levelTwo);
                }else {
                    // If only one company
                    if (isset($SESSION->onlyCompany)) {
                        $options = CompetenceManager::get_companies_level_list($level,$SESSION->onlyCompany[$level-1],$levelTwo);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }
                }//if_parent

                break;
            case 3:
                if ($parent) {
                    $options = CompetenceManager::get_companies_level_list($level,$parent,$levelThree);
                }else {
                    // Only one company
                    if (isset($SESSION->onlyCompany)) {
                        $options = CompetenceManager::get_companies_level_list($level,$SESSION->onlyCompany[$level-1],$levelThree);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }
                }//if_parent

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * Description
     * Set the company selected
     *
     * @param           $form
     * @param           $level
     *
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     */
    function setLevelDefault(&$form,$level) {
        /* Variables    */
        global $SESSION;
        $default    = null;
        $parent     = null;

        // Get default value
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

        // Set default
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
     * Description
     * Add the job role selector to the form
     *
     * @param           $form
     * @param           $level
     *
     * @creationDate    17/03/2015
     * @author          eFaktor     (fbv)
     */
    function Add_JobRoleLevel(&$form,$level) {
        /* Variables    */
        global $SESSION;
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;
        $options    = array();

        // Job roles
        switch ($level) {
            case 0:
                // Level zero
                $levelZero = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

                // Check old selection
                if ((!$levelZero) && isset($SESSION->selection)) {
                    $levelZero = $SESSION->selection[MANAGER_COURSE_STRUCTURE_LEVEL . $level];
                }else if ((!$levelZero) && isset($SESSION->onlyCompany)) {
                    $levelZero = $SESSION->onlyCompany[$level];
                }

                // Job roles connected with level
                if ($levelZero) {
                    // Add generics --> only public job roles
                    if (CompetenceManager::is_public($levelZero)) {
                        CompetenceManager::get_jobroles_generics($options);
                    }//if_isPublic

                    CompetenceManager::get_jobroles_hierarchy($options,$level,$levelZero);
                }//if_level_Zero

                break;
            case 1:
                // Level zero
                $levelZero = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
                // Level one
                $levelOne = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

                // Check old selection
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


                // Add generics --> only public job roles
                if (CompetenceManager::is_public($levelZero)) {
                    CompetenceManager::get_jobroles_generics($options);
                }//if_isPublic

                // Job roles connected with level
                if ($levelOne) {
                    CompetenceManager::get_jobroles_hierarchy($options,$level-1,$levelZero);
                    CompetenceManager::get_jobroles_hierarchy($options,$level,$levelZero,$levelOne);
                }//if_level_One

                break;
            case 2:
                // Level zero
                $levelZero = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-2), 0, PARAM_INT);
                // Level one
                $levelOne = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
                // Level two
                $levelTwo = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . $level, 0, PARAM_INT);

                // Check old selection
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

                // Add generics --> only public job roles
                if (CompetenceManager::is_public($levelOne)) {
                    CompetenceManager::get_jobroles_generics($options);
                }//if_isPublic

                // Job roles connected with level
                if ($levelTwo) {
                    CompetenceManager::get_jobroles_hierarchy($options,$level-2,$levelZero);
                    CompetenceManager::get_jobroles_hierarchy($options,$level-1,$levelZero,$levelOne);
                    CompetenceManager::get_jobroles_hierarchy($options,$level,$levelZero,$levelOne,$levelTwo);
                }//if_level_Two

                break;
            case 3:
                // Level zero
                $levelZero  = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-3), 0, PARAM_INT);
                // Level one
                $levelOne   = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-2), 0, PARAM_INT);
                // Level two
                $levelTwo   = optional_param(MANAGER_COURSE_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
                // Level three
                $levelThree = (Array)optional_param_array(MANAGER_COURSE_STRUCTURE_LEVEL . $level, 0,PARAM_RAW);

                // Check old selection
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

                    // Level three
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

                // Add generics --> only public job roles
                if (CompetenceManager::is_public($levelTwo)) {
                    CompetenceManager::get_jobroles_generics($options);
                }//if_isPublic

                // Job roles connected with the level
                if (is_array($levelThree)) {
                    if (!in_array('0',$levelThree)) {
                        $levelThree = implode(',',$levelThree);
                        CompetenceManager::get_jobroles_hierarchy($options,$level,$levelZero,$levelOne,$levelTwo,$levelThree);
                    }else {
                        CompetenceManager::get_jobroles_hierarchy($options,$level-3,$levelZero);
                        CompetenceManager::get_jobroles_hierarchy($options,$level-2,$levelZero,$levelOne);
                        CompetenceManager::get_jobroles_hierarchy($options,$level-1,$levelZero,$levelOne,$levelTwo);
                    }//if_level_Three
                }else {
                    CompetenceManager::get_jobroles_hierarchy($options,$level-3,$levelZero);
                    CompetenceManager::get_jobroles_hierarchy($options,$level-2,$levelZero,$levelOne);
                    CompetenceManager::get_jobroles_hierarchy($options,$level-1,$levelZero,$levelOne,$levelTwo);
                }
                
                break;
        }//switch_level

        $form->getElement(REPORT_MANAGER_JOB_ROLE_LIST)->load($options);
    }//Add_JobRoleLevel
}//manager_course_report_level_form
