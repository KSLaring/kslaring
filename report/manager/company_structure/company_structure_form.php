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
 * Report Competence Manager - Company structure.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/company_structure
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  06/09/2012
 * @author      eFaktor     (fbv)
 *
 * @updateDate  24/01/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Add Level Zero
 * Remove Counties/Municipalities
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/* Levels Company - Form  */
class manager_company_structure_form extends moodleform {
    function definition() {
        global $SESSION;
        $m_form = $this->_form;

        /* My Access    */
        $myAccess = $this->_customdata;

        /* Add Level Company Structure      */
        /* Level Zero   */
        /* Companies connected with super user  */
        $myCompanies    = null;
        $myCompanies = $this->Get_MyLevelAccess($myAccess,0);
        $this->AddLevel(0,$m_form,REPORT_MANAGER_IMPORT_0,$myCompanies);
        /* Level One    */
        /* Companies connected with super user  */
        $myCompanies = $this->Get_MyLevelAccess($myAccess,1);
        $this->AddLevel(1,$m_form,REPORT_MANAGER_IMPORT_1,$myCompanies);
        /* Level Two    */
        /* Companies connected with super user  */
        $myCompanies = $this->Get_MyLevelAccess($myAccess,2);
        $this->AddLevel(2,$m_form,REPORT_MANAGER_IMPORT_2,$myCompanies);
        /* Level Three  */
        /* Companies connected with super user  */
        $myCompanies = $this->Get_MyLevelAccess($myAccess,3);
        $this->AddLevel(3,$m_form,REPORT_MANAGER_IMPORT_3,$myCompanies);

        /* Level Four - Employees   */
        $parentThree    = optional_param(COMPANY_STRUCTURE_LEVEL . 3, 0, PARAM_INT);
        $options        = array();
        if ($parentThree) {
            $options = company_structure::get_employee_level($parentThree);
        }else if (isset($SESSION->onlyCompany)) {
            $options = company_structure::get_employee_level($SESSION->onlyCompany[3]);
        }//if
        $m_form->addElement('header', 'employees', get_string('company_structure_employees', 'report_manager'));
        $m_form->setExpanded('employees',true);
        $m_form->addElement('html', '<div class="level-wrapper">');
            $m_form->addElement('select',
                                REPORT_MANAGER_EMPLOYEE_LIST,
                                get_string('company_structure_employees', 'report_manager'),
                                $options,
                                'size = 10 multiple="true"');
            /* Options */
            $m_form->disabledIf(REPORT_MANAGER_EMPLOYEE_LIST,COMPANY_STRUCTURE_LEVEL . '3','eq',0);

            /* Variables    */
            $button = array();
            $button_array_attr = array('class' => 'submit-btn');

            /* Delete Employees   */
            $button[] = $m_form->createElement('button','btn-' . REPORT_MANAGER_DELETE_EMPLOYEES . '3',get_string('remove'),$button_array_attr);
            /* Delete All Employees */
            $button[] = $m_form->createElement('button','btn-' . REPORT_MANAGER_DELETE_ALL_EMPLOYEES . '3',get_string('removeall', 'bulkusers') ,$button_array_attr);
            $m_form->addGroup($button, 'btn' . REPORT_MANAGER_DELETE_EMPLOYEES, '&nbsp;', '&nbsp;', false);
        $m_form->addElement('html', '</div>');

        /* Cancel Button */
        $m_form->addElement('header');
        $m_form->addElement('submit','btn-' . REPORT_MANAGER_COMPANY_CANCEL . '1',get_string('btn_cancel','report_manager'));
    }//definition

