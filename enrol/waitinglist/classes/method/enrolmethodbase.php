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
        	}
        	$record = static::add_default_instance($courseid,$waitinglist->id);
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
     * We are not using single instance parameter because sometimes
     * we might want to prevent icon repetition when multiple instances
     * of one type exist. One instance may also produce several icons.
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
    public function enrol_page_hook(\stdClass $waitinglist) {
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
	
	public abstract function graduate_from_list(\stdClass $waitinglist,\stdClass $queueentry);
	
	 /**
     * Enrol user into waitinglist via enrol method
     *
     * @param stdClass $waitinglist
     * @param int $userid
     * @param int $seats 
     * @return int queueid or false if not added to queue (ie enroled directly)
     */
    public function add_to_waitinglist(\stdClass $waitinglist, $queue_entry) {
        global $DB, $USER, $CFG; // CFG necessary!!!
		
		//there are spaces on the course, we don't need to use the waitlist
		//enrol the user directly.
		$wl = enrol_get_plugin('waitinglist');
		if ($waitinglist->courseid == SITEID) {
            throw new coding_exception('invalid attempt to enrol on frontpage waitinglist!');
        }

		//get the queue manager and add the entry to the queue
        $courseid = $waitinglist->courseid;
		$queueman= \enrol_waitinglist\queuemanager::get_by_course($courseid);
		
		$oldentry = $queueman->get_qentry_by_userid($USER->id,static::METHODTYPE);
		if(!$oldentry){
			$queueid = $queueman->add($queue_entry);
		}else{
			$queue_entry->id = $oldentry->id;
			$queueid = $queueman->update($queue_entry);
		}
		
		$queue_entry->id = $queueid;
		
		//this part is a bit tricky, it will skip the ad hoc procesising and email if it enrols the user
		//immediately. NB not unnamedbulk
		$enroled = false;
		if($this->can_enrol_directly() && $wl->can_enrol_directly($waitinglist)){
			$enroled = $this->graduate_from_list($waitinglist,$queue_entry);
		}

		if (!$enroled && $this->emailalert && $waitinglist->{ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE}) {
			$queue_entry = $queueman->get_qentry($queueid);
			$this->email_waitlist_message($waitinglist,$queue_entry,$USER);
		}

		return $queueid;
    }
    
    
    protected function get_email_template($waitinglist) {
    	if (trim($this->{static::MFIELD_WAITLISTMESSAGE}) !== '') {
    		$message = $this->{static::MFIELD_WAITLISTMESSAGE};
    	}else{
    		$message = get_string('welcometowaitlisttext_' . static::METHODTYPE, 'enrol_waitinglist');
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
    protected function email_waitlist_message($waitinglist, $queue_entry, $user) {
        global $CFG, $DB;

        $course = $DB->get_record('course', array('id'=>$waitinglist->courseid), '*', MUST_EXIST);
        $context =  \context_course::instance($course->id);

        $a = new  \stdClass();
        $a->coursename = format_string($course->fullname, true, array('context'=>$context));
        $a->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $waitinglist->courseid;
        $a->editenrolurl = $CFG->wwwroot . '/enrol/waitinglist/edit_enrolform.php?id=' . 
        		$waitinglist->courseid . '&methodtype=' . static::METHODTYPE;

		$queueman= \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		$qposition= $queueman->get_listtotal($queue_entry->id);
        $a->queueno = $qposition;
        $a->queueseats = $queue_entry->seats;


        $message = $this->get_email_template($waitinglist);
		$message = str_replace('{$a->coursename}', $a->coursename, $message);
		$message = str_replace('{$a->courseurl}', $a->courseurl, $message);
		$message = str_replace('{$a->editenrolurl}', $a->editenrolurl, $message);
		$message = str_replace('{$a->queueno}', $a->queueno, $message);
		$message = str_replace('{$a->queueseats}', $a->queueseats, $message);
		
		if (strpos($message, '<') === false) {
			// Plain text only.
			$messagetext = $message;
			$messagehtml = text_to_html($messagetext, null, false, true);
		} else {
			// This is most probably the tag/newline soup known as FORMAT_MOODLE.
			$messagehtml = format_text($message, FORMAT_MOODLE, array('context'=>$context, 'para'=>false, 'newlines'=>true, 'filter'=>true));
			$messagetext = html_to_text($messagehtml);
		}
      

        $subject = get_string('welcometowaitlist_' . static::METHODTYPE, 'enrol_waitinglist', format_string($course->fullname, true, array('context'=>$context)));

        $rusers = array();
        if (!empty($CFG->coursecontact)) {
            $croles = explode(',', $CFG->coursecontact);
            list($sort, $sortparams) = users_order_by_sql('u');
            $rusers = get_role_users($croles, $context, true, '', 'r.sortorder ASC, ' . $sort, null, '', '', '', '', $sortparams);
        }
        if ($rusers) {
            $contact = reset($rusers);
        } else {
            $contact =  \core_user::get_support_user();
        }

        // Directly emailing welcome message rather than using messaging.
        email_to_user($user, $contact, $subject, $messagetext, $messagehtml);
    }

	 //some methods such as "unnamed bulk" don't enrol onto course automatically
	 //others like "self" do. We check for that here
	 public  function can_enrol_directly(){return false;}
	 
	 public  function can_self_enrol(\stdClass $waitinglist){return false;}
	 public  function can_enrol(\stdClass $waitinglist, $checkuserenrolment = true){return false;}
	 public function has_notifications(){return false;}
	 public  function show_notifications_settings_link(){return false;}
	 public  function has_settings(){return false;}
	 public  function get_dummy_form_plugin(){return false;}
	 

}
