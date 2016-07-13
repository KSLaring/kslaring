<?php
/**
 * Micro Learning Deliveries    - Duplicate Calendar Campaign (Form)
 *
 * @package         local/microlearnig
 * @subpackage      mode/calendar
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      21/11/2014
 * @author          eFaktor     (fbv)
 *
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class duplicate_calendar_form extends moodleform {
    function definition() {
        /* Parameters   */
        list($course_id,$campaign) = $this->_customdata;
        /* Form         */
        $form       = $this->_form;

        /* Name Campaign to duplicate   */
        $campaign_name  = Micro_Learning::Get_NameCampaign($campaign);

        /* New Campaign             */
        $form->addElement('header', 'new_campaign',get_string('header_campaign','local_microlearning'));

        /* Campaign to Duplicate    */
        $form->addElement('text','campaign_old',get_string('campaign_duplicate','local_microlearning'),'style="width:80%;" disabled');
        $form->setType('campaign_old',PARAM_TEXT);
        $form->setDefault('campaign_old',$campaign_name);

        /* Name                     */
        $form->addElement('text','campaign',get_string('name_campaign','local_microlearning'),'style="width:80%;"');
        $form->setType('campaign',PARAM_TEXT);
        $form->addRule('campaign',get_string('required'), 'required', null, 'server');

        /* Type Campaigns       */
        $form->addElement('radio','type','',get_string('calendar_mode','local_microlearning'),CALENDAR_MODE);
        $form->setDefault('type',true);

        $this->add_action_buttons(true, get_string('btn_duplicate','local_microlearning'));

        /* Course   */
        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$course_id);

        /* Campaign Id  */
        $form->addElement('hidden','cp');
        $form->setType('cp',PARAM_INT);
        $form->setDefault('cp',$campaign);
    }//definition
}//duplicate_calendar_form