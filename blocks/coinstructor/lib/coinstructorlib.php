<?php
// This file is part of ksl
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

defined('MOODLE_INTERNAL') || die();

class coinstructor
{

    /**
     * @return null | object
     * @throws Exception
     *
     * Gets all the courses where the user is a teacher and returns the neccessary information in an object
     *
     * @creationDate   12/04/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_courses() {
        // Variables!
        global $DB, $USER;
        $courses    = null;
        $rdo        = null;

        // The SQL Query!
        $userquery = "SELECT DISTINCT(CONCAT(c.id, ra.userid)) as 'id',
						        c.id            as 'courseid',
						        co.id 			as 'coursecontext',
						        c.fullname 		as 'coursename',
						        ca.name 		as 'categoryname',
						        r.shortname 	as 'role',
						        ra.userid		as 'user',
                                ca.path         as 'path'
                      FROM 	    {course} 			c
	                    JOIN 	{context} 	    	co 	ON co.instanceid = c.id
	                    JOIN	{role_assignments} 	ra 	ON ra.contextid = co.id
	                    JOIN	{role}				r  	ON r.id = ra.roleid
	                    JOIN	{enrol}				e	ON e.courseid = c.id
	                    JOIN	{user_enrolments}	ue	ON ue.userid = ra.userid
	                    JOIN	{course_categories}	ca	ON ca.id = c.category
                        
                      WHERE archetype = 'teacher'
                      AND ra.userid = :userid
                      LIMIT 20";

        try {
            // Parameters!
            $params = array();
            $params['userid'] = $USER->id;
            $rdo = $DB->get_records_sql($userquery, $params);

            // Exec!
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $infocourse = new stdClass();
                    $infocourse->courseid = $instance->courseid;
                    $infocourse->coursecontext = $instance->coursecontext;
                    $infocourse->coursename = $instance->coursename;
                    $infocourse->categoryname = $instance->categoryname;
                    $infocourse->role = $instance->role;
                    $infocourse->userid = $instance->user;
                    $infocourse->path = $instance->path;
                    $infocourse->path_name = null;

                    $courses[$instance->id] = $infocourse;
                }
            }

        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch

        return $courses;
    }

    /**
     * @return mixed|null
     * @throws Exception
     *
     * Gets the amount of courses found and is used to display the "show all"-link when more than 20 courses are found
     *
     * @creationeDate   05/04/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_courses_count() {
        // Variables!
        global $DB, $USER;
        $rdo        = null;

        // The SQL Query!
        $userquery = "SELECT COUNT(DISTINCT(CONCAT(c.id, ra.userid))) as 'count'
                      FROM 	    {course} 			c
	                    JOIN 	{context} 			co 	ON co.instanceid = c.id
	                    JOIN	{role_assignments} 	ra 	ON ra.contextid = co.id
	                    JOIN	{role}				r  	ON r.id = ra.roleid
	                    JOIN	{enrol}				e	ON e.courseid = c.id
	                    JOIN	{user_enrolments}	ue	ON ue.userid = ra.userid
	                    JOIN	{course_categories}	ca	ON ca.id = c.category
                      WHERE archetype = 'teacher'
                      AND ra.userid = :userid";

        try {
            // Parameters!
            $params = array();
            $params['userid'] = $USER->id;
            $rdo = $DB->get_record_sql($userquery, $params);
            if ($rdo) {
                return $rdo->count;
            }

        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    }

    /**
     * @param $mycourses
     * @return string
     * @throws Exception
     *
     * Displays the users with a link to the overview page
     *
     * @creationeDate   05/04/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function display_courses($mycourses) {
        // Variables!
        $out = '';
        $url = null;

        if ($mycourses) {
            try {

                // Loops the object.
                foreach ($mycourses as $coursevalue) {
                    $url = new moodle_url('/grade/report/grader/index.php?id=' . $coursevalue->courseid);
                    $out .= "<div><a href=$url> $coursevalue->coursename </a> </div>";
                }

            } catch (Exception $ex) {
                throw $ex;
            } // end try_catch

        } else {
            $out .= '';
        }

        return $out;
    } //end display_courses

    /**
     * @param $courselst
     * @return string
     *
     * Displays the courses from the "show all" link
     *
     * @creationeDate   05/04/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function display_overview($courselst, $path) {
        // Variables!
        $out = '';
        $url = new moodle_url('/my/');
        $back = get_String('back', 'local_ksl');

        // Add back url!
        $out .= html_writer::start_div('back_btn');
        $out .= "<div><a href=$url> $back </a>";
        $out .= html_writer::end_div(); // ...back_btn.

        $out .= html_writer::start_div('overviewtable');
        $out .= html_writer::start_tag('table');
        $out .= self::add_headertable();
        $out .= self::add_content($courselst, $path);
        $out .= html_writer::end_tag('table');
        $out .= html_writer::end_div(); // ...overviewtable.

        // Add back url!
        $out .= html_writer::start_div('back_btn');
        $out .= "<div><a href=$url> $back </a>";
        $out .= html_writer::end_div(); // ...back_btn.

        return $out;
    }

    /**
     * @param $courses
     * @return array|null
     *
     * Used to return the path for the parents
     *
     * @creationeDate   18/04/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_path($courses) {

        $returnpath = array();

        foreach ($courses as $coursevalue) {
            $path = $coursevalue->path;

            $mypath = explode('/', $path);

            foreach ($mypath as $thispath) {
                if ($thispath) {
                    $coursevalue->path_name .=  self::get_mypath($thispath) . "/";
                }
            }

            $returnpath[$coursevalue->courseid] = $coursevalue->path_name;
        }

        if ($returnpath) {
            return $returnpath;
        } else {
            return null;
        }
    }

    /**
     * @param $mypath
     * @return array|null
     *
     * Called from get_path and gets the path to it
     *
     * @creationeDate   18/04/2017
     * @author          eFaktor     (nas)
     *
     */
    private static function get_mypath($mypath) {
        // Variables!
        global $DB;
        $rdo = null;

        // The SQL Query!
        $userquery = "SELECT 	ca.id,
		                        ca.name
                      FROM 	    {course_categories} ca
                      WHERE 	ca.id = :id";

        try {
            // Parameters!
            $params = array();
            $params['id'] = $mypath;
            $rdo = $DB->get_record_sql($userquery, $params);
            if ($rdo) {
                return $rdo->name;
            } else {
                return null;
            }

        } catch (Exception $ex) {
            Throw $ex;
        }  // end try_catch
    }

