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
    public static function get_categories()
    {
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
    public static function get_courses()
    {
        // Variables!
        global $DB;
        $courses = array();  // Array declaration for the categories.
        $rdo = null;     // Used to query the database.

        $query = "SELECT  c.id,
		                  c.fullname
                  FROM    {course} c";

        try {
            $courses[0] = get_string('selectone', 'local_friadmin'); // Sets the first value in the array as "Select one...".
            $rdo = $DB->get_records_sql($query);

            // Gets all the categories from the kurskategori table that does have courses in them.
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
    public static function get_courses_js($category)
    {
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
                        ORDER BY      c.fulllname";
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
    public static function get_javascript_values($course, $category, $prevcourse)
    {

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

    public static function get_course_summary_data($category)
    {
        // Variables!
        global $DB;
        $rdo = null;     // Used to query the database.

        $query = "SELECT    c.id			    as 'courseid',			-- The course ID
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
                                    ue.timeend		    as 'expiration', 	    -- Deadline for enrolments (This only gets one of the expiration dates)
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
                WHERE ca.id = 1
                GROUP BY c.id";

        try {
            $params = array();
            $params['categoryid'] = $category;

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


    public static function download_participants_list($coursesdata)
    {
        /* Variables */
        global $CFG;
        $row = 0;
        $time = null;
        $name = null;
        $export = null;
        $my_xls = null;

        foreach ($coursesdata as $coursevalue) {

            try {
                require_once($CFG->dirroot . '/lib/excellib.class.php');

                $time = userdate(time(), '%d.%m.%Y', 99, false);
                $name = clean_filename('Participants_List_' . $coursevalue->category . '_' . $time . ".xls");
                // Creating a workbook
                $export = new MoodleExcelWorkbook($name);
                // Sending HTTP headers
                //$export->send($name);

                $my_xls = $export->add_worksheet(get_string('pluginname', 'local_friadmin'));

                // Course name
                self::add_info_course_excel($coursevalue, $my_xls, $row);

                $export->close();
                exit;
            } catch (Exception $ex) {
                throw $ex;
            }//try_catch
        }
    }//download_participants_list


    private static function add_info_course_excel($coursesdata, &$my_xls, &$row)
    {
        /* Variables */
        $col = 0;

        try {
            // Course name.
            $my_xls->write($row, $col, get_string('course', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Course long.
            $my_xls->write($row, $col, $coursesdata->coursefull, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Course short.
            $my_xls->write($row, $col, $coursesdata->courseshort, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Course format.
            $my_xls->write($row, $col, $coursesdata->courseformat, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Producer.
            $my_xls->write($row, $col, get_string('producer', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->producer, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Levelone
            $my_xls->write($row, $col, get_string('levelone', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->levelone, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Sector
            $my_xls->write($row, $col, get_string('sector', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->sector, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Category
            $my_xls->write($row, $col, get_string('category', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->category, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Coordinator
            $my_xls->write($row, $col, get_string('coordinator', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->coursecoordinator, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Expiration
            $my_xls->write($row, $col, get_string('expiration', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->expiration, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Spots
            $my_xls->write($row, $col, get_string('spots', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->spots, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Prices
            $my_xls->write($row, $col, get_string('prices', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->internalprice, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->externalprice, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Instructors
            $my_xls->write($row, $col, get_string('instructors', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->instructors, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Students
            $my_xls->write($row, $col, get_string('students', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->instructors, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Waitinglist
            $my_xls->write($row, $col, get_string('waitinglist', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->waiting, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Completed
            $my_xls->write($row, $col, get_string('completed', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->completed, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Visibility
            $my_xls->write($row, $col, get_string('visible', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->visibility, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            // Fromto
            $my_xls->write($row, $col, get_string('fromto', 'local_friadmin'), array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'bg_color' => '#efefef', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;
            $my_xls->write($row, $col, $coursesdata->fromto, array('size' => 12, 'name' => 'Arial', 'bold' => '1', 'text_wrap' => true, 'v_align' => 'left'));
            $my_xls->merge_cells($row, $col, $row, $col + 5);
            $my_xls->set_row($row, 20);
            $row++;
            $col = 0;

        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_info_course_excel
}