    /**
     * @param           $level
     * @param           $form
     * @param           $level_import
     * @param           $myCompanies
     *
     * @creationDate    24/01/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      23/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Level Company Structure
     */
    function AddLevel($level,&$form,$level_import,$myCompanies){
        /* Variables    */
        global $USER;
        $header_ID      = 'header_level_' . $level;
        $header_Label   = get_string(REPORT_MANAGER_COMPANY_STRUCTURE_LEVEL, 'report_manager', $level);


        /* URL to import level  */
        $url_import = new moodle_url('/report/manager/import_structure/import.php');

        /* Header  - Level */
        $form->addElement('header', $header_ID, $header_Label);
        $form->setExpanded($header_ID,true);

        /* Import Structure Link - Level X  */
        $url_import->params(array('level' => $level_import));
        $link = html_writer::link($url_import,get_string('link_level','report_manager',$level_import),array('style' => 'float: right; padding-left: 5px; font-weight:bold; color: #bc8f8f; position:relative;'));
        $form->addElement('html',$link);

        $form->addElement('html', '<div class="level-wrapper">');
            /* Add Company List */
            if (is_siteadmin($USER->id)) {
                $options = $this->getCompanyList($level);
            }else {
                $options = $this->getCompanyList($level,$myCompanies);
            }
            
            $form->addElement('select',
                              COMPANY_STRUCTURE_LEVEL . $level,
                              get_string('level'.$level,'report_manager'),
                              $options);

            /* Check Only One Company */
            $this->SetOnlyOneCompany($level,$options);

            $unlink_btn = $this->setLevelDefault($level,$form);
            if (($level == 0) && $myCompanies) {
                $unlink_btn = ' lnk_disabled';
            }
        
            // Public or private
            $form->addElement('checkbox', 'public_' . $level,'',get_string('public', 'report_manager'),'disabled');

            // Mapped with
            $class = 'label_mapped_hidden';
            $label = "<label id=mapped_" . $level . " class=$class>" . get_string('mapped_with','report_manager') . "</label>";
            $form->addElement('html',$label);
        
            /* Add Action Buttons   */
            $this->AddActionButtons($level,$form,$unlink_btn);
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
        global $SESSION;
        $myLevelAccess  = null;
        $parent         = null;

        try {
            if ($myAccess) {
                $parent     = optional_param(COMPANY_STRUCTURE_LEVEL . 0, 0, PARAM_INT);

                if ((!$parent) && isset($SESSION->onlyCompany)) {
                    $parent = $SESSION->onlyCompany[0];

                }
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
            }

            return $myLevelAccess;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyLevelAccess

    /**
     * @param           $level
     * @param           $myCompanies
     *
     * @return          array
     *
     * @creationDate    24/01/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      23/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the company List connected to the level and parent
     */
    function getCompanyList($level,$myCompanies = null) {
        /* Variables    */
        global $SESSION;
        $options        = array();

        /* Get Company List */
        switch ($level) {
            case 0:
                /* Companies for Level Zero */
                $options    = CompetenceManager::GetCompanies_LevelList($level,0,$myCompanies);

                break;
            default:
                /* Parent*/
                $parent     = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);

                /* Companies for the current level */
                if ($parent) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$parent,$myCompanies);
                }else {
                    /* Check if there is only one company */
                    if (isset($SESSION->onlyCompany)) {
                        $options = CompetenceManager::GetCompanies_LevelList($level,$SESSION->onlyCompany[$level-1],$myCompanies);
                    }else {
                        $options[0] = get_string('select_level_list','report_manager');
                    }
                }//if_parent

                break;
        }//level

        return $options;
    }//getCompanyList

    /**
     * @param           $level
     * @param           $form
     * @return          string
     *
     * @creationDate    24/01/2015
     * @author          eFaktor     (fbv)
     *
     * @updateDate      23/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Set the company selected
     */
    function setLevelDefault($level,&$form) {
        /* Variables    */
        global $SESSION;
        $unlink     = '';
        $default    = null;
        $parent     = null;

        /* Get Default Value    */
        if (isset($SESSION->onlyCompany)) {
            $default = $SESSION->onlyCompany[$level];
        }else {
            $default = optional_param(COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
        }//if_only

        $form->setDefault(COMPANY_STRUCTURE_LEVEL . $level,$default);

        if ($level >0) {
            $parent  = optional_param(COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);

            if ($parent) {
                if (company_structure::company_count_parents($parent) <= 1) {
                    $unlink = 'lnk_disabled ';
                }
            }else {
                $unlink = ' lnk_disabled ';
            }//if_parent
        }

        /* Deactivate levels    */
        if ($level) {
            $form->disabledIf(COMPANY_STRUCTURE_LEVEL . $level ,COMPANY_STRUCTURE_LEVEL . ($level - 1),'eq',0);
        }//if_elvel


        return $unlink;
    }//setLevelDefault

    /**
     * @param           $level
     * @param           $form
     * @param           $unlink_btn
     *
     * @creationDate    24/01/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the actions buttons
     *      - Add new level
     *      - Rename level
     *      - Delete level
     *      - Unlink level
     *
     * @updateDate      23/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update to super users
     */
    function AddActionButtons($level,&$form,$unlink_btn){
        /* Variables    */
        $button = array();
        $button_array_attr = array('class' => 'submit-btn');

        /* Common Buttons   */
        /* Add Level    */
        $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_ADD_ITEM . $level,get_string('add_item','report_manager'),$button_array_attr);
        /* Rename Level */
        $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_RENAME_SELECTED . $level,get_string('rename_selected','report_manager'),$button_array_attr);
        /* Delete Level */
        $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_DELETE_SELECTED . $level,get_string('delete_selected','report_manager'),$button_array_attr);
        /* Move Company to other level */
        if ($level > 0) {
            $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_MOVED_SELECTED . $level,get_string('btn_move','report_manager'),$button_array_attr);
        }//if_level

        /* Manager Button   */
        $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_MANAGERS_SELECTED . $level,get_string('btn_managers','report_manager'),$button_array_attr);
        /* Reporter Button */
        $button[] = $form->createElement('submit','btn-' . REPORT_MANAGER_REPORTERS_SELECTED . $level,get_string('btn_reporters','report_manager'),$button_array_attr);

        /* Add Buttons  */
        $form->addElement('html', '<div class="btn-wrapper">');
            $form->addGroup($button, 'btn_' . $level, '&nbsp;', '&nbsp;', false);
        $form->addElement('html', '</div>');
        $form->addHelpButton('btn_' . $level ,'level_' . $level . '_btn','report_manager');
    }//AddActionButtons
}//manager_company_structure_1_form





