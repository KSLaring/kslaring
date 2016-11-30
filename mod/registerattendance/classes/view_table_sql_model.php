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

defined('MOODLE_INTERNAL') || die;

/**
 * Model class for the mod_registerattendance view table
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_registerattendance_view_table_sql_model extends mod_registerattendance_widget {
    /* @var object The course module object */
    protected $cm = null;

    /* @var array The related filter data returned from the form. */
    protected $filterdata = null;

    /* @var object The related sort data returned from the table. */
    protected $sort = null;

    /* @var int The number of not paged but filtered records. */
    protected $countrecords = 0;

    /* @var array with the userids of all enrolled users. */
    protected $enrolledusers = array();

    /* @var array with the userids of users with the completed state. */
    protected $completedusers = array();

    /**
     * Construct the view table.
     *
     * @param object $filterdata The filter data
     * @param string $sort       The sort string
     * @param        $where
     * @param array  $whereparams
     * @param int    $start      The first paged user
     * @param int    $rowstoshwo The amonut of rows to show
     * @param object $cm         The course module
     */
    public function __construct($filterdata, $sort, $where, $whereparams, $start = 0, $rowstoshwo = 0, $cm) {
        // Create the data object and set the first values.
        parent::__construct();

        // Set up the data.
        $this->cm = $cm;
        $this->filterdata = $filterdata;
        $this->sort = $sort;

        // Get the enrolled users.
        $this->get_data_from_db($start, $rowstoshwo, $where, $whereparams);
    }//constructor

    /**
     * Getter for $countrecords
     *
     * @return int
     */
    public function get_countrecords() {
        return $this->countrecords;
    }

    /**
     * Setter for $countrecords
     *
     * @param int $countrecords The number of records
     */
    public function set_countrecords($countrecords) {
        $this->countrecords = $countrecords;
    }

    /**
     * Getter for $enrolledusers
     *
     * @return array
     */
    public function get_enrolledusers() {
        return $this->enrolledusers;
    }

    /**
     * Getter for $completedusers
     *
     * @return array
     */
    public function get_completedusers() {
        return $this->completedusers;
    }

    /**
     * Get the enrolled users.
     *
     * @param int    $start      The first paged user
     * @param int    $rowstoshwo The amonut of rows to show
     * @param $where
     * @param $whereparams
     */
    protected function get_data_from_db($start, $rowstoshwo, $where, $whereparams) {
        $result = null;

        $userfields = 'u.id, ' . get_all_user_name_fields(true, 'u');
        $context = context_course::instance($this->cm->course);

        // Remove the »attended« column from the sort for the DB query.
        $dbsort = null;
        if (strpos($this->sort, 'attended') === false) {
            $dbsort = $this->sort;
        } else {
            $sortitems = explode(',', $this->sort);
            $reducedsortitems = array();
            foreach ($sortitems as $item) {
                if (strpos($this->sort, 'attended') === false) {
                    $reducedsortitems[] = trim($item);
                }
            }
            $dbsort = implode(', ', $reducedsortitems);
        }

        list($count, $result) = $this->get_enrolled_users($context, '', 0, $userfields, $dbsort, $where, $whereparams,
                                                          $start, $rowstoshwo);

        // Add Municipality && Workplace.
        $this->add_municipality_workplace($result);

        $this->countrecords = $count;
        $this->data = $result;
    }//get_data_from_db

    /**
     * Returns list of users enrolled into course.
     *
     * @package   core_enrol
     * @category  access
     *
     * @param context $context
     * @param string  $withcapability
     * @param int     $groupid    0 means ignore groups, any other value limits the result by group id
     * @param string  $userfields requested user record fields
     * @param string  $orderby
     * @param string  $where
     * @param array   $whereparams
     * @param int     $limitfrom  return a subset of records, starting at this point (optional, required if $limitnum is set).
     * @param int     $limitnum   return a subset comprising this many records (optional, required if $limitfrom is set).
     * @param bool    $onlyactive consider only active enrolments in enabled plugins and time restrictions
     *
     * @return array of user records
     * @throws Exception
     */
    protected function get_enrolled_users(context $context, $withcapability = '', $groupid = 0, $userfields = 'u.*',
        $orderby = '', $where = '', $whereparams = array(), $limitfrom = 0, $limitnum = 0, $onlyactive = false) {
        global $DB;
        $params = array();
        $sql = '';
        $enrolledusers = null;
        $sqlcompleted = null;
        $completedusers = null;

        try {
            // Search Criteria.
            $params['contextid'] = $context->id;
            $params['courseid']   = $this->cm->course;

            // Completed Users - Get user list with completed state.
            $sqlcompleted = " SELECT   userid
                              FROM    {course_modules_completion}
                              WHERE   coursemoduleid  = ?
                                AND   completionstate = 1 ";

            $completedusers = $DB->get_records_sql($sqlcompleted, array($this->cm->id));
            $this->completedusers = array_keys($completedusers);

            // Enrolled Users - Only users enrolled as student.
            $sql = " SELECT 	DISTINCT  u.id, 
                                          u.firstnamephonetic,
                                          u.lastnamephonetic,
                                          u.middlename,
                                          u.alternatename,
                                          u.firstname,
                                          u.lastname,
                                          '' as 'municipality',
                                          '' as 'workplace'
                     FROM 		{user} 				u 
                        -- ENROLLED USERS. STUDENTS
                        JOIN	{user_enrolments}	ue	ON 	ue.userid 		= u.id
                        JOIN	{enrol}				e	ON	e.id 			= ue.enrolid
                                                        AND e.courseid 		= :courseid
                        JOIN	{role_assignments}	ra	ON	ra.userid		= ue.userid
                                                        AND	ra.contextid 	= :contextid
                        JOIN	{role}				ro	ON	ro.id			= ra.roleid
                                                        AND	ro.archetype	= 'student'
                     WHERE	u.deleted = 0
                        AND	u.username != 'guest' ";

            // Get Total Enrolled Users.
            $enrolledusers = $DB->get_records_sql($sql, $params);
            $this->enrolledusers = array_keys($enrolledusers);

            // Apply the filter.
            if ($where) {
                $sql = "$sql AND $where";
                $params = array_merge($params, $whereparams);
            }

            // Handle the »show attended« filter.
            $showattended = 0;
            if (!empty($this->filterdata['showattended'])) {
                $showattended = (int)$this->filterdata['showattended'];
            }

            // Get the users with the completed state and either use or exclude them
            // depending on the showattended setting.
            if ($showattended && !empty($this->completedusers)) {
                // If »show attended« then 1, else if »show not attended« then 2.
                $equal = $showattended === 1;
                list($in, $inparams) = $DB->get_in_or_equal($this->completedusers, SQL_PARAMS_NAMED, 'param', $equal);

                $sql = "$sql AND u.id $in";
                $params = array_merge($params, $inparams);
            } else if ($showattended === 1 && empty($this->completedusers)) {
                // Attended users requested but no attended users to show.
                return array(0, array());
            }

            if ($orderby) {
                $sql = "$sql ORDER BY $orderby";
            } else {
                list($sort, $sortparams) = users_order_by_sql('u');
                $sql = "$sql ORDER BY $sort";
                $params = array_merge($params, $sortparams);
            }

            // Get the users and add the attended state for each user.
            $result = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
            $result = $this->add_attended($result);

            /* @TODO Find a better to get the whole amount of records without $limitfrom, $limitnum for paging. */

            // The counted records need to return the number of all records without the $limitfrom, $limitnum restrictions
            // otherwise the paging bar is not shown.
            return array(count($DB->get_records_sql($sql, $params)), $result);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_enrolled_users

    protected function get_enrolled_users_old(context $context, $withcapability = '', $groupid = 0, $userfields = 'u.*',
        $orderby = null, $where = null, $whereparams = null, $limitfrom = 0, $limitnum = 0, $onlyactive = false) {
        global $DB;

        list($esql, $params) = get_enrolled_sql($context, $withcapability, $groupid, $onlyactive);
        $sql = "SELECT $userfields
                 FROM {user} u
                 JOIN ($esql) je ON je.id = u.id
                WHERE u.deleted = 0";

        // Could there be a friadmin helper method to get the SQL for the user's selected municipality
        // and/or workplace similar to the »get_enrolled_sql« method above? Such a method would be very
        // helpful to build modular SQL queries and the SQL would need to be written once and could
        // be used at several places.
        // We could for example add some code like the following (or similar):
        // // list($munisql, $params) = friadmin_helper::get_muni_sql();
        // and add a line of SQL like "JOIN ($munisql) muni ON muni.id = u.id"
        // What do you think?

        // Get user list with enrolled users unfiltered.
        // Why is twice this sql???
        $enrolledsql = "SELECT $userfields
                         FROM {user} u
                         JOIN ($esql) je ON je.id = u.id
                        WHERE u.deleted = 0";

        $enrolledusers = $DB->get_records_sql($enrolledsql, $params);
        $this->enrolledusers = array_keys($enrolledusers);

        // Get user list with completed state.
        $completedsql = "SELECT userid
           FROM {course_modules_completion}
          WHERE coursemoduleid = ?
            AND completionstate = 1";

        $completedusers = $DB->get_records_sql($completedsql, array($this->cm->id));
        $this->completedusers = array_keys($completedusers);

        // Add filters.
        if ($where) {
            $sql = "$sql AND $where";
            $params = array_merge($params, $whereparams);
        }

        // Handle the »show attended« filter.
        $showattended = 0;
        if (!empty($this->filterdata['showattended'])) {
            $showattended = (int)$this->filterdata['showattended'];
        }

        // Get the users with the completed state and either use or exclude them
        // depending on the showattended setting.
        if ($showattended === 1 && !empty($this->completedusers)) {
            // If »show attended« then 1, else if »show not attended« then 2.
            $equal = $showattended === 1;
            list($in, $inparams) = $DB->get_in_or_equal($this->completedusers, SQL_PARAMS_NAMED, 'param', $equal);

            $sql = "$sql AND u.id $in";
            $params = array_merge($params, $inparams);
        } else if ($showattended === 1 && empty($this->completedusers)) {
            // Attended users requested but no attended users to show.
            return array(0, array());
        }

        if ($orderby) {
            $sql = "$sql ORDER BY $orderby";
        } else {
            list($sort, $sortparams) = users_order_by_sql('u');
            $sql = "$sql ORDER BY $sort";
            $params = array_merge($params, $sortparams);
        }

        // Get the users and add the attended state for each user.
        $result = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
        $result = $this->add_attended($result);

        /* @TODO Find a better way to get the whole amount of records without $limitfrom, $limitnum for paging. */
        return array(count($DB->get_records_sql($sql, $params)), $result);
    }

    /**
     * Add the municipality and workplace data
     *
     * @param       $data       Array
     * @throws      Exception
     *
     * @updateDate  19/10/2016
     * @author      eFaktor (fbv)
     *
     * Description
     * Get workplace form the course or from competence profile
     */
    protected function add_municipality_workplace(&$data) {
        /* Variables */
        global $CFG;
        $inUsers        = null;
        $notIn          = null;
        $usersMuni      = null;
        $usersWorkplace = null;

        try {
            if ($data) {
                $inUsers = implode(',',array_keys($data));

                /* Add Municipalities */
                if (file_exists($CFG->dirroot . '/user/profile/field/municipality/municipalitylib.php')) {
                    require_once($CFG->dirroot . '/user/profile/field/municipality/municipalitylib.php');

                    /* Get Municipalities   */
                    $usersMuni = MunicipalityProfile::MunicipalitiesConnected($inUsers);
                    if ($usersMuni) {
                        foreach ($usersMuni as $info) {
                            $data[$info->userid]->municipality = $info->municipality;
                        }//muni
                    }//if_usersMuni
                }//municipality

                /* Add Workplace    */
                /**
                 * First, it gets workspace connected with course via enrolment
                 */
                $usersWorkplace = self::GetWorkplaceEnrolled($this->cm->course,$inUsers);

                /**
                 * Second, it gets workplace from competence profile for users without company
                 * in the enrolment method.
                 */
                if ($usersWorkplace) {
                    $notIn      = implode(',',array_keys($usersWorkplace));
                    $inUsers    = implode(',',array_keys(array_diff_key($data,$usersWorkplace)));
                }else {
                    $notIn = 0;
                }
                if ($inUsers) {
                    self::GetWorkspaceCompetence($usersWorkplace,$inUsers,$notIn);
                }


                /*
                 * Add workplace to the user data
                 */
                if ($usersWorkplace) {
                    foreach ($usersWorkplace as $id => $workplace) {
                        $data[$id]->workplace = $workplace;
                    }//info
                }//if_UsersWorkplace
            }//if_data
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_municipality_workplace

    /**
     * @param           $courseId
     * @param           $inUsers
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    19/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get workspace connected with the course
     */
    private static function GetWorkplaceEnrolled($courseId,$inUsers) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $usersWorkplace = array();
        
        try {
            /* Search Criteria  */
            $params = array();
            $params['course'] = $courseId;
            
            /* SQL Instruction  */
            $sql = " SELECT	ewq.userid,
                            CONCAT(co.industrycode, ' - ',co.name) 	as 'workplace'
                     FROM		{enrol_waitinglist_queue}	ewq 
                        JOIN	{report_gen_companydata}	co	ON co.id = ewq.companyid	
                     WHERE	ewq.courseid 	= :course
                        AND	ewq.companyid  != 0
                        AND ewq.userid IN ($inUsers) ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $usersWorkplace[$instance->userid] = $instance->workplace;
                }//for_rdo
            }//if_redo
            
            return $usersWorkplace;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetWorkplaceEnrolled

    /**
     * @param           $usersWorkplace
     * @param           $inUsers
     * @param           $noIn
     *
     * @throws          Exception
     *
     * @creationDate    19/10/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get worskplace from user profile
     */
    private static function GetWorkspaceCompetence(&$usersWorkplace,$inUsers,$noIn) {
        /* Variables    */
        global $DB;
        $sql = null;
        $rdo = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT 	uic.userid,
                                GROUP_CONCAT(DISTINCT CONCAT(co.industrycode, ' - ',co.name) ORDER BY co.industrycode,co.name SEPARATOR '#SE#') 	as 'workplace'
                     FROM		{user_info_competence_data}	uic
                        JOIN	{report_gen_companydata}	co	ON co.id = uic.companyid
                     WHERE	uic.userid IN 		($inUsers)
                        AND	uic.userid NOT IN 	($noIn)
                        AND uic.level = 3
                     GROUP BY uic.userid ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $usersWorkplace[$instance->userid] = str_replace('#SE#',',',$instance->workplace);
                }//for
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetWorkspaceCompetence

    protected function add_municipality_workplace_old($data) {
        $result = $data;

        if ($result) {
            $result = array_map(array($this, 'callback_municipality_workplace'), $data);
        }

        return $result;
    }


    /**
     * Add the user attended state
     *
     * @param array $data Array with user data objects
     *
     * @return array The extended user data
     */
    protected function add_attended($data) {
        $result = $data;

        if ($data) {
            $result = array_map(array($this, 'callback_attended'), $data);
        }

        return $result;
    }

    /**
     * Callback for the user attended state
     *
     * Check if the id of the current user row is in the list of completedusers.
     *
     * @param object $row The user object from the database
     *
     * @return object The extended user data row
     */
    protected function callback_attended($row) {
        if (in_array($row->id, $this->completedusers)) {
            $row->attended = 1;
        } else {
            $row->attended = 0;
        }

        return $row;
    }
}
