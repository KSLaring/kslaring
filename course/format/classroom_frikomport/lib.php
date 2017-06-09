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

/**
 * Classroom Frikomport Format - Library
 *
 * Description
 *
 * @package             course
 * @subpackage          format/classroom_frikomport
 * @copyright           2010 eFaktor
 * @license             http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @creationDate        20/04/2015
 * @author              eFaktor     (fbv)
 *
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/course/format/lib.php');
require_once($CFG->dirroot . '/local/course_page/locallib.php');

class format_classroom_frikomport extends format_base {

    /**
     * Returns true if this course format uses sections
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns the display name of the given section that the course prefers.
     *
     * Use section name is specified by user. Otherwise use default ("Topic #")
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            return format_string($section->name, true,
                    array('context' => context_course::instance($this->courseid)));
        } else if ($section->section == 0) {
            return get_string('section0name', 'format_classroom_frikomport');
        } else {
            return get_string('topic').' '.$section->section;
        }
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                $usercoursedisplay = $course->coursedisplay;
            }
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else {
                if (!empty($options['navigation'])) {
                    return null;
                }
                $url->set_anchor('section-'.$sectionno);
            }
        }
        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        global $PAGE;
        // if section is specified in course/view.php, make sure it is expanded in navigation
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('section', null, PARAM_INT);
            if ($selectedsection !== null && (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                    $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                $navigation->includesectionnum = $selectedsection;
            }
        }

        // check if there are callbacks to extend course navigation
        parent::extend_course_navigation($navigation, $node);
    }

    /**
     * Custom action after section has been moved in AJAX mode
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    function ajax_section_move() {
        global $PAGE;
        $titles = array();
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return array('sectiontitles' => $titles, 'action' => 'move');
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => array('classroom')
        );
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * Topics format uses the following options:
     * - coursedisplay
     * - numsections
     * - hiddensections
     *
     * @param       bool $foreditform
     * @return           array
     *
     * @updateDate  20/04/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add Home Page Course fields
     *
     * @updateDate  24/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add an extra field. Time from to
     *
     * @updateDate  21/01/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add the 'ratings' option format
     *
     * @updateDate  15/06/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Remove page video
     *
     * @updateDate  10/08/2016
     * @auhtor      eFaktor     (fbv)
     *
     * Description
     * From - To date
     *
     * @updateDate  09/06/2017
     * @author      eFaktor     (fbv)
     *
     * Locatoin/Sector not available for the manager --> readonly
     */
    public function course_format_options($foreditform = false) {
        /* Variables    */
        global $USER,$COURSE;
        $lstLocations   = null;
        $lstSectors     = null;
        $location       = null;
        $assigned       = null;
        $readonly       = null;

        /**
         * @updateDate  08/05/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Get the available locations for the course
         */
        $lstLocations = course_page::get_course_locations_list($USER->id);
        // Get locations already assigned by other managers
        $assigned       = course_page::get_course_location_assigned($COURSE->id);
        // Check if it belongs to the present user or not
        if ($assigned) {
            if (!array_key_exists($assigned->id,$lstLocations)) {
                $lstLocations[$assigned->id] = $assigned->name;
                $readonly = 'readonly';
            }else {
                $readonly = '';
            }//if_Exists
        }else {
            $readonly = '';
        }//if_assigned

        /**
         * @updateDate  08/05/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Get the sectors connected with locations
         *
         * @updateDate  21/03/2016
         * @author      eFaktor     (fbv)
         *
         * Description
         * Sectors based on the location. Uses javascript
         */
        $location = course_page::get_course_location($COURSE->id);
        if ($location) {
            $lstSectors = course_page::get_sectors_locations_list($location);
        }else {
            $lstSectors = array();
            $lstSectors[0] = get_string('sel_sector','local_friadmin');
        }//if_location

        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = array(
                'numsections' => array(
                    'default' => $courseconfig->numsections,
                    'type' => PARAM_INT,
                ),
                'hiddensections' => array(
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT,
                ),
                'coursedisplay' => array(
                    'default' => $courseconfig->coursedisplay,
                    'type' => PARAM_INT,
                ),
                /**
                 * @updateDate  20/04/2015
                 * @author      eFaktor (fbv)
                 *
                 * Description
                 * Add an extra fields
                 */
                'homepage' => array(
                    'label' => get_string('checkbox_home', 'local_course_page'),
                    'element_type' => 'checkbox',
                ),
                /**
                 * @updateDate  21/01/2016
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Course ratings
                 */
                'ratings' => array(
                    'label' => get_string('home_ratings', 'local_course_page'),
                    'element_type' => 'checkbox',
                ),
                'participant' => array(
                    'label' => get_string('home_participant', 'local_course_page'),
                    'element_type' => 'checkbox',
                ),
                'homevisible' => array(
                    'label' => get_string('home_visible', 'local_course_page'),
                    'default' => 1,
                    'element_type' => 'checkbox',
                ),
                'homesummary' => array(
                    'label' => 'homesummary',
                    'element_type' => 'hidden',
                    'default' => '',
                ),
                'pagegraphics' => array(
                    'label' => 'pagegraphics',
                    'element_type' => 'hidden',
                    'default' => 0,
                ),
                'pagegraphicstitle' => array(
                    'type' => PARAM_TEXT,
                ),
                'prerequisities' => array(
                    'type' => PARAM_TEXT,
                ),
                'producedby' => array(
                    'type' => PARAM_TEXT,
                ),
                'course_location' => array(
                    'default' => 0,
                    'type' => PARAM_INT,
                ),
                'course_sector' => array(
                    'default' => 0,
                    'type' => PARAM_RAW,
                ),
                'time'      => array(
                    'type'      => PARAM_TEXT,
                ),
                'length' => array(
                    'type' => PARAM_TEXT,
                ),
                'effort' => array(
                    'type' => PARAM_TEXT,
                )
            );
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseconfig = get_config('moodlecourse');
            $max = $courseconfig->maxsections;
            if (!isset($max) || !is_numeric($max)) {
                $max = 52;
            }
            $sectionmenu = array();
            for ($i = 0; $i <= $max; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseformatoptionsedit = array(
                'numsections' => array(
                    'label' => new lang_string('numberweeks'),
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                ),
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('hiddensectionscollapsed'),
                            1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                ),
                'coursedisplay' => array(
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
                        )
                    ),
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                ),
                /**
                 * @updateDate  20/04/2015
                 * @author      eFaktor (fbv)
                 *
                 * Description
                 * Add an extra fields
                 */
                'pagegraphicstitle' => array(
                    'label' => get_string('home_graphicstitle', 'local_course_page'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        0 => 'style="width:95%;"'
                    )
                ),
                'prerequisities' => array(
                    'label' => get_string('home_prerequisities', 'format_classroom_frikomport'),
                    'element_type' => 'textarea',
                    'element_attributes' => array(
                        0 => 'rows="5" style="width:95%;"'
                    )
                ),
                'producedby' => array(
                    'label' => get_string('home_producedby', 'format_classroom_frikomport'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        0 => 'style="width:95%;"'
                    )
                ),
                'course_location' => array(
                    'label' => get_string('home_location', 'format_classroom_frikomport'),
                    'element_type' => 'select',
                    'element_attributes' => array($lstLocations,$readonly)
                ),
                'course_sector' => array(
                    'label' => get_string('home_sector', 'format_classroom_frikomport'),
                    'element_type' => 'select',
                    'element_attributes' => array($lstSectors,'multiple ' . $readonly)
                ),
                'time'          => array(
                    'label'                 => get_string('home_time_from_to','format_classroom'),
                    'help'                  => 'home_time_from_to',
                    'help_component'        => 'format_classroom',
                    'element_type'          => 'textarea',
                    'element_attributes'    => array(0 => 'rows="4" style="width:50%;"'),
                ),
                'length' => array(
                    'label' => get_string('home_length', 'format_classroom_frikomport'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        0 => 'style="width:95%;"'
                    )
                ),
                'effort' => array(
                    'label' => get_string('home_effort', 'format_classroom_frikomport'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        0 => 'style="width:95%;"'
                    )
                )
            );
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@link course_edit_form::definition_after_data()}.
     *
     * @param           MoodleQuickForm $mform          form the elements are added to.
     * @param           bool            $forsection     'true' if this is a section edit form, 'false' if this is course edit form.
     *
     * @return          array           array of references to the added form elements.
     *
     * @updateDate      20/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Don't call create_edit_form      --> parent
     * Different functionality          --> Course Home Page
     *
     * @updateDate      05/11/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add search filter for the manager
     *
     * @updateDate  21/01/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add the 'ratings' option format
     *
     * @updateDate  21/03/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * The value of sectors selectors depends on the location chosen. Uses javascript
     *
     * @updateDate  15/06/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Remove page video
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        //$elements = parent::create_edit_form_elements($mform, $forsection);

        $elements = array();
        if ($forsection) {
            $options = $this->section_format_options(true);
        } else {
            $options = $this->course_format_options(true);
        }

        /* Initialize Javascrips */
        course_page::init_locations_sector();

        foreach ($options as $optionname => $option) {
            switch ($optionname) {
                case 'homepage':
                case 'ratings':
                case 'participant':
                case 'homevisible':
                case 'homesummary':
                case 'pagegraphics':
                case 'pagegraphicstitle':
                case 'manager':
                    course_page::add_course_home_page_section($mform, $optionname);

                    break;
                default:

                    if (!isset($option['element_type'])) {
                        $option['element_type'] = 'text';
                    }
                    $args = array($option['element_type'], $optionname, $option['label']);
                    if (!empty($option['element_attributes'])) {
                        $args = array_merge($args, $option['element_attributes']);
                    }
                    $elements[] = call_user_func_array(array($mform, 'addElement'), $args);
                    if (isset($option['help'])) {
                        $helpcomponent = 'format_' . $this->get_format();
                        if (isset($option['help_component'])) {
                            $helpcomponent = $option['help_component'];
                        }
                        $mform->addHelpButton($optionname, $option['help'], $helpcomponent);
                    }
                    if (isset($option['type'])) {
                        $mform->setType($optionname, $option['type']);
                    }

                    break;
            }
            //swicth

            if (is_null($mform->getElementValue($optionname)) && isset($option['default'])) {
                $mform->setDefault($optionname, $option['default']);
            }
        }
        //for

        // Increase the number of sections combo box values if the user has increased the number of sections
        // using the icon on the course page beyond course 'maxsections' or course 'maxsections' has been
        // reduced below the number of sections already set for the course on the site administration course
        // defaults page.  This is so that the number of sections is not reduced leaving unintended orphaned
        // activities / resources.
        if (!$forsection) {
            $maxsections = get_config('moodlecourse', 'maxsections');
            $numsections = $mform->getElementValue('numsections');
            $numsections = $numsections[0];
            if ($numsections > $maxsections) {
                $element = $mform->getElement('numsections');
                for ($i = $maxsections+1; $i <= $numsections; $i++) {
                    $element->addOption("$i", $i);
                }
            }
        }
        return $elements;
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'classroom', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * If previous course format did not have 'numsections' option, we populate it with
     * thecurrent number of sections
     *
     *
     * @param       array|stdClass $data            return value from {@link moodleform::get_data()} or array with data
     * @param       null           $oldcourse       if this function is called from {@link update_course()}
     *                                              this object contains information about the course before update
     *
     * @return      bool           whether there were any changes to the options values
     *
     * @updateDate  20/04/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Update the course format options.
     *
     * @updateDate  21/01/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add the 'ratings' option format
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB, $delete;

        $data = (array)$data;
        $oldcourse = (array)$oldcourse;
        $options = $this->course_format_options();
        foreach ($options as $key => $unused) {
            switch ($key) {
                case 'homepage':
                    if (isset($data['homepage']) && $data['homepage']) {
                        $data[$key] = 1;
                    } else {
                        $data[$key] = 0;
                    }
                    //if_homepage

                    break;

                case 'ratings':
                    if (isset($data['ratings']) && $data['ratings']) {
                        $data[$key] = 1;
                    } else {
                        $data[$key] = 0;
                    }
                    //if_homepage

                    break;

                case 'participant':
                    if (isset($data['participant']) && $data['participant']) {
                        $data[$key] = 1;
                    } else {
                        $data[$key] = 0;
                    }
                    //if_homepage

                    break;

                case 'homesummary':
                    if (isset($data['homesummary_editor']) && ($data['homesummary_editor'])) {
                        $data[$key] = course_page::get_home_summary_editor($data['homesummary_editor']);
                    }

                    break;

                case 'pagegraphics':
                    if (isset($data['pagegraphics']) && isset($data['pagegraphics_filemanager'])) {
                        $graphic_id = course_page::postupdate_homegraphics_manager($this->courseid,'pagegraphics','pagegraphics_filemanager',$data['pagegraphics_filemanager']);
                        $data[$key] = $graphic_id;
                    }

                    break;

                case 'pagevideo':
                    if (isset($data['deletevideo']) && ($data['deletevideo'])) {
                        $delete = true;
                    } else {
                        $delete = false;
                    }
                    //if_delete
                    if (isset($data['pagevideo']) && isset($data['pagevideo_filemanager'])) {
                        $video_id = course_page::get_home_graphics_video($data['pagevideo'], 'pagevideo', $data['pagevideo_filemanager'], $delete);
                        if ($video_id) {
                            $data[$key] = $video_id;
                        }
                        //if_graphic_id
                    }
                    //if_page_video_pagevideo_filemanager

                    break;

                case 'course_sector':
                    if (isset($_COOKIE['sectors'])) {
                        $data['course_sector'] = $_COOKIE['sectors'];
                    }else {
                        $data['course_sector'] = 0;
                    }

                    break;
                
                default:
                    break;
            }//switch_key

            if (!array_key_exists($key, $data)) {
                if (array_key_exists($key, $oldcourse)) {
                    $data[$key] = $oldcourse[$key];
                } else if ($key === 'numsections') {
                    // If previous format does not have the field 'numsections'
                    // and $data['numsections'] is not set,
                    // we fill it with the maximum section number from the DB
                    $maxsection = $DB->get_field_sql('SELECT max(section) from
                            {course_sections} WHERE course = ?', array($this->courseid));
                    if ($maxsection) {
                        // If there are no sections, or just default 0-section,
                        // 'numsections' will be set to default
                        $data['numsections'] = $maxsection;
                    }//if_maxsection
                }//if_array_key
            }//if_array_key
        }//for_options

        return $this->update_format_options($data);
    }
}
