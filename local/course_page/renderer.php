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
    }//footer

    public function display_home_page($course,&$format_options) {
        /* Variables    */
        $output  = $this->output->header();

        $context = CONTEXT_COURSE::instance($course->id);

        $course->summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $context->id, 'course', 'summary',null);
        $home_summary = $format_options['homesummary']->value;
        $home_summary = file_rewrite_pluginfile_urls($home_summary, 'pluginfile.php', $context->id, 'course', 'homesummary',null);
        $format_options['homesummary']->value = $home_summary;

        $output .= html_writer::start_tag('div',array('class' => 'home_page'));
            /* Header   */
            $output .= $this->addHeader_HomePage($course->fullname);

            /* Add Block One */
            $output .= $this->addBlockOne_HomePage($course,$format_options);
            /* Add Block Two */
            $output .= $this->addBlockTwo_HomePage($course,$format_options);
        $output .= html_writer::end_tag('div');//home_page_block_one

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
    protected function addHeader_HomePage($course_name) {
        /* Header   */
        $header = '';

        $header .= html_writer::start_tag('div',array('class' => 'header'));
            $header .=  '<h1>' . $course_name . '</h1>';
        $header .=  html_writer::end_tag('div');//header

        return $header;
    }//addHeader_HomePage

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
    private function addBlockOne_HomePage($course,$format_options) {
        /* Variables    */
        $block_one = '';

        $block_one .= html_writer::start_tag('div',array('class' => 'home_page_block_one'));
            /* Add Short Description  */
            $block_one .= $this->addSummary_HomePage($course,$format_options['pagegraphics']);
            /* Add Home Description / Video */
            $block_one .= $this->addDescription_HomePage($format_options['homesummary'],$format_options['pagevideo']);
        $block_one .= html_writer::end_tag('div');//home_page_block_one

        return $block_one;
    }//addBlockOne_HomePage

    /**
     * @param           $course
     * @param           $home_graphics
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the summary. Short Description/Button Register/Graphics
     */
    protected function addSummary_HomePage($course,$home_graphics) {
        /* Variables   */
        global $USER;
        $disabled = '';

        $out = '';

        /* Graphics */
        if ($home_graphics->value) {
            $url_img = course_page::getUrlPageGraphicsVideo($home_graphics->value);
            $img = '<img src="'  . $url_img . '" class="graphic"></br>';
            $out .= $img;
        }//if_graphics

        $out .=  '<p>' . trim($course->summary) . '</p>';

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

        $out .=  '<h3>' . get_string('home_about','local_course_page') . '</h3>';

        $out .=  '<p>' . $home_summary->value . '</p>';
        /* Graphics */
        if ($video->value) {
            $url_video = course_page::getUrlPageGraphicsVideo($video->value);
            $media_renderer = $this->page->get_renderer('core', 'media');
            $embed_options = array(
                core_media::OPTION_TRUSTED => true,
                core_media::OPTION_BLOCK => true,
            );
            // Media (audio/video) file.
            $code = $media_renderer->embed_url($url_video, '', 0, 0, $embed_options);
            $out .= $code;
        }

        return $out;
    }//addDescription_HomePage

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
     */
    private function addBlockTwo_homePage($course,$format_options) {
        /* Variables    */
        $manager        = 0;
        $block_two      = '';
        $extra_info     = '';
        $coordinator    = '';
        $ratings        = '';


        /* Extra info   */
        $extra_info = $this->addExtraInfo_HomePage($course,$format_options,$manager);
        /* Coordinator */
        $coordinator = $this->addCoordinatorBlock($course->id,$manager);
        /* Ratings      */
        $ratings     = $this->addCourseRatings($course->id);

        $block_two .= html_writer::start_tag('div',array('class' => 'home_page_block_two'));
            /* Coordinator  Block   */
            $block_two .= $coordinator;
            /* Ratings Block        */
            $block_two .= $ratings;
            /* Add Extra Into */
            $block_two .= $extra_info;
        $block_two .= html_writer::end_tag('div');//home_page_block_two

        return $block_two;
    }//addBlockTwo_homePage

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
        global $OUTPUT,$DB;
        $out = '';

        $out .= html_writer::start_tag('div',array('class' => 'manager'));
            $out .= '<div class="label_manager">' . get_string('block_staff','local_course_page') . '</div>';
            /* Main Manager */
            if ($manager) {
                $user = $DB->get_record('user',array('id' => $manager));
                $user->description = file_rewrite_pluginfile_urls($user->description, 'pluginfile.php', CONTEXT_USER::instance($user->id)->id, 'user', 'profile', null);
                $url_user = new moodle_url('/user/profile.php',array('id' => $user->id));

                $out .= '<div class="label_coordinator">' . get_string('home_coordinater','local_course_page') . '</div>';
                $out .= '<div class="user_picture">' . $OUTPUT->user_picture($user, array('size'=>150)) . '</div>';
                $out .= '<div class="user"><a href="' . $url_user . '">' . fullname($user) . '</a>';
                $out .= '<div class="extra_coordinator">' . $user->description . '</div>'  . '</div>';
            }//if_manager

            /* Teachers */
            $out .= '<div class="label_teacher">' . get_string('home_teachers','local_course_page') . '</div>';

            $lst_teachers = course_page::getCoursesTeachers($course_id,$manager);
            if ($lst_teachers) {
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
            $out .= '<h5 class="title_ratings">' . get_string('home_ratings','local_course_page') . '</h5>';
            $out .= '<div class="label_ratings">';
                $out .= $OUTPUT->pix_icon('star', get_string('giverating', 'block_rate_course'),'block_rate_course', array('class'=>'icon'));
                $url = new moodle_url('/blocks/rate_course/rate.php', array('courseid'=>$course_id));
                $out .= $OUTPUT->action_link($url, get_string('giverating', 'block_rate_course'));
            $out .= '</div>';//label_ratings

            if ($is_rating) {
                $url_avg = new moodle_url('/blocks/rate_course/pix/rating_graphic.php',array('courseid' => $course_id));
                $out .= '<h5 class="title_ratings">' . get_string('rate_avg','local_course_page') . '</h5>';
                $out .= '<div class="label_ratings">' . '<img src="'. $url_avg . '" .  alt="average ratings"/>' . '</div>';
                $url_user = new moodle_url('/blocks/rate_course/pix/rating_user_graphic.php');
                $out .= '<h5 class="title_ratings">' . get_string('rate_users','local_course_page') . '</h5>';
                $out .= '<div class="label_ratings">';
                    foreach ($last_rates as $user=>$rate) {
                        $url_user->param('rate',$rate);
                        $out .= $user  . '</br>';
                        $out .= '<img src="'. $url_user .'" .  alt="user ratings"/></br>';
                    }//for_each_rate
                $out .= '</div>';//label_ratings
            }//if_$rate_avg
        $out .= html_writer::end_tag('div');//ratings

        return $out;
    }//addCourseRatings

    /**
     * @param           $course
     * @param           $format_options
     * @param           $manager
     * @return          string
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Extra fields from the Course format to Course Home Page
     */
    protected function addExtraInfo_HomePage($course,$format_options,&$manager) {
        /* Variables    */
        $out     = '';

        /* Get Extra Options    */
        $out .= html_writer::start_tag('div',array('class' => 'extra'));
            $out .= '<h5 class="label_home">' . get_string('home_course_id','local_course_page') . ':</h5>';
            $out .= '<div class="extra_home">' . $course->idnumber . '</div>';
            $out .= '<h5 class="label_home">' . get_string('home_published','local_course_page') . ':</h5>';
            $out .= '<div class="extra_home">' . userdate($course->startdate,'%d.%m.%Y', 99, false) . '</div>';

            foreach ($format_options as $option) {
                $out .= $this->addExtraOption($option,$manager);
            }//format_options

            /* Add Course Type Icon */
            $out .= '<h5 class="label_home">' . get_string('home_type','local_course_page') . ':</h5>';
            $out .= '<div class="extra_home">';
                switch ($course->format) {
                    case 'netcourse':
                        $url_img = $this->getURLIcon('nett_kurs');
                        $out .= html_writer::empty_tag('img', array('src'=>$url_img,'alt'=> '','class'=>'icon'));
                        $out .= get_string('net_course','local_course_page');

                        break;
                    case 'classroom':
                        $url_img = $this->getURLIcon('classroom');
                        $out .= html_writer::empty_tag('img', array('src'=>$url_img,'alt'=> '','class'=>'icon'));
                        $out .= get_string('class_course','local_course_page');

                        break;
                    case 'whitepaper':
                        $url_img = $this->getURLIcon('whitepaper');
                        $out .= html_writer::empty_tag('img', array('src'=>$url_img,'alt'=> '','class'=>'icon'));

                        break;
                    default:
                        break;
                }//format_ico
            $out .= '</div>';
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

        switch ($option->name) {
            case 'prerequisities':
                $out .= '<h5 class="label_home">' . get_string('home_prerequisities','local_course_page') . ':</h5>';
                $out .= '<div class="extra_home">' . $option->value . '</div>';
                break;
            case 'producedby':
                $out .= '<h5 class="label_home">' . get_string('home_producedby','local_course_page') . ':</h5>';
                $out .= '<div class="extra_home">' . $option->value . '</div>';
                break;
            case 'location':
                $out .= '<h5 class="label_home">' . get_string('home_location','local_course_page') . ':</h5>';
                $out .= '<div class="extra_home">' . $option->value . '</div>';
                break;
            case 'length':
                $out .= '<h5 class="label_home">' . get_string('home_length','local_course_page') . ':</h5>';
                $out .=  '<div class="extra_home">' . $option->value . '</div>';
                break;
            case 'effort':
                $out .= '<h5 class="label_home">' . get_string('home_effort','local_course_page') . ':</h5>';
                $out .= '<div class="extra_home">' . $option->value . '</div>';
                break;
            case 'manager':
                $manager = $option->value;
                break;
            case 'author':
                $out .= '<h5 class="label_home">' . get_string('home_author','local_course_page') . ':</h5>';
                $out .= '<div class="extra_home">' . $option->value . '</div>';
                break;
            case 'licence':
                $out .= '<h5 class="label_home">' . get_string('home_licence','local_course_page') . ':</h5>';
                $out .= '<div class="extra_home">' . $option->value . '</div>';
                break;
            default:
                break;
        }//switch

        return $out;
    }//addExtraOption

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
    protected function getURLIcon($icon) {
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
    }//getURLIcon
}//local_course_page_renderer