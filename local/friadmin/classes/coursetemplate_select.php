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
 * Class containing data for the local_friadmin course_template selection area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_coursetemplate_select extends local_friadmin_widget implements renderable {

    // The form feeback - for debugging
    protected $formdatadump = null;

    // The Moodle form
    protected $mform = null;

    // The returned form data
    protected $fromform = null;

    /**
     * Construct the courselist_page renderable.
     */
    public function __construct() {

        // Create the data object and set the first values
        parent::__construct();

        $customdata = $this->get_fixture('friadmin_coursetemplate_select');
//        $customdata = $this->get_popup_data();

        $mform = new local_friadmin_coursetemplate_select_form(null, $customdata, 'post',
            '', array('id' => 'mform-coursetemplate-select'));

        $this->mform = $mform;

        if ($fromform = $mform->get_data()) {
            $this->fromform = $fromform;
//            local_logger\console::log($fromform, "Filter - Fromform");

            $this->formdatadump = '<div class="form-data"><h4>Form data</h4><pre>' .
                var_export($fromform, true) . '</pre></div>';
        }
    }

    /**
     * Get the returned form data, if any
     */
    public function get_fromform() {
        return $this->fromform;
    }

    /**
     * Get the form formdatadump
     */
    public function get_formdatadump() {
        return $this->formdatadump;
    }

    /**
     * Render the form and set the data
     */
    public function render() {
        $this->data->content = $this->mform->render();
        $this->data->content .= $this->formdatadump;
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

    /**
     * Get the user managed category and the template list
     *
     * @return Array An array with the two popup lists
     */
    protected function get_popup_data() {

        $result = array(
            'categories' => array(),
            'templates' => array()
        );

        $plugin_info = get_config('local_friadmin');

        $templatecategoryid = $plugin_info->template_category;
    }
}
