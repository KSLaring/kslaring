<?php

class block_coinstructor extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_coinstructor');
    }//init

    function get_content() {
        // Variables!

        try {
            // Title Block!
            $this->title = get_string('blocktitle', 'block_coinstructor');

            if ($this->content !== NULL) {
                return $this->content;
            }

            // Add the content to the block
            $this->content = new stdClass;
            $this->content->text = '';
            $this->content->footer = '';

            // Add course info to the block
            require_once('lib/coinstructorlib.php');
            $courses = coinstructor::get_courses();
            $mycourses = coinstructor::display_courses($courses);
            $amount = coteacher::get_courses_count();

            if ($amount->count > 20) {
                $url = new moodle_url('/grade/report/overview/index.php');
                $this->content->text .= "<div><a href=$url>" . get_string('showall', 'block_coteacher') . " </a> </div>";
            }

            $this->content->text .= $mycourses;
            // $this->content->text .= coinstructorlib::GetContentBlock($COURSE->id);

            return $this->content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_content
}//block_coinstructor