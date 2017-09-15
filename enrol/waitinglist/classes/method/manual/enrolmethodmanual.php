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
 * Waiting List - Manual submethod
 *
 * @package         enrol/waitinglist
 * @subpackage      classes/method/manual
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    17/08/2016
 * @author          efaktor     (fbv)
 *
 * Description
 */

namespace enrol_waitinglist\method\manual;

class enrolmethodmanual extends \enrol_waitinglist\method\enrolmethodbase{
    /* Variables */
    const METHODTYPE='manual';
    protected $active = false;

    const MFIELD_MAXENROLLED            = 'customint2';
    const MFIELD_SENDWAITLISTMESSAGE    = 'customint4';
    const MFIELD_COHORTONLY             = 'customint5';
    const MFIELD_NEWENROLS              = 'customint6';
    const MFIELD_WAITLISTMESSAGE        = 'customtext1';
    const MFIELD_PRICE                  = 'customtext3';
    const MAX_USERS                     = '100';
    
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
     * Constructor
     */
    public function __construct() {
        
    }//construct

    public static function add_default_instance($courseid,$waitinglistid) {
        /* Variables */
        global $DB;
        $newInstance    = null;
        $oldRecord      = null;
        $sql            = null;

        try {
            /* Default Instance */
            $newInstance = new \stdClass();
            $newInstance->courseid         = $courseid;
            $newInstance->waitinglistid    = $waitinglistid;
            $newInstance->methodtype       = static::METHODTYPE;
            $newInstance->status           = true;
            $newInstance->emailalert       = false;

            /**
             * First remove any old entries that might be kicking
             * around from deleted waitinglist
             */
            /* SQL Instruction */
            $sql = " SELECT *
                     FROM   {" . self::TABLE . "}
                     WHERE  courseid    = " . $courseid .
                        " AND " . $DB->sql_compare_text('methodtype') . "='". static::METHODTYPE . "'";

            $oldRecord = $DB->get_records_sql($sql);
            if ($oldRecord) {
                foreach ($oldRecord as $instance) {
                    $DB->delete_records(self::TABLE, array( 'id'=>$instance->id));
                }//for_oldREcords

            }

            /**
             * Create instance
             */
            $id = $DB->insert_record(self::TABLE,$newInstance);
            if($id){
                $newInstance->id = $id;
                return $newInstance;
            }else{
                return $id;
            }
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_default_instance

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
     * Restore instance for manual enrolment.
     */
    public static function restore_instance($oldWaitId,$oldCourse,$newWaitId,$courseId) {
        /* Variables */
        global $DB;
        $newInstance    = null;
        $oldInstance    = null;
        $params         = null;

        try {
            // New instance
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
                     WHERE	  ew.methodtype like '%manual%'
                        AND   ew.waitinglistid = :waitinglistid
                        AND   ew.courseid      = :courseid ";

            // Excute - get old instance
            $oldInstance = $DB->get_record_sql($sql,$params);
            if ($oldInstance) {
                // Create a new one from the old one
                $newInstance->status           = $oldInstance->status;
                $newInstance->emailalert       = $oldInstance->emailalert;
                $newInstance->maxseats          = $oldInstance->maxseats;
                $newInstance->cost              = $oldInstance->cost;
                $newInstance->currency          = $oldInstance->currency;
                $newInstance->roleid            = $oldInstance->roleid;
                $newInstance->password          = $oldInstance->password;
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

                // Execute
                $newInstance->id = $DB->insert_record('enrol_waitinglist_method',$newInstance);
            }else {
                // Create a new one
                $newInstance->status        = true;
                $newInstance->emailalert    = false;
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
     * @throws \Exception
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
                     WHERE	  ew.methodtype like '%manual%'
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
    public function has_notifications() {
        return false;
    }
    public function show_notifications_settings_link() {
        return false;
    }
    public function has_settings() {
        return true;
    }

    /**
     * Returns maximum enrolable via this enrolment method
     * Though it is inconsistent, currently a value of 0 = unlimited
     * This is different to the waitinglist itself, where the value 0 = 0.
     * A value of 0 here effectively means "as many as the waitinglist method allows."
     *
     * @return int max enrolable
     */
    public function get_max_can_enrol(){
        //return $this->{self::MFIELD_MAXENROLLED};
    }

    public function get_dummy_form_plugin(){
        return enrol_get_plugin('manual');
    }

    public function can_enrol(\stdClass $waitinglist, $checkuserenrolment = true) {
        return false;
    }

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
     * @param       \stdClass   $waitinglist    enrolment instance
     * @param                   $userId         user id
     * @param                   $company        company
     *
     * @throws      \Exception
     * @throws      \enrol_waitinglist\method\coding_exception
     *
     * Description
     * Manual enrolment
     */
    public function waitlistrequest_manual(\stdClass $waitinglist, $userId,$company) {
        /* Variables    */
        $queue_entry    = null;
        $infoApproval   = null;
        $infoMail       = null;
        $queueUpdate    = null;

        try {
            //prepare additional fields for our queue DB entry
            $queue_entry = new \stdClass;
            $queue_entry->waitinglistid     = $waitinglist->id;
            $queue_entry->courseid          = $waitinglist->courseid;
            $queue_entry->userid            = $userId;
            $queue_entry->companyid         = $company;
            $queue_entry->methodtype        = static::METHODTYPE;
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
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//

    /**
     * Functions to enrol/unenrol users manually
     */
    public static function GetAvailableSeats($instance,$courseId) {
        /* Variables */
        global $DB;
        $count      = null;
        $entryman   = null;
        $confirmed  = null;
        $vacancies  = null;
        
        try {
            $count      = $DB->count_records('user_enrolments', array('enrolid' => $instance->id));

            $entryman   = \enrol_waitinglist\entrymanager::get_by_course($courseId);
            $confirmed  = $entryman->get_confirmed_listtotal();

            if ($instance->{self::MFIELD_MAXENROLLED}) {
                $vacancies = $instance->{self::MFIELD_MAXENROLLED}  - $confirmed;
                if (!$vacancies) {
                    $vacancies = -1;
                }
            }else {
                $vacancies = "u";
            }
            
            return $vacancies;
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetAvailableSeats
    
    /**
     * @param           $instanceId
     * @param           $courseId
     * @param           $addSearch
     *
     * @param           $removeSearch
     * @throws          \Exception
     *
     * @creationDate    19/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize manual user selectors
     */
    public static function Init_ManualSelectors($instanceId,$courseId,$addSearch,$removeSearch) {
        /* Variables */
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;
        $hashAdd    = null;
        $hashRemove = null;

        try {
            /* Initialise variables */
            $name       = 'manual_selector';
            $path       = '/enrol/waitinglist/yui/searchmanual.js';
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

            /* Manual - Add Selector       */
            self::InitManual_AddSelector($instanceId,$courseId,$addSearch,$jsModule);
            /* Manual - Remove Selector    */
            self::InitManual_RemoveSelector($instanceId,$courseId,$removeSearch,$jsModule);
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_ManualSelectors


    /**
     * @param           $reload
     * @param           $isInvoice
     * @throws          \Exception
     * 
     * @creationDate    11/09/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Initialize organization structure
     */
    public static function Init_Organization_Structure($reload,$isInvoice) {
        /* Variables */
        $wl = null;
        
        try {
            /* Get plugin */
            $wl = enrol_get_plugin('waitinglist');
            
            $wl->Init_Organization_Structure($reload,$isInvoice);
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Organization_Structure
    
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
     * Get compentece data
     */
    public function GetCompetenceData($userId) {
        /* Variables */
        $wl             = null;
        $myCompetence   = null;
        
        try {
            /* Get plugin */
            $wl = enrol_get_plugin('waitinglist');

            $myCompetence = $wl->GetUserCompetenceData($userId);
            
            return $myCompetence;
        }catch (\Exception $ex) {
            throw $ex;
        }
    }//GetCompetenceData

    /**
     * @param           $lstUsers
     * @param           $instance
     * @param           $company
     *
     * @throws          \Exception
     *
     * @creationDate    18/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Enrol users selected
     */
    public function EnrolUsers($lstUsers,$instance,$company) {
        /* Variables */

        try {
            foreach ($lstUsers as $userId) {
                $this->waitlistrequest_manual($instance,$userId,$company);
            }
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//EnrolUsers

    /**
     * @param           $lstUsers
     * @param           $instance
     *
     * @throws          \Exception
     *
     *
     */
    public function UnenrolUsers($lstUsers,$instance) {
        /* Variables */
        $waitinglist = null;

        try {
            /* Get plugin */
            $waitinglist = enrol_get_plugin('waitinglist');

            /* Unenrol Users */
            foreach ($lstUsers as $userId) {
                $waitinglist->unenrol_user($instance, $userId);
            }//forUsers
        }catch (\Exception $ex) {
            throw $ex;
        }//try_Catch
    }//UnenrolUsers

    /**
     * @param           $instanceId
     * @param           $courseId
     * @param           $levelThree
     * @param           $noDemanded
     * @param           $search
     *
     * @return          array
     * @throws          \Exception
     *
     * @creationDate    18/08/2016
     * @author          eFaktor     (fbv)
     */
    public static function FindEnrolledUsers($instanceId,$courseId,$levelThree,$noDemanded,$search) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $sqlWhere       = null;
        $params         = null;
        $locate         = '';
        $extra          = null;
        $groupName      = null;
        $total          = null;
        $enrolUsers     = array();
        $availableUsers = array();

        try {
            /* Search criteria  */
            $params = array();
            $params['course']   = $courseId;
            $params['instance'] = $instanceId;
            
            /* SQL Instruction  */
            $sql = " SELECT	 	u.id,
                                u.firstname,
                                u.lastname, 
                                u.email
                     FROM		{user}				      	u
                        JOIN	{user_enrolments}			ue	ON  ue.userid 			= u.id
                                                                AND	ue.enrolid 			= :instance
                        JOIN	{enrol_waitinglist_queue}	ewq	ON  ewq.userid 			= ue.userid 
                                                                AND ewq.methodtype LIKE 'manual'
                                                                AND ewq.waitinglistid 	= ue.enrolid
                                                                AND	ewq.courseid		= :course
                     WHERE      u.deleted 	= 0
                          AND   u.username != 'guest'  ";

            /* Search Option */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= ") AND (";
                    }
                    $locate .= " LOCATE('" . $str . "',u.firstname)
                                 OR
                                 LOCATE('" . $str . "',u.lastname)
                                 OR
                                 LOCATE('" . $str . "',CONCAT(u.firstname,' ',u.lastname))
                                 OR
                                 LOCATE('". $str . "',u.email) ";
                }//if_search_opt

                $sql .= " 	AND ($locate) ";
            }//if_search

            /* Order    */
            $sql .= " ORDER BY u.firstname, u.lastname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                $total = count($rdo);
                if ($total > self::MAX_USERS) {
                    $availableUsers = self::TooMany_UsersSelector($search,$total);
                }else {
                    if ($search) {
                        $groupName = get_string('enrolledusersmatching','enrol', $search);
                    }else {
                        $groupName = get_string('enrolledusers', 'enrol');
                    }//if_serach

                    /* Get Users    */
                    foreach ($rdo as $instance) {
                        $enrolUsers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    /* Add users    */
                    $availableUsers[$groupName] = $enrolUsers;
                }//if_max
            }else {
                /* Info to return */
                $groupName = get_string('none');
                $availableUsers[$groupName]  = array('');
            }//if_rdo

            return $availableUsers;
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindEnrolledUsers

    /**
     * @param           $instanceId
     * @param           $courseId
     * @param           $levelThree
     * @param           $noDemanded
     * @param           $search
     *
     * @return          array
     * @throws          \Exception
     *
     * @creationDate    18/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find potential users to enrol in
     */
    public static function FindCandidatesUsers($instanceId,$courseId,$levelThree,$noDemanded,$search) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $sqlWhere       = null;
        $params         = null;
        $locate         = '';
        $extra          = null;
        $groupName      = null;
        $total          = null;
        $lstUsers       = array();
        $availableUsers = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['course']   = $courseId;
            $params['three']    = $levelThree;

            /* SQL Instruction  */
            $sql = " SELECT	u.id,
                            u.firstname,
                            u.lastname,
                            u.email
                     FROM			{user}	                      u
                        LEFT JOIN (
                                    SELECT	  ue.userid
                                    FROM	  {enrol}			e
                                        JOIN  {user_enrolments} ue	ON 	ue.enrolid	= e.id
                                                                    AND	e.courseid	= :course
                                  ) uen ON uen.userid = u.id
                     ";

            /* SQL criteria     */
            $sqlWhere = " WHERE	u.deleted 	= 0
                            AND	u.username != 'guest'
                            AND uen.userid IS NULL  ";

            /* Company Demanded or not */
            if (!$noDemanded) {
                $sql .= " JOIN   {user_info_competence_data}	  uid	ON 	uid.userid 		= u.id 
                                                                    AND	uid.companyid	= :three ";
            }

            /* Add criteria */
            $sql .= $sqlWhere;

            /* With or without competence profile */
            /* Search Option */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($locate) {
                        $locate .= ") AND (";
                    }
                    $locate .= " LOCATE('" . $str . "',u.firstname)
                                 OR
                                 LOCATE('" . $str . "',u.lastname)
                                 OR
                                 LOCATE('" . $str . "',CONCAT(u.firstname,' ',u.lastname))
                                 OR
                                 LOCATE('". $str . "',u.email) ";
                }//if_search_opt

                $sql .= " 	AND ($locate) ";
            }//if_search

            /* Order    */
            $sql .= " ORDER BY u.firstname, u.lastname ";

            /* Execute  */
            if (!$noDemanded) {
                if ($levelThree) {
                    $rdo = $DB->get_records_sql($sql,$params);
                }
            }else {
                $rdo = $DB->get_records_sql($sql,$params);
            }

            if ($rdo) {
                $total = count($rdo);
                if ($total > self::MAX_USERS) {
                    $availableUsers = self::TooMany_UsersSelector($search,$total);
                }else {
                    if ($search) {
                        $groupName = get_string('enrolcandidatesmatching','enrol', $search);
                    }else {
                        $groupName = get_string('enrolcandidates', 'enrol');
                    }//if_serach

                    /* Get Users    */
                    foreach ($rdo as $instance) {
                        $lstUsers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    /* Add users    */
                    $availableUsers[$groupName] = $lstUsers;
                }//if_max
            }else {
                /* Info to return */
                if ($search) {
                    $groupName = get_string('nomatchingusers', '', $search);
                }else {
                    $groupName = get_string('none');
                }

                $availableUsers[$groupName]  = array('');
            }//if_rdo

            return $availableUsers;
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindCandidatesUsers

    
    
    /**
     * @param           $search
     * @param           $total
     *
     * @return          array
     * @throws          \Exception
     *
     * @creationDate    18/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the options to show when there are too many users
     */
    private static function TooMany_UsersSelector($search,$total) {
        /* Variables    */
        $availableUsers = array();
        $info           = null;
        $tooMany        = null;
        $searchMore     = null;

        try {
            if ($search) {
                /* Info too many    */
                $info = new \stdClass();
                $info->count    = $total;
                $info->search   = $search;

                /* Get Info to show  */
                $tooMany    = get_string('toomanyusersmatchsearch', '', $info);
                $searchMore = get_string('pleasesearchmore');

            }else {
                /* Get Info to show */
                $tooMany    = get_string('toomanyuserstoshow', '', $total);
                $searchMore = get_string('pleaseusesearch');
            }//if_search

            /* Info to return   */
            $availableUsers[$tooMany]       = array('');
            $availableUsers[$searchMore]    = array('');

            return $availableUsers;
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//TooMany_UsersSelector

    /**
     * @param           $instanceId
     * @param           $courseId
     * @param           $search
     * @param           $jsModule
     *
     * @throws          \Exception
     *
     * @creationDate    19/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize Add users selector
     */
    private static function InitManual_AddSelector($instanceId,$courseId,$search,$jsModule) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindCandidatesUsers';
            $options['name']        = 'addselect';
            $options['multiselect'] = true;

            /* Connect Selector User    */
            $hash                           = md5(serialize($options));
            $USER->manual_selectors[$hash]  = $options;

            $PAGE->requires->js_init_call('M.core_user.init_manual_selector',
                                          array('addselect',$hash,$courseId,$instanceId,$search),
                                          false,
                                          $jsModule
                                         );
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//InitManual_AddSelector

    /**
     * @param           $instanceId
     * @param           $courseId
     * @param           $search
     * @param           $jsModule
     *
     * @throws          \Exception
     *
     * @creationDate    19/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize Remove Users selector
     */
    private static function InitManual_RemoveSelector($instanceId,$courseId,$search,$jsModule) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            $options['class']       = 'FindEnrolledUsers';
            $options['name']        = 'removeselect';
            $options['multiselect'] = true;

            /* Connect Selector User    */
            $hash                           = md5(serialize($options));
            $USER->manual_selectors[$hash]  = $options;

            $PAGE->requires->js_init_call('M.core_user.init_manual_selector',
                                          array('removeselect',$hash,$courseId,$instanceId,$search),
                                          false,
                                          $jsModule
                                         );
        }catch (\Exception $ex) {
            throw $ex;
        }//try_catch
    }//InitManual_RemoveSelector
}//waitinglist_manual