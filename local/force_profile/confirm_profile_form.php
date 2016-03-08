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

        /* Add the Fields to update */
        if ($my_fields->normal) {
            $normal_fields = $my_fields->normal;
            foreach ($normal_fields as $field) {
                ForceProfile::ForceProfile_CreateUserElement($form,$field->name,$user,$file_options);
            }
        }//normal_fields
        if ($my_fields->profile) {
            $profile_fields = $my_fields->profile;
            foreach ($profile_fields as $field) {
                ForceProfile::ForceProfile_CreateExtraProfileElement($form,$field->name,$user->id);
            }
        }//profile_fields

        $form->addElement('checkbox','confirm_check',null,get_string('confirm_check','local_force_profile'));
        $form->addRule('confirm_check','','required', null, 'server');
        $form->setDefault('confirm_check',true);

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$user->id);

        $this->add_action_buttons(false, get_string('updatemyprofile'));
    }
}//confirm_profile_form