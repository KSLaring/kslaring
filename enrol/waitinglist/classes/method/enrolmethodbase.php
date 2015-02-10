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
		$wlm = new self();
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
		$strictness = IGNORE_MISSING;	
        $record = $DB->get_record_sql("SELECT * FROM {".self::TABLE."} WHERE courseid = $courseid AND " .$DB->sql_compare_text('methodtype') . "='". static::METHODTYPE ."'", null, $strictness);
		
		if(!$record && $courseid!=SITEID){
			//waitinglist
			if(!$waitinglistid){
				$waitinglist = $DB->get_record('enrol', array('courseid' => $courseid,'enrol'=>'waitinglist'));
				$waitinglistid = $waitinglist->id;
			}
			$rec = new \stdClass();
			$rec->courseid = $courseid;
			$rec->waitinglistid = $waitinglistid;
			$rec->methodtype = static::METHODTYPE;
			$id = $DB->insert_record(self::TABLE,$rec);
			$record = $DB->get_record(self::TABLE,array('id'=>$id));
		}
		
		return $record ? static::from_record($record) : null;
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
     * is user already on list?
     *
     * @param int User IDstdClass $instance enrolment instance
     * 
     * @return bool|string true if on list, else false if not.
     */
	 /*
	 public function is_already_on_list($userid){
		global $DB;
		 $records = $DB->get_record_sql("SELECT * FROM {".static::QTABLE."} WHERE waitinglistid = $this->waitlist AND userid = $userid AND " .$DB->sql_compare_text('methodtype') . "='". static::METHODTYPE ."'");
		 return $records ? true : false;
	}
	*/
	
	  /**
     * count users already on list
     *
     * 
     * @return bool|string true if on list, else false if not.
     */
	 /*
	 public function count_users_on_list(){
		global $DB;
		 $records = $DB->get_record_sql("SELECT * FROM {".static::QTABLE."} WHERE waitinglistid = $this->waitlist AND " .$DB->sql_compare_text('methodtype') . "='". static::METHODTYPE ."'");
		 return $records ? $records.length : false;
	}
	*/
	

	
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
		return $queueid;
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
