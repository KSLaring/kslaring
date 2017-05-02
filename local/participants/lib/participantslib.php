<?php
/**
 * Participants List - Library
 *
 * @package         local
 * @subpackage      participants
 * @copyright       2014    eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    06/07/2016
 * @author          eFaktor     (fbv)
 */

define('EXPORT_PDF','1');
define('EXPORT_EXCEL','2');

class ParticipantsList {
    /**********/
    /* PUBLIC */
    /**********/

    /**
     * @param           $contextId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Members of the course and no students
     */
    public static function get_not_members_participant_list($contextId) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $noParticipants = array();

        try {
            /* Search criteria */
            $params = array();
            $params['ctlevel'] = CONTEXT_COURSE;
            $params['context'] = $contextId;

            /* SQL Instruction  */
            $sql = " SELECT		DISTINCT ra.userid
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 	r.id			= ra.roleid
                                                            AND	r.archetype		IN ('manager','coursecreator','editingteacher','teacher')
                                                            AND r.shortname     = r.archetype
                        JOIN    {context}           ct      ON  ct.id			= ra.contextid
                                                            AND	ct.contextlevel	= :ctlevel
                     WHERE		ra.contextid 	= :context ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $noParticipants[$instance->userid] = $instance->userid;
                }//for_rdo
            }//iff_rdo

            return $noParticipants;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_not_members_participant_list

    /**
     * Description
     * Get participant list
     * 
     * @param           $courseId
     * @param           $notIn
     * @param           $start
     * @param           $limit
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_participant_list($courseId,$notIn,$sort,$field,$start=null,$limit=null) {
        /* Variables */
        global $DB;
        $rdo                = null;
        $sql                = null;
        $params             = null;
        $participantsLst    = array();
        $infoParticipant    = null;
        $selAttendance      = null;
        $joinAttendance     = null;
        $condAttendance     = null;
        $group              = null;

        try {
            // Search criteria
            $params = array();
            $params['course'] = $courseId;
            $params['wk']     = 3;
            $params['se']     = 2;
            $params['mu']     = 1;

            // Sort field
            $field = 'u.' . $field;

            // SQL instruction - get participant list
            $sql = " SELECT	DISTINCT 	
                                  u.id,
                                  u.firstname,
                                  u.lastname,
                                  u.email,
                                  cc.timecompleted,
                                  mu.name as 'municipality',
                                  se.name as 'sector',
                                  co.name as 'workplace'
                     FROM		  {user}						  u
                        JOIN	  {user_enrolments}	  		      ue  	ON 	ue.userid 			= u.id
                        JOIN	  {enrol}						  e		ON 	e.id 				= ue.enrolid
                                                                        AND	e.courseid 			= :course
                                                                        AND e.status 			= 0
                        -- Time completion
                        LEFT JOIN {course_completions}			  cc	ON	cc.course			= e.courseid
                                                                        AND cc.userid			= ue.userid
                        LEFT JOIN {enrol_waitinglist_queue}		  ew	ON  ew.waitinglistid	= e.id
                                                                        AND	ew.userid			= ue.userid
                        -- WORKAPLCE
                        LEFT JOIN {report_gen_companydata}	  	  co	ON	co.id 				= ew.companyid
                                                                        AND	co.hierarchylevel	= :wk
                        -- SECTOR
                        LEFT JOIN {report_gen_company_relation}   co_r	ON	co_r.companyid		= co.id
                        LEFT JOIN {report_gen_companydata}	  	  se	ON	se.id				= co_r.parentid
                                                                        AND	se.hierarchylevel	= :se
                        -- Municipality
                        LEFT JOIN {report_gen_company_relation}   mu_r	ON	mu_r.companyid		= se.id
                        LEFT JOIN {report_gen_companydata}	  	  mu	ON	mu.id				= mu_r.parentid
                                                                        AND	mu.hierarchylevel	= :mu
                     WHERE	      u.deleted = 0
                          AND     u.id NOT IN ($notIn)
                     GROUP BY     u.id  
                     ORDER BY     $field $sort ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);

            return $rdo;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_participant_list

    /**
     * @param           $courseId
     * @param           $notIn
     *
     * @return          int
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get total participants list
     */
    public static function get_total_participants($courseId,$notIn) {
        /* Variables */
        global $DB;
        $total      = 0;
        $rdo        = null;
        $sql        = null;
        $sqlFilter  = null;
        $params     = null;

        try {
            /* Search Criteria */
            $params = array();
            $params['course'] = $courseId;
            
            /* SQL Instruction */
            $sql = " SELECT	        count(u.id) as 'total'
                     FROM			{user}					u
                        JOIN		{user_enrolments}		ue 	ON 	ue.userid 	= u.id
                        JOIN		{enrol}					e	ON 	e.id 		= ue.enrolid
                                                                AND	e.courseid 	= :course
                                                                AND e.status 	= 0
                     WHERE	u.deleted = 0
                        AND u.id NOT IN ($notIn) ";
            
            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $total = $rdo->total;
            }//if_rdo
            
            return $total;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_total_participants

    /**
     * Description
     * Display participant list in the table
     *
     * @param           $participantList
     * @param           $sort
     * @param           $fieldSort
     * 
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     */
    public static function display_participant_list($participantList,$sort,$fieldSort) {
        /* Variables */
        $out    = '';

        try {
            // Participant list block
            $out .= html_writer::start_div('block_participants');
                // Participant list
                $out .= self::add_participants($participantList,$sort,$fieldSort);
            $out .= html_writer::end_div();//block_participants

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//display_participant_list

    /**
     * Description
     * Add header course info
     *
     * @param           $course
     * @param           $location
     * @param           $instructors
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    04/04/2017
     * @author          eFaktor     (fbv)
     */
    public static function display_participant_list_info_course($course,$location,$instructors) {
        /* Variables */
        $out    = '';

        try {
            // Participant list block
            $out .= html_writer::start_div('block_participants');
                // Course info
                $out .= self::add_info_course($course,$location,$instructors);
            $out .= html_writer::end_div();//block_participants

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//display_participant_list_info_course

    /**
     * Description
     * Add extra links.
     *
     * @param           $page
     * @param           $perpage
     * @param           $url
     * @param           $total
     *
     * @return          null|string
     * @throws          Exception
     *
     * @creationDate    04/04/2017
     * @author          eFaktor     (fbv)
     */
    public static function add_extra_links($page,$perpage,$url,$total) {
        // Variables
        global $SESSION,$OUTPUT;
        $extra = null;

        try {
            // Excel/PDF
            $extra .= html_writer::start_div('extra_lst_participants');
                // Excel
                $extra .= '<a href="'. $SESSION->xls_download .'" class="label_download">'.get_string('csvdownload','local_participants').'</a>';
                // PDF
                $extra .= '<a href="'. $SESSION->pdf_download .'" class="label_download">'.get_string('pdfdownload','local_participants').'</a>';
            $extra .= html_writer::end_div();//lst_participant

            // Paging bar
            $extra .= $OUTPUT->paging_bar($total, $page, $perpage, $url);

            return $extra;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_extra_links
    /**
     * @param           $courseId
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add back and tick button
     */
    public static function get_back_button($courseId) {
        /* Variables */
        $out    = '';
        $return = null;

        try {
            // Return to course view or home page
            $return = self::get_return_back_link($courseId);

            // button
            $out .= html_writer::start_tag('div',array('class' => 'div_button_participants'));
                $out .= html_writer::link($return,get_string('lnk_back','local_participants'),array('class' => 'button_participants'));
            $out .= html_writer::end_tag('div');

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_back_button

    /**
     * @param           $usersToTick
     * @param           $courseId
     *
     * @return          array
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Tick participants
     */
    public static function TickParticipants($usersToTick,$courseId) {
        /* Variables */
        global $DB,$USER;
        $today      = null;
        $instance   = null;
        $toTick     = null;
        $rdo        = null;
        $ticked     = array();

        try {
            /* Get Users To Tick    */
            if ($usersToTick) {
                $toTick = explode('_#_',$usersToTick);

                /* Attendance date */
                $today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

                /* Search criteria */
                $params = array();
                $params['courseid']         = $courseId;
                $params['attendacedate']    = $today;

                /* Tick users */
                foreach ($toTick as $userId) {
                    $instance = new stdClass();
                    $instance->courseid         = $courseId;
                    $instance->userid           = $userId;
                    $instance->attendacedate    = $today;
                    $instance->ticketby         = $USER->id;

                    /* Execute */
                    /* Check if already exist   */
                    $params['userid'] = $userId;
                    $rdo = $DB->get_record('course_attendance',$params);
                    if (!$rdo) {
                        $DB->insert_record('course_attendance',$instance);
                        $ticked[$userId] = $today;
                    }//if_rdo
                }//for
            }//if_usersToTick
            
            return $ticked;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//TickParticipants

    /**
     * Description
     * Get location
     *
     * @param           $courseId
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    20/09/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_location($courseId) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $params         = null;
        $sql            = null;

        try {
            // Search criteria
            $params = array();
            $params['name']     = 'course_location';
            $params['course']   = $courseId;

            // SQL Instruction to get locations
            $sql = " SELECT	      lo.id,
                                  lo.name,
                                  lo.floor,
                                  lo.room,
                                  lo.street,
                                  lo.postcode,
                                  lo.city
                     FROM		  {course_format_options}	cf
                        LEFT JOIN {course_locations}		lo ON lo.id = cf.value
                     WHERE	      cf.courseid = :course
                          AND	  cf.name     = :name ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_location

    /**
     * Description
     * Get instructors connected with the course
     *
     * @param           $courseId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    12/07/2016
     * @author          eFaktor     (fbv)
     */
    public static function get_instructors($courseId) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $sql            = null;
        $params         = null;
        $ctxSystem      = null;
        $ctxCourse      = null;
        $ctxCourseCat   = null;
        $context        = null;
        $instructors    = null;
        $info           = null;

        try {
            // Course context
            $context = context_course::instance($courseId);

            // Search criteria
            $params = array();
            $params['context']  = $context->id;
            $ctxSystem          = CONTEXT_SYSTEM;
            $ctxCourse          = CONTEXT_COURSE;
            $ctxCourseCat       = CONTEXT_COURSECAT;

            // SQL Instruction
            $sql = " SELECT		u.id,
                                CONCAT(u.firstname,' ',u.lastname) as 'user',
                                u.email
                     FROM		{user}				u
                        JOIN	{role_assignments}	ra		ON	ra.userid 		= u.id
                        JOIN	{role}				r		ON 	r.id			= ra.roleid
                                                            AND	r.archetype		IN ('editingteacher','teacher')
                                                            AND r.shortname     = r.archetype
                        JOIN    {context}           ct      ON  ct.id			= ra.contextid
                                                            AND	ct.contextlevel	IN ($ctxSystem,$ctxCourse,$ctxCourseCat)
                     WHERE		ra.contextid 	= :context ";

            // Execute
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    // Ad instructor
                    $instructors[$instance->id] = $instance->user;
                }//for_rdo
            }//if_Rdo

            return $instructors;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_instructors

    /**
     * Description
     * Download participant list
     *
     * @param           $participantsList
     * @param           $course
     * @param           $location
     * @param           $instructors
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor
     */
    public static function download_participants_list($participantsList,$course,$location,$instructors) {
        /* Variables */
        global $CFG;
        $row    = 0;
        $time   = null;
        $name   = null;
        $export = null;
        $my_xls = null;

        try {
            require_once($CFG->dirroot.'/lib/excellib.class.php');

            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $name = clean_filename('Participants_List_' . $course->fullname . '_' . $time . ".xls");
            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($name);

            $my_xls = $export->add_worksheet(get_string('pluginname','local_participants'));

            // Course name
            self::add_info_course_excel($course,$location,$instructors,$my_xls,$row);
            $row ++;

            // Participants table
            // Header
            self::add_participants_header_excel($my_xls,$row);
            $row ++;
            // Content
            self::add_participants_content_excel($participantsList,$my_xls,$row);

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//download_participants_list

    
    /***********/
    /* PRIVATE */
    /***********/

    /**
     * Description
     * Get return link from participant list
     *
     * @param           $courseId
     * @return          moodle_url|null
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor     (fbv)
     */
    private static function get_return_back_link($courseId) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql = null;
        $returnLnk  = null;

        try {
            // Search criteria
            $params = array();
            $params['course'] = $courseId;
            $params['name'] = 'homevisible';
            $params['value'] = 1;

            // SQL to check home page
            $sql = " SELECT   co.id
                     FROM	  {course_format_options}	co
                     WHERE	  co.courseid = :course
                        AND   co.name = :name
                        AND   co.value = :value ";

            // Execute
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                $returnLnk = new moodle_url('/local/course_page/home_page.php',array('id' => $courseId,'start' => 0));
            }else {
                $returnLnk = new moodle_url('/course/view.php',array('id' => $courseId));
            }//if_Rdo

            return $returnLnk;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_return_back_link

    /**
     * Description
     * Add info of the course
     *
     * @param           $course
     * @param           $location
     * @param           $instructors
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    12/07/2016
     * @author          eFaktor     (fbv)
     */
    private static function add_info_course($course,$location,$instructors) {
        /* Variables */
        $header         = '';
        $url            = null;
        $lnkCourse      = null;
        $urlLocation    = null;
        $lnkLocation    = null;
        $strInstructors = null;
        $lnkUser        = null;
        $urlUser        = null;

        try {
            // Course name
            $url        = new moodle_url('/course/view.php',array('id' => $course->id));
            $lnkCourse  = '<a href="' . $url. '">' . $course->fullname . '</a>';
            $header .= html_writer::start_div('course_info_one');
                $header .= html_writer::start_tag('label',array('class' => ' header_course'));
                    $header .= get_string('course') ;
                $header .= html_writer::end_tag('label');
            $header .= html_writer::end_div();//course_info_one
            $header .= html_writer::start_div('course_info_two');
                $header .= '<p class="info_course_value">' . $lnkCourse . '</p>';
            $header .= html_writer::end_div();//course_info_two

            // Course start date
            $header .= html_writer::start_div('course_info_one');
                $header .= html_writer::start_tag('label',array('class' => ' header_course'));
                    $header .=  get_string('date');
                $header .= html_writer::end_tag('label');
            $header .= html_writer::end_div();//course_info_one
            $header .= html_writer::start_div('course_info_two');
                $header .= '<p class="info_course_value">' . userdate($course->startdate,'%d.%m.%Y', 99, false) . '</p>';
            $header .= html_writer::end_div();//course_info_two

            // Location
            $header .= html_writer::start_div('course_info_one');
                $header .= html_writer::start_tag('label',array('class' => ' header_course'));
                    $header .= get_string('header_lo','local_participants');
                $header .= html_writer::end_tag('label');
            $header .= html_writer::end_div();//course_info_one
            $header .= html_writer::start_div('course_info_two');
                if ($location) {
                    $urlLocation  = new moodle_url('/local/friadmin/course_locations/view.php',array('id' => $location->id));
                    $lnkLocation  = '<a href="' . $urlLocation. '">' . $location->name . '</a>';

                    $header .= '<p class="info_course_value">' . $lnkLocation . '</p>';
                }else {
                    $header .= ' ';
                }
            $header .= html_writer::end_div();//course_info_two

            $header .= "</br>";

            // Get instructors
            if ($instructors) {
                $urlUser = new moodle_url('/user/profile.php');
                foreach ($instructors as $key => $info) {
                    $urlUser->param('id',$key);
                    $lnkUser = '<a href="' . $urlUser. '">' . $info . '</a>';

                    $instructors[$key] = $lnkUser;
                }
                $strInstructors = implode('</br>',$instructors);
            }else {
                $strInstructors = ' - ';
            }

            //Add instructors
            $header .= html_writer::start_div('course_info_one');
                $header .= html_writer::start_tag('label',array('class' => ' header_course'));
                    $header .=  get_string('str_instructors','local_participants');
                $header .= html_writer::end_tag('label');
            $header .= html_writer::end_div();//course_info_one
            $header .= html_writer::start_div('course_info_two');
                $header .= '<p class="info_course_value">' . $strInstructors . '</p>';
            $header .= html_writer::end_div();//course_info_two

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_info_course

    /**
     * Description
     * Add participant table
     *
     * @param           $participantsList
     * @param           $sort
     * @param           $fieldSort
     * 
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     */
    private static function add_participants($participantsList,$sort,$fieldSort) {
        /* Variables */
        $content = '';
        $csv_url = null;

        try {
            // Add participant list
            $content .= html_writer::start_div('lst_participants');
                // Participants table
                $content .= html_writer::start_tag('table');
                    // Header
                    $content .= self::add_header_participants_table($sort,$fieldSort);
                    // Content
                    $content .= self::add_content_participants_table($participantsList);
                $content .= html_writer::end_tag('table');
            $content .= html_writer::end_div();//lst_participants

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants

    /**
     * Description
     * Add header to the participant table
     *
     * @param           $sort
     * @param           $fieldSort
     * 
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     */
    private static function add_header_participants_table($sort,$fieldSort) {
        /* Variables */
        $header = '';
        $strFirstname   = null;
        $strLastname    = null;
        $strMail        = null;
        $strcompletion  = null;
        $strMuni        = null;
        $strSector      = null;
        $strWorkspace   = null;
        $dirFirstName   = null;
        $dirLastName    = null;

        try {
            // Set order
            switch ($fieldSort) {
                case 'firstname':
                    $dirFirstName   = $sort;
                    $dirLastName    = 'ASC';

                    break;

                case 'lastname':
                    $dirFirstName = 'ASC';
                    $dirLastName  = $sort;

                    break;
                default:
                    $dirFirstName = 'ASC';
                    $dirLastName  = 'ASC';

                    break;
            }//fieldSort

            // Headers
            $strFirstname   = get_string('firstname');
            $strLastname    = get_string('lastname');
            $strMail        = get_string('email','local_participants');
            $strcompletion  = get_string('header_completed','local_participants');
            $strMuni        = get_string('header_mu','local_participants');
            $strSector      = get_string('header_se','local_participants');
            $strWorkplace   = get_string('header_wk','local_participants');

            $header .=  html_writer::start_tag('thead');
                $header .=  html_writer::start_tag('tr',array('class' => 'header_participants'));
                    // Firstname
                    $header .= html_writer::start_tag('th',array('class' => 'user'));
                        $header .= "<button class='button_order' id='firstname' name='firstname' value='" . $dirFirstName. "'>" . $strFirstname . "</button>";
                    $header .= html_writer::end_tag('th');
                    // Lastname
                    $header .= html_writer::start_tag('th',array('class' => 'user'));
                        $header .= "<button class='button_order' id='lastname' name='lastname' value='" . $dirLastName. "'>" . $strLastname . "</button>";
                    $header .= html_writer::end_tag('th');
                    // eMail
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strMail;
                    $header .= html_writer::end_tag('th');
                    // Municipality
                    $header .= html_writer::start_tag('th',array('class' => 'muni'));
                        $header .= $strMuni;
                    $header .= html_writer::end_tag('th');
                    // Sector
                    $header .= html_writer::start_tag('th',array('class' => 'sector'));
                        $header .= $strSector;
                    $header .= html_writer::end_tag('th');
                    // Workplace
                    $header .= html_writer::start_tag('th',array('class' => 'sector'));
                        $header .= $strWorkplace;
                    $header .= html_writer::end_tag('th');
                    // Completed
                    $header .= html_writer::start_tag('th',array('class' => 'attend'));
                        $header .= $strcompletion;
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('thead');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_header_participants_table

    /**
     * Description
     * Add content to the participants table
     *
     * @param           $participantsList
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     */
    private static function add_content_participants_table($participantsList) {
        /* Variables */
        $content    = '';
        $strFirstname   = null;
        $strLastname    = null;
        $strMail        = null;
        $strcompletion  = null;
        $strMuni        = null;
        $strSector      = null;
        $strWorkspace   = null;

        try {
            // Headers
            $strFirstname   = get_string('firstname');
            $strLastname    = get_string('lastname');
            $strMail        = get_string('email','local_participants');
            $strcompletion  = get_string('header_completed','local_participants');
            $strMuni        = get_string('header_mu','local_participants');
            $strSector      = get_string('header_se','local_participants');
            $strWorkplace   = get_string('header_wk','local_participants');

            // Add participants to the table
            foreach ($participantsList as $participant) {
                $content .= html_writer::start_tag('tr');
                    // Firstname
                    $content .= html_writer::start_tag('td',array('class' => 'user','scope' => 'row','data-th' => $strFirstname));
                        $content .= $participant->firstname;
                    $content .= html_writer::end_tag('td');
                    // Lastname
                    $content .= html_writer::start_tag('td',array('class' => 'user','data-th' => $strLastname));
                        $content .= $participant->lastname;
                    $content .= html_writer::end_tag('td');
                    // eMail
                    $content .= html_writer::start_tag('td',array('class' => 'info','data-th' => $strMail));
                        $content .= $participant->email;
                    $content .= html_writer::end_tag('td');
                    // Municipality
                    $content .= html_writer::start_tag('td',array('class' => 'muni','data-th' => $strMuni));
                        $content .= ($participant->municipality ? $participant->municipality: '&nbsp;');
                    $content .= html_writer::end_tag('td');
                    // Sector
                    $content .= html_writer::start_tag('td',array('class' => 'sector','data-th' => $strSector));
                        $content .= ($participant->sector ? $participant->sector: '&nbsp;');
                    $content .= html_writer::end_tag('td');
                    // Workplace
                    $content .= html_writer::start_tag('td',array('class' => 'sector','data-th' => $strWorkplace));
                        $content .= ($participant->workplace ? $participant->workplace : '&nbsp;');
                    $content .= html_writer::end_tag('td');
                    // Completions
                    $content .= html_writer::start_tag('td',array('class' => 'attend','data-th' => $strcompletion));
                        $content .= ($participant->timecompleted ? userdate($participant->timecompleted ,'%d.%m.%Y', 99, false): '&nbsp;');
                    $content .= html_writer::end_tag('td');
                $content .= html_writer::end_tag('tr');
            }//participant_list

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_content_participants_table


    /**
     * @param           $course
     * @param           $location
     * @param           $instructors
     * @param           $my_xls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    12/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add course info header to excel report
     */
    private static function add_info_course_excel($course,$location,$instructors,&$my_xls,&$row) {
        /* Variables */
        $col = 0;

        try {
            // Course name
            $my_xls->write($row, $col, get_string('course'),array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $row ++;
            $col = 0;
            $my_xls->write($row, $col, $course->fullname,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $row ++;

            // Date
            $my_xls->write($row, $col, get_string('date'),array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $row++;
            $col=0;
            $my_xls->write($row, $col, userdate($course->startdate,'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $col=0;
            $row ++;

            // Location
            $my_xls->write($row, $col, get_string('header_lo','local_participants'),array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $row ++;
            $col = 0;
            if ($location) {
                $address     = $location->street;
                $address    .= "\n";
                $address    .= $location->postcode . ' ' . $location->city;
                $address    .= "\n";
                $my_xls->write($row, $col, $address,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,40);
                $col=0;
                $row ++;
            }else {
                $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'left'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,20);
                $row ++;
                $col = 0;
                $row ++;
            }

            // Instructors
            $my_xls->write($row, $col, get_string('str_instructors','local_participants'),array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $row ++;
            $col = 0;
            if ($instructors) {
                foreach ($instructors as $key => $info) {
                    $my_xls->write($row, $col, $info,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'left'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);
                    $row++;
                    $col=0;
                    $row ++;
                }//instructor
            }else {
                $my_xls->write($row, $col, '-',array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'left'));
                $my_xls->merge_cells($row,$col,$row,$col+5);
                $my_xls->set_row($row,20);
                $row ++;
                $col = 0;
                $row ++;
            }//if_instructors


            $row ++;
            $my_xls->merge_cells($row,0,$row,19);
            $row ++;
            $my_xls->merge_cells($row,0,$row,19);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_info_course_excel

    /**
     * Description
     * Add the header of the table to the excel report
     *
     * @param           $my_xls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor     (fbv)
     */
    private static function add_participants_header_excel(&$my_xls,$row) {
        /* Variables */
        $col            = 0;
        $strFirstname   = null;
        $strLastname    = null;
        $strMail        = null;
        $strcompletion  = null;
        $strSector      = null;
        $strMuni        = null;
        $strWorkplace   = null;

        try {
            $strFirstname   = get_string('firstname');
            $strLastname    = get_string('lastname');
            $strMail        = get_string('email','local_participants');
            $strcompletion  = get_string('header_completed','local_participants');
            $strMuni        = get_string('header_mu','local_participants');
            $strSector      = get_string('header_se','local_participants');
            $strWorkplace   = get_string('header_wk','local_participants');

            // Firstname
            $my_xls->write($row, $col, $strFirstname,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+3);
            $my_xls->set_row($row,20);

            // Lastname
            $col += 4;
            $my_xls->write($row, $col, $strLastname,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+3);
            $my_xls->set_row($row,20);

            // eMail
            $col += 4;
            $my_xls->write($row, $col, $strMail,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // Municipality
            $col += 6;
            $my_xls->write($row, $col, $strMuni,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            // Sector
            $col += 6;
            $my_xls->write($row, $col, $strSector,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+6);
            $my_xls->set_row($row,20);

            // Workplace
            $col += 7;
            $my_xls->write($row, $col, $strWorkplace,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+6);
            $my_xls->set_row($row,20);

            // Completion
            $col += 7;
            $my_xls->write($row, $col, $strcompletion,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_header_excel

    /**
     * Description
     * Add the content of the table to the excel report
     *
     * @param           $participantList
     * @param           $my_xls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor     (fbv)
     */
    private static function add_participants_content_excel($participantList,&$my_xls,&$row) {
        /* Variables */
        $col            = 0;
        $last           = null;
        $workplaces     = null;
        $setRow         = null;
        $strUser        = null;
        $completion     = null;

        try {
            if ($participantList) {
                foreach ($participantList as $participant) {
                    if ($participant->workplace) {
                        $setRow = 15 * count($participant->workplace);
                    }else {
                        $setRow = 15;
                    }

                    // Firstname
                    $my_xls->write($row, $col, $participant->firstname,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+3);
                    $my_xls->set_row($row,$setRow);

                    // Lastname
                    $col += 4;
                    $my_xls->write($row, $col, $participant->lastname,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+3);
                    $my_xls->set_row($row,$setRow);

                    // eMail
                    $col += 4;
                    $my_xls->write($row, $col, $participant->email,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,$setRow);

                    // Municipality
                    $col += 6;
                    $my_xls->write($row, $col, $participant->municipality,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,$setRow);

                    // Sector
                    $col += 6;
                    $my_xls->write($row, $col, $participant->sector,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+6);
                    $my_xls->set_row($row,$setRow);

                    // Workplace
                    $col += 7;
                    $my_xls->write($row, $col, $participant->workplace,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+6);
                    $my_xls->set_row($row,$setRow);

                    // Completion
                    $completion = " ";
                    if ($participant->timecompleted) {
                        $completion = userdate($participant->timecompleted ,'%d.%m.%Y', 99, false);
                    }
                    $col += 7;
                    $my_xls->write($row, $col, $completion,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,$setRow);

                    $row ++;
                    $col = 0;
                }//for_participants
            }//if_participantList
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_participants_content_excel
}//ParticipantsList