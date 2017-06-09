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
 * Course Template - Enrolment Methods
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    07/01/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Course create form template. Enrolment Methods
 * 
 * @updateDate      27/06/2016
 * @author          eFaktor     (fbv)
 * 
 * Description
 * Different enrolment method based on course format
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class ct_enrolment_form extends moodleform {
    function definition () {
        /* Variables */
        list($course,$ct) = $this->_customdata;

        /* Form         */
        $form   = $this->_form;

        $radioBtn = array();
        $radioBtn[0] = $form->createElement('radio','waitinglist','',get_string('enrol_wait_self','local_friadmin'),ENROL_WAITING_SELF);
        $radioBtn[1] = $form->createElement('radio','waitinglist','',get_string('enrol_wait_buk','local_friadmin'),ENROL_WAITING_BULK);
        $form->addGroup($radioBtn,'waiting_radio','','</br></br>',false);
        $form->addRule('waiting_radio',get_string('required'),'required', null, 'server');

        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);
        $form->setDefault('id',$course);

        /* Course Template */
        $form->addElement('hidden', 'ct');
        $form->setType('ct', PARAM_INT);
        $form->setDefault('ct',$ct);

        $this->add_action_buttons(true,get_string('continue'));
    }//definition
}//ct_enrolment_form

class ct_enrolment_settings_form extends moodleform {
    function definition() {
        /* Variables */
        list($course,$enrolMethod,$instance,$ct) = $this->_customdata;

        /* Form     */
        $form   = $this->_form;

        /* Enrolment Key */
        if ($enrolMethod == ENROL_WAITING_SELF) {
            $plugin = enrol_get_plugin('self');
            $form->addElement('passwordunmask', 'password', get_string('password', 'enrol_self'));
            $form->addHelpButton('password', 'password', 'enrol_self');
            if (empty($instance->id) && $plugin->get_config('requirepassword','enrol_self')) {
                $form->addRule('password', get_string('required'), 'required', null, 'client');
            }
        }

        $form->addElement('date_selector', 'date_off', get_string('cutoffdate', 'enrol_waitinglist'),array('optional' => true));
        $form->setDefault('date_off',$instance->date_off);

        if ($enrolMethod == ENROL_WAITING_SELF) {
            $form->addElement('date_selector', 'unenrolenddate', get_string('unenrolenddate', 'enrol_waitinglist'), array('optional' => true));
            $form->setDefault('unenrolenddate', $instance->unenrolenddate);
            $form->addHelpButton('unenrolenddate', 'unenrolenddate', 'enrol_waitinglist');
        }
        
        /* Participants     */
        $form->addElement('text','max_enrolled',  get_string('maxenrolments', 'enrol_waitinglist'), array('size' => '8'));
        $form->addHelpButton('max_enrolled','maxenrolments','enrol_waitinglist');
        $form->setType('max_enrolled',PARAM_INT);
        $form->setDefault('max_enrolled',$instance->max_enrolled);
        /* Size Wait list   */
        $form->addElement('text', 'list_size',  get_string('waitlistsize', 'enrol_waitinglist'), array('size' => '8'));
        $form->addHelpButton('list_size','waitlistsize','enrol_waitinglist');
        $form->setType('list_size',PARAM_INT);
        $form->setDefault('list_size', $instance->list_size);

        /* Require Invoice Information */
        $pluginInvoice = enrol_get_plugin('invoice');
        if ($pluginInvoice) {
            $form->addElement('advcheckbox', 'invoice', get_string('invoice', 'enrol_waitinglist'));
            $form->setDefault('invoice',$instance->invoice);
            $form->addHelpButton('invoice', 'invoice', 'enrol_waitinglist');
        }

        /**
         * Approval
         */
        /* None Option              */
        $form->addElement('radio','approval',get_string('none_approval','enrol_waitinglist'),'',CT_APPROVAL_NONE);
        /* Approval required by manager */
        $form->addElement('radio','approval',get_string('approval','enrol_waitinglist'),'',CT_APPROVAL_REQUIRED);
        /* Mail to manager option   */
        $form->addElement('radio','approval',get_string('approval_message','enrol_waitinglist'),'',CT_APPROVAL_MESSAGE);
        /* No Demand Company        */
        $form->addElement('radio','approval',get_string('company_demanded','enrol_waitinglist'),'',CT_COMPANY_NO_DEMANDED);
        $form->setDefault('approval',$instance->approval);
        /**
         * @updateDate      21/06/2016
         * @author          eFaktor     (fbv)
         *
         * Description
         * Internal & External Price
         */
        /* Internal */
        $form->addElement('text','priceinternal',  get_string('in_price', 'enrol_waitinglist'), array('size' => '8'));
        $form->setType('priceinternal',PARAM_TEXT);
        $form->setDefault('priceinternal',$instance->priceinternal);
        /* External */
        $form->addElement('text','priceexternal',  get_string('ext_price', 'enrol_waitinglist'), array('size' => '8'));
        $form->setType('priceexternal',PARAM_TEXT);
        $form->setDefault('priceexternal',$instance->priceexternal);

        /* Course Id */
        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);
        $form->setDefault('id',$course);

