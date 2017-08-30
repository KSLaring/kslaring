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
 * Friadmin - Category reports (Library)
 *
 * @package         local/friadmin
 * @subpackage      reports/lib
 * @copyright       2012        eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    01/04/2017
 * @author          eFaktor         (nas)
 *
 */

defined('MOODLE_INTERNAL') || die();

class friadminrpt {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * Description
     * Initialize javascript that is going to be used
     *
     * @param           $parent
     * @param           $category
     * @param           $course
     *
     * @throws          Exception
     *
     * @creationDate    29/08/2017
     * @author          eFaktor     (fbv)
     */
    public static function ini_data_reports($parent,$category,$course) {
        /* Variables */
        global $PAGE;
        $name       = null;
        $path       = null;
        $requires   = null;
        $jsmodule   = null;

        try {
            // Initialise variables
            $name       = 'data_rpt';
            $path       = '/local/friadmin/reports/js/reports.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');

            // Initialise js module
            $jsmodule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires
            );

            // Javascript
            $PAGE->requires->js_init_call('M.core_user.init_data_report',
                array($parent,$category,$course),
                false,
                $jsmodule
            );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//ini_data_reports

    /**
     * @param           $cat
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    28/08/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_subcategories_by_cat($cat) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $sql    = null;
        $subcat = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['cat'] = $cat;

            // SQL Instruction
            $sql = " SELECT	GROUP_CONCAT(DISTINCT ca.id ORDER BY ca.id SEPARATOR ',') as 'category'
                     FROM	{course_categories}	ca
                     WHERE	LOCATE(:cat,ca.path) ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                if ($rdo->category) {
                    $subcat = $rdo->category;
                }//if_rdo_category
            }//if_rdo

            return $subcat;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_subcategories_by_cat

    /**
     * Description
     * Get all categories list by depth
     *
     * @param           $mycategories
     * @param           $depth
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    23/08/2017
     * @author          eFaktor     (fbv)
     */
    public static function get_my_categories_by_depth($mycategories,$depth=null,$parent=null) {
        /* Variables */
        global $DB;
        $sql    = null;
        $rdo    = null;
        $lstcat = array();

        try {
            // First Element of the list
            $lstcat[0] = get_string('selectone', 'local_friadmin');

            // SQL Instruction
            $sql = " SELECT ca.id,
                            ca.name
                     FROM   {course_categories} ca
                     WHERE  ca.id IN ($mycategories->total) ";

            // Search criteria
            $params = array();

            // Criteria depth
            if ($depth) {
                $params['depth'] = $depth;

                $sql .= " AND ca.depth = :depth ";
            }//if_depth

            // Criteria parent
            if ($parent) {
                $params['parent'] = $parent;

                $sql .= " AND ca.parent = :parent ";
            }//if_parent

            // Execute
            $sql .= " ORDER BY ca.name ";
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lstcat[$instance->id] = $instance->name;
                }//for_rdo
            }//if_rdo

            return $lstcat;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_my_categories_by_depth

