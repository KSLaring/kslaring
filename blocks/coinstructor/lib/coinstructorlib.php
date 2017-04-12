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
     * Gets all the courses where the user is an editingteacher and returns the neccessary information in an object
     *
     * @creationDate   05/04/2017
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
						        ra.userid		as 'user'
                      FROM 	    {course} 			c
	                    JOIN 	{context} 	    	co 	ON co.instanceid = c.id
	                    JOIN	{role_assignments} 	ra 	ON ra.contextid = co.id
	                    JOIN	{role}				r  	ON r.id = ra.roleid
	                    JOIN	{enrol}				e	ON e.courseid = c.id
	                    JOIN	{user_enrolments}	ue	ON ue.userid = ra.userid
	                    JOIN	{course_categories}	ca	ON ca.id = c.category
                      WHERE archetype = 'teacher'
                      AND ra.userid = :userid
                      LIMIT 2";

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
                return $rdo;
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
     */
    public static function display_overview($courselst) {
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
        $out .= self::add_content($courselst);
        $out .= html_writer::end_tag('table');
        $out .= html_writer::end_div(); // ...overviewtable.

        // Add back url!
        $out .= html_writer::start_div('back_btn');
        $out .= "<div><a href=$url> $back </a>";
        $out .= html_writer::end_div(); // ...back_btn.

        return $out;
    }

    private static function add_headertable() {
        // Variables!
        $header         = '';
        $strcategory    = get_string('headercategory', 'block_coteacher');
        $strcourse      = get_string('headercourse', 'block_coteacher');

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

    private static function add_content($courselst) {
        // Variables!
        $body = ' ';
        $strcategory      = get_string('headercategory', 'block_coteacher');
        $strcourse        = get_string('headercourse', 'block_coteacher');

        foreach ($courselst as $course) {
            $body .= html_writer::start_tag('tr');

            // Category!
            $body .= html_writer::start_tag('td', array('class' => 'category', 'data-label' => $strcategory));
            $body .= $course->categoryname;
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
