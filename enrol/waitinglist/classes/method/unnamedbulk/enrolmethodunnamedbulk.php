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
 * Waiting List Enrol Method Unnamed Bulk enrolment Plugin
 *
 * @package    enrol_waitinglist
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 onwards Justin Hunt  http://poodll.com
 */
 
namespace enrol_waitinglist\method\unnamedbulk;
require_once($CFG->dirroot . '/report/manager/managerlib.php');

class enrolmethodunnamedbulk extends \enrol_waitinglist\method\enrolmethodbase {

	const METHODTYPE='unnamedbulk';
	protected $active = false;

    /**
     * @updateDate  02/09/2016
     * @author      eFaktor     (fbv)
     */
    const OFFQ = 99999;

	//const QFIELD_ENROLPASSWORD='customtext1';
	//const MFIELD_GROUPKEY = 'customint1';
	//const MFIELD_LONGTIMENOSEE = 'customint2';
	//const MFIELD_COHORTONLY = 'customint5';
	//const MFIELD_NEWENROLS = 'customint6';
	const MFIELD_MAXENROLLED = 'customint3';
	const MFIELD_SENDWAITLISTMESSAGE = 'customint4';
	const MFIELD_WAITLISTMESSAGE = 'customtext1';
	const MFIELD_SENDCONFIRMMESSAGE = 'customint5';
	const MFIELD_CONFIRMEDMESSAGE = 'customtext2';
	//const DFIELD_SEATS = 'customint1';
	const QFIELD_ASSIGNEDSEATS = 'allocseats';
	const QFIELD_SEATS = 'seats';
    const MFIELD_PRICE                  = 'customtext3';
	
	public $course = 0;
    public $waitlist = 0;
	public $maxseats = 0;
    public $activeseats = 0;
	public $notificationtypes = 0;
    /**
     * @updateDate  28/12/2015
     * @author      eFaktor     (fbv)
     */
    private $myManagers         = null;

	 /**
     *  Constructor
     */
    public function __construct()
    {
        global $CFG;

        /**
         * @updateDate  30/10/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Update to invoice data
         */
        if (enrol_get_plugin('invoice')) {
            require_once($CFG->dirroot . '/enrol/invoice/invoicelib.php');
        }

        require_once($CFG->dirroot . '/enrol/waitinglist/lib.php');
        require_once($CFG->dirroot . '/enrol/waitinglist/approval/approvallib.php');
    }
    
    /**
     * Show link to enrol form from course->user menu
     * 
     * @return boolean true=show | false = hide
     */
   public static function can_enrol_from_course_admin(){
		//set this to true to show bulkenrol link under course->user menu
		//return true;
		return false;
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
			$rec->{enrolmethodunnamedbulk::MFIELD_SENDCONFIRMMESSAGE}=true;
			$rec->{enrolmethodunnamedbulk::MFIELD_WAITLISTMESSAGE}=get_string('waitlistmessagetext_unnamedbulk','enrol_waitinglist');
			$rec->{enrolmethodunnamedbulk::MFIELD_CONFIRMEDMESSAGE}=get_string('confirmedmessagetext_unnamedbulk','enrol_waitinglist');
			
			//first remove any old entries that might still be kicking around from deleted waitinglists
			$oldrecord = $DB->get_record_sql("SELECT * FROM {".self::TABLE."} WHERE courseid = " . 
		 		$courseid .  " AND " .$DB->sql_compare_text('methodtype') . "='". static::METHODTYPE ."'");
			if($oldrecord){
				$DB->delete_records(self::TABLE, array( 'id'=>$oldrecord->id));
			}
			
			$id = $DB->insert_record(self::TABLE,$rec);
			if($id){
				$rec->id = $id;
				return $rec;
			}else{
				return $id;
			}
    }