        /* Course Template */
        $form->addElement('hidden', 'ct');
        $form->setType('ct', PARAM_INT);
        $form->setDefault('ct',$ct);

        /* Enrol Method Selected */
        $form->addElement('hidden', 'waitinglist');
        $form->setType('waitinglist', PARAM_INT);
        $form->setDefault('waitinglist',$enrolMethod);


        /* Instance Id - Waiting list Id */
        $form->addElement('hidden', 'instanceid');
        $form->setType('instanceid', PARAM_INT);
        $form->setDefault('instanceid',$instance->id);

        /* Self Method Id   */
        $form->addElement('hidden', 'selfid');
        $form->setType('selfid', PARAM_INT);
        $form->setDefault('selfid',$instance->selfid);

        /* Bulk Method Id   */
        $form->addElement('hidden', 'bulkid');
        $form->setType('bulkid', PARAM_INT);
        $form->setDefault('bulkid',$instance->bulkid);

        /* Manual Method    */
        $form->addElement('hidden', 'manualid');
        $form->setType('manualid', PARAM_INT);
        $form->setDefault('manualid',$instance->manualid);

        /**
         * @updateDate  17/06/2016
         * @author      eFaktor     (fbv)
         *
         * Description
         * Add informatin about welcome messages
         */
        /* Welcome Message */
        $form->addElement('hidden', 'welcome_message');
        $form->setType('welcome_message', PARAM_TEXT);
        $form->setDefault('welcome_message',$instance->welcome_message);

        /* Self Waiting Welcome Message */
        $form->addElement('hidden', 'self_waiting_message');
        $form->setType('self_waiting_message', PARAM_TEXT);
        $form->setDefault('self_waiting_message',$instance->self_waiting_message);

        /* Bulk Waiting Welcome Message */
        $form->addElement('hidden', 'bulk_waiting_message');
        $form->setType('bulk_waiting_message', PARAM_TEXT);
        $form->setDefault('bulk_waiting_message',$instance->bulk_waiting_message);

        /* Bulk Renovation Message */
        $form->addElement('hidden', 'bulk_renovation_message');
        $form->setType('bulk_renovation_message', PARAM_TEXT);
        $form->setDefault('bulk_renovation_message',$instance->bulk_renovation_message);

        /**
         * @updateDate  30/08/2016
         * @author      eFaktor     (fbv)
         *
         * Description
         * Add field for send confirmation
         */
        /* Bulk Send Confirmation   */
        $form->addElement('hidden', 'bulk_send_confirmation');
        $form->setType('bulk_send_confirmation', PARAM_TEXT);
        $form->setDefault('bulk_send_confirmation',$instance->bulk_send_confirmation);

