<?php

/**
 * Single Sign On Enrolment Plugin - Library Plugin Implementation
 *
 * @package         enrol
 * @subpackage      wsdoskom
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    26/02/2015
 * @author          efaktor     (fbv)
 */

defined('MOODLE_INTERNAL') || die();

class enrol_wsdoskom_plugin extends enrol_plugin {
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
            $icons[] = new pix_icon('withoutkey', get_string('pluginname', 'enrol_wsdoskom'), 'enrol_wsdoskom');
        }
        if ($key) {
            $icons[] = new pix_icon('withkey', get_string('pluginname', 'enrol_wsdoskom'), 'enrol_wsdoskom');
        }
        return $icons;
    }

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
    }

    public function roles_protected() {
        // Users may tweak the roles later.
        return false;
    }

    public function allow_unenrol(stdClass $instance) {
        // Users with unenrol cap may unenrol other users manually manually.
        return true;
    }

    public function allow_manage(stdClass $instance) {
        // Users with manage cap may tweak period and status.
        return true;
    }

    public function show_enrolme_link(stdClass $instance) {

        if (true !== $this->can_self_enrol($instance, false)) {
            return false;
        }

        return true;
    }

    /**
     * Sets up navigation entries.
     *
     * @param stdClass $instancesnode
     * @param stdClass $instance
     * @return void
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'wsdoskom') {
            throw new coding_exception('Invalid enrol instance type!');
        }

        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/wsdoskom:config', $context)) {
            $managelink = new moodle_url('/enrol/wsdoskom/edit.php', array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'wsdoskom') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();

        if (has_capability('enrol/wsdoskom:config', $context)) {
            $editlink = new moodle_url("/enrol/wsdoskom/edit.php", array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($editlink,
                                            new pix_icon('t/edit', get_string('edit'), 'core',
                array('class' => 'iconsmall')));
        }
        return $icons;
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/wsdoskom:config', $context)) {
            return NULL;
        }
        // Multiple instances supported - different roles with different password.
        return new moodle_url('/enrol/wsdoskom/edit.php', array('courseid'=>$courseid));
    }

    /**
     * Self enrol user to course
     *
     * @param stdClass $instance enrolment instance
     * @param stdClass $data data needed for enrolment.
     * @return bool|array true if enroled else eddor code and messege
     */
    public function enrol_wsdoskom(stdClass $instance, $data = null) {
        global $DB, $USER, $CFG;


        $timestart = time();
        if ($instance->enrolperiod) {
            $timeend = $timestart + $instance->enrolperiod;
        } else {
            $timeend = 0;
        }

        $this->enrol_user($instance, $USER->id, $instance->roleid, $timestart, $timeend);

    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        global $CFG, $OUTPUT, $USER;

    }

    /**
     * Checks if user can self enrol.
     *
     * @param stdClass $instance enrolment instance
     * @param bool $checkuserenrolment if true will check if user enrolment is inactive.
     *             used by navigation to improve performance.
     * @return bool|string true if successful, else error message or false.
     */
    public function can_wsdoskom_enrol(stdClass $instance, $checkuserenrolment = true) {
        global $DB, $USER, $CFG;

        return false;
    }

    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/wsdoskom:config', $context);
    }


    /**
     * Return information for enrolment instance containing list of parameters required
     * for enrolment, name of enrolment plugin etc.
     *
     * @param stdClass $instance enrolment instance
     * @return stdClass instance info.
     */
    public function get_enrol_info(stdClass $instance) {

        $instanceinfo = new stdClass();
        $instanceinfo->id = $instance->id;
        $instanceinfo->courseid = $instance->courseid;
        $instanceinfo->type = $this->get_name();
        $instanceinfo->name = $this->get_instance_name($instance);
        $instanceinfo->status = false;//$this->can_wsdoskom_enrol($instance);

        return $instanceinfo;
    }

    /**
     * Add new instance of enrol plugin with default settings.
     * @param stdClass $course
     * @return int id of new instance
     */
    public function add_default_instance($course) {
        $fields = $this->get_instance_defaults();

        return $this->add_instance($course, $fields);
    }

    /**
     * Returns defaults for new instances.
     * @return array
     */
    public function get_instance_defaults() {
        $expirynotify = $this->get_config('expirynotify');
        if ($expirynotify == 2) {
            $expirynotify = 1;
            $notifyall = 1;
        } else {
            $notifyall = 0;
        }

        $fields = array();
        $fields['status']          = 0;
        $fields['roleid']          = $this->get_config('roleid');
        $fields['enrolperiod']     = $this->get_config('enrolperiod');

        return $fields;
    }

    /**
     * Enrol self cron support.
     * @return void
     */
    public function cron() {
        $trace = new text_progress_trace();
        $this->sync($trace, null);
        //$this->send_expiry_notifications($trace);
    }

    /**
     * Sync all meta course links.
     *
     * @param progress_trace $trace
     * @param int $courseid one course, empty mean all
     * @return int 0 means ok, 1 means error, 2 means plugin disabled
     */
    public function sync(progress_trace $trace, $courseid = null) {
        global $DB;

        if (!enrol_is_enabled('wsdoskom')) {
            $trace->finished();
            return 2;
        }

        // Unfortunately this may take a long time, execution can be interrupted safely here.
        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);

        $trace->output('Verifying wsdoskom-enrolments...');

        $params = array('now'=>time(), 'useractive'=>ENROL_USER_ACTIVE, 'courselevel'=>CONTEXT_COURSE);
        $coursesql = "";
        if ($courseid) {
            $coursesql = "AND e.courseid = :courseid";
            $params['courseid'] = $courseid;
        }

        // Note: the logic of self enrolment guarantees that user logged in at least once (=== u.lastaccess set)
        //       and that user accessed course at least once too (=== user_lastaccess record exists).

        // First deal with users that did not log in for a really long time - they do not have user_lastaccess records.
        $sql = "SELECT e.*, ue.userid
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'wsdoskom' AND e.customint2 > 0)
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
                  JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'wsdoskom' AND e.customint2 > 0)
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

        $trace->output('...user wsdoskom-enrolment updates finished.');
        $trace->finished();

        $this->process_expirations($trace, $courseid);

        return 0;
    }

    /**
     * Returns the user who is responsible for self enrolments in given instance.
     *
     * Usually it is the first editing teacher - the person with "highest authority"
     * as defined by sort_by_roleassignment_authority() having 'enrol/self:manage'
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

        if ($users = get_enrolled_users($context, 'enrol/wsdoskom:manage')) {
            $users = sort_by_roleassignment_authority($users, $context);
            $this->lasternoller = reset($users);
            unset($users);
        } else {
            $this->lasternoller = parent::get_enroller($instanceid);
        }

        $this->lasternollerinstanceid = $instanceid;

        return $this->lasternoller;
    }

    /**
     * Gets an array of the user enrolment actions.
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;

        if ($this->allow_manage($instance) && has_capability("enrol/wsdoskom:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class'=>'editenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }

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
                    // Use some id that can not exist in order to prevent self enrolment,
                    // because we do not know what cohort it is in this site.
                    $data->customint5 = -1;
                }
            }
            $instanceid = $this->add_instance($course, (array)$data);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }

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
    }

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
        // we do not use component in manual or self enrol.
        role_assign($roleid, $userid, $contextid, '', 0);
    }


}//enrol_wsdoskom_plugin