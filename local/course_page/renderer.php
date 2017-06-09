<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Course Home Page  - Renderer
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate    15/05/2014
 * @author          eFaktor     (fbv)
 */

require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/outputlib.php');
require_once($CFG->libdir . '/weblib.php');

class local_course_page_renderer extends plugin_renderer_base {
    public function footer() {
        return $this->output->footer();
    }//footer

    public function display_home_page($course,&$format_options) {
        /* Variables    */
        $output  = $this->output->header();

        $context = context_course::instance($course->id);

        $course->summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $context->id, 'course', 'summary',null);
        if (isset($format_options['homesummary'])) {
            $home_summary = $format_options['homesummary']->value;
            $home_summary = course_page::file_rewrite_pluginfile_urls_homepage($home_summary, 'pluginfile.php', $context->id, 'course', 'homesummary',null);
            $format_options['homesummary']->value = $home_summary;
        }else {
            $format_options['homesummary'] = null;
        }

        $output .= html_writer::start_tag('div',array('class' => 'home_page'));
            /* Header   */
            $output .= $this->add_header_homepage($course->fullname);

            /* Add Block One */
            $output .= $this->add_block_one_homepage($course,$format_options);
            /* Add Block Two */
            $output .= $this->add_block_two_homepage($course,$format_options);
        $output .= html_writer::end_tag('div');//home_page

