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

namespace enrol_waitinglist\method;

require_once($CFG->dirroot . '/enrol/waitinglist/lib.php');

/**
 * Waiting List Enrol Method Base Plugin
 *
 * @package    enrol_waitinglist
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 onwards Justin Hunt  http://poodll.com
 */

abstract class enrolmethodbase  {

	const METHODTYPE='base';
	const TABLE='enrol_waitinglist_method';
	const DATATABLE='enrol_waitinglist_methoddata';
	
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
      $this->_cache = array();
    }
	
	 /**
     *  Construct instance from DB record
     */
	 public static function get_by_record($record){
		$wlm = new static();
		foreach(get_object_vars($record) as $propname=>$propvalue){
			$wlm->{$propname}=$propvalue;
		}
		return $wlm;
	}
	
	public static function get_display_name(){
		return get_string(static::METHODTYPE . '_displayname', 'enrol_waitinglist');
	}
	
	public static function can_enrol_from_course_admin(){
		return false;
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
	
	
	public function get_methodtype(){
		return static::METHODTYPE;
	}
	
	 /**
     *  Construct instance from courseid
     */
	  public static function get_by_course($courseid,$waitinglistid=false){
		global $DB;
		$strictness = IGNORE_MULTIPLE;	
		$record = $DB->get_record_sql("SELECT * FROM {".self::TABLE."} WHERE courseid = $courseid AND " .$DB->sql_compare_text('methodtype') . "='". static::METHODTYPE ."'", null, $strictness);		
        if(!$record){
        	if(!$waitinglistid){
        		$waitinglist = $DB->get_record('enrol',array('courseid'=>$courseid,'enrol'=>'waitinglist'));
        		if(!$waitinglist){return null;}
        		$record = static::add_default_instance($courseid,$waitinglist->id);
        	}else{
        		$record = static::add_default_instance($courseid,$waitinglistid);
        	}
        	
        }
        return $record ? self::get_by_record($record) : null;
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
			$rec->status = false;
			$rec->emailalert=true;
			$id = $DB->insert_record(self::TABLE,$rec);
			if($id){
				$rec->id = $id;
				return $rec;
			}else{
				return $id;
			}
    }

	 
	
	 
	 //activation functions
	 public function is_active(){return $this->status;}
	 public function activate(){
		global $DB;
		$this->status=true;
		$updateobject = new \stdClass;
		$updateobject->id=$this->id;
		$updateobject->status=true;
		$DB->update_record(self::TABLE,$updateobject);
	 }
	public function deactivate(){
		global $DB;
		$this->status=false;
		$updateobject = new \stdClass;
		$updateobject->id=$this->id;
		$updateobject->status=false;
		$DB->update_record(self::TABLE,$updateobject);
	 }
	 
	 public function get_type(){return static::METHODTYPE;}


	
	/**
     * Returns optional enrolment information icons.
     *
     * This is used in course list for quick overview of enrolment options.
     *
     * @param array $instances all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons(array $waitinglists) {
		return array();
	}
	
	/**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $waitinglist
     * @return null
     */
    public function enrol_page_hook(\stdClass $waitinglist,$flagged) {
        return null;
    }
	
	/**
     * After enroling into course and removeing from waiting list. Return here to do any post processing 
     *
     * @param stdClass $waitinglist
	 * @param stdClass $queueentry
     * @return null 
     */
	public function do_post_enrol_actions(\stdClass $waitinglist,\stdClass $queueentry){
		return null;
	}
	
	public abstract function graduate_from_list(\stdClass $waitinglist,\stdClass $queueentry,$seats);
	
	 /**
     * Add user onto waitinglist via enrol method
     *
     * @param stdClass $waitinglist
     * @param int $userid
     * @param int $seats 
     * @return int queueid or false if not added to queue (ie enroled directly)
     */
    public function add_to_waitinglist(\stdClass $waitinglist, $queue_entry) {
        global $USER, $CFG; // CFG necessary!!!
		$queueid 	= null;
		$giveseats 	= null;

		//If the waitinglist is not correct, exit
		$wl = enrol_get_plugin('waitinglist');
		if ($waitinglist->courseid == SITEID) {
            throw new coding_exception('invalid attempt to enrol on frontpage waitinglist!');
        }

		//get the queue manager and add the entry to the queue
        $courseid = $waitinglist->courseid;
		$queueman= \enrol_waitinglist\queuemanager::get_by_course($courseid);
		$entryman= \enrol_waitinglist\entrymanager::get_by_course($waitinglist->courseid);
		
		//if we don't have an old entry in the table, add a new one
		$oldentry = $queueman->get_qentry_by_userid($USER->id,static::METHODTYPE);
		if(!$oldentry){
			$oldentry = $entryman->get_entry_by_userid($USER->id,static::METHODTYPE);
			if(!$oldentry){
				$queueid = $queueman->add($queue_entry);
			}
		}
		
		//if we did have an old entry, we sure dont want two, so we just update it
		if($oldentry){
			$queue_entry->id = $oldentry->id;
			$queueid = $queueman->update($queue_entry);
		}
		$queue_entry->id = $queueid;
		
		//If waitinglist says there are vacancies
		//and we are at the top of the waitinglist, enrol/confirm the user immediately.
		$graduationcomplete = false;
        $vacancies = $wl->get_vacancy_count($waitinglist);
        //there is a wee issue here.
        //if the top entry is blocking (its method is maxed out) it will prevent 
        //immediate enrolments here. But cron will still enrol them
        $position = 0;
        if (!$waitinglist->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS}) {
            $position = 1;
            $giveseats = $queue_entry->seats;
        }else if ($vacancies && $queueman->get_listposition($queue_entry)==1) {
            if($queue_entry->seats > $vacancies){
                $giveseats = $vacancies;
            }else{
                $giveseats = $queue_entry->seats;
            }

            $position = $queueman->get_listposition($queue_entry);
        }

		if($position ==1 ){
            //adjust seats according to max allowed by this enrolment method
			$method_enrolable = $this->get_max_can_enrol();
			if($method_enrolable){
				$method_enroled = $entryman->get_allocated_listtotal_by_method(static::METHODTYPE);
				$remaining_can_enrol = $method_enrolable - $method_enroled ;
				
				if($giveseats > $remaining_can_enrol){
					$giveseats = $remaining_can_enrol;
				}
			}
			
			//move them off the waitinglist, and onto the course or confirmed list
			//post processing (emails mainly) should happen from the function call.	
			//graduationcomplete means all seats are allocated and we can remove this entry
			//from queue
			if($giveseats){		
				$graduationcomplete = $this->graduate_from_list($waitinglist,$queue_entry,$giveseats);
			}
		}

		//if we were not enrolled or not all our seats were granted, AND we are sending email, send email.
		if ((!$graduationcomplete || $giveseats < $queue_entry->seats)
				&& $this->emailalert 
				&& $waitinglist->{ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE}) {
			$queue_entry = $queueman->get_qentry($queueid);
			$this->email_waitlist_message($waitinglist,$queue_entry,$USER);
		}

		return $queueid;
    }
    
    /**
     * Get the email template to send
     *
     * @param stdClass $waitinglist instance data
     * @param string $message key
     * @return void
     */
    protected function get_email_template($waitinglist,$messagekey='') {
    	if (trim($this->{static::MFIELD_WAITLISTMESSAGE}) !== '') {
    		$message = $this->{static::MFIELD_WAITLISTMESSAGE};
    	}else{
    		$message = get_string('waitlistmessagetext_' . static::METHODTYPE, 'enrol_waitinglist');
    	}
	     return $message;
    }
    
    
    /**
     * Send  email to specified user telling them they are waitlisted
     *
     * @param stdClass $instance
     * @param stdClass $user user record
     * @return void
     */
    protected function email_waitlist_message($waitinglist, $entry, $user, $messagekey='',$changed=null) {
        global $CFG, $DB,$SITE;

        $course = $DB->get_record('course', array('id'=>$waitinglist->courseid), '*', MUST_EXIST);
        $context =  \context_course::instance($course->id);

        $a = new  \stdClass();
        $a->site = $SITE->shortname;
        $a->coursename = format_string($course->fullname, true, array('context'=>$context));
        $a->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $waitinglist->courseid;
        $a->editenrolurl = $CFG->wwwroot . '/enrol/waitinglist/edit_enrolform.php?id=' . 
        		$waitinglist->courseid . '&methodtype=' . static::METHODTYPE;

		$queueman= \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		$entryman= \enrol_waitinglist\entrymanager::get_by_course($waitinglist->courseid);
		$seatsonqueue = $entry->seats - $entry->allocseats;
		if($seatsonqueue > 0){
			$qposition= $queueman->get_listtotal($entry->id);
		}else{
			$qposition= 0;
			$seatsonqueue= 0;
		}
        $a->queueno = $qposition;
        $a->totalseats = $entry->seats;
        $a->allocatedseats = $entry->allocseats;
        $a->waitingseats = $seatsonqueue;

		$message = $this->get_email_template($waitinglist,$messagekey);
		$message = str_replace('{$a->coursename}', $a->coursename, $message);
		$message = str_replace('{$a->courseurl}', $a->courseurl, $message);
		$message = str_replace('{$a->editenrolurl}', $a->editenrolurl, $message);
		$message = str_replace('{$a->queueno}', $a->queueno, $message);
		$message = str_replace('{$a->totalseats}', $a->totalseats, $message);
		$message = str_replace('{$a->queueseats}', $a->totalseats, $message);//legacy
		$message = str_replace('{$a->waitingseats}', $a->waitingseats, $message);
		$message = str_replace('{$a->allocatedseats}', $a->allocatedseats, $message);
		
		if (strpos($message, '<') === false) {
			// Plain text only.
			$messagetext = $message;
			$messagehtml = text_to_html($messagetext, null, false, true);
		} else {
			$messagehtml = format_text($message, FORMAT_MOODLE, array('context'=>$context, 'para'=>false, 'newlines'=>true, 'filter'=>true));
			$messagetext = html_to_text($messagehtml);
		}
      
		/*
		 * @updateDate	01/09/2016
		 * @author		eFaktor		(fbv)
		 *
		 * Description
		 * If it has been a change of seats
		 */
		if ($changed) {
			$subject = (string)new \lang_string('waitlistmessagetitle' . $messagekey . '_' . static::METHODTYPE . '_changed', 'enrol_waitinglist', format_string($course->fullname, true, array('context'=>$context)),$user->lang);
		}else {
			$subject = (string)new \lang_string('waitlistmessagetitle' . $messagekey . '_' . static::METHODTYPE, 'enrol_waitinglist', format_string($course->fullname, true, array('context'=>$context)),$user->lang);
		}
        

        $rusers = array();
        if (!empty($CFG->coursecontact)) {
            $croles = explode(',', $CFG->coursecontact);
			list($sort, $sortparams) = users_order_by_sql('u');
			// We only use the first user.
			$i = 0;
			do {
				$rusers = get_role_users($croles[$i], $context, true, '',
					'r.sortorder ASC, ' . $sort, null, '', '', '', '', $sortparams);
				$i++;
			} while (empty($rusers) && !empty($croles[$i]));
        }
        //if ($rusers) {
        //    $contact = reset($rusers);
        //} else {
            $contact =  \core_user::get_support_user();
        //}

        // Directly emailing welcome message rather than using messaging.
        email_to_user($user, $contact, $subject, $messagetext, $messagehtml);
    }
    
    /**
     * Returns maximum enrolable via this enrolment method
	 * Though it is inconsistent, currently a value of 0 = unlimited
	 * This is different to the waitinglist itself, where the value 0 = 0.
	 * A value of 0 here effectively means "as many as the waitinglist method allows."
     *
     * @return int max enrolable
     */
	public function get_max_can_enrol(){
		return 0;
	}

	 //some methods such as "unnamed bulk" don't enrol onto course automatically
	 //others like "self" do. We check for that here
	
	 public  function can_enrol(\stdClass $waitinglist, $checkuserenrolment = true){return false;}
	 public function has_notifications(){return false;}
	 public  function show_notifications_settings_link(){return false;}
	 public  function has_settings(){return false;}
	 public  function get_dummy_form_plugin(){return false;}
	 
}
