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

require_once($CFG->dirroot . '/enrol/waitinglist/lib.php');

//use renderable;
//use renderer_base;
//use stdClass;

/**
 * Class containing data for the local_friadmin course_detail table
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_coursedetail_table extends local_friadmin_widget implements renderable {

    // The table column names
    protected $colnames = array("header", "data");

    // The id of the course to display
    protected $courseid = 0;

    /**
     * Construct the coursedetail table renderable.
     */
    public function __construct($courseid) {
        // Create the data object and set the first values
        parent::__construct();

        $this->courseid = $courseid;
    }

    /**
     * Create the Moodle table with the saved data
     *
     * @return String $out The rendered Moodle table
     */
    public function get_table_html() {
        global $CFG;

        $out = '';

        $table = new \html_table();
        $table->id = 'coursedetails';

        $table->colclasses = $this->colnames;
        $table->attributes['cellspacing'] = '0';
        $table->attributes['class'] = 'generaltable';

        // Get the data for the table rows,
        // format the date columns
//        $table_model = new local_friadmin_coursedetail_table_datalist_model($this->courseid);
        $table_model = new local_friadmin_coursedetail_table_sql_model($this->courseid);

        $result = $table_model->data[0];
        if ($result) {
            $result = $this->format_date($result, array('date', 'deadline'));
            // remove the link field, we have the link button
            // $result = $this->add_courselink($result);
            $result = $this->set_responsiblename($result);
            $result = $this->set_course_contacts($result);
            $result = $this->set_availseats($result);
            $result = $this->remove_id($result);
            $result = $this->row_to_columns($result);

            $table->data = $result;
            $out = \html_writer::table($table);
        }

        return $out;
    }

    /**
     * Format the date fields from UNIX timestamp to userdate.
     *
     * @param Array $data   The table data
     * @param Array $fields The fields containing dates
     *
     * @return Array The modified table data
     */
    protected function format_date($data, $fields) {
        foreach ($fields as $field) {
            if (0 == $data[$field]) {
                $data[$field] = '-';
            } else {
                $data[$field] = '<span class="nowrap">' .
                    userdate($data[$field], '%d.%m.%Y', 99, false) . '</span>';
            }
        }

        return $data;
    }

    /**
     * Remove the courseid
     *
     * @param Array $data The table data
     *
     * @return Array The modified table data
     */
    protected function remove_id($data) {

        unset($data['courseid']);

        return $data;
    }

    /**
     * Add a link field
     *
     * @param Array $data The table data
     *
     * @return Array The modified table data
     */
    protected function add_courselink($data) {
        $url = new moodle_url('/course/view.php?id=' . $this->courseid);

        $data['link'] = '<a href="' . $url . '">' . $url . '</a>';

        return $data;
    }

    /**
     * Get the manager = responsible user name
     * if the responsible field holds a number
     *
     * @param Array $data The table data
     *
     * @return Array The modified table data
     */
    protected function set_responsiblename($data) {

        if (is_numeric($data['responsible'])) {
            global $DB;

            if ($user = $DB->get_record('user', array('id' => $data['responsible']))) {
                $data['responsible'] = fullname($user);
            }
        };

        return $data;
    }

    /**
     * Get the manager = responsible user name
     * if the responsible field holds a number
     *
     * @param Array $data The table data
     *
     * @return Array The modified table data
     */
    protected function set_course_contacts($data) {

        if ($data['teacher'] === '-') {
            global $CFG, $DB;

            $course = $DB->get_record('course', array('id' => $this->courseid));
            if ($course instanceof stdClass) {
                require_once($CFG->libdir . '/coursecatlib.php');
                $course = new course_in_list($course);
            }

            if ($course->has_course_contacts()) {
                $teachers = '';
                foreach ($course->get_course_contacts() as $userid => $coursecontact) {
//                    $teachers .= $coursecontact['rolename'] . ': ';
                    $teachers .= $coursecontact['username'] . ' ';
                }
                $data['teacher'] = $teachers;
            }
        };

        return $data;
    }

    /**
     * Add the the available seats for the courses.
     *
     * @param Object $data The table data
     *
     * @return Object The modified table data
     */
    protected function set_availseats($data) {
        global $DB;

        $instance = $DB->get_record('enrol',
            array('courseid' => $data['courseid'], 'enrol' => 'waitinglist'));

        if ($instance) {
            $enrol_waitinglist_plugin = new enrol_waitinglist_plugin();
            $data['seats'] = $enrol_waitinglist_plugin->get_vacancy_count($instance);
        }

        return $data;
    }

    /**
     * Switch the data row to columns with titles in the first column
     *
     * @param Array $data The table data
     *
     * @return Array The modified table data
     */
    protected function row_to_columns($data) {
        $result = array();

        foreach ($data as $key => $field) {
            /**
             * @updateDate  22/06/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Temporay
             */
            if (($key != 'time') && ($key != 'priceinternal') && ($key != 'priceexternal')) {
                $result[] = array(get_string('course_' . $key, 'local_friadmin'), $field);
            }

        }

        return $result;
    }
}
