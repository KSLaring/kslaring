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
 * Single Frikomport Format - Library
 *
 * Description
 *
 * @package             course
 * @subpackage          format/single_frikomport
 * @copyright           2010 eFaktor
 *
 * @creationDate        20/04/2015
 * @author              eFaktor     (fbv)
 *
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/course/format/lib.php');
require_once($CFG->dirroot . '/local/course_page/locallib.php');


class format_single_frikomport extends format_base {
    /** @var cm_info the current activity. Use get_activity() to retrieve it. */
    private $activity = false;

    /**
     * The URL to use for the specified course
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if null the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        $sectionnum = $section;
        if (is_object($sectionnum)) {
            $sectionnum = $section->section;
        }
        if ($sectionnum == 1) {
            return new moodle_url('/course/view.php', array('id' => $this->courseid, 'section' => 1));
        }
        if (!empty($options['navigation']) && $section !== null) {
            return null;
        }
        return new moodle_url('/course/view.php', array('id' => $this->courseid));
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        // Display orphaned activities for the users who can see them.
        $context = context_course::instance($this->courseid);
        if (has_capability('moodle/course:viewhiddensections', $context)) {
            $modinfo = get_fast_modinfo($this->courseid);
            if (!empty($modinfo->sections[1])) {
                $section1 = $modinfo->get_section_info(1);
                // Show orphaned activities.
                $orphanednode = $node->add(get_string('orphaned', 'format_single_frikomport'),
                        $this->get_view_url(1), navigation_node::TYPE_SECTION, null, $section1->id);
                $orphanednode->nodetype = navigation_node::NODETYPE_BRANCH;
                $orphanednode->add_class('orphaned');
                foreach ($modinfo->sections[1] as $cmid) {
                    if (has_capability('moodle/course:viewhiddenactivities', context_module::instance($cmid))) {
                        $this->navigation_add_activity($orphanednode, $modinfo->cms[$cmid]);
                    }
                }
            }
        }
    }

    /**
     * Adds a course module to the navigation node
     *
     * This is basically copied from function global_navigation::load_section_activities()
     * because it is not accessible from outside.
     *
     * @param navigation_node $node
     * @param cm_info $cm
     * @return null|navigation_node
     */
    protected function navigation_add_activity(navigation_node $node, $cm) {
        if (!$cm->uservisible) {
            return null;
        }
        $action = $cm->url;
        if (!$action) {
            // Do not add to navigation activity without url (i.e. labels).
            return null;
        }
        $activityname = format_string($cm->name, true, array('context' => context_module::instance($cm->id)));
        if ($cm->icon) {
            $icon = new pix_icon($cm->icon, $cm->modfullname, $cm->iconcomponent);
        } else {
            $icon = new pix_icon('icon', $cm->modfullname, $cm->modname);
        }
        $activitynode = $node->add($activityname, $action, navigation_node::TYPE_ACTIVITY, null, $cm->id, $icon);
        if (global_navigation::module_extends_navigation($cm->modname)) {
            $activitynode->nodetype = navigation_node::NODETYPE_BRANCH;
        } else {
            $activitynode->nodetype = navigation_node::NODETYPE_LEAF;
        }
        return $activitynode;
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        // No blocks for this format because course view page is not displayed anyway.
        return array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => array()
        );
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * Single_frikomport course format uses one option 'activitytype'
     *
     * @param       bool $foreditform
     * @return           array
     *
     * @updateDate      21/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add Home Page Course fields
     *
     * @updateDate  21/01/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add the 'ratings' option format
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $config = get_config('format_single_frikomport');
            $courseformatoptions = array(
                'activitytype' => array(
                    'default' => $config->activitytype,
                    'type' => PARAM_TEXT,
                ),
                'homepage'          => array(
                    'label'         => get_string('checkbox_home','local_course_page'),
                    'element_type'  => 'checkbox',
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
                'homevisible'       => array(
                    'label'         => get_string('home_visible','local_course_page'),
                    'default'       => 1,
                    'element_type'  =>  'checkbox',
                ),
                'homesummary'           => array(
                    'label'             => 'homesummary',
                    'element_type'      => 'hidden',
                    'default'           => '',
                ),
                'pagegraphics'          => array(
                    'label'             => 'pagegraphics',
                    'element_type'      => 'hidden',
                    'default'           => 0,
                ),
                'pagevideo'             => array(
                    'label'             => 'pagevideo',
                    'element_type'      => 'hidden',
                    'default'           => 0,
                ),
                'pagegraphicstitle' => array(
                    'type' => PARAM_TEXT,
                ),
                'author'        => array(
                    'type'      => PARAM_TEXT,
                ),
                'licence'       => array(
                    'type'      => PARAM_TEXT,
                )
            );
        }
        if ($foreditform && !isset($courseformatoptions['activitytype']['label'])) {
            $availabletypes = $this->get_supported_activities();
            $courseformatoptionsedit = array(
                'activitytype' => array(
                    'label' => new lang_string('activitytype', 'format_single_frikomport'),
                    'help' => 'activitytype',
                    'help_component' => 'format_single_frikomport',
                    'element_type' => 'select',
                    'element_attributes' => array($availabletypes),
                ),
                'pagegraphicstitle' => array(
                    'label' => get_string('home_graphicstitle', 'local_course_page'),
                    'element_type' => 'text',
                    'element_attributes' => array(
                        0 => 'style="width:95%;"'
                    )
                ),
                'author'        => array(
                    'label'                 => get_string('home_author','format_whitepaper'),
                    'element_type'          => 'text',
                    'element_attributes'    => array(
                        0 => 'style="width:95%;"'
                    )
                ),
                'licence'        => array(
                    'label'                 => get_string('home_licence','format_whitepaper'),
                    'element_type'          => 'text',
                    'element_attributes'    => array(
                        0 => 'style="width:95%;"'
                    )
                ),
            );
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form
     *
     * This function is called from {@link course_edit_form::definition_after_data()}
     *
     * Format single_frikomport adds a warning when format of the course is about to be changed.
     *
     *
     * @param           MoodleQuickForm $mform
     * @param           bool            $forsection
     * @return          array
     *
     * @updateDate      21/04/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Don't call create_edit_form      --> parent
     * Different functionality          --> Course Home Page
     *
     * @updateDate  21/01/2016
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add the 'ratings' option format
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $PAGE;
        //$elements = parent::create_edit_form_elements($mform, $forsection);
        $elements = array();
        if ($forsection) {
            $options = $this->section_format_options(true);
        } else {
            $options = $this->course_format_options(true);
        }
        foreach ($options as $optionname => $option) {
            switch ($optionname) {
                case 'homepage':
                case 'ratings':
                case 'homevisible':
                case 'homesummary':
                case 'pagegraphics':
                case 'pagegraphicstitle':
                case 'pagevideo':
                    course_page::addCourseHomePage_Section($mform,$optionname);

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
                        $helpcomponent = 'format_'. $this->get_format();
                        if (isset($option['help_component'])) {
                            $helpcomponent = $option['help_component'];
                        }
                        $mform->addHelpButton($optionname, $option['help'], $helpcomponent);
                    }
                    if (isset($option['type'])) {
                        $mform->setType($optionname, $option['type']);
                    }

                    break;
            }//swicth

            if (is_null($mform->getElementValue($optionname)) && isset($option['default'])) {
                $mform->setDefault($optionname, $option['default']);
            }
        }//for

