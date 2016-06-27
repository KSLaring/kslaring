<?php
/**
 * Course Template - Edit Course Settings
 *
 * @package         local
 * @subpackage      friadmin/course_template
 * @copyright       2014        eFaktor {@link http://www.efaktor.no}
 *
 * @creationDate    07/01/2016
 * @author          eFaktor     (fbv)
 *
 * Description
 * Course create form template. General settings
 */
require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot . '/local/course_page/locallib.php');

class ct_settings_form extends moodleform {
    function definition () {
        /* Variables    */
        global $PAGE;
        $courseContext  = null;
        $catContext     = null;

        /* Form         */
        $form               = $this->_form;
        /* Java script for Course Format selector */
        $PAGE->requires->yui_module('moodle-course-formatchooser', 'M.course.init_formatchooser',
                                    array(array('formid' => $form->getAttribute('id'))));

        list($course,$category,$editor,$ct) = $this->_customdata;

        /* Context */
        $courseContext  = CONTEXT_COURSE::instance($course->id);
        $catContext     = CONTEXT_COURSECAT::instance($course->category);

        /* General Header */
        $form->addElement('header','general', get_string('general', 'form'));

        /* Full name        */
        $form->addElement('text','fullname', get_string('fullnamecourse'),'maxlength="254" size="50" readonly');
        $form->setType('fullname', PARAM_TEXT);

        /* Category         */
        $form->addElement('text', 'categoryName', get_string('coursecategory'), 'maxlength="100" size="20" readonly');
        $form->setType('categoryName', PARAM_TEXT);
        $form->setDefault('categoryName',$category);

        // Description.
        $form->addElement('header', 'descriptionhdr', get_string('description'));
        $form->setExpanded('descriptionhdr');

        $form->addElement('editor','summary_editor', get_string('coursesummary'), null, $editor);
        $form->addHelpButton('summary_editor', 'coursesummary');
        $form->setType('summary_editor', PARAM_RAW);
        $summaryfields = 'summary_editor';

        /* Files Summary */
        if ($overviewfilesoptions = course_overviewfiles_options($course)) {
            $form->addElement('filemanager', 'overviewfiles_filemanager', get_string('courseoverviewfiles'), null, $overviewfilesoptions);
            $form->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
            $summaryfields .= ',overviewfiles_filemanager';
        }

        if (!empty($course->id) and !has_capability('moodle/course:changesummary', $courseContext)) {
            // Remove the description header it does not contain anything any more.
            $form->removeElement('descriptionhdr');
            $form->hardFreeze($summaryfields);
        }

        // Course format.
        $form->addElement('header', 'courseformathdr', get_string('type_format', 'plugin'));
        $form->setExpanded('courseformathdr');

        $courseformats = get_sorted_course_formats(true);
        $formcourseformats = array();
        foreach ($courseformats as $courseformat) {
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }
        if (isset($course->format)) {
            $course->format = course_get_format($course)->get_format(); // replace with default if not found
            if (!in_array($course->format, $courseformats)) {
                // this format is disabled. Still display it in the dropdown
                $formcourseformats[$course->format] = get_string('withdisablednote', 'moodle',
                    get_string('pluginname', 'format_'.$course->format));
            }
        }

        $form->addElement('select', 'format', get_string('format'), $formcourseformats);
        $form->addHelpButton('format', 'format');
        $form->setDefault('format', $course->format);

        // Button to update format-specific options on format change (will be hidden by JavaScript).
        $form->registerNoSubmitButton('updatecourseformat');
        $form->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));

        // Just a placeholder for the course format options.
        $form->addElement('hidden', 'addcourseformatoptionshere');
        $form->setType('addcourseformatoptionshere', PARAM_BOOL);

        $format_options = course_get_format($course)->get_format_options();
        foreach ($format_options as $name=>$option) {
            $this->AddCourseFormat($form,$name,$option,$course->format,$course->id);
        }
        
        // Appearance.
        $form->addElement('hidden', 'appearancehdr', get_string('appearance'));
        $form->setType('appearancehdr', PARAM_RAW);

        $this->add_action_buttons();

        $form->addElement('hidden', 'id', null);
        $form->setType('id', PARAM_INT);

        $form->addElement('hidden', 'ct', $ct);
        $form->setType('ct', PARAM_INT);

        // Finally set the current form data
        $this->set_data($course);
    }//definition

    /**
     * @param           $form
     * @param           $option
     * @param           $value
     * @param           $format
     * @param           $courseId
     *
     * @throws          Exception
     * @throws          coding_exception
     *
     * @creationDate    27/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add course format
     */
    function AddCourseFormat(&$form,$option,$value,$format,$courseId) {
        global $USER;
        
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
                $lstLocations = course_page::Get_CourseLocationsList($USER->id);
                $form->addElement('select','course_location',get_string('home_location',$str_format),$lstLocations);
                $form->setDefault('course_location',$value);
                break;
            case 'course_sector':
                $location = course_page::GetCourseLocation($courseId);
                $lstSectors     = course_page::Get_SectorsLocationsList($location);
                $form->addElement('select','course_sector',get_string('home_sector',$str_format),$lstSectors,'multiple');
                $form->setDefault('course_sector',$value);
                break;
            case 'time':
                $form->addElement('textarea','time',get_string('home_time_from_to',$str_format),'rows="5" style="width:95%;"');
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
            case 'manager':
                $lst_manager = course_page::getCourseManager();
                $form->addElement('select','manager',get_string('home_manager',$str_format),$lst_manager);
                $form->setDefault('manager',$value);

                $form->addElement('static', 'serach-description', '', 'enter the first letters to reduce the list');
                $form->addElement('text','manager_search' ,'','size = 25');
                $form->setType('manager_search',PARAM_TEXT);

                course_page::Init_Manager_Selector('manager',null,$courseId);
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
    }
}//ct_settings_form