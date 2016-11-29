<?php
/**
 * Course Template - Library
 *
 * @package         local/
 * @subpackage      course_template/library
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/01/2016
 * @author          eFaktor     (fbv)
 *
 */

define('ENROL_WAITING_SELF',1);
define('ENROL_WAITING_BULK',2);
define('ENROL_FIELD_INVOICE','customint8');
define('ENROL_FIELD_APPROVAL','customint7');
define('ENROL_FIELD_CUTOFFDATE', 'customint1');
define('ENROL_FIELD_MAXENROLMENTS', 'customint2');
define('METHOD_FIELD_MAXENROLMENTS','customint3');
define('ENROL_FIELD_WAITLISTSIZE', 'customint6');
define('ENROL_FIELD_SENDWELCOMEMESSAGE', 'customint4');
define('ENROL_FIELD_SENDWAITLISTMESSAGE', 'customint5');
define('ENROL_FIELD_PRICE','customtext3');
define('ENROL_FIELD_WELCOME_MESSAGE','customtext1');
define('ENROL_FIELD_SELF_WAITING_MESSAGE','customtext1');
define('ENROL_FIELD_BULK_WAITING_MESSAGE','customtext1');
define('ENROL_FIELD_BULK_RENOVATION_MESSAGE','customtext2');
define('ENROL_FIELD_BULK_SEND_CONFIRMATION','customint5');

define('ENROL_FILED_COURSE_INTERNAL_PRICE','customtext3');
define('ENROL_FILED_COURSE_EXTERNAL_PRICE','customtext4');

define('SETTINGS_DEFAULT_SIZE',100);
define('MAX_TEACHERS_PAGE',100);
define('ACTION_ENROLMENT',1);
define('ACTION_SHOW_COURSE',0);

define('CT_APPROVAL_NONE',0);
define('CT_APPROVAL_REQUIRED',1);
define('CT_APPROVAL_MESSAGE',2);
define('CT_COMPANY_NO_DEMANDED',3);

