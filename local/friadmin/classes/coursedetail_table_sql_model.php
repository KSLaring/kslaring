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
     *
     * @updateDate  22/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add LEFT
     *
     * @updateDate  23/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Replace customint1 by enrolenddate
     * Add 'Time From To'
     *
     */
    protected $sql = " SELECT	c.id            as 'courseid',
                                c.fullname      as 'name',
                                c.summary       as 'summary',
                                '-'             as 'targetgroup',
                                c.startdate     as 'date',
                                cft.value       as 'time',
                                cln.value  	    as 'length',
                                rgcmu.name      as 'municipality',
                                rgcse.name      as 'sector',
                                cl.name         as 'location',
                                cmn.value 	    as 'responsible',
                                '-'             as 'teacher',
                                IF(ep.customtext3,ep.customtext3,0)  as 'priceinternal',
                                IF(ep.customtext4,ep.customtext4,0)  as 'priceexternal',
                                '-'             as 'seats',
                                e.deadline      as 'deadline'
                       FROM 	{course} c
                          # Get the deadline from enrol
                          JOIN (
                                 SELECT	e.courseid,
                                        IFNULL(MAX(e.customint1), 0) AS 'deadline'
                                 FROM 	{enrol} e
                                 WHERE 	e.status = 0
                                 GROUP BY e.courseid
                               ) e ON e.courseid = c.id
	                      # Get price
                          LEFT JOIN	{enrol} 					ep 		ON 	ep.courseid = c.id
														                AND	ep.status 	= 0
                                                                        AND ep.enrol 	= 'waitinglist'
                          # Get the Time From - To
                          LEFT JOIN {course_format_options}	    cft		ON	cft.courseid	= c.id
													                    AND cft.name		= 'time'
                          # Get the length
                          LEFT JOIN {course_format_options}	    cln 	ON 	cln.courseid 	= c.id
                                                                        AND	cln.name 		= 'length'
                          # Get the manager id = responsible
                          LEFT JOIN {course_format_options}	    cmn		ON 	cmn.courseid	= c.id
                                                                        AND cmn.name		= 'manager'
                          # Get the course location
                          LEFT JOIN {course_format_options}	    clo		ON  clo.courseid	= c.id
                                                                        AND	clo.name 		= 'course_location'
                          # Get the course location name
                          LEFT JOIN {course_locations} 		    cl		ON 	cl.id 			= clo.value
                          # Get the course sector
                          LEFT JOIN {course_format_options}	    cse		ON 	cse.courseid 	= c.id
                                                                        AND	cse.name		= 'course_sector'
                          # Get the course sector name
                          LEFT JOIN {report_gen_companydata} 	rgcse	ON 	rgcse.id 		= cse.value
                          # Get the municipality
                          LEFT JOIN {report_gen_companydata} 	rgcmu 	ON 	rgcmu.id 		= cl.levelone ";

    /**
     * @param           $courseId
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Construct the coursedetail_page renderable.
     *
     * @updateDate      26/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add exception,comments..
     */
    public function __construct($courseId) {
        /* Variables    */
        $filterData = null;

        try {
            // Create the data object and set the first values
            parent::__construct();

            /* Filter Data Structure    */
            $filterData = new \stdClass();
            $filterData->selcourseid    = $courseId;
            $this->filterdata           = $filterData;

            /* Get the course detail    */
            $this->get_data_from_db();
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//constructor

    /**
     * Get the data from the DB and save it in the $data property
     */
    /**
     * @throws      Exception
     *
     * @updateDate  22/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add comments, exception
     * Add teachers
     */
    protected function get_data_from_db() {
        /* Variables    */
        global $DB;
        $result     = array();
        $teachers   = null;

        try {
            /* Add Filter   */
            $sql = $this->add_filters($this->sql);

            /* Execute  */
            $result = $DB->get_record_sql($sql);

            /* Format Time From - To    */
            /* Teachers                 */
            if ($result) {
                /* Format for Time From - To    */
                if ($result->time) {
                    $result->time = str_replace(',','</br>',$result->time);
                }else {
                    $result->time = '-';
                }//if_time

                /* Add Teachers */
                $teachers = self::getTeachers_Course($result->courseid);
                /* Add the teachers */
                if ($teachers) {
                    $result->teacher = implode(', ',$teachers);
                }//if_teachers
            }//if_result

            // Save an array with an associative data array to make the sql model
            // and the fixture model compatible
            $this->data = array((array)$result);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }

    /**
     * Add filters.
     *
     * @param String $sql The SQL query
     *
     * @return Array An array with the extended SQL and the parameters
     */
    /**
     * @param       $sql
     * @return      string
     * @throws      Exception
     *
     * @updateDate  22/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add Exception and comments
     */
    protected function add_filters($sql) {
        /* Variables    */
        $courseContext  = null;

        try {
            /* Filter Data  - Course Id */
            if (isset($this->filterdata->selcourseid) && ($this->filterdata->selcourseid)) {
                /* Course Id Criteria   */
                $sql .= " WHERE c.id = " . $this->filterdata->selcourseid;
            }//course_id

            return $sql;
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//add_filters

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

    /**
     * @param           $courseId
     * @return          array
     * @throws          Exception
     *
     * @creationDate    22/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the teachers connected with
     */
    private function getTeachers_Course($courseId) {
        /* Variables    */
        global $DB;
        $teachers   = array();
        $context    = null;
        $params     = null;

        try {
            /* Context Course   */
            $context = CONTEXT_COURSE::instance($courseId);

            /* Search Criteria  */
            $params = array();
            $params['context'] = $context->id;


            /* SQL Instruction  */
            $sql = " SELECT 	DISTINCT 	u.id,
                                            CONCAT(u.firstname, ' ' , u.lastname) as 'name'
                     FROM		{user}				u
                        JOIN	{role_assignments}	ra	ON 	ra.userid 		= u.id
                                                        AND	ra.contextid    = :context
                        JOIN	{role}				r	ON	r.id 			= ra.roleid
                                                        AND	r.archetype 	IN ('teacher','editingteacher')
                     WHERE      u.deleted = 0";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                foreach ($rdo as $instance) {
                    $teachers[$instance->id] = $instance->name;
                }//for_rdo_teaches
            }//if_rdo

            return $teachers;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getTeachers_Course
}
