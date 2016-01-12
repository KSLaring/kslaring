<?php
/**
 * Course Template - Enrolment Methods
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/01/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Course create form template. Enrolment Methods
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class ct_enrolment_form extends moodleform {
    function definition () {
        /* Variables */
        $course = $this->_customdata;

        /* Form         */
        $form   = $this->_form;

        $radioBtn = array();
        $radioBtn[0] = $form->createElement('radio','waitinglist','',get_string('enrol_wait_self','local_friadmin'),ENROL_WAITING_SELF);
        $radioBtn[1] = $form->createElement('radio','waitinglist','',get_string('enrol_wait_buk','local_friadmin'),ENROL_WAITING_BULK);
        $form->addGroup($radioBtn,'waiting_radio','','</br></br>',false);
        $form->addRule('waiting_radio',get_string('required'),'required', null, 'server');

        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);
        $form->setDefault('id',$course);

        $this->add_action_buttons(true,get_string('continue'));
    }//definition
}//ct_enrolment_form

class ct_enrolment_settings_form extends moodleform {
    function definition() {
        /* Variables */
        list($course,$enrolMethod,$instance) = $this->_customdata;

        /* Form     */
        $form   = $this->_form;

        /* Enrolment Key */
        if ($enrolMethod == ENROL_WAITING_SELF) {
            $plugin = enrol_get_plugin('self');
            $form->addElement('passwordunmask', 'password', get_string('password', 'enrol_self'));
            $form->addHelpButton('password', 'password', 'enrol_self');
            if ($instance && $plugin->get_config('requirepassword','enrol_self')) {
                $form->addRule('password', get_string('required'), 'required', null, 'client');
            }
        }

        $form->addElement('date_selector', 'date_off', get_string('cutoffdate', 'enrol_waitinglist'),array('optional' => true));
        $form->setDefault('date_off',$instance->date_off);
        /* Participants     */
        $form->addElement('text','max_enrolled',  get_string('maxenrolments', 'enrol_waitinglist'), array('size' => '8'));
        $form->addHelpButton('max_enrolled','maxenrolments','enrol_waitinglist');
        $form->setType('max_enrolled',PARAM_INT);
        $form->setDefault('max_enrolled',$instance->max_enrolled);
        /* Size Wait list   */
        $form->addElement('text', 'list_size',  get_string('waitlistsize', 'enrol_waitinglist'), array('size' => '8'));
        $form->addHelpButton('list_size','waitlistsize','enrol_waitinglist');
        $form->setType('list_size',PARAM_INT);
        $form->setDefault('list_size', $instance->list_size);

        /* Require Invoice Information */
        $pluginInvoice = enrol_get_plugin('invoice');
        if ($pluginInvoice) {
            $form->addElement('advcheckbox', 'invoice', get_string('invoice', 'enrol_waitinglist'));
            $form->setDefault('invoice',$instance->invoice);
            $form->addHelpButton('invoice', 'invoice', 'enrol_waitinglist');
        }

        /**
         * Approval
         */
        $form->addElement('advcheckbox', 'approval', get_string('approval', 'enrol_waitinglist'));
        $form->setDefault('approval',$instance->approval);
        $form->addHelpButton('approval', 'approval', 'enrol_waitinglist');

        /* Course Id */
        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);
        $form->setDefault('id',$course);

        /* Enrol Method Selected */
        $form->addElement('hidden', 'waitinglist');
        $form->setType('waitinglist', PARAM_INT);
        $form->setDefault('waitinglist',$enrolMethod);

        /* Waiting id   */
        $form->addElement('hidden', 'waitinglistid');
        $form->setType('waitinglistid', PARAM_INT);
        $form->setDefault('waitinglistid',$instance->waitinglistid);

        /* Instance Id  */
        $form->addElement('hidden', 'instanceid');
        $form->setType('instanceid', PARAM_INT);
        $form->setDefault('instanceid',$instance->id);

        $this->add_action_buttons(true,get_string('continue'));
    }//definition
}//ct_enrolment_settings_form