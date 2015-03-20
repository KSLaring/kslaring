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
 * Waiting List EntriesManager
 *
 * @package    enrol_waitinglist
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 onwards Justin Hunt  http://poodll.com
 */

class entrymanager  {

	const CTABLE='enrol_waitinglist_queue';

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
			/*
			$records =  $DB->get_records_select(self::CTABLE, "courseid = $courseid AND waitinglistid = $wlm->waitinglist->id AND allocseats > 0"),'id ASC');
			if($records){
				$wlm->centries = $records;
			}
			*/
      //  }
		return $wlm;
	}
	
	
	/**
     *  return a particular entry
     *
     * @param int the entryid
     * @return stdClass|boolean the entry or false if it failed
     */
	public function get_entry($entryid){
		global $DB;
		$entry =  $DB->get_record(self::CTABLE, array('id' => $entryid));
		return $entry;
	}
	
	/**
     *  update a particular entry
     *
     * @param stdClass the entry
     * @return stdClass|boolean the updated entry or false if it failed
     */
	public function update_entry($entry){
		global $DB;
		$entry =  $DB->update_record(self::CTABLE, $entry);
		return $entry;
	}
	
	/**
     *  Return all queue entries
     *
     * @return array all the confirmed entries
     */
	public function get_confirmed_entries(){
		global $DB;
		$where = "courseid = $this->courseid ";
		$where .="AND waitinglistid = " . $this->waitinglist->id . " ";
		$where .="AND confirmedseats > 0 ";
		$entries = $DB->get_records_select(self::CTABLE, $where);
		return $entries;
	}
	
	/**
     * There should only be 1 entry on the waitinglist per course, for a single user.
     * This fetches it
     *
     * @param int userid
     * @param string methodtype
     * @return stdClass the db entry for a user.
     */
	public function get_entry_by_userid($userid,$methodtype=false){
		global $DB;
		if($methodtype){
			$methodtypecond = $this->get_methodtype_condition($methodtype);
			$where = "courseid = $this->courseid ";
			$where .="AND waitinglistid = " . $this->waitinglist->id ." ";
			$where .="AND userid = $userid ";
			$where .="AND " . $methodtypecond; 
			$entry = $DB->get_record_select(self::CTABLE, $where);
		}else{
			$entry = $DB->get_record(self::CTABLE, array('userid' => $userid, 
					'courseid'=>$this->courseid, 'waitinglistid'=>$this->waitinglist->id));
		}
		return $entry;	
	}
	
	/**
     * Moodle doesn't allow raw DB string comparisons (GRRRR!!#$&"$#)
     * So this just centralizes the methodtype sql condition, to make it easy
     *
     * @param string methodtype
     * 
     * @return string the methodtype sql condition
     */
	protected function get_methodtype_condition($methodtype){
		global $DB;
		$condition = $DB->sql_compare_text('methodtype') . "='". $methodtype ."'";
		return $condition;
	}
	
	/**
     * is user already on list
     *
     * @param int User IDstdClass $instance enrolment instance
     * 
     * @return bool|string true if on list, else false if not.
     */
	 public function is_on_list($userid,$methodtype=false){
		global $DB;
		$details = $DB->get_records(self::CTABLE,array('courseid'=>$this->courseid,'userid'=>$userid,'waitinglistid'=>$this->waitinglist->id));
		//if not on list, return false
		if(!$details){return false;}
		//if on list and we dont care about methodtype, return true
		if(!$methodtype){
			return true;
		}
		//check if list entry matches desired methodtype
		foreach($details as $detail){
			if($detail->methodtype==$methodtype){
				return true;
			}
		}
		return false;
	}
	
	  /**
     * count confirmed seat total of particular methodtype
     *
     * 
     * @return bool|string true if on list, else false if not.
     */
	 public function get_confirmed_listtotal_by_method($methodtype){
		global $DB;
		 $record = $DB->get_record_sql("SELECT SUM(confirmedseats) as seatcount FROM {".static::CTABLE."} WHERE courseid = " . 
		 	$this->courseid . " AND waitinglistid = " . $this->waitinglist->id . 
		 	" AND " . $this->get_methodtype_condition($methodtype));
		 return $record ? $record->seatcount : 0;
	}
	
	  /**
     * count alloc seat total of particular methodtype
     *
     * 
     * @return bool|string true if on list, else false if not.
     */
	 public function get_allocated_listtotal_by_method($methodtype){
		global $DB;
		 $record = $DB->get_record_sql("SELECT SUM(allocseats) as seatcount FROM {".static::CTABLE."} WHERE courseid = " . 
		 	$this->courseid . " AND waitinglistid = " . $this->waitinglist->id . 
		 	" AND " . $this->get_methodtype_condition($methodtype));
		 return $record ? $record->seatcount : 0;
	}
	
	  /**
     * count seat total of particular methodtype
     *
     * 
     * @return bool|string true if on list, else false if not.
     */
	 public function get_listtotal_by_method($methodtype){
		global $DB;
		 $record = $DB->get_record_sql("SELECT SUM(seats) as seatcount FROM {".static::CTABLE."} WHERE courseid = " . 
		 	$this->courseid . " AND waitinglistid = " . $this->waitinglist->id . 
		 	" AND " . $this->get_methodtype_condition($methodtype));
		 return $record ? $record->seatcount : 0;
	}

     /**
     * count confirmed seats irrespective of methodtype
     *
     * 
     * @return bool|string true if on list, else false if not.
     */
	 public function get_confirmed_listtotal(){
		global $DB;
		 $record = $DB->get_record_sql("SELECT SUM(confirmedseats) as seatcount FROM {".static::CTABLE."} WHERE courseid = " . 
		 	$this->courseid . " AND waitinglistid = " . $this->waitinglist->id);
		 return $record ? $record->seatcount : 0;
	}
	
	
	
     /**
     * Add more confirmed seats to an entry. ie shift off queue and onto confirmed list
     *
     * @param int entryid
     * @param int the number of seats to add
     * @return stdClass the updatedentry if successful, false if not
    */
	public function confirm_seats($entryid,$seats){
		global $DB;
		
		//get the entry
		$entry = $this->get_entry($entryid);
		
		//always the chief user is enrolled, so lets do that
		if($entry->allocseats==0 && $entry->enroledseats==0){
			 $wl = enrol_get_plugin('waitinglist');
			 $wl->enrol_user($this->waitinglist,$entry->userid);
			 $entry->allocseats=1;
			 $entry->enroledseats=1;
			 $seats = $seats -1;
		}
		
		//if we still need to allocate seats, lets do that
		if($seats > 0){
			$entry->confirmedseats+=$seats;
			$entry->allocseats+=$seats;
			if($entry->allocseats >= $entry->seats){
				$entry->offqueue=1;
				$entry->queueno=queuemanager::OFFQ;
			}
		}
		
		//update the DB and return
        $result = $this->update_entry($entry);
 		if ($result){
 			return $entry;
 		}else{
 			return false;
 		}
	}
	
	/**
     * When the user changes thenumber of seats, deal with it. 
     * adjust queue/confirmations etc
     *
     * @param int entryid
     * @param int newseatcount
     * @return stdClass the updatedentry if successful, false if not
    */
	public function update_seats($entryid,$newseatcount){
		global $DB;
		
		$entry = $this->get_entry($entryid);
		if(!$entry){return false;}
		
		//if no change, just return
		if($entry->seats == $newseatcount){return $entry;}
		
		//if seats set to zero, we remove the entry, unenrol the user, slap hands and return
		if($newseatcount==0){
			 $DB->delete_records(self::CTABLE,array('id'=>$entryid));
			 $wl = enrol_get_plugin('waitinglist');
			 $wl->unenrol_user($this->waitinglist,$entry->userid);
			 return true;
		}
		
		//if seat count increased
		//always add seats to queue. later can be graduated off queue
		if($entry->seats < $newseatcount){
			//if we are not on queue, add to end of queue
			if($entry->offqueue==1){
				$entry->offqueue=0;
				$entry->queueno= queuemanager::get_maxq_no($this->waitinglist->id) + 1;
			}
		//if seat count decreased
		}else{

			//if we new seats are equal to or less than current allocations
			//make sure our queue entry is "removed" , and update confirmations
			if($newseatcount <= $entry->allocseats){
				$entry->offqueue=1;
				$entry->queueno=queuemanager::OFFQ;
				$entry->allocseats=$newseatcount;
				$entry->confirmedseats=$newseatcount -1;
			}
		}
		
		//This is enough to tidy up unconfirm logic
		//and deal with a reduced queue size
		$entry->seats=$newseatcount;

		//finally update DB and return
		$ret = $this->update_entry($entry); 
		if($ret){
			return $entry;
		}else{
			return false;
		}
	}
	
	/**
     * Takes an entry off confirmed list and return to waiting list
     *
     * @return stdclass the top entry on the waiting list
     */
	public function unconfirm_entry($entryid){
		global $DB;
		$entry = $this->get_entry($entryid);
		if(!$entry){return false;}
		
		//if not on the queue, put back on
		if($entry->offqueue==1){
			$entry->offqueue=0;
			$entry->queueno= queuemanager::get_maxq_no($this->waitinglist->id) + 1;
		}
		
		
		if($entry->allocseats && $entry->enroledseats){
			//unenrol user
			$wl = enrol_get_plugin('waitinglist');
			$wl->unenrol_user($this->waitinglist,$entry->userid);
		}
		
		//update seat info
		$entry->allocseats = 0;
		$entry->confirmedseats = 0;
		$entry->enroledseats=0;
		
		//finally update DB and return
		$ret = $this->update_entry($entry); 
		if($ret){
			return $entry;
		}else{
			return false;
		}
	}
	

}
