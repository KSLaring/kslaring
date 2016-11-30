<?php
/**
 * Gender Profile Field - Define class
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/gender
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    04/10/2014
 * @author          eFaktor     (fbv)
 *
 */

class profile_define_gender extends profile_define_base {
    function define_form_specific($form) {
        // Default data.
        $form->addElement('text', 'defaultdata', get_string('profiledefaultdata', 'admin'), 'size="50"');
        $form->setType('defaultdata', PARAM_TEXT);

    }//define_form_specific
}//profile_define_gender