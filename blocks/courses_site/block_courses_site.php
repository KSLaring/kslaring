<?php
/**
 * Courses Site Block -  Main Page
 *
 * @package         block
 * @subpackage      courses_site
 * @copyright       2014 efaktor    {@link http://www.efaktor.no}
 *
 * @creationDate    22/05/2014
 * @author          efaktor     (fbv)
 */
require_once($CFG->dirroot . '/local/courses_site/courses_site.php');

class block_courses_site extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_courses_site');
    }//init

    function has_config() {
        return true;
    }//has_config

    public function get_aria_role() {
        return 'navigation';
    }

    function specialization() {
        global $PAGE;

        if ($PAGE->user_is_editing()) {
            $this->title = get_string('name', 'block_courses_site');
        }
    }//specialization

    // Load the needed JavaScript
    function get_required_javascript() {
        global $PAGE;

        $PAGE->requires->jquery();
        $PAGE->requires->js('/blocks/courses_site/javascript/equal-height-row.out.js');
    }

    function get_content() {
        global $OUTPUT, $PAGE;

        /* Block Settings       */
        $config = get_config('block_courses_site');
        /* Courses Site List    */
        $lst_courses_site = courses_site::courses_site_getBlockList();
        $count_courses_site = count($lst_courses_site);

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text    = '';

        if ($PAGE->user_is_editing()) {

            $this->content->text = html_writer::start_tag('div',array('class' => 'course-content'));
            if ($lst_courses_site) {
                $this->content->text .= '<ul class="list">';
                foreach ($lst_courses_site as $key => $course_site) {
                    /* Edit Option  */
                    $url_edit = new moodle_url('/local/courses_site/edit_courses_site.php', array('id' => $course_site->course));
                    $edit = html_writer::link($url_edit,
                                              html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'),
                                                                      'alt' => get_string('edit'), 'class' => 'iconsmall')),
                                              array('title' => get_string('edit')));
                    /* Delete Option    */
                    $url_del = new moodle_url('/local/courses_site/delete_courses_site.php',array('id' => $course_site->course));
                    $del = html_writer::link($url_del,
                                             html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'),
                                                                    'alt' => get_string('delete'), 'class' => 'iconsmall')),
                                             array('title' => get_string('delete')));

                    /* Add Course to the List with the edit and delete options */
                    $this->content->text .= '<li class="listentry">';
                    $this->content->text .= $course_site->title . ' ' . $edit . ' ' . $del;
                    $this->content->text .= '</li>';
                }//for_list
                $this->content->text .= '</ul></br>';
            }//if_lst_courses_site

            /* If it is possible add more courses   */
            if ($count_courses_site < $config->max) {
                $link = new moodle_url('/local/courses_site/add_courses_site.php');
                $this->content->footer = $OUTPUT->action_link($link,get_string('add_course', 'block_courses_site'), null);
            }//if_config_max
            $this->content->text .= html_writer::end_tag('div');
        } else {
            if ($lst_courses_site) {
                $total_records = count($lst_courses_site);
                $index = 0;

                $this->title = $config->title;
                $this->content->text = '<div id="navarea" class="coursebox clearfix">';
                $lst_info = $this->block_courses_site_GetInfoDisplay($lst_courses_site);
                foreach ($lst_info as $blockinfo) {
                    $this->block_courses_site_AddOneBlock($blockinfo);
                }
                $this->content->text .= '</div>';

            }//if
        }//if_else_editing

        $js = '<script type="text/javascript">
            $(function() {
                $(window).on("load resize orientationchange", function () {
                    equalheight("#navarea .bcs-course");
                });
            });
        </script>';

        $this->content->text .= $js;

        return $this->content;
    }//get_content

    /**
     * @param           $lst_courses_site
     *
     * @return          array
     *
     * @creationDate    30/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the information connected with the course that has to be displayed
     */
    function block_courses_site_GetInfoDisplay($lst_courses_site) {
        /* Variables    */
        $lst_info = array();
        foreach ($lst_courses_site as $key => $course_site) {
            $lst_info[] = $info_display = courses_site::courses_site_GetInfoBlock($course_site);
        }//for

        return $lst_info;
    }//block_courses_site_GetInfoDisplay

    /**
     * @param           $lst_info
     *
     * @creationDate    29/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the block with the information
     */
    function block_courses_site_AddOneBlock($lst_info) {
        global $OUTPUT;

        $block_class = 'bcs-course';

        /* Add Block */
        $this->content->text .= '<div class="' . $block_class . '">';
        $this->content->text .= '<div class="' . $block_class . '-inner">';

        $this->content->text .= '<div class="course-info clearfix">';
        $this->block_courses_site_AddColumnHeader($lst_info, 'bcs-info');
        $this->block_courses_site_AddColumnExtra($lst_info, 'bcs-type');
        $this->content->text .= '</div>';

        $this->block_courses_site_AddColumnButton($lst_info, 'course-type clearfix');

        $this->content->text .= '</div>';
        $this->content->text .= '</div>';
    }//block_courses_site_AddBlock

    /**
     * @param           $lst_info
     *
     * @creationDate    29/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the block with the information
     */
    function block_courses_site_AddBlock($lst_info) {
        global $OUTPUT;

        /* Add Block */
        $this->content->text .= '<div class="block_courses_site_one">';
        if (array_key_exists(0, $lst_info)) {
            $this->block_courses_site_AddColumnHeader($lst_info[0], 'block_courses_site_info');
            $this->block_courses_site_AddColumnExtra($lst_info[0], 'block_courses_site_type');
            $this->block_courses_site_AddColumnButton($lst_info[0], 'course_type');
        }
        $this->content->text .= '</div>';//div_block_courses_site

        $this->content->text .= '<div class="block_courses_site_three">';
        if (array_key_exists(2, $lst_info)) {
            $this->block_courses_site_AddColumnHeader($lst_info[2], 'block_courses_site_info');
            $this->block_courses_site_AddColumnExtra($lst_info[2], 'block_courses_site_type');
            $this->block_courses_site_AddColumnButton($lst_info[2], 'course_type');
        }
        $this->content->text .= '</div>';//div_block_courses_site

        $this->content->text .= '<div class="block_courses_site_two">';
        if (array_key_exists(1, $lst_info)) {
            $this->block_courses_site_AddColumnHeader($lst_info[1], 'block_courses_site_info');
            $this->block_courses_site_AddColumnExtra($lst_info[1], 'block_courses_site_type');
            $this->block_courses_site_AddColumnButton($lst_info[1], 'course_type');
        }
        $this->content->text .= '</div>';//div_block_courses_site
    }//block_courses_site_AddBlock

    function block_courses_site_AddColumnHeader($course_site, $class) {
        /* Variables    */
        $description = null;

        /* Get URL For Course   */
        $url = new moodle_url('/local/course_page/home_page.php',array('id' => $course_site->course));
        $this->content->text .= '<div class="' . $class . '">';
            /*  Image / Title   */
            $this->content->text .= '<a class="img-site" href="' . $url . '">';
                $this->content->text .= '<img src="' . $course_site->picture .'" class="graphic-site"  title="' . $course_site->picturetitle .'" alt="' . $course_site->picturetitle . '"/>';
            $this->content->text .= '</a>';
            $this->content->text .= '<a class="title-site" href="' . $url . '">' . $course_site->title . '</a>';

            /* Description      */
            if (strlen($course_site->description) > 100) {
                $description = shorten_text($course_site->description, 100);
            } else {
                $description = $course_site->description;
            }
            $this->content->text .= '<p class="label-header">' . $description . '</p>';
        $this->content->text .= '</div>';
    }//block_courses_site_AddColumn

    function block_courses_site_AddColumnExtra($course_site, $class) {
        /* Variables    */
        $pre = '';
        $str_format = 'format_' . $course_site->type;

        $this->content->text .= '<div class="' . $class . '">';
        $this->content->text .= '<div class="course-extra clearfix">';
        /* Published */
        $this->content->text .= '<div class="col-one">';
        $this->content->text .= get_string('home_published', 'local_course_page') . ':';
        $this->content->text .= '</div>'; //col-one

        $this->content->text .= '<div class="col-three">';
        $this->content->text .= $course_site->published;
        $this->content->text .= '</div>'; //col-three

        $this->content->text .= '<div class="col-two">';
        $this->content->text .= '</div>'; //col-two

        /* Prerequisites / Author   */
        if (isset($course_site->prerequisities)) {
            $this->content->text .= '<div class="col-one">';
            $this->content->text .= get_string('home_prerequisities', $str_format) . ':';
            $this->content->text .= '</div>'; //col-one

            $this->content->text .= '<div class="col-three">';
            if (strlen($course_site->prerequisities) > 40) {
                $pre = shorten_text($course_site->prerequisities, 50);
            } else {
                $pre = $course_site->prerequisities;
            }
            $this->content->text .= '<p>' . $pre . '</p>';
            $this->content->text .= '</div>'; //col-two

            $this->content->text .= '<div class="col-two">';
            $this->content->text .= '</div>'; //col-two
        }//if_prerequisites

        if (isset($course_site->author)) {
            $this->content->text .= '<div class="col-one">';
            $this->content->text .= get_string('home_author', $str_format) . ':';
            $this->content->text .= '</div>'; //col-one

            $this->content->text .= '<div class="col-three">';
            $this->content->text .= $course_site->author;
            $this->content->text .= '</div>'; //col-three

            $this->content->text .= '<div class="col-two">';
            $this->content->text .= '</div>'; //col-two
        }//if_author
        $this->content->text .= '</div>';//course-extra
        $this->content->text .= '</div>';//class
    }//block_courses_site_AddColumnExtra

    /**
     * @param           $course_site
     * @param           $class
     *
     * @creationDate    22/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the column button
     *
     * @updateDate      21/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the frikomport formats
     */
    function block_courses_site_AddColumnButton($course_site, $class) {
        global $OUTPUT, $CFG;
        /* Get URL For Course   */
        $url = new moodle_url('/local/course_page/home_page.php',array('id' => $course_site->course));

        $this->content->text .= '<div class="' . $class . '">';
            $this->content->text .= '<div class="left">';
            /* Button */
            $this->content->text .= '<a href="' . $url . '"><button class="button-site">' . get_string('btn_more', 'local_courses_site') . '</button></a>';
            $this->content->text .= '</div>'; //left

            $this->content->text .= '<div class="right">';
            switch ($course_site->type) {
                case 'netcourse':
                    $url_img = $OUTPUT->pix_url('i/nettkurs');
                    $this->content->text .= html_writer::empty_tag('img', array('src' => $url_img, 'alt' => 'nett kurs icon', 'class' => 'icon'));
                    break;
                case 'elearning_frikomport':
                    $url_img = $OUTPUT->pix_url('i/nettkurs');
                    $this->content->text .= html_writer::empty_tag('img', array('src' => $url_img, 'alt' => 'eLearning kurs icon', 'class' => 'icon'));
                    break;
                case 'classroom':
                case 'classroom_frikomport':
                    $url_img = $OUTPUT->pix_url('i/classroom');
                    $this->content->text .= html_writer::empty_tag('img', array('src' => $url_img, 'alt' => 'classroom icon', 'class' => 'icon'));
                    break;
                case 'whitepaper':
                    $url_img = $OUTPUT->pix_url('i/whitepaper');
                    $this->content->text .= html_writer::empty_tag('img', array('src' => $url_img, 'alt' => 'whitepaper icon', 'class' => 'icon'));
                    break;
                case 'single_frikomport':
                    $url_img = $OUTPUT->pix_url('i/whitepaper');
                    $this->content->text .= html_writer::empty_tag('img', array('src' => $url_img, 'alt' => 'single activity icon', 'class' => 'icon'));
                    break;
                default:
                    break;
            }//format_ico
        $this->content->text .= '</div>'; //right
        $this->content->text .= '</div>';
    }//block_courses_site_AddColumnButton
}