        return $output;
    }//display_home_page

    /**
     * @param           $course_name
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header to Course Home Page
     */
    protected function add_header_homepage($course_name) {
        /* Header   */
        $header = '';

        $header .= html_writer::start_tag('div',array('class' => 'header'));
            $header .=  '<h1>' . $course_name . '</h1>';
        $header .=  html_writer::end_tag('div');//header

        return $header;
    }//add_header_homepage

    /* ********* */
    /* BLOCK ONE */
    /* ********* */

    /**
     * @param           $course
     * @param           $format_options
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the first block of the Course Home Page
     * - Course Name, Short Description, Button Register, Home page graphics...
     */
    private function add_block_one_homepage($course,$format_options) {
        /* Variables    */
        $block_one = '';
        $homeSummary    = null;
        $pageVideo      = null;
        $pageGraphics   = null;

        $block_one .= html_writer::start_tag('div',array('class' => 'home_page_block_one'));
            /* Add Short Description  */
            $pagegraphicstitle = !empty($format_options['pagegraphicstitle']) ? $format_options['pagegraphicstitle']->value : '';
            if (isset($format_options['pagegraphics'])) {
                $pageGraphics = $format_options['pagegraphics'];
            }
            $block_one .= $this->add_summary_homepage($course,$pageGraphics,$pagegraphicstitle);
            /* Add Home Description / Video */
            if (isset($format_options['homesummary'])) {
                $homeSummary = $format_options['homesummary'];
            }
            if (isset($format_options['pagevideo'])) {
                $pageVideo = $format_options['pagevideo'];
            }
            $block_one .= $this->add_description_homepage($homeSummary,$pageVideo);
        $block_one .= html_writer::end_tag('div');//home_page_block_one

        return $block_one;
    }//add_block_one_homepage

    /**
     * @param           $course
     * @param           $home_graphics
     * @param           $home_graphicstitle
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the summary. Short Description/Button Register/Graphics
     *
     * @updateDate      15/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Change the labe for the button §Go course' when there are none seats available
     *
     * @updateDate      16/06/2016
     * @author          eFaktor (fbv)
     *
     * Description
     * Remove start course button
     */
    protected function add_summary_homepage($course,$home_graphics,$home_graphicstitle) {
        /* Variables   */
        $btnString  = null;
        $out        = '';

        /* Graphics */
        if ($home_graphics) {
            if ($home_graphics->value) {
                $url_img = course_page::get_url_page_graphics_video($home_graphics->value);
                $img = '<img src="'  . $url_img . '" 
                             class="img-responsive"' . ' title="' . $home_graphicstitle . '" 
                             alt ="' . $home_graphicstitle . '"></br>';
                $out .= $img;
            }//if_graphics
        }//home_graphics

        $out .=  '<p>' . trim($course->summary) . '</p>';

        return $out;
    }//add_summary_homepage

    /**
     * @param           $home_summary
     * @param           $video
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Home PAge Description and Video
     */
    protected function add_description_homepage($home_summary,$video) {
        /* Variables */
        $out = '';

        $out .=  '<h3>' . get_string('home_about','local_course_page') . '</h3>';

        if ($home_summary) {
            $out .=  '<p>' . $home_summary->value . '</p>';
        }

        /* Graphics */
        if ($video) {
            if ($video->value) {
                $url_video = course_page::get_url_page_graphics_video($video->value);
                if ($url_video) {
                    $media_renderer = $this->page->get_renderer('core', 'media');
                    $embed_options = array(
                        core_media::OPTION_TRUSTED => true,
                        core_media::OPTION_BLOCK => true,
                    );
                    // Media (audio/video) file.
                    $code = $media_renderer->embed_url($url_video, '', 0, 0, $embed_options);
                    $out .= $code;
                }//if_url_video
            }//if_value
        }//if_video

        return $out;
    }//add_description_homepage

    /* ********* */
    /* BLOCK TWO */
    /* ********* */

    /**
     * @param           $course
     * @param           $format_options
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the second block of the Course Hom Page
     * - Home PAge Summary, Video
     * - Block Coordinator, Ratings...
     *
     *
     * @updateDate      21/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the course has to be add the course ratings block
     *
     * @updateDate      04/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add deadline course
     * Add price of the course
     *
     * @updateDate      16/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the course button
     */
    private function add_block_two_homepage($course,$format_options) {
        /* Variables    */
        global $DB;
        $block_two      = '';

        $block_two .= html_writer::start_tag('div',array('class' => 'home_page_block_two'));
            $block_two .= html_writer::start_tag('div',array('class' => 'go-left clearfix'));
                /* Start Course Button */
                $block_two .= $this->block_course_button($course);
                /* Block Prerequisites  */
                $block_two .= $this->add_extra_prerequisites_block($course,$format_options);
                /* Block Coordinator    */
                $block_two .= $this->add_coordinator_block($course->id);
                /* Block Participant    */
                $block_two .= $this->add_participant_list_block($course->id,$course->format,$format_options);
                /* Block Duration       */
                $block_two .= $this->add_extra_duration_block($course->format,$format_options,$course->id);
                /* Block Course Type    */
                $block_two .= $this->add_extra_type_course_block($course->format);
                /* Block Available seats    */
                $block_two .= $this->add_available_seats_block($format_options);
                /* Block Deadline       */
                $block_two .= $this->add_deadline_course_block($course->id);
            $block_two .= html_writer::end_tag('div');//go-left

            /* Block Ratings        */
            $ratings = $DB->get_record('course_format_options',array('courseid' => $course->id,'format' => $course->format,'name'=>'ratings'),'value');
            if ($ratings->value) {
                $block_two .= html_writer::start_tag('div',array('class' => 'go-right clearfix'));
                    $block_two .= $this->add_course_ratings($course->id);
                $block_two .= html_writer::end_tag('div');//go-right
            }//if_Ratings
        $block_two .= html_writer::end_tag('div');//home_page_block_two

        return $block_two;
    }//add_block_two_homepage

    /**
     * @param           $course
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    16/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the course button block
     */
    private static function block_course_button($course) {
        /* Variables */
        global $USER;
        $out        = '';
        $disabled   = '';

        try {
            /* The course Visible   */
            if (!$course->visible) {
                $disabled = 'disabled';
            }//if_visible

            /* Check if the user is enrolled    */
            $out .= html_writer::start_tag('div',array('class' => 'buttons'));
            if (!course_page::is_user_enrol($course->id,$USER->id)) {
                $url_start = new moodle_url('/course/view.php',array('id' => $course->id,'start' => 1));
                /* Check if there are seats available */
                if (course_page::get_available_seats($course->id)) {
                    $btnString = get_string('home_register','local_course_page');
                }else {
                    $btnString = get_string('on_wait','local_course_page');
                }//if_seats

                $out .= '<a href="' . $url_start . '"><button ' . $disabled . '>' . $btnString . '</button></a>';
            }else {
                $url_start = new moodle_url('/course/view.php',array('id' => $course->id,'start' => 1));
                $out .= '<a href="' . $url_start . '"><button ' . $disabled .'>' . get_string('home_start','local_course_page') . '</button></a>';
            }//if_else
            $out .= html_writer::end_tag('div'); //buttons

            $out .= "</br>";

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//block_course_button

    /**
     * @param           $course
     * @param           $format_options
     * @return          string
     *
     * @creationDate    05/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Prerequisites Block
     *
     * @updateDate      21/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the frikomport formats
     */
    protected function add_extra_prerequisites_block($course,$format_options) {
        /* Variables */
        $out = '';
        $str_format     = null;

        $out .= html_writer::start_tag('div',array('class' => 'extra chp-block'));
            if (isset($course->idnumber) && $course->idnumber) {
                $out .= '<h5 class="title_home chp-title">' . get_string('home_course_id','local_course_page') . '</h5>';
                $out .= '<div class="extra_home chp-content">' . $course->idnumber . '</div>';
            }//if_number

            $str_format = 'format_' . $course->format;

            /**
             * @updateDate      03/08/2015
             * @author          eFaktor     (fbv)
             *
             * Description
             * Change Start Date for Modified Date
             */
            if (($course->format != 'classroom') && ($course->format != 'classroom_frikomport')) {
                $out .= '<h5 class="title_home chp-title">' . get_string('home_published',$str_format) . '</h5>';
                $out .= '<div class="extra_home chp-content">' . userdate($course->timemodified,'%d.%m.%Y', 99, false) . '</div>';
            }

            switch ($course->format) {
                case 'netcourse':
                case 'classroom':
                case 'elearning_frikomport':
                case 'classroom_frikomport':
                    foreach ($format_options as $option) {
                        if ($option->name == 'prerequisities') {
                            if ($option->value) {
                                $out .= '<h5 class="title_home chp-title">' . get_string('home_prerequisities',$str_format) . '</h5>';
                                $out .= '<div class="extra_home chp-content">' . $option->value . '</div>';
                            }//if_value
                        }//if_prerequisites

                        if ($option->name == 'producedby') {
                            if ($option->value) {
                                $out .= '<h5 class="title_home chp-title">' . get_string('home_producedby',$str_format) . '</h5>';
                                $out .= '<div class="extra_home chp-content">' . $option->value . '</div>';
                            }//if_value
                        }//if_produced
                    }//for_format_options

                    break;

                case 'whitepaper':
                case 'single_frikomport':
                    foreach ($format_options as $option) {
                        if ($option->name == 'author') {
                            if ($option->value) {
                                $out .= '<h5 class="title_home chp-title">' . get_string('home_author',$str_format) . '</h5>';
                                $out .= '<div class="extra_home chp-content">' . $option->value . '</div>';
                            }//if_value
                        }//author

                        if ($option->name == 'licence') {
                            if ($option->value) {
                                $out .= '<h5 class="title_home chp-title">' . get_string('home_licence',$str_format) . '</h5>';
                                $out .= '<div class="extra_home chp-content">' . $option->value . '</div>';
                            }//if_value
                        }//licence
                    }//for_format_options

                    break;

                default:
                    break;
            }//course_format
        $out .=  html_writer::end_tag('div');//extra

        return $out;
    }//add_extra_prerequisites_block

    /**
     * @param           $course_format
     * @param           $format_options
     * @param           $courseId
     *
     * @return          string
     *
     * @creationDate    05/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Duration Block
     *
     * @updateDate      21/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the frikomport formats
     *
     * @updateDate      11/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Course Location / Course Sectors
     *
     * @updateDate      17/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Course Location -- Classroom format
     *
     * @updateDate      04/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the course price
     */
    protected function add_extra_duration_block($course_format,$format_options,$courseId) {
        /* Variables */
        $out = '';
        $strLocationName    = null;
        $strLocationTitle   = null;
        $infoLocation       = null;
        $lightBox           = null;
        $sectorsName        = null;
        $str_format         = 'format_' . $course_format;
        $outLocation        = null;
        $outSector          = null;
        $outTime            = null;
        $outLength          = null;
        $outEffort          = null;
        $outPrice           = null;
        $price              = null;

        /* Get course price */
        $price = course_page::price_course($courseId);
        if ($price) {
            /* Internal Price */
            if ($price->internal) {
                $outPrice .= html_writer::start_tag('div',array('class' => 'extra chp-block'));
                    $outPrice .= '<h5 class="title_home chp-title">' . get_string('home_int_price','local_course_page') . '</h5>';
                    $outPrice .= '<div class="extra_home chp-content">' . $price->internal. '</div>';
                $outPrice .=  html_writer::end_tag('div');//extra
            }//if_internal

            /* External Price */
            if ($price->external) {
                $outPrice .= html_writer::start_tag('div',array('class' => 'extra chp-block'));
                    $outPrice .= '<h5 class="title_home chp-title">' . get_string('home_ext_price','local_course_page') . '</h5>';
                    $outPrice .= '<div class="extra_home chp-content">' . $price->external. '</div>';
                $outPrice .=  html_writer::end_tag('div');//extra
            }//if_external
        }//if_price

        $out .= html_writer::start_tag('div',array('class' => 'extra chp-block'));
            switch ($course_format) {
                case 'netcourse':
                case 'elearning_frikomport':
                    foreach ($format_options as $option) {
                        if ($option->name == 'length') {
                            if ($option->value) {
                                $out .= '<h5 class="title_home chp-title">' . get_string('home_length',$str_format) . '</h5>';
                                $out .=  '<div class="extra_home chp-content">' . $option->value . '</div>';
                            }//if_value
                        }//if_length

                        if ($option->name == 'effort') {
                            if ($option->value) {
                                $out .= '<h5 class="title_home chp-title">' . get_string('home_effort',$str_format) . '</h5>';
                                $out .= '<div class="extra_home chp-content">' . $option->value . '</div>';
                            }//if_value
                        }//if_effort
                    }//for_format_options

                    break;

                case 'classroom':
                case 'classroom_frikomport':
                    foreach ($format_options as $option) {
                        if ($option->name == 'course_location') {
                            if ($option->value) {
                                $infoLocation       = course_page::get_location_detail($option->value);
                                $strLocationTitle   = get_string('home_title_location',$str_format);

                                /* Get Lightbox to add*/
                                $lightBox = self::add_lightbox_location($infoLocation,$courseId);

                                $this->page->requires->yui_module('moodle-local_course_page-location','M.local_course_page.location',
                                                                   array(array('header' => $strLocationTitle,'content' => $lightBox)));

                                $outLocation  = '<h5 class="title_home chp-title">' . $strLocationTitle . '</h5>';
                                $outLocation .= '<div class="extra_home chp-content">';
                                    $outLocation .= $infoLocation->name;

                                    $outLocation .= html_writer::start_tag('div', array('class' => 'mdl-left'));
                                        $outLocation .= '<a href="#" id="show_location" >' . get_string('view_detail','local_course_page') . '</a>';
                                    $outLocation .= html_writer::end_tag('div');//div_mdl_right
                                $outLocation .= '</div>';
                            }//if_value
                        }//if_course_location

                        if ($option->name == 'course_sector') {
                            if ($option->value) {
                                $sectorsName = course_page::get_sectors_name($option->value);
                                $outSector   = '<h5 class="title_home chp-title">' . get_string('home_title_sector',$str_format) . '</h5>';
                                $outSector  .= '<div class="extra_home chp-content">' . $sectorsName . '</div>';
                            }//if_value
                        }//if_course_sector

                        if ($option->name == 'time') {
                            if ($option->value) {
                                $time = str_replace(',','</br>',$option->value);
                                $time = str_replace('\n','</br>',$time);
                                $outTime  = '<h5 class="title_home chp-title">' . get_string('home_time_from_to',$str_format) . '</h5>';
                                $outTime .=  '<div class="extra_home chp-content">' . $time . '</div>';
                            }//if_value
                        }//if_time

                        if ($option->name == 'length') {
                            if ($option->value) {
                                $outLength  = '<h5 class="title_home chp-title">' . get_string('home_length',$str_format) . '</h5>';
                                $outLength .=  '<div class="extra_home chp-content">' . $option->value . '</div>';
                            }//if_value
                        }//if_length

                        if ($option->name == 'effort') {
                            if ($option->value) {
                                $outEffort  = '<h5 class="title_home chp-title">' . get_string('home_effort',$str_format) . '</h5>';
                                $outEffort .= '<div class="extra_home chp-content">' . $option->value . '</div>';
                            }//if_value
                        }//if_effort
                    }//for_format_options

                    $out .= $outLocation . $outSector  . $outTime . $outPrice . $outLength . $outEffort;
                    break;

                default:
                    break;
            }//course_format
        $out .=  html_writer::end_tag('div');//extra

        return $out;
    }//add_extra_duration_block

    /**
     * @param           $location
     * @param           $courseId
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    23/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add lightbox panel to the location
     */
    private static function add_lightbox_location($location,$courseId) {
        /* Variables */
        $lightBox       = null;
        $strDetail      = null;
        $strCourses     = null;
        $strDescription = null;
        $strComments    = null;
        $strAddress     = null;
        $strContact     = null;
        $strMap         = null;

        try {
            /* Sub titles */
            $strDetail          = get_string('location_detail','local_friadmin');
            $strCourses         = get_string('course');
            $strDescription     = get_string('location_desc','local_friadmin');
            $strComments        = get_string('location_comments','local_friadmin');
            $strAddress         = get_string('location_address','local_friadmin');
            $strContact         = get_string('location_contact_inf','local_friadmin');
            $strMap             = get_string('location_map','local_friadmin');

            $lightBox   = '<div class="location_panel">';
                /* Courses  */
                $lightBox  .= '<div class="location_sub_panel">';
                    $lightBox .= '<div class="location_review_title">' . '<h4>' . $strCourses  . '</h4>' . '</div>';
                    $lightBox .= '<div class="location_review_value">';
                        $lightBox .= '<h5>' . $location->courses[$courseId] . '</h5>';
                        $lightBox .= '<hr class="line_rating">';
                    $lightBox .= '</div>';
                $lightBox  .= '</div>';//location_sub_panel

                /* Address  */
                $lightBox  .= '<div class="location_sub_panel">';
                    $lightBox .= '<div class="location_review_title">' . '<h4>' . $strAddress    . '</h4>' . '</div>';
                    $lightBox .= '<div class="location_review_value">';
                            $lightBox .= '<h5>' . $location->address . '</h5>';
                            $lightBox .= '<hr class="line_rating">';
                    $lightBox .= '</div>';
                $lightBox  .= '</div>';//location_sub_panel

                /* Detail   */
                $lightBox   .= '<div class="location_sub_panel">';
                    $lightBox .= '<div class="location_review_title">' . '<h4>' . $strDetail . '</h4>' . '</div>';
                    $lightBox .= '<div class="location_review_value">';
                        $lightBox .=  '<h5>' . $location->detail . '</h5>';
                        $lightBox .= '<hr class="line_rating">';
                    $lightBox .= '</div>';
                $lightBox  .= '</div>';//location_sub_panel

                /* Description  */
                $lightBox   .= '<div class="location_sub_panel">';
                    $lightBox .= '<div class="location_review_title">' . '<h4>' . $strDescription . '</h4>' . '</div>';
                    $lightBox .= '<div class="location_review_value">';
                        $lightBox .=  '<h5>' . $location->description . '</h5>';
                        $lightBox .= '<hr class="line_rating">';
                    $lightBox .= '</div>';
                $lightBox  .= '</div>';//location_sub_panel

                /* Comments */
                $lightBox  .= '<div class="location_sub_panel">';
                    $lightBox .= '<div class="location_review_title">' . '<h4>' . $strComments  . '</h4>' . '</div>';
                    $lightBox .= '<div class="location_review_value">';
                        $lightBox .= '<h5>' . $location->comments . '</h5>';
                        $lightBox .= '<hr class="line_rating">';
                    $lightBox .= '</div>';
                $lightBox  .= '</div>';//location_sub_panel

                /* Contact  */
                $lightBox  .= '<div class="location_sub_panel">';
                    $lightBox .= '<div class="location_review_title">' . '<h4>' . $strContact   . '</h4>' . '</div>';
                    $lightBox .= '<div class="location_review_value">';
                        $lightBox .= '<h5>' . $location->contact . '</h5>';
                        $lightBox .= '<hr class="line_rating">';
                    $lightBox .= '</div>';
                $lightBox  .= '</div>';//location_sub_panel

                /* Url Map  */
                $lightBox  .= '<div class="location_sub_panel">';
                    $lightBox .= '<div class="location_review_title">' . '<h4>' . $strMap   . '</h4>' . '</div>';
                        $lightBox .= '<div class="location_review_value">';
                            $lightBox .= '<h5>' . '<a href="' . $location->map. '" target="_blank">' . get_string('url_map','local_course_page') . '</a></h5>';
                            $lightBox .= '<hr class="line_rating">';
                    $lightBox .= '</div>';
                $lightBox  .= '</div>';//location_sub_panel
            $lightBox  .= '</div>';//location_panel

            return $lightBox;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_lightbox_location

    /**
     * @param           $course_format
     * @return          string
     *
     * @creationDate    05/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Course Type Block
     *
     * @updateDate      21/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the frikomport formats
     */
    protected function add_extra_type_course_block($course_format) {
        /* Variables    */
        global $OUTPUT;
        $out     = '';

        /* Get Extra Options    */
        $out .= html_writer::start_tag('div',array('class' => 'extra chp-block'));
        /* Add Course Type Icon */
        $out .= '<h5 class="title_home chp-title">' . get_string('home_type','local_course_page') . '</h5>';
            $out .= '<div class="extra_home chp-content">';
            switch ($course_format) {
                case 'netcourse':
                    $out .= get_string('net_course','local_course_page');

                    break;

                case 'elearning_frikomport';
                    $out .= get_string('elearning','local_course_page');

                    break;

                case 'classroom':
                case 'classroom_frikomport':
                    $out .= get_string('class_course','local_course_page');

                    break;

                case 'whitepaper':
                    $url_img = $OUTPUT->pix_url('i/whitepaper');
                    $alt        = get_string('whitepaper','local_course_page');

                    $out .= html_writer::empty_tag('img', array('src' => $url_img, 'alt' => $alt, 'class' => 'icon'));

                    break;

                case 'single_frikomport':
                    $url_img = $OUTPUT->pix_url('i/whitepaper');
                    $alt        = get_string('single','local_course_page');

                    $out .= html_writer::empty_tag('img', array('src' => $url_img, 'alt' => $alt, 'class' => 'icon'));

                    break;

                default:
                    break;
            }//format_ico
            $out .= '</div>';//extra_home
        $out .=  html_writer::end_tag('div');//extra

        return $out;
    }//add_extra_type_course_block


    /**
     * @param           $format_options
     * @return          string
     *
     * @creationDate    2015-12-06
     * @author          eFaktor     (uh)
     *
     * Description
     * Add available seats block
     */
    protected function add_available_seats_block($format_options) {
        /* Variables    */
        $out = '';

        /* Get Extra Options    */
        if ($format_options['enrolledusers']->value != 'hide') {
            $out .= html_writer::start_tag('div', array('class' => 'extra chp-block'));
                $out .= '<h5 class="title_home chp-title">' . get_string('available_seats', 'local_course_page') . '</h5>';
                $out .= '<div class="extra_home chp-content">';
                    $out .= $format_options['enrolledusers']->value;
                $out .= '</div>';//extra_home
            $out .= html_writer::end_tag('div');//extra
        }

        return $out;
    }//add_available_seats_block

    /**
     * @param           $courseId
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    04/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add deadline course block
     */
    protected function add_deadline_course_block($courseId) {
        /* Variables */
        $out        = '';
        $deadLine   = null;

        try {
            /* Get deadline connected with */
            $deadLine = course_page::deadline_course($courseId);
            if ($deadLine) {
                $out .= html_writer::start_tag('div',array('class' => 'extra chp-block'));
                    $out .= '<h5 class="title_home chp-title">' . get_string('home_deadline','local_course_page') . '</h5>';
                    $out .= '<div class="extra_home chp-content">' . userdate($deadLine,'%d.%m.%Y', 99, false) . '</div>';
                $out .=  html_writer::end_tag('div');//extra
            }//if_deadline

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_deadline_course_block

    /**
     * @param           $course_id
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Coordinator Block
     *
     * @updateDate      22/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add coordinator's email
     *
     * @updateDate      16/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Replace mail of coordinator by 'Send a message'
     */
    protected function add_coordinator_block($course_id) {
        /* Variables    */
        global $OUTPUT,$DB;
        $urlMessage = null;
        $lnkMessage = null;
        $notIn      = 0;
        $out = '';

        $out .= html_writer::start_tag('div',array('class' => 'manager chp-block clearfix'));
            /* Main Manager */
            $user = course_page::get_courses_manager($course_id);
            if ($user) {
                $user->description = file_rewrite_pluginfile_urls($user->description, 'pluginfile.php',
                                                                  context_user::instance($user->id)->id, 'user', 'profile', null);
                $url_user = new moodle_url('/user/profile.php',array('id' => $user->id));

                $out .= '<h5 class="title_coordinator chp-title">' . get_string('home_coordinater','local_course_page') . '</h5>';
                $out .= '<div class="user_profile chp-content clearfix">';
                    $out .= '<div class="user_picture">';
                        $out .= $OUTPUT->user_picture($user, array('size'=>150));
                    $out .= '</div>';//div_user_picture

                    $out .= '<div class="user">';
                        $out .= '<a href="' . $url_user . '">' . fullname($user) . '</a>';
                        $urlMessage = new moodle_url('/message/index.php',array('id' => $user->id));
                        $lnkMessage = "<a href='". $urlMessage."'>" . get_string('messageselectadd') . "</a>";
                        $out .= '<div class="extra_home chp-content">';
                            $out .= $lnkMessage;
                        $out .= '</div>';
                        $out .= '<div class="extra_coordinator">';
                            $out .= $user->description;
                        $out .= '</div>';
                    $out .= '</div>';//div_user
                $out .= '</div>';//div_user_profile
            }//if_user

            /* Teachers */
            if ($user) {
                $notIn = $user->id;
            }
            $lst_teachers = course_page::get_courses_teachers($course_id,$notIn);
            if ($lst_teachers) {
                $out .= '<div class="title_coordinator chp-title">' . get_string('home_teachers','local_course_page') . '</div>';
                $url_user = new moodle_url('/user/profile.php');
                $out .= '<div class="extra_teacher">';
                    foreach ($lst_teachers as $id => $teacher) {
                        $url_user->param('id',$id);
                        $out .= '<a href="' . $url_user . '">' . $teacher . '</a></br>';
                    }//foreach_teacher
                $out .= '</div>';//extra_teacher
            }//if_teachers
        $out .= html_writer::end_tag('div');//manager

        return $out;
    }//add_coordinator_block

    /**
     * @param           $courseId
     * @param           $courseFormat
     * @param           $formatOptions
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add participant list link
     */
    protected function add_participant_list_block($courseId,$courseFormat,$formatOptions) {
        /* Variables */
        $out        = '';
        $urlLink    = null;
        $strTitle   = null;
        $context    = null;

        try {
            /* Get Context */
            $context = context_course::instance($courseId);

            if (($courseFormat == 'classroom')
                ||
                ($courseFormat == 'classroom_frikomport')) {
                /* Link participants list   */
                $urlLink    = new moodle_url('/local/participants/participants.php',array('id' => $courseId));
                $strTitle   = get_string('home_participant','local_course_page');

                if (isset($formatOptions['participant'])) {
                    if ($formatOptions['participant']->value) {
                        /* That means everybody */
                        $out .= html_writer::start_tag('div',array('class' => 'manager chp-block clearfix'));
                        $out .= '<h5 class="title_coordinator chp-title">' . get_string('home_participant_header','local_course_page') . '</h5>';
                        $out .= '<div class="extra_home chp-content">';
                        $out .= '<a href="' . $urlLink . '">' . $strTitle . "</a>";
                        $out .= '</div>';
                        $out .= html_writer::end_tag('div');//manager
                    }else if (has_capability('local/participants:manage',$context)) {
                        /* Only teachers    */
                        $out .= html_writer::start_tag('div',array('class' => 'manager chp-block clearfix'));
                        $out .= '<h5 class="title_coordinator chp-title">' . get_string('home_participant_header','local_course_page') . '</h5>';
                        $out .= '<div class="extra_home chp-content">';
                        $out .= '<a href="' . $urlLink . '">' . $strTitle . "</a>";
                        $out .= '</div>';
                        $out .= html_writer::end_tag('div');//manager
                    }//if_option
                }
            }//if_format

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participant_list_block

    /**
     * @param           $course_id
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Course Ratings Block
     *
     * @updateDate      14/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user can rate or not the course
     *
     * @updateDate      21/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if there are enough rates to publish the result of the course
     *
     */
    protected function add_course_ratings($course_id) {
        /* Variables    */
        global $OUTPUT,$USER;
        $out            = '';
        $is_rating      = null;
        $class          = null;
        $ratingsConfig  = null;

        /* Get Config Block for Ratings */
        $ratingsConfig = get_config('block_rate_course');

        /* Add  Ratings */
        $out .= html_writer::start_tag('div',array('class' => 'ratings chp-block'));
            /* Add Total Average of course rating   */
            /* Total Rates  */
            $total_rates = course_page::get_total_rates_course($course_id);

            if ($total_rates >= $ratingsConfig->block_rate_course_minimum) {
                $out .= course_page::add_ratings_total($course_id,$total_rates);

                $out .= '<h5 class="title_ratings chp-title">' . get_string('rate_users','local_course_page') . '</h5>';
                $out.= '<div class="content_rating_bar chp-content">';
                    /* Excellent Rate   */
                    $excellent_rate = course_page::get_count_type_rate_course($course_id,EXCELLENT_RATING);
                    $exc_bar        = course_page::get_progress_bar_code($excellent_rate,$total_rates,get_string('rate_exc','local_course_page'));
                    $out .= $exc_bar;
                    /* Good Rate        */
                    $good_rate      = course_page::get_count_type_rate_course($course_id,GOOD_RATING);
                    $good_bar       = course_page::get_progress_bar_code($good_rate,$total_rates,get_string('rate_good','local_course_page'));
                    $out .= $good_bar;
                    /* Average Rate */
                    $avg_rate       = course_page::get_count_type_rate_course($course_id,AVG_RATING);
                    $avg_bar        = course_page::get_progress_bar_code($avg_rate,$total_rates,get_string('rate_avg','local_course_page'));
                    $out .= $avg_bar;
                    /* Poor Rate    */
                    $poor_rate      = course_page::get_count_type_rate_course($course_id,POOR_RATING);
                    $poor_bar       = course_page::get_progress_bar_code($poor_rate,$total_rates,get_string('rate_poor','local_course_page'));
                    $out .= $poor_bar;
                    /* Bad Rate */
                    $bad_rate       = course_page::get_count_type_rate_course($course_id,BAD_RATING);
                    $bad_bar        = course_page::get_progress_bar_code($bad_rate,$total_rates,get_string('rate_bad','local_course_page'));
                    $out .= $bad_bar;
                $out .= '</div>';//content_rating_bar

                /* Add Reviews  */
                $light_box = '';
                $disabled = '';
                $out .= '<h5 class="title_ratings chp-title">' . get_string('title_reviews','local_course_page') . '</h5>';
                $last_rates = course_page::get_last_comments_rate_course($course_id);
                if ($last_rates) {
                    $url_user = new moodle_url('/blocks/rate_course/pix/rating_user_graphic.php');
                    $i = 1;
                    foreach ($last_rates as $rate) {
                        $url_user->param('rate',$rate->rating);

                        $str_comment = trim($rate->comment);
                        if (strlen($str_comment) > 100) {
                            $str_comment = '"' . substr($str_comment,0,97) . ' ...' .' "';
                        }

                        $out .= '<div class="ratings_review chp-content clearfix">';
                            $out .= '<div class="ratings_review_title">' . $rate->modified . '</div>';
                            $out .= '<div class="ratings_review_value">' . format_text($str_comment);
                                $out .= '<img src="'. $url_user .'"/>';
                            $out .= '</div>';//ratings_review_value
                        $out .= '</div>';//ratings_review

                        if ($i == 1) {
                            $out .= '<div class="ratings_break"></div>';
                        }

                        $light_box .= '<div class="ratings_panel">';
                            $light_box .= '<div class="ratings_review_title">' . $rate->modified . '</div>';
                            $light_box .= '<div class="ratings_review_value">';
                                $light_box .= format_text(trim($rate->comment));
                                $light_box .= '<img src="'. $url_user .'"/>';
                                if ($i == 1) {
                                    $light_box .= '<hr class="line_rating">';
                                }
                            $light_box .= '</div>';//ratings_review_value
                        $light_box .= '</div>';///ratings_panel

                        $i ++;
                    }//for_lastcomments
                }else {
                    $out .= '<div class="ratings_review">' . get_string('not_comments','local_course_page') . '</div>';
                    $disabled = ' disabled="disabled"';
                }//if_lst_comments

                /* Lightbox --> see the last five comments  */
                $header ='<h5 class="ratings_panel_title">' . get_string('title_reviews','local_course_page') . '</h5>';
                $this->page->requires->yui_module('moodle-local_course_page-ratings','M.local_course_page.ratings',
                                                  array(array('header' => $header,'content' => $light_box)));
                $out .= html_writer::start_tag('div', array('class' => 'mdl-right commentPanel'));
                $out .= '<button class="buttons" id="show" ' . $disabled . '>' . get_string('btn_more','local_course_page') . '</button>';
                $out.= html_writer::end_tag('div');//div_mdl_right
            }else {
                $out .= '<h5 class="title_ratings chp-title">' . get_string('rate_users','local_course_page') . '</h5>';
                $out.= '<div class="content_rating_bar chp-content">';
                    $out .= '<div class="extra_home chp-content">' . get_string('no_minimum_rates','local_course_page') . '</div>';
                $out .= '</div>';//content_rating_bar
            }//if_minimum_ratings


            /* Give a rating */
            $out .= '<h5 class="title_ratings chp-title">' . get_string('home_ratings','local_course_page') . '</h5>';
            $out .= '<div class="label_ratings chp-content">';
                $out .= $OUTPUT->pix_icon('star', get_string('giverating', 'block_rate_course'),'block_rate_course', array('class'=>'icon'));
                $url = new moodle_url('/blocks/rate_course/rate.php', array('courseid'=>$course_id));

                if (course_page::user_can_rate_course($USER->id,$course_id)) {
                    $class = null;
                }else {
                    $class = array('class' => 'disabled_ratings');
                }//CanRateCourse
                $out .= $OUTPUT->action_link($url, get_string('giverating', 'block_rate_course'),null,$class);
            $out .= '</div>';//label_ratings

        $out .= html_writer::end_tag('div');//ratings

        return $out;
    }//add_course_ratings

    /**
     * @param           $icon
     * @return          moodle_url|string
     *
     * @creationDate    19/06/2014
     * @author          efaktor     (fbv)
     *
     * Description
     * Get the url for the icon
     */
    protected function get_url_icon($icon) {
        /* Variables    */
        global $CFG;
        $url_img = '#';

        /* svg  */
        $file =  $CFG->dirroot . '/pix/i/' . $icon . '.svg';
        if (file_exists($file)) {
            return new moodle_url('/pix/i/' . $icon . '.svg');
        }//if_svg

        /* png  */
        $file = $CFG->dirroot . '/pix/i/' . $icon . '.png';
        if (file_exists($file)) {
            return new moodle_url('/pix/i/' . $icon . '.png');
        }//if_png

        /* gif  */
        $file = $CFG->dirroot . '/pix/i/' . $icon . '.gif';
        if (file_exists($file)) {
            return new moodle_url('/pix/i/' . $icon . '.gif');
        }//if_gif

        /* jpg  */
        $file = $CFG->dirroot . '/pix/i/' . $icon . '.jpg';
        if (file_exists($file)) {
            return new moodle_url('/pix/i/' . $icon . '.jpg');
        }//if_jpg

        /* jpeg */
        $file = $CFG->dirroot . '/pix/i/' . $icon . '.jpeg';
        if (file_exists($file)) {
            return new moodle_url('/pix/i/' . $icon . '.jpeg');
        }//if_jpeg

        /* ico  */
        $file = $CFG->dirroot . '/pix/i/' . $icon . '.ico';
        if (file_exists($file)) {
            return new moodle_url('/pix/i/' . $icon . '.ico');
        }//if_ico

        return $url_img;
    }//get_url_icon
}//local_course_page_renderer
