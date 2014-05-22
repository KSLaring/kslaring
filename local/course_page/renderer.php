<?php
/**
 * Course Home Page  - Renderer
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    15/05/2014
 * @author          eFaktor     (fbv)
 */
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/outputlib.php');
class local_course_page_renderer extends plugin_renderer_base {

    public function footer() {
        return $this->output->footer();
    }

    public function display_home_page($course) {
        /* Variables    */
        $output  = $this->output->header();

        $output .= html_writer::start_tag('div',array('class' => 'home_page'));
            /* Header   */
            $output .= $this->addHeader_HomePage($course->fullname);

            /* Add Block One */
            $output .= $this->addBlockOne_HomePage($course);
            /* Add Block Two */
            $output .= $this->addBlockTwo_HomePage($course);
        $output .= html_writer::end_tag('div');//home_page_block_one

        return $output;
    }//display_summary

    /**
     * @param           $course
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the first block of the Course Home Page
     * - Course Name, Short Description, Button Register, Home page graphics...
     */
    private function addBlockOne_HomePage($course) {
        /* Variables    */
        $block_one = '';

        $block_one .= html_writer::start_tag('div',array('class' => 'home_page_block_one'));
            /* Add Short Description  */
            $block_one .= $this->addSummary_HomePage($course);
            /* Add Home Description / Video */
            $block_one .= $this->addDescription_HomePage($course->homesummary,$course->homevideo);
        $block_one .= html_writer::end_tag('div');//home_page_block_one

        return $block_one;
    }//addBlockOne_HomePage

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
    protected function addHeader_HomePage($course_name) {
        /* Header   */
        $header = '';

        $header .= html_writer::start_tag('div',array('class' => 'header'));
        $header .=  '<h3>' . $course_name . '</h3>';
        $header .=  html_writer::end_tag('div');//header

        return $header;
    }//addHeader_HomePage

    /**
     * @param           $course
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the summary. Short Description/Button Register/Graphics
     */
    protected function addSummary_HomePage($course) {
        /* Variables   */
        global $USER;
        $disabled = '';

        $out = '';
        $out .=  '<p>' . $course->summary . '</p>';
        /* Graphics */
        if ($course->homegraphics) {
            $url_img = course_page::getUrlPageGraphicsVideo($course->homegraphics);
            $img = '<img src="'  . $url_img . '" class="graphic"></br>';
            $out .= $img;
        }//if_graphics

        /* The course Visible   */
        if (!$course->visible) {
            $disabled = 'disabled';
        }//if_visible

        /* Check if the user is enrolled    */
        $out .= html_writer::start_tag('div',array('class' => 'buttons'));
        if (!course_page::IsUserEnrol($course->id,$USER->id)) {
            $url_start = new moodle_url('/course/view.php',array('id' => $course->id,'start' => 1));
            $out .= '<a href="' . $url_start . '"><button ' . $disabled . '>' . get_string('home_register','local_course_page') . '</button></a>';
        }else {
            $url_start = new moodle_url('/course/view.php',array('id' => $course->id,'start' => 1));
            $out .= '<a href="' . $url_start . '"><button ' . $disabled .'>' . get_string('home_start','local_course_page') . '</button></a>';
        }//if_else
        $out .= html_writer::end_tag('div'); //buttons

        return $out;
    }//addSummary_HomePage

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
    protected function addDescription_HomePage($home_summary,$video) {
        /* Variables */
        $out = '';

        $out .=  '<h4>' . get_string('home_about','local_course_page') . '</h4>';
        $out .= '<hr class="line">';

        $out .=  '<p>' . $home_summary;
        /* Graphics */
        $url_video = course_page::getUrlPageGraphicsVideo($video);
        $out .= '<object data="' . $url_video. '" class="video">' .
                    '<param name="src" value="' . $url_video . '">' .
                    '<param name="controller" value="true">' .
                    '<param name="loop" value="false">' .
                    '<param name="autoplay" value="false">' .
                    '<param name="autostart" value="false">' .
                    '<param name="scale" value="aspect">' .
                '</object>';
        $out .= '</p>';
        return $out;
    }//addDescription_HomePage

    /**
     * @param           $course
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the second block of the Course Hom Page
     * - Home PAge Summary, Video
     * - Block Coordinator, Ratings...
     */
    private function addBlockTwo_homePage($course) {
        /* Variables    */
        $block_two = '';
        $manager = 0;

        $block_two .= html_writer::start_tag('div',array('class' => 'home_page_block_two'));
            /* Add Extra Into */
            $block_two .= $this->addExtraInfo_HomePage($course,$manager);
            /* Coordinator Block    */
            $block_two .= $this->addCoordinatorBlock($course->id,$manager);
            /* Ratings Block        */
            $block_two .= $this->addCourseRatings($course->id);
        $block_two .= html_writer::end_tag('div');//home_page_block_two

        return $block_two;
    }//addBlockTwo_homePage

    /**
     * @param           $course
     * @param           $manager
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Extra fields from the Course format to Course Home Page
     */
    protected function addExtraInfo_HomePage($course,&$manager) {
        /* Variables    */
        $out     = '';

        /* Get Extra Options    */
        $format_options = course_page::getFormatFields($course->id);

        $out .= html_writer::start_tag('div',array('class' => 'extra'));
        $out .= '<p>';
        $out .= '<label class="label_home">' . get_string('home_course_id','local_course_page') . ':</label>';
        $out .= $course->idnumber;
        $out .= '</p>';
        $out .= '<p>';
        $out .= '<label class="label_home">' . get_string('home_published','local_course_page') . ':</label>';
        $out .= userdate($course->startdate,'%d.%m.%Y', 99, false);
        $out .= '</p>';

        foreach ($format_options as $option) {
            $out .= $this->addExtraOption($option,$manager);
        }//format_options
        $out .=  html_writer::end_tag('div');//extra

        return $out;
    }//addExtraInfo_HomePage

