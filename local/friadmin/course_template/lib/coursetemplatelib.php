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
 * Course Template - Library
 *
 * @package         local/
 * @subpackage      course_template/library
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

define('AS_TEACHER',1);
define('UN_TEACHER',2);
define('AS_INSTRUCTOR',3);
define('UN_INSTRUCTOR',4);

class CourseTemplate {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     *
     * Description
     * Initialize the sector selector
     *
     * @creationDate    21/03/2016
     * @author          eFaktor     (fbv)
     *
     * @throws          Exception
     */
    public static function init_locations_sector() {
        /* Variables    */
        global $PAGE;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;

        try {
            // Initialise variables
            $name       = 'sectors';
            $path       = '/local/course_page/yui/sectors.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');

            // Initialise js module
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
     * Description
     * Initialize both selector for teachers
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $addSearch
     * @param           $removeSearch
     * @param           $course
     * @param           $nonediting
     *
     * @throws          Exception
     */
    public static function init_teachers_selectors($addSearch,$removeSearch,$course,$nonediting = 0) {
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
            // Initialise variables
            $name       = 'teacher_selector';
            $path       = '/local/friadmin/course_template/js/search.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpOne     = array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%');
            $grpTwo     = array('nomatchingusers', 'moodle', '%%SEARCHTERM%%');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpOne,$grpTwo,$grpThree);

