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
 * Report Competence Manager - Outcome.
 *
 * Description
 *
 * @package     report
 * @subpackage  manager/outcome
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  13/09/2012
 * @author      eFaktor     (fbv)
 *
 * Edit Outcome
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/* Form to edit a outcome. */
class manager_edit_outcome_form extends moodleform {
    function definition() {
        /* Variables    */
        global $OUTPUT;

        $m_form = $this->_form;

        list($outcome_id,$expiration_id,$addSearch,$removeSearch,$removeSelected) = $this->_customdata;

        $m_form->addElement('header', 'name_area', get_string('expiration_period', 'report_manager'));
        $m_form->addElement('text', 'expiration_period', get_string('expiration_period', 'report_manager'));
        $m_form->setType('expiration_period',PARAM_INT);

        if ($expiration_id) {
            $m_form->setDefault('expiration_period',outcome::Outcome_Expiration($expiration_id));
        }//if_expiration

        $m_form->addElement('header', 'job_roles', get_string('related_job_roles', 'report_manager'));

        /* Job Roles */
        $m_form->addElement('html','<div class="userselector" id="addselect_wrapper">');
            /* Selected Job Roles   */
            $m_form->addElement('html','<div class="sel_users_left">');
                $schoices = outcome::FindJobRoles_Selector($outcome_id,$removeSearch);
                $m_form->addElement('select','removeselect','',$schoices,'multiple size="15"');
                $m_form->setDefault('removeselect',0);
                $m_form->addElement('text','removeselect_searchtext',get_string('search'),'id="removeselect_searchtext"');
                $m_form->setType('removeselect_searchtext',PARAM_TEXT);
            $m_form->addElement('html','</div>');//sel_jobroles_left

            /* Buttons          */
            $m_form->addElement('html','<div class="sel_users_buttons">');
                /* Add Job Roles     */
                $add_btn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $m_form->addElement('submit','add_sel',$add_btn);

                $m_form->addElement('html','</br>');

                /* Remove Job Roles  */
                $remove_btn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                $m_form->addElement('submit','remove_sel',$remove_btn);


            $m_form->addElement('html','</div>');//sel_jobroles_buttons

            /* Job Role List */
            $m_form->addElement('html','<div class="sel_users_right">');
                $selected = implode(',',array_keys($schoices));
                $achoices = outcome::FindPotentialJobRole_Selector($selected,$addSearch);
                $m_form->addElement('select','addselect', '',$achoices,'multiple size="15"');
                $m_form->setDefault('addselect',0);
                $m_form->addElement('text','addselect_searchtext',get_string('search'),'id="addselect_searchtext"');
                $m_form->setType('addselect_searchtext',PARAM_TEXT);
            $m_form->addElement('html','</div>');//sel_jobroles_right
        $m_form->addElement('html','</div>');//job_roles_selector

        $m_form->addElement('hidden','id');
        $m_form->setDefault('id',$outcome_id);
        $m_form->setType('id',PARAM_INT);

        $m_form->addElement('hidden','expid');
        $m_form->setDefault('expid',$expiration_id);
        $m_form->setType('expid',PARAM_INT);

        $this->add_action_buttons();
    }//definition



    function validation($data, $files) {
        /* Variables    */
        $errors = parent::validation($data, $files);

        /* Get Extra Info   */
        list($outcome_id,$expiration_id,$addSearch,$removeSearch,$removeSelected) = $this->_customdata;

        /* Check there are users to add */
        if ((isset($data['add_sel']) && $data['add_sel'])) {
            if (!isset($data['addselect'])) {
                $errors['addselect'] = get_string('required','report_manager');
            }//if_addselect
        }//if_add_sel

        /* Check there are users to remove  */
        if ((isset($data['remove_sel']) && $data['remove_sel'])) {
            if (!isset($data['removeselect'])) {
                $errors['removeselect'] = get_string('required','report_manager');
            }//if_removeselect
        }//if_remove_sel

        return $errors;
    }//validation
}//manager_edit_outcome_form