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
 * Waiting List - Manual submethod - Form
 *
 * @package         enrol/waitinglist
 * @subpackage      lang
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    18/08/2016
 * @author          efaktor     (fbv)
 *
 * Description
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class managemanual_form extends moodleform {


    function definition() {
        // TODO: Implement definition() method.
        /* Variables */
        global $OUTPUT;
        $achoices   = null;
        $schoices   = null;
        $mForm      = $this->_form;
        $manualClass = 'enrol_waitinglist\method\manual\enrolmethodmanual';
        $seats       = 0;
        $disabled    = '';
        $levelThree     = optional_param('levelThree',0,PARAM_TEXT);
        list($instance,$course,$addSearch,$removeSearch,$addSelected,$removeSelected) = $this->_customdata;
        $noDemanded = false;
        
        /* Available Seats  */
        $seats = $manualClass::GetAvailableSeats($instance,$course);

        if ($seats == 'u') {
            $mForm->addElement('static', 'manual-notification', '', get_string('manual_unlimit', 'enrol_waitinglist'),'id="manual_notification"');
        }else if ($seats > 0) {
            $mForm->addElement('static', 'manual-notification', '', get_string('manual_notification', 'enrol_waitinglist',$seats),'id="manual_notification"');
        }else {
            $disabled = 'disabled';
            $mForm->addElement('static', 'manual-notification', '', get_string('manual_none_seats', 'enrol_waitinglist'),'id="manual_notification"');
        }

        /* Companies Levels Connected With  */
        if ($instance->{ENROL_WAITINGLIST_FIELD_APPROVAL} != COMPANY_NO_DEMANDED) {
            $mForm->addElement('header', 'levels_connected', get_string('company_sel', 'enrol_waitinglist'));
            $mForm->setExpanded('levels_connected',true);

            /* Add Levels   */
            for ($i = 0; $i <= 3; $i++) {
                $this->Add_CompanyLevel($i,$mForm);
            }//for_levels

            $noDemanded = false;
        }else {
            $mForm->addElement('static', 'manual-nodemanded', '', get_string('company_demanded_manual', 'enrol_waitinglist'),'id="manual_not_demanded"');
            $mForm->addElement('hidden', 'level_3');
            $mForm->setType('level_3', PARAM_INT);
            $mForm->setDefault('level_3', 0);
        }
        
        /* Users Connected */
        $mForm->addElement('header', 'users_connected', get_string('users_connected', 'enrol_waitinglist'));
        $mForm->setExpanded('users_connected',true);
        /* Users Selectors - Left Enrolled users */
        $mForm->addElement('html','<div class="userselector" id="addselect_wrapper">');
            /* Left - Users enrolled        */
            $schoices = $manualClass::FindEnrolledUsers($instance->id,$course,$levelThree,$noDemanded,$removeSearch);
            $mForm->addElement('html','<div class="sel_users_left">');
                $strEnrolled = get_string('enrolledusers','enrol');
                $mForm->addElement('html','<label>' . $strEnrolled . '</label>');
                $mForm->addElement('selectgroups','removeselect', '',$schoices,'multiple size="20" id="removeselect"');
                $mForm->addElement('text','removeselect_searchtext',get_string('search'),'id="removeselect_searchtext"');
                $mForm->setType('removeselect_searchtext',PARAM_TEXT);
            $mForm->addElement('html','</div>');//sel_users_left
        $mForm->addElement('html','</div>');//userselector_managers

        /* Actions Buttons  */
        $mForm->addElement('html','<div class="userselector" id="addselect_wrapper">');
            $mForm->addElement('html','<div class="sel_users_buttons">');
                /* Add Users        */
                $addBtn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $mForm->addElement('submit','add_sel',$addBtn,$disabled);

                /* Separator    */
                $mForm->addElement('html','</br>');

                /* Remove Users     */
                $removeBtn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                $mForm->addElement('submit','remove_sel',$removeBtn);
            $mForm->addElement('html','</div>');//sel_users_buttons
        $mForm->addElement('html','</div>');//userselector_managers

        /* Users Selectors - Right Not Enrolled users */
        $mForm->addElement('html','<div class="userselector" id="addselect_wrapper">');
            $achoices = $manualClass::FindCandidatesUsers($instance->id,$course,$levelThree,$noDemanded,$addSearch);
            $mForm->addElement('html','<div class="sel_users_right">');
                $strCandidates = get_string('enrolcandidates','enrol');
                $mForm->addElement('html','<label>' . $strCandidates . '</label>');
                $mForm->addElement('selectgroups','addselect', '',$achoices,'multiple size="20" id="addselect"');
                $mForm->addElement('text','addselect_searchtext',get_string('search'),'id="addselect_searchtext"');
                $mForm->setType('addselect_searchtext',PARAM_TEXT);
            $mForm->addElement('html','</div>');//sel_users_right
        $mForm->addElement('html','</div>');//userselector_managers

        /* BUTTONS  */
        $buttons = array();
        $buttons[] = $mForm->createElement('cancel','btn_back',get_string('back'));

        $mForm->addGroup($buttons, 'buttonar', '', array(' '), false);
        $mForm->setType('buttonar', PARAM_RAW);
        $mForm->closeHeaderBefore('buttonar');

        /* Hidden   */
        $mForm->addElement('hidden','id');
        $mForm->setDefault('id',$instance->id);
        $mForm->setType('id',PARAM_INT);

        $mForm->addElement('hidden','co');
        $mForm->setDefault('co',$course);
        $mForm->setType('co',PARAM_INT);
    }//definition

    function validation($data, $files) {
        /* Variables    */
        $errors = parent::validation($data, $files);

        list($instance,$course,$addSearch,$removeSearch,$addSelected,$removeSelected) = $this->_customdata;
        $manualClass = 'enrol_waitinglist\method\manual\enrolmethodmanual';

        /* Check there are users to add */
        if ((isset($data['add_sel']) && $data['add_sel'])) {
            if (!$addSelected)  {
                $errors['addselect'] = get_string('required');
            }else {
                /* 0 -> Unlimitedd*/
                if ($instance->customint2) {
                    $total = count($addSelected);
                    $seats = $manualClass::GetAvailableSeats($instance,$course);
                    
                    if ($total > $seats) {
                        if ($seats) {
                            $errors['addselect'] = get_string('manual_no_seats','enrol_waitinglist',$seats);
                        }else {
                            $errors['addselect'] = get_string('manual_none_seats','enrol_waitinglist');
                        }
                        
                    }
                }
            }//if_addselect
        }//if_add_sel

        /* Check there are users to remove  */
        if ((isset($data['remove_sel']) && $data['remove_sel'])) {
            if (!$removeSelected) {
                $errors['removeselect'] = get_string('required');
            }//if_removeselect
        }//if_remove_sel

        return $errors;
    }//validation

    /**
     * @param $level
     * @param $form
     * @throws Exception
     * @throws coding_exception
     *
     * Description.
     * Add companies 
     */
    function Add_CompanyLevel($level,&$form) {
        /* Variables    */
        global $USER;
        $options    = array();
        $my         = null;
        $parent     = null;
        $inThree    = null;
        $levelZero  = null;
        $levelOne   = null;
        $levelTwo   = null;
        $myCompetence = null;
        $manualClass = null;
        
        /* Get Company List */
        switch ($level) {
            case 0:
                /* Companies for Level Zero */
                $options    = CompetenceManager::GetCompanies_LevelList($level);
                
                break;
            default:
                /* Parent*/
                $parent     = optional_param('level_' . ($level-1), 0, PARAM_INT);

                /* Companies for the current level */
                if ($parent) {
                    $options = CompetenceManager::GetCompanies_LevelList($level,$parent);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//if_parent

                break;
        }//level

        /* Add Level/ Company List  */
        $form->addElement('select','level_' . $level,get_string('select_company_structure_level','report_manager',$level), $options);

        /* Get Default value    */
        $my = optional_param('level_' . $level, 0, PARAM_INT);
        
        /* Set Default Values   */
        $form->setDefault('level_' . $level,$my);
    }//Add_CompanyLevel
}//managemanual_form