    /**
     * @param           $oldWaitId
     * @param           $oldCourse
     * @param           $newWaitId
     * @param           $courseId
     *
     * @throws          \Exception
     *
     * @creationDate    21/10/2016
     * @author          eFaktor     (fbv)
     *
     * Descripiton
     * Restore instance fot bulk-method
     */
    public static function restore_instance($oldWaitId,$oldCourse,$newWaitId,$courseId) {
        /* Variables */
        global $DB;
        $newInstance    = null;
        $oldInstance    = null;
        $params         = null;
        $dbLog          = null;
	 
        try {
            // New Instance
            $newInstance                = new \stdClass();
            $newInstance->courseid      = $courseId;
            $newInstance->waitinglistid = $newWaitId;
            $newInstance->methodtype    = static::METHODTYPE;

            // Old instance
            $params = array();
            $params['waitinglistid'] = $oldWaitId;
            $params['courseid']      = $oldCourse;

            // SQL Instance
            $sql = " SELECT	  ew.*
                     FROM	  {enrol_waitinglist_method}	ew
                        JOIN  {enrol}						e 	ON 	e.id 	= ew.waitinglistid
                                                                AND e.enrol = 'waitinglist'
                     WHERE	  ew.methodtype like '%unnamedbulk%'
                        AND   ew.waitinglistid = :waitinglistid
                        AND   ew.courseid      = :courseid ";
            
            // Execute  - get old instance
            $oldInstance = $DB->get_record_sql($sql,$params);
            if ($oldInstance) {
                // Create a new one from the old one
                $newInstance->status                                                = $oldInstance->status;
                $newInstance->emailalert                                            = $oldInstance->emailalert;
                $newInstance->maxseats                                              = $oldInstance->maxseats;
                $newInstance->cost                                                  = $oldInstance->cost;
                $newInstance->currency                                              = $oldInstance->currency;
                $newInstance->roleid                                                = $oldInstance->roleid;
                $newInstance->password                                              = $oldInstance->password;
                $newInstance->unenrolenddate                                        = $oldInstance->unenrolenddate;
                $newInstance->customint1                                            = $oldInstance->customint1;
                $newInstance->customint2                                            = $oldInstance->customint2;
                $newInstance->customint3                                            = $oldInstance->customint3;
                $newInstance->customint4                                            = $oldInstance->customint4;
                $newInstance->customtext3                                           = $oldInstance->customtext3;
                $newInstance->customtext4                                           = $oldInstance->customtext4;
                $newInstance->customint6                                            = $oldInstance->customint6;
                $newInstance->customint7                                            = $oldInstance->customint7;
                $newInstance->customint8                                            = $oldInstance->customint8;
                $newInstance->customchar1                                           = $oldInstance->customchar1;
                $newInstance->customchar2                                           = $oldInstance->customchar2;
                $newInstance->customchar3                                           = $oldInstance->customchar3;
                $newInstance->customdec1                                            = $oldInstance->customdec1;
                $newInstance->customdec2                                            = $oldInstance->customdec2;
                $newInstance->customdec3                                            = $oldInstance->customdec3;
                $newInstance->{enrolmethodunnamedbulk::MFIELD_SENDCONFIRMMESSAGE}   = $oldInstance->{enrolmethodunnamedbulk::MFIELD_SENDCONFIRMMESSAGE};
                $newInstance->{enrolmethodunnamedbulk::MFIELD_WAITLISTMESSAGE}      = $oldInstance->{enrolmethodunnamedbulk::MFIELD_WAITLISTMESSAGE};
                $newInstance->{enrolmethodunnamedbulk::MFIELD_CONFIRMEDMESSAGE}     = $oldInstance->{enrolmethodunnamedbulk::MFIELD_CONFIRMEDMESSAGE};

                // Execute 
                $newInstance->id = $DB->insert_record('enrol_waitinglist_method',$newInstance);
            }else {
                // New one
                $newInstance->status        = true;
                $newInstance->emailalert    = true;
                $newInstance->{enrolmethodunnamedbulk::MFIELD_SENDCONFIRMMESSAGE}   = true;
                $newInstance->{enrolmethodunnamedbulk::MFIELD_WAITLISTMESSAGE}      = get_string('waitlistmessagetext_unnamedbulk','enrol_waitinglist');
                $newInstance->{enrolmethodunnamedbulk::MFIELD_CONFIRMEDMESSAGE}     = get_string('confirmedmessagetext_unnamedbulk','enrol_waitinglist');
                // Execute
                $newInstance->id = $DB->insert_record('enrol_waitinglist_method',$newInstance);
            }//if_oldInstance
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//restore_instance

    /**
     * Description
     * updated restored instance from the original
     *
     * @creationDate    02/12/2016
     * @author          eFaktor     (fbv)
     * 
     * @param       int $oldWaitingId   Original instance
     * @param       int $oldCourse      Original course
     * @param       int $newWaitingId   New instance
     * @param       int $courseId       New course
     * 
     * @throws          \Exception
     */
    public static function update_restored_instance($oldWaitingId,$oldCourse,$newWaitingId,$courseId) {
        /* Variables */
        global $DB;
        $newInstance    = null;
        $oldInstance    = null;
        $params         = null;
        $newParams      = null;
        $rdo            = null;

        try {
            // SQL Instruction
            $sql = " SELECT	  ew.*
                     FROM	  {enrol_waitinglist_method}	ew
                        JOIN  {enrol}						e 	ON 	e.id 	= ew.waitinglistid
                                                                AND e.enrol = 'waitinglist'
                     WHERE	  ew.methodtype like '%unnamedbulk%'
                        AND   ew.waitinglistid = :waitinglistid
                        AND   ew.courseid      = :courseid ";

            // Get old instance
            // Execute
            $oldInstance = $DB->get_record_sql($sql,array('courseid' => $oldCourse,'waitinglistid' => $oldWaitingId));

            // Get new instance
            // Execute
            $newInstance = $DB->get_record_sql($sql,array('courseid' => $courseId,'waitinglistid' => $newWaitingId));

            if ($oldInstance && $newInstance) {
                $newInstance->maxseats          = $oldInstance->maxseats;
                $newInstance->emailalert        = $oldInstance->emailalert;
                $newInstance->cost              = $oldInstance->cost;
                $newInstance->currency          = $oldInstance->currency;
                $newInstance->roleid            = $oldInstance->roleid;
                $newInstance->password          = $oldInstance->password;
                $newInstance->status            = $oldInstance->status;
                $newInstance->unenrolenddate    = $oldInstance->unenrolenddate;
                $newInstance->customint1        = $oldInstance->customint1;
                $newInstance->customint2        = $oldInstance->customint2;
                $newInstance->customint3        = $oldInstance->customint3;
                $newInstance->customint4        = $oldInstance->customint4;
                $newInstance->customint5        = $oldInstance->customint5;
                $newInstance->customtext1       = $oldInstance->customtext1;
                $newInstance->customtext2       = $oldInstance->customtext2;
                $newInstance->customtext3       = $oldInstance->customtext3;
                $newInstance->customtext4       = $oldInstance->customtext4;
                $newInstance->customint6        = $oldInstance->customint6;
                $newInstance->customint7        = $oldInstance->customint7;
                $newInstance->customint8        = $oldInstance->customint8;
                $newInstance->customchar1       = $oldInstance->customchar1;
                $newInstance->customchar2       = $oldInstance->customchar2;
                $newInstance->customchar3       = $oldInstance->customchar3;
                $newInstance->customdec1        = $oldInstance->customdec1;
                $newInstance->customdec2        = $oldInstance->customdec2;
                $newInstance->customdec3        = $oldInstance->customdec3;

                // Update
                $DB->update_record('enrol_waitinglist_method',$newInstance);
            }
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//update_restored_instance

	 //settings related functions
	 public function has_notifications(){return false;}
	 public  function show_notifications_settings_link(){return false;}
	 public  function has_settings(){return true;}
	 
	 /**
     * Returns maximum enrolable via this enrolment method
	 * Though it is inconsistent, currently a value of 0 = unlimited
	 * This is different to the waitinglist itself, where the value 0 = 0.
	 * A value of 0 here effectively means "as many as the waitinglist method allows."
     *
     * @return int max enrolable
     */
	public function get_max_can_enrol(){
		return $this->{self::MFIELD_MAXENROLLED};
	}
	 
	 //what to do here?????
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
     * of one type exist. In this case probably not, but to maintain the method signature we do it this way
     *
     * @param array $waitinglists all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons(array $waitinglists) {
		  $key=false;
		  foreach ($waitinglists as $waitinglist) {
      		if ($this->can_self_enrol($waitinglist) !== true) {
                // User can not enrol himself.
                // Note that we do not check here if user is already enrolled for performance reasons -
                // such check would execute extra queries for each course in the list of courses and
                // would hide self-enrolment icons from guests.
                continue;
            }
			
            if (false && $this->password) {
				$key=true;
            }
		}
        $icons = array();
		
        if ($key) {
            $icons[] = new \pix_icon('withkey', get_string('pluginname', 'enrol_self'), 'enrol_self');
        }else{
			 $icons[] = new \pix_icon('withoutkey', get_string('pluginname', 'enrol_self'), 'enrol_self');
		}
        return $icons;
    }

    /**
     * @param       \stdClass $waitinglist      enrolment instance
     * @param       bool $checkuserenrolment    if true will check db and queue for user
     *
     * @return      bool|string                 true if successful, else error message or false.
     *
     * Description
     * Checks if user can enrol.
     *
     * @updateDate  29/12/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * If it is an approval method, it'll check if the user has mangers or not.
     * No managers --> CANNOT ENROL
     * 
     * @updateDate  15/09/2016
     * @author      eFaktor     (fbv)
     * 
     * Description
     * The checking for manager moved to enrol_page_hook
     */
    public function can_enrol(\stdClass $waitinglist, $checkuserenrolment = true) {
        /* Variables */
        global $USER,$DB;
        $rejected = null;

        /**
         * @updateDate  26/09/2016
         * @author      eFaktor     (fbv)
         *
         * Description
         * If company is compulsory
         */
        if ($waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL} != COMPANY_NO_DEMANDED) {
            if (!$rdo = $DB->get_records('user_info_competence_data',array('level' => 3,'userid' =>$USER->id),'id')) {
                $urlProfile = new \moodle_url('/user/profile/field/competence/competence.php',array('id' => $USER->id));
                $lnkProfile = "<a href='". $urlProfile . "'>". get_string('profile') . "</a>";
                return get_string('no_competence','enrol_waitinglist',$lnkProfile);
            }
        }

		$entryman =  \enrol_waitinglist\entrymanager::get_by_course($waitinglist->courseid);
		$entry = $entryman->get_entry_by_userid($USER->id);
		if($entry && $entry->methodtype!=static::METHODTYPE){
			return get_string('onlyoneenrolmethodallowed', 'enrol_waitinglist');
		}

        //checking the queue (db calls)
        //to do: turn queuemanager into a singleton, and remove the checkusenrolment condition
         if ($checkuserenrolment) {
            //$entryman =  \enrol_waitinglist\entrymanager::get_by_course($waitinglist->courseid);
         	$queueman =  \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		
			//maximum users for this enrolment method
        	if ($this->{self::MFIELD_MAXENROLLED} > 0 && false) {
				// Max enrol limit specified.
				$count = $queueman->get_listtotal_by_method(static::METHODTYPE);
				//$count = $entryman->get_allocated_listtotal_by_method(static::METHODTYPE);
				if ($count >= $this->{self::MFIELD_MAXENROLLED}) {
					// Bad luck, no more  enrolments here.
					return get_string('noroomonlist', 'enrol_waitinglist');
				}
        	}
        }

		//basic waitinglist and plugin checks (no db calls)
        if (!$this->is_active()) {
            return get_string('canntenrol', 'enrol_self');
        }

        return true;
    }
	

    /**
     * enrol_unnamedbulk
     * @param       \stdClass $waitinglist   enrolment instance
     * @param       null $data               data needed for enrolment.
     * 
     * @return      int|null                 true if enroled else eddor code and messege
     * @throws      \Exception
     * @throws      \enrol_waitinglist\method\coding_exception
     * 
     * @updateDate  15/09/2016
     * @author      eFaktor     (fbv)
     * 
     * Description
     * Add company
     */
    public function waitlistrequest_unnamedbulk(\stdClass $waitinglist, $data = null) {
        /* Variables    */
        global $USER;
        $queue_entry    = null;
        $infoApproval   = null;
        $infoMail       = null;
        $queueid        = null;

        try {
            /**
             * @updateDate  29/12/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Check if it is an approval method or not
             */
            if ($waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL} != APPROVAL_REQUIRED) {
                //prepare additional fields for our queue DB entry
                $queue_entry = new \stdClass;
                $queue_entry->waitinglistid     = $waitinglist->id;
                $queue_entry->courseid          = $waitinglist->courseid;
                $queue_entry->userid            = $USER->id;
                $queue_entry->companyid         = $data->level_3;
                $queue_entry->methodtype        = static::METHODTYPE;
                $queue_entry->timecreated       = time();
                $queue_entry->queueno           = 0;
                $queue_entry->seats             = $data->seats;
                $queue_entry->allocseats        = 0;
                $queue_entry->confirmedseats    = 0;
                $queue_entry->enroledseats      = 0;
                $queue_entry->offqueue          = 0;
                $queue_entry->timemodified      = $queue_entry->timecreated;

                //add the user to the waitinglist queue
                $queueid = $this->add_to_waitinglist($waitinglist, $queue_entry);
            }else {
                list($infoApproval,$infoMail) = \Approval::add_approval_entry($data,$USER->id,$waitinglist->courseid,static::METHODTYPE,$data->seats,$waitinglist->id);
                /* Check Vancancies */
                $wl         = enrol_get_plugin('waitinglist');
                $vacancies  = $wl->get_vacancy_count($waitinglist);
                if ($vacancies) {
                    if (array_key_exists($USER->id,$this->myManagers)) {
                        $infoApproval->action = APPROVED_ACTION;
                        \Approval::apply_action_from_manager($infoApproval);
                    }else {
                        /* Send Mails   */
                        \Approval::send_notifications($USER,$infoMail,$this->myManagers);
                    }
                }//if_vacancies
            }//if_approval

            /**
             * @updateDate  28/10/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Save Invoice Information
             */
            if (enrol_get_plugin('invoice')) {
                if ($waitinglist->{ENROL_WAITINGLIST_FIELD_INVOICE}) {
                    \Invoices::add_invoice_info($data,$USER->id,$waitinglist->courseid,$waitinglist->id);
                }//if_invoice_info
            }

            return $queueid;
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }

    /**
     * @param           $userId
     *
     * @return          null
     * @throws          \Exception
     *
     * @creationDate    15/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get competence data
     */
    public function GetCompetenceData($userId) {
        /* Variables */
        $wl             = null;
        $myCompetence   = null;

        try {
            $wl = enrol_get_plugin('waitinglist');

            $myCompetence = $wl->GetUserCompetenceData($userId);

            return $myCompetence;
        }catch (\Exception $ex) {
            throw $ex;
        }
    }//GetCompetenceData

    /**
     * @param           $reload
     * @param           $invoice
     *
     * @throws          \Exception
     *
     * @creationDate    13/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize Organization Structure
     */
    public static function Init_Organization_Structure($reload,$invoice) {
        /* Variables */
        $wl = null;

        try {
            /* Get plugin */
            $wl = enrol_get_plugin('waitinglist');

            /* Init Organization Structure */
            $wl->Init_Organization_Structure($reload,$invoice);
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Organization_Structure
    
    /**
     * @param           \stdClass $waitinglist
     * @param                     $queue_entry
     *
     * @return                    int
     * @throws           Exception|\Exception
     *
     * @creationDate    30/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * To make accesible the 'add_to_waitinglist' when the request is approved
     */
    public function add_to_waitinglist_from_approval(\stdClass $waitinglist, $queue_entry) {
        /* Variables */
        $queueid = null;

        try {
            $queueid = $this->add_to_waitinglist($waitinglist,$queue_entry);

            return $queueid;
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_to_waitinglist_from_approval

    /**
     * Get the email template to send
     *
     * @param stdClass $waitinglist instance data
     * @param string $message key
     * @return void
     */
    protected function get_email_template($waitinglist,$messagekey='') {
    	if(empty($messagekey)){
			if (trim($this->{static::MFIELD_WAITLISTMESSAGE}) !== '') {
				$message = $this->{static::MFIELD_WAITLISTMESSAGE};
			}else{
				$message = get_string('waitlistmessagetext_' . static::METHODTYPE, 'enrol_waitinglist');
			}
    	}else{
    		$message = $this->{static::MFIELD_CONFIRMEDMESSAGE};
    	}
	     return $message;
    }


/**
     * Move seats off waitinglist and into confirmed list
     *
     * @param stdClass $waitinglist
	 * @param stdClass $queueentry
     * @return boolean success or failure 
     */
	public function graduate_from_list(\stdClass $waitinglist,\stdClass $queue_entry,$giveseats,$changed=null){
		global $DB;
		$entryman= \enrol_waitinglist\entrymanager::get_by_course($waitinglist->courseid);
		$newallocations = 0;
		$success=false;
		
		//if we already have enough of this method type, return false.
    	$entryman= \enrol_waitinglist\entrymanager::get_by_course($waitinglist->courseid);
    	if($this->maxseats>0){
    		$currentcount = $entryman->get_allocated_listtotal_by_method(static::METHODTYPE);
    		if($currentcount + $giveseats >= $this->maxseats){
    			$giveseats = $this->maxseats - $currentcount;
    			if($giveseats<1){return false;}
    		}
    	}

		//do the update, and assess the result.
		$updatedentry = $entryman->confirm_seats($queue_entry->id,$giveseats);
		if($updatedentry){
			$newallocations = $queue_entry->allocseats - $updatedentry->allocseats;
			$success =true;
		}

		//if we have allocated seats, send the user confirmation.
        $user = $DB->get_record('user',array('id'=>$queue_entry->userid));
		if ($newallocations && $this->{static::MFIELD_SENDCONFIRMMESSAGE}) {
			if($user){
				//somehow need to add allocation count here ... or do we?
				$this->email_waitlist_message($waitinglist, $updatedentry,$user, 'confirmation',$changed);
			}
		}

		return $success;
	}


	/**
     * After enroling into course and removing from waiting list. Return here to do any post processing 
     *
     * @param stdClass $waitinglist
	 * @param stdClass $queueentry
     * @return null 
     */
	public function do_post_enrol_actions(\stdClass $waitinglist,\stdClass $queueentry){
		global $DB,$CFG;
		//we should never actually enrol anyone from unnamedbulk into course
		//apart from the chief user, but that happens elsewhere
		//so we should never arrive here.
		return;
	
	}
	

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     * @param       \stdClass   $waitinglist
     * @param                   $flagged
     * 
     * @return                  array
     * @throws      \Exception
     * @throws      \coding_exception
     * @throws      \moodle_exception
     * 
     * @updateDate  15/09/2016
     * @author      eFaktor     (fbv)
     * 
     * Description
     * Selector company. Check managers.
     */
    public function enrol_page_hook(\stdClass $waitinglist, $flagged) {
        global $CFG, $OUTPUT, $USER,$DB;
		$isInvoice = false;

		$queueman= \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		$entryman= \enrol_waitinglist\entrymanager::get_by_course($waitinglist->courseid);
		$entry = $entryman->get_entry_by_userid($USER->id,static::METHODTYPE);
		$updatedentry =false;

		//if we have an unnamedbulk entry, we proceed to show them the edit form
		if($entry){
			$enrolstatus =true;

		//if we don't have an entry we do all the "can_enrol" checks
		}else{
			//if user is flagged as cant be a new enrol, then just exit
			if($flagged){
				return array(false,'');
			}
			$enrolstatus = $this->can_enrol($waitinglist,true);
		}
		
		//get waitlist object and  vacancies count
		 $wl = enrol_get_plugin('waitinglist');
        /**
         * @updateDate  02/09/2016
         * @author      eFaktor     (fbv)
         * 
         * Description
         * calculate the vacancies
         */
         if ($waitinglist->customint2) {
             $vacancies = $waitinglist->customint2 - $entryman->GetOcuppaiedSeats_NotConnectedUser($USER->id,$waitinglist->courseid,$waitinglist->id);
         }else {
             /* Unlimited */
             $vacancies = 1;
         }

        // Don't show enrolment instance form, if user can't enrol using it.
        if (true === $enrolstatus) {
            /**
             * To initialize the organization structure
             */
            if ($waitinglist->{ENROL_WAITINGLIST_FIELD_INVOICE}) {
                $isInvoice = true;
            }
            $this->Init_Organization_Structure(false,$isInvoice);

            $qstatus = new \stdClass;
            $qstatus->hasentry      = false;
            $qstatus->seats         = 0;
            $qstatus->waitlistsize  = $waitinglist->{ENROL_WAITINGLIST_FIELD_WAITLISTSIZE};
            $qstatus->vacancies     = $vacancies;
            $qstatus->assignedseats = 0;
            $qstatus->queueposition = 0;
            $qstatus->waitingseats  = 0;
            $qstatus->companyid     = 0;

            if($entry){
                $qstatus->hasentry  = true;
                $qstatus->seats     = $entry->seats;
                //$qstatus->islast = ($entry->queueno == $queueman->get_entrycount());
                $qstatus->assignedseats = $entry->{static::QFIELD_ASSIGNEDSEATS};
                $qstatus->waitingseats  = $entry->{static::QFIELD_SEATS} - $entry->{static::QFIELD_ASSIGNEDSEATS};
                $qstatus->queueposition = $queueman->get_listposition($entry);
                $qstatus->companyid     = $entry->companyid;
            }

            /**
             * @updateDate  02/12/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Add checking for vacancies and if the user wants to be set on the wait list or no.
             */
            $waitinglistid      = optional_param('waitinglist', 0, PARAM_INT);
            $confirm            = optional_param('confirm', 0, PARAM_INT);
            $toConfirm          = null;
            $infoRequest        = null;

            $remainder  = null;

            if ($waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL} == APPROVAL_REQUIRED) {
                $infoRequest        = \Approval::get_request($USER->id,$waitinglist->courseid,$waitinglist->id);
                $remainder          = \Approval::get_notification_sent($USER->id,$waitinglist->courseid);
            }//

            if (($confirm) || isset($entry->seats)) {
                $toConfirm          =  false;
            }else {
                if ($infoRequest) {
                    $toConfirm = false;
                }else{
                    if (!$vacancies) {
                        $toConfirm      =  true;
                    }else {
                        $toConfirm      =  false;
                    }
                }
            }

            if ($remainder) {
                $form = new enrolmethodunnamedbulk_enrolform(NULL, array($waitinglist,$this,$qstatus,false,$remainder));

                if ($form->is_cancelled()) {
                    redirect($CFG->wwwroot . '/index.php');
                }else if ($form->is_submitted()) {
                    $this->myManagers   = \Approval::managers_connected($USER->id,$infoRequest->companyid);
                    \Approval::send_reminder($USER,$remainder,$waitinglist->id,$this->myManagers);
                    
                    redirect($CFG->wwwroot . '/index.php');
                }

                //begin the output
                ob_start();
                $form->display();
                $output = ob_get_clean();
                $message =$OUTPUT->box($output);
                $ret = array(true,$message);
            }else {
                if ($toConfirm) {
                    $form = new enrolmethodunnamedbulk_enrolform(NULL, array($waitinglist,$this,$qstatus,true,null));

                    if ($form->is_cancelled()) {
                        redirect($CFG->wwwroot . '/index.php');
                    }else if ($form->is_submitted()) {
                        $form = new enrolmethodunnamedbulk_enrolform(NULL, array($waitinglist,$this,$qstatus,false,null));
                    }

                    //begin the output
                    ob_start();
                    $form->display();
                    $output = ob_get_clean();
                    $message =$OUTPUT->box($output);
                    $ret = array(true,$message);
                }else {
                    $form = new enrolmethodunnamedbulk_enrolform(NULL, array($waitinglist,$this,$qstatus,false,null));
                    //check we had an error free submission
                    $data = false;

                    if ($form->is_cancelled()) {
                        redirect($CFG->wwwroot . '/index.php');
                    }else if ($form->is_submitted()) {

                        if($form->is_validated()){
                            $data = $form->get_data();
                        }
                    }

                    /**
                     * @updateDate  14/09/2016
                     * @author      eFaktor     (fbv)
                     *
                     * Description
                     * Check if user can enroll in the case of needing an approval
                     */
                    if ($data) {
                        if ($waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL} == APPROVAL_REQUIRED) {
                            $this->myManagers   = \Approval::managers_connected($USER->id,$data->level_3);
                            if ($this->myManagers) {
                                $enrolstatus = true;
                            }else {
                                $enrolstatus = false;
                            }
                        }else {
                            $enrolstatus = true;
                        }

                        if ($enrolstatus) {
                            if($entry){
                                //if this is an update of user enrol details, process it
                                if($data->seats != $entry->seats){
                                    /**
                                     * @updateDate 02/09/2016
                                     * @auhtor      eFaktor     (fbv)
                                     *
                                     * Description
                                     * Update seats
                                     */
                                    $updatedentry = $entryman->update_seats_bulk($entry->id,$data->seats,$entry->userid,$entry->courseid,$waitinglist,$vacancies);
                                    $actiontaken ='updated';
                                }else{
                                    $actiontaken='nothingchanged';
                                }
                            }else{
                                $actiontaken='updated';
                                //if this is a new enrol form submission, process it
                                $this->waitlistrequest_unnamedbulk($waitinglist, $data);

                                if ($waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL} == APPROVAL_REQUIRED) {
                                    $params = array();
                                    $params['id']   = $USER->id;
                                    $params['co']   = $waitinglist->courseid;

                                    if ($vacancies) {
                                        $params['se'] = 1;
                                    }else {
                                        $params['se'] = 0;
                                    }//if_infoMail

                                    $redirect       = new \moodle_url('/enrol/waitinglist/approval/info.php',$params);
                                    redirect($redirect);
                                }else {
                                    /**
                                     * @updateDate  28/10/2015
                                     * @author      eFaktor     (fbv)
                                     *
                                     * Description
                                     * Save Invoice Information
                                     */
                                    if (enrol_get_plugin('invoice')) {
                                        if ($waitinglist->{ENROL_WAITINGLIST_FIELD_INVOICE}) {
                                            \Invoices::activate_enrol_invoice($USER->id,$waitinglist->courseid,$waitinglist->id);
                                        }//if_invoice_info
                                    }
                                }
                            }//if_entry

                            //in the case that the user has updated their entry, we
                            //might want to process graduations
                            //this already happens in the sequence from "enrol_unnamedbulk"
                            //so we only need to do this for updates

                            if($actiontaken =='updated' && $updatedentry){
                                //if there are vacancies, and we have an updatedentry
                                //and seats was not set to 0, and we are on top of waitinglist
                                //or there is no waitinglist at all ....give some seats
                                /////;
                                //
                                /**
                                 * @updateDate  02/09/2016
                                 * @author      eFaktor     (fbv)
                                 *
                                 * Description
                                 * Mail to inform about the change
                                 */
                                if ($data->seats != $entry->seats) {
                                    if ($entry->queueno=static::OFFQ) {
                                        $this->email_waitlist_message($waitinglist, $updatedentry,$USER, 'confirmation',true);
                                    }else if ($entry->queueno == 1) {
                                        $this->email_waitlist_message($waitinglist, $updatedentry,$USER, '',true);
                                    }
                                }

                            }

                            //Send the user on somewhere
                            $continueurl = new \moodle_url('/enrol/waitinglist/edit_enrolform.php',
                                array('id'=>$waitinglist->courseid,'methodtype'=> static::METHODTYPE));
                            $actionreport = get_string('qentry' . $actiontaken, 'enrol_waitinglist');
                            redirect($continueurl,$actionreport,2);
                        }//if_enrolstatus
                    }//if_data
                    
                    /**
                     * @updateDate  15/09/2016
                     * @author      eFaktor     (fbv)
                     *
                     * Description
                     * If the user can enroll --> Enrolled
                     * Cannot enroll --> Message
                     */
                    if ($enrolstatus) {
                        //prepare our gotocourse button and form data, if required
                        if($entry){
                            $formdata = new \stdClass;
                            $formdata->seats=$entry->seats;
                            $form->set_data($formdata);
                        }

                        //begin the output
                        ob_start();
                        $form->display();
                        $output = ob_get_clean();
                        $message =$OUTPUT->box($output);
                        $ret = array(true,$message);
                    }else {
                        $message = $OUTPUT->box($enrolstatus);
                        $company = \CompetenceManager::GetCompany_Name($data->level_3);
                        $ret = array(false,get_string('not_managers_company','enrol_waitinglist',$company));
                    }
                }
            }//if_reminder

        } else {
			//if the user cant enrol, tell them why
			$message = $OUTPUT->box($enrolstatus);
			$ret = array(false,$message);
        }
        
        return $ret;
    }
}