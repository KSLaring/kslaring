<?php
/**
 * Web Services KS - Slave Forms
 *
 * @package         local/wsks
 * @subpackage      slaves/classes
 * @copyright       2015 eFaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    08/11/2016
 * @author          eFaktor     (fbv)
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class add_slave_form extends moodleform {
    function definition() {
        // TODO: Implement definition() method.
        /* Variables    */
        $form           = $this->_form;
        $lstServices    = null;

        /**
         * Site
         */
        $form->addElement('text','slave', get_string('site','local_wsks'),'maxlength="254" size="50"');
        $form->setType('slave', PARAM_TEXT);
        $form->addRule('slave',get_string('required'), 'required', null, 'server');

        /**
         * Token
         */
        $form->addElement('text', 'token', get_string('token','local_wsks'), ' size="35" maxlength="128" ');
        $form->setType('token', PARAM_RAW);
        $form->addRule('token',get_string('required'), 'required', null, 'server');

        /**
         * Services Connected with
         */
        $lstServices = Slaves::GetServices();
        $form->addElement('select','services',get_string('services','local_wsks'),$lstServices,'multiple sizer=10');
        $form->addRule('services',get_string('required'), 'required', null, 'server');
        $form->setDefault('services',0);
        /**
         * Buttons
         */
        $this->add_action_buttons(true, get_string('add'));
    }//definition

    function validation($data, $files) {
        /* Variables    */
        $errors = parent::validation($data, $files);

        /**
         * Check if the site already exists
         */
        if (Slaves::CheckSlaveSystem($data['slave'])) {
            $errors['slave'] = get_string('exist_slave','local_wsks');
        }


        if (isset($data['services']) && count($data['services']) == 1) {
            $services = $data['services'];
            if (!$services[0]) {
                $errors['services'] = get_string('required');
            }
        }
        
        return $errors;
    }//validation
}//add_slave_form

class update_slaves_systems_form extends moodleform {
    function definition() {
        // TODO: Implement definition() method.
        /* Variables    */
        $form = $this->_form;
        
        /**
         * Select Service to update
         */
        $lstServices = Slaves::GetServices();
        $form->addElement('select','services',get_string('services','local_wsks'),$lstServices);
        $form->addRule('services', 'required', 'required', 'nonzero', 'client');
        $form->addRule('services', 'required', 'nonzero', null, 'client');

        /**
         * Buttons
         */
        $this->add_action_buttons(true, get_string('update'));
        
    }//definition
}//update_slaves_systems_form