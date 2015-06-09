<?php
/**
 * Course Home Page
 *
 * Description
 *
 * @package         local
 * @subpackage      course_page
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      28/04/2014
 * @author          eFaktor     (fbv)
 *
 */
require_once($CFG->libdir.'/formslib.php');

define('EXCELLENT_RATING',5);
define('GOOD_RATING',4);
define('AVG_RATING',3);
define('POOR_RATING',2);
define('BAD_RATING',1);

class course_page  {
    /* GET FUNCTIONS    */

    /**
     * @return          mixed
     *
     * @createDate      20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the course
     */
    public function get_course() {
        return $this->course;
    }//get_course

    /* PUBLIC FUNCTIONS */

    /**
     * @param           $itemid
     * @return          moodle_url|null
     * @throws          Exception
     *
     * @creationDate    12/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * get the correct url to display the Home Graphics
     */
    public static function getUrlPageGraphicsVideo($itemid) {
        try {
            /* Store File   */
            $fs = get_file_storage();

            /* File Instance        */
            $file   = $fs->get_file_by_id($itemid);

            /* Make URL */
            if ($file) {
                $url = new moodle_url('/local/course_page/draftfile.php/' .
                                      $file->get_contextid() .
                                      '/' .
                                      $file->get_component() .
                                      '/' .
                                      $file->get_filearea() .
                                      '/' .
                                      $file->get_itemid() .
                                      '/' .
                                      $file->get_filename());

                return $url;
            }else {
                return null;
            }//if_file

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getUrlPageGraphicsVideo

    /**
     * @static
     * @return          array
     * @throws          Exception
     *
     * @creationDate    14/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all the users are candidates to be manager
     */
    public static function getCourseManager() {
        global $DB;

        try {
            /* Context LEvels   */
            $context_levels =  CONTEXT_SYSTEM . ',' . CONTEXT_COURSE . ',' . CONTEXT_COURSECAT . ',' . CONTEXT_MODULE;

            /* Managers */
            $lst_manager = array();
            $lst_manager[0] = get_string('sel_course_manager','local_course_page');

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT u.id,
                                CONCAT(u.firstname, ' ' , u.lastname) as 'name'
                     FROM		{user}					u
                        JOIN	{role_assignments}		ra		ON		ra.userid 		= u.id
                        JOIN	{role}					r		ON		r.id 			= ra.roleid
                                                                AND		r.archetype 	IN ('teacher','editingteacher','coursecreator')
                        JOIN 	{context}				c		ON		c.id 			= ra.contextid
                                                                AND		c.contextlevel  IN ($context_levels)
                     WHERE		u.deleted = 0
                     ORDER BY 	u.firstname, u.lastname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach($rdo as $manager) {
                    $lst_manager[$manager->id] = $manager->name;
                }///for_rdo
            }//if_rdo

            return $lst_manager;
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//getCourseManager

    /**
     * @static
     * @param           $course_id
     * @param           $manager_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    19/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the teachers connected with the course.
     */
    public static function getCoursesTeachers($course_id,$manager_id) {
        global $DB;

        try {
            /* Teachers */
            $lst_teachers = array();

            /* Context  */
            $context = CONTEXT_COURSE::instance($course_id);
            /* Search Criteria  */
            $params = array();
            $params['context_id'] = $context->id;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT u.id,
                                CONCAT(u.firstname, ' ' , u.lastname) as 'name'
                     FROM		{user}					u
                        JOIN	{role_assignments}		ra		ON		ra.userid 		= u.id
                                                                AND     ra.contextid    = :context_id
                        JOIN	{role}					r		ON		r.id 			= ra.roleid
                                                                AND		r.archetype 	IN ('teacher','editingteacher')

                     WHERE		u.deleted = 0
                        AND     u.id NOT IN ($manager_id)
                     ORDER BY 	u.firstname, u.lastname ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $user) {
                    $lst_teachers[$user->id] = $user->name;
                }//for_rdo
            }//if_rod

            return $lst_teachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getCoursesTeachers

    /**
     * @static
     * @param           $course_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the last ratings.
     */
    public static function getLastRatings($course_id) {
        global $DB;

        try {
            /* Last Ratings */
            $last_rates = array();

            /* PARAMS   */
            $params = array();
            $params['course_id'] = $course_id;

            /* SQL Instruction  */
            $sql = " SELECT	  CONCAT(u.firstname, ', ',u.lastname) as 'user',
                              rc.rating
                     FROM	  {block_rate_course}       rc
                        JOIN  {user}					u 	ON u.id = rc.userid
                     WHERE	  rc.course = :course_id
                     ORDER	BY rc.id DESC
                     LIMIT	5 ";

            /* Execue   */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach($rdo as $rate) {
                    $last_rates[$rate->user] = $rate->rating;
                }//for_rdo
            }//if_rdo

            return $last_rates;
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//getLastRatings

    /**
     * @static
     * @param           $course_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the course has been rated.
     */
    public static function IsCourseRating($course_id) {
        global $DB;

        try {
           /* Execute   */
           $rdo = $DB->get_records('block_rate_course',array('course' => $course_id));
           if ($rdo) {
               return true;
           }else {
               return false;
           }//if_else_rdo
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsCourseRating

    /**
     * @static
     * @param           $user_id
     * @param           $course_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    03/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the course has been evaluated by user
     */
    public static function UserRateCourse($user_id,$course_id) {
        /* Variables    */
        global $DB;

        try {
            /* Execute   */
            $rdo = $DB->get_records('block_rate_course',array('course' => $course_id,'userid' => $user_id));
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UserRateCourse

    /**
     * @static
     * @param           $course_id
     * @param           $type_rate
     * @return          null
     * @throws          Exception
     *
     * @creationDate    04/07/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get how many records there are for each rating
     */
    public static function getCountTypeRateCourse($course_id,$type_rate) {
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course'] = $course_id;
            $params['rating'] = $type_rate;

            /* Execute  */
            $total = $DB->count_records('block_rate_course',$params);

            return $total;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getCountTypeRateCourse

    /**
     * @static
     * @param           $course_id
     * @param           $type_rate
     * @return          array
     * @throws          Exception
     *
     * @creationDate    09/07/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the last comments for specific rating
     */
    protected static function getComentsTypeRateCourse($course_id,$type_rate) {
        global $DB;

        try {
            $coments_rate = array();

            /* Search Criteria  */
            $params = array();
            $params['course'] = $course_id;
            $params['rating'] = $type_rate;

            /* SQL Instruction  */
            $sql = " SELECT	  rc.id,
                              rc.comment
                     FROM	  {block_rate_course}       rc
                     WHERE	  rc.course = :course_id
                        AND   rc.rating = :rating
                        AND   rc.comment IS NOT NULL
                     ORDER	BY rc.id DESC
                     LIMIT	2 ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $rate) {
                    $coments_rate[$rate->id] = $rate->comment;
                }
            }//if_rdo

            return $coments_rate;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getComentsTypeRateCourse

    /**
     * @static
     * @param           $course_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    08/07/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the last comments
     */
    public static function getLastCommentsRateCourse($course_id) {
        global $DB;

        try {
            /* Last Ratings */
            $last_comments = array();

            /* PARAMS   */
            $params = array();
            $params['course_id'] = $course_id;

            /* SQL Instruction  */
            $sql = " SELECT	  rc.id,
                              rc.comment,
                              rc.rating,
                              rc.modified
                     FROM	  {block_rate_course}       rc
                     WHERE	  rc.course = :course_id
                        AND   rc.comment IS NOT NULL
                     ORDER	BY rc.id DESC
                     LIMIT	2 ";

            /* Execue   */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach($rdo as $rate) {
                    $info = new stdClass();
                    $info->comment  = $rate->comment;
                    $info->rating   = $rate->rating;
                    $info->modified = userdate($rate->modified,'%d.%m.%Y', 99, false);

                    $last_comments[$rate->id] = $info;
                }//for_rdo
            }//if_rdo

            return $last_comments;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getLastCommentsRateCourse

    /**
     * @static
     * @param           $course_id
     * @return          int
     * @throws          Exception
     *
     * @creationDate    07/07/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get how many users are enrolled in the course
     */
    public static function getTotalUsersEnrolledCourse($course_id) {
        global $DB;

        try {
            /* Search Criteria  */
            $params             = array();
            $params['course']   = $course_id;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT count(u.id) as 'total'
                     FROM		{user}				u
                        JOIN	{user_enrolments}	ue	ON 	ue.userid 	= u.id
                        JOIN	{enrol}				e	ON	e.id		= ue.enrolid
                                                        AND	e.status	= 0
                                                        AND e.courseid 	= :course
                     WHERE		u.deleted = 0 ";

            /* Execute      */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return 0;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getTotalUsersEnrolledCourse

    /**
     * @static
     * @param           $course_id
     * @return          int
     * @throws          Exception
     *
     * @creationDate    07/07/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Count all the rates
     */
    public static function getTotalRatesCourse($course_id) {
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['course'] = $course_id;

            /* Execute  */
            $total = $DB->count_records('block_rate_course',$params);

            return $total;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//getTotalRatesCourse

    /**
     * @static
     * @param           $rate
     * @param           $total
     * @param           $title
     * @return          string
     *
     * @creationDate    07/07/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add progress bar connected with rating
     */
    public static function getProgressBarCode($rate,$total,$title) {
        /* Variables    */
        $id_bar = 'pbar_'.uniqid();
        $w          = 0;
        $bar_out    = '';

        if ($total) {
            $w = round(($rate*100/$total),0);
        }//if_total

        $bar_out .= '<div class="rating_bar_block">';
            $bar_out .= '<div class="rating_title">' . $title . '</div>';

            $bar_out .= '<div class="rating_bar" id="bar_{' . $id_bar . '}">';
                $bar_out .= '<div id="progress_{' . $id_bar .'}" class="rating_progress" style="width:'. $w . '%;"></div>';
            $bar_out .= '</div>';

            $bar_out .= '<div class="rating_value">';
                $bar_out .= '<div class="rating_value_num">' . $w.'</div>';
                $bar_out .= '<div class="rating_value_per"> %</div>';
            $bar_out .='</div>';
        $bar_out .= '</div>';//rating_bar_block

        return $bar_out;
    }//getProgressBarCode

    /**
     * @static
     * @param           $course_id
     * @param           $total_rates
     * @return          string
     *
     * @creationDate    03/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the Ratings Total
     */
    public static function AddRatingsTotal($course_id,$total_rates) {
        /* Variables    */
        $out = '';

        $url_avg = new moodle_url('/blocks/rate_course/pix/rating_graphic.php',array('courseid' => $course_id));
        $out .= '<h5 class="title_ratings chp-title">' . get_string('ratings_avg','local_course_page') . '</h5>';

        $out .= '<div class="rating_total clearfix chp-content">';
        $out .= '<div class="rating_total_title">' . '<img src="'. $url_avg . '" .  alt="average ratings"/>' . '</div>';
        $out .= '<div class="rating_total_value">' . $total_rates . '</div>';
        $out .= '</div>';

        return $out;
    }//AddRatingsTotal

    /**
     * @static
     * @param           $course_id
     * @param           $user_id
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    20/04/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * It checks if the user is just enrolled.
     */
    public static function IsUserEnrol($course_id,$user_id) {
        global $DB;

        try {
            /* Params   */
            $params = array();
            $params['course_id']    = $course_id;
            $params['user_id']      = $user_id;

            /* SQL Instruction  */
            $sql = " SELECT		ue.enrolid
                     FROM		{enrol}					e
                        JOIN	{user_enrolments}		ue	ON  ue.enrolid  = e.id
                                                            AND ue.userid   = :user_id
                     WHERE		e.courseid = :course_id
                        AND		e.status = 0 ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_else
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//IsUserEnrol

    /**
     * @static
     * @param           $course_id
     * @return          array|null
     * @throws          Exception
     *
     * @creationDate    14/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the fields/options connected with course format
     */
    public static function getFormatFields($course_id) {
        global $DB;

        try {
            /* Format Fields    */
            $format_fields = array();

            /* Search Criteria  */
            $params = array();
            $params['course_id'] = $course_id;

            /* SQL Instruction  */
            $sql = " SELECT		id,
                                name,
                                value
                     FROM		{course_format_options}
                     WHERE		courseid = :course_id
                     ORDER BY   id ASC ";

            /* Execute          */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $field = new stdClass();
                    $field->id      = $instance->id;
                    $field->name    = $instance->name;
                    $field->value   = $instance->value;

                    $format_fields[$instance->name] = $field;
                }//for_rdo

                return $format_fields;
            }else {
                return null;
            }//if_rdo

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getFormatFields

    /**
     * @return          array
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the edit_options
     */
    public static function get_edit_options() {
        global $CFG,$COURSE;

        /* Get the context  */
        if ($COURSE->id) {
            $context        = CONTEXT_COURSE::instance($COURSE->id);
        }else {
            $context = CONTEXT_COURSECAT::instance(0);
        }//if_else_course

        $edit_options   = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true, 'context' => $context);

        return array($edit_options,$context);
    }//get_edit_options


    /**
     * @return          array
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the file_options
     */
    public static function get_file_options() {
        global $CFG,$COURSE;

        /* Get the context  */
        if ($COURSE->id) {
            $context        = CONTEXT_COURSE::instance($COURSE->id);
        }else {
            $context = CONTEXT_COURSECAT::instance(0);
        }//if_else_course

        $file_options   = array('maxfiles' => 1, 'maxbytes'=>$CFG->maxbytes, 'subdirs' => 0, 'context' => $context);

        return array($file_options,$context);
    }//get_file_options

    /**
     * @static
     * @param           $edit_options
     * @param           $context
     * @return          stdClass
     *
     * @creationDate    27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Prepare the Standard Video
     */
    public static function prepareStandardHomeSummaryEditor($edit_options,$context) {
        /* Variables  */
        global $COURSE;
        $editor         = new stdClass();

        /* Prepare Standard Editor */
        $editor->homesummary       = '';
        $editor->homesummaryformat  = FORMAT_HTML;

        /* Prepare the editor   */
        if ($COURSE->id) {
            $format_options = course_get_format($COURSE->id)->get_format_options();
            if (array_key_exists('homesummary',$format_options)) {
                $editor->homesummary = $format_options['homesummary'];
            }//if_array_exists
            $editor = file_prepare_standard_editor($editor, 'homesummary', $edit_options,$context, 'course', 'homesummary',0);
        }else {
            $editor = file_prepare_standard_editor($editor, 'homesummary', $edit_options,null, 'course', 'homesummary',0);
        }//if_course


        return $editor;
    }//prepareStandardHomeSummaryEditor

    /**
     * @static
     * @param           $file_options
     * @param           $context
     * @param           $field
     * @return          stdClass
     *
     * @creationDate    27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Prepare the File Manager
     */
    public static function prepareFileManagerHomeGraphicsVideo($file_options,$context,$field) {
        /* Variables */
        global $COURSE;
        $file_editor = new stdClass();
        $file_editor->$field  = 0;

        /* Prepare Standard Editor */
        if ($COURSE->id) {
            $format_options = course_get_format($COURSE->id)->get_format_options();
            if (array_key_exists($field,$format_options)) {
                $file_editor->$field    = $format_options[$field];
            }//if_array_Exists

            file_prepare_standard_filemanager($file_editor, $field,$file_options,$context, 'course',$field,0);
        }else {
            file_prepare_standard_filemanager($file_editor, $field,$file_options,null, 'course',$field,0);
        }//if_course

        return $file_editor;
    }//prepareFileManagerHomeGraphics

    /**
     * @static
     * @param           $homesummary_editor
     * @return          mixed
     *
     * @creationDate    27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the content of 'Home Summary Editor'
     */
    public static function getHomeSummaryEditor($homesummary_editor) {
        /* Get Home Page Description */
        list($edit_options,$context) = self::get_edit_options();
        $editor = new stdClass();
        $editor->homesummary_editor = $homesummary_editor;
        $editor->homesummary = '';

        $editor = file_postupdate_standard_editor($editor, 'homesummary', $edit_options, $context, 'course', 'homesummary', 0);

        return $editor->homesummary;
    }//postupdateHomeSummaryEditor

    /**
     * @static
     * @param           $file_id
     * @param           $field
     * @param           $file_manager
     * @param           $delete
     * @return          int
     *
     * @creationDate    27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the reference (id) of the Video/Graphics File.
     */
    public static function getHomeGraphicsVideo($file_id,$field,$file_manager,$delete) {
        /* Variables    */
        global $DB,$COURSE;
        $Id_GraphicVideo = 0;
        $field_manager = $field . '_filemanager';

        /* First Remove Previous    */
        $fs = get_file_storage();
        if ($delete) {
            $file = $fs->get_file_by_id($file_id);

            $DB->delete_records('files',array('itemid' => $file->get_itemid()));
        }///deletepicture

        /* Get Home Graphics    */
        list($file_options,$context) = self::get_file_options();
        $file_options['accepted_types'] = array('image','web_image','video','web_video');

        $file_graphicVideo = new stdClass();
        $file_graphicVideo->$field_manager = $file_manager;

        $file_graphicVideo = file_postupdate_standard_filemanager($file_graphicVideo, $field, $file_options, $context, 'course', $field, $file_graphicVideo->$field_manager);

        if ($files = $fs->get_area_files($context->id, 'course', $field, $file_graphicVideo->$field_manager, 'id DESC', false)) {
            /* Remove Previous  */
            $file = reset($files);

            $Id_GraphicVideo = $file->get_id();
        }//if_file

        return $Id_GraphicVideo;
    }//getHomeGraphics

    /**
     * @static
     * @param           $data
     * @param           $course
     * @throws          Exception
     *
     * @creationDate    27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update all the information connected with the Course Home Page
     *
     * @updateDate      14/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Course Sector
     */
    public static function UpdateCourseHomePage($data,$course) {
        global $DB;

        try {
            /* Update Course Details    */
            /* Short Description    */
            list($editor_options,$context) = self::get_edit_options();
            $data = file_postupdate_standard_editor($data, 'summary', $editor_options,$context, 'course', 'summary', 0);
            $DB->set_field('course','summary',$data->summary,array('id' => $course->id));
            /* ID Number            */
            $DB->set_field('course','idnumber',$data->idnumber,array('id' => $course->id));
            /* Publihed Data        */
            $DB->set_field('course','startdate',$data->startdate,array('id' => $course->id));

            /* Update Format Options   */
            $format_fields = self::getFormatFields($course->id);
            foreach ($format_fields as $option) {
                $field = $option->name;
                switch ($field) {
                    case 'homesummary':
                        $option->value = self::getHomeSummaryEditor($data->homesummary_editor);
                        $DB->update_record('course_format_options',$option);

                        break;
                    case 'pagegraphics':
                        if (isset($data->deletepicture) && ($data->deletepicture)) {
                            $delete = true;
                        }else {
                            $delete = false;
                        }//if_delete
                        /* Get Id Graphic file  */
                        $graphic_id = course_page::getHomeGraphicsVideo($data->$field,$field,$data->pagegraphics_filemanager,$delete);
                        if ($graphic_id) {
                            $option->value = $graphic_id;
                            $DB->update_record('course_format_options',$option);
                        }//if_graphic_id

                        break;
                    case 'pagevideo':
                        if (isset($data->deletevideo) && ($data->deletevideo)) {
                            $delete = true;
                        }else {
                            $delete = false;
                        }//if_delete
                        /* Get Id Video File */
                        $video_id = course_page::getHomeGraphicsVideo($data->$field,$field,$data->pagevideo_filemanager,$delete);
                        if ($video_id) {
                            $option->value = $video_id;
                            $DB->update_record('course_format_options',$option);
                        }//if_video_id

                        break;
                    case 'course_sector':
                        if (isset($data->$field)) {
                            $option->value = implode(',',$data->$field);
                            $DB->update_record('course_format_options',$option);
                        }//if_data_field

                        break;
                    default:
                        if (isset($data->$field)) {
                            $option->value = $data->$field;
                            $DB->update_record('course_format_options',$option);
                        }//if_data_field

                        break;
                }//switch
            }//for_option
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//UpdateCourseHomePage

    /**
     * @param           $form
     * @param           $option
     * @param           $value
     * @param           $format
     * @throws          Exception
     *
     * @creationDate    15/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the options/fields connected with the course format. Only for the Course Home Page
     *
     * @updateDate      08/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Course Location and Course Sector
     */
    public static function printFormatOptions(&$form,$option,$value,$format) {
        /* Variables*/
        global  $USER;
        $str_format     = null;;
        $lstManager     = null;
        $lstLocations   = null;
        $lstSectors     = null;

        try {
            $str_format = 'format_' . $format;
                switch ($option) {
                    case 'prerequisities':
                        $form->addElement('textarea','prerequisities',get_string('home_prerequisities',$str_format),'rows="5" style="width:95%;"');
                        $form->setDefault('prerequisities',$value);
                        break;
                    case 'producedby':
                        $form->addElement('text','producedby',get_string('home_producedby',$str_format),'style="width:95%;"');
                        $form->setDefault('producedby',$value);
                        $form->setType('producedby',PARAM_TEXT);
                        break;
                    case 'location':
                        $form->addElement('text','location',get_string('home_location',$str_format),'style="width:95%;"');
                        $form->setDefault('location',$value);
                        $form->setType('location',PARAM_TEXT);
                        break;
                    case 'course_location':
                        $lstLocations = course_page::Get_CourseLocationsList($USER->id);
                        $form->addElement('select','course_location',get_string('home_location',$str_format),$lstLocations);
                        $form->setDefault('course_location',$value);
                        break;
                    case 'course_sector':
                        $lstLocations   = course_page::Get_CourseLocationsList($USER->id);
                        $lstSectors     = course_page::Get_SectorsLocationsList(implode(',',array_keys($lstLocations)));;
                        $form->addElement('select','course_sector',get_string('home_sector',$str_format),$lstSectors,'multiple');
                        $form->setDefault('course_sector',$value);
                        break;
                    case 'length':
                        $form->addElement('text','length',get_string('home_length',$str_format),'style="width:95%;"');
                        $form->setDefault('length',$value);
                        $form->setType('length',PARAM_TEXT);
                        break;
                    case 'effort':
                        $form->addElement('text','effort',get_string('home_effort',$str_format),'style="width:95%;"');
                        $form->setDefault('effort',$value);
                        $form->setType('effort',PARAM_TEXT);
                        break;
                    case 'manager':
                        $lst_manager = self::getCourseManager();
                        $form->addElement('select','manager',get_string('home_manager',$str_format),$lst_manager);
                        $form->setDefault('manager',$value);
                        break;
                    case 'author':
                        $form->addElement('text','author',get_string('home_author',$str_format),'style="width:95%;"');
                        $form->setDefault('author',$value);
                        $form->setType('author',PARAM_TEXT);
                        break;
                    case 'licence':
                        $form->addElement('text','licence',get_string('home_licence',$str_format),'style="width:95%;"');
                        $form->setDefault('licence',$value);
                        $form->setType('licence',PARAM_TEXT);
                        break;
                    default:
                        break;
                }//switch
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//printFormatOptions

    /**
     * @static
     * @param           $form
     * @param           $field
     *
     * @creationDate    27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the elements to the 'Course Home Page' form
     */
    public static function addCourseHomePage_Section(&$form,$field) {
        /* Variables    */
        global $COURSE;
        $visible        = array();

        switch ($field) {
            case 'homepage':
                $home_page = $form->createElement('checkbox','homepage',get_string('checkbox_home','local_course_page'));
                $form->insertElementBefore($home_page,'descriptionhdr');
                $format_options = course_get_format($COURSE->id)->get_format_options();
                if (!array_key_exists('homepage',$format_options)) {
                    $form->setDefault('homepage',1);
                }//if_exists

                break;
            case 'homevisible':
                $visible['0'] = get_string('hide');
                $visible['1'] = get_string('show');
                $home_visible = $form->createElement('select', 'homevisible', get_string('home_visible','local_course_page'), $visible);
                $form->insertElementBefore($home_visible,'descriptionhdr');

                break;
            case 'homesummary':
                $home_page_header = $form->createElement('header', 'homepagehdr',get_string('home_page','local_course_page'));
                $form->insertElementBefore($home_page_header,'courseformathdr');

                $form->addElement('hidden','homesummary');
                $form->setType('homesummary',PARAM_RAW);

                /* Get Editor   */
                list($edit_options,$context) = self::get_edit_options();
                $editor = self::prepareStandardHomeSummaryEditor($edit_options,$context);

                $home_summay = $form->createElement('editor','homesummary_editor',get_string('home_desc','local_course_page'),null,$edit_options);
                $form->insertElementBefore($home_summay,'courseformathdr');
                $form->setType('homesummary_editor',PARAM_RAW);
                $form->setDefault('homesummary_editor',$editor->homesummary_editor);

                break;
            case 'pagegraphics':
                $current_graphic = $form->createElement('static', 'current_graphic', get_string('current_graphic','local_course_page'));
                $form->insertElementBefore($current_graphic,'courseformathdr');

                $delete_picture = $form->createElement('checkbox', 'deletepicture', get_string('delete'));
                $form->insertElementBefore($delete_picture,'courseformathdr');
                $form->setDefault('deletepicture',0);

                /* Get FileManager   */
                list($file_options,$context) = self::get_file_options();
                $file_editor['accepted_types'] = array('image','web_image');
                $file_editor = self::prepareFileManagerHomeGraphicsVideo($file_options,$context,'pagegraphics');

                if ($file_editor->pagegraphics) {
                    /* URL IMAGE */
                    $img = '<img src="'  . self::getUrlPageGraphicsVideo($file_editor->pagegraphics) . '" width="75" height="75" />';

                    $form->setDefault('current_graphic',$img);
                }//if_pagegraphics

                $page_graphics = $form->createElement('filemanager', 'pagegraphics_filemanager', get_string('home_graphics','local_course_page'), null, $file_options);
                $form->insertElementBefore($page_graphics,'courseformathdr');
                $form->setDefault('pagegraphics_filemanager',$file_editor->pagegraphics);

                $form->addElement('hidden','pagegraphics');
                $form->setType('pagegraphics',PARAM_RAW);
                $format_options = course_get_format($COURSE->id)->get_format_options();
                if (array_key_exists('pagegraphics',$format_options)) {
                    $form->setDefault('pagegraphics',$format_options['pagegraphics']);
                }//if_exists

                break;
            case 'pagevideo':
                $current_video = $form->createElement('static', 'current_video', get_string('home_current_video','local_course_page'));
                $form->insertElementBefore($current_video,'courseformathdr');

                $delete_video  = $form->createElement('checkbox', 'deletevideo', get_string('home_delete_video','local_course_page'));
                $form->insertElementBefore($delete_video,'courseformathdr');
                $form->setDefault('deletevideo', 0);

                /* Get FileManager   */
                list($file_options,$context) = self::get_file_options();
                $file_editor['accepted_types'] = array('video','web_video');
                $file_editor = self::prepareFileManagerHomeGraphicsVideo($file_options,$context,'pagevideo');
                if ($file_editor->pagevideo) {
                    $fs = get_file_storage();
                    $file = $fs->get_file_by_id($file_editor->pagevideo);
                    if ($file) {
                        $form->setDefault('current_video',$file->get_filename());
                    }
                }//if_video

                $page_video = $form->createElement('filemanager', 'pagevideo_filemanager', get_string('home_video','local_course_page'), null, $file_options);
                $form->insertElementBefore($page_video,'courseformathdr');
                $form->setDefault('pagegraphics_filemanager',$file_editor->pagevideo_filemanager);

                $form->addElement('hidden','pagevideo');
                $form->setType('pagevideo',PARAM_RAW);
                $format_options = course_get_format($COURSE->id)->get_format_options();
                if (array_key_exists('pagevideo',$format_options)) {
                    $form->setDefault('pagevideo',$format_options['pagevideo']);
                }//if_exists

                break;
            default:
                break;
        }//switch_field
    }//addCourseHomePage_Section

    /**
     * @static
     * @param           $text
     * @param           $file
     * @param           $contextid
     * @param           $component
     * @param           $filearea
     * @param           $itemid
     * @param           array $options
     * @return          mixed
     *
     * @creationDate    04/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the url to show the content of home page summary like images...
     */
    public static function fileRewritePluginfileUrls_HomePage($text, $file, $contextid, $component, $filearea, $itemid, array $options=null) {
        global $CFG;

        $options = (array)$options;
        if (!isset($options['forcehttps'])) {
            $options['forcehttps'] = false;
        }

        if (!$CFG->slasharguments) {
            $file = $file . '?file=';
        }

        $baseurl = "$CFG->wwwroot/local/course_page/$file/$contextid/$component/$filearea/";

        if ($itemid !== null) {
            $baseurl .= "$itemid/";
        }

        if ($options['forcehttps']) {
            $baseurl = str_replace('http://', 'https://', $baseurl);
        }

        return str_replace('@@PLUGINFILE@@/', $baseurl, $text);
    }//fileRewritePluginfileUrls_HomePage

    /**
     * @static
     * @param           $relativepath
     * @param           $forcedownload
     * @param           $preview
     *
     * @creationDate    04/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Show the content of home page summary like images...
     */
    public static function filePluginfile_HomePage($relativepath,$forcedownload,$preview) {
        global $DB, $CFG, $USER;
        // relative path must start with '/'
        if (!$relativepath) {
            print_error('invalidargorconf');
        } else if ($relativepath[0] != '/') {
            print_error('pathdoesnotstartslash');
        }

        // extract relative path components
        $args = explode('/', ltrim($relativepath, '/'));

        if (count($args) < 3) { // always at least context, component and filearea
            print_error('invalidarguments');
        }

        $contextid = (int)array_shift($args);
        $component = clean_param(array_shift($args), PARAM_COMPONENT);
        $filearea  = clean_param(array_shift($args), PARAM_AREA);

        list($context, $course, $cm) = get_context_info_array($contextid);

        $fs = get_file_storage();

        if ($component === 'course') {
            if ($context->contextlevel != CONTEXT_COURSE) {
                send_file_not_found();
            }

            if ($filearea === 'homesummary') {
                if ($CFG->forcelogin) {
                    require_login();
                }

                $filename = array_pop($args);
                $filepath = $args ? '/'.implode('/', $args).'/' : '/';
                if (!$file = $fs->get_file($context->id, 'course', $filearea, 0, $filepath, $filename) or $file->is_directory()) {
                    send_file_not_found();
                }

                \core\session\manager::write_close(); // Unlock session during file serving.
                send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));

            }
        }
    }//filePluginfile_HomePage

    /**
     * @param           $userId
     * @return          array
     * @throws          Exception
     *
     * @creationDate    08/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the locations that can be added to the course
     */
    public static function Get_CourseLocationsList($userId) {
        /* Variables    */
        global $DB,$CFG;
        $myCompetence       = null;
        $sqlWhere           = null;
        $courseLocations    = array();

        try {
            /* Course Locations List    */
            $courseLocations[0] = get_string('sel_location','local_course_locations');
            /* Get Competence connected with user    */
            require_once($CFG->dirroot . '/local/course_locations/locationslib.php');
            $myCompetence = CourseLocations::Get_MyCompetence($userId);

            /* SQL Instruction  */
            /* All Locations    */
            $sql = " SELECT			cl.id,
                                    cl.name
                     FROM			{course_locations}	cl ";
            if ($myCompetence) {
                if ($myCompetence->levelZero) {
                    /* Locations Connected with level zero  */
                    if ($sqlWhere) {
                        $sqlWhere  = " WHERE cl.levelzero IN ($myCompetence->levelZero) ";
                    }else {
                        $sqlWhere .= " AND cl.levelzero IN ($myCompetence->levelZero) ";
                    }//if_sqlWhere

                    /* Locations Level One  */
                    if ($myCompetence->levelOne) {
                        if ($sqlWhere) {
                            $sqlWhere  = " WHERE cl.levelone IN ($myCompetence->levelOne) ";
                        }else {
                            $sqlWhere .= " AND cl.levelone IN ($myCompetence->levelOne) ";
                        }//if_sqlWhere
                    }//if_levelOne
                }//if_levelZero

                /* Add Criteria */
                $sql .= $sqlWhere;
                /* ADD Order    */
                $sql .= " ORDER BY cl.name ";

                /* Execute  */
                $rdo = $DB->get_records_sql($sql);
                if ($rdo) {
                    foreach ($rdo as $instance) {
                        $courseLocations[$instance->id] = $instance->name;
                    }//for_location
                }//if_rdo
            }//if_myCompetence

            return $courseLocations;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_MyCourseLocations

    /**
     * @param           $locations
     * @return          array
     * @throws          Exception
     *
     * @creationDate    08/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the sectors that can be added to the course
     */
    public static function Get_SectorsLocationsList($locations) {
        /* Variables    */
        global $DB;
        $sectors = array();

        try {
            /* Sectors List     */
            $sectors[0] = get_string('sel_sector','local_course_locations');

            if ($locations) {
                /* SQL Instruction  */
                $sql = " SELECT		DISTINCT 	rgc.id,
                                                rgc.name,
                                                rgc.industrycode
                         FROM		{report_gen_companydata}		rgc
                            JOIN	{report_gen_company_relation}	rg_cr	ON rg_cr.companyid 	= rgc.id
                            JOIN	{course_locations}			    cl		ON cl.levelone 		= rg_cr.parentid
                                                                            AND cl.id IN ($locations)
                         ORDER BY	rgc.industrycode, rgc.name ";

                /* Execute  */
                $rdo = $DB->get_records_sql($sql);
                if ($rdo) {
                    foreach ($rdo as $instance) {
                        $sectors[$instance->id] = $instance->industrycode . ' - ' . $instance->name;
                    }//for_Each
                }//if_rdo
            }//if_locations


            return $sectors;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_SectorsLocationsList

    /**
     * @param           $locationId
     * @return          null
     * @throws          Exception
     *
     * @creationDate    11/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the location name
     */
    public static function Get_LocationName($locationId) {
        /* Variables    */
        global $DB;

        try {
            /* Execute  */
            $rdo = $DB->get_record('course_locations',array('id' => $locationId),'name');
            if ($rdo) {
                return $rdo->name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_LocationName


    /**
     * @param           $sectorsLst
     * @return          null
     * @throws          Exception
     *
     * @creationDate    11/05/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the sectors name. List Format
     */
    public static function Get_SectorsName($sectorsLst) {
        /* Variables    */
        global $DB;
        $sectorsName = null;

        try {
            /* SQL Instruction  */
            $sql = " SELECT		GROUP_CONCAT(DISTINCT CONCAT(rgc.industrycode,' - ', rgc.name) ORDER BY rgc.industrycode, rgc.name SEPARATOR ', ') as 'sectors'
                     FROM		{report_gen_companydata}	rgc
                     WHERE      rgc.id IN ($sectorsLst) ";

            /* Execute*/
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                $sectorsName = $rdo->sectors;
            }//if_rdo

            return $sectorsName;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Get_SectorsName

    /* PRIVATE          */
}//course_page

class home_page_form extends moodleform {
    function definition() {
        $form    = $this->_form;

        $course   = $this->_customdata['course'];

        list($editor_options,$context) = course_page::get_edit_options();

        // Form definition with new course defaults.
        $form->addElement('header','general', get_string('general', 'form'));

        $form->addElement('text','idnumber', get_string('idnumbercourse'),'maxlength="100"  size="10"');
        $form->addHelpButton('idnumber', 'idnumbercourse');
        $form->setType('idnumber', PARAM_RAW);
        if (!empty($course->id) and !has_capability('moodle/course:changeidnumber', $context)) {
            $form->hardFreeze('idnumber');
            $form->setConstants('idnumber', $course->idnumber);
        }//idnumber
        $form->setDefault('idnumber',$course->idnumber);

        $form->addElement('date_selector', 'startdate', get_string('startdate'));
        $form->addHelpButton('startdate', 'startdate');
        if ($course->startdate) {
            $form->setDefault('startdate', $course->startdate);
        }else {
            $form->setDefault('startdate', time() + 3600 * 24);
        }//if_startdate


        // Description.
        $form->addElement('header', 'descriptionhdr', get_string('description'));
        $form->setExpanded('descriptionhdr');
        $course = file_prepare_standard_editor($course, 'summary', $editor_options,$context, 'course', 'summary', 0);
        $form->addElement('editor','summary_editor', get_string('coursesummary'), null, $editor_options);
        $form->addHelpButton('summary_editor', 'coursesummary');
        $form->setType('summary_editor', PARAM_RAW);
        $form->setDefault('summary_editor',$course->summary_editor);
        $form->addRule('summary_editor', null, 'required');

        // Course format.
        $form->addElement('header', 'courseformathdr', get_string('type_format', 'plugin'));
        $form->setExpanded('courseformathdr');

        /* Course Format Section    */
        $format_options = course_get_format($course)->get_format_options();
        foreach ($format_options as $name=>$option) {
            course_page::addCourseHomePage_Section($form,$name);
            course_page::printFormatOptions($form,$name,$option,$course->format);
        }

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$course->id);

        $form->addElement('hidden','show');
        $form->setType('show',PARAM_INT);
        $form->setDefault('show',1);

        $form->setExpanded('homepagehdr');

        $this->add_action_buttons(true);

        $this->set_data($format_options);
    }//definition
}//home_page_form
