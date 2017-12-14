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
 * @subpackage      manager/company_report
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * @creationDate    08/04/2015
 * @author          eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/formslib.php');

// Company report form
class manager_company_report_form extends moodleform {
    function  definition(){
        $form = $this->_form;
        list($my_hierarchy,$advanced,$IsReporter)  = $this->_customdata;

        // Company Hierarchy - Levels
        $form->addElement('header', 'company', get_string('company', 'report_manager'));
        $form->setExpanded('company',true);
        for ($i = 0; $i <= 3; $i++) {
            $this->AddLevel($form,$i,$my_hierarchy,$IsReporter);
        }//for_levels

        // Format Report
        $form->addElement('header', 'report', get_string('report'));
        $form->addElement('html', '<div class="level-wrapper">');
            $list = array(
                          COMPANY_REPORT_FORMAT_SCREEN        => get_string('preview', 'report_manager'),
                          COMPANY_REPORT_FORMAT_SCREEN_EXCEL  => get_string('excel', 'report_manager')
                         );

            $form->addElement('select',COMPANY_REPORT_FORMAT_LIST, get_string('report_format_list', 'report_manager'),$list);
        $form->addElement('html', '</div>');

        $this->add_action_buttons(true, get_string('create_report', 'report_manager'));

        $form->addElement('hidden','advanced');
        $form->setType('advanced',PARAM_INT);
        $form->setDefault('advanced',$advanced);
    }//definition

    /**
     * @param           $form
     * @param           $level
     * @param           $my_hierarchy
     * @param           $IsReporter
     *
     * @creationDate    08/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Level Company Structure
     */
    function AddLevel(&$form,$level,$my_hierarchy,$IsReporter){
        $form->addElement('html', '<div class="level-wrapper">');
            // Add Company List
            $options = $this->getCompanyList($level,$my_hierarchy,$IsReporter);
            $form->addElement('select',COMPANY_STRUCTURE_LEVEL . $level,
                              get_string('select_company_structure_level', 'report_manager', $level),
                              $options
                             );

            // Check Only One Company
            $this->SetOnlyOneCompany($level,$options);

            // Set default value
            $this->setLevelDefault($form,$level);

            $form->addRule(COMPANY_STRUCTURE_LEVEL . $level, get_string('required','report_manager'), 'required', null, 'client');
            $form->addRule(COMPANY_STRUCTURE_LEVEL . $level, get_string('required','report_manager'), 'nonzero', null, 'client');
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
            // Check if there is only one company
            $aux = $companiesLst;
            unset($aux[0]);
            if (count($aux) == 1) {
                $onlyCompany = implode(',',array_keys($aux));
            }

            // Save Company
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
     * @param           $level
     * @param           $myHierarchy
     * @param           $IsReporter
     * @return          array
     *
     * @creationDate    08/04/2015
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the company list connected with the level
     *
     * @updateDate      15/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Companies connected with my level and/or competence
     *
     */
    function getCompanyList($level,$myHierarchy,$IsReporter) {
        /* Variables    */
        global $USER,$SESSION;
        $levelThree     = null;
        $levelTwo       = null;
        $levelOne       = null;
        $levelZero      = null;
        $parent         = null;
        $parentZero     = null;
        $companies_in   = null;
        $options        = array();

        // Get My Companies by Level
        if ($IsReporter) {
            $levelZero  = implode(',',$myHierarchy->competence->levelzero);

            if (count($myHierarchy->competence->levelzero) == 1) {
                $levelOne = implode(',',$myHierarchy->competence->levelone[$levelZero]);
                if (count($myHierarchy->competence->levelone[$levelZero]) == 1) {
                    $levelTwo = implode(',',$myHierarchy->competence->leveltwo[$levelOne]);
                    if (count($myHierarchy->competence->leveltwo[$levelOne]) == 1) {
                        $levelThree = implode(',',$myHierarchy->competence->levelthree[$levelTwo]);
                    }
                }
            }
        }else {
            list($levelZero,$levelOne,$levelTwo,$levelThree) = CompetenceManager::get_my_companies_by_Level($myHierarchy->competence);
        }//if_IsReporter

        // Parent
        if ($level) {
            $parent     = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);
            if ((!$parent) && isset($SESSION->selection)) {
                $parent = $SESSION->selection[COMPANY_STRUCTURE_LEVEL . ($level-1)];
            }//if_selection
        }//if_level

        switch ($level) {
            case 0:
                // Only My Companies
                $options = CompetenceManager::get_companies_level_list($level,null,$levelZero);

                break;
            case 1:
               if ($parent) {
                    $options = CompetenceManager::get_companies_level_list($level,$parent,$levelOne);
                }else if (isset($_COOKIE['level' . ($level-1)]) && $_COOKIE['level' . ($level-1)]) {
                   $options = CompetenceManager::get_companies_level_list($level,$_COOKIE['level' . ($level-1)],$levelOne);
               }else {
                   // Check if there is only one company
                   if (isset($SESSION->onlyCompany)) {
                       $options = CompetenceManager::get_companies_level_list($level,$SESSION->onlyCompany[$level-1],$levelOne);
                   }else {
                       $options[0] = get_string('select_level_list','report_manager');
                   }
               }
                break;
            case 2:
                if ($parent) {
                    $options = CompetenceManager::get_companies_level_list($level,$parent,$levelTwo);
                }else if (isset($_COOKIE['level' . ($level-1)]) && $_COOKIE['level' . ($level-1)]) {
                    $options = CompetenceManager::get_companies_level_list($level,$_COOKIE['level' . ($level-1)],$levelTwo);
                }else {
                    // If there is only one company
                    if (isset($SESSION->onlyCompany)) {
                        $options = CompetenceManager::get_companies_level_list($level,$SESSION->onlyCompany[$level-1],$levelTwo);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }
                }//IF_COOKIE

                break;
            case 3:
                if ($parent) {
                    $options = CompetenceManager::get_companies_level_list($level,$parent,$levelThree);
                }else if (isset($_COOKIE['level' . ($level-1)]) && $_COOKIE['level' . ($level-1)]) {
                    $options = CompetenceManager::get_companies_level_list($level,$_COOKIE['level' . ($level-1)],$levelThree);
                }else {
                    // If there is only one company
                    if (isset($SESSION->onlyCompany)) {
                        $options = CompetenceManager::get_companies_level_list($level,$SESSION->onlyCompany[$level-1],$levelThree);
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
     * @creationDate    08/04/2015
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

        // Get default value
        if (isset($SESSION->selection)) {
            $default = $SESSION->selection[COMPANY_STRUCTURE_LEVEL . $level];
        }else if (isset($SESSION->onlyCompany)) {
            $default = $SESSION->onlyCompany[$level];
        }else if (isset($_COOKIE['level' . $level]) && $_COOKIE['level' . $level]) {
            $default = $_COOKIE['level' . $level];
        }else {
            $default = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
        }

        // Set default
        $form->setDefault(COMPANY_STRUCTURE_LEVEL . $level,$default);
    }//setLevelDefault
}//manager_company_report_form