<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////


/**
 * Waiting List Enrol Method Self enrolment Plugin
 *
 * @package    enrol_waitinglist
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 onwards Justin Hunt  http://poodll.com
 */
 
namespace enrol_waitinglist\method\self;

require_once($CFG->dirroot . '/enrol/waitinglist/lib.php');

class enrolmethodself extends \enrol_waitinglist\method\enrolmethodbase{

	const METHODTYPE='self';
	protected $active = false;
	
	const QFIELD_ENROLPASSWORD='customtext1';
	const MFIELD_GROUPKEY = 'customint1';
	const MFIELD_LONGTIMENOSEE = 'customint2';
	const MFIELD_MAXENROLLED = 'customint3';
	const MFIELD_SENDWAITLISTMESSAGE = 'customint4';
	const MFIELD_COHORTONLY = 'customint5';
	const MFIELD_NEWENROLS = 'customint6';
	const MFIELD_WAITLISTMESSAGE = 'customtext1';
	
	public $course = 0;
    public $waitlist = 0;
	public $maxseats = 0;
    public $activeseats = 0;
	public $notificationtypes = 0;
	

	 /**
     *  Constructor
     */
    public function __construct()
    {

    }
	 
	  /**
     * Add new instance of method with default settings.
     * @param stdClass $course
     * @return int id of new instance, null if can not be created
     */
    public static function add_default_instance($courseid,$waitinglistid) {
    	global $DB;
        	$rec = new \stdClass();
			$rec->courseid = $courseid;
			$rec->waitinglistid = $waitinglistid;
			$rec->methodtype = static::METHODTYPE;
			$rec->status = true;
			$rec->emailalert=true;
			$rec->{enrolmethodself::MFIELD_WAITLISTMESSAGE}=get_string('waitlistmessagetext_self','enrol_waitinglist');
			$id = $DB->insert_record(self::TABLE,$rec);
			if($id){
				$rec->id = $id;
				return $rec;
			}else{
				return $id;
			}
    }

	 
	 
	 //settings related functions
	 public function has_notifications(){return false;}
	 public  function show_notifications_settings_link(){return false;}
	 public  function has_settings(){return true;}

	 
	 public function get_dummy_form_plugin(){
		return enrol_get_plugin('self');
	 }
	
	/**
     * Returns optional enrolment information icons.
     *
     * This is used in course list for quick overview of enrolment options.
     *
     * We are not using single waitinglist parameter because sometimes
     * we might want to prevent icon repetition when multiple instances
     * of one type exist. One waitinglist may also produce several icons.
     *
     * @param array $waitinglists all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons(array $waitinglists) {
        $key = false;
        $nokey = false;
        foreach ($waitinglists as $waitinglist) {

            
			if ($this->can_self_enrol($waitinglist) !== true) {
                // User can not enrol himself.
                // Note that we do not check here if user is already enrolled for performance reasons -
                // such check would execute extra queries for each course in the list of courses and
                // would hide self-enrolment icons from guests.
                continue;
            }
            if ($this->password or $this->{self::MFIELD_GROUPKEY}) {
                $key = true;
            } else {
                $nokey = true;
            }
        }
        $icons = array();
        if ($nokey) {
            $icons[] = new \pix_icon('withoutkey', get_string('pluginname', 'enrol_self'), 'enrol_self');
        }
        if ($key) {
            $icons[] = new \pix_icon('withkey', get_string('pluginname', 'enrol_self'), 'enrol_self');
        }
        return $icons;
    }
	
	 /**
     * Checks if user can self enrol.
     * used for displaying icons and links on course list page
     **
     */
	public  function can_self_enrol(\stdClass $waitinglist){
		//this will be called from course page
		//we don't check current enrolments from can_enrol
		//because we try to avoid lots of db calls
		if($this->can_enrol($waitinglist,false) === true){
			return true;
		}
		return false;
	
	}
	
	/**
     * Can we auto take a user from waitlist and put onto course?
     ** for self .... YES
     */
	public function can_enrol_directly(){
		return true;
	}
	
	
	  /**
     * Checks if user can enrol.
     *
     * @param stdClass $waitinglist enrolment instance
     * @param bool $checkuserenrolment if true will check db and queue for user
     * @return bool|string true if successful, else error message or false.
     */
    public function can_enrol(\stdClass $waitinglist, $checkuserenrolment = true) {
        global $DB, $USER, $CFG;


		//checking enroled in course, (db calls)
        if ($checkuserenrolment) {
            if (isguestuser()) {
                // Can not enrol guest.
                return get_string('noguestaccess', 'enrol');
            }
            // Check if user is already enroled.
            if ($DB->get_record('user_enrolments', array('userid' => $USER->id, 'enrolid' => $waitinglist->id))) {
                return get_string('canntenrol', 'enrol_self');
            }
        }
        
        //checking the queue (db calls)
        //to do: turn queuemanager into a singleton, and remove the checkusenrolment condition
         if ($checkuserenrolment) {
         	$queueman =  \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
            if($queueman->is_on_list($USER->id,static::METHODTYPE)){
				return get_string('alreadyonlist', 'enrol_waitinglist');
        	}
		
			//maximum users for this enrolment method
        	if ($this->{self::MFIELD_MAXENROLLED} > 0) {
				// Max enrol limit specified.
				//$count = $this->count_users_on_list();
				$count = $queueman->get_listtotal_by_method(static::METHODTYPE);
				if ($count >= $this->{self::MFIELD_MAXENROLLED}) {
					// Bad luck, no more self enrolments here.
					return get_string('noroomonlist', 'enrol_waitinglist');
				}
        	}
		
			//is waiting list is full
			if ($queueman->is_full()){
					return  get_string('noroomonlist', 'enrol_waitinglist');
			}
        }
        
        //checking cohort status (db calls)
        if($checkuserenrolment){
			if ($this->{self::MFIELD_COHORTONLY}) {
				require_once("$CFG->dirroot/cohort/lib.php");
				if (!cohort_is_member($this->{self::MFIELD_COHORTONLY}, $USER->id)) {
					$cohort = $DB->get_record('cohort', array('id' => $this->{self::MFIELD_COHORTONLY}));
					if (!$cohort) {
						return null;
					}
					$a = format_string($cohort->name, true, array('context' => \context::instance_by_id($cohort->contextid)));
					return markdown_to_html(get_string('cohortnonmemberinfo', 'enrol_self', $a));
				}
			}
        }

		//basic waitinglist and plugin checks (no db calls)
        if (!$this->is_active()) {
            return get_string('canntenrol', 'enrol_self');
        }
        if ($waitinglist->enrolstartdate != 0 and $waitinglist->enrolstartdate > time()) {
			return get_string('canntenrol', 'enrol_self');
        }
        if ($waitinglist->enrolenddate != 0 and $waitinglist->enrolenddate < time()) {
			return get_string('canntenrol', 'enrol_self');
        }
/*
        if (!$this->{self::MFIELD_NEWENROLS}) {
            // New enrols not allowed.
			return get_string('canntenrol', 'enrol_self');
        }
*/

        return true;
    }
	
