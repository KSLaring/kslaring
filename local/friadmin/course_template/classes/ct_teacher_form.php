<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Course Template - Teachers
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    20/06/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Course create form template. Adding teachers
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class ct_enrolment_teachers_form extends moodleform {
    function definition() {
        /* Variables */
        global $OUTPUT;
        $schoices = null;
        $achoices = null;

        /* Form */
        $form         = $this->_form;

        /* Get Extra Info   */
        list($course,$courseTemplate,$addSearch,$removeSearch) = $this->_customdata;

        /* Teachers selector */
        $form->addElement('header', 'teachers', get_string('teachers'));
        $form->setExpanded('teachers',true);
        $form->addElement('html','<div class="userselector" id="addselect_wrapper">');
            /* Left.    Existing Teachers      */
            $schoices   = CourseTemplate::find_teachers_selectors($course,$removeSearch);

            $form->addElement('html','<div class="sel_users_left">');
                $form->addElement('selectgroups','removeselect', '',$schoices,'multiple size="20" id="removeselect"');
                    $form->addElement('text','removeselect_searchtext',get_string('search'),'id="removeselect_searchtext"');
                $form->setType('removeselect_searchtext',PARAM_TEXT);
            $form->addElement('html','</div>');//sel_users_left

            /* Actions Buttons  */
            $form->addElement('html','<div class="sel_users_buttons">');
            /* Add Users        */
            $addBtn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
            $form->addElement('submit','add_sel',$addBtn);

            /* Separator    */
            $form->addElement('html','</br>');

            /* Remove Users     */
            $removeBtn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
            $form->addElement('submit','remove_sel',$removeBtn);
            $form->addElement('html','</div>');//sel_users_buttons

            /* Right.   Potential Teachers     */
            $achoices   = CourseTemplate::find_potential_teachers_selector($course,$addSearch);
            $form->addElement('html','<div class="sel_users_right">');
                $form->addElement('selectgroups','addselect', '',$achoices,'multiple size="20" id="addselect"');
                    $form->addElement('text','addselect_searchtext',get_string('search'),'id="addselect_searchtext"');
                $form->setType('addselect_searchtext',PARAM_TEXT);
            $form->addElement('html','</div>');//sel_users_right
        $form->addElement('html','</div>');

        /* BUTTONS  */
        $this->add_action_buttons(true,get_string('continue'));

        /* Course */
        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);
        $form->setDefault('id',$course);

        /* Course Template */
        $form->addElement('hidden', 'ct');
        $form->setType('ct', PARAM_INT);
        $form->setDefault('ct',$courseTemplate);
    }//definition
}//ct_enrolment_teachers_form

class ct_enrolment_noed_teachers_form extends moodleform {
    function definition() {
        /* Variables */
        global $OUTPUT;
        $schoices = null;
        $achoices = null;

        /* Form */
        $form         = $this->_form;

        /* Get Extra Info   */
        list($course,$courseTemplate,$addSearch,$removeSearch) = $this->_customdata;

        /* Teachers selector */
        $form->addElement('header', 'noed_teachers', get_string('noed_teachers','local_friadmin'));
        $form->setExpanded('noed_teachers',true);
        $form->addElement('html','<div class="userselector" id="addselect_wrapper">');
        /* Left.    Existing Teachers      */
        $schoices   = CourseTemplate::find_noed_teachers_selectors($course,$removeSearch);

        $form->addElement('html','<div class="sel_users_left">');
        $form->addElement('selectgroups','removeselect', '',$schoices,'multiple size="20" id="removeselect"');
        $form->addElement('text','removeselect_searchtext',get_string('search'),'id="removeselect_searchtext"');
        $form->setType('removeselect_searchtext',PARAM_TEXT);
        $form->addElement('html','</div>');//sel_users_left

        /* Actions Buttons  */
        $form->addElement('html','<div class="sel_users_buttons">');
        /* Add Users        */
        $addBtn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
        $form->addElement('submit','add_sel',$addBtn);

        /* Separator    */
        $form->addElement('html','</br>');

        /* Remove Users     */
        $removeBtn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
        $form->addElement('submit','remove_sel',$removeBtn);
        $form->addElement('html','</div>');//sel_users_buttons

        /* Right.   Potential Teachers     */
        $achoices   = CourseTemplate::find_noed_potential_teachers_selector($course,$addSearch);
        $form->addElement('html','<div class="sel_users_right">');
        $form->addElement('selectgroups','addselect', '',$achoices,'multiple size="20" id="addselect"');
        $form->addElement('text','addselect_searchtext',get_string('search'),'id="addselect_searchtext"');
        $form->setType('addselect_searchtext',PARAM_TEXT);
        $form->addElement('html','</div>');//sel_users_right
        $form->addElement('html','</div>');

        /* BUTTONS  */
        $this->add_action_buttons(true,get_string('continue'));

        /* Course */
        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);
        $form->setDefault('id',$course);

        /* Course Template */
        $form->addElement('hidden', 'ct');
        $form->setType('ct', PARAM_INT);
        $form->setDefault('ct',$courseTemplate);
    }//definition
}//ct_enrolment_noed_teachers_form