<?php
// This file is part of Historical (Local)
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
//
// * Historical(local) - Historicallib
// *
// * @package         local                                                 !
// * @subpackage      historical/reports                                    !
// * @copyright       2017        eFaktor {@link http://www.efaktor.no}     !
// *                                                                        !
// * @updateDate      20/01/2017                                            !
// * @author          eFaktor     (nas)                                     !

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
     * @updateDate    08/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_categories() {
        // Variables!
        global $DB;
        $categories = array();  // Array declaration for the categories.
        $rdo = null;     // Used to query the database.

        $query = "SELECT  ca.id,
		                  ca.name
                  FROM    {course_categories} ca";

        try {
            $categories[0] = get_string('selectone', 'local_friadmin'); // Sets the first value in the array as "Select one...".
            $rdo = $DB->get_records_sql($query);

            // Gets all the categories from the kurskategori table that does have courses in them.
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
     * @updateDate    08/03/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_courses() {
        // Variables!
        global $DB;
        $courses = array();  // Array declaration for the categories.
        $rdo = null;        // Used to query the database.

        $query = "SELECT  c.id,
		                  c.fullname
                  FROM    {course} c";

        try {
            $courses[0] = get_string('selectone', 'local_friadmin'); // Sets the first value in the array as "Select one...".
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
     * @updateDate    20/04/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_courses_js($category) {
        // Variables!
        global $DB;
        $courses = array();  // Array declaration for the courses.
        $rdo = null;     // Used to query the database.

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
     * @param           $course
     * @param           $category
     * @param           $prevcourse
     *
     * @updateDate    20/04/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_javascript_values($course, $category, $prevcourse) {

        global $PAGE;

        /* Initialise variables */
        $name = 'lst_courses';
        $path = '/local/friadmin/reports/js/report.js';
        $requires = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
        $grpthree = array('none', 'moodle');
        $strings = array($grpthree);

        /* Initialise js module */
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

    public static function get_course_summary_data($category, $from, $to) {
        // Variables!
        global $DB;
        $rdo = null;     // Used to query the database.

        $query = "SELECT            c.id			    as 'courseid',			-- The course ID
                                    c.fullname 		    as 'coursefull', 		-- Course full name
                                    c.shortname 	    as 'courseshort', 		-- Course short name
                                    c.format 		    as 'courseformat', 	    -- Course format
                                    fo4.value		    as 'producer',			-- Produced by
                                    l1.city			    as 'levelone',		    -- Municipality (level one) / Course location
                                    fo2.value		    as 'sector',			-- Course Sector
                                    ca.name 		    as 'category', 		    -- Category Name
                                    cord.cord 		    as 'coursecoordinator', -- Coordinator
                                                                                -- Course dates (duplicate, its the same as from - to)
                                                                                -- Number of days
                                    ue.timeend		    as 'expiration', 	    -- Deadline for enrolments
                                    e.customint2	    as 'spots',			    -- Number of places
                                    e.customtext3	    as 'internalprice',	    -- Internal price
                                    e.customtext4	    as 'externalprice',     -- external price
                                    ci.count		    as 'instructors',       -- Amount of instructors
                                    cs.count		    as 'students',		    -- Amount of students
                                    wa.count 		    as 'waiting',		    -- Amount in waitinglist
                                    cm.count		    as 'completed',		    -- Amount of completions
                                    c.visible		    as 'visibility',	    -- Course visibility
                                    fo3.value 		    as 'fromto'			    -- From - To
                FROM 				{course} 					c
                    -- Category
                    JOIN 			{course_categories} 		ca 	ON ca.id 		  = c.category
                    JOIN			{enrol}					    e 	ON e.courseid 	  = c.id
                    JOIN 			{user_enrolments}			ue 	ON ue.enrolid 	  = e.id
                    -- Format Options
                    JOIN			{course_format_options} 	fo 	ON fo.courseid 	  = c.id
                    -- Total Instructors
                    LEFT JOIN (
                        SELECT 		count(ra.userid) 	as 'count',
                                    ct.instanceid 		as 'course'
                        FROM		{role_assignments}		    ra
                            JOIN	{context}					ct	ON 	ct.id 		  = ra.contextid
                        JOIN  		{role}					    r 	ON 	r.id 		  = ra.roleid
                                                                    AND r.archetype   = 'teacher'
                        GROUP BY 	course
                    ) ci  ON 		ci.course = c.id
                    -- Total Students
                        LEFT JOIN (
                        SELECT 		count(ra.userid) 	as 'count',
                                    ct.instanceid 		as 'course'
                        FROM		{role_assignments}		    ra
                            JOIN	{context}					ct	ON 	ct.id 		  = ra.contextid
                        JOIN  		{role}					    r 	ON 	r.id 		  = ra.roleid
                                                                    AND r.archetype   = 'student'
                        GROUP BY 	course
                    ) cs  ON 		cs.course = c.id
                    -- Total Waiting
                        LEFT JOIN (
                        SELECT 		count(userid) 		as 'count',
                                    courseid			as 'course'
                        FROM		{enrol_waitinglist_queue}
                        WHERE		queueno != '99999'
                        GROUP BY 	courseid
                    ) wa  ON		wa.course = e.courseid
                    -- Total Completed
                        LEFT JOIN (
                        SELECT 		count(cc.userid) 	as 'count',
                                    cc.course 			as 'course'
                        FROM		{course_completions} 		cc
                            JOIN	{course} 					c 	ON 	c.id = cc.course
                        WHERE		cc.timecompleted IS NOT NULL
                        GROUP BY	course
                    ) cm  ON 		cm.course = c.id
                   -- Municipality
                    LEFT JOIN		{course_locations} 		    l1  ON 	l1.id 	      = fo.value
                                                                    AND fo.name       = 'course_location'
                   -- Sector
                    LEFT JOIN		{course_format_options}	    fo2	ON 	fo2.courseid  = c.id
                                                                    AND fo2.name 	  = 'course_sector'
                    -- Course Dates
                    LEFT JOIN		{course_format_options}	    fo3	ON 	fo3.courseid  = c.id
                                                                    AND fo3.name 	  = 'time'
                    -- Produced By
                    LEFT JOIN		{course_format_options}	    fo4	ON 	fo4.courseid  = c.id
                                                                    AND fo4.name 	  = 'producedby'
                    -- Course Coordinator / first teacher
                        LEFT JOIN (
                        SELECT 		ue.id,
                                    e.courseid,
                                    u.firstname 		as 'cord',
                                    u.lastname
                        FROM 		{enrol} 					e
                            JOIN	{user_enrolments} 		    ue 	ON 	ue.enrolid 	  = e.id
                            JOIN	{user}					    u	ON 	u.id 		  = ue.userid
                            JOIN 	{role_assignments}		    ra	ON 	ra.userid 	  = u.id
                            JOIN 	{role}					    r	ON 	r.id 		  = ra.roleid
                                                                    AND r.archetype   = 'editingteacher'
                        ORDER BY 	ue.id, courseid
                    ) cord ON 		cord.courseid = c.id
                WHERE ca.id = :categoryid
                AND   c.startdate >= :from
                AND   c.startdate <= :to
                GROUP BY c.id";

        try {
            $params = array();
            $params['categoryid'] = $category;
            $params['from'] = $from;
            $params['to'] = $to;

            $rdo = $DB->get_records_sql($query, $params);

            if ($rdo) {
                return $rdo;
            } else {
                return null;
            }
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_course_summarydata

    public static function get_course_instructor_data($course, $category, $fullname, $username, $email) {
        // Variables!
        global $DB;
        $rdo = null;
        $extrasql = '';

        if ($course) {
            $extrasql .= " AND c.id = :course ";
        }

        if ($category) {
            $extrasql .= " AND ca.id = :category ";
        }

        if ($fullname) {
            $extrasql .= " AND CONCAT(u.firstname, ' ', u.lastname) LIKE '%" . $fullname . "%' ";
        }

        if ($username) {
            $extrasql .= " AND u.username LIKE '%" . $username . "%' ";
        }

        if ($email) {
            $extrasql .= " AND u.email LIKE '%" . $email . "%' ";
        }


        $query = "  SELECT 	CONCAT(u.id,c.id) 				    as 'unique',
                            CONCAT(u.firstname,' ', u.lastname) as 'instr',
                            c.fullname							as 'coursename',
                            ca.name								as 'category',
                            c.format							as 'courseformat',
                            co.cord								as 'coursecoordinator',
                            cl.city								as 'levelone',
                            fo1.value							as 'fromto',
                            c.visible							as 'visibility'
                            
                    FROM		  {user}				  u
                        -- Instructors
                        JOIN 	  {role_assignments}	  ra  ON ra.userid 	= u.id
                        JOIN	  {context}				  ct  ON ct.id 	 	= ra.contextid
                        JOIN  	  {role}				  r   ON 	r.id 	 	= ra.roleid
                                                              AND r.archetype = 'teacher'
                        -- Course
                        JOIN 	  {course}				  c	  ON c.id 		= ct.instanceid
                        -- Category
                        JOIN	  {course_categories}     ca  ON ca.id	= c.category
                        -- Location
                        LEFT JOIN {course_format_options} fo  ON fo.courseid = c.id
                                                              AND fo.name = 'course_location'
                        LEFT JOIN {course_locations}	  cl  ON cl.id = fo.value
                        -- Dates
                        LEFT JOIN {course_format_options} fo1 ON fo1.courseid = c.id
                                                              AND fo1.name = 'time'
                        -- Coordinator
                        LEFT JOIN (
                            SELECT 		ra.userid,
                                        ct.instanceid 		                 as 'course',
                                        concat(u.firstname, ' ', u.lastname) as 'cord'
                            FROM		{role_assignments}		ra
                                JOIN	{context}				ct	ON 	ct.id 		= ra.contextid
                            JOIN  		{role}					r 	ON 	r.id 		= ra.roleid
                                                                    AND r.archetype = 'editingteacher'
                            JOIN		{user}                  u 	ON	u.id = ra.userid
                            GROUP BY course
                        ) co  ON co.course = c.id
                    WHERE 	u.deleted = 0
                    $extrasql
                    ORDER BY u.id";

        try {

            $params = array();

            if ($course) {
                $params['course'] = $course;
            }

            if ($category) {
                $params['category'] = $category;
            }

            $rdo = $DB->get_records_sql($query, $params);

            if ($rdo) {
                return $rdo;
            } else {
                return null;
            }
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_categories

    public static function get_course_coordinator_data($course, $category, $fullname, $userjobrole, $username) {
        // Variables!
        global $DB;
        $rdo = null;

        $extrasql = '';

        if ($course) {
            $extrasql .= " AND c.id = :course";
        }

        if ($category) {
            $extrasql .= " AND ca.id = :category";
        }

        if ($fullname) {
            $extrasql .= " AND CONCAT(u.firstname, ' ', u.lastname) LIKE '%" . $fullname . "%' ";
        }

        /*
        if ($userjobrole) {
            $extraselect = " ,jr.name as 'jobrole' ";
            $jobsql = " JOIN {report_gen_jobrole} jr
                        -- Users conneced with
	                    JOIN {user_info_competence_data} icd 	ON  icd.userid = u.id
                                                                AND jr.id IN (icd.jobroles)
                        AND jr.name LIKE '%" . $userjobrole . "%' ";
        } else {
            $extraselect = '';
            $jobsql = '';
        } */

        if ($username) {
            $extrasql .= " AND u.username LIKE '%" . $username . "%' ";
        }

        $query = "  SELECT 	CONCAT(u.id,c.id) 					as 'unique',
                            CONCAT(u.firstname, ' ', u.lastname)as 'coursecoordinator',
                            c.fullname							as 'coursename',
                            ca.name								as 'category',
                            c.format							as 'courseformat',
                            cl.city								as 'levelone',
                            fo1.value							as 'fromto',
                            c.visible							as 'visibility',
                            ca.path
                            
                            
                    FROM		    {user}					u
                        -- Coordinator
                        JOIN 	    {role_assignments}		ra 	    ON ra.userid 	= u.id
                        JOIN	    {context}				ct	    ON ct.id 	 	= ra.contextid
                        JOIN  	    {role}					r 	    ON 	r.id 	 	= ra.roleid
                                                                    AND r.archetype = 'editingteacher'
                        -- Course
                        JOIN 	    {course}			    c		ON c.id 		= ct.instanceid
                        
                        -- Category
                        JOIN	    {course_categories}		ca	    ON ca.id	= c.category
                        -- Location
                        LEFT JOIN   {course_format_options} fo 	    ON fo.courseid = c.id
                                                                    AND fo.name = 'course_location'
                        LEFT JOIN	{course_locations}	    cl 	    ON cl.id = fo.value
                        -- Dates
                        LEFT JOIN 	{course_format_options} fo1 	ON fo1.courseid = c.id
                                                                    AND fo1.name = 'time'
                    WHERE 	u.deleted = 0
                    $extrasql
                    GROUP BY c.id
                    ORDER BY u.id";

        try {

            $params = array();

            if ($course) {
                $params['course'] = $course;
            }

            if ($category) {
                $params['category'] = $category;
            }

            $rdo = $DB->get_records_sql($query, $params);

            if ($rdo) {
                return $rdo;
            } else {
                // If RDO is NULL.
                return null;
            }
        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    } // end get_categories

    public static function download_participants_list($coursesdata, $from, $to) {
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
            $name = clean_filename('Participants_List_Summary' . $time . ".xls");
            // Creating a workbook.
            $export = new MoodleExcelWorkbook($name);

            /*
            $myxls = $export->add_worksheet(get_string('pluginname', 'local_friadmin'));
            foreach ($coursesdata as $coursevalue) {
                self::add_info_course_excel($coursevalue, $myxls, $row);
            } */

            // Raw.
            $myxls = $export->add_worksheet('secondtab');

                // Headers.
                self::add_participants_header_excel($myxls, $row, $coursesdata);
                // Content.
                self::add_participants_content_excel($coursesdata, $myxls, $row, $from, $to);


            $export->close();
            exit;
        } catch (Exception $ex) {
            throw $ex;
        }
    }//download_participants_list

    public static function download_participants_list_instructor($coursesdata) {
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
            $name = clean_filename('Participants_List_Instructor' . $time . ".xls");
            // Creating a workbook.
            $export = new MoodleExcelWorkbook($name);

            // Raw.
            $myxls = $export->add_worksheet('secondtab');

            // Headers.
            self::add_participants_header_excel_instructor($myxls, $row, $coursesdata);
            // Content.
            self::add_participants_content_excel_instructor($coursesdata, $myxls, $row);


            $export->close();
            exit;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public static function download_participants_list_coordinator($coursesdata) {
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
            $name = clean_filename('Participants_List_Summary' . $time . ".xls");
            // Creating a workbook.
            $export = new MoodleExcelWorkbook($name);

            // Raw.
            $myxls = $export->add_worksheet('secondtab');

            // Headers.
            self::add_participants_header_excel_coordinator($myxls, $row, $coursesdata);
            // Content.
            self::add_participants_content_excel_coordinator($coursesdata, $myxls, $row);


            $export->close();
            exit;
        } catch (Exception $ex) {
            throw $ex;
        }
    }//download_participants_list

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

    private static function add_info_course_excel($coursesdata, &$myxls, &$row) {
        /* Variables */
        $col = 0;

        try {

            // Click next tab to see the raw data.
            $myxls->write($row, $col, get_string('nexttab', 'local_friadmin'), array('size' => 20, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 15);
            $myxls->set_row($row, 20);
            $row++;
            // Course name.
            $myxls->write($row, $col, get_string('course', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Course long.
            $myxls->write($row, $col, $coursesdata->coursefull, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Course short.
            $myxls->write($row, $col, $coursesdata->courseshort, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Course format.
            $myxls->write($row, $col, $coursesdata->courseformat, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Producer.
            $myxls->write($row, $col, get_string('producer', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->producer, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Levelone.
            $myxls->write($row, $col, get_string('levelone', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->levelone, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Sector.
            $myxls->write($row, $col, get_string('sector', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->sector, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Category.
            $myxls->write($row, $col, get_string('category', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->category, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Coordinator.
            $myxls->write($row, $col, get_string('coordinator', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->coursecoordinator, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Expiration.
            $myxls->write($row, $col, get_string('expiration', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->expiration, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Spots.
            $myxls->write($row, $col, get_string('spots', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->spots, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Prices.
            $myxls->write($row, $col, get_string('prices', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->internalprice, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->externalprice, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Instructors.
            $myxls->write($row, $col, get_string('instructors', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->instructors, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Students.
            $myxls->write($row, $col, get_string('students', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->instructors, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Waitinglist.
            $myxls->write($row, $col, get_string('waitinglist', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->waiting, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Completed.
            $myxls->write($row, $col, get_string('completed', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->completed, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Visibility.
            $myxls->write($row, $col, get_string('visible', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->visibility, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            // Fromto.
            $myxls->write($row, $col, get_string('fromto', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;
            $myxls->write($row, $col, $coursesdata->fromto, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $myxls->merge_cells($row,  $col , $row, $col + 4);
            $myxls->set_row($row, 20);
            $row++;

            // SpaceBeforeNextCourse.
            $row++;
            $row++;

        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_info_course_excel

    /**
     * Description
     * Add the header of the table to the excel report
     *
     * @param           $myxls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor     (fbv)
     */
    private static function add_participants_header_excel(&$myxls,$row, $coursesdata) {
        GLOBAL $SESSION;
        /* Variables */
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
            $myxls->write($row, $col, $strcoursefull, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);

            // Course shortname.
            $col += 5;
            $myxls->write($row, $col, $strcourseshort, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 3);
            $myxls->set_row($row,20);

            // Course format.
            $col += 4;
            $myxls->write($row, $col, $strcourseformat, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 3);
            $myxls->set_row($row,20);

            // Category.
            $col += 4;
            $myxls->write($row, $col, $strcategory, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);

            // Producer.
            $col += 5;
            $myxls->write($row, $col, $strproducer, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);

            // Levelone.
            $col += 5;
            $myxls->write($row, $col, $strlevelone, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row,20);

            // Sector.
            $col += 3;
            $myxls->write($row, $col, $strsector, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);

            // Course coordinator.
            $col += 5;
            $myxls->write($row, $col, $strcoursecoordinator, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);
            $col += 5;

            // Course dates.
            $i = 1;
            while ($i <= $maxdates) {
                $myxls->write($row, $col, $strdates . $i, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col + 1);
                $myxls->set_row($row,20);
                $col += 2;
                $i ++;
            }

            // Number of days.
            $myxls->write($row, $col, $strnumberdays, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Expiration.
            $col += 2;
            $myxls->write($row, $col, $strexpiration, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Spots.
            $col += 2;
            $myxls->write($row, $col, $strspots, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Internalprice.
            $col += 2;
            $myxls->write($row, $col, $strinternalprice, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Externalprice.
            $col += 2;
            $myxls->write($row, $col, $strexternalprice, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Instructors.
            $col += 2;
            $myxls->write($row, $col, $strinstructors, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Students.
            $col += 2;
            $myxls->write($row, $col, $strstudents, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Waiting.
            $col += 2;
            $myxls->write($row, $col, $strwaiting, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Completed.
            $col += 2;
            $myxls->write($row, $col, $strcompleted, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Visibility.
            $col += 2;
            $myxls->write($row, $col, $strvisibility, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Fromto.
            $col += 2;
            $myxls->write($row, $col, $strfromto, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            $fromtodates = null;


        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_header_excel

    /**
     * @param $coursedata
     * @param $myxls
     * @param $row
     * @throws Exception
     */
    private static function add_participants_content_excel($coursedata,&$myxls,&$row, $from, $to) {

        GLOBAL $SESSION;
        // Variables.
        $col            = 0;
        $row            = 1;
        $last           = null;
        $workplaces     = null;
        $setRow         = null;
        $strUser        = null;
        $completion     = null;
        $maxdates       = null;
        $mysectors      = null;

        try {
            if ($coursedata) {
                foreach ($coursedata as $coursevalue) {

                    $fromtodates = explode(",", $coursevalue->fromto);

                    if ($coursevalue->sector) {
                        $mysectors .= self::get_sectors($coursevalue->sector);
                    } else {
                        $mysectors = '';
                    }

                    // Course fullname.
                    $myxls->write($row, $col, $coursevalue->coursefull, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);

                    // Course shortname.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->courseshort, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 3);
                    $myxls->set_row($row,20);

                    // Course format.
                    $col += 4;
                    $myxls->write($row, $col, $coursevalue->courseformat, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 3);
                    $myxls->set_row($row,20);

                    // Category.
                    $col += 4;
                    $myxls->write($row, $col, $coursevalue->category, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);

                    // Producer.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->producer, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);

                    // Levelone.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->levelone, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 2);
                    $myxls->set_row($row,20);

                    // Sector.
                    $col += 3;
                    //$myxls->write($row, $col, str_replace(',',"\n",$mysectors), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->write($row, $col, $mysectors, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);

                    // Course coordinator.
                    $col += 5;
                    $myxls->write($row, $col,$coursevalue->coursecoordinator, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);
                    $col += 5;

                    // Dates.
                    if ($fromtodates) {
                        $i = null;
                        $lowdateunix = null;
                        $highdateunix = null;
                        $lowdateformated = null;
                        $highdateformated = null;
                        $a = '';


                        foreach ($fromtodates as $date) {
                            // If the date is not empty.
                            if ($date != '') {

                                /*
                                // Sets the dates to first value.
                                if ($lowdateunix == null) {
                                    $lowdateunix = strtotime($date);
                                }

                                if ($highdateunix == null) {
                                    $highdateunix = strtotime($date);
                                }

                                // Checks if the value is lower or bigger.
                                if ($lowdateunix > strtotime($date)) {
                                    $lowdateunix = strtotime($date);
                                }

                                if ($highdateunix < strtotime($date)) {
                                    $highdateunix = strtotime($date);
                                }

                                // Checks if the lowdateunix and highdateunix is approved criteria.
                                if ($lowdateunix < $from) {
                                    $a .= ' The course has lower date than the search ';
                                }

                                if ($highdateunix > $to) {
                                    $a .= ' The course has bigger date than the search ';
                                } */

                                // $myxls->write($row, $col, 'courselowdate: ' . $lowdateunix . ' decided from: ' . $from . ' coursehighdate: ' . $highdateunix . ' decided to: ' . $to , array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                                $myxls->write($row, $col, $date, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                                $myxls->merge_cells($row, $col, $row, $col + 1);
                                $myxls->set_row($row, 20);
                                $col += 2;
                            } else {
                                while ($i < $SESSION->maxdates) {
                                    $myxls->write($row, $col, '', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                                    $myxls->merge_cells($row, $col, $row, $col + 1);
                                    $myxls->set_row($row, 20);
                                    $col += 2;
                                    $i++;
                                }
                            }
                            $a = null;
                        }
                    }

                    // Number of days.
                    $numberdays = count($fromtodates);
                    $myxls->write($row, $col, $numberdays, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row,20);

                    // Expiration.
                    $col += 2;
                    $myxls->write($row, $col, $expiration = date("d.m.Y", $coursevalue->expiration), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row,20);

                    // Spots.
                    $col += 2;
                    if ($coursevalue->spots != '') {
                        $myxls->write($row, $col, $coursevalue->spots, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    } else {
                        $myxls->write($row, $col, '0', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    }

                    // Internalprice.
                    $col += 2;
                    if ($coursevalue->internalprice != '') {
                        $myxls->write($row, $col, $coursevalue->internalprice, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    } else {
                        $myxls->write($row, $col, '0', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    }

                    // Externalprice.
                    $col += 2;
                    if ($coursevalue->externalprice != '') {
                        $myxls->write($row, $col, $coursevalue->externalprice, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    } else {
                        $myxls->write($row, $col, '0', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    }
                    // Instructors.
                    $col += 2;
                    if ($coursevalue->instructors != '') {
                        $myxls->write($row, $col, $coursevalue->instructors, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    } else {
                        $myxls->write($row, $col, '0', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                            }
                    // Students.
                    $col += 2;
                    if ($coursevalue->students != '') {
                        $myxls->write($row, $col, $coursevalue->students, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    } else {
                        $myxls->write($row, $col, '0', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    }
                    // Waiting.
                    $col += 2;
                    if ($coursevalue->waiting != '') {
                        $myxls->write($row, $col, $coursevalue->waiting, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    } else {
                        $myxls->write($row, $col, '0', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    }
                    // Completed.
                    $col += 2;
                    if ($coursevalue->completed != '') {
                        $myxls->write($row, $col, $coursevalue->completed, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    } else {
                        $myxls->write($row, $col, '0', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                        $myxls->merge_cells($row, $col, $row, $col + 1);
                        $myxls->set_row($row,20);
                    }
                    // Visibility.
                    $col += 2;
                    if ($coursevalue->visibility = 0) {
                        $myxls->write($row, $col, get_string('no', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    } else if ($coursevalue->visibility = 1) {
                        $myxls->write($row, $col, get_string('yes', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    }
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row,20);

                    // Fromto.
                    $col += 2;
                    $myxls->write($row, $col, $coursevalue->fromto, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row,20);

                    $row ++;
                    $col = 0;

                    $fromtodates = null;
                    $mysectors   = null;

                }//for_participants
            }//if_participantList
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_content_excel

    /**
     * Description
     * Add the header of the table to the excel report
     *
     * @param           $myxls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor     (fbv)
     */
    private static function add_participants_header_excel_instructor(&$myxls,$row, $coursesdata) {
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
            $myxls->write($row, $col, $strinstructorname, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);

            // Course fullname.
            $col += 5;
            $myxls->write($row, $col, $strcoursename, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);

            // Category.
            $col += 5;
            $myxls->write($row, $col, $strcategory, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);

            // Course format.
            $col += 5;
            $myxls->write($row, $col, $strcourseformat, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 3);
            $myxls->set_row($row,20);

            // Levelone.
            $col += 4;
            $myxls->write($row, $col, $strlevelone, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row,20);

            // Course coordinator.
            $col += 3;
            $myxls->write($row, $col, $strcoordinatorname, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);
            $col += 5;

            // Course dates.
            $i = 1;
            while ($i <= $maxdates) {
                $myxls->write($row, $col, $strdates . $i, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col + 1);
                $myxls->set_row($row,20);
                $col += 2;
                $i ++;
            }

            // Fromto.
            $myxls->write($row, $col, $strfromto, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Visibility.
            $col += 2;
            $myxls->write($row, $col, $strvisibility, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            $fromtodates = null;


        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_header_excel

    /**
     * @param $coursedata
     * @param $myxls
     * @param $row
     * @throws Exception
     */
    private static function add_participants_content_excel_instructor($coursedata,&$myxls,&$row) {

        GLOBAL $SESSION;
        // Variables.
        $col            = 0;
        $row            = 1;
        $last           = null;
        $workplaces     = null;
        $setRow         = null;
        $strUser        = null;
        $completion     = null;
        $maxdates       = null;
        $mysectors      = null;

        try {
            if ($coursedata) {
                foreach ($coursedata as $coursevalue) {

                    $fromtodates = explode(",", $coursevalue->fromto);

                    // Instructor name.
                    $myxls->write($row, $col, $coursevalue->instr, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);

                    // Course fullname.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->coursename, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);

                    // Category.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->category, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);

                    // Course format.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->courseformat, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 3);
                    $myxls->set_row($row,20);

                    // Levelone.
                    $col += 4;
                    $myxls->write($row, $col, $coursevalue->levelone, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 2);
                    $myxls->set_row($row,20);

                    // Course coordinator.
                    $col += 3;
                    $myxls->write($row, $col, $coursevalue->coursecoordinator, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);
                    $col += 5;

                    // Dates.
                    if ($fromtodates) {
                        $i = null;
                        foreach ($fromtodates as $date) {
                            if ($date != '') {
                                $myxls->write($row, $col, $date, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                                $myxls->merge_cells($row, $col, $row, $col + 1);
                                $myxls->set_row($row, 20);
                                $col += 2;
                            } else {
                                while ($i < $SESSION->maxdates) {
                                    $myxls->write($row, $col, '', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                                    $myxls->merge_cells($row, $col, $row, $col + 1);
                                    $myxls->set_row($row, 20);
                                    $col += 2;
                                    $i++;
                                }
                            }
                        }
                    }

                    // Fromto.
                    $myxls->write($row, $col, $coursevalue->fromto, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row,20);

                    // Visibility.
                    $col += 2;
                    if ($coursevalue->visibility = 0) {
                        $myxls->write($row, $col, get_string('no', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    } else if ($coursevalue->visibility = 1) {
                        $myxls->write($row, $col, get_string('yes', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    }
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row,20);

                    $row ++;
                    $col = 0;

                    $fromtodates = null;
                    $mysectors   = null;

                }//for_participants
            }//if_participantList
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_content_excel

    /**
     * Description
     * Add the header of the table to the excel report
     *
     * @param           $myxls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor     (fbv)
     */
    private static function add_participants_header_excel_coordinator(&$myxls,$row, $coursesdata) {
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

            // Coordinator name.
            $myxls->write($row, $col, $strcoordinatorname, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);

            // Course fullname.
            $col += 5;
            $myxls->write($row, $col, $strcoursename, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);

            // Category.
            $col += 5;
            $myxls->write($row, $col, $strcategory, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 4);
            $myxls->set_row($row,20);

            // Course format.
            $col += 5;
            $myxls->write($row, $col, $strcourseformat, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 3);
            $myxls->set_row($row,20);

            // Levelone.
            $col += 4;
            $myxls->write($row, $col, $strlevelone, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 2);
            $myxls->set_row($row,20);
            $col += 3;

            // Course dates.
            $i = 1;
            while ($i <= $maxdates) {
                $myxls->write($row, $col, $strdates . $i, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
                $myxls->merge_cells($row, $col, $row, $col + 1);
                $myxls->set_row($row,20);
                $col += 2;
                $i ++;
            }

            // Fromto.
            $myxls->write($row, $col, $strfromto, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            // Visibility.
            $col += 2;
            $myxls->write($row, $col, $strvisibility, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true,'v_align' => 'left'));
            $myxls->merge_cells($row, $col, $row, $col + 1);
            $myxls->set_row($row,20);

            $fromtodates = null;


        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_header_excel

    /**
     * @param $coursedata
     * @param $myxls
     * @param $row
     * @throws Exception
     */
    private static function add_participants_content_excel_coordinator($coursedata,&$myxls,&$row) {

        GLOBAL $SESSION;
        // Variables.
        $col            = 0;
        $row            = 1;
        $last           = null;
        $workplaces     = null;
        $setRow         = null;
        $strUser        = null;
        $completion     = null;
        $maxdates       = null;
        $mysectors      = null;

        try {
            if ($coursedata) {
                foreach ($coursedata as $coursevalue) {

                    $fromtodates = explode(",", $coursevalue->fromto);

                    // Coordinatorname name.
                    $myxls->write($row, $col, $coursevalue->coursecoordinator, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);

                    // Course fullname.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->coursename, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);

                    // Category.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->category, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 4);
                    $myxls->set_row($row,20);

                    // Course format.
                    $col += 5;
                    $myxls->write($row, $col, $coursevalue->courseformat, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 3);
                    $myxls->set_row($row,20);

                    // Levelone.
                    $col += 4;
                    $myxls->write($row, $col, $coursevalue->levelone, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 2);
                    $myxls->set_row($row,20);
                    $col += 3;

                    // Dates.
                    if ($fromtodates) {
                        $i = null;
                        foreach ($fromtodates as $date) {
                            if ($date != '') {
                                $myxls->write($row, $col, $date, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                                $myxls->merge_cells($row, $col, $row, $col + 1);
                                $myxls->set_row($row, 20);
                                $col += 2;
                            } else {
                                while ($i < $SESSION->maxdates) {
                                    $myxls->write($row, $col, '', array('size' => 12, 'name' => 'Arial', 'text_wrap' => true, 'v_align' => 'left'));
                                    $myxls->merge_cells($row, $col, $row, $col + 1);
                                    $myxls->set_row($row, 20);
                                    $col += 2;
                                    $i++;
                                }
                            }
                        }
                    }

                    // Fromto.
                    $myxls->write($row, $col, $coursevalue->fromto, array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row,20);

                    // Visibility.
                    $col += 2;
                    if ($coursevalue->visibility = 0) {
                        $myxls->write($row, $col, get_string('no', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    } else if ($coursevalue->visibility = 1) {
                        $myxls->write($row, $col, get_string('yes', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'text_wrap' => true,'v_align' => 'left'));
                    }
                    $myxls->merge_cells($row, $col, $row, $col + 1);
                    $myxls->set_row($row,20);


                    $row ++;
                    $col = 0;

                    $fromtodates = null;
                    $mysectors   = null;

                }//for_participants
            }//if_participantList
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_content_excel
}