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
 * This file contains classes used to manage the course_navigation structures in Moodle
 * and was introduced as part of the changes occuring in Moodle 2.0
 *
 * @since      2.0
 * @package    block_course_navigation
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The global course_navigation tree block class
 *
 * Used to produce the global course_navigation block new to Moodle 2.0
 *
 * @package    block_course_navigation
 * @category   course_navigation
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_navigation extends block_base {

    /** @var int This allows for multiple course_navigation trees */
    public static $navcount;
    /** @var string The name of the block */
    public $blockname = null;
    /** @var bool A switch to indicate whether content has been generated or not. */
    protected $contentgenerated = false;
    /** @var bool|null variable for checking if the block is docked */
    protected $docked = null;

    /** @var int Trim characters from the right */
    const TRIM_RIGHT = 1;
    /** @var int Trim characters from the left */
    const TRIM_LEFT = 2;
    /** @var int Trim characters from the center */
    const TRIM_CENTER = 3;

    /**
     * Set the initial properties for the block
     */
    function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    /**
     * Set the block position to top
     * and make it visible on "incourse" = module pages
     *
     * @return boolean
     */
    function instance_create() {
        global $DB;

        $write = false;
        $instance = $this->instance;
        if (!isset($instance->weight) || $instance->weight !== "-10") {
            $instance->weight = "-10";
            $write = true;
        }
        if (!isset($instance->defaultweight) || $instance->defaultweight !== "-10") {
            $instance->defaultweight = "-10";
            $write = true;
        }
        if (!isset($instance->pagetypepattern) || $instance->pagetypepattern !== "*") {
            $instance->pagetypepattern = "*";
            $write = true;
        }
        if (!isset($instance->showinsubcontexts) || $instance->showinsubcontexts !== "1") {
            $instance->showinsubcontexts = "1";
            $write = true;
        }

        if ($write) {
            $DB->update_record('block_instances', $instance);
        }

        return true;
    }

    /**
     * Define if the block header may be shown
     *
     * @return boolean
     */
    function hide_header() {
        return true;
    }

    /**
     * All multiple instances of this block
     *
     * @return bool Returns false
     */
    function instance_allow_multiple() {
        return false;
    }

    /**
     * Set the applicable formats for this block
     *
     * @return array
     */
    function applicable_formats() {
        return array('all' => false, 'course' => true, 'mod' => true);
    }

    /**
     * Allow the user to configure a block instance
     *
     * @return bool Returns true
     */
    function instance_allow_config() {
        return true;
    }

    /**
     * The course_navigation block cannot be hidden by default as it is integral to
     * the course_navigation of Moodle.
     *
     * @return false
     */
    function instance_can_be_hidden() {
        return false;
    }

    /**
     * Find out if an instance can be docked.
     *
     * @return bool true or false depending on whether the instance can be docked or not.
     */
    function instance_can_be_docked() {
        return (parent::instance_can_be_docked() && (empty($this->config->enabledock) ||
                        $this->config->enabledock == 'yes'));
    }

    /**
     * Gets Javascript that may be required for course_navigation
     */
    function get_required_javascript() {
        global $CFG;
        parent::get_required_javascript();
        $limit = 20;
        if (!empty($CFG->navcourselimit)) {
            $limit = $CFG->navcourselimit;
        }
        $expansionlimit = 0;
        if (!empty($this->config->expansionlimit)) {
            $expansionlimit = $this->config->expansionlimit;
        }
        $arguments = array(
                'id' => $this->instance->id,
                'instance' => $this->instance->id,
                'candock' => $this->instance_can_be_docked(),
                'courselimit' => $limit,
                'expansionlimit' => $expansionlimit
        );
        $this->page->requires->string_for_js('viewallcourses', 'moodle');
        $this->page->requires->yui_module('moodle-block_course_navigation-course_navigation',
                'M.block_course_navigation.init_add_tree', array($arguments));
    }

    /**
     * Gets the content for this block by grabbing it from $this->page
     *
     * @return object $this->content
     */
    function get_content() {
        // First check if we have already generated, don't waste cycles
        if ($this->contentgenerated === true) {
            return $this->content;
        }
        // JS for course_navigation moved to the standard theme,
        // the code will probably have to depend on the actual page structure
        // $this->page->requires->js('/lib/javascript-course_navigation.js');
        // Navcount is used to allow us to have multiple trees although I dont' know why
        // you would want two trees the same

        block_course_navigation::$navcount++;

        // Check if this block has been docked
        if ($this->docked === null) {
            $this->docked = get_user_preferences('nav_in_tab_panel_globalnav' .
                    block_course_navigation::$navcount, 0);
        }

        // Check if there is a param to change the docked state
        if ($this->docked &&
                optional_param('undock', null, PARAM_INT) == $this->instance->id
        ) {
            unset_user_preference('nav_in_tab_panel_globalnav' .
                    block_course_navigation::$navcount);
            $url = $this->page->url;
            $url->remove_params(array('undock'));
            redirect($url);
        } else if (!$this->docked && optional_param('dock', null, PARAM_INT) ==
                $this->instance->id
        ) {
            set_user_preferences(array('nav_in_tab_panel_globalnav' .
            block_course_navigation::$navcount => 1));
            $url = $this->page->url;
            $url->remove_params(array('dock'));
            redirect($url);
        }

        $trimmode = self::TRIM_LEFT;
        $trimlength = 50;

        if (!empty($this->config->trimmode)) {
            $trimmode = (int)$this->config->trimmode;
        }

        if (!empty($this->config->trimlength)) {
            $trimlength = (int)$this->config->trimlength;
        }

        // Get the course_navigation object or don't display the block if none provided.
        if (!$course_navigation = $this->get_course_navigation()) {
            return null;
        }

        // Get the current course nodes and extract the course node collection
        // The current course has only one collection, can be fetched with "last"
        $thiscourse_navigation = $course_navigation->get("currentcourse");
        $thiscourse_navigation = $thiscourse_navigation->children->last();

        // Remove all nodes which are not section nodes
        // and the nodes without an action (section 0 has no action)
        foreach ($thiscourse_navigation->children as $node) {
            if ($node->type !== navigation_node::TYPE_SECTION) {
                $node->remove();
            } else if (empty($node->action)) {
                $node->remove();
            }
        }

        // Add an action URL to the section 0 node
        // Get the action from the last node and set the section parameter to 0
//        $firstnode = reset($thiscourse_navigation->children)[0];
//        $lastnode = $thiscourse_navigation->children->last();
//        $action = clone($lastnode->action);
//        $action->param('section', 0);
//        $firstnode->action = $action;

        $expansionlimit = null;
//        if (!empty($this->config->expansionlimit)) {
//            $expansionlimit = $this->config->expansionlimit;
//            $course_navigation->set_expansion_limit($this->config->expansionlimit);
//        }
//        $this->trim($course_navigation, $trimmode, $trimlength, ceil($trimlength / 2));
        $this->trim($thiscourse_navigation, $trimmode, $trimlength, ceil($trimlength / 2));

        // Get the expandable items so we can pass them to JS
        $expandable = array();
//        $course_navigation->find_expandable($expandable);
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

        $this->page->requires->data_for_js('navtreeexpansions' . $this->instance->id,
                $expandable);

        $options = array();
        $options['linkcategories'] = (!empty($this->config->linkcategories) &&
                $this->config->linkcategories == 'yes');

        // Grab the items to display
        $renderer = $this->page->get_renderer($this->blockname);
        $this->content = new stdClass();
//        $this->content->text = $renderer->course_navigation_tree($course_navigation,
//                $expansionlimit, $options);
        $this->content->text = $renderer->course_navigation_tree($thiscourse_navigation,
                $expansionlimit, $options);

        // Set content generated to true so that we know it has been done
        $this->contentgenerated = true;

        return $this->content;
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
     * Returns the attributes to set for this block
     *
     * This function returns an array of HTML attributes for this block including
     * the defaults.
     * {@link block_tree::html_attributes()} is used to get the default arguments
     * and then we check whether the user has enabled hover expansion and add the
     * appropriate hover class if it has.
     *
     * @return array An array of HTML attributes
     */
    public function html_attributes() {
        $attributes = parent::html_attributes();
        if (!empty($this->config->enablehoverexpansion) &&
                $this->config->enablehoverexpansion == 'yes'
        ) {
            $attributes['class'] .= ' block_js_expansion';
        }

        return $attributes;
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

    /**
     * Returns the role that best describes the course_navigation block... 'navigation'
     *
     * @return string 'navigation'
     */
    public function get_aria_role() {
        return 'navigation';
    }
}
