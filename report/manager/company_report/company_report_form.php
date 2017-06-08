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

/* Company Report - Form */
class manager_company_report_form extends moodleform {
    function  definition(){
        $form = $this->_form;
        list($my_hierarchy,$advanced,$IsReporter)  = $this->_customdata;

        /* Company Hierarchy - Levels */
        $form->addElement('header', 'company', get_string('company', 'report_manager'));
        $form->setExpanded('company',true);
        for ($i = 0; $i <= 3; $i++) {
            $this->AddLevel($form,$i,$my_hierarchy,$IsReporter);
        }//for_levels

        /* Format Report    */
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
            /* Add Company List */
            $options = $this->getCompanyList($level,$my_hierarchy,$IsReporter);
            $form->addElement('select',COMPANY_STRUCTURE_LEVEL . $level,
                              get_string('select_company_structure_level', 'report_manager', $level),
                              $options
                             );

            /* Check Only One Company */
            $this->SetOnlyOneCompany($level,$options);

            /* Set default value    */
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

        /* Get My Companies by Level    */
        if ($IsReporter) {
            $levelZero  = array_keys($myHierarchy->competence);

            if (count($levelZero) == 1) {
                $parentZero = implode(',',$levelZero);
            }else {
                $parentZero = optional_param(COMPANY_STRUCTURE_LEVEL . 0, 0, PARAM_INT);
                if ((!$parentZero) && isset($SESSION->selection)) {
                    $parentZero = $SESSION->selection[COMPANY_STRUCTURE_LEVEL . 0];
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
            $parent     = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);

            if ((!$parent) && isset($SESSION->selection)) {
                $parent = $SESSION->selection[COMPANY_STRUCTURE_LEVEL . ($level-1)];
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
                   /* Check if there is only one company */
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

        /* Get Default Value    */
        if (isset($SESSION->selection)) {
            $default = $SESSION->selection[COMPANY_STRUCTURE_LEVEL . $level];
        }else if (isset($SESSION->onlyCompany)) {
            $default = $SESSION->onlyCompany[$level];
        }else {
            $default = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
        }

        /* Set Default  */
        $form->setDefault(COMPANY_STRUCTURE_LEVEL . $level,$default);
    }//setLevelDefault
}//manager_company_report_form