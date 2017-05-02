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
require_once($CFG->dirroot . '/report/manager/managerlib.php');

class enrolmethodself extends \enrol_waitinglist\method\enrolmethodbase{

	const METHODTYPE='self';
	protected $active = false;
	
	const QFIELD_ENROLPASSWORD          = 'customtext1';
	const MFIELD_GROUPKEY               = 'customint1';
	const MFIELD_LONGTIMENOSEE          = 'customint2';
	const MFIELD_MAXENROLLED            = 'customint3';
	const MFIELD_SENDWAITLISTMESSAGE    = 'customint4';
	const MFIELD_COHORTONLY             = 'customint5';
	const MFIELD_NEWENROLS              = 'customint6';
	const MFIELD_WAITLISTMESSAGE        = 'customtext1';
	const MFIELD_PRICE                  = 'customtext3';

	public  $course             = 0;
    public  $waitlist           = 0;
	public  $maxseats           = 0;
    public  $activeseats        = 0;
	public  $notificationtypes  = 0;
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
     * Description
     * Restore instance for self-enrolment
     */
    public static function restore_instance($oldWaitId,$oldCourse,$newWaitId,$courseId) {
        /* Variables */
        global $DB;
        $newInstance    = null;
        $oldInstance    = null;
        $params         = null;

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
            

            // SQL Instruction
            $sql = " SELECT	  ew.*
                     FROM	  {enrol_waitinglist_method}	ew
                        JOIN  {enrol}						e 	ON 	e.id 	= ew.waitinglistid
                                                                AND e.enrol = 'waitinglist'
                     WHERE	  ew.methodtype like '%self%'
                        AND   ew.waitinglistid = :waitinglistid
                        AND   ew.courseid      = :courseid ";

            // Execute - get old instance
            $oldInstance = $DB->get_record_sql($sql,$params);
            if ($oldInstance) {
                // Create a new one from the old one
                $newInstance->status                                    = $oldInstance->status;
                $newInstance->emailalert                                = $oldInstance->emailalert;
                $newInstance->maxseats                                  = $oldInstance->maxseats;
                $newInstance->cost                                      = $oldInstance->cost;
                $newInstance->currency                                  = $oldInstance->currency;
                $newInstance->roleid                                    = $oldInstance->roleid;
                $newInstance->password                                  = $oldInstance->password;
                $newInstance->unenrolenddate                            = $oldInstance->unenrolenddate;
                $newInstance->customint1                                = $oldInstance->customint1;
                $newInstance->customint2                                = $oldInstance->customint2;
                $newInstance->customint3                                = $oldInstance->customint3;
                $newInstance->customint4                                = $oldInstance->customint4;
                $newInstance->customint5                                = $oldInstance->customint5;
                $newInstance->customtext2                               = $oldInstance->customtext2;
                $newInstance->customtext3                               = $oldInstance->customtext3;
                $newInstance->customtext4                               = $oldInstance->customtext4;
                $newInstance->customint6                                = $oldInstance->customint6;
                $newInstance->customint7                                = $oldInstance->customint7;
                $newInstance->customint8                                = $oldInstance->customint8;
                $newInstance->customchar1                               = $oldInstance->customchar1;
                $newInstance->customchar2                               = $oldInstance->customchar2;
                $newInstance->customchar3                               = $oldInstance->customchar3;
                $newInstance->customdec1                                = $oldInstance->customdec1;
                $newInstance->customdec2                                = $oldInstance->customdec2;
                $newInstance->customdec3                                = $oldInstance->customdec3;
                $newInstance->{enrolmethodself::MFIELD_WAITLISTMESSAGE} = $oldInstance->{enrolmethodself::MFIELD_WAITLISTMESSAGE};

                // Execute
                $newInstance->id = $DB->insert_record('enrol_waitinglist_method',$newInstance);
            }else {
                // Create a new one
                $newInstance->status        = true;
                $newInstance->emailalert    = true;
                $newInstance->{enrolmethodself::MFIELD_WAITLISTMESSAGE} = get_string('waitlistmessagetext_self','enrol_waitinglist');
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
     * 
     */
    public static function update_restored_instance($oldWaitingId,$oldCourse,$newWaitingId,$courseId) {
        /* Variables */
        global $DB;
        $sql            = null;
        $newInstance    = null;
        $oldInstance    = null;
        $rdo            = null;

        try {
            // SQL Instruction
            $sql = " SELECT	  ew.*
                     FROM	  {enrol_waitinglist_method}	ew
                        JOIN  {enrol}						e 	ON 	e.id 	= ew.waitinglistid
                                                                AND e.enrol = 'waitinglist'
                     WHERE	  ew.methodtype like '%self%'
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
	 public function show_notifications_settings_link(){return false;}
	 public function has_settings(){return true;}

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
     * @param array $waitinglists all enrol instances of this type in one course. We are likely to have only one. But maintaining original code, just in case.
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
     * @param           \stdClass   $waitinglist            enrolment instance
     * @param           bool        $checkuserenrolment     if true will check db and queue for user
     *
     * @return          bool|null|string                true if successful, else error message or false.
     * @throws          \Exception
     *
     * Description
     * Checks if user can enrol.
     *
     * @updateDate      28/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * If it is an approval method, it'll check if the user has mangers or not.
     * No managers --> CANNOT ENROL
     *
     * @updateDate      13/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Checking for manager moved to enrol_page_hook
     */
    public function can_enrol(\stdClass $waitinglist, $checkuserenrolment = true) {
        /* Variables */
        global $DB, $USER, $CFG;
        $rejected   = null;

        try {
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
                    //$count = $entryman->get_allocated_listtotal_by_method(static::METHODTYPE);
                    $count = $queueman->get_listtotal_by_method(static::METHODTYPE);
                    if ($count >= $this->{self::MFIELD_MAXENROLLED}) {
                        // Bad luck, no more self enrolments here.
                        return get_string('noroomonlist', 'enrol_waitinglist');
                    }
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


            return true;
        }catch (\Exception $ex) {
            throw $ex;
        }

    }

    /**
     * Self enrol user to course
     * @param           \stdClass $waitinglist
     * @param           null $data
     *
     * @return          bool|array true if enroled else eddor code and messege
     * @throws          \Exception
     * @throws          \enrol_waitinglist\method\coding_exception
     *
     * @updateDate      13/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add company
     */
    public function waitlistrequest_self(\stdClass $waitinglist, $data = null) {
        /* Variables    */
        global $USER;
        $queue_entry    = null;
        $infoApproval   = null;
        $infoMail       = null;

        try {
            // Don't enrol user if password is not passed when required.
            if ($this->password && !isset($data->enrolpassword)) {
                return;
            }

            /**
             * @updateDate  28/12/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Check if it is an approval method or not
             */
            if ($waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL} != APPROVAL_REQUIRED) {
                //prepare additional fields for our queue DB entry
                $queue_entry = new \stdClass;
                $queue_entry->waitinglistid                 = $waitinglist->id;
                $queue_entry->courseid                      = $waitinglist->courseid;
                $queue_entry->userid                        = $USER->id;
                $queue_entry->companyid                     = $data->level_3;
                $queue_entry->methodtype                    = static::METHODTYPE;
                if(!isset($data->enrolpassword)){$data->enrolpassword='';}
                $queue_entry->{self::QFIELD_ENROLPASSWORD}  = $data->enrolpassword;
                $queue_entry->timecreated       = time();
                $queue_entry->queueno           = 0;
                $queue_entry->seats             = 1;
                $queue_entry->allocseats        = 0;
                $queue_entry->confirmedseats    = 0;
                $queue_entry->enroledseats      = 0;
                $queue_entry->offqueue          = 0;
                $queue_entry->timemodified      = $queue_entry->timecreated;

                //add the user to the waitinglist queue
                $queueid = $this->add_to_waitinglist($waitinglist, $queue_entry);
            }else {
                list($infoApproval,$infoMail) = \Approval::add_approval_entry($data,$USER->id,$waitinglist->courseid,static::METHODTYPE,1,$waitinglist->id);
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
     * @creationDate    12/09/2016
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
     * @creationDate    29/12/2015
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
     * Pop off queue and enrol in course
     *
     * @param stdClass $waitinglist
	 * @param stdClass $queueentry
     * @return null 
     */
	public function graduate_from_list(\stdClass $waitinglist,\stdClass $queue_entry, $seats){
        global $CFG;

		$entryman= \enrol_waitinglist\entrymanager::get_by_course($waitinglist->courseid);
		$success =false;
		
		//if we already have enough of this method type, return false.
    	if($this->maxseats>0){
    		$currentcount = $entryman->get_allocated_listtotal_by_method(static::METHODTYPE);
    		if($currentcount + $seats >= $this->maxseats){
    			$giveseats = $this->maxseats - $currentcount;
    			if($giveseats<1){return false;}
    		}
    	}
    	
		//do the update
		$updatedentry = $entryman->confirm_seats($queue_entry->id, $seats);

		if($updatedentry){
			$this->do_post_enrol_actions($waitinglist, $updatedentry);
			$success =true;
		}
		return $success;
	}
	

	/**
     * After enroling into course and removeing from waiting list. Return here to do any post processing 
     *
     * @param stdClass $waitinglist
	 * @param stdClass $queueentry
     * @return null 
     */
	public function do_post_enrol_actions(\stdClass $waitinglist,\stdClass $queueentry){
        /* Variables    */
		global $DB,$CFG, $USER;

        try {
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
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
	}

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param       \stdClass $waitinglist
     * @param       $flagged
     *
     * @return      array
     * @throws      \Exception
     * @throws      \coding_exception
     * @throws      \moodle_exception
     *
     * @updateDate  13/09/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Selector company. Check managers.
     */
    public function enrol_page_hook(\stdClass $waitinglist, $flagged) {
        global $CFG, $OUTPUT, $USER,$SESSION;
        $isInvoice          = false;
        $redirect           = null;
        $ret                = null;

		$queueman= \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		$qdetails = $queueman->get_user_queue_details(static::METHODTYPE);
        if ($waitinglist->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS}) {
            if($qdetails->queueposition > 0 && $qdetails->offqueue == 0){
                $enrolstatus = get_string('yourqueuedetails','enrol_waitinglist', $qdetails);
            }else{
                //if user is flagged as cant be a new enrol, then just exit
                if($flagged){
                    return array(false,'');
                }
                $enrolstatus = $this->can_enrol($waitinglist,true);
            }
        }else {
            //if user is flagged as cant be a new enrol, then just exit
            if($flagged){
                return array(false,'');
            }
            $enrolstatus = $this->can_enrol($waitinglist,true);
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

        	$listtotal = $queueman->get_listtotal();

            $waitinglistid  = optional_param('waitinglist', 0, PARAM_INT);
            /**
             * @updateDate  02/12/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Add checking for vacancies and if the user wants to be set on the wait list or no.
             */
            $plugin         = enrol_get_plugin('waitinglist');
            $vacancies      = $plugin->get_vacancy_count($waitinglist);
            $confirm        = optional_param('confirm', 0, PARAM_INT);
            $toConfirm      = null;
            $remainder      = null;
            $infoRequest    = null;

            if ($waitinglist->{ENROL_WAITINGLIST_FIELD_APPROVAL} == APPROVAL_REQUIRED) {
                $remainder          = \Approval::get_notification_sent($USER->id,$waitinglist->courseid);
                $infoRequest        = \Approval::get_request($USER->id,$waitinglist->courseid,$waitinglist->id);
            }

            if ($confirm) {
                $toConfirm      =  false;
            }else {
                if ($infoRequest) {
                    $toConfirm = false;
                }else {
                    if (!$vacancies) {
                        $toConfirm  =  true;
                    }else {
                        $toConfirm  =  false;
                    }
                }
            }

            if ($remainder) {
                $form = new enrolmethodself_enrolform(NULL, array($waitinglist,$this,$listtotal,false,$remainder));

                if ($form->is_cancelled()) {
                    $redirect = $CFG->wwwroot . '/index.php';
                    
                    redirect($redirect);
                }else if ($form->is_submitted()) {
                    $this->myManagers   = \Approval::managers_connected($USER->id,$infoRequest->companyid);
                    \Approval::send_reminder($USER,$remainder,$waitinglist->id,$this->myManagers);

                    $redirect = $CFG->wwwroot . '/index.php';
                    redirect($redirect);
                }

                //begin the output
                ob_start();
                $form->display();
                $output = ob_get_clean();
                $message =$OUTPUT->box($output);
                $ret = array(true,$message);
            }else {
                if ($toConfirm) {
                    $form = new enrolmethodself_enrolform(NULL, array($waitinglist,$this,$listtotal,true,null));

                    if ($form->is_cancelled()) {
                        $redirect = $CFG->wwwroot . '/index.php';
                        redirect($redirect);
                    }else if ($form->is_submitted()) {
                        $form = new enrolmethodself_enrolform(NULL, array($waitinglist,$this,$listtotal,false,null));
                    }

                    //begin the output
                    ob_start();
                    $form->display();
                    $output = ob_get_clean();
                    $message =$OUTPUT->box($output);
                    $ret = array(true,$message);
                }else {
                    $form = new enrolmethodself_enrolform(NULL, array($waitinglist,$this,$listtotal,false,null));

                    if ($form->is_cancelled()) {
                        $redirect = $CFG->wwwroot . '/index.php';
                        redirect($redirect);
                    }else if ($data = $form->get_data()) {
                        /**
                         * @updateDate  14/09/2016
                         * @author      eFaktor     (fbv)
                         *
                         * Description
                         * Check if user can enroll in the case of needing an approval
                         */
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
                            $this->waitlistrequest_self($waitinglist, $data);

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
                                }//if_enrol_invoice
                            }//Approval_method
                        }//if_enrolstatus
                    }//if_form

                    /**
                     * @updateDate  14/09/2016
                     * @author      eFaktor     (fbv)
                     * 
                     * Description
                     * If the user can enroll --> Enrolled
                     * Cannot enroll --> Message
                     */
                    if ($enrolstatus) {
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
                }//if_toConfirm
            }//if_else
        } else {
            $message = $OUTPUT->box($enrolstatus);
			$ret = array(false,$message);
        }

        return $ret;
    }
}
