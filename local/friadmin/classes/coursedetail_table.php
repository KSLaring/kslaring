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
        $table_model = new local_friadmin_coursedetail_table_datalist_model($this->courseid);

        if ($result = $table_model->data) {
            $result = $this->format_date($result, array('date', 'deadline'));
            $result = $this->remove_id($result);
            $result = $this->add_courselink($result);
            $result = $this->row_to_columns($result);

            $table->data = $result;
            $out = \html_writer::table($table);
        }

        return $out;
    }

    /**
     * Format the date fields from UNIX timestamp to userdate.
     *
     * @param Array $data The table data
     * @param Array $fields The fields containing dates
     *
     * @return Array The modified table data
     */
    protected function format_date($data, $fields) {
        $result = array();

        foreach ($data as $row) {
            if (is_array($row)) {
                $isarray = true;
                $row = (object)$row;
            } else {
                $isarray = false;
            }

            foreach ($fields as $field) {
                $row->$field = '<span class="nowrap">' .
                    userdate($row->$field, '%Y-%m-%d', 99, false) . '</span>';
            }

            if ($isarray) {
                $result[] = (array)$row;
            } else {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * Remove the courseid
     *
     * @param Array $data The table data
     *
     * @return Array The modified table data
     */
    protected function remove_id($data) {

        unset($data[0]['courseid']);

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

        $data[0]['link'] = '<a href="'.$url.'">'.$url.'</a>';

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

        foreach ($data[0] as $key => $field) {
            $result[] = array(get_string('course_'.$key, 'local_friadmin'), $field);
        }

        return $result;
    }
}
