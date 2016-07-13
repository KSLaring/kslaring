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
class profile_field_express extends profile_field_base {
    function display_data() {
        /// Default formatting
        $data = parent::display_data();

        return $data;
    }

    function edit_field_add($mForm) {
        /* Variables    */
        $options    = null;

        /* Options  */
        $options     = array();
        $options[1]  = get_string('yes');
        $options[0]  = get_string('no');

        $mForm->addElement('select',$this->inputname,get_string('auto_pin_code','profilefield_express'),$options);
        $mForm->setDefault($this->inputname,1);
    }

    /**
     * @return      bool
     *
     * @updateDate  18/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Always return true, so it will never be shown
     */
    function is_empty() {
        return true;
    }
}//profile_field_express