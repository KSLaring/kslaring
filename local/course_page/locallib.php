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

class course_page  {
    protected $course_page;
    protected $context;
    protected $edit_options;
    protected $course;
    protected $file_options;
    protected $img_draft;
    /**
     *
     * Constructor
     */
    public function __construct($course,$category) {
        global $CFG;

        if (isset($course->id)) {
            $this->context = CONTEXT_COURSE::instance($course->id);
        }else {
            $this->context = CONTEXT_COURSECAT::instance($category->id);
        }//if_empty_course

        $this->course       = $course;
        $this->edit_options = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true, 'context' => $this->context);
        $this->file_options = array('maxfiles' => 1, 'maxbytes'=>$CFG->maxbytes, 'accepted_types' => 'web_image', 'subdirs' => 0, 'context' => $this->context);
        $this->img_draft    = 0;
    }//constructor

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

    /**
     * @return          array
     *
     * @creationDate    20/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return the edit_options
     */
    public function get_edit_options() {
        return $this->edit_options;
    }//get_edit_options

    /* PUBLIC FUNCTIONS */

    /**
     * @param           $form
     *
     * @creationDate    05/05/2014
     * @author          eFaktor     (fbV)
     *
     * Description
     * Add the 'Home Page Section' to the form
     */
    public function add_CourseHomePage_Section(&$form) {
        $form->addElement('header', 'homepagehdr',get_string('home_page','local_course_page'));

        /* Visible  */
        $choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $form->addElement('select', 'homevisible', get_string('home_visible','local_course_page'), $choices);
        $form->setDefault('homevisible', '1');

        /* Short Description */
        $this->course->homesummaryformat = FORMAT_HTML;
        $this->course = file_prepare_standard_editor($this->course, 'homesummary', $this->edit_options,$this->context, 'course', 'homesummary',0);
        $form->addElement('editor','homesummary_editor',get_string('home_desc','local_course_page'),null,$this->edit_options);
        $form->setType('homesummary_editor',PARAM_RAW);


        /* Page Graphics    */
        $form->addElement('static', 'current_graphic', get_string('current_graphic','local_course_page'));
        $form->addElement('checkbox', 'deletepicture', get_string('delete'));
        $form->setDefault('deletepicture', 0);
        $form->disabledIf('deletepicture',$this->course->homegraphics,0);
        $this->course = file_prepare_standard_filemanager($this->course, 'pagegraphics',$this->file_options,$this->context, 'course','pagegraphics',0);
        if ($this->course->homegraphics) {
            /* URL IMAGE */
            $img = '<img src="'  . $this->getUrlPageGraphicsVideo($this->course->homegraphics) . '" width="75" height="75" />';

            $form->setDefault('current_graphic',$img);
        }//if_pagegraphics
        $form->addElement('filemanager', 'pagegraphics_filemanager', get_string('home_graphics','local_course_page'), null, $this->file_options);

        /* Page Video       */
        $form->addElement('static', 'current_video', get_string('home_current_video','local_course_page'));
        $form->addElement('checkbox', 'deletevideo', get_string('home_delete_video','local_course_page'));
        $form->setDefault('deletevideo', 0);
        $form->disabledIf('deletevideo',$this->course->homevideo,0);
        $this->course = file_prepare_standard_filemanager($this->course, 'pagevideo',$this->file_options,$this->context, 'course','pagevideo',0);
        $form->addElement('filemanager', 'pagevideo_filemanager', get_string('home_video','local_course_page'));
        if ($this->course->homevideo) {
            $fs = get_file_storage();
            try {
                $file = $fs->get_file_by_id($this->course->homevideo);
                $form->setDefault('current_video',$file->get_filename());
            }catch(Exception $ex) {
                return true;
            }
        }///if_homevideo
    }//add_CourseHomePage_Section

    /**
     * @param           $course_id
     * @param           $data
     * @throws          Exception
     *
     * @creationDate    05/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Update Home Page information
     */
    public function updateCourseHomePage($course_id,$data) {
        global $DB;

        try {
            /* Save Hompage Check           */
            if (isset($data->homepage) && $data->homepage) {
                $DB->set_field('course','homepage',1);
            }else {
                $DB->set_field('course','homepage',0);
            }//home_page

            /* Save Home page description   */
            $data = file_postupdate_standard_editor($data, 'homesummary', $this->edit_options, $this->context, 'course', 'homesummary', 0);
            $DB->set_field('course', 'homesummary', $data->homesummary, array('id'=>$course_id));

            /* Deleted Previous Page Graphics   *//**
             * @updateDate  2014-05-19
             * @author      eFaktor     (uh)
             *
             * Description
             * The "deletpicture" is not set when the checkbox is unchecked - check isset
             */
            $fs = get_file_storage();
            if (isset($data->deletepicture) && $data->deletepicture) {
                $file = $fs->get_file_by_id($this->course->homegraphics);

                $DB->delete_records('files',array('itemid' => $file->get_itemid()));
            }///deletepicture

            /* Save Image   */
            $data = file_postupdate_standard_filemanager($data, 'pagegraphics', $this->file_options, $this->context, 'course', 'pagegraphics', $data->pagegraphics_filemanager);
            if ($files = $fs->get_area_files($this->context->id, 'course', 'pagegraphics', $data->pagegraphics_filemanager, 'id DESC', false)) {
                /* Remove Previous  */
                $file = reset($files);
                $DB->set_field('course', 'homegraphics', $file->get_id(), array('id'=>$course_id));
            }//if_file

            /* Delete Previous Video    */
            if (isset($data->deletevideo) && $data->deletevideo) {
                $file = $fs->get_file_by_id($this->course->homevideo);

                $DB->delete_records('files',array('itemid' => $file->get_itemid()));
            }///deletepicture
            /* Upload New Video */
            $data = file_postupdate_standard_filemanager($data, 'pagevideo', $this->file_options, $this->context, 'course', 'pagevideo', $data->pagevideo_filemanager);
            if ($files = $fs->get_area_files($this->context->id, 'course', 'pagevideo', $data->pagevideo_filemanager, 'id DESC', false)) {
                /* Remove Previous  */
                $file = reset($files);
                $DB->set_field('course', 'homevideo', $file->get_id(), array('id'=>$course_id));
            }//if_file
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//updateCourseHomePage

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
                                CONCAT(u.firstname, ',' , u.lastname) as 'name'
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
                    $field->name    = $instance->name;
                    $field->value   = $instance->value;

                    $format_fields[$instance->id] = $field;
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
     * @param           $format_options
     * @param           $form
     * @throws          Exception
     *
     * @creationDate    15/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Print the options/fields connected with the course format. Only for the Course Home Page
     */
    public function printFormatOptions($format_options,&$form) {
        try {
            /* Header   */
            $form->addElement('header', 'courseformathdr', get_string('type_format', 'plugin'));
            /* Add the fields   */
            foreach ($format_options as $option) {
                switch ($option->name) {
                    case 'prerequisities':
                        $form->addElement('textarea','prerequisities',get_string('home_prerequisities','local_course_page'),'rows="5" style="width:95%;"');
                        $form->setDefault('prerequisities',$option->value);
                        break;
                    case 'producedby':
                        $form->addElement('text','producedby',get_string('home_producedby','local_course_page'),'style="width:95%;"');
                        $form->setDefault('producedby',$option->value);
                        $form->setType('producedby',PARAM_TEXT);
                        break;
                    case 'location':
                        $form->addElement('text','location',get_string('home_location','local_course_page'),'style="width:95%;"');
                        $form->setDefault('location',$option->value);
                        $form->setType('location',PARAM_TEXT);
                        break;
                    case 'length':
                        $form->addElement('text','length',get_string('home_length','local_course_page'),'style="width:95%;"');
                        $form->setDefault('length',$option->value);
                        $form->setType('length',PARAM_TEXT);
                        break;
                    case 'effort':
                        $form->addElement('text','effort',get_string('home_effort','local_course_page'),'style="width:95%;"');
                        $form->setDefault('effort',$option->value);
                        $form->setType('effort',PARAM_TEXT);
                        break;
                    case 'manager':
                        $lst_manager = $this->getCourseManager();
                        $form->addElement('select','manager',get_string('home_manager','local_course_page'),$lst_manager);
                        $form->setDefault('manager',$option->value);
                        break;
                    case 'author':
                        $form->addElement('text','author',get_string('home_author','local_course_page'),'style="width:95%;"');
                        $form->setDefault('author',$option->value);
                        $form->setType('author',PARAM_TEXT);
                        break;
                    case 'licence':
                        $form->addElement('text','licence',get_string('home_licence','local_course_page'),'style="width:95%;"');
                        $form->setDefault('licence',$option->value);
                        $form->setType('licence',PARAM_TEXT);
                        break;
                    default:
                        break;
                }//switch
            }//for_each_option
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//printFormatOptions

    /* PRIVATE          */
}//course_page

class home_page_form extends moodleform {
    function definition() {
        $form    = $this->_form;

        $course_page    = $this->_customdata['course_page'];
        $course         = $course_page->get_course();
        $editor_options = $course_page->get_edit_options();

        $context = CONTEXT_COURSE::instance($course->id);
        $course = file_prepare_standard_editor($course, 'summary', $editor_options,$context, 'course', 'summary', 0);

        // Description.
        $form->addElement('header', 'descriptionhdr', get_string('description'));
        $form->setExpanded('descriptionhdr');

        $form->addElement('editor','summary_editor', get_string('coursesummary'), null, $editor_options);
        $form->addHelpButton('summary_editor', 'coursesummary');
        $form->setType('summary_editor', PARAM_RAW);
        $form->addRule('summary_editor', null, 'required');

        $form->addElement('checkbox','homepage',get_string('checkbox_home','local_course_page'));
        if ($course->homepage) {
            $form->setDefault('homepage',1);
        }//if_home_page

        $course_page->add_CourseHomePage_Section($form);


        /* Course Format Section    */
        $format_options = $course_page->getFormatFields($course->id);
        if ($format_options) {
            $course_page->printFormatOptions($format_options,$form);
        }//if_format_options

        $form->addElement('hidden','id');
        $form->setType('id',PARAM_INT);
        $form->setDefault('id',$course->id);

        $form->addElement('hidden','show');
        $form->setType('show',PARAM_INT);
        $form->setDefault('show',1);

        $this->add_action_buttons(true);
        $this->set_data($course);
    }//definition

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['homesummary_editor']['text'])) {
            $errors['homesummary_editor'] = get_string('required');
        }//home_summary

        return $errors;
    }
}//home_page_form