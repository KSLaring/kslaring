<?php
/**
 * Related Courses (local) - Library
 *
 * Description
 *
 * @package         local
 * @subpackage      related_courses
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      24/04/2014
 * @author          eFaktor     (fbv)
 *
 */


/**
 * @param           $course_id
 * @return          array
 * @throws          Exception
 *
 * @creationDate    24/04/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all the related courses connected with
 */
function local_related_courses_getMyRelatedCourses($course_id) {
    global $DB;

    try {
        /* Related Courses */
        $lst_related = array();

        /* Search Criteria  */
        $params = array();
        $params['enrol']        = 'meta';
        $params['course_id']    = $course_id;

        /* SQL Instruction  */
        $sql = " SELECT		c.id,
                            c.fullname
                 FROM		{course} 	c
                    JOIN	{enrol}	    e 	ON 	e.customint1  = :course_id
                                            AND	e.enrol 	  = :enrol
                                            AND	e.courseid 	  = c.id
                 WHERE      c.visible = 1
                 ORDER BY	c.fullname ASC ";

        /* Execute  */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            foreach ($rdo as $course) {
                $lst_related[$course->id] = $course->fullname;
            }//for_rdo
        }//if_rdo

        return $lst_related;
    }catch(Exception $ex) {
        throw $ex;
    }//try_catch
}//local_related_courses_getMyRelatedCourses

/**
 * @param           $course_id
 * @param           $my_related
 * @return          array
 * @throws          Exception
 *
 * @creationDate    24/04/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Get all available courses that can be connected with
 */
function local_related_courses_getAllAvailableCourses($course_id,$my_related) {
   global $DB;

    try {
        /* All Courses  */
        $lst_courses = array();
        /* Except       */
        $except_courses = '1,' . $course_id;
        if ($my_related) {
            $except_courses .= ',' . implode(',',array_keys($my_related));
        }//if_my_related

        /* SQL Instruction  */
        $sql = " SELECT     c.id,
                            c.fullname
                 FROM       {course}    c
                 WHERE      c.visible = 1
                    AND     c.id NOT IN ($except_courses)
                 ORDER BY	c.fullname ASC ";

        /* Execute  */
        $rdo = $DB->get_records_sql($sql);
        if ($rdo) {
            foreach($rdo as $course) {
                $lst_courses[$course->id] = $course->fullname;
            }//for_rdo
        }//if_rdo

        return $lst_courses;
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_related_courses_getAllAvailableCourses

/**
 * @param           $course_id
 * @param           $lst_related
 * @throws          Exception
 *
 * @creationDate    24/04/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Establish a connection between the main course and the others
 */
function local_related_courses_AddCourse($course_id,$lst_related) {
    global $DB;

    try {
        $time = time();

        foreach ($lst_related as $rel) {
            $instance = new stdClass();
            $instance->enrol        = 'meta';
            $instance->status       = 0;
            $instance->courseid     = $rel;
            $instance->customint1   = $course_id;
            $instance->timcreated   = $time;
            $instance->timemodified = $time;

            /* Add */
            $DB->insert_record('enrol',$instance);
        }//for_rdo
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_related_courses_AddCourse

/**
 * @param           $course_id
 * @param           $lst_related
 * @throws          Exception
 *
 * @creationDate    24/04/2014
 * @author          eFaktor     (fbv)
 *
 * Description
 * Remove the connection between the main course and the others
 */
function local_related_courses_RemoveCourse($course_id,$lst_related) {
    global $DB;

    try {
        /* Search Criteria  */
        $params = array();
        $params['enrol']    = 'meta';
        $params['status']   = 0;

        $params['customint1'] = $course_id;

        foreach ($lst_related as $rel) {
            $params['courseid'] = $rel;

            /* Delete   */
            $DB->delete_records('enrol',$params);
        }
    }catch (Exception $ex) {
        throw $ex;
    }//try_catch
}//local_related_courses_RemoveCourse