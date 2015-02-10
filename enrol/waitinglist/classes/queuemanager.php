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

namespace enrol_waitinglist;

/**
 * Waiting List QueueManager
 *
 * @package    enrol_waitinglist
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 onwards Justin Hunt  http://poodll.com
 */

class queuemanager  {

	const QTABLE='enrol_waitinglist_queue';
	
	public $qentries = array();
	public $courseid =0;
	public $waitinglist=null;

	 /**
     *  Constructor
     */
    public function __construct()
    {

    }
	
	 /**
     *  Construct instance from DB record
     */
	 public static function get_by_course($courseid){
		global $DB;
		
	//	static $wlm = null;
   //     if (null === $wlm) {
            $wlm = new static();
			$wlm->courseid=$courseid;
			$wlm->waitinglist = $DB->get_record('enrol', array('courseid' => $courseid,'enrol'=>'waitinglist'));
			$records =  $DB->get_records(self::QTABLE, array('courseid' => $courseid),'queueno ASC');
			if($records){
				$wlm->qentries = $records;
			}
      //  }
		return $wlm;
	}
	
	/**
     *  Enrol the next user on the list into the course
     */
	public function enrol_next($wlinstance){
		if($this->get_listtotal() > 0){
			$qitem = $this->remove_first();
			$wl = enrol_get_plugin('waitinglist');
			$wl->enrol_user($wlinstance,$qitem->userid);
			$methodtype = $qitem->methodtype. '\enrolmethod' . $qitem->methodtype;
			$themethod =  $methodtype::get_by_course($this->courseid);
			$themethod->post_enrol_hook($wlinstance, $qitem);
			return true;
		}
		return false;
	}
	
	/**
     *  Return a users position on the queue, and the total no on the queue
     */
	public function get_qentry($qentryid){
		foreach($this->qentries as $qentry){
			if($qentry->id == $qentryid){
				return $qentry;
			}
		}
		return false;
	}
	
	
	/**
     *  Return a users position on the queue, and the total no on the queue
     */
	public function get_user_queue_details(){
		global $DB,$USER;
		
		$qdetails = new \stdClass;
		$qdetails->queueno=0;
		$qdetails->queuetotal=$this->get_listtotal();
		$details = $DB->get_record(self::QTABLE,array('courseid'=>$this->courseid,'userid'=>$USER->id));
		if($details){		
			//this logic will have to cahnge when we consider bulk seats on one entry on queue
			$qdetails->queueno = $details->queueno;
			//$qdetails->queuetotal = $DB->count_records(self::QTABLE,array('courseid'=>$courseid));
		}
		return $qdetails;
	}
	
		 /**
     * is user already on list?
     *
     * @param int User IDstdClass $instance enrolment instance
     * 
     * @return bool|string true if on list, else false if not.
     */
	 public function is_on_list($userid){
		global $DB;
		 $record = $DB->get_record(static::QTABLE, array('courseid' => $this->courseid,'userid' => $userid));
		 return $record ? true : false;
	}
	
	  /**
     * count users already on list
     *
     * 
     * @return bool|string true if on list, else false if not.
     */
	 public function get_listtotal_by_method($methodtype){
		global $DB;
		 $records = $DB->get_records_sql("SELECT * FROM {".static::QTABLE."} WHERE courseid = $this->courseid AND " .$DB->sql_compare_text('methodtype') . "='". $methodtype ."'");
		 return $records ? count($records) : false;
	}

	  /**
     * count users already on list
     *
     * 
     * @return bool|string true if on list, else false if not.
     */
	 public function bump($qentry, $oldposition, $newposition){
		global $DB;
			$ok = $DB->set_field(static::QTABLE, 'queueno', $oldposition, array('queueno'=>$newposition,'waitinglistid'=>$this->waitinglist->id));
			if($ok){
				$ok = $DB->set_field(static::QTABLE, 'queueno', $newposition, array('id'=>$qentry->id));
			}
			return $ok;
	}
	
	/**
     * Adds a user to the waiting list
     *
     * @param stdclass queue object (db fields basically for queue table)
     * @return int the id of the queue item, or false if we somehow failed.
     */
	public function add($qentry){
		global $DB;
		
        if ($wle = $DB->get_record('user_enrolments', array('enrolid'=>$this->waitinglist->id, 'userid'=>$qentry->userid))) {
            throw new coding_exception('user is already enrolled in this course');
		}
		if ($wle = $DB->get_record(self::QTABLE, array('waitinglistid'=>$this->waitinglist->id, 'userid'=>$qentry->userid))) {
            throw new coding_exception('user is already on the waiting list for this course');
        } else {
            
            $qentry->id = $DB->insert_record(self::QTABLE, $qentry);
            
			if($qentry->id){
				$maxq = $DB->get_record_sql('SELECT MAX(queueno) AS maxq, 1		
                                     FROM {'. self::QTABLE .'} WHERE waitinglistid=' . $this->waitinglist->id);
				$queue_entry = new \stdClass;
				$queue_entry->id= $qentry->id;
				$queue_entry->queueno =$maxq->maxq +1;
				$DB->update_record(self::QTABLE, $queue_entry);
				$this->qentries[] =$qentry;
				return $qentry->id;
			}
			return false;
        }

            // Trigger event.
			/*
            $event = \core\event\user_enrolment_created::create(
                    array(
                        'objectid' => $ue->id,
                        'courseid' => $courseid,
                        'context' => $context,
                        'relateduserid' => $ue->userid,
                        'other' => array('enrol' => $name)
                        )
                    );
            $event->trigger();
			*/
		
	}
	
	/**
     * Takes the top user off the waiting list and returns them
     *
     * @return stdclass the top entry on the waiting list
     */
	public function remove_first(){
		global $DB;
		
		$qentry = array_shift($this->qentries);
		$DB->delete_records(self::QTABLE,array('id'=>$qentry->id));
		$this->reorder();
		return $qentry;
	}
	
	/**
     * Takes a user off the list
     *
     * @return stdclass the top entry on the waiting list
     */
	public function remove_entry($qentryid){
		global $DB;
		$ok = $DB->delete_records(self::QTABLE,array('id'=>$qentryid));
		if($ok){
			$ok= $this->reorder();
		}
		return $ok;
	}
	
	/**
     * Reorder the queue
     *
     * @return stdclass the top entry on the waiting list
     */
	public function reorder(){
		global $DB;
		$records =  $DB->get_records(self::QTABLE, array('waitinglistid' => $this->waitinglist->id),'queueno ASC');
		if(!$records){return;}
		$queueno = 0;
		foreach ($records as $record){
			$queueno++;
			$DB->set_field(self::QTABLE, 'queueno', $queueno,array('id'=>$record->id));
		}
		return true;
	}
	
	/**
     * Checks if our waiting list is full
     *
     * @return stdclass the top entry on the waiting list
     */
	public function is_full(){
		return $this->get_listtotal() >= $this->waitinglist->{ENROL_WAITINGLIST_FIELD_WAITLISTSIZE};
	}
	
	/**
     * GEts the total of users on our waiting list
     *
     * @return int  users on the waiting list
     */
	public function get_listtotal(){
		return $this->qentries ? count($this->qentries) : 0;
	}
}