    /**
     * @param           $option
     * @param           $manager
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Course Format Information
     */
    protected function addExtraOption($option,&$manager) {
        /* Variables    */
        $out      = '';

        $out .= '<p>';
        switch ($option->name) {
            case 'prerequisities':
                $out .= '<label class="label_home">' . get_string('home_prerequisities','local_course_page') . ':</label>';
                $out .= $option->value;
                break;
            case 'producedby':
                $out .= '<label class="label_home">' . get_string('home_producedby','local_course_page') . ':</label>';
                $out .= $option->value;
                break;
            case 'location':
                $out .= '<label class="label_home">' . get_string('home_location','local_course_page') . ':</label>';
                $out .= $option->value;
                break;
            case 'length':
                $out .= '<label class="label_home">' . get_string('home_length','local_course_page') . ':</label>';
                $out .=  $option->value;
                break;
            case 'effort':
                $out .= '<label class="label_home">' . get_string('home_effort','local_course_page') . ':</label>';
                $out .= $option->value;
                break;
            case 'manager':
                $manager = $option->value;
                break;
            case 'author':
                $out .= '<label class="label_home">' . get_string('home_author','local_course_page') . ':</label>';
                $out .= $option->value;
                break;
            case 'licence':
                $out .= '<label class="label_home">' . get_string('home_licence','local_course_page') . ':</label>';
                $out .= $option->value;
                break;
            default:
                break;
        }//switch
        $out .= '</p>';

        return $out;
    }//addExtraOption

    /**
     * @param           $course_id
     * @param           $manager
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Coordinator Block
     */
    protected function addCoordinatorBlock($course_id,$manager) {
        /* Variables    */
        global $OUTPUT;
        $out = '';

        $out .= html_writer::start_tag('div',array('class' => 'manager'));
            $out .= '<p>';
                $out .= '<label class="label_manager">' . get_string('home_manager','local_course_page') . '</label>';
                /* Main Manager */
                if ($manager) {
                    $user = get_complete_user_data('id',$manager);
                    $url_user = new moodle_url('/user/profile.php',array('id' => $user->id));

                    $out .= $OUTPUT->user_picture($user, array('size'=>150));
                    $out .= '<label class="label_home">' . get_string('home_coordinater','local_course_page') . '</label>';
                    $out .= '<a href="' . $url_user . '">' . fullname($user) . '</a>';
                }//if_manager
            $out .= '</p>';

            /* Teachers */
            $out .= '<p>';
                $out .= '<label class="label_home">' . get_string('home_teachers','local_course_page') . '</label>';

                $lst_teachers = course_page::getCoursesTeachers($course_id,$manager);
                if ($lst_teachers) {
                    $url_user = new moodle_url('/user/profile.php');
                    foreach ($lst_teachers as $id => $teacher) {
                        $url_user->param('id',$id);
                        $out .= '<a href="' . $url_user . '">' . $teacher . '</a></br>';
                    }//foreach_teacher
                }//if_teachers
            $out .= '</p>';
        $out .= html_writer::end_tag('div');//manager

        return $out;
    }//addCoordinatorBlock

    /**
     * @param           $course_id
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Course Ratings Block
     */
    protected function addCourseRatings($course_id) {
        /* Varaibles    */
        global $OUTPUT;
        $out = '';
        $is_rating   = null;
        $last_rates = null;

        /* Get Last Rates  */
        $is_rating = course_page::IsCourseRating($course_id);
        if ($is_rating) {
            $last_rates = course_page::getLastRatings($course_id);
        }//if_rate_avg

        $out .= html_writer::start_tag('div',array('class' => 'ratings'));
            $out .= '<p>';
                $out .= '<label class="title_ratings">' . get_string('home_ratings','local_course_page') . '</label>';
                $out .= $OUTPUT->pix_icon('star', get_string('giverating', 'block_rate_course'),'block_rate_course', array('class'=>'icon'));
                $url = new moodle_url('/blocks/rate_course/rate.php', array('courseid'=>$course_id));
                $out .= $OUTPUT->action_link($url, get_string('giverating', 'block_rate_course'));
            $out .= '</p>';

            if ($is_rating) {
                $url_avg = new moodle_url('/blocks/rate_course/pix/rating_graphic.php',array('courseid' => $course_id));
                $out .= '<p>';
                    $out .= '<label class="label_ratings">' . get_string('rate_avg','local_course_page') . '</label>';
                    $out .= '<img src="'. $url_avg . '"/>';
                $out .= '</p>';
                $url_user = new moodle_url('/blocks/rate_course/pix/rating_user_graphic.php');
                $out .= '<p>';
                    $out .= '<label class="label_ratings">' . get_string('rate_users','local_course_page') . '</label>';
                    foreach ($last_rates as $user=>$rate) {
                        $url_user->param('rate',$rate);
                        $out .= $user  . '</br>';
                        $out .= '<img src="'. $url_user .'"/></br>';
                    }//for_each_rate
                $out .= '</p>';
            }//if_$rate_avg
        $out .= html_writer::end_tag('div');//ratings

        return $out;
    }//addCourseRatings
}//home_page_renderer