<?php

defined('MOODLE_INTERNAL') || die();

class coinstructor
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
        $userquery = "SELECT 	 DISTINCT(c.id)	            as 'courseid', 
		                                  co.id 			as 'coursecontext', 
                                          c.fullname 		as 'coursename', 
                                          ca.name 		    as 'categoryname', 
                                          r.shortname 	    as 'role',
                                          ra.userid		    as 'user'
                      FROM 	mdl_course 				    c
                        JOIN 	mdl_context 			co 	ON co.instanceid = c.id
                        JOIN	mdl_role_assignments 	ra 	ON ra.contextid = co.id
                        JOIN	mdl_role				r  	ON r.id = ra.roleid
                        JOIN	mdl_enrol				e	ON e.courseid = c.id
                        JOIN	mdl_user_enrolments		ue	ON ue.userid = ra.userid
                        JOIN	mdl_course_categories	ca	ON ca.id = c.category
                      WHERE 	r.archetype = 'editingteacher'
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

                    $courses[$instance->courseid] = $infocourse;
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
        $userquery = "SELECT 	COUNT(DISTINCT(c.id))	    as 'count'
                      FROM 	mdl_course 				    c
                        JOIN 	mdl_context 			co 	ON co.instanceid = c.id
                        JOIN	mdl_role_assignments 	ra 	ON ra.contextid = co.id
                        JOIN	mdl_role				r  	ON r.id = ra.roleid
                        JOIN	mdl_enrol				e	ON e.courseid = c.id
                        JOIN	mdl_user_enrolments		ue	ON ue.userid = ra.userid
                        JOIN	mdl_course_categories	ca	ON ca.id = c.category
                      WHERE 	r.archetype = 'editingteacher'
                      AND 	    ra.userid = :userid";

        try {
            // Parameters!
            $params = array();
            $params['userid'] = $USER->id;
            $rdo = $DB->get_record_sql($userquery, $params);

            // Exec!
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
                    $url = new moodle_url('/grade/report/overview/index.php');
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
} //end coinstructor class
