<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Waitinglist enrolment plugin main library file.
 *
 * @package    enrol_waitinglist
 * @copyright  2015 Justin Hunt {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('ENROL_WAITINGLIST_FIELD_CUTOFFDATE', 'customint1');
define('ENROL_WAITINGLIST_FIELD_MAXENROLMENTS', 'customint2');
//define('ENROL_WAITINGLIST_FIELD_WAITLISTSIZE', 'customint3');
define('ENROL_WAITINGLIST_FIELD_WAITLISTSIZE', 'customint6');
define('ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE', 'customint4');
define('ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE', 'customint5');
define('ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE', 'customtext1');
define('ENROL_WAITINGLIST_FIELD_WAITLISTMESSAGE', 'customtext2');
define('ENROL_WAITINGLIST_TABLE_QUEUE', 'enrol_waitinglist_queue');
define('ENROL_WAITINGLIST_TABLE_METHODS', 'enrol_waitinglist_method');
define('ENROL_WAITINGLIST_FIELD_INVOICE','customint8');
define('ENROL_WAITINGLIST_FIELD_APPROVAL','customint7');
/**
 * @updateDate  21/06/2016
 * @author      eFaktor (fbv)
 *
 * Description
 * Course price split in internal and external prices.
 */
define('ENROL_WAITINGLIST_FIELD_INTERNAL_PRICE','customtext3');
define('ENROL_WAITINGLIST_FIELD_EXTERNAL_PRICE','customtext4');

/**
 * @updateDate  26/09/2016
 * @author      eFaktor     (fbv)
 *
 * Description
 * Add an extra option no demand company
 */
define('APPROVAL_NONE',0);
define('APPROVAL_REQUIRED',1);
define('APPROVAL_MESSAGE',2);
define('COMPANY_NO_DEMANDED',3);
define('REDIRECT','redirect');

class enrol_waitinglist_plugin extends enrol_plugin {

    protected $lasternoller = null;
    protected $lasternollerinstanceid = 0;

	
	public static function get_method_names(){
		//return array('self','unnamedbulk','namedbulk','selfconfirmation','paypal');
		return array('self','unnamedbulk','manual');
	}

    public function roles_protected() {
        // Users may tweak the roles later.
        return false;
    }

	/*
	* We do not allow manual enrolments
	*/
    public function allow_enrol(stdClass $instance) {
        // Users with enrol cap may unenrol other users waitinglistly waitinglistly.
        return false;
    }

    public function allow_unenrol(stdClass $instance) {
        // Users with unenrol cap may unenrol other users waitinglistly waitinglistly.
        return true;
    }

    public function allow_manage(stdClass $instance) {
        // Users with manage cap may tweak period and status.
        return true;
    }
	
	/**
     * Returns all the waiting list enrolment methods we have
     *
     * @return array array of waitlinglist enrolment methods
     */
	public static function get_methods($course, $waitinglistid = false){
		$methods=array();
		foreach(self::get_method_names() as $methodtype){
		 $class = '\enrol_waitinglist\method\\' . $methodtype. '\enrolmethod' .$methodtype ;
		   if (class_exists($class)){
				$themethod = $class::get_by_course($course->id, $waitinglistid); 
				if($themethod){$methods[$methodtype]=$themethod;}
		   }
		}
		return $methods;
	}



    /**
     * Returns enrolment instance manage link.
     *
     * By defaults looks for manage.php file and tests for manage capability.
     *
     * @param navigation_node $instancesnode
     * @param stdClass $instance
     * @return moodle_url;
     */
  
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'waitinglist') {
             throw new coding_exception('Invalid enrol instance type!');
        }

        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/waitinglist:manage', $context)) {
            $managelink = new moodle_url('/enrol/waitinglist/edit.php', array('courseid'=>$instance->courseid));
            $waitinglistnode = $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        
        	//add manage links
        	//methods
        	$managelink=new moodle_url('/enrol/waitinglist/managemethods.php', array('id'=>$instance->courseid));
        	$waitinglistnode->add(get_string('managemethods','enrol_waitinglist'), $managelink, navigation_node::TYPE_SETTING);
            /**
             * @updateDate  18/08/2016
             * @author      eFaktor     (fbv)
             * 
             * Description
             * Add option to enrol users manually
             */
            global $DB;
            $str_title      = get_string('manual_manage','enrol_waitinglist');
            $managelink = new moodle_url('/enrol/waitinglist/managemanual.php',array('id' => $instance->id,'co' => $instance->courseid));
            $manual_enrol   = navigation_node::create($str_title,
                                                      $managelink,
                                                      navigation_node::TYPE_SETTING,'manual_enrol',
                                                      'manual_enrol',
                                                      new pix_icon('i/report', $str_title)
                                                     );
            /* Check if is active or not    */
            $sql = " SELECT status
                     FROM   {enrol_waitinglist_method} 
                     WHERE  waitinglistid = :wait
                        AND courseid = :course
                        AND methodtype LIKE 'manual' ";

            $rdo = $DB->get_record_sql($sql,array('wait' => $instance->id,'course' => $instance->courseid ));
            if ($rdo) {
            if ($rdo->status) {
                $waitinglistnode->add_node($manual_enrol);
            }
            }

            //queue
        	$managelink=new moodle_url('/enrol/waitinglist/managequeue.php', array('id'=>$instance->courseid));
        	$waitinglistnode->add(get_string('managequeue','enrol_waitinglist'), $managelink, navigation_node::TYPE_SETTING);
        	//queue
        	$managelink=new moodle_url('/enrol/waitinglist/manageconfirmed.php', array('id'=>$instance->courseid));
        	$waitinglistnode->add(get_string('manageconfirmed','enrol_waitinglist'), $managelink, navigation_node::TYPE_SETTING);
 
        }
        //add bulk enrol links to menu if have permission
		 $context = context_course::instance($instance->courseid);
		if (has_capability('enrol/waitinglist:canbulkenrol', $context)) {
			$course = get_course($instance->courseid);
			$methods = $this->get_methods($course, $instance->id);
			$usersnode = $instancesnode->parent;
			foreach($methods as $method){
				if($method->can_enrol_from_course_admin() && $method->is_active()){
					$managelink=new moodle_url('/enrol/waitinglist/edit_enrolform.php', array('id'=>$instance->courseid, 'methodtype'=>$method->get_methodtype()));
					$usersnode->add(get_string($method->get_methodtype() . '_menutitle','enrol_waitinglist'), $managelink, navigation_node::TYPE_SETTING);
				}//end of if
			}//end of for each
		}//end of if has capability

        /* Add Report Invoice Link */
        if ((has_capability('enrol/waitinglist:manage', $context) || has_capability('enrol/waitinglist:manage', $context)) && $instance->{ENROL_WAITINGLIST_FIELD_INVOICE}) {
            $parent_node        = $instancesnode->parent;
            $parent_node        = $parent_node->parent;
            $str_title          = get_string('report_link', 'enrol_invoice');
            $url                = new moodle_url('/enrol/invoice/report/report_invoice.php',array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $report_invoices    = navigation_node::create($str_title,
                                                          $url,
                                                          navigation_node::TYPE_SETTING,'report_invoices',
                                                          'report_invoices',
                                                          new pix_icon('i/report', $str_title)
                                                         );

            global $PAGE;
            if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                $report_invoices->make_active();
            }
            $parent_node->add_node($report_invoices,'users');
        }//Invoice_Link

        /* Add Approval Requests Link */
        if ((has_capability('enrol/waitinglist:manage', $context)) && ($instance->{ENROL_WAITINGLIST_FIELD_APPROVAL} == APPROVAL_REQUIRED)) {
            $parent_node        = $instancesnode->parent;
            $parent_node        = $parent_node->parent;
            $str_title          = get_string('lnk_approval', 'enrol_waitinglist');
            $url                = new moodle_url('/enrol/waitinglist/approval/request.php',array('courseid'=>$instance->courseid, 'id'=>$instance->id));

            $approvalRequests   = navigation_node::create($str_title,
                                                          $url,
                                                          navigation_node::TYPE_SETTING,'approval_requests',
                                                          'approval_requests',
                                                          new pix_icon('i/report', $str_title)
                                                         );

            global $PAGE;
            if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
                $approvalRequests->make_active();
            }
            $parent_node->add_node($approvalRequests,'users');
        }//Approval_Requests_Link
    }//end of function

    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/waitinglist:config', $context);
    }
    
    /**
     * Returns edit icons for the page with list of instances.
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'waitinglist') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();
/*
        if (has_capability('enrol/waitinglist:enrol', $context) or has_capability('enrol/waitinglist:unenrol', $context)) {
            $managelink = new moodle_url("/enrol/waitinglist/manage.php", array('enrolid'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($managelink, new pix_icon('t/enrolusers', get_string('enrolusers', 'enrol_waitinglist'), 'core', array('class'=>'iconsmall')));
        }
 */
		if (has_capability('enrol/waitinglist:config', $context)) {
			//edit settings
            $editlink = new moodle_url("/enrol/waitinglist/edit.php", array('courseid'=>$instance->courseid));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit'), 'core',
                    array('class' => 'iconsmall')));
			//manage queue
			$queuelink = new moodle_url("/enrol/waitinglist/managequeue.php", array('id'=>$instance->courseid));
			$icons[] = $OUTPUT->action_icon($queuelink, new pix_icon('t/groupv', get_string('managequeue', 'enrol_waitinglist' ), 'core',
                    array('class' => 'iconsmall')));
			//manage subplugins
			$methodslink = new moodle_url("/enrol/waitinglist/managemethods.php", array('id'=>$instance->courseid));
			$icons[] = $OUTPUT->action_icon($methodslink, new pix_icon('i/mnethost', get_string('managemethods','enrol_waitinglist'), 'core',
                    array('class' => 'iconsmall')));
        }
		

        return $icons;
    }

	public function can_enrol_directly($instance){
		global $DB;
		//there probably wull be cases where we set the max enrolments to 0, to buffer until a particular start date
		if ($instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS} > 0) {
            // Max enrol limit specified.
            $vacancies = $this->get_vacancy_count($instance);
			$queueman= \enrol_waitinglist\queuemanager::get_by_course($instance->courseid);
            if ($vacancies && $queueman->get_listtotal() <1) {
                return true;
            }else{
				return false;
			}
        }
		return true;
	}
	
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/waitinglist:config', $context);
    }

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
    public function get_info_icons(array $instances) {
       $info_icons = array();
	   if(empty($instances)){return $info_icons;}
	   foreach($instances as $instance){
		$course = get_course($instance->courseid);
		break;
	   }
		$methods = $this->get_methods($course, $instance->id);
		if(!$methods){return array();}
		foreach($methods as $method){
			if($method->is_active()){
				$info_icons = array_merge($info_icons, $method->get_info_icons($instances));
			}
		}
		return $info_icons;
    }

	
    /**
     * Checks if user can enrol. This is a general check
     * specific checks are done by the enrolment method according to its rules
     *
     * @param stdClass $waitinglist enrolment instance
     * @return bool|string true if successful, else error message or false.
     */
    public function can_enrol(stdClass $instance) {
        global $DB, $USER, $CFG;
        
		if (isguestuser()) {
			// Can not enrol guest.
			return get_string('noguestaccess', 'enrol');
		}
		
		$queueman =  \enrol_waitinglist\queuemanager::get_by_course($instance->courseid);
		//is waiting list is full
		if ($queueman->is_full()){
				return  get_string('noroomonlist', 'enrol_waitinglist');
		}

		$rightnow = time();
		//We have not implemented an enrolment period on the UI
		//and we used a custom field for the cut off date, so these are never used.
		/*
        if ($instance->enrolstartdate != 0 and $instance->enrolstartdate > $rightnow) {
			return get_string('enrolmentsnotyet', 'enrol_waitinglist');
        }
        if ($instance->enrolenddate != 0 and $instance->enrolenddate < $rightnow) {
			return get_string('enrolmentsclosed', 'enrol_waitinglist');
        }
        */
        
        //we did implement a cut off date
        /**
         * @updateDate  17/02/2016
         * @author      eFaktor     (fbv)
         *
         * Description
         * Check deadline date to enrol
         */
        if ($instance->{ENROL_WAITINGLIST_FIELD_CUTOFFDATE}) {
            $cutDate = $instance->{ENROL_WAITINGLIST_FIELD_CUTOFFDATE} + (24*60*60);

            if ($cutDate < $rightnow ) {
                return get_string('enrolmentsclosed', 'enrol_waitinglist');
            }
        }

        //if ($instance->{ENROL_WAITINGLIST_FIELD_CUTOFFDATE} && $instance->{ENROL_WAITINGLIST_FIELD_CUTOFFDATE} < $rightnow) {
        //    return get_string('enrolmentsclosed', 'enrol_waitinglist');
        //}
        
        //check if already enroled
        $enrolled = $DB->record_exists('user_enrolments', array('enrolid' => $instance->id, 'userid'=>$USER->id));
        if($enrolled){
        	return get_string('alreadyenroled', 'enrol_waitinglist');
        }
        
        //We dont perform this check here.This is because the enrolment method should do this and return some sort of
        //status report or edit form to display.
        /*
        $waiting = $DB->record_exists('enrol_waitinglist_queue', array('waitinglistid' => $instance->id, 'userid'=>$USER->id));
        if($waiting){
        	return get_string('alreadyonlist', 'enrol_waitinglist');
        }
        */
        
        
        return true;
    }
	
	
	  /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        $course = get_course($instance->courseid);
        $can_html = array();
        $cant_html = array();
        
         //basic checks for can user be a fresh new enrol
         //even if they fail we need to go to method checks
         //because if user is on waitinglist, we may need to show a status or edit form
        $ret = $this->can_enrol($instance);
        $flagged= false;
        if($ret !== true){
        	$cant_html[]=$ret;
        	$flagged= true;
        }
       
		//Loop through the methods and get the enrol methods enrol form, edit form or status
		$methods = $this->get_methods($course, $instance->id);
		foreach($methods as $method){
			if(!$method->is_active() ){continue;}
			if (list($canenrol,$hook) = $method->enrol_page_hook($instance, $flagged)) {
				if($canenrol){
					$can_html[]= $hook;
				}else{
					$cant_html[]= $hook;
				}
			}
		}
		
		
		//return our results to show on page
		//if we have one can, we show it.
		// if we have no cans, we show the cants 
		if(!empty($can_html)){
			return implode($can_html);
		}elseif(!empty($cant_html)){
			return implode($cant_html);
		}else{
			return null;
		}
    }
	
	
	/**
     * Do we show an enrol me link?
     *
     * @param array $instances all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function show_enrolme_link(stdClass $instance) {
        if($instance->status == ENROL_INSTANCE_ENABLED){
			return $this->can_self_enrol($instance);
		}
		return false;
    }
    
    
    /**
     * @param           stdClass    $instance
     * @param           int         $userid
     * 
     * @throws                      Exception
     * 
     * @creationDate    06/10/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Unenrol the user and send an email confirmation
     */
    public function unenrol_user(stdClass $instance, $userid) {
        /* Variables */
        global $CFG;
        global $DB;
        $user        = null;
        $course      = null;
        $context     = null;
        $messagehtml = null;
        $messagetext = null;
        $subject     = null;

        try {
            /* First unenrol    */
            parent::unenrol_user($instance,$userid);

            //remove from waiting list
            $DB->delete_records(ENROL_WAITINGLIST_TABLE_QUEUE,array('userid'=>$userid,'courseid' => $instance->courseid,'waitinglistid' => $instance->id));

            /* Extra information */
            $user       = get_complete_user_data('id',$userid);
            $course     = get_course($instance->courseid);
            if ($course) {
                $context    = context_course::instance($instance->courseid);
                $rusers = array();
                if (!empty($CFG->coursecontact)) {
                    $croles = explode(',', $CFG->coursecontact);
                    list($sort, $sortparams) = users_order_by_sql('u');
                    $i = 0;
                    do {
                        $rusers = get_role_users($croles[$i], $context, true, '',
                            'r.sortorder ASC, ' . $sort, null, '', '', '', '', $sortparams);
                        $i++;
                    } while (empty($rusers) && !empty($croles[$i]));
                }

                $contact = core_user::get_support_user();

                /* Subject      */
                $subject    = (string)new lang_string('unenrol_subject','enrol_waitinglist',$course->fullname,$user->lang);

                /* Body Message */
                $messagetext = (string)new lang_string('unenrol_body','enrol_waitinglist',$course->fullname,$user->lang) . "</br></br>";
                $messagehtml = text_to_html($messagetext, null, false, true);

                /* Sencd confirmation message   */
                email_to_user($user, $contact, $subject, $messagetext, $messagehtml);
            }//id_Course
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }
    
    /**
     * Enrol user into course via enrol instance.
     *
     * @param stdClass $instance
     * @param int $userid
     * @param int $roleid optional role id
     * @param int $timestart 0 means unknown
     * @param int $timeend 0 means forever
     * @param int $status default to ENROL_USER_ACTIVE for new enrolments, no change by default in updates
     * @param bool $recovergrades restore grade history
     * @return void
     */
    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
   		global $USER,$CFG;
		
        $timestart = time();
        if ($instance->enrolperiod) {
            $timeend = $timestart + $instance->enrolperiod;
        } else {
            $timeend = 0;
        }
		
		$roleid = $instance->roleid;
   		
   		parent::enrol_user($instance,$userid,$roleid,$timestart,$timeend,$status,$recovergrades);

        // Send welcome message.
        if ($instance->{ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE}) {
            $userMail = get_complete_user_data('id', $userid);
            $this->email_welcome_message($instance, $userMail);
        }
        /**
         * @updateDate  @17/02/2016
         * @author      eFaktor     (fbv)
         *
         * Description
         * Send email to the managers
         */
        if ($instance->{ENROL_WAITINGLIST_FIELD_APPROVAL} == APPROVAL_MESSAGE) {
            require_once('approval/approvallib.php');
            /* Get Managers */
            $myManagers = \Approval::get_managers($userid,$instance);
            
            /* Send Notification Manager Approved   */
            $infoNotification = \Approval::info_notification_approved($userid,$instance->courseid,$instance->id);

            /* Add Info Managers    */
            $infoNotification->managers = $myManagers;
            \Approval::send_approved_notification_managers($infoNotification);
        }
    }

    /**
     * @param           $reload
     * @param           $invoice
     * 
     * @throws          Exception
     * 
     * @creationDate    13/09/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Initialize organization structure
     */
    public function Init_Organization_Structure($reload,$invoice = false) {
        /* Variables    */
        global $USER,$PAGE;
        $options    = null;
        $hash       = null;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;

        try {
            /* Initialise variables */
            $name       = 'organization';
            $path       = '/enrol/waitinglist/yui/structure.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpOne     = array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%');
            $grpTwo     = array('nomatchingusers', 'moodle', '%%SEARCHTERM%%');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpOne,$grpTwo,$grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
            );


            $PAGE->requires->js_init_call('M.core_user.init_structure',
                                          array('level_',$reload,$invoice),
                                          false,
                                          $jsModule
            );
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Organization_Structure
    
    /**
     * @param           $userId
     * 
     * @return          mixed|null
     * @throws          Exception
     * 
     * @creationDate    12/09/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Get competence data connected with the user.
     * 
     */
    public  function GetUserCompetenceData($userId) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;

        try {
            /* Search Criteria */
            $params = array();
            $params['user'] = $userId;

            /* SQL Instruction */
            $sql = " SELECT		GROUP_CONCAT(DISTINCT co_zero.id  		ORDER BY co_zero.id 		SEPARATOR ',') as 'levelzero',
                                GROUP_CONCAT(DISTINCT co_one.id  		ORDER BY co_one.id  		SEPARATOR ',') as 'levelone',
                                GROUP_CONCAT(DISTINCT co_two.id  		ORDER BY co_two.id  		SEPARATOR ',') as 'leveltwo',
                                GROUP_CONCAT(DISTINCT uicd.companyid  	ORDER BY uicd.companyid  	SEPARATOR ',') as 'levelthree'
                     FROM		{user_info_competence_data} 		uicd
                        -- LEVEL TWO
                        JOIN	{report_gen_company_relation}   	cr_two	ON 	cr_two.companyid 		= uicd.companyid
                        JOIN	{report_gen_companydata}			co_two	ON 	co_two.id 				= cr_two.parentid
                                                                            AND co_two.hierarchylevel 	= 2
                        -- LEVEL ONE
                        JOIN	{report_gen_company_relation}   	cr_one	ON 	cr_one.companyid 		= cr_two.parentid
                        JOIN	{report_gen_companydata}			co_one	ON 	co_one.id 				= cr_one.parentid
                                                                            AND co_one.hierarchylevel 	= 1
                        -- LEVEL ZERO
                        JOIN	{report_gen_company_relation}  	    cr_zero	ON 	cr_zero.companyid 		= cr_one.parentid
                        JOIN	{report_gen_companydata}			co_zero	ON 	co_zero.id 				= cr_zero.parentid
                                                                            AND co_zero.hierarchylevel 	= 0
                     WHERE		uicd.userid     = :user
                        AND     (uicd.rejected  = 0
                                 OR
                                 uicd.rejected IS NULL)
                        AND     uicd.approved   = 1  ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->levelthree) {
                    return $rdo;
                }else {
                    return null;
                }
            }else {
                return null;
            }//if_Rdo
        }catch (\Exception $ex) {
            throw $ex;
        }
    }//GetUserCompetenceData
    
	    /**
     * Checks if user can self enrol.
     *
     * @param stdClass $instance enrolment instance
     * @param bool $checkuserenrolment if true will check if user enrolment is inactive.
     *             used by navigation to improve performance.
     * @return bool|string true if successful, else error message or false
     */
    public function can_self_enrol(stdClass $instance, $checkuserenrolment = true) {
       $course = get_course($instance->courseid);
		$methods = $this->get_methods($course,$instance->id);
		foreach($methods as $method){
			if ((true === $method->can_self_enrol($instance, false)) && $method->is_active()) {
				return true;
			}
		}
        return false;
    }
    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        global $DB;

        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/waitinglist:config', $context)) {
            return NULL;
        }

        if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'waitinglist'))) {
            return NULL;
        }

        return new moodle_url('/enrol/waitinglist/edit.php', array('courseid'=>$courseid));
    }
	
	  /**
     * Called when user is about to be deleted
     * @param object $user
     * @return void
     */
    public function user_delete($user) {
        global $DB;

        $sql = "SELECT e.*
                  FROM {enrol} e
                  JOIN {user_enrolments} ue ON (ue.enrolid = e.id)
                 WHERE e.enrol = :name AND ue.userid = :userid";
        $params = array('name'=>$this->get_name(), 'userid'=>$user->id);

        $rs = $DB->get_recordset_sql($sql, $params);
        foreach($rs as $instance) {
            $this->unenrol_user($instance, $user->id);
        }
        $rs->close();
		//remove from waiting list
		$DB->delete_records(ENROL_WAITINGLIST_TABLE_QUEUE,array('userid'=>$user->id));
    }

    /**
     * Add new instance of enrol plugin with default settings.
     * @param stdClass $course
     * @return int id of new instance, null if can not be created
     *
     * @updateDate  28/10/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add Invoice Information Option
     *
     * @updateDate  04/03/2016
     * @author      eFaktor (fbv)
     *
     * Description
     * Add price of the course
     *
     * @updateDate  21/06/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Internal && External prices
     */
    public function add_default_instance($course) {
        $expirynotify = $this->get_config('expirynotify', 0);
        if ($expirynotify == 2) {
            $expirynotify   = 1;
            $notifyall      = 1;
        } else {
            $notifyall = 0;
        }
        $fields = array(
                        'status'                                    => $this->get_config('status'),
                        'roleid'                                    => $this->get_config('roleid'),
                        'enrolperiod'                               => $this->get_config('enrolperiod', 0),
                        'expirynotify'                              => $expirynotify,
                        'notifyall'                                 => $notifyall,
                        ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE  => $this->get_config('sendcoursewelcomemessage'),
                        ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE => $this->get_config('sendcoursewaitlistmessage'),
                        ENROL_WAITINGLIST_FIELD_MAXENROLMENTS       => $this->get_config('maxenrolments'),
                        ENROL_WAITINGLIST_FIELD_WAITLISTSIZE        => $this->get_config('waitlistsize'),
                        'expirythreshold'                           => $this->get_config('expirythreshold', 86400),
                        ENROL_WAITINGLIST_FIELD_INVOICE             => 0,
                        ENROL_WAITINGLIST_FIELD_APPROVAL            => 0,
                        ENROL_WAITINGLIST_FIELD_INTERNAL_PRICE      => 0,
                        ENROL_WAITINGLIST_FIELD_EXTERNAL_PRICE      => 0
                       );
        $waitinglistid = $this->add_instance($course, $fields);

        //add an instance of each of the methods, if the waitinglist instance was created ok
        if($waitinglistid){
			$methods=array();
			foreach(self::get_method_names() as $methodtype){
			 $class = '\enrol_waitinglist\method\\' . $methodtype. '\enrolmethod' .$methodtype ;
			   if (class_exists($class)){
                   /**
                    * @updateDate   25/10/2016
                    * @author       eFaktor     (fbv)
                    *
                    * Description
                    * The order parameters was wrong
                    */
                   $class::add_default_instance($course->id,$waitinglistid);
			   }
			}
		}
		return $waitinglistid;  
    }

    /**
     * Add new instance of enrol plugin.
     * @param stdClass $course
     * @param array instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = NULL) {
        global $DB;

        if ($DB->record_exists('enrol', array('courseid'=>$course->id, 'enrol'=>'waitinglist'))) {
            // only one instance allowed, sorry
            return NULL;
        }

        return parent::add_instance($course, $fields);
    }

	
	/**
     * check if there are spaces on the course and enrol if we can
     * @return void
     */
	public function check_and_enrol(progress_trace $trace){
		global $DB;
		$trace->output('waitinglist enrolment check for vacant seats');
		$instances = $DB->get_records('enrol', array( 'enrol'=>'waitinglist'));
		if (!$instances){
			$trace->output('No waitinglist enrolment instances, bye bye');
			 $trace->finished();
			return;
		}
		$wl = enrol_get_plugin('waitinglist');
		
		//this will loop through each instance
		//i) check for vacancies
		//ii) loop through each queue item and "give" them as many seats as possible
		//iii) queue item will determine how to handle the seats
		//iv) queue item will enrol or confirm users if needed 
		foreach($instances as $instance){
			$course         = get_course($instance->courseid);
			$methods        = $this->get_methods($course, $instance->id);
			$queueman       = \enrol_waitinglist\queuemanager::get_by_course($instance->courseid);
			$entryman       = \enrol_waitinglist\entrymanager::get_by_course($instance->courseid);
			$availableseats = $this->get_vacancy_count($instance);

			$trace->output('waitinglist enrolment availabilities: ' . $availableseats);	
			if($availableseats > 0 AND $queueman->get_listtotal() > 0){	
				$allocatedseats=0;
				$qentries = $queueman->get_qentries();
		
				foreach($qentries as $qentry){
					$neededseats = $qentry->seats - $qentry->allocseats;
					if($neededseats > $availableseats - $allocatedseats){
						$giveseats = $availableseats - $allocatedseats;
					}else{
						$giveseats = $neededseats;
					}
					
					//adjust seats according to max allowed by this enrolment method
					$method_enrolable = $methods[$qentry->methodtype]->get_max_can_enrol();
					if($method_enrolable){
						$method_enroled = $entryman->get_allocated_listtotal_by_method($qentry->methodtype);
						$remaining_can_enrol = $method_enrolable - $method_enroled ;
						if($giveseats > $remaining_can_enrol){
							$giveseats = $remaining_can_enrol;
						}
					}
					
					//call into the enrolment method and give it the seats
					$success = $methods[$qentry->methodtype]->graduate_from_list($instance,$qentry,$giveseats);					
					$allocatedseats+=$giveseats;
					if(!($availableseats>$allocatedseats)){break;}
				}//end of for each
				$trace->output('waitinglist enrolment allocated seats:' . $allocatedseats);
			}else{
				$trace->output('.... no eligible entries on waiting list');
			}//end of if count
		}//end of for each instances
		 $trace->finished();
	}//end of check and enrol


    /**
     * @param           progress_trace $trace
     *
     * @throws          Exception
     *
     * @creationDate    29/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if there are users with invoices to activate
     */
    public function check_invoices(progress_trace $trace){
        /* Variables    */
        global $DB;
        $instances  = null;
        $wl         = null;
        $sql        = null;
        $rdo        = null;
        $time       = null;

        try {
            $trace->output('waitinglist enrolment check for invoices to update');
            /* Enrol Plugin */
            $wl = enrol_get_plugin('waitinglist');

            /* Get Invoice Users to activate    */
            $trace->output('waitinglist get users with invoice to update');

            /* SQL Instruction  */
            $sql = " SELECT		ei.id,
                                ei.userid,
                                ei.courseid,
                                ue.id as 'userenrolid',
                                ei.waitinglistid,
                                ei.timemodified
                     FROM		{enrol_invoice}	    ei
                        JOIN	{user_enrolments}	ue	ON 	ue.userid 	= ei.userid
                        JOIN	{enrol}			    e	ON 	e.id 		= ue.enrolid
                                                        AND e.id 		= ei.waitinglistid
                     WHERE		ei.userenrolid		= 0 ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                /* Time local   */
                $time = time();

                /* Update each user */
                foreach ($rdo as $instance) {
                    $instance->timemodified = $time;

                    /* Update   */
                    $DB->update_record('enrol_invoice',$instance);
                }//for_rdo
            }//if_rdo

            $trace->output('waitinglist enrolment check for invoices to update - Finished');
            $trace->finished();
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }

    /**
     * @param   progress_trace  $trace
     *
     * @throws                  Exception
     *
     * @creationDate            19/02/2016
     * @author                  eFaktor     (fbv)
     *
     * Description
     * Check if there are users with approval to activate
     * 
     * @updateDate              16/09/2016
     * @author                  eFaktor     (fbv)
     * 
     * Description
     * Add company
     */
    public function check_approval(progress_trace $trace) {
        /* Variables    */
        global $DB,$CFG;
        $instances          = null;
        $wl                 = null;
        $vacancies          = null;
        $sendNotification   = null;
        $totalSeats         = null;
        $params             = null;
        $sql                = null;
        $rdo                = null;
        $time               = null;

        try {
            /* Local Time  */
            $time = time();

            /* Enrol Plugin */
            $wl = enrol_get_plugin('waitinglist');

            $trace->output('waitinglist enrolment check for approval to update');

            /* Get Invoice Users to activate    */
            $trace->output('waitinglist get users with approval to update');

            $instances = $DB->get_records('enrol', array( 'enrol'=>'waitinglist'));
            if ($instances) {
                $params = array();
                foreach ($instances as $waitingInstance) {
                    $vacancies      = $wl->get_vacancy_count($waitingInstance);

                    /* Check Approval request pendent to send   */
                    if ($vacancies) {
                        $params['waiting']  = $waitingInstance->id;
                        $params['course']   = $waitingInstance->courseid;

                        /* SQL Instruction */
                        $sql = " SELECT	  ea.id,
                                          ea.waitinglistid,
                                          ea.methodtype,
                                          ea.userid,
                                          ea.companyid,
                                          ea.courseid,
                                          c.fullname,
                                          e.customtext3 as 'price',
                                          ea.arguments,
                                          ea.seats,
                                          ea.token,
                                          ap.token 	as 'approve',
                                          re.token	as 'reject',
                                          ''          as 'action'
                                 FROM		{enrol_approval}			ea
                                    JOIN	{enrol}					e	ON 	e.id  			= ea.waitinglistid
                                    JOIN	{course}				c	ON	c.id			= e.courseid
                                    -- Approve Action
                                    JOIN	{enrol_approval_action}	ap	ON	ap.approvalid	= ea.id
                                                                        AND	ap.action		= 1
                                    -- Reject action
                                    JOIN	{enrol_approval_action}	re	ON	re.approvalid	= ea.id
                                                                        AND	re.action		= 2
                                 WHERE		ea.waitinglistid	= :waiting
                                    AND		ea.courseid			= :course
                                    AND		ea.userenrolid		= 0
                                    AND		ea.timesent IS NULL
                                    OR		ea.timesent = 0 ";

                        /* Execute  */
                        $rdo = $DB->get_records_sql($sql,$params);
                        if ($rdo) {
                            foreach ($rdo as $instance) {
                                $sendNotification = false;
                                if ($vacancies) {
                                    switch ($instance->methodtype) {
                                        case 'self':
                                            if ($vacancies >= $instance->seats) {
                                                $sendNotification   = true;
                                                $totalSeats         = $instance->seats;
                                            }
                                            break;
                                        case 'unnamedbulk':
                                            if ($vacancies >= $instance->seats) {
                                                $totalSeats = $instance->seats;
                                            }else {
                                                $totalSeats = $instance->seats - $vacancies;
                                            }//if_else

                                            $sendNotification = true;
                                            break;
                                    }//switch_methodtype
                                }//vacancies

                                if ($sendNotification) {
                                    $myManagers = \Approval::get_managers($instance->userid);
                                    $infoMail = new stdClass();
                                    $infoMail->approvalid   = $instance->id;
                                    $infoMail->course       = $instance->fullname;
                                    $infoMail->price        = $instance->price;
                                    $infoMail->arguments    = $instance->arguments;
                                    $infoMail->managers = \Approval::add_approval_entry_manager($myManagers,$waitingInstance->id,$waitingInstance->courseid);

                                    if (array_key_exists($instance->userid,$myManagers)) {
                                        $instance->action = APPROVED_ACTION;
                                        $infoManager  = \Approval::get_request_manager($infoMail->managers[$instance->userid],$instance->userid);
                                        \Approval::apply_action_from_manager($instance,$infoManager);
                                    }else {
                                        /* Approve Link */
                                        $lnkApprove = $CFG->wwwroot . '/enrol/waitinglist/approval/action.php/' . $instance->token . '/' . $instance->approve;
                                        $infoMail->approve = $lnkApprove;
                                        /* Reject Link  */
                                        $lnkReject  = $CFG->wwwroot . '/enrol/waitinglist/approval/action.php/' . $instance->token . '/' . $instance->reject;
                                        $infoMail->reject = $lnkReject; 

                                        /* Send Mails   */
                                        $user = get_complete_user_data('id',$instance->userid);
                                        \Approval::send_notifications($user,$infoMail,$myManagers);

                                        $instanceApproval = new stdClass();
                                        $instanceApproval->id           = $instance->id;
                                        $instanceApproval->timesent     = $time;
                                        $instanceApproval->timemodified = $time;
                                        $DB->update_record('enrol_approval',$instanceApproval);

                                        $vacancies = $vacancies - $totalSeats;
                                    }
                                }//if_sendNotifications
                            }//for_rdo_instances_approval
                        }//if_rdo
                    }//if_vacancies
                }//$waitingInstance
            }//if_instances

            $trace->output('waitinglist enrolment check for approval to update - Finished');
            $trace->finished();
        }catch (Exception $ex) {
            $trace->output('waitinglist enrolment check for approval to update - ERROR' . $ex->getMessage());
            throw $ex;
        }//try_catch
    }//check_approval

    /**
     * Description
     * Get the vacancy count for this waiting list
     * We need remove enrolments and confirmations from maxenrolments
     *
     * @param   stdClass $instance      waitinglist
     * @return           int|null       seats available
     * @throws           Exception
     *
     * @updateDate  04/07/2017
     */
	public function get_vacancy_count($instance){
	    /* Variables */
		global $DB;
        $entryman = null;
        $confirmedlistcount = null;
        $vacancies = null;

        try {
            //$count = $DB->count_records('user_enrolments', array('enrolid' => $instance->id));
            // Get seats assigned
            $entryman           = \enrol_waitinglist\entrymanager::get_by_course($instance->courseid);
            $confirmedlistcount = $entryman->get_confirmed_listtotal();

            /**
             * @updateDate  19/02/2016
             * @author      eFaktor     (fbv)
             *
             * Description
             * If the max enrolments is set to 0, it means unlimited.
             */
            if ($instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS}) {
                $vacancies = $instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS} - $confirmedlistcount;
                if($vacancies < 0){$vacancies=0;}
            }else {
                $vacancies = 1;
            }

            return $vacancies;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
	}
	
	
	/**
     * Handle users who are enroled. Called from observer class.
     * currently we dont do anything here. But it seems one day we might
     *
     * @param int $courseid
     * @param int $userid
     * @return boolean true = successful
     */
	public function handle_enrol($courseid,$userid){
        return true;
	}

    /**
     * Description
     * Update all users that are waiting in the queue, after the enrol instance has been update
     *
     * @param       integer $courseid
     * @param       integer $enrolid
     *
     * @throws              Exception
     *
     * @creationDate    06/07/2017
     * @author          eFaktor     (fbv)
     */
	public function handle_enrolupdated($courseid,$enrolid) {
	    /* Variables */
	    global $DB;
	    $trans              = null;
	    $role               = null;
	    $entry              = null;
        $entryman           = null;
        $queueman           = null;
        $vacancies          = null;
        $instance           = null;
        $plugin             = null;
        $vacancies          = null;
        $updatetoenrol      = null;
        $enrolled           = 0;

	    // Start transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Plugin info
            $plugin = enrol_get_plugin('waitinglist');

            // Get role student
            $role = $DB->get_record('role',array('archetype' => 'student'));

            // Enrol Instance
            $instance = $DB->get_record('enrol',array('id' => $enrolid,'courseid' => $courseid));

            // queue manager
            $queueman = \enrol_waitinglist\queuemanager::get_by_course_workspace($courseid);
            // Entry manager
            $entryman = \enrol_waitinglist\entrymanager::get_by_course($courseid);

            // Entries to update. Entries in the waiting list to update
            if ($queueman->qentries) {
                foreach ($queueman->qentries as $entry) {
                    $vacancies      = 0;
                    $updatetoenrol  = false;

                    // Calculate vacancies
                    if ($instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS}) {
                        $vacancies    = $instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS} - $entryman->GetOcuppaiedSeats_NotConnectedUser($entry->userid,$entry->courseid,$instance->id);
                    }

                    if ($instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS}) {
                        if ($vacancies) {
                            if ($vacancies >= $entry->seats) {
                                $entry->offqueue		= 1;
                                $entry->queueno			= \enrol_waitinglist\queuemanager::OFFQ;
                                $entry->allocseats		= $entry->seats;
                                $entry->confirmedseats	= $entry->seats -1;
                                $entry->enroledseats    = 1;
                                $updatetoenrol          = true;

                            }else {
                                $entry->queueno		= $entry->queueno - $enrolled;
                                $updatetoenrol      = false;
                            }
                        }else {
                            $entry->queueno		    = $entry->queueno - $enrolled;
                            $updatetoenrol          = false;
                        }//if_vacancies
                    }else {
                        // Unlimitted
                        $entry->offqueue		= 1;
                        $entry->queueno			= \enrol_waitinglist\queuemanager::OFFQ;
                        $entry->allocseats		= $entry->seats;
                        $entry->confirmedseats	= $entry->seats -1;
                        $entry->enroledseats    = 1;
                        $updatetoenrol          = true;
                    }

                    // Update entry && enroll user
                    $DB->update_record(\enrol_waitinglist\entrymanager::CTABLE, $entry);
                    // Enroll user
                    if ($updatetoenrol) {
                        $enrolled ++;
                        $plugin->enrol_user($instance,$entry->userid,$role->id);
                    }
                }//for_Each_Entry
            }//if_entries

            // Commit
            $trans->allow_commit();
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//handle_enrolupdated

    /**
     * @param           $courseid
     * @param           $userid
     *
     * @return          bool
     * @throws          Exception
     *
     * Description
     * Handle users who are unenroled. Called from observer class
     *
     * @updateDate      29/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update to invoice option
     *
     * @updateDate      30/12/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update to approval option
     * 
     * @updateDate      03/10/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Delete unenrol actions
     */
    public function handle_unenrol($courseid,$userid){
        /* Variables    */
		global  $DB;
        $entryman   = null;
        $entry      = null;
        $waitingLst = null;
        $rdo        = null;
        $params     = null;

        try {
            $entryman   =  \enrol_waitinglist\entrymanager::get_by_course($courseid);
            $entry      = $entryman->get_entry_by_userid($userid);

            //We might get here without an entry
            //if the user was unenroled via seats modifcation in the entry manager
            //in that case, and unforeseen proces flows, just return
            if(!$entry){return true;}

            //remove entry from list altogether
            $entryman->remove_entry_from_db($entry->id);

            /* Check Invoice Option */
            $waitingLst = $entryman->waitinglist;
            if (enrol_get_plugin('invoice')) {
                if ($waitingLst->{ENROL_WAITINGLIST_FIELD_INVOICE}) {
                    /* Mark user as unenrolled  */
                    /* Get record   */
                    $params = array();
                    $params['userid']           = $userid;
                    $params['courseid']         = $courseid;
                    $params['waitinglistid']    = $waitingLst->id;
                    $params['unenrol']          = 0;
                    $rdo = $DB->get_record('enrol_invoice',$params);
                    if ($rdo) {
                        $rdo->unenrol       = 1;
                        $rdo->timemodified  = time();

                        /* Update   */
                        $DB->update_record('enrol_invoice',$rdo);
                    }//if_rdo
                }//if_invoice_option
            }//if_invoice

            /* Approval Option */
            if ($waitingLst->{ENROL_WAITINGLIST_FIELD_APPROVAL}) {
                /* Mark user as unenrolled  */
                /* Get record   */
                $params = array();
                $params['userid']           = $userid;
                $params['courseid']         = $courseid;
                $params['waitinglistid']    = $waitingLst->id;
                $params['unenrol']          = 0;
                $rdo = $DB->get_record('enrol_approval',$params);
                if ($rdo) {
                    $rdo->unenrol       = 1;
                    $rdo->timemodified  = time();

                    /* Update   */
                    $DB->update_record('enrol_approval',$rdo);
                }//if_rdo
            }//if_approval_option

            /* Unenrol actions  */
            $params = array();
            $params['userid']           = $userid;
            $params['courseid']         = $courseid;
            $params['waitingid']        = $waitingLst->id;
            $DB->delete_records('enrol_waitinglist_unenrol',$params);

            // Unroll from mdl_user_enrolments
            $params = array();
            $params['userid']    = $userid;
            $params['enrolid']   = $waitingLst->id;
            $rdo = $DB->get_record('user_enrolments',$params);
            if ($rdo) {
                // deleted
                $DB->delete_records('user_enrolments',$params);
            }
            
            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_vcathc
	}

    /**
     * @param           $courseid
     *
     * @return          bool
     * @throws          Exception
     *
     * Description
     * Remove entries from DB when a course is deleted. Called from observer class
     *
     * @updateDate      29/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * remove invoice entries
     *
     * @updateDate      03/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Remove unenrol entries
     */
    public function handle_coursedeleted($courseid){
        /* Variables    */
		global $DB;
        $rdoApproval    = null;
        $approval       = null;
        $rdoUnenrol     = null;

        try {
            /**
             * @updateDate  29/10/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Remove from enrol invoice
             *
             * @updateDate  30/12/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Remove from enrol approval
             */
            /* GEt Instace Waiting List Id  */
            $waitingLst = $DB->get_records(ENROL_WAITINGLIST_TABLE_METHODS,array('courseid' => $courseid));
            if ($waitingLst) {
                /* Criteria */
                $params = array();
                $params['courseid']         = $courseid;
                $params['waitinglistid']    = 0;

                foreach ($waitingLst as $instance) {
                    /* Criteria */
                    unset($params['waitingid']);
                    $params['waitinglistid'] = $instance->waitinglistid;
                    /* Invoice Option */
                    if (enrol_get_plugin('invoice')) {
                        $DB->delete_records('enrol_invoice',$params);
                    }//if_invoice

                    /* Approval Option */
                    $rdoApproval = $DB->get_records('enrol_approval',$params,'id');
                    $DB->delete_records('enrol_approval',$params);
                    /* Approval Action */
                    foreach ($rdoApproval as $approval) {
                        $DB->delete_records('enrol_approval_action',array('approvalid' => $approval->id));
                    }//for

                    /* Unenrol actions */
                    unset($params['waitinglistid']);
                    $params['waitingid'] = $instance->waitinglistid;
                    $DB->delete_records('enrol_waitinglist_unenrol',$params);
                }//for
            }//if_waitinglist


            $DB->delete_records(ENROL_WAITINGLIST_TABLE_QUEUE,array('courseid'=>$courseid));
            $DB->delete_records(ENROL_WAITINGLIST_TABLE_METHODS,array('courseid'=>$courseid));

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
	}


    /**
     * Sync course enrolment expiry info with enrolments
     *
     * @param progress_trace $trace
     * @param int $courseid one course, empty mean all
     * @return int 0 means ok, 1 means error, 2 means plugin disabled
     */
    public function sync(progress_trace $trace, $courseid = null) {
        global $DB;

        if (!enrol_is_enabled('waitinglist')) {
            $trace->finished();
            return 2;
        }

        // Unfortunately this may take a long time, execution can be interrupted safely here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $trace->output('Verifying waitinglist enrolment expiration...');

        $params = array('now'=>time(), 'useractive'=>ENROL_USER_ACTIVE, 'courselevel'=>CONTEXT_COURSE);
        $coursesql = "";
        if ($courseid) {
            $coursesql = "AND e.courseid = :courseid";
            $params['courseid'] = $courseid;
        }

        // Deal with expired accounts.
        $action = $this->get_config('expiredaction', ENROL_EXT_REMOVED_KEEP);

        if ($action == ENROL_EXT_REMOVED_UNENROL) {
            $instances = array();
            $sql = "SELECT ue.*, e.courseid, c.id AS contextid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'waitinglist')
                      JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :courselevel)
                     WHERE ue.timeend > 0 AND ue.timeend < :now
                           $coursesql";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                if (empty($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];
                // Always remove all waitinglistly assigned roles here, this may break enrol_self roles but we do not want hardcoded hacks here.
                role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'', 'itemid'=>0), true);
                $this->unenrol_user($instance, $ue->userid);
                $trace->output("unenrolling expired user $ue->userid from course $instance->courseid", 1);
            }
            $rs->close();
            unset($instances);

        } else if ($action == ENROL_EXT_REMOVED_SUSPENDNOROLES or $action == ENROL_EXT_REMOVED_SUSPEND) {
            $instances = array();
            $sql = "SELECT ue.*, e.courseid, c.id AS contextid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'waitinglist')
                      JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :courselevel)
                     WHERE ue.timeend > 0 AND ue.timeend < :now
                           AND ue.status = :useractive
                           $coursesql";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                if (empty($instances[$ue->enrolid])) {
                    $instances[$ue->enrolid] = $DB->get_record('enrol', array('id'=>$ue->enrolid));
                }
                $instance = $instances[$ue->enrolid];
                if ($action == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                    // Remove all waitinglistly assigned roles here, this may break enrol_self roles but we do not want hardcoded hacks here.
                    role_unassign_all(array('userid'=>$ue->userid, 'contextid'=>$ue->contextid, 'component'=>'', 'itemid'=>0), true);
                    $this->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                    $trace->output("suspending expired user $ue->userid in course $instance->courseid, roles unassigned", 1);
                } else {
                    $this->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                    $trace->output("suspending expired user $ue->userid in course $instance->courseid, roles kept", 1);
                }
            }
            $rs->close();
            unset($instances);

        } else {
            // ENROL_EXT_REMOVED_KEEP means no changes.
        }

        $trace->output('...waitinglist enrolment updates finished.');
        $trace->finished();

        return 0;
    }

    /**
     * Returns the user who is responsible for waitinglist enrolments in given instance.
     *
     * Usually it is the first editing teacher - the person with "highest authority"
     * as defined by sort_by_roleassignment_authority() having 'enrol/waitinglist:manage'
     * capability.
     *
     * @param int $instanceid enrolment instance id
     * @return stdClass user record
     */
    protected function get_enroller($instanceid) {
        global $DB;

        if ($this->lasternollerinstanceid == $instanceid and $this->lasternoller) {
            return $this->lasternoller;
        }

        $instance = $DB->get_record('enrol', array('id'=>$instanceid, 'enrol'=>$this->get_name()), '*', MUST_EXIST);
        $context = context_course::instance($instance->courseid);

        if ($users = get_enrolled_users($context, 'enrol/waitinglist:manage')) {
            $users = sort_by_roleassignment_authority($users, $context);
            $this->lasternoller = reset($users);
            unset($users);
        } else {
            $this->lasternoller = parent::get_enroller($instanceid);
        }

        $this->lasternollerinstanceid = $instanceid;

        return $this->lasternoller;
    }

    /**
     * Gets an array of the user enrolment actions.
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability("enrol/waitinglist:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/waitinglist:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class'=>'editenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }


    /**
     * @param       restore_enrolments_structure_step $step
     * @param       stdClass $data
     * @param       stdClass $course
     * @param       int $oldid
     *
     * @throws      Exception
     *
     * @updateDate  21/10/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add restore instance for sub-methods connected
     */
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        /* Variables */
        global $DB;
        $instanceId     = null;
        $rdoWaitOld     = null;
        $rdoOldSub      = null;
        $params         = null;

        try {
            /**
             * Get old Waiting List instance
             */
            $params = array();
            $params['enrol'] = 'waitinglist';
            $params['id']    = $oldid;
            /* Execute */
            $rdoWaitOld = $DB->get_record('enrol',$params);
            if ($rdoWaitOld) {
                /**
                 * Update waiting list instance.
                 * Only one waiting list instance per course.
                 */
                if ($instances = $DB->get_records('enrol', array('courseid'=>$data->courseid, 'enrol'=>'waitinglist'), 'id')) {
                    $instance   = reset($instances);
                    $instanceId = $instance->id;
                } else {
                    $instanceId = $this->add_instance($course, (array)$data);
                }

                /**
                 * Generate instance sub-methods
                 */
                foreach(self::get_method_names() as $methodtype){
                    $class = '\enrol_waitinglist\method\\' . $methodtype. '\enrolmethod' .$methodtype ;
                    if (class_exists($class)){
                        $class::restore_instance($oldid,$rdoWaitOld->courseid,$instanceId,$course->id);
                    }
                }

                /* Mapping */
                $step->set_mapping('enrol', $oldid, $instanceId);
            }//if_$rdoWaitOld
        }catch (Exception $ex) {
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
     * @param       int $oldCourse      Original course
     * @param       int $courseId       New course
     *
     * @throws          Exception
     */
    public static function update_restored_instance($oldCourse,$courseId) {
        /* Variables */
        global $DB;
        $newInstance    = null;
        $oldInstance    = null;
        $params         = null;
        $rdoOld         = null;
        $newParams      = null;
        $rdo            = null;

        try {
            // Get old instance
            $params = array();
            $params['courseid']     = $oldCourse;
            $params['enrol']        = 'waitinglist';
            // Execute
            $rdoOld = $DB->get_record('enrol',$params);

            // Get new instance
            $newParams = array();
            $newParams['courseid']     = $courseId;
            $newParams['enrol']        = 'waitinglist';
            // Execute
            $rdo = $DB->get_record('enrol',$newParams);

            if ($rdoOld && $rdo) {
                // Update new instance
                $rdo->status                                        = $rdoOld->status;
                $rdo->roleid                                        = $rdoOld->roleid;
                $rdo->enrolperiod                                   = $rdoOld->enrolperiod  ;
                $rdo->expirynotify                                  = $rdoOld->expirynotify;
                $rdo->notifyall                                     = $rdoOld->notifyall;

                $rdo->{ENROL_WAITINGLIST_FIELD_CUTOFFDATE}          = $rdoOld->{ENROL_WAITINGLIST_FIELD_CUTOFFDATE};
                $rdo->{ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE}  = $rdoOld->{ENROL_WAITINGLIST_FIELD_SENDWELCOMEMESSAGE};
                $rdo->{ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE} = $rdoOld->{ENROL_WAITINGLIST_FIELD_SENDWAITLISTMESSAGE};
                $rdo->{ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE}      = $rdoOld->{ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE};
                $rdo->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS}       = $rdoOld->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS};
                $rdo->{ENROL_WAITINGLIST_FIELD_WAITLISTSIZE}        = $rdoOld->{ENROL_WAITINGLIST_FIELD_WAITLISTSIZE};
                $rdo->expirythreshold                               = $rdoOld->expirythreshold;
                $rdo->{ENROL_WAITINGLIST_FIELD_INVOICE}             = $rdoOld->{ENROL_WAITINGLIST_FIELD_INVOICE};
                $rdo->{ENROL_WAITINGLIST_FIELD_APPROVAL}            = $rdoOld->{ENROL_WAITINGLIST_FIELD_APPROVAL};
                $rdo->{ENROL_WAITINGLIST_FIELD_INTERNAL_PRICE}      = $rdoOld->{ENROL_WAITINGLIST_FIELD_INTERNAL_PRICE};
                $rdo->{ENROL_WAITINGLIST_FIELD_EXTERNAL_PRICE}      = $rdoOld->{ENROL_WAITINGLIST_FIELD_EXTERNAL_PRICE};

                // Execute
                $DB->update_record('enrol',$rdo);

                // Update Submethods
                foreach(self::get_method_names() as $methodtype){
                    $class = '\enrol_waitinglist\method\\' . $methodtype. '\enrolmethod' .$methodtype ;
                    if (class_exists($class)){
                        $class::update_restored_instance($rdoOld->id,$oldCourse,$rdo->id,$courseId);
                    }
                }
            }
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//update_restored_instance

    /**
     * Restore user enrolment.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $instance
     * @param int $oldinstancestatus
     * @param int $userid
     */
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        global $DB,$CFG;

        /* Log  */
        $dbLog = userdate(time(),'%d.%m.%Y', 99, false). ' HOLA CARACOLA  . ' . "\n";
        error_log($dbLog, 3, $CFG->dataroot . "/restore_paqui.log");

        // Note: manual enrolment is a bit tricky because other types may be converted to waitinglist enrolments,
        //       and waitinglist is restricted to one enrolment per user. Waitinglist is based in manual, so 
		//		this could be simplified

        $ue = $DB->get_record('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid));
        $enrol = false;
        if ($ue and $ue->status == ENROL_USER_ACTIVE) {
            // We do not want to restrict current active enrolments, let's kind of merge the times only.
            // This prevents some teacher lockouts too.
            if ($data->status == ENROL_USER_ACTIVE) {
                if ($data->timestart > $ue->timestart) {
                    $data->timestart = $ue->timestart;
                    $enrol = true;
                }

                if ($data->timeend == 0) {
                    if ($ue->timeend != 0) {
                        $enrol = true;
                    }
                } else if ($ue->timeend == 0) {
                    $data->timeend = 0;
                } else if ($data->timeend < $ue->timeend) {
                    $data->timeend = $ue->timeend;
                    $enrol = true;
                }
            }
        } else {
            if ($instance->status == ENROL_INSTANCE_ENABLED and $oldinstancestatus != ENROL_INSTANCE_ENABLED) {
                // Make sure that user enrolments are not activated accidentally,
                // we do it only here because it is not expected that enrolments are migrated to other plugins.
                $data->status = ENROL_USER_SUSPENDED;
            }
            $enrol = true;
        }

        if ($enrol) {
            $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
        }
    }

    /**
     * Send welcome email to specified user.
     *
     * @param           $instance
     * @param           $user
     *
     * @throws          Exception
     *
     * @updateDate      03/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the unenrol link
     *
     * @updateDate      17/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the unenrol link if the deadlin allow it
     */
    protected function email_welcome_message($instance, $user) {
        /* Variables */
        global $CFG, $DB;
        $unEnrolURL     = null;
        $unenrolLnk     = null;
        $unenrolStr     = null;
        $course         = null;
        $context        = null;
        $a              = null;
        $message        = null;
        $messagetext    = null;
        $messagehtml    = null;
        $subject        = null;
        $rusers         = null;
        $croles         = null;
        $sort           = null;
        $sortparams     = null;
        $contact        = null;

        try {
            $course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
            $context = context_course::instance($course->id);

            $a = new stdClass();
            $a->coursename = format_string($course->fullname, true, array('context'=>$context));
            $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id&course=$course->id";

            /**
             * @updateDate  03/10/2016
             * @author      eFaktor     (fbv)
             *
             * Description
             * Add unenrol link
             */
            require_once('unenrol/unenrollib.php');
            if (\Unenrol_Waiting::Can_Unenrol($user->id,$instance->courseid,$instance->id)) {
                $unEnrolURL = \Unenrol_Waiting::UnenrolLink($user->id,$instance->courseid,$instance->id);
                $unenrolLnk = "<a href='" . $unEnrolURL ."'>" . get_string('unenrol_me','enrol_waitinglist') . "</a>";
                $unenrolStr = "</br>". get_string('unenrol_link','enrol_waitinglist',$unenrolLnk);
            }//if_can_unenrol

            if (trim($instance->{ENROL_WAITINGLIST_FIELD_WELCOMEMESSAGE}) !== '') {
                $message  = $instance->customtext1;
                $message  = str_replace('{$a->coursename}', $a->coursename, $message);
                $message  = str_replace('{$a->profileurl}', $a->profileurl, $message);

                if (strpos($message, '<') === false) {
                    // Plain text only.
                    $message .= "</br>" . $unenrolStr;
                    $messagetext = $message;
                    $messagehtml = text_to_html($messagetext, null, false, true);
                } else {
                    // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                    $messagehtml = format_text($message, FORMAT_MOODLE, array('context'=>$context, 'para'=>false, 'newlines'=>true, 'filter'=>true));
                    $messagehtml .= "</br>" . $unenrolStr;
                    $messagetext = html_to_text($messagehtml);
                }
            } else {
                $messagetext = (string)new lang_string('welcometocoursetext', 'enrol_waitinglist', $a,$user->lang);
                $messagetext .= "</br>" . $unenrolStr;
                $messagehtml = text_to_html($messagetext, null, false, true);
            }

            $subject = (string)new lang_string('welcometocourse', 'enrol_waitinglist',
                                               format_string($course->fullname, true, array('context'=>$context)),null,$user->lang);

            $rusers = array();
            if (!empty($CFG->coursecontact)) {
                $croles = explode(',', $CFG->coursecontact);
                list($sort, $sortparams) = users_order_by_sql('u');
                // We only use the first user.
                $i = 0;
                do {
                    $rusers = get_role_users($croles[$i], $context, true, '',
                        'r.sortorder ASC, ' . $sort, null, '', '', '', '', $sortparams);
                    $i++;
                } while (empty($rusers) && !empty($croles[$i]));
            }

            //if ($rusers) {
            //    $contact = reset($rusers);
            //} else {
                $contact = core_user::get_support_user();
            //}

            // Directly emailing welcome message rather than using messaging.
            /**
             * @updateDate  05/09/2016
             * @author      eFaktor     (fbv)
             *
             * Description
             * Add iCal file
             *
             * @updateDate  20/09/2016
             * @author      eFaktor     (fbv)
             *
             * Description
             * Only for classroom formats
             */
            if (($course->format == 'classroom') || ($course->format == 'classroom_frikomport')) {
                $fileCal = $this->iCalendar_StartDate($course);
                if ($fileCal) {
                    $messagehtml .= "</br></br>" . (string)new lang_string('welcome_ical_attach','enrol_waitinglist',null,$user->lang) . "</br></br>";
                    $messagetext .= "</br></br>" . (string)new lang_string('welcome_ical_attach','enrol_waitinglist',null,$user->lang) . "</br></br>";
                    email_to_user($user, $contact, $subject, $messagetext, $messagehtml,'iCal/'.$fileCal ,$fileCal);
                }else {
                    email_to_user($user, $contact, $subject, $messagetext, $messagehtml);
                }//if_file_cal
            }else {
                email_to_user($user, $contact, $subject, $messagetext, $messagehtml);
            }//classroom_format
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }

    /**
     * @param           $course
     *
     * @return          null|string
     * @throws          Exception
     *
     * @updateDate      05/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get iCal filte to attach
     */
    private function iCalendar_StartDate($course) {
        /* Variables */
        global $CFG;
        $iCal           = null;
        $fileCal        = null;
        $fileName       = null;
        $fileLocation   = null;
        $pluginInfo     = null;
        $uid            = null;
        $location       = null;

        try {
            // Plugin info
            $pluginInfo     = get_config('enrol_waitinglist');
            if ($pluginInfo) {
                // Get location to save iCal file
                $fileLocation   = $CFG->dataroot . '/' . $pluginInfo->file_location;
                if (file_exists($fileLocation)) {
                    if (is_dir($fileLocation)) {
                        $created = true;
                    }else {
                        return false;
                    }
                }else {
                    mkdir($fileLocation);
                    $created = true;
                }

                // File created
                if ($created) {
                    // Location connected with the course
                    $location = self::get_location($course->id);

                    // Get events connected with course
                    $myevents = $this->get_events($course->id);

                    // iCal file -- Headers
                    $iCal  = "BEGIN:VCALENDAR"  . "\n";
                    $iCal .= "METHOD:PUBLISH"   . "\n";
                    $iCal .= "VERSION:2.0"      . "\n";
                    $iCal .= "PRODID:-//KSLæring//EN"   . "\n";
                    $iCal .= "CALSCALE:GREGORIAN" . "\n";
                    $iCal .= "X-WR-TIMEZONE:Europe/Oslo " . "\n";
                    // iCal file - Events
                    if ($myevents) {
                        foreach ($myevents as $event) {

                            // Add Event
                            $this->add_ical_event($course->fullname,$event,$location,$iCal);
                        }//for_event
                    }else {
                        $event = new stdClass();
                        $event->date  = $course->startdate;
                        $event->time  = null;
                        $event->start = $course->startdate;
                        $event->end   = $course->startdate;

                        // Add Event
                        $this->add_ical_event($course->fullname,$event,$location,$iCal);
                    }//if_myevents
                    $iCal .= "END:VCALENDAR"    . "\n";

                    // Save iCal
                    $fileName  = 'Kalender' . uniqid()  . '.ics';
                    $fileCal = fopen($CFG->dataroot . '/iCal/' . $fileName,'w+');
                    fwrite($fileCal,$iCal);
                    fclose($fileCal);

                    return $fileName;
                }else {
                    return false;
                }//if_created
            }else {
                return false;
            }//if_else
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//iCalendar_StartDate

    /**
     * Description
     * Add an iCal event
     *
     * @param       String      $course
     * @param       stdClass    $event
     * @param       stdClass    $location
     * @param       String      $ical
     *
     * @throws                  Exception
     *
     * @creationDate    05/04/2017
     * @author          eFaktor     (fbv)
     */
    private function add_ical_event($course,$event,$location,&$ical) {
        // Variables
        $time = null;

        try {
            //local time
            $time = time();

            // Create event
            $ical .= "BEGIN:VEVENT"     . "\n";
            $ical .= "SUMMARY:"         . $course . "\n";
            $ical .= "UID:"             . uniqid() . "\n";
            $ical .= "DTSTART:"         . date('Ymd\THis', $event->start) . "\n";
            $ical .= "DTEND:"           . date('Ymd\THis', $event->end) . "\n";
            $ical .= "CREATED:"         . date('Ymd\THis', $time) . "\n";
            $ical .= "LAST-MODIFIED:"   . date('Ymd\THis', $time) . "\n";
            $ical .= "DTSTAMP:"         . date('Ymd\THis', $time) . "\n";
            $ical .= "SEQUENCE:0"       . "\n";
            $ical .= "STATUS:CONFIRMED" . "\n";
            // Location
            if ($location) {
                $ical .= "LOCATION:"        . $location->name . '\n' . $location->address. "\n";
                if ($location->map) {
                    $ical .= "URL;VALUE=URI:" . $location->map . "\n";
                }

                if ($location->detail) {
                    $ical .= "DESCRIPTION:"     . str_replace(',','\,',$location->detail) . "\n";
                }
            }//if_location
            $ical .= "END:VEVENT"       . "\n";
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_ical_event

    /**
     * Description
     * Get events connected with course
     *
     * @param       integer $courseid
     *
     * @return              array|null
     * @throws              Exception
     *
     * @creationDate    05/04/2017
     * @author          eFaktor     (fbv)
     */
    private function get_events($courseid) {
        // Variables
        global $DB;
        $rdo        = null;
        $sql        = null;
        $params     = null;
        $myevents   = null;
        $timeslst   = null;
        $event      = null;
        $index      = null;
        $time       = null;
        $from       = null;
        $to         = null;
        $today      = null;
        $year       = null;
        $mydate     = null;

        try {
            // Checking
            $today = getdate(time());
            $year = $today['year'];

            // Search criteria
            $params = array();
            $params['course'] = $courseid;

            // SQL Instruction - get events
            $sql = " SELECT	cf.id,
                            cf.value
                     FROM	{course_format_options}	cf
                     WHERE	cf.courseid = :course
                        AND cf.name like '%time%'
                        AND cf.value != '' ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $timeslst = explode(',',$rdo->value);
                // Get all eevents with the right format
                foreach ($timeslst as $time) {
                    // Extract date and time
                    $time =  str_replace(chr(13),'',$time);
                    $time =  str_replace("\r",'',$time);
                    $time =  str_replace("\n",'',$time);
                    $time  = str_replace('kl','#',str_replace('kl.','#',$time));
                    $index = strrpos($time,'#');
                    if ($index) {
                        // Event
                        $event = new stdClass();
                        $event->date  = null;
                        $event->time  = null;
                        $event->start = null;
                        $event->end   = null;

                        // Extract date
                        $mydate = strtotime(substr($time,0,$index));
                        $mydate = getdate($mydate);
                        if ($mydate['year'] >= $year) {
                            $event->date =  substr($time,0,$index);
                            // Extract time
                            $event->time = substr($time,$index+1);

                            // Extract From/to
                            $index = strrpos($event->time,'-');
                            if ($index) {
                                $from   = substr($event->time,0,$index);
                                $to     = substr($event->time,$index+1);

                                // time start
                                $event->start   = strtotime($event->date . ' ' . $from);
                                // time end
                                $event->end     = strtotime($event->date . ' ' . $to);
                            }else {
                                $event->start   = strtotime($event->date);
                                $event->end     = strtotime($event->date);
                            }//if_index
                        }

                    }//if_index

                    // Add event
                    if ($event) {
                        $myevents[] = $event;
                    }//if_Event
                }//foreach
            }//if_rdo

            return $myevents;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_events

    /**
     * Description
     * Get location connected with
     *
     * @param           $courseId
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    15/09/2016
     * @author          eFaktor     (fbv)
     */
    private function get_location($courseId) {
        // Variables
        global $DB;
        $sql            = null;
        $rdo            = null;
        $params         = null;
        $infoLocation   = null;

        try {
            // Search criteria
            $params = array();
            $params['course'] = $courseId;
            $params['name']   = 'course_location';

            // SQL instruction - get location
            $sql = " SELECT	  lo.name,
                              lo.floor,
                              lo.room,
                              lo.street,
                              lo.postcode,
                              lo.city,
                              trim(lo.urlmap) as 'urlmap'
                     FROM	  {course_format_options}	cf
                        JOIN  {course_locations}		lo ON lo.id = cf.value
                     WHERE	  cf.courseid = :course
                        AND	  cf.name     = :name ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                // Location
                $infoLocation = new stdClass();
                $infoLocation->name        = str_replace(',','\,',$rdo->name);
                // Detail
                $infoLocation->detail      = get_string('location_floor','local_friadmin') . ': ' . str_replace(',','\,',$rdo->floor);
                $infoLocation->detail     .= '\n';
                $infoLocation->detail     .= get_string('location_room','local_friadmin')  . ': ' . str_replace(',','\,',$rdo->room);
                $infoLocation->detail     .= '\n';
                // Address
                $infoLocation->address     = str_replace(',','\,',$rdo->street);
                $infoLocation->address    .= '\n';
                $infoLocation->address    .= $rdo->postcode . ' ' . str_replace(',','\,',$rdo->city);
                // Url map
                $infoLocation->map         = $rdo->urlmap;
            }//if_Rdo

            return $infoLocation;
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_location

    /**
     * Restore role assignment. 
     *
     * @param stdClass $instance
     * @param int $roleid
     * @param int $userid
     * @param int $contextid
     */
	 /* Probably not necessary for waitinglist enrolments */
    public function restore_role_assignment($instance, $roleid, $userid, $contextid) {
        // This is necessary only because we may migrate other types to this instance,
        // we do not use component in waitinglist or self enrol.
        role_assign($roleid, $userid, $contextid, '', 0);
    }

    /**
     * Restore user group membership. 
     * @param stdClass $instance
     * @param int $groupid
     * @param int $userid
     */
	  /* Probably not necessary for waitinglist enrolments */
    public function restore_group_member($instance, $groupid, $userid) {
        global $CFG;
        require_once("$CFG->dirroot/group/lib.php");

        // This might be called when forcing restore as waitinglist enrolments.

        groups_add_member($groupid, $userid);
    }
}