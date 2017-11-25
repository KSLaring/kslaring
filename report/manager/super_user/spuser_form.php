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
 * Report Competence Manager - Super Users.
 *
 * @package         report
 * @subpackage      manager/super_user
 * @copyright       2013    eFaktor {@link http://www.efaktor.no}
 * @licence         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    14/10/2015
 * @author          eFaktor     (fbv)
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class manager_spuser_form extends moodleform {
    function definition() {
        /* Variables    */
        global $OUTPUT;
        $addBtn         = null;
        $addAllBtn      = null;
        $removeBtn      = null;
        $removeAllBtn   = null;
        $addSearch      = null;
        $removeSearch   = null;
        $levelZero      = optional_param(SP_USER_COMPANY_STRUCTURE_LEVEL . 0, 0, PARAM_INT);

        /* Form */
        $form         = $this->_form;

        /* Get Extra Info   */
        list($addSearch,$removeSearch,$addSelected,$removeSelected) = $this->_customdata;

        /* Companies Levels Connected With  */
        $form->addElement('header', 'levels_connected', get_string('jr_connected', 'report_manager'));
        $form->setExpanded('levels_connected',true);
        /* Level Zero   */
        $this->Add_CompanyLevel(0,$form);
        /* Level One    */
        $this->Add_CompanyLevel(1,$form);
        /* Level Two    */
        $this->Add_CompanyLevel(2,$form);
        /* Level Three  */
        $this->Add_CompanyLevel(3,$form);

        /* USERS SELECTOR   */
        $form->addElement('header', 'users', get_string('users'));
        $form->setExpanded('users',true);
        $form->addElement('html','<div class="userselector" id="addselect_wrapper">');
            /* Left.    Existing Users      */
            $schoices   = SuperUser::FindSuperUsers_Selector($removeSearch,$levelZero);

            $form->addElement('html','<div class="sel_users_left">');
                $form->addElement('selectgroups','removeselect', '',$schoices,'multiple size="20" id="removeselect"');
                $form->addElement('text','removeselect_searchtext',get_string('search'),'id="removeselect_searchtext"');
                $form->setType('removeselect_searchtext',PARAM_TEXT);
            $form->addElement('html','</div>');//sel_users_left

            /* Actions Buttons  */
            $form->addElement('html','<div class="sel_users_buttons">');
                /* Add Users        */
                $addBtn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $form->addElement('submit','add_sel',$addBtn);

                /* Separator    */
                $form->addElement('html','</br>');

                /* Remove Users     */
                $removeBtn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                $form->addElement('submit','remove_sel',$removeBtn);
            $form->addElement('html','</div>');//sel_users_buttons

            /* Right.   Potential Users     */
            $achoices   = SuperUser::FindPotentialUsers_Selector($addSearch,$levelZero);
            $form->addElement('html','<div class="sel_users_right">');
                $form->addElement('selectgroups','addselect', '',$achoices,'multiple size="20" id="addselect"');
                $form->addElement('text','addselect_searchtext',get_string('search'),'id="addselect_searchtext"');
                $form->setType('addselect_searchtext',PARAM_TEXT);
            $form->addElement('html','</div>');//sel_users_right
        $form->addElement('html','</div>');//sel_jobroles_right

        /* BUTTONS  */
        $buttons = array();
        $buttons[] = $form->createElement('cancel');

        $form->addGroup($buttons, 'buttonar', '', array(' '), false);
        $form->setType('buttonar', PARAM_RAW);
        $form->closeHeaderBefore('buttonar');
    }//definition


    /**
     * @param           $level
     * @param           $form
     *
     * @creationDate    15/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the company selector for a specific level
     */
    function Add_CompanyLevel($level,&$form) {
        /* Variables    */
        $options    = array();
        $my         = null;
        $parent     = null;

        /* Get Company List */
        switch ($level) {
            case 0:
                /* Companies for Level Zero */
                $options    = CompetenceManager::get_companies_level_list($level);

                break;
            default:
                /* Parent*/
                $parent     = optional_param(SP_USER_COMPANY_STRUCTURE_LEVEL . ($level-1), 0, PARAM_INT);

                /* Companies for the current level */
                if ($parent) {
                    $options = CompetenceManager::get_companies_level_list($level,$parent);
                }else {
                    $options[0] = get_string('select_level_list','report_manager');
                }//if_parent

                break;
        }//level

        /* Add Level/ Company List  */
        $select = &$form->addElement('select',
                                      SP_USER_COMPANY_STRUCTURE_LEVEL . $level,
                                      get_string('select_company_structure_level','report_manager',$level),
                                      $options);
        if ($level == 3) {
            $select->setMultiple(true);
            $select->setSize(10);
        }//if_level_three

        /* Get Default value    */
        if ($level == 3) {
            $my         = optional_param_array(SP_USER_COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
        }else {
            $my         = optional_param(SP_USER_COMPANY_STRUCTURE_LEVEL . $level, 0, PARAM_INT);
        }

        /* Set Default Values   */
        $form->setDefault(SP_USER_COMPANY_STRUCTURE_LEVEL . $level,$my);
    }//Add_CompanyLevel

    /**
     * @param       array $data
     * @param       array $files
     *
     * @return      array
     *
     * @creationDate    21/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Validate that there are all data to create/remove a super user
     */
    function validation($data, $files) {
        /* Variables    */
        $levelZero  = SP_USER_COMPANY_STRUCTURE_LEVEL . 0;

        $errors = parent::validation($data, $files);

        /* Get Extra Info   */
        list($addSearch,$removeSearch,$addSelected,$removeSelected) = $this->_customdata;

        /* Check there are users to add */
        if ((isset($data['add_sel']) && $data['add_sel'])) {
            if (!$addSelected) {
                $errors['addselect'] = get_string('required','report_manager');
            }//if_addselect
        }//if_add_sel

        /* Check there are users to remove  */
        if ((isset($data['remove_sel']) && $data['remove_sel'])) {
            if (!$removeSelected) {
                $errors['removeselect'] = get_string('required','report_manager');
            }//if_removeselect
        }//if_remove_sel

        /* Check that there is at least one level selected  */
        if (!$data[$levelZero]) {
            $errors[$levelZero] = get_string('sp_level_required','report_manager');
        }//if_levels_organization

        return $errors;
    }//validation
}//manager_spuser_form