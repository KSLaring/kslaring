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
//
// * friadmin(local) - categoryrptlib
// *
// * @package         local                                                 !
// * @subpackage      friadmin/reports                                      !
// * @copyright       2017        eFaktor {@link http://www.efaktor.no}     !
// *                                                                        !
// * @updateDate      23/05/2017                                            !
// * @author          eFaktor     (nas)                                     !

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
     * @param   integer         $category   The category selected by the user in the form (required)
     * @param   integer         $from       The unix timestamp for from date, selected by the user in the form (required)
     * @param   integer         $to         The unix timestamp for to date, selected by the user in the form (required)
     * @return array|null
     * @throws Exception
     *
     * @updateDate 23/05/2017
     * @author      eFaktor     (nas)
     *
     */
    public static function get_course_summary_data($category, $from, $to) {
        /* Variables */
        global $DB;
        $rdo    = null;
        $query  = null;
        $params = null;

        try {
            // Search criteria
            $params = array();
            $params['categoryid']   = $category;
            $params['from']         = $from;
            $params['to']           = $to;

            // SQL Instruction
            $query = " SELECT       c.id			    as 'courseid',			-- The course ID
                                    c.fullname 		    as 'coursefull', 		-- Course full name
                                    c.shortname 	    as 'courseshort', 		-- Course short name
                                    c.format 		    as 'courseformat', 	    -- Course format,
                                    c.visible		    as 'visibility',	    -- Course visibility								--
                                    ca.name 		    as 'category', 		    -- Category Name
                                    mu.name				as 'levelone',			-- Municipality (Level One)
                                    l1.name				as 'location',			-- Course location,
                                    se.name				as 'sector',			-- Course Sector (Level two)
                                    fo3.value		    as 'producer',			-- Produced by
                                    fo4.value 		    as 'fromto',			-- From - To
                                    e.customint1		as 'deadline',			-- Deadline
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
                          LEFT JOIN	{report_gen_companydata}  se	ON	se.id		  = fo2.value
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
                       GROUP BY c.id ";

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
     * @param integer   $course     Selected by the user in the form (optional)
     * @param integer   $category   Selected by the user in the form (required)
     * @param string    $fullname   Written by the user in the form (optional)
     * @param string    $username   Written by the user in the form (optional)
     * @param string    $email      Written by the user in the form (optional)
     * @param string    $workplace  Written by the user in the form (optional)
     * @param string    $jobrole    Written by the user in the form (optional)
     * @return array|null Returns the ID of all the instructors
     * @throws Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_course_instructors($course, $category, $fullname, $username, $email, $workplace, $jobrole) {
        // Variables!
        global $DB;
        $rdo            = null;
        $extrasql       = '';
        $workplacesql   = '';
        $jobrolesql     = '';
        $params         = null;
        $query          = null;

        try {
            // Search criteria
            $params = array();
            $params['category'] = $category;

            // Course.
            if ($course) {
                $params['course']   = $course;
                $extrasql .= " AND c.id = :course ";
            }

            // Users fullname.
            if ($fullname) {
                $extrasql .= " AND CONCAT(u.firstname, ' ', u.lastname) LIKE '%" . $fullname . "%' ";
            }

            // Username.
            if ($username) {
                $extrasql .= " AND u.username LIKE '%" . $username . "%' ";
            }

            // Email.
            if ($email) {
                $extrasql .= " AND u.email LIKE '%" . $email . "%' ";
            }

            // Workplace.
            if ($workplace) {
                $workplacesql = " JOIN {user_info_competence_data} 	uic ON  uic.userid  = u.id
                                  JOIN {report_gen_companydata} 	rgc ON  rgc.id      = uic.competenceid
                                                                        AND rgc.name LIKE '%" . $workplace . "'%' ";
            }

            // Jobrole.
            if ($jobrole) {
                $jobrolesql = " JOIN {user_info_competence_data}  uic2 ON  uic2.userid = u.id
                                JOIN {report_gen_jobrole}         gjr  ON  gjr.id IN (uic2.jobroles)
                                                                       AND gjr.name LIKE '%" . $jobrole . "%' ";
            }

            // Query.
            $query = " SELECT  DISTINCT u.id
                       FROM    {user}                u
                          -- INSTRUCTORS
                          JOIN  {role_assignments}    ra  ON  ra.userid   = u.id
                          JOIN  {context}             ct  ON  ct.id       = ra.contextid
                          JOIN  {role}                r   ON  r.id        = ra.roleid
                                                          AND r.archetype = 'teacher'
                          -- Course
                          JOIN 	{course}		      c	  ON  c.id        = ct.instanceid
                          -- Category
                          JOIN	{course_categories} ca  ON ca.id = c.category
    
                          -- Jobroles
                          $jobrolesql
                          -- Workplace
                          $workplacesql
                       WHERE u.deleted = 0
                          AND ca.id = :category
                          $extrasql ";

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
    } // end get_course_instructors

    /**
     * Description
     * Gets all the neccessary data from the database for course instructors
     *
     * @param array     $instructors    All the instructor ID's from get_course_instructors
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
        $myarray    = implode(',', array_keys($instructors));
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
                                    CONCAT(u.id,c.id)          as 'unique',
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
                          -- Location
                          LEFT JOIN {course_format_options}   fo    ON  fo.courseid = c.id
                                                                    AND fo.name     = 'course_location'
                          LEFT JOIN {course_locations}        cl    ON  cl.id       = fo.value
                          LEFT JOIN {report_gen_companydata}  co    ON  co.id       = cl.levelone
                          -- Dates
                          LEFT JOIN {course_format_options}   fo1   ON  fo1.courseid = c.id
                                                                    AND fo1.name     = 'time'
                       WHERE u.deleted = 0
                          AND ca.id = :category
                          AND u.id IN ($myarray)
                          $extrasql ";


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
     * Gets all the neccessary information from the database for the coordinators
     *
     * @param integer   $course         The course selected by the user in the form (optional)
     * @param integer   $category       The category selected by the user in the form (required)
     * @return array|null Returns all the data used in the coordinator excel
     * @throws Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_course_coordinator_data($course, $category) {
        // Variables!
        global $DB;
        $rdo = null;
        $extrasql = ' ';

        if ($course) {
            $extrasql .= " AND c.id = :course ";
        }

        $query = "  SELECT 	    DISTINCT CONCAT(u.id, c.id) 		as 'unique',
                                CONCAT(u.firstname, ' ', u.lastname)as 'coursecoordinator',
                                c.fullname							as 'coursename',
                                fo1.value							as 'fromto',
                                c.visible							as 'visibility',
                                ca.name                             as 'category',
                                c.format                            as 'courseformat',
                                co.name                             as 'levelone'
                    FROM		{user}					  u
                    JOIN 	    {role_assignments}		  ra 	    ON  ra.userid 	  = u.id
                    JOIN	    {context}				  ct	    ON  ct.id 	 	  = ra.contextid
                    JOIN  	    {role}					  r 	    ON 	r.id 	 	  = ra.roleid
                    JOIN 	    {course}			      c		    ON  c.id 		  = ct.instanceid
                    JOIN	    {course_categories}		  ca	    ON  ca.id	      = c.category
                    LEFT JOIN   {course_format_options}   fo        ON  fo.courseid   = c.id
                                                                    AND fo.name       = 'course_location'
                    LEFT JOIN   {course_locations}        cl        ON  cl.id         = fo.value
                    LEFT JOIN   {report_gen_companydata}  co        ON  co.id         = cl.levelone
                    LEFT JOIN 	{course_format_options}   fo1 	    ON  fo1.courseid  = c.id
                                                                    AND fo1.name      = 'time'
                    WHERE u.deleted = 0
                    AND ca.id = :category
                    $extrasql
                    GROUP BY c.id
                    ORDER BY ra.id";

        try {
            $params = array();
            $params['course'] = $course;
            $params['category'] = $category;

            $rdo = $DB->get_records_sql($query, $params);

            if ($rdo) {
                return $rdo;
            } else {
                return null;
            }
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_course_coordinator_data

    /**
     * Description
     * Creates the excel for the summary report
     *
     * @param array     $coursesdata   The data from the get_course_summary_data
     * @param integer   $from          The from unix timestamp selected by the user in the form
     * @param integer   $to            The to unix timestamp selected by the user in the form
     * @throws Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function download_participants_list($coursesdata, $from, $to, $category) {
        // Variables.
        global $CFG;
        $row = 0;
        $time = null;
        $name = null;
        $export = null;
        $myxls = null;

        try {
            require_once($CFG->dirroot . '/lib/excellib.class.php');

            $time = userdate(time(), '%d.%m.%Y', 99, false);
            $name = clean_filename(get_string('participantslistsummary', 'local_friadmin') . $time . ".xls");
            // Creating a workbook.
            $export = new MoodleExcelWorkbook($name);

            // Search criterias.
            $myxls = $export->add_worksheet(get_string('filter', 'local_friadmin'));
            self::add_participants_excel_filter($myxls, $row, $from, $to, $category);

            // Raw.
            $myxls = $export->add_worksheet(get_string('content', 'local_friadmin'));
            // Headers.
            self::add_participants_header_excel($myxls, $row, $coursesdata);
            // Content.
            //self::add_participants_content_excel($coursesdata, $myxls, $row, $from, $to);

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
     * @param array $coursesdata   The data from the get_course_instructor_data (array of objects)
     * @throws Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function download_participants_list_instructor(
        $coursesdata, $category, $course, $userfullname, $username, $useremail, $userworkplace, $userjobrole) {

        // Variables.
        global $CFG;
        $row = 0;
        $time = null;
        $name = null;
        $export = null;
        $myxls = null;

        try {
            require_once($CFG->dirroot . '/lib/excellib.class.php');

            $time = userdate(time(), '%d.%m.%Y', 99, false);
            $name = clean_filename(get_string('participantslistinstructors', 'local_friadmin') . $time . ".xls");
            // Creating a workbook.
            $export = new MoodleExcelWorkbook($name);

            // Search criterias.
            $myxls = $export->add_worksheet(get_string('filter', 'local_friadmin'));

                self::add_participants_excel_filter_instructor(
                    $myxls, $row, $category, $course, $userfullname, $username, $useremail, $userworkplace, $userjobrole);

            // Raw.
            $myxls = $export->add_worksheet(get_string('content', 'local_friadmin'));

                // Headers.
                self::add_participants_header_excel_instructor($myxls, $row, $coursesdata);
                // Content.
                self::add_participants_content_excel_instructor($coursesdata, $myxls, $row);

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
     * @param array $coursesdata   The data from the get_course_coordinator_report (array of objects)
     * @throws Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function download_participants_list_coordinator(
        $coursesdata, $category, $course, $userfullname, $username, $useremail, $userworkplace, $userjobrole) {

        // Variables.
        global $CFG;
        $row = 0;
        $time = null;
        $name = null;
        $export = null;
        $myxls = null;

        try {
            require_once($CFG->dirroot . '/lib/excellib.class.php');

            $time = userdate(time(), '%d.%m.%Y', 99, false);
            $name = clean_filename(get_string('participantslistcoordinators', 'local_friadmin') . $time . ".xls");
            // Creating a workbook.
            $export = new MoodleExcelWorkbook($name);

            // Search criterias.
            $myxls = $export->add_worksheet(get_string('filter', 'local_friadmin'));

            self::add_participants_excel_filter_coordinator(
                $myxls, $row, $category, $course, $userfullname, $username, $useremail, $userworkplace, $userjobrole);

            // Raw.
            $myxls = $export->add_worksheet(get_string('content', 'local_friadmin'));

            // Headers.
            self::add_participants_header_excel_coordinator($myxls, $row, $coursesdata);
            // Content.
            self::add_participants_content_excel_coordinator($coursesdata, $myxls, $row);

            $export->close();
            exit;
        } catch (Exception $ex) {
            throw $ex;
        }
    }//download_participants_list_coordinator

    /**
     * Description
     * Adds the first page to the summary excel and writes all the search criterias to it
     *
     * @param $myxls
     * @param $row
     * @param $from
     * @param $to
     * @param $category
     * @throws Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function add_participants_excel_filter(&$myxls, $row, $from, $to, $category) {
        // Variables.
        $col        = 0;
        $row        = 0;
        $strsummary     = get_string('summaryrptexcel', 'local_friadmin');
        $strcategory    = get_string('categoryexcel', 'local_friadmin');
        $strfrom        = get_string('fromexcel', 'local_friadmin');
        $strto          = get_string('toexcel', 'local_friadmin');

        $myfrom = date("d-m-Y", $from);
        $myto   = date("d-m-Y", $to);

        try {

            $mycategory = self::get_category_name($category);

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
     * @param $myxls
     * @param $row
     * @param $category
     * @param $course
     * @param $userfullname
     * @param $username
     * @param $useremail
     * @param $userworkplace
     * @param $userjobrole
     * @throws Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function add_participants_excel_filter_instructor(
        &$myxls, $row, $category, $course, $userfullname, $username, $useremail, $userworkplace, $userjobrole) {

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

            $mycategory = self::get_category_name($category);
            $mycourse = self::get_course_name($course);

            // Instructor Report Header.
            $myxls->write($row, $col, $strinsructor, array(
                'size' => 22,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#d4d4d4',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Category Header.
            $row += 1;
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
            $myxls->write($row, $col, $userfullname, array(
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
            $myxls->write($row, $col, $username, array(
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
            $myxls->write($row, $col, $useremail, array(
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
            $myxls->write($row, $col, $userworkplace, array(
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
            $myxls->write($row, $col, $userjobrole, array(
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
     * @param $myxls
     * @param $row
     * @param $category
     * @param $course
     * @param $userfullname
     * @param $username
     * @param $useremail
     * @param $userworkplace
     * @param $userjobrole
     * @throws Exception
     *
     * @updateDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function add_participants_excel_filter_coordinator(
        &$myxls, $row, $category, $course, $userfullname, $username, $useremail, $userworkplace, $userjobrole) {

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

            $mycategory = self::get_category_name($category);
            $mycourse = self::get_course_name($course);

            // Coordinator Report Header.
            $myxls->write($row, $col, $strcoordinator, array(
                'size' => 22,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#d4d4d4',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Category Header.
            $row += 1;
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
            $myxls->write($row, $col, $userfullname, array(
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
            $myxls->write($row, $col, $username, array(
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
            $myxls->write($row, $col, $useremail, array(
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
            $myxls->write($row, $col, $userworkplace, array(
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
            $myxls->write($row, $col, $userjobrole, array(
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
                  WHERE   id IN ($sector)
	                AND hierarchylevel = 2";

        try {
            $rdo = $DB->get_record_sql($query);

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
     */
    public static function get_coordinator($courseid) {
        // Variables!
        global $DB;
        $rdo = null;
        $empty = '';

        $query = "  SELECT 		ue.id,
                                e.courseid,
                                concat(u.firstname, ' ', u.lastname)		as 'cord'
                    FROM 		{enrol} 			    e
                        JOIN	{user_enrolments} 		ue 	ON 	ue.enrolid 	= e.id
                        JOIN	{user}					u	ON 	u.id 		= ue.userid
                        JOIN 	{role_assignments}		ra	ON 	ra.userid 	= u.id
                        JOIN 	{role}					r	ON 	r.id 		= ra.roleid
                                                            AND r.archetype = 'editingteacher'
                    WHERE courseid = :courseid
                    ORDER BY ue.id
                    LIMIT 0,1";
        try {

            $params = array();
            $params['courseid'] = $courseid;
            $rdo = $DB->get_record_sql($query, $params);

            if ($rdo) {
                return $rdo->cord;
            } else {
                return $empty;
            }
        } catch (Exception $ex) {
            Throw $ex;
        }
    } // end get_coordinator

    /**
     * Description
     * Add the header of the table to the excel report for the summary
     *
     * @param           $myxls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function add_participants_header_excel(&$myxls, $row, $coursesdata) {
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

        $SESSION->maxdates = null;

        try {
            $strcoursefull      = get_string('courselong', 'local_friadmin');
            $strcourseshort     = get_string('courseshort', 'local_friadmin');
            $strcourseformat    = get_string('courseformat', 'local_friadmin');
            $strproducer        = get_string('producer', 'local_friadmin');
            $strlevelone        = get_string('levelone', 'local_friadmin');
            $strsector          = get_string('sector', 'local_friadmin');
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
            $maxdates           = null;

            foreach ($coursesdata as $coursevalue) {
                $fromtodates = explode(",", $coursevalue->fromto);
                if ($maxdates < count($fromtodates)) {
                    $maxdates = count($fromtodates);
                }
            }

            $SESSION->maxdates = $maxdates;

            // Course fullname.
            $myxls->write($row, $col, $strcoursefull, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Course shortname.
            $col += 5;
            $myxls->write($row, $col, $strcourseshort, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 3);
            $myxls->set_row($row, 20);

            // Course format.
            $col += 4;
            $myxls->write($row, $col, $strcourseformat, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Category.
            $col += 2;
            $myxls->write($row, $col, $strcategory, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Producer.
            $col += 5;
            $myxls->write($row, $col, $strproducer, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Levelone.
            $col += 5;
            $myxls->write($row, $col, $strlevelone, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Sector.
            $col += 3;
            $myxls->write($row, $col, $strsector, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Course coordinator.
            $col += 5;
            $myxls->write($row, $col, $strcoursecoordinator, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);
            $col += 5;

            // Course dates.
            $i = 1;
            while ($i <= $maxdates) {
                $myxls->write($row, $col, $strdates . $i, array(
                    'size' => 12,
                    'name' => 'Arial',
                    'bold' => '1',
                    'bg_color' => '#efefef',
                    'text_wrap' => true,
                    'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col + 1);
                $myxls->set_row($row, 20);
                $col += 2;
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
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Expiration.
            $col += 2;
            $myxls->write($row, $col, $strexpiration, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Spots.
            $col += 2;
            $myxls->write($row, $col, $strspots, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Internalprice.
            $col += 2;
            $myxls->write($row, $col, $strinternalprice, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Externalprice.
            $col += 2;
            $myxls->write($row, $col, $strexternalprice, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Instructors.
            $col += 2;
            $myxls->write($row, $col, $strinstructors, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Students.
            $col += 2;
            $myxls->write($row, $col, $strstudents, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Waiting.
            $col += 2;
            $myxls->write($row, $col, $strwaiting, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Completed.
            $col += 2;
            $myxls->write($row, $col, $strcompleted, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Visibility.
            $col += 2;
            $myxls->write($row, $col, $strvisibility, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Fromto.
            $col += 2;
            $myxls->write($row, $col, $strfromto, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            $fromtodates = null;

        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    } //add_participants_header_excel


    /**
     * Description
     * Add the header of the table to the excel report for instructors
     *
     * @param           $myxls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function add_participants_header_excel_instructor(&$myxls, $row, $coursesdata) {
        GLOBAL $SESSION;
        /* Variables */
        $col                = 0;
        $row                = 0;
        $strinstructorname  = null;
        $strcoursename      = null;
        $strcategory        = null;
        $strcourseformat    = null;
        $strlevelone        = null;
        $strcoordinatorname = null;
        $strdates           = null;
        $strfromto          = null;
        $strvisibility      = null;

        $SESSION->maxdates = null;

        try {
            $strinstructorname  = get_string('instructorname', 'local_friadmin');
            $strcoursename      = get_string('coursename', 'local_friadmin');
            $strcategory        = get_string('category', 'local_friadmin');
            $strcourseformat    = get_string('courseformat', 'local_friadmin');
            $strlevelone        = get_string('levelone', 'local_friadmin');
            $strcoordinatorname = get_string('coordinatorname', 'local_friadmin');
            $strdates           = get_string('dates', 'local_friadmin');
            $strfromto          = get_string('fromto', 'local_friadmin');
            $strvisibility      = get_string('visible', 'local_friadmin');

            $maxdates = null;

            foreach ($coursesdata as $coursevalue) {
                $fromtodates = explode(",", $coursevalue->fromto);
                if ($maxdates < count($fromtodates)) {
                    $maxdates = count($fromtodates);
                }
            }

            $SESSION->maxdates = $maxdates;

            // Instructor name.
            $myxls->write($row, $col, $strinstructorname, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Course fullname.
            $col += 5;
            $myxls->write($row, $col, $strcoursename, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Category.
            $col += 5;
            $myxls->write($row, $col, $strcategory, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Course format.
            $col += 5;
            $myxls->write($row, $col, $strcourseformat, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 3);
            $myxls->set_row($row, 20);

            // Levelone.
            $col += 4;
            $myxls->write($row, $col, $strlevelone, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);

            // Course coordinator.
            $col += 3;
            $myxls->write($row, $col, $strcoordinatorname, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);
            $col += 5;

            // Course dates.
            $i = 1;
            while ($i <= $maxdates) {
                $myxls->write($row, $col, $strdates . $i, array(
                    'size' => 12,
                    'name' => 'Arial',
                    'bold' => '1',
                    'bg_color' => '#efefef',
                    'text_wrap' => true,
                    'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col + 1);
                $myxls->set_row($row, 20);
                $col += 2;
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
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Visibility.
            $col += 2;
            $myxls->write($row, $col, $strvisibility, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

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
     */
    private static function add_participants_content_excel_instructor($coursedata, &$myxls, &$row) {
        // Variables!
        GLOBAL $SESSION;
        $col            = 0;
        $row            = 1;
        $last           = null;
        $workplaces     = null;
        $setrow         = null;
        $struser        = null;
        $completion     = null;
        $maxdates       = null;
        $mysectors      = null;

        try {
            if ($coursedata) {
                foreach ($coursedata as $coursevalue) {

                    $fromtodates = explode(",", $coursevalue->fromto);

                    $coordinator = self::get_coordinator($coursevalue->courseid);

                    // Instructor name.
                    $myxls->write($row, $col, $coursevalue->instr, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row, 20);

                    // Course fullname.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->coursename, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row, 20);

                    // Category.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->category, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row, 20);

                    // Course format.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->courseformat, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 3);
                    $myxls->set_row($row, 20);

                    // Levelone.
                    $col += 4;
                    $myxls->write($row, $col, $coursevalue->levelone, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 2);
                    $myxls->set_row($row, 20);

                    // Course coordinator.
                    $col += 3;
                    $myxls->write($row, $col, $coordinator, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row, 20);
                    $col += 5;

                    // Dates.
                    if ($fromtodates) {
                        $i = null;
                        foreach ($fromtodates as $date) {
                            if ($date != '') {
                                $myxls->write($row, $col, $date, array(
                                    'size' => 12,
                                    'name' => 'Arial',
                                    'text_wrap' => true,
                                    'v_align' => 'left'));
                                $myxls->merge_cells($row, $col, $row, $col + 1);
                                $myxls->set_row($row, 20);
                                $col += 2;
                            } else {
                                while ($i < $SESSION->maxdates) {
                                    $myxls->write($row, $col, '', array(
                                        'size' => 12,
                                        'name' => 'Arial',
                                        'text_wrap' => true,
                                        'v_align' => 'left'));
                                    $myxls->merge_cells($row, $col, $row, $col + 1);
                                    $myxls->set_row($row, 20);
                                    $col += 2;
                                    $i++;
                                }
                            }
                        }
                    }

                    // Fromto.
                    $myxls->write($row, $col, $coursevalue->fromto, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row, 20);

                    // Visibility.
                    $col += 2;
                    if ($coursevalue->visibility = 0) {
                        $myxls->write($row, $col, get_string('no', 'local_friadmin'), array(
                            'size' => 12,
                            'name' => 'Arial',
                            'text_wrap' => true,
                            'v_align' => 'left'));
                    } else if ($coursevalue->visibility = 1) {
                        $myxls->write($row, $col, get_string('yes', 'local_friadmin'), array(
                            'size' => 12,
                            'name' => 'Arial',
                            'text_wrap' => true,
                            'v_align' => 'left'));
                    }
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row, 20);

                    $row ++;
                    $col = 0;

                    $fromtodates = null;
                    $mysectors   = null;

                }//for_participants
            }//if_participantList
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_content_excel

    /**
     * Description
     * Add the header of the table to the excel report for the coordinators
     *
     * @param           $myxls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    23/05/2017
     * @author          eFaktor     (nas)
     */
    private static function add_participants_header_excel_coordinator(&$myxls, $row, $coursesdata) {
        // Variables!
        GLOBAL $SESSION;
        $col                = 0;
        $row                = 0;
        $strinstructorname  = null;
        $strcoursename      = null;
        $strcategory        = null;
        $strcourseformat    = null;
        $strlevelone        = null;
        $strcoordinatorname = null;
        $strdates           = null;
        $strfromto          = null;
        $strvisibility      = null;

        $SESSION->maxdates = null;

        try {
            $strcoursename      = get_string('coursename', 'local_friadmin');
            $strcategory        = get_string('category', 'local_friadmin');
            $strcourseformat    = get_string('courseformat', 'local_friadmin');
            $strlevelone        = get_string('levelone', 'local_friadmin');
            $strcoordinatorname = get_string('coordinatorname', 'local_friadmin');
            $strdates           = get_string('dates', 'local_friadmin');
            $strfromto          = get_string('fromto', 'local_friadmin');
            $strvisibility      = get_string('visible', 'local_friadmin');

            $maxdates = null;

            foreach ($coursesdata as $coursevalue) {
                $fromtodates = explode(",", $coursevalue->fromto);
                if ($maxdates < count($fromtodates)) {
                    $maxdates = count($fromtodates);
                }
            }

            $SESSION->maxdates = $maxdates;

            // Coordinator name.
            $myxls->write($row, $col, $strcoordinatorname, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Course fullname.
            $col += 5;
            $myxls->write($row, $col, $strcoursename, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Category.
            $col += 5;
            $myxls->write($row, $col, $strcategory, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row, 20);

            // Course format.
            $col += 5;
            $myxls->write($row, $col, $strcourseformat, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 3);
            $myxls->set_row($row, 20);

            // Levelone.
            $col += 4;
            $myxls->write($row, $col, $strlevelone, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row, 20);
            $col += 3;

            // Course dates.
            $i = 1;
            while ($i <= $maxdates) {
                $myxls->write($row, $col, $strdates . $i, array(
                    'size' => 12,
                    'name' => 'Arial',
                    'bold' => '1',
                    'bg_color' => '#efefef',
                    'text_wrap' => true,
                    'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col + 1);
                $myxls->set_row($row, 20);
                $col += 2;
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
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            // Visibility.
            $col += 2;
            $myxls->write($row, $col, $strvisibility, array(
                'size' => 12,
                'name' => 'Arial',
                'bold' => '1',
                'bg_color' => '#efefef',
                'text_wrap' => true,
                'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row, 20);

            $fromtodates = null;

        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_header_excel

    /**
     * Description
     * Adds the content to the coordinators excel document
     *
     * @param array    $coursedata     The information from the database (an array of objects)
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
        $row            = 1;
        $last           = null;
        $workplaces     = null;
        $setrow         = null;
        $struser        = null;
        $completion     = null;
        $maxdates       = null;
        $mysectors      = null;

        try {
            if ($coursedata) {
                foreach ($coursedata as $coursevalue) {

                    $fromtodates = explode(",", $coursevalue->fromto);

                    // Coordinatorname name.
                    $myxls->write($row, $col, $coursevalue->coursecoordinator, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row, 20);

                    // Course fullname.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->coursename, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row, 20);

                    // Category.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->category, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row, 20);

                    // Course format.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->courseformat, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 3);
                    $myxls->set_row($row, 20);

                    // Levelone.
                    $col += 4;
                    $myxls->write($row, $col, $coursevalue->levelone, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 2);
                    $myxls->set_row($row, 20);
                    $col += 3;

                    // Dates.
                    if ($fromtodates) {
                        $i = null;
                        foreach ($fromtodates as $date) {
                            if ($date != '') {
                                $myxls->write($row, $col, $date, array(
                                    'size' => 12,
                                    'name' => 'Arial',
                                    'text_wrap' => true,
                                    'v_align' => 'left'));
                                $myxls->merge_cells($row, $col, $row, $col + 1);
                                $myxls->set_row($row, 20);
                                $col += 2;
                            } else {
                                while ($i < $SESSION->maxdates) {
                                    $myxls->write($row, $col, '', array(
                                        'size' => 12,
                                        'name' => 'Arial',
                                        'text_wrap' => true,
                                        'v_align' => 'left'));
                                    $myxls->merge_cells($row, $col, $row, $col + 1);
                                    $myxls->set_row($row, 20);
                                    $col += 2;
                                    $i++;
                                }
                            }
                        }
                    }

                    // Fromto.
                    $myxls->write($row, $col, $coursevalue->fromto, array(
                        'size' => 12,
                        'name' => 'Arial',
                        'text_wrap' => true,
                        'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row, 20);

                    // Visibility.
                    $col += 2;
                    if ($coursevalue->visibility = 0) {
                        $myxls->write($row, $col, get_string('no', 'local_friadmin'), array(
                            'size' => 12,
                            'name' => 'Arial',
                            'text_wrap' => true,
                            'v_align' => 'left'));
                    } else if ($coursevalue->visibility = 1) {
                        $myxls->write($row, $col, get_string('yes', 'local_friadmin'), array(
                            'size' => 12,
                            'name' => 'Arial',
                            'text_wrap' => true,
                            'v_align' => 'left'));
                    }
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row, 20);

                    $row ++;
                    $col = 0;

                    $fromtodates = null;
                    $mysectors   = null;

                }//for_participants
            }//if_participantList
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