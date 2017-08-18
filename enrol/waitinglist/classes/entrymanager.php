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
    /**
     * @param           $courseid
     * @return          null|static
     * @throws          \Exception
     *
     * @updateDate      17/06/2015
     * @author          eFakor      (fbv)
     *
     * Description
     * If there is none enrolment method return null
     */
    public static function get_by_course($courseid){
        /* Variables    */
        global $DB;
        $wlm = null;

         try {
             /* Execute */
             $rdo = $DB->get_record('enrol', array('courseid' => $courseid,'enrol'=>'waitinglist'));
             if ($rdo) {
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
             }//if_rdo

             return $wlm;
         }catch (\Exception $ex) {
             throw $ex;
         }//try_catch
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
	 *
	 * @updateDate	03/11/2016
	 * @author		eFaktor		(fbv)
	 *
	 * Description
	 * Confirmed entries --> allocseats
     */
	public function get_confirmed_entries_old(){
		global $DB;
		$where = "courseid = $this->courseid ";
		$where .="AND waitinglistid = " . $this->waitinglist->id . " ";
		$where .="AND allocseats > 0 ";
		$entries = $DB->get_records_select(self::CTABLE, $where);
		return $entries;
	}

	/**
	 * Return all queue entries
	 * 
	 * @updateDate		05/12/2016
	 * @author			eFaktor	(fbv)
	 * 
	 * Add company name
	 * 
	 * @return 			array|null
	 * @throws 				 \Exception
	 */
	public function get_confirmed_entries() {
		/* Variables */
		global $DB;
		$entries 	= null;
		$sql 		= null;
		$params 	= null;

		try {
			// Search criteria
			$params = array();
			$params['wait'] 	= $this->waitinglist->id;
			$params['course']	= $this->courseid;

			// SQL Instruction
			$sql = " SELECT  		eq.id,
 									eq.userid,
									eq.companyid,
									CONCAT(co.industrycode,' - ',co.name) as 'company',
									eq.methodtype,
									eq.seats,
									eq.confirmedseats,
									eq.waitinglistid
					 FROM			{enrol_waitinglist_queue} 	eq
						LEFT JOIN	{report_gen_companydata}	co	ON co.id = eq.companyid
					 WHERE 	eq.courseid 		= :course
						AND	eq.waitinglistid 	= :wait
						AND allocseats > 0 ";
			
			// Execute
			$entries = $DB->get_records_sql($sql,$params);
			
			return $entries;
		}catch (\Exception $ex) {
			throw $ex;
		}//try_catch
	}//get_confirmed_entries


	/**
	 * Description
	 * Get workplaces connected with user
	 *
	 * @creationDate	05/12/2016
	 * @author			eFaktor		(fbv)
	 *
	 * @param 		int $userId		User id
	 *
	 * @return 			mixed|null
	 * @throws 			\Exception
	 * @throws 			\dml_missing_record_exception
	 * @throws 			\dml_multiple_records_exception
	 */
	public static function get_workplace_connected($userId) {
		/* Variables */
		global $DB;
		$rdo        = null;
		$sql        = null;
		$params     = null;
		$workplace  = null;

		try {
			// Search criteria
			$params =array();
			$params['user_id']  = $userId;
			$params['level']    = 3;

			// SQL Instruction
			$sql = " SELECT   GROUP_CONCAT(DISTINCT CONCAT(co.industrycode, ' - ',co.name) 
                                          ORDER BY co.industrycode,co.name SEPARATOR '#SE#') 	as 'workplace'
                     FROM	  {user_info_competence_data}	uic
                        JOIN  {report_gen_companydata}	co	ON co.id = uic.companyid
                     WHERE	  uic.userid = :user_id
                        AND   uic.level  = :level ";

			// Execute
			$rdo = $DB->get_record_sql($sql,$params);
			if ($rdo) {
				if ($rdo->workplace) {
					$workplace =     str_replace('#SE#','</br>',$rdo->workplace);
				}
			}//if_Rdo

			return $workplace;
		}catch (\Exception $ex) {
			throw $ex;
		}//try_catch
	}//get_workplace_connected
	
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
     * Description
     * count confirmed seats irrespective of methodtype
     *
     * @return      bool|string true if on list, else false if not
     * @throws      \Exception
     *
     * @updateDate  04/07/2017
     * @auhtor      eFaktor     (fbv)
     */
	 public function get_confirmed_listtotal(){
         /* Variables */
         global $DB;

         try {
             $record = $DB->get_record_sql("SELECT SUM(allocseats) as seatcount FROM {".static::CTABLE."} WHERE courseid = " .
                 $this->courseid . " AND waitinglistid = " . $this->waitinglist->id);
             return $record ? $record->seatcount : 0;
         }catch (\Exception $ex) {
             throw $ex;
         }//try_catch
	}//get_confirmed_listtotal
	
	
	
     /**
     * Add more confirmed seats to an entry. ie shift off queue and onto confirmed list
     *
     * @param int entryid
     * @param int the number of seats to add
     * @return stdClass the updatedentry if successful, false if not
    */
	public function confirm_seats($entryid,$seats){
		/* Variables */
		global $DB;
		$entry = null;
		$wl		= null;
		$rdo 	= null;
		$params	= null;

		try {
			//get the entry
			$entry 	= $this->get_entry($entryid);

			//always the chief user is enrolled, so lets do that
			if($entry->allocseats==0 && $entry->enroledseats==0){
				$wl = enrol_get_plugin('waitinglist');

				/* check if the users is already enrolled */
				$params = array();
				$params['enrolid'] 	= $entry->waitinglistid;
				$params['userid']	= $entry->userid;
				/* Execute */
				$rdo = $DB->get_record('user_enrolments',$params);
				if (!$rdo) {
					/* Enrolled */
					$wl->enrol_user($this->waitinglist,$entry->userid);
					$entry->allocseats		= 1;
					$entry->enroledseats	= 1;
					$seats = $seats -1;
				}

				//if we still need to allocate seats, lets do that
				if($seats > 0) {
					$entry->confirmedseats	+= $seats;
					$entry->allocseats		+= $seats;
				}

				//lets make sure we take entry off list
				if($entry->allocseats >= $entry->seats){
					$entry->offqueue	= 1;
					$entry->queueno		= queuemanager::OFFQ;
				}

				//update the DB and return
				$result = $this->update_entry($entry);
				if ($result){
					return $entry;
				}else{
					return false;
				}
			}

			return $entry;
		}catch (\Exception $ex) {
			throw $ex;
		}//try_catch
	}
	
	/**
     * Remove this entry from DB. Calling function should handle housekeeping
     * associated with this
     *
     * @param int entryid
     * @return true if successful, false if not
    */
	public function remove_entry_from_db($entryid){
		global $DB;
		$ret = $DB->delete_records(self::CTABLE,array('id'=>$entryid));
		return $ret;
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
			$this->remove_entry_from_db($entryid);
			//$DB->delete_records(self::CTABLE,array('id'=>$entryid));

			$wl = enrol_get_plugin('waitinglist');
			$wl->unenrol_user($this->waitinglist,$entry->userid);
			return true;
		}

		//if seat count increased
		//always add seats to queue. later can be graduated off queue
		if($entry->seats < $newseatcount){
		//	//if we are not on queue, add to end of queue
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
	}//update_Seats

	/**
	 * @param $entryid
	 * @param $newseatcount
	 * @param $userId
	 * @param $courseId
	 * @param $waitingList
	 * @param $vacancies
	 * @return bool|stdClass|null
	 * @throws \Exception
	 *
	 * @creationDate	03/09/2016
	 * @author			eFaktor		(fbv)
	 *
	 * Description
	 * Update seats ferom bulk, after a change.
	 */
	public function update_seats_bulk($entryid,$newseatcount,$userId,$courseId,$waitingList,&$vacancies) {
		/* Variables */
		global  $DB;
		$entry 		= null;
		$rdo 		= null;
		$params		= null;
		$sql 		= null;
		$occupaied	= null;
		
		try {
			/* Get Entry	*/
			$entry = $this->get_entry($entryid);
			if(!$entry){return false;}

			//if no change, just return
			if($entry->seats == $newseatcount){return $entry;}

			if($newseatcount==0){
				$this->remove_entry_from_db($entryid);

				$wl = enrol_get_plugin('waitinglist');
				$wl->unenrol_user($this->waitinglist,$entry->userid);
				return true;
			}else {
				if ($waitingList->customint2) {
					/* Get vacancies */
					$occupaied = $this->GetOcuppaiedSeats_NotConnectedUser($userId,$courseId,$waitingList->id);
					$vacancies = $waitingList->customint2 - $occupaied;
					if ($newseatcount > $vacancies) {
						$entry->offqueue		= 0;
						$entry->queueno			= queuemanager::get_maxq_no($waitingList->id) + 1;
						$entry->allocseats		= $vacancies;
						$entry->confirmedseats	= $vacancies -1;
					}else {
						$entry->offqueue		= 1;
						$entry->queueno			= queuemanager::OFFQ;
						$entry->allocseats		= $newseatcount;
						$entry->confirmedseats	= $newseatcount -1;
					}
				}else {
					/* Unlimitted */
					$entry->offqueue		= 1;
					$entry->queueno			= queuemanager::OFFQ;
					$entry->allocseats		= $newseatcount;
					$entry->confirmedseats	= $newseatcount -1;
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
		}catch (\Exception $ex) {
			throw $ex;
		}//try_catch
	}//update_seats_bulk

	/**
	 * @param 			$userId
	 * @param 			$courseId
	 * @param 			$waitingId
	 * @return int
	 * @throws \Exception
	 *
	 * @creationDate	03/09/2016
	 * @author			eFaktor		(fbv)
	 *
	 * Description
	 * Get total seats occupaid for other users
	 */
	public function GetOcuppaiedSeats_NotConnectedUser($userId,$courseId,$waitingId) {
		/* Variables */
		global $DB;
		$rdo 		= null;
		$sql 		= null;
		$params 	= null;
		$occupaied 	= 0;
		
		try {
			/* Search criteria	*/
			$params = array();
			$params['user'] 	= $userId;
			$params['course']	= $courseId;
			$params['wait']		= $waitingId;

			/* SQL Instruction */
			$sql = " SELECT     SUM(confirmedseats) as 'confirm',
							    SUM(enroledseats) as 'enrol'
					 	 FROM	{enrol_waitinglist_queue}
					 	 WHERE	courseid 		 = :course
							AND	waitinglistid	 = :wait
							AND userid 			!= :user ";
			/* Execute */
			$rdo = $DB->get_record_sql($sql,$params);
			if ($rdo) {
				$occupaied = $rdo->confirm + $rdo->enrol;
			}
			
			return $occupaied;
		}catch (\Exception $ex) {
			throw $ex;
		}//try_catch
	}//GetOcuppaiedSeats_NotConnectedUser
	
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
