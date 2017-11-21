<?php
/**
 * Local Block Courses Site
 *
 * Description
 *
 * @package         local
 * @subpackage      courses_site
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @updateDate      23/05/2014
 * @author          eFaktor     (fbv)
 *
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class courses_site  {

    /* PUBLIC FUNCTIONS */

    /**
     * @static
     * @return          array
     * @throws          Exception
     *
     * @creationDate    29/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the courses that are displayed into Courses Site Block
     */
    public static function courses_site_getBlockList() {
        global $DB;

        try {
            /* Courses Site List    */
            $lst_courses_site = array();

            /* SQL Instruction  */
            $sql = " SELECT		cs.id,
                                cs.course_id,
                                cs.title,
                                cs.description,
                                cs.picture,
                                cs.picturetitle,
                                c.startdate
                     FROM		{block_courses_site}	cs
                        JOIN	{course}		        c 	ON 	  c.id      = cs.course_id
                                                            AND   c.visible = 1
                     ORDER BY   cs.sortorder ASC, cs.title ASC ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $course_site = new stdClass();
                    $course_site->id            = $instance->id;
                    $course_site->course        = $instance->course_id;
                    $course_site->title         = $instance->title;
                    $course_site->description   = $instance->description;
                    $course_site->picture       = $instance->picture;
                    $course_site->picturetitle  = $instance->picturetitle;
                    $course_site->startdate     = $instance->startdate;

                    $lst_courses_site[] = $course_site;
                }//for_rdo
            }//if_rdo

            return $lst_courses_site;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//courses_site_getBlockList

    /**
     * @static
     * @return          array
     * @throws          Exception
     *
     * @creationDate    29/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the category list
     */
    public static function courses_site_getCategoryList() {
        global $DB;

        try {
            /* Category List    */
            $lst_category       = array();

            /* Execute      */
            $rdo = $DB->get_records('course_categories',array('visible' => '1'),'name ASC','id,name');
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $lst_category[$instance->id] = $instance->name;
                }//for
            }//if_rdo

            return $lst_category;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//courses_site_getCategoryList

    /**
     * @static
     * @param           $category_id
     * @return          array
     * @throws          Exception
     *
     * @creationDate    29/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the courses connected with category and can be added to Course Site Block
     */
    public static function courses_site_CoursesByCategory($category_id = null) {
        global $DB;

        try {
            /* List Courses */
            $lst_courses    = array();

            /* Search Criteria  */
            $params = array();
            $params['visible'] = 1;
            $params['category_id'] = $category_id;

            /* SQL Instruction  */
            $sql = " SELECT     id,
                                fullname
                     FROM       {course}
                     WHERE      visible  = :visible
                        AND		id NOT IN (SELECT	course_id
                                           FROM	    {block_courses_site})
                      ";

            if ($category_id) {
                $sql .= " AND category = :category_id ";
            }//if_category_id
            $sql .= " ORDER BY   fullname ASC";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $course) {
                    $lst_courses[$course->id] = $course->fullname;
                }   //for_rdo
            }//if_rdo

            return $lst_courses;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//courses_site_CoursesByCategory

    /**
     * @static
     * @return          int
     * @throws          Exception
     *
     * @creationDate    29/05/2014
     * @author          eFaktor         (fbv)
     *
     * Description
     * Get the next order of the course
     */
    public static function courses_site_GetNextOrder() {
        global $DB;

        try {
            /* SQL Instruction  */
            $sql = " SELECT	MAX(cs.sortorder) as 'order'
                     FROM	{block_courses_site} cs ";

            /* Execute  */
            $rdo = $DB->get_record_sql($sql);
            if ($rdo) {
                return ($rdo->order + 1);
            }else {
                return 1;
            }//if_else_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//courses_site_GetNextOrder

    /**
     * @static
     * @return          array
     *
     * @creationDate    29/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the Options for the filemanager editor
     */
    public static function courses_site_GetFileOptions() {
        global $CFG;

        $accepted_types = array('image','web_image');
        $context        = CONTEXT_SYSTEM::instance();
        $file_options   = array('maxfiles' => 1, 'maxbytes'=>$CFG->maxbytes, 'subdirs' => 0, 'accepted_types' => $accepted_types ,'context' => $context);

        return array($file_options,$context);
    }//courses_site_GetFileOptions

    /**
     * @static
     * @param           $file_options
     * @param           $context
     * @return          stdClass
     *
     * @creationDate    29/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Prepare the Filemanager editor
     */
    public static function courses_site_PrepareFileEditor($file_options,$context) {
        /* Variables    */
        $file_editor = new stdClass();
        $file_editor->picturesite  = 0;

        $file_editor = file_prepare_standard_filemanager($file_editor, 'picture',$file_options,$context, 'course','picture',0);

        return $file_editor;
    }//courses_site_PrepareFileEditor

    /**
     * @static
     * @param           $picture_filemanager
     * @param           bool $delete
     * @param           null $file_id
     * @return          int
     * @throws          Exception
     *
     * @creationDate    29/05/2014
     * @author          efaktor     (fbv)
     *
     * Description
     * Get the id connected with the picture to save it.
     */
    public static function courses_site_GetPictureReference($picture_filemanager,$delete=false,$file_id = null) {
        /* Variables    */
        global $DB;
        $picture_id = 0;

        try {
            /* Get File Editor Options  */
            list($file_options,$context) = courses_site::courses_site_GetFileOptions();

            /* First Remove Previous    */
            $fs = get_file_storage();
            if ($delete) {
                $file = $fs->get_file_by_id($file_id);

                $DB->delete_records('files',array('itemid' => $file->get_itemid()));
            }///deletepicture

            $picture_id = self::courses_site_GetPictureId($picture_filemanager,$file_options,$context);

            return $picture_id;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//courses_site_GetPictureReference

    /**
     * @static
     * @param           $picture_filemanager
     * @param           $file_options
     * @param           $context
     * @return          int
     *
     * @creationDate    29/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the Id connected with the picture
     */
    public static function courses_site_GetPictureId($picture_filemanager,$file_options,$context) {
        /* Variables    */
        $picture_id = 0;

        $file_manager = new stdClass();
        $file_manager->picture_filemanager = $picture_filemanager;

        $file_manager = file_postupdate_standard_filemanager($file_manager, 'picture', $file_options, $context, 'course', 'picture', $file_manager->picture_filemanager);
        $fs = get_file_storage();
        if ($files = $fs->get_area_files($context->id, 'course', 'picture', $file_manager->picture_filemanager, 'id DESC', false)) {
            /* Remove Previous  */
            $file = reset($files);

            $picture_id = $file->get_id();
        }//if_file

        return $picture_id;
    }//courses_site_GetPictureId

    /**
     * @param           $itemid
     * @return          moodle_url|null
     * @throws          Exception
     *
     * @creationDate    29/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the url reference to display the image/picture
     */
    public static function courses_site_GetUrlReferencePicture($itemid) {
        try {
            /* Store File   */
            $fs = get_file_storage();

            /* File Instance        */
            $file   = $fs->get_file_by_id($itemid);

            /* Make URL */
            if ($file) {
                $url = new moodle_url('/local/courses_site/draftfile.php/' .
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
    }//courses_site_GetUrlReferencePicture

    /**
     * @static
     * @param           $data
     * @throws          Exception
     *
     * @creationDate    29/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a new instance into 'block_courses_site' table
     */
    public static function courses_site_AddCourseToBlockSite($data) {
        /* Variables    */
        global $DB;
        $picture_id = 0;

        try {
            /* New Instance */
            $course_site = new stdClass();
            $course_site->course_id      = $data->sel_courses;
            $course_site->title          = $data->title;
            $course_site->description    = $data->txt_descrip;
            $course_site->picturetitle   = $data->picturetitle;
            $course_site->sortorder      = $data->sort_order;
            $course_site->timecreated    = time();

            $course_site->id = $DB->insert_record('block_courses_site',$course_site);

            /* Add the Picture reference    */
            $picture_id = courses_site::courses_site_GetPictureReference($data->picture_filemanager);
            if ($picture_id) {
                $course_site->picture = $picture_id;
                $DB->update_record('block_courses_site',$course_site);
            }//if_picture_id

        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//courses_site_AddCourseToBlockSite

    /**
     * @static
     * @param               $data
     * @param               $course_site
     * @throws              Exception
     *
     * @creationDate        29/05/2014
     * @author              eFaktor     (fbv)
     *
     * Description
     * Update the course instance for Course Site Block
     */
    public static function courses_site_UpdateCourseToBlockSite($data,&$course_site) {
        /* Variables    */
        global $DB;
        $picture_id = 0;
        $delete     = false;

        try {
            /* New Instance */
            $course_site->title          = $data->title;
            $course_site->description    = $data->txt_descrip;
            $course_site->picturetitle   = $data->picturetitle;
            $course_site->sortorder      = $data->sort_order;
            $course_site->timemodified   = time();

            if (isset($data->deletepicture) && $data->deletepicture) {
                $delete = true;
            }else {
                $delete = false;
            }//if_data_delete_picture

            /* Add the Picture reference    */
            $picture_id = courses_site::courses_site_GetPictureReference($data->picture_filemanager,$delete,$course_site->picture);
            if ($picture_id) {
                $course_site->picture = $picture_id;
            }//if_picture_id

            $DB->update_record('block_courses_site',$course_site);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//courses_site_AddCourseToBlockSite

    /**
     * @static
     * @param           $course_site
     * @return          bool
     *
     * @creationDate    29/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Delete the course form 'Course Site Block'
     */
    public static function courses_site_DeleteCourseFromBlockSite($course_site) {
        global $DB;

        try {
            $DB->delete_records('block_courses_site',array('id' => $course_site->id,'course_id' => $course_site->course_id));

            /* First Remove Previous    */
            if ($course_site->picture) {
                $fs = get_file_storage();
                $file = $fs->get_file_by_id($course_site->picture);
                if ($file && $file->get_itemid()) {
                    $DB->delete_records('files',array('itemid' => $file->get_itemid()));
                }
            }//course_site

            return true;
        }catch (Exception $ex) {
            return false;
        }//try_catch
    }//courses_site_DeleteCourseFromBlockSite

    /**
     * @static
     * @param               $course_site
     * @return              stdClass
     * @throws              Exception
     *
     * @creationDate        29/05/2014
     * @author              eFaktor     (fbv)
     *
     * Description
     * Get info to display.
     *
     * @updateDate          21/04/2015
     * @author              eFaktor     (fbv)
     *
     * Description
     * Add the frikomport formats
     */
    public static function courses_site_GetInfoBlock($course_site) {
        /* Variables    */
        $info_display = new stdClass();

        try {
            /* Common fields        */
            $info_display->course       = $course_site->course;
            $info_display->title        = $course_site->title;
            $info_display->description  = $course_site->description;
            $info_display->picture      = self::courses_site_GetUrlReferencePicture($course_site->picture);
            $info_display->picturetitle = $course_site->picturetitle;
            $info_display->published    = userdate($course_site->startdate,'%d.%m.%Y', 99, false);

            /* Get Format Options   */
            $info_display->type = course_get_format($course_site->course)->get_format();
            $format_options     = course_get_format($course_site->course)->get_format_options();

            switch ($info_display->type) {
                case 'netcourse':
                case 'elearning_frikomport':
                case 'classroom':
                case 'classroom_frikomport':
                    $info_display->prerequisities = $format_options['prerequisities'];

                    break;
                case 'whitepaper':
                case 'single_frikomport':
                    $info_display->author = $format_options['author'];

                    break;
                default:
                    break;
            }//switch

            return $info_display;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//courses_site_GetInfoBlock
}//courses_site

class add_course_site_form extends moodleform {
    function definition() {
        $form = $this->_form;

        /* Header   */
        $form->addElement('header', 'description', get_string('pluginname','local_courses_site'));
        $form->setExpanded('description');

        /* Category List */
        $lst_category = courses_site::courses_site_getCategoryList();
        $lst_category[0]    = get_string('select_category','local_courses_site');
        $form->addElement('select','sel_category',get_string('category','local_courses_site'),$lst_category,'onChange=getCategory("sel_category")');
        $form->addRule('sel_category',null, 'required');
        if (isset($_COOKIE['parentCategory'])) {
            $form->setDefault('sel_category',$_COOKIE['parentCategory']);
        }else {
            $form->setDefault('sel_category',0);
        }//if_parentCategory

        /* Courses  */
        if (isset($_COOKIE['parentCategory']) && (!empty($_COOKIE['parentCategory']))) {
            $lst_courses = courses_site::courses_site_CoursesByCategory($_COOKIE['parentCategory']);
        }else {
            $lst_courses = courses_site::courses_site_CoursesByCategory();
        }//if_category_sel
        $lst_courses[0] = get_string('select_course','local_courses_site');
        $form->addElement('select','sel_courses',get_string('course','local_courses_site'),$lst_courses);
        $form->addRule('sel_courses',null, 'required');
        $form->setDefault('sel_courses',0);
        $form->disabledIf('sel_courses','sel_category','eq',0);

        /* Order        */
        $next_order = courses_site::courses_site_GetNextOrder();
        $form->addElement('text','sort_order',get_string('order','local_courses_site'),' size=4');
        $form->setType('sort_order',PARAM_INT);
        $form->addRule('sort_order',null, 'required');
        $form->setDefault('sort_order',$next_order);

        /* Title        */
        $form->addElement('text','title',get_string('title','local_courses_site'),' size=50');
        $form->setType('title',PARAM_TEXT);
        $form->addRule('title',null, 'required');

        /* Description  */
        $form->addElement('textarea','txt_descrip',get_string('description','local_courses_site'),'rows=5 cols=40');
        $form->setType('txt_descrip',PARAM_TEXT);
        $form->addRule('txt_descrip',null, 'required');

        /* File Manager */
        list($file_options,$context) = courses_site::courses_site_GetFileOptions();
        $file_editor = courses_site::courses_site_PrepareFileEditor($file_options,$context);

        $form->addElement('filemanager', 'picture_filemanager', get_string('picture','local_courses_site'), null, $file_options);
        $form->setType('picture_filemanager',PARAM_RAW);
        $form->addRule('picture_filemanager',null, 'required');

        /* Picture title */
        $form->addElement('text','picturetitle',get_string('picturetitle','local_courses_site'),'size=50');
        $form->setType('picturetitle',PARAM_TEXT);

        $this->add_action_buttons(true, get_string('save', 'local_courses_site'));
    }//definition


}//add_course_site_form

class edit_course_site_form extends moodleform {
    function definition() {
        $form = $this->_form;

        list($course_site,$category,$fullname) = $this->_customdata;

        /* Header   */
        $form->addElement('header', 'description', get_string('pluginname','local_courses_site'));
        $form->setExpanded('description');

        /* Category List */
        $lst_category = courses_site::courses_site_getCategoryList();
        $form->addElement('text','category',get_string('category','local_courses_site'),'disabled');
        $form->setDefault('category',$lst_category[$category]);
        $form->setType('category',PARAM_TEXT);

        /* Courses  */
        $form->addElement('text','course',get_string('course','local_courses_site'),'disabled');
        $form->setDefault('course',$fullname);
        $form->setType('course',PARAM_TEXT);

        /* Order        */
        $form->addElement('text','sort_order',get_string('order','local_courses_site'),' size=4');
        $form->setType('sort_order',PARAM_INT);
        $form->addRule('sort_order',null, 'required');
        $form->setDefault('sort_order',$course_site->sortorder);

        /* Title        */
        $form->addElement('text','title',get_string('title','local_courses_site'),' size=50');
        $form->setType('title',PARAM_TEXT);
        $form->addRule('title',null, 'required');
        $form->setDefault('title',$course_site->title);

        /* Description  */
        $form->addElement('textarea','txt_descrip',get_string('description','local_courses_site'),'rows=5 cols=40');
        $form->setType('txt_descrip',PARAM_TEXT);
        $form->addRule('txt_descrip',null, 'required');
        $form->setDefault('txt_descrip',$course_site->description);

        /* Picture  */
        $form->addElement('static', 'current_picture', get_string('current_picture','local_courses_site'));
        $form->addElement('checkbox', 'deletepicture', get_string('delete'));
        $form->setDefault('deletepicture', 0);
        if ($course_site->picture) {
            /* URL IMAGE */
            $img = '<img src="'  . courses_site::courses_site_GetUrlReferencePicture($course_site->picture) . '" width="75" height="75" />';

            $form->setDefault('current_picture',$img);
        }//if_picture

        /* File Manager */
        list($file_options,$context) = courses_site::courses_site_GetFileOptions();
        $file_editor = courses_site::courses_site_PrepareFileEditor($file_options,$context);

        $form->addElement('filemanager', 'picture_filemanager', get_string('picture','local_courses_site'), null, $file_options);
        $form->setType('picture_filemanager',PARAM_RAW);

        /* Picture title */
        $form->addElement('text','picturetitle',get_string('picturetitle','local_courses_site'),'size=50');
        $form->setType('picturetitle',PARAM_TEXT);
        $form->setDefault('picturetitle',$course_site->picturetitle);

        $form->addElement('hidden','id');
        $form->setDefault('id',$course_site->course_id);
        $form->setType('id',PARAM_INT);

        $this->add_action_buttons(true, get_string('save', 'local_courses_site'));
    }

    function validation($data, $files) {
        global $DB, $CFG, $SESSION;
        $errors = parent::validation($data, $files);

        if (isset($data['deletepicture']) && $data['deletepicture']) {
            /* Get File Editor Options  */
            list($file_options,$context) = courses_site::courses_site_GetFileOptions();
            $picture = courses_site::courses_site_GetPictureId($data['picture_filemanager'],$file_options,$context);
            if (!$picture) {
                $errors['picture_filemanager'] = get_string('required');
                return $errors;
            }
        }//delete

        return $errors;
    }//validation
}//edit_course_site_form
