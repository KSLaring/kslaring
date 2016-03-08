<?php
/**
 * Invoice Enrolment - Edit Form
 *
 * @package         enrol
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    25/09/2014
 * @author          efaktor     (fbv)
 *
 * Description
 *  - Add a new instance of Invoice enrollment to specified course or edits current instance.
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class enrol_invoice_edit_form extends moodleform {
    function definition() {
        global $DB;

        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_invoice'));

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        /* Status   */
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_invoice'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_invoice');

        /* New Enrols   */
        $options = array(1 => get_string('yes'), 0 => get_string('no'));
        $mform->addElement('select', 'customint6', get_string('new_enrols', 'enrol_invoice'), $options);
        $mform->addHelpButton('customint6', 'new_enrols', 'enrol_invoice');
        $mform->disabledIf('customint6', 'status', 'eq', ENROL_INSTANCE_DISABLED);

        /* Password     */
        $mform->addElement('passwordunmask', 'password', get_string('password', 'enrol_invoice'));
        $mform->addHelpButton('password', 'password', 'enrol_invoice');
        if (empty($instance->id) and $plugin->get_config('require_password')) {
            $mform->addRule('password', get_string('required'), 'required', null, 'client');
        }

        /* Group Key    */
        $options = array(1 => get_string('yes'),
                         0 => get_string('no'));
        $mform->addElement('select', 'customint1', get_string('group_key', 'enrol_invoice'), $options);
        $mform->addHelpButton('customint1', 'group_key', 'enrol_invoice');

        /* Role         */
        if ($instance->id) {
            $roles = $this->extend_assignable_roles($context, $instance->roleid);
        } else {
            $roles = $this->extend_assignable_roles($context, $plugin->get_config('role_id'));
        }//if_instance_id
        $mform->addElement('select', 'roleid', get_string('role', 'enrol_invoice'), $roles);

        /* Enrol Period */
        $mform->addElement('duration', 'enrolperiod', get_string('enrol_period', 'enrol_invoice'), array('optional' => true, 'defaultunit' => 86400));
        $mform->addHelpButton('enrolperiod', 'enrol_period', 'enrol_invoice');

        /* Expire Notify    */
        $options = array(0 => get_string('no'), 1 => get_string('expirynotifyenroller', 'core_enrol'), 2 => get_string('expirynotifyall', 'core_enrol'));
        $mform->addElement('select', 'expirynotify', get_string('expirynotify', 'core_enrol'), $options);
        $mform->addHelpButton('expirynotify', 'expirynotify', 'core_enrol');

        /* expiry threshold */
        $mform->addElement('duration', 'expirythreshold', get_string('expirythreshold', 'core_enrol'), array('optional' => false, 'defaultunit' => 86400));
        $mform->addHelpButton('expirythreshold', 'expirythreshold', 'core_enrol');
        $mform->disabledIf('expirythreshold', 'expirynotify', 'eq', 0);

        /* Enrol Start Date */
        $mform->addElement('date_selector', 'enrolstartdate', get_string('enrol_start_date', 'enrol_invoice'), array('optional' => true));
        $mform->setDefault('enrolstartdate', 0);
        $mform->addHelpButton('enrolstartdate', 'enrol_start_date', 'enrol_invoice');

        /* Enrol End Date   */
        $mform->addElement('date_selector', 'enrolenddate', get_string('enrol_end_date', 'enrol_invoice'), array('optional' => true));
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrol_end_date', 'enrol_invoice');

        /* Timezone         */
        $options = array(0                  => get_string('never'),
                         1800 * 3600 * 24   => get_string('numdays', '', 1800),
                         1000 * 3600 * 24   => get_string('numdays', '', 1000),
                         365 * 3600 * 24    => get_string('numdays', '', 365),
                         180 * 3600 * 24    => get_string('numdays', '', 180),
                         150 * 3600 * 24    => get_string('numdays', '', 150),
                         120 * 3600 * 24    => get_string('numdays', '', 120),
                         90 * 3600 * 24     => get_string('numdays', '', 90),
                         60 * 3600 * 24     => get_string('numdays', '', 60),
                         30 * 3600 * 24     => get_string('numdays', '', 30),
                         21 * 3600 * 24     => get_string('numdays', '', 21),
                         14 * 3600 * 24     => get_string('numdays', '', 14),
                         7 * 3600 * 24      => get_string('numdays', '', 7));
        $mform->addElement('select', 'customint2', get_string('long_time_no_see', 'enrol_invoice'), $options);
        $mform->addHelpButton('customint2', 'long_time_no_see', 'enrol_invoice');

        /* Max Enrolled */
        $mform->addElement('text', 'customint3', get_string('max_enrolled', 'enrol_invoice'));
        $mform->addHelpButton('customint3', 'max_enrolled', 'enrol_invoice');
        $mform->setType('customint3', PARAM_INT);

        /* Cohorts      */
        $cohorts = array(0 => get_string('no'));
        list($sqlparents, $params) = $DB->get_in_or_equal($context->get_parent_context_ids(), SQL_PARAMS_NAMED);
        $params['current'] = $instance->customint5;
        $sql = "SELECT id, name, idnumber, contextid
                  FROM {cohort}
                 WHERE contextid $sqlparents OR id = :current
              ORDER BY name ASC, idnumber ASC";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $c) {
            $ccontext = context::instance_by_id($c->contextid);
            if ($c->id != $instance->customint5 and !has_capability('moodle/cohort:view', $ccontext)) {
                continue;
            }
            $cohorts[$c->id] = format_string($c->name, true, array('context'=>$context));
            if ($c->idnumber) {
                $cohorts[$c->id] .= ' ['.s($c->idnumber).']';
            }
        }
        if (!isset($cohorts[$instance->customint5])) {
            // Somebody deleted a cohort, better keep the wrong value so that random ppl can not enrol.
            $cohorts[$instance->customint5] = get_string('unknowncohort', 'cohort', $instance->customint5);
        }
        $rs->close();
        if (count($cohorts) > 1) {
            $mform->addElement('select', 'customint5', get_string('cohort_only', 'enrol_invoice'), $cohorts);
            $mform->addHelpButton('customint5', 'cohort_only', 'enrol_invoice');
        } else {
            $mform->addElement('hidden', 'customint5');
            $mform->setType('customint5', PARAM_INT);
            $mform->setConstant('customint5', 0);
        }

        /* Welcome Message  */
        $mform->addElement('advcheckbox', 'customint4', get_string('send_course_welcome_message', 'enrol_invoice'));
        $mform->addHelpButton('customint4', 'send_course_welcome_message', 'enrol_invoice');

        /* Custom Welcome Message   */
        $mform->addElement('textarea', 'customtext1', get_string('custom_welcome_message', 'enrol_invoice'), array('cols'=>'60', 'rows'=>'8'));
        $mform->addHelpButton('customtext1', 'custom_welcome_message', 'enrol_invoice');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        if (enrol_accessing_via_instance($instance)) {
            $mform->addElement('static', 'selfwarn', get_string('instanceeditselfwarning', 'core_enrol'), get_string('instanceeditselfwarningtext', 'core_enrol'));
        }

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        /**
         * @updateDate  30/14/2014
         * @author      eFaktor     (fbv)
         *
         * Description
         * Add the participants if it's sandnes format
         */
        $format_options = course_get_format($instance->courseid)->get_format_options();
        if (array_key_exists('participants',$format_options)) {
            $instance->customint3 = $format_options['participants'];
        }
        $this->set_data($instance);
    }//definition

    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);

        list($instance,$plugin, $context) = $this->_customdata;
        $checkpassword = false;

        if ($instance->id) {
            if ($data['status'] == ENROL_INSTANCE_ENABLED) {
                if ($instance->password !== $data['password']) {
                    $checkpassword = true;
                }
            }
        } else {
            if ($data['status'] == ENROL_INSTANCE_ENABLED) {
                $checkpassword = true;
            }
        }//if_instance_id

        if ($checkpassword) {
            $require = $plugin->get_config('require_password');
            $policy  = $plugin->get_config('use_password_policy');
            if ($require and trim($data['password']) === '') {
                $errors['password'] = get_string('required');
            } else if ($policy) {
                $errmsg = '';//prevent eclipse warning
                if (!check_password_policy($data['password'], $errmsg)) {
                    $errors['password'] = $errmsg;
                }
            }
        }//if_check_password

        if ($data['status'] == ENROL_INSTANCE_ENABLED) {
            if (!empty($data['enrolenddate']) and $data['enrolenddate'] < $data['enrolstartdate']) {
                $errors['enrolenddate'] = get_string('enrol_end_dat_error', 'enrol_invoice');
            }
        }//if_status

        if ($data['expirynotify'] > 0 and $data['expirythreshold'] < 86400) {
            $errors['expirythreshold'] = get_string('errorthresholdlow', 'core_enrol');
        }

        return $errors;
    }//validation

    /**
     * Gets a list of roles that this user can assign for the course as the default for self-enrolment.
     *
     * @param context $context the context.
     * @param integer $defaultrole the id of the role that is set as the default for self-enrolment
     * @return array index is the role id, value is the role name
     */
    function extend_assignable_roles($context, $defaultrole) {
        global $DB;

        $roles = get_assignable_roles($context, ROLENAME_BOTH);
        if (!isset($roles[$defaultrole])) {
            if ($role = $DB->get_record('role', array('id'=>$defaultrole))) {
                $roles[$defaultrole] = role_get_name($role, $context, ROLENAME_BOTH);
            }
        }
        return $roles;
    }
}//enrol_invoice_edit_form