        $this->add_action_buttons(true,get_string('continue'));
    }//definition

    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);

        list($course,$enrolMethod,$instance,$ct) = $this->_customdata;

        return $errors;
    }
}//ct_enrolment_settings_form

class ct_self_enrolment_settings_form extends moodleform {
    function definition() {
        /* Variables */
        list($course,$instance,$ct) = $this->_customdata;

        $plugin     = enrol_get_plugin('self');
        $context   = context_course::instance($course);

        /* Form     */
        $form   = $this->_form;

        $form->addElement('header', 'header', get_string('pluginname', 'enrol_self'));

        $nameattribs = array('size' => '20', 'maxlength' => '255');
        $form->addElement('text', 'name', get_string('custominstancename', 'enrol'), $nameattribs);
        $form->setType('name', PARAM_TEXT);
        $form->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'server');

        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $form->addElement('select', 'status', get_string('status', 'enrol_self'), $options);
        $form->addHelpButton('status', 'status', 'enrol_self');

        $options = array(1 => get_string('yes'), 0 => get_string('no'));
        $form->addElement('select', 'customint6', get_string('newenrols', 'enrol_self'), $options);
        $form->addHelpButton('customint6', 'newenrols', 'enrol_self');
        $form->disabledIf('customint6', 'status', 'eq', ENROL_INSTANCE_DISABLED);

        $passattribs = array('size' => '20', 'maxlength' => '50');
        $form->addElement('passwordunmask', 'password', get_string('password', 'enrol_self'), $passattribs);
        $form->addHelpButton('password', 'password', 'enrol_self');
        if (empty($instance->id) and $plugin->get_config('requirepassword')) {
            $form->addRule('password', get_string('required'), 'required', null, 'client');
        }
        $form->addRule('password', get_string('maximumchars', '', 50), 'maxlength', 50, 'server');

        $options = array(1 => get_string('yes'),
            0 => get_string('no'));
        $form->addElement('select', 'customint1', get_string('groupkey', 'enrol_self'), $options);
        $form->addHelpButton('customint1', 'groupkey', 'enrol_self');

        $roles = $this->extend_assignable_roles($context, $instance->roleid);
        $form->addElement('select', 'roleid', get_string('role', 'enrol_self'), $roles);

        $form->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_self'), array('optional' => true, 'defaultunit' => 86400));
        $form->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_self');

        $options = array(0 => get_string('no'), 1 => get_string('expirynotifyenroller', 'core_enrol'), 2 => get_string('expirynotifyall', 'core_enrol'));
        $form->addElement('select', 'expirynotify', get_string('expirynotify', 'core_enrol'), $options);
        $form->addHelpButton('expirynotify', 'expirynotify', 'core_enrol');

        $form->addElement('duration', 'expirythreshold', get_string('expirythreshold', 'core_enrol'), array('optional' => false, 'defaultunit' => 86400));
        $form->addHelpButton('expirythreshold', 'expirythreshold', 'core_enrol');
        $form->disabledIf('expirythreshold', 'expirynotify', 'eq', 0);

