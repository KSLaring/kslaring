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
 * @package   block_fri_course_list
 * @copyright 2015 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The frikomport course list block class
 *
 * Used to produce the frikomport course list block
 *
 * @package   block_fri_course_list
 * @copyright 2015 eFaktor
 * @author    Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_fri_course_list extends block_base {

    /** @var string */
    public $blockname = null;

    /** @var bool */
    protected $contentgenerated = false;

    /**
     * Set the initial properties for the block
     */
    function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    /**
     * The frikomport course list block cannot be hidden by default as it is integral to
     * the navigation of Moodle.
     *
     * @return false
     */
    function instance_can_be_hidden() {
        return false;
    }

    /**
     * Set the applicable formats for this block to all
     *
     * @return array
     */
    function applicable_formats() {
        return array('all' => true);
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        false;
    }

    /**
     * Gets the content for this block by grabbing it from $this->page
     *
     * @return bool|stdObject
     */
    function get_content() {
        global $CFG, $OUTPUT;

        // First check if we have already generated, don't waste cycles
        if ($this->contentgenerated === true) {
            return $this->content;
        }

        $this->content = new stdClass();

        // Get the content from the local friadmin plugin
        $friadmin = new local_friadmin\friadmin();

        // In Moodle 2.7 renderers and renderables can't be loaded via namespaces.
        // Get the renderer for this plugin.
        $output = $this->page->get_renderer('local_friadmin');

        // Prepare the renderables for the page and the page areas.
        $block = new local_friadmin_usercourselist_block();
        $filter = new local_friadmin_usercourselist_filter();

        $table = new local_friadmin_usercourselist_table($block->data->url,
            $filter->get_userleveloneids(), $filter->get_myCategories(), $filter->get_fromform());

        $friadmin->set_usercourselist_references($block, $filter, $table, $output);

        $o = $friadmin->render_courselist_for_block();

        $o .= '<div class="alert alert-info">' .
            get_string('morecoursesinfo', 'block_fri_course_list'). '</div>';

        $this->content->text = $o;

        $this->contentgenerated = true;

        return $this->content;
    }
}
