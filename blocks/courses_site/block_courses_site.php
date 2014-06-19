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

    function get_content() {
        global $OUTPUT, $PAGE;

        /* Block Settings       */
        $config = get_config('block_courses_site');
        /* Courses Site List    */
        $lst_courses_site   = courses_site::courses_site_getBlockList();
        $count_courses_site = count($lst_courses_site);

        $this->content = new stdClass;
        $this->content->footer = '';

        if ($PAGE->user_is_editing()) {

            $this->content->text  = html_writer::start_tag('div', array('class'=>'course-content'));
                if ($lst_courses_site) {
                    $this->content->text .= '<ul class="list">';
                        foreach ($lst_courses_site as $key=>$course_site) {
                            /* Edit Option  */
                            $url_edit = new moodle_url('/local/courses_site/edit_courses_site.php',array('id'=>$course_site->course));
                            $edit = html_writer::link($url_edit,
                                                      html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),'alt'=>get_string('edit'),'class'=>'iconsmall')),
                                                      array('title'=>get_string('edit')));
                            /* Delete Option    */
                            $url_del = new moodle_url('/local/courses_site/delete_courses_site.php',array('id'=>$course_site->course));
                            $del = html_writer::link($url_del,
                                                     html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'),'alt'=>get_string('delete'),'class'=>'iconsmall')),
                                                     array('title'=>get_string('delete')));

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
                    $this->content->footer = $OUTPUT->action_link($link, get_string('add_course', 'block_courses_site'),null);
                }//if_config_max
            $this->content->text .= html_writer::end_tag('div');
        }else {
            if ($lst_courses_site) {
                $total_records  = count($lst_courses_site);
                $index          = 0;

                $this->title = $config->title;
                $this->content->text = '<div id="navarea" class="coursebox clearfix">';
                    $lst_info = $this->block_courses_site_GetInfoDisplay($lst_courses_site);
                    if ($total_records <= 3) {
                        /* Block One    */
                        $this->block_courses_site_AddBlock($lst_info);
                    }else {
                        /* Block One    */
                        $block_one = array($lst_info[0],$lst_info[1],$lst_info[2]);
                        $this->block_courses_site_AddBlock($block_one);

                        /* Block Two    */
                        $block_two = array();
                        $block_two[0] = $lst_info[3];
                        if (array_key_exists(4,$lst_info)) {
                            $block_two[1] = $lst_info[4];
                        }//pos_4
                        if (array_key_exists(5,$lst_info)) {
                            $block_two[2] = $lst_info[5];
                        }//pos_5
                        $this->content->text .= '<hr class="line">';
                        $this->block_courses_site_AddBlock($block_two);
                    }//if_total_records
                $this->content->text .= '</div>';

            }//if
        }//if_else_editing
        return $this->content;
    }//get_content

    /**
     * @param           $lst_courses_site
     * @return          array
     *
     * @creationDate    30/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the information connected with the course that has to be displayed
     */
    function block_courses_site_GetInfoDisplay($lst_courses_site) {
        /* Varaibles    */
        $lst_info = array();
        foreach ($lst_courses_site as $key=>$course_site) {
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
    function block_courses_site_AddBlock($lst_info) {
        global $OUTPUT;


        /* Add Block */
            $this->content->text .= '<div class="block_courses_site_one">';
                if (array_key_exists(0,$lst_info)) {
                    $this->block_courses_site_AddColumnHeader($lst_info[0],'block_courses_site_info');
                    $this->block_courses_site_AddColumnExtra($lst_info[0],'block_courses_site_type');
                    $this->block_courses_site_AddColumnButton($lst_info[0],'course_type');
                }
            $this->content->text .= '</div>';//div_block_courses_site

            $this->content->text .= '<div class="block_courses_site_three">';
                if (array_key_exists(2,$lst_info)) {
                    $this->block_courses_site_AddColumnHeader($lst_info[2],'block_courses_site_info');
                    $this->block_courses_site_AddColumnExtra($lst_info[2],'block_courses_site_type');
                    $this->block_courses_site_AddColumnButton($lst_info[2],'course_type');
                }
            $this->content->text .= '</div>';//div_block_courses_site

            $this->content->text .= '<div class="block_courses_site_two">';
                if (array_key_exists(1,$lst_info)) {
                    $this->block_courses_site_AddColumnHeader($lst_info[1],'block_courses_site_info');
                    $this->block_courses_site_AddColumnExtra($lst_info[1],'block_courses_site_type');
                    $this->block_courses_site_AddColumnButton($lst_info[1],'course_type');
                }
            $this->content->text .= '</div>';//div_block_courses_site
    }//block_courses_site_AddBlock

    function block_courses_site_AddColumnHeader($course_site,$class) {
        /* Variables    */
        $description = ' ';
        /* Get URL For Course   */
        $url                  = new moodle_url('/local/course_page/home_page.php',array('id' => $course_site->course));
        $this->content->text .= '<div class="' . $class . '">';
            /*  Image / Title   */
            $this->content->text .= '<p>';
                $this->content->text .= '<a href="' . $url . '">' ;
                    $this->content->text .= '<img src="'  . $course_site->picture . '" class="graphic_site">';
                $this->content->text .= '</a>';
                $this->content->text .= '</br>';
            $this->content->text .= '<a href="' . $url . '">' ;
                    $this->content->text .= '<p class="title_site">'  . $course_site->title . '</p>';
                $this->content->text .= '</a>';
            $this->content->text .= '</p>';

            /* Description      */
            if (strlen($course_site->description) > 150) {
                $description = substr($course_site->description,0,150) . ' ...';
            }else {
                $description = $course_site->description;
            }
            $this->content->text .=  '<p class="label_header">' . $description . '</br></p>';
        $this->content->text .= '</div>';
    }//block_courses_site_AddColumn

    function block_courses_site_AddColumnExtra($course_site,$class) {
        /* Varaibles    */
        $pre = '';

        $this->content->text .= '<div class="' . $class . '">';
            $this->content->text .= '<div class="course_extra">';
                /* Published */
                $this->content->text .= '<div class="col_one">';
                    $this->content->text .= get_string('home_published','local_course_page') . ':';
                $this->content->text .= '</div>'; //col_one

                $this->content->text .= '<div class="col_three">';
                    $this->content->text .= $course_site->published;
                $this->content->text .= '</div>'; //col_two

                $this->content->text .= '<div class="col_two">';
                $this->content->text .= '</div>'; //col_two

                /* Prerequisites / Author   */
                if (isset($course_site->prerequisities)) {
                    $this->content->text .= '<div class="col_one">';
                        $this->content->text .= get_string('home_prerequisities','local_course_page') . ':';
                    $this->content->text .= '</div>'; //col_one

                    $this->content->text .= '<div class="col_three">';
                        if (strlen($course_site->prerequisities) > 50) {
                            $pre = substr($course_site->prerequisities,0,50) . '..';
                        }else {
                            $pre = $course_site->prerequisities;
                        }
                        $this->content->text .= '<p>' . $pre .'</p>';
                    $this->content->text .= '</div>'; //col_two

                    $this->content->text .= '<div class="col_two">';
                    $this->content->text .= '</div>'; //col_two
                }//if_prerequisites

                if (isset($course_site->author)) {
                    $this->content->text .= '<div class="col_one">';
                        $this->content->text .= get_string('home_author','local_course_page') . ':';
                    $this->content->text .= '</div>'; //col_one

                    $this->content->text .= '<div class="col_three">';
                        $this->content->text .= $course_site->author;
                    $this->content->text .= '</div>'; //col_two

                    $this->content->text .= '<div class="col_two">';
                    $this->content->text .= '</div>'; //col_two
                }//if_author
            $this->content->text .= '</div>';//course_extra
        $this->content->text .= '</div>';//class
    }//block_courses_site_AddColumnExtra

    function block_courses_site_AddColumnButton($course_site,$class) {
        global $OUTPUT,$CFG;
        /* Get URL For Course   */
        $url                  = new moodle_url('/local/course_page/home_page.php',array('id' => $course_site->course));

        $this->content->text .= '<div class="' . $class . '">';
            $this->content->text .= '<div class="col_one">';
                /* Button */
                $this->content->text .= '<a href="' . $url . '"><button class="button_site">' . get_string('btn_more','local_courses_site') . '</button></a>';
            $this->content->text .= '</div>'; //col_one

            $this->content->text .= '<div class="col_three">';
                switch ($course_site->type) {
                    case 'netcourse':
                        $url_img = new moodle_url('/pix/i/nett_kurs');
                        $this->content->text .= html_writer::empty_tag('img', array('src'=>$url_img,'class'=>'icon'));
                        break;
                    case 'classroom':
                        $url_img = new moodle_url('/pix/i/classroom');
                        $this->content->text .= html_writer::empty_tag('img', array('src'=>$url_img,'alt'=> '','class'=>'icon'));
                        break;
                    case 'whitepaper':
                        $url_img = new moodle_url('/pix/i/whitepaper');
                        $this->content->text .= html_writer::empty_tag('img', array('src'=>$url_img,'alt'=> '','class'=>'icon'));
                        break;
                    default:
                        break;
                }//format_ico
            $this->content->text .= '</div>'; //col_two

            $this->content->text .= '<div class="col_two">';
            $this->content->text .= '</div>'; //col_two
        $this->content->text .= '</div>';
    }//block_courses_site_AddColumnButton
}//block_courses_site