        $form->addElement('date_time_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_self'), array('optional' => true));
        $form->setDefault('enrolstartdate', 0);
        $form->addHelpButton('enrolstartdate', 'enrolstartdate', 'enrol_self');

        $form->addElement('date_time_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_self'), array('optional' => true));
        $form->setDefault('enrolenddate', 0);
        $form->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_self');

        $options = array(0 => get_string('never'),
                         1800 * 3600 * 24 => get_string('numdays', '', 1800),
                         1000 * 3600 * 24 => get_string('numdays', '', 1000),
                         365 * 3600 * 24 => get_string('numdays', '', 365),
                         180 * 3600 * 24 => get_string('numdays', '', 180),
                         150 * 3600 * 24 => get_string('numdays', '', 150),
                         120 * 3600 * 24 => get_string('numdays', '', 120),
                         90 * 3600 * 24 => get_string('numdays', '', 90),
                         60 * 3600 * 24 => get_string('numdays', '', 60),
                         30 * 3600 * 24 => get_string('numdays', '', 30),
                         21 * 3600 * 24 => get_string('numdays', '', 21),
                         14 * 3600 * 24 => get_string('numdays', '', 14),
                         7 * 3600 * 24 => get_string('numdays', '', 7));
        $form->addElement('select', 'customint2', get_string('longtimenosee', 'enrol_self'), $options);
        $form->addHelpButton('customint2', 'longtimenosee', 'enrol_self');

        $form->addElement('text', 'customint3', get_string('maxenrolled', 'enrol_self'));
        $form->addHelpButton('customint3', 'maxenrolled', 'enrol_self');
        $form->setType('customint3', PARAM_INT);

        $cohorts = $this->getCohorts($context,$instance->customint5);
        if (count($cohorts) > 1) {
            $form->addElement('select', 'customint5', get_string('cohortonly', 'enrol_self'), $cohorts);
            $form->addHelpButton('customint5', 'cohortonly', 'enrol_self');
        } else {
            $form->addElement('hidden', 'customint5');
            $form->setType('customint5', PARAM_INT);
            $form->setConstant('customint5', 0);
        }

        $this->add_action_buttons(true,get_string('continue'));

        $this->set_data($instance);

        /* Instance Id  */
        $form->addElement('hidden', 'instanceid');
        $form->setType('instanceid', PARAM_INT);
        $form->setDefault('instanceid',$instance->id);

        /* Course Id */
        $form->addElement('hidden', 'id');
        $form->setType('id', PARAM_INT);
        $form->setDefault('id',$course);

        /* Course Template */
        $form->addElement('hidden', 'ct');
        $form->setType('ct', PARAM_INT);
        $form->setDefault('ct',$ct);

        /* Welcome Message */
        $form->addElement('hidden', 'customtext1');
        $form->setType('customtext1', PARAM_TEXT);
        $form->setDefault('customtext1',$instance->customtext1);
    }//definition

    function extend_assignable_roles($context, $defaultrole) {
        global $DB;
        $roles = null;

        try {
            $roles = get_assignable_roles($context, ROLENAME_BOTH);
            if (!isset($roles[$defaultrole])) {
                if ($role = $DB->get_record('role', array('id'=>$defaultrole))) {
                    $roles[$defaultrole] = role_get_name($role, $context, ROLENAME_BOTH);
                }
            }
            return $roles;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }


    function getCohorts($context,$current) {
        global $DB;
        $cohorts = null;
        $params  = null;

        try {
            $cohorts = array(0 => get_string('no'));
            /* Search criteria */
            list($sqlparents, $params)  = $DB->get_in_or_equal($context->get_parent_context_ids(), SQL_PARAMS_NAMED);
            $params['current']          = $current;

            /* SQL Instrucion */
            $sql = "SELECT id, name, idnumber, contextid
                    FROM {cohort}
                    WHERE   contextid $sqlparents 
                        OR  id = :current
                    ORDER BY name ASC, idnumber ASC ";
            $rs = $DB->get_recordset_sql($sql, $params);

            foreach ($rs as $c) {
                $ccontext = context::instance_by_id($c->contextid);
                if ($c->id != $current and !has_capability('moodle/cohort:view', $ccontext)) {
                    continue;
                }
                $cohorts[$c->id] = format_string($c->name, true, array('context'=>$context));
                if ($c->idnumber) {
                    $cohorts[$c->id] .= ' ['.s($c->idnumber).']';
                }
            }
            if (!isset($cohorts[$current])) {
                // Somebody deleted a cohort, better keep the wrong value so that random ppl can not enrol.
                $cohorts[$current] = get_string('unknowncohort', 'cohort', $current);
            }
            $rs->close();

            return $cohorts;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getCohorts
}//ct_self_enrolment_settings_form