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

defined('MOODLE_INTERNAL') || die;

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

    /* @var $formdatadump string - The form feeback - for debugging. */
    protected $formdatadump = null;

    /* @var $mform local_friadmin_mysettings_select_form - The Moodle form. */
    protected $mform = null;

    /* @var $fromform object - The returned form data. */
    protected $fromform = null;

    /**
     * Construct the mysettings renderable.
     */
    public function __construct() {
        /* @var $customdata array - The data for the form. */
        $customdata = null;

        try {
            // ToDo 2016-09-28 - the error occurs when the local template folder has been changed.
            // The two subsequent lists are not updated - only the local templates in the changed
            // folder must be listed. Solve the issue.
            // Create the data object and set the first values.
            parent::__construct();

            // Custom data used.
            $customdata = local_friadmin_helper::get_usercategories_data();

            // Create form.
            $mform = new local_friadmin_mysettings_select_form(null, $customdata, 'post',
                '', array('id' => 'mform-mysettings-select'));
            $this->mform = $mform;

            // Collect the input data and save the settings.
            if ($fromform = $mform->get_data()) {
                $this->fromform = $fromform;

                $this->formdatadump = '<div class="form-data"><h4>Form data</h4><pre>' .
                    var_export($fromform, true) . '</pre></div>';

                // Save the settings.
                $this->save_localtempcategory();
                $this->save_preftemplate_selection();

                // When data has been changed the form needs to be rebuild with the changed data.
                // Get the actual custom data for the form.
                $customdata = local_friadmin_helper::get_usercategories_data();

                // Create the form and save it for further processing.
                $mform = new local_friadmin_mysettings_select_form(null, $customdata, 'post',
                    '', array('id' => 'mform-mysettings-select'));
                $this->mform = $mform;
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

        // Check if the category still exists.
        // If not check if there is a DB entry of the local templates category. If so delete it.
        if (!$record->categoryid || !$DB->record_exists('course_categories', array('id' => $record->categoryid))) {
            if ($DB->record_exists('friadmin_local_templates', array('userid' => $USER->id))) {
                $DB->delete_records('friadmin_local_templates', array('userid' => $USER->id));
            }
        } else {
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
    }

    /**
     * Save or update the user's local template selctions.
     */
    protected function save_preftemplate_selection() {
        if (!empty($this->fromform->selpreftemplate)) {
            $this->save_preftemplate_type($this->fromform->selpreftemplate,
                TEMPLATE_TYPE_EVENT);
        }
        if (!empty($this->fromform->selprefnetcoursetemplate)) {
            $this->save_preftemplate_type($this->fromform->selprefnetcoursetemplate,
                TEMPLATE_TYPE_NETCOURSE);
        }
    }

    /**
     * Save or update the user's local template selctions.
     *
     * @param int $courseid The template course id
     * @param int $type     The template type
     *
     * @throws dml_exception
     */
    protected function save_preftemplate_type($courseid, $type) {
        global $DB, $USER;

        $record = new stdClass();
        $record->userid = $USER->id;
        $record->courseid = $courseid;
        $record->type = $type;
        $record->timemodified = time();

        // Check if the course still exists.
        // If not check if there is a DB entry of the templates. If so delete it.
        if (!$record->courseid || !$DB->record_exists('course', array('id' => $record->courseid))) {
            if ($DB->record_exists('friadmin_preferred_template', array('userid' => $USER->id, 'type' => $type))) {
                $DB->delete_records('friadmin_preferred_template', array('userid' => $USER->id, 'type' => $type));
            }
        } else {
            if ($DB->record_exists('friadmin_preferred_template', array('userid' => $USER->id, 'type' => $type))) {
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
}
