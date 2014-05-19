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


    /* SET FUNCTIONS    */

    /* GET FUNCTIONS    */

    /* PUBLIC           */

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

        /* Short Description */
        $this->course = file_prepare_standard_editor($this->course, 'homesummary', $this->edit_options,$this->context, 'course', 'homesummary', null);
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
            $img = '<img src="'  . $this->getUrlPageGraphics($this->course->homegraphics) . '" width="75" height="75" />';

            $form->setDefault('current_graphic',$img);
        }//if_pagegraphics
        $form->addElement('filemanager', 'pagegraphics_filemanager', get_string('home_graphics','local_course_page'), null, $this->file_options);

        /* Page Video       */
        $form->addElement('text', 'pagevideo', get_string('home_video','local_course_page'));
        /**
         * @updateDate  2014-05-19
         * @author      eFaktor     (uh)
         *
         * Description
         * The type needs to be set for the submission check - Moodle had thrown
         * an error asking for setType.
         */
        $form->setType('pagevideo', PARAM_TEXT);

        /* Visible  */
        $choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $form->addElement('select', 'homevisible', get_string('visible'), $choices);
        $form->setDefault('homevisible', '1');
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
            if (isset($data->deletepicture) && $data->deletepicture) {
                $fs = get_file_storage();
                $file = $fs->get_file_by_id($this->course->homegraphics);

                $DB->delete_records('files',array('itemid' => $file->get_itemid()));
            }///deletepicture

            /* Save Image   */
            $data = file_postupdate_standard_filemanager($data, 'pagegraphics', $this->file_options, $this->context, 'course', 'pagegraphics', $data->pagegraphics_filemanager);
            $fs = get_file_storage();
            if ($files = $fs->get_area_files($this->context->id, 'course', 'pagegraphics', $data->pagegraphics_filemanager, 'id DESC', false)) {
                /* Remove Previous  */
                $file = reset($files);
                $DB->set_field('course', 'homegraphics', $file->get_id(), array('id'=>$course_id));
            }//if_file
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch

        /* Save URL Video   */
        $DB->set_field('course','homevideo',$data->pagevideo);
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
    public static function getUrlPageGraphics($itemid) {
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
    }//getUrlPageGraphics

    public static function getCourseManager() {
        global $DB;

        try {
            /* Context LEvels   */
            $context_levels =  CONTEXT_SYSTEM . ',' . CONTEXT_COURSE . ',' . CONTEXT_COURSECAT . ',' . CONTEXT_MODULE;

            /* Managers */
            $lst_manager = array();
            $lst_manager[0] = get_string('sel_course_manager','local_course_page');

            /* SQL Instruction  */
            $sql = " SELECT		u.id,
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

    /* PRIVATE          */
}//course_page
