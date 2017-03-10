<?php
/**
 * Invoice Enrolment Plugin - Library Plugin Implementation
 *
 * @package         enrol
 * @subpackage      invoice
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    25/09/2014
 * @author          efaktor     (fbv)
 */

define('ACCOUNT_INVOICE','ACCOUNT');
define('ADDRESS_INVOICE','ADDRESS');
require_once('invoicelib.php');

class enrol_invoice_plugin extends enrol_plugin {
    protected $lasternoller = null;
    protected $lasternollerinstanceid = 0;

    /**
     * Returns optional enrolment information icons.
     *
     * This is used in course list for quick overview of enrolment options.
     *
     * We are not using single instance parameter because sometimes
     * we might want to prevent icon repetition when multiple instances
     * of one type exist. One instance may also produce several icons.
     *
     * @param array $instances all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons(array $instances) {
        $key = false;
        $nokey = false;
        foreach ($instances as $instance) {
            if (!$instance->customint6) {
                // New enrols not allowed.
                continue;
            }
            if ($instance->password or $instance->customint1) {
                $key = true;
            } else {
                $nokey = true;
            }
        }
        $icons = array();
        if ($nokey) {
            $icons[] = new pix_icon('withoutkey', get_string('pluginname', 'enrol_invoice'), 'enrol_invoice');
        }
        if ($key) {
            $icons[] = new pix_icon('withkey', get_string('pluginname', 'enrol_invoice'), 'enrol_invoice');
        }
        return $icons;
    }//get_info_icons

    /**
     * Returns localised name of enrol instance
     *
     * @param stdClass $instance (null is accepted too)
     * @return string
     */
    public function get_instance_name($instance) {
        global $DB;

        if (empty($instance->name)) {
            if (!empty($instance->roleid) and $role = $DB->get_record('role', array('id'=>$instance->roleid))) {
                $role = ' (' . role_get_name($role, context_course::instance($instance->courseid, IGNORE_MISSING)) . ')';
            } else {
                $role = '';
            }
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_'.$enrol) . $role;
        } else {
            return format_string($instance->name);
        }
    }//get_instance_name

    public function roles_protected() {
        // Users may tweak the roles later.
        return false;
    }//roles_protected

    public function allow_unenrol(stdClass $instance) {
        // Users with unenrol cap may unenrol other users manually manually.
        return true;
    }//allow_unenrol

    public function allow_manage(stdClass $instance) {
        // Users with manage cap may tweak period and status.
        return true;
    }//allow_manage

    public function show_enrolme_link(stdClass $instance) {

        if (true !== $this->can_invoice_enrol($instance, false)) {
            return false;
        }

        return true;
    }//show_enrolme_link

