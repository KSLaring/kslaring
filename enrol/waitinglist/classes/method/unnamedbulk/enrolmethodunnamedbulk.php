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

class enrolmethodunnamedbulk extends \enrol_waitinglist\method\enrolmethodbase {

	const METHODTYPE='unnamedbulk';
	protected $active = false;
	
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
	const QFIELD_ASSIGNEDSEATS = 'customint1';
	
	
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
    }
    
   public static function can_enrol_from_course_admin(){
		return true;
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
			$rec->{enrolmethodunnamedbulk::MFIELD_WAITLISTMESSAGE}=get_string('welcometowaitlisttext_unnamedbulk','enrol_waitinglist');
			$rec->{enrolmethodunnamedbulk::MFIELD_CONFIRMEDMESSAGE}=get_string('confirmedmessagetext_unnamedbulk','enrol_waitinglist');
			$id = $DB->insert_record(self::TABLE,$rec);
			if($id){
				$rec->id = $id;
				return $rec;
			}else{
				return $id;
			}
    }

	 
	 
	 //settings related functions
	 public function has_notifications(){return false;}
	 public  function show_notifications_settings_link(){return false;}
	 public  function has_settings(){return true;}
	 
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
     * of one type exist. One waitinglist may also produce several icons.
     *
     * @param array $waitinglists all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons(array $waitinglists) {
        $icons = array();
        return $icons;
    }
	
	 /**
     * Checks if user can self enrol.
     * used for displaying icons and links on course list page
     **
     */
	public  function can_self_enrol(\stdClass $waitinglist){
		
		return false;
	
	}
	
	/**
     * Can we auto take a user from waitlist and put onto course?
     ** for unnamed bulk .... NO
     */
	public  function can_enrol_directly(){
		return false;
	}
	
	
	  /**
     * Checks if user can enrol.
     *
     * @param stdClass $waitinglist enrolment instance
     * @param bool $checkuserenrolment if true will check db and queue for user
     * @return bool|string true if successful, else error message or false.
     */
    public function can_enrol(\stdClass $waitinglist, $checkuserenrolment = true) {
        global $DB, $USER, $CFG;
        
        //do we have bulk enrol capability
         $context = \context_course::instance($waitinglist->courseid);
        if (!has_capability('enrol/waitinglist:canbulkenrol', $context)) {
        	return get_string('insufficientpermissions', 'enrol_waitinglist');
        }

		//checking enroled in course, (db calls)
        if ($checkuserenrolment) {
            if (isguestuser()) {
                // Can not enrol guest.
                return get_string('noguestaccess', 'enrol');
            }
        }
        
        //checking the queue (db calls)
        //to do: turn queuemanager into a singleton, and remove the checkusenrolment condition
         if ($checkuserenrolment) {
         	$queueman =  \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		
			//maximum users for this enrolment method
        	if ($this->{self::MFIELD_MAXENROLLED} > 0) {
				// Max enrol limit specified.
				//$count = $this->count_users_on_list();
				$count = $queueman->get_listtotal_by_method(static::METHODTYPE);
				if ($count >= $this->{self::MFIELD_MAXENROLLED}) {
					// Bad luck, no more  enrolments here.
					return get_string('noroomonlist', 'enrol_waitinglist');
				}
        	}
		
			//is waiting list is full
			if ($queueman->is_full()){
					return  get_string('noroomonlist', 'enrol_waitinglist');
			}
        }

		//basic waitinglist and plugin checks (no db calls)
        if (!$this->is_active()) {
            return get_string('canntenrol', 'enrol_self');
        }
        if ($waitinglist->enrolstartdate != 0 and $waitinglist->enrolstartdate > time()) {
			return get_string('canntenrol', 'enrol_self');
        }
        if ($waitinglist->enrolenddate != 0 and $waitinglist->enrolenddate < time()) {
			return get_string('canntenrol', 'enrol_self');
        }


        return true;
    }
	
	   /**
     * enrol_unnamedbulk
     *
     * @param stdClass $waitinglist enrolment instance
     * @param stdClass $data data needed for enrolment.
     * @return bool|array true if enroled else eddor code and messege
     */
    public function enrol_unnamedbulk(\stdClass $waitinglist, $data = null) {
        global $DB, $USER, $CFG;
		
		//prepare additional fields for our queue DB entry
		//we need at least one, so we set an empty string for password if necessary
		//prepare additional fields for our queue DB entry
		//we need at least one, so we set an empty string for password if necessary
		$queue_entry = new \stdClass;
		$queue_entry->waitinglistid      = $waitinglist->id;
		$queue_entry->courseid       = $waitinglist->courseid;
		$queue_entry->userid       = $USER->id;
		$queue_entry->methodtype   = static::METHODTYPE;
		$queue_entry->timecreated  = time();
		$queue_entry->queueno = 	0;
		$queue_entry->seats = $data->seats;
		$queue_entry->timemodified = $queue_entry->timecreated;
		
		
		//add the user to the waitinglist queue 
		$queueid = $this->add_to_waitinglist($waitinglist,$queue_entry );
		
		return $queueid;
    }
	
	/**
     * Message user that they have been allocated seats and need to do something about it
     *
     * @param stdClass $waitinglist
	 * @param stdClass $queueentry
     * @return null 
     */
	public function graduate_from_list(\stdClass $waitinglist,\stdClass $queue_entry){
		$queueman= \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		$wl = enrol_get_plugin('waitinglist');
		$wl->enrol_user($wlinstance,$queue_entry->userid);
		$this->do_post_enrol_actions($wlinstance, $queue_entry);
		$queueman->remove_entry($queue_entry->id);
		return true;
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
		//so we should never really arrive here.
		return;
		
		/*
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
        */
	
	}

	
	  /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $waitinglist
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(\stdClass $waitinglist) {
        global $CFG, $OUTPUT, $USER,$DB;
		
		$queueman= \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		$qentry = $queueman->get_qentry_by_userid($USER->id,static::METHODTYPE);
		
		if($qentry){
			$enrolstatus =true;
		}else{				
			$enrolstatus = $this->can_enrol($waitinglist,true);
		}

        // Don't show enrolment instance form, if user can't enrol using it.
        if (true === $enrolstatus) {	
			$qstatus = new \stdClass;
			$qstatus->seats=0;
			$qstatus->assignedseats=0;
			$qstatus->queueposition=0;
			if($qentry){
				$qstatus->seats = $qentry->seats;
				$qstatus->assignedseats=$qentry->{static::QFIELD_ASSIGNEDSEATS};
				$qstatus->queueposition=$queueman->get_listposition($qentry);
			}
            $form = new enrolmethodunnamedbulk_enrolform(NULL, array($waitinglist,$this,$qstatus));
			
            $waitinglistid = optional_param('waitinglist', 0, PARAM_INT);
            if ($waitinglist->id == $waitinglistid) {
                if ($data = $form->get_data()) {
					 $this->enrol_unnamedbulk($waitinglist, $data);
                    //$this->enrol_self($waitinglist, $data);
					//add to waiting list or something;
                     redirect($CFG->wwwroot . '/enrol/waitinglist/edit_enrolform.php?id=' . $waitinglist->courseid . '&methodtype=' . static::METHODTYPE);
                }
            }

			if($qentry){
				$formdata = new \stdClass;
				$formdata->seats=$qentry->seats;
				$form->set_data($formdata);
			}
			//begin the output
            ob_start();
            $form->display();
            $output = ob_get_clean();
            
            return $OUTPUT->box($output);
        } else {
			//in this case don't show anything, regular joes don't need to see it
            //return $OUTPUT->box($enrolstatus);
        }
    }
	 
	
	
    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $waitinglist
     * @return string html text, usually a form in a text box
     */
    public function xenrol_page_hook(\stdClass $waitinglist) {
        global $CFG, $OUTPUT, $USER,$DB;
		
		//this is checked in lib.php before calling enrol_page_hook
		//so we don't need it here
        //$enrolstatus = $this->can_enrol($waitinglist,true);
        $enrolstatus  =true;

        // Don't show enrolment instance form, if user can't enrol using it.
        if (true === $enrolstatus) {

            $form = new enrolmethodunnamedbulk_enrolform(NULL, array($waitinglist,$this));
			
            $waitinglistid = optional_param('waitinglist', 0, PARAM_INT);
            if ($waitinglist->id == $waitinglistid) {
                if ($data = $form->get_data()) {
					$data->userid=$USER->id;
					$data->waitinglistid= $waitinglist->id;
					$data->courseid= $waitinglist->courseid;
					$data->methodtype=static::METHODTYPE;
					if(!$data->datarecordid){
						$DB->insert_record(self::DATATABLE, $data);
					}else{
						$data->id = $data->datarecordid;
						unset($data->datarecordid);
						$DB->update_record(self::DATATABLE, $data);
					}
                    //$this->enrol_self($waitinglist, $data);
					//add to waiting list or something;
                     redirect($CFG->wwwroot . '/enrol/index.php?id=' . $waitinglist->courseid);
                }
            }
			//pick up any existing data we have set
			$strictness = IGNORE_MULTIPLE;	
			$formdata = $DB->get_record_sql("SELECT * FROM {".self::DATATABLE."} WHERE courseid = $waitinglist->courseid AND " . 
				$DB->sql_compare_text('methodtype') . "='". static::METHODTYPE . "' " . 
				" AND waitinglistid = $waitinglist->id AND userid = $USER->id",null,$strictness);	

			if($formdata){
				$formdata->datarecordid=$formdata->id;
				unset($formdata->id);
				$form->set_data($formdata);
			}
			//begin the output
            ob_start();
            $form->display();
            $output = ob_get_clean();
            
            return $OUTPUT->box($output);
        } else {
            return $OUTPUT->box($enrolstatus);
        }
    }
	 

}
