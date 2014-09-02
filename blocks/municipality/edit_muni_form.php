<?php
/**
 * Municipality Block - Edit Muni Form
 *
 * @package         block
 * @subpackage      municipality
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/08/2013
 * @updateDate      20/08/2014
 * @author          efaktor     (fbv)
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class municipality_block_form extends moodleform {
    function definition() {
        $m_form = $this->_form;

        /* Municipalities    */
        $options = Municipality::municipality_GetMunicipality_List();
        $m_form->addElement('select','sel_muni',get_string('sel_muni','block_municipality'),$options);

        /* Add Actions Buttons */
        $this->add_action_buttons(true, get_string('save','block_municipality'));
    }//definition

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['sel_muni'] == '0') {
            $errors['sel_muni'] = get_string('required','block_municipality');
        }//if_title_exist

        return $errors;
    }//validation
}//municipality_block_form