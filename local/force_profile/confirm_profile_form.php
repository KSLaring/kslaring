<?php
/**
 * Confirm Profile - Force Profile
 *
 * Description
 *
 * @package         local
 * @subpackage      force_profile
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      21/08/2014
 * @author          eFaktor     (fbv)
 *
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class confirm_profile_form extends moodleform {
    // Define the form
    function definition () {

        list($my_fields,$user,$file_options)  = $this->_customdata;
        $form       = $this->_form;

        $form->addElement('header', 'confirm',get_string('confirm_header','local_force_profile'));

        foreach ($my_fields as $field) {
            if ($field->type == 'user') {
                ForceProfile::ForceProfile_CreateUserElement($form,$field->name,$user,$file_options);
            }else {
                ForceProfile::ForceProfile_CreateExtraProfileElement($form,$field->name,$user->id);
            }//if_else
        }//for_my_fields

        $form->addElement('checkbox','confirm_check',null,get_string('confirm_check','local_force_profile'));
        $form->addRule('confirm_check','','required', null, 'server');
        $form->setDefault('confirm_check',true);

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$user->id);

        $this->add_action_buttons(false, get_string('updatemyprofile'));
    }
}//confirm_profile_form