    /**
     * Sets up navigation entries.
     *
     * @param stdClass $instancesnode
     * @param stdClass $instance
     * @return void
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        global $PAGE;

        if ($instance->enrol !== 'invoice') {
            throw new coding_exception('Invalid enrol instance type!');
        }

        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/invoice:config', $context)) {
            $managelink = new moodle_url('/enrol/invoice/edit.php', array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);

            /* Add Report Invoice Link */
            $parent_node = $instancesnode->parent;
            $parent_node = $parent_node->parent;
            $str_title = get_string('report_link', 'enrol_invoice');
            $url = new moodle_url('/enrol/invoice/report/report_invoice.php',array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $report_invoices = navigation_node::create($str_title,
                                                       $url,
                                                       navigation_node::TYPE_SETTING,'report_invoices',
                                                       'report_invoices',
                                                       new pix_icon('i/report', $str_title)
                                                      );

            if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                $report_invoices->make_active();
            }
            $parent_node->add_node($report_invoices,'users');
        }
    }//add_course_navigation

    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'invoice') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();

        if (has_capability('enrol/invoice:config', $context)) {
            $editlink = new moodle_url("/enrol/invoice/edit.php", array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit'), 'core',array('class' => 'iconsmall')));
        }

        return $icons;
    }//get_action_icons

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/invoice:config', $context)) {
            return NULL;
        }
        // Multiple instances supported - different roles with different password.
        return new moodle_url('/enrol/invoice/edit.php', array('courseid'=>$courseid));
    }//get_newinstance_link

    /**
     * Invoice enrol user to course
     *
     * @param stdClass $instance enrolment instance
     * @param stdClass $data data needed for enrolment.
     * @return bool|array true if enroled else eddor code and messege
     */
    public function enrol_invoice(stdClass $instance, $data = null) {
        global $DB, $USER, $CFG;

        // Don't enrol user if password is not passed when required.
        if ($instance->password && !isset($data->enrolpassword)) {
            return;
        }

        $timestart = time();
        if ($instance->enrolperiod) {
            $timeend = $timestart + $instance->enrolperiod;
        } else {
            $timeend = 0;
        }

        $this->enrol_user($instance, $USER->id, $instance->roleid, $timestart, $timeend);

        if ($instance->password and $instance->customint1 and $data->enrolpassword !== $instance->password) {
            // It must be a group enrolment, let's assign group too.
            $groups = $DB->get_records('groups', array('courseid'=>$instance->courseid), 'id', 'id, enrolmentkey');
           foreach ($groups as $group) {
                if (empty($group->enrolmentkey)) {
                    continue;
                }
                if ($group->enrolmentkey === $data->enrolpassword) {
                    // Add user to group.
                    require_once($CFG->dirroot.'/group/lib.php');
                    groups_add_member($group->id, $USER->id);
                    break;
                }
            }
        }

        Invoices::add_invoice_info($data,$USER->id,$instance->courseid);

        // Send welcome message.
        if ($instance->customint4) {
            $this->email_welcome_message($instance, $USER);
        }
    }//enrol_invoice

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        global $CFG, $OUTPUT, $USER,$PAGE;

        require_once("$CFG->dirroot/enrol/invoice/locallib.php");

        $enrolstatus = $this->can_invoice_enrol($instance);

        // Don't show enrolment instance form, if user can't enrol using it.
        if (true === $enrolstatus) {
            $PAGE->requires->js('/enrol/invoice/js/invoice.js');
            $form = new enrol_invoice_enrol_form(NULL, $instance);
            $instanceid = optional_param('instance', 0, PARAM_INT);
            if ($instance->id == $instanceid) {
                if ($data = $form->get_data()) {
                        $this->enrol_invoice($instance, $data);
                }
            }

            ob_start();
            $form->display();
            $output = ob_get_clean();
            return $OUTPUT->box($output);
        }

        return null;
    }//enrol_page_hook

    /**
     * Checks if user can invoice enrol.
     *
     * @param stdClass $instance enrolment instance
     * @param bool $checkuserenrolment if true will check if user enrolment is inactive.
     *             used by navigation to improve performance.
     * @return bool|string true if successful, else error message or false.
     */
    public function can_invoice_enrol(stdClass $instance, $checkuserenrolment = true) {
        global $DB, $USER, $CFG;

        if ($checkuserenrolment) {
            if (isguestuser()) {
                // Can not enrol guest.
                return get_string('cannt_enrol', 'enrol_invoice');
            }
            // Check if user is already enroled.
            if ($DB->get_record('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id))) {
                return get_string('cannt_enrol', 'enrol_invoice');
            }
        }

        if ($instance->status != ENROL_INSTANCE_ENABLED) {
            return get_string('cannt_enrol', 'enrol_invoice');
        }

        if ($instance->enrolstartdate != 0 and $instance->enrolstartdate > time()) {
            return get_string('cannt_enrol', 'enrol_invoice');
        }

        if ($instance->enrolenddate != 0 and $instance->enrolenddate < time()) {
            return get_string('cannt_enrol', 'enrol_invoice');
        }

        if (!$instance->customint6) {
            // New enrols not allowed.
            return get_string('cannt_enrol', 'enrol_invoice');
        }

        if ($DB->record_exists('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id))) {
            return get_string('cannt_enrol', 'enrol_invoice');
        }

        if ($instance->customint3 > 0) {
            // Max enrol limit specified.
            $count = $DB->count_records('user_enrolments', array('enrolid' => $instance->id));
            if ($count >= $instance->customint3) {
                // Bad luck, no more invoice enrolments here.
                return get_string('max_enrolled_reached', 'enrol_invoice');
            }
        }

        if ($instance->customint5) {
            require_once("$CFG->dirroot/cohort/lib.php");
            if (!cohort_is_member($instance->customint5, $USER->id)) {
                $cohort = $DB->get_record('cohort', array('id' => $instance->customint5));
                if (!$cohort) {
                    return null;
                }
                $a = format_string($cohort->name, true, array('context' => context::instance_by_id($cohort->contextid)));
                return markdown_to_html(get_string('cohort_non_member_info', 'enrol_invoice', $a));
            }
        }

        return true;
    }//can_invoice_enrol

    /**
     * Return information for enrolment instance containing list of parameters required
     * for enrolment, name of enrolment plugin etc.
     *
     * @param stdClass $instance enrolment instance
     * @return stdClass instance info.
     */
    public function get_enrol_info(stdClass $instance) {

        $instanceinfo = new stdClass();
        $instanceinfo->id       = $instance->id;
        $instanceinfo->courseid = $instance->courseid;
        $instanceinfo->type     = $this->get_name();
        $instanceinfo->name     = $this->get_instance_name($instance);
        $instanceinfo->status   = $this->can_invoice_enrol($instance);

        if ($instance->password) {
            $instanceinfo->requiredparam = new stdClass();
            $instanceinfo->requiredparam->enrolpassword = get_string('password', 'enrol_invoice');
        }

        // If enrolment is possible and password is required then return ws function name to get more information.
        if ((true === $instanceinfo->status) && $instance->password) {
            $instanceinfo->wsfunction = 'enrol_invoice_get_instance_info';
        }
        return $instanceinfo;
    }//get_enrol_info

    /**
     * Add new instance of enrol plugin with default settings.
     * @param stdClass $course
     * @return int id of new instance
     */
    public function add_default_instance($course) {
        $fields = $this->get_instance_defaults();

        if ($this->get_config('require_password')) {
            $fields['password'] = generate_password(20);
        }

        return $this->add_instance($course, $fields);
    }//add_default_instance

    /**
     * Returns defaults for new instances.
     * @return array
     */
    public function get_instance_defaults() {
        $expirynotify = $this->get_config('expiry_notify');
        if ($expirynotify == 2) {
            $expirynotify = 1;
            $notifyall = 1;
        } else {
            $notifyall = 0;
        }

        $fields = array();
        $fields['status']          = $this->get_config('status');
        $fields['roleid']          = $this->get_config('role_id');
        $fields['enrolperiod']     = $this->get_config('enrol_period');
        $fields['expirynotify']    = $expirynotify;
        $fields['notifyall']       = $notifyall;
        $fields['expirythreshold'] = $this->get_config('expiry_threshold');
        $fields['customint1']      = $this->get_config('group_key');
        $fields['customint2']      = $this->get_config('long_time_no_see');
        $fields['customint3']      = $this->get_config('max_enrolled');
        $fields['customint4']      = $this->get_config('send_course_welcome_message');
        $fields['customint5']      = 0;
        $fields['customint6']      = $this->get_config('new_enrols');

        return $fields;
    }//get_instance_defaults


    /**
     * Send welcome email to specified user.
     *
     * @param stdClass $instance
     * @param stdClass $user user record
     * @return void
     */
    protected function email_welcome_message($instance, $user) {
        global $CFG, $DB;

        $course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
        $context = context_course::instance($course->id);

        $a = new stdClass();
        $a->coursename = format_string($course->fullname, true, array('context'=>$context));
        $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id&course=$course->id";

        if (trim($instance->customtext1) !== '') {
            $message = $instance->customtext1;
            $message = str_replace('{$a->coursename}', $a->coursename, $message);
            $message = str_replace('{$a->profileurl}', $a->profileurl, $message);
            if (strpos($message, '<') === false) {
                // Plain text only.
                $messagetext = $message;
                $messagehtml = text_to_html($messagetext, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $messagehtml = format_text($message, FORMAT_MOODLE, array('context'=>$context, 'para'=>false, 'newlines'=>true, 'filter'=>true));
                $messagetext = html_to_text($messagehtml);
            }
        } else {
            $messagetext = get_string('welcome_to_course_text', 'enrol_invoice', $a);
            $messagehtml = text_to_html($messagetext, null, false, true);
        }

        $subject = get_string('welcome_to_course', 'enrol_invoice', format_string($course->fullname, true, array('context'=>$context)));

        $rusers = array();
        if (!empty($CFG->coursecontact)) {
            $croles = explode(',', $CFG->coursecontact);
            list($sort, $sortparams) = users_order_by_sql('u');
            $rusers = get_role_users($croles, $context, true, '', 'r.sortorder ASC, ' . $sort, null, '', '', '', '', $sortparams);
        }
        if ($rusers) {
            $contact = reset($rusers);
        } else {
            $contact = core_user::get_support_user();
        }

        // Directly emailing welcome message rather than using messaging.
        email_to_user($user, $contact, $subject, $messagetext, $messagehtml);
    }//email_welcome_message

    /**
     * Enrol invoice cron support.
     * @return void
     */
    public function cron() {
        $trace = new text_progress_trace();
        $this->sync($trace, null);
    }//cron

    /**
     * Sync all meta course links.
     *
     * @param progress_trace $trace
     * @param int $courseid one course, empty mean all
     * @return int 0 means ok, 1 means error, 2 means plugin disabled
     */
    public function sync(progress_trace $trace, $courseid = null) {
        global $DB;

        if (!enrol_is_enabled('invoice')) {
            $trace->finished();
            return 2;
        }

        // Unfortunately this may take a long time, execution can be interrupted safely here.
        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);

        $trace->output('Verifying invoice-enrolments...');

        $params = array('now'=>time(), 'useractive'=>ENROL_USER_ACTIVE, 'courselevel'=>CONTEXT_COURSE);
        $coursesql = "";
        if ($courseid) {
            $coursesql = "AND e.courseid = :courseid";
            $params['courseid'] = $courseid;
        }

        // Note: the logic of invoice enrolment guarantees that user logged in at least once (=== u.lastaccess set)
        //       and that user accessed course at least once too (=== user_lastaccess record exists).

        // First deal with users that did not log in for a really long time - they do not have user_lastaccess records.
        $sql = "SELECT e.*, ue.userid
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'invoice' AND e.customint2 > 0)
                  JOIN {user} u ON u.id = ue.userid
                 WHERE :now - u.lastaccess > e.customint2
                       $coursesql";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $instance) {
            $userid = $instance->userid;
            unset($instance->userid);
            $this->unenrol_user($instance, $userid);
            $days = $instance->customint2 / 60*60*24;
            $trace->output("unenrolling user $userid from course $instance->courseid as they have did not log in for at least $days days", 1);
        }
        $rs->close();

        // Now unenrol from course user did not visit for a long time.
        $sql = "SELECT e.*, ue.userid
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'invoice' AND e.customint2 > 0)
                  JOIN {user_lastaccess} ul ON (ul.userid = ue.userid AND ul.courseid = e.courseid)
                 WHERE :now - ul.timeaccess > e.customint2
                       $coursesql";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $instance) {
            $userid = $instance->userid;
            unset($instance->userid);
            $this->unenrol_user($instance, $userid);
            $days = $instance->customint2 / 60*60*24;
            $trace->output("unenrolling user $userid from course $instance->courseid as they have did not access course for at least $days days", 1);
        }
        $rs->close();

        $trace->output('...user invoice-enrolment updates finished.');
        $trace->finished();

        $this->process_expirations($trace, $courseid);

        return 0;
    }//sync

    /**
     * Returns the user who is responsible for invoice enrolments in given instance.
     *
     * Usually it is the first editing teacher - the person with "highest authority"
     * as defined by sort_by_roleassignment_authority() having 'enrol/invoice:manage'
     * capability.
     *
     * @param int $instanceid enrolment instance id
     * @return stdClass user record
     */
    protected function get_enroller($instanceid) {
        global $DB;

        if ($this->lasternollerinstanceid == $instanceid and $this->lasternoller) {
            return $this->lasternoller;
        }

        $instance = $DB->get_record('enrol', array('id'=>$instanceid, 'enrol'=>$this->get_name()), '*', MUST_EXIST);
        $context = context_course::instance($instance->courseid);

        if ($users = get_enrolled_users($context, 'enrol/invoice:manage')) {
            $users = sort_by_roleassignment_authority($users, $context);
            $this->lasternoller = reset($users);
            unset($users);
        } else {
            $this->lasternoller = parent::get_enroller($instanceid);
        }

        $this->lasternollerinstanceid = $instanceid;

        return $this->lasternoller;
    }//get_enroller

    /**
     * Gets an array of the user enrolment actions.
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions        = array();
        $context        = $manager->get_context();
        $instance       = $ue->enrolmentinstance;
        $params         = $manager->get_moodlepage()->url->params();
        $params['ue']   = $ue->id;
        if ($this->allow_unenrol($instance) && has_capability("enrol/invoice:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/invoice:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class'=>'editenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }//get_user_enrolment_actions

    /**
     * Restore instance and map settings.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $course
     * @param int $oldid
     */
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB;
        if ($step->get_task()->get_target() == backup::TARGET_NEW_COURSE) {
            $merge = false;
        } else {
            $merge = array(
                'courseid'   => $data->courseid,
                'enrol'      => $this->get_name(),
                'roleid'     => $data->roleid,
            );
        }
        if ($merge and $instances = $DB->get_records('enrol', $merge, 'id')) {
            $instance = reset($instances);
            $instanceid = $instance->id;
        } else {
            if (!empty($data->customint5)) {
                if ($step->get_task()->is_samesite()) {
                    // Keep cohort restriction unchanged - we are on the same site.
                } else {
                    // Use some id that can not exist in order to prevent invoice enrolment,
                    // because we do not know what cohort it is in this site.
                    $data->customint5 = -1;
                }
            }
            $instanceid = $this->add_instance($course, (array)$data);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }//restore_instance

    /**
     * Restore user enrolment.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $instance
     * @param int $oldinstancestatus
     * @param int $userid
     */
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
    }//restore_user_enrolment

    /**
     * Restore role assignment.
     *
     * @param stdClass $instance
     * @param int $roleid
     * @param int $userid
     * @param int $contextid
     */
    public function restore_role_assignment($instance, $roleid, $userid, $contextid) {
        // This is necessary only because we may migrate other types to this instance,
        // we do not use component in manual or invoice enrol.
        role_assign($roleid, $userid, $contextid, '', 0);
    }//restore_role_assignment

    public function unenrol_user(stdClass $instance, $userid) {
        global $DB;

        self::Unerol_UserInvoiceInfo($instance->id,$userid);
        parent::unenrol_user($instance,$userid);

    }

    public static function Unerol_UserInvoiceInfo($enrol_id,$user_id) {
        /* Variables    */
        global $DB;

        try {
            /* First Get user_enrol_id  */
            $rdo = $DB->get_record('user_enrolments',array('userid' => $user_id,'enrolid' => $enrol_id),'id');

            /* Search Criteria  */
            $params = array();
            $params['user_id']          = $user_id;
            $params['user_enrol_id']    = $rdo->id;
            $params['time']             = time();

            /* SQL Instruction  */
            $sql = " UPDATE   {enrol_invoice}
                        SET    unenrol      = 1,
                               timemodified = :time

                     WHERE    userid      = :user_id
                        AND   userenrolid = :user_enrol_id ";

            $DB->execute($sql,$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Unerol_UserInvoiceInfo



}//enrol_invoice_plugin