<?php

/**
 * Tracker Block - Main Page
 *
 * @package         block
 * @subpackage      tracker
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    17/02/2014
 * @author          efaktor     (fbv)
 */



class block_tracker extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_tracker');
    }//init

    function has_config() {
        return false;
    }//has_config

    function applicable_formats() {
        return array('all' => true);
    }

    function instance_allow_multiple() {
        return true;
    }

    function get_content() {
        global $USER, $CFG;

        /* External References  */
        $this->page->requires->js(new moodle_url('/blocks/tracker/js/block_tracker.js'));
        require_once('locallib.php');
        require_once($CFG->dirroot . '/report/generator/trackerlib.php');

        /* Add title        */
        $this->title = get_string('pluginname', 'block_tracker');

        if ($this->content !== NULL) {
            return $this->content;
        }

        /* Add the content to the block */
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        /* Get all the information to show */
        $tracker_user   = tracker_get_info_user_tracker($USER->id);
        $tracker_info   = tracker_get_tracker_page_user_info($tracker_user);

        /* Outcomes */
        $url_img    = new moodle_url('/pix/t/expanded.png');
        $toggle_outcome = 1;
        $toggle_job = 1;
        if ($tracker_info) {
            /* Courses Connected    */
            if ($tracker_info->connected) {
                $connected = $tracker_info->connected;
                foreach ($connected as $name=>$outcomes) {
                    /* Title Outcome    */
                    $id_toggle = 'YUI_' . $toggle_outcome;
                    $this->content->text .= block_tracker_getTagTitleOutcome($name,$id_toggle,$url_img);

                    $this->content->text .= html_writer::start_tag('div',array('class' => 'tracker_list','id'=> $id_toggle . '_div'));
                    /* JOB ROLES        */
                    foreach ($outcomes as $jr_name=>$out) {
                        $id_toogle = 'YUI_' . $toggle_outcome . '_' . $toggle_job;

                        $this->content->text .= html_writer::start_tag('div',array('class' => 'job_list'));
                            $this->content->text .= html_writer::start_tag('div',array('class' => ' job_list header_job'));
                                /* Job Role Title (Header)  */
                                $this->content->text .= tracker_getTagTitleJobRole($jr_name);

                                /* Header Courses Table     */
                                $this->content->text .= tracker_getTagHeaderCoursesTable($id_toogle,$url_img);

                                /* Content Courses Table    */
                                $this->content->text .= html_writer::start_tag('div',array('id' => $id_toogle . '_div', 'class' => 'body_job'));
                                    $this->content->text .= html_writer::start_tag('table');
                                        /* Not Completed    */
                                        if (isset($out->not_completed)) {
                                            $this->content->text .= tracker_getContentNotCompletedCourses($out->not_completed);
                                        }//not_completed

                                        /* Not Enrolled     */
                                        if (isset($out->not_enrolled)) {
                                            $this->content->text .= tracker_getContentNotEnrolCourses($out->not_enrolled);
                                        }//not_enrolled

                                        /* Completed    */
                                        if (isset($out->completed)) {
                                            $expired    = tracker_get_expired_courses($out->completed,$out->expiration);
                                            $completed  = tracker_get_finished_courses($out->completed,$out->expiration);

                                            if ($expired) {
                                                $this->content->text .= tracker_getContentExpiredCourses($expired);
                                            }//if_expored

                                            if ($completed) {
                                                $this->content->text .= tracker_getContentCompletedCourses($completed);
                                            }//if_completed
                                        }//if_completed_expired
                                    $this->content->text .= html_writer::end_tag('table');
                                $this->content->text .= html_writer::end_tag('div');//div_body_job
                            $this->content->text .= html_writer::end_tag('div');
                        $this->content->text .= html_writer::end_tag('div');//div_job_list

                        $toggle_job += 1;
                    }//for_job_roles
                    $this->content->text .= html_writer::end_tag('div');//tracker_list

                    $this->content->text .= '<hr style="border: 0; border-top: 1px solid #d2bcbc;width: 99.5%;">';

                    $toggle_outcome += 1;
                }//for_outcomes
            }//if_courses_connected

            /* Title    */
            $toggle_outcome = 'YUI_' . '0';
            $title = get_string('individual_courses','local_tracker');
            $this->content->text .= block_tracker_getTagTitleOutcome($title,$toggle_outcome,$url_img);

            /* Courses Not Connected    */
            $this->content->text .= html_writer::start_tag('div',array('class' => 'tracker_list','id'=> $toggle_outcome . '_div'));
                $this->content->text .= html_writer::start_tag('div',array('class' => 'job_list'));
                    $this->content->text .= html_writer::start_tag('div',array('class' => ' job_list header_job'));
                        /* Header Courses Table     */
                        $toggle_outcome .= '_table';
                        $this->content->text .= tracker_getTagHeaderCoursesTable($toggle_outcome ,$url_img,true);

                        /* Content Courses Table    */
                        $this->content->text .= html_writer::start_tag('div',array('id' => $toggle_outcome . '_div', 'class' => 'body_job'));
                            $this->content->text .= html_writer::start_tag('table');
                                /* Not Completed    */
                                if ($tracker_info->not_connected_not_completed) {
                                    $this->content->text .= tracker_getContentNotCompletedCourses($tracker_info->not_connected_not_completed,true);
                                }//if_not_connected_not_completed

                                /* Completed        */
                                if ($tracker_info->not_connected_completed) {
                                    $this->content->text .= tracker_getContentCompletedCourses($tracker_info->not_connected_completed,true);
                                }//if_not_connected_completed

                            $this->content->text .= html_writer::end_tag('table');
                        $this->content->text .= html_writer::end_tag('div');//body_job

                    $this->content->text .= html_writer::end_tag('div');//job_list_header_job
                $this->content->text .= html_writer::end_tag('div');//job_list
            $this->content->text .= html_writer::end_tag('div');//tracker_list
        }//tracker_info


        return $this->content;
    }//get_content
}//class block_tracker