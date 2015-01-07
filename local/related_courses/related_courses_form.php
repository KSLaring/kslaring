<?php
/**
 * Related Courses (local) - Main Page
 *
 * @package         local
 * @subpackage      related_courses
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      24/04/2014
 * @author          eFaktor     (fbv)
 *
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class related_courses_form extends moodleform {
    function definition() {
        $form                       = $this->_form;
        list($id,$related,$available)   = $this->_customdata;

        if (!$available) {
            $available[0] = get_string('none','local_related_courses');
        }//if_available

        if (!$related) {
            $related[0] = get_string('none_rel','local_related_courses');
        }//if_related

        $form->addElement('header', 'courses',get_string('header','local_related_courses'));
        $obj = array();
        $obj[0] = $form->createElement('select', 'add_fields',get_string('av_courses', 'local_related_courses'), $available, 'size="12" style="margin-right: 65px; "');
        $obj[0]->setMultiple(true);
        $obj[1] = $form->createElement('select', 'sel_fields',get_string('sel_courses', 'local_related_courses'),$related, 'size="12"');
        $obj[1]->setMultiple(true);

        $grp = $form->addElement('group', 'courses_grp',null, $obj, ' ', false);
        $form->addElement('static', 'comment');

        $buttons = array();
        $buttons[] = $form->createElement('submit', 'addsel', get_string('addsel', 'bulkusers'),'style="margin-left:30px; margin-right: 40px;"');
        $buttons[] = $form->createElement('submit', 'removesel', get_string('removesel', 'bulkusers'));
        $grp = $form->addElement('group', 'buttonsgrp', '', $buttons, array(' ', '<br />'), false);

        $renderer =& $form->defaultRenderer();
        $template = '<label class="qflabel" style="vertical-align:top;margin-left: -30px;">{label}</label> {element}';
        $renderer->setGroupElementTemplate($template, 'courses_grp');

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$id);
    }//definition
}//related_courses_form

class related_courses_footer extends moodleform {
    function definition() {
        $form = $this->_form;
        $id   = $this->_customdata;

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$id);

        $this->add_action_buttons(false, get_string('btn_return','local_related_courses'));
    }//definition
}//related_courses_footer