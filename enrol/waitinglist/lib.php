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
 * Waitinglist enrolment plugin main library file.
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('ENROL_WAITINGLIST_FIELD_CUTOFFDATE', 'customint1');
define('ENROL_WAITINGLIST_FIELD_MAXENROLMENTS', 'customint2');
define('ENROL_WAITINGLIST_FIELD_WAITLISTSIZE', 'customint3');

class enrol_waitinglist_plugin extends enrol_plugin {

    protected $lasternoller = null;
    protected $lasternollerinstanceid = 0;

    public function roles_protected() {
        // Users may tweak the roles later.
        return false;
    }

    public function allow_enrol(stdClass $instance) {
        // Users with enrol cap may unenrol other users waitinglistly waitinglistly.
        return true;
    }

    public function allow_unenrol(stdClass $instance) {
        // Users with unenrol cap may unenrol other users waitinglistly waitinglistly.
        return true;
    }

    public function allow_manage(stdClass $instance) {
        // Users with manage cap may tweak period and status.
        return true;
    }

    /**
     * Returns link to waitinglist enrol UI if exists.
     * Does the access control tests automatically.
     *
     * @param stdClass $instance
     * @return moodle_url
     */
    public function get_waitinglist_enrol_link($instance) {
        $name = $this->get_name();
        if ($instance->enrol !== $name) {
            throw new coding_exception('invalid enrol instance!');
        }

        if (!enrol_is_enabled($name)) {
            return NULL;
        }

        $context = context_course::instance($instance->courseid, MUST_EXIST);

        if (!has_capability('enrol/waitinglist:enrol', $context)) {
            // Note: manage capability not used here because it is used for editing
            // of existing enrolments which is not possible here.
            return NULL;
        }

        return new moodle_url('/enrol/waitinglist/manage.php', array('enrolid'=>$instance->id, 'id'=>$instance->courseid));
    }

    /**
     * Returns enrolment instance manage link.
     *
     * By defaults looks for manage.php file and tests for manage capability.
     *
     * @param navigation_node $instancesnode
     * @param stdClass $instance
     * @return moodle_url;
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'waitinglist') {
             throw new coding_exception('Invalid enrol instance type!');
        }

        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/waitinglist:config', $context)) {
            $managelink = new moodle_url('/enrol/waitinglist/edit.php', array('courseid'=>$instance->courseid));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

    /**
     * Returns edit icons for the page with list of instances.
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'waitinglist') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();

        if (has_capability('enrol/waitinglist:enrol', $context) or has_capability('enrol/waitinglist:unenrol', $context)) {
            $managelink = new moodle_url("/enrol/waitinglist/manage.php", array('enrolid'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($managelink, new pix_icon('t/enrolusers', get_string('enrolusers', 'enrol_waitinglist'), 'core', array('class'=>'iconsmall')));
        }
        if (has_capability('enrol/waitinglist:config', $context)) {
            $editlink = new moodle_url("/enrol/waitinglist/edit.php", array('courseid'=>$instance->courseid));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit'), 'core',
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
        global $DB;

        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/waitinglist:config', $context)) {
            return NULL;
        }

        if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'waitinglist'))) {
            return NULL;
        }

        return new moodle_url('/enrol/waitinglist/edit.php', array('courseid'=>$courseid));
    }

    /**
     * Add new instance of enrol plugin with default settings.
     * @param stdClass $course
     * @return int id of new instance, null if can not be created
     */
    public function add_default_instance($course) {
        $expirynotify = $this->get_config('expirynotify', 0);
        if ($expirynotify == 2) {
            $expirynotify = 1;
            $notifyall = 1;
        } else {
            $notifyall = 0;
        }
        $fields = array(
            'status'          => $this->get_config('status'),
            'roleid'          => $this->get_config('roleid', 0),
            'enrolperiod'     => $this->get_config('enrolperiod', 0),
            'expirynotify'    => $expirynotify,
            'notifyall'       => $notifyall,
            'expirythreshold' => $this->get_config('expirythreshold', 86400),
        );
        return $this->add_instance($course, $fields);
    }

