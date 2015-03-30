<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/* Form to edit a outcome. */
class manager_edit_outcome_form extends moodleform {
    function definition() {
        /* Variables    */
        global $OUTPUT,$SESSION;

        $m_form = $this->_form;

        list($outcome_id,$expiration_id) = $this->_customdata;

        $m_form->addElement('header', 'name_area', get_string('expiration_period', 'report_manager'));
        $m_form->addElement('text', 'expiration_period', get_string('expiration_period', 'report_manager'));
        $m_form->setType('expiration_period',PARAM_INT);

        if ($expiration_id) {
            $m_form->setDefault('expiration_period',outcome::Outcome_Expiration($expiration_id));
        }//if_expiration

        $m_form->addElement('header', 'job_roles', get_string('related_job_roles', 'report_manager'));

        /* Job Roles Related    */
        list($job_role_list, $roles_selected) = outcome::Get_JobRoles_ConnectedOutcome($outcome_id);

        /* Available Job roles  */
        $achoices       = null;
        $achoices[0]    = get_string('av_jobroles','report_manager');
        $achoices       = $achoices + $job_role_list;

        /* Selected Job roles   */
        $schoices       = array();
        $schoices[0]    = get_string('not_sel_jobroles','report_manager');
        if ($roles_selected) {
            $schoices [0] = get_string('selected_jobroles','report_manager');
            foreach ($roles_selected as $role) {
                if (!in_array($role,$SESSION->selJobRoles) &&
                    !in_array($role,$SESSION->jobRoles)) {
                    $SESSION->selJobRoles[$role] = $role;
                }

                $schoices[$role] = $job_role_list[$role];
                unset($achoices[$role]);
            }
        }//if_roles_selected

        /* REMOVE ALL JOB ROLES */
        if (isset($SESSION->removeAll) && $SESSION->removeAll) {
            $schoices       = array();
            $schoices[0]    = get_string('not_sel_jobroles','report_manager');
            $achoices[0]    = get_string('av_jobroles','report_manager');
            $achoices       = $achoices + $job_role_list;
        }//if_remove_all

        /* ADD ALL JOB ROLES    */
        if (isset($SESSION->addAll) && $SESSION->addAll) {
            $schoices       = array();
            $schoices [0]   = get_string('selected_jobroles','report_manager');
            $schoices       = $schoices + $job_role_list;
            $achoices       = array();
            $achoices[0]    = get_string('av_jobroles','report_manager');
        }//if_remove_all

        /* Job Roles Selected   */
        if (isset($SESSION->selJobRoles) && $SESSION->selJobRoles) {
            $schoices       = array();
            $schoices[0]    = get_string('selected_jobroles','report_manager');

            foreach ($SESSION->selJobRoles as $job_role) {
                $schoices[$job_role] = $job_role_list[$job_role];
                unset($achoices[$job_role]);
            }
        }//if_selJobRoles

        /* Available Job Roles  */
        if (isset($SESSION->jobRoles) && $SESSION->jobRoles) {
            foreach ($SESSION->jobRoles as $job_role) {
                $achoices[$job_role] = $job_role_list[$job_role];
            }
        }//if_addJobRoles

        $m_form->addElement('html','<div class="job_roles_selector">');
            /* Selected Job Roles   */
            $m_form->addElement('html','<div class="sel_jobroles_left">');
                $m_form->addElement('select','sjobroles','',$schoices,'multiple size="15"');
                $m_form->addElement('text','search_sel_jobroles',get_string('search'));
                $m_form->setType('search_sel_jobroles',PARAM_TEXT);
            $m_form->addElement('html','</div>');//sel_jobroles_left

            /* Buttons          */
            $m_form->addElement('html','<div class="sel_jobroles_buttons">');
                /* Add Job Roles     */
                $add_btn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add'));
                $m_form->addElement('submit','add_sel',$add_btn);
                /* Remove Job Roles  */
                $remove_btn = html_to_text(get_string('remove') . '&nbsp;' . $OUTPUT->rarrow());
                $m_form->addElement('submit','remove_sel',$remove_btn);

                $m_form->addElement('html','</br>');

                /* Add All Job Roles     */
                $add_all_btn    = html_to_text($OUTPUT->larrow() . '&nbsp;'.get_string('add_all', 'report_manager'));
                $m_form->addElement('submit','add_all',$add_all_btn);
                /* Remove All Job Roles  */
                $remove_all_btn = html_to_text(get_string('remove_all', 'report_manager') . '&nbsp;' . $OUTPUT->rarrow());
                $m_form->addElement('submit','remove_all',$remove_all_btn);
            $m_form->addElement('html','</div>');//sel_jobroles_buttons

            /* Job Role List */
            $m_form->addElement('html','<div class="sel_jobroles_right">');
                $m_form->addElement('select','ajobroles', '',$achoices,'multiple size="15"');
                $m_form->addElement('text','search_add_jobroles',get_string('search'));
                $m_form->setType('search_add_jobroles',PARAM_TEXT);
            $m_form->addElement('html','</div>');//sel_jobroles_right
        $m_form->addElement('html','</div>');//job_roles_selector

        $m_form->addElement('hidden','id');
        $m_form->setDefault('id',$outcome_id);
        $m_form->setType('id',PARAM_INT);

        $m_form->addElement('hidden','expid');
        $m_form->setDefault('expid',$expiration_id);
        $m_form->setType('expid',PARAM_INT);

        $this->add_action_buttons();
    }//definition
}//manager_edit_outcome_form