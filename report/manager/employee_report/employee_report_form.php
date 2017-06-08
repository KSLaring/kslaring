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
            /* Outcomes List */
            $outcome_lst    = array();
            $form->addElement('select',REPORT_MANAGER_OUTCOME_LIST,get_string('select_outcome_to_report', 'report_manager'),$outcome_lst);
            $form->addRule(REPORT_MANAGER_OUTCOME_LIST, get_string('required','report_manager'), 'required', null, 'client');
            $form->addRule(REPORT_MANAGER_OUTCOME_LIST, get_string('required','report_manager'), 'nonzero', null, 'client');
            $this->Add_OutcomesList($form);

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
            $form->addElement('select',EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level,
                              get_string('select_company_structure_level', 'report_manager', $level),
                              $options
            );

            /* Check Only One Company */
            $this->SetOnlyOneCompany($level,$options);

            /* Set default value    */
            $this->setLevelDefault($form,$level);

            $form->addRule(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level, get_string('required','report_manager'), 'required', null, 'client');
            $form->addRule(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level, get_string('required','report_manager'), 'nonzero', null, 'client');
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
        global $USER,$SESSION;
        $levelThree     = null;
        $levelTwo       = null;
        $levelOne       = null;
        $levelZero      = null;
        $companies_in   = null;
        $options        = array();
        $parent         = null;
        $parentZero     = null;

        /* Get My Companies by Level    */
        if ($IsReporter) {
            $levelZero  = array_keys($myHierarchy->competence);

            if (count($levelZero) == 1) {
                $parentZero = implode(',',$levelZero);
            }else {
                $parentZero = optional_param(EMPLOYEE_REPORT_STRUCTURE_LEVEL . 0, 0, PARAM_INT);
                if ((!$parentZero) && isset($SESSION->selection)) {
                    $parentZero = $SESSION->selection[EMPLOYEE_REPORT_STRUCTURE_LEVEL . 0];
                }
            }//if_onlyOne

            if ($parentZero) {
                $levelOne   = $myHierarchy->competence[$parentZero]->levelOne;
                $levelTwo   = $myHierarchy->competence[$parentZero]->levelTwo;
                $levelThree = $myHierarchy->competence[$parentZero]->levelThree;
            }
        }else {
            list($levelZero,$levelOne,$levelTwo,$levelThree) = CompetenceManager::GetMyCompanies_By_Level($myHierarchy->competence,$myHierarchy->my_level);
        }//if_IsReporter

        /* Parent*/
        if ($level) {
            $parent     = optional_param(EMPLOYEE_REPORT_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);

            if ((!$parent) && isset($SESSION->selection)) {
                $parent = $SESSION->selection[EMPLOYEE_REPORT_STRUCTURE_LEVEL . ($level-1)];
            }//if_selection
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
                    /* If there is only one company */
                    if (isset($SESSION->onlyCompany)) {
                        $options = CompetenceManager::GetCompanies_LevelList($level,$SESSION->onlyCompany[$level-1],$companies_in);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }
                }//IF_COOKIE

                break;
            case 3:
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
        global $SESSION;
        $default    = null;
        $parent     = null;

        /* Get Default Value    */
        if (isset($SESSION->selection)) {
            $default = $SESSION->selection[EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level];
        }else if (isset($SESSION->onlyCompany)) {
            $default = $SESSION->onlyCompany[$level];
        }else {
            $default = optional_param(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
        }

        /* Set Default  */
        $form->setDefault(EMPLOYEE_REPORT_STRUCTURE_LEVEL . $level,$default);
    }//setLevelDefault

    /**
     * @param           $form
     *
     * @throws          Exception
     *
     * @creationDate    14/04/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Load outcomes list based on companies selection
     */
    function Add_OutcomesList(&$form) {
        /* Variables    */
        global $SESSION;
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $levelThree = null;
        $outcomeLst = null;

        try {
            /* GEt Parameters   */
            $levelZero  = optional_param(EMPLOYEE_REPORT_STRUCTURE_LEVEL . 0, 0, PARAM_INT);
            $levelOne   = optional_param(EMPLOYEE_REPORT_STRUCTURE_LEVEL . 1, 0, PARAM_INT);
            $levelTwo   = optional_param(EMPLOYEE_REPORT_STRUCTURE_LEVEL . 2, 0, PARAM_INT);
            $levelThree = optional_param(EMPLOYEE_REPORT_STRUCTURE_LEVEL . 3, 0, PARAM_INT);

            /* Check old selection/Only One Company  */
            if (isset($SESSION->selection)) {
                if (!$levelZero) {
                    $levelZero = $SESSION->selection[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '0'];
                }//if_levelZero

                if (!$levelOne) {
                    $levelOne = $SESSION->selection[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '1'];
                }//if_levelOne

                if (!$levelTwo) {
                    $levelTwo = $SESSION->selection[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '2'];
                }//if_levleTwo

                if (!$levelThree) {
                    $levelThree = $SESSION->selection[EMPLOYEE_REPORT_STRUCTURE_LEVEL . '3'];
                }
            }else if (isset($SESSION->onlyCompany)) {
                if (!$levelZero) {
                    $levelZero = $SESSION->onlyCompany[0];
                }//if_levelZero

                if (!$levelOne) {
                    $levelOne = $SESSION->onlyCompany[1];
                }//if_levelOne

                if (!$levelTwo) {
                    $levelTwo = $SESSION->onlyCompany[2];
                }//if_levleTwo

                if (!$levelThree) {
                    $levelThree = $SESSION->onlyCompany[3];
                }
            }//if_selection

            if ($levelThree) {
                $outcomeLst = EmployeeReport::GetOutcomes_EmployeeReport($levelZero,$levelOne,$levelTwo,$levelThree);
            }else {
                $outcomeLst    = array();
                $outcomeLst[0] = get_string('select') . '...';
            }//IF_COOKIE

            $form->getElement(REPORT_MANAGER_OUTCOME_LIST)->load($outcomeLst);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Add_OutcomesList
}//manager_employee_report_form