            // Initialise js module
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
                             );

            // Teachers - add selector
            self::init_teachers_add_selector($addSearch,$jsModule,$course,$nonediting);
            // Teachers - remove selector
            self::init_teachers_remove_selector($removeSearch,$jsModule,$course,$nonediting);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//init_teachers_selectors
    
    /**
     * Description
     * Find the current teachers connected with the course
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $courseId
     * @param           $search
     *
     * @return          array
     * @throws          Exception
     */
    public static function find_teachers_selectors($courseId,$search) {
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
            // Context
            $context = context_course::instance($courseId);

            // Search criteria
            $params = array();
            $params['context']      = $context->id;
            $params['archetype']    = 'editingteacher';

            // SQL instruction
            $sql = " SELECT	DISTINCT 
                              u.id,
                              u.firstname,
                              u.lastname,
                              u.email
                     FROM	  {user}				u
                        JOIN  {role_assignments}	ra	  ON	ra.userid 		= u.id
                                                          AND   ra.contextid    = :context
                        JOIN  {role}				r	  ON	r.id 			= ra.roleid
                                                          AND	r.archetype 	= :archetype

                     WHERE	  u.deleted = 0
                     ";

            // Search
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

            // Order criteria
            $sql .= "  ORDER BY 	u.firstname, u.lastname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                if ($search) {
                    $groupName = get_string('current_teachers_matching', 'local_friadmin', $search);
                }else {
                    $groupName = get_string('current_teachers', 'local_friadmin');
                }//if_serach

                // Get teachers
                foreach ($rdo as $instance) {
                    $teachers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                }//for_Rdo

                // Add existing teachers
                $currentTeachers[$groupName] = $teachers;
            }else {
                // Info to return
                $groupName = get_string('no_teachers','local_friadmin');
                $currentTeachers[$groupName]  = array('');
            }//if_rdo

            return $currentTeachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//find_teachers_selectors

    /**
     * Description
     * Find potential teachers fro the course
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $courseId
     * @param           $search
     *
     * @return          array
     * @throws          Exception
     */
    public static function find_potential_teachers_selector($courseId,$search) {
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
            // Context
            $context = context_course::instance($courseId);

            // Teachers and students connected with
            $lstTeachersStudents = self::get_teachers_students($context->id);
            if ($lstTeachersStudents) {
                $lstTeachersStudents = implode(',',$lstTeachersStudents);
            }else {
                $lstTeachersStudents = 0;
            }

            // Search criteria
            $params = array();
            $params['context']      = $context->id;

            // SQL instruction
            $sql = " SELECT	u.id,
                            u.firstname,
                            u.lastname,
                            u.email
                     FROM	{user}	u
                     WHERE 	u.deleted = 0
                        AND	u.username != 'guest'
                        AND u.id NOT IN ($lstTeachersStudents) ";

            // Search
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

            // Order criteria
            $sql .= "  ORDER BY 	u.firstname, u.lastname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if($rdo) {
                $total = count($rdo);
                if ($total > MAX_TEACHERS_PAGE) {
                    $potentialTeachers = self::too_many_teachers_selector($search,$total);

                }else {
                    if ($search) {
                        $groupName = get_string('pot_teachers_matching', 'local_friadmin', $search);
                    }else {
                        $groupName = get_string('pot_teachers', 'local_friadmin');
                    }//if_serach

                    // Get teachers
                    foreach ($rdo as $instance) {
                        $teachers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    // Add potential users
                    $potentialTeachers[$groupName] = $teachers;
                }//if_tooMany
            }//if_rdo

            return $potentialTeachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//find_potential_teachers_selector

    /**
     * Description
     * Find potential users for non editing teachers
     *
     * @creationDate    18/10/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $courseId
     * @param           $search
     * 
     * @return          array
     * @throws          Exception
     */
    public static function find_noed_potential_teachers_selector($courseId,$search) {
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
            // Context
            $context = context_course::instance($courseId);

            // Teachers and students connected with
            $lstTeachersStudents = self::get_teachers_students($context->id,true);
            if ($lstTeachersStudents) {
                $lstTeachersStudents = implode(',',$lstTeachersStudents);
            }else {
                $lstTeachersStudents = 0;
            }

            // SQL instruction
            $sql = " SELECT	u.id,
                            u.firstname,
                            u.lastname,
                            u.email
                     FROM	{user}	u
                     WHERE 	u.deleted = 0
                        AND	u.username != 'guest'
                        AND u.id NOT IN ($lstTeachersStudents) ";

            // Search
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

            // Order criteria
            $sql .= "  ORDER BY 	u.firstname, u.lastname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if($rdo) {
                $total = count($rdo);
                if ($total > MAX_TEACHERS_PAGE) {
                    $potentialNoEdTeachers = self::too_many_teachers_selector($search,$total);

                }else {
                    if ($search) {
                        $groupName = get_string('pot_noed_teachers_matching', 'local_friadmin', $search);
                    }else {
                        $groupName = get_string('pot_noed_teachers', 'local_friadmin');
                    }//if_serach

                    // Get course instructors
                    foreach ($rdo as $instance) {
                        $noEdTeachers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                    }//for_Rdo

                    // Add potential course instructors
                    $potentialNoEdTeachers[$groupName] = $noEdTeachers;
                }//if_tooMany
            }//if_rdo

            return $potentialNoEdTeachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//find_noed_potential_teachers_selector

    /**
     * Description
     * Find no editing teachers connected with the course
     *
     * @creationDate    18/10/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $courseId
     * @param           $search
     * 
     * @return          array
     * @throws          Exception
     *
     */
    public static function find_noed_teachers_selectors($courseId,$search) {
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
            // Context
            $context = context_course::instance($courseId);

            // Search criteria
            $params = array();
            $params['context']      = $context->id;

            // SQL instruction
            $sql = " SELECT	DISTINCT 	
                              u.id,
                              u.firstname,
                              u.lastname,
                              u.email
                     FROM	  {user}				u
                        JOIN  {role_assignments}	ra	ON		ra.userid 		= u.id
                                                        AND     ra.contextid    = :context
                        JOIN  {role}				r	ON		r.id 			= ra.roleid
                                                        AND		r.archetype 	IN ('teacher')
                     WHERE	  u.deleted = 0
                        AND	  u.username != 'guest' ";

            // Search
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

            // Order criteria
            $sql .= "  ORDER BY 	u.firstname, u.lastname ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                if ($search) {
                    $groupName = get_string('current_noed_teachers_matching', 'local_friadmin', $search);
                }else {
                    $groupName = get_string('current_noed_teachers', 'local_friadmin');
                }//if_serach

                // Get course instructors
                foreach ($rdo as $instance) {
                    $noEdTeachers[$instance->id] = $instance->firstname . " " . $instance->lastname . "(" . $instance->email . ")";
                }//for_Rdo

                // Add existing course instructors
                $currentNoEdTeachers[$groupName] = $noEdTeachers;
            }else {
                // Info to return
                $groupName = get_string('no_noed_teachers','local_friadmin');
                $currentNoEdTeachers[$groupName]  = array('');
            }//if_rdo

            return $currentNoEdTeachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//find_noed_teachers_selectors

    /**
     * Description
     * Assign a teacher or course instructor to the course
     * Send notification        (27/01/2017)
     *
     * @updateDate              27/01/2017
     * @author                  eFaktor     (fbv)
     *
     * @creationDate            18/10/2017
     * @author                  eFaktor     (fbv)
     *
     *
     * @param           integer $courseId   Course id
     * @param           array   $teachers   Teachers conencted
     * @param           bool    $noEditing  Teacher or course instructor
     *
     * @throws                  Exception
     */
    public static function assign_teacher($courseId,$teachers,$noEditing = false) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $plugin     = null;
        $type       = null;

        try {
            // Plugin info
            $plugin = enrol_get_plugin('manual');

            // Get role id for the teacher or course instructor
            if ($noEditing) {
                $type = AS_TEACHER;
                $rdo  = $DB->get_record('role',array('archetype' => 'teacher'));
            }else {
                $type = AS_INSTRUCTOR;
                $rdo  = $DB->get_record('role',array('archetype' => 'editingteacher'));
            }

            if ($rdo) {
                $instance = $DB->get_record('enrol',array('courseid' => $courseId,'enrol' => 'manual'));
                // Assign teacher or course instructor
                foreach ($teachers as $teacher) {
                    // Enrol user as a teacher  or course instructor
                    $plugin->enrol_user($instance,$teacher,$rdo->id);

                    // Send notification
                    self::send_notification_assignment_teacher($teacher,$courseId,$type);
                }//for_each
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//assign_teacher


    /**
     * Description
     * Unassign user as a teacher or course instructor
     * Send notification    (27/01/2017)
     *
     * @updateDate          27/01/2017
     * @author              eFaktor     (fbv)
     *
     * @creationDate        20/06/2016
     * @author              eFaktor     (fbv)
     *
     * @param       integer $courseId
     * @param       array   $teachers
     * @param       bool    $noEditing
     *
     * @throws              Exception
     */
    public static function unassign_teacher($courseId,$teachers,$noEditing = false) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $plugin     = null;
        $type       = null;

        try {
            // Plugin info
            $plugin = enrol_get_plugin('manual');

            // Get role id for teacher/course instructor
            if ($noEditing) {
                $type   = UN_TEACHER;
                $rdo    = $DB->get_record('role',array('archetype' => 'teacher'));
            }else {
                $type   = UN_INSTRUCTOR;
                $rdo    = $DB->get_record('role',array('archetype' => 'editingteacher'));
            }

            if ($rdo) {
                $instance = $DB->get_record('enrol',array('courseid' => $courseId,'enrol' => 'manual'));
                // Unassign teacher or course instructor
                foreach ($teachers as $teacher) {
                    // Enrol user as course instructor or teacher
                    $plugin->unenrol_user($instance,$teacher);

                    // Send notification
                    self::send_notification_assignment_teacher($teacher,$courseId,$type);
                }//for_each
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//unassign_teacher

    /**
     * Description
     * Get category name
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $categoryId
     *
     * @return          null
     * @throws          Exception
     */
    public static function get_category_name($categoryId) {
        /* Variables */
        global $DB;
        $rdo = null;

        try {
            // Get category name
            $rdo = $DB->get_record('course_categories',array('id' => $categoryId),'name');
            if ($rdo) {
                return $rdo->name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_category_name

    /**
     * Description
     * Check if the user has the correct permissions to create a new course from the
     * template
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * @return          bool
     * @throws          Exception
     */
    public static function has_correct_permissions() {
        /* Variables    */
        global $DB, $USER;
        $contextCat     = null;
        $contextCourse  = null;
        $contextSystem  = null;
        
        try {
            // Admin always permission
            if (is_siteadmin($USER)) {
                return true;
            }

            // First, check if the user has the correct permissions
            // Search criteria
            $params = array();
            $params['user']     = $USER->id;
            $contextCat         = CONTEXT_COURSECAT;
            $contextCourse      = CONTEXT_COURSE;
            $contextSystem      = CONTEXT_SYSTEM;

            // SQL instruction
            $sql = " SELECT	  ra.id,
                              ra.contextid,
                              ra.userid
                     FROM	  {role_assignments}  ra
                        JOIN  {role}			  r	  ON  r.id 			  = ra.roleid
                                                      AND r.archetype	  IN ('manager','coursecreator')
                                                      AND r.shortname     = r.archetype
                        JOIN  {context}           ct  ON  ct.id			  = ra.contextid
                                                      AND ct.contextlevel IN ($contextCat,$contextCourse,$contextSystem)
                     WHERE    ra.userid     = :user
                         ";

            // Execute
            $rdo = $DB->get_records_sql($sql, $params);
            if ($rdo) {
                return true;
            } else {
                return false;
            }//if_rdo
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//has_correct_permissions

    /**
     * Description
     * Enrol instance
     *
     * @creationDate    27/06/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $courseId
     * @param           $courseTemplate
     * @param           $format
     *
     * @return          mixed|null|stdClass
     * @throws          Exception
     *
     */
    public static function get_enrol_instance($courseId,$courseTemplate,$format) {
        /* Variables */
        $instance = null;

        try {
            switch ($format) {
                case 'classroom':
                case 'classroom_frikomport':
                    $instance = self::get_waiting_enrol_instance($courseId,$courseTemplate);

                    break;
                case 'elearning_frikomport':
                case 'netcourse':
                    $instance = self::get_self_enrol_instance($courseId,$courseTemplate);

                    break;
            }//format

            return $instance;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//get_enrol_instance

    /**
     * Description
     * Update the self-enrolment instance for the new course
     *
     * @creationDate    27/06/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $data
     * @param           $action
     *
     * @throws          Exception
     */
    public static function self_enrolment($data,$action) {
        /* Variables */
        global $DB;
        $instance   = null;
        $sql        = null;
        $rdo        = null;
        $params     = null;

        try {
            // Data
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

            // Update to status = 1 the rest of enrolments
            // Search criteria
            $params = array();
            $params['course']       = $data->id;
            $params['enrol']        = 'self';
            $params['enrol_doskom'] = 'wsdoskom';
            $params['status']       = 0;

            // SQL Instruction
            $sql = " SELECT	e.id,
                            e.status
                     FROM	{enrol}	e
                     WHERE	e.courseid = :course
                        AND e.enrol   != :enrol
                        AND e.enrol	  != :enrol_doskom
                        AND e.status   = :status ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                // update status
                foreach ($rdo as $instance) {
                    $instance->status = 1;

                    $DB->update_record('enrol',$instance);
                }//for_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//self_enrolment
    
    /**
     * Description
     * Update Waiting Enrolment
     * Add manual method    (26/08/2016)
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      26/08/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $data
     * @param           $new
     *
     * @return          bool
     * @throws          Exception
     */
    public static function update_waiting_enrolment($data,$new = false) {
        /* Variables */
        global $DB;
        $trans          = null;
        $enrolInstance  = null;
        $methodSelf     = null;
        $methodBulk     = null;
        $methodManual   = null;
        $time           = null;

        // Begin transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Local time
            $time = time();

            // Enrol instance
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
            $enrolInstance->timemodified                            = $time;

            // Execute
            $DB->update_record('enrol',$enrolInstance);

            // Self method
            $methodSelf = new stdClass();
            $methodSelf->timemodified                       = $time;
            if (isset($data->password)) {
                $methodSelf->password                       = $data->password;
            }
            $methodSelf->{ENROL_FIELD_SELF_WAITING_MESSAGE} = $data->self_waiting_message;

            // Bulk method
            $methodBulk = new stdClass();
            $methodBulk->timemodified                           = $time;
            $methodBulk->{ENROL_FIELD_BULK_WAITING_MESSAGE}     = $data->bulk_waiting_message;
            $methodBulk->{ENROL_FIELD_BULK_RENOVATION_MESSAGE}  = $data->bulk_renovation_message;

            // Manual method
            $methodManual = new stdClass();

            // new or update
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

            // Method enrol instance
            switch ($data->waitinglist) {
                case ENROL_WAITING_SELF:
                    // Self method
                    $methodSelf->unenrolenddate  = $data->unenrolenddate;
                    $methodSelf->status          = 1;
                    // Bulk method
                    $methodBulk->status = 0;
                    $methodManual->status   = 1;
                    break;
                case ENROL_WAITING_BULK:
                    // Self method
                    $methodSelf->status = 0;
                    // Bulk method
                    $methodBulk->status = 1;
                    $methodManual->status   = 1;

                    break;
            }//switch

            // Update self method
            $DB->update_record('enrol_waitinglist_method',$methodSelf);
            // Update bulk method
            $DB->update_record('enrol_waitinglist_method',$methodBulk);
            // Update manual method
            $DB->update_record('enrol_waitinglist_method',$methodManual);

            // Commit
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//update_waiting_enrolment

    /**
     * Description
     * Create waiting enrolment instance
     * Add Manual method    (26/08/2016)
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      26/08/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $data
     *
     * @return          bool
     * @throws          Exception
     */
    public static function create_waiting_enrolment($data) {
        /* Variables */
        global $DB;
        $trans          = null;
        $course         = null;
        $method         = null;
        $plugin         = null;
        $methodId       = null;

        // Begin transaction
        $trans = $DB->start_delegated_transaction();

        try {
            // Plugin info
            $plugin = enrol_get_plugin('waitinglist');

            // Course data
            $course = get_course($data->id);

            // Add instance
            $plugin->add_default_instance($course);
            // Get instance id
            $enrol = $DB->get_record('enrol',array('courseid'=>$course->id, 'enrol'=>'waitinglist'),'id');
            $data->instanceid = $enrol->id;

            // Update it
            self::update_waiting_enrolment($data,true);

            // Commit
            $trans->allow_commit();

            return true;
        }catch (Exception $ex) {
            // Rollback
            $trans->rollback($ex);

            throw $ex;
        }//try_catch
    }//create_waiting_enrolment

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Get teaches, noneditingteachers and students connected with the corse
     *
     * @creationDate    18/10/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $contextId
     * @param           bool $noEditing
     * 
     * @return          array
     * @throws          Exception
     */
    private static function get_teachers_students($contextId,$noEditing = false) {
        /* Variables */
        global $DB;
        $rdo                    = null;
        $sql                    = null;
        $params                 = null;
        $sqlEditing             = null;
        $lstTeachersStudents    = array();

        try {
            // Search criteria
            $params = array();
            $params['context']      = $contextId;

            // teacher or course instructor
            if ($noEditing) {
                $sqlEditing = " AND	r.archetype 	IN ('editingteacher','student','teacher')";
            }else {
                $sqlEditing = " AND	r.archetype 	IN ('editingteacher','student') ";
            }

            // SQL instruction
            $sql = " SELECT	DISTINCT 
                              u.id
                     FROM	  {user}				u
                        JOIN  {role_assignments}	ra	ON	ra.userid 		= u.id
                                                        AND ra.contextid    = :context
                        JOIN  {role}				r	ON	r.id 			= ra.roleid
                                                        $sqlEditing    
                     WHERE	  u.deleted = 0
                        AND	  u.username != 'guest' ";

            // Execute
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
    }//get_teachers_students

    /**
     * Description
     * Get the enrolment instance for self enrolment method
     *
     * @creationDate    27/06/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $courseId
     * @param           $courseTemplate
     *
     * @return          mixed|null|stdClass
     * @throws          Exception
     */
    private static function get_self_enrol_instance($courseId,$courseTemplate) {
        /* Variables */
        global $DB;
        $params         = null;
        $rdoCourse      = null;
        $rdoTemplate    = null;
        $sql            = null;
        $instance       = null;

        try {
            // Search criteria
            $params = array();
            $params['enrol']        = 'self';

            // Execute course
            $params['courseid']     = $courseId;
            $rdoCourse = $DB->get_record('enrol',$params);

            // Execute template
            $params['courseid']     = $courseTemplate;
            $rdoTemplate = $DB->get_record('enrol',$params);

            // Get the right instance
            if ($rdoCourse && $rdoTemplate) {
                // Course and template
                $rdoTemplate->id        = $rdoCourse->id;
                $rdoTemplate->courseid  = $courseId;
                $rdoTemplate->status    = 0;

                return $rdoTemplate;
            }else if ($rdoTemplate && !$rdoCourse) {
                // Template but no course
                $rdoTemplate->id        = null;
                $rdoTemplate->courseid  = $courseId;
                $rdoTemplate->status    = 0;

                return $rdoTemplate;
            }else {
                // No template, no course
                // Instance
                $instance = new stdClass();
                $instance->id               = null;
                $instance->courseid         = $courseId;
                $instance->status           = 0;

                return $instance;
            }//if
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_self_enrol_instance

    /**
     * Description
     * Get enrol instance connected with the method
     * Get the right information from the template,
     * if the instance exists       (17/06/2016)
     *
     * @creationDate    12/01/2016
     * @author          eFaktor     (fbv)
     *
     * @updateDate      17/06/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $courseId
     * @param           $courseTemplate
     *
     * @return          mixed|stdClass
     * @throws          Exception
     */
    private static function get_waiting_enrol_instance($courseId,$courseTemplate) {
        /* Variables */
        global $DB;
        $params         = null;
        $rdoCourse      = null;
        $rdoTemplate    = null;
        $sql            = null;
        $instance       = null;

        try {
            // Search criteria
            $params = array();
            $params['enrol']        = 'waitinglist';
            $params['self']         = 'self';
            $params['bulk']         = 'unnamedbulk';
            $params['manual']       = 'manual';

            // SQL instruction
            $sql = " SELECT	  e.id,
                              e.courseid,
                              IF(e.customint1,e.customint1,0)	as 'date_off',
                              e.password                        as 'password',
                              e.customtext1                     as 'welcome_message',
                              es.customtext1                    as 'self_waiting_message',
                              un.customtext1                    as 'bulk_waiting_message',
                              un.customtext2                    as 'bulk_renovation_message',
                              un.customint5                     as 'bulk_send_confirmation',
                              e.customint2 	                    as 'max_enrolled',
                              e.customint6 	                    as 'list_size',
                              e.customint8 	                    as 'invoice',
                              e.customint7 	                    as 'approval',
                              e.customtext3                     as 'priceinternal',
                              e.customtext4                     as 'priceexternal',
                              es.id							    as 'selfid',
                              es.unenrolenddate                 as 'unenrolenddate',     
                              un.id							    as 'bulkid',
                              ma.id                             as 'manualid'
                     FROM	  {enrol}						e
                        -- SELF METHOD
                        JOIN  {enrol_waitinglist_method}	es	ON 	es.waitinglistid 	= e.id
                                                                AND es.courseid 		= e.courseid
                                                                AND	es.methodtype		= :self
                        -- UNNAMED METHOD
                        JOIN  {enrol_waitinglist_method}	un	ON 	un.waitinglistid 	= e.id
                                                                AND un.courseid 		= e.courseid
                                                                AND	un.methodtype		= :bulk
                        -- manual
                        JOIN  {enrol_waitinglist_method}	ma	ON 	ma.waitinglistid 	= e.id
                                                                AND ma.courseid 		= e.courseid
                                                                AND	ma.methodtype		= :manual
                     WHERE	  e.enrol 	= :enrol
                        AND	  e.courseid 	= :courseid ";

            // Execute course
            $params['courseid']     = $courseId;
            $rdoCourse = $DB->get_record_sql($sql,$params);

            // Execute for template
            $params['courseid']     = $courseTemplate;
            $rdoTemplate = $DB->get_record_sql($sql,$params);

            // Get the right instance
            if ($rdoCourse && $rdoTemplate) {
                // Course and template
                $rdoTemplate->id        = $rdoCourse->id;
                $rdoTemplate->selfid    = $rdoCourse->selfid;
                $rdoTemplate->bulkid    = $rdoCourse->bulkid;
                $rdoTemplate->courseid  = $courseId;

                return $rdoTemplate;
            }else if ($rdoTemplate && !$rdoCourse) {
                // Template but no course
                $rdoTemplate->id        = null;
                $rdoTemplate->selfid    = null;
                $rdoTemplate->bulkid    = null;
                $rdoTemplate->courseid  = $courseId;

                return $rdoTemplate;
            }else {
                // No template, no course
                // Instance
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
    }//get_waiting_enrol_instance


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
    private static function too_many_teachers_selector($search,$total) {
        /* Variables    */
        $availableTeachers  = array();
        $info               = null;
        $tooMany            = null;
        $searchMore         = null;

        try {
            if ($search) {
                // Information too many
                $info = new stdClass();
                $info->count    = $total;
                $info->search   = $search;

                // Messages to display
                $tooMany    = get_string('toomanyusersmatchsearch', '', $info);
                $searchMore = get_string('pleasesearchmore');

            }else {
                // Messages to display
                $tooMany    = get_string('toomanyuserstoshow', '', $total);
                $searchMore = get_string('pleaseusesearch');
            }//if_search

            // Information to return
            $availableTeachers[$tooMany]       = array('');
            $availableTeachers[$searchMore]    = array('');

            return $availableTeachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//too_many_teachers_selector

    /**
     * Description
     * Initialize selector for adding teachers
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $search
     * @param           $jsModule
     * @param           $course
     * @param           $nonediting
     *
     * @throws          Exception
     */
    private static function init_teachers_add_selector($search,$jsModule,$course,$nonediting) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            // Initialise options selector
            $options = array();
            if ($nonediting) {
                $options['class']   = 'find_noed_potential_teachers_selector';
            }else {
                $options['class']   = 'find_potential_teachers_selector';
            }

            $options['name']        = 'addselect';
            $options['multiselect'] = true;

            // Connect teacher selector
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
    }//init_teachers_add_selector

    /**
     * Description
     * initialize selector for removing teachers
     *
     * @creationDate    20/06/2016
     * @author          eFaktor     (fbv)
     *
     * @param           $search
     * @param           $jsModule
     * @param           $course
     * @param           $nonediting
     *
     * @throws          Exception
     */
    private static function init_teachers_remove_selector($search,$jsModule,$course,$nonediting) {
        /* Variables */
        global $USER,$PAGE;
        $options    = null;

        try {
            // Initialise options selector
            $options = array();
            if ($nonediting) {
                $options['class']   = 'find_noed_teachers_selectors';
            }else {
                $options['class']   = 'find_teachers_selectors';
            }
            $options['name']        = 'removeselect';
            $options['multiselect'] = true;

            // Connect teacher selector
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
    }//init_teachers_remove_selector

    /**
     * Description
     * Send a notification to the teacher or course instructor
     * that has been assigned or unassigned
     *
     * @creationDate        27/01/2017
     * @author              eFaktor     (fbv)
     *
     * @param       integer $user       User id
     * @param       integer $course     Course id
     * @param       string  $type       Teacher/Course instructor
     *
     * @throws              Exception
     */
    private static function send_notification_assignment_teacher($user,$course,$type) {
        /* Variables */
        global $SITE,$DB;
        $subject    = null;
        $body       = null;
        $bodyText   = null;
        $bodyHtml   = null;
        $info       = null;
        $teacher    = null;

        try {
            // Teacher/instructor info
            $teacher = get_complete_user_data('id',$user);

            // Course name
            $rdo = $DB->get_record('course',array('id' => $course),'fullname');

            // Information to send
            $info = new stdClass();
            $info->site = $SITE->shortname;
            if ($rdo) {
                $info->course = $rdo->fullname;
            }else {
                $info->course = '';
            }//if_rdo

            // Content of the notification depends on if it is a teacher or instructor
            switch ($type) {
                case AS_TEACHER:
                    // Subject
                    $subject    = (string)new lang_string('msg_teacher','local_friadmin',$info,$teacher->lang);
                    // Body
                    $body       = (string)new lang_string('body_teacher','local_friadmin',$info,$teacher->lang);

                    break;

                case UN_TEACHER:
                    // Subject
                    $subject    = (string)new lang_string('msg_teacher','local_friadmin',$info,$teacher->lang);
                    // Body
                    $body       = (string)new lang_string('body_unteacher','local_friadmin',$info,$teacher->lang);

                    break;

                case AS_INSTRUCTOR:
                    // Subject
                    $subject = (string)new lang_string('msg_instructor','local_friadmin',$info,$teacher->lang);
                    // Body
                    $body       = (string)new lang_string('body_instructor','local_friadmin',$info,$teacher->lang);

                    break;

                case UN_INSTRUCTOR:
                    // Subject
                    $subject = (string)new lang_string('msg_instructor','local_friadmin',$info,$teacher->lang);
                    // Body
                    $body       = (string)new lang_string('body_uninstructor','local_friadmin',$info,$teacher->lang);

                    break;
            }//switch_type


            // build mail Mail
            if (strpos($body, '<') === false) {
                // Plain text only.
                $bodyText = $body;
                $bodyHtml = text_to_html($bodyText, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $bodyHtml = format_text($body, FORMAT_MOODLE);
                $bodyText = html_to_text($bodyHtml);
            }

            // Send Mail
            email_to_user($teacher, $SITE->shortname, $subject, $bodyText,$bodyHtml);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//send_notification_assignment_teacher
}//CourseTemplate