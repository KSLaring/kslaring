<?php
/**
 * Force Update Profile - Bulk Action (Form)
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

require_once($CFG->libdir.'/formslib.php');

class force_profile_form extends moodleform {
    function definition() {
        global $SESSION;

        $form = $this->_form;

        list($users,$add_choices) = $this->_customdata;

        /* Users List */
        $user_lst = ForceProfile::ForceProfile_GetUsers($users);

        $form->addElement('header', 'users_lst',get_string('users_lst','local_force_profile'));
        $form->setExpanded('users_lst',true);
        $form->addElement('textarea','users_txt',get_string('users'),'rows="5" style="width:98%; overflow-y:scroll;" disabled');
        $form->setDefault('users_txt',html_to_text($user_lst));
        $sel_choices = array();
        if (isset($SESSION->fields)) {
            $sel_choices = $sel_choices + $SESSION->fields;
        }

        if (!$sel_choices) {
            $sel_choices[0] = get_string('none','local_force_profile');
        }
        /* Profile List */
        $form->addElement('header', 'profile_lst',get_string('profile_lst','local_force_profile'));
        $form->setExpanded('profile_lst',true);
        $obj = array();
        $obj[0] = $form->createElement('select', 'add_fields',get_string('av_profile','local_force_profile'), $add_choices, 'size="12" style="margin-right: 65px;"');
        $obj[0]->setMultiple(true);
        $obj[2] = $form->createElement('select', 'sel_fields',get_string('sel_profile','local_force_profile'),$sel_choices, 'size="12"');
        $obj[2]->setMultiple(true);

        $grp = $form->addElement('group', 'profile_grp', null, $obj, ' ', false);

        $buttons = array();
        $buttons[] = $form->createElement('submit', 'addsel', get_string('addsel', 'bulkusers'),'style="margin-left:30px; margin-right: 40px;"');
        $buttons[] = $form->createElement('submit', 'removesel', get_string('removesel', 'bulkusers'));
        $grp = $form->addElement('group', 'buttonsgrp', '', $buttons, array(' ', '<br />'), false);

        $form->addElement('static', 'comment');

        $renderer =& $form->defaultRenderer();
        $template = '<label class="qflabel" style="vertical-align:top;margin-left: -30px;">{label}</label> {element}';
        $renderer->setGroupElementTemplate($template, 'profile_grp');
    }//definition
}//force_profile_form

class force_profile_message_form extends moodleform {
    function definition() {
        $form = $this->_form;

        /* Message      */
        $form->addElement('header', 'msg_title',get_string('msg_title','local_force_profile'));
        $form->setExpanded('msg_title',true);
        $form->addElement('editor','msg_body',get_string('msg_body','local_force_profile'));
        $form->setType('msg_body',PARAM_RAW);
        $form->addRule('msg_body','','required', null, 'server');

        $this->add_action_buttons(true, get_string('sendmessage','message'));
    }
}//force_profile_message_form