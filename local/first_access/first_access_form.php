<?php
/**
 * First Access
 *
 * Description
 *
 * @package             local
 * @subpackage          first_access
 * @copyright           2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate        18/06/2015
 * @author              eFaktor     (fbv)
 *
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

        /* Form         */
        $form   = $this->_form;

        $userId = $this->_customdata;
        $infoUser = get_complete_user_data('id',$userId);

        /* Generic Header   */
        $form->addElement('header', 'generic', get_string('general'));
        $form->setExpanded('generic',true);

        /* First Name       */
        $form->addElement('text','firstname',get_string('firstname'),'maxlength="100" size="30"');
        $form->addRule('firstname','required','required', null, 'client');
        $form->setType('firstname',PARAM_TEXT);
        if ($infoUser->firstname) {
            $form->setDefault('firstname',$infoUser->firstname);
        }//first_name

        /* Surname          */
        $form->addElement('text','lastname',get_string('lastname'),'maxlength="100" size="30"');
        $form->addRule('lastname','required','required',null,'client');
        $form->setType('lastname',PARAM_TEXT);
        if ($infoUser->lastname) {
            $form->setDefault('lastname',$infoUser->lastname);
        }//surname

        /* Email Address    */
        $form->addElement('text','email',get_string('email'),'maxlength="100" size="30"');
        $form->addRule('email','required','required',null,'client');
        $form->setType('email',PARAM_TEXT);
        if ($infoUser->email) {
            $form->setDefault('email',$infoUser->email);
        }//email

        /* City             */
        $form->addElement('text','city',get_string('city'),'maxlength="100" size="30"');
        $form->addRule('city','required','required',null,'client');
        $form->setType('city',PARAM_TEXT);
        if ($infoUser->city) {
            $form->setDefault('city',$infoUser->city);
        }//city

        /* Country          */
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

        /* County / Kommune - Header    */
        $form->addElement('header', 'muni', get_string('header_muni', 'local_first_access'));
        $form->setExpanded('muni',true);
        /* County / Kommune */
        $muniProfile = FirstAccess::GetMunicipalityProfile();
        if ($muniProfile) {
            require_once($CFG->dirroot.'/user/profile/lib.php');
            require_once($CFG->dirroot . '/user/profile/field/'.$muniProfile->datatype.'/field.class.php');

            $newfield = 'profile_field_'.$muniProfile->datatype;
            $formfield = new $newfield($muniProfile->id, $userId);
            $formfield->edit_field($form);
        }//if_muniProfile

        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);
        $form->setDefault('id',$userId);

        /* Add Actions Buttons */
        $this->add_action_buttons(false, get_string('btn_save','local_first_access'));
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

        $index   = strpos($data['email'],'@fakeEmail.no');
        if ($index) {
            $errors['email'] = get_string('invalidemail');
        }

        return $errors;
    }//validation
}//first_access_form