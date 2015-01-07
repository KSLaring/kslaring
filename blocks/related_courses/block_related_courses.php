<?php 

class block_related_courses extends block_list {
    
    function init() {
        $this->title = get_string('relatedcourses', 'block_related_courses');
        $this->version = 2011111100;
    }

    function get_content() {
        global $CFG, $COURSE, $DB;
        
        //print_object($course);
        
        if ($this->content !== NULL) {
            return $this->content;
        }

        $dir = '';
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->items = array();
        $this->content->icons = array();
        
        // Following the explanation in http://docs.moodle.org/en/Metacourses
        try {
            /* External References  */
            $this->page->requires->js(new moodle_url('/blocks/related_courses/related.js'));
            $url_img    = new moodle_url('/pix/t/expanded.png');

            if (isset($_COOKIE['dir'])) {
                $dir = $_COOKIE['dir'];
            }else {
                $dir = 'ASC';
            }//if_dir

            $this->content->items = get_RelatedCourses($dir);

            array_unshift($this->content->items,'<button class="button_related_courses" id="' . $dir . '"><img src='. $url_img . '></button>');
            $this->content->footer = '';
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch

    }//get_content
}//block_related_courses


function get_RelatedCourses($dir) {
    global $DB,$COURSE,$CFG;

    try {
        /* Courses */
        $lst_courses = array();

        /* Search Criteria  */
        $params = array();
        $params['enrol_name']   = 'meta';
        $params['course_id']    = $COURSE->id;

        /* SQL Instruction  */
        $sql = " SELECT		c.id,
                                c.fullname
                     FROM		{course} c
                        JOIN	{enrol}	e 	ON 	e.customint1  = c.id
                                            AND	e.enrol 	  = :enrol_name
                                            AND	e.courseid 	  = :course_id
                     ORDER BY	c.fullname $dir ";

        /* Execute */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            foreach($rdo as $course) {
                $lst_courses[] = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'. format_string($course->fullname).'</a><br />';
            }//for_rdo
        }//if_rdo

        /* Now checks if this course has any children   */
        /* SQL Instruction  */
        $sql = " SELECT		  c.id,
                                  c.fullname
                     FROM		  {course} c
                        JOIN	  {enrol}	  e ON 	e.courseid 	  = c.id
                                                AND	e.enrol 	  = :enrol_name
                                                AND	e.customint1  = :course_id
                        ORDER BY  c.fullname ASC ";

        /* Execute     */
        $rdo = $DB->get_records_sql($sql,$params);
        if ($rdo) {
            foreach($rdo as $course) {
                $lst_courses[] = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'. format_string($course->fullname).'</a><br />';
            }//for_rdo
        }//if_rdo

        return $lst_courses;
    }catch(Exception $ex) {
        throw $ex;
    }
}//get_Related_Courses
?>
