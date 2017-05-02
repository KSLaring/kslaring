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
 * @updateDate      21/06/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Internal && External prices
 *
 */
require_once($CFG->libdir.'/formslib.php');

define('EXCELLENT_RATING',5);
define('GOOD_RATING',4);
define('AVG_RATING',3);
define('POOR_RATING',2);
define('BAD_RATING',1);

define('FILED_COURSE_INTERNAL_PRICE','customtext3');
define('FILED_COURSE_EXTERNAL_PRICE','customtext4');
define('MAXENROLMENTS','customint2');

class course_page  {
    /**********/
    /* PUBLIC */
    /**********/

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

    /**
     * @throws          Exception
     *
     * @creationDate    21/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Initialize the sector selector
     */
    public static function init_locations_sector() {
        // Variables
        global $PAGE;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;

        try {
            // Initialise variables
            $name       = 'sectors';
            $path       = '/local/course_page/yui/sectors.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');

            // Initialise js module
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => null
                             );

            $PAGE->requires->js_init_call('M.core_coursepage.init_sectors',
                                           array('course_location','course_sector'),
                                           false,
                                           $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//init_locations_sector

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
    public static function get_url_page_graphics_video($itemid) {
        // Variables
        $fs     = null;
        $file   = null;
        $url    = null;

        try {
            // Store File
            $fs = get_file_storage();

            // File Instance
            $file   = $fs->get_file_by_id($itemid);

            // Make URL
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
    }//get_url_page_graphics_video

    /**
     * @param           $courseId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    05/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Gets the present manager connected with the course
     */
    public static function get_manager_assigned($courseId) {
        // Variables
        global $DB;
        $params     = null;
        $rdo        = null;
        $manager    = null;

        try {
            // Search Criteria
            $params = array();
            $params['name']     = 'manager';
            $params['courseid'] = $courseId;

            // Execute
            $rdo = $DB->get_record('course_format_options',$params,'value');
            if ($rdo) {
                $manager = $rdo->value;
            }//if_rdo

            return $manager;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_manager_assigned

    /**
     * @static
     * @param           $course_id
     * @param           $notIn
     * 
     * @return          array
     * @throws          Exception
     *
     * @creationDate    19/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the teachers connected with the course.
     */
    public static function get_courses_teachers($course_id,$notIn) {
        // Variables
        global $DB;
        $lst_teachers   = null;
        $context        = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            // Teachers
            $lst_teachers = array();

            // Context
            $context = context_course::instance($course_id);

            // Search Criteria
            $params = array();
            $params['context_id'] = $context->id;

            // SQL Instruction
            $sql = " SELECT	DISTINCT 
                                u.id,
                                CONCAT(u.firstname, ' ' , u.lastname) as 'name'
                     FROM		{user}					u
                        JOIN	{role_assignments}		ra		ON		ra.userid 		= u.id
                                                                AND     ra.contextid    = :context_id
                        JOIN	{role}					r		ON		r.id 			= ra.roleid
                                                                AND		r.archetype 	IN ('teacher')

                     WHERE		u.deleted = 0
                        AND     u.id NOT IN ($notIn)
                     ORDER BY 	u.firstname, u.lastname ";

            // Execute
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
    }//get_courses_teachers


    /**
     * @param           $course_id
     * 
     * @return          mixed|null
     * @throws          Exception
     * 
     * @creationDate    14/10/2016
     * @author          eFaktor     (fbv)
     * 
     * Description
     * Get manager connected with the course
     */
    public static function get_courses_manager($course_id) {
        // Variables
        global $DB;
        $context        = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            // Context
            $context = context_course::instance($course_id);

            // Search Criteria
            $params = array();
            $params['context_id'] = $context->id;

            // SQL Instruction
            $sql = " SELECT		u.*
                     FROM		{user}					u
                        JOIN	{role_assignments}		ra		ON		ra.userid 		= u.id
                                                                AND     ra.contextid    = :context_id
                        JOIN	{role}					r		ON		r.id 			= ra.roleid
                                                                AND		r.archetype 	IN ('editingteacher')

                     WHERE		u.deleted = 0
                     ORDER BY 	ra.timemodified,u.firstname, u.lastname
                     LIMIT 0,1 ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }//if_rod
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_courses_manager

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
    public static function get_last_ratings($course_id) {
        // Variables
        global $DB;
        $last_rates = null;
        $params     = null;
        $sql        = null;
        $rdo        = null;

        try {
            // Last Ratings
            $last_rates = array();

            // Search criteria
            $params = array();
            $params['course_id'] = $course_id;

            // SQL Instruction
            $sql = " SELECT	  CONCAT(u.firstname, ', ',u.lastname) as 'user',
                              rc.rating
                     FROM	  {block_rate_course}       rc
                        JOIN  {user}					u 	ON u.id = rc.userid
                     WHERE	  rc.course = :course_id
                     ORDER	BY rc.id DESC
                     LIMIT	5 ";

            // Execute
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
    }//get_last_ratings

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
    public static function is_course_rating($course_id) {
        // Variables
        global $DB;
        $rdo    = null;

        try {
           // Execute
           $rdo = $DB->get_records('block_rate_course',array('course' => $course_id));
           if ($rdo) {
               return true;
           }else {
               return false;
           }//if_else_rdo
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//is_course_rating

    /**
     * @param           $userId
     * @param           $courseId
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    14/10/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user can give a rate
     */
    public static function user_can_rate_course($userId,$courseId) {
        // Variables
        global $DB;
        $rdo    = null;
        $params = null;
        $sql    = null;

        try {
            // Search Criteria
            $params = array();
            $params['userid']   = $userId;
            $params['course']   = $courseId;

            // Rate course only if the user has completed it
            // SQL Instruction
            $sql = " SELECT	id
                     FROM	{course_completions}
                     WHERE	userid = :userid
                        AND	course = :course
                        AND timecompleted IS NOT NULL
                        AND	timecompleted != 0 ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                // Check if the user has already rated
                // Execute
                $rdo = $DB->get_records('block_rate_course',$params);
                if ($rdo) {
                    // Already rated
                    return false;
                }else {
                    // No rated yet
                    return true;
                }
            }else {
                // Not Completed -- No Rate
                return false;
            }//if_else
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//user_can_rate_course

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
    public static function get_count_type_rate_course($course_id,$type_rate) {
        // Variables
        global $DB;
        $params = null;
        $total  = null;

        try {
            // Search Criteria
            $params = array();
            $params['course'] = $course_id;
            $params['rating'] = $type_rate;

            // Execute
            $total = $DB->count_records('block_rate_course',$params);

            return $total;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_count_type_rate_course

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
    protected static function get_coments_type_rate_course($course_id,$type_rate) {
        /* Variables    */
        global $DB;
        $coments_rate   = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;

        try {
            $coments_rate = array();

            // Search Criteria
            $params = array();
            $params['course'] = $course_id;
            $params['rating'] = $type_rate;

            // SQL Instruction
            $sql = " SELECT	  rc.id,
                              rc.comment
                     FROM	  {block_rate_course}       rc
                     WHERE	  rc.course = :course_id
                        AND   rc.rating = :rating
                        AND   rc.comment IS NOT NULL
                     ORDER	BY rc.id DESC
                     LIMIT	2 ";

            // Execute
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
    }//get_coments_type_rate_course

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
    public static function get_last_comments_rate_course($course_id) {
        /* Variables    */
        global $DB;
        $last_comments  = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $info           = null;

        try {
            // Last Ratings
            $last_comments = array();

            // Search criteria
            $params = array();
            $params['course_id'] = $course_id;

            // SQL Instruction
            $sql = " SELECT	  rc.id,
                              rc.comment,
                              rc.rating,
                              rc.modified
                     FROM	  {block_rate_course}       rc
                     WHERE	  rc.course = :course_id
                        AND   rc.comment IS NOT NULL
                     ORDER	BY rc.id DESC
                     LIMIT	2 ";

            // Execute
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
    }//get_last_comments_rate_course

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
    public static function get_total_users_enrolled_course($course_id) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;
        try {
            // Search Criteria
            $params             = array();
            $params['course']   = $course_id;

            // SQL Instruction
            $sql = " SELECT	DISTINCT 
                                count(u.id) as 'total'
                     FROM		{user}				u
                        JOIN	{user_enrolments}	ue	ON 	ue.userid 	= u.id
                        JOIN	{enrol}				e	ON	e.id		= ue.enrolid
                                                        AND	e.status	= 0
                                                        AND e.courseid 	= :course
                     WHERE		u.deleted = 0 ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->total;
            }else {
                return 0;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_users_enrolled_course

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
    public static function get_total_rates_course($course_id) {
        /* Variables */
        global $DB;
        $params = null;
        $total  = null;

        try {
            // Search Criteria
            $params = array();
            $params['course'] = $course_id;

            // Execute
            $total = $DB->count_records('block_rate_course',$params);

            return $total;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//get_total_rates_course

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
    public static function get_progress_bar_code($rate,$total,$title) {
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
    }//get_progress_bar_code

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
    public static function add_ratings_total($course_id,$total_rates) {
        /* Variables    */
        $out = '';

        $url_avg = new moodle_url('/blocks/rate_course/pix/rating_graphic.php',array('courseid' => $course_id));
        $out .= '<h5 class="title_ratings chp-title">' . get_string('ratings_avg','local_course_page') . '</h5>';

        $out .= '<div class="rating_total clearfix chp-content">';
            $out .= '<div class="rating_total_title">' . '<img src="'. $url_avg . '" .  alt="average ratings"/>' . '</div>';
            $out .= '<div class="rating_total_value">' . $total_rates . '</div>';
        $out .= '</div>';

        return $out;
    }//add_ratings_total

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
    public static function is_user_enrol($course_id,$user_id) {
        /* Variables    */
        global $DB;
        $params = null;
        $sql    = null;
        $rdo    = null;

        try {
            // Search criteria
            $params = array();
            $params['course_id']    = $course_id;
            $params['user_id']      = $user_id;

            // SQL Instruction
            $sql = " SELECT		ue.enrolid
                     FROM		{enrol}					e
                        JOIN	{user_enrolments}		ue	ON  ue.enrolid  = e.id
                                                            AND ue.userid   = :user_id
                     WHERE		e.courseid = :course_id
                        AND		e.status = 0 ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_else
        }catch(Exception $ex) {
            throw $ex;
        }//try_catch
    }//is_user_enrol

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
    public static function get_format_fields($course_id) {
        /* Variables    */
        global $DB;
        $format_fields  = null;
        $params         = null;
        $sql            = null;
        $rdo            = null;
        $field          = null;

        try {
            // Format Fields
            $format_fields = array();

            // Search Criteria
            $params = array();
            $params['course_id'] = $course_id;

            // SQL Instruction
            $sql = " SELECT		id,
                                name,
                                value
                     FROM		{course_format_options}
                     WHERE		courseid = :course_id
                     ORDER BY   id ASC ";

            // Execute
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
    }//get_format_fields

    /**
     * Description
     * Add the available seats to the fields/options connected with course format.
     *
     * @param           int     $course_id          Course id
     * @param           array   $format_options     Course format options
     *
     * @return                  mixed
     * @throws                  Exception
     *
     * @creationDate    06/12/2015
     * @author          efaktor     (fbv)
     */
    public static function get_available_seats_format_option ($course_id,$format_options) {
        /* Variables */
        global $DB;
        $sql        = null;
        $params     = null;
        $avail      = null;

        try {
            $avail = '-';

            // Add enrolled users.
            // Get Instance Enrolment Waiting List
            $instance = $DB->get_record('enrol', array('courseid' => $course_id,'enrol' => 'waitinglist','status' => '0'));
            if ($instance) {
                if ($instance->{MAXENROLMENTS}) {
                    // Get total seats confirmed
                    //Search criteria
                    $params = array();
                    $params['course'] = $course_id;

                    // SQL Instruction
                    $sql = " SELECT sum(allocseats) as 'confirmed'
                             FROM   {enrol_waitinglist_queue}
                             WHERE 	courseid = :course
                                AND allocseats > 0 ";

                    //Execute
                    $rdo = $DB->get_record_sql($sql,$params);
                    if ($rdo) {
                        $avail = ($instance->{MAXENROLMENTS} - $rdo->confirmed);
                    }else {
                        $avail = $instance->{MAXENROLMENTS};
                    }//if_rdo

                    $avail .= ' ' . get_string('of','local_course_page') . ' '. $instance->{MAXENROLMENTS};
                }else {
                    // Unlimit
                    $avail = get_string('unlimited_seats','local_course_page');
                }//if_else_max
            }else {
                $avail = 'hide';
            }//if_instance

            //Info to display in home page
            $field = new stdClass();
            $field->id      = 0;
            $field->name    = 'enrolledusers';
            $field->value   = $avail;
            $format_options['enrolledusers'] = $field;

            return $format_options;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_available_seats_format_option

    /***
     * @param           $courseID
     *
     * @return          int
     * @throws          Exception
     *
     * @creationDate    15/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get how many seats are available
     */
    public static function get_available_seats($courseID) {
        /* Variables */
        global $CFG, $DB;
        $instance   = null;
        $seats      = 0;

        try {
            // Get Instance Enrolment Waiting List
            $instance = $DB->get_record('enrol', array('courseid' => $courseID,'enrol' => 'waitinglist','status' => '0'));
            if ($instance) {
                /* Get Seats    */
                require_once($CFG->dirroot . '/enrol/waitinglist/lib.php');

                if ($instance->{ENROL_WAITINGLIST_FIELD_MAXENROLMENTS}) {
                    $enrolWaitingList = new enrol_waitinglist_plugin();
                    $seats = $enrolWaitingList->get_vacancy_count($instance);
                }else {
                    $seats = 1;
                }

            }else {
                $seats = 1;
            }//if_instance

            return $seats;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_available_seats


    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the edit_options
     */
    public static function get_edit_options($courseId = null) {
        /* Variables    */
        global $CFG,$COURSE;
        $context        = null;
        $edit_options   = null;

        try {
            //Get the context
            if ($COURSE->id) {
                $context        = context_course::instance($COURSE->id);
            }elseif ($courseId) {
                $context        = context_course::instance($COURSE->id);
            }else {
                $context        = context_coursecat::instance(0);
            }//if_else_course

            $edit_options   = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false,
                                    'noclean'=>true, 'context' => $context);

            return array($edit_options,$context);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_edit_options


    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the file_options
     */
    public static function get_file_options($courseId = null) {
        /* Variables    */
        global $CFG,$COURSE;
        $context        = null;
        $file_options   = null;

        try {
            // Get the context
            if ($courseId) {
                $context        = context_course::instance($courseId);
            }else if ($COURSE->id) {
                $context        = context_course::instance($COURSE->id);
            }else {
                $context        = context_coursecat::instance(0);
            }//if_else_course

            $file_options   = array('maxfiles' => 1, 'maxbytes'=>$CFG->maxbytes, 'subdirs' => 0, 'context' => $context);

            return array($file_options,$context);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_file_options

    /**
     * @static
     * @param           $edit_options
     * @param           $context
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Prepare the Standard Video
     */
    public static function prepare_standard_home_summary_editor($edit_options,$context,$courseId = null) {
        /* Variables  */
        global $COURSE;
        $editor         = null;
        $format_options = null;

        try {
            /* Prepare Standard Editor */
            $editor         = new stdClass();
            $editor->homesummary        = '';
            $editor->homesummaryformat  = FORMAT_HTML;

            // Prepare the editor
            // When changes are made during a new course setup the $COURSE global
            // is set to the site, therefore we need to check if courseid is set
            // and if it is greater than 1.
            if ($courseId) {
                $COURSE->id = $courseId;
            }
            if (isset($COURSE->id) && $COURSE->id > 1) {
                $format_options = course_get_format($COURSE->id)->get_format_options();
                if (array_key_exists('homesummary',$format_options)) {
                    $editor->homesummary = $format_options['homesummary'];
                }//if_array_exists
                $editor = file_prepare_standard_editor($editor, 'homesummary', $edit_options,$context, 'course', 'homesummary',0);
            }else {
                // If changes are made during a new course setup the context needs
                // to be removed from the $edit_options.
                $edit_options['context'] = null;
                $editor = file_prepare_standard_editor($editor, 'homesummary', $edit_options,null, 'course', 'homesummary',0);
            }//if_course

            return $editor;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//prepare_standard_home_summary_editor

    /**
     * @static
     * @param           $file_options
     * @param           $context
     * @param           $field
     * @return          stdClass
     * @throws          Exception
     *
     * @creationDate    27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Prepare the File Manager
     */
    public static function prepare_file_manager_home_graphics_video($file_options,$context,$field,$courseId = null) {
        /* Variables */
        global $COURSE;
        $file_editor    = null;
        $format_options = null;
        $itemId         = 0;

        try {
            // File Editor
            $file_editor = new stdClass();
            $file_editor->$field  = 0;

            // Prepare Standard Editor
            if ($courseId) {
                $COURSE->id = $courseId;
            }
            if ($COURSE->id) {
                $format_options = course_get_format($COURSE->id)->get_format_options();
                if (array_key_exists($field,$format_options)) {
                    if ($format_options[$field]) {
                        $file_editor->$field    = $format_options[$field];

                        // Store File
                        $fs = get_file_storage();

                        // File Instance
                        $file   = $fs->get_file_by_id($format_options[$field]);
                        if ($file) {
                            $itemId = $file->get_itemid();
                        }
                    }
                }//if_array_Exists
            }//if_course

            file_prepare_standard_filemanager($file_editor, $field,$file_options,$context, 'course',$field,$itemId);

            return $file_editor;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//prepare_file_manager_home_graphics_video

    /**
     * Description
     * update the graphic connected with the course and home page.
     * 
     * @creationDate        02/12/2016
     * @author              eFaktor     (fbv)
     * 
     * @param       int     $course_id      Course id
     * @param       string  $field          Course format field
     * @param       string  $field_manager  Course format filemanager
     * @param       int     $field_value    filemanager id
     * 
     * @return      null
     * @throws      Exception
     */
    public static function postupdate_homegraphics_manager($course_id,$field,$field_manager,$field_value) {
        /* Variables */
        global $CFG;
        $editor     = null;
        $options    = null;
        $context    = null;
        $fs         = null;
        $file       = null;
        $itemId     = null;

        try {
            // Course context
            $context = context_course::instance($course_id);

            // Get file options
            $options   = array('maxfiles'       => 1,
                               'maxbytes'       => $CFG->maxbytes,
                               'subdirs'        => 0,
                               'context'        => $context,
                               'accepted_types' => array('image','web_image','video','web_video'));

            // Editor
            // File Graphic Video
            $editor = new stdClass();
            $editor->$field_manager = $field_value;

            $editor = file_postupdate_standard_filemanager($editor, $field, $options, $context, 'course', $field, 0);

            /* For Home page */
            $editor = file_postupdate_standard_filemanager($editor, $field, $options, $context, 'course', $field, $field_value);

            // Return Graphic Id for the Home Page
            $fs = get_file_storage();
            if ($files = $fs->get_area_files($context->id, 'course', $field, $field_value, 'id DESC', false)) {
                if ($files) {
                    // Remove Previous
                    $file = reset($files);

                    if ($file) {
                        $itemId = $file->get_id();
                    }
                }
            }//if_file

            return $itemId;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//postupdate_homegraphics_manager


    /**
     * @static
     * @param           $homesummary_editor
     * @return          mixed
     * @throws          Exception
     *
     * @creationDate    27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the content of 'Home Summary Editor'
     */
    public static function get_home_summary_editor($homesummary_editor) {
        /* Variables    */
        $edit_options   = null;
        $context        = null;
        $editor         = null;

        try {
            // Get Home Page Description
            list($edit_options,$context) = self::get_edit_options();

            // Editor
            $editor = new stdClass();
            $editor->homesummary_editor = $homesummary_editor;
            $editor->homesummary        = '';

            $editor = file_postupdate_standard_editor($editor, 'homesummary', $edit_options, $context, 'course', 'homesummary', 0);

            return $editor->homesummary;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_home_summary_editor

    /**
     * @static
     * @param           $file_id
     * @param           $field
     * @param           $file_manager
     * @param           $delete
     * @return          int
     * @throws          Exception
     *
     * @creationDate    27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the reference (id) of the Video/Graphics File.
     */
    public static function get_home_graphics_video($file_id,$field,$file_manager,$delete) {
        /* Variables    */
        global $DB;
        $fs                 = null;
        $file               = null;
        $file_options       = null;
        $context            = null;
        $file_graphicVideo  = null;
        $Id_GraphicVideo    = 0;
        $field_manager      = $field . '_filemanager';

        try {
            // First Remove Previous
            $fs = get_file_storage();
            if ($delete) {
                $file = $fs->get_file_by_id($file_id);

                $DB->delete_records('files',array('itemid' => $file->get_itemid()));
            }///deletepicture

            // Get Home Graphics
            list($file_options,$context) = self::get_file_options();
            $file_options['accepted_types'] = array('image','web_image','video','web_video');

            // File Graphic Video
            $file_graphicVideo = new stdClass();
            $file_graphicVideo->$field_manager = $file_manager;

            $file_graphicVideo = file_postupdate_standard_filemanager($file_graphicVideo, $field, $file_options, $context, 'course', $field, $file_graphicVideo->$field_manager);

            if ($files = $fs->get_area_files($context->id, 'course', $field, $file_graphicVideo->$field_manager, 'id DESC', false)) {
                // Remove Previous
                $file = reset($files);

                $Id_GraphicVideo = $file->get_id();
            }//if_file

            return $Id_GraphicVideo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_home_graphics_video

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
    public static function update_course_home_page($data,$course) {
        /* Variables    */
        global $DB;
        $editor_options = null;
        $context        = null;
        $format_fields  = null;
        $delete         = null;
        $graphic_id     = null;
        $video_id       = null;

        try {
            // Update Course Details
            // Short Description
            list($editor_options,$context) = self::get_edit_options();
            $data = file_postupdate_standard_editor($data, 'summary', $editor_options,$context, 'course', 'summary', 0);
            $DB->set_field('course','summary',$data->summary,array('id' => $course->id));
            // ID Number
            $DB->set_field('course','idnumber',$data->idnumber,array('id' => $course->id));
            // Publihed Data
            $DB->set_field('course','startdate',$data->startdate,array('id' => $course->id));

            // Update Format Options
            $format_fields = self::get_format_fields($course->id);
            foreach ($format_fields as $option) {
                $field = $option->name;
                switch ($field) {
                    case 'homesummary':
                        $option->value = self::get_home_summary_editor($data->homesummary_editor);
                        $DB->update_record('course_format_options',$option);

                        break;

                    case 'pagegraphics':
                        // Get Id Graphic file
                        $graphic_id = course_page::get_home_graphics_video($data->$field,$field,$data->pagegraphics_filemanager,false);
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

                        //Get Id Video File
                        $video_id = course_page::get_home_graphics_video($data->$field,$field,$data->pagevideo_filemanager,$delete);
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
    }//update_course_home_page

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
     *
     * @updateDate      02/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the manager list with the search filter
     */
    public static function print_format_options(&$form,$option,$value,$format) {
        /* Variables*/
        global  $COURSE,$USER;
        $str_format     = null;
        $lstLocations   = null;
        $lstSectors     = null;
        $location       = null;

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

                    case 'course_location':
                        $lstLocations = course_page::get_course_locations_list($USER->id);
                        $form->addElement('select','course_location',get_string('home_location',$str_format),$lstLocations);
                        $form->setDefault('course_location',$value);

                        break;

                    case 'course_sector':
                        $location = self::get_course_location($COURSE->id);
                        $lstSectors     = course_page::get_sectors_locations_list($location);
                        $form->addElement('select','course_sector',get_string('home_sector',$str_format),$lstSectors,'multiple');
                        $form->setDefault('course_sector',$value);

                        break;
                    case 'time':
                        $form->addElement('textarea','time',get_string('home_time_from_to',$str_format),'rows="5" style="width:95%;" readonly');
                        $form->setDefault('time',$value);

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
    }//print_format_options

    /**
     * @static
     * @param           $form
     * @param           $field
     * @param           $from_home
     *
     * @creationDate    27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the elements to the 'Course Home Page' form
     *
     * @updateDate      02/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the manager list with the search filter
     *
     * @updateDate  21/01/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add the 'ratings' option format
     */
    public static function add_course_home_page_section(&$form,$field,$from_home = false) {
        /* Variables    */
        global $COURSE;
        $visible        = array();

        $format_options = course_get_format($COURSE->id)->get_format_options();

        switch ($field) {
            case 'homepage':
                $home_page = $form->createElement('checkbox','homepage',get_string('checkbox_home','local_course_page'));
                $form->insertElementBefore($home_page,'descriptionhdr');

                if (!array_key_exists('homepage',$format_options)) {
                    $form->setDefault('homepage',1);
                }//if_exists

                break;
            case 'ratings':

                $home_ratings = $form->createElement('checkbox','ratings',get_string('home_ratings','local_course_page'));
                $form->insertElementBefore($home_ratings,'descriptionhdr');

                if (!array_key_exists('ratings',$format_options)) {
                    $form->setDefault('ratings',1);
                }//if_exists

                break;

            case 'participant':
                $home_participant = $form->createElement('checkbox','participant',get_string('home_participant','local_course_page'));
                $form->insertElementBefore($home_participant,'descriptionhdr');

                if (!array_key_exists('participant',$format_options)) {
                    $form->setDefault('participant',1);
                }//if_exists

                break;

            case 'homevisible':
                $visible['0'] = get_string('hide');
                $visible['1'] = get_string('show');
                $home_visible = $form->createElement('select', 'homevisible', get_string('home_visible','local_course_page'), $visible);
                $form->insertElementBefore($home_visible,'ratings');

                break;
            case 'homesummary':
                $home_page_header = $form->createElement('header', 'homepagehdr',get_string('home_page','local_course_page'));
                $form->insertElementBefore($home_page_header,'courseformathdr');

                $form->addElement('hidden','homesummary');
                $form->setType('homesummary',PARAM_RAW);

                /* Get Editor   */
                list($edit_options,$context) = self::get_edit_options();
                $editor = self::prepare_standard_home_summary_editor($edit_options,$context);

                $home_summay = $form->createElement('editor','homesummary_editor',get_string('home_desc','local_course_page'),null,$edit_options);
                $form->insertElementBefore($home_summay,'courseformathdr');
                $form->setType('homesummary_editor',PARAM_RAW);
                $form->setDefault('homesummary_editor',$editor->homesummary_editor);

                break;
            case 'pagegraphics':
                /* Get FileManager   */
                list($file_options,$context) = self::get_file_options();
                $file_options['accepted_types'] = array('image','web_image');
                $file_editor = self::prepare_file_manager_home_graphics_video($file_options,$context,'pagegraphics');

                $page_graphics = $form->createElement('filemanager', 'pagegraphics_filemanager', get_string('home_graphics','local_course_page'), null, $file_options);
                $form->insertElementBefore($page_graphics,'courseformathdr');
                $form->setDefault('pagegraphics_filemanager',$file_editor->pagegraphics_filemanager);

                $form->addElement('hidden','pagegraphics');
                $form->setType('pagegraphics',PARAM_RAW);
                if (array_key_exists('pagegraphics',$format_options)) {
                    $form->setDefault('pagegraphics',$format_options['pagegraphics']);
                }//if_exists

                break;

            case 'pagegraphicstitle':
                $pageTitle = $form->createElement('text','pagegraphicstitle',get_string('home_graphicstitle','local_course_page'),'style="width:95%;"');
                $form->setType('pagegraphicstitle',PARAM_TEXT);
                $form->insertElementBefore($pageTitle,'courseformathdr');

                if (array_key_exists('pagegraphicstitle',$format_options)) {
                    $form->setDefault('pagegraphicstitle',$format_options['pagegraphicstitle']);
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
                $file_editor = self::prepare_file_manager_home_graphics_video($file_options,$context,'pagevideo');
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
                if (array_key_exists('pagevideo',$format_options)) {
                    $form->setDefault('pagevideo',$format_options['pagevideo']);
                }//if_exists

                break;
            default:
                break;
        }//switch_field
    }//add_course_home_page_section

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
     * @throws          Exception
     *
     * @creationDate    04/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the url to show the content of home page summary like images...
     */
    public static function file_rewrite_pluginfile_urls_homepage($text, $file, $contextid, $component, $filearea, $itemid, array $options=null) {
        /* Variables */
        global $CFG;
        $baseurl    = null;

        try {
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
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//file_rewrite_pluginfile_urls_homepage

    /**
     * @static
     * @param           $relativepath
     * @param           $forcedownload
     * @param           $preview
     * @throws          Exception
     *
     * @creationDate    04/09/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Show the content of home page summary like images...
     */
    public static function file_pluginfile_homepage($relativepath,$forcedownload,$preview) {
        /* Variables    */
        global $CFG;
        $args       = null;
        $contextid  = null;
        $component  = null;
        $filearea   = null;
        $fs         = null;
        $filename   = null;
        $filepath   = null;

        try {
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
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//file_pluginfile_homepage

    /**
     * @param           $courseId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    21/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get course location connected with the course
     */
    public static function get_course_location($courseId) {
        /* Variables    */
        global $DB;
        $params     = null;
        $rdo        = null;
        $location   = null;

        try {
            // Search Criteria
            $params = array();
            $params['name']     = 'course_location';
            $params['courseid'] = $courseId;

            // Execute
            $rdo = $DB->get_record('course_format_options',$params,'value');
            if ($rdo) {
                if ($rdo->value) {
                    $location = $rdo->value;
                }
            }//if_Rdo

            return $location;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_course_location

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
    public static function get_course_locations_list($userId) {
        /* Variables    */
        global $DB,$CFG;
        $myCompetence       = null;
        $sql                = null;
        $sqlWhere           = null;
        $rdo                = null;
        $courseLocations    = array();

        try {
            // Course Locations List
            $courseLocations[0] = get_string('sel_location','local_friadmin');

            // Get Competence connected with user
            require_once($CFG->dirroot . '/local/friadmin/course_locations/locationslib.php');
            $myCompetence = CourseLocations::Get_MyCompetence($userId);

            // SQL Instruction
            $sql = " SELECT			cl.id,
                                    cl.name
                     FROM			{course_locations}	cl ";
            if ($myCompetence) {
                if ($myCompetence->levelZero) {
                    // Locations Connected with level zero
                    if (!$sqlWhere) {
                        $sqlWhere  = " WHERE cl.levelzero IN ($myCompetence->levelZero) ";
                    }else {
                        $sqlWhere .= " AND cl.levelzero IN ($myCompetence->levelZero) ";
                    }//if_sqlWhere

                    // Locations Level One
                    if ($myCompetence->levelOne) {
                        if (!$sqlWhere) {
                            $sqlWhere  = " WHERE cl.levelone IN ($myCompetence->levelOne) ";
                        }else {
                            $sqlWhere .= " AND cl.levelone IN ($myCompetence->levelOne) ";
                        }//if_sqlWhere
                    }//if_levelOne
                }//if_levelZero

                // Add Criteria
                $sql .= $sqlWhere;

                // ADD Order
                $sql .= " ORDER BY cl.name ";

                // Execute
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
    }//get_course_locations_list

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
    public static function get_sectors_locations_list($locations) {
        /* Variables    */
        global $DB;
        $sectors = array();
        $sql     = null;
        $rdo     = null;

        try {
            // Sectors List
            $sectors[0] = get_string('sel_sector','local_friadmin');

            if ($locations) {
                /* SQL Instruction  */
                $sql = " SELECT	DISTINCT 	
                                    rgc.id,
                                    rgc.name,
                                    rgc.industrycode
                         FROM		{report_gen_companydata}		rgc
                            JOIN	{report_gen_company_relation}	rg_cr	ON rg_cr.companyid 	= rgc.id
                            JOIN	{course_locations}			    cl		ON cl.levelone 		= rg_cr.parentid
                                                                            AND cl.id IN ($locations)
                         ORDER BY	rgc.industrycode, rgc.name ";

                // Execute
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
    }//get_sectors_locations_list

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
    public static function get_location_name($locationId) {
        /* Variables    */
        global $DB;
        $rdo = null;

        try {
            // Execute
            $rdo = $DB->get_record('course_locations',array('id' => $locationId),'name');
            if ($rdo) {
                return $rdo->name;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_location_name

    /**
     * @param           $locationId
     *
     * @return          null|stdClass
     * @throws          Exception
     *
     * @creationDate    23/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get location detail
     */
    public static function get_location_detail($locationId) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $infoLocation   = null;

        try {
            // Search Criteria
            $params = array();
            $params['location'] = $locationId;

            // SQL Instruction
            $sql = " SELECT	      cl.id,
                                  cl.name,
                                  levelone.name 	as 'muni',
                                  cl.floor,
                                  cl.room,
                                  cl.seats,
                                  cl.street,
                                  cl.postcode,
                                  cl.city,
                                  cl.contact,
                                  cl.phone,
                                  cl.email,
                                  cl.comments,
                                  cl.description,
                                  cl.urlmap,
                                  GROUP_CONCAT(DISTINCT cfo.courseid ORDER BY cfo.courseid) as 'courses'
                     FROM		  {course_locations}		cl
                        JOIN	  {report_gen_companydata}	levelone  ON  levelone.id 	= cl.levelone
                        LEFT JOIN {course_format_options}	cfo		  ON  cfo.value 	= cl.id
                                                                      AND cfo.name like '%location%'
                     WHERE		  cl.id = :location ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                // Info Location
                $infoLocation = new stdClass();
                $infoLocation->id   = $rdo->id;
                $infoLocation->name = $rdo->name;
                // Detail
                $infoLocation->detail      = get_string('location_floor','local_friadmin') . ': ' . $rdo->floor;
                $infoLocation->detail     .= "</br>";
                $infoLocation->detail     .= get_string('location_room','local_friadmin')  . ': ' . $rdo->room;
                $infoLocation->detail     .= "</br>";
                $infoLocation->detail     .= get_string('location_seats','local_friadmin') . ': ' . $rdo->seats;
                // Address
                $infoLocation->address     = $rdo->street;
                $infoLocation->address    .= "</br>";
                $infoLocation->address    .= $rdo->postcode . ' ' . $rdo->city;
                $infoLocation->address    .= "</br>";
                // URL Map
                $infoLocation->map         = $rdo->urlmap;
                // Courses
                $infoLocation->courses     = self::get_courses_names($rdo->courses);
                // Comments
                $infoLocation->comments    = $rdo->comments;
                // Description
                $infoLocation->description = $rdo->description;
                // Contact
                $infoLocation->contact     = $rdo->contact;
                if ($infoLocation->contact) {
                    $infoLocation->contact .= "</br>";
                }
                $infoLocation->contact    .= $rdo->email;
                if ($infoLocation->contact) {
                    $infoLocation->contact    .= "</br>";
                }
                $infoLocation->contact    .= $rdo->phone;
            }//if_Rdo

            return $infoLocation;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_location_detail

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
    public static function get_sectors_name($sectorsLst) {
        /* Variables    */
        global $DB;
        $sectorsName = null;
        $sql         = null;
        $rdo         = null;
        try {
            // SQL Instruction
            $sql = " SELECT		GROUP_CONCAT(DISTINCT CONCAT(rgc.industrycode,' - ', rgc.name) 
                                             ORDER BY rgc.industrycode, rgc.name SEPARATOR ', ') as 'sectors'
                     FROM		{report_gen_companydata}	rgc
                     WHERE      rgc.id IN ($sectorsLst) ";

            // Execute
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                $sectorsName = $rdo->sectors;
            }//if_rdo

            return $sectorsName;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_sectors_name

    /**
     * @param           $courseId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    04/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the deadline course
     */
    public static function deadline_course($courseId) {
        /* Variables */
        global $DB;
        $sql    = null;
        $params = null;
        $rdo    = null;

        try {
            // Search Criteria
            $params = array();
            $params['course']   = $courseId;
            $params['enrol']    = 'waitinglist';

            // SQL Instruction
            $sql = " SELECT	e.courseid,
                            IFNULL(e.customint1, 0) AS 'deadline'
                     FROM 	{enrol} e
                     WHERE 	e.status 	= 0
                        AND	e.enrol 	= :enrol
                        AND	e.courseid 	= :course  ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo->deadline;
            }else {
                return null;
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//deadline_course

    /**
     * @param           $courseId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    04/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get price of the course
     */
    public static function price_course($courseId) {
        /* Variables */
        global $DB;
        $sql        = null;
        $params     = null;
        $rdo        = null;
        $infoPrice  = null;
        $fields     = null;

        try {
            // Price
            $infoPrice = new stdClass();
            $infoPrice->internal = 0;
            $infoPrice->external = 0;

            // Search Criteria
            $params = array();
            $params['courseid']     = $courseId;
            $params['enrol']        = 'waitinglist';
            $params['status']       = 0;

            // Execute
            $fields = ' id, ' . FILED_COURSE_INTERNAL_PRICE  . ', ' . FILED_COURSE_EXTERNAL_PRICE;
            $rdo = $DB->get_record('enrol',$params, $fields);
            if ($rdo) {
                $infoPrice->internal = $rdo->{FILED_COURSE_INTERNAL_PRICE};
                $infoPrice->external = $rdo->{FILED_COURSE_EXTERNAL_PRICE};
            }//if_ro

            return $infoPrice;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//price_course

    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $coursesLst
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    23/02/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get names connected with the courses
     */
    private static function get_courses_names($coursesLst) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $coursesNames   = array();

        try {
            // SQL Instruction
            $sql = " SELECT     id,
                                fullname
                     FROM       {course}
                     WHERE      id IN ($coursesLst)
                     ORDER BY   fullname ";

            // Execute
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $coursesNames[$instance->id] = $instance->fullname;
                }//for_rdo
            }//if_rdo

            return $coursesNames;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_courses_names
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
        course_page::init_locations_sector();
        $format_options = course_get_format($course)->get_format_options();

        list($file_options,$context) = course_page::get_file_options();
        $file_options['accepted_types'] = array('image','web_image');

        $editor = new stdClass();
        $editor->pagegraphics = 0;

        file_prepare_standard_filemanager($editor, 'pagegraphics', $file_options, $context, 'course', 'pagegraphics', 0);
        foreach ($format_options as $name=>$option) {
            course_page::add_course_home_page_section($form,$name,true);
            course_page::print_format_options($form,$name,$option,$course->format);
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
