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
        global $DB,$USER;

        $m_form = $this->_form;

        $field = $DB->get_record('user_info_field', array('datatype' => 'municipality'));
        $newfield = 'profile_field_'.$field->datatype;
        $formfield = new $newfield($field->id, $USER->id);
        $formfield->edit_field($m_form);

        $m_form->addElement('hidden', 'id');
        $m_form->setType('id', PARAM_INT);
        $m_form->setDefault('id',$USER->id);

        /* Add Actions Buttons */
        $this->add_action_buttons(true, get_string('save','block_municipality'));
    }//definition
}//municipality_block_form