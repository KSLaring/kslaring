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

        /* Short Name       */
        $form->addElement('text', 'shortname', get_string('shortnamecourse'), 'maxlength="100" size="20" readonly');
        $form->setType('shortname', PARAM_TEXT);

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

        /* Id Number    */
        $form->addElement('text','idnumber', get_string('idnumbercourse'),'maxlength="100"  size="10"');
        $form->addHelpButton('idnumber', 'idnumbercourse');
        $form->setType('idnumber', PARAM_RAW);
        if (!empty($course->id) and !has_capability('moodle/course:changeidnumber', $courseContext)) {
            $form->hardFreeze('idnumber');
            $form->setConstants('idnumber', $course->idnumber);
        }

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
     * Fill in the current page data for this course.
     */
    function definition_after_data() {
        global $DB;

        $form = $this->_form;


        // add course format options
        $formatvalue = $form->getElementValue('format');
        if (is_array($formatvalue) && !empty($formatvalue)) {
            $courseformat = course_get_format((object)array('format' => $formatvalue[0]));

            $elements = $courseformat->create_edit_form_elements($form);
            for ($i = 0; $i < count($elements); $i++) {
                $form->insertElementBefore($form->removeElement($elements[$i]->getName(), false),
                    'addcourseformatoptionshere');
            }
        }

        if ($form->elementExists('homepagehdr')) {
            $form->setExpanded('homepagehdr');
        }
    }

}//ct_settings_form