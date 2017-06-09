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

class friadminrpt
{
    
    /**
     * Description
     * Gets all the course-categories with courses connected to them and returns them in a single array
     *
     * @param           NULL
     *
     * @return          array   All the course-categories
     * @throws          Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_categories() {
        // Variables!
        global $DB;
        $categories = array();
        $rdo = null;

        // Query gets the course category names.
        $query = "SELECT  ca.id,
		                  ca.name
                  FROM    {course_categories} ca";

        try {
            $categories[0] = get_string('selectone', 'local_friadmin'); // Sets the first value in the array as "Select one...".
            $rdo = $DB->get_records_sql($query);

            if ($rdo) {
                foreach ($rdo as $instance) {
                    $categories[$instance->id] = $instance->name;
                }
            }
            return $categories;
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_categories

    /**
     * Description
     * Gets all the course-categories with courses connected to them and returns them in a single array
     *
     * @param           NULL
     *
     * @return          array   All the course-categories
     * @throws          Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_courses() {
        // Variables!
        global $DB;
        $courses = array();
        $rdo = null;

        // Query gets the course fullnames.
        $query = "SELECT  c.id,
		                  c.fullname
                  FROM    {course} c";

        try {
            $courses[0] = get_string('selectone', 'local_friadmin');
            $rdo = $DB->get_records_sql($query);

            if ($rdo) {
                foreach ($rdo as $instance) {
                    $courses[$instance->id] = $instance->fullname;
                }
            }
            return $courses;
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_categories

    /**
     * Description
     * Gets all the courses based on category and used only by the javascript
     *
     * @param           integer $category The Category ID
     * @return          array|null  All the courses for javascript purposes
     * @throws          Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_courses_js($category) {
        // Variables!
        global $DB;
        $courses = array();
        $rdo = null;

        // Query gets the courseid and the coursename based on the categoryid parameter in this function.
        $coursequery = "SELECT        c.id,
		                              c.fullname
                        FROM          {course} c
                          INNER JOIN  {course_categories} ca ON ca.id = c.category
                        WHERE         ca.id = :category
                        ORDER BY      c.fullname";
        try {
            $courses[0] = get_string('selectone', 'local_friadmin'); // Sets the first value in the array as "Select one...".
            $rdo = $DB->get_records_sql($coursequery, array('category' => $category));

            return $rdo;
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_courses_js

    /**
     * Description
     * A function used to get the correct javasript values, it calls the M.core_user.init_courses in the javascript
     *
     * @param           $course
     * @param           $category
     * @param           $prevcourse
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_javascript_values($course, $category, $prevcourse) {
        // Variables!
        global $PAGE;
        $name = 'lst_courses';
        $path = '/local/friadmin/reports/js/report.js';
        $requires = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
        $grpthree = array('none', 'moodle');
        $strings = array($grpthree);

        // Initialise js module.
        $jsmodule = array('name' => $name,
            'fullpath' => $path,
            'requires' => $requires,
            'strings' => $strings
        );

        $PAGE->requires->js_init_call('M.core_user.init_courses',
            array($course, $category, $prevcourse),
            false,
            $jsmodule
        );
    }

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
        $rdo    = null;
        $query  = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['categoryid']   = $data->category;
            $params['from']         = $data->selsummaryfrom;
            $params['to']           = $data->selsummaryto;

            // SQL Instruction
            $query = " SELECT       c.id			    as 'courseid',			-- The course ID
                                    c.fullname 		    as 'coursefull', 		-- Course full name
                                    c.shortname 	    as 'courseshort', 		-- Course short name
                                    c.format 		    as 'courseformat', 	    -- Course format,
                                    c.visible		    as 'visibility',	    -- Course visibility								--
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
                       WHERE 	 c.category = :categoryid
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

        try {
            // Search criteria
            $params = array();
            $params['category'] = $data->category;

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
                                                        AND c.category		    = :category
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

        try {
            // Search criteria
            $params = array();
            $params['category'] = $category;

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
                                    co.name                             as 'levelone',
                                    cl.name                             as 'location',
                                    fo1.value                           as 'fromto',
                                    c.visible                           as 'visibility'
                       FROM         {user}                    u
                          -- Course
                          JOIN 		{role_assignments}		  ra 	ON ra.userid  = u.id
						  JOIN		{context}				  ct	ON ct.id 	  = ra.contextid
                          JOIN      {course}                  c     ON c.id       = ct.instanceid

                          -- Category
                          JOIN      {course_categories}       ca    ON  ca.id = c.category
                                                                    AND ca.id = :category
                          -- Location
                          LEFT JOIN {course_format_options}   fo    ON  fo.courseid = c.id
                                                                    AND fo.name     = 'course_location'
                          LEFT JOIN {course_locations}        cl    ON  cl.id       = fo.value
                          LEFT JOIN {report_gen_companydata}  co    ON  co.id       = cl.levelone
                          -- Dates
                          LEFT JOIN {course_format_options}   fo1   ON  fo1.courseid = c.id
                                                                    AND fo1.name     = 'time'
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

        try {
            // Search criteria
            $params = Array();
            $params['category'] = $data->category;

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
                                  cl.name		as 'location',
                                  fo1.value		as 'fromto',
                                  c.visible		as 'visibility'
                     FROM		  mdl_course				c
                        -- Coordinators
                        JOIN	  {context}				    ct	ON  ct.instanceid 	= c.id
                        JOIN 	  {role_assignments}		ra	ON  ra.contextid	= ct.id
                        JOIN  	  {role}					r 	ON 	r.id 		    = ra.roleid
                                                                AND r.archetype    	= 'editingteacher'
                        -- User criteria
                        $joinuser
                        -- Category
                        JOIN	  {course_categories}		ca	ON  ca.id	      	= c.category
                                                                AND ca.id 			= :category
                        -- Location
                        LEFT JOIN {course_format_options}   fo  ON  fo.courseid   	= ct.instanceid
                                                                AND fo.name       	= 'course_location'
                        LEFT JOIN {course_locations}        cl  ON  cl.id         	= fo.value
                        LEFT JOIN {report_gen_companydata}  co  ON  co.id         	= cl.levelone
                        -- From/to (time)
                        LEFT JOIN {course_format_options}   fo1 ON  fo1.courseid 	= ct.instanceid
                                                                AND fo1.name      	= 'time' 
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
            self::add_participants_header_excel($myxls, $coursesdata);
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
            $mycategory = self::get_category_name($data->category);

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
            $mycategory = self::get_category_name($data->category);
            $mycourse   = self::get_course_name($data->course);

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
        $strcoordinator = get_string('coordinatorexcel', 'local_friadmin');
        $strcategory    = get_string('categoryexcel', 'local_friadmin');
        $strcourse      = get_string('courseexcel', 'local_friadmin');
        $strfullname    = get_string('fullnameexcel', 'local_friadmin');
        $strusername    = get_string('usernameexcel', 'local_friadmin');
        $stremail       = get_string('emailexcel', 'local_friadmin');
        $strworkplace   = get_string('workplaceexcel', 'local_friadmin');
        $strjobrole     = get_string('jobroleexcel', 'local_friadmin');

        try {
            // Course/Category name
            $mycategory = self::get_category_name($data->category);
            $mycourse = self::get_course_name($data->course);

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
     * @param   array     $sector     All the sectors in an array
     * @return  null      Returns the sectors in text format or null
     * @throws  Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function get_sectors($sector) {
        // Variables!
        global $DB;
        $rdo = null;     // Used to query the database.

        $query = "SELECT GROUP_CONCAT(DISTINCT cd.name ORDER BY cd.name SEPARATOR ',') as 'sectors'
                  FROM 	{report_gen_companydata} cd
                  WHERE   id IN (:sector)
	                AND hierarchylevel = 2";

        try {
            $params = array();
            $params['sector'] = $sector;

            $rdo = $DB->get_record_sql($query, $params);

            if ($rdo) {
                return $rdo->sectors;
            } else {
                return null;
            }
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
    public static function get_coordinator($courseid) {
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
    private static function add_participants_header_excel(&$myxls, $coursesdata) {
        // Variables.
        GLOBAL $SESSION;
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
        $strfromto          = null;
        $strdates           = null;
        $strnumberdays      = null;
        $fromtodates        = null;
        $maxdates           = null;
        $h                  = null;
        $w                  = null;


        try {
            // Headers
            $strcoursefull      = get_string('courselong', 'local_friadmin');
            $strcourseshort     = get_string('courseshort', 'local_friadmin');
            $strcourseformat    = get_string('courseformat', 'local_friadmin');
            $strproducer        = get_string('producer', 'local_friadmin');
            $strlevelone        = get_string('kommune', 'local_friadmin');
            $strsector          = get_string('sector', 'local_friadmin');
            $strlocation        = get_string('usercourse_location','local_friadmin');
            $strcategory        = get_string('category', 'local_friadmin');
            $strexpiration      = get_string('expiration', 'local_friadmin');
            $strspots           = get_string('spots', 'local_friadmin');
            $strinternalprice   = get_string('internalprice', 'local_friadmin');
            $strexternalprice   = get_string('externalprice', 'local_friadmin');
            $strinstructors     = get_string('instructors', 'local_friadmin');
            $strstudents        = get_string('students', 'local_friadmin');
            $strwaiting         = get_string('waitinglist', 'local_friadmin');
            $strcompleted       = get_string('completed', 'local_friadmin');
            $strvisibility      = get_string('visible', 'local_friadmin');
            $strfromto          = get_string('fromto', 'local_friadmin');
            $strdates           = get_string('dates', 'local_friadmin');
            $strnumberdays      = get_string('numberofdays', 'local_friadmin');
            $strcoursecoordinator = get_string('coursecoordinator', 'local_friadmin');

            // Get max dates
            $SESSION->maxdates  = null;
            if ($coursesdata) {
                foreach ($coursesdata as $coursevalue) {
                    $fromtodates = explode(",", $coursevalue->fromto);
                    if ($maxdates < count($fromtodates)) {
                        $maxdates = count($fromtodates);
                    }
                }
            }else {
                $maxdates = 1;
            }
            $SESSION->maxdates = $maxdates;


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

            // Course dates.
            $col ++;
            $i = 1;
            while ($i <= $maxdates) {
                $myxls->write($row, $col, $strdates . $i, array(
                    'size' => 12,
                    'name' => 'Arial',
                    'bold' => '1',
                    'bg_color' => '#efefef',
                    'text_wrap' => true,
                    'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$ws);
                $col ++;
                $i ++;
            }

            // Number of days.
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
        GLOBAL $SESSION;
        // Variables.
        $i              = null;
        $col            = 0;
        $last           = null;
        $workplaces     = null;
        $setRow         = null;
        $strUser        = null;
        $completion     = null;
        $maxdates       = null;
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
                // Extract From/To
                $fromtodates = explode(",", $course->fromto);

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
                $format = (get_string_manager()->string_exists($course->courseformat,'local_admin')
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

                // Dates.
                $col ++;
                if ($fromtodates) {
                    $i = 0;
                    // Loop that sets the dates into the excel if there are any dates.
                    foreach ($fromtodates as $date) {
                        // If the date is not empty.
                        if ($date != '') {
                            $myxls->write($row, $col, $date, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'top'));
                            $myxls->merge_cells($row, $col, $row, $col);
                            $myxls->set_row($row,$h);
                            $myxls->set_column($col,$col,$ws);
                            $col ++;
                            $i++;
                        }
                    }

                    // Creates emtpy cells in excel up to the max amount of dates found.
                    while ($i < $SESSION->maxdates) {
                        $myxls->write($row, $col, '', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'top'));
                        $myxls->merge_cells($row, $col, $row, $col);
                        $myxls->set_row($row,$h);
                        $myxls->set_column($col,$col,$ws);
                        $col ++;
                        $i++;
                    }
                }

                // Number of days.
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
                $myxls->write($row, $col, $course->instructors, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);


                // Students.
                $col ++;
                $myxls->write($row, $col, $course->students, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Waiting.
                $col ++;
                $myxls->write($row, $col, $course->waiting, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row,$h);
                $myxls->set_column($col,$col,$ws);

                // Completed.
                $col ++;
                $myxls->write($row, $col, $course->completed, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
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
        GLOBAL $SESSION;
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
        $strdates           = null;
        $strfromto          = null;
        $strvisibility      = null;
        $maxdates           = null;
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
            $strdates           = get_string('dates', 'local_friadmin');
            $strfromto          = get_string('fromto', 'local_friadmin');
            $strvisibility      = get_string('visible', 'local_friadmin');

            // Get max dates
            $SESSION->maxdates = null;
            if ($coursesdata) {
                foreach ($coursesdata as $coursevalue) {
                    $fromtodates = explode(",", $coursevalue->fromto);
                    if ($maxdates < count($fromtodates)) {
                        $maxdates = count($fromtodates);
                    }
                }
            }else {
                $maxdates = 1;
            }
            $SESSION->maxdates = $maxdates;

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

            // Course dates.
            $col ++;
            $i = 1;
            while ($i <= $maxdates) {
                $myxls->write($row, $col, $strdates . $i, array(
                    'size' => 12,
                    'name' => 'Arial',
                    'bold' => '1',
                    'bg_color' => '#efefef',
                    'text_wrap' => true,
                    'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$ws);
                $col ++;
                $i ++;
            }

            // Fromto.
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
        GLOBAL $SESSION;
        $col            = 0;
        $last           = null;
        $workplaces     = null;
        $setrow         = null;
        $struser        = null;
        $completion     = null;
        $maxdates       = null;
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
                // Extract from/to
                $fromtodates = explode(",", $course->fromto);

                // Coordinator
                $coordinator = self::get_coordinator($course->courseid);

                // Instructor name.
                $myxls->write($row, $col, $course->instr, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Course fullname.
                $col ++;
                $myxls->write($row, $col, $course->coursename, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
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
                $format = (get_string_manager()->string_exists($course->courseformat,'local_admin')
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

                // Dates.
                $col ++;
                if ($fromtodates) {
                    $i = 0;
                    // Loop that sets the dates into the excel if there are any dates.
                    foreach ($fromtodates as $date) {
                        // If the date is not empty.
                        if ($date != '') {

                            $myxls->write($row, $col, $date, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'top'));
                            $myxls->merge_cells($row, $col, $row, $col);
                            $myxls->set_row($row, $h);
                            $myxls->set_column($col,$col,$ws);
                            $col ++;
                            $i++;
                        }
                    }

                    // Creates emtpy cells in excel up to the max amount of dates found.
                    while ($i < $SESSION->maxdates) {
                        $myxls->write($row, $col, '', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'top'));
                        $myxls->merge_cells($row, $col, $row, $col);
                        $myxls->set_row($row, $h);
                        $myxls->set_column($col,$col,$ws);
                        $col ++;
                        $i++;
                    }
                }

                // Fromto.
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
        GLOBAL $SESSION;
        $col                = 0;
        $row                = 0;
        $strinstructorname  = null;
        $strcoursename      = null;
        $strcategory        = null;
        $strcourseformat    = null;
        $strlevelone        = null;
        $strlocation        = null;
        $strcoordinatorname = null;
        $strdates           = null;
        $strfromto          = null;
        $strvisibility      = null;
        $maxdates           = null;
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
            $strdates           = get_string('dates', 'local_friadmin');
            $strfromto          = get_string('fromto', 'local_friadmin');
            $strvisibility      = get_string('visible', 'local_friadmin');

            // Get max dates
            $SESSION->maxdates = null;
            if ($coursesdata) {
                foreach ($coursesdata as $coursevalue) {
                    $fromtodates = explode(",", $coursevalue->fromto);
                    if ($maxdates < count($fromtodates)) {
                        $maxdates = count($fromtodates);
                    }
                }
            }else {
                $maxdates = 1;
            }
            $SESSION->maxdates = $maxdates;

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

            // Course dates.
            $col ++;
            $i = 1;
            while ($i <= $maxdates) {
                $myxls->write($row, $col, $strdates . $i, array(
                    'size' => 12,
                    'name' => 'Arial',
                    'bold' => '1',
                    'bg_color' => '#efefef',
                    'text_wrap' => true,
                    'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$ws);
                $col ++;
                $i ++;
            }

            // Fromto.
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
        GLOBAL $SESSION;
        $col            = 0;
        $last           = null;
        $workplaces     = null;
        $setrow         = null;
        $struser        = null;
        $completion     = null;
        $maxdates       = null;
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
                // Get coordinator
                $coordinator = self::get_coordinator($course->id);

                // Extract from/to
                $fromtodates = explode(",", $course->fromto);

                // Coordinatorname name.
                $myxls->write($row, $col, $coordinator, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Course fullname.
                $col ++;
                $myxls->write($row, $col, $course->coursename, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
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
                $format = (get_string_manager()->string_exists($course->courseformat,'local_admin')
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

                // Location
                $col ++;
                $myxls->write($row, $col, $course->location, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col);
                $myxls->set_row($row, $h);
                $myxls->set_column($col,$col,$w);

                // Dates.
                $col ++;
                if ($fromtodates) {
                    $i = 0;
                    // Loop that sets the dates into the excel if there are any dates.
                    foreach ($fromtodates as $date) {
                        // If the date is not empty.
                        if ($date != '') {
                            $myxls->write($row, $col, $date, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'top'));
                            $myxls->merge_cells($row, $col, $row, $col);
                            $myxls->set_row($row, $h);
                            $myxls->set_column($col,$col,$ws);
                            $col ++;
                            $i++;
                        }
                    }

                    // Creates emtpy cells in excel up to the max amount of dates found.
                    while ($i < $SESSION->maxdates) {
                        $myxls->write($row, $col, '', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'top'));
                        $myxls->merge_cells($row, $col, $row, $col);
                        $myxls->set_row($row, $h);
                        $myxls->set_column($col,$col,$ws);
                        $col ++;
                        $i++;
                    }
                }

                // Fromto.
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

    /**
     * Description
     * Gets the categoryname from the category id selected by the user in the form
     *
     * @param   integer $category    The category integer selected by the user in the form
     *
     * @return  string  $rdo         The category name
     * @throws          Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_category_name($category) {
        // Variables!
        global $DB;
        $rdo = null;

        $query = "SELECT  ca.name
                  FROM    {course_categories} ca
                  WHERE   ca.id = :category";

        try {
            $params = array();
            $params['category'] = $category;

            $rdo = $DB->get_record_sql($query, $params);

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
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_course_name($course) {
        // Variables!
        global $DB;
        $rdo = null;

        $query = "SELECT  c.fullname
                  FROM    {course} c
                  WHERE   c.id = :course";

        try {
            $params = array();
            $params['course'] = $course;

            $rdo = $DB->get_record_sql($query, $params);

            // Gets the category.
            if ($rdo) {
                return $rdo->fullname;
            } else {
                return null;
            }
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_categories
}