<?php
/**
 * Extra Profile Field Municipality
 *
 * Description
 *
 * @package         user/profile
 * @subpackage      field/municipality
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    19/11/2014
 * @author          eFaktor     (fbv)
 *
 */

class profile_define_municipality extends profile_define_base {
    function define_form_specific($form) {
        // Default data.
        $form->addElement('text', 'defaultdata', get_string('profiledefaultdata', 'admin'), 'size="50"');
        $form->setType('defaultdata', PARAM_TEXT);
    }//define_form_specific
    
}//profile_define_municipality