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
 * @updateDate  10/09/2012
 * @author      eFaktor     (fbv)
 *
 * Add a new company into a specific level
 *
 * @updateDate  24/01/2015
 * @author      eFaktor     (fbv)
 *
 * Description
 * Update to Level Zero.
 * - Remove Counties and Municipalities
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/* Form to add a new company into one level */
class manager_add_company_structure_form extends moodleform {
    function definition() {
        /* Variables    */
        global $SESSION;
        $parent_info    = null;
        $attr           = '';
        $default        = 1;

        $m_form = $this->_form;

        /* General Settings */
        $text_attr = array(
            'class' => 'text-input',
            'size'  => '50'
        );

        $level= $this->_customdata;


        $m_form->addElement('header', 'header_level_' . $level, get_string('company_structure','report_manager') . ' - ' . get_string('company_structure_level','report_manager',$level));
        /* Add reference's parents */
        $parents = $SESSION->parents;
        /* Add Parents */
        for ($i = 0; $i < $level; $i++) {
            $parent_info = company_structure::get_company_info($parents[$i]);
            $m_form->addElement('text','parent_' . $i,get_string('comp_parent','report_manager', $i),'size = 50 readonly');
            $m_form->setDefault('parent_' . $i,$parent_info->name);
            $m_form->setType('parent_' . $i,PARAM_TEXT);
        }//for

        /* New Item / Company */
        $m_form->addElement('text', 'name', get_string('add_company_level','report_manager'), $text_attr);
        $m_form->setType('name',PARAM_TEXT);
        $m_form->addRule('name',get_string('required','report_manager'),'required', null, 'client');

        /* Industry Code        */
        $m_form->addElement('text', 'industry_code', get_string('industry_code','report_manager'), $text_attr);
        $m_form->setType('industry_code',PARAM_TEXT);
        $m_form->addRule('industry_code',get_string('required','report_manager'),'required', null, 'client');

        /* Public Check Box     */
        $m_form->addElement('checkbox', 'public','',get_string('public', 'report_manager'));

        /* Public Parent Hide   */
        $m_form->setDefault('public',1);

        $m_form->addElement('hidden','level');
        $m_form->setDefault('level',$level);
        $m_form->setType('level',PARAM_INT);

        $this->add_action_buttons(true);
        $this->set_data($level);
    }//definition

    function validation($data, $files) {
        global $DB, $CFG, $SESSION;
        $errors = parent::validation($data, $files);

        $level = $this->_customdata;
        $parents = $SESSION->parents;
        $bln_exist = false;

        if ($level) {
            $index = $level-1;
            $bln_exist = company_structure::exists_company($level,$data,$parents[$index]);
        }else {
            $bln_exist = company_structure::exists_company($level,$data);
        }
        if ($bln_exist) {
            $errors['name'] = get_string('exists_name','report_manager');
        }//if_exist

        return $errors;
    }//validation
}//manager_add_company_structure_form