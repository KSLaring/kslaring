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
	const OFFQ = 99999;
	
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
    /**
     * @param       $courseid
     * @return      null|static
     * @throws      \Exception
     *
     * @updateDate  17/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * It there is none enrolment mwthod return null
     */
    public static function get_by_course($courseid){
        /* Variables   */
        global $DB;
        $wlm = null;

        try {
            /* Execute  */
            $rdo = $DB->get_record('enrol', array('courseid' => $courseid,'enrol'=>'waitinglist'));
            if ($rdo) {
                //	static $wlm = null;
                //     if (null === $wlm) {
                $wlm = new static();
                $wlm->courseid=$courseid;
                $wlm->waitinglist = $DB->get_record('enrol', array('courseid' => $courseid,'enrol'=>'waitinglist'));
                $records =  $DB->get_records(self::QTABLE, array('courseid' => $courseid, 'waitinglistid'=>$wlm->waitinglist->id, 'offqueue'=>0),'queueno ASC');
                if($records){
                    $wlm->qentries = $records;
                }
                //  }
            }//if_rdo

            return $wlm;
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
	}
	
	/**
	 * Description
	 * All queue instance connected plus workplace
	 *
	 * @param 			int $courseid	Course id
	 *
	 * @return 				null|static
	 * @throws   			\Exception
	 */
	public static function get_by_course_workspace($courseid) {
		/* Variables */
		global $DB;
		$params 	= null;
		$sql 		= null;
		$rdo 		= null;
		$wlm		= null;
		$entries	= null;

		try {
			/* First find get the instance */
			// Search criteria
			$params = array();
			$params['courseid'] = $courseid;
			$params['enrol']	= 'waitinglist';
			// Execute
			$rdo = $DB->get_record('enrol',$params);

			// Get instances waiting in the queue
			if ($rdo) {
				$wlm = new static();
				$wlm->courseid		= $courseid;
				$wlm->waitinglist	= $rdo;
				// get entries
				$wlm->qentries = self::get_entries_no_confirmed($courseid,$rdo->id);
			}//if_rdo
			
			return $wlm;
		}catch (\Exception $ex) {
			throw $ex;
		}//try_catch
	}//get_by_course_workspace

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
	 * Description
	 * Get all entries not confirmed yet
	 *
	 * @creationDate	05/12/2016
	 * @author			eFaktor		(fbv)
	 *
	 * @param 		int $courseid
	 * @param 		int $waitingid
	 *
	 * @return 			array|null
	 * @throws 			\Exception
	 */
	private static function get_entries_no_confirmed($courseid,$waitingid) {
		/* Variables */
		global $DB;
		$params 	= null;
		$sql 		= null;
		$rdo 		= null;
		$entries	= null;

		try {
			// Search criteria
			$params = array();
			$params['wait'] 	= $waitingid;
			$params['course']	= $courseid;
			$params['queue']	= 0;

			// SQL Instruction
			$sql = " SELECT  		eq.*,
									CONCAT(co.industrycode,' - ',co.name) as 'company'
					 FROM			{enrol_waitinglist_queue} 	eq
						LEFT JOIN	{report_gen_companydata}	co	ON co.id = eq.companyid
					 WHERE 	eq.courseid 		= :course
						AND	eq.waitinglistid 	= :wait
						AND eq.offqueue 		= :queue
					 ORDER BY eq.queueno ";

			// Execute
			$entries = $DB->get_records_sql($sql,$params);

			return $entries;
		}catch (\Exception $ex) {
			throw $ex;
		}//try_catch
	}//get_entries_no_confirmed

	
	public static function get_maxq_no($waitinglistid){
		global $DB;
		$ret = $DB->get_record_sql('SELECT MAX(queueno) AS maxq, 1		
                                     FROM {'. self::QTABLE .'} WHERE offqueue=0 AND waitinglistid=' . $waitinglistid);
        if(empty($ret->maxq)){
        	$maxq=0;
        }else{
        	$maxq=$ret->maxq;
        }
        return $maxq;
	
	}
	
	
	/**
     *  Return a particular queue entry
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
     *  Return all queue entries
     */
	public function get_qentries(){
		return $this->qentries;
	}
	
		/**
     *  Return a particular users queue entry
     */
	public function get_qentry_by_userid($userid,$methodtype=false){
		foreach($this->qentries as $qentry){
			if($qentry->userid == $userid){
				if($methodtype===false){
					return $qentry;
				}elseif($methodtype==$qentry->methodtype){
					return $qentry;
				}
			}
		}
		return false;
	}
	
	
	/**
     *  Return a users position on the queue, and the total no on the queue
     */
	public function get_user_queue_details($methodtype){
		global $DB,$USER;
		
		$qdetails = new \stdClass;
		$qdetails->queueposition=0;
		$qdetails->offqueue=1;
		$qdetails->queuetotal=$this->get_listtotal();
		$details = $DB->get_records(self::QTABLE,array('courseid'=>$this->courseid,'userid'=>$USER->id,'waitinglistid'=>$this->waitinglist->id, 'offqueue'=>0));
		if(!$details){return $qdetails;}
		$qdetails->offqueue=0;
		foreach($details as $detail){
			if($detail->methodtype==$methodtype){
				$qdetails->queueposition = $this->get_listposition($detail);
				break;
			}
		}
		return $qdetails;
	}
	
		 /**
     * is user already on list?
     *
     * @param int User ID
     * @param string methodtype 
     * @return bool|string true if on list, else false if not.
     */
	 public function is_on_list($userid,$methodtype){
		global $DB;
		$details = $DB->get_records(self::QTABLE,array('courseid'=>$this->courseid,'userid'=>$userid,'waitinglistid'=>$this->waitinglist->id, 'offqueue'=>0));
		if(!$details){return false;}
		foreach($details as $detail){
			if($detail->methodtype==$methodtype){
				return true;
			}
		}
		return false;
	}
	
	  /**
     * count users already on list
     *
     * 
     * @return bool|string true if on list, else false if not.
     */
	 public function get_listtotal_by_method($methodtype){
		global $DB;
		 $record = $DB->get_record_sql("SELECT SUM(seats - allocseats) as seatcount FROM {".static::QTABLE."} WHERE courseid = " . 
		 	$this->courseid . " AND offqueue = 0  AND waitinglistid = " . $this->waitinglist->id . 
		 	" AND " .$DB->sql_compare_text('methodtype') . "='". $methodtype ."'");
		 return $record ? $record->seatcount : 0;
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
	public function update($qentry){
		global $DB;
		unset($qentry->queueno);
		$qentry->courseid=$this->courseid;
		$qentry->waitinglistid=$this->waitinglist->id;
		$DB->update_record(self::QTABLE, $qentry);
		
		//refresh our list
		$records =  $DB->get_records(self::QTABLE, array('courseid' => $this->courseid, 'waitinglistid'=>$this->waitinglist->id, 'offqueue'=>0),'queueno ASC');
		if($records){
			$this->qentries = $records;
		}
		
		return $qentry->id;
	}
	
	/**
     * Adds a user to the waiting list
     *
     * @param stdclass queue object (db fields basically for queue table)
     * @return int the id of the queue item, or false if we somehow failed.
     */
	public function add($qentry){
		global $DB;
		
		if ($this->is_on_list($qentry->userid,$qentry->methodtype)) {
            throw new \coding_exception('user is already on the waiting list for this course and methodtype');
        } else {
            
            $qentry->id = $DB->insert_record(self::QTABLE, $qentry);
            
			if($qentry->id){
				$maxq = self::get_maxq_no($this->waitinglist->id);
				$queue_entry = new \stdClass;
				$queue_entry->id= $qentry->id;
				$queue_entry->queueno =$maxq +1;
				$DB->update_record(self::QTABLE, $queue_entry);
				$this->qentries[] =$qentry;
				return $qentry->id;
			}
			return false;
        }

      
	}
	
	/**
     * SHOULD NEVER BE USED : OBSELETE
     *
     * @return stdclass the top entry on the waiting list
     */
	public function remove_first(){
		global $DB;
		$qentry = array_shift($this->qentries);
		$qentry->offqueue=1;
		$DB->update_record(self::QTABLE,$qentry);
		$this->reorder();
		return $qentry;
	}
	
	/**
     * Returns the top user off the waiting list, but doesn't remove it
     *
     * @return stdclass the top entry on the waiting list
     */
	public function peek_first(){
		global $DB;

		return $this->qentries[0];
		//return array_shift(array_values($this->qentries));
	}
	
	/**
     * Takes a user off the list
     *
     * @return stdclass the top entry on the waiting list
     */
	public function really_remove_entry($qentryid){
		global $DB;
		
		$qentry = $this->get_qentry($qentryid);
		
		//unenrol user if they exist
		if($qentry){
			$wl = enrol_get_plugin('waitinglist');
			$wl->unenrol_user($this->waitinglist,$qentry->userid);
		}
		
		//delete from DB
		$ok = $DB->delete_records(self::QTABLE,array('id'=>$qentryid));
		//reorder and return
		if($ok){
			$ok= $this->reorder();
		}
		
		return $ok;
	}
	
	/**
     * Takes a user off the list
     *
     * @return stdclass the top entry on the waiting list
     */
	public function remove_entry($qentryid){
		global $DB;
		$ok = $DB->set_field(self::QTABLE,'offqueue',1,array('id'=>$qentryid));
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
		$records =  $DB->get_records(self::QTABLE, array('waitinglistid' => $this->waitinglist->id, 'offqueue'=>0),'queueno ASC');
		if($records){
			$queueno = 0;
			foreach ($records as $record){
				$queueno++;
				$DB->set_field(self::QTABLE, 'queueno', $queueno,array('id'=>$record->id));
			}
		}
		return true;
	}
	
	/**
     * Checks if our waiting list is full
     *
     * @return boolean true=list is full | false = not full yet
     */
	public function is_full(){
        if ($this->waitinglist->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS}) {
            return $this->get_listtotal() >= $this->waitinglist->{ENROL_WAITINGLIST_FIELD_WAITLISTSIZE};
        }
	}
	
	/**
     * GEts the total of users on our waiting list
     *
     * @return int  users on the waiting list
     */
	public function get_listtotal($until_qentryid=false){
		$seatcount = 0;
		if(!$this->qentries){return 0;}
		foreach($this->qentries as $qentry){
			if($qentry->id == $until_qentryid){ 
				$seatcount += 1;
				break;
			}else{
				$seatcount += $qentry->seats - $qentry->allocseats ;
			}
		}
		return $seatcount;
	}
	
	/**
     * Gets the position on the queue of the passed in entry
     *
     * @return int queue position
     */
	public function get_listposition($qentry){
		return $this->get_listtotal($qentry->id);
		//return $this->qentries ? count($this->qentries) : 0;
	}
	
	/**
     * GEts the total queue items on our waiting list
     *
     * @return int  users on the waiting list
     */
	public function get_entrycount(){
		return $this->qentries ? count($this->qentries) : 0;
	}
	
	
}
