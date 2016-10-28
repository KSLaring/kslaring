<?php
/**
 * eMail Fake - Form
 *
 * Description
 *
 * @package         local/wsks
 * @subpackage      eMail
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    27/10/2016
 * @author          eFaktor     (fbv)
 *
 */

require_once($CFG->dirroot.'/lib/formslib.php');

class email_fake_form extends moodleform {
    function definition (){
        /* Form         */
        $form   = $this->_form;

        list($userId,$userMail) = $this->_customdata;

        /* Old eMail            */
        $form->addElement('text','old_email',get_string('email'),'maxlength="100" size="30" readonly');
        $form->setType('old_email',PARAM_TEXT);
        $form->setDefault('old_email',$userMail);

        /* New Email Address    */
        $form->addElement('text','email',get_string('email'),'maxlength="100" size="30"');
        $form->addRule('email','required','required',null,'client');
        $form->setType('email',PARAM_TEXT);

        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);
        $form->setDefault('id',$userId);
        
        /* Add Actions Buttons */
        $this->add_action_buttons(false, get_string('save','admin'));
    }

    function validation($data, $files) {
        /* Variables */
        $errors = parent::validation($data, $files);

        if (WS_FELLESDATA::IsFakeMail($data['email'])) {
            $errors['email'] = get_string('invalidemail');
        }else if ($data['email'] == $data['old_email']) {
            $errors['email'] = get_string('invalidemail');
        }else if (!validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');
        }else {
            $errorstr = email_is_not_allowed($data['email']);
            if ($errorstr !== false) {
                $errors['email'] = $errorstr;
            }
        }

        return $errors;
    }//validation
}//email_fake_form