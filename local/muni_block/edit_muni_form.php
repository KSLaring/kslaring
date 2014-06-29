<?php

/**
 * Local Municipality Block  - Form Municipality
 *
 * @package         local
 * @subpackage      muni_block
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/08/2013
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class municipality_block_form extends moodleform {
    function definition() {
        $m_form = $this->_form;

        /* Municipalities    */
        $options = local_muni_get_list_municipalities();
        $m_form->addElement('select','sel_muni',get_string('sel_muni','local_muni_block'),$options);
        //$m_form->addRule('sel_muni',get_string('required','local_muni_block'), 'required', null, 'server');

        /* Add Actions Buttons */
        $this->add_action_buttons(true, get_string('save','local_muni_block'));
    }//definition

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['sel_muni'] == '0') {
            $errors['sel_muni'] = get_string('required','local_muni_block');
        }//if_title_exist

        return $errors;
    }//validation
}//municipality_block_form

