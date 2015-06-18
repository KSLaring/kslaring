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

    /**
     * @var         string
     *
     * @updateDate  18/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Rebuild the query
     */
    protected $sql = " SELECT	c.id        as 'courseid',
                                c.fullname  as 'name',
                                c.summary   as 'summary',
                                '-'         as 'targetgroup',
                                c.startdate as 'date',
                                '-'         as 'time',
                                cln.value  	as 'length',
                                rgcmu.name  as 'municipality',
                                rgcse.name  as 'sector',
                                cl.name     as 'location',
                                cmn.value 	as 'responsible',
                                '-'         as 'teacher',
                                '-'         as 'priceinternal',
                                '-'         as 'priceexternal',
                                '-'         as 'seats',
                                e.deadline  as 'deadline'
                       FROM 	{course} c
                          # Get the deadline from enrol
                          JOIN (
                                 SELECT	e.courseid,
                                        IFNULL(MAX(e.customint1), 0) AS deadline
                                 FROM 	{enrol} e
                                 WHERE 	e.status = 0
                                 GROUP BY e.courseid
                               ) e ON e.courseid = c.id
                          # Get the length
                          JOIN 	{course_format_options}	    cln 	ON 	cln.courseid 	= c.id
                                                                    AND	cln.name 		= 'length'
                          # Get the manager id = responsible
                          JOIN 	{course_format_options}	    cmn		ON 	cmn.courseid	= c.id
                                                                    AND cmn.name		= 'manager'
                          # Get the course location
                          JOIN 	{course_format_options}	    clo		ON  clo.courseid	= c.id
                                                                    AND	clo.name 		= 'course_location'
                          # Get the course location name
                          JOIN 	{course_locations} 		    cl		ON 	cl.id 			= clo.value
                          # Get the course sector
                          JOIN 	{course_format_options}	    cse		ON 	cse.courseid 	= c.id
                                                                    AND	cse.name		= 'course_sector'
                          # Get the course sector name
                          JOIN 	{report_gen_companydata} 	rgcse	ON 	rgcse.id 		= cse.value
                          # Get the municipality
                          JOIN 	{report_gen_companydata} 	rgcmu 	ON 	rgcmu.id 		= cl.levelone ";

    // The query SQL
    protected $sql_old = "
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