class CourseTemplate {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @throws          Exception
     *
     * @creationDate    21/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize the sector selector
     */
    public static function init_locations_sector() {
        /* Variables    */
        global $PAGE;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;

        try {
            /* Initialise variables */
            $name       = 'sectors';
            $path       = '/local/course_page/yui/sectors.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => null
            );

            $PAGE->requires->js_init_call('M.core_coursepage.init_sectors',
                array('course_location','course_sector'),
                false,
                $jsModule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//init_locations_sector
    
    /**
     * @param           $addSearch
     * @param           $removeSearch
     * @param           $course
     * @param           $nonediting
     *
     * @throws          Exception
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize both selector for teachers
     */
    public static function Init_Teachers_Selectors($addSearch,$removeSearch,$course,$nonediting = 0) {
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
            $name       = 'teacher_selector';
            $path       = '/local/friadmin/course_template/js/search.js';
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

            /* Teachers - Add Selector       */
            self::Init_Teachers_AddSelector($addSearch,$jsModule,$course,$nonediting);
            /* Teachers - Remove Selector    */
            self::Init_Teachers_RemoveSelector($removeSearch,$jsModule,$course,$nonediting);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Managers_Selectors
    
    /**
     * @param           $courseId
     * @param           $search
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find the current teachers connected with the course
     */
    public static function FindTeachers_Selectors($courseId,$search) {
        /* Variables */
        global $DB;
        $params                 = null;
        $sql                    = null;
        $sqlSearch              = null;
        $rdo                    = null;
        $context                = null;
        $teachers               = array();
        $currentTeachers        = array();
        $groupName              = null;

        try {
            /* Context  */
            $context = context_course::instance($courseId);

            /* Search Criteria  */
            $params = array();
            $params['context']      = $context->id;
            $params['archetype']    = 'editingteacher';

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT u.id,
                                         u.firstname,
                                         u.lastname,
                                         u.email
                     FROM		{user}					u
                        JOIN	{role_assignments}		ra		ON		ra.userid 		= u.id
                                                                AND     ra.contextid    = :context
                        JOIN	{role}					r		ON		r.id 			= ra.roleid
                                                                AND		r.archetype 	= :archetype

                     WHERE		u.deleted = 0
                     ";

            /* Search   */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($sqlSearch) {
                        $sqlSearch .= ") AND (";
                    }
                    $sqlSearch .= " LOCATE('" . $str . "',u.firstname)
                                    OR
                                    LOCATE('" . $str . "',u.lastname)
                                    OR
                                    LOCATE('" . $str . "',CONCAT(u.firstname,' ',u.lastname))
                                    OR
                                    LOCATE('" . $str . "',u.email) ";
                }//if_search_opt

                $sql .=  " AND ($sqlSearch) ";
            }//if_search

            /* Order Criteria */
            $sql .= "  ORDER BY 	u.firstname, u.lastname ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                if ($search) {
                    $groupName = get_string('current_teachers_matching', 'local_friadmin', $search);
                }else {
                    $groupName = get_string('current_teachers', 'local_friadmin');
                }//if_serach

                /* Get Teachers    */
                foreach ($rdo as $instance) {
                    $teachers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                }//for_Rdo

                /* Add users    */
                $currentTeachers[$groupName] = $teachers;
            }else {
                /* Info to return */
                $groupName = get_string('no_teachers','local_friadmin');
                $currentTeachers[$groupName]  = array('');
            }//if_rdo

            return $currentTeachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindTeachers_Selectors

    /**
     * @param           $courseId
     * @param           $search
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Find potential teachers fro the course
     */
    public static function FindPotentialTeachers_Selector($courseId,$search) {
        /* Variables */
        global $DB;
        $params                 = null;
        $sql                    = null;
        $sqlSearch              = null;
        $rdo                    = null;
        $context                = null;
        $teachers               = array();
        $potentialTeachers      = array();
        $groupName              = null;
        $total                  = null;
        $lstTeachersStudents    = null;

        try {
            /* Context  */
            $context = context_course::instance($courseId);

            /* Teachers Students Connected with */
            $lstTeachersStudents = self::GetTeachersStudents($context->id);
            if ($lstTeachersStudents) {
                $lstTeachersStudents = implode(',',$lstTeachersStudents);

            }else {
                $lstTeachersStudents = 0;
            }

            /* Search Criteria  */
            $params = array();
            $params['context']      = $context->id;

            /* SQL Instruction */
            $sql = " SELECT	u.id,
                            u.firstname,
                            u.lastname,
                            u.email
                     FROM		{user}	u
                     WHERE 	u.deleted = 0
                        AND	u.username != 'guest'
                        AND u.id NOT IN ($lstTeachersStudents) ";

            /* Search   */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($sqlSearch) {
                        $sqlSearch .= ") AND (";
                    }
                    $sqlSearch .= " LOCATE('" . $str . "',u.firstname)
                                    OR
                                    LOCATE('" . $str . "',u.lastname)
                                    OR
                                    LOCATE('" . $str . "',CONCAT(u.firstname,' ',u.lastname))
                                    OR
                                    LOCATE('" . $str . "',u.email) ";
                }//if_search_opt

                $sql .=  " AND ($sqlSearch) ";
            }//if_search

            /* Order Criteria */
            $sql .= "  ORDER BY 	u.firstname, u.lastname ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if($rdo) {
                $total = count($rdo);
                if ($total > MAX_TEACHERS_PAGE) {
                    $potentialTeachers = self::TooMany_TeachersSelector($search,$total);

                }else {
                    if ($search) {
                        $groupName = get_string('pot_teachers_matching', 'local_friadmin', $search);
                    }else {
                        $groupName = get_string('pot_teachers', 'local_friadmin');
                    }//if_serach

                    /* Get Teachers    */
                    foreach ($rdo as $instance) {
                        $teachers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    /* Add Users */
                    $potentialTeachers[$groupName] = $teachers;
                }//if_tooMany
            }//if_rdo

            return $potentialTeachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindPotentialTeachers_Selector

    /**
     * @param           $courseId
     * @param           $search
     * 
     * @return          array
     * @throws          Exception
     * 
     * @creationDate    18/10/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Find potential users for non editing teachers
     */
    public static function FindNoEdPotentialTeachers_Selector($courseId,$search) {
        /* Variables */
        global $DB;
        $params                 = null;
        $sql                    = null;
        $sqlSearch              = null;
        $rdo                    = null;
        $context                = null;
        $noEdTeachers           = array();
        $potentialNoEdTeachers  = array();
        $groupName              = null;
        $total                  = null;
        $lstTeachersStudents    = null;

        try {
            /* Context  */
            $context = context_course::instance($courseId);

            /* Teachers Students Connected with */
            $lstTeachersStudents = self::GetTeachersStudents($context->id,true);
            if ($lstTeachersStudents) {
                $lstTeachersStudents = implode(',',$lstTeachersStudents);
            }else {
                $lstTeachersStudents = 0;
            }

            /* SQL Instruction */
            $sql = " SELECT	u.id,
                            u.firstname,
                            u.lastname,
                            u.email
                     FROM		{user}	u
                     WHERE 	u.deleted = 0
                        AND	u.username != 'guest'
                        AND u.id NOT IN ($lstTeachersStudents) ";

            /* Search   */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($sqlSearch) {
                        $sqlSearch .= ") AND (";
                    }
                    $sqlSearch .= " LOCATE('" . $str . "',u.firstname)
                                    OR
                                    LOCATE('" . $str . "',u.lastname)
                                    OR
                                    LOCATE('" . $str . "',CONCAT(u.firstname,' ',u.lastname))
                                    OR
                                    LOCATE('" . $str . "',u.email) ";
                }//if_search_opt

                $sql .=  " AND ($sqlSearch) ";
            }//if_search

            /* Order Criteria */
            $sql .= "  ORDER BY 	u.firstname, u.lastname ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if($rdo) {
                $total = count($rdo);
                if ($total > MAX_TEACHERS_PAGE) {
                    $potentialNoEdTeachers = self::TooMany_TeachersSelector($search,$total);

                }else {
                    if ($search) {
                        $groupName = get_string('pot_noed_teachers_matching', 'local_friadmin', $search);
                    }else {
                        $groupName = get_string('pot_noed_teachers', 'local_friadmin');
                    }//if_serach

                    /* Get Teachers    */
                    foreach ($rdo as $instance) {
                        $noEdTeachers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    /* Add Users */
                    $potentialNoEdTeachers[$groupName] = $noEdTeachers;
                }//if_tooMany
            }//if_rdo

            return $potentialNoEdTeachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindNoEdPotentialTeachers_Selector

    /**
     * @param           $courseId
     * @param           $search
     * 
     * @return          array
     * @throws          Exception
     * 
     * @creationDate    18/10/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Find no editing teachers connected with the course
     */
    public static function FindNoEdTeachers_Selectors($courseId,$search) {
        /* Variables */
        global $DB;
        $params                 = null;
        $sql                    = null;
        $sqlSearch              = null;
        $rdo                    = null;
        $context                = null;
        $noEdTeachers           = array();
        $currentNoEdTeachers    = array();
        $groupName              = null;
        $total                  = null;

        try {
            /* Context  */
            $context = context_course::instance($courseId);

            /* Search Criteria  */
            $params = array();
            $params['context']      = $context->id;

            /* SQL Instruction */
            $sql = " SELECT	DISTINCT 	u.id,
                                        u.firstname,
                                        u.lastname,
                                        u.email
                     FROM		{user}				u
                        JOIN	{role_assignments}	ra	ON		ra.userid 		= u.id
                                                        AND     ra.contextid    = :context
                        JOIN	{role}				r	ON		r.id 			= ra.roleid
                                                        AND		r.archetype 	IN ('teacher')
                     WHERE	u.deleted = 0
                        AND	u.username != 'guest' ";

            /* Search   */
            if ($search) {
                $extra = explode(' ',$search);
                foreach ($extra as $str) {
                    if ($sqlSearch) {
                        $sqlSearch .= ") AND (";
                    }
                    $sqlSearch .= " LOCATE('" . $str . "',u.firstname)
                                    OR
                                    LOCATE('" . $str . "',u.lastname)
                                    OR
                                    LOCATE('" . $str . "',CONCAT(u.firstname,' ',u.lastname))
                                    OR
                                    LOCATE('" . $str . "',u.email) ";
                }//if_search_opt

                $sql .=  " AND ($sqlSearch) ";
            }//if_search

            /* Order Criteria */
            $sql .= "  ORDER BY 	u.firstname, u.lastname ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                if ($search) {
                    $groupName = get_string('current_noed_teachers_matching', 'local_friadmin', $search);
                }else {
                    $groupName = get_string('current_noed_teachers', 'local_friadmin');
                }//if_serach

                /* Get Teachers    */
                foreach ($rdo as $instance) {
                    $noEdTeachers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                }//for_Rdo

                /* Add users    */
                $currentNoEdTeachers[$groupName] = $noEdTeachers;
            }else {
                /* Info to return */
                $groupName = get_string('no_noed_teachers','local_friadmin');
                $currentNoEdTeachers[$groupName]  = array('');
            }//if_rdo

            return $currentNoEdTeachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//FindNoEdTeachers_Selectors

    /**
     * @param           $courseId
     * @param           $teachers
     * @param           $noEditing
     *
     * @throws          Exception
     *
     * @creationDate    20/06/0216
     * @author          eFaktor     (fbv)
     *
     * Description
     * Assign/Enrol user as a teacher
     * 
     * @updateDate      18/10/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Assign a non editing teacher
     */
    public static function AssignTeacher($courseId,$teachers,$noEditing = false) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $plugin     = null;

        try {
            /* Plugin Info  */
            $plugin = enrol_get_plugin('manual');

            /* Get Role Id for teacher  */
            if ($noEditing) {
                $rdo = $DB->get_record('role',array('archetype' => 'teacher'));
            }else {
                $rdo = $DB->get_record('role',array('archetype' => 'editingteacher'));
            }

            if ($rdo) {
                $instance = $DB->get_record('enrol',array('courseid' => $courseId,'enrol' => 'manual'));
                /* Assign teacher role  */
                foreach ($teachers as $teacher) {
                    /* Enrol user as a teacher */
                    $plugin->enrol_user($instance,$teacher,$rdo->id);
                }//for_each
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AssignTeacher

    /**
     * @param           $courseId
     * @param           $teachers
     * @param           $noEditing
     *
     * @throws          Exception
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Unassign the user as a teacher. Unenrol
     * 
     * @upateDate       18/10/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Unassign the user as no editing teacher
     */
    public static function UnassignTeacher($courseId,$teachers,$noEditing = false) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $plugin     = null;

        try {
            /* Plugin Info  */
            $plugin = enrol_get_plugin('manual');

            /* Get Role Id for teacher  */
            if ($noEditing) {
                $rdo = $DB->get_record('role',array('archetype' => 'teacher'));
            }else {
                $rdo = $DB->get_record('role',array('archetype' => 'editingteacher'));
            }

            if ($rdo) {
                $instance = $DB->get_record('enrol',array('courseid' => $courseId,'enrol' => 'manual'));
                /* Unassign teacher role  */
                foreach ($teachers as $teacher) {
                    $plugin->unenrol_user($instance,$teacher);
                }//for_each
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UnassignTeacher

    /**
     * @param           $categoryId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get category name
     */
    public static function GetCategoryName($categoryId) {
        /* Variables */
        global $DB;
        $rdo = null;

        try {
            /* Get category name */
            $rdo = $DB->get_record('course_categories',array('id' => $categoryId),'name');
            if ($rdo) {
                return $rdo->name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetCategoryName

    /**
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user has the correct permissions to create a new course from the
     * template
     */
    public static function HasCorrectPermissions() {
        /* Variables    */
        global $DB, $USER;
        $contextCat     = null;
        $contextCourse  = null;
        $contextSystem  = null;
        
        try {
            if (is_siteadmin($USER)) {
                return true;
            }
            
            /* Fist, check if the user has the correct permissions  */
            /* Search Criteria  */
            $params = array();
            $params['user']     = $USER->id;
            $contextCat         = CONTEXT_COURSECAT;
            $contextCourse      = CONTEXT_COURSE;
            $contextSystem      = CONTEXT_SYSTEM;

            /* SQL Instruction  */
            $sql = " SELECT		ra.id,
                                ra.contextid,
                                ra.userid
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 	r.id 			= ra.roleid
                                                            AND	r.archetype		IN ('manager','coursecreator')
                                                            AND r.shortname     = r.archetype
                        JOIN    {context}           ct      ON  ct.id			= ra.contextid
                                                            AND	ct.contextlevel	IN ($contextCat,$contextCourse,$contextSystem)
                     WHERE		ra.userid     = :user
                         ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql, $params);
            if ($rdo) {
                return true;
            } else {
                return false;
            }//if_rdo
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//HasCorrectPermissions

    /**
     * @param           $courseId
     * @param           $courseTemplate
     * @param           $format
     *
     * @return          mixed|null|stdClass
     * @throws          Exception
     *
     * @creationDate    27/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Enrol instance
     */
    public static function GetEnrolInstance($courseId,$courseTemplate,$format) {
        /* Variables */
        $instance = null;

        try {
            switch ($format) {
                case 'classroom':
                case 'classroom_frikomport':
                    $instance = self::GetWaitingEnrolInstance($courseId,$courseTemplate);

                    break;
                case 'elearning_frikomport':
                case 'netcourse':
                    $instance = self::GetSelfEnrolInstance($courseId,$courseTemplate);

                    break;
            }//format

            return $instance;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//GetEnrolInstance

    /**
     * @param           $data
     * @param           $action
     *
     * @throws          Exception
     *
     * @creationDate    27/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update the self-enrolment instance for the new course
     */
    public static function SelfEnrolment($data,$action) {
        /* Variables */
        global $DB;
        $instance = null;

        try {
            /* Data */
            $instance = new stdClass();
            $instance->enrol            = 'self';
            $instance->courseid         = $data->id;
            $instance->name             = $data->name;
            $instance->status           = $data->status;
            $instance->customint6       = $data->customint6;
            $instance->password         = $data->password;
            $instance->customint1       = $data->customint1;
            $instance->roleid           = $data->roleid;
            $instance->enrolperiod      = $data->enrolperiod;
            $instance->expirynotify     = $data->expirynotify;
            $instance->expirythreshold  = $data->expirythreshold;
            $instance->enrolstartdate   = $data->enrolstartdate;
            $instance->enrolenddate     = $data->enrolenddate;
            $instance->customint2       = $data->customint2;
            $instance->customint3       = $data->customint3;
            $instance->customint5       = $data->customint5;
            $instance->customtext1      = $data->customtext1;

            switch ($action) {
                case 'add':
                    $DB->insert_record('enrol',$instance);

                    break;
                case 'update':
                    $instance->id = $data->instanceid;
                    $DB->update_record('enrol',$instance);

                    break;
            }//action
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//SelfEnrolment
    
    /**
     * @param           $data
     * @param           $new
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update Waiting Enrolment
     *
     * @updateDate      26/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add manual method
     */
    public static function UpdateWaitingEnrolment($data,$new = false) {
        /* Variables */
        global $DB;
        $trans          = null;
        $enrolInstance  = null;
        $methodSelf     = null;
        $methodBulk     = null;
        $methodManual   = null;
        $time           = null;

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local time   */
            $time = time();

            /* Enrol Instance   */
            $enrolInstance = new stdClass();
            $enrolInstance->id                                      = $data->instanceid;
            $enrolInstance->courseid                                = $data->id;
            if ($data->welcome_message) {
                $enrolInstance->{ENROL_FIELD_WELCOME_MESSAGE}       = $data->welcome_message;
            }
            $enrolInstance->{ENROL_FIELD_CUTOFFDATE}                = $data->date_off;
            $enrolInstance->{ENROL_FIELD_WAITLISTSIZE}              = $data->list_size;
            $enrolInstance->{ENROL_FIELD_MAXENROLMENTS}             = $data->max_enrolled;
            $enrolInstance->{ENROL_FIELD_INVOICE}                   = $data->invoice;
            $enrolInstance->{ENROL_FIELD_APPROVAL}                  = $data->approval;
            $enrolInstance->{ENROL_FILED_COURSE_INTERNAL_PRICE}     = $data->priceinternal;
            $enrolInstance->{ENROL_FILED_COURSE_EXTERNAL_PRICE}     = $data->priceexternal;

            $enrolInstance->timemodified                    = $time;
            /* Execute  */
            $DB->update_record('enrol',$enrolInstance);

            /* Self Method  */
            $methodSelf = new stdClass();
            $methodSelf->timemodified                       = $time;
            if (isset($data->password)) {
                $methodSelf->password                       = $data->password;
            }
            $methodSelf->{ENROL_FIELD_SELF_WAITING_MESSAGE} = $data->self_waiting_message;

            /* Bulk Method  */
            $methodBulk = new stdClass();
            $methodBulk->timemodified                           = $time;
            $methodBulk->{ENROL_FIELD_BULK_WAITING_MESSAGE}     = $data->bulk_waiting_message;
            $methodBulk->{ENROL_FIELD_BULK_RENOVATION_MESSAGE}  = $data->bulk_renovation_message;

            /* Manaul Method    */
            $methodManual = new stdClass();


            /* New or update */
            if (!$new) {
                $methodSelf->id     = $data->selfid;
                $methodBulk->id     = $data->bulkid;
                $methodManual->id   = $data->manualid;
            }else {
                $rdo = $DB->get_records('enrol_waitinglist_method',array('waitinglistid' => $data->instanceid,'courseid' => $data->id));
                if ($rdo) {
                    foreach ($rdo as $instance) {
                        if ($instance->methodtype == 'self') {
                            $methodSelf->id = $instance->id;
                        }else if ($instance->methodtype == 'unnamedbulk')  {
                            $methodBulk->id = $instance->id;
                        }else if ($instance->methodtype == 'manual') {
                            $methodManual->id = $instance->id;
                        }
                    }
                }
            }//if_new

            /* Method Enrol Instance    */
            switch ($data->waitinglist) {
                case ENROL_WAITING_SELF:
                    /* Self Method  */
                    $methodSelf->unenrolenddate  = $data->unenrolenddate;
                    $methodSelf->status          = 1;
                    /* Bulk Method  */
                    $methodBulk->status = 0;
                    $methodManual->status   = 1;
                    break;
                case ENROL_WAITING_BULK:
                    /* Self Method  */
                    $methodSelf->status = 0;
                    /* Bulk Method  */
                    $methodBulk->status = 1;
                    $methodManual->status   = 1;

                    break;
            }//switch

            /* Update - Self method */
            $DB->update_record('enrol_waitinglist_method',$methodSelf);
            /* Update - Bulk method */
            $DB->update_record('enrol_waitinglist_method',$methodBulk);
            /* Update - Manual Method   */
            $DB->update_record('enrol_waitinglist_method',$methodManual);

            /* Commit */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//UpdateWaitingEnrolment

    /**
     * @param           $data
     *
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Create waiting enrolment instance
     *
     * @updateDate      26/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Manual method
     */
    public static function CreateWaitingEnrolment($data) {
        /* Variables */
        global $DB;
        $trans          = null;
        $course         = null;
        $method         = null;
        $plugin         = null;
        $methodId       = null;

        /* Begin Transaction    */
        $trans = $DB->start_delegated_transaction();

        try {
            /* Local time   */
            $time = time();

            /* Plugin info  */
            $plugin = enrol_get_plugin('waitinglist');

            $course = get_course($data->id);

            /* Add the instance */
            $plugin->add_default_instance($course);
            /* Get the id */
            $enrol = $DB->get_record('enrol',array('courseid'=>$course->id, 'enrol'=>'waitinglist'),'id');
            $data->instanceid = $enrol->id;

            self::UpdateWaitingEnrolment($data,true);

            /* Commit */
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            /* Rollback */
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//CreateWaitingEnrolment

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $contextId
     * @param           bool $noEditing
     * 
     * @return          array
     * @throws          Exception
     * 
     * @creationDate    18/10/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Get teaches, noneditingteachers and students connected with the corse
     */
    private static function GetTeachersStudents($contextId,$noEditing = false) {
        /* Variables */
        global $DB;
        $rdo                    = null;
        $sql                    = null;
        $params                 = null;
        $sqlEditing             = null;
        $lstTeachersStudents    = array();

        try {
            /* Search Criteria  */
            $params = array();
            $params['context']      = $contextId;

            if ($noEditing) {
                $sqlEditing = " AND	r.archetype 	IN ('editingteacher','student','teacher')";
            }else {
                $sqlEditing = " AND	r.archetype 	IN ('editingteacher','student') ";
            }

            /* SQL Instruction  */
            $sql = " SELECT	DISTINCT u.id
                     FROM		{user}				u
                        JOIN	{role_assignments}	ra	ON	ra.userid 		= u.id
                                                        AND ra.contextid    = :context
                        JOIN	{role}				r	ON	r.id 			= ra.roleid
                                                        $sqlEditing    
                     WHERE	u.deleted = 0
                        AND	u.username != 'guest' ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lstTeachersStudents[$instance->id] = $instance->id;
                }//for_rdo
            }

            return $lstTeachersStudents;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetTeachersStudents

    /**
     * @param           $courseId
     * @param           $courseTemplate
     *
     * @return          mixed|null|stdClass
     * @throws          Exception
     *
     * @creationDate    27/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the enrolment instance for self enrolment method
     */
    private static function GetSelfEnrolInstance($courseId,$courseTemplate) {
        /* Variables */
        global $DB;
        $params         = null;
        $rdoCourse      = null;
        $rdoTemplate    = null;
        $sql            = null;
        $instance       = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['enrol']        = 'self';

            /* Execute Course */
            $params['courseid']     = $courseId;
            $rdoCourse = $DB->get_record('enrol',$params);

            /* Execute Template */
            $params['courseid']     = $courseTemplate;
            $rdoTemplate = $DB->get_record('enrol',$params);

            /* Return the right instance    */
            if ($rdoCourse && $rdoTemplate) {
                /* Course && Template */
                $rdoTemplate->id        = $rdoCourse->id;
                $rdoTemplate->courseid  = $courseId;

                return $rdoTemplate;
            }else if ($rdoTemplate && !$rdoCourse) {
                /* Template but no course   */
                $rdoTemplate->id        = null;
                $rdoTemplate->courseid  = $courseId;

                return $rdoTemplate;
            }else {
                /* No Template No Course    */
                /* Instance */
                $instance = new stdClass();
                $instance->id               = null;
                $instance->courseid         = $courseId;

                return $instance;
            }//if
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetSelfEnrolInstance

    /**
     * @param           $courseId
     * @param           $courseTemplate
     *
     * @return          mixed|stdClass
     * @throws          Exception
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get enrol instance connected with the method
     *
     * @updateDate      17/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the right information from the template, if the instance exists
     */
    private static function GetWaitingEnrolInstance($courseId,$courseTemplate) {
        /* Variables */
        global $DB;
        $params         = null;
        $rdoCourse      = null;
        $rdoTemplate    = null;
        $sql            = null;
        $instance       = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['enrol']        = 'waitinglist';
            $params['self']         = 'self';
            $params['bulk']         = 'unnamedbulk';
            $params['manual']       = 'manual';

            /* SQL Instruction */
            $sql = " SELECT	e.id,
                            e.courseid,
                            IF(e.customint1,e.customint1,0)	as 'date_off',
                            e.password                      as 'password',
                            e.customtext1                   as 'welcome_message',
                            es.customtext1                  as 'self_waiting_message',
                            un.customtext1                  as 'bulk_waiting_message',
                            un.customtext2                  as 'bulk_renovation_message',
                            un.customint5                   as 'bulk_send_confirmation',
                            e.customint2 	                as 'max_enrolled',
                            e.customint6 	                as 'list_size',
                            e.customint8 	                as 'invoice',
                            e.customint7 	                as 'approval',
                            e.customtext3                   as 'priceinternal',
                            e.customtext4                   as 'priceexternal',
                            es.id							as 'selfid',
                            es.unenrolenddate               as 'unenrolenddate',     
                            un.id							as 'bulkid',
                            ma.id                           as 'manualid'
                     FROM		{enrol}						e
                        -- SELF METHOD
                        JOIN 	{enrol_waitinglist_method}	es	ON 	es.waitinglistid 	= e.id
                                                                AND es.courseid 		= e.courseid
                                                                AND	es.methodtype		= :self
                        -- UNNAMED METHOD
                        JOIN 	{enrol_waitinglist_method}	un	ON 	un.waitinglistid 	= e.id
                                                                AND un.courseid 		= e.courseid
                                                                AND	un.methodtype		= :bulk
                        -- manual
                        JOIN 	{enrol_waitinglist_method}	ma	ON 	ma.waitinglistid 	= e.id
                                                                AND ma.courseid 		= e.courseid
                                                                AND	ma.methodtype		= :manual
                     WHERE	e.enrol 	= :enrol
                        AND	e.courseid 	= :courseid ";

            /* Execute Course */
            $params['courseid']     = $courseId;
            $rdoCourse = $DB->get_record_sql($sql,$params);

            /* Execute Template */
            $params['courseid']     = $courseTemplate;
            $rdoTemplate = $DB->get_record_sql($sql,$params);

            /* Return the right instance    */
            if ($rdoCourse && $rdoTemplate) {
                /* Course && Template */
                $rdoTemplate->id        = $rdoCourse->id;
                $rdoTemplate->selfid    = $rdoCourse->selfid;
                $rdoTemplate->bulkid    = $rdoCourse->bulkid;
                $rdoTemplate->courseid  = $courseId;

                return $rdoTemplate;
            }else if ($rdoTemplate && !$rdoCourse) {
                /* Template but no course   */
                $rdoTemplate->id        = null;
                $rdoTemplate->selfid    = null;
                $rdoTemplate->bulkid    = null;
                $rdoTemplate->courseid  = $courseId;

                return $rdoTemplate;
            }else {
                /* No Template No Course    */
                /* Instance */
                $instance = new stdClass();
                $instance->id               = null;
                $instance->selfid           = null;
                $instance->bulkid           = null;
                $instance->manualid         = null;
                $instance->courseid         = $courseId;
                $instance->password         = null;
                $instance->date_off         = 0;
                $instance->unenrolenddate   = 0;
                $instance->max_enrolled     = 0;
                $instance->list_size        = SETTINGS_DEFAULT_SIZE;
                $instance->invoice          = 0;
                $instance->approval         = 0;
                $instance->priceinternal    = 0;
                $instance->priceexternal    = 0;
                $instance->welcome_message  = null;
                /**
                 * @updateDate  30/08/2016
                 * @author      eFaktor     (fbv)
                 * 
                 * Description
                 * Add welcome messages when is created from scratch
                 */
                $instance->self_waiting_message     = get_string('waitlistmessagetext_self','enrol_waitinglist');
                $instance->bulk_waiting_message     = get_string('waitlistmessagetext_unnamedbulk','enrol_waitinglist');;
                $instance->bulk_renovation_message  = get_string('confirmedmessagetext_unnamedbulk','enrol_waitinglist');
                $instance->bulk_send_confirmation   = true;

                return $instance;
            }//if
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetWaitingEnrolInstance


    /**
     * @param           $search
     * @param           $total
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the options to show when there are too many teachers
     */
    private static function TooMany_TeachersSelector($search,$total) {
        /* Variables    */
        $availableTeachers  = array();
        $info               = null;
        $tooMany            = null;
        $searchMore         = null;

        try {
            if ($search) {
                /* Info too many    */
                $info = new stdClass();
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
            $availableTeachers[$tooMany]       = array('');
            $availableTeachers[$searchMore]    = array('');

            return $availableTeachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//TooMany_TeachersSelector

    /**
     * @param           $search
     * @param           $jsModule
     * @param           $course
     * @param           $nonediting
     *
     * @throws          Exception
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize selector for adding teachers
     */
    private static function Init_Teachers_AddSelector($search,$jsModule,$course,$nonediting) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            if ($nonediting) {
                $options['class']   = 'FindNoEdPotentialTeachers_Selector';
            }else {
                $options['class']   = 'FindPotentialTeachers_Selector';
            }

            $options['name']        = 'addselect';
            $options['multiselect'] = true;

            /* Connect Teacher Selector    */
            $hash                           = md5(serialize($options));
            $USER->teacher_selectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_teachers_selector',
                                          array('addselect',$hash, $course,$search),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Teachers_AddSelector

    /**
     * @param           $search
     * @param           $jsModule
     * @param           $course
     * @param           $nonediting
     *
     * @throws          Exception
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * initialize selector for removing teachers
     */
    private static function Init_Teachers_RemoveSelector($search,$jsModule,$course,$nonediting) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            /* Initialise Options Selector  */
            $options = array();
            if ($nonediting) {
                $options['class']   = 'FindNoEdTeachers_Selectors';
            }else {
                $options['class']   = 'FindTeachers_Selectors';
            }
            $options['name']        = 'removeselect';
            $options['multiselect'] = true;

            /* Connect Teacher Selector    */
            $hash                           = md5(serialize($options));
            $USER->teacher_selectors[$hash] = $options;

            $PAGE->requires->js_init_call('M.core_user.init_teachers_selector',
                                          array('removeselect',$hash, $course,$search),
                                          false,
                                          $jsModule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Init_Teachers_RemoveSelector
}//CourseTemplate