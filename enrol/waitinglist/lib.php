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
//define('ENROL_WAITINGLIST_FIELD_WAITLISTSIZE', 'customint3');
define('ENROL_WAITINGLIST_FIELD_WAITLISTSIZE', 'customint6');
define('ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE', 'customint4');
define('ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE', 'customint5');
define('ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE', 'customtext1');
define('ENROL_WAITINGLIST_FIELD_WAITLISTMESSAGE', 'customtext2');
define('ENROL_WAITINGLIST_TABLE_QUEUE', 'enrol_waitinglist_queue');
define('ENROL_WAITINGLIST_TABLE_METHODS', 'enrol_waitinglist_method');


class enrol_waitinglist_plugin extends enrol_plugin {

    protected $lasternoller = null;
    protected $lasternollerinstanceid = 0;

	
	public static function get_method_names(){
		//return array('self','unnamedbulk','namedbulk','selfconfirmation','paypal');
		return array('self','unnamedbulk');
	}

    public function roles_protected() {
        // Users may tweak the roles later.
        return false;
    }

	/*
	* We do not allow manual enrolments
	*/
    public function allow_enrol(stdClass $instance) {
        // Users with enrol cap may unenrol other users waitinglistly waitinglistly.
        return false;
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
     * Returns all the waiting list enrolment methods we have
     *
     * @return array array of waitlinglist enrolment methods
     */
	public static function get_methods($course, $waitinglistid = false){
		$methods=array();
		foreach(self::get_method_names() as $methodtype){
		 $class = '\enrol_waitinglist\method\\' . $methodtype. '\enrolmethod' .$methodtype ;
		   if (class_exists($class)){
				$themethod = $class::get_by_course($course->id, $waitinglistid); 
				if($themethod){$methods[$methodtype]=$themethod;}
		   }
		}
		return $methods;
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
            $waitinglistnode = $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        
        	//add manage links
        	//methods
        	$managelink=new moodle_url('/enrol/waitinglist/managemethods.php', array('id'=>$instance->courseid));
        	$waitinglistnode->add(get_string('managemethods','enrol_waitinglist'), $managelink, navigation_node::TYPE_SETTING);
        	//queue
        	$managelink=new moodle_url('/enrol/waitinglist/managequeue.php', array('id'=>$instance->courseid));
        	$waitinglistnode->add(get_string('managequeue','enrol_waitinglist'), $managelink, navigation_node::TYPE_SETTING);
 
        }
        //add bulk enrol links to menu if have permission
		 $context = context_course::instance($instance->courseid);
		if (has_capability('enrol/waitinglist:canbulkenrol', $context)) {
			$course = get_course($instance->courseid);
			$methods = $this->get_methods($course, $instance->id);
			$usersnode = $instancesnode->parent;
			foreach($methods as $method){
				if($method->can_enrol_from_course_admin() && $method->is_active()){
					$managelink=new moodle_url('/enrol/waitinglist/edit_enrolform.php', array('id'=>$instance->courseid, 'methodtype'=>$method->get_methodtype()));
					$usersnode->add(get_string($method->get_methodtype() . '_menutitle','enrol_waitinglist'), $managelink, navigation_node::TYPE_SETTING);
				}//end of if
			}//end of for each
		}//end of if has capability
    }//end of function

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
/*
        if (has_capability('enrol/waitinglist:enrol', $context) or has_capability('enrol/waitinglist:unenrol', $context)) {
            $managelink = new moodle_url("/enrol/waitinglist/manage.php", array('enrolid'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($managelink, new pix_icon('t/enrolusers', get_string('enrolusers', 'enrol_waitinglist'), 'core', array('class'=>'iconsmall')));
        }
 */
		if (has_capability('enrol/waitinglist:config', $context)) {
			//edit settings
            $editlink = new moodle_url("/enrol/waitinglist/edit.php", array('courseid'=>$instance->courseid));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit'), 'core',
                    array('class' => 'iconsmall')));
			//manage queue
			$queuelink = new moodle_url("/enrol/waitinglist/managequeue.php", array('id'=>$instance->courseid));
			$icons[] = $OUTPUT->action_icon($queuelink, new pix_icon('t/groupv', get_string('managequeue', 'enrol_waitinglist' ), 'core',
                    array('class' => 'iconsmall')));
			//manage subplugins
			$methodslink = new moodle_url("/enrol/waitinglist/managemethods.php", array('id'=>$instance->courseid));
			$icons[] = $OUTPUT->action_icon($methodslink, new pix_icon('i/mnethost', get_string('managemethods','enrol_waitinglist'), 'core',
                    array('class' => 'iconsmall')));
        }
		

        return $icons;
    }

	public function can_enrol_directly($instance){
		global $DB;
		//there probably wull be cases where we set the max enrolments to 0, to buffer until a particular start date
		if ($instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS} > -1) {
            // Max enrol limit specified.
            $count = $DB->count_records('user_enrolments', array('enrolid' => $instance->id));
			$queueman= \enrol_waitinglist\queuemanager::get_by_course($instance->courseid);
            if ($count < $instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS} && $queueman->get_listtotal() <1) {
                return true;
            }else{
				return false;
			}
        }
		return true;
	}
	
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
       $info_icons = array();
	   if(empty($instances)){return $info_icons;}
	   foreach($instances as $instance){
		$course = get_course($instance->courseid);
		break;
	   }
		$methods = $this->get_methods($course, $instance->id);
		if(!$methods){return array();}
		foreach($methods as $method){
			if($method->is_active()){
				$info_icons = array_merge($info_icons, $method->get_info_icons($instances));
			}
		}
		return $info_icons;
    }

	
	  /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        $course = get_course($instance->courseid);
		
		$methods = $this->get_methods($course, $instance->id);

		$hooks = array();
		foreach($methods as $method){
			if(!$method->is_active() ){continue;}
			if ($hook = $method->enrol_page_hook($instance)) {
				$hooks[]= $hook;
			}
		}
		if(empty($hooks)){
			return null;
		}else{
			return implode($hooks);
		}
    }
	
	
	/**
     * Do we show an enrol me link?
     *
     * @param array $instances all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function show_enrolme_link(stdClass $instance) {
        if($instance->status == ENROL_INSTANCE_ENABLED){
			return $this->can_self_enrol($instance);
		}
		return false;
    }
    
    
    /**
     * Enrol user into course via enrol instance.
     *
     * @param stdClass $instance
     * @param int $userid
     * @param int $roleid optional role id
     * @param int $timestart 0 means unknown
     * @param int $timeend 0 means forever
     * @param int $status default to ENROL_USER_ACTIVE for new enrolments, no change by default in updates
     * @param bool $recovergrades restore grade history
     * @return void
     */
    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
   		global $USER;
		
        $timestart = time();
        if ($instance->enrolperiod) {
            $timeend = $timestart + $instance->enrolperiod;
        } else {
            $timeend = 0;
        }
		
		$roleid = $instance->roleid;
   		
   		parent::enrol_user($instance,$userid,$roleid,$timestart,$timeend,$status,$recovergrades);
     	// Send welcome message.
        if ($instance->{ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE}) {
            $this->email_welcome_message($instance, $USER);
        }
    }
    
	
	    /**
     * Checks if user can self enrol.
     *
     * @param stdClass $instance enrolment instance
     * @param bool $checkuserenrolment if true will check if user enrolment is inactive.
     *             used by navigation to improve performance.
     * @return bool|string true if successful, else error message or false
     */
    public function can_self_enrol(stdClass $instance, $checkuserenrolment = true) {
       $course = get_course($instance->courseid);
		$methods = $this->get_methods($course,$instance->id);
		foreach($methods as $method){
			if ((true === $method->can_self_enrol($instance, false)) && $method->is_active()) {
				return true;
			}
		}
        return false;
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
     * Called when user is about to be deleted
     * @param object $user
     * @return void
     */
    public function user_delete($user) {
        global $DB;

        $sql = "SELECT e.*
                  FROM {enrol} e
                  JOIN {user_enrolments} ue ON (ue.enrolid = e.id)
                 WHERE e.enrol = :name AND ue.userid = :userid";
        $params = array('name'=>$this->get_name(), 'userid'=>$user->id);

        $rs = $DB->get_recordset_sql($sql, $params);
        foreach($rs as $instance) {
            $this->unenrol_user($instance, $user->id);
        }
        $rs->close();
		//remove from waiting list
		$DB->delete_records(ENROL_WAITINGLIST_TABLE_QUEUE,array('userid'=>$user->id));
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
             ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE=> $this->get_config('sendcoursewelcomemessage'),
             ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE=> $this->get_config('sendcoursewaitlistmessage'),
             ENROL_WAITINGLIST_FIELD_MAXENROLMENTS> $this->get_config('maxenrolments'),
             ENROL_WAITINGLIST_FIELD_WAITLISTSIZE=> $this->get_config('waitlistsize'),
            'expirythreshold' => $this->get_config('expirythreshold', 86400),
        );
        $waitinglistid = $this->add_instance($course, $fields);

        //add an instance of each of the methods, if the waitinglist instance was created ok
        if($waitinglistid){
			$methods=array();
			foreach(self::get_method_names() as $methodtype){
			 $class = '\enrol_waitinglist\method\\' . $methodtype. '\enrolmethod' .$methodtype ;
			   if (class_exists($class)){
					$class::add_default_instance( $waitinglistid,$course->id); 
			   }
			}
		}
		return $waitinglistid;  
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
     * check if there are spaces on the course and enrol if we can
     * @return void
     */
	public function check_and_enrol(progress_trace $trace){
		global $DB;
		$trace->output('waitinglist enrolment check for vacant seats');
		$instances = $DB->get_records('enrol', array( 'enrol'=>'waitinglist'));
		if (!$instances){
			$trace->output('No waitinglist enrolment instances, bye bye');
			 $trace->finished();
			return;
		}
		$wl = enrol_get_plugin('waitinglist');
		
		foreach($instances as $instance){
			$course = get_course($instance->courseid);
			$methods = $this->get_methods($course, $instance->id);
			$queueman= \enrol_waitinglist\queuemanager::get_by_course($instance->courseid);
			$count = $DB->count_records('user_enrolments', array('enrolid' => $instance->id));
			$trace->output('waitinglist current enrolments: ' . $count);		
			if($count<$instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS} AND $queueman->get_listtotal() > 0){
				$allocatedseats=0;
				$availableseats=$instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS}-$count;
				$trace->output('waitinglist enrolment availabilities: ' . $availableseats);
				
				$qentries = $queueman->get_qentries();
				foreach($qentries as $qentry){
					if($qentry->seats > $availableseats - $allocatedseats){
						$giveseats = $availableseats - $allocatedseats;
					}else{
						$giveseats = $qentry->seats;
					}
					$remove = $methods[$qentry->methodtype]->graduate_from_list($instance,$qentry,$giveseats);
					if($remove){$queueman->remove_entry($qentry->id);}
					$allocatedseats+=$giveseats;
					if(!($availableseats>$allocatedseats)){break;}
				}//end of for each
				$trace->output('waitinglist enrolment allocated seats:' . $allocatedseats);
			}else{
				$trace->output('.... no eligible entries on waiting list');
			}//end of if count
		}//end of for each instances
		 $trace->finished();
	}//end of check and enrol
	


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

        // Note: manual enrolment is a bit tricky because other types may be converted to waitinglist enrolments,
        //       and waitinglist is restricted to one enrolment per user. Waitinglist is based in manual, so 
		//		this could be simplified

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

        if (trim($instance->{ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE}) !== '') {
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
            $messagetext = get_string('welcometocoursetext', 'enrol_waitinglist', $a);
            $messagehtml = text_to_html($messagetext, null, false, true);
        }

        $subject = get_string('welcometocourse', 'enrol_waitinglist', format_string($course->fullname, true, array('context'=>$context)));

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
    }


    /**
     * Restore role assignment. 
     *
     * @param stdClass $instance
     * @param int $roleid
     * @param int $userid
     * @param int $contextid
     */
	 /* Probably not necessary for waitinglist enrolments */
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
	  /* Probably not necessary for waitinglist enrolments */
    public function restore_group_member($instance, $groupid, $userid) {
        global $CFG;
        require_once("$CFG->dirroot/group/lib.php");

        // This might be called when forcing restore as waitinglist enrolments.

        groups_add_member($groupid, $userid);
    }
}