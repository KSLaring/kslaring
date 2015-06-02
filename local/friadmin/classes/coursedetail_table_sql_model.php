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
 * Model class for the local_friadmin course_list table
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_coursedetail_table_sql_model extends local_friadmin_widget {

    // The related filter data returned from the form
    protected $filterdata = null;

    // The query SQL
    protected $sql = "
      SELECT
          c.id        courseid,
          c.fullname  name,
          c.summary   summary,
          '-'         targetgroup,
          c.startdate date,
          '-'         time,
          cln.length  length,
          rgcmu.name  municipality,
          rgcse.name  sector,
          cl.name     location,
          cmn.manager responsible,
          '-'         teacher,
          '-'         priceinternal,
          '-'         priceexternal,
          '-'         seats,
          e.deadline  deadline
      FROM {course} c
      # Get the deadline from enrol
        JOIN (
               SELECT
                 e.courseid,
                 IFNULL(MAX(e.customint1), 0) AS deadline
               FROM {enrol} e
               WHERE e.status = 0
               GROUP BY e.courseid
             ) e ON e.courseid = c.id
      # Get the length
        JOIN (
               SELECT
                 cfo.courseid,
                 cfo.value AS 'length'
               FROM {course_format_options} cfo
               WHERE cfo.name = 'length'
             ) cln ON cln.courseid = c.id
        # Get the manager id = responsible
          JOIN (
                 SELECT
                   cfo.courseid,
                   cfo.value AS 'manager'
                 FROM {course_format_options} cfo
                 WHERE cfo.name = 'manager'
               ) cmn ON cmn.courseid = c.id
      # Get the course location
        JOIN (
               SELECT
                 cfo.courseid,
                 cfo.value AS 'location'
               FROM {course_format_options} cfo
               WHERE cfo.name = 'course_location'
             ) clo ON clo.courseid = c.id
      # Get the course location name
        JOIN {course_locations} cl
          ON clo.location = cl.id
      # Get the course sector
        JOIN (
               SELECT
                 cfo.courseid,
                 cfo.value AS 'sector'
               FROM {course_format_options} cfo
               WHERE cfo.name = 'course_sector'
             ) cse ON cse.courseid = c.id
      # Get the course sector name
        JOIN {report_gen_companydata} rgcse
          ON rgcse.id = cse.sector
      # Get the municipality
        JOIN {report_gen_companydata} rgcmu
          ON rgcmu.id = cl.levelone
    ";

    /**
     * Construct the coursedetail_page renderable.
     */
    public function __construct($courseid) {
        // Create the data object and set the first values
        parent::__construct();

        $filterdata = new \stdClass();
        $filterdata->selcourseid = $courseid;
        $this->filterdata = $filterdata;

        $this->get_data_from_db();
    }

    /**
     * Get the data from the DB and save it in the $data property
     */
    protected function get_data_from_db() {
        global $DB;

        $result = array();

        $sql = $this->add_filters($this->sql);

        $result = $DB->get_record_sql($sql);

        // Save an array with an associative data array to make the sql model
        // and the fixture model compatible
        $this->data = array((array)$result);
    }

    /**
     * Add filters.
     *
     * @param String $sql The SQL query
     *
     * @return Array An array with the extended SQL and the parameters
     */
    protected function add_filters($sql) {

        if (is_null($this->filterdata)) {
            return $sql;
        }

        if (!empty($this->filterdata->selcourseid)) {
            $sql .= " WHERE c.id = " . $this->filterdata->selcourseid;
        }

        return $sql;
    }
}