    /**
     * Add new instance of enrol plugin.
     * @param stdClass $course
     * @param array instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = NULL) {
        global $DB;

        if ($DB->record_exists('enrol', array('courseid'=>$course->id, 'enrol'=>'waitinglist'))) {
            // only one instance allowed, sorry
            return NULL;
        }

        return parent::add_instance($course, $fields);
    }

    /**
     * Returns a button to waitinglistly enrol users through the waitinglist enrolment plugin.
     *
     * By default the first waitinglist enrolment plugin instance available in the course is used.
     * If no waitinglist enrolment instances exist within the course then false is returned.
     *
     * This function also adds a quickenrolment JS ui to the page so that users can be enrolled
     * via AJAX.
     *
     * @param course_enrolment_manager $manager
     * @return enrol_user_button
     */
    public function get_waitinglist_enrol_button(course_enrolment_manager $manager) {
        global $CFG;

        $instance = null;
        $instances = array();
        foreach ($manager->get_enrolment_instances() as $tempinstance) {
            if ($tempinstance->enrol == 'waitinglist') {
                if ($instance === null) {
                    $instance = $tempinstance;
                }
                $instances[] = array('id' => $tempinstance->id, 'name' => $this->get_instance_name($tempinstance));
            }
        }
        if (empty($instance)) {
            return false;
        }

        if (!$waitinglistlink = $this->get_waitinglist_enrol_link($instance)) {
            return false;
        }

        $button = new enrol_user_button($waitinglistlink, get_string('enrolusers', 'enrol_waitinglist'), 'get');
        $button->class .= ' enrol_waitinglist_plugin';

        $startdate = $manager->get_course()->startdate;
        $startdateoptions = array();
        $timeformat = get_string('strftimedatefullshort');
        if ($startdate > 0) {
            $startdateoptions[2] = get_string('coursestart') . ' (' . userdate($startdate, $timeformat) . ')';
        }
        $today = time();
        $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
        $startdateoptions[3] = get_string('today') . ' (' . userdate($today, $timeformat) . ')' ;
        $defaultduration = $instance->enrolperiod > 0 ? $instance->enrolperiod / 86400 : '';

        $modules = array('moodle-enrol_waitinglist-quickenrolment', 'moodle-enrol_waitinglist-quickenrolment-skin');
        $arguments = array(
            'instances'           => $instances,
            'courseid'            => $instance->courseid,
            'ajaxurl'             => '/enrol/waitinglist/ajax.php',
            'url'                 => $manager->get_moodlepage()->url->out(false),
            'optionsStartDate'    => $startdateoptions,
            'defaultRole'         => $instance->roleid,
            'defaultDuration'     => $defaultduration,
            'disableGradeHistory' => $CFG->disablegradehistory,
            'recoverGradesDefault'=> ''
        );

        if ($CFG->recovergradesdefault) {
            $arguments['recoverGradesDefault'] = ' checked="checked"';
        }

        $function = 'M.enrol_waitinglist.quickenrolment.init';
        $button->require_yui_module($modules, $function, array($arguments));
        $button->strings_for_js(array(
            'ajaxoneuserfound',
            'ajaxxusersfound',
            'ajaxnext25',
            'enrol',
            'enrolmentoptions',
            'enrolusers',
            'errajaxfailedenrol',
            'errajaxsearch',
            'none',
            'usersearch',
            'unlimitedduration',
            'startdatetoday',
            'durationdays',
            'enrolperiod',
            'finishenrollingusers',
            'recovergrades'), 'enrol');
        $button->strings_for_js('assignroles', 'role');
        $button->strings_for_js('startingfrom', 'moodle');

        return $button;
    }

