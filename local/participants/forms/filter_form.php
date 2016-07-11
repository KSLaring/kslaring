<?php
/**
 * Participants List - Filter
 *
 * @package         local
 * @subpackage      participants/forms
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    10/07/2016
 * @author          eFaktor     (fbv)
 */

require_once($CFG->dirroot.'/lib/formslib.php');

class filter_participants_form extends moodleform {
    function definition() {
        /* Variables */
        global $SESSION;
        $form  = $this->_form;
        list($courseId,$page,$per_page) = $this->_customdata;

        /* Header */
        $form->addElement('header', 'filter',get_string('header_filter','local_participants'));

        /* From Attendance  */
        $form->addElement('date_selector','date_from',get_string('attend_from','local_participants'));
        $form->setDefault('date_from',time());
        if (isset($SESSION->filter)) {
            $form->setDefault('date_from',$SESSION->filter->from);
        }

        /* To Attendance     */
        $form->addElement('date_selector','date_to',get_string('attend_to','local_participants'));
        $form->setDefault('date_to',time());
        if (isset($SESSION->filter)) {
            $form->setDefault('date_to',$SESSION->filter->to);
        }

        /* Attended     */
        //$form->addElement('checkbox','attended',get_string('attendance','local_participants'));
        
        /* Course */
        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$courseId);
        /* Page     */
        $form->addElement('hidden','page');
        $form->setType('page',PARAM_INT);
        $form->setDefault('page',$page);
        /* Per Page */
        $form->addElement('hidden','perpage');
        $form->setType('perpage',PARAM_INT);
        $form->setDefault('perpage',$per_page);

        //$this->add_action_buttons(false, get_string('header_filter','local_participants'));

        /* BUTTONS  */
        $buttons = array();
        $buttons[] = $form->createElement('submit','submitbutton',get_string('header_filter','local_participants'));
        $buttons[] = $form->createElement('submit','submitbutton2',get_string('remove_filter','local_participants'));

        $form->addGroup($buttons, 'buttonar', '', array(' '), false);
        $form->setType('buttonar', PARAM_RAW);
        $form->closeHeaderBefore('buttonar');
    }//definition

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $today = time();

        if (isset($data['submitbutton']) && ($data['submitbutton'])) {
            /* 'From Date' can be bigger than today     */
            if ($data['date_from']) {
                if ($data['date_from'] > $today) {
                    $errors['date_from'] = get_string('err_start','local_participants');

                    return $errors;
                }
            }//if_data_from


            /* 'To Date' can be bigger than 'From Date' */
            if ($data['date_to']) {
                if ($data['date_from'] > $data['date_to']) {
                    $errors['date_from'] = get_string('err_dates','local_participants');

                    return $errors;
                }
            }//if_data_to
        }
    }//validation
}//filter_participants_form