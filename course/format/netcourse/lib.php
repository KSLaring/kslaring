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
 * @since      2.0
 * @package    format_netcourse
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/lib.php');

/**
 * Main class for the Netcourse course format
 *
 * @package    format_netcourse
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_netcourse extends format_base {

    /** @var int Trim characters from the right */
    const TRIM_RIGHT = 1;
    /** @var int Trim characters from the left */
    const TRIM_LEFT = 2;
    /** @var int Trim characters from the center */
    const TRIM_CENTER = 3;

    protected $openlast = null;

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
            return get_string('section0name', 'format_netcourse');
        } else {
            return get_string('topic') . ' ' . $section->section;
        }
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
                $url->set_anchor('section-' . $sectionno);
            }
        }

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
     * Check the REQUEST_URI and redirext to the last visited
     * activity/resource if the REQUEST_URI is the course page. Redirect
     * to the first activity/resource in section 0 (description) if the user never
     * has entered the course before.
     *
     * Add the course navigation as a Moodle "fake" block.
     *
     * @param moodle_page $page instance of page calling set_course
     */
    public function page_set_course(moodle_page $page) {
        global $USER, $FULLME;

        if (is_null($this->openlast)) {
            $this->openlast = new format_netcourse_openlast($page,
                $page->course, $USER, $FULLME);
        }

        $redirecturl = $this->openlast->redirect($_SERVER['REQUEST_URI']);

        if ($redirecturl === -1) {
            return;
        } else if ($redirecturl) {
            redirect($redirecturl);
        }

        // Check if the navigation trigger parameter "nonav" is set
        $nonav = optional_param('nonav', 0, PARAM_INT);

        // If the "nonav" parameter is not set show the course navigation
        if (!$nonav) {
            $this->add_fake_nav_block($page);
        }
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
        global $PAGE, $DB;

        // If section is specified in course/view.php, make sure it is expanded
        // in navigation
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

        // Remove existing course navigation nodes
        if ($node->has_children()) {
            foreach ($node->children as $childnode) {
                $childnode->remove();
            }
        }

        $modinfo = get_fast_modinfo($this->courseid);

        foreach ($modinfo->get_section_info_all() as $section) {
            // Exclude section 0
            // Section 0 is used for the course description and potential
            // Sections with no activities/resources are excluded
            if ($section->section > 0 && isset($modinfo->sections[$section->section])) {
                $sectionNode = $this->navigation_add_section($navigation, $node, $section);
                foreach ($modinfo->sections[$section->section] as $cmid) {
                    $this->navigation_add_activity($sectionNode, $modinfo->get_cm($cmid));
                }
            }
        }

        $PAGE->initialise_theme_and_output();
        if (!$PAGE->user_is_editing()) {
            $PAGE->theme->layouts['incourse']['options']['nonavbar'] = true;
        }

        return array();
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
        if (!$section->section) {
            return false;
        } else if ($section->parent == $parentnum) {
            return true;
        } else if ($section->parent == 0) {
            return false;
        } else if ($section->parent >= $section->section) {
            // some error
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
//        $url = course_get_url($this->get_course(), $section->section, array('navigation' => true));

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
        $action = $cm->get_url();
        if ($cm->icon) {
            $icon = new pix_icon($cm->icon, $cm->modfullname, $cm->iconcomponent);
        } else {
            $icon = new pix_icon('icon', $cm->modfullname, $cm->modname);
        }
        $activitynode = $node->add($activityname, $action, navigation_node::TYPE_ACTIVITY,
            null, $cm->id, $icon);
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
            BLOCK_POS_LEFT => array('course_navigation'),
            BLOCK_POS_RIGHT => array()
        );
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * Netcourse format uses the following options:
     * - coursedisplay
     * - numsections
     * - hiddensections
     *
     * @param bool $foreditform
     *
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
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
     * @param MoodleQuickForm $mform      form the elements are added to.
     * @param bool            $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     *
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        $elements = parent::create_edit_form_elements($mform, $forsection);

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
     * In case if course format was changed to 'netcourse', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * If previous course format did not have 'numsections' option, we populate it with
     * thecurrent number of sections
     *
     * @param stdClass|array $data      return value from {@link moodleform::get_data()} or array with data
     * @param stdClass       $oldcourse if this function is called from {@link update_course()}
     *                                  this object contains information about the course before update
     *
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB;
        if ($oldcourse !== null) {
            $data = (array)$data;
            $oldcourse = (array)$oldcourse;
            $options = $this->course_format_options();
            foreach ($options as $key => $unused) {
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
                        }
                    }
                }
            }
        }

        return $this->update_format_options($data);
    }

    /**
     * Display the special course navigation above the course content
     * and the blocks.
     *
     * @return format_netcourse_specialnav | null
     */
    public function course_header() {
        global $USER, $CFG, $PAGE, $FULLME;

        $strcourse = get_string('course');
        $strdescription = get_string('description');
        $strforums = get_string('forums', 'format_netcourse');
        $strprogress = get_string('progress', 'format_netcourse');
        $mymoodle = get_string('mymoodle', 'my');

        if (is_null($this->openlast)) {
            $this->openlast = new format_netcourse_openlast($PAGE,
                $PAGE->course, $USER, $FULLME);
        }

        $editing = $this->openlast->is_editing();
        $description = optional_param('description', 0, PARAM_BOOL);

        list($text, $module, $openedcmid, $courseurl) =
            $this->openlast->get_last_opened($this->courseid, $USER->id);
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
        // resource in section 0
        if (isset($modinfo->sections[0])) {
            $cmid = $modinfo->sections[0][0];
            $descriptionurl = $modinfo->cms[$cmid]->url;
        } else {
            $descriptionurl = new moodle_url('#');
        }

        // Set the "Discussion" link to the first forum in section 0
        $discussionurl = new moodle_url('#');
        if (!is_null($this->openlast->get_section0modids())) {
            foreach($this->openlast->get_section0modids() as $cmid) {
                if ($modinfo->cms[$cmid]->modname === 'forum') {
                    $discussionurl = $modinfo->cms[$cmid]->url;
                    break;
                }
            }
        }

        // Set the link to the progress page
        $progressurl = new moodle_url('#');
        if (!is_null($this->openlast->get_section0modids())) {
            foreach($this->openlast->get_section0modids() as $cmid) {
                if ($modinfo->cms[$cmid]->modname === 'completionreport') {
                    $progressurl = $modinfo->cms[$cmid]->url;
                    break;
                }
            }
        }

        $courseactive = '';
        $discussactive = '';
        $descactive = '';
        $progressactive = '';
        if ($PAGE->url->compare($descriptionurl, URL_MATCH_EXACT)) {
            $descactive = ' btn-primary active';
        } else if ($PAGE->url->compare($discussionurl, URL_MATCH_EXACT)) {
            $discussactive = ' btn-primary active';
        } else if ($PAGE->url->compare($progressurl, URL_MATCH_EXACT)) {
            $progressactive = ' btn-primary active';
        } else {
            $courseactive = ' btn-primary active';
        }

        $courseurl = $courseurl->out();

        // Add the nonav parameter to hide the course navigation
        if ($discussionurl->get_host() !== "") {
            $discussionurl->param('nonav', 1);
        }
        $discussionurl = $discussionurl->out();

        // Add the nonav parameter to hide the course navigation
        if ($descriptionurl->get_host() !== "") {
            $descriptionurl->param('nonav', 1);
            $descriptionurl->param('description', 1);
        }
        $descriptionurl = $descriptionurl->out();

        // Add the nonav parameter to hide the course navigation
        if ($progressurl->get_host() !== "") {
            $progressurl->param('nonav', 1);
        }
        $progressurl = $progressurl->out();

        return new format_netcourse_specialnav('
        <div class="btn-toolbar">
            <a class="btn' . $courseactive . '" type="button" href="' .
            $courseurl . '">' . $strcourse . '</a>
            <a class="btn' . $descactive . '" type="button" href="' .
            $descriptionurl . '">' . $strdescription . '</a>
            <a class="btn' . $discussactive . '" type="button" href="' .
            $discussionurl . '">' . $strforums . '</a>
            <a class="btn' . $progressactive . '" type="button" href="' .
            $progressurl . '">' . $strprogress . '</a>
            <a class="btn" type="button" href="javascript:void(0)"
            onclick="document.location.href=\'' . $CFG->wwwroot . '/my\'">' .
            $mymoodle . '</a>
        </div>'
        );
    }

    /**
     * Display the special module navigation above the content
     * between the blocks.
     *
     * Create the module navigation for each module.
     *
     * @return format_netcourse_specialnav | null
     */
    public function course_content_header() {
        global $CFG, $DB, $cm, $PAGE;

        $retval = null;

        if (!is_null($cm)) {
            if ($cm->modname === 'lesson') {
                // Get the lesson library with the lesson class and create a new instance
                // and get the lesson renderer
                require_once($CFG->dirroot.'/mod/lesson/locallib.php');
                $lesson = new lesson($DB->get_record('lesson',
                    array('id' => $cm->instance), '*', MUST_EXIST));
                $lessonoutput = $PAGE->get_renderer('mod_lesson');

                // Force the progressbar on, render the progressbar
                // and force the progressbar off to avoid the lesson's own progressbar
                // at the bottom of the lesson page. The progressbar will be rendered
                // independent of the lesson settings.
                $lesson->progressbar = 1;
                $progressbar = $lessonoutput->progress_bar($lesson);
                $lesson->progressbar = 0;

                // Create the object for the course content header renderer
                $retval = new format_netcourse_specialnav($progressbar);
            }
        }

        return $retval;
    }

    /**
     * Display the special module navigation above the content
     * between the blocks.
     *
     * @return format_netcourse_specialnav | null
     */
    public function _course_content_footer() {
        return new format_netcourse_specialnav('This is the course content footer');
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
     * Gets the content for this block by grabbing it from $this->page
     *
     * @return object $this->content
     */
    protected function get_content() {
        $trimmode = self::TRIM_LEFT;
        $trimlength = 50;

        // Get the course_navigation object or don't display the block if none provided.
        if (!$course_navigation = $this->get_course_navigation()) {
            return null;
        }

        // Get the current course nodes and extract the course node collection
        // The current course has only one collection, can be fetched with "last"
        $thiscourse_navigation = $course_navigation->get("currentcourse");
        $thiscourse_navigation = $thiscourse_navigation->children->last();

        // Remove all nodes which are not section nodes
        foreach ($thiscourse_navigation->children as $node) {
            if ($node->type !== navigation_node::TYPE_SECTION) {
                $node->remove();
            }
        }

        // Check if there is an active node
        // If not make the current activity node active (pages within lessons for example)
        global $FULLME, $cm;

        $fullmeurl = new moodle_url($FULLME);
        $activenode = $thiscourse_navigation->find_active_node();
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
            } else {
//                $activitynodes = $thiscourse_navigation->
//                    find_all_of_type(navigation_node::TYPE_ACTIVITY);
//                foreach ($activitynodes as $activitynode) {
//                    if ($activitynode->action->compare($fullmeurl, URL_MATCH_PARAMS)) {
//                        $activenode->make_inactive();
//                        $activenode->parent->forceopen = false;
//                        $activitynode->make_active();
//                        break;
//                    }
//                }
            }
        }


        $expansionlimit = null;
        $this->trim($thiscourse_navigation, $trimmode, $trimlength, ceil($trimlength / 2));

        // Get the expandable items so we can pass them to JS
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

        // Grab the items to display
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
        $this->page->requires->yui_module('moodle-format_netcourse-netcourse_navigation',
            'M.format_netcourse.init_add_tree', array($arguments));
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
                    // Truncate the text to $long characters
                    $node->text = $this->trim_right($node->text, $long);
                }
                if (is_string($node->shorttext) &&
                    core_text::strlen($node->shorttext) > ($short + 3)
                ) {
                    // Truncate the shorttext
                    $node->shorttext = $this->trim_right($node->shorttext, $short);
                }
                break;
            case self::TRIM_LEFT :
                if (core_text::strlen($node->text) > ($long + 3)) {
                    // Truncate the text to $long characters
                    $node->text = $this->trim_left($node->text, $long);
                }
                if (is_string($node->shorttext) &&
                    core_text::strlen($node->shorttext) > ($short + 3)
                ) {
                    // Truncate the shorttext
                    $node->shorttext = $this->trim_left($node->shorttext, $short);
                }
                break;
            case self::TRIM_CENTER :
                if (core_text::strlen($node->text) > ($long + 3)) {
                    // Truncate the text to $long characters
                    $node->text = $this->trim_center($node->text, $long);
                }
                if (is_string($node->shorttext) &&
                    core_text::strlen($node->shorttext) > ($short + 3)
                ) {
                    // Truncate the shorttext
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
        return '...' . core_text::substr($string,
            core_text::strlen($string) - $length, $length);
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
        return core_text::substr($string, 0, $length) . '...';
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
 * @package    format_netcourse
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_netcourse_specialnav implements renderable {
    public $text;

    public function __construct($text) {
        $this->text = $text;
    }
}
