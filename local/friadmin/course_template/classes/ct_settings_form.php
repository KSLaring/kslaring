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

        list($course,$category,$editor,$ct) = $this->_customdata;

        /* Context */
        $courseContext  = context_course::instance($course->id);
        $catContext     = context_coursecat::instance($course->category);

        /* General Header */
        $form->addElement('header','general', get_string('general', 'form'));

        /* Full name        */
        $form->addElement('text','fullname', get_string('fullnamecourse'),'maxlength="254" size="50" readonly');
        $form->setType('fullname', PARAM_TEXT);

        /* Category         */
        $form->addElement('text', 'categoryName', get_string('coursecategory'), 'maxlength="100" size="20" readonly');
        $form->setType('categoryName', PARAM_TEXT);
        $form->setDefault('categoryName',$category);

        /* Visble   */
        $choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $form->addElement('select', 'visible', get_string('visible'), $choices);
        $form->addHelpButton('visible', 'visible');
        $form->setDefault('visible', $course->visible);
        if (!empty($course->id)) {
            if (!has_capability('moodle/course:visibility', $courseContext)) {
                $form->hardFreeze('visible');
                $form->setConstant('visible', $course->visible);
            }
        } else {
            if (!guess_if_creator_will_have_course_capability('moodle/course:visibility', $catContext)) {
                $form->hardFreeze('visible');
                $form->setConstant('visible', $course->visible);
            }
        }

        /* Start Date   */
        $form->addElement('date_selector', 'startdate', get_string('startdate'));
        $form->addHelpButton('startdate', 'startdate');
        $form->setDefault('startdate', time() + 3600 * 24);
        
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

        $form->setExpanded('homepagehdr');

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
            case 'homesummary':
                $home_page_header = $form->createElement('header', 'homepagehdr',get_string('home_page','local_course_page'));
                $form->insertElementBefore($home_page_header,'courseformathdr');

                $form->addElement('hidden','homesummary');
                $form->setType('homesummary',PARAM_RAW);

                /* Get Editor   */
                list($edit_options,$context) = course_page::get_edit_options();
                $editor = course_page::prepare_standard_home_summary_editor($edit_options,$context,$courseId);

                /* Editor */
                $home_summay = $form->createElement('editor','homesummary_editor',get_string('home_desc','local_course_page'),null,$edit_options);
                $form->insertElementBefore($home_summay,'courseformathdr');
                $form->setType('homesummary_editor',PARAM_RAW);
                $form->setDefault('homesummary_editor',$editor->homesummary_editor);

                break;

            case 'pagegraphics':
                /* Get FileManager   */
                list($file_options,$context) = course_page::get_file_options($courseId);
                $file_options['accepted_types'] = array('image','web_image');
                $file_editor = course_page::prepare_file_manager_home_graphics_video($file_options,$context,'pagegraphics');
                
                $page_graphics = $form->createElement('filemanager', 'pagegraphics_filemanager', get_string('home_graphics','local_course_page'), null, $file_options);
                $form->insertElementBefore($page_graphics,'courseformathdr');
                $form->setDefault('pagegraphics_filemanager',$file_editor->pagegraphics_filemanager);

                $form->addElement('hidden','pagegraphics');
                $form->setType('pagegraphics',PARAM_RAW);
                $form->setDefault('pagegraphics',$value);

                break;

            case 'pagegraphicstitle':
                $pageTitle = $form->createElement('text','pagegraphicstitle',get_string('home_graphicstitle','local_course_page'),'style="width:95%;"');
                $form->setDefault('pagegraphicstitle',$value);
                $form->setType('pagegraphicstitle',PARAM_TEXT);
                $form->insertElementBefore($pageTitle,'courseformathdr');

                break;

            case 'homepage':
                $home_page = $form->createElement('checkbox','homepage',get_string('checkbox_home','local_course_page'));
                $form->insertElementBefore($home_page,'descriptionhdr');
                $form->setDefault('homepage',$value);

                break;

            case 'ratings':
                $home_ratings = $form->createElement('checkbox','ratings',get_string('home_ratings','local_course_page'));
                $form->insertElementBefore($home_ratings,'descriptionhdr');
                $form->setDefault('ratings',$value);

                break;

            case 'participant':
                $home_participant = $form->createElement('checkbox','participant',get_string('home_participant','local_course_page'));
                $form->insertElementBefore($home_participant,'descriptionhdr');
                $form->setDefault('participant',$value);
                
                break;

            case 'homevisible':
                $visible['0'] = get_string('hide');
                $visible['1'] = get_string('show');
                $home_visible = $form->createElement('select', 'homevisible', get_string('home_visible','local_course_page'), $visible);
                $form->insertElementBefore($home_visible,'ratings');
                $form->setDefault('homevisible',$value);

                break;

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
                $location = course_page::get_course_location($courseId);
                $lstSectors     = course_page::get_sectors_locations_list($location);
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