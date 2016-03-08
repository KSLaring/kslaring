<?php
/**
 * Extra Profile Field Auto Generated Express Login
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/express
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    16/11/2015
 * @author          eFaktor     (fbv)
 *
 */

class profile_define_express extends profile_define_base {
    function define_form_specific($form) {
        // Default data.
        $form->addElement('text', 'defaultdata', get_string('profiledefaultdata', 'admin'), 'size="50"');
        $form->setType('defaultdata', PARAM_TEXT);
    }//define_form_specific

}//profile_define_express