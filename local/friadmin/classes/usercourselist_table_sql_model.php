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
 * Model class for the local_friadmin usercourse_list table
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_usercourselist_table_sql_model extends local_friadmin_widget {

    // The user municipality list.
    protected $userleveloneids = null;

    // Categories connected with user.
    protected $myCategories = array();

    // The related filter data returned from the form.
    protected $filterdata = null;

    // The related sort data returned from the table.
    protected $sort = null;

    // SQL to get all the courses connected to a user.
    protected $sql = " SELECT	c.id        	as 'courseid',
                                c.fullname  	as 'name',
                                c.startdate 	as 'date',
                                '-'         	as 'seats',
                                e.deadline  	as 'deadline',
                                cln.value   	as 'length',
                                rgcmu.name  	as 'municipality',
                                rgcse.name  	as 'sector',
                                cl.name     	as 'location'
                       FROM 	{course} c

                           # Get the deadline from enrol
                           JOIN (
                                    SELECT	e.courseid,
                                            IFNULL(MAX(e.customint1), 0) AS 'deadline'
                                    FROM 	{enrol} e
                                    WHERE 	e.status = 0
                                    GROUP BY e.courseid
                                 ) e ON e.courseid = c.id

                           # Get the length
                           LEFT JOIN {course_format_options} 	cln   ON 	cln.courseid 	= c.id
                                                                      AND	cln.name 		= 'length'
                           # Get the course location
                           LEFT JOIN {course_format_options}	clo	  ON	clo.courseid 	= c.id
                                                                      AND	clo.name		= 'course_location'
                           # Get the course location name
                           LEFT JOIN {course_locations} 		cl	  ON 	cl.id 			= clo.value

                           # Get the course sector
                           LEFT JOIN {course_format_options}	cse	  ON	cse.courseid	= c.id
                                                                      AND	cse.name		= 'course_sector'
                           # Get the course sector name
                           LEFT JOIN {report_gen_companydata} 	rgcse ON 	rgcse.id 		= cse.value

                           # Get the municipality
                           LEFT JOIN {report_gen_companydata} 	rgcmu ON 	rgcmu.id 		= cl.levelone

                           # Get only courses with a course home page
                           LEFT JOIN {course_format_options}	csh	  ON	csh.courseid	= c.id
                                                                      AND	csh.name	    = 'homepage'

                           # Get only courses with a course home page which is visible
                           LEFT JOIN {course_format_options}	cshv  ON	cshv.courseid	= c.id
                                                                      AND	cshv.name	    = 'homevisible'";

    /**
     * Construct the courselist_page renderable.
     *
     * @param           $userleveloneids
     * @param   null    $usercategories
     * @param           $filterdata
     * @param           $sort
     *
     * @throws          Exception
     */
    public function __construct($userleveloneids, $usercategories = null, $filterdata, $sort) {
        /* Variables    */

        try {
            // Create the data object and set the first values
            parent::__construct();

            /* Set up the data  */
            $this->userleveloneids = $userleveloneids;
            $this->filterdata = $filterdata;
            $this->sort = $sort;
            $this->myCategories = $usercategories;

            /* Get courses list */
            $this->get_data_from_db();
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//constructor

    /**
     * Rebuild the logical to get the data from DB and to add the filter criteria
     *
     * @throws      Exception
     */
    protected function get_data_from_db() {
        /* Variables    */
        global $DB;
        $params = null;
        $result = null;
        $sqlWhere = null;

        try {
            /* Add Filter   */
            list($sqlWhere, $params) = self::AddCriteria_Filter();
            if ($sqlWhere) {
                $this->sql .= $sqlWhere;
            }//if_sqlWhere

            /* Add Sort */
            if ($this->sort) {
                $this->sql .= ' ORDER BY ' . $this->sort;
            }//if_sort

            /* Add the course limit */
            $this->sql .= ' LIMIT ' . (local_friadmin\friadmin::MAX_LISTED_COURSES + 1);

            /* Execute  */
            $result = $DB->get_records_sql($this->sql, $params);

            $this->data = $result;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_data_from_db

    /**
     * Get the filter criteria
     *
     * @return          array
     * @throws          Exception
     */
    private function AddCriteria_Filter() {
        /* Variables    */
        global $DB;
        $params = array();
        $categories = null;
        $sqlWhere = null;
        $filterData = null;

        try {
            /* Courses with a visible homepage only, and they must be visible */
            if (!$sqlWhere) {
                $sqlWhere = " WHERE ";
            } else {
                $sqlWhere .= " AND ";
            }//if_selWhere
            $sqlWhere .= " csh.value = 1 AND cshv.value = 1 AND c.visible = 1 ";

            /* Get filter criteria from the form */
            $filterData = $this->filterdata;
            if ($filterData) {
                /**
                 * @updateDate  02/12/2015
                 * @author      eFaktor     (fbv)
                 *
                 * Description
                 * Add Only eLearning Courses
                 */
                /* Add Only Classroom Courses   */
                if (isset($filterData['classroom']) && ($filterData['classroom'])) {
                    if (!$sqlWhere) {
                        $sqlWhere = " WHERE ";
                    }else {
                        $sqlWhere .= " AND ";
                    }//if_selWhere

                    if (isset($filterData['elearning']) && ($filterData['elearning'])) {
                        $sqlWhere .= " (c.format like '%classroom%' OR c.format like '%netcourse%' OR c.format like '%elearning%') ";
                    }else {
                        $sqlWhere .= " c.format like '%classroom%' ";
                    }//if_elearning_courses
                }else {
                    if (isset($filterData['elearning']) && ($filterData['elearning'])) {
                        if (!$sqlWhere) {
                            $sqlWhere = " WHERE ";
                        }else {
                            $sqlWhere .= " AND ";
                        }//if_selWhere

                        $sqlWhere .= " (c.format like '%netcourse%' OR c.format like '%elearning%') ";
                    }//if_elearning_courses
                }//if_classroom

                /* Municipality Filter  */
                if (isset($filterData['selmunicipality']) && ($filterData['selmunicipality'])) {
                    /* Categories   */
                    if ($this->myCategories) {
                        $categories = implode(',', array_keys($this->myCategories));
                        if (!$sqlWhere) {
                            $sqlWhere = " WHERE ";
                        } else {
                            $sqlWhere .= " AND ";
                        }//if_selWhere
                        $sqlWhere .= " c.category IN ($categories) ";
                    }//if_Categories

                    if (!$sqlWhere) {
                        $sqlWhere = " WHERE ";
                    } else {
                        $sqlWhere .= " AND ";
                    }//if_selWhere
                    $sqlWhere .= " rgcmu.id = :selmunicipality ";
                    $params['selmunicipality'] = $filterData['selmunicipality'];
                }//if_selmunicipality

                /* Location Filter      */
                if (isset($filterData['sellocation']) && ($filterData['sellocation'])) {
                    if (!$sqlWhere) {
                        $sqlWhere = " WHERE ";
                    } else {
                        $sqlWhere .= " AND ";
                    }//if_selWhere
                    $sqlWhere .= " cl.id = :sellocation ";
                    $params['sellocation'] = $filterData['sellocation'];
                }//if_location

                /* Sector Filter        */
                if (isset($filterData['selsector']) && ($filterData['selsector'])) {
                    if (!$sqlWhere) {
                        $sqlWhere = " WHERE ";
                    } else {
                        $sqlWhere .= " AND ";
                    }//if_selWhere
                    $sqlWhere .= " rgcse.id = :selsector ";
                    $params['selsector'] = $filterData['selsector'];
                }//if_sector

                /* From Time Filter     */
                if (isset($filterData['seltimefrom']) && ($filterData['seltimefrom'])) {
                    if (!$sqlWhere) {
                        $sqlWhere = " WHERE ";
                    } else {
                        $sqlWhere .= " AND ";
                    }//if_selWhere
                    $sqlWhere .= " c.startdate >= :seltimefrom ";
                    $params['seltimefrom'] = $filterData['seltimefrom'];
                }//if_seltimeFrom

                /* To Time Filter       */
                if (isset($filterData['seltimeto']) && ($filterData['seltimeto'])) {
                    if (!$sqlWhere) {
                        $sqlWhere = " WHERE ";
                    } else {
                        $sqlWhere .= " AND ";
                    }//if_selWhere
                    $sqlWhere .= " c.startdate <= :seltimeto ";
                    $params['seltimeto'] = $filterData['seltimeto'];
                }//if_seltimeto

                /* Course Name Filter   */
                if (isset($filterData['selname']) && ($filterData['selname'])) {
                    if (!$sqlWhere) {
                        $sqlWhere = " WHERE ";
                    } else {
                        $sqlWhere .= " AND ";
                    }//if_selWhere
                    $sqlWhere .= $DB->sql_like('c.fullname', ':selname', false, false);
                    $params['selname'] = "%" . $filterData['selname'] . "%";
                }//if_selName
            }//if_filterData

            return array($sqlWhere, $params);
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddCategories_Filter
}