    /**
     * Enrol cron support.
     * @return void
     */
    public function cron() {
        $trace = new text_progress_trace();
        $this->sync($trace, null);
        $this->send_expiry_notifications($trace);
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

        if (!enrol_is_enabled('waitinglist')) {
            $trace->finished();
            return 2;
        }

        // Unfortunately this may take a long time, execution can be interrupted safely here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $trace->output('Verifying waitinglist enrolment expiration...');

        $params = array('now'=>time(), 'useractive'=>ENROL_USER_ACTIVE, 'courselevel'=>CONTEXT_COURSE);
        $coursesql = "";
        if ($courseid) {
            $coursesql = "AND e.courseid = :courseid";
            $params['courseid'] = $courseid;
        }

        // Deal with expired accounts.
        $action = $this->get_config('expiredaction', ENROL_EXT_REMOVED_KEEP);

        if ($action == ENROL_EXT_REMOVED_UNENROL) {
            $instances = array();
            $sql = "SELECT ue.*, e.courseid, c.id AS contextid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'waitinglist')
                      JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :courselevel)
                     WHERE ue.timeend > 0 AND ue.timeend < :now
                           $coursesql";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                if (empty($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];
                // Always remove all waitinglistly assigned roles here, this may break enrol_self roles but we do not want hardcoded hacks here.
                role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'', 'itemid'=>0), true);
                $this->unenrol_user($instance, $ue->userid);
                $trace->output("unenrolling expired user $ue->userid from course $instance->courseid", 1);
            }
            $rs->close();
            unset($instances);

        } else if ($action == ENROL_EXT_REMOVED_SUSPENDNOROLES or $action == ENROL_EXT_REMOVED_SUSPEND) {
            $instances = array();
            $sql = "SELECT ue.*, e.courseid, c.id AS contextid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'waitinglist')
                      JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :courselevel)
                     WHERE ue.timeend > 0 AND ue.timeend < :now
                           AND ue.status = :useractive
                           $coursesql";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                if (empty($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];
                if ($action == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                    // Remove all waitinglistly assigned roles here, this may break enrol_self roles but we do not want hardcoded hacks here.
                    role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'', 'itemid'=>0), true);
                    $this->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                    $trace->output("suspending expired user $ue->userid in course $instance->courseid, roles unassigned", 1);
                } else {
                    $this->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                    $trace->output("suspending expired user $ue->userid in course $instance->courseid, roles kept", 1);
                }
            }
            $rs->close();
            unset($instances);

        } else {
            // ENROL_EXT_REMOVED_KEEP means no changes.
        }

        $trace->output('...waitinglist enrolment updates finished.');
        $trace->finished();

        return 0;
    }

    /**
     * Returns the user who is responsible for waitinglist enrolments in given instance.
     *
     * Usually it is the first editing teacher - the person with "highest authority"
     * as defined by sort_by_roleassignment_authority() having 'enrol/waitinglist:manage'
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

        if ($users = get_enrolled_users($context, 'enrol/waitinglist:manage')) {
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
        if ($this->allow_unenrol_user($instance, $ue) && has_capability("enrol/waitinglist:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/waitinglist:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class'=>'editenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }

    /**
     * The waitinglist plugin has several bulk operations that can be performed.
     * @param course_enrolment_manager $manager
     * @return array
     */
    public function get_bulk_operations(course_enrolment_manager $manager) {
        global $CFG;
        require_once($CFG->dirroot.'/enrol/waitinglist/locallib.php');
        $context = $manager->get_context();
        $bulkoperations = array();
        if (has_capability("enrol/waitinglist:manage", $context)) {
            $bulkoperations['editselectedusers'] = new enrol_waitinglist_editselectedusers_operation($manager, $this);
        }
        if (has_capability("enrol/waitinglist:unenrol", $context)) {
            $bulkoperations['deleteselectedusers'] = new enrol_waitinglist_deleteselectedusers_operation($manager, $this);
        }
        return $bulkoperations;
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
        // There is only I waitinglist enrol instance allowed per course.
        if ($instances = $DB->get_records('enrol', array('courseid'=>$data->courseid, 'enrol'=>'waitinglist'), 'id')) {
            $instance = reset($instances);
            $instanceid = $instance->id;
        } else {
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
        global $DB;

        // Note: this is a bit tricky because other types may be converted to waitinglist enrolments,
        //       and waitinglist is restricted to one enrolment per user.

        $ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid));
        $enrol = false;
        if ($ue and $ue->status == ENROL_USER_ACTIVE) {
            // We do not want to restrict current active enrolments, let's kind of merge the times only.
            // This prevents some teacher lockouts too.
            if ($data->status == ENROL_USER_ACTIVE) {
                if ($data->timestart > $ue->timestart) {
                    $data->timestart = $ue->timestart;
                    $enrol = true;
                }

                if ($data->timeend == 0) {
                    if ($ue->timeend != 0) {
                        $enrol = true;
                    }
                } else if ($ue->timeend == 0) {
                    $data->timeend = 0;
                } else if ($data->timeend < $ue->timeend) {
                    $data->timeend = $ue->timeend;
                    $enrol = true;
                }
            }
        } else {
            if ($instance->status == ENROL_INSTANCE_ENABLED and $oldinstancestatus != ENROL_INSTANCE_ENABLED) {
                // Make sure that user enrolments are not activated accidentally,
                // we do it only here because it is not expected that enrolments are migrated to other plugins.
                $data->status = ENROL_USER_SUSPENDED;
            }
            $enrol = true;
        }

        if ($enrol) {
            $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
        }
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
        // we do not use component in waitinglist or self enrol.
        role_assign($roleid, $userid, $contextid, '', 0);
    }

    /**
     * Restore user group membership.
     * @param stdClass $instance
     * @param int $groupid
     * @param int $userid
     */
    public function restore_group_member($instance, $groupid, $userid) {
        global $CFG;
        require_once("$CFG->dirroot/group/lib.php");

        // This might be called when forcing restore as waitinglist enrolments.

        groups_add_member($groupid, $userid);
    }
}
