<?php

defined('MOODLE_INTERNAL') || die();

class coteacher
{

    /**
     * @return null | object
     * @throws Exception
     *
     * Gets all the courses where the user is an editingteacher and returns the neccessary information in an object
     *
     * @creationeDate   05/04/2017
     * @author          eFaktor     (nas)
     *
     */
    public static function get_courses()
    {
        // Variables!
        global $DB, $USER;
        $courses    = null;
        $rdo        = null;

        // The SQL Query!
        $userquery = "SELECT DISTINCT(CONCAT(ue.id, c.id))	as 'id', 
                                          c.id              as 'courseid',
		                                  co.id 			as 'coursecontext', 
                                          c.fullname 		as 'coursename', 
                                          ca.name 		    as 'categoryname', 
                                          r.shortname 	    as 'role',
                                          ra.userid		    as 'user'
                      FROM 	    mdl_course 				c
                        JOIN 	mdl_context 			co 	ON co.instanceid = c.id
                        JOIN	mdl_role_assignments 	ra 	ON ra.contextid = co.id
                        JOIN	mdl_role				r  	ON r.id = ra.roleid
                        JOIN	mdl_enrol				e	ON e.courseid = c.id
                        JOIN	mdl_user_enrolments		ue	ON ue.userid = ra.userid
                        JOIN	mdl_course_categories	ca	ON ca.id = c.category
                      WHERE 	r.archetype = 'teacher'
                      AND 	    ra.userid = :userid
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
    public static function get_courses_count()
    {
        // Variables!
        global $DB, $USER;
        $rdo        = null;

        // The SQL Query!
        $userquery = "SELECT COUNT(DISTINCT(CONCAT(ue.id, c.id)))	as 'count'
                      FROM 	    mdl_course 				c
                        JOIN 	mdl_context 			co 	ON co.instanceid = c.id
                        JOIN	mdl_role_assignments 	ra 	ON ra.contextid = co.id
                        JOIN	mdl_role				r  	ON r.id = ra.roleid
                        JOIN	mdl_enrol				e	ON e.courseid = c.id
                        JOIN	mdl_user_enrolments		ue	ON ue.userid = ra.userid
                        JOIN	mdl_course_categories	ca	ON ca.id = c.category
                      WHERE 	r.archetype = 'teacher'
                      AND 	    ra.userid = :userid";

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
                $url = new moodle_url('/grade/report/overview/index.php');
                // Loops the object.
                foreach ($mycourses as $coursevalue) {
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
        $url = null;

        $out .= html_writer::start_div('overviewtable');
            $out .= html_writer::start_tag('table');
            $out .= self::add_headertable();
            $out .= self::add_content($courselst);
            $out .= html_writer::end_tag('table');
        $out .= html_writer::end_div(); //overviewtable

        // Add back url
        $out .= html_writer::start_div('back_btn');
        $url = new moodle_url('/my/');
        $back = get_String('back', 'local_ksl');
        $out .= "<div><a href=$url> $back </a>";
        $out .= html_writer::end_div();//back_btn

        return $out;
    }

    private static function add_headertable() {
        // Variables!
        $header         = '';
        $strcategory    = get_string('headercategory', 'block_coteacher');
        $strcourse      = get_string('headercourse', 'block_coteacher');

        // The table header!
        $header .= html_writer::start_tag('thead');
            $header .= html_writer::start_tag('tr', array('class' => 'header_overview'));

                // Category!
                $header .= html_writer::start_tag('th', array('class' => 'category'));
                $header .= $strcategory;
                $header .= html_writer::end_tag('th');

                // Course!
                $header .= html_writer::start_tag('th', array('class' => 'course'));
                $header .= $strcourse;
                $header .= html_writer::end_tag('th');

            $header .= html_writer::end_div(); //header_overview
        $header .= html_writer::end_div(); //thead

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
                $body .= $course->coursename;
                $body .= html_writer::end_tag('td');
            $body .= html_writer::end_tag('tr');
        }
        return $body;
    }
} // end coteacher class
