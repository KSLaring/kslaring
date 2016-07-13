<?php
/**
 * Micro Learning - Calendar Mode Page
 *
 * @package         local/microlearnig
 * @subpackage      mode/calendar
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      14/09/2014
 * @author          eFaktor     (fbv)
 *
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class calendar_mode_form extends moodleform {
    function definition() {
        global $SESSION,$OUTPUT;
        $lst_users = null;
        /* Parameters   */
        list($course_id,$mode,$users_campaign,$campaign,$delivery_info,$edit_options) = $this->_customdata;
        /* Form         */
        $form       = $this->_form;


        $activities     = Micro_Learning::Get_ActivitiesList($course_id);
        $output         = array_slice($activities, 0, 1);
        $add_activities = array_diff($activities,$output);

        /* Header Users Campaign */
        if ($users_campaign) {
            $lst_users      = implode('<br/>',$users_campaign);
        }

        $form->addElement('header', 'users_lst',get_string('users_lst','local_microlearning'));
        $form->setExpanded('users_lst',true);
        $form->addElement('textarea','users_txt',get_string('users'),'rows="5" disabled style="width:70%;overflow-y:scroll"');
        $form->setDefault('users_txt',html_to_text($lst_users));

        /* Header Send Options      */
        $form->addElement('header','send_opt',get_string('options_mode','local_microlearning'));
        $form->setExpanded('send_opt',true);
        $form->addElement('html','<div class="micro_calendar_mode">');
            /* Date To Send */
            $form->addElement('html','<div class="grp_option_calendar">');
                /* Date To Send */
                $objs = array();

                $objs[0] = $form->createElement('radio', 'sel_date','',get_string('calendar_sel_date','local_microlearning'),CALENDAR_DATE_TO_SEND);
                $objs[0]->setValue(CALENDAR_DATE_TO_SEND);
                $grp = $form->addElement('group', 'grp_DateToSend', null, $objs,null , false);
            $form->addElement('html','</div>');//grp_option_calendar

            /* Date To Send - Date Selector */
            $form->addElement('html','<div class="grp_option_calendar">');
                $form->addElement('date_selector','date_send',null);
                $form->setDefault('date_send',time() + 3600 * 24);
            $form->addElement('html','</div>');//grp_option_calendar

            /* X Days   */
            $form->addElement('html','<div class="grp_option_calendar">');
                /* X Days   */
                $objs = array();
                $objs[0] = $form->createElement('radio', 'sel_date','',get_string('calendar_not_done_one','local_microlearning'),CALENDAR_X_DAYS);
                $objs[0]->setValue(CALENDAR_X_DAYS);
                $grp = $form->addElement('group', 'grp_XDays', null, $objs,null , false);
            $form->addElement('html','</div>');//grp_option_calendar

            /* X Days - Activity to Select  */
            $form->addElement('html','<div class="grp_option_calendar">');
                $form->addElement('select','act_not_done',null,$activities);
                $form->setDefault('act_not_done',0);
            $form->addElement('html','</div>');//grp_option_calendar

            /* X Days - Days After Label    */
            $form->addElement('html','<div class="grp_option_calendar">');
                $objs = array();

                $objs[0] = $form->createElement('text','x_days',null,'size=2 class="input_x_days"');
                $objs[0]->setType('x_days',PARAM_INT);
                $objs[1] = $form->createElement('date_selector', 'date_after',null);
                $objs[1]->setValue(time() + 3600 * 24);
                $grp = $form->addElement('group', 'grp_XDaysSelector', get_string('calendar_not_done_two','local_microlearning'), $objs,get_string('calendar_not_done_three','local_microlearning') , false);
            $form->addElement('html','</div>');//grp_option_calendar

        $form->addElement('html','</div>');//micro_calendar_mode


        /* Header - eMail Details   */
        $form->addElement('header','email_header',get_string('email_header','local_microlearning'));
        $form->setExpanded('email_header',true);
        $form->addElement('text','subject',get_string('email_sub','local_microlearning'),'style="width:70%;"');
        $form->setType('subject',PARAM_TEXT);
        /* Body --> Editor  */
        $form->addElement('editor','body_editor', get_string('email_body','local_microlearning'), null, $edit_options);
        $form->setType('body_editor', PARAM_RAW);

        /* Header - Activities  */
        $form->addElement('header','activities_header',get_string('activities_header','local_microlearning'));
        $form->setExpanded('activities_header',true);
        $form->addElement('static', 'activities-description', '', get_string('activities_desc', 'local_microlearning'));

        $form->addElement('html','<div class="micro_calendar_mode">');
            $form->addElement('html','<div class="sel_activities_left">');
                $sel_activities = array();

                if (($delivery_info) && (isset($delivery_info->activities)) && ($delivery_info->activities)) {
                    foreach ($delivery_info->activities as $key=>$act) {
                        if (array_key_exists($act,$add_activities)) {
                            if (!array_key_exists($act,$SESSION->activities)) {
                                $SESSION->activities[$act] = $add_activities[$act];
                            }
                        }

                        unset($add_activities[$act]);
                    }//for_session_activities
                }//if_delivery_info_Activities

                if (isset($SESSION->activities) && $SESSION->activities) {
                    $sel_activities = $sel_activities + $SESSION->activities;

                    foreach ($SESSION->activities as $key=>$act) {
                        unset($add_activities[$key]);
                    }//for_session_activities
                }//if_session_activities

                if (isset($SESSION->removeActivities) && $SESSION->removeActivities) {
                    foreach ($SESSION->removeActivities as $key=>$act) {
                        unset($sel_activities[$key]);
                        unset($SESSION->activities[$key]);

                        $add_activities[$key] = $act;
                    }//for_session_removeactivities
                }//if_session_remove

                $form->addElement('select','sel_activities','',$sel_activities,'multiple size="10"');
                $form->addElement('text','search_sel_act',get_string('search'));
                $form->setType('search_sel_act',PARAM_TEXT);
            $form->addElement('html','</div>');//sel_activities_left

            $form->addElement('html','<div class="sel_activities_buttons">');
                $add_btn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $remove_btn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                /* Add Activity     */
                $form->addElement('submit','add_sel',$add_btn);
                /* Remove Activity  */
                $form->addElement('submit','remove_sel',$remove_btn);
            $form->addElement('html','</div>');//sel_activities_buttons

            /* Activities -- To Add     */
            $form->addElement('html','<div class="sel_activities_right">');
                $form->addElement('select','add_activities','',$add_activities,'multiple size="10"');
                $form->addElement('text','search_add_act',get_string('search'));
                $form->setType('search_add_act',PARAM_TEXT);
            $form->addElement('html','</div>');///sel_activities_right
        $form->addElement('html','</div>');//micro_calendar_mode

        /* BUTTONS  */
        $buttons = array();
        $buttons[] = $form->createElement('submit','submitbutton','SAVE');
        $buttons[] = $form->createElement('submit','submitbutton2','SAVE & RETURN COURSE');
        $buttons[] = $form->createElement('cancel');

        $form->addGroup($buttons, 'buttonar', '', array(' '), false);
        $form->setType('buttonar', PARAM_RAW);
        $form->closeHeaderBefore('buttonar');

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$course_id);

        $form->addElement('hidden','mode');
        $form->setType('mode',PARAM_INT);
        $form->setDefault('mode',$mode);

        $form->addElement('hidden','cp');
        $form->setType('cp',PARAM_INT);
        $form->setDefault('cp',$campaign);

        if (($delivery_info) && isset($delivery_info->activities)) {
            $form->addElement('hidden','cm');
            $form->setType('cm',PARAM_INT);
            $form->setDefault('cm',$delivery_info->delivery);
            $this->set_data($delivery_info);
        }//if_delivery_info
    }//function_definition

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        /* Parameters   */
        list($course_id,$mode,$users_campaign,$campaign,$delivery_info,$edit_options) = $this->_customdata;

        if ((isset($data['submitbutton']) && $data['submitbutton'])
            ||
            (isset($data['submitbutton2']) && $data['submitbutton2'])) {
            /* Check subject    */
            if (!$data['subject']) {
                $errors['subject'] = get_string('required');
                return $errors;
            }//if_subject

            /* Get the eMail Body   from the editor && and check that's not empty */
            $editor = new stdClass();
            $editor->body_editor = $data['body_editor'];
            $editor->body = '';
            $editor = file_postupdate_standard_editor($editor, 'body', $edit_options, context_course::instance($course_id), 'course', 'calendar_mode', 0);
            if (!$editor->body) {
                $errors['body_editor'] = get_string('required');
                return $errors;
            }

            /* SEND OPTIONS */
            if (isset($data['sel_date']) && ($data['sel_date'])) {
                $time = time() - (60*60*24);
                switch ($data['sel_date']) {
                    case CALENDAR_X_DAYS:
                        if (!$data['act_not_done']) {
                            $errors['grp_XDays'] = get_string('required');
                            return $errors;
                        }//if_act_not_done

                        if (!$data['x_days']) {
                            $errors['grp_XDaysSelector'] = get_string('required');
                            return $errors;
                        }//if_x_days

                        $days       = 60*60*24*$data['x_days'];
                        $date_after = $data['date_after'] + $days;
                        if ($date_after <= $time) {
                            $errors['grp_XDaysSelector'] = get_string('calendar_date_err','local_microlearning');
                            return $errors;
                        }

                        break;
                    case CALENDAR_DATE_TO_SEND:
                        if ($data['date_send'] <= $time) {
                            $errors['grp_DateToSend'] = get_string('calendar_date_err','local_microlearning');
                            return $errors;
                        }

                        break;
                }//switch_sel_data
            }else {
                $errors['grp_DateToSend']   = get_string('required');
                $errors['grp_XDays']        = get_string('required');
            }//send_options

            /* Sel Activities */
            global $SESSION;
            if (!isset($SESSION->activities) || !$SESSION->activities) {
                $errors['sel_activities'] = get_string('required');
            }//sel_activities
        }//data_submitbutton

        return $errors;
    }//validaton
}//calendat_mode_form