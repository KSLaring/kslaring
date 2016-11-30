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
    public static function GetNotMembersParticipantList($contextId) {
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
    }//GetNotMembersParticipantList

    /**
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
     *
     * Description
     * Get participant list
     */
    public static function GetParticipantList($courseId,$notIn,$sort,$field,$start=null,$limit=null) {
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
            /* Search Criteria */
            $params = array();
            $params['course'] = $courseId;

            /* Sort Field */
            $field = 'u.' . $field;

            /* SQL Instruction */
            $sql = " SELECT	DISTINCT 	u.id,
                                        u.firstname,
                                        u.lastname,
                                        u.email,
                                        se.name as 'sector',
                                        co.name as 'workplace'
                     FROM			{user}						    u
                        JOIN		{user_enrolments}	  		    ue  	ON 	ue.userid 			= u.id
                        JOIN		{enrol}						    e		ON 	e.id 				= ue.enrolid
                                                                            AND	e.courseid 			= :course
                                                                            AND e.status 			= 0
                        LEFT JOIN	{enrol_waitinglist_queue}		ew		ON  ew.waitinglistid	= e.id
                                                                            AND	ew.userid			= ue.userid
                        -- WORKAPLCE
                        LEFT JOIN	{report_gen_companydata}	  	co		ON	co.id 				= ew.companyid
                                                                            AND	co.hierarchylevel	= 3
                        -- SECTOR
                        LEFT JOIN	{report_gen_company_relation}   co_r	ON	co_r.companyid		= co.id
                        LEFT JOIN	{report_gen_companydata}	  	se		ON	se.id				= co_r.parentid
                                                                            AND	se.hierarchylevel	= 2
                     WHERE	u.deleted = 0
                        AND u.id NOT IN ($notIn)
                     GROUP BY u.id 
                     ORDER BY $field $sort ";

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params,$start,$limit);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Participant */
                    $infoParticipant = new stdClass();
                    $infoParticipant->id        = $instance->id;
                    $infoParticipant->firstname = $instance->firstname;
                    $infoParticipant->lastname  = $instance->lastname;
                    $infoParticipant->email     = $instance->email;
                    $infoParticipant->sector    = $instance->sector;
                    $infoParticipant->workplace = $instance->workplace;
                    
                    /* Add Participant */
                    $participantsLst[$instance->id] = $infoParticipant;
                }
            }//if_rdo

            return $participantsLst;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetParticipantList

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
    public static function GetTotalParticipants($courseId,$notIn) {
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
    }//GetTotalParticipants

    /**
     * @param           $participantList
     * @param           $course
     * @param           $location
     * @param           $instructors
     * @param           $sort
     * @param           $fieldSort
     * 
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Display participant list in the table
     */
    public static function DisplayParticipantList($participantList,$course,$location,$instructors,$sort,$fieldSort) {
        /* Variables */
        global $OUTPUT;
        $out    = '';

        try {
            /* Display Participant List */
            $out .= html_writer::start_div('block_participants');
                /* Course Info  */
                 $out .= self::AddInfoCourse($course,$location,$instructors);
            
                /* Participants List    */
                $out .= self::AddParticipants($participantList,$sort,$fieldSort);
            $out .= html_writer::end_div();//block_participants

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//DisplayParticipantList

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
    public static function GetBackButton($courseId) {
        /* Variables */
        $out    = '';
        $return = null;

        try {
            /* Return */
            /* Course View / Home Page */
            $return = self::getReturnBackLink($courseId);

            /* Link Button to back */
            $out .= html_writer::start_tag('div',array('class' => 'div_button_participants'));
                $out .= html_writer::link($return,get_string('lnk_back','local_participants'),array('class' => 'button_participants'));
            $out .= html_writer::end_tag('div');

            return $out;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetBackButton

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
     * @param           $courseId
     *
     * @return          mixed|null
     * @throws          Exception
     *
     * @creationDate    20/09/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get location
     */
    public static function GetLocation($courseId) {
        /* Variables */
        global $DB;
        $rdo            = null;
        $params         = null;
        $sql            = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['name']     = 'course_location';
            $params['course']   = $courseId;

            /* SQL Instruction  */
            $sql = " SELECT	lo.id,
                            lo.name,
                            lo.floor,
                            lo.room,
                            lo.street,
                            lo.postcode,
                            lo.city
                     FROM			{course_format_options}	cf
                        LEFT JOIN	{course_locations}		lo ON lo.id = cf.value
                     WHERE	cf.courseid = :course
                        AND	cf.name     = :name ";

            /* Execute */
            $rdo = $DB->get_record_sql($sql,$params);
            if ($rdo) {
                return $rdo;
            }else {
                return null;
            }
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetLocation

    /**
     * @param           $courseId
     *
     * @return          null
     * @throws          Exception
     *
     * @creationDate    12/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get instructors connected with the course
     */
    public static function GetInstructors($courseId) {
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
            /* Course Context   */
            $context = context_course::instance($courseId);

            /* Search Criteria  */
            $params = array();
            $params['context']  = $context->id;
            $ctxSystem          = CONTEXT_SYSTEM;
            $ctxCourse          = CONTEXT_COURSE;
            $ctxCourseCat       = CONTEXT_COURSECAT;

            /* SQL Instruction */
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

            /* Execute */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    /* Add instructor   */
                    $instructors[$instance->id] = $instance->user;
                }//for_rdo
            }//if_Rdo

            return $instructors;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//GetInstructors

    /**
     * @param           $participantsList
     * @param           $course
     * @param           $location
     * @param           $instructors
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor
     *
     * Description
     * Download participant list
     */
    public static function Download_ParticipantsList($participantsList,$course,$location,$instructors) {
        /* Variables */
        $row    = 0;
        $col    = 0;
        $time   = null;
        $name   = null;
        $export = null;
        $my_xls = null;

        try {
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $name = clean_filename('Participants_List_' . $course->fullname . '_' . $time . ".xls");
            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($name);

            $my_xls = $export->add_worksheet(get_string('pluginname','local_participants'));

            /* Course Name          */
            self::AddInfoCourse_Excel($course,$location,$instructors,$my_xls,$row);
            $row ++;

            /* Participants Table   */
            /* Header   */
            self::AddParticipants_HeaderExcel($my_xls,$row);
            $row ++;
            /* Content  */
            self::AddParticipants_ContentExcel($participantsList,$my_xls,$row);

            $export->close();
            exit;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//Download_ParticipantsList

    
    /***********/
    /* PRIVATE */
    /***********/

    /**
     * @param           $courseId
     * @return          moodle_url|null
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get return link from participant list
     */
    private static function getReturnBackLink($courseId) {
        /* Variables */
        global $DB;
        $rdo        = null;
        $sql = null;
        $returnLnk  = null;

        try {
            /* Search Criteria */
            $params = array();
            $params['course'] = $courseId;
            $params['name'] = 'homevisible';
            $params['value'] = 1;

            /* Check Home page Visible  */
            $sql = " SELECT   co.id
                     FROM	  {course_format_options}	co
                     WHERE	  co.courseid = :course
                        AND   co.name = :name
                        AND   co.value = :value ";

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
    }//getReturnBackLink

    /**
     * @param           $course
     * @param           $location
     * @param           $instructors
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    12/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add info of the course
     */
    private static function AddInfoCourse($course,$location,$instructors) {
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
            /* Course Name      */
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

            /* Date */
            $header .= html_writer::start_div('course_info_one');
                $header .= html_writer::start_tag('label',array('class' => ' header_course'));
                    $header .=  get_string('date');
                $header .= html_writer::end_tag('label');
            $header .= html_writer::end_div();//course_info_one
            $header .= html_writer::start_div('course_info_two');
                $header .= '<p class="info_course_value">' . userdate(time(),'%d.%m.%Y', 99, false) . '</p>';
            $header .= html_writer::end_div();//course_info_two

            /* Location */
            $header .= html_writer::start_div('course_info_one');
                $header .= html_writer::start_tag('label',array('class' => ' header_course'));
                    $header .= get_string('header_lo','local_participants');
                $header .= html_writer::end_tag('label');
            $header .= html_writer::end_div();//course_info_one
            /* Location Name    */
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

            /* Get Instructors */
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

            /* Instructors  */
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
    }//AddInfoCourse

    /**
     * @param           $participantsList
     * @param           $sort
     * @param           $fieldSort
     * 
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add participant table
     */
    private static function AddParticipants($participantsList,$sort,$fieldSort) {
        /* Variables */
        global $SESSION;
        $content = '';
        $csv_url = null;

        try {
            $csv_url  = $SESSION->url_download;
            /* Add Participants List    */
            $content .= html_writer::start_div('lst_participants');
                $content .= '<a href="'.$csv_url->out().'" class="label_download">'.get_string('csvdownload','local_participants').'</a>';
                $content .= "</br>";
            
                /* Table    */
                $content .= html_writer::start_tag('table',array('class' => 'generaltable'));
                    /* Header   */
                    $content .= self::AddHeader_ParticipantsTable($sort,$fieldSort);
            
                    /* Content  */
                    $content .= self::AddContent_ParticipantsTable($participantsList);
                $content .= html_writer::end_tag('table');

                $content .= "</br>";
                $content .= '<a href="'.$csv_url->out().'" class="label_download">'.get_string('csvdownload','local_participants').'</a>';
            $content .= html_writer::end_div();//lst_participants

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddParticipants

    /**
     * @param           $sort
     * @param           $fieldSort
     * 
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add header to the participant table
     */
    private static function AddHeader_ParticipantsTable($sort,$fieldSort) {
        /* Variables */
        $header = '';
        $strFirstname   = null;
        $strLastname    = null;
        $strMail        = null;
        $strSector      = null;
        $strWorkspace   = null;
        $dirFirstName   = null;
        $dirLastName    = null;

        try {
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

            /* Headers  */
            $strFirstname   = get_string('firstname');
            $strLastname    = get_string('lastname');
            $strMail        = get_string('email','local_participants');
            $strSector      = get_string('header_se','local_participants');
            $strWorkplace   = get_string('header_wk','local_participants');

            $header .=  html_writer::start_tag('thead');
                $header .=  html_writer::start_tag('tr',array('class' => 'header_participants'));
                    /* Firstname */
                    $header .= html_writer::start_tag('th',array('class' => 'user'));
                        $header .= "<button class='button_order' id='firstname' name='firstname' value='" . $dirFirstName. "'>" . $strFirstname . "</button>";
                    $header .= html_writer::end_tag('th');
                    /* Lastname             */
                    $header .= html_writer::start_tag('th',array('class' => 'user'));
                        $header .= "<button class='button_order' id='lastname' name='lastname' value='" . $dirLastName. "'>" . $strLastname . "</button>";
                    $header .= html_writer::end_tag('th');
                    /* eMail            */
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strMail;
                    $header .= html_writer::end_tag('th');
                    /* Sector   */
                    $header .= html_writer::start_tag('th',array('class' => 'sector'));
                        $header .= $strSector;
                    $header .= html_writer::end_tag('th');
                    /* Workplace */
                    $header .= html_writer::start_tag('th',array('class' => 'sector'));
                        $header .= $strWorkplace;
                    $header .= html_writer::end_tag('th');
                $header .= html_writer::end_tag('tr');
            $header .= html_writer::end_tag('thead');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddHeader_ParticipantsTable

    /**
     * @param           $participantsList
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add content to the participants table
     */
    private static function AddContent_ParticipantsTable($participantsList) {
        /* Variables */
        $content    = '';
        $name       = null;
        $checked    = null;
        $last       = null;
        $seWK       = null;
        $sectors    = null;
        $workplaces = null;

        try {
            foreach ($participantsList as $participant) {
                $content .= html_writer::start_tag('tr');
                    /* firstname     */
                    $content .= html_writer::start_tag('td',array('class' => 'user'));
                        $content .= $participant->firstname;
                    $content .= html_writer::end_tag('td');
                    /* lastname     */
                    $content .= html_writer::start_tag('td',array('class' => 'user'));
                        $content .= $participant->lastname;
                    $content .= html_writer::end_tag('td');
                    /* eMail    */
                    $content .= html_writer::start_tag('td',array('class' => 'info'));
                        $content .= $participant->email;
                    $content .= html_writer::end_tag('td');
                    /* Sector */
                    $content .= html_writer::start_tag('td',array('class' => 'sector'));
                        $content .= $participant->sector;
                    $content .= html_writer::end_tag('td');
                    /* Workplace    */
                    $content .= html_writer::start_tag('td',array('class' => 'sector'));
                        $content .= $participant->workplace;
                    $content .= html_writer::end_tag('td');
                $content .= html_writer::end_tag('tr');
            }//participant_list

            return $content;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddContent_ParticipantsTable


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
    private static function AddInfoCourse_Excel($course,$location,$instructors,&$my_xls,&$row) {
        /* Variables */
        $col = 0;

        try {
            /* Course Name  */
            $my_xls->write($row, $col, get_string('course'),array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $row ++;
            $col = 0;
            $my_xls->write($row, $col, $course->fullname,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $row ++;

            /* Date */
            $my_xls->write($row, $col, get_string('date'),array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $row++;
            $col=0;
            $my_xls->write($row, $col, userdate(time(),'%d.%m.%Y', 99, false),array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);
            $col=0;
            $row ++;

            /* Location     */
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

            /* Instructors  */
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
    }//AddInfoCourse_Excel

    /**
     * @param           $my_xls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the header of the table to the excel report
     */
    private static function AddParticipants_HeaderExcel(&$my_xls,$row) {
        /* Variables */
        $col            = 0;
        $strFirstname   = null;
        $strLastname    = null;
        $strMail        = null;
        $strSeWK        = null;
        $strAttend      = null;

        try {
            $strFirstname   = get_string('firstname');
            $strLastname    = get_string('lastname');
            $strMail        = get_string('email','local_participants');
            $strAttend      = get_string('attendance','local_participants');
            $strSector      = get_string('header_se','local_participants');
            $strWorkplace   = get_string('header_wk','local_participants');

            /* Firstname Header      */
            $my_xls->write($row, $col, $strFirstname,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+3);
            $my_xls->set_row($row,20);

            /* Lastname Header      */
            $col += 4;
            $my_xls->write($row, $col, $strLastname,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+3);
            $my_xls->set_row($row,20);

            /* Mail Header      */
            $col += 4;
            $my_xls->write($row, $col, $strMail,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Sector   */
            $col += 6;
            $my_xls->write($row, $col, $strSector,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+6);
            $my_xls->set_row($row,20);

            /* Workplace    */
            $col += 7;
            $my_xls->write($row, $col, $strWorkplace,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+6);
            $my_xls->set_row($row,20);

            /* Last Attended    */
            //$col += 7;
            //$my_xls->write($row, $col, $strAttend,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left','align' => 'center'));
            //$my_xls->merge_cells($row,$col,$row,$col+1);
            //$my_xls->set_row($row,20);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddParticipants_HeaderExcel

    /**
     * @param           $participantList
     * @param           $my_xls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the content of the table to the excel report
     */
    private static function AddParticipants_ContentExcel($participantList,&$my_xls,&$row) {
        /* Variables */
        $col            = 0;
        $last           = null;
        $workplaces     = null;
        $setRow         = null;
        $strUser        = null;

        try {
            if ($participantList) {
                foreach ($participantList as $participant) {
                    if ($participant->workplace) {
                        $setRow = 15 * count($participant->workplace);
                    }else {
                        $setRow = 15;
                    }


                    /* Firstname        */
                    $my_xls->write($row, $col, $participant->firstname,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+3);
                    $my_xls->set_row($row,$setRow);

                    /* lastname         */
                    $col += 4;
                    $my_xls->write($row, $col, $participant->lastname,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+3);
                    $my_xls->set_row($row,$setRow);

                    /* eMail            */
                    $col += 4;
                    $my_xls->write($row, $col, $participant->email,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,$setRow);

                    /* Sector           */
                    $col += 6;
                    $my_xls->write($row, $col, $participant->sector,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+6);
                    $my_xls->set_row($row,$setRow);

                    /* Workplace        */
                    $col += 7;
                    $my_xls->write($row, $col, $participant->workplace,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    $my_xls->merge_cells($row,$col,$row,$col+6);
                    $my_xls->set_row($row,$setRow);

                    /* Attended         */
                    //$col += 7;
                    //$my_xls->write($row, $col, '',array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'top'));
                    //$my_xls->merge_cells($row,$col,$row,$col+1);
                    //$my_xls->set_row($row,$setRow);

                    $row ++;
                    $col = 0;
                }//for_participants
            }//if_participantList
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddParticipants_ContentExcel
}//ParticipantsList