	   /**
     * Self enrol user to course
     *
     * @param stdClass $waitinglist enrolment instance
     * @param stdClass $data data needed for enrolment.
     * @return bool|array true if enroled else eddor code and messege
     */
    public function enrol_self(\stdClass $waitinglist, $data = null) {
        global $DB, $USER, $CFG;
		
		
        // Don't enrol user if password is not passed when required.
        if ($this->password && !isset($data->enrolpassword)) {
            return;
        }


		//prepare additional fields for our queue DB entry
		//we need at least one, so we set an empty string for password if necessary
		$queue_entry = new \stdClass;
		$queue_entry->waitinglistid      = $waitinglist->id;
		$queue_entry->courseid       = $waitinglist->courseid;
		$queue_entry->userid       = $USER->id;
		$queue_entry->methodtype   = static::METHODTYPE;
		if(!isset($data->enrolpassword)){$data->enrolpassword='';}
		$queue_entry->{self::QFIELD_ENROLPASSWORD}=$data->enrolpassword;
		$queue_entry->timecreated  = time();
		$queue_entry->queueno = 	0;
		$queue_entry->seats = 1;
		$queue_entry->allocseats = 0;
		$queue_entry->timemodified = $queue_entry->timecreated;
		
		//add the user to the waitinglist queue 
        $queueid = $this->add_to_waitinglist($waitinglist, $queue_entry);

    }
	
	/**
     * Pop off queue and enrol in course
     *
     * @param stdClass $waitinglist
	 * @param stdClass $queueentry
     * @return null 
     */
	public function graduate_from_list(\stdClass $waitinglist,\stdClass $queue_entry, $seats){
		$queueman= \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		$wl = enrol_get_plugin('waitinglist');
		$wl->enrol_user($waitinglist,$queue_entry->userid);
		$this->do_post_enrol_actions($waitinglist, $queue_entry);
		$removeqitem=true;
		return $removeqitem;
	}
	
	/**
     * After enroling into course and removeing from waiting list. Return here to do any post processing 
     *
     * @param stdClass $waitinglist
	 * @param stdClass $queueentry
     * @return null 
     */
	public function do_post_enrol_actions(\stdClass $waitinglist,\stdClass $queueentry){
		global $DB,$CFG;
		 if ($this->password and $this->{self::MFIELD_GROUPKEY} and $queueentry->{QFIELD_ENROLPASSWORD} !== $this->password) {
            // It must be a group enrolment, let's assign group too.
            $groups = $DB->get_records('groups', array('courseid'=>$waitinglist->courseid), 'id', 'id, enrolmentkey');
            foreach ($groups as $group) {
                if (empty($group->enrolmentkey)) {
                    continue;
                }
                if ($group->enrolmentkey ===  $queueentry->{QFIELD_ENROLPASSWORD} ) {
                    // Add user to group.
                    require_once($CFG->dirroot.'/group/lib.php');
                    groups_add_member($group->id, $USER->id);
                    break;
                }
            }
        }
	
	}

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $waitinglist
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(\stdClass $waitinglist) {
        global $CFG, $OUTPUT, $USER;
		
		$queueman= \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		$qdetails = $queueman->get_user_queue_details(static::METHODTYPE);
		if($qdetails->queueposition > 0){
			$enrolstatus = get_string('yourqueuedetails','enrol_waitinglist', $qdetails);
		}else{				
			$enrolstatus = $this->can_enrol($waitinglist,true);
		}

        // Don't show enrolment instance form, if user can't enrol using it.
        if (true === $enrolstatus) {
        	$listtotal = $queueman->get_listtotal();
            $form = new enrolmethodself_enrolform(NULL, array($waitinglist,$this,$listtotal));
            $waitinglistid = optional_param('waitinglist', 0, PARAM_INT);
            if ($waitinglist->id == $waitinglistid) {
                if ($data = $form->get_data()) {
                    $this->enrol_self($waitinglist, $data);
                    redirect($CFG->wwwroot . '/course/view.php?id=' . $waitinglist->courseid);
                }
            }

            ob_start();
            $form->display();
            $output = ob_get_clean();
            return $OUTPUT->box($output);
        } else {
            return $OUTPUT->box($enrolstatus);
        }
    }
}