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
	const QTABLE='enrol_waitinglist_queue';
	
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

	 
	 /**
     *  Exists in Couse
     */
	  public static function exists_in_course($courseid){
		global $DB;	
        $count = $DB->count_records(self::TABLE, array('courseid' => $courseid,'type'=>self::METHODTYPE));
        return $count ? true : false;
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
	public function post_enrol_hook(\stdClass $waitinglist,\stdClass $queueentry){
		return null;
	}
	
	 /**
     * Enrol user into waitinglist via enrol method
     *
     * @param stdClass $waitinglist
     * @param int $userid
     * @param int $roleid optional role id
     * @param int $timestart 0 means unknown
     * @param int $timeend 0 means forever
     * @param int $status default to ENROL_USER_ACTIVE for new enrolments, no change by default in updates
     * @param bool $recovergrades restore grade history
     * @return void
     */
    public function enrol_user(\stdClass $waitinglist, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
        global $DB, $USER, $CFG; // CFG necessary!!!
		
		//there are spaces on the course, we don't need to use the waitlist
		//enrol the user directly.
		$wl = enrol_get_plugin('waitinglist');
		if($wl->can_enrol_directly($waitinglist)){
			$wl->enrol_user($waitinglist,$userid,$roleid,$timestart,$timeend,$status,$recovergrades);
			//upon return (since we pass no queueid) the post enrol hook will be run immediately (see enrolmethodbase.php)
			return;
		}

		
        if ($waitinglist->courseid == SITEID) {
            throw new coding_exception('invalid attempt to enrol on frontpage waitinglist!');
        }

        $courseid = $waitinglist->courseid;
		//prepare our queue entry
		$queueentry = new \stdClass();
		$queueentry->waitinglistid      = $waitinglist->id;
		$queueentry->courseid       = $courseid;
		$queueentry->userid       = $userid;
		$queueentry->timestart    = $timestart;
		$queueentry->timeend      = $timeend;
		$queueentry->methodtype   = static::METHODTYPE;
		$queueentry->customint1 = 0;
		$queueentry->customint2 = 0;
		$queueentry->customint3 = 0;
		$queueentry->customtext1 = '';
		$queueentry->customtext2 = '';
		$queueentry->customtext3 = '';
		$queueentry->timecreated  = time();
		$queueentry->queueno = 	0;
		$queueentry->seats = 1;
		$queueentry->timemodified = $queueentry->timecreated;
		
		$queueman= \enrol_waitinglist\queuemanager::get_by_course($courseid);
		$queueid = $queueman->add($queueentry);

        // Send waitlistmessage message.
        if ($this->emailalert && $waitinglist->{ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE}) {
        	$queueentry = $queueman->get_qentry($queueid);
            $this->email_waitlist_message($waitinglist,$queueentry,$USER);
        }
		
		return $queueid;
    }
    
    
    protected function get_email_template($waitinglist) {
    	if (trim($waitinglist->{ENROL_WAITINGLIST_FIELD_WAITLISTMESSAGE}) !== '') {
    		$message = $waitinglist->{ENROL_WAITINGLIST_FIELD_WAITLISTMESSAGE};
    	}else{
    		$message = get_string('welcometowaitlisttext', 'enrol_waitinglist');
    	}
    
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
        $a->queueno = $queue_entry->queueno;


        $message = $this->get_email_template($waitinglist);
		$message = str_replace('{$a->coursename}', $a->coursename, $message);
		$message = str_replace('{$a->courseurl}', $a->courseurl, $message);
		$message = str_replace('{$a->queueno}', $a->queueno, $message);
		
		if (strpos($message, '<') === false) {
			// Plain text only.
			$messagetext = $message;
			$messagehtml = text_to_html($messagetext, null, false, true);
		} else {
			// This is most probably the tag/newline soup known as FORMAT_MOODLE.
			$messagehtml = format_text($message, FORMAT_MOODLE, array('context'=>$context, 'para'=>false, 'newlines'=>true, 'filter'=>true));
			$messagetext = html_to_text($messagehtml);
		}
      

        $subject = get_string('welcometowaitlist', 'enrol_waitinglist', format_string($course->fullname, true, array('context'=>$context)));

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
	 
	 //other functions
	 public function has_enrolme_link(){return false;}
	 public function show_enrolme_link(){return false;}
	 public  function can_enrol(){return false;}
	 public  function can_self_enrol(\stdClass $waitinglist, $checkuserenrolment = true){return false;}
	 public function has_notifications(){return false;}
	 public  function show_notifications_settings_link(){return false;}
	 public  function has_settings(){return false;}
	 public  function get_dummy_form_plugin(){return false;}
	 public  function show_settings(){return false;}
	 

}
