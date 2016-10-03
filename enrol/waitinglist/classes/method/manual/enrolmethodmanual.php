<?php
/**
 * Waiting List - Manual submethod
 *
 * @package         enrol/waitinglist
 * @subpackage      classes/method/manual
 * @copyright       2013 efaktor    {@link http://www.efaktor.no}
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
        $count      = 0;
        $entryman   = null;
        $confirmed  = 0;
        $vacancies  = null;
        
        try {
            $count      = $DB->count_records('user_enrolments', array('enrolid' => $instance->id));
            $entryman   = \enrol_waitinglist\entrymanager::get_by_course($courseId);
            $confirmed  = $entryman->get_confirmed_listtotal();
            
            if ($instance->{self::MFIELD_MAXENROLLED}) {
                $vacancies = $instance->{self::MFIELD_MAXENROLLED} - $count - $confirmed;
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
            
            /* Init Organization Structure */
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
            $params['three']    = $levelThree;
            
            /* SQL Instruction  */

            $sql = " SELECT	u.id,
                            u.firstname,
                            u.lastname,
                            u.email
                     FROM		{user}				          u
                        JOIN	{user_enrolments}	          ue	ON 	ue.userid       = u.id
                                                                    AND ue.enrolid      = :instance
                        JOIN	{enrol}				          e	    ON 	e.id            = ue.enrolid
                                                                    AND e.courseid      = :course ";

            /* SQL Criteria */
            $sqlWhere = " WHERE	u.deleted 	= 0
                            AND	u.username != 'guest' ";

            /* Company Demanded or not */
            if (!$noDemanded) {
                $sql .= " JOIN   {user_info_competence_data}    uid	ON 	uid.userid 		= u.id 
                                                                    AND	uid.companyid	= :three ";
            }

            /* Add criteria */
            $sql .= $sqlWhere;

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