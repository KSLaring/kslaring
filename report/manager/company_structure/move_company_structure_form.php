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
 * Report Competence Manager - Move Company structure.
 *
 * Description
 *
 * @package         report
 * @subpackage      manager/company_structure
 * @copyright       2010 eFaktor
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    15/04/2016
 * @author          eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class move_company_structure_form extends moodleform {
    function definition() {
        global $SESSION;
        $parents        = $SESSION->parents;
        $companyName    = null;
        $moveFrom       = array();
        list($company,$level) = $this->_customdata;

        $mForm = $this->_form;

        /* Move From    */
        $mForm->addElement('header', 'header_from' , get_string('move_from','report_manager'));
        $mForm->setExpanded('header_from',true);
        /* Company  */
        $companyName = company_structure::get_company_name($company);
        $mForm->addElement('text', 'name', get_string('select_company_structure_level','report_manager',$level), 'size = 50 readonly');
        $mForm->setDefault('name',$companyName);
        $mForm->setType('name',PARAM_TEXT);

        for ($i = ($level-1); $i >= 0; $i--) {
            $moveFrom[$i] = company_structure::get_company_info($parents[$i]);
            $mForm->addElement('text','from_' . $i,get_string('comp_parent','report_manager', $i),'size = 50 readonly');
            $mForm->setDefault('from_' . $i,$moveFrom[$i]->name);
            $mForm->setType('from_' . $i,PARAM_TEXT);
        }//for

        /* Move To      */
        $mForm->addElement('header', 'header_to' , get_string('move_to','report_manager'));
        $mForm->setExpanded('header_to',true);
        for ($i = 0; $i < $level-1; $i++) {
            $parentInfo = company_structure::get_company_info($parents[$i]);
            $mForm->addElement('text','to_' . $i,get_string('comp_parent','report_manager', $i),'size = 50 readonly');
            $mForm->setDefault('to_' . $i,$parentInfo->name);
            $mForm->setType('to_' . $i,PARAM_TEXT);
        }//for

        /* Get companies where it's possible to move */
        switch ($level) {
            case 1:
                $moveTo = CompetenceManager::GetCompanies_LevelList($level-1);
                break;
            default:
                $moveTo = CompetenceManager::GetCompanies_LevelList($level-1,$parents[$level-2]);
                break;
        }

        unset($moveTo[$parents[$level-1]]);
        $mForm->addElement('select','move_to',get_string('comp_parent','report_manager', ($level-1)),$moveTo);
        $mForm->addRule('move_to', get_string('required','report_manager'), 'required', null, 'client');
        $mForm->addRule('move_to', get_string('required','report_manager'), 'nonzero', null, 'client');

        /* Company */
        $mForm->addElement('hidden','id');
        $mForm->setDefault('id',$company);
        $mForm->setType('id',PARAM_INT);

        /* Level */
        $mForm->addElement('hidden','le');
        $mForm->setDefault('le',$level);
        $mForm->setType('le',PARAM_INT);

        $this->add_action_buttons(true);
    }//definition
}//move_company_structure_form