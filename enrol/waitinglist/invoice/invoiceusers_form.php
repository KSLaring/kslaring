<?php
/**
 * Invoice Approval Users - Form
 *
 * @package         enrol/waitinglist
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    28/09/2016
 * @author          efaktor     (fbv)
 *
 */

require_once($CFG->libdir.'/formslib.php');

class invoice_users_form extends moodleform {
    function definition() {
        list($courseId,$lstUsers)       = $this->_customdata;
        $form                           = $this->_form;

        $form->addElement('header', 'users', get_string('users'));
        $form->addElement('selectgroups','invoice_user', '',$lstUsers,'size="20"');

        $this->add_action_buttons(true, get_string('add'));
        
        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);
        $form->setDefault('id', $courseId);
        
    }
}//invoice_users_form