    /**
     * Description
     * Return all categories connected with the user, based on the context
     *
     * @param           $user
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    23/08/2017
     * @author          eFaktor (fbv)
     */
    public static function get_my_categories_by_context($user) {
        /* Variables */
        global $DB;
        $mycategories   = null;
        $categories     = null;
        $aux            = null;
        $sql            = null;
        $params         = null;
        $sqlwhere       = null;
        $sqlcontext     = null;

        try {
            // My categories
            $mycategories = new stdClass();
            $mycategories->ctx_course   = null;
            $mycategories->ctx_cat      = null;
            $mycategories->ctx_system   = null;
            $mycategories->total        = null;

            // Search criteria
            $params = array();
            $params['user'] = $user;

            // Admin all categories
            if (is_siteadmin($user)) {
                $mycategories->ctx_system = true;
                $mycategories->total      = self::get_all_categories_with_courses();
            }else {
                // Context System all categories
                // By CONTEXT SYSTEM
                $params['context'] = CONTEXT_SYSTEM;
                $sql = self::get_sql_my_categories_as(CONTEXT_SYSTEM);
                // Execute
                $rdo = $DB->get_record_sql($sql,$params);
                if ($rdo) {
                    $mycategories->ctx_system = true;
                    $mycategories->total      = self::get_all_categories_with_courses();
                }//if_rdo
            }//if_else


            // CONTEXT COURSE && CONTEXT COURSE CAT
            if (!$mycategories->ctx_system) {
                // By CONTEXT COURSE
                $params['context'] = CONTEXT_COURSE;
                $sql = self::get_sql_my_categories_as(CONTEXT_COURSE);
                // Execute - Get categories
                $rdo = $DB->get_record_sql($sql,$params);
                if ($rdo) {
                    if ($rdo->category) {
                        $mycategories->ctx_course = $rdo->category;
                        if ($mycategories->total) {
                            $mycategories->total .= ',';
                        }

                        $mycategories->total .= $mycategories->ctx_course;
                    }//if_Cattegory
                }//if_rdo

                // By CONTEXT COURSE CAT
                $params['context'] = CONTEXT_COURSECAT;
                $sql = self::get_sql_my_categories_as(CONTEXT_COURSECAT);
                // Execute - Get categories
                $rdo = $DB->get_record_sql($sql,$params);
                if ($rdo) {
                    if ($rdo->category) {
                        $mycategories->ctx_cat = $rdo->category;
                        if ($mycategories->total) {
                            $mycategories->total .= ',';
                        }
                        $mycategories->total .= $mycategories->ctx_cat;
                    }//if_category
                }//if_rdo

                // Get subcategories
                if ($mycategories->total) {
                    $categories = null;
                    $aux        = explode(',',$mycategories->total);
                    foreach ($aux as $cat) {
                        $category   = "/" . $cat . "/";
                        $categories = self::get_subcategories_by_cat($category);
                        if ($categories) {
                            $mycategories->total .= ',' . $categories;
                        }
                    }//for_aux

                    // Only catgories wit
                    $mycategories->total      = self::get_all_categories_with_courses($mycategories->total);
                }//my_categories
            }

            // REturn categories
            if ($mycategories->total
                ||
                $mycategories->ctx_system) {

                return $mycategories;
            }else {
                return null;
            }//mycategories
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_my_categories_by_context

    /**
     * Description
     * Get all courses connected with a category
     *
     * @param           $categories
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    28/08/2017
     * @author          eFaktor     (fbv)
     *
     */
    public static function get_courses_by_cat($categories) {
        /* Variables */
        global $DB;
        $sql        = null;
        $rdo        = null;
        $lstcourses = null;

        try {
            // First element
            $lstcourses[0] = get_string('selectone', 'local_friadmin');

            // SQL Instruction
            $sql = " SELECT   c.id,
                              c.fullname
                     FROM 	  {course}	c
                     WHERE 	  c.category in ($categories)
                     ORDER BY c.fullname ";

            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lstcourses[$instance->id] = $instance->fullname;
                }//for_rdo
            }//if_rdo

            return $lstcourses;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_courses_by_cat

    /**
     * Description
     * A function used to get all the information from the databse that is used to create the summary excel
     *

     * @param   Object      $data       Search criteria
     *
     * @return  array|null
     * @throws  Exception
     *
     * @updateDate  23/05/2017
     * @author      eFaktor     (nas)
     *
     *
     * @updateDate  08/06/2017
     * @author      eFaktor     (fbv)
     *
     */
    public static function get_course_summary_data($data) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $query      = null;
        $params     = null;
        $categories = null;
        $category   = null;

        try {
            // Search criteria
            $params = array();
            $params['from']         = $data->selsummaryfrom;
            $params['to']           = $data->selsummaryto;

            // Get subcategories
            $category = "/" . $data->parentcat . "/";
            $categories = self::get_subcategories_by_cat($category);
            if ($categories) {
                $categories .= ',' . $data->parentcat;
            }else {
                $categories = $data->parentcat;
            }//if_categories

            // SQL Instruction
            $query = " SELECT       c.id			    as 'courseid',			-- The course ID
                                    c.fullname 		    as 'coursefull', 		-- Course full name
                                    c.shortname 	    as 'courseshort', 		-- Course short name
                                    c.format 		    as 'courseformat', 	    -- Course format,
                                    c.visible		    as 'visibility',	    -- Course visibility	
                                    c.startdate,								-- Start date 							--
                                    ca.name 		    as 'category', 		    -- Category Name
                                    mu.name				as 'levelone',			-- Municipality (Level One)
                                    l1.name				as 'location',			-- Course location,
                                    fo2.value			as 'sector',			-- SEctors
                                    fo3.value		    as 'producer',			-- Produced by
                                    fo4.value 		    as 'fromto',			-- From - To
                                    e.customint1		as 'expiration',		-- Deadline
                                    e.customint2	    as 'spots',			    -- Number of places
                                    e.customtext3	    as 'internalprice',	    -- Internal price
                                    e.customtext4	    as 'externalprice',     -- external price  
                                    csi.instructors		as 'instructors',       -- Amount of instructors
                                    csi.students		as 'students',			-- Total users
                                    count(wa.userid) 	as 'waiting',			-- Total users waiting list
                                    count(cc.id)        as 'completed'			-- Total users completed
                       FROM			{course} 				  c
                          JOIN		{course_categories}		  ca    ON ca.id 			= c.category
                          -- Format options -- Location (Municipality - Level one)
                          LEFT JOIN	{course_format_options}   fo 	ON fo.courseid 	= c.id
                                                                    AND fo.name 	= 'course_location' 
                          LEFT JOIN	{course_locations} 		  l1    ON 	l1.id 	    = fo.value
                          LEFT JOIN	{report_gen_companydata}  mu	ON	mu.id		= l1.levelone
                          -- Format options -- Sector (Level two)
                          LEFT JOIN	{course_format_options}	  fo2	ON 	fo2.courseid  = c.id
                                                                    AND fo2.name 	  = 'course_sector'
                          -- Format options -- Produced by
                          LEFT JOIN	{course_format_options}	  fo3	ON 	fo3.courseid  = c.id
                                                                    AND fo3.name 	  = 'producedby'   
                          -- Format options -- time 
                          LEFT JOIN	{course_format_options}	  fo4	ON 	fo4.courseid  = c.id
                                                                    AND fo4.name 	  = 'time'    
                          -- Deadline / Internal price && External price
                          LEFT JOIN	{enrol}					  e   ON 	e.courseid    = c.id
                                                                  AND e.enrol		  = 'waitinglist'
                                                                  AND e.status 	  = 0
                          -- Total users in waiting list
                          LEFT JOIN	{enrol_waitinglist_queue} wa  ON  wa.waitinglistid	= e.id
                                                                  AND wa.courseid			= c.id
                                                                  AND queueno 		   != '99999'
                          -- Total users completed the course
                          LEFT JOIN	{course_completions}	  cc  ON  cc.course	= c.id
                                                                  AND (cc.timecompleted IS NOT NULL 
                                                                       OR 
                                                                       cc.timecompleted != 0)
                                
                          -- TOTAL USERS ENROLLED AS STUDENT
                          -- Total instructors --> non_editing teacher
                          LEFT JOIN (
                                     SELECT 	  ct.instanceid as 'course',
                                                  count(rs.id)  as 'students',
                                                  count(ri.id)  as 'instructors'
                                     FROM		  {role_assignments}  ra
                                        -- Only users with contextlevel = 50 (Course)
                                        JOIN	  {context}			  ct  ON  ct.id 		  = ra.contextid
                                                                          AND ct.contextlevel = 50
                                        -- Students
                                        LEFT JOIN {role}			  rs  ON  rs.id 		= ra.roleid
                                                                          AND rs.archetype  = 'student'
                                        -- Intructors
                                        LEFT JOIN {role}			  ri  ON  ri.id 		= ra.roleid
                                                                          AND ri.archetype  = 'teacher'
                                     GROUP BY ct.instanceid
                                    ) csi ON csi.course = c.id
                       WHERE 	 c.category IN ($categories)
                          AND   c.startdate BETWEEN :from AND :to
                       GROUP BY c.id 
                       ORDER BY c.fullname ";

            // Execute
            $rdo = $DB->get_records_sql($query, $params);
            if ($rdo) {
                return $rdo;
            } else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_course_summary_data

    /**
     * Description
     * Get all courses with coordinators
     *
     * @param   Object      $data         Filter criteria
     *
     * @return  array|null Returns all the data used in the coordinator excel
     * @throws Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      08/06/2017
     * @auhtor          eFaktor     (fbv)
     *
     */
    public static function get_courses_with_coordinator($data) {
        /* Variables */
        global $DB;
        $sql            = null;
        $sqlextra       = null;
        $params         = null;
        $joinuser       = null;
        $workplacesql   = null;
        $jobrolesql     = null;
        $categories     = null;

        try {
            // Search criteria
            $params = Array();

            // Get subcategories
            $category = "/" . $data->parentcat . "/";
            $categories = self::get_subcategories_by_cat($category);
            if ($categories) {
                $categories .= ',' . $data->parentcat;
            }else {
                $categories = $data->parentcat;
            }//if_categories

            // Course criteria
            if ($data->course) {
                $params['course'] = $data->course;
                $sqlextra = " WHERE c.id = :course ";
            }//if_course

            // Users fullname.
            if ($data->userfullname) {
                if (!$joinuser) {
                    $joinuser = " JOIN	mdl_user	u	ON	u.id			= ra.userid ";
                }//if_joinuser
                $joinuser .= " AND CONCAT(u.firstname, ' ', u.lastname) LIKE '%" . $data->userfullname . "%'";
            }//fullname

            // Username.
            if ($data->username) {
                if (!$joinuser) {
                    $joinuser = " JOIN	mdl_user	u	ON	u.id			= ra.userid ";
                }//if_joinuser
                $joinuser .= " AND u.username  LIKE '%" . $data->username . "%'";
            }//username

            // Email.
            if ($data->useremail) {
                if (!$joinuser) {
                    $joinuser = " JOIN	mdl_user	u	ON	u.id			= ra.userid ";
                }//if_joinuser
                $joinuser .= " AND u.email  LIKE '%" . $data->useremail . "%'";
            }//email

            // Workplace.
            if ($data->userworkplace) {
                $workplacesql = " JOIN {user_info_competence_data} 	uic ON  uic.userid  = u.id
                                  JOIN {report_gen_companydata} 	rgc ON  rgc.id      = uic.competenceid
                                                                        AND rgc.name LIKE '%" . $data->userworkplace . "'%' ";
            }

            // Jobrole.
            if ($data->userjobrole) {
                $jobrolesql = " JOIN {user_info_competence_data}  uic2 ON  uic2.userid = u.id
                                JOIN {report_gen_jobrole}         gjr  ON  gjr.id IN (uic2.jobroles)
                                                                       AND gjr.name LIKE '%" . $data->userjobrole . "%' ";
            }

            // SQL Instruction
            $sql = " SELECT	DISTINCT 
                                  c.id,
                                  c.fullname	as 'coursename',
                                  ca.name       as 'category',
                                  c.format      as 'courseformat',
                                  co.name       as 'levelone',
                                  c.startdate,
                                  cl.name		as 'location',
                                  fo2.value		as 'sector',
                                  fo1.value		as 'fromto',
                                  c.visible		as 'visibility'
                     FROM		  {course}				    c
                        -- Coordinators
                        JOIN	  {context}				    ct	ON  ct.instanceid 	= c.id
                        JOIN 	  {role_assignments}		ra	ON  ra.contextid	= ct.id
                        JOIN  	  {role}					r 	ON 	r.id 		    = ra.roleid
                                                                AND r.archetype    	= 'editingteacher'
                        -- User criteria
                        $joinuser
                        -- Category
                        JOIN	  {course_categories}		ca	ON  ca.id	      	= c.category
                                                                AND ca.id 			IN ($categories)
                        -- Location
                        LEFT JOIN {course_format_options}   fo  ON  fo.courseid   	= ct.instanceid
                                                                AND fo.name       	= 'course_location'
                        LEFT JOIN {course_locations}        cl  ON  cl.id         	= fo.value
                        LEFT JOIN {report_gen_companydata}  co  ON  co.id         	= cl.levelone
                        -- From/to (time)
                        LEFT JOIN {course_format_options}   fo1 ON  fo1.courseid 	= ct.instanceid
                                                                AND fo1.name      	= 'time' 
                        -- Format options -- Sector (Level two)
                        LEFT JOIN {course_format_options}	fo2	ON 	fo2.courseid  = c.id
                                                                AND fo2.name 	  = 'course_sector'
                        -- Jobroles
                        $jobrolesql
                        -- Workplace
                        $workplacesql
                     $sqlextra ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_courses_with_coordinator

    /**
     * Description
     * Gets all the neccessary data from the database for course instructors
     *
     * @param string    $instructors    All the instructor ID's from get_course_instructors
     * @param integer   $course         The course selected by the user in the form (optional)
     * @param integer   $category       The category selected by the user in the form (required)
     * @return array|null               Returns all the data used in the instructor excel
     * @throws Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_course_instructor_data($instructors, $course, $category) {
        // Variables!
        global $DB;
        $rdo        = null;
        $extrasql   = null;
        $query      = null;
        $params     = null;
        $categories = null;
        $mycat      = null;

        try {
            // Search criteria
            $params = array();

            // Get subcategories
            $mycat = "/" . $category . "/";
            $categories = self::get_subcategories_by_cat($mycat);
            if ($categories) {
                $categories .= ',' . $category;
            }else {
                $categories = $category;
            }//if_categories

            // Course criteria
            if ($course) {
                $params['course'] = $course;
                $extrasql .= " AND c.id = :course ";
            }//if_course

            // SQL -Instruction
            $query = " SELECT  DISTINCT 
                                    CONCAT(u.id,c.id)                   as 'unique',
                                    c.id                                as 'courseid',
                                    CONCAT(u.firstname,' ', u.lastname) as 'instr',
                                    c.fullname                          as 'coursename',
                                    ca.name                             as 'category',
                                    c.format                            as 'courseformat',
                                    c.startdate,
                                    co.name                             as 'levelone',
                                    cl.name                             as 'location',
                                    fo2.value							as 'sector',
                                    fo1.value                           as 'fromto',
                                    c.visible                           as 'visibility'
                       FROM         {user}                    u
                          -- Course
                          JOIN 		{role_assignments}		  ra 	ON ra.userid  = u.id
						  JOIN		{context}				  ct	ON ct.id 	  = ra.contextid
                          JOIN      {course}                  c     ON c.id       = ct.instanceid

                          -- Category
                          JOIN      {course_categories}       ca    ON  ca.id = c.category
                                                                    AND ca.id IN ($categories)
                          -- Location
                          LEFT JOIN {course_format_options}   fo    ON  fo.courseid = c.id
                                                                    AND fo.name     = 'course_location'
                          LEFT JOIN {course_locations}        cl    ON  cl.id       = fo.value
                          LEFT JOIN {report_gen_companydata}  co    ON  co.id       = cl.levelone
                          -- Dates
                          LEFT JOIN {course_format_options}   fo1   ON  fo1.courseid = c.id
                                                                    AND fo1.name     = 'time'
                          -- Format options -- Sector (Level two)
                          LEFT JOIN	{course_format_options}	 fo2	ON 	fo2.courseid  = c.id
													                AND fo2.name 	  = 'course_sector'
                       WHERE u.deleted = 0
                          AND u.id IN ($instructors)
                          $extrasql
                       ORDER BY c.fullname ";


            // Execute
            $rdo = $DB->get_records_sql($query, $params);
            if ($rdo) {
                return $rdo;
            } else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    } // end get_course_instructor_data

    /**
     * Description
     * A function that gets all the information from the database that will be used to create the instructors excel
     *
     * @param object $data  Data coming from the from. Course, category, username...
     *
     * @return array|null Returns the ID of all the instructors
     * @throws Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_course_instructors($data) {
        // Variables!
        global $DB;
        $rdo            = null;
        $extrasql       = null;
        $workplacesql   = null;
        $jobrolesql     = null;
        $params         = null;
        $query          = null;
        $category       = null;
        $categories     = null;

        try {
            // Search Criteria
            $params = array();

            // Get subcategories
            $category   = "/" . $data->parentcat . "/";
            $categories = self::get_subcategories_by_cat($category);
            if ($categories) {
                $categories .= ',' . $data->parentcat;
            }else {
                $categories = $data->parentcat;
            }//if_categories

            // Course.
            if ($data->course) {
                $params['course']   = $data->course;
                $extrasql .= " AND c.id = :course ";
            }

            // Users fullname.
            if ($data->userfullname) {
                $extrasql .= " AND CONCAT(u.firstname, ' ', u.lastname) LIKE '%" . $data->userfullname . "%' ";
            }

            // Username.
            if ($data->username) {
                $extrasql .= " AND u.username LIKE '%" . $data->username . "%' ";
            }

            // Email.
            if ($data->useremail) {
                $extrasql .= " AND u.email LIKE '%" . $data->useremail . "%' ";
            }

            // Workplace.
            if ($data->userworkplace) {
                $workplacesql = " JOIN {user_info_competence_data} 	uic ON  uic.userid  = u.id
                                  JOIN {report_gen_companydata} 	rgc ON  rgc.id      = uic.competenceid
                                                                        AND rgc.name LIKE '%" . $data->userworkplace . "'%' ";
            }

            // Jobrole.
            if ($data->userjobrole) {
                $jobrolesql = " JOIN {user_info_competence_data}  uic2 ON  uic2.userid = u.id
                                JOIN {report_gen_jobrole}         gjr  ON  gjr.id IN (uic2.jobroles)
                                                                       AND gjr.name LIKE '%" . $data->userjobrole . "%' ";
            }

            // Query.
            $query = " SELECT 	GROUP_CONCAT(DISTINCT u.id ORDER BY u.id SEPARATOR ',') as 'instructors'
                       FROM    	{user}              u
                          -- INSTRUCTORS
                          JOIN  {role_assignments}  ra  ON  ra.userid   		= u.id
                          JOIN  {context}           ct  ON  ct.id       		= ra.contextid
                          JOIN  {role}              r   ON  r.id        		= ra.roleid
                                                        AND r.archetype 		= 'teacher'
                          -- Course
                          JOIN 	{course}		     c	ON  c.id        		= ct.instanceid
                                                        AND c.category		    IN ($categories)
                          -- Jobroles
                          $jobrolesql
                          -- Workplace
                          $workplacesql
                       WHERE u.deleted = 0 
                          $extrasql ";

            // Execute
            $rdo = $DB->get_record_sql($query, $params);
            if ($rdo) {
                return $rdo->instructors;
            } else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    } // end get_course_instructors

    /**
     * Description
     * Gets the categoryname from the category id selected by the user in the form
     *
     * @param   integer $category    The category integer selected by the user in the form
     *
     * @return  string  $rdo         The category name
     * @throws          Exception
     *
     * @updateDate      23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      28/08/2017
     * @auhtor          eFaktor     (fbv)
     *
     */
    public static function get_category_name($category) {
        // Variables
        global $DB;
        $rdo = null;

        try {
            // Search criteria
            $params = array();
            $params['id'] = $category;

            $rdo = $DB->get_record('course_categories', $params,'name');

            // Gets the category.
            if ($rdo) {
                return $rdo->name;
            } else {
                return null;
            }
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_categories

    /**
     * Description
     * Gets the coursename from the category id selected by the user in the form
     *
     * @param   integer $course    The course integer selected by the user in the form
     *
     * @return  string  $rdo       The coursename
     * @throws          Exception
     *
     * @updateDate      23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      28/08/2017
     * @author          eFaktor     (fbv)
     *
     */
    public static function get_course_name($course) {
        // Variables
        global $DB;
        $rdo = null;

        try {
            $params = array();
            $params['id'] = $course;

            $rdo = $DB->get_record('course', $params,'fullname');

            // Gets the category.
            if ($rdo) {
                return $rdo->fullname;
            } else {
                return null;
            }
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    }//get_course_name

    /**
     * Description
     * Creates the excel for the summary report
     *
     * @param   array   $coursesdata   The data from the get_course_summary_data
     * @param   Object  $data   Search criteria
     *
     * @throws  Exception
     *
     * @updateDate      23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      08/06/2017
     * @author          eFaktor     (fbv)
     *
     */
    public static function download_participants_list($coursesdata, $data) {
        // Variables.
        $row    = 0;
        $time   = null;
        $name   = null;
        $export = null;
        $myxls  = null;

        try {
            // Creating excel book
            $time = userdate(time(), '%d.%m.%Y', 99, false);
            $name = clean_filename(get_string('participantslistsummary', 'local_friadmin') . $time . ".xls");
            $export = new MoodleExcelWorkbook($name);

            // Sheet - Search criterias.
            $myxls = $export->add_worksheet(get_string('filter', 'local_friadmin'));
            self::add_participants_excel_filter($myxls, $data);

            // Sheet Content
            $myxls = $export->add_worksheet(get_string('content', 'local_friadmin'));
            // Headers.
            self::add_participants_header_excel($myxls);
            $row ++;
            // Content.
            if ($coursesdata) {
                self::add_participants_content_excel($coursesdata, $myxls, $row);
            }else {
                $noresults = get_string('noresults','local_friadmin');
                $myxls->write($row, 0, $noresults, array('size' => 16, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, 0, $row, 5);
                $myxls->set_row($row, 20);
            }//if_coursesdata

            $export->close();

            exit;
        } catch (Exception $ex) {
            throw $ex;
        }
    }//download_participants_list

    /**
     * Description
     * Creates the excel for the coordinator report
     *
     * @param   array   $courses   The data from the get_course_coordinator_report (array of objects)
     * @param   Object  $data    Filter criteria. coming from the form
     *
     * @throws  Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      08/06/2017
     * @author          eFaktor     (fbv)
     *
     */
    public static function download_participants_list_coordinator($courses,$data) {
        // Variables
        $row        = 0;
        $time       = null;
        $name       = null;
        $export     = null;
        $myxls      = null;
        $noresults  = null;

        try {
            //Creating excel book
            $time = userdate(time(), '%d.%m.%Y', 99, false);
            $name = clean_filename(get_string('participantslistcoordinators', 'local_friadmin') . $time . ".xls");
            $export = new MoodleExcelWorkbook($name);

            // Excel sheet with the criteria (filter).
            $myxls = $export->add_worksheet(get_string('filter', 'local_friadmin'));
            self::add_participants_excel_filter_coordinator($myxls, $data);

            // Sheet witht all courses by coordinator
            $myxls = $export->add_worksheet(get_string('content', 'local_friadmin'));

            // Headers.
            self::add_participants_header_excel_coordinator($myxls,$courses);
            // Content.
            $row ++;
            if ($courses) {
                self::add_participants_content_excel_coordinator($courses, $myxls, $row);
            }else {
                $noresults = get_string('noresults','local_friadmin');
                $myxls->write($row, 0, $noresults, array('size' => 16, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, 0, $row, 5);
                $myxls->set_row($row, 20);
            }//if_course

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//download_participants_list_coordinator

    /**
     * Description
     * Creates the excel for the instructor report
     *
     * @param   array   $courses  Instructors and theirs courses
     * @param   object  $data       Data from the filter (form)
     *
     * @throws          Exception
     *
     * @updateDate      23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      08/06/17
     * @auhtor          eFaktor     (fbv)
     *
     */
    public static function download_participants_list_instructor($courses, $data) {
        // Variables.
        $row        = 0;
        $time       = null;
        $name       = null;
        $export     = null;
        $myxls      = null;
        $noresults  = null;

        try {
            // Creating a workbook.
            $time = userdate(time(), '%d.%m.%Y', 99, false);
            $name = clean_filename(get_string('participantslistinstructors', 'local_friadmin') . $time . ".xls");
            $export = new MoodleExcelWorkbook($name);

            // Sheet - Search criterias.
            $myxls = $export->add_worksheet(get_string('filter', 'local_friadmin'));
            self::add_participants_excel_filter_instructor($myxls, $data);

            // Shhet with content.
            $myxls = $export->add_worksheet(get_string('content', 'local_friadmin'));
            // Headers.
            self::add_participants_header_excel_instructor($myxls,$courses);
            $row ++;
            // Content.
            if ($courses) {
                self::add_participants_content_excel_instructor($courses, $myxls, $row);
            }else {
                $noresults = get_string('noresults','local_friadmin');
                $myxls->write($row, 0, $noresults, array('size' => 16, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, 0, $row, 5);
                $myxls->set_row($row, 20);
            }//if_courses

            $export->close();
            exit;
        } catch (Exception $ex) {
            throw $ex;
        } //try_catch
    } //download_participants_list_instructor

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Get sql instruction to get all categories connected with user
     * based on the context
     *
     * @param           $context
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    23/08/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_sql_my_categories_as($context) {
        /* Variables */
        $sql = null;

        try {
            // Switch role
            switch ($context) {
                case CONTEXT_COURSE:
                    $sql = " SELECT	  GROUP_CONCAT(DISTINCT cc.id ORDER BY c.id SEPARATOR ',') as 'category'
                             FROM	  {role_assignments}	ra
                                JOIN  {role}				r		ON 	ra.roleid 		= r.id
                                                                    AND	r.archetype 	IN ('manager','coursecreator')
                                JOIN  {context}				ct  	ON  ct.id 			= ra.contextid
                                                                    AND ct.contextlevel = :context
                                JOIN  {course}				c		ON	c.id 			= ct.instanceid
                                JOIN  {course_categories}	cc		ON  cc.id 			= c.category
                             WHERE ra.userid = :user ";


                    break;
                case CONTEXT_COURSECAT:
                    $sql = " SELECT	  GROUP_CONCAT(DISTINCT cc.id ORDER BY cc.id SEPARATOR ',') as 'category'
                             FROM	  {role_assignments}	ra
                                JOIN  {role}				r	ON 	ra.roleid 		= r.id
                                                                AND	r.archetype 	IN ('manager','coursecreator')
                                JOIN  {context}			    ct  ON  ct.id 			= ra.contextid
                                                                AND ct.contextlevel = :context
                                JOIN  {course_categories}	cc	ON  cc.id 			= ct.instanceid
                             WHERE ra.userid = :user ";

                    break;
                case CONTEXT_SYSTEM:
                    $sql = " SELECT	  ra.id,
                                      ra.contextid,
                                      ct.instanceid
                             FROM	  {role_assignments}  ra
                                JOIN  {role}			  r	  ON 	ra.roleid 		= r.id
                                                              AND	r.archetype 	IN ('manager','coursecreator')
                                JOIN  {context}			  ct  ON  ct.id 			= ra.contextid
                                                              AND ct.contextlevel = :context
                             WHERE ra.userid = :user ";

                    break;
            }//switch_role

            return $sql;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_sql_my_categories_as

    /**
     * Description
     * Return all categories with courses
     *
     * @param           null $in
     *
     * @return          bool|mixed|null|string
     * @throws          Exception
     *
     * @creationDate    23/08/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_all_categories_with_courses($in=null) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql        = null;
        $categories = null;

        try {
            // SQL Instruction
            $sql = " SELECT	  GROUP_CONCAT(DISTINCT ca.id ORDER BY ca.id SEPARATOR ',') as 'category'
                     FROM	  {course}				c
                        JOIN  {course_categories}	ca ON ca.id = c.category ";

            // Execute
            if ($in) {
                $sql .= " AND ca.id IN ($in) ";
            }//in
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                if ($rdo->category) {
                    $categories= $rdo->category;
                }//if_Cattegory
            }//if_rdo

            return $categories;
        }catch (Exception $ex) {
            throw $ex;
        }//try_cstch
    }//get_all_categories_with_courses

    /**
     * @param   array     $sector     All the sectors in an array
     * @return  null      Returns the sectors in text format or null
     * @throws  Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function get_sectors($sector) {
        // Variables
        global $DB;
        $rdo = null;

        try {
            // SQL Instruction
            $query = "SELECT GROUP_CONCAT(DISTINCT cd.name ORDER BY cd.name SEPARATOR ',') as 'sectors'
                      FROM 	{report_gen_companydata} cd
                      WHERE cd.id IN ($sector)
	                    AND cd.hierarchylevel = 2";

            // Execute
            $rdo = $DB->get_record_sql($query);
            if ($rdo) {
                return $rdo->sectors;
            } else {
                return null;
            }//if_rdo
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_categories

    /**
     * Description
     * Used to get the coordinators during the excel download call
     *
     * @param       integer     $courseid from the database
     * @return      string      The coordinators firstname and lastname
     * @throws      Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      07/06/2017
     * @author          eFaktor     (fbv)
     */
    private static function get_coordinator($courseid) {
        // Variables!
        global $DB;
        $rdo = null;

        try {
            // Search criteria
            $params = array();
            $params['courseid'] = $courseid;

            // SQL Instruction
            $query = " SELECT 	  u.id,
                                  concat(u.firstname, ' ', u.lastname)		as 'cord'
                       FROM	      {role_assignments}	ra
                            -- Only users with contextlevel = 50 (Course)
                            JOIN  {context}		ct  ON  ct.id 			= ra.contextid
                                                    AND ct.instanceid	= :courseid
                            -- Coordinators
                            JOIN   {role}	    rs 	ON 	rs.id 		    = ra.roleid
                                                    AND rs.archetype    = 'editingteacher'
                            -- User info
                            JOIN   {user}		u	ON 	u.id 		    = ra.userid
                       ORDER BY ra.id
                       LIMIT 0,1 ";

            // Execute
            $rdo = $DB->get_record_sql($query, $params);
            if ($rdo) {
                return $rdo->cord;
            } else {
                return null;
            }
        } catch (Exception $ex) {
            Throw $ex;
        }//try_catch
    } // end get_coordinator

    /**
     * Description
     * Adds the first page to the summary excel and writes all the search criterias to it
     *
     * @param           $myxls
     * @param   Object  $data   Search criteria
     *
     * @throws Exception
     *
     * @updateDate      23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      08/06/2017
     * @author          eFaktor     (fbv)
     *
     */
    private static function add_participants_excel_filter(&$myxls, $data) {
        // Variables.
        $col        = 0;
        $row        = 0;
        $strsummary     = get_string('summaryrptexcel', 'local_friadmin');
        $strcategory    = get_string('categoryexcel', 'local_friadmin');
        $strfrom        = get_string('fromexcel', 'local_friadmin');
        $strto          = get_string('toexcel', 'local_friadmin');


        try {
            // Extract criteria
            $myfrom     = userdate($data->selsummaryfrom,'%d.%m.%Y', 99, false);
            $myto       = userdate($data->selsummaryto,'%d.%m.%Y', 99, false);

            // Category name
            $mycategory = self::get_category_name($data->parentcat);

            // Summary Report Header.
            $myxls->write($row, $col, $strsummary, array(
                'size' => 22,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#d4d4d4',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row + 1, $col + 4);
            $myxls->set_row($row, 20);

            // Category Header.
            $row += 2;
            $myxls->write($row, $col, $strcategory, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Category Content.
            $col += 2;
            $myxls->write($row, $col, $mycategory, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // From Header.
            $col = 0;
            $row += 1;
            $myxls->write($row, $col, $strfrom, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // From Content.
            $col += 2;
            $myxls->write($row, $col, $myfrom, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // To Header.
            $col = 0;
            $row += 1;
            $myxls->write($row, $col, $strto, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // To Content.
            $col += 2;
            $myxls->write($row, $col, $myto, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    } // end add_participants_excel_filter

    /**
     * Description
     * Adds the first page in the excel for the instructors and writes all the search criterias to it
     *
     * @param   $myxls
     * @param   Object $data     From the form. (filter data)
     *
     * @throws  Exception
     *
     * @updateDate      23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      07/06/2017
     * @author          eFaktor     (fbv)
     *
     */
    private static function add_participants_excel_filter_instructor(&$myxls, $data) {
        // Variables.
        $col        = 0;
        $row        = 0;
        $strinsructor   = get_string('instructorexcel', 'local_friadmin');
        $strcategory    = get_string('categoryexcel', 'local_friadmin');
        $strcourse      = get_string('courseexcel', 'local_friadmin');
        $strfullname    = get_string('fullnameexcel', 'local_friadmin');
        $strusername    = get_string('usernameexcel', 'local_friadmin');
        $stremail       = get_string('emailexcel', 'local_friadmin');
        $strworkplace   = get_string('workplaceexcel', 'local_friadmin');
        $strjobrole     = get_string('jobroleexcel', 'local_friadmin');

        try {
            //Category and course name
            $mycategory = self::get_category_name($data->parentcat);
            $mycourse   = ($data->course ? self::get_course_name($data->course) : '');

            // Instructor Report Header
            $myxls->write($row, $col, $strinsructor, array(
                'size' => 22,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#d4d4d4',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row + 1, $col + 4);
            $myxls->set_row($row, 20);

            // Category Header.
            $row += 2;
            $myxls->write($row, $col, $strcategory, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Category Content.
            $col += 2;
            $myxls->write($row, $col, $mycategory, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Course Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $strcourse, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Course Content.
            $col += 2;
            $myxls->write($row, $col, $mycourse, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Userfullname Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $strfullname, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Userfullname Content.
            $col += 2;
            $myxls->write($row, $col, $data->userfullname, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Username Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $strusername, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Username Content.
            $col += 2;
            $myxls->write($row, $col, $data->username, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // User email Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $stremail, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // User email Content.
            $col += 2;
            $myxls->write($row, $col, $data->useremail, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Userworkplace Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $strworkplace, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Userworkplace Content.
            $col += 2;
            $myxls->write($row, $col, $data->userworkplace, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Userjobrole Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $strjobrole, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Userjobrole Content.
            $col += 2;
            $myxls->write($row, $col, $data->userjobrole, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    } // end add_participants_excel_filter_instructor

    /**
     * Description
     * Adds the first page to the coordinator excel and write all the search criterias to it
     *
     * @param   $myxls
     * @param   Object  $data   Filter criteria
     *
     * @throws  Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      08/06/2017
     * @author          eFaktor     (fbv)
     *
     */
    private static function add_participants_excel_filter_coordinator(&$myxls, $data) {
        // Variables.
        $col        = 0;
        $row        = 0;
        $mycategory = null;
        $mycourse   = null;

        $strcoordinator = get_string('coordinatorexcel', 'local_friadmin');
        $strcategory    = get_string('categoryexcel', 'local_friadmin');
        $strcourse      = get_string('courseexcel', 'local_friadmin');
        $strfullname    = get_string('fullnameexcel', 'local_friadmin');
        $strusername    = get_string('usernameexcel', 'local_friadmin');
        $stremail       = get_string('emailexcel', 'local_friadmin');
        $strworkplace   = get_string('workplaceexcel', 'local_friadmin');
        $strjobrole     = get_string('jobroleexcel', 'local_friadmin');

        try {
            // Category and course name
            $mycategory = self::get_category_name($data->parentcat);
            $mycourse   = ($data->course ? self::get_course_name($data->course) : '');

            // Coordinator Report Header
            $myxls->write($row, $col, $strcoordinator, array(
                'size' => 22,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#d4d4d4',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row + 1, $col + 4);
            $myxls->set_row($row, 20);

            // Category Header.
            $row += 2;
            $myxls->write($row, $col, $strcategory, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Category Content.
            $col += 2;
            $myxls->write($row, $col, $mycategory, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Course Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $strcourse, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Course Content.
            $col += 2;
            $myxls->write($row, $col, $mycourse, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Userfullname Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $strfullname, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Userfullname Content.
            $col += 2;
            $myxls->write($row, $col, $data->userfullname, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Username Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $strusername, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Username Content.
            $col += 2;
            $myxls->write($row, $col, $data->username, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // User email Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $stremail, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // User email Content.
            $col += 2;
            $myxls->write($row, $col, $data->useremail, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Userworkplace Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $strworkplace, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Userworkplace Content.
            $col += 2;
            $myxls->write($row, $col, $data->userworkplace, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Userjobrole Header.
            $row += 1;
            $col -= 2;
            $myxls->write($row, $col, $strjobrole, array(
                'size' => 16,
                'name' => 'Arial',
                'bold' => '0',
                'bg_color' => '#e9e9e9',
                'text_wrap' => true,
                'v_align' => 'left',
                'h_align' => 'right'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Userjobrole Content.
            $col += 2;
            $myxls->write($row, $col, $data->userjobrole, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '0',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    } // end add_participants_excel_filter_coordinator

    /**
     * Description
     * Add the header of the table to the excel report for the summary
     *
     * @param           $myxls
     *
     * @throws          Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      08/06/2017
     * @author          eFaktor     (fbv)
     *
     */
    private static function add_participants_header_excel(&$myxls) {
        // Variables.
        $col                = 0;
        $row                = 0;
        $strcoursefull      = null;
        $strcourseshort     = null;
        $strcourseformat    = null;
        $strproducer        = null;
        $strlevelone        = null;
        $strsector          = null;
        $strlocation        = null;
        $strcategory        = null;
        $strexpiration      = null;
        $strspots           = null;
        $strinternalprice   = null;
        $strexternalprice   = null;
        $strinstructors     = null;
        $strstudents        = null;
        $strwaiting         = null;
        $strcompleted       = null;
        $strvisibility      = null;
        $strstartdate       = null;
        $strfromto          = null;
        $strnumberdays      = null;
        $fromtodates        = null;
        $h                  = null;
        $w                  = null;


        try {
            // Headers
            $strcoursefull          = get_string('courselong', 'local_friadmin');
            $strcourseshort         = get_string('courseshort', 'local_friadmin');
            $strcourseformat        = get_string('courseformat', 'local_friadmin');
            $strproducer            = get_string('producer', 'local_friadmin');
            $strlevelone            = get_string('kommune', 'local_friadmin');
            $strsector              = get_string('sector', 'local_friadmin');
            $strlocation            = get_string('usercourse_location','local_friadmin');
            $strcategory            = get_string('category', 'local_friadmin');
            $strexpiration          = get_string('expiration', 'local_friadmin');
            $strspots               = get_string('spots', 'local_friadmin');
            $strinternalprice       = get_string('internalprice', 'local_friadmin');
            $strexternalprice       = get_string('externalprice', 'local_friadmin');
            $strinstructors         = get_string('instructors', 'local_friadmin');
            $strstudents            = get_string('students', 'local_friadmin');
            $strwaiting             = get_string('waitinglist', 'local_friadmin');
            $strcompleted           = get_string('completed', 'local_friadmin');
            $strvisibility          = get_string('visible', 'local_friadmin');
            $strfromto              = get_string('fromto', 'local_friadmin');
            $strnumberdays          = get_string('numberofdays', 'local_friadmin');
            $strcoursecoordinator   = get_string('coursecoordinator', 'local_friadmin');
            $strstartdate           = get_string('startdate');

            // Height row
            $h = 28;
            // Width colum
            $w  = 35;
            $ws = 15;

            // Course fullname.
            $myxls->write($row, $col, $strcoursefull, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Course shortname.
            $col ++;
            $myxls->write($row, $col, $strcourseshort, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Course format.
            $col ++;
            $myxls->write($row, $col, $strcourseformat, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Category.
            $col ++;
            $myxls->write($row, $col, $strcategory, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Producer.
            $col ++;
            $myxls->write($row, $col, $strproducer, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Levelone.
            $col ++;
            $myxls->write($row, $col, $strlevelone, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Sector.
            $col ++;
            $myxls->write($row, $col, $strsector, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Course coordinator.
            $col ++;
            $myxls->write($row, $col, $strcoursecoordinator, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Location
            $col ++;
            $myxls->write($row, $col, $strlocation, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Start date
            $col ++;
            $myxls->write($row, $col, $strstartdate, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Number of days.
            $col ++;
            $myxls->write($row, $col, $strnumberdays, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Expiration.
            $col ++;
            $myxls->write($row, $col, $strexpiration, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Spots.
            $col ++;
            $myxls->write($row, $col, $strspots, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Internalprice.
            $col ++;
            $myxls->write($row, $col, $strinternalprice, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Externalprice.
            $col ++;
            $myxls->write($row, $col, $strexternalprice, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Instructors.
            $col ++;
            $myxls->write($row, $col, $strinstructors, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Students.
            $col ++;
            $myxls->write($row, $col, $strstudents, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Waiting.
            $col ++;
            $myxls->write($row, $col, $strwaiting, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Completed.
            $col ++;
            $myxls->write($row, $col, $strcompleted, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Visibility.
            $col ++;
            $myxls->write($row, $col, $strvisibility, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Fromto.
            $col ++;
            $myxls->write($row, $col, $strfromto, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            $fromtodates = null;

        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    } //add_participants_header_excel

    /**
     * @param       array $coursedata
     * @param             $myxls
     * @param             $row
     *
     * @throws     Exception
     *
     * @creationDate    xx/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      08/06/2017
     * @author          eFaktor     (fbv)
     */
    private static function add_participants_content_excel($coursedata, $myxls, &$row) {
        // Variables.
        $i              = null;
        $col            = 0;
        $last           = null;
        $workplaces     = null;
        $setRow         = null;
        $strUser        = null;
        $completion     = null;
        $mysectors      = null;
        $fromtodates    = null;
        $coordinator    = null;
        $strVisibility  = null;
        $h              = null;
        $w              = null;
        $ws             = null;

        try {
            // Height row
            $h = 20;
            // Width colum
            $w  = 35;
            $ws = 15;

            foreach ($coursedata as $course) {
                // Extract sectors
                if ($course->sector) {
                    $mysectors .= self::get_sectors($course->sector);
                } else {
                    $mysectors = '';
                }

                // Extract coordinator
                $coordinator = self::get_coordinator($course->courseid);

                // Course fullname.
                $myxls->write($row, $col, $course->coursefull, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$w);

                // Course shortname.
                $col ++;
                $myxls->write($row, $col, $course->courseshort, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$w);

                // Course format.
                $col ++;
                $format = (get_string_manager()->string_exists($course->courseformat,'local_friadmin')
                    ? get_string($course->courseformat,'local_friadmin') : $course->courseformat);
                $myxls->write($row, $col, $format, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Category.
                $col ++;
                $myxls->write($row, $col, $course->category, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$w);

                // Producer.
                $col ++;
                $myxls->write($row, $col, $course->producer, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$w);

                // Levelone.
                $col ++;
                $myxls->write($row, $col, $course->levelone, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$w);

                // Sector.
                $col ++;
                $myxls->write($row, $col, $mysectors, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$w);

                // Course coordinator.
                $col ++;
                $myxls->write($row, $col, $coordinator, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$w);

                // Location
                $col ++;
                $myxls->write($row, $col, $course->location, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$w);

                // Start date
                $col ++;
                $startdate = userdate($course->startdate, '%d.%m.%Y', 99, false);
                $myxls->write($row, $col, $startdate, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Number of days.
                $col ++;
                $fromtodates = explode(",", $course->fromto);
                $numberdays = ($course->fromto ? count($fromtodates) : 0);
                $myxls->write($row, $col, $numberdays, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Expiration.
                $col ++;
                $today = ($course->expiration ? userdate($course->expiration, '%d.%m.%Y', 99, false) : '');
                $myxls->write($row, $col, $today , array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'center'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Spots.
                $col ++;
                $myxls->write($row, $col, ($course->spots ? $course->spots : 0), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Internalprice.
                $col ++;
                $myxls->write($row, $col, ($course->internalprice ? $course->internalprice : 0), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Externalprice.
                $col ++;
                $myxls->write($row, $col, ($course->externalprice ? $course->externalprice : 0), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Instructors.
                $col ++;
                $instructors = ($course->instructors ? $course->instructors : 0);
                $myxls->write($row, $col, $instructors, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);


                // Students.
                $col ++;
                $myxls->write($row, $col, ($course->students ? $course->students : 0), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Waiting.
                $col ++;
                $myxls->write($row, $col, ($course->waiting ? $course->waiting :0), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Completed.
                $col ++;
                $myxls->write($row, $col, ($course->completed ? $course->completed : 0), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Visibility.
                $col ++;
                $strVisibility = ($course->visibility ? get_string('yes', 'local_friadmin') : get_string('no', 'local_friadmin'));
                $myxls->write($row, $col,$strVisibility , array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Fromto.
                $col ++;
                $myxls->write($row, $col, $course->fromto, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'top'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$w);

                $row ++;
                $col = 0;

                $fromtodates = null;
                $mysectors   = null;
            }//for_participants
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_content_excel

    /**
     * Description
     * Add the header of the table to the excel report for instructors
     *
     * @param           $myxls
     *
     * @throws          Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function add_participants_header_excel_instructor(&$myxls,$coursesdata) {
        /* Variables */
        $col                = 0;
        $row                = 0;
        $strinstructorname  = null;
        $strcoursename      = null;
        $strcategory        = null;
        $strcourseformat    = null;
        $strlevelone        = null;
        $strlocation        = null;
        $strcoordinatorname = null;
        $strfromto          = null;
        $strvisibility      = null;
        $strstartdate       = null;
        $strsector          = null;
        $h                  = null;
        $w                  = null;
        $ws                 = null;

        try {
            // Headers
            $strinstructorname  = get_string('instructorname', 'local_friadmin');
            $strcoursename      = get_string('coursename', 'local_friadmin');
            $strcategory        = get_string('category', 'local_friadmin');
            $strcourseformat    = get_string('courseformat', 'local_friadmin');
            $strlevelone        = get_string('kommune', 'local_friadmin');
            $strlocation        = get_string('usercourse_location','local_friadmin');
            $strcoordinatorname = get_string('coordinatorname', 'local_friadmin');
            $strnumberdays      = get_string('numberofdays', 'local_friadmin');
            $strfromto          = get_string('fromto', 'local_friadmin');
            $strvisibility      = get_string('visible', 'local_friadmin');
            $strstartdate       = get_string('startdate');
            $strsector          = get_string('sector', 'local_friadmin');

            // Height row
            $h = 20;
            // Width colum
            $w  = 35;
            $ws = 15;

            // Instructor name.
            $myxls->write($row, $col, $strinstructorname, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Course fullname.
            $col ++;
            $myxls->write($row, $col, $strcoursename, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Category.
            $col ++;
            $myxls->write($row, $col, $strcategory, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Course format.
            $col ++;
            $myxls->write($row, $col, $strcourseformat, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Levelone.
            $col ++;
            $myxls->write($row, $col, $strlevelone, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Sector.
            $col ++;
            $myxls->write($row, $col, $strsector, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Course coordinator.
            $col ++;
            $myxls->write($row, $col, $strcoordinatorname, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Location
            $col ++;
            $myxls->write($row, $col, $strlocation, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Start date.
            $col ++;
            $myxls->write($row, $col, $strstartdate, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Number of days.
            $col ++;
            $myxls->write($row, $col, $strnumberdays, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Fromto.
            $col ++;
            $myxls->write($row, $col, $strfromto, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Visibility.
            $col ++;
            $myxls->write($row, $col, $strvisibility, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            $fromtodates = null;

        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_header_excel

    /**
     * Description
     * Adds the contect for the excel report about instructors
     *
     * @param array     $coursedata     The information from the database (an array of objects)
     * @param           $myxls
     * @param           $row
     * @throws Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     * @updateDate      07/06/17
     * @author          eFaktor     (fbv)
     */
    private static function add_participants_content_excel_instructor($coursedata, &$myxls, &$row) {
        // Variables!
        $col            = 0;
        $last           = null;
        $workplaces     = null;
        $setrow         = null;
        $struser        = null;
        $completion     = null;
        $mysectors      = null;
        $h              = null;
        $w              = null;
        $ws             = null;

        try {
            // Height row
            $h = 20;
            // Width colum
            $w  = 35;
            $ws = 15;

            foreach ($coursedata as $course) {
                // Sector
                if ($course->sector) {
                    $mysectors = self::get_sectors($course->sector);
                } else {
                    $mysectors .= '';
                }

                // Coordinator
                $coordinator = self::get_coordinator($course->courseid);

                // Instructor name.
                $myxls->write($row, $col, $course->instr, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Course fullname.
                $col ++;
                $myxls->write($row, $col,$course->coursename, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Category.
                $col ++;
                $myxls->write($row, $col, $course->category, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Course format.
                $col ++;
                $format = (get_string_manager()->string_exists($course->courseformat,'local_friadmin')
                    ? get_string($course->courseformat,'local_friadmin') : $course->courseformat);
                $myxls->write($row, $col, $format, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$ws);

                // Levelone.
                $col ++;
                $myxls->write($row, $col, $course->levelone, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Sector
                $col ++;
                $myxls->write($row, $col,$mysectors, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Course coordinator.
                $col ++;
                $myxls->write($row, $col, $coordinator, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Location.
                $col ++;
                $myxls->write($row, $col, $course->location, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Start date
                $col ++;
                $startdate = userdate($course->startdate, '%d.%m.%Y', 99, false);
                $myxls->write($row, $col, $startdate, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Number of days.
                $col ++;
                $fromtodates = explode(",", $course->fromto);
                $numberdays = ($course->fromto ? count($fromtodates) : 0);
                $myxls->write($row, $col, $numberdays, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Fromto.
                $col ++;
                $myxls->write($row, $col, $course->fromto, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'top'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Visibility.
                $col ++;
                $strVisible = ($course->visibility ? get_string('yes', 'local_friadmin') : get_string('no', 'local_friadmin'));
                $myxls->write($row, $col, $strVisible, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$ws);

                $row ++;
                $col = 0;

                $fromtodates = null;
                $mysectors   = null;
            }//for_participants
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_content_excel

    /**
     * Description
     * Add the header of the table to the excel report for the coordinators
     *
     * @param           $myxls
     *
     * @throws          Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     */
    private static function add_participants_header_excel_coordinator(&$myxls,$coursesdata) {
        // Variables!
        $col                = 0;
        $row                = 0;
        $strinstructorname  = null;
        $strcoursename      = null;
        $strcategory        = null;
        $strcourseformat    = null;
        $strlevelone        = null;
        $strlocation        = null;
        $strcoordinatorname = null;
        $strfromto          = null;
        $strvisibility      = null;
        $strstartdate       = null;
        $strsector          = null;
        $h                  = null;
        $w                  = null;
        $ws                 = null;

        try {
            // Headers
            $strcoursename      = get_string('coursename', 'local_friadmin');
            $strcategory        = get_string('category', 'local_friadmin');
            $strcourseformat    = get_string('courseformat', 'local_friadmin');
            $strlevelone        = get_string('kommune', 'local_friadmin');
            $strlocation        = get_string('usercourse_location','local_friadmin');
            $strcoordinatorname = get_string('coordinatorname', 'local_friadmin');
            $strnumberdays      = get_string('numberofdays', 'local_friadmin');
            $strfromto          = get_string('fromto', 'local_friadmin');
            $strvisibility      = get_string('visible', 'local_friadmin');
            $strstartdate       = get_string('startdate');
            $strsector          = get_string('sector', 'local_friadmin');

            // Height row
            $h = 20;
            // Width colum
            $w  = 35;
            $ws = 15;

            // Coordinator name.
            $myxls->write($row, $col, $strcoordinatorname, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Course fullname.
            $col ++;
            $myxls->write($row, $col, $strcoursename, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Category.
            $col ++;
            $myxls->write($row, $col, $strcategory, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Course format.
            $col ++;
            $myxls->write($row, $col, $strcourseformat, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Sector.
            $col ++;
            $myxls->write($row, $col, $strsector, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Levelone.
            $col ++;
            $myxls->write($row, $col, $strlevelone, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Location.
            $col ++;
            $myxls->write($row, $col, $strlocation, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Start date.
            $col ++;
            $myxls->write($row, $col, $strstartdate, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Number of days.
            $col ++;
            $myxls->write($row, $col, $strnumberdays, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            // Fromto.
            $col ++;
            $myxls->write($row, $col, $strfromto, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$w);

            // Visibility.
            $col ++;
            $myxls->write($row, $col, $strvisibility, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col);
            $myxls->set_row($row, $h);
            $myxls->set_column($col,$col,$ws);

            $fromtodates = null;

        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_header_excel

    /**
     * Description
     * Adds the content to the coordinators excel document
     *
     * @param array     $coursedata     The information from the database (an array of objects)
     * @param           $myxls
     * @param           $row
     * @throws Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     */
    private static function add_participants_content_excel_coordinator($coursedata, &$myxls, &$row) {
        // Variables!
        $col            = 0;
        $last           = null;
        $workplaces     = null;
        $setrow         = null;
        $struser        = null;
        $completion     = null;
        $mysectors      = null;
        $strvisible     = null;
        $h              = null;
        $w              = null;
        $ws             = null;

        try {
            // Height row
            $h = 20;
            // Width colum
            $w  = 35;
            $ws = 15;

            foreach ($coursedata as $course) {
                // Sectors
                if ($course->sector) {
                    $mysectors .= self::get_sectors($course->sector);
                } else {
                    $mysectors = '';
                }

                // Get coordinator
                $coordinator = self::get_coordinator($course->id);

                // Coordinatorname name.
                $myxls->write($row, $col, $coordinator, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Course fullname.
                $col ++;
                $myxls->write($row, $col,$course->coursename, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Category.
                $col ++;
                $myxls->write($row, $col, $course->category, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Course format.
                $col ++;
                $format = (get_string_manager()->string_exists($course->courseformat,'local_friadmin')
                    ? get_string($course->courseformat,'local_friadmin') : $course->courseformat);
                $myxls->write($row, $col, $format, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$ws);

                // Sectors.
                $col ++;
                $myxls->write($row, $col, $mysectors, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Levelone.
                $col ++;
                $myxls->write($row, $col, $course->levelone, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Location
                $col ++;
                $myxls->write($row, $col, $course->location, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Start date
                $col ++;
                $startdate = userdate($course->startdate, '%d.%m.%Y', 99, false);
                $myxls->write($row, $col, $startdate, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Number of days.
                $col ++;
                $fromtodates = explode(",", $course->fromto);
                $numberdays = ($course->fromto ? count($fromtodates) : 0);
                $myxls->write($row, $col, $numberdays, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Fromto.
                $col ++;
                $myxls->write($row, $col, $course->fromto, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'top'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Visibility.
                $col ++;
                $strvisible = ($course->visibility ? get_string('yes', 'local_friadmin') : get_string('no', 'local_friadmin'));
                $myxls->write($row, $col, $strvisible, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$ws);

                // new row
                $row ++;
                $col = 0;

                $fromtodates = null;
                $mysectors   = null;
            }//for_participants
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_content_excel

}//friadminrpt