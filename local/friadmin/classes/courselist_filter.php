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
 * Class containing data for the local_friadmin course_list selection area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_courselist_filter extends local_friadmin_widget implements renderable {

    // The Moodle form
    protected $mform = null;

    // The returned form data
    protected $fromform = null;

    /**
     * Construct the courselist_page renderable.
     */
    public function __construct() {
        global $SESSION;

        if (!isset($SESSION->friadmin_courselist_filtering)) {
            $SESSION->friadmin_courselist_filtering = array();
        }

        // Create the data object and set the first values
        parent::__construct();

        $customdata = $this->get_fixture('friadmin_coursefilter');

        $mform = new local_friadmin_courselist_filter_form(null, $customdata, 'post', '',
            array('id' => 'mform-coursefilter'));

        $this->mform = $mform;

        if ($fromform = $mform->get_data()) {
            $this->fromform = $fromform;
            $SESSION->friadmin_courselist_filtering = $fromform;
//            local_logger\console::log($fromform, "Filter - Fromform");
        } else if (!empty($SESSION->friadmin_courselist_filtering)) {
            $this->fromform = $SESSION->friadmin_courselist_filtering;
            $mform->set_defaults($SESSION->friadmin_courselist_filtering);
//            local_logger\console::log($SESSION->friadmin_courselist_filtering,
//                "Filter - SESSION friadmin_courselist_filtering");
        }
    }

    /**
     * Get the returned form data, if any
     */
    public function get_fromform() {
        return $this->fromform;
    }

    /**
     * Render the form and set the data
     */
    public function render() {
        $this->data->content = $this->mform->render();
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
}
