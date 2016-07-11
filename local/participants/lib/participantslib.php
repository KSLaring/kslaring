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
     * @param           $courseId
     *
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor (fbv)
     *
     * Description
     * Initialize participants
     */
    public static function InitParticipants($courseId) {
        /* Variables */
        global $PAGE;
        $options    = null;
        $hash       = null;
        $jsModule   = null;
        $name       = null;
        $path       = null;
        $requires   = null;
        $strings    = null;
        $grpOne     = null;
        $grpTwo     = null;
        $grpThree   = null;


        try {
            /* Initialise variables */
            $name       = 'participant';
            $path       = '/local/participants/js/attend.js';
            $requires   = array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification');
            $grpOne     = array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%');
            $grpTwo     = array('nomatchingusers', 'moodle', '%%SEARCHTERM%%');
            $grpThree   = array('none', 'moodle');
            $strings    = array($grpOne,$grpTwo,$grpThree);

            /* Initialise js module */
            $jsModule = array('name'        => $name,
                              'fullpath'    => $path,
                              'requires'    => $requires,
                              'strings'     => $strings
                             );


            $PAGE->requires->js_init_call('M.core_user.init_participants_list',
                                          array($courseId),
                                          false,
                                          $jsModule
                                         );
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//InitParticipants

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
            $sql = " SELECT		ra.userid
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
     * @param           null $filter
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
    public static function GetParticipantList($courseId,$notIn,$filter = null,$start=null,$limit=null) {
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
            if ($filter) {
                /* Criteria */
                $params['from'] = $filter->from;
                $params['to']   = $filter->to;

                /* SQL Filter   */
                $selAttendance  = " ca.attendacedate as 'attendacedate' ";
                $joinAttendance = " JOIN ";
                $condAttendance = " AND ca.attendacedate BETWEEN :from AND :to ";
                $group          = "";
            }else {
                /* SQL */
                $selAttendance  = " MAX(ca.attendacedate) as 'attendacedate' ";
                $joinAttendance = " LEFT JOIN ";
                $group          = " GROUP BY u.id ";
                $condAttendance = "";
            }//if_filter

            /* SQL Instruction */
            $sql = " SELECT	u.id,
                            u.firstname,
                            u.lastname,
                            u.email,
                            $selAttendance
                     FROM			{user}					  u
                        JOIN		{user_enrolments}		  ue  ON 	ue.userid 	= u.id
                        JOIN		{enrol}					  e	  ON 	e.id 		= ue.enrolid
                                                                  AND	e.courseid 	= :course
                                                                  AND   e.status 	= 0
                        -- LAST ATTENDACE
                        $joinAttendance	{course_attendance}	  ca  ON 	ca.courseid = e.courseid
                                                                  AND   ca.userid 	= ue.userid
                                                                  $condAttendance
                     WHERE	u.deleted = 0
                        AND u.id NOT IN ($notIn)
                     $group
                     ORDER BY u.firstname, u.lastname ";

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
                    $infoParticipant->last      = $instance->attendacedate;

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
     * @param           null $filter
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
    public static function GetTotalParticipants($courseId,$notIn,$filter=null) {
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
            if ($filter) {
                /* Criteria */
                $params['from'] = $filter->from;
                $params['to']   = $filter->to;

                $sqlFilter = " JOIN	{course_attendance}	ca	ON 	ca.courseid = e.courseid
								                            AND ca.userid 	= ue.userid
										                    AND ca.attendacedate BETWEEN :from AND :to ";
            }else {
                $sqlFilter = "";
            }//if_filter

            $sql = " SELECT	        count(u.id) as 'total'
                     FROM			{user}					u
                        JOIN		{user_enrolments}		ue 	ON 	ue.userid 	= u.id
                        JOIN		{enrol}					e	ON 	e.id 		= ue.enrolid
                                                                AND	e.courseid 	= :course
                                                                AND e.status 	= 0
                        $sqlFilter
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
     * @param           $filtered
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
    public static function DisplayParticipantList($participantList,$course,$filtered) {
        /* Variables */
        global $OUTPUT;
        $out    = '';

        try {
            /* Display Participant List */
            $out .= html_writer::start_div('block_participants');
                /* Course Info  */
                $out .= self::AddNameCourse($course->id,$course->fullname);

                /* Participants List    */
                $out .= self::AddParticipants($participantList,$filtered);
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
    public static function GetBackAndTickButton($courseId) {
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
                $out .= "<button class='button_participants' id='id_tick'>" . get_string('lnk_tick','local_participants') . "</button>";
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
     * @param           $participantsList
     * @param           $course
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor
     *
     * Description
     * Download participant list
     */
    public static function Download_ParticipantsList($participantsList,$course) {
        /* Variables */
        $row    = 0;
        $col    = 0;
        $time   = null;
        $name   = null;
        $export = null;
        $my_xls = null;

        try {
            $time = userdate(time(),'%d.%m.%Y', 99, false);
            $name = clean_filename('Participants_List_' . $course . '_' . $time . ".xls");
            // Creating a workbook
            $export = new MoodleExcelWorkbook("-");
            // Sending HTTP headers
            $export->send($name);

            $my_xls = $export->add_worksheet(get_string('pluginname','local_participants'));

            /* Course Name          */
            self::AddNameCourse_Excel($course,$my_xls,$row);

            /* Participants Table   */
            $row ++;
            $row ++;
            $row ++;
            /* Header   */
            self::AddParticipants_HeaderExcel($my_xls,$row);
            $row++;
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
     * @param           $courseId
     * @param           $courseName
     *
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the name of the course to the table
     */
    private static function AddNameCourse($courseId,$courseName) {
        /* Variables */
        $header = '';

        try {
            $url        = new moodle_url('/course/view.php',array('id' => $courseId));
            $lnkCourse  = '<a href="' . $url. '">' . $courseName . '</a>';

            $header .= html_writer::start_tag('label',array('class' => ' header_course'));
                $header .= $lnkCourse ;
            $header .= html_writer::end_tag('label');

            return $header;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddNameCourse

    /**
     * @param           $participantsList
     * @param           $filtered
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
    private static function AddParticipants($participantsList,$filtered) {
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
                    $content .= self::AddHeader_ParticipantsTable();
            
                    /* Content  */
                    $content .= self::AddContent_ParticipantsTable($participantsList,$filtered);
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
     * @return          string
     * @throws          Exception
     *
     * @creationDate    06/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add header to the participant table
     */
    private static function AddHeader_ParticipantsTable() {
        /* Variables */
        $header = '';
        $strUser     = null;
        $strMail     = null;
        $strAttend   = null;

        try {
            /* Headers  */
            $strUser    = get_string('user');
            $strMail    = get_string('email','local_participants');
            $strAttend  = get_string('last_attendance','local_participants');

            $header .=  html_writer::start_tag('thead');
                $header .=  html_writer::start_tag('tr',array('class' => 'header_participants'));
                    /* User             */
                    $header .= html_writer::start_tag('th',array('class' => 'user'));
                        $header .= $strUser;
                    $header .= html_writer::end_tag('th');
                    /* eMail            */
                    $header .= html_writer::start_tag('th',array('class' => 'info'));
                        $header .= $strMail;
                    $header .= html_writer::end_tag('th');
                    /* Attendance Day   */
                    $header .= html_writer::start_tag('th',array('class' => 'attend'));
                        $header .= $strAttend;
                    $header .= html_writer::end_tag('th');
                    /* Checkbox         */
                    $header .= html_writer::start_tag('th',array('class' => 'seats'));
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
     * @param           $filtered
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
    private static function AddContent_ParticipantsTable($participantsList,$filtered) {
        /* Variables */
        $content = '';
        $name    = null;
        $checked = null;
        $last    = null;
        $today   = null;

        try {
            /* Today date */
            $today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

            foreach ($participantsList as $participant) {
                $content .= html_writer::start_tag('tr');
                    /* User     */
                    $content .= html_writer::start_tag('td',array('class' => 'user'));
                        $content .= $participant->firstname . ' ' . $participant->lastname;
                    $content .= html_writer::end_tag('td');
                    /* eMail    */
                    $content .= html_writer::start_tag('td',array('class' => 'info'));
                        $content .= $participant->email;
                    $content .= html_writer::end_tag('td');
                    /* Attend   */
                    $content .= html_writer::start_tag('td',array('class' => 'attend'));
                        if ($participant->last) {
                            $last       = userdate($participant->last,'%d.%m.%Y', 99, false);
                            if ($participant->last == $today) {
                                $checked    = 'checked';
                            }else {
                                if ($filtered) {
                                    $checked = 'disabled';
                                }else {
                                    $checked = '';
                                }
                            }//if_today
                        }else {
                            $last       = ' - ';
                            $checked    = '';
                        }//if_participant
                        $content .= '<label id="UE_' . $participant->id . '">' . $last .'</label>';
                    $content .= html_writer::end_tag('td');
                    /* Checkbox */
                    $content .= html_writer::start_tag('td',array('class' => 'seats'));
                        $content .= '<input type="checkbox" name="attend" id="id_attend" value="' . $participant->id . '"' . $checked . '>';
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
     * @param           $my_xls
     * @param           $row
     *
     * @throws          Exception
     *
     * @creationDate    11/07/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add name of the course to the excel report
     */
    private static function AddNameCourse_Excel($course,&$my_xls,&$row) {
        /* Variables */
        $col        = 0;

        try {
            $my_xls->write($row, $col, $course,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

        }catch (Exception $ex) {
            throw $ex;
        }
    }//AddNameCourse_Excel

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
        $col        = 0;
        $strUser    = null;
        $strMail    = null;
        $strAttend  = null;

        try {
            $strUser    = get_string('user');
            $strMail    = get_string('email','local_participants');
            $strAttend  = get_string('last_attendance','local_participants');

            /* User Header      */
            $my_xls->write($row, $col, $strUser,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Mail Header      */
            $col += 6;
            $my_xls->write($row, $col, $strMail,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left'));
            $my_xls->merge_cells($row,$col,$row,$col+5);
            $my_xls->set_row($row,20);

            /* Last Attended    */
            $col += 6;
            $my_xls->write($row, $col, $strAttend,array('size'=>12, 'name'=>'Arial','bold'=>'1','bg_color'=>'#efefef','text_wrap'=>true,'v_align'=>'left','align' => 'center'));
            $my_xls->merge_cells($row,$col,$row,$col+2);
            $my_xls->set_row($row,20);
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
        $col    = 0;
        $user   = null;
        $last   = null;

        try {
            if ($participantList) {
                foreach ($participantList as $participant) {
                    /* User             */
                    $user = $participant->firstname . ' ' . $participant->lastname;
                    $my_xls->write($row, $col, $user,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'left'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* eMail            */
                    $col += 6;
                    $my_xls->write($row, $col, $participant->email,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'left'));
                    $my_xls->merge_cells($row,$col,$row,$col+5);
                    $my_xls->set_row($row,20);

                    /* Last Attendance  */
                    if ($participant->last) {
                        $last = userdate($participant->last,'%d.%m.%Y', 99, false);
                    }else {
                        $last = ' - ';
                    }
                    $col += 6;
                    $my_xls->write($row, $col, $last,array('size'=>12, 'name'=>'Arial','bold'=>'1','text_wrap'=>true,'v_align'=>'left','align' => 'center'));
                    $my_xls->merge_cells($row,$col,$row,$col+2);
                    $my_xls->set_row($row,20);

                    $row++;
                    $col = 0;
                }//for_participants
            }//if_participantList
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddParticipants_ContentExcel
}//ParticipantsList