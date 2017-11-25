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
 * First access - First access
 *
 * @package
 * @subpackage
 * @copyright       2012    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    10/11/2014
 * @author          eFaktor     (fbv)
 *
 * @updateDate      12/06/2017
 * @author          eFaktor     (fbv)
 */

require_once($CFG->dirroot.'/lib/formslib.php');

class first_access_form extends moodleform {
    function definition (){
        /* Variables    */
        global $CFG;
        $userId         = null;
        $countries      = null;
        $muniProfile    = null;
        $newfield       = null;
        $infoUser       = null;

        // Form
        $form   = $this->_form;

        $userId = $this->_customdata;
        $infoUser = get_complete_user_data('id',$userId);

        // Header
        $form->addElement('header', 'generic', get_string('general'));
        $form->setExpanded('generic',true);

        // Firstname
        $form->addElement('text','firstname',get_string('firstname'),'maxlength="100" size="30"');
        $form->addRule('firstname','required','required', null, 'client');
        $form->setType('firstname',PARAM_TEXT);
        if ($infoUser->firstname) {
            $form->setDefault('firstname',$infoUser->firstname);
        }//first_name

        // Lastname
        $form->addElement('text','lastname',get_string('lastname'),'maxlength="100" size="30"');
        $form->addRule('lastname','required','required',null,'client');
        $form->setType('lastname',PARAM_TEXT);
        if ($infoUser->lastname) {
            $form->setDefault('lastname',$infoUser->lastname);
        }//surname

        // Email address
        $form->addElement('text','email',get_string('email'),'maxlength="100" size="30"');
        $form->addRule('email','required','required',null,'client');
        $form->setType('email',PARAM_TEXT);
        if ($infoUser->email) {
            $form->setDefault('email',$infoUser->email);
        }//email


        // Country
        $countries = get_string_manager()->get_list_of_countries();
        $countries = array('' => get_string('selectacountry') . '...') + $countries;
        $form->addElement('select','country',get_string('selectacountry'),$countries);
        if ($infoUser->country) {
            $form->setDefault('country', $infoUser->country);
        }else {
            if (!empty($CFG->country)) {
                $form->setDefault('country', $CFG->country);
            }
        }//country

        // Kommune
        $muniProfile = FirstAccess::get_municipality_profile();
        if ($muniProfile) {
            // Header - County/kommune
            $form->addElement('header', 'muni', get_string('header_muni', 'local_first_access'));
            $form->setExpanded('muni',true);

            require_once($CFG->dirroot.'/user/profile/lib.php');
            require_once($CFG->dirroot . '/user/profile/field/'.$muniProfile->datatype.'/field.class.php');

            $newfield = 'profile_field_'.$muniProfile->datatype;
            $formfield = new $newfield($muniProfile->id, $userId);
            $formfield->edit_field($form);
        }//if_muniProfile

        // Buttons
        $buttons = array();
        $buttons[] = $form->createElement('submit','submitbutton',get_string('btn_save_course', 'local_first_access'));
        $buttons[] = $form->createElement('submit','submitbutton2',get_string('btn_save_competence', 'local_first_access'));
        //$buttons[] = $form->createElement('cancel');

        $form->addGroup($buttons, 'buttonar', '', array(' '), false);
        $form->setType('buttonar', PARAM_RAW);
        $form->closeHeaderBefore('buttonar');

        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);
        $form->setDefault('id',$userId);

        // Action buttons
        //$this->add_action_buttons(false, get_string('btn_save','local_first_access'));
    }//definition

    /**
     * @param       array $data
     * @param       array $files
     *
     * @return      array
     * @throws      Exception
     * @throws      coding_exception
     *
     * @updateDate  27/10/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Check if it is a fake email
     */
    function validation($data, $files) {
        /* Variables */
        $index  = null;
        $errors = parent::validation($data, $files);

        $index   = strpos($data['email'],'@byttmegut.no');
        if ($index) {
            $errors['email'] = get_string('invalidemail');
        }

        return $errors;
    }//validation
}//first_access_form