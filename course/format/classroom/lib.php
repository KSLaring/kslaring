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
 * This file contains main class for the course format Topic
 *
 * @package    format_classroom
 * @copyright  2016 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->dirroot . '/local/course_page/locallib.php');

/**
 * Main class for the Classroom course format
 *
 * @package     format_classroom
 * @copyright   2016 eFaktor
 * @author      Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  14/05/2014
 * @author      eFaktor     (fbv)
 *
 * Description
 * Add all the reference for the extra fields.
 * Add extra fields.
 */
class format_classroom extends format_base {

    /** @var int Trim characters from the right */
    const TRIM_RIGHT = 1;
    /** @var int Trim characters from the left */
    const TRIM_LEFT = 2;
    /** @var int Trim characters from the center */
    const TRIM_CENTER = 3;

    const LESSON_NOT_LASTPAGE = 0;
    const LESSON_LASTPAGE_GRADINGON = 1;
    const LESSON_LASTPAGE_GRADINGOFF = 2;

    protected $openlast = null;

    // The HTML to display the completion checkbox in activities and resource
    // with manual completion.
    protected $manualcompletionhtml = null;

    static protected $lastlessonpage = null;

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
     *
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            return format_string($section->name, true,
                array('context' => context_course::instance($this->courseid)));
        } else if ($section->section == 0) {
            return get_string('section0name', 'format_classroom');
        } else {
            return get_string('topic') . ' ' . $section->section;
        }
    }

    /**
     * @return null|string The HTML for the completion checkbox
     */
    public function get_manualcompletionhtml() {
        return $this->manualcompletionhtml;
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field
     *                              course_sections.section if omitted the course view page
     *                              is returned
     * @param array        $options options for view URL. At the moment core uses:
     *                              'navigation' (bool) if true and section has no separate
     *                              page, the function returns null 'sr' (int) used by
     *                              multipage formats to specify to which section to return.
     *
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
            // Force the course display to single page.
            $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else {
                if (!empty($options['navigation'])) {
                    return null;
                }
                $url->set_anchor('section-' . $sectionno);
            }
        }

        $url->param('start', 1);

        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     * The property (array)testedbrowsers can be used as a parameter for {@link ajaxenabled()}.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        $ajaxsupport->testedbrowsers = array('MSIE' => 6.0, 'Gecko' => 20061111,
            'Safari' => 531, 'Chrome' => 6.0);

        return $ajaxsupport;
    }

    /**
     * Allows course format to execute code on moodle_page::set_course()
     * Check the $ME and redirect to the last visited
     * activity/resource if the $ME is the course page. Redirect
     * to the first activity/resource in section 0 (description) if the user never
     * has entered the course before.
     *
     * Add the course navigation as a Moodle "fake" block.
     *
     * @param moodle_page $page instance of page calling set_course
     */
    public function page_set_course(moodle_page $page) {
        global $USER, $FULLME, $ME;

        // No specific changes if a file shall be downloaded.
        if (strpos($ME, 'forcedownload=1') !== false) {
            return;
        }

        if (is_null($this->openlast)) {
            $this->openlast = new format_classroom_openlast($page,
                $page->course, $USER, $FULLME);
        }

        $redirecturl = $this->openlast->redirect($ME);

        if ($redirecturl === -1) {
            return;
        } else if ($redirecturl) {
            redirect($redirecturl);
        }

        // Load the lightbox script.
        $page->requires->yui_module(array('moodle-local_lightbox-lightbox'),
            'M.local_lightbox.lightbox.init_lightbox',
            array());
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * @param global_navigation $navigation
     * @param navigation_node   $node The course node within the navigation
     *
     * @return array
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        global $PAGE, $SCRIPT, $DB;

        // Exclude the navigation changes and the fake navigation block on report pages
        // because some code on report page expect navigation nodes we exclude.
        if ($SCRIPT === '/report/log/user.php' ||
            $SCRIPT === '/report/outline/user.php' ||
            $SCRIPT === '/course/user.php'
        ) {
            $this->extend_course_navigation_unmodified($navigation, $node);

            return array();
        }

        // Exclude the SCORM report page - it has issues with
        // the netcourse navigation block.
        if ($PAGE->pagetype === 'mod-scorm-report') {
            return array();
        }

        // If section is specified in course/view.php, make sure it is expanded
        // in navigation.
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('sectionid', null, PARAM_INT);
            if (is_null($selectedsection)) {
                $selectedsection = optional_param('section', null, PARAM_INT);
            } else {
                $selectedsection = $DB->get_field('course_sections', 'section',
                    array('id' => $selectedsection));
            }

            if ($selectedsection !== null && (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            ) {
                $navigation->includesectionnum = $selectedsection;
            }
        }

        // Remove existing course navigation nodes.
        if ($node->has_children()) {
            foreach ($node->children as $childnode) {
                $childnode->remove();
            }
        }

        $modinfo = get_fast_modinfo($this->courseid);

        foreach ($modinfo->get_section_info_all() as $section) {
            // Exclude section 0
            // Section 0 is used for the course description and potential
            // Sections with no activities/resources are excluded.
            if ($section->section > 0 && isset($modinfo->sections[$section->section])) {
                $sectionNode = $this->navigation_add_section($navigation, $node, $section);
                // Section may be hidden, so check if null.
                if (!is_null($sectionNode)) {
                    foreach ($modinfo->sections[$section->section] as $cmid) {
                        $this->navigation_add_activity($sectionNode, $modinfo->get_cm($cmid));
                    }
                }
            }
        }

        return array();
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * @param global_navigation $navigation
     * @param navigation_node   $node The course node within the navigation
     */
    public function extend_course_navigation_unmodified($navigation, navigation_node $node) {
        global $PAGE;
        // If section is specified in course/view.php, make sure it is expanded in navigation.
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('section', null, PARAM_INT);
            if ($selectedsection !== null && (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            ) {
                $navigation->includesectionnum = $selectedsection;
            }
        }

        // Check if there are callbacks to extend course navigation.
        parent::extend_course_navigation($navigation, $node);

        // We want to remove the general section if it is empty.
        $modinfo = get_fast_modinfo($this->get_course());
        $sections = $modinfo->get_sections();
        if (!isset($sections[0])) {
            // The general section is empty to find the navigation node for it we need to get its ID.
            $section = $modinfo->get_section_info(0);
            $generalsection = $node->get($section->id, navigation_node::TYPE_SECTION);
            if ($generalsection) {
                // We found the node - now remove it.
                $generalsection->remove();
            }
        }
    }

    /**
     * Let the course format modify the page object before the page is generated.
     *
     * This hook is called from the method theme_kommit_page_init in
     * theme/kommit/lib.php.
     */
    public function page_init(moodle_page $page) {
        global $CFG, $OUTPUT;

        if (!$page->user_is_editing()) {
            $page->theme->layouts['incourse']['options']['nonavbar'] = true;
        }

        // Check if the navigation trigger parameter "nonav" is set.
        $nonav = optional_param('nonav', 0, PARAM_INT);

        // If the "nonav" parameter is not set show the course navigation.
        if (!$nonav) {
            // Exclude the SCORM report page - it has issues with
            // the classroom navigation block.
            if ($page->pagetype !== 'mod-scorm-report') {
                $this->add_fake_nav_block_later($page);
            }
        }

        // Get the manual completion form.
        if ($page->pagelayout === 'incourse') {
            $completioninfo = new completion_info($page->course);
            $cancomplete = isloggedin() && !isguestuser();
            $thismod = $page->cm;
            $completionicon = false;
            $output = '';
            $completionactivated = true;

            // The check for the completioncriteria is only needed for course completion.
            /*$completionactivated = false;
            $completioncriteria = $completioninfo->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY);
            foreach ($completioncriteria as $completioncriterium) {
                if ($completioncriterium->moduleinstance == $thismod->id) {
                    $completionactivated = true;
                    break;
                }
            }*/

            if (!is_null($thismod) && $completionactivated && $cancomplete &&
                $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE
            ) {
                $completiondata = $completioninfo->get_data($thismod, true);
                $completion = $completioninfo->is_enabled($thismod);
                if ($completion == COMPLETION_TRACKING_MANUAL) {
                    switch ($completiondata->completionstate) {
                        case COMPLETION_INCOMPLETE:
                            $completionicon = 'manual-n';
                            break;
                        case COMPLETION_COMPLETE:
                            $completionicon = 'manual-y';
                            break;
                    }
                }

                if ($completionicon) {
                    $formattedname = $thismod->get_formatted_name();
                    $imgalt = get_string('completion-alt-' . $completionicon, 'completion', $formattedname);

                    if ($completion == COMPLETION_TRACKING_MANUAL) {
                        $imgtitle = get_string('completion-title-' . $completionicon, 'completion', $formattedname);
                        $newstate = $completiondata->completionstate ==
                        COMPLETION_COMPLETE ? COMPLETION_INCOMPLETE : COMPLETION_COMPLETE;
                        // In manual mode the icon is a toggle form...

                        // If this completion state is used by the
                        // conditional activities system, we need to turn
                        // off the JS.
                        $extraclass = '';
                        if (!empty($CFG->enableavailability) &&
                            core_availability\info::completion_value_used($page->course, $thismod->id)
                        ) {
                            $extraclass = ' preventjs';
                        }
                        $output .= html_writer::start_tag('form', array('method' => 'post',
                            'action' => new moodle_url('/course/togglecompletion.php'),
                            'class' => 'togglecompletion' . $extraclass));
                        $output .= html_writer::start_tag('div');
                        $output .= html_writer::empty_tag('input', array(
                            'type' => 'hidden', 'name' => 'id', 'value' => $thismod->id));
                        $output .= html_writer::empty_tag('input', array(
                            'type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
                        $output .= html_writer::empty_tag('input', array(
                            'type' => 'hidden', 'name' => 'modulename', 'value' => $thismod->name));
                        $output .= html_writer::empty_tag('input', array(
                            'type' => 'hidden', 'name' => 'completionstate', 'value' => $newstate));
                        $output .= get_string('completion-title-' . $completionicon, 'completion', '');
                        $output .= html_writer::empty_tag('input', array(
                            'type' => 'image',
                            'src' => $OUTPUT->pix_url('i/completion-' . $completionicon),
                            'alt' => $imgalt, 'title' => $imgtitle,
                            'aria-live' => 'polite'));
                        $output .= html_writer::end_tag('div');
                        $output .= html_writer::end_tag('form');

                        $this->manualcompletionhtml = $output;
                    }
                }
            }
        }
    }

    /**
     * Checks if given section has another section among it's parents
     *
     * @param int|section_info $section   child section
     * @param int              $parentnum parent section number
     *
     * @return boolean
     */
    protected function section_has_parent($section, $parentnum) {
        if (!$section) {
            return false;
        }
        $section = $this->get_section($section);
        if (!$section->section || empty($section->parent)) {
            return false;
        } else if ($section->parent == $parentnum) {
            return true;
        } else if ($section->parent == 0) {
            return false;
        } else if ($section->parent >= $section->section) {
            // Some error.
            return false;
        } else {
            return $this->section_has_parent($section->parent, $parentnum);
        }
    }

    /**
     * Adds a section to navigation node, loads modules and subsections if necessary
     *
     * @param global_navigation $navigation
     * @param navigation_node   $node
     * @param section_info      $section
     *
     * @return null|navigation_node
     */
    protected function navigation_add_section($navigation, navigation_node $node, $section) {
        if (!$section->uservisible) {
            return null;
        }
        $sectionname = get_section_name($this->get_course(), $section);

        $sectionnode = $node->add($sectionname, null, navigation_node::TYPE_SECTION, null,
            $section->id);
        $sectionnode->nodetype = navigation_node::NODETYPE_BRANCH;
        $sectionnode->hidden = !$section->visible || !$section->available;
        if ($this->section_has_parent($navigation->includesectionnum, $section->section)
            || $navigation->includesectionnum == $section->section
        ) {
            $modinfo = get_fast_modinfo($this->courseid);
            if (!empty($modinfo->sections[$section->section])) {
                foreach ($modinfo->sections[$section->section] as $cmid) {
                    $this->navigation_add_activity($sectionnode, $modinfo->get_cm($cmid));
                }
            }
        }

        return $sectionnode;
    }

    /**
     * Adds a course module to the navigation node
     *
     * @param navigation_node $node
     * @param cm_info         $cm
     *
     * @return null|navigation_node
     */
    protected function navigation_add_activity(navigation_node $node, $cm) {
        if (!$cm->uservisible || $cm->modname === 'label') {
            return null;
        }
        $activityname = format_string($cm->name, true,
            array('context' => context_module::instance($cm->id)));
        $action = $cm->url;
        if ($cm->icon) {
            $icon = new pix_icon($cm->icon, $cm->modfullname, $cm->iconcomponent);
        } else {
            $icon = new pix_icon('icon', $cm->modfullname, $cm->modname);
        }

        $key = $cm->id;
        $type = navigation_node::TYPE_ACTIVITY;

        // Check if node exists, if not add it.
        $activitynode = $node->get($key, $type);
        if (!$activitynode) {
            $activitynode = $node->add($activityname, $action, $type,
                null, $key, $icon);
        }

        if (global_navigation::module_extends_navigation($cm->modname)) {
            $activitynode->nodetype = navigation_node::NODETYPE_BRANCH;
        } else {
            $activitynode->nodetype = navigation_node::NODETYPE_LEAF;
        }

        return $activitynode;
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
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and
     * BLOCK_POS_RIGHT each of values is an array of block names
     * (for left and right side columns)
     */
    public function get_default_blocks() {
        return array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => array()
        );
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * Classroom format uses the following options:
     * - coursedisplay
     * - numsections
     * - hiddensections
     *
     *
     * @param               bool $foreditform
     *
     * @return                      array
     *
     * @updateDate      14/05/2014
     * @author          eFaktor
     *
     * Description
     * Add the Home Page Generator Fields
     *
     * @updateDate      17/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Course Locations - New Version
     * Add Course Sectors
     *
     * @updateDate      24/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add an extra field. Time from to
     *
     * @updateDate      21/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the 'ratings' option format
     *
     * @updateDate      15/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Remove video
     *
     * @updateDate      10/08/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * From - To date
     *
     * @updateDate      09/06/2017
     * @author          eFaktor     (fbv)
     * Location/sector not available for the manager --> Readonly
     */
    public function course_format_options($foreditform = false) {
        /* Variables    */
        global $USER, $COURSE;
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
        $lstLocations   = course_page::get_course_locations_list($USER->id);
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
        } else {
            $lstSectors = array();
            $lstSectors[0] = get_string('sel_sector', 'local_friadmin');
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
                    'label' => null,
                    'default' => COURSE_DISPLAY_MULTIPAGE,
                    'type' => PARAM_INT,
                    'element_type' => 'hidden',
                ),
                /**
                 * @updateDate  08/05/2014
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
                    'type' => PARAM_INT,
                    'default' => 0,
                ),
                'course_sector' => array(
                    'default' => 0,
                    'type' => PARAM_RAW,
                ),
                'time' => array(
                    'type' => PARAM_TEXT,
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
                /**
                 * @updateDate  08/05/2014
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
                    'label' => get_string('home_prerequisities', 'format_classroom'),
                    'element_type' => 'textarea',
                    'element_attributes' => array(
                        0 => 'rows="5" style="width:95%;"'
                    )
                ),
                'producedby' => array(
                    'label' => get_string('home_producedby', 'format_classroom'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        0 => 'style="width:95%;"'
                    )
                ),
                'course_location' => array(
                    'label' => get_string('home_location', 'format_classroom'),
                    'element_type' => 'select',
                    'element_attributes' => array($lstLocations,$readonly)
                ),
                'course_sector' => array(
                    'label' => get_string('home_sector', 'format_classroom'),
                    'element_type' => 'select',
                    'element_attributes' => array($lstSectors, 'multiple ' . $readonly)
                ),
                'time' => array(
                    'label' => get_string('home_time_from_to', 'format_classroom'),
                    'help' => 'home_time_from_to',
                    'help_component' => 'format_classroom',
                    'element_type' => 'textarea',
                    'element_attributes' => array(0 => 'rows="4" style="width:50%;"'),
                ),
                'length' => array(
                    'label' => get_string('home_length', 'format_classroom'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        0 => 'style="width:95%;"'
                    )
                ),
                'effort' => array(
                    'label' => get_string('home_effort', 'format_classroom'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        0 => 'style="width:95%;"'
                    )
                )

            );
            $courseformatoptions = array_merge_recursive($courseformatoptions,
                $courseformatoptionsedit);
        }

        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@link course_edit_form::definition_after_data()}.
     *
     *
     * @param       MoodleQuickForm $mform      form the elements are added to.
     * @param       bool            $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     *
     * @return                      array           array of references to the added form elements.
     *
     * @updateDate      27/05/2014
     * @author          eFaktor     (fbv)
     *
     * Description
     * Don't call create_edit_form      --> parent
     * Different functionality          --> Course Home Page
     *
     * @updateDate      21/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the 'ratings' option format
     *
     * @updateDate      21/03/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * The value of sectors selectors depends on the location chosen. Uses javascript
     *
     * @updateDate      15/06/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Remove pagevideo
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        //$elements = parent::create_edit_form_elements($mform, $forsection);

        $elements = array();
        if ($forsection) {
            $options = $this->section_format_options(true);
        } else {
            $options = $this->course_format_options(true);
        }

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
            // Switch.

            if (is_null($mform->getElementValue($optionname)) && isset($option['default'])) {
                $mform->setDefault($optionname, $option['default']);
            }
        }
        // For.

        // Increase the number of sections combo box values if the user has increased
        // the number of sections using the icon on the course page beyond course
        // 'maxsections' or course 'maxsections' has been reduced below the number of
        // sections already set for the course on the site administration course
        // defaults page.  This is so that the number of sections is not reduced leaving
        // unintended orphaned activities / resources.
        if (!$forsection) {
            $maxsections = get_config('moodlecourse', 'maxsections');
            $numsections = $mform->getElementValue('numsections');
            $numsections = $numsections[0];
            if ($numsections > $maxsections) {
                $element = $mform->getElement('numsections');
                for ($i = $maxsections + 1; $i <= $numsections; $i++) {
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
     * @return                      bool whether there were any changes to the options values
     *
     * @updateDate  27/05/2014
     * @author      eFaktor     (fbv)
     *
     * Description
     * Update the course format options.
     *
     * @updateDate  17/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Integrate course sector
     *
     * @updateDate  21/01/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add the 'ratings' option format
     *
     * @updateDate  10/08/2016
     * @author      eFaktor     (fbv9
     *
     * Description
     * From - To date
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
                    // Homesummary_editor.

                    break;

                case 'pagegraphics':
                    if (isset($data['pagegraphics']) && isset($data['pagegraphics_filemanager'])) {
                        $graphic_id = course_page::postupdate_homegraphics_manager($this->courseid, 'pagegraphics', 'pagegraphics_filemanager', $data['pagegraphics_filemanager']);
                        $data[$key] = $graphic_id;
                    }

                    break;

                case 'pagevideo':
                    if (isset($data['deletevideo']) && ($data['deletevideo'])) {
                        $delete = true;
                    } else {
                        $delete = false;
                    }
                    // If_delete.
                    if (isset($data['pagevideo']) && isset($data['pagevideo_filemanager'])) {
                        $videoid = course_page::get_home_graphics_video($data['pagevideo'], 'pagevideo', $data['pagevideo_filemanager'], $delete);
                        if ($videoid) {
                            $data[$key] = $videoid;
                        } // If_graphic_id.
                    } // If_page_video_pagevideo_filemanager.

                    break;

                case 'course_sector':
                    if (isset($_COOKIE['sectors'])) {
                        $data['course_sector'] = $_COOKIE['sectors'];
                    } else {
                        $data['course_sector'] = 0;
                    }

                    break;

                default:
                    break;
            } // Switch_key.

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
                        // 'numsections' will be set to default.
                        $data['numsections'] = $maxsection;
                    } // If_maxsection.
                } // If_array_key.
            } // If_array_key.
        } // For_options.

        return $this->update_format_options($data);
    }

    /**
     * Display the special course navigation above the course content
     * and the blocks.
     *
     * @return format_classroom_specialnav | null
     */
    public function course_header() {
        global $USER, $CFG, $PAGE, $FULLME;

        if (!($PAGE->pagetype === 'course-view-classroom' ||
            $PAGE->pagetype === 'local-course_page-home_page' ||
            strpos($PAGE->pagetype, 'mod-') !== false)
        ) {
            return new format_classroom_specialnav('');
        }

        $strcourse = get_string('course', 'format_classroom');
        $strdescription = get_string('description', 'format_classroom');
        $strforums = get_string('forums', 'format_classroom');
        $strprogress = get_string('progress', 'format_classroom');
        $strcoursehomepage = get_string('coursehomepage', 'format_netcourse');
        $mymoodle = get_string('mymoodle', 'format_classroom');

        if (is_null($this->openlast)) {
            $this->openlast = new format_classroom_openlast($PAGE,
                $PAGE->course, $USER, $FULLME);
        }

        $editing = $this->openlast->is_editing();
        $description = optional_param('description', 0, PARAM_BOOL);

        list($text, $module, $openedcmid, $courseurl) =
            $this->openlast->get_last_opened();
        $modinfo = $this->openlast->get_modinfo();

        // If the user never visited the course the last opened page URL is null.
        // In this case show the first activity/resource in the section 1
        if ($editing) {
            $courseurl = new moodle_url('/course/view.php?id=' . $PAGE->course->id);
        } else if (is_null($courseurl)) {
            if (isset($modinfo->sections[1])) {
                $cmid = $modinfo->sections[1][0];

                $courseurl = $modinfo->cms[$cmid]->url;
            } else {
                $courseurl = new moodle_url('#');
            }
        }

        // Create the url for the course overview which is the first
        // resource in section 0.
        if (isset($modinfo->sections[0])) {
            $cmid = $modinfo->sections[0][0];
            $descriptionurl = $modinfo->cms[$cmid]->url;
            if (is_null($descriptionurl)) {
                $descriptionurl = new moodle_url('#');
            }
        } else {
            $descriptionurl = new moodle_url('#');
        }

        // Set the "Discussion" link to the first forum in section 0.
        $discussionurl = new moodle_url('#');
        if (!is_null($this->openlast->get_section0modids())) {
            foreach ($this->openlast->get_section0modids() as $cmid) {
                if ($modinfo->cms[$cmid]->modname === 'forum') {
                    $discussionurl = $modinfo->cms[$cmid]->url;
                    break;
                }
            }
        }

        // Set the link to the progress page
        // Dont't show the progress button when no completion activity set.
        $progressurl = false;
        if (!is_null($this->openlast->get_section0modids())) {
            foreach ($this->openlast->get_section0modids() as $cmid) {
                if ($modinfo->cms[$cmid]->modname === 'completionreport') {
                    if ($modinfo->cms[$cmid]->uservisible) {
                        $progressurl = $modinfo->cms[$cmid]->url;
                    }
                    break;
                }
            }
        }

        // Set the link to the course home page
        // Dont't show the course home button when it is not set in the course.
        $coursehomepageurl = false;
        if ($this->check_course_homepage_active($PAGE->course->id)) {
            $coursehomepageurl = new moodle_url('/local/course_page/home_page.php',
                array('id' => $PAGE->course->id, 'start' => 0));
        }

        $courseactive = '';
        $discussactive = '';
        $descactive = '';
        $progressactive = '';
        $coursehomepactive = '';
        if ($PAGE->url->compare($descriptionurl, URL_MATCH_EXACT)) {
            $descactive = ' btn-primary active';
        } else if ($PAGE->url->compare($discussionurl, URL_MATCH_EXACT)) {
            $discussactive = ' btn-primary active';
        } else if ($progressurl && $PAGE->url->compare($progressurl, URL_MATCH_EXACT)) {
            $progressactive = ' btn-primary active';
        } else if ($coursehomepageurl && $PAGE->url->compare($coursehomepageurl, URL_MATCH_BASE)) {
            $coursehomepactive = ' btn-primary active';
        } else {
            $courseactive = ' btn-primary active';
        }

        $courseurl = $courseurl->out();

        // Add the nonav parameter to hide the course navigation.
        if ($discussionurl->get_host() !== "") {
            $discussionurl->param('nonav', 1);
        }
        $discussionurl = $discussionurl->out();

        // Add the nonav parameter to hide the course navigation.
        if ($descriptionurl->get_host() !== "") {
            $descriptionurl->param('nonav', 1);
            $descriptionurl->param('description', 1);
        }
        $descriptionurl = $descriptionurl->out();

        if ($progressurl) {
            // Add the nonav parameter to hide the course navigation.
            if ($progressurl->get_host() !== "") {
                $progressurl->param('nonav', 1);
            }
            $progressurl = $progressurl->out();
        }

        if ($coursehomepageurl) {
            $coursehomepageurl = $coursehomepageurl->out();
        }

        $out = '<div class="btn-toolbar">
            <a class="btn' . $courseactive . '" type="button" href="' .
            $courseurl . '">' . $strcourse . '</a>
            <a class="btn' . $descactive . '" type="button" href="' .
            $descriptionurl . '">' . $strdescription . '</a>
            <a class="btn' . $discussactive . '" type="button" href="' .
            $discussionurl . '">' . $strforums . '</a>';
        if ($progressurl) {
            $out .= '<a class="btn' . $progressactive . '" type="button" href="' .
                $progressurl . '">' . $strprogress . '</a>';
        }
        if ($coursehomepageurl) {
            $out .= '<a class="btn' . $coursehomepactive . '" type="button" href="' .
                $coursehomepageurl . '">' . $strcoursehomepage . '</a>';
        }
        $out .= '</div>';

        return new format_classroom_specialnav($out);
    }

    /**
     * Check if the course homepage option is active for the given course
     *
     * @param Int $courseid The course id to check for
     *
     * @return bool Return true if the course homepage is set
     */
    protected function check_course_homepage_active($courseid) {
        global $DB;

        $sql = "SELECT *
            FROM {course_format_options}
            WHERE";

        $where_clause = ' ' .
            $DB->sql_compare_text('courseid') . ' = ' . $DB->sql_compare_text(':courseid') .
            ' AND ' .
            $DB->sql_compare_text('name') . ' = ' . $DB->sql_compare_text(':name') .
            ' AND ' .
            $DB->sql_compare_text('value') . ' = ' . $DB->sql_compare_text(':value');

        $params = array(
            'courseid' => $courseid,
            'name' => 'homepage',
            'value' => '1'
        );

        $isactive = $DB->record_exists_sql($sql . $where_clause, $params);

        return $isactive;
    }

    /**
     * Display the special module navigation above the content
     * between the blocks.
     *
     * Create the module navigation for each module.
     *
     * @return format_classroom_specialnav | null
     */
    public function course_content_header() {
        global $CFG, $DB, $cm, $PAGE;

        $retval = null;

        if (!is_null($cm)) {
            if ($cm->modname === 'lesson') {
                global $fullme, $pageid, $USER, $lesson, $lessonoutput;

                // Don't need the following information for the lessonexport.
                if (!empty($fullme) && strpos($fullme, 'lessonexport') !== false) {
                    return $retval;
                }

                // If no lesson page is set we don't need the following information.
                if (is_null($pageid)) {
                    return $retval;
                }

                // Hack: page->cm is null in this state, add it here.
                $PAGE->set_cm($cm);

                // Get the lesson library with the lesson class and create a new instance
                // and get the lesson renderer if the global lesson object is not present.
                if (empty($lesson)) {
                    require_once($CFG->dirroot . '/mod/lesson/locallib.php');
                    $lesson = new lesson($DB->get_record('lesson',
                        array('id' => $cm->instance), '*', MUST_EXIST));
                    // If the progressbar is activated for this course and the
                    // global lesson object is not set then turn the progressbar off in
                    // the lesson setting to avoid duplication. The global lesson object
                    // should be set.
                    if ($lesson->properties()->progressbar == 1) {
                        $DB->set_field('lesson', 'progressbar', 0,
                            array('id' => $lesson->properties()->id));
                    }
                }
                if (empty($lessonoutput)) {
                    $lessonoutput_local = $PAGE->get_renderer('mod_lesson');
                } else {
                    $lessonoutput_local = $lessonoutput;
                }

                // Force the progressbar on, render the progressbar
                // and force the progressbar off to avoid the lesson's own progressbar
                // at the bottom of the lesson page. The progressbar will be rendered
                // independent of the lesson settings.
                if (!empty($lesson->pages)) {
                    $lesson->properties()->progressbar = 1;
                }

                // The lesson page with the pageid -9 is the last lesson page
                // On the last page force the progress bar to 100% which happens when
                // $USER->modattempts for this lesson is true.
                $showgrades = $PAGE->course->showgrades;
                $actualpageid = null;
                $nextpage = $lesson->get_next_page($pageid);
                // Force the progress bar to 100%.
                if ($pageid === -9) {
                    if (!isset($USER->modattempts[$lesson->id])) {
                        $USER->modattempts[$lesson->id] = true;
                    }
                }
                // The last lesson page is shown
                // Either when the course settings showgrade is on and the
                // lesson nextpage returns -9 and the global pageid is -9
                // OR showgrade is off and the lesson nexpage returns -9
                // and the global pageid is null.
                $lastpage = self::LESSON_NOT_LASTPAGE;
                if ($showgrades && $nextpage === -9 && $pageid === -9) {
                    $lastpage = self::LESSON_LASTPAGE_GRADINGON;
                } else if (!$showgrades && $nextpage === -9 && empty($pageid)) {
                    $lastpage = self::LESSON_LASTPAGE_GRADINGOFF;
                }
                self::$lastlessonpage = $lastpage;

                $progressbar = $lessonoutput_local->progress_bar($lesson);
                $lesson->properties()->progressbar = 0;

                // Create the object for the course content header renderer.
                $retval = new format_classroom_specialnav($progressbar);
            }
        }

        return $retval;
    }

    /**
     * Display the special module navigation below the content
     * between the blocks.
     *
     * @return format_classroom_specialnav | null
     */
    public function course_content_footer() {
        global $cm;

        $retval = null;

        if (!is_null($cm)) {
            if ($cm->modname === 'feedback') {
                $strmessage = get_string('feedbacklastpage', 'format_classroom');
                $js = <<< EOT
                <script>
                YUI().use("node", function(Y) {
                    var regionmain = Y.one("#region-main");
                    var continuebutton = regionmain.one(".continuebutton");
                    if (continuebutton) {
                        // console.log('feedback with continue btn.');
                        var form = continuebutton.one("form");
                        if (form) {
                            var action = form.getAttribute('action');
                            if (action.indexOf('course/view.php') !== -1 ||
                                action === '#'
                            ) {
                                // console.log('feedback with course link.');
                                var box = Y.one("#region-main").one(".generalbox");
                                if (box) {
                                    box
                                        .set("text", "$strmessage")
                                        .addClass("lastfeedbackpage-info");
                                     form.remove();
                                }
                            }
                        }
                    }
                });
                </script>
EOT;
                $retval = new format_classroom_specialnav($js);
            }

            // If the last lesson page is reached add JavaScript to the page
            // which manipulates the page.
            else if (self::$lastlessonpage === self::LESSON_LASTPAGE_GRADINGON) {
                $strmessage = get_string('lessonlastpageon', 'format_classroom');
                $js = <<< EOT
            <script>
            YUI().use("node", function(Y) {
                var lbtns = Y.all(".lessonbutton.standardbutton");
                lbtns.remove();
                var box = Y.one("#region-main").one(".generalbox");
                if (box) {
                    box
                        .set("text", "$strmessage")
                        .addClass("lastlessonpage-info");
                }
            });
            </script>
EOT;
                $retval = new format_classroom_specialnav($js);

            } else if (self::$lastlessonpage === self::LESSON_LASTPAGE_GRADINGOFF) {
                $strmessage = get_string('lessonlastpageoff', 'format_classroom');
                $js = <<< EOT
            <script>
            YUI().use("node", function(Y) {
                var regionmain = Y.one("#region-main");
                var singlebtn = regionmain.one(".singlebutton");
                if (singlebtn) {
                    var pageid = singlebtn.one("input[name=pageid]");
                    if (pageid && pageid.get("value") == -9) {
                        singlebtn.one("form").remove();
                        singlebtn
                            .set("text", "$strmessage")
                            .removeClass("singlebutton")
                            .addClass("box generalbox lastlessonpage-info");
                    }
                }
            });
            </script>
EOT;
                $retval = new format_classroom_specialnav($js);
            } else if ($cm->modname === 'scorm') {
                $scormdata = $this->get_scorm_data($cm);
                $url = new moodle_url('/mod/scorm/view.php',
                    array('id' => $cm->id));

                // Set parameters for the link.
                //                $params['rel'] = 'lightbox';
                $params['class'] = 'btn btn-lightbox scorm';
                $params['data-scormid'] = $cm->id;
                if (!is_null($scormdata)) {
                    $params['data-scormwidth'] = $scormdata->width;
                    $params['data-scormheight'] = $scormdata->height;
                    $params['data-scormlaunch'] = $scormdata->launch;
                }

                $link = html_writer::link($url, get_string('enter', 'mod_scorm'), $params);

                $js = <<< EOT
                <script>
                YUI().use("node", function(Y) {
                    if (WURFL && WURFL.form_factor === "Desktop") {
                        var regionmain = Y.one("#region-main");
                        var scormviewform = regionmain.one("#scormviewform");
                        if (scormviewform) {
                            scormviewform.replace('{$link}');
//                            scormviewform.get('parentNode').append('{$link}');
                        }
                    }
                });
                </script>
EOT;
                $retval = new format_classroom_specialnav($js);
            }
        }

        return $retval;
    }

    /**
     * Get the width and height set in the SCROM module settings.
     *
     * @param object $cm The SCORM course module
     *
     * @return stdClass The width and hieght set for the SCORM module
     */
    protected function get_scorm_data($cm) {
        global $DB;

        $scormdata = null;

        if ($result = $DB->get_record('scorm', array('id' => $cm->instance))) {
            $scormdata = new stdClass();
            $scormdata->width = $result->width;
            $scormdata->height = $result->height;
            $scormdata->launch = $result->launch;
        }

        return $scormdata;
    }

    /**
     * Add the course navigation sticky block at the top of the default region
     */
    public function add_fake_nav_block($page) {
        global $CFG, $COURSE, $SCRIPT;

        $blockid = 'cnav';

        $this->page = $page;

        // The pagelayout has not been set yet so no block regions are known
        // Set the pagelayout. Use the URL in $SCRIPT to detect if
        // the course page or a resource is viewed.
        if ($SCRIPT === '/course/view.php') {
            $page->set_pagelayout('course');
        } else {
            $page->set_pagelayout('incourse');
        }

        $this->get_required_javascript($blockid);

        $content = $this->get_content();

        $bc = new block_contents();
        $bc->title = '';
        $bc->annotation = '';
        $bc->attributes['id'] = 'inst' . $blockid;
        $bc->attributes['class'] = 'block no-header block_navigation';
        $bc->attributes['data-block'] = 'navigation';
        $bc->attributes['data-instanceid'] = $blockid;
        $bc->attributes['role'] = 'navigation';
        $bc->attributes['aria-label'] = 'Course navigation';
        $bc->content = $content;

        $defaultregion = $page->blocks->get_default_region();
        $page->blocks->add_fake_block($bc, $defaultregion);
    }

    /**
     * Add the course navigation sticky block at the top of the default region.
     */
    public function add_fake_nav_block_later($page) {
        global $CFG, $COURSE, $SCRIPT;

        // Exclude the navigation changes and the fake navigation block on report pages
        // because some code on report pages expects navigation nodes we exclude.
        if ($SCRIPT === '/report/log/user.php' ||
            $SCRIPT === '/report/outline/user.php' ||
            $SCRIPT === '/course/user.php'
        ) {
            return null;
        }

        $blockid = 'cnav';

        $this->page = $page;

        $this->get_required_javascript($blockid);

        $content = $this->get_content();

        $bc = new block_contents();
        $bc->title = '';
        $bc->annotation = '';
        $bc->attributes['id'] = 'inst' . $blockid;
        $bc->attributes['class'] = 'block no-header block_navigation';
        $bc->attributes['data-block'] = 'navigation';
        $bc->attributes['data-instanceid'] = $blockid;
        $bc->attributes['role'] = 'navigation';
        $bc->attributes['aria-label'] = 'Course navigation';
        $bc->content = $content;

        // Place the nav in the 'side-pre' region
        $page->blocks->add_fake_block($bc, 'side-pre');
    }

    /**
     * Gets the content for this block by grabbing it from $this->page
     *
     * @return object $this->content
     */
    protected function get_content() {
        $trimmode = self::TRIM_RIGHT;
        $trimlength = 50;

        // Get the course_navigation object or don't display the block if none provided.
        if (!$course_navigation = $this->get_course_navigation()) {
            return null;
        }

        // Get the current course nodes and extract the course node collection
        // The current course has only one collection, can be fetched with "last".
        // $thiscourse_navigation = $course_navigation->get("currentcourse");
        $thiscourse_navigation = clone($course_navigation->get("currentcourse"));

        // Return null if the currentcourse has no navigation items.
        if (empty($thiscourse_navigation->children)) {
            return null;
        }

        $thiscourse_navigation = $thiscourse_navigation->children->last();

        // Remove all nodes which are not section nodes.
        foreach ($thiscourse_navigation->children as $node) {
            if ($node->type !== navigation_node::TYPE_SECTION) {
                $node->remove();
            }
        }

        // Check if there is an active node
        // If not make the current activity node active (pages within lessons for example).
        global $FULLME, $cm;

        $fullmeurl = new moodle_url($FULLME);
        $activenode = $thiscourse_navigation->find_active_node();

        // Check if there is an active node - activities from section 0
        // are not included in the left navigation and when the user is
        // going to edit such an activity find_active_node returns false.
        if ($activenode) {
            $activeaction = $activenode->action;

            // Walk all nodes and find the node with the same action url as fullme
            // if the action and the fullme URL don't match.
            // Deactivate the wrong node and activate the right one.
            if (!$activeaction->compare($fullmeurl, URL_MATCH_PARAMS)) {
                if (!is_null($cm)) {
                    $cmnode = $thiscourse_navigation->find($cm->id, navigation_node::TYPE_ACTIVITY);
                    if ($cmnode) {
                        $activenode->make_inactive();
                        $activenode->parent->forceopen = false;
                        $cmnode->make_active();
                    }
                } else if ($this->page->pagetype === 'mod-quiz-attempt' ||
                    $this->page->pagetype === 'mod-quiz-summary' ||
                    $this->page->pagetype === 'mod-quiz-review'
                ) {
                    // In quiz attempts the action url and the node fullme url don't match -
                    // get the quiz attemptobject, from that get the course module
                    // and activate the quiz node.
                    $attemptid = $fullmeurl->get_param('attempt');
                    // The attemptid may be null, so check
                    if ($attemptid) {
                        $attemptobj = quiz_attempt::create($attemptid);
                        $cm = $attemptobj->get_cm();
                        if (!is_null($cm)) {
                            $cmnode = $thiscourse_navigation->find($cm->id, navigation_node::TYPE_ACTIVITY);
                            if ($cmnode) {
                                $activenode->make_inactive();
                                $activenode->parent->forceopen = false;
                                $cmnode->make_active();
                            }
                        }
                    }
                } else {
                    $activitynodes = $thiscourse_navigation->
                    find_all_of_type(navigation_node::TYPE_ACTIVITY);
                    foreach ($activitynodes as $activitynode) {
                        if ($activitynode->action->compare($fullmeurl, URL_MATCH_PARAMS)) {
                            $activenode->make_inactive();
                            $activenode->parent->forceopen = false;
                            $activitynode->make_active();
                            break;
                        }
                    }
                }
            }
        }

        // Remove all activty submenu entries with the node type TYPE_SETTING.
        $customnodes = $thiscourse_navigation->
        find_all_of_type(navigation_node::TYPE_SETTING);
        foreach ($customnodes as $customnode) {
            $customnode->remove();
        }

        // Remove all activty submenu entries with the node type TYPE_CUSTOM.
        $customnodes = $thiscourse_navigation->
        find_all_of_type(navigation_node::TYPE_CUSTOM);
        foreach ($customnodes as $customnode) {
            $customnode->remove();
        }

        // Set the completion state of the navigation nodes.
        // Walk all TYPE_ACTIVITY and TYPE_RESOURCE nodes and set the completion state.
        $course = $this->page->course;
        $coursemods = get_course_mods($course->id);
        $completioninfo = new completion_info($course);
        $cancomplete = isloggedin() && !isguestuser();
        $activitynodes = $thiscourse_navigation->
        find_all_of_type(navigation_node::TYPE_ACTIVITY);
        /* @var $oneactivitynode navigation_node */
        foreach ($activitynodes as $oneactivitynode) {
            $nodeinfo = $oneactivitynode;
            $thismod = $coursemods[$nodeinfo->key];
            if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                $completiondata = $completioninfo->get_data($thismod, true);
                if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                    $completiondata->completionstate == COMPLETION_COMPLETE_PASS
                ) {
                    $nodeinfo->add_class('completed');
                }
            }
        }

        $resourcenodes = $thiscourse_navigation->
        find_all_of_type(navigation_node::TYPE_RESOURCE);
        /* @var $oneresourcenode navigation_node */
        foreach ($resourcenodes as $oneresourcenode) {
            $nodeinfo = $oneresourcenode;
            $thismod = $coursemods[$nodeinfo->key];
            if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                $completiondata = $completioninfo->get_data($thismod, true);
                if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                    $completiondata->completionstate == COMPLETION_COMPLETE_PASS
                ) {
                    $nodeinfo->add_class('completed');
                }
            }
        }

        $expansionlimit = null;
        $this->trim($thiscourse_navigation, $trimmode, $trimlength, ceil($trimlength / 2));

        // Get the expandable items so we can pass them to JS.
        $expandable = array();
        $thiscourse_navigation->find_expandable($expandable);
        if ($expansionlimit) {
            foreach ($expandable as $key => $node) {
                if ($node['type'] > $expansionlimit &&
                    !($expansionlimit == navigation_node::TYPE_COURSE &&
                        $node['type'] == $expansionlimit &&
                        $node['branchid'] == SITEID)
                ) {
                    unset($expandable[$key]);
                }
            }
        }

        $this->page->requires->data_for_js('navtreeexpansions' . 1,
            $expandable);

        $options = array();
        $options['linkcategories'] = false;

        // Grab the items to display.
        //        $renderer = $this->get_renderer($this->page);
        $renderer = $this->page->get_renderer('format_' . $this->get_format(), 'fakeblock');
        $content = $renderer->course_navigation_tree($thiscourse_navigation,
            $expansionlimit, $options);

        //        $content ='test';

        return $content;
    }

    /**
     * Returns the course_navigation
     *
     * @return navigation_node The course_navigation object to display
     */
    protected function get_course_navigation() {
        // Initialise (only actually happens if it hasn't already been done yet)
        $this->page->navigation->initialise();

        return clone($this->page->navigation);
    }

    /**
     * Gets Javascript that may be required for course_navigation
     */
    protected function get_required_javascript($id) {
        global $CFG;

        $limit = 20;
        $expansionlimit = 0;
        $arguments = array(
            'id' => $id,
            'instance' => $id,
            'candock' => false,
            'courselimit' => $limit,
            'expansionlimit' => $expansionlimit
        );
        $this->page->requires->string_for_js('viewallcourses', 'moodle');
        //$this->page->requires->yui_module('moodle-format_classroom-classroom_navigation',
        //    'M.format_classroom.init_add_tree', array($arguments));

        $arguments = array(
            'instanceid' => $id
        );
        $this->page->requires->js_call_amd('format_classroom/navblock', 'init', $arguments);
    }

    /**
     * Trims the text and shorttext properties of this node and optionally
     * all of its children.
     *
     * @param navigation_node $node
     * @param int             $mode    One of navigation_node::TRIM_*
     * @param int             $long    The length to trim text to
     * @param int             $short   The length to trim shorttext to
     * @param bool            $recurse Recurse all children
     */
    public function trim(navigation_node $node, $mode = 1, $long = 50, $short = 25,
        $recurse = true) {
        switch ($mode) {
            case self::TRIM_RIGHT :
                if (core_text::strlen($node->text) > ($long + 3)) {
                    // Truncate the text to $long characters.
                    $node->text = $this->trim_right($node->text, $long);
                }
                if (is_string($node->shorttext) &&
                    core_text::strlen($node->shorttext) > ($short + 3)
                ) {
                    // Truncate the shorttext.
                    $node->shorttext = $this->trim_right($node->shorttext, $short);
                }
                break;
            case self::TRIM_LEFT :
                if (core_text::strlen($node->text) > ($long + 3)) {
                    // Truncate the text to $long characters.
                    $node->text = $this->trim_left($node->text, $long);
                }
                if (is_string($node->shorttext) &&
                    core_text::strlen($node->shorttext) > ($short + 3)
                ) {
                    // Truncate the shorttext.
                    $node->shorttext = $this->trim_left($node->shorttext, $short);
                }
                break;
            case self::TRIM_CENTER :
                if (core_text::strlen($node->text) > ($long + 3)) {
                    // Truncate the text to $long characters.
                    $node->text = $this->trim_center($node->text, $long);
                }
                if (is_string($node->shorttext) &&
                    core_text::strlen($node->shorttext) > ($short + 3)
                ) {
                    // Truncate the shorttext.
                    $node->shorttext = $this->trim_center($node->shorttext, $short);
                }
                break;
        }
        if ($recurse && $node->children->count()) {
            foreach ($node->children as &$child) {
                $this->trim($child, $mode, $long, $short, true);
            }
        }
    }

    /**
     * Truncate a string from the left
     *
     * @param string $string The string to truncate
     * @param int    $length The length to truncate to
     *
     * @return string The truncated string
     */
    protected function trim_left($string, $length) {
        //        return '...' . core_text::substr($string,
        //            core_text::strlen($string) - $length, $length);
        return '... ' . shorten_text($string, $ideal = $length, $exact = false, $ending = '');
    }

    /**
     * Truncate a string from the right
     *
     * @param string $string The string to truncate
     * @param int    $length The length to truncate to
     *
     * @return string The truncated string
     */
    protected function trim_right($string, $length) {
        return shorten_text($string, $ideal = $length, $exact = false, $ending = '...');
    }

    /**
     * Truncate a string in the center
     *
     * @param string $string The string to truncate
     * @param int    $length The length to truncate to
     *
     * @return string The truncated string
     */
    protected function trim_center($string, $length) {
        $trimlength = ceil($length / 2);
        $start = core_text::substr($string, 0, $trimlength);
        $end = core_text::substr($string, core_text::strlen($string) - $trimlength);
        $string = $start . '...' . $end;

        return $string;
    }
}

/**
 * Class storing information to be displayed in course header
 *
 * @package    format_classroom
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_classroom_specialnav implements renderable {
    public $text;

    public function __construct($text) {
        $this->text = $text;
    }
}