        if (!$forsection && ($course = $PAGE->course) && !empty($course->format) &&
            $course->format !== 'site' && $course->format !== 'single_frikomport') {
            // This is the existing course in other format, display a warning.
            $element = $mform->addElement('static', '', '',
                html_writer::tag('span', get_string('warningchangeformat', 'format_single_frikomport'),
                    array('class' => 'error')));
            array_unshift($elements, $element);
        }
        return $elements;
    }

    /**
     * Make sure that current active activity is in section 0
     *
     * All other activities are moved to section 1 that will be displayed as 'Orphaned'.
     * It may be needed after the course format was changed or activitytype in
     * course settings has been changed.
     *
     * @return null|cm_info current activity
     */
    public function reorder_activities() {
        course_create_sections_if_missing($this->courseid, array(0, 1));
        foreach ($this->get_sections() as $sectionnum => $section) {
            if (($sectionnum && $section->visible) ||
                    (!$sectionnum && !$section->visible)) {
                // Make sure that 0 section is visible and all others are hidden.
                set_section_visible($this->courseid, $sectionnum, $sectionnum == 0);
            }
        }
        $modinfo = get_fast_modinfo($this->courseid);

        // Find the current activity (first activity with the specified type in all course activities).
        $activitytype = $this->get_activitytype();
        $activity = null;
        if (!empty($activitytype)) {
            foreach ($modinfo->sections as $sectionnum => $cmlist) {
                foreach ($cmlist as $cmid) {
                    if ($modinfo->cms[$cmid]->modname === $activitytype) {
                        $activity = $modinfo->cms[$cmid];
                        break 2;
                    }
                }
            }
        }

        // Make sure the current activity is in the 0-section.
        if ($activity && $activity->sectionnum != 0) {
            moveto_module($activity, $modinfo->get_section_info(0));
            // Cache was reset so get modinfo again.
            $modinfo = get_fast_modinfo($this->courseid);
        }

        // Move all other activities into section 1 (the order must be kept).
        $hasvisibleactivities = false;
        $firstorphanedcm = null;
        foreach ($modinfo->sections as $sectionnum => $cmlist) {
            if ($sectionnum && !empty($cmlist) && $firstorphanedcm === null) {
                $firstorphanedcm = reset($cmlist);
            }
            foreach ($cmlist as $cmid) {
                if ($sectionnum > 1) {
                    moveto_module($modinfo->get_cm($cmid), $modinfo->get_section_info(1));
                } else if (!$hasvisibleactivities && $sectionnum == 1 && $modinfo->get_cm($cmid)->visible) {
                    $hasvisibleactivities = true;
                }
            }
        }
        if (!empty($modinfo->sections[0])) {
            foreach ($modinfo->sections[0] as $cmid) {
                if (!$activity || $cmid != $activity->id) {
                    moveto_module($modinfo->get_cm($cmid), $modinfo->get_section_info(1), $firstorphanedcm);
                }
            }
        }
        if ($hasvisibleactivities) {
            set_section_visible($this->courseid, 1, false);
        }
        return $activity;
    }

    /**
     * Returns the name of activity type used for this course
     *
     * @return string|null
     */
    protected function get_activitytype() {
        $options = $this->get_format_options();
        $availabletypes = $this->get_supported_activities();
        if (!empty($options['activitytype']) &&
                array_key_exists($options['activitytype'], $availabletypes)) {
            return $options['activitytype'];
        } else {
            return null;
        }
    }

    /**
     * Returns the current activity if exists
     *
     * @return null|cm_info
     */
    protected function get_activity() {
        if ($this->activity === false) {
            $this->activity = $this->reorder_activities();
        }
        return $this->activity;
    }

    /**
     * Get the activities supported by the format.
     *
     * Here we ignore the modules that do not have a page of their own, like the label.
     *
     * @return array array($module => $name of the module).
     */
    public static function get_supported_activities() {
        $availabletypes = get_module_types_names();
        foreach ($availabletypes as $module => $name) {
            if (plugin_supports('mod', $module, FEATURE_NO_VIEW_LINK, false)) {
                unset($availabletypes[$module]);
            }
        }
        return $availabletypes;
    }

    /**
     * Checks if the current user can add the activity of the specified type to this course.
     *
     * @return bool
     */
    protected function can_add_activity() {
        global $CFG;
        if (!($modname = $this->get_activitytype())) {
            return false;
        }
        if (!has_capability('moodle/course:manageactivities', context_course::instance($this->courseid))) {
            return false;
        }
        if (!course_allowed_module($this->get_course(), $modname)) {
            return false;
        }
        $libfile = "$CFG->dirroot/mod/$modname/lib.php";
        if (!file_exists($libfile)) {
            return null;
        }
        return true;
    }

    /**
     * Checks if the activity type requires subtypes.
     *
     * @return bool|null (null if the check is not possible)
     */
    public function activity_has_subtypes() {
        global $CFG;
        if (!($modname = $this->get_activitytype())) {
            return null;
        }
        $libfile = "$CFG->dirroot/mod/$modname/lib.php";
        if (!file_exists($libfile)) {
            return null;
        }
        include_once($libfile);
        return function_exists($modname. '_get_types');
    }

    /**
     * Allows course format to execute code on moodle_page::set_course()
     *
     * This function is executed before the output starts.
     *
     * If everything is configured correctly, user is redirected from the
     * default course view page to the activity view page.
     *
     * "Section 1" is the administrative page to manage orphaned activities
     *
     * If user is on course view page and there is no module added to the course
     * and the user has 'moodle/course:manageactivities' capability, redirect to create module
     * form.
     *
     * @param moodle_page $page instance of page calling set_course
     */
    public function page_set_course(moodle_page $page) {
        global $PAGE;
        $page->add_body_class('format-'. $this->get_format());
        if ($PAGE == $page && $page->has_set_url() &&
                $page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
            $edit = optional_param('edit', -1, PARAM_BOOL);
            if (($edit == 0 || $edit == 1) && confirm_sesskey()) {
                // This is a request to turn editing mode on or off, do not redirect here, /course/view.php will do redirection.
                return;
            }
            $cm = $this->get_activity();
            $cursection = optional_param('section', null, PARAM_INT);
            if (!empty($cursection) && has_capability('moodle/course:viewhiddensections',
                    context_course::instance($this->courseid))) {
                // Display orphaned activities (course view page, section 1).
                return;
            }
            if (!$this->get_activitytype()) {
                if (has_capability('moodle/course:update', context_course::instance($this->courseid))) {
                    // Teacher is redirected to edit course page.
                    $url = new moodle_url('/course/edit.php', array('id' => $this->courseid));
                    redirect($url, get_string('erroractivitytype', 'format_single_frikomport'));
                } else {
                    // Student sees an empty course page.
                    return;
                }
            }
            if ($cm === null) {
                if ($this->can_add_activity()) {
                    // This is a user who has capability to create an activity.
                    if ($this->activity_has_subtypes()) {
                        // Activity that requires subtype can not be added automatically.
                        if (optional_param('addactivity', 0, PARAM_INT)) {
                            return;
                        } else {
                            $url = new moodle_url('/course/view.php', array('id' => $this->courseid, 'addactivity' => 1));
                            redirect($url);
                        }
                    }
                    // Redirect to the add activity form.
                    $url = new moodle_url('/course/mod.php', array('id' => $this->courseid,
                        'section' => 0, 'sesskey' => sesskey(), 'add' => $this->get_activitytype()));
                    redirect($url);
                } else {
                    // Student views an empty course page.
                    return;
                }
            } else if (!$cm->uservisible || !$cm->url) {
                // Activity is set but not visible to current user or does not have url.
                // Display course page (either empty or with availability restriction info).
                return;
            } else {
                // Everything is set up and accessible, redirect to the activity page!
                redirect($cm->url);
            }
        }
    }

    /**
     * Allows course format to execute code on moodle_page::set_cm()
     *
     * If we are inside the main module for this course, remove extra node level
     * from navigation: substitute course node with activity node, move all children
     *
     * @param moodle_page $page instance of page calling set_cm
     */
    public function page_set_cm(moodle_page $page) {
        global $PAGE;
        parent::page_set_cm($page);
        if ($PAGE == $page && ($cm = $this->get_activity()) &&
                $cm->uservisible &&
                ($cm->id === $page->cm->id) &&
                ($activitynode = $page->navigation->find($cm->id, navigation_node::TYPE_ACTIVITY)) &&
                ($node = $page->navigation->find($page->course->id, navigation_node::TYPE_COURSE))) {
            // Substitute course node with activity node, move all children.
            $node->action = $activitynode->action;
            $node->type = $activitynode->type;
            $node->id = $activitynode->id;
            $node->key = $activitynode->key;
            $node->isactive = $node->isactive || $activitynode->isactive;
            $node->icon = null;
            if ($activitynode->children->count()) {
                foreach ($activitynode->children as &$child) {
                    $child->remove();
                    $node->add_node($child);
                }
            } else {
                $node->search_for_active_node();
            }
            $activitynode->remove();
        }
    }

    /**
     * Returns true if the course has a front page.
     *
     * @return boolean false
     */
    public function has_view_page() {
        return false;
    }

}
