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

//namespace mod_registerattendance;

defined('MOODLE_INTERNAL') || die;

//use renderable;
//use renderer_base;
//use stdClass;

/**
 * Class containing data for the mod_registerattendance view selection area
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_registerattendance_view_filter extends mod_registerattendance_widget implements renderable {

    /* @var object The Moodle form. */
    protected $mform = null;

    /* @var object The returned form data. */
    protected $fromform = null;

    /* @var object The course module object. */
    protected $cm = null;

    /**
     * Construct the view filter renderable.
     *
     * @param object $cm The course module
     */
    public function __construct($cm) {
        global $SESSION;

        $this->cm = $cm;

        // Create the data object and set the first values
        parent::__construct();

        $customdata = array();
        $this->fromform = $customdata;

        if (!isset($SESSION->filterData)) {
            $SESSION->filterData = array();
        }//if_filterData_SESSION

        $customdata['id'] = $cm->id;

        $mform = new mod_registerattendance_view_filter_form(null, $customdata, 'post', '',
            array('id' => 'mform-attendancelist-filter'));

        $this->mform = $mform;
        if ($fromform = $mform->get_data()) {
            $SESSION->filterData = (Array)$fromform;
            $this->fromform = $SESSION->filterData;
        } else if ($SESSION->filterData) {
            $this->fromform = $SESSION->filterData;
            $mform->set_defaults($SESSION->filterData);
        }//if_form_data
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
     * @param array $defaults The default values
     */
    public function set_defaults($defaults = array()) {
        $this->mform->set_defaults($defaults);
    }
}
