<?php
/**
 * Inconsistencies Course Completions  - Index Form
 *
 * @package         local
 * @subpackage      icp
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    25/05/2015
 * @author          eFaktor     (fbv)
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class inconsistencies_start_form extends moodleform {
    // Define the form
    function definition () {
        $form       = $this->_form;

        $course     = $this->_customdata;

        $form->addElement('static', 'icp-description', '', get_string('info_icp', 'local_icp'));
        
        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$course);

        $this->add_action_buttons(true, get_string('start','local_icp'));
    }
}//inconsistencies_start_form