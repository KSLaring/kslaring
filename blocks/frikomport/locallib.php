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
 * Frikomport block support functions
 *
 * @package    block_frikomport
 * @copyright  2014 eFaktor
 * @author     Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_frikomport_menu_manager {
    // The menu node structure
    protected $nodes = array();

    public function __construct() {
        $this->build_tree();
    }

    /**
     * Return the saved menu tree
     *
     * @return array
     */
    public function get_nodes() {
        return $this->nodes;
    }

    /**
     * Build the navigation tree and store it in the $nodes property
     *
     * First create the root element
     * Add leaves as new nodes
     * Add sections - a branch with leaves - with the »add_tree_section« method
     */
    protected function build_tree() {
        $settingsicon = new pix_icon('i/settings', '');

        // Create the root node
        $item = array(
            'text' => 'root',
            'type' => navigation_node::TYPE_ROOTNODE
        );
        $this->nodes = new navigation_node($item);

        // Create and add the course listing link
        /**
         * @updateDate      2015-05-11
         * @author          eFaktor         (uh)
         *
         * Description
         * Change Action link to course list
         */
        $item = array(
            'text' => get_string('ncourses', 'block_frikomport'),
            'icon' => $settingsicon,
            'type' => navigation_node::NODETYPE_LEAF,
            'action' => '/local/friadmin/courselist.php'
        );
        $subnode = new navigation_node($item);
        $this->nodes->add_node($subnode);

        $item = array(
            'text' => get_string('naddfromtemplate', 'block_frikomport'),
            'icon' => $settingsicon,
            'type' => navigation_node::NODETYPE_LEAF,
            'action' => '/local/friadmin/coursetemplate.php'
        );
        $subnode = new navigation_node($item);
        $this->nodes->add_node($subnode);

        /**
         * @updateDate      27/04/2015
         * @author          eFaktor         (fbv)
         *
         * Description
         * Remove sub-menu for Organization Structure
         * Add sub-menu for locations
         */
        $lst_action = '#';
        $new_action = '#';
        if (get_config('course_locations')) {
            $lst_action = new moodle_url('/local/friadmin/course_locations/index.php');
            $new_action = new moodle_url('/local/friadmin/course_locations/add_location.php');
        }//if_course_locations

        $branch = array(
            array(
                'text'      => get_string('nlocations', 'block_frikomport'),
                'type'      => navigation_node::NODETYPE_BRANCH
            ),
            array(
                'text'      => get_string('lst_locations', 'block_frikomport'),
                'icon'      => $settingsicon,
                'type'      => navigation_node::NODETYPE_LEAF,
                'action'    => $lst_action
            ),
            array(
                'text'      => get_string('new_location', 'block_frikomport'),
                'icon'      => $settingsicon,
                'type'      => navigation_node::NODETYPE_LEAF,
                'action'    => $new_action
            )
        );
        $this->add_tree_section($branch);
    }

    /**
     * Create one section for the menu
     *
     * The array contains arrays of menu items. The first one is the title branch,
     * the following items are leafs.
     *
     * @param array $branch The information about the section
     */
    protected function add_tree_section($branch) {
        // Create the section title from the first item
        $item = array_shift($branch);
        $groupnode = new navigation_node($item);

        // Create the section items
        foreach ($branch as $item) {
            $subnode = new navigation_node($item);
            $groupnode->add_node($subnode);
        }

        // Add the menu section to the menu
        $this->nodes->add_node($groupnode);
    }
}
