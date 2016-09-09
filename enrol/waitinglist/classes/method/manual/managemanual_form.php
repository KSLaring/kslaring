<?php
/**
 * Waiting List - Manual submethod - Form
 *
 * @package         enrol/waitinglist
 * @subpackage      lang
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    18/08/2016
 * @author          efaktor     (fbv)
 *
 * Description
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class managemanual_form extends moodleform {


    function definition() {
        // TODO: Implement definition() method.
        /* Variables */
        global $OUTPUT;
        $achoices   = null;
        $schoices   = null;
        $mForm      = $this->_form;
        $manualClass = 'enrol_waitinglist\method\manual\enrolmethodmanual';
        $seats       = 0;

        list($instance,$course,$addSearch,$removeSearch) = $this->_customdata;

        /* Available Seats  */
        $seats = $manualClass::GetAvailableSeats($instance,$course);

        /* Users Selectors - Left Enrolled users */
        $mForm->addElement('html','<div class="userselector" id="addselect_wrapper">');
            if ($seats == 'u') {
                $mForm->addElement('static', 'manual-notification', '', get_string('manual_unlimit', 'enrol_waitinglist'),'id="manual_notification"');
            }else if ($seats > 0) {
                $mForm->addElement('static', 'manual-notification', '', get_string('manual_notification', 'enrol_waitinglist',$seats),'id="manual_notification"');    
            }else {
                $mForm->addElement('static', 'manual-notification', '', get_string('manual_none_seats', 'enrol_waitinglist'),'id="manual_notification"');
            }
            
            /* Left - Users enrolled        */
            $schoices = $manualClass::FindEnrolledUsers($instance->id,$course,$removeSearch);
            $mForm->addElement('html','<div class="sel_users_left">');
                $strEnrolled = get_string('enrolledusers','enrol');
                $mForm->addElement('html','<label>' . $strEnrolled . '</label>');
                $mForm->addElement('selectgroups','removeselect', '',$schoices,'multiple size="20" id="removeselect"');
                $mForm->addElement('text','removeselect_searchtext',get_string('search'),'id="removeselect_searchtext"');
                $mForm->setType('removeselect_searchtext',PARAM_TEXT);
            $mForm->addElement('html','</div>');//sel_users_left
        $mForm->addElement('html','</div>');//userselector_managers

        /* Actions Buttons  */
        $mForm->addElement('html','<div class="userselector" id="addselect_wrapper">');
            $mForm->addElement('html','<div class="sel_users_buttons">');
                /* Add Users        */
                $addBtn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $mForm->addElement('submit','add_sel',$addBtn);

                /* Separator    */
                $mForm->addElement('html','</br>');

                /* Remove Users     */
                $removeBtn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                $mForm->addElement('submit','remove_sel',$removeBtn);
            $mForm->addElement('html','</div>');//sel_users_buttons
        $mForm->addElement('html','</div>');//userselector_managers

        /* Users Selectors - Right Not Enrolled users */
        $mForm->addElement('html','<div class="userselector" id="addselect_wrapper">');
            $achoices = $manualClass::FindCandidatesUsers($instance->id,$course,$addSearch);
            $mForm->addElement('html','<div class="sel_users_right">');
                $strCandidates = get_string('enrolcandidates','enrol');
                $mForm->addElement('html','<label>' . $strCandidates . '</label>');
                $mForm->addElement('selectgroups','addselect', '',$achoices,'multiple size="20" id="addselect"');
                $mForm->addElement('text','addselect_searchtext',get_string('search'),'id="addselect_searchtext"');
                $mForm->setType('addselect_searchtext',PARAM_TEXT);
            $mForm->addElement('html','</div>');//sel_users_right
        $mForm->addElement('html','</div>');//userselector_managers

        /* BUTTONS  */
        $buttons = array();
        $buttons[] = $mForm->createElement('cancel','btn_back',get_string('back'));

        $mForm->addGroup($buttons, 'buttonar', '', array(' '), false);
        $mForm->setType('buttonar', PARAM_RAW);
        $mForm->closeHeaderBefore('buttonar');

        /* Hidden   */
        $mForm->addElement('hidden','id');
        $mForm->setDefault('id',$instance->id);
        $mForm->setType('id',PARAM_INT);
    }//definition

    function validation($data, $files) {
        /* Variables    */
        $errors = parent::validation($data, $files);

        list($instance,$course,$addSearch,$removeSearch) = $this->_customdata;
        $manualClass = 'enrol_waitinglist\method\manual\enrolmethodmanual';

        /* Check there are users to add */
        if ((isset($data['add_sel']) && $data['add_sel'])) {
            if (!isset($data['addselect']))  {
                $errors['addselect'] = get_string('required');
            }else {
                /* 0 -> Unlimitedd*/
                if ($instance->customint2) {
                    $total = count($data['addselect']);
                    $seats = $manualClass::GetAvailableSeats($instance,$course);
                    
                    if ($total > $seats) {
                        if ($seats) {
                            $errors['addselect'] = get_string('manual_no_seats','enrol_waitinglist',$seats);
                        }else {
                            $errors['addselect'] = get_string('manual_none_seats','enrol_waitinglist');
                        }
                        
                    }
                }
            }//if_addselect
        }//if_add_sel

        /* Check there are users to remove  */
        if ((isset($data['remove_sel']) && $data['remove_sel'])) {
            if (!isset($data['removeselect'])) {
                $errors['removeselect'] = get_string('required');
            }//if_removeselect
        }//if_remove_sel

        return $errors;
    }//validation
}//managemanual_form