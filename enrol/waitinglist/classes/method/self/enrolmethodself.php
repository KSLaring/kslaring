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

require_once($CFG->dirroot . '/enrol/waitinglist/lib.php');

class enrolmethodself extends \enrol_waitinglist\method\enrolmethodbase{

	const METHODTYPE='self';
	protected $active = false;
	
	const QFIELD_ENROLPASSWORD='customtext1';
	const MFIELD_GROUPKEY = 'customint1';
	const MFIELD_LONGTIMENOSEE = 'customint2';
	const MFIELD_MAXENROLLED = 'customint3';
	const MFIELD_SENDWAITLISTMESSAGE = 'customint4';
	const MFIELD_COHORTONLY = 'customint5';
	const MFIELD_NEWENROLS = 'customint6';
	const MFIELD_WAITLISTMESSAGE = 'customtext1';
	
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
				$DB->delete_record(self::TABLE, array( 'id'=>$oldrecord->id));
			}
			
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
     * Checks if user can enrol.
     *
     * @param stdClass $waitinglist enrolment instance
     * @param bool $checkuserenrolment if true will check db and queue for user
     * @return bool|string true if successful, else error message or false.
     */
    public function can_enrol(\stdClass $waitinglist, $checkuserenrolment = true) {
        global $DB, $USER, $CFG;
        
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
    }
	
	   /**
     * Self enrol user to course
     *
     * @param stdClass $waitinglist enrolment instance
     * @param stdClass $data data needed for enrolment.
     * @return bool|array true if enroled else eddor code and messege
     */
    public function waitlistrequest_self(\stdClass $waitinglist, $data = null) {
        global $DB, $USER, $CFG;
		
		
        // Don't enrol user if password is not passed when required.
        if ($this->password && !isset($data->enrolpassword)) {
            return;
        }

		//prepare additional fields for our queue DB entry
		$queue_entry = new \stdClass;
		$queue_entry->waitinglistid      = $waitinglist->id;
		$queue_entry->courseid       = $waitinglist->courseid;
		$queue_entry->userid       = $USER->id;
		$queue_entry->methodtype   = static::METHODTYPE;
		if(!isset($data->enrolpassword)){$data->enrolpassword='';}
		$queue_entry->{self::QFIELD_ENROLPASSWORD}=$data->enrolpassword;
		$queue_entry->timecreated  = time();
		$queue_entry->queueno = 	0;
		$queue_entry->seats = 1;
		$queue_entry->allocseats = 0;
		$queue_entry->confirmedseats = 0;
		$queue_entry->enroledseats = 0;
		$queue_entry->offqueue = 0;
		$queue_entry->timemodified = $queue_entry->timecreated;
		
		//add the user to the waitinglist queue 
        $queueid = $this->add_to_waitinglist($waitinglist, $queue_entry);

    }
    
    
    /**
     * Pop off queue and enrol in course
     *
     * @param stdClass $waitinglist
	 * @param stdClass $queueentry
     * @return null 
     */
	public function graduate_from_list(\stdClass $waitinglist,\stdClass $queue_entry, $seats){
		$entryman= \enrol_waitinglist\entrymanager::get_by_course($waitinglist->courseid);
		$success =false;
		
		//if we already have enough of this method type, return false.
    	if($this->maxseats>0){
    		$currentcount = $entryman->get_allocated_listtotal_by_method(static::METHODTYPE);
    		if($currentcount + $giveseats >= $this->maxseats){
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
		global $DB,$CFG;
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
	
	}

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $waitinglist
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(\stdClass $waitinglist, $flagged) {
        global $CFG, $OUTPUT, $USER;
		
		$queueman= \enrol_waitinglist\queuemanager::get_by_course($waitinglist->courseid);
		$qdetails = $queueman->get_user_queue_details(static::METHODTYPE);
		if($qdetails->queueposition > 0 && $qdetails->offqueue == 0){
			$enrolstatus = get_string('yourqueuedetails','enrol_waitinglist', $qdetails);
		}else{
			//if user is flagged as cant be a new enrol, then just exit
			if($flagged){
				return array(false,'');
			}				
			$enrolstatus = $this->can_enrol($waitinglist,true);
		}

        // Don't show enrolment instance form, if user can't enrol using it.
        if (true === $enrolstatus) {
        	$listtotal = $queueman->get_listtotal();
            $form = new enrolmethodself_enrolform(NULL, array($waitinglist,$this,$listtotal));
            $waitinglistid = optional_param('waitinglist', 0, PARAM_INT);
            if ($waitinglist->id == $waitinglistid) {
            	//if this is an enrol form submission, process it
                if ($data = $form->get_data()) {
                    $this->waitlistrequest_self($waitinglist, $data);
                    redirect($CFG->wwwroot . '/course/view.php?id=' . $waitinglist->courseid);
                }
            }

            ob_start();
            $form->display();
            $output = ob_get_clean();
    
            $message =$OUTPUT->box($output);
			$ret = array(true,$message);
        } else {
            $message = $OUTPUT->box($enrolstatus);
			$ret = array(false,$message);
        }
        return $ret;
    }
}
