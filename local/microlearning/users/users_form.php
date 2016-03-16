<?php
/**
 * Micro Learning - Selector Users Page
 *
 * @package         local
 * @subpackage      microlearning
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      12/09/2014
 * @author          eFaktor     (fbv)
 *
 * @updateDate      12/11/2015
 * @author          eFaktor     (fbv)
 *
 * Description
 * Search fields. New javascript and functionality
 *
 */
require_once($CFG->libdir.'/formslib.php');

class microlearning_users_form extends moodleform {
    function definition() {
        list($course_id,$campaign_id,$mode_learning,$action)  = $this->_customdata;
        $form       = $this->_form;

        $this->add_action_buttons(true, get_string('btn_next','local_microlearning'));

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$course_id);

        $form->addElement('hidden','cp');
        $form->setType('cp',PARAM_INT);
        $form->setDefault('cp',$campaign_id);

        $form->addElement('hidden','mode');
        $form->setType('mode',PARAM_INT);
        $form->setDefault('mode',$mode_learning);

        $form->addElement('hidden','action');
        $form->setType('action',PARAM_INT);
        $form->setDefault('action',$action);
    }//definition
}//microlearning_users_form

class microlearning_users_selector_form extends moodleform {
    function definition() {
        /* Variables */
        global $OUTPUT;
        $disabled = '';

        /* Form */
        $form = $this->_form;

        /* Params   */
        $acount         = $this->_customdata['acount'];
        $scount         = $this->_customdata['scount'];
        $ausers         = $this->_customdata['ausers'];
        $susers         = $this->_customdata['susers'];
        $totalPotential = $this->_customdata['total_potential'];
        $totalCampaign  = $this->_customdata['total_camp'];
        $course_id      = $this->_customdata['course'];
        $mode_learning  = $this->_customdata['mode'];
        $campaign_id    = $this->_customdata['campaign'];
        $started        = $this->_customdata['started'];

        $achoices = array();
        $schoices = array();

        if (is_array($ausers)) {
            $a = new stdClass();
            $a->total   = $totalPotential;
            $a->count   = $acount;
            $achoices[0] = get_string('allfilteredusers', 'bulkusers', $a);
            $achoices = $achoices + $ausers;
        } else {
            $achoices[-1] = get_string('nofilteredusers', 'bulkusers', $totalPotential);
        }

        if (is_array($susers)) {
            $a = new stdClass();
            $a->total       = $totalCampaign;
            $a->count       = $scount;
            $schoices[0]    = get_string('allselectedusers', 'bulkusers', $a);
            $schoices       = $schoices + $susers;
        } else {
            $schoices[-1] = get_string('noselectedusers', 'bulkusers');
        }


        $form->addElement('header', 'users', get_string('usersinlist', 'bulkusers'));

        $form->addElement('html','<div class="micro_learning_users">');
            /* Selected Users   */
            $form->addElement('html','<div class="sel_users_left">');
                $form->addElement('select','susers','',$schoices,'multiple size="15"');
                $form->addElement('text','removeselect_searchtext',get_string('search'),'id="removeselect_searchtext"');
                $form->setType('removeselect_searchtext',PARAM_TEXT);
            $form->addElement('html','</div>');//sel_users_left

            /* Buttons          */
            /* Started --> Not Add Users --> Calendar Mode*/
            if (($started) && ($mode_learning == CALENDAR_MODE)) {
                $disabled = 'disabled';
            }//if_started
            $form->addElement('html','<div class="sel_users_buttons">');
                /* Add Activity     */
                $add_btn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $form->addElement('submit','add_sel',$add_btn,$disabled);
                /* Remove Activity  */
                $remove_btn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                $form->addElement('submit','remove_sel',$remove_btn);

                $form->addElement('html','</br>');

                /* Add Activity     */
                $add_all_btn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('addall', 'bulkusers'));
                $form->addElement('submit','add_all',$add_all_btn,$disabled);
                /* Remove Activity  */
                $remove_all_btn = html_to_text(get_string('removeall', 'bulkusers') . '&nbsp;' . $OUTPUT->rarrow());
                $form->addElement('submit','remove_all',$remove_all_btn);

            $form->addElement('html','</div>');//sel_users_buttons

            /* Add Users        */
            $form->addElement('html','<div class="sel_users_right">');
                $form->addElement('select','ausers','',$achoices,'multiple size="15"');
                $form->addElement('text','addselect_searchtext',get_string('search'),'id="addselect_searchtext"');
                $form->setType('addselect_searchtext',PARAM_TEXT);
            $form->addElement('html','</div>');//sel_users_right
        $form->addElement('html','</div>');//micro_learning_users

        /* Add automatically users*/
        if ($mode_learning == ACTIVITY_MODE) {
            $form->addElement('checkbox', 'add_users', get_string('add_users', 'local_microlearning'));
            $form->setDefault('add_users',Micro_Users::GetStatus_AutomaticallyUsers($campaign_id,$course_id,$mode_learning));
        }//if_activity_mode

        $this->add_action_buttons(true, get_string('btn_next','local_microlearning'));

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$course_id);

        $form->addElement('hidden','cp');
        $form->setType('cp',PARAM_INT);
        $form->setDefault('cp',$campaign_id);

        $form->addElement('hidden','mode');
        $form->setType('mode',PARAM_INT);
        $form->setDefault('mode',$mode_learning);

        $form->addElement('hidden','st');
        $form->setType('st',PARAM_INT);
        $form->setDefault('st',$started);
    }//definition

    function validation($data, $files) {
        $errors         = parent::validation($data, $files);
        $susers         = $this->_customdata['susers'];
        /* Check if there are users connected with the campaign */
        if ((isset($data['submitbutton']) && $data['submitbutton'])) {
            if (!$susers) {
                $errors['susers'] = get_string('required');
            }//if_addselect
        }else {
            /* Check there are users to add */
            if ((isset($data['add_sel']) && $data['add_sel'])) {
                if (!isset($data['ausers'])) {
                    $errors['ausers'] = get_string('required');
                }//if_addselect
            }//if_add_sel

            /* Check there are users to remove  */
            if ((isset($data['remove_sel']) && $data['remove_sel'])) {
                if (!isset($data['susers'])) {
                    $errors['susers'] = get_string('required');
                }//if_addselect
            }//if_add_sel
        }//if_submiitbutton_next

        return $errors;
    }//validation
}//microlearning_users_selector_form