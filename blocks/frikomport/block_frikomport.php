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
 * This file contains classes used to manage the navigation structures in Moodle
 * and was introduced as part of the changes occuring in Moodle 2.0
 *
 * @since     Moodle 2.0
 * @package   block_frikomport
 * @copyright 2014 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The frikomport navigation tree block class
 *
 * Used to produce the frikomport navigation block new to Moodle 2.0
 *
 * @package   block_frikomport
 * @copyright 2014 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_frikomport extends block_base {
    /** @var string */
    public static $navcount;

    /** @var string|null */
    public $blockname = null;

    /** @var bool */
    protected $contentgenerated = false;

    /** @var bool|null */
    protected $docked = null;

    /**
     * Set the initial properties for the block
     */
    public function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    /**
     * All multiple instances of this block
     *
     * @return bool Returns true
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * The frikomport block cannot be hidden by default as it is integral to
     * the navigation of Moodle.
     *
     * @return false
     */
    public function instance_can_be_hidden() {
        return false;
    }

    /**
     * Set the applicable formats for this block to all
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }

    /**
     * Allow the user to configure a block instance
     *
     * @return bool Returns true
     */
    public function instance_allow_config() {
        return true;
    }

    public function instance_can_be_docked() {
        return (parent::instance_can_be_docked() && (empty($this->config->enabledock) ||
                $this->config->enabledock == 'yes'));
    }

    public function get_required_javascript_old() {
        parent::get_required_javascript();
        $arguments = array(
            'id' => $this->instance->id,
            'instance' => $this->instance->id,
            'candock' => $this->instance_can_be_docked()
        );
        $this->page->requires->yui_module('moodle-block_navigation-navigation',
            'M.block_navigation.init_add_tree', array($arguments));
    }

    function get_required_javascript() {
        global $PAGE;
        $adminnode = $PAGE->settingsnav->find('siteadministration', navigation_node::TYPE_SITE_ADMIN);
        parent::get_required_javascript();
        $arguments = array(
            'instanceid' => $this->instance->id,
            'adminnodeid' => $adminnode ? $adminnode->id : null
        );
        $this->page->requires->js_call_amd('block_frikomport/frikomportblock', 'init', $arguments);
    }


    /**
     * Gets the content for this block by grabbing it from $this->page
     * Change the logical to check if the user is super user
     *
     * @author      eFaktor     (fbv)
     *
     * @return      bool|Object
     */
    public function get_content() {
        global $CFG, $OUTPUT;

        // First check if we have already generated, don't waste cycles.
        if ($this->contentgenerated === true) {
            return true;
        }

        // Check if the user is super user.
        if (!has_capability('block/frikomport:view', context_block::instance($this->instance->id))) {
            if (!self::checkcapability_friadmin()) {
                $this->content = new stdClass();
                $this->content->text = '';

                return false;
            }
        }

        self::$navcount++;

        // Check if this block has been docked.
        if ($this->docked === null) {
            $this->docked = get_user_preferences('nav_in_tab_panel_frikomportnav' .
                self::$navcount, 0);
        }

        // Check if there is a param to change the docked state.
        if ($this->docked && optional_param('undock', null, PARAM_INT) == $this->instance->id) {
            unset_user_preference('nav_in_tab_panel_frikomportnav' .
                self::$navcount, 0);
            $url = $this->page->url;
            $url->remove_params(array('undock'));
            redirect($url);
        } else if (!$this->docked && optional_param('dock', null, PARAM_INT) == $this->instance->id) {
            set_user_preferences(array('nav_in_tab_panel_frikomportnav' .
            self::$navcount => 1));
            $url = $this->page->url;
            $url->remove_params(array('dock'));
            redirect($url);
        }

        $menumanager = new block_frikomport_menu_manager();
        $nodes = $menumanager->get_nodes();

        $renderer = $this->page->get_renderer('block_frikomport');
        $this->content = new stdClass();
        $this->content->text = $renderer->frikomport_tree($nodes);

        // Only do search if you have moodle/site:config.
        if (!empty($this->content->text)) {
            if (!empty($this->config->enabledock) && $this->config->enabledock == 'yes') {
                user_preference_allow_ajax_update('nav_in_tab_panel_frikomportnav' .
                    self::$navcount, PARAM_INT);
            }
        }

        $this->contentgenerated = true;

        return true;
    }

    /**
     * Returns the role that best describes the frikomport block.
     *
     * @return string 'navigation'
     */
    public function get_aria_role() {
        return 'navigation';
    }

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * Check if the user is a super user
     * Permissions for managers and course creators
     *
     * @author          eFaktor     (fbv)
     *
     * @return          bool
     * @throws          Exception
     */
    private static function checkcapability_friadmin() {
        /* Variables    */
        global $DB, $USER;
        $contextcat = null;
        $contextcourse = null;
        $contextsystem = null;

        try {
            /* Search Criteria  */
            $params = array();
            $params['user'] = $USER->id;
            $contextcat = CONTEXT_COURSECAT;
            $contextcourse = CONTEXT_COURSE;
            $contextsystem = CONTEXT_SYSTEM;

            /* SQL Instruction  */
            $sql = " SELECT		ra.id
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 		r.id			= ra.roleid
                                                            AND		r.archetype		IN ('manager','coursecreator')
                                                            AND     r.shortname     = r.archetype
                        JOIN	{context}		    ct		ON		ct.id			= ra.contextid
                                                            AND		ct.contextlevel	IN ($contextcat, $contextcourse, $contextsystem)
                     WHERE		ra.userid 		= :user ";

            // Execute.
            $rdo = $DB->get_records_sql($sql, $params);
            if ($rdo) {
                return true;
            } else {
                return false;
            }//if_Rdo
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//checkcapability_friadmin
}