    /**
     * @return string
     */
    private static function add_headertable() {
        // Variables!
        $header         = '';
        $strcategory    = get_string('headercategory', 'block_coinstructor');
        $strcourse      = get_string('headercourse', 'block_coinstructor');

        // The table header!
        $header .= html_writer::start_tag('thead',array('class' => 'header_overview'));
        $header .= html_writer::start_tag('tr');

        // Category!
        $header .= html_writer::start_tag('th', array('class' => 'category'));
        $header .= $strcategory;
        $header .= html_writer::end_tag('th');

        // Course!
        $header .= html_writer::start_tag('th', array('class' => 'course'));
        $header .= $strcourse;
        $header .= html_writer::end_tag('th');

        $header .= html_writer::end_tag('tr'); // ...header_overview.
        $header .= html_writer::end_tag('thead'); // ...thead.

        return $header;
    }

    /**
     * @param $courselst
     * @param $path
     * @return string
     */
    private static function add_content($courselst, $path) {
        // Variables!
        $body = ' ';
        $strcategory      = get_string('headercategory', 'block_coinstructor');
        $strcourse        = get_string('headercourse', 'block_coinstructor');

        foreach ($courselst as $course) {
            $body .= html_writer::start_tag('tr');

            // Category!
            $body .= html_writer::start_tag('td', array('class' => 'category', 'data-label' => $strcategory));

            $body .= substr($path[$course->courseid], 0, -1);

            $body .= html_writer::end_tag('td');

            // Course!
            $body .= html_writer::start_tag('td', array('class' => 'course', 'data-label' => $strcourse));

            $url = new moodle_url('/grade/report/grader/index.php?id=' . $course->courseid);
            $body .= "<a href=$url> " . $course->coursename;
            $body .= html_writer::end_tag('td');
            $body .= html_writer::end_tag('tr');
        }
        return $body;
    }
} // end coinstructor class