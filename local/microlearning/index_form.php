<?php
/**
 * Micro Learning - Index Main Page
 *
 * @package         local
 * @subpackage      microlearning
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      12/09/2014
 * @author          eFaktor     (fbv)
 *
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class microlearning_form extends moodleform {
    function definition() {

        list($course_id)  = $this->_customdata;
        $form       = $this->_form;

        /* New Campaign         */
        $form->addElement('header', 'new_campaign',get_string('header_campaign','local_microlearning'));
        /* Name                 */
        $form->addElement('text','campaign',get_string('name_campaign','local_microlearning'),'style="width:80%;"');
        $form->setType('campaign',PARAM_TEXT);
        $form->addRule('campaign',get_string('required'), 'required', null, 'server');

        $form->addElement('checkbox', 'activate', get_string('action_activate', 'local_microlearning'));
        $form->setDefault('activate',1);

        /* Type Campaigns       */
        $radio_button = array();
        $radio_button[] =& $form->createElement('radio','type','',get_string('calendar_mode','local_microlearning'),CALENDAR_MODE);
        $radio_button[] =& $form->createElement('radio','type','',get_string('activity_mode','local_microlearning'),ACTIVITY_MODE);
        $form->addGroup($radio_button,'radio_type',get_string('campaign_mode','local_microlearning'),'</br>',false);
        $form->addRule('radio_type',get_string('required'), 'required', null, 'server');


        $this->add_action_buttons(true, get_string('btn_submit','local_microlearning'));

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$course_id);
    }//definition
}//microlearning_form