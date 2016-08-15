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
 * Class containing data for the local_friadmin mysettings selection area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_mysettings_select extends local_friadmin_widget implements renderable {

    // The form feeback - for debugging
    protected $formdatadump = null;

    // The Moodle form
    protected $mform = null;

    // The returned form data
    protected $fromform = null;

    /**
     * Construct the mysettings renderable.
     */
    public function __construct() {
        /* Variables    */
        $customdata = null;

        try {
            // Create the data object and set the first values
            parent::__construct();

            /* custom data used */
            $customdata = local_friadmin_helper::get_usercategories_data();

            /* Create form  */
            $mform = new local_friadmin_mysettings_select_form(null, $customdata, 'post',
                '', array('id' => 'mform-mysettings-select'));
            $this->mform = $mform;

            /* Collect the input data and save the settings */
            if ($fromform = $mform->get_data()) {
                $this->fromform = $fromform;

                $this->formdatadump = '<div class="form-data"><h4>Form data</h4><pre>' .
                    var_export($fromform, true) . '</pre></div>';

                /* Save the settings */
                $this->save_localtempcategory();
                $this->save_preftemplate_selection();

            }//if_get_data
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//construct

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

    /**
     * Save or update the user's local template category.
     */
    protected function save_localtempcategory() {
        global $DB, $USER;

        $record = new stdClass();
        $record->userid = $USER->id;
        $record->categoryid = $this->fromform->selcategory;
        $record->timemodified = time();

        if ($DB->record_exists('friadmin_local_templates', array('userid' => $USER->id))) {
            // Update the record.
            $recordid = $DB->get_field('friadmin_local_templates', 'id',
                array('userid' => $USER->id));
            $record->id = $recordid;
            $DB->update_record('friadmin_local_templates', $record);
        } else {
            // Insert a new record.
            $DB->insert_record('friadmin_local_templates', $record);
        }
    }

    /**
     * Save or update the user's local template selctions.
     */
    protected function save_preftemplate_selection() {
        $this->save_preftemplate_type($this->fromform->selpreftemplate,
            TEMPLATE_TYPE_EVENT);
        $this->save_preftemplate_type($this->fromform->selprefnetcoursetemplate,
            TEMPLATE_TYPE_NETCOURSE);
    }

    /**
     * Save or update the user's local template selctions.
     *
     * @param int $courseid The template course id
     * @param int $type     The template type
     */
    protected function save_preftemplate_type($courseid, $type) {
        global $DB, $USER;

        $record = new stdClass();
        $record->userid = $USER->id;
        $record->courseid = $courseid;
        $record->type = $type;
        $record->timemodified = time();

        if ($DB->record_exists('friadmin_preferred_template',
            array('userid' => $USER->id, 'type' => $type))
        ) {
            // Update the record.
            $recordid = $DB->get_field('friadmin_preferred_template', 'id',
                array('userid' => $USER->id, 'type' => $type));
            $record->id = $recordid;
            $DB->update_record('friadmin_preferred_template', $record);
        } else {
            // Insert a new record.
            $DB->insert_record('friadmin_preferred_template', $record);
        }
    }
}
