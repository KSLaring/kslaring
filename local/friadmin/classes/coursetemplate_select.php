<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

//namespace local_friadmin;

defined('MOODLE_INTERNAL') || die;

//use renderable;
//use renderer_base;
//use stdClass;

/**
 * Class containing data for the local_friadmin course_template selection area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_coursetemplate_select extends local_friadmin_widget implements renderable {

    // The form feeback - for debugging
    protected $formdatadump = null;

    // The course creation process result
    protected $coursecreationresult = null;

    // The Moodle form
    protected $mform = null;

    // The returned form data
    protected $fromform = null;

    // The id of the created course
    protected $newcourseid = null;

    /**
     * Construct the courselist_page renderable.
     */
    public function __construct() {

        // Create the data object and set the first values
        parent::__construct();

//        $customdata = $this->get_fixture('friadmin_coursetemplate_select');
        $customdata = $this->get_popup_data();

        $mform = new local_friadmin_coursetemplate_select_form(null, $customdata, 'post',
            '', array('id' => 'mform-coursetemplate-select'));

        $this->mform = $mform;

        if ($fromform = $mform->get_data()) {
            $this->fromform = $fromform;
//            local_logger\console::log($fromform, "Filter - Fromform");

            $this->formdatadump = '<div class="form-data"><h4>Form data</h4><pre>' .
                var_export($fromform, true) . '</pre></div>';

            $this->coursecreationresult = $this->create_course();
        }
    }

    /**
     * Get the returned form data, if any
     */
    public function get_fromform() {
        return $this->fromform;
    }

    /**
     * Get the form formdatadump
     */
    public function get_formdatadump() {
        return $this->formdatadump;
    }

    /**
     * Get the new courseid
     */
    public function get_newcourseid() {
        return $this->newcourseid;
    }

    /**
     * Render the form and set the data
     */
    public function render() {
        if (is_null($this->coursecreationresult)) {
            $this->data->content = $this->mform->render();
        } else {
            $this->data->content = $this->coursecreationresult;
        }
    }

    /**
     * Set the default form values
     *
     * The associative $defaults: array 'elementname' => 'defaultvalue'
     *
     * @param Array $defaults The default values
     */
    public function set_defaults($defaults = array()) {
        $this->mform->set_defaults($defaults);
    }

    /**
     * Get the user managed categories and the template list
     *
     * @return Array An array with the two popup lists
     */
    protected function get_popup_data() {
        global $CFG;

        require_once($CFG->libdir . '/coursecatlib.php');

        $result = array(
            'categories' => array(),
            'templates' => array()
        );

        $result['categories'] = coursecat::make_categories_list('moodle/category:manage');

        // Get all visible course templates from the predefined course category
        if ($plugin_info = get_config('local_friadmin')) {
            if (!empty($plugin_info->template_category)) {
                $templatecategoryid = $plugin_info->template_category;
                $coursecat = coursecat::get($templatecategoryid, MUST_EXIST, true);

                $templatecurses = $coursecat->get_courses();

                foreach ($templatecurses as $templatecurse) {
                    if ($templatecurse->visible) {
                        $result['templates'][$templatecurse->shortname] = $templatecurse->fullname;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Create the course with the given data
     *
     * Use uplaodcourse functionality to create a course from CSV data,
     * see tool/uploadcourse/cli/uploadcourse.php
     */
    protected function create_course() {
        global $CFG, $DB;

        $result = false;
        $courseconfig = get_config('moodlecourse');

        // Set the course generation options
        $options = array(
            'mode' => tool_uploadcourse_processor::MODE_CREATE_NEW,
            'updatemode' => tool_uploadcourse_processor::UPDATE_NOTHING,
            'file' => '',
            'delimiter' => 'comma',
            'encoding' => 'UTF-8',
            'shortnametemplate' => '',
            'templatecourse' => $this->fromform->seltemplate,
            'restorefile' => false,
            'allowdeletes' => false,
            'allowrenames' => false,
            'allowresets' => false,
            'reset' => false,
            'category' => $this->fromform->selcategory
        );

        // Set the processor options
        $processoroptions = array(
            'mode' => $options['mode'],
            'updatemode' => $options['updatemode'],
            'allowdeletes' => $options['allowdeletes'],
            'allowrenames' => $options['allowrenames'],
            'allowresets' => $options['allowresets'],
            'reset' => $options['reset'],
            'shortnametemplate' => $options['shortnametemplate'],
            'templatecourse' => $options['templatecourse']
        );

        // Set the course default values
        $defaults = array();
        $defaults['category'] = $options['category'];
        $defaults['startdate'] = time() + 3600 * 24;
        $defaults['newsitems'] = $courseconfig->newsitems;
        $defaults['showgrades'] = $courseconfig->showgrades;
        $defaults['showreports'] = $courseconfig->showreports;
        $defaults['maxbytes'] = $courseconfig->maxbytes;
        $defaults['legacyfiles'] = $CFG->legacyfilesinnewcourses;
        $defaults['groupmode'] = $courseconfig->groupmode;
        $defaults['groupmodeforce'] = $courseconfig->groupmodeforce;
        $defaults['visible'] = $courseconfig->visible;
        $defaults['lang'] = $courseconfig->lang;

        // Set the course format to the same format as the template course
        if ($templatecourseformat = $DB->get_field('course', 'format',
            array('shortname' => $this->fromform->seltemplate), MUST_EXIST)
        ) {
            $defaults['format'] = $templatecourseformat;
        }

        // Create the course from CSV data
        // Create the CSV: line 1: field names, line2: data
        $content = 'shortname,fullname,category,templatecourse' . "\n" .
            $this->fromform->selshortname . ',' . $this->fromform->selfullname .
            ',' . $this->fromform->selcategory . ',' . $this->fromform->seltemplate;

        $importid = csv_import_reader::get_new_iid('uploadcourse');
        $cir = new csv_import_reader($importid, 'uploadcourse');
        $readcount = $cir->load_csv_content($content, $options['encoding'], $options['delimiter']);
        unset($content);
        if ($readcount === false) {
            print_error('csvfileerror', 'tool_uploadcourse', '', $cir->get_error());
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', '', $cir->get_error());
        }
        $processor = new tool_uploadcourse_processor($cir, $processoroptions, $defaults);

        $processor->execute(new tool_uploadcourse_tracker(tool_uploadcourse_tracker::NO_OUTPUT));
        $errors = $processor->get_errors();

        if (empty($errors)) {
            // Get the new course from the DB
            $newcourse = $DB->get_record('course',
                array('shortname' => $this->fromform->selshortname), '*', MUST_EXIST);

            if ($newcourse) {
                $this->newcourseid = $newcourse->id;
                $info = array(
                    'id' => $newcourse->id,
                    'shortname' => $newcourse->shortname,
                    'fullname' => $newcourse->fullname
                );
                $result = '<p class="result">' .
                    get_string('coursetemplate_result', 'local_friadmin', $info) . '</p>';
            } else {
                // The course should exist when no processor errors had been generated
                $result = '<p class="result">' .
                    get_string('coursetemplate_error', 'local_friadmin') . '</p>';
            }
        } else {
            $result = $errors;
        }

        return $result